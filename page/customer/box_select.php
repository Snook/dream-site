<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/DAO/BusinessObject/CBox.php');
require_once('includes/CLog.inc');

class page_box_select extends CPage
{

	/**
	 * @throws Exception
	 */
	function runPublic(): void
	{
		$tpl = CApp::instance()->template();

		$this->runBoxSelectPage($tpl);
	}

	/**
	 * @throws Exception
	 */
	function runCustomer(): void
	{
		$tpl = CApp::instance()->template();

		$this->runBoxSelectPage($tpl);
	}

	/**
	 * @throws Exception
	 */
	function runBoxSelectPage($tpl): void
	{
		$CartObj = CCart2::instance();

		if (!empty($_POST['delivered_zip']))
		{
			$req_post_zip = CGPC::do_clean((!empty($_POST['delivered_zip']) ? $_POST['delivered_zip'] : false), TYPE_POSTAL_CODE, true);

			$DAO_zipcodes = DAO_CFactory::create('zipcodes');
			$DAO_zipcodes->zip = $req_post_zip;
			$DAO_zipcodes->whereAdd("zipcodes.distribution_center IS NOT NULL");

			if (!$DAO_zipcodes->find(true))
			{
				$tpl->setStatusMsg('Dream Dinners does not currently deliver to ' . $req_post_zip);
				CApp::bounce('/locations');
			}

			$BoxesSurvived = false;
			if (!$CartObj->isShippingAddressPostalCodeEmptyOrEqualTo($req_post_zip))
			{
				$CartObj->clearShippingAddress();
				if (COrdersDelivered::cartInventoryCheck($CartObj, $DAO_zipcodes->distribution_center))
				{
					$BoxesSurvived = true;
					$CartObj->clearDeliveredBoxes();
				}
			}

			$CartObj->storeChangeEvent($DAO_zipcodes->distribution_center, $BoxesSurvived);
			// Note: this call, if the DCs are different. will clear _delivered_boxes variable but not erase the stored Box array
			$CartObj->addNavigationType(CTemplate::DELIVERED);
			$CartObj->changeEventPostalCode($DAO_zipcodes->zip);
		}

		$storeId = $CartObj->getStoreId();

		if (empty($storeId))
		{
			CApp::bounce('/locations');
		}

		$boxArray = CBox::getBoxArray($storeId, false, true, true, false, false, true);

		$tpl->assign('boxArray', $boxArray['box']);
		$tpl->assign('cart_info', CUser::getCartIfExists());
	}
}