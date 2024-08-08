<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_access_agreement extends CPageAdminOnly
{

	/**
	 * @throws Exception
	 */
	function runNewEmployee()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runEventCoordinator()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runOpsLead()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runOpsSupport()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runDishwasher()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runManufacturerStaff()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseOwner()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseStaff()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseLead()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseManager()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runHomeOfficeStaff()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runHomeOfficeManager()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runSiteAdmin()
	{
		$this->runAccessAgreement();
	}

	/**
	 * @throws Exception
	 */
	function runAccessAgreement()
	{
		$this->Template->assign('back', '/backoffice');

		if (!empty($_POST['agree_to_nda']) && !empty($_POST['agree_to_nda_submit']))
		{
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->id = CBrowserSession::getCurrentFadminStore();
			$DAO_store->find(true);

			$DAO_user = DAO_CFactory::create('user');
			$DAO_user->id = CUser::getCurrentUser()->id;

			if ($DAO_user->find(true))
			{
				$DAO_user->fadmin_nda_agree = 1;
				$DAO_user->update();

				CUserHistory::recordUserEvent($DAO_user->id, $DAO_store->id, 'null', 700, 'null', 'null', 'Agreed to BackOffice NDA');

				require_once('CMail.inc');

				$Mail = new CMail();

				$emailHTML = '<a href="' . HTTPS_SERVER . '/backoffice/user-details?id=' . $DAO_user->id . '">' . $DAO_user->firstname . ' ' . $DAO_user->lastname . '</a> has signed the BackOffice NDA.';

				if (!$DAO_user->isUserGroupHomeOffice())
				{
					$emailHTML .= ' <a href="' . HTTPS_SERVER . '/backoffice/store-details?id=' . $DAO_store->id . '">' . $DAO_store->store_name . '</a>.';
				}

				$Mail->send(ADMINISTRATOR_EMAIL, ADMINISTRATOR_EMAIL, 'josh.thayer@dreamdinners.com', 'josh.thayer@dreamdinners.com', $DAO_user->firstname . ' ' . $DAO_user->lastname . ' has signed the BackOffice NDA', $emailHTML, null, '', '', null, 'admin_generic');


				if ($DAO_user->isUserType(user_type: CUser::NEW_EMPLOYEE) || $DAO_user->isUserType(user_type: CUser::DISHWASHER))
				{
					CApp::bounce("/backoffice/safe-landing");
				}

				CApp::bounce($this->Template->back);
			}
			else
			{
				$this->Template->setErrorMsg('Unexpected error, the user was not found, please contact support.');
			}
		}

		if (CUser::getCurrentUser()->fadmin_nda_agree == 1 && empty($_GET['read_only']))
		{
			CApp::bounce($this->Template->back);
		}

		$this->Template->assign('hide_navigation', true);

		$this->Template->assign('read_only', false);
		if (!empty($_GET['read_only']))
		{
			$this->Template->assign('read_only', true);
		}

		$this->Template->assign('print_view', false);
		if (!empty($_REQUEST['print_view']) && $_REQUEST['print_view'] == true)
		{
			$this->Template->assign('print_view', true);
		}
	}
}