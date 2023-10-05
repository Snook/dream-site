<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CPayment.php');

class page_admin_credit_cards extends CPageAdminOnly
{

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		CTemplate::noCache();

		if (isset($_GET['user']))
		{
			$id = CGPC::do_clean($_GET['user'], TYPE_INT);
		}
		else
		{
			CApp::bounce('/backoffice/list_users');
			$id = null;
		}

		$Customer = DAO_CFactory::create('user');
		$Customer->id = $id;
		$Customer->find(true);
		$tpl->assign('customer_first', $Customer->firstname);
		$tpl->assign('customer_last', $Customer->lastname);
		$tpl->assign('customer_id', $id);
		$customersHomeStoreID = $Customer->home_store_id;

		$adminUser = CUser::getCurrentUser();

		$Form = new CForm();
		$Form->Repost = true;

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));
		}
		else
		{
			$Store = CStore::getFranchiseStore();

			$Form->addElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'store',
				CForm::default_value => $Store->id
			));
		}

		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'operation',
			CForm::value => 'none'
		));

		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'target_card_number',
			CForm::value => 'none'
		));

		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'target_card_type',
			CForm::value => 'none'
		));

		if (empty($store))
		{
			$store = $Form->value('store');
		}

		$tpl->assign('store_id', $store);

		if (isset($_POST['operation']) && $_POST['operation'] == 'remove')
		{
			$number = CGPC::do_clean($_POST['target_card_number'], TYPE_INT);
			$number = !empty($number) ? $number : false;

			if ($number)
			{
				if (CPayment::removePaymentStoredForReference($number, $id))
				{
					$tpl->setStatusMsg('The card has been removed.');
				}
				else
				{
					$tpl->setErrorMsg('There was a problem updating this card.');
				}
			}
			else
			{
				$tpl->setErrorMsg('There was a problem updating this card.');
			}
		}

		if (isset($_POST['operation']) && $_POST['operation'] == 'allow')
		{
			$number = CGPC::do_clean($_POST['target_card_number'], TYPE_INT);
			$type = CGPC::do_clean($_POST['target_card_type'], TYPE_STR);
			$number = !empty($number) ? $number : false;
			$type = !empty($type) ? $type : false;

			if ($number && $type)
			{
				$paymentObj = DAO_CFactory::create('payment');
				$paymentObj->query("update payment set do_not_reference = 0 where user_id = $id and payment_number like '%$number' and credit_card_type = '$type' 
	 				and payment_type = 'CC' and is_deleted = 0 and  DATEDIFF( now(), timestamp_created ) < 365 and store_id = $store");
				$tpl->setStatusMsg('Card was successfully set to be reference-able.');
			}
			else
			{
				$tpl->setErrorMsg('There was a problem updating this card.');
			}
		}

		$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference($id, $store);

		$tpl->assign('eligibleArray', $refPaymentTypeDataArray);
		$tpl->assign('form', $Form->Render());
	}
}

?>