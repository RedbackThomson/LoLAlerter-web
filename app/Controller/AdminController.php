<?php
class AdminController extends AppController 
{
	var $processLocation = "";
	public function index()
	{
		$password = $this->request->params['password'];
		if(!isset($password) || $password != Configure::read('Website.Admin'))
			throw new Exception("User could not be authenticated");

		$this->set('password', $password);

		$this->processLocation = realpath('../../alerter/').'/pid.txt';

		$server_start = $this->request->params['server_start'];
		if($server_start == "Start")
			$this->startServer();
		elseif ($server_start == "Stop") 
			$this->stopServer();

		$serverStatus = $this->getServerStatus();
		$this->set('serverStatus', $serverStatus);
	}

	private function getServerStatus()
	{
		$pid = file_get_contents($this->processLocation);
		if(strlen($pid) == 0) return false;

		exec("ps $pid", $pState);
		return (count($pState) >= 2);
	}

	private function stopServer($pid)
	{
		exec("kill $pid");
		file_put_contents($this->processLocation, '');
	}

	private function startServer()
	{
		$command = 'python "' . realpath('../../alerter/') . '/alerter.py"';
		$pid = exec("$command > C:/null &");

		file_put_contents($this->processLocation, strval($pid));
	}
}