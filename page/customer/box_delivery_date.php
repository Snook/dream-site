<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/DAO/BusinessObject/CBox.php');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CLog.inc');

class page_box_delivery_date extends CPage
{

	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runBoxDeliveryDayPage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$this->runBoxDeliveryDayPage($tpl);
	}

	function runBoxDeliveryDayPage($tpl)
	{
		$CartObj = CCart2::instance();

		if (!empty($_POST['sid']) && is_numeric($_POST['sid']))
		{
			$CartObj->addSessionId($_POST['sid']);
			CApp::bounce('/checkout');
		}

		$zip = $CartObj->getPostalCode();
		if (empty($zip) || !is_numeric($zip))
		{
			$tpl->setErrorMsg("There was a problem with the destination postal code. Please try again.");
			CApp::bounce("/locations");
		}

		$serviceDaysRetriever = new DAO();
		$serviceDaysRetriever->query("select service_days from zipcodes where zip = '$zip' limit 1");
		$serviceDaysRetriever->fetch();
		$serviceDays = $serviceDaysRetriever->service_days;
		if (empty($serviceDays))
		{
			$tpl->setErrorMsg("There was a problem determining the shipping days required. Please try again.");
			CApp::bounce("/locations");
		}

		$OrderObj = $CartObj->getOrder();
		$StoreObj = $OrderObj->getStore();
		$CartMenuId = $CartObj->getMenuId();

		$sessionsArray = CSession::getCurrentDeliveredSessionArrayForCustomer($StoreObj, $serviceDays, false, $CartMenuId, true, false, true);

		$tpl->assign('sessions', $sessionsArray);

		$tpl->assign('selected_session', $OrderObj->getSession()?->id);
	}
}