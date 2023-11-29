<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStoreActivityLog.php");
$totalAlertsSent = 0;

try {
	$THRESHOLD = 18; //servings

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: low_inventory_alert called but cron is disabled");
		exit;
	}
	date_default_timezone_set('America/New_York');
	$eventTime = date("Y-m-d H:i:s");
	$currentMenuId = CMenu::getCurrentMenuId();



	$stores = DAO_CFactory::create('store');
	$stores->active = 1;
	$stores->find();
	while($stores->fetch()){
		$templateText = 'BackOffice Low Inventory Notification:'.PHP_EOL;
		$templateHtml = 'BackOffice Low Inventory Notification:<br>';
		$menu_item_inventory = DAO_CFactory::create('menu_to_menu_item');
		$hasLowInventory = false;
		$query = "SELECT
				menu_item_category.category_type AS 'category',
				menu_item.recipe_id,
				menu_item.menu_item_name,
       			menu_item.pricing_type,
				menu_item_inventory.initial_inventory,
				menu_item_inventory.override_inventory,
				menu_item_inventory.number_sold,
				menu_to_menu_item.override_price,
				menu_to_menu_item.is_visible,
       			 menu_to_menu_item.show_on_order_form,
				menu_to_menu_item.is_hidden_everywhere,
       			menu.menu_name
				FROM menu_to_menu_item
				INNER JOIN  menu_item  ON menu_to_menu_item.menu_item_id=menu_item.id
				LEFT JOIN menu on menu.id = menu_to_menu_item.menu_id and menu.id = $currentMenuId AND menu.is_deleted = '0'
        		LEFT JOIN recipe on recipe.recipe_id = menu_item.recipe_id and recipe.override_menu_id = $currentMenuId AND recipe.is_deleted = '0'
        		LEFT JOIN menu_item_category on menu_item.menu_item_category_id = menu_item_category.id
       			LEFT JOIN menu_item_inventory on menu_item_inventory.recipe_id = menu_item.recipe_id and menu_item_inventory.store_id = $stores->id and menu_item_inventory.menu_id = $currentMenuId and menu_item_inventory.is_deleted = 0 
				where menu_to_menu_item.store_id = $stores->id
				and  menu_to_menu_item.menu_id = $currentMenuId
				AND menu_to_menu_item.is_deleted = 0
				AND menu_item.is_deleted = 0
				AND menu_item.pricing_type = 'FULL'
				AND ( menu_item.menu_item_category_id < 4 or (menu_item.menu_item_category_id < 5  AND  (menu_item.menu_item_category_id = 4 and menu_item.is_store_special = 0)))
				AND menu_to_menu_item.is_visible = 1
				and menu_to_menu_item.is_hidden_everywhere = 0";

		$menu_item_inventory->query($query);
		while($menu_item_inventory->fetch()){


			$remainingInventory = (is_null($menu_item_inventory->override_inventory) ? $menu_item_inventory->initial_inventory - $menu_item_inventory->number_sold : $menu_item_inventory->override_inventory - $menu_item_inventory->number_sold);
			//used to uniquely identify events by data - eliminate duplicates per day
			$compositeKey = $stores->id.'|'.$currentMenuId.'|'.$menu_item_inventory->recipe_id .'|'.date("Y-m-d");
			$recordExists= CStoreActivityLog::doesKeyExist($compositeKey);
			if( $remainingInventory <= $THRESHOLD && !$recordExists){

				$typeId = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::INVENTORY,CStoreActivityLog::SUBTYPE_LOW);
				$description = 'Low Inventory '.$menu_item_inventory->menu_name.': '.$menu_item_inventory->menu_item_name . ' has '.$remainingInventory.' servings remaining.';
				CStoreActivityLog::addEvent($stores->id,$description,$eventTime,$typeId,$compositeKey);

				$templateText .= $description.PHP_EOL;
				$templateHtml .= $description.'<br>';
				$hasLowInventory = true;

			}
		}

		if($hasLowInventory && $stores->receive_low_inv_alert){
			$totalAlertsSent++;
			$date = date("Y-m-d");
			CStoreActivityLog::sendStoreAlertEmail($stores->store_name,$stores->email_address,'Low Inventory Alert ' .$date,$templateText,$templateHtml);
		}
	}

	CLog::RecordCronTask($totalAlertsSent, CLog::SUCCESS, CLog::ALERT_LOW_INVENTORY, "low inventory alert finished");



} catch (exception $e) {
	CLog::RecordCronTask($totalAlertsSent, CLog::FAILURE, CLog::ALERT_LOW_INVENTORY, "low inventory alert: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>