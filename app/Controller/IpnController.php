<?php
class IpnController extends AppController {
	public $uses = array('User', 'SubscriptionPayment', 'Setting');
	public function index()
	{
		App::uses('IpnListener', 'Lib/IPN');
		App::uses('ItemEncoder', 'Lib/Encoder');

		file_put_contents('../../donation_'.date('m-d-Y_Hiss').'.txt', print_r($_POST, true));

		$this->HandleDonation();
	}

	private function HandleDonation()
	{
		if(!$this->VerifyDonation()) return;

		$txnType = $this->request->data('txn_type');
		
		if($txnType == "subscr_signup" || $txnType == "subscr_eot")
			$this->Signup();
		elseif($txnType == "subscr_cancel")
			$this->Cancel();
		elseif($txnType == "subscr_payment")
			$this->Payment();
	}

	private function Payment()
	{
		$username = ItemEncoder::GetUsername($this->request->data('item_number'));
		$user = $this->User->find('first', array('conditions' => array('TwitchUsername' => $username)));
		$user = $user['User'];

		//Removing the PST stamp on it
        $paymentDate = strtotime($_POST['payment_date']);

		$payment = array(
			'User' => $user['ID'],
			'PayerEmail' => $_POST['payer_email'],
            'FirstName' => $_POST['first_name'],
            'LastName' => $_POST['last_name'],
			'TXNID' => $_POST['txn_id'],
			'GrossAmount' => $_POST['mc_gross'],
			'FeeAmount' => $_POST['mc_fee'],
            'PaymentDate' => date('Y-m-d H:i:s', $paymentDate)
		);

		$this->SubscriptionPayment->save($payment);
	}

	private function Signup()
	{
		//Must be the correct billing amount
	    $amount = $this->Setting->find('first', array('conditions' => array('Key' => 'SubscriptionMonthly')));
		if($this->request->data('mc_amount3') != $amount['Setting']['Value'])
			return false;

		$username = ItemEncoder::GetUsername($this->request->data('item_number'));
		$this->User->updateAll(
			array('Active' => 1),
			array('TwitchUsername' => $username)
		);
	}

	private function Cancel()
	{
		$username = ItemEncoder::GetUsername($this->request->data('item_number'));
		$this->User->updateAll(
			array('Active' => 0),
			array('TwitchUsername' => $username)
		);
	}

	private function VerifyDonation()
	{
		//Create the listener
		$listener = new IpnListener();

		//Tell the listener to use the sandbox, for the debugging
		$listener->use_sandbox = true;

		try {
		    /*$listener->requirePostMethod();
		    $verified = $listener->processIpn();

		    if (!$verified)
		    	return false;*/

	        //Must match the required item number
	        $username = ItemEncoder::GetUsername($this->request->data('item_number'));
	        if($this->User->find('count', array('conditions' => array('TwitchUsername'=>$username))) >= 0) 
	            return true;

		} catch (Exception $e) {
		    return false;
		}
		return false;
	}
}