<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/BusinessObject/CMembershipHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

ini_set('memory_limit', '768M');

define("TESTING", false);

try {

	$daoMenu = DAO_CFactory::create('menu');
	$daoMenu->id = 258;
	$DAO_menu_item = $daoMenu->findMenuItemDAO(array(
		'exclude_menu_item_category_sides_sweets' => false,
		'exclude_menu_item_category_core' => true,
		'exclude_menu_item_category_efl' => true,
		'menu_to_menu_item_store_id' => 'all',
		'having' => 'ISNULL(menu_item_inventory.id) AND menu_to_menu_item.store_id'
	));

	while($DAO_menu_item->fetch())
	{
		if (empty($DAO_menu_item->DAO_menu_item_inventory->id) && !empty($DAO_menu_item->store_id))
		{
			$menuInv = DAO_CFactory::create('menu_item_inventory');

			$menuInv->menu_id = $DAO_menu_item->menu_id;
			$menuInv->recipe_id = $DAO_menu_item->recipe_id;
			$menuInv->store_id = $DAO_menu_item->store_id;

			if (!$menuInv->find())
			{
				$menuInv->initial_inventory = 0;
				$menuInv->override_inventory = 0;

				$menuInv->insert();
			}
		}
	}

} catch (exception $e) {
}


?>