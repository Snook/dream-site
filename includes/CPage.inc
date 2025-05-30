<?php
require_once("CApp.inc");

/**
 * CPage member functions are called by CApp based on the user's access level.
 *
 * Each page needs to override the run methods based on the functionality that is
 * available for each access group. Access is by default hierarchical.
 */
class CPage
{
	protected object|null $CurrentUser = null;
	protected ?array $CurrentFutureMenus = null;
	protected ?array $CurrentFutureMenusDelivered = null;

	protected object|null $Template = null;

	public bool $use_GPC = false;

	/**
	 * @throws Exception
	 */
	function __construct()
	{
		$this->CurrentUser = CUser::getCurrentUser();
		$this->CurrentFutureMenus = CMenu::getCurrentAndFutureMenuObj();
		$this->CurrentFutureMenusDelivered = CMenu::getCurrentAndFutureDeliveredMenuObj();

		$this->Template = CApp::instance()->template();
		if (isset($this->Template))
		{
			$this->Template->assign('UserCurrent', $this->CurrentUser);
		}
	}

	function runPublic()
	{
	}

	function runCustomer()
	{
		$this->runPublic();
	}

	function runFranchiseStaff()
	{
		$this->runCustomer();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseStaff();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseStaff();
	}

	function runFranchiseOwner()
	{
		$this->runFranchiseManager();
	}

	function runHomeOfficeStaff()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runHomeOfficeStaff();
	}

	function runSiteAdmin()
	{
		$this->runHomeOfficeManager();
	}

	function runManufacturerStaff()
	{
		$this->runCustomer();
	}

	function runEventCoordinator()
	{
		$this->runCustomer();
	}

	function runOpsLead()
	{
		$this->runCustomer();
	}

	function runOpsSupport()
	{
		$this->runCustomer();
	}

	function runDishwasher()
	{
		$this->runCustomer();
	}

	function runNewEmployee()
	{
		$this->runCustomer();
	}
}