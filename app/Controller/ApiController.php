<?php
class ApiController extends AppController {
	public $uses = array('User', 'Summoner');
	public function user($username, $token)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
		if(!isset($token))
			throw new Exception("You didn't give me the token");
		
		if(!$this->verifyUser($username, $token))
			throw new Exception("That's not your token");

		$user = $this->User->find('first', 
			array(
				'conditions' => array('TwitchUsername' => $username)
			)
		);
		if(!isset($user['User']['ID']))
			$json = $this->createUser($username, $token);
		else
		{
			$json = Set::extract('/User/.', $user);
			if($json[0]['TwitchToken'] != $token) 
				$this->updateToken($username, $token);

			$summoners = $this->Summoner->find('all', array('conditions' => array('User' => $json[0]['ID'])));
			$summoners = Set::extract('/Summoner/.', $summoners);
			$json[0]['summoners'] = $summoners;
		}

        $this->renderJSON($json[0], true);
	}

	public function summoners($apikey, $value)
	{
		if(!isset($apikey))
			throw new Exception("You didn't give me the api key");
		if(!isset($value))
			throw new Exception("You didn't give me the value");

		$user = $this->getAPIKeyUser($apikey);
		if(is_null($user))
			throw new Exception("Unknown API Key");

		//Remove the current summoners
		$this->Summoner->deleteAll(array('User' => $user['ID']));

		$splitSummoners = explode(',', $value);
		$notFound = array();
		$inUse = array();
		$save = array();
		foreach($splitSummoners as $summoner)
		{
			//Cut that fat
			$summoner = trim($summoner);
			$lolUserID = $this->getSummonerID($summoner);
			if($this->summonerAlreadyExists($lolUserID))
				$inUse[] = $summoner;
			else if($lolUserID != 0)
				$save[] = array('User'=> $user['ID'], 'SummonerName' => $summoner, 'SummonerID' => $lolUserID);
			else
				$notFound[] = $summoner;
		}

		$this->Summoner->saveMany($save);
		if(count($notFound)>0 || count($inUse)>0)
		{
			$errorText = "Unable to update summoners.";
			if(count($notFound)>0)
				$errorText .= "<br/>Summoner names could not be found: ".implode(", ", $notFound).". Ensure summoner is on the NA server. ";
			if(count($inUse)>0)
				$errorText .= "<br/>The following summoners are registered under a different Twitch account: ".implode(", ", $inUse).".";
			$this->renderJSON(array("error" => $errorText), false);
		}
		else
			$this->renderJSON(array("success" => true), true);
	}

	public function subscribers($username, $apikey, $limit = 25, $offset = 0)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
		if(!isset($apikey))
			throw new Exception("You didn't give me the api key");

		$user = $this->getAPIKeyUser($apikey);
		if(!isset($user['ID']))
			throw new Exception("Unknown api key");
		if(strtolower($user['TwitchUsername']) != strtolower($username))
			throw new Exception("That's not your api key");

		try
		{
			if(isset($limit))
				$results = $this->getURLContents("https://api.twitch.tv/kraken/channels/".$user['TwitchUsername']."/subscriptions?limit=25&offset=$offset&direction=desc&oauth_token=".$user['TwitchToken']);
			else
				$results = $this->getURLContents("https://api.twitch.tv/kraken/channels/".$user['TwitchUsername']."/subscriptions?limit=$limit&offset=$offset&direction=desc&oauth_token=".$user['TwitchToken']);
		}
		catch(Exception $e)
		{
			$this->renderJSON(array('error' => 'User is not a partner'), false);
			return;
		}
			
		$results = json_decode($results);
		$return = array();
		for($i=0;$i<count($results->subscriptions);$i++)
		{
			$current = $results->subscriptions[$i];
			$return["aaData"][]=array('<a href="'.$current->_links->self.'">'.$current->user->display_name.'</a>', 
				str_replace(array("T", "Z"), " ", $current->created_at));
		}
		//$return = array('total' => $results->_total);
		$this->renderJSON($return, true);
	}

	public function partner($username)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
			
		//Hard coded in Redback93
		if($username == "Redback93")
		{
			$this->renderJSON(array('partner'=>'true'), true);
			return;
		}
			
		try
		{
			$results = $this->getURLContents("https://api.twitch.tv/api/channels/".$username);
			$json = json_decode($results, true);
			$this->renderJSON(array('partner'=>$json['partner']), true);
			return;
		}
		catch(Exception $e)
		{
			$this->renderJSON(array('partner'=>false), true);
			return;
		}
	}
	
	private function getSummonerID($username)
	{
		$key = Configure::read('RiotAPI.Key');
		$username = str_replace(' ','',strtolower($username));
		$url = "http://prod.api.pvp.net/api/lol/na/v1.4/summoner/by-name/$username?api_key=" .
		       $key;
		$json = json_decode(file_get_contents($url), true);
		return $json[$username]['id'];
	}

	private function updateToken($username, $token)
	{
		$update = $this->User->updateAll(array('TwitchToken' => "'$token'"), array('TwitchUsername' => "$username"));
	}

	private function createUser($username, $token)
	{
		$newUser = array('APIKey' => uniqid('', true), 'TwitchUsername' => $username, 'TwitchToken' => $token);
		$this->User->save($newUser);
		return array($newUser);
	}

	private function verifyUser($username, $token)
	{
		$json = json_decode($this->getURLContents('https://api.twitch.tv/kraken/user?oauth_token='.$token));
		return (strtolower($username) == strtolower($json->name));
	}

	private function getAPIKeyUser($apikey)
	{
		$user = $this->User->find('first', array('conditions' => array('APIKey' => $apikey)));
		$user = Set::extract('/User/.', $user);
		if(isset($user[0]))
			return $user[0];
		else return NULL;
	}

	private function summonerAlreadyExists($summonerID)
	{
		$exists = $this->Summoner->find('count', array('conditions' => array('SummonerID' => $summonerID)));
		return ($exists === 1);
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

	private function renderJSON($json, $success)
	{
		$this->autoLayout = false; 
		$json['success'] = $success;
		$this->set(compact('json'));
        $this->render(JSON);	
	}
}