<?php // page_admin_errors.php

/**
 * @author Todd Wallar
 */

require_once("includes/CPageAdminOnly.inc");

class page_admin_errors extends CPageAdminOnly
{


    function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

 	function runSiteAdmin()
 	{
		$tpl = CApp::instance()->template();

		$type = false;
		if ( isset($_REQUEST['log_type']) && $_REQUEST['log_type'] )
		{
			$type = CGPC::do_clean($_REQUEST['log_type'],TYPE_STR);
		}

		$tpl->assign('stuff', $this->DisplayAdminErrorLog($type));
 	}

	function DisplayAdminErrorLog($type = false)
	{
		$db = CLog::instance()->connect();

		if ( $type )
		{
			$query = "SELECT * FROM " . CLog::getEventLogTable() . " WHERE log_type = '$type' ORDER BY id DESC LIMIT 0,50 ";
		}
		else
		{
			$query = "SELECT * FROM " . CLog::getEventLogTable() . " ORDER BY id DESC LIMIT 0,50 ";
		}

		$res = mysqli_query($db, $query);

		$resultsArray = array();
		while($row = mysqli_fetch_array($res))
		{
			$resultsArray[] = $row;
		}

		return $resultsArray;
	}
}
?>