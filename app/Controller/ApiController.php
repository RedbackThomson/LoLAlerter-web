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
		if(!isset($user['User']['APIKey']) || $user['User']['APIKey'] == "")
			$json = $this->createUser($username, $token);
		else
		{
			$json = Set::extract('/User/.', $user);
			if($json[0]['TwitchToken'] != $token) 
				$this->updateToken($username, $token);
		}

        $this->renderJSON($json[0], true);
	}

	public function addSummoner($apiKey, $summoner)
	{
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");
		if(!isset($summoner))
			throw new Exception("You didn't give me the summoner");

		$user = $this->getAPIKeyUser($apiKey);
		if(is_null($user))
			throw new Exception("Unknown API Key");

		$notFound = array();
		$inUse = array();
		$save = array();

		//Cut that fat
		$summoner = trim($summoner);
		try
		{
			$lolUserID = $this->getSummonerID($summoner);
		}
		catch (Exception $x) {}
		if(!isset($lolUserID))
			$this->renderError($summoner . " could not be found on the NA servers");
		else if($this->summonerAlreadyExists($lolUserID))
			$this->renderError($summoner . " is already registered under a different Twitch account");
		else
		{
			$this->Summoner->save(array('User'=> $user['ID'], 'SummonerName' => 
				$summoner, 'SummonerID' => $lolUserID));
			$this->renderJSON(array(), true);
		}
	}

	public function removeSummoner($apiKey, $value)
	{
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");
		if(!isset($value))
			throw new Exception("You didn't give me the value");

		$user = $this->getAPIKeyUser($apiKey);
		if(is_null($user))
			throw new Exception("Unknown API Key");

		//Remove the current summoners
		$this->Summoner->deleteAll(array('SummonerName' => $value));
		$this->renderJSON(array(), true);
	}

	public function subscribers($username, $apiKey, $limit = 25, $offset = 0)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");

		$user = $this->getAPIKeyUser($apiKey);
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

	public function summoners($username, $apiKey)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");

		$user = $this->getAPIKeyUser($apiKey);
		if(!isset($user['ID']))
			throw new Exception("Unknown api key");
		if(strtolower($user['TwitchUsername']) != strtolower($username))
			throw new Exception("That's not your api key");

		$summoners = $this->Summoner->find('all', array('conditions' => array('User' => $user['ID'])));
		$summoners = Set::extract('/Summoner/.', $summoners);
		$summonerIDs = implode(',', Set::extract('/SummonerID', $summoners));

		if($summonerIDs == "")
		{
			$this->renderJSON(array(), true);
			return;
		}

		$info = $this->getSummonersInfoByID($summonerIDs);
		$leagues = $this->getLeaguesByID($summonerIDs);

		foreach($summoners as &$summoner)
		{
			$summoner['Level'] = $info[$summoner['SummonerID']]['summonerLevel'];
			if(!is_null($leagues) && array_key_exists($summoner['SummonerID'], $leagues))
			{
				foreach($leagues[$summoner['SummonerID']] as $league)
					if($league['queue'] == "RANKED_SOLO_5x5")
						foreach($league['entries'] as $entry)
							if($entry['playerOrTeamId'] == $summoner['SummonerID'])
								$summoner['Division'] = ucwords(strtolower($league['tier'])) .' '. $league['entries'][0]['division'];
			}
			else
			{
				$summoner['Division'] = 'Unranked';
			}
			if(!isset($summoner['Division'])) $summoner['Division'] = 'Unranked';
			$summoner['Region'] = 'NA';
		}

		$this->renderJSON($summoners, true);
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
		
		//Search for existing users
		$exists = $this->User->find('count', array('conditions' => array('TwitchUsername' => $username)));
		if($exists == 1)
		{
			$this->renderJSON(array('partner'=>'true'), true);
			return;
		}

		//Search on Twitch API
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
		$info = $this->getSummonerInfoByName($username);
		return $info['id'];
	}

	private function getSummonerInfoByName($username)
	{
		$key = Configure::read('RiotAPI.Key');
		$username = str_replace(' ','',strtolower($username));
		$url = "http://na.api.pvp.net/api/lol/na/v1.4/summoner/by-name/$username?api_key=" .
			$key;
		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		return $json[$username];
	}

	private function getSummonersInfoByID($IDs)
	{
		$key = Configure::read('RiotAPI.Key');
		$url = "http://na.api.pvp.net/api/lol/na/v1.4/summoner/$IDs?api_key=" .
		       $key;

		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		return $json;
	}

	private function getLeaguesByID($IDs)
	{
		$key = Configure::read('RiotAPI.Key');
		$url = "http://na.api.pvp.net/api/lol/na/v2.5/league/by-summoner/$IDs?api_key=" .
		       $key;
		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		return $json;	
	}

	private function updateToken($username, $token)
	{
		$update = $this->User->updateAll(array('TwitchToken' => "'$token'"), array('TwitchUsername' => "$username"));
	}

	private function createUser($username, $token)
	{
		$this->User->deleteAll(array('TwitchUsername' => $username));
		$newUser = array('APIKey' => uniqid('', true), 'TwitchUsername' => $username, 'TwitchToken' => $token);
		$this->User->save($newUser);
		return array($newUser);
	}

	private function verifyUser($username, $token)
	{
		$json = json_decode($this->getURLContents('https://api.twitch.tv/kraken/user?oauth_token='.$token));
		return (strtolower($username) == strtolower($json->name));
	}

	private function getAPIKeyUser($apiKey)
	{
		$user = $this->User->find('first', array('conditions' => array('APIKey' => $apiKey)));
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

	private function renderError($text)
	{
		$this->renderJSON(array("error" => $text), false);
	}

	private function renderJSON($json, $success)
	{
		$this->autoLayout = false; 
		$json['success'] = $success;
		$this->set(compact('json'));
        $this->render(JSON);	
	}
}