import logging
import os, sys

class AlerterLogger:
	@staticmethod
	def InitLogger():
		AlerterLogger.logger = logging.getLogger('LoLAlerter')
		logPath = os.path.dirname(__file__) + '/log/'
		if not os.path.exists(logPath):
			os.makedirs(logPath)

		hdlr = logging.FileHandler(logPath + 'LoLAlerter.log')
		hdlr.setFormatter(logging.Formatter('%(asctime)s [%(levelname)s] %(message)s'))
		AlerterLogger.logger.addHandler(hdlr)
		AlerterLogger.logger.setLevel(logging.INFO)