<?php
class IndexController extends AppController {
	public $uses = array('AlerterStatistic', 'Setting', 'Region');
	public function index()
	{
		$onlineUsers = 
			$this->AlerterStatistic->find('all', array('fields' => array('sum(OnlineUsers) as totalOnline')));
		$onlineUsers = $onlineUsers[0][0]['totalOnline'];

		$largestDonation = 
			$this->Setting->find('first', array('conditions' => array('Key' => 'LargestDonation')));
		$largestDonation = $largestDonation['Setting']['Value'];

		$totalSubscribed = 
			$this->AlerterStatistic->find('all', array('fields' => array('sum(TotalSubscribed) as totalSubscribed')));
		$totalSubscribed = $totalSubscribed[0][0]['totalSubscribed'];

		$this->set('onlineUsers', $onlineUsers);
		$this->set('largestDonation', $largestDonation);
		$this->set('totalSubscribed', $totalSubscribed);

		$this->set('regions', $this->getAllRegions());
	}

	private function getAllRegions()
	{
		$regions = $this->Region->find('all');
		return Set::extract('/Region/.', $regions);
	}
}