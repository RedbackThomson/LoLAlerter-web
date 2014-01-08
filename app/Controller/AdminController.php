<?php
class AdminController extends AppController 
{
	public function index()
	{
		$password = $this->request->params['password'];
		if(!isset($password) || $password != Configure::read('Website.Admin'))
			throw new Exception("User could not be authenticated");

		$server_start = $this->request->params['server_start'];
		$serverStatus = $this->getServerStatus();
		$this->set('serverStatus', $serverStatus);

		if($serverStatus)
		{

		}
	}

	private function getServerStatus()
	{
		return True;
	}

	private function startServer()
	{

	}
}