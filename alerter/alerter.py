#!/usr/bin/python

import threading
import os
import inspect

from AlerterUser import AlerterUser
from AlerterLogger import AlerterLogger
from LoLChat import LoLChat
from LoLDB import LoLDB

class LoLAlerter:
	lol_username, lol_password, lol_id = "", "", 0
	current_alerts = dict()

	def __init__(self):
		AlerterLogger.InitLogger()
		self.loldb = LoLDB()
		self.loldb.Connect()
		print '[LoLAlerter] Loaded Database' 
		self.LoadSummonerSettings()
		self.lolchat = LoLChat(self, self.loldb, self.lol_username, self.lol_password)

	def LoadSummonerSettings(self):
		self.lol_username = self.loldb.GetSetting("LoLUsername")
		self.lol_password = self.loldb.GetSetting("LoLPassword")
		self.lol_id = self.loldb.GetSetting("LoLSummonerID")

	def Start(self):
		#Connect to the XMPP
		self.lolchat.Start()
		print '[LoLAlerter] Started'

		#Reset Counters
		self.loldb.ResetOnlineUsers()

	def Stop(self):
		self.loldb.Stop()
		print '[LoLAlerter] Stopped'

	def NewUser(self, summoner_id):
		try:
			if(summoner_id == self.lol_id): return
			user = self.loldb.GetUserBySummonerId(summoner_id)
			if(user == None): 
				#Freeloader
				self.SendMessage(summoner_id, "This summoner has not been configured.")
				self.lolchat.UnFriend(summoner_id)

			self.CheckUserNotices(user, summoner_id)

			print '[LoLAlerter] User Online: ' + str(user[2]) + '@' + str(summoner_id)
			AlerterLogger.logger.info('User Online: ' + str(user[2]) + '@' + str(summoner_id))
			self.loldb.IncrementOnlineUsers()
			if(user[2] in self.current_alerts): 
				##Chat might have reset - no need to restart the whole thread service
				self.current_alerts[user[2]].Stop()
				del self.current_alerts[user[2]]

			self.current_alerts[user[2]] = AlerterUser(self.SendNewSub, user[2], summoner_id, user[3])
			self.current_alerts[user[2]].Start()
		except Exception, e:
			print e
			AlerterLogger.logger.error(str(e))

	def UserOff(self, summoner_id):
		if(summoner_id == self.lol_id): return
		user = self.loldb.GetUserBySummonerId(summoner_id)
		if(user == None): return

		print '[LoLAlerter] User Offline: ' + str(user[2]) + '@' + str(summoner_id)
		AlerterLogger.logger.info('User Offline: ' + str(user[2]) + '@' + str(summoner_id))
		self.loldb.DecrementOnlineUsers()
		if(user[2] in self.current_alerts): 
			self.current_alerts[user[2]].Stop()
			del self.current_alerts[user[2]]

	def SendMessage(self, target, message):
		self.lolchat.SendMessage(target, message)

	def SendNewSub(self, target, new_sub):
		"""Send new sub message"""
		print '[LoLAlerter] Sending {} to {}'.format(new_sub, target)
		AlerterLogger.logger.info('Sending {} to {}'.format(new_sub, target))
		self.loldb.IncrementTotalSubscribed()
		message = '{} has just subscribed!'.format(new_sub)
		self.SendMessage(target, message)

	def CheckUserNotices(self, user, summoner_id):
		lastNotice = user[5]
		latestNotice = self.loldb.GetLatestNotice()
		#Hasn't received the latest message
		if (latestNotice[0] > lastNotice):
			self.lolchat.SendMessage(summoner_id, latestNotice[1])
			self.loldb.UpdateNotice(user[0], latestNotice[0])

	def Broadcast(self, message):
		for username, alert in self.current_alerts.iteritems():
			AlerterLogger.logger.info('Sending broadcast to {}'.format(username))
			self.SendMessage(alert.summoner_id, message)

	def GetOnline(self):
		return self.current_alerts.keys()

	def Restart(self):
		for username, alert in self.current_alerts.iteritems():
			alert.Stop()
		self.current_alerts = dict()

def checkPidRunning(pid):        
    try:
        os.kill(pid, 0)
    except OSError:
        return False
    else:
        return True

def writePID():
	pid = str(os.getpid())
	pidpath = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) # script directory
	pidfile = os.path.join(pidpath, 'tmp_alerter.pid')

	if os.path.isfile(pidfile) and checkPidRunning(int(file(pidfile,'r').readlines()[0])):
		print "%s already exists, exiting" % pidfile
		sys.exit()
	else:
		file(pidfile, 'w').write(pid)

#Entry Point
if __name__ == '__main__':
	writePID()

	lolAlerter = LoLAlerter()
	lolAlerter.Start()