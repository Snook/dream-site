<?php // page_admin_create_store.php

/**
 * @author Todd Wallar
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once('includes/DAO/BusinessObject/CGiftCard.php');
 require_once('includes/payment/PayPalProcess.php');
 require_once('includes/DAO/BusinessObject/COrders.php');
 require_once('includes/DAO/BusinessObject/CPayment.php');
 require_once('includes/class.inputfilter_clean.php');

 class page_admin_gift_card_load_confirm extends CPageAdminOnly {

	private $needStoreSelector = false;

 	function runSiteAdmin()
 	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runHomeOfficeStaff()
	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runHomeOfficeManager()
	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runFranchiseManager()
	{
            $this->run();
	}
	function runFranchiseOwner()
	{
            $this->run();
	}
	function runFranchiseStaff()
	{
            $this->run();
	}
	function runFranchiseLead()
	{
    		$this->run();
	}
	function runEventCoordinator()
	{
    	    $this->run();
	}
	function runOpsLead()
	{
    	    $this->run();
	}

	static function sendLoadConfirmationEmail($valArray)
	{
		try {
			$Mail = new CMail();

			$data = array('credit_amount' => CTemplate::moneyFormat($valArray['amount']),
				 'gift_card_number' => str_repeat('X', (strlen($valArray['gift_card_number']) - 4)) . substr($valArray['gift_card_number'], -4),
				 'date_of_purchase' => CTemplate::dateTimeFormat(date("Y-m-d H:i:s")),
				 'charged_amount' => CTemplate::moneyFormat($valArray['amount']),
				 'credit_card_number' => str_repeat('X', (strlen($valArray['credit_card_number']) - 4)) . substr($valArray['credit_card_number'], -4),
				 'billing_name' => $valArray['billing_name'],
				 'billing_address' => $valArray['billing_address'],
				 'billing_zip' => $valArray['billing_zip'],
				 'billing_email' => $valArray['primary_email'],
				 'credit_card_type' => $valArray['credit_card_type']);

				$contentsText = CMail::mailMerge('order_gift_card_load.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('order_gift_card_load.html.php', $data, false);

				$Mail->send(null, null,
						null, $valArray['primary_email'],
						'Gift Card Load Confirmation', $contentsHtml,
						$contentsText, '','', null, 'order_gift_card - load');

		} catch (exception $e) {
			CLog::RecordException($e);
		}

	}


    function run()
    {

    	

    	header('Pragma: no-cache');
    	header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
    	header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

        $tpl = CApp::instance()->template();

        $GCTrans = DAO_CFactory::create('gift_card_transaction');
        $GCTrans->id = CGPC::do_clean($_REQUEST['dd_trans_id'],TYPE_INT);
        if (!$GCTrans->find(true))
        {
        	throw new Exception('Bad Transaction ID on gift_card_load_confirm');
        }
        
        $addInfo = json_decode($GCTrans->additional_info, true);
        

        $dataArr = array('amount' => $GCTrans->transaction_amount,
        	'gift_card_number' => $GCTrans->gift_card_number,
        	'date' => $GCTrans->transaction_date,
        	'credit_card_number' =>  $GCTrans->cc_number,
        	'billing_name' => $addInfo['billing_name'],
        	'billing_address' => $addInfo['billing_address'],
        	'billing_zip' => $addInfo['billing_zip'],
        	'primary_email' => $GCTrans->billing_email,
        	'credit_card_type' => $GCTrans->cc_type);

		$tpl->assign('purchase_data', $dataArr);

        
		if (isset($_GET['print']) && $_GET['print'] == "true")
		{
			$tpl->assign('print_view', true);
		}

	}

}

?>