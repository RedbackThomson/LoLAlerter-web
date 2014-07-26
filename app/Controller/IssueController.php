<?php
App::uses('CakeEmail', 'Network/Email');
class IssueController extends AppController {
	public function index()
	{
		if(!$this->request->is('post'))
			return $this->redirect(array('controller' => '', 'action' => 'index'));

		$name = $this->request->data('username');
		$body = $this->request->data('body');
		date_default_timezone_set('Asia/Hong_Kong');
		$dateTime = date('d/m/Y h:i:s a', time());

		$Email = new CakeEmail('gmail');
		$Email->to('redback93@hotmail.com')
			->subject('Support ['.$dateTime.']')
			->template('support')
			->emailFormat('html')
			->viewVars(array('username' => $name, 'body' => $body, 'dateTime' => $dateTime))
			->send();

		return $this->redirect(array('controller' => '', 'action' => 'index', '?' = array('support')));
	}
}