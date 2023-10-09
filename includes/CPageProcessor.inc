<?php

/**
 * CPageProcessor is meant to be a protected AJAX script .. protected by authentication.
 */
class CPageProcessor
{

	protected $doGenericInputCleaning = true;
	protected $inputTypeMap = array();
	protected $CurrentBackOfficeStore = null;
	protected $CurrentUser = null;
	protected $CurrentFutureMenus = null;
	protected $CurrentFutureMenusDelivered = null;

	protected $Template = null;

	public $use_GPC = false;

	function __construct()
	{
		$this->CurrentBackOfficeStore = CBrowserSession::getCurrentFadminStoreObj();
		$this->CurrentUser = CUser::getCurrentUser();
		$this->CurrentFutureMenus = CMenu::getCurrentAndFutureMenuObj();
		$this->CurrentFutureMenusDelivered = CMenu::getCurrentAndFutureDeliveredMenuObj();

		$this->Template = CApp::instance()->template();
		if (isset($this->Template))
		{
			$this->Template->assign('UserCurrent', $this->CurrentUser);
			$this->Template->assign('CurrentBackOfficeStore', $this->CurrentBackOfficeStore);
		}
	}

	function runPublic()
	{
		echo json_encode(array(
			'processor_success' => false,
			'processor_message' => 'No Permission'
		));
		exit;
	}

	function runCustomer()
	{
		$this->runPublic();
	}

	function runNewEmployee()
	{
		$this->runCustomer();
	}

	function runDishwasher()
	{
		$this->runNewEmployee();
	}

	function runOpsSupport()
	{
		$this->runDishwasher();
	}

	function runOpsLead()
	{
		$this->runDishwasher();
	}

	function runEventCoordinator()
	{
		$this->runDishwasher();
	}

	function runFranchiseStaff()
	{
		$this->runDishwasher();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseStaff();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseLead();
	}

	function runFranchiseOwner()
	{
		$this->runFranchiseManager();
	}

	function runHomeOfficeStaff()
	{
		$this->runCustomer();
	}

	function runHomeOfficeManager()
	{
		$this->runHomeOfficeStaff();
	}

	function runSiteAdmin()
	{
		$this->runHomeOfficeManager();
	}

	function cleanArrayRecursive(&$inArray)
	{
		$inArray = CGPC::do_clean($inArray, TYPE_ARRAY);
		foreach ($inArray as $key => &$val)
		{
			if (is_array($inArray[$key]))
			{
				$this->cleanArrayRecursive($inArray[$key]);
			}
			else
			{
				$inArray[$key] = CGPC::do_clean($inArray[$key], TYPE_STR);
			}
		}
	}

	function cleanInput()
	{
		if ($this->doGenericInputCleaning)
		{
			foreach ($_POST as $k => $v)
			{
				if (isset($this->inputTypeMap[$k]))
				{
					$_POST[$k] = CGPC::do_clean($_POST[$k], $this->inputTypeMap[$k]);
				}
				else
				{
					if (is_array($_POST[$k]))
					{
						$this->cleanArrayRecursive($_POST[$k]);
					}
					else
					{
						$_POST[$k] = CGPC::do_clean($_POST[$k], TYPE_STR);
					}
				}
			}
			foreach ($_GET as $k => $v)
			{
				if (isset($this->inputTypeMap[$k]))
				{
					$_GET[$k] = CGPC::do_clean($_GET[$k], $this->inputTypeMap[$k]);
				}
				else
				{
					// Note: arrays in the GET params is not supported
					$_GET[$k] = CGPC::do_clean($_GET[$k], TYPE_STR);
				}
			}
			foreach ($_REQUEST as $k => $v)
			{
				if (isset($this->inputTypeMap[$k]))
				{
					$_REQUEST[$k] = CGPC::do_clean($_REQUEST[$k], $this->inputTypeMap[$k]);
				}
				else
				{
					if (is_array($_REQUEST[$k]))
					{
						$this->cleanArrayRecursive($_REQUEST[$k]);
					}
					else
					{
						$_REQUEST[$k] = CGPC::do_clean($_REQUEST[$k], TYPE_STR);
					}
				}
			}
		}
	}

}

?>