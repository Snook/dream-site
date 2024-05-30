<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

set_time_limit(100000);
ini_set('memory_limit', '-1');

try
{
	$DAO_user_digest = DAO_CFactory::create('user_digest', true);
	$DAO_user_digest->orderBy('user_digest.id DESC');
	$DAO_user_digest->find();

	while ($DAO_user_digest->fetch())
	{
		$org_DAO_user_digest = clone $DAO_user_digest;

		$DAO_booking = DAO_CFactory::create('booking', true);
		$DAO_booking->user_id = $DAO_user_digest->user_id;
		$DAO_booking->status = CBooking::ACTIVE;
		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->whereAdd("session.session_type = '" . CSession::SPECIAL_EVENT . "' OR session.session_type = '" . CSession::DELIVERED . "'");
		$DAO_booking->joinAddWhereAsOn($DAO_session);
		$DAO_booking->orderBy('session.session_start ASC');
		$DAO_booking->find();

		while($DAO_booking->fetch())
		{
			if (empty($DAO_user_digest->order_id_first_pick_up) && $DAO_booking->DAO_session->isPickUp())
			{
				$DAO_user_digest->order_id_first_pick_up = $DAO_booking->order_id;
			}

			if (empty($DAO_user_digest->order_id_first_shipping) && $DAO_booking->DAO_session->isShipping())
			{
				$DAO_user_digest->order_id_first_shipping = $DAO_booking->order_id;
			}

			if (empty($DAO_user_digest->order_id_first_home_delivery) && $DAO_booking->DAO_session->isDelivery())
			{
				$DAO_user_digest->order_id_first_home_delivery = $DAO_booking->order_id;
			}
		}

		$DAO_user_digest->update($org_DAO_user_digest);

		echo "Completed row " . $DAO_user_digest->id . ".\n";
	}

	echo "All Done.\n";

}
catch (exception $e)
{

}
?>