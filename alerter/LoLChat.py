import ssl
from sleekxmpp import ClientXMPP
from AlerterLogger import AlerterLogger

class LoLChat(ClientXMPP):
	ADDRESS, ADDRESS_PORT, SERVER = 'chat.na1.lol.riotgames.com', 5223, 'pvp.net'
	users = []

	def __init__(self, lolalerter, loldb, jid, password):
		self.lolalerter = lolalerter
		self.loldb = loldb

		"""Create the XMPP connection"""
		ClientXMPP.__init__(self, jid + '@' + LoLChat.SERVER + '/xiff', 'AIR_' + password)

		"""Automatically add new friends?"""
		self.auto_authorize = None
		self.auto_subscribe = True
		self.ssl_version = ssl.PROTOCOL_SSLv3

		""" Add the event handlers """
		self.add_event_handler("session_start", self._session_start)
		self.add_event_handler("message", self._message)
		self.add_event_handler("got_online", self._got_online)
		self.add_event_handler("got_offline", self._got_offline)
		self.add_event_handler("presence_subscribe", self._presence_subscribe)
		self.add_event_handler("presence_unsubscribe", self._presence_unsubscribe)
		self.add_event_handler("disconnected", self._disconnected)

	def Start(self):
		address = (LoLChat.ADDRESS, LoLChat.ADDRESS_PORT)
		AlerterLogger.logger.info('Connecting to server...')
		self.connect(address, True, False, True)
		self.process(block=False)

	def SendMessage(self, target, message):
		self.send_message(mto='sum'+target+'@'+LoLChat.SERVER+'/xiff', mbody=message, mtype='chat')

	def _session_start(self, event):
		self.send_presence(-1, self._getPresenceString('Bot Online'))
		self.get_roster()

	def _message(self, msg):
		if msg['type'] in ('chat', 'normal'):
			AlerterLogger.logger.info('Recieved message ('+str(msg['from'])+'): ' + str(msg['body']))
			msgResponse = self._processMessage(str(msg['body']), self._getSummonerId(str(msg['from'])))
			if(msgResponse is not None):
				msg.reply(msgResponse).send()

	def _got_online(self, presence):
		AlerterLogger.logger.info('Friend Online: ' + str(presence['from']))
		newUser = self._getSummonerId(str(presence['from']))
		self.lolalerter.NewUser(newUser)

	def _got_offline(self, presence):
		AlerterLogger.logger.info('Friend Offline: ' + str(presence['from']))
		newUser = self._getSummonerId(str(presence['from']))
		self.lolalerter.UserOff(newUser)

	def _presence_subscribe(self, presence):
		requestor = self._getSummonerId(str(presence['from']))
		toAccept = self.lolalerter.loldb.CheckUserExists(requestor)
		print '[LoLAlerter] Friendship Requested: ' + str(requestor) + ' : ' + str(toAccept)
		AlerterLogger.logger.info('Friendship Requested: ' + str(requestor) + ' : ' + str(toAccept))
		if(toAccept):
			self.sendPresence(pto=presence['from'], ptype='subscribed')
			self.sendPresence(pto=presence['from'], ptype='subscribe')
		else:
			self.sendPresence(pto=presence['from'], ptype='unsubscribed')
			self.sendPresence(pto=presence['from'], ptype='unsubscribe')

	def _presence_unsubscribe(self, presence):
		print 'unsubscribed: ' + str(presence['from'])

	def _disconnected(self):
		self.lolalerter.Restart()

	def _getSummonerId(self, fromID):
		return fromID.replace('@'+LoLChat.SERVER, '').replace('sum','').replace('/xiff', '').replace('/lolapp.me', '').replace('/Smack', '')

	def _getPresenceString(self, message):
		return '<body><profileIcon>668</profileIcon><level>1</level><wins>0</wins><leaves>0</leaves>'+\
		'<queueType>RANKED_SOLO_5x5</queueType><rankedWins>0</rankedWins><rankedLosses>0</rankedLosses>'+\
		'<rankedRating>0</rankedRating><statusMsg>'+message+\
		'</statusMsg><gameStatus>outOfGame</gameStatus><tier>PLATINUM</tier></body>'

	def _processMessage(self, message_body, sender):
		firstChar = message_body[0]

		if(firstChar is '!'):
			#Commands go here
			split = message_body[1:].split(' ')
			if(split[0].lower() == 'hello'):
				return 'Hi!'
			elif (split[0].lower() == 'info'):
				return 'This is the LoLAlerter bot run by Redback93 (or Intercontinent).'+\
				' For more information, visit http://LoLAlerter.softcode.co/'
			elif sender == "31186414":
				if split[0].lower() == 'message' and len(split) >= 3:
					newSplit = message_body[1:].split(' ', 2)
					self.SendMessage(newSplit[1], newSplit[2])
				elif split[0].lower() == 'broadcast' and len(split) >= 2:
					newSplit = message_body[1:].split(' ', 1)
					self.lolalerter.Broadcast(newSplit[1])
		else:
			self.SendMessage('31186414', sender + ': '+message_body)
