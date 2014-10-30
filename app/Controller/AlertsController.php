<?php
class AlertsController extends AppController {
	public $uses = array('User');
	public function index()
	{
		
	}

	public function latest($apiKey)
	{
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");

		$user = $this->getAPIKeyUser($apiKey);
		if(!isset($user['ID']))
			throw new Exception("Unknown api key");

		$timestamp = (new DateTime())->getTimestamp();
		$url = "https://api.twitch.tv/kraken/channels/".$user['TwitchUsername']."/subscriptions?direction=DESC&limit=1&oauth_token=".$user['TwitchToken']."&_=$timestamp";
		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		echo $json['subscriptions'][0]['user']['display_name'];
		die();
	}

	public function onscreen($apiKey)
	{
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");

		$user = $this->getAPIKeyUser($apiKey);
		if(!isset($user['ID']))
			throw new Exception("Unknown api key");

		$timestamp = (new DateTime())->getTimestamp();
		$url = "https://api.twitch.tv/kraken/channels/".$user['TwitchUsername']."/subscriptions?direction=DESC&limit=1&oauth_token=".$user['TwitchToken']."&_=$timestamp";
		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		echo $json['subscriptions'][0]['user']['display_name'];
		die();
	}

	private function getAPIKeyUser($apiKey)
	{
		$user = $this->User->find('first', array('conditions' => array('APIKey' => $apiKey)));
		$user = Set::extract('/User/.', $user);
		if(isset($user[0]))
			return $user[0];
		else return NULL;
	}

	private function getURLContents($url)
	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER, false);
		$contents = curl_exec($ch);
		if (curl_errno($ch))
		  $contents = '';
		else
		  curl_close($ch);

		return $contents;
	}
}