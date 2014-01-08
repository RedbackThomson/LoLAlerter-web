from AlerterUser import AlerterUser
from AlerterLogger import AlerterLogger
from LoLChat import LoLChat
from LoLDB import LoLDB

class LoLAlerter:
	lol_username, lol_password, lol_id = "LoLAlerter", "1PoJTWDdgqeG", 51012155
	current_alerts = dict()

	def __init__(self):
		AlerterLogger.InitLogger()
		self.loldb = LoLDB()
		self.lolchat = LoLChat(self, self.loldb, self.lol_username, self.lol_password)

	def Start(self):
		"""Connect to the XMPP"""
		self.loldb.Connect()
		self.lolchat.Start()
		print '[LoLAlerter] Started'

	def Stop(self):
		self.loldb.Stop()
		print '[LoLAlerter] Stopped'

	def NewUser(self, summoner_id):
		if(summoner_id == self.lol_id): return
		user = self.loldb.GetUserBySummonerId(summoner_id)
		if(user == None): return

		if(user[2] in self.current_alerts): 
			##Chat might have reset - no need to restart the whole thread service
			self.current_alerts[user[2]].summoner_id = summoner_id
		else:
			AlerterLogger.logger.info('User Online: ' + str(user[2]) + '@' + str(summoner_id))
			self.current_alerts[user[2]] = AlerterUser(self.SendNewSub, user[2], summoner_id, user[3])
			self.current_alerts[user[2]].Start()

	def UserOff(self, summoner_id):
		if(summoner_id == self.lol_id): return
		user = self.loldb.GetUserBySummonerId(summoner_id)
		if(user == None): return

		AlerterLogger.logger.info('User Offline: ' + str(user[2]) + '@' + str(summoner_id))
		if(user[2] in self.current_alerts): self.current_alerts[user[2]].Stop()

	def SendMessage(self, target, message):
		self.lolchat.SendMessage(target, message)

	def SendNewSub(self, target, new_sub):
		"""Send new sub message"""
		AlerterLogger.logger.info('Sending {} to {}'.format(new_sub, target))
		message = '{} has just subscribed!'.format(new_sub)
		self.SendMessage(target, message)

lolAlerter = LoLAlerter()
lolAlerter.Start()