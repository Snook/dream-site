<?php
/**
 * subclass of DAO/Menu_item
 */
require_once 'DAO/Menu_item_inventory_history.php';
require_once 'DAO/BusinessObject/COrders.php';

class CMenuItemInventoryHistory extends DAO_Menu_item_inventory_history
{
	const ATTEND = 'ATTEND';
	const RESCHEDULE_P_TO_F = 'RESCHEDULE_P_TO_F';
	const RESCHEDULE_F_TO_P = 'RESCHEDULE_F_TO_P';
	const CANCEL = 'CANCEL';
	const EDIT_PHASE_1 = 'EDIT_PHASE_1'; 
	const EDIT_PHASE_2 = 'EDIT_PHASE_2'; 
	const ORDER = 'ORDER';
	const MENU_EDIT = 'MENU_EDIT';
	
	// Note: this is called after the update so IS values reflect the event.   IS = Inventory state
	static function RecordEvent($store_id, $recipe_id, $order_id, $eventType, $delta)
	{
		
		$logObj = DAO_CFactory::create('menu_item_inventory_history');
		
		
		$invObj = DAO_CFactory::create('menu_item_inventory');
		$invObj->query("select override_inventory, number_sold from menu_item_inventory where store_id = $store_id and recipe_id = $recipe_id
						and isnull(menu_id) and is_deleted = 0");
		
		$invObj->fetch();
		
		$logObj->store_id = $store_id;
		$logObj->recipe_id = $recipe_id;
		$logObj->order_id = $order_id;
		$logObj->event_type = $eventType;
		$logObj->delta = $delta;
		$logObj->IS_override_inventory = $invObj->override_inventory;
		$logObj->IS_number_sold = $invObj->number_sold;
		$logObj->IS_future_pickups = CMenu::getFutureFTPickupsPerItem($store_id, $recipe_id);
		$logObj->created_by = CUser::getCurrentUser()->id;
		
		$logObj->insert();
		
		
	}
	
	
}
?>