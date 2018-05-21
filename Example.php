<?php
/*
 *  Example Class
 *  Comment : You can see transactions of your wallet
 *  Author : gitshinesun
 */

class Example{

	//api token and basic address of your wallet
	public $access_token = '1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstu';
	public $wallet = "1234567890abcdefghijklmnopqrstuvwx";

	public function __construct()
	{
		parent::__construct();

		//load bigto library
		$this->load->libaray('bitgosdk');
		
		//set token and endpoint
		$this->bitgosdk->set_init($this->access_token, false);
	}

	public function index()
	{
		//list wallet address
		$result = $this->bitgosdk->listWalletTransactions($this->wallet);
		var_dump($result);
	}
}

?>