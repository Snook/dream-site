<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("CLog.inc");
require_once("CAppUtil.inc");
require_once("CApp.inc");
require_once("DAO/BusinessObject/CPayment.php");
require_once("DAO/BusinessObject/CBox.php");
require_once("DAO/BusinessObject/CBoxInstance.php");
require_once("DAO/BusinessObject/CSession.php");

require_once("../processor/cart_session_processor.php");
require_once("../processor/delivered_box.php");



if (false)
{
	$Store = DAO_CFactory::create('store');
	$Store->id =310;
	$Store->find(true);

	$cart_info = array('cart_info_array' => array());
	$cart_info['cart_info_array']['navigation_type'] = CTemplate::DELIVERED;
//$result = CSession::getMonthlySessionInfoArrayForDelivered($Store, time(), false, $cart_info);

	$result = CSession::getMonthlySessionInfoArray($Store, time(), false, $cart_info);

	print_r($result);
	exit;

}

if (false)
{
	$result = CSession::generateDeliveredSessionsForMenu(239);
	$result = CSession::generateDeliveredSessionsForMenu(240);
	exit;

}

if (false)
{
	$s = DAO_CFactory::create('session');
	$s->id = 888961;
	$s->find(true);
	$s->getShippingCount();
	exit;
}

if (false)
{

	$store = DAO_CFactory::create('store');
	$store->id = 310;
	$store->find(true);
	$result = CSession::getCurrentDeliveredSessionArrayForCustomer($store, 2, false, 238);
	print_r($result);
	exit;
}


global $output;
$output = "";

function output_delayed($in)
{
	global $output;
	$output .= "\r\n" .  $in . "\r\n";
}

// -------------------------------------------------------------------------------------------------------

try {
	$output = "";

	//$data = unserialize(base64_decode("YToxOntpOjA7aToxODY1Mjt9"));


	$menu_id = 239;

	$storeGetter = new DAO();
	$storeGetter->query("select id from store where store_type = 'DISTRIBUTION_CENTER' and active = 1 and is_deleted = 0 order by rand() limit 1");
	$storeGetter->fetch();
	$store_id = $storeGetter->id;

	$sessionGetter = new DAO();
	$sessionGetter->query("select id from session where store_id = $store_id and menu_id = $menu_id and is_deleted = 0 and session_start > now() order by rand() limit 1");
	$sessionGetter->fetch();
	$session_id = $sessionGetter->id;

	$menuInfo = CBox::getBoxArray($store_id, false, false);
	$thisBox = $menuInfo['box'][array_rand($menuInfo['box'])];


	$bundleGetter = DAO_CFactory::create('bundle');
	$bundleGetter->query("select * from bundle where  id = {$thisBox->box_bundle_1} and bundle_type = 'DELIVERED' and  is_deleted = 0");
	$bundleGetter->fetch();
	$bundle_id = $bundleGetter->id;

	// for now get 4 random items regardless of bundle type or size
	$itemArray = array();
	$bundleItemGetter = new DAO();
	$bundleItemGetter->query("select menu_item_id from bundle_to_menu_item where bundle_id = $bundle_id and is_deleted = 0 order by rand() limit 4");
	while($bundleItemGetter->fetch())
	{
		$itemArray[] = $bundleItemGetter->menu_item_id;
	}


	$app = new CApp();
	$app->fakeTemplate();
	$CartObj = CCart2::instance();
	// Note: in the real world the guest must provide a zip code to establish eligibility and distribution center

	$CartObj->storeChangeEvent($store_id);
	$CartObj->addNavigationType(CTemplate::DELIVERED);
	$CartObj->addSessionId($session_id);
	$CartObj->addMenuId($menu_id);

	$data = array('bundle_id' => $bundle_id, 'items' => array());
	$box_inst_id = CBoxInstance::getNewEmptyBoxForBundle($bundle_id, $thisBox->id);

	$proc = new processor_delivered_box();
	$proc->box_instance_id = $box_inst_id;
	$proc->setEditMode(true);


	$CartObj->addDeliveredBox($box_inst_id, $data);
	$CartObj->addItemToDeliveredBox($box_inst_id, $itemArray[0], 1);
	$CartObj->addItemToDeliveredBox($box_inst_id, $itemArray[1], 1);
	$CartObj->addItemToDeliveredBox($box_inst_id, $itemArray[2], 1);
	$CartObj->addItemToDeliveredBox($box_inst_id, $itemArray[3], 2);

	$proc->setEditMode(false);
	$proc->setCompleteStatus(true);


	$CartObj->restoreContents();

	$Order = $CartObj->getOrder();
	$session = $Order->findSession();
	$store = $Order->getStore();
	$CartObj->addUserId(908157);

	output_delayed( print_r($CartObj->get_cart_info_array(), true));

	$Order->refresh();
	$Order->recalculate();

	/*
	// Initiate $OrderObj, we need to grab the order ID
	// A method of getting address data
	$OrderObj = DAO_CFactory::create('orders');
	$OrderObj->id = 3574311;
	$OrderObj->find(true); // NEW – this loads the columns into the Object.
	$OrderObj->orderAddress(); // NEW-  we must load the address as well
*/

	$creditCardArray = array();
	$creditCardArray['ccType'] = CPayment::VISA;
	$creditCardArray['ccNumber'] = '4111111111111111';
	$creditCardArray['ccMonth'] = '02';
	$creditCardArray['ccYear'] = '22';
	$creditCardArray['ccNameOnCard'] = "Barney Rubble";
	$creditCardArray['ccSecurityCode'] = '111';
	$creditCardArray['billing_address'] = '1111 main st';
	$creditCardArray['city'] = "Spokane";
	$creditCardArray['state_id'] = 'WA';
	$creditCardArray['billing_postal_code'] = "98021";
	$creditCardArray['do_delayed_payment']  = false;

	$Order->orderAddress();

	$Order->orderAddress->firstname = "DragonFly";
	$Order->orderAddress->lastname = "Inn";
	$Order->orderAddress->telephone_1 = "111-111-1111";
	$Order->orderAddress->address_line1 = "365 Sunkist Beach Rd, ";
	$Order->orderAddress->address_line2 = "";
	$Order->orderAddress->city = "Tiptonville";
	$Order->orderAddress->state_id = "TN";
	$Order->orderAddress->postal_code = "38079";
	$Order->orderAddress->usps_adc = "";
	$Order->orderAddress->address_note = "Testing:" . date("Y-m-d H:i:s");

	$Order->defaultShippingInfo('38079',$session,false);




 	$result = $Order->processNewOrderGC(CPayment::CC, $creditCardArray);
	output_delayed(print_r($result, true));
	output_delayed("done");

} catch (exception $e) {
    output_delayed($e->getMessage());
}

echo $output;
?>