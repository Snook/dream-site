<?php // page_account.php

require_once('includes/ValidationRules.inc');
require_once('includes/DAO/Address.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once("includes/CForm.inc");
require_once("includes/CPageAdminOnly.inc");
require_once('page/customer/account.php');
require_once('includes/class.inputfilter_clean.php');

//require_once('includes/DAO/BusinessObject/CUserReferralSource.php');	// included in page/customer/account.php


class page_admin_account extends CPageAdminOnly {

	private $restrictToCustomers = true;

	private $can = array(
		'editSecondaryEmail' => false,
		'selectDistributionCenter' => false
	);

	function runManufacturerStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runEventCoordinator()
	{
		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->can['selectDistributionCenter'] = true;
		$this->can['editSecondaryEmail'] = true;
		$this->restrictToCustomers = false;
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$tpl = CApp::instance()->template();

		form_account::$forwardTo = '/backoffice/user_details';

		$id = null;

		if (!empty($_GET['id']))
		{
			$id = CGPC::do_clean($_GET['id'],TYPE_INT);
		}

		//if an id is passed in, then edit that user's account
		$User = new CUser();
		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$AdminUser = CUser::getCurrentUser();  // this is the detail info for the user that is logged onto the system
		$hasReferraSource = false;
		if ($id)
		{
			$hasAccessToUser = true;
			$User->id = $id;
			//if ( $this->restrictToCustomers ) {
			//$User->user_type = CUser::CUSTOMER;
			//}

			$found = $User->find(true);

			if (!$found)
			{
				$tpl->setErrorMsg('That guest could not be found, or you do not have permission to edit that account.');
				CApp::bounce('/backoffice/list_users');
			}
			else
			{
				switch ($AdminUser->user_type)
				{
					//case CUser::SITE_ADMIN:
					//case CUser::FRANCHISE_OWNER:
					//case CUser::HOME_OFFICE_MANAGER:
					case CUser::HOME_OFFICE_STAFF:
						$hasAccessToUser = false;
						break;
				}
			}

			if ($hasAccessToUser == false)
			{
				$tpl->setErrorMsg('That guest could not be found, or you do not have permission to edit that account.');
				CApp::bounce('/backoffice/list_users');
			}

			$tpl->assign('isPartialAccount', $User->is_partial_account ? true : false);

			$tpl->assign('user_id', $id);


			$Addr = $User->getPrimaryAddress();
			$aProps = $User->toArray();
			if ($Addr)
			{
				$aProps = array_merge($Addr->toArray(), $aProps);
			}
			$Form->DefaultValues = $aProps;


			$ReferralSource = false;
			$ReferralSourceMetaData = false;
			$UserRefSource = DAO_CFactory::create('user_referral_source');
			$UserRefSource->user_id = $User->id;
			$UserRefSource->find();

			while ($UserRefSource->fetch())
			{
				$ReferralSource = $UserRefSource->source;
				$ReferralSourceMetaData = $UserRefSource->meta;
				$hasReferraSource = true;
				break;
				// Historically guests have been able to add multiple sources but we now only support one
			}

			$Form->DefaultValues['referral_source'] = $ReferralSource;
			if ($ReferralSource == CUserReferralSource::OTHER)
			{
				$Form->DefaultValues['referral_source_details'] = $ReferralSourceMetaData;
			}
			if ($ReferralSource == CUserReferralSource::VIRTUAL_PARTY)
			{
				$Form->DefaultValues['virtual_party_source_details'] = $ReferralSourceMetaData;
			}
			else if ($ReferralSource== CUserReferralSource::CUSTOMER_REFERRAL)
			{
				$Form->DefaultValues['customer_referral_email'] = $ReferralSourceMetaData;
			}


			$tpl->assign('isCreate', false);
			$tpl->assign('page_title', 'Edit Account');

			form_account::$forwardTo = '/backoffice/user_details?id=' . $id;

			if (!empty($_REQUEST['back']))
			{
				form_account::$forwardTo = $_REQUEST['back'];
			}
		}
		else
		{
			$tpl->assign('isCreate', true);
			$this->can['editSecondaryEmail'] = false;
			$tpl->assign('page_title', 'Create Account');
		}

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		$SFICurrentValues = null;
		$fadminStoreID = false;

		if (!$tpl->isCreate && $User->primary_email == null)
		{
			$tpl->assign('isEMailLess', true);
		}

		$suppress_email = false;
		// This signals a user that has no email address
		// could also look for _POST['generateLogin']
		if (!empty($_POST['generateLogin']) || (!$tpl->isCreate && $User->primary_email == null && empty($_POST['add_email'])))
		{
			$suppress_email = true;
			$_POST['primary_email'] = null;
		}

		$isHomeOffice = false;

		if (($AdminUser->user_type == CUser::SITE_ADMIN || $AdminUser->user_type == CUser::HOME_OFFICE_STAFF || $AdminUser->user_type == CUser::HOME_OFFICE_MANAGER))
		{
			if (!empty($User->home_store_id))
			{
				$Form->DefaultValues['store'] = $User->home_store_id;
			}

			// create store popup to allow setting home store id
			$omitDC = !$this->can['selectDistributionCenter'];
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => false,
				CForm::omitDistributionCenters => $omitDC,
				CForm::css_class => 'custom-select',
				CForm::name => 'store'
			));

			// get the current fadmin

			$Form->DefaultValues['home_store_id'] = $Form->value('store');
			$fadminStoreID = $Form->value('store');
			$isHomeOffice = true;
		}
		else if ($AdminUser->isFranchiseAccess())
		{
			$fadminStoreID = CBrowserSession::getCurrentFadminStore();

			// get the current fadmin
			if ($tpl->isCreate && empty($User->home_store_id))
			{
				$User->home_store_id = $fadminStoreID;
			}
		}

		$sAddr = $User->getShippingAddress();

		if (!empty($sAddr->id))
		{
			$Form->DefaultValues['shipping_address_line1'] = $sAddr->address_line1;
			$Form->DefaultValues['shipping_address_line2'] = $sAddr->address_line2;
			$Form->DefaultValues['shipping_city'] = $sAddr->city;
			$Form->DefaultValues['shipping_state_id'] = $sAddr->state_id;
			$Form->DefaultValues['shipping_postal_code'] = $sAddr->postal_code;
			$Form->DefaultValues['shipping_address_note'] = $sAddr->address_note;
		}

		$Form = form_account::_buildForm($Form, $tpl->isCreate, false, false, $User, false);

		$tpl->assign('isHomeOffice', $isHomeOffice);

		$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User, true);
		$tpl->assign('getUserData', true);

		// set this here, never to require this of a fadmin
		$_POST['customers_terms'] = 1;

		if (!empty($_POST["submit_account"]))
		{
			if ($User->is_partial_account)
			{
				if (!isset($_POST['convertToFull']))
				{
					$error = form_account::_saveFormPartial($Form, $User, $fadminStoreID);
				}
				else
				{
					$error = form_account::_saveForm($Form, $User, true, true, $suppress_email, $SFICurrentValues, $fadminStoreID, true, false);
				}

				if (!$error)
				{
					CApp::bounce('/backoffice/user_details?id=' . $User->id);
				}
			}
			else
			{
				$error = form_account::_saveForm($Form, $User, true, true, $suppress_email, $SFICurrentValues, $fadminStoreID, false, false);

				if (!$error)
				{
					CApp::bounce('/backoffice/user_details?id=' . $User->id);
				}
			}
		}

		//set template vars
		if (!empty($fadminStoreID))
		{
			$platePointsStatus = CPointsUserHistory::getPlatePointsStatus($fadminStoreID, $User);
		}
		else
		{
			$platePointsStatus = null;
		}

		$User->getMembershipStatus();

		$tpl->assign('platePointsStatus', $platePointsStatus);
		$tpl->assign('user', $User);
		$tpl->assign('form_account', $Form->Render());
		$tpl->assign('isAdmin', true);
		$tpl->assign('hasReferralSource', $hasReferraSource);
		$tpl->assign('can', $this->can);
	}
}

?>