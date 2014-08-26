import MySQLdb
from AlerterLogger import AlerterLogger

class LoLDB:
	login_host, login_user, login_pass, login_db = "", "", "", ""

	def Connect(self):
		self.conn = MySQLdb.connect(host=self.login_host,user=self.login_user,passwd=self.login_pass,db=self.login_db)
		self.conn.autocommit(True)

	def Close(self):
		self.conn.close()

	def CheckUserExists(self, summoner_id):
		cursor = self.conn.cursor()
		cursor.execute("SELECT ID FROM `summoners` WHERE `SummonerID`= " + summoner_id + ";")
		return (cursor.fetchone() != None)

	def GetUserBySummonerId(self, summoner_id):
		cursor = self.conn.cursor()
		cursor.execute("SELECT * FROM `users` WHERE `ID`= (SELECT `User` FROM `summoners` WHERE `SummonerID`= "+str(summoner_id)+");")
		return cursor.fetchone()

	def IncrementOnlineUsers(self):
		cursor = self.conn.cursor()
		cursor.execute("UPDATE `statistics` SET `Value` = `Value` + 1 WHERE `Key`='OnlineUsers';")

	def DecrementOnlineUsers(self):
		cursor = self.conn.cursor()
		cursor.execute("UPDATE `statistics` SET `Value` = `Value` - 1 WHERE `Key`='OnlineUsers';")

	def ResetOnlineUsers(self):
		cursor = self.conn.cursor()
		cursor.execute("UPDATE `statistics` SET `Value` = 0 WHERE `Key`='OnlineUsers';")

	def IncrementTotalSubscribed(self):
		cursor = self.conn.cursor()
		cursor.execute("UPDATE `statistics` SET `Value` = `Value` + 1 WHERE `Key`='TotalSubscribed';")

	def GetLatestNotice(self):
		cursor = self.conn.cursor()
		cursor.execute("SELECT * FROM `notices` ORDER BY `Timestamp` DESC LIMIT 1;")
		return cursor.fetchone()

	def UpdateNotice(self, user_id, notice):
		cursor = self.conn.cursor()
		cursor.execute("UPDATE `users` SET `LastNotice`="+str(notice)+" WHERE `ID`="+str(user_id)+";")

	def GetSetting(self, key):
		cursor = self.conn.cursor()
		cursor.execute("SELECT * FROM `settings` WHERE `Key`='"+key+"';")
		return (cursor.fetchone())[1]