<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/Store_weekly_data.php");
require_once("includes/DAO/BusinessObject/CMenuItem.php");
require_once("includes/DAO/BusinessObject/COrders.php");

class processor_admin_menuEditor extends CPageProcessor
{
	function runSiteAdmin()
	{
		$this->menuEditorProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->menuEditorProcessor();
	}

	function runFranchiseLead()
	{
		$this->menuEditorProcessor();
	}

	function runOpsLead()
	{
		$this->menuEditorProcessor();
	}

	function runFranchiseManager()
	{
		$this->menuEditorProcessor();
	}

	function runFranchiseOwner()
	{
		$this->menuEditorProcessor();
	}

	function menuEditorProcessor()
	{
		if ($_POST['op'] == 'save_preorder_inventory' && is_numeric($_POST['store_id']) && is_numeric($_POST['menu_id']) and !empty($_POST['preorderInventory']))
		{
			$store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : 0), TYPE_INT);
			$menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : 0), TYPE_INT);

			foreach ($_POST['preorderInventory'] as $recipe_id => $inv_amount)
			{
				$week1Number = $_POST['week1Inv'][$recipe_id];
				$week2Number = $_POST['week2Inv'][$recipe_id];
				$week3Number = $_POST['week3Inv'][$recipe_id];
				$week4Number = $_POST['week4Inv'][$recipe_id];

				if (isset($_POST['week5Inv'][$recipe_id]))
				{
					$week5Number = $_POST['week5Inv'][$recipe_id];
				}
				else
				{
					$week5Number = 0;
				}

				$invObj = DAO_CFactory::create('menu_item_inventory');
				$invObj->store_id = $store_id;
				$invObj->menu_id = $menu_id;
				$invObj->recipe_id = $recipe_id;

				if ($invObj->find(true))
				{
					$invObj->override_inventory = $inv_amount;
					$invObj->initial_inventory = $inv_amount;
					$invObj->week1_projection = $week1Number;
					$invObj->week2_projection = $week2Number;
					$invObj->week3_projection = $week3Number;
					$invObj->week4_projection = $week4Number;
					$invObj->week5_projection = $week5Number;

					$invObj->update();
				}
				else
				{
					$invObj->override_inventory = $inv_amount;
					$invObj->initial_inventory = $inv_amount;
					$invObj->week1_projection = $week1Number;
					$invObj->week2_projection = $week2Number;
					$invObj->week3_projection = $week3Number;
					$invObj->week4_projection = $week4Number;
					$invObj->week5_projection = $week5Number;

					$invObj->insert();
				}
			}
			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Store Sales mix values were saved.',
			));
			exit;
		}
		else if ($_POST['op'] == 'save_guest_counts' && is_numeric($_POST['store_id']) && !empty($_POST['menu_anchor_date']))
		{
			$numRegGuests = CGPC::do_clean((!empty($_POST['numRegGuests']) ? $_POST['numRegGuests'] : 0), TYPE_NUM);
			$numTasteGuests = CGPC::do_clean((!empty($_POST['numTasteGuests']) ? $_POST['numTasteGuests'] : 0), TYPE_NUM);
			$numIntroGuests = CGPC::do_clean((!empty($_POST['numIntroGuests']) ? $_POST['numIntroGuests'] : 0), TYPE_NUM);
			$store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : 0), TYPE_INT);
			$menu_date = CGPC::do_clean((!empty($_POST['menu_anchor_date']) ? $_POST['menu_anchor_date'] : 0), TYPE_STR);

			$goalsObj = DAO_CFactory::create('store_monthly_goals');
			$goalsObj->store_id = $store_id;
			$goalsObj->date = $menu_date;
			if ($goalsObj->find(true))
			{
				$orgObj = clone($goalsObj);
				$goalsObj->regular_guest_count_goal_for_inventory = $numRegGuests;
				$goalsObj->taste_guest_count_goal_for_inventory = $numTasteGuests;
				$goalsObj->intro_guest_count_goal_for_inventory = $numIntroGuests;
				$goalsObj->update($orgObj);
			}
			else
			{
				$goalsObj->regular_guest_count_goal_for_inventory = $numRegGuests;
				$goalsObj->taste_guest_count_goal_for_inventory = $numTasteGuests;
				$goalsObj->intro_guest_count_goal_for_inventory = $numIntroGuests;
				$goalsObj->intro_guest_count_goal = 0;
				$goalsObj->taste_guest_count_goal = 0;
				$goalsObj->regular_guest_count_goal = 0;

				$goalsObj->insert();
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Guest count inventory goals were saved.',
			));
			exit;
		}
		else if ($_POST['op'] == 'save_store_sales_mix' && is_numeric($_POST['store_id']) && is_numeric($_POST['menu_id']))
		{
			$store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : 0), TYPE_INT);
			$menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : 0), TYPE_INT);

			foreach ($_POST['storeSalesMix'] as $recipe_id => $mix)
			{
				$invObj = DAO_CFactory::create('menu_item_inventory');
				$invObj->store_id = $store_id;
				$invObj->menu_id = $menu_id;
				$invObj->recipe_id = $recipe_id;

				if ($invObj->find(true))
				{
					$invObj->sales_mix = $mix / 100;
					$invObj->update();
				}
				else
				{
					$invObj->sales_mix = $mix / 100;
					$invObj->insert();
				}
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Store Sales mix values were saved.',
			));
			exit;
		}
		else if ($_POST['op'] == 'finalize_week' && is_numeric($_POST['store_id']) && is_numeric($_POST['menu_id']))
		{
			$store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : 0), TYPE_INT);
			$menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : 0), TYPE_INT);
			$thisWeek = CGPC::do_clean((!empty($_POST['week_number']) ? $_POST['week_number'] : 0), TYPE_INT);

			if (!is_numeric($thisWeek) || ($thisWeek < 1 && $thisWeek > 5))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Invalid Week Number'
				));
			}

			$fieldName = "week" . $thisWeek . "_projection";
			foreach ($_POST['overrideValues'] as $recipe_id => $inv)
			{
				$invObj = DAO_CFactory::create('menu_item_inventory');
				$invObj->store_id = $store_id;
				$invObj->menu_id = $menu_id;
				$invObj->recipe_id = $recipe_id;

				if (!is_numeric($inv))
				{
					$inv = 0;
				}

				if ($invObj->find(true))
				{
					$old = clone($invObj);
					$invObj->$fieldName = $inv;
					$invObj->update($old);
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Uanable to find inventory.  The Menu PreOrder may not have been finalized.',
					));
					exit;
				}
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Override Inventory values were saved.',
			));
			exit;
		}
		else if ($_POST['op'] == 'save_override_inventory' && is_numeric($_POST['store_id']) && is_numeric($_POST['menu_id']))
		{
			$store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : 0), TYPE_INT);
			$menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : 0), TYPE_INT);

			if (!empty($_POST['save_to_global']) && defined('USE_GLOBAL_SIDES_MENU') && USE_GLOBAL_SIDES_MENU)
			{
				foreach ($_POST['overrideValues'] as $recipe_id => $inv)
				{
					$inventory = DAO_CFactory::create('menu_item_inventory');
					$inventory->query("select mii.override_inventory, mii.number_sold, mii.initial_inventory from menu_item_inventory mii
							where mii.recipe_id = $recipe_id and isnull(mii.menu_id) and mii.store_id = $store_id and mii.is_deleted = 0");
					if ($inventory->fetch())
					{

						$invUpdater = DAO_CFactory::create('menu_item_inventory');
						$invUpdater->query("update menu_item_inventory set override_inventory = $inv where store_id = $store_id  and is_deleted = 0 and recipe_id = $recipe_id and menu_id is null");
						if ($inv != $inventory->override_inventory)
						{
							CMenuItemInventoryHistory::RecordEvent($store_id, $recipe_id, null, CMenuItemInventoryHistory::MENU_EDIT, $inv - $inventory->override_inventory);
						}
					}
				}
			}
			else
			{
				foreach ($_POST['overrideValues'] as $recipe_id => $inv)
				{
					$invObj = DAO_CFactory::create('menu_item_inventory');
					$invObj->store_id = $store_id;
					$invObj->menu_id = $menu_id;
					$invObj->recipe_id = $recipe_id;

					if (!is_numeric($inv))
					{
						$inv = 0;
					}

					if ($invObj->find(true))
					{
						$old = clone($invObj);
						$invObj->override_inventory = $inv;
						$invObj->update($old);
					}
				}
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Override Inventory values were saved.',
			));
			exit;
		}
		else if ($_POST['op'] == 'add_menu_item' && is_numeric($_POST['store_id']))
		{
			$req_entree_ids = CGPC::do_clean((!empty($_POST['entree_ids']) ? $_POST['entree_ids'] : false), TYPE_ARRAY);
			$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
			$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT);

			$retVal = array();

			if (!empty($req_entree_ids))
			{

				foreach ($req_entree_ids as $entree_id)
				{
					$result = CMenuItem::duplicateItemForMenu($entree_id, $req_menu_id, $req_store_id);

					// do this store's distribution centers
					$storeObj = DAO_CFactory::create('store');
					$storeObj->parent_store_id = $req_store_id;

					if ($storeObj->find(true))
					{
						while ($storeObj->fetch())
						{
							CMenuItem::duplicateItemForMenu($entree_id, $req_menu_id, $storeObj->id);
						}
					}

					$thisCount = count($result);

					$retVal[$entree_id]['count'] = $thisCount;
					$retVal[$entree_id]['items'] = $result;
				}

				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $req_store_id;
				$storeObj->find(true);

				$menuInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $req_menu_id, 'FeaturedFirst', true, true, false, false);

				// count "sub-entrees" for each item so the display loop can group correctly
				$qtyMap2 = array();
				$menuInfo['mid'] = array();

				foreach ($menuInfo as $categoryName => $subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $item => $menu_item2)
						{
							if (is_numeric($item))
							{
								if ($menu_item2['pricing_type'] != 'INTRO')
								{
									if (!isset($qtyMap2[$menu_item2['entree_id']]))
									{
										$qtyMap2[$menu_item2['entree_id']] = array('count' => 0);
									}
									$qtyMap2[$menu_item2['entree_id']]['count'] += 1;
								}
							}

							$menuInfo['mid'][$menu_item2['id']] = $menu_item2;
						}
					}
				}

				foreach ($menuInfo as $categoryName => &$subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $item => &$menu_item2)
						{
							if (is_numeric($item))
							{
								if ($menu_item2['pricing_type'] != 'INTRO')
								{
									$menu_item2['sub_entree_count'] = $qtyMap2[$menu_item2['entree_id']]['count'];
								}
							}
						}
					}
				}

				$ctsArray = CMenu::buildCTSArray($storeObj, $req_menu_id);

				foreach ($ctsArray AS $menuItem)
				{
					$menuInfo['mid'][$menuItem['id']] = $menuItem;
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Menu items added.',
					'results' => $retVal,
					'menuInfo' => $menuInfo
				));
				exit;
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'There was a problem with the entree ids.'
				));
				exit;
			}
		}
		else if ($_POST['op'] == 'add_menu_side' && is_numeric($_POST['store_id']))
		{
			$req_ids = CGPC::do_clean((!empty($_POST['entree_ids']) ? $_POST['entree_ids'] : false), TYPE_ARRAY);
			$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
			$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT);

			$retVal = array();

			if (!empty($req_ids))
			{

				foreach ($req_ids as $entree_id => $selectedCategory)
				{
					$result = CMenuItem::duplicateItemForMenu($entree_id, $req_menu_id, $req_store_id,$selectedCategory);

					// do this store's distribution centers
					$storeObj = DAO_CFactory::create('store');
					$storeObj->parent_store_id = $req_store_id;

					if ($storeObj->find(true))
					{
						while ($storeObj->fetch())
						{
							CMenuItem::duplicateItemForMenu($entree_id, $req_menu_id, $storeObj->id,$selectedCategory);
						}
					}

					$thisCount = count($result);

					$retVal[$entree_id]['count'] = $thisCount;
					$retVal[$entree_id]['items'] = $result;
				}

				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $req_store_id;
				$storeObj->find(true);

				$menuInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $req_menu_id, 'FeaturedFirst', false, false, false, false);

				// count "sub-entrees" for each item so the display loop can group correctly
				$qtyMap2 = array();
				$menuInfo['mid'] = array();

				foreach ($menuInfo as $categoryName => $subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $item => $menu_item2)
						{
							if (is_numeric($item))
							{
								if ($menu_item2['pricing_type'] != 'INTRO')
								{
									if (!isset($qtyMap2[$menu_item2['entree_id']]))
									{
										$qtyMap2[$menu_item2['entree_id']] = array('count' => 0);
									}
									$qtyMap2[$menu_item2['entree_id']]['count'] += 1;
								}
							}

							$menuInfo['mid'][$menu_item2['id']] = $menu_item2;
						}
					}
				}

				foreach ($menuInfo as $categoryName => &$subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $item => &$menu_item2)
						{
							if (is_numeric($item))
							{
								if ($menu_item2['pricing_type'] != 'INTRO')
								{
									$menu_item2['sub_entree_count'] = $qtyMap2[$menu_item2['entree_id']]['count'];
								}
							}
						}
					}
				}

				$ctsArray = CMenu::buildCTSArray($storeObj, $req_menu_id);

				foreach ($ctsArray AS $menuItem)
				{
					$menuInfo['mid'][$menuItem['id']] = $menuItem;
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Side & Sweets added to the menu.',
					'results' => $retVal,
					'menuInfo' => $menuInfo
				));
				exit;
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'There was a problem with the sides & sweets ids.'
				));
				exit;
			}
		}
		else if ($_POST['op'] == 'menu_item_info' && is_numeric($_POST['store_id']))
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

				$data = $tpl->fetch('admin/menu_editor_item_info.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Menu item info retrieved.',
					'data' => $data
				));
				exit;
			}
		}
	}
}

?>