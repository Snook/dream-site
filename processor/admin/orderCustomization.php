<?php
/*
 * Created on October 26, 2011
 * project_name orderAdminNotes
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_orderCustomization extends CPageProcessor
{

	function runFranchiseStaff()
	{
		$this->adminNotes();
	}

	function runFranchiseLead()
	{
		$this->adminNotes();
	}

	function runEventCoordinator()
	{
		$this->adminNotes();
	}

	function runOpsLead()
	{
		$this->adminNotes();
	}

	function runOpsSupport()
	{
		$this->adminNotes();
	}

	function runFranchiseManager()
	{
		$this->adminNotes();
	}

	function runFranchiseOwner()
	{
		$this->adminNotes();
	}

	function runHomeOfficeStaff()
	{
		$this->adminNotes();
	}

	function runHomeOfficeManager()
	{
		$this->adminNotes();
	}

	function runSiteAdmin()
	{
		$this->adminNotes();
	}

	function adminNotes()
	{
		// Process post
		if (isset($_POST['order_id']) && is_numeric($_POST['order_id']) && isset($_POST['customizations']))
		{
			self::update_order_meal_customization($_POST['order_id'], $_POST['customizations']);
		}
	}



	function update_order_meal_customization($order_id, $mealCustomUpdates)
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$order = DAO_CFactory::create('orders');
		$order->id = $order_id;

		$mealCustomUpdates = stripslashes(html_entity_decode($mealCustomUpdates));

		if ($order->find(true))
		{
			$mealCustomUpdates = json_decode($mealCustomUpdates);
			$orderCustomizations = OrdersCustomization::getInstance($order);
			$order = $orderCustomizations->updateMealCustomization($mealCustomUpdates,true);

			$order->opted_to_customize_recipes = $orderCustomizations->hasMealCustomizationPreferencesSet();
			$order->setAllowRecalculateMealCustomizationFeeClosedSession(true);
			$order->recalculate();


			// menu item was set to zero, removed item from cart
			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Update Order Customization Fee.',
				'orderInfo' => $order->toArray(),
				'cost' => $order->subtotal_meal_customization_fee
			));

		}
	}
}
?>