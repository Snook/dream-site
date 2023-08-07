<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");


require_once("CLog.inc");

 try {

	 try
	 {
		 $Mail = new CMail();

		 $data = array(
			 'credit_amount' => '500.00',
			 'gift_card_number' => '7050',
			 'date_of_purchase' => 'May 25, 2023',
			 'charged_amount' => '500',
			 'credit_card_number' => '1004',
			 'billing_name' => 'Anneliese Martinez',
			 'billing_address' => '2455 GWAY #P282',
			 'billing_zip' => '99354',
			 'billing_email' => 'anneliese.martinez@tablet.org',
			 'credit_card_type' => 'American Express'
		 );
		 $contentsText = CMail::mailMerge('order_gift_card_load.txt.php', $data, false);
		 $contentsHtml = CMail::mailMerge('order_gift_card_load.html.php', $data, false);

		 $Mail->send(null, null, null, 'evan.lee@dreamdinners.com', 'Gift Card Load Confirmation', $contentsHtml, $contentsText, '', '', null, 'order_gift_card - load');
	 }
	 catch (exception $e)
	 {
		 CLog::RecordException($e);
	 }

	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>
