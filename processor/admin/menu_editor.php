<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/Store_weekly_data.php");
require_once("includes/DAO/BusinessObject/CMenuItem.php");
require_once("includes/DAO/BusinessObject/COrders.php");

class processor_admin_menu_editor extends CPageProcessor
{
	function runSiteAdmin()
	{
		$this->menuEditorAddItem();
	}

	function runHomeOfficeManager()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseLead()
	{
		$this->menuEditorAddItem();
	}

	function runOpsLead()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseManager()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseOwner()
	{
		$this->menuEditorAddItem();
	}

	function menuEditorAddItem()
	{
		$menu_id = false;

		if (!empty($_REQUEST['menu_id']) && is_numeric($_REQUEST['menu_id']))
		{
			$menu_id = $_REQUEST['menu_id'];
		}

		if (!empty($_POST['op']))
		{
			if ($_POST['op'] == 'get_items')
			{
				$menuItems = array();

				if ($_POST['item_type'] == 'efl')
				{
					$menuItems = CMenuItem::getEFLEligibleItems($menu_id, CBrowserSession::getCurrentFadminStoreID());
				}
				else if ($_POST['item_type'] == 'sides')
				{
					$menuItems = CMenuItem::getEFLEligibleSidesSweets($menu_id, CBrowserSession::getCurrentFadminStoreID());
				}

				if (!empty($menuItems))
				{
					$this->Template->assign('menuItems', $menuItems);

					$menu_item_html = $this->Template->fetch('admin/subtemplate/menu_editor/menu_editor_add_item.tpl.php');

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu items',
						'menu_item_html' => $menu_item_html
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'No menu items found'
					));
				}
			}

			if ($_POST['op'] == 'add_items')
			{
				$req_entree_ids = CGPC::do_clean((!empty($_POST['items']) ? $_POST['items'] : false), TYPE_ARRAY);
				$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT);

				if (!empty($req_entree_ids))
				{
					foreach ($req_entree_ids as $entree_id)
					{
						CMenuItem::duplicateItemForMenu($entree_id, $req_menu_id, $req_store_id);
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu items added.'
					));
				}
			}

			if ($_POST['op'] == 'menu_item_info' && is_numeric($_POST['store_id']))
			{
				$req_entree_id = CGPC::do_clean((!empty($_POST['entree_id']) ? $_POST['entree_id'] : false), TYPE_INT);
				$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT);
				$req_recipe_id = CGPC::do_clean((!empty($_POST['recipe_id']) ? $_POST['recipe_id'] : false), TYPE_INT);

				if ($req_entree_id)
				{
					$result = CMenuItem::getInfoForEFLCandidate($req_recipe_id, $req_menu_id, $req_store_id);

					$tpl = new CTemplate();

					$tpl->assign('curItem', $result['item_detail']);
					$tpl->assign('salesData', $result['sales_data']);
					$tpl->assign('cardData', $result['card_data']);

					$data = $tpl->fetch('admin/subtemplate/menu_editor/menu_editor_item_info.tpl.php');

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu item info retrieved.',
						'data' => $data
					));
				}
			}

			if ($_POST['op'] == 'default_pricing_get')
			{
				$DAO_default_prices = DAO_CFactory::create('default_prices');
				$DAO_default_prices->store_id = $_POST['store_id'];
				$DAO_default_prices->find();

				$pricingData = array();

				while ($DAO_default_prices->fetch())
				{
					// Currently just FULL, this is only used for Sides & Sweets
					$pricingData[$DAO_default_prices->recipe_id][CMenuItem::FULL] = array(
						'default_customer_visibility' => $DAO_default_prices->default_customer_visibility,
						'default_hide_completely' => $DAO_default_prices->default_hide_completely,
						'default_price' => $DAO_default_prices->default_price
					);
				}

				if (!empty($pricingData))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Pricing retrieved.',
						'pricingData' => $pricingData
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Pricing not found'
					));
				}
			}

			if ($_POST['op'] == 'default_pricing_save')
			{
				foreach ($_POST['prices'] as $recipe_id => $pricingArray)
				{
					$DAO_default_prices = DAO_CFactory::create('default_prices');
					$DAO_default_prices->store_id = $_POST['store_id'];
					$DAO_default_prices->recipe_id = $recipe_id;

					if ($DAO_default_prices->find(true))
					{
						$org_DAO_default_prices = $DAO_default_prices->cloneObj(false, false);

						$DAO_default_prices->default_price = $pricingArray['price'];
						$DAO_default_prices->default_customer_visibility = $pricingArray['vis'];
						$DAO_default_prices->default_hide_completely = $pricingArray['hid'];

						$DAO_default_prices->update($org_DAO_default_prices);
					}
					else
					{
						$DAO_default_prices->default_price = $pricingArray['price'];
						$DAO_default_prices->default_customer_visibility = $pricingArray['vis'];
						$DAO_default_prices->default_hide_completely = $pricingArray['hid'];

						$DAO_default_prices->insert();
					}
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Pricing updated.',
					'dd_toasts' => array(
						array('message' => 'Default pricing saved.')
					)
				));
			}
		}
	}
}

?>