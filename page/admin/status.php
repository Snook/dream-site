<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_status extends CPageAdminOnly {

	function runSiteAdmin()
	{
		return $this->runStatus();
	}
	
	function runHomeOfficeManager()
	{
		return $this->runStatus();
	}
	

	function runStatus()
	{
		$tpl = CApp::instance()->template();

		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT * FROM menu ORDER BY id DESC LIMIT 5");

		$menu_array = array();

		while ($Menu->fetch())
		{
			$menu_array[$Menu->id] = clone $Menu;
		}

		$tpl->assign('menus', $menu_array);
	}
}

?>