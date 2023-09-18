<?php


/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */

require_once('includes/CCart2.inc');
require_once ("includes/CPageProcessor.inc");
require_once ("CTemplate.inc");

class processor_admin_defaultPricingProcessor extends CPageProcessor {

	 private $currentStore = null;

	function runSiteAdmin(){
	 	$this->runFranchiseOwner();
	}

    function runFranchiseLead() {
	 	$this->runFranchiseOwner();
	 }
	 function runOpsLead() {
	 	$this->runFranchiseOwner();
	 }

	 function runFranchiseManager() {
	 	$this->runFranchiseOwner();
	 }

	 function runHomeOfficeManager() {
	 	$this->runFranchiseOwner();
	 }

	 function runFranchiseOwner() {


		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$storeID = null;
		if (isset($_REQUEST['store_id'])) $storeID = $_REQUEST['store_id'];

		if (!$storeID || !is_numeric($storeID)) return "error";



		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
		{


			$data = explode("|", $_POST['data']);
			$extra = array_pop($data);

			$org_data = array();

			$existingItems = DAO_CFactory::create('default_prices');
			$existingItems->store_id = $_REQUEST['store_id'];

			$existingItems->find();

			while($existingItems->fetch())
			{
				$org_data[$existingItems->recipe_id] = $existingItems->default_price;
			}

			foreach($data as $thisItem)
			{

				list($recipeID, $showCustomer, $hideEverywhere, $itemPrice ) = explode("~", $thisItem);

				$recipeID = CGPC::do_clean($recipeID, TYPE_INT);
				$showCustomer = CGPC::do_clean($showCustomer, TYPE_INT);
				$hideEverywhere = CGPC::do_clean($hideEverywhere, TYPE_INT);
				$itemPrice = CGPC::do_clean($itemPrice, TYPE_NUM);

				if (array_key_exists($recipeID, $org_data))
				{
					//update
					$upateObj = DAO_CFactory::create('default_prices');
					$upateObj->query("update default_prices set default_price = $itemPrice, default_customer_visibility = $showCustomer, default_hide_completely = $hideEverywhere where store_id = $storeID and recipe_id = $recipeID and is_deleted = 0 ");
				}
				else
				{
					//insert
					$insertObj = DAO_CFactory::create('default_prices');
					$insertObj->store_id = $storeID;
					$insertObj->recipe_id = $recipeID;
					$insertObj->default_price = $itemPrice;
					$insertObj->default_customer_visibility = $showCustomer;
					$insertObj->default_hide_completely = $hideEverywhere;

					$insertObj->insert();
				}


			}


		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retrieve')
		{


			$org_data = array();

			$existingItems = DAO_CFactory::create('default_prices');
			$existingItems->store_id = $_REQUEST['store_id'];

			$existingItems->find();

			$returnData = "";

			while($existingItems->fetch())
			{
				$returnData .= $existingItems->recipe_id . "~" .  $existingItems->default_customer_visibility . "~" .
									  $existingItems->default_hide_completely .  "~" .  $existingItems->default_price . "|";

			}

			print $returnData;

		}



	}

}

?>