<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

$_DAYS_BEFORE = 1;

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: what_to_expect called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::SESSION_REMINDERS, "what_to_expect called but cron is disabled.");
		exit;
	}

	$DAO_booking = DAO_CFactory::create('booking', true);
	$DAO_booking->status = CBooking::ACTIVE;
	$DAO_booking->whereAdd("DATEDIFF(NOW(), session.session_start ) = -" . $_DAYS_BEFORE);
	$DAO_booking->whereAdd("session.session_type_subtype <> '" . CSession::WALK_IN . "' OR session.session_type_subtype IS NULL");
	$DAO_booking->whereAdd("store.store_type = '" . CStore::FRANCHISE . "'");
	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$DAO_booking->limit(2);
	}
	$DAO_booking->find_DAO_booking();

	$totalCount = 0;

	while ($DAO_booking->fetch())
	{
		if ($DAO_booking->DAO_orders->isShipping())
		{
			if ($DAO_booking->DAO_orders->id == $DAO_booking->DAO_user_digest->order_id_first_shipping)
			{
				$Mail = new CMail();
				$Mail->to_name = $DAO_booking->DAO_user->firstname . ' ' . $DAO_booking->DAO_user->lastname;
				$Mail->to_email = $DAO_booking->DAO_user->primary_email;
				$Mail->from_email = $DAO_booking->DAO_store->email_address;
				$Mail->subject = 'What to Expect with Your Dream Dinners Shipment';
				$Mail->bodyHTML('shipping/shipping_what_to_expect.html.php', array('DAO_booking' => $DAO_booking));
				$Mail->bodyText('shipping/shipping_what_to_expect.txt.php', array('DAO_booking' => $DAO_booking));
				$Mail->template_name = 'shipping_what_to_expect';
				$Mail->sendEmail();

				$totalCount++;
			}
		}
		else if ($DAO_booking->DAO_orders->isDelivery())
		{
			if ($DAO_booking->DAO_orders->id == $DAO_booking->DAO_user_digest->order_id_first_home_delivery)
			{
				$Mail = new CMail();
				$Mail->to_name = $DAO_booking->DAO_user->firstname . ' ' . $DAO_booking->DAO_user->lastname;
				$Mail->to_email = $DAO_booking->DAO_user->primary_email;
				$Mail->from_email = $DAO_booking->DAO_store->email_address;
				$Mail->subject = 'What to Expect with Your Dream Dinners Delivery';
				$Mail->bodyHTML('what_to_expect_home_delivery.html.php', array('DAO_booking' => $DAO_booking));
				$Mail->bodyText('what_to_expect_home_delivery.txt.php', array('DAO_booking' => $DAO_booking));
				$Mail->template_name = 'what_to_expect_home_delivery';
				$Mail->sendEmail();

				$totalCount++;
			}
		}
		else if ($DAO_booking->DAO_session->isPickUp() || $DAO_booking->DAO_session->isRemotePickup())
		{
			if ($DAO_booking->DAO_orders->id == $DAO_booking->DAO_user_digest->order_id_first_pick_up)
			{
				$Mail = new CMail();
				$Mail->to_name = $DAO_booking->DAO_user->firstname . ' ' . $DAO_booking->DAO_user->lastname;
				$Mail->to_email = $DAO_booking->DAO_user->primary_email;
				$Mail->from_email = $DAO_booking->DAO_store->email_address;
				$Mail->subject = 'What to Expect from Your Dream Dinners Pick Up Order';
				$Mail->bodyHTML('what_to_expect_pickup.html.php', array('DAO_booking' => $DAO_booking));
				$Mail->bodyText('what_to_expect_pickup.txt.php', array('DAO_booking' => $DAO_booking));
				$Mail->template_name = 'what_to_expect_pickup';
				$Mail->sendEmail();

				$totalCount++;
			}
		}
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::SESSION_REMINDERS, " $totalCount what to expect emails processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::SESSION_REMINDERS, "what_to_expect: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>