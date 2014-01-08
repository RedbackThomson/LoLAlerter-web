from SubscriberFetcher import SubscriberFetcher
from AlerterLogger import AlerterLogger

class AlerterUser:
	token, twitch_username, summoner_id, newsub_callback = "", "", "", ""

	def __init__(self, newsub_callback, twitch_username, summoner_id, token):
		self.token = token
		self.twitch_username = twitch_username
		self.summoner_id = summoner_id
		self.newsub_callback = newsub_callback
		self.subFetcher = SubscriberFetcher(self.SendMessage, twitch_username, token)

	def Start(self):
		"""Create a connection to the base"""
		self.subFetcher.Start()

	def Stop(self):
		self.subFetcher.alive = False

	def SendMessage(self, newsub_username):
		self.newsub_callback(self.summoner_id, newsub_username)