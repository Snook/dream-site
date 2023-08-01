<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_report_access_levels extends CPageAdminOnly
{

	function runHomeOfficeManager()
	{
		$this->runReportAccessLevels();
	}

 	function runSiteAdmin()
 	{
 		$this->runReportAccessLevels();
 	}

	function runReportAccessLevels()
	{
		$tpl = CApp::instance()->template();

		$user = DAO_CFactory::create('user');
		$user->query("SELECT
			u.id,
			u.user_type,
			u.firstname,
			u.lastname,
			u.primary_email,
			u.last_login
			FROM
			`user` AS u
			WHERE (u.user_type = '". CUser::HOME_OFFICE_MANAGER . "' OR u.user_type = '". CUser::HOME_OFFICE_STAFF . "' OR u.user_type = '". CUser::SITE_ADMIN . "')
			AND u.is_deleted = '0'
			ORDER BY u.user_type DESC, u.lastname ASC");

		while ($user->fetch())
		{
			$userArray[$user->id] = $user->toArray();
		}

		$tpl->assign('users', $userArray);
	}
}
?>