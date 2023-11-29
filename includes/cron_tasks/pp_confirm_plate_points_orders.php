<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: referral_rewards called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CONFIRM_PLATEPOINTS_ORDERS, "pp_confirm_plate_points_orders called but cron is disabled.");
		exit;
	}

	$Orders = DAO_CFactory::create('customer_referral');

	$Orders->query("select b.order_id as order_id, s.session_start, s.id as session_id from booking b
			join user u on u.id = b.user_id and u.dream_rewards_version = 3 and (u.dream_reward_status = 1 or u.dream_reward_status = 3) and u.is_deleted = 0
			join session s on s.id = b.session_id and DATEDIFF(now(),s.session_start) > 6 and DATEDIFF(now(),s.session_start) < 45
			join orders o on o.id = b.order_id and o.is_in_plate_points_program = 1 and o.is_deleted = 0 and o.points_are_actualized = 0
			where b.`status` = 'ACTIVE' and b.no_show = 0");

	$totalCount = 0;
	// Note: exception are handled, logged but not rethrown in the send_reminder_email function

    $countFirstDDs = 0;

	while ( $Orders->fetch() ) {

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->id = $Orders->order_id;
		$orderObj->find(true);


		$oldOrder = clone($orderObj);
		$orderObj->points_are_actualized = 1;
		$orderObj->update($oldOrder);

		list($results, $platePointsStatus) = CPointsUserHistory::handleEvent($orderObj->user_id, CPointsUserHistory::ORDER_CONFIRMED, false, $orderObj);

		if (isset($results))
			$resultsArr[$orderObj->id] = $results;
		else
			$resultsArr[$orderObj->id] = array("no result");

		CPointsUserHistory::clearLastOperationResult();




        $totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::CONFIRM_PLATEPOINTS_ORDERS, "$totalCount order confirmations processed.");


}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::CONFIRM_PLATEPOINTS_ORDERS, "expire_store_credit - warnings: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}
?>