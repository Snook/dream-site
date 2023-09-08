<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_access_agreement extends CPageAdminOnly {

	function runNewEmployee()
	{
		$this->runAccessAgreement();
	}

	function runEventCoordinator()
	{
		$this->runAccessAgreement();
	}
	function runOpsLead()
	{
		$this->runAccessAgreement();
	}
	function runOpsSupport()
	{
		$this->runAccessAgreement();
	}
	function runDishwasher()
	{
		$this->runAccessAgreement();
	}
	function runManufacturerStaff()
	{
		$this->runAccessAgreement();
	}
	function runFranchiseOwner()
	{
		$this->runAccessAgreement();
	}
	function runFranchiseStaff()
	{
		$this->runAccessAgreement();
	}
	function runFranchiseLead()
	{
		$this->runAccessAgreement();
	}
	function runFranchiseManager()
	{
		$this->runAccessAgreement();
	}
	function runHomeOfficeStaff()
	{
		$this->runAccessAgreement();
	}
	function runHomeOfficeManager()
	{
		$this->runAccessAgreement();
	}
	function runSiteAdmin()
	{
		$this->runAccessAgreement();
	}

	function runAccessAgreement()
	{
		$tpl = CApp::instance()->template();

		$tpl->assign('back', '?page=admin_main');

		if (!empty($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back']);
		}

		if (!empty($_POST['agree_to_nda']) && !empty($_POST['agree_to_nda_submit']))
		{
			$Store = DAO_CFactory::create('store');
			$Store->id = CBrowserSession::getCurrentFadminStore();
			$Store->find(true);

			$User = DAO_CFactory::create('user');
			$User->id = CUser::getCurrentUser()->id;

			if ($User->find(true))
			{
				$User->fadmin_nda_agree = 1;
				$User->update();

				CUserHistory::recordUserEvent($User->id, $Store->id, 'null', 700, 'null', 'null', 'Agreed to BackOffice NDA');

				require_once('CMail.inc');

				/* Notify if user has .local or .com email address */
				list($account, $domain) = explode('@', $User->primary_email);

				if (strtolower($domain) == 'dreamdinners.com' || strtolower($domain) == 'dreamdinners.local')
				{
					$Mail = new CMail();

					$emailHTML = '<a href="' . HTTPS_SERVER . '/?page=admin_user_details&id=' . $User->id . '">' . $User->firstname . ' ' . $User->lastname . '</a> has signed the BackOffice NDA.';

					if ($User->user_type != CUser::SITE_ADMIN && $User->user_type != CUser::HOME_OFFICE_MANAGER && $User->user_type != CUser::HOME_OFFICE_STAFF)
					{
						$emailHTML .= ' <a href="' . HTTPS_SERVER . '/?page=admin_store_details&id=' . $Store->id . '">' . $Store->store_name . '</a>.';
					}

					$Mail->send(ADMINISTRATOR_EMAIL,
						ADMINISTRATOR_EMAIL,
						'elaine.sanchez@dreamdinners.com',
						'elaine.sanchez@dreamdinners.com',
						$User->firstname . ' ' . $User->lastname . ' has signed the BackOffice NDA',
						$emailHTML,
						null,
						'',
						'',
						null,
						'admin_generic');
				}

				if ($User->user_type == CUser::NEW_EMPLOYEE || $User->user_typw == CUser::DISHWASHER)
				{
					unset(CApp::instance()->template()->back);
					CApp::bounce("?page=admin_safe_landing");
				}

				CApp::bounce($tpl->back);
			}
			else
			{
				$tpl->setErrorMsg('Unexpected error, the user was not found, please contact support.');
			}
		}

		if (CUser::getCurrentUser()->fadmin_nda_agree == 1 && empty($_GET['read_only']))
		{
			CApp::bounce($tpl->back);
		}

		$tpl->assign('hide_navigation', true);

		$tpl->assign('read_only', false);
		if (!empty($_GET['read_only']))
		{
			$tpl->assign('read_only', true);
		}

		$tpl->assign('print_view', false);
		if (!empty($_REQUEST['print_view']) && $_REQUEST['print_view'] == true)
		{
			$tpl->assign('print_view', true);
		}
	}
}
?>