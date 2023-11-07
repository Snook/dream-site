<?php
/*
 * Created on Nov 7, 2023
 *
 * @author evan lee
 */

$path = '/DreamWeb/dream-site/'. PATH_SEPARATOR . '/DreamWeb/dream-site/phplib/'. PATH_SEPARATOR .'/DreamWeb/dream-site/includes/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once("config/Config.server.inc");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");


set_time_limit(7200);
ini_set('memory_limit','1012M');



define('TEST_MODE', false);

if( TEST_MODE ){
	define('TO_EMAIL','evan.lee@dreamdinners.com,ryan.snook@dreamdinners.com');
	//define('TO_EMAIL','evan.lee@dreamdinners.com');
}
else
{
	define('TO_EMAIL','Cristen.Ellis@dreamdinners.com');
}

function sendEmail($storeObj, $agrObj, $previousMonthName, $selectMonthYear)
{

	$data = array("storeName" => $storeObj->store_name, "month" => $previousMonthName , "year" => $selectMonthYear, "agr"=>$agrObj->total_agr);


	$Mail = new CMail();
	$Mail->from_name = null;
	$Mail->from_email = null;
	$Mail->to_name = TO_EMAIL;
	$Mail->to_email = TO_EMAIL;
	$Mail->subject = "Shipping Revenue for " . $storeObj->store_name;
	$Mail->body_html =CMail::mailMerge('shipping_agr.html.php', $data);
	$Mail->body_text =CMail::mailMerge('shipping_agr.txt.php', $data);
	$Mail->reply_email = null;
	$Mail->template_name = 'shipping_agr';

	$Mail->sendEmail();

}
$totalCount = 0;
try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: email_shippping_agr called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::EMAIL_SHIPPING_AGR, "email_shippping_agr called but cron is disabled.");
		exit;
	}

	$curMonth = date("n");
	$curYear = date('Y');
	$curDay = date("j");
	$curMonthName = date("F");

	//The crontab will be set up to call this script on the 21st and the 24th.   Let's be cautious and be sure it is one of those 2 days and do nothing if not

	if (!TEST_MODE && $curDay != 6)
	{
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::EMAIL_SHIPPING_AGR, "email_shippping_agr: Called on wrong day: $curDay");
		exit;
	}

	$previousMonthTS = mktime(0,0,0,$curMonth-1,1,$curYear);
	$selectMonthYear = date("Y", $previousMonthTS);
	$previousMonthName = date("F", $previousMonthTS);
	$previousMonthStart = date('Y-m-01', $previousMonthTS);

	// The first reminder is from Finance
	$storeObj = DAO_CFactory::create('store');


	//get active dist centers
	$storeObj->query("select * from store where store_type = 'DISTRIBUTION_CENTER' and active = 1 and is_deleted = 0");

	while($storeObj->fetch())
	{
		$totalCount++;

		if (TEST_MODE || $curDay == 6)
		{

			$agrObj = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$agrObj->query("select total_agr, date from dashboard_metrics_agr_by_menu where store_id = {$storeObj->id} and date = '{$previousMonthStart}' and is_deleted = 0 limit 1");
			$agrObj->fetch();
			sendEmail($storeObj,$agrObj, $previousMonthName, $selectMonthYear);
		}
	}

	if (TEST_MODE || $curDay == 6)
	{
		CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::EMAIL_SHIPPING_AGR, "$totalCount shipping AGR sent.");
	}


}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::EMAIL_SHIPPING_AGR, "email_late_COA_filers: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>