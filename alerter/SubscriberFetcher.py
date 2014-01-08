import urllib2
import json
import time
import threading
from AlerterLogger import AlerterLogger

class SubscriberFetcher:
	SUB_LOCATION = "https://api.twitch.tv/kraken/channels/{0}/subscriptions?direction=desc&limit={1}&oauth_token={2}"
	token, twitch_username, newsub_callback, alive = "", "", "", True
	run_thread = ""
	last_sub = 0

	def __init__(self, newsub_callback, twitch_username, token):
		self.token = token
		self.twitch_username = twitch_username
		self.newsub_callback = newsub_callback
	
	def Start(self):
		self.run_thread = threading.Thread(target = self._run, args=())
		self.run_thread.start()

	def _run(self):
		while(self.alive):
			time.sleep(5)
			current_sub = self._getLatestSubscriber()
			if(current_sub is None):
				self._throwError('Error getting info for: ' + self.twitch_username)
				AlerterLogger.logger.error('Error getting info for: ' + self.twitch_username)
			else:
				current_id = current_sub['_id']
				if(self.last_sub == 0):
					self.last_sub = current_id
				else:
					if(self.last_sub != current_id):
						self.last_sub = current_id
						self.newsub_callback(current_sub['display_name'])

	def _getLatestSubscriber(self):
		#Change it so it's not Mylixia
		#url = self.SUB_LOCATION.format(self.twitch_username, 1, self.token)
		url = self.SUB_LOCATION.format('Nightblue3', 1, '3d6upau5a7oq7znl2vjaan20f3mmpk3')
		try:
			response = urllib2.urlopen(url)
			json = response.read()
			return self._parseLatestSubscriber(json)
		except Exception, e:
			return None

	def _parseLatestSubscriber(self, jsonData):
		subs = json.loads(jsonData)
		return subs['subscriptions'][0]['user']

	def _throwError(self, message):
		print '[LoLAlerter] Error: ' + message			