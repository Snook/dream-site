<?php
require_once("includes/CPageAdminOnly.inc");
require_once('DAO/BusinessObject/CUser.php');
require_once('DAO/BusinessObject/CFile.php');

class page_admin_reports extends CPageAdminOnly
{
	const LIMITED_P_AND_L_ACCESS_SECTION_ID = 8;
	const LIMITED_WEEKLY_REPORT_ACCESS = 9;

	function runFranchiseStaff()
	{
		$this->runReports();
	}

	function runFranchiseLead()
	{
		$this->runReports();
	}

	function runEventCoordinator()
	{
		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

		$this->Template->assign('hasPandLAccess', $hasPandLAccess);

		$this->runReports();
	}

	function runOpsLead()
	{
		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

		$this->Template->assign('hasPandLAccess', $hasPandLAccess);

		$this->runReports();
	}

	function runOpsSupport()
	{
		$this->runReports();
	}

	function runFranchiseManager()
	{
		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);
		$hasWeeklyReportAccess = CApp::directAccessControlTest(self::LIMITED_WEEKLY_REPORT_ACCESS, CUser::getCurrentUser()->id);

		$this->Template->assign('hasPandLAccess', $hasPandLAccess);
		$this->Template->assign('hasWeeklyReportAccess', $hasWeeklyReportAccess);

		$this->runReports();
	}

	function runFranchiseOwner()
	{
		$this->runReports();
	}

	function runHomeOfficeStaff()
	{
		$this->runReports();
	}

	function runHomeOfficeManager()
	{
		$this->runReports();
	}

	function runManufacturerStaff()
	{
		$this->runReports();
	}

	function runSiteAdmin()
	{
		$this->runReports();
	}

	function runReports()
	{
		if (isset($_REQUEST['file_id']) && is_numeric($_REQUEST['file_id']))
		{
			$File = CFile::downloadFile($_REQUEST['file_id']);

			if (!$File)
			{
				$this->Template->setErrorMessage("An error occurred when retrieving the file.");
				CLog::RecordIntense('Missing File or shenanigans', 'ryan.snook@dreamdinners.com');
			}
		}

		if (defined('HOME_SITE_SERVER'))
		{
			$this->Template->assign('HOME_SITE_SERVER', true);
		}

		if (!defined('HOME_SITE_SERVER'))
		{
			$canoverride = CApp::overrideAdminPage();
			if ($canoverride == true)
			{
				$this->Template->assign('HOME_SITE_SERVER', true);
			}
		}
	}
}

?>