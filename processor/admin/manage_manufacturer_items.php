<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CRecipe.php");

class processor_admin_manage_manufacturer_items extends CPageProcessor
{

	function runHomeOfficeStaff()
	{
		$this->manageList();
	}

	function runHomeOfficeManager()
	{
		$this->manageList();
	}

	function runSiteAdmin()
	{
		$this->manageList();
	}

	function manageList()
	{
		if (!empty($_POST['recipe_id']) && !empty($_POST['store_id']) && is_numeric($_POST['recipe_id']) && is_numeric($_POST['store_id']))
		{
			if ($_POST['state'] == 'yes')
			{
				CRecipe::assignManufacturingRecipe($_REQUEST['store_id'], $_REQUEST['recipe_id'], true);
			}
			else
			{
				CRecipe::assignManufacturingRecipe($_REQUEST['store_id'], $_REQUEST['recipe_id'], false);
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Recipe availability updated.',
				'dd_toasts' => array(
					array('message' => 'Recipe availability updated.')
				)
			));
		}
	}
}
?>