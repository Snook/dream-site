<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/CStoreFee.php';
require_once 'includes/DAO/BusinessObject/CStore.php';
require_once 'includes/OrdersCustomization.php';

/*
$stores = new DAO();
$stores->query("select id from store where active = 1");
while ($stores->fetch())
{
	$test = $stores->cloneObj();
}
*/

$DAO_session = DAO_CFactory::create('session', true);
//$DAO_session->id = 989828;
$DAO_session->store_id = 244;
$DAO_session->menu_id = 263;

//$DAO_booking = DAO_CFactory::create('booking');
//$DAO_booking->status = CBooking::ACTIVE;
//$DAO_session->joinAddWhereAsOn($DAO_booking, 'LEFT');
//$DAO_session->orderBy('session.id DESC');
$DAO_session->find_DAO_session();

while ($DAO_session->fetch())
{
	$result = $DAO_session->cloneObj();
}

?>