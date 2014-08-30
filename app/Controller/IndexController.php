<?php
class IndexController extends AppController {
	public $uses = array('Statistic');
	public function index()
	{
		$onlineUsers = 
			$this->Statistic->find('first', array('conditions' => array('Key' => 'OnlineUsers')));
		$onlineUsers = $onlineUsers['Statistic']['Value'];

		$largestDonation = 
			$this->Statistic->find('first', array('conditions' => array('Key' => 'LargestDonation')));
		$largestDonation = $largestDonation['Statistic']['Value'];

		$totalSubscribed = 
			$this->Statistic->find('first', array('conditions' => array('Key' => 'TotalSubscribed')));
		$totalSubscribed = $totalSubscribed['Statistic']['Value'];

		$this->set('onlineUsers', $onlineUsers);
		$this->set('largestDonation', $largestDonation);
		$this->set('totalSubscribed', $totalSubscribed);
	}
}