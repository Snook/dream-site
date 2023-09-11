<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/OrdersHelper.php");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/CPricing.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');

class page_admin_menu_editor extends CPageAdminOnly
{
	private $needStoreSelector = false;
	private $limitToInventoryControl = false;
	private $storeSupportsPlatePoints = false;
	private $canOverrideMenu = false;

	function runSiteAdmin()
	{
		$this->needStoreSelector = true;
		$this->canOverrideMenu = true;
		$this->runMenuEditor();
	}

	function runHomeOfficeManager()
	{
		$this->needStoreSelector = true;
		$this->runMenuEditor();
	}

	function runFranchiseLead()
	{
		$this->limitToInventoryControl = true;
		$this->runMenuEditor();
	}

	function runOpsLead()
	{
		$this->runMenuEditor();
	}

	function runFranchiseManager()
	{
		$this->runMenuEditor();
	}

	function runFranchiseOwner()
	{
		$this->runMenuEditor();
	}

	function runMenuEditor()
	{
		$tpl = CApp::instance()->template();
		$tpl->assign('limitToInventoryControl', $this->limitToInventoryControl);
		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		// ------------------------------ figure out active store and create store widget if necessary
		$store_id = false;
		if ($this->needStoreSelector)
		{
			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => false,
				CForm::onChange => 'storeChange',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));
			$store_id = $Form->value('store');
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}
		if (!$store_id)
		{
			throw new Exception('no store id found');
		}

		CLog::Record("MENU_EDITOR: Page accessed for store: " . $store_id);
		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->id = $store_id;
		if (!$DAO_store->find_DAO_store(true))
		{
			throw new Exception('Store not found in Menu Editor');
		}

		$tpl->assign("DAO_store", $DAO_store);

		ini_set('memory_limit', '128M');
		// ------------------------------------------ Build menu array and wdiget
		$menus = CMenu::getLastXMenus(4);
		$lastActiveMenuId = null;
		$menuOptions = array();
		$lowestMenuID = 1000000;

		foreach ($menus as $thisMenu)
		{
			if ($thisMenu->id < $lowestMenuID)
			{
				$lowestMenuID = $thisMenu->id;
			}

			$menuOptions[$thisMenu->id] = $thisMenu->menu_name;
			$lastActiveMenuId = $thisMenu->id;
		}

		$lastActiveMenuId++;
		$MenuTest = DAO_CFactory::create('menu');
		$MenuTest->query("select menu.id, menu.menu_name from menu join menu_to_menu_item on menu.id = menu_to_menu_item.menu_id where menu.id = $lastActiveMenuId AND menu.is_deleted = 0 limit 1");
		if ($MenuTest->fetch())
		{
			$menuOptions[$MenuTest->id] = $MenuTest->menu_name;
		}
		// look 1 more menu ahead
		$lastActiveMenuId++;
		$MenuTest2 = DAO_CFactory::create('menu');
		$MenuTest2->query("select menu.id, menu.menu_name from menu join menu_to_menu_item on menu.id = menu_to_menu_item.menu_id where menu.id = $lastActiveMenuId AND menu.is_deleted = 0 limit 1");
		if ($MenuTest2->fetch())
		{
			$menuOptions[$MenuTest2->id] = $MenuTest2->menu_name;
		}

		if (!empty($_GET['menus']) && is_numeric($_GET['menus']))
		{
			$currentMenu = $_GET['menus'];
		}
		else if (!empty($_GET['rd_menu']))
		{
			$currentMenu = CGPC::do_clean($_GET['rd_menu'], TYPE_INT);
		}
		else
		{
			$currentMenu = CBrowserSession::instance()->getValue('menu_editor_current_menu');
		}

		$startDateOfActiveMenuTS = $menus[$lowestMenuID]->global_menu_start_date;
		$seventhDayTS = strtotime($menus[$lowestMenuID]->menu_start) + (86400 * 8);

		if (time() >= $startDateOfActiveMenuTS && time() < $seventhDayTS)
		{
			$lowestMenuID--;

			$MenuTest3 = DAO_CFactory::create('menu');
			$MenuTest3->query("select menu.id, menu.menu_name from menu join menu_to_menu_item on menu.id = menu_to_menu_item.menu_id where menu.id = $lowestMenuID AND menu.is_deleted = 0 limit 1");
			if ($MenuTest3->fetch())
			{
				$menuOptions[$MenuTest3->id] = $MenuTest3->menu_name;
				ksort($menuOptions);
			}
		}

		if (array_key_exists($currentMenu, $menuOptions))
		{
			$Form->DefaultValues['menus'] = $currentMenu;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::onChange => 'menuChange',
			CForm::options => $menuOptions,
			CForm::name => 'menus'
		));
		CBrowserSession::instance()->setValue('menu_editor_current_menu', $Form->value('menus'));
		$menu_id = $Form->value('menus');

		if ($this->canOverrideMenu && isset($_GET['om']))
		{
			$menu_id = CGPC::do_clean($_GET['om'], TYPE_INT);
		}

		// continue setting store info
		$this->storeSupportsPlatePoints = CStore::storeSupportsPlatePoints($DAO_store);
		$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);
		$tpl->assign('storeInfo', $DAO_store);
		if (isset($_POST['action']) && ($_POST['action'] === "storeChange"))
		{
			// clear the POST paramaters so database values will not be overridden by POST params
			unset($_POST);
		}
		if (isset($_POST['action']) && ($_POST['action'] === "menuChange"))
		{
			// clear the POST paramaters so database values will not be overridden by POST params
			unset($_POST);
		}
		$DAO_store->clearMarkupMultiObj();
		// ----------------------------- build menu array from current state
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu->find(true);

		$menuInfo = COrders::buildNewPricingMenuPlanArrays($DAO_store, $DAO_menu->id, 'FeaturedFirst', true, true, false, false);

		// count "sub-entrees" for each item so the display loop can group correctly
		$qtyMap = array();
		$menuInfo['mid'] = array();

		foreach ($menuInfo as $categoryName => $subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $item => $menu_item)
				{
					if (is_numeric($item))
					{
						if ($menu_item['pricing_type'] != 'INTRO')
						{
							if (!isset($qtyMap[$menu_item['entree_id']]))
							{
								$qtyMap[$menu_item['entree_id']] = array('count' => 0);
							}

							$qtyMap[$menu_item['entree_id']]['count'] += 1;
						}
					}

					$menuInfo['mid'][$menu_item['id']] = $menu_item;
				}
			}
		}

		foreach ($menuInfo as $categoryName => &$subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $item => &$menu_item)
				{
					if (is_numeric($item))
					{
						if ($menu_item['pricing_type'] != 'INTRO')
						{
							$menu_item['sub_entree_count'] = $qtyMap[$menu_item['entree_id']]['count'];
						}
					}
				}
			}
		}

		if (!CMenu::storeSpecificMenuExists($menu_id, $store_id))
		{
			// Store Specials are a sub category of FastLane
			// if the store has no store-specific menu then we must override the visibility flag as the are hidden by default
			if (isset($menuInfo['Fast Lane']))
			{
				foreach ($menuInfo['Fast Lane'] as $id => &$itemRef)
				{
					if ($itemRef['is_store_special'])
					{
						$itemRef['is_visible'] = false;
					}
				}
			}
		}

		$DAO_store->clearMarkupMultiObj();
		$markup = $DAO_store->getMarkUpMultiObj($menu_id);

		if ($markup->menu_id_start == $menu_id)
		{
			$tpl->assign('default_settings_description', "");
		}
		else
		{
			$tempMenu = DAO_CFactory::create('menu');
			$tempMenu->id = $markup->menu_id_start;
			$tempMenu->find(true);
			$tpl->assign('default_settings_description', 'Default ' . $tempMenu->menu_name . " Settings in use.");
		}

		$DAO_order_minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $store_id, $menu_id);
		$tpl->assign('DAO_order_minimum', $DAO_order_minimum);

		$Form->DefaultValues['is_default_markup'] = "no";
		$Form->DefaultValues['delivery_assembly_fee'] = 0;
		$Form->DefaultValues['assembly_fee'] = 25;

		if ($markup)
		{
			$Form->DefaultValues['markup_2_serving'] = $markup->markup_value_2_serving;
			$Form->DefaultValues['markup_3_serving'] = $markup->markup_value_3_serving;
			$Form->DefaultValues['markup_4_serving'] = $markup->markup_value_4_serving;
			$Form->DefaultValues['markup_6_serving'] = $markup->markup_value_6_serving;
			$Form->DefaultValues['markup_sides'] = $markup->markup_value_sides;
			$Form->DefaultValues['is_default_markup'] = ($markup->is_default && $markup->menu_id_start == $menu_id) ? "yes" : "no";
			$Form->DefaultValues['delivery_assembly_fee'] = $markup->delivery_assembly_fee;
			$Form->DefaultValues['assembly_fee'] = $markup->assembly_fee;
		}

		$tpl->assign('allow_assembly_fee', OrdersHelper::allow_assembly_fee($menu_id));
		if (!OrdersHelper::allow_assembly_fee($menu_id))
		{
			$Form->DefaultValues['delivery_assembly_fee'] = 0;
			$Form->DefaultValues['assembly_fee'] = 0;
			if ($markup)
			{
				$markup->delivery_assembly_fee = 0;
				$markup->assembly_fee = 0;
			}
		}

		$defaultVolumeReward = 30;
		if ($markup)
		{
			$vd = DAO_CFactory::create('volume_discount_type');
			$vd->query("select
				vd.discount_value
				from volume_discount_type vd, mark_up_multi_to_volume_discount mmtvd
				where mmtvd.mark_up_multi_id = " . $markup->id . "
				and mmtvd.volume_discount_id = vd.id");
			if ($vd->N > 0)
			{
				$vd->fetch();
				$defaultVolumeReward = $vd->discount_value;
			}
		}

		if ($menu_id > 186)
		{
			$tpl->assign('canAddEFLItems', true);
		}
		else
		{
			$tpl->assign('canAddEFLItems', false);
		}

		$Form->DefaultValues['volume_reward'] = $defaultVolumeReward;
		// ------------------------------ handle submission
		if (isset($_POST['action']) && $_POST['action'] === "finalize")
		{
			if ($menu_id != $_POST['loaded_menu_id'])
			{
				$tpl->setErrorMsg('You attempted to finalize a menu that did not load properly. The page has now been reloaded. Please check the menu selector and if it is not correct select the desired menu and be certain is has completely loaded before making and finalizing any edits.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			CLog::Record("MENU_EDITOR: Page finalized for store: " . $store_id);
			CLog::RecordDebugTrace("Menu Finalized\r\n" . print_r($_POST, true), "MENU_EDITOR");

			// ---------------------- handle markup
			// if there is a markup directly associated with this menu then check to see if the new values exactly match
			// if so there is no need to create a markup. In all other cases do create a new one. The other cases are:
			// 1) There is a direct assoc. markup but the values have changed
			// 2) There is no dir ass but there is a default .... The default values initialized the widgets. They may have been adjusted .. in either
			//	case create a new one. Orders may have been placed that refer to the default. That is ok.
			// 3) There was neither a dir ass or a default .. there was no markup. So, save the new one.

			if (!$DAO_menu->isEnabled_Markup())
			{
				$_POST['markup_2_serving'] = 0;
				$_POST['markup_3_serving'] = 0;
				$_POST['markup_4_serving'] = 0;
				$_POST['markup_6_serving'] = 0;
			}
			else
			{
				if (trim($_POST['markup_6_serving']) == "")
				{
					$_POST['markup_6_serving'] = 0;
				}

				if (trim($_POST['markup_4_serving']) == "")
				{
					$_POST['markup_4_serving'] = 0;
				}

				if (trim($_POST['markup_3_serving']) == "")
				{
					$_POST['markup_3_serving'] = 0;
				}

				if (trim($_POST['markup_2_serving']) == "")
				{
					$_POST['markup_2_serving'] = 0;
				}
			}

			if (trim($_POST['markup_sides']) == "")
			{
				$_POST['markup_sides'] = 0;
			}

			if (!is_numeric($_POST['markup_6_serving']) || !is_numeric($_POST['markup_4_serving']) || !is_numeric($_POST['markup_3_serving']) || !is_numeric($_POST['markup_2_serving']) || !is_numeric($_POST['markup_sides']))
			{
				$tpl->setErrorMsg('Non-numeric markup values are illegal.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			if ($_POST['markup_6_serving'] < 0 || $_POST['markup_4_serving'] < 0 || $_POST['markup_3_serving'] < 0 || $_POST['markup_2_serving'] < 0 || $_POST['markup_sides'] < 0)
			{
				$tpl->setErrorMsg('Negative per cent markups are not currently permitted.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			$_POST['volume_reward'] = 30;
			// Note: the volume reward is no longer applied. For now just set it to the old default to keep everythign happy.
			// but someday this can be completely removed. schema must be left alone to support legacy orders.
			if ($_POST['volume_reward'] < 0)
			{
				$tpl->setErrorMsg('Negative volume discounts are not permitted.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			if ($_POST['volume_reward'] === "")
			{
				$tpl->setErrorMsg('Please supply a valid volume discount. Use zero if you want no volume discount applied.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			if ($_POST['volume_reward'] > 75)
			{
				$tpl->setErrorMsg('Volume Discounts greater than $75.00 are not currently permitted.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);

			if ($_POST['assembly_fee'] < 0 && OrdersHelper::allow_assembly_fee($menu_id))
			{
				$tpl->setErrorMsg('The service fee must be a minimum of $0.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			if ($_POST['assembly_fee'] > 60 && OrdersHelper::allow_assembly_fee($menu_id))
			{
				$tpl->setErrorMsg('The service fee must be a maximum of $60.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			if ($_POST['delivery_assembly_fee'] < 0 && OrdersHelper::allow_assembly_fee($menu_id))
			{
				$tpl->setErrorMsg('The delivery service fee must be a minimum of $0.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			if ($_POST['delivery_assembly_fee'] > 60 && OrdersHelper::allow_assembly_fee($menu_id))
			{
				$tpl->setErrorMsg('The delivery service fee must be a maximum of $60.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}

			if ($_POST['markup_6_serving'] > 70 || $_POST['markup_4_serving'] > 70 || $_POST['markup_3_serving'] > 70 || $_POST['markup_2_serving'] > 70 || $_POST['markup_sides'] > 70)
			{
				$tpl->setErrorMsg('Markups greater than 70% are not currently permitted.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			if (!$this->storeSupportsPlatePoints)
			{
				// -------------------------------------------------------------------------------update markup,etc for store not in PLATEPOINTS
				$shouldSaveNewMarkup = false;
				if (!$this->limitToInventoryControl)
				{
					$posted_default_value = $_POST['is_default_markup'] == "yes" ? 1 : 0;
				}
				else
				{
					$posted_default_value = false;
				}
				if ($markup)
				{
					if ($markup->markup_value_6_serving != $_POST['markup_6_serving'] || $markup->markup_value_4_serving != $_POST['markup_4_serving'] || $markup->markup_value_3_serving != $_POST['markup_3_serving'] || $markup->markup_value_2_serving != $_POST['markup_2_serving'] || $markup->markup_value_sides != $_POST['markup_sides'] || $markup->is_default != $posted_default_value || $markup->assembly_fee != $_POST['assembly_fee'] || $markup->delivery_assembly_fee != $_POST['delivery_assembly_fee'] || $markup->menu_id_start != $menu_id)
					{
						$shouldSaveNewMarkup = true;
					}
					else
					{ // both markup values are the same but we should still check the volume reward
						if ($defaultVolumeReward != $_POST['volume_reward'])
						{
							$shouldSaveNewMarkup = true;
						}
					}
					// TODO: ALSO if the default flag changed we should update just the column, if nothing else changed
				}
				else
				{
					$shouldSaveNewMarkup = true;
				}
				if ($shouldSaveNewMarkup && !$this->limitToInventoryControl)
				{
					if ($posted_default_value)
					{
						// make sure older default markups are set to non-default
						$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
						$oldDefaultMarkups->query("update mark_up_multi set is_default = 0 where store_id = $store_id and is_default = 1");
					}
					// delete any older markups for this menu/store
					$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
					$oldDefaultMarkups->query("update mark_up_multi set is_deleted = 1 where store_id = $store_id and menu_id_start = $menu_id and is_deleted = 0");
					$DAO_store->setMarkUpMulti($_POST['markup_6_serving'], $_POST['markup_4_serving'], $_POST['markup_3_serving'], $_POST['markup_2_serving'], $_POST['markup_sides'], $_POST['volume_reward'], $menu_id, $posted_default_value, 12.5, $_POST['assembly_fee'], $_POST['delivery_assembly_fee']);
					$defaultVolumeReward = $_POST['volume_reward']; // used below
				}
			}
			else
			{
				// -------------------------------------------------------------------------------update markup,etc for store IN PLATEPOINTS
				$shouldSaveNewMarkup = false;
				if (!$this->limitToInventoryControl)
				{
					$posted_default_value = $_POST['is_default_markup'] == "yes" ? 1 : 0;
				}
				else
				{
					$posted_default_value = false;
				}
				if ($markup)
				{
					if ($markup->markup_value_6_serving != $_POST['markup_6_serving'] || $markup->markup_value_sides != $_POST['markup_sides'] || $markup->markup_value_4_serving != $_POST['markup_4_serving'] || $markup->markup_value_3_serving != $_POST['markup_3_serving'] || $markup->markup_value_2_serving != $_POST['markup_2_serving'] || $markup->is_default != $posted_default_value || $markup->assembly_fee != $_POST['assembly_fee'] || $markup->delivery_assembly_fee != $_POST['delivery_assembly_fee'] || $markup->menu_id_start != $menu_id)
					{
						$shouldSaveNewMarkup = true;
					}
				}
				else
				{
					$shouldSaveNewMarkup = true;
				}
				if ($shouldSaveNewMarkup && !$this->limitToInventoryControl)
				{
					if ($posted_default_value)
					{
						// make sure older default markups are set to non-default
						$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
						$oldDefaultMarkups->query("update mark_up_multi set is_default = 0 where store_id = $store_id and is_default = 1");
					}
					// delete any older markups for this menu/store
					$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
					$oldDefaultMarkups->query("update mark_up_multi set is_deleted = 1 where store_id = $store_id and menu_id_start = $menu_id and is_deleted = 0");
					$DAO_store->setMarkUpMulti($_POST['markup_6_serving'], $_POST['markup_4_serving'], $_POST['markup_3_serving'], $_POST['markup_2_serving'], $_POST['markup_sides'], $_POST['volume_reward'], $menu_id, $posted_default_value, 12.5, $_POST['assembly_fee'], $_POST['delivery_assembly_fee']);
				}
			}
			// Test for existing store menu
			$storeMenuExists = CMenu::storeSpecificMenuExists($menu_id, $store_id);
			if ($storeMenuExists)
			{
				// first update all non optional items
				foreach ($menuInfo as $catName => $subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $id => $item)
						{
							if ($item['is_optional'])
							{
								continue;
							}
							$mmi = DAO_CFactory::create('menu_to_menu_item');
							$mmi->menu_item_id = $item['id'];
							$mmi->menu_id = $menu_id;
							$mmi->store_id = $store_id;
							if ($mmi->find(true))
							{
								$oldItem = clone($mmi);
								//override_price
								$priceName = "ovr_" . $item['id'];
								if ($item['is_price_controllable'] && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
								{
									// TODO : validate price
									$mmi->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
								}
								else
								{
									if ($item['pricing_type'] == CMenuItem::INTRO)
									{
										$mmi->override_price = 12.5;
									}
									else
									{
										$mmi->override_price = 'null';
									}
								}
								$pickSheetVisName = "pic_" . $item['id'];
								if (isset($_POST[$pickSheetVisName]) && !empty($_POST[$pickSheetVisName]))
								{
									$mmi->show_on_pick_sheet = 1;
								}
								else
								{
									$mmi->show_on_pick_sheet = 0;
								}
								//visibility
								$visName = "vis_" . $item['id'];
								if (($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName])) || $item['sell_in_store_only'])
								{
									$mmi->is_visible = 0;
								}
								else
								{
									$mmi->is_visible = 1;
								}
								$mmi->update($oldItem);
							}

							if (isset($_POST['upd_mkdn_' . $item['id']]))
							{

								$tArr = explode("|", CGPC::do_clean($_POST['upd_mkdn_' . $item['id']], TYPE_STR));
								$markdownID = $tArr[0];
								$markdownValue = $tArr[1];

								if ($item['markdown_id'])
								{
									// A markdown already exists for this item so update if different
									if ($markdownID == $item['markdown_id'] && $item['markdown_value'] != $markdownValue)
									{
										$markDownObj = DAO_CFactory::create('menu_item_mark_down');
										$markDownObj->query("update menu_item_mark_down set is_deleted = 1 where menu_item_id = {$item['id']} and store_id = $store_id");

										if ($markdownValue != 0)
										{

											$newMDObj = DAO_CFactory::create('menu_item_mark_down');
											$newMDObj->menu_item_id = $item['id'];
											$newMDObj->store_id = $store_id;
											$newMDObj->markdown_value = $markdownValue;
											$newMDObj->insert();
										}
									}
									else
									{
										// here the id's match AND the values match so there is nothing to do
									}
								}
								else
								{
									CLog::Assert($markdownID = "new", "Mark down id should ne 'new'.");

									if ($markdownValue != 0)
									{
										$newMDObj = DAO_CFactory::create('menu_item_mark_down');
										$newMDObj->menu_item_id = $item['id'];
										$newMDObj->store_id = $store_id;
										$newMDObj->markdown_value = $markdownValue;
										$newMDObj->insert();
									}
								}
							}
							else
							{

								// Note: the current UI logic does not remove the markdown but instead sets it to zero.
								// So the above code that handles updates will delete the current markdown and not insert a new one.

							}
						}
					}
				}
				$menuAddonArray = CMenu::buildMenuAddonArray($DAO_store, $menu_id);
				// Menu Addons
				foreach ($menuAddonArray as $id => $item)
				{
					if ($item['is_optional'])
					{
						continue;
					}
					$mmi = false;
					$priceName = "ovr_" . $item['id'];
					if ($item['is_price_controllable'] && isset($_POST[$priceName]))
					{
						if ($_POST[$priceName] == "")
						{
							$_POST[$priceName] = 'null';
						}
						$mmi = DAO_CFactory::create('menu_to_menu_item');
						$mmi->menu_item_id = $item['id'];
						$mmi->menu_id = $menu_id;
						$mmi->store_id = $store_id;
						if ($mmi->find(true))
						{
							$oldItem = clone($mmi);
							$mmi->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
							//visibility
							$visName = "vis_" . $item['id'];
							if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
							{
								$mmi->is_visible = 0;
							}
							else
							{
								$mmi->is_visible = 1;
							}
							$mmi->update($oldItem);
						}
					}
				}
				$ctsArray = CMenu::buildCTSArray($DAO_store, $menu_id);
				// Menu Addons
				foreach ($ctsArray as $id => $item)
				{
					if ($item['is_optional'])
					{
						continue;
					}
					$mmi = false;
					$priceName = "ovr_" . $item['id'];
					if ($item['is_price_controllable'] && isset($_POST[$priceName]))
					{
						$mmi = DAO_CFactory::create('menu_to_menu_item');
						$mmi->menu_item_id = $item['id'];
						$mmi->menu_id = $menu_id;
						$mmi->store_id = $store_id;
						if ($mmi->find(true))
						{
							$oldItem = clone($mmi);
							$mmi->override_price = ((CGPC::do_clean($_POST[$priceName], TYPE_NUM) == '') ? 'null' : CGPC::do_clean($_POST[$priceName], TYPE_NUM));
							//visibility
							$visName = "vis_" . $item['id'];
							if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
							{
								$mmi->is_visible = 1;
							}
							else
							{
								$mmi->is_visible = 0;
							}

							$formName = "form_" . $item['id'];
							if ($item['is_visibility_controllable'] && isset($_POST[$formName]) && !empty($_POST[$formName]))
							{
								$mmi->show_on_order_form = 1;
							}
							else
							{
								$mmi->show_on_order_form = 0;
							}
							//visibility
							$visName = "hid_" . $item['id'];
							if (isset($_POST[$visName]) && !empty($_POST[$visName]))
							{
								$mmi->is_hidden_everywhere = 1;
							}
							else
							{
								$mmi->is_hidden_everywhere = 0;
							}
							$mmi->update($oldItem);
						}
					}
				}
			}
			else// If it doesn't exist then create it with non optional items
			{
				$pos_counter = 0;
				foreach ($menuInfo as $catName => $subArray)
				{
					if (!is_array($subArray))
					{
						continue;
					}
					foreach ($subArray as $id => $item)
					{
						if ($item['is_optional'])
						{
							continue;
						}
						$mmi = DAO_CFactory::create('menu_to_menu_item');
						$mmi->menu_item_id = $item['id'];
						$mmi->menu_id = $menu_id;
						$mmi->store_id = $store_id;
						$mmi->menu_order_value = ++$pos_counter;
						$mmi->featuredItem = 0;
						//override_price
						$priceName = "ovr_" . $item['id'];
						if ($item['is_price_controllable'] && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
						{
							// TODO : validate price
							$mmi->override_price = ((CGPC::do_clean($_POST[$priceName], TYPE_NUM) == '') ? 'null' : CGPC::do_clean($_POST[$priceName], TYPE_NUM));
						}
						else
						{
							if ($item['pricing_type'] == CMenuItem::INTRO)
							{
								$mmi->override_price = 12.5;
							}
							else
							{
								$mmi->override_price = 'null';
							}
						}
						//visibility
						$visName = "vis_" . $item['id'];
						if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
						{
							$mmi->is_visible = 0;
						}
						else
						{
							$mmi->is_visible = 1;
						}
						$mmi->insert();
					}
				}
				// This is tricky, the store menu now exists because of the loop above, so this function will return nothing unless we override the store specific
				// query
				$menuAddonArray = CMenu::buildMenuAddonArray($DAO_store, $menu_id, true);
				foreach ($menuAddonArray as $id => $item)
				{
					if ($item['is_optional'])
					{
						continue;
					}
					$mmi = DAO_CFactory::create('menu_to_menu_item');
					$mmi->menu_item_id = $item['id'];
					$mmi->menu_id = $menu_id;
					$mmi->store_id = $store_id;
					$mmi->menu_order_value = ++$pos_counter;
					$mmi->featuredItem = 0;
					//override_price
					$priceName = "ovr_" . $item['id'];
					if ($item['is_price_controllable'] && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
					{
						// TODO : validate price
						$mmi->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
					}
					else
					{
						$mmi->override_price = 'null';
					}
					//visibility
					$visName = "vis_" . $item['id'];
					if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
					{
						$mmi->is_visible = 0;
					}
					else
					{
						$mmi->is_visible = 1;
					}
					$mmi->insert();
				}
				// This is tricky, the store menu now exists because of the loop above, so this function will return nothing unless we override the store specific
				// query
				$ctsArray = CMenu::buildCTSArray($DAO_store, $menu_id);
				foreach ($ctsArray as $id => $item)
				{
					if ($item['is_optional'])
					{
						continue;
					}
					$mmi = DAO_CFactory::create('menu_to_menu_item');
					$mmi->menu_item_id = $item['id'];
					$mmi->menu_id = $menu_id;
					$mmi->store_id = $store_id;
					$mmi->menu_order_value = ++$pos_counter;
					$mmi->featuredItem = 0;
					//override_price
					$priceName = "ovr_" . $item['id'];
					if ($item['is_price_controllable'] && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
					{
						// TODO : validate price
						$mmi->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
					}
					else
					{
						$mmi->override_price = 'null';
					}
					//visibility
					$visName = "vis_" . $item['id'];
					if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
					{
						$mmi->is_visible = 1;
					}
					else
					{
						$mmi->is_visible = 0;
					}
					//visibility
					$visName = "hid_" . $item['id'];
					if ($item['is_visibility_controllable'] && isset($_POST[$visName]) && !empty($_POST[$visName]))
					{
						$mmi->is_hidden_everywhere = 1;
					}
					else
					{
						$mmi->is_hidden_everywhere = 0;
					}
					$mmi->insert();
				}
			}

			// 2 steps for optional items
			// first loop over POST array and check for new items and updates
			// second loop over current menu and look for deleted items
			$pos_counter = 0;

			foreach ($_POST as $k => $v)
			{
				if (strpos($k, "opt") === 0)
				{
					$itemId = substr($k, 4);
					$mmiTest = DAO_CFactory::create('menu_to_menu_item');
					$mmiTest->query("select mmi.*, mi.is_price_controllable, mi.is_visibility_controllable 
						rom menu_to_menu_item mmi 
						join menu_item mi on mmi.menu_item_id = mi.id
						where mmi.menu_id = $menu_id and store_id = $store_id and mmi.menu_item_id = $itemId and mmi.is_deleted = 0");

					if ($mmiTest->N > 0)
					{ // update
						$mmiTest->fetch();
						$oldItem = clone($mmiTest);
						//override_price
						$priceName = "ovr_" . $itemId;
						if ($mmiTest->is_price_controllable && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
						{
							// TODO : validate price
							$mmiTest->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
						}
						else
						{
							$mmiTest->override_price = 'null';
						}
						//visibility
						$visName = "vis_" . $itemId;
						if ($mmiTest->is_visibility_controllable && isset($_POST[$visName]) && !empty($_POST[$visName]))
						{
							$mmiTest->is_visible = 0;
						}
						else
						{
							$mmiTest->is_visible = 1;
						}
						$mmiTest->update($oldItem);
					}
					else // add
					{
						$mmiTest->menu_order_value = ++$pos_counter;
						$mmiTest->featuredItem = 0;
						$mmiTest->menu_item_id = $itemId;
						$mmiTest->menu_id = $menu_id;
						$mmiTest->store_id = $store_id;
						$mi = DAO_CFactory::create('menu_item');
						$mi->id = $itemId;
						$mi->selectAdd();
						$mi->selectAdd('is_price_controllable, is_visibility_controllable');
						$mi->find(true);
						//override_price
						$priceName = "ovr_" . $itemId;
						if ($mi->is_price_controllable && isset($_POST[$priceName]) && !empty($_POST[$priceName]))
						{
							// TODO : validate price
							$mmiTest->override_price = CGPC::do_clean($_POST[$priceName], TYPE_NUM);
						}
						else
						{
							$mmiTest->override_price = 'null';
						}
						//visibility
						$visName = "vis_" . $itemId;
						if ($mi->is_visibility_controllable && isset($_POST[$visName]) && !empty($_POST[$visName]))
						{
							$mmiTest->is_visible = 0;
						}
						else
						{
							$mmiTest->is_visible = 1;
						}
						$mmiTest->insert();
					}
				}
			}

			// loop over optional items in current menu and delete any no longer in POST
			foreach ($menuInfo as $catName => $subArray)
			{
				if (!is_array($subArray))
				{
					continue;
				}
				foreach ($subArray as $id => $item)
				{
					if (!$item['is_optional'])
					{
						continue;
					}
					$optName = "opt_" . $item['id'];
					if (!isset($_POST[$optName]))
					{
						$deleteMMI = DAO_CFactory::create('menu_to_menu_item');
						$deleteMMI->store_id = $store_id;
						$deleteMMI->menu_id = $menu_id;
						$deleteMMI->menu_item_id = $item['id'];
						$deleteMMI->selectAdd();
						$deleteMMI->selectAdd('id');
						$deleteMMI->find(true);
						$deleteMMI->delete();
					}
				}
			}

			//recalculate arrays and markup for display
			$DAO_store->clearMarkupMultiObj();

			unset($menuInfo);

			$menuInfo = COrders::buildNewPricingMenuPlanArrays($DAO_store, $menu_id, 'FeaturedFirst', true, true, false, false);

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

			$markup = $DAO_store->getMarkUpMultiObj($menu_id);
		}

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::min => 0,
			CForm::attribute => array('step' => '0.01'),
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'markup_2_serving'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array('step' => '0.01'),
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'markup_3_serving'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array('step' => '0.01'),
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'markup_4_serving'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array('step' => '0.01'),
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'markup_6_serving'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array('step' => '0.01'),
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'markup_sides'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::size => 2,
			CForm::number => true,
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'volume_reward'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::size => 5,
			CForm::number => true,
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'assembly_fee'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::size => 5,
			CForm::number => true,
			CForm::readonly => $this->limitToInventoryControl,
			CForm::name => 'delivery_assembly_fee'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "is_default_markup",
			CForm::disabled => $this->limitToInventoryControl,
			//	CForm::required => true,
			CForm::value => 'no'
		));
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "is_default_markup",
			CForm::disabled => $this->limitToInventoryControl,
			//CForm::required => true,
			CForm::value => 'yes'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "loaded_menu_id",
			CForm::value => $menu_id
		));

		$currentMarkupArray = array(
			'markup_value_2_serving' => null,
			'markup_value_3_serving' => null,
			'markup_value_4_serving' => null,
			'markup_value_6_serving' => null,
			'markup_value_sides' => null,
			'is_default' => null,
			'volume_reward' => ((!empty($this->storeSupportsPlatePoints)) ? 0 : $defaultVolumeReward),
			'sampler_item_price' => 12.50,
			'assembly_fee' => 20,
			'delivery_assembly_fee' => 0.00
		);

		if ($markup)
		{

			$assembly_fee = $currentMarkupArray = array(
				'markup_value_2_serving' => $markup->markup_value_2_serving,
				'markup_value_3_serving' => $markup->markup_value_3_serving,
				'markup_value_4_serving' => $markup->markup_value_4_serving,
				'markup_value_6_serving' => $markup->markup_value_6_serving,
				'markup_value_sides' => $markup->markup_value_sides,
				'is_default' => $markup->is_default && $markup->menu_id_start == $menu_id,
				'volume_reward' => ((!empty($this->storeSupportsPlatePoints)) ? 0 : $defaultVolumeReward),
				'sampler_item_price' => $markup->sampler_item_price,
				'assembly_fee' => $markup->assembly_fee,
				'delivery_assembly_fee' => $markup->delivery_assembly_fee
			);
		}

		$menuAddonArray = CMenu::buildMenuAddonArray($DAO_store, $menu_id);

		$ctsArray = CMenu::buildCTSArray($DAO_store, $menu_id);

		foreach ($ctsArray as $menuItem)
		{
			$menuInfo['mid'][$menuItem['id']] = $menuItem;
		}

		$tpl->assign('CTSMenu', $ctsArray);

		// build menu addon drop down and add item rows for existing addons all in one loop
		$addonSelectArray = array();
		$addonSelectArray[0] = "Select Item To Edit";
		foreach ($menuAddonArray as $id => $data)
		{
			$addonSelectArray[$id] = $data['display_title'] . " | " . $data['base_price'] . " | " . $data['price'] . " | " . ($data['is_visible'] ? 'Shown' : 'Hidden') . (!empty($data['override_price']) ? " | Override Price : " . $data['override_price'] : "");
		}

		if (count($addonSelectArray) > 1)
		{
			if (isset($_POST['addons']))
			{
				unset($_POST['addons']);
			}
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => false,
				CForm::options => $addonSelectArray,
				CForm::name => 'addons'
			));
		}

		$storeSpecialsItems = array();
		if (!empty($menuInfo['Fast Lane']))
		{
			foreach ($menuInfo['Fast Lane'] as $id => $val)
			{
				$storeSpecialsItems[$id] = $id;
			}
		}

		if (!empty($menuInfo['Extended Fast Lane']))
		{
			foreach ($menuInfo['Extended Fast Lane'] as $id => $val)
			{
				$storeSpecialsItems[$id] = $id;
			}
		}

		$pricingReference = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_is_bundle' => false,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => true,
			'exclude_menu_item_category_sides_sweets' => true,
			'groupBy' => 'RecipeID'
		));

		$pricingReferenceArray = array();
		while ($pricingReference->fetch())
		{
			$pricingReferenceArray[$pricingReference->recipe_id] = $pricingReference->cloneObj();
		}

		$tpl->assign('DAO_menu', $DAO_menu);
		$tpl->assign('pricingReferenceArray', $pricingReferenceArray);
		$tpl->assign('markupData', $currentMarkupArray);
		$tpl->assign('menuInfo', $menuInfo);
		$tpl->assign('menuInfoJS', json_encode($menuInfo));
		$tpl->assign('storeSpecialsItems', json_encode($storeSpecialsItems));

		$tpl->assign('form', $Form->render());
	}

}

?>