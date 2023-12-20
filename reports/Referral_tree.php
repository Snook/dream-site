<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");
require_once('ExcelExport.inc');

define('DEV', false);

ini_set('memory_limit', '-1');
set_time_limit(3600 * 24);

function getNextLevelResults($lastLevel, $lastLevelInviters)
{

	$getter = new DAO();
	$getter->query("select referred_user_id from customer_referral where referral_status = 4 and referring_user_id in (" . implode(",", $lastLevelInviters) . ")");

	return $getter->N;
}

try
{
	$fh = fopen($path, 'w');
	$fh2 = fopen($path2, 'w');

	$results = array();

	$labels = array(
		'DD_USER_ID',
		'First_name',
		'Last_name',
		'Primary_email',
		'last_order_date',
		"Store_name",
		"Store_phone",
		"Store_email_address",
		"Number Referrals"
	);
	$labels2 = array(
		'DD_USER_ID_inviter',
		'DD_USER_ID_invitee',
		'First_name',
		'Last_name',
		'Type_of_order'
	);

	$length = fputs($fh, implode(",", $labels) . "\r\n");
	$length = fputs($fh2, implode(",", $labels2) . "\r\n");

	$userDAO = new DAO();

	echo "begin user query\r\n";

	$userDAO->query("select iq.*, u.firstname, u.lastname, u.primary_email, st.store_name, st.telephone_day, st.email_address from (
										select distinct referring_user_id as user_id 
										from customer_referral where referral_status = 4) as iq
										join user u on iq.user_id = u.id and u.primary_email not like '%@dreamdinners.com'
										join store st on st.id = u.home_store_id and st.active = 1");

	echo "found {$userDAO->N} users\r\n";

	$processed = 0;

	while ($userDAO->fetch())
	{
		$processed++;
		if ($processed % 500 == 0)
		{
			echo "processed $processed users\r\n";
		}

		$lastOrder = new DAO();
		$lastOrder->query("select max(s.session_start) as last_session from booking b
					join session s on s.id= b.session_id and s.session_start > '2019-02-01 00:00:00'
					where b.user_id = {$userDAO->user_id} and b.status = 'ACTIVE' and b.is_deleted = 0 
					group by b.user_id");

		if ($lastOrder->N == 0)
		{
			continue;
		}

		$lastOrder->fetch();

		$UserPreferredDAO = DAO_CFactory::create('user_preferred');
		$UserPreferredDAO->user_id = $userDAO->user_id;

		if ($UserPreferredDAO->find())
		{
			continue;
		}

		$firstLevel = new DAO();
		$firstLevel->query("select cr.referred_user_id, u.firstname, u.lastname, cr.first_order_id, o.type_of_order from customer_referral cr
									join orders o on o.id = cr.first_order_id
									join user u on u.id = cr.referred_user_id
									 where cr.referral_status = 4 and cr.referring_user_id = {$userDAO->user_id}");

		if ($firstLevel->N < 5)
		{
			continue;
		}

		$length = fputs($fh, $userDAO->user_id . "," . $userDAO->firstname . "," . $userDAO->lastname . "," . $userDAO->primary_email . "," . $lastOrder->last_session . "," . $userDAO->store_name . "," . $userDAO->telephone_day . "," . $userDAO->email_address . "," . $firstLevel->N . "\r\n");

		while ($firstLevel->fetch())
		{
			$length = fputs($fh2, $userDAO->user_id . "," . $firstLevel->referred_user_id . "," . $firstLevel->firstname . "," . $firstLevel->lastname . "," . $firstLevel->type_of_order . "\r\n");
		}
		//$results = getNextLevelResults(1, $firstLevelArr);

	}

	fclose($fh);
	fclose($fh2);
}
catch (exception $e)
{
	CLog::RecordException($e);
}

?>