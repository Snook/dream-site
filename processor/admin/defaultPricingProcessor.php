<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");

class processor_admin_defaultPricingProcessor extends CPageProcessor
{

	private $currentStore = null;

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_POST['action']) && $_POST['action'] == 'save')
		{
			foreach ($_POST["data"] as $recipe_id => $setting)
			{
				$DAO_default_prices = DAO_CFactory::create('default_prices', true);
				$DAO_default_prices->store_id = $_POST["store_id"];
				$DAO_default_prices->recipe_id = $recipe_id;

				if ($DAO_default_prices->find(true))
				{
					$update_DAO_default_prices = clone $DAO_default_prices;
					if (isset($setting['ovr']))
					{
						$update_DAO_default_prices->default_price = $setting['ovr'];
					}
					if (isset($setting['vis']))
					{
						$update_DAO_default_prices->default_customer_visibility = $setting['vis'];
					}
					if (isset($setting['form']))
					{
						$update_DAO_default_prices->show_on_order_form = $setting['form'];
					}
					if (isset($setting['hid']))
					{
						$update_DAO_default_prices->default_hide_completely = $setting['hid'];
					}
					$update_DAO_default_prices->update($DAO_default_prices);
				}
				else
				{
					if (isset($setting['ovr']))
					{
						$DAO_default_prices->default_price = $setting['ovr'];
					}
					if (isset($setting['vis']))
					{
						$DAO_default_prices->default_customer_visibility = $setting['vis'];
					}
					if (isset($setting['form']))
					{
						$DAO_default_prices->show_on_order_form = $setting['form'];
					}
					if (isset($setting['hid']))
					{
						$DAO_default_prices->default_hide_completely = $setting['hid'];
					}
					$DAO_default_prices->insert();
				}
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Defaults saved.'
			));
		}

		if (isset($_POST['action']) && $_POST['action'] == 'retrieve')
		{
			$DAO_default_prices = DAO_CFactory::create('default_prices', true);
			$DAO_default_prices->store_id = $_POST['store_id'];

			$returnData = array();

			if ($DAO_default_prices->find())
			{
				while ($DAO_default_prices->fetch())
				{
					$returnData[$DAO_default_prices->recipe_id] = array(
						'ovr' => $DAO_default_prices->default_price,
						'vis' => $DAO_default_prices->default_customer_visibility,
						'hid' => $DAO_default_prices->default_hide_completely,
						'form' => $DAO_default_prices->show_on_order_form
					);
				}
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved store select form.',
				'settings' => $returnData
			));
		}
	}

}

?>