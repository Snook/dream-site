<?php
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CMenu.php');
require_once('DAO/BusinessObject/CStatesAndProvinces.php');

class page_location extends CPage
{

	function runPublic()
	{
		parent::runPublic();

		$store_id = $_REQUEST['select_store'];
		$menu_id = $_REQUEST['select_menu'];

		if( !empty($store_id) && !empty($menu_id))
		{
			CApp::bounce('menu/'.$store_id.'-'.$menu_id);
		}
		else
		{
			CApp::bounce('main.php?page=locations');
		}
	}
}

?>