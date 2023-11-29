<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO.inc");

$_time_start = time();

$DAO_menu = DAO_CFactory::create('menu', true);
$DAO_menu->id = CMenu::getCurrentMenuId();
$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
	'menu_to_menu_item_store_id' => 244,
	'exclude_menu_item_category_core' => false,
	'exclude_menu_item_category_efl' => false,
	'exclude_menu_item_category_sides_sweets' => false
));

$menuItemArray = array();
while ($DAO_menu_item->fetch(array('digestMenuItem' => true)))
{
	//$menuItemArray[$DAO_menu_item->id] = $DAO_menu_item->id;
	$menuItemArray[$DAO_menu_item->id] = $DAO_menu_item->cloneObj();
}

$_time_end = time();
$_time_diff = $_time_end - $_time_start;

$debugbreakpoint = true; // just a var to set a breakpoint on
?>