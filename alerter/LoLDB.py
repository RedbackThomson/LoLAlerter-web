import MySQLdb
from AlerterLogger import AlerterLogger

class LoLDB:
	login_host, login_user, login_pass, login_db = "localhost", "root", "admin", "lolalerter"

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