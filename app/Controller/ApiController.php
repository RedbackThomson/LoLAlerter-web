<?php
class ApiController extends AppController {
	public $uses = array('User', 'Summoner', 'Alerter', 'Region', 'SubscriptionPayment', 'Setting');
	public function user($username, $display, $token)
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
			$json = $this->createUser($username, $display, $token)[0];
		else
		{
			$json = Set::extract('/User/.', $user);
			if($json[0]['TwitchToken'] != $token) 
				$this->updateToken($username, $token);
			$json = $json[0];
		}

		//Remove unnecessary json fields
		$output = $json;
		unset($output['Timestamp']);
		unset($output['CreateDate']);
		unset($output['LastNotice']);

        $this->renderJSON($output, true);
	}

	public function addSummoner($apiKey, $summoner, $region)
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
			$lolUserID = $this->getSummonerID($summoner, strtolower($region));
		}
		catch (Exception $x) {}

		//Get alerters on that region
		$alerter = $this->getRegionAlerters($region);
		//TODO: check if alerter is full

		if(count($alerter) < 1)
			$this->renderError("There are currently no alerters for that region");
		else if(!isset($lolUserID))
			$this->renderError($summoner . " could not be found on the ".$region." servers");
		else if($this->summonerAlreadyExists($lolUserID))
			$this->renderError($summoner . " is already registered under a different Twitch account");
		else
		{
			$alerter = $alerter[0];
			$this->Summoner->save(array('User'=> $user['ID'], 'SummonerName' => 
				$summoner, 'SummonerID' => $lolUserID, 'Alerter' => $alerter['ID']));
			$this->renderJSON(array(), true);
		}
	}

	public function removeSummoner($apiKey, $summonerID, $region)
	{
		if(!isset($apiKey))
			throw new Exception("You didn't give me the api key");
		if(!isset($summonerID))
			throw new Exception("You didn't give me the value");

		$user = $this->getAPIKeyUser($apiKey);
		if(is_null($user))
			throw new Exception("Unknown API Key");

		//Remove the current summoners
		$this->Summoner->deleteAll(array('SummonerID' => $summonerID, 'User' => $user['ID']));
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

	public function summoners($username, $apiKey, $region)
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

		//Get alerters for that region
		$alerters = $this->array_column($this->getRegionAlerters($region), 'ID');

		$summoners = $this->Summoner->find('all', array('conditions' => array('User' => $user['ID'], 'Alerter'=>$alerters)));
		$summoners = Set::extract('/Summoner/.', $summoners);
		$summonerIDs = implode(',', Set::extract('/SummonerID', $summoners));

		if($summonerIDs == "")
		{
			$this->renderJSON(array(), true);
			return;
		}

		$info = $this->getSummonersInfoByID($summonerIDs, strtolower($region));
		$leagues = $this->getLeaguesByID($summonerIDs, strtolower($region));

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
			$summoner['Region'] = $region;
		}

		$this->renderJSON($summoners, true);
	}

	public function partner($username)
	{
		if(!isset($username))
			throw new Exception("You didn't give me the username");
			
		//Hard coded in Redback93
		if($username == "redback93")
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
	
	public function subscription($username, $apiKey)
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

		//Load user subscriptions
		$payments = $this->SubscriptionPayment->find('all', 
			array('conditions' => array('User' => $user['ID']),
				'limit' => 50,
				'order' => array('Timestamp DESC')));
		$payments = Set::extract('/SubscriptionPayment/.', $payments);

		//Load into a user-friendly amount of information
		$output = array();
		foreach($payments as $payment)
			$output[] = array('Transaction' => $payment['TXNID'], 'Amount' => $payment['GrossAmount'], 'Timestamp' => $payment['PaymentDate']);

		App::uses('ItemEncoder', 'Lib/Encoder');
		//Get the settings
		$email = $this->Setting->find('first', array('conditions' => array('Key' => 'PayPalEmail')));
		$email = $email['Setting']['Value'];

		$subMonthly = $this->Setting->find('first', array('conditions' => array('Key' => 'SubscriptionMonthly')));
		$subMonthly = $subMonthly['Setting']['Value'];		

		$vars = array('payments' => $output, 'active' => $user['Active'], 
			'item' => ItemEncoder::EncodeItem($user['TwitchUsername']), 'display' => $user['TwitchDisplay'],
			'email' => $email, 'monthly' => $subMonthly);
		$this->set($vars);
		$this->render('/Subscription/index', 'empty');
	}

	private function getSummonerID($username, $region)
	{
		$info = $this->getSummonerInfoByName($username, $region);
		return $info['id'];
	}

	private function getSummonerInfoByName($username, $region)
	{
		$key = Configure::read('RiotAPI.Key');
		$username = str_replace(' ','',strtolower($username));
		$url = "http://$region.api.pvp.net/api/lol/$region/v1.4/summoner/by-name/$username?api_key=" .
			$key;
		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		return $json[$username];
	}

	private function getSummonersInfoByID($IDs, $region)
	{
		$key = Configure::read('RiotAPI.Key');
		$url = "http://$region.api.pvp.net/api/lol/$region/v1.4/summoner/$IDs?api_key=" .
		       $key;

		try
		{
			$contents = $this->getURLContents($url);
		}
		catch(Exception $x) {return;}
		$json = json_decode($contents, true);
		return $json;
	}

	private function getLeaguesByID($IDs, $region)
	{
		$key = Configure::read('RiotAPI.Key');
		$url = "http://$region.api.pvp.net/api/lol/$region/v2.5/league/by-summoner/$IDs?api_key=" .
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

	private function createUser($username, $display, $token)
	{
		App::uses('String', 'Utility');
		$this->User->deleteAll(array('TwitchUsername' => $username));
		$newUser = array('APIKey' => strtoupper(String::uuid()), 'TwitchUsername' => $username, 
			'TwitchDisplay'=> $display,'TwitchToken' => $token, 'CreateDate'=>DboSource::expression('NOW()'));
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

	private function getRegionAlerters($regionCode)
	{
		//Get regions
		$region = $this->Region->find('first', array('conditions' => array('RegionCode' => $regionCode)));

		//Get assosciated alerters
		$alerters = $this->Alerter->find('all', array('conditions' => array('Region' => $region['Region']['ID'])));
		$alerters = Set::extract('/Alerter/.', $alerters);
		return $alerters;
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

	private function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }
}