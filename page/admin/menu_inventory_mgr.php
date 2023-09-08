<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');
require_once('DAO/BusinessObject/CBundle.php');

class page_admin_menu_inventory_mgr extends CPageAdminOnly
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
		ini_set('memory_limit', '64M');
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$menuPricingInfo = null;

		if (CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER)
		{
			CApp::bounce('/?page=admin_main');
		}

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

		CLog::Record("MENU_INVENTORY_MGR: Page accessed for store: " . $store_id);
		$tpl->assign("store_id", $store_id);
		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $store_id;
		if (!$storeObj->find(true))
		{
			throw new Exception('Store not found in Menu Editor');
		}

		if (isset($_GET['op']) && $_GET['op'] == 'export_month_sales_projection' && is_numeric($_GET['store']) && is_numeric($_GET['menu_id']))
		{
			$DAO_menu_item = DAO_CFactory::create('menu_item');
			$DAO_menu_item->is_store_special = 0;
			$DAO_menu_item->whereAdd("menu_item.menu_item_category_id <> 9");

			$DAO_menu_item->selectAdd();
			$DAO_menu_item->selectAdd("menu_item.id");
			$DAO_menu_item->selectAdd("menu_item.recipe_id");
			$DAO_menu_item->selectAdd("menu_item.menu_item_name");
			$DAO_menu_item->selectAdd("menu_item.entree_id");
			$DAO_menu_item->selectAdd("menu_item_inventory.initial_inventory");
			$DAO_menu_item->selectAdd("GROUP_CONCAT(menu_item.pricing_type order by menu_item.pricing_type DESC) as types");
			$DAO_menu_item->selectAdd("GROUP_CONCAT(menu_item.servings_per_item order by menu_item.pricing_type DESC) as servings_per_item");
			$DAO_menu_item->selectAdd("GROUP_CONCAT(menu_item.price order by menu_item.pricing_type DESC) as prices");

			$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
			$DAO_menu_to_menu_item->menu_id = $_GET['menu_id'];

			// include child distribution center sales
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->parent_store_id = $_GET['store'];

			if ($DAO_store->find())
			{
				$store_id_query = "menu_to_menu_item.store_id = " . $DAO_store->parent_store_id;

				while ($DAO_store->fetch())
				{
					$store_id_query .= " OR menu_to_menu_item.store_id = " . $DAO_store->id;
				}

				$DAO_menu_to_menu_item->whereAdd($store_id_query);
			}
			else
			{
				$DAO_menu_to_menu_item->store_id = $_GET['store'];
			}

			$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
			$DAO_menu_item_inventory->whereAdd("menu_item_inventory.recipe_id = menu_item.recipe_id");
			$DAO_menu_item_inventory->whereAdd("menu_item_inventory.store_id = menu_to_menu_item.store_id");

			$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_menu_item_inventory, 'LEFT');
			$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);

			$DAO_menu_item->groupBy("menu_item.entree_id");
			$DAO_menu_item->orderBy('FeaturedFirst');

			$DAO_menu_item->find();

			$itemArray = array();

			while ($DAO_menu_item->fetch())
			{
				$hasMedium = false;
				$hasLarge = false;

				$types = explode(",", $DAO_menu_item->types);
				$prices = explode(",", $DAO_menu_item->prices);

				if (in_array("FULL", $types))
				{
					$hasLarge = true;
				}
				if (in_array("HALF", $types))
				{
					$hasMedium = true;
				}

				if ($hasMedium && $hasLarge)
				{
					list($med_servings_per_item, $large_servings_per_item) = explode(",", $DAO_menu_item->servings_per_item);

					// do large first se we don't end up with half dinners
					$proj_inv = $DAO_menu_item->initial_inventory;
					$large_val = (int)($proj_inv * 0.3);
					$large_val_in_items = (int)($large_val / $large_servings_per_item);

					if ($large_val / $large_servings_per_item > $large_val_in_items)
					{
						$large_val_in_items++; // roundup
					}

					$large_val_servings = $large_val_in_items * $large_servings_per_item;
					$medium_val_in_servings = $proj_inv - $large_val_servings;
					$medium_val_in_items = (int)($medium_val_in_servings / $med_servings_per_item);

					$key = $DAO_menu_item->recipe_id . "_M";
					$itemArray[$DAO_menu_item->id . "_M"] = array(
						$key,
						$DAO_menu_item->menu_item_name,
						$medium_val_in_items,
						$prices[0]
					);

					$key = $DAO_menu_item->recipe_id . "_L";
					$itemArray[$DAO_menu_item->id . "_L"] = array(
						$key,
						$DAO_menu_item->menu_item_name,
						$large_val_in_items,
						$prices[1]
					);
				}
				else if ($hasMedium)
				{
					$med_servings_per_item = $DAO_menu_item->servings_per_item;
					$key = $DAO_menu_item->recipe_id . "_M";
					if ($med_servings_per_item == 0)
					{
						$medium_val_in_items = 0;
					}
					else
					{
						$medium_val_in_items = (int)($proj_inv / $med_servings_per_item);
					}
					$itemArray[$DAO_menu_item->id . "_M"] = array(
						$key,
						$DAO_menu_item->menu_item_name,
						$medium_val_in_items,
						$prices[0]
					);
				}
				else if ($hasLarge)
				{
					$large_servings_per_item = $DAO_menu_item->servings_per_item;
					$key = $DAO_menu_item->recipe_id . "_L";
					if ($large_servings_per_item == 0)
					{
						$large_val_in_items = 0;
					}
					else
					{
						$large_val_in_items = (int)($proj_inv / $large_servings_per_item);
					}
					$itemArray[$DAO_menu_item->id . "_L"] = array(
						$key,
						$DAO_menu_item->menu_item_name,
						$large_val_in_items,
						'',
						$prices[1]
					);
				}
			}

			//$cvs_string = implode("\r\n", $itemArray);
			$menuInfo = CMenu::getMenuInfo($_GET['menu_id']);

			require_once('CSV.inc');
			$csvObj = new CSV();
			$printable_menu = $menuInfo['menu_name'];
			$fileName = "Projected Sales numbers for " . $storeObj->store_name . " for month of " . $printable_menu;
			$csvObj->writeCSVFile($fileName, array(
				"item",
				"name",
				"count",
				"blank",
				"price"
			), $itemArray, false, false, false);
			exit;
		}

		if (isset($_GET['op']) && $_GET['op'] == 'export_weekly_sales' && is_numeric($_GET['store']) && is_numeric($_GET['menu_id']))
		{
			$weekStart = CGPC::do_clean($_GET['weekStart'], TYPE_STR);

			$DAO_menu_item = DAO_CFactory::create('menu_item');
			$DAO_menu_item->is_store_special = 0;

			$DAO_menu_item->selectAdd();
			$DAO_menu_item->selectAdd("menu_item.id");
			$DAO_menu_item->selectAdd("menu_item.recipe_id");
			$DAO_menu_item->selectAdd("menu_item.pricing_type");
			$DAO_menu_item->selectAdd("menu_item.servings_per_item");
			$DAO_menu_item->selectAdd("menu_item.menu_item_category_id");
			$DAO_menu_item->selectAdd("sum(order_item.item_count) as item_count");
			$DAO_menu_item->selectAdd("max(menu_to_menu_item.override_price) as price");
			$DAO_menu_item->selectAdd("menu_item.price as default_price");

			$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
			$DAO_menu_to_menu_item->whereAdd("menu_to_menu_item.menu_item_id = menu_item.id");

			$DAO_session = DAO_CFactory::create('session');
			$DAO_session->menu_id = $_GET['menu_id'];
			$DAO_session->whereAdd("session.session_start > '" . $weekStart . "'");
			$DAO_session->whereAdd("session.session_start < date_add('$weekStart', INTERVAL 7 DAY)");

			// include child distribution center sales
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->parent_store_id = $_GET['store'];

			if ($DAO_store->find())
			{
				$store_id_query = "session.store_id = " . $DAO_store->parent_store_id;

				while ($DAO_store->fetch())
				{
					$store_id_query .= " OR session.store_id = " . $DAO_store->id;
				}

				$DAO_session->whereAdd($store_id_query);
			}
			else
			{
				$DAO_session->store_id = $_GET['store'];
			}

			$DAO_booking = DAO_CFactory::create('booking');
			$DAO_booking->status = CBooking::ACTIVE;

			$DAO_order_item = DAO_CFactory::create('order_item');

			$DAO_session->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_booking->joinAddWhereAsOn($DAO_session);
			$DAO_order_item->joinAddWhereAsOn($DAO_booking);
			$DAO_menu_item->joinAddWhereAsOn($DAO_order_item);

			$DAO_menu_item->groupBy('menu_item.id, menu_item.pricing_type');
			$DAO_menu_item->orderBy('FeaturedFirst');

			$DAO_menu_item->find();

			$itemArray = array();

			while ($DAO_menu_item->fetch())
			{
				if ($DAO_menu_item->pricing_type == CMenuItem::FULL && $DAO_menu_item->menu_item_category_id != 9)
				{
					$key = $DAO_menu_item->recipe_id . "_L";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::HALF)
				{
					$key = $DAO_menu_item->recipe_id . "_M";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::FOUR)
				{
					$key = $DAO_menu_item->recipe_id . "_4";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::TWO)
				{
					$key = $DAO_menu_item->recipe_id . "_2";
				}
				else
				{
					$key = $DAO_menu_item->recipe_id;
				}

				$price = $DAO_menu_item->price;

				if (is_null($price))
				{
					if (is_null($menuPricingInfo))
					{
						$storeObj->clearMarkupMultiObj();
						$menuPricingInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $_GET['menu_id'], 'FeaturedFirst', false, false, false, false);
					}
					$price = $menuPricingInfo['Specials'][$DAO_menu_item->id]['price'];

					if (is_null($price))
					{
						//EFL
						$price = $menuPricingInfo['Extended Fast Lane'][$DAO_menu_item->id]['price'];
					}

					if (is_null($price))
					{
						//S&S
						$price = $menuPricingInfo['Chef Touched Selections'][$DAO_menu_item->id]['price'];
					}
				}

				$itemArray[$DAO_menu_item->id] = array(
					$key,
					$DAO_menu_item->item_count,
					'',
					$price
				);
			}

			//$cvs_string = implode("\r\n", $itemArray);

			require_once('CSV.inc');
			$csvObj = new CSV();
			$printable_week = date("m_d_Y", strtotime($weekStart));
			$fileName = "Sales numbers for " . $storeObj->store_name . " for week of " . $printable_week;
			$csvObj->writeCSVFile($fileName, array(
				"item",
				"count",
				"blank",
				"price"
			), $itemArray, false, false, false);
			exit;
		}

		if (isset($_GET['op']) && $_GET['op'] == 'export_sales_by_range' && is_numeric($_GET['store']) && is_numeric($_GET['menu_id']))
		{
			$start = CGPC::do_clean($_GET['start'], TYPE_STR);
			$end = CGPC::do_clean($_GET['end'], TYPE_STR);

			//determine menu
			$menu_ids = CMenu::getMenuIdsInDateRange($start, $end);
			$first_menu_id = $_GET['menu_id'];
			if (strlen($menu_ids) == 0)
			{
				$menu_ids = $_GET['menu_id'];
			}
			else
			{
				$ids = explode(',', $menu_ids);
				$first_menu_id = $ids[0];
			}

			$storeQuery = "and s.store_id = " . $_GET['store'];
			$thisStoreQueryMenuToMenuItem = "and mmi.store_id = " . $_GET['store'];
			$storeQueryMenuToMenuItem = "and mmi.store_id = " . $_GET['store'];

			// include child distribution center sales
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->parent_store_id = $_GET['store'];
			$DAO_store->find();

			if (!empty($DAO_store->N))
			{
				$storeQuery = "and (s.store_id = " . $_GET['store'];
				$storeQueryMenuToMenuItem = "and (mmi.store_id = " . $_GET['store'];

				while ($DAO_store->fetch())
				{
					$storeQuery .= " OR s.store_id = " . $DAO_store->id;
					$storeQueryMenuToMenuItem .= " OR mmi.store_id = " . $DAO_store->id;
				}

				$storeQuery .= ")";
				$storeQueryMenuToMenuItem .= ")";
			}

			$endTime = $end . ' 23:59:59';

			$DAO_menu_item = DAO_CFactory::create('menu_item');

			$DAO_menu_item->selectAdd();
			$DAO_menu_item->selectAdd("menu_item.id");
			$DAO_menu_item->selectAdd("menu_item.recipe_id");
			$DAO_menu_item->selectAdd("menu_item.pricing_type");
			$DAO_menu_item->selectAdd("menu_item.servings_per_item");
			$DAO_menu_item->selectAdd("menu_item.menu_item_category_id");
			$DAO_menu_item->selectAdd("sum(order_item.item_count) as item_count");
			$DAO_menu_item->selectAdd("max(menu_to_menu_item.override_price) as price");
			$DAO_menu_item->selectAdd("menu_item.price as default_price");

			$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
			$DAO_menu_to_menu_item->whereAdd("menu_to_menu_item.menu_item_id = menu_item.id");

			$DAO_session = DAO_CFactory::create('session');
			$DAO_session->whereAdd("session.menu_id IN (" . $menu_ids . ")");
			$DAO_session->whereAdd("session.session_start > '" . $start . "'");
			$DAO_session->whereAdd("session.session_start < '" . $endTime . "'");

			// include child distribution center sales
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->parent_store_id = $_GET['store'];

			if ($DAO_store->find())
			{
				$store_id_query = "session.store_id = " . $DAO_store->parent_store_id;

				while ($DAO_store->fetch())
				{
					$store_id_query .= " OR session.store_id = " . $DAO_store->id;
				}

				$DAO_session->whereAdd($store_id_query);
			}
			else
			{
				$DAO_session->store_id = $_GET['store'];
			}

			$DAO_booking = DAO_CFactory::create('booking');
			$DAO_booking->status = CBooking::ACTIVE;

			$DAO_order_item = DAO_CFactory::create('order_item');

			$DAO_session->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_booking->joinAddWhereAsOn($DAO_session);
			$DAO_order_item->joinAddWhereAsOn($DAO_booking);
			$DAO_menu_item->joinAddWhereAsOn($DAO_order_item);

			$DAO_menu_item->groupBy('menu_item.id, menu_item.pricing_type');
			$DAO_menu_item->orderBy('FeaturedFirst');

			$DAO_menu_item->find();

			$itemArray = array();

			while ($DAO_menu_item->fetch())
			{
				if ($DAO_menu_item->pricing_type == CMenuItem::FULL && $DAO_menu_item->menu_item_category_id != 9)
				{
					$key = $DAO_menu_item->recipe_id . "_L";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::HALF)
				{
					$key = $DAO_menu_item->recipe_id . "_M";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::FOUR)
				{
					$key = $DAO_menu_item->recipe_id . "_4";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::TWO)
				{
					$key = $DAO_menu_item->recipe_id . "_2";
				}
				else
				{
					$key = $DAO_menu_item->recipe_id;
				}

				$price = $DAO_menu_item->price;

				if (is_null($price))
				{
					if (is_null($menuPricingInfo))
					{
						$storeObj->clearMarkupMultiObj();
						$menuPricingInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $first_menu_id, 'FeaturedFirst', false, false, false, false);
					}
					$price = $menuPricingInfo['Specials'][$DAO_menu_item->id]['price'];

					if (is_null($price))
					{
						//EFL
						$price = $menuPricingInfo['Extended Fast Lane'][$DAO_menu_item->id]['price'];
					}

					if (is_null($price))
					{
						//S&S
						$price = $menuPricingInfo['Chef Touched Selections'][$DAO_menu_item->id]['price'];
					}
				}

				$itemArray[$DAO_menu_item->id] = array(
					$key,
					$DAO_menu_item->item_count,
					'',
					$price
				);
			}

			//$cvs_string = implode("\r\n", $itemArray);

			require_once('CSV.inc');
			$csvObj = new CSV();
			$printable_start = date("m_d_Y", strtotime($start));
			$printable_end = date("m_d_Y", strtotime($end));
			$fileName = "Sales numbers for " . $storeObj->store_name . " for " . $printable_start . " - " . $printable_end;
			$csvObj->writeCSVFile($fileName, array(
				"item",
				"count",
				"blank",
				"price"
			), $itemArray, false, false, false);
			exit;
		}

		if (isset($_GET['op']) && $_GET['op'] == 'export_EFL_sales' && is_numeric($_GET['store']) && is_numeric($_GET['menu_id']))
		{
			$DAO_menu_item = DAO_CFactory::create('menu_item');
			$DAO_menu_item->is_store_special = 1;

			$DAO_menu_item->selectAdd();
			$DAO_menu_item->selectAdd("menu_item.id");
			$DAO_menu_item->selectAdd("menu_item.recipe_id");
			$DAO_menu_item->selectAdd("menu_item.pricing_type");
			$DAO_menu_item->selectAdd("menu_item.servings_per_item");
			$DAO_menu_item->selectAdd("menu_item.menu_item_category_id");
			$DAO_menu_item->selectAdd("sum(order_item.item_count) as item_count");
			$DAO_menu_item->selectAdd("max(menu_to_menu_item.override_price) as price");
			$DAO_menu_item->selectAdd("menu_item.price as default_price");

			$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
			$DAO_menu_to_menu_item->whereAdd("menu_to_menu_item.menu_item_id = menu_item.id");

			$DAO_session = DAO_CFactory::create('session');
			$DAO_session->menu_id = $_GET['menu_id'];

			// include child distribution center sales
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->parent_store_id = $_GET['store'];

			if ($DAO_store->find())
			{
				$store_id_query = "session.store_id = " . $DAO_store->parent_store_id;

				while ($DAO_store->fetch())
				{
					$store_id_query .= " OR session.store_id = " . $DAO_store->id;
				}

				$DAO_session->whereAdd($store_id_query);
			}
			else
			{
				$DAO_session->store_id = $_GET['store'];
			}

			$DAO_booking = DAO_CFactory::create('booking');
			$DAO_booking->status = CBooking::ACTIVE;

			$DAO_order_item = DAO_CFactory::create('order_item');

			$DAO_session->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_booking->joinAddWhereAsOn($DAO_session);
			$DAO_order_item->joinAddWhereAsOn($DAO_booking);
			$DAO_menu_item->joinAddWhereAsOn($DAO_order_item);

			$DAO_menu_item->groupBy('menu_item.id, menu_item.pricing_type');
			$DAO_menu_item->orderBy('FeaturedFirst');

			$DAO_menu_item->find();

			$itemArray = array();

			while ($DAO_menu_item->fetch())
			{
				if ($DAO_menu_item->pricing_type == CMenuItem::FULL && $DAO_menu_item->menu_item_category_id != 9)
				{
					$key = $DAO_menu_item->recipe_id . "_L";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::HALF)
				{
					$key = $DAO_menu_item->recipe_id . "_M";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::FOUR)
				{
					$key = $DAO_menu_item->recipe_id . "_4";
				}
				else if ($DAO_menu_item->pricing_type == CMenuItem::TWO)
				{
					$key = $DAO_menu_item->recipe_id . "_2";
				}
				else
				{
					$key = $DAO_menu_item->recipe_id;
				}

				$price = $DAO_menu_item->price;

				if (is_null($price))
				{
					if (is_null($menuPricingInfo))
					{
						$storeObj->clearMarkupMultiObj();
						$menuPricingInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $_GET['menu_id'], 'FeaturedFirst', true, true, false, false);
					}
					$price = $menuPricingInfo['Specials'][$DAO_menu_item->id]['price'];

					if (is_null($price))
					{
						//EFL
						$price = $menuPricingInfo['Extended Fast Lane'][$DAO_menu_item->id]['price'];
					}

					if (is_null($price))
					{
						//S&S
						$price = $menuPricingInfo['Chef Touched Selections'][$DAO_menu_item->id]['price'];
					}
				}

				$itemArray[$DAO_menu_item->id] = array(
					$key,
					$DAO_menu_item->item_count,
					'',
					$price
				);
			}

			$menuInfo = CMenu::getMenuInfo($_GET['menu_id']);
			$printable_menu = $menuInfo['menu_name'];

			require_once('CSV.inc');
			$csvObj = new CSV();
			$fileName = "EFL Sales numbers for " . $storeObj->store_name . " for month of " . $printable_menu;
			$csvObj->writeCSVFile($fileName, array(
				"item",
				"count",
				"blank",
				"price"
			), $itemArray, false, false, false);
			exit;
		}

		$tpl->assign('store_id', $store_id);

		// ------------------------------------------ Build menu array and wdiget
		$menus = CMenu::getLastXMenus(4);
		$lastActiveMenuId = null;
		$menuOptions = array();
		//$lowestMenuID = 1000000;

		foreach ($menus as $thisMenu)
		{
			//	if ($thisMenu->id < $lowestMenuID)
			//	{
			//		$lowestMenuID = $thisMenu->id;
			//	}

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
		else
		{
			$currentMenu = CBrowserSession::instance()->getValue('menu_editor_current_menu');
		}

		// For Now just the last 4 menus returned by getLastXMenus
		/*
				$startDateOfActiveMenuTS = $menus[$lowestMenuID]->menu_start;
				$seventhDayTS = strtotime($menus[$lowestMenuID]->anchor) + (86400 * 8);

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

		*/
		if (!CStore::storeSupportsReciProfity($store_id, $currentMenu))
		{
			$currentMenu = 245;
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

		if (!CStore::storeSupportsReciProfity($store_id, $menu_id))
		{
			CApp::bounce("?page=admin_menu_editor&rd_menu=$menu_id");
		}

		if ($this->canOverrideMenu && isset($_GET['om']))
		{
			$menu_id = CGPC::do_clean($_GET['om'], TYPE_INT);
		}

		$this->storeSupportsPlatePoints = CStore::storeSupportsPlatePoints($storeObj);
		$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);
		$tpl->assign('storeInfo', $storeObj);

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

		$storeObj->clearMarkupMultiObj();
		// ----------------------------- build menu array from current state
		$menuInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $menu_id, 'FeaturedFirst', true, true, false, false, false, true);

		// count "sub-entrees" for each item so the display loop can group correctly
		$qtyMap = array();
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

		$targetMenu = CMenu::getMenuInfo($menu_id);

		$tpl->assign("menu_start", $targetMenu['global_menu_start_date']);
		$dateDT = new DateTime($targetMenu['global_menu_start_date']);
		$endDT = new DateTime($targetMenu['global_menu_end_date']);
		$endDT->modify('+1 days');
		$menuInterval = $dateDT->diff($endDT);
		$menuIntervalDays = $menuInterval->format('%a DAY');

		$tpl->assign("menu_interval", $menuIntervalDays);

		// ------------------------------Setup initial guest count values

		$goalsObj = DAO_CFactory::create('store_monthly_goals');
		$goalsObj->query("select * from store_monthly_goals where store_id = $store_id and date = '{$targetMenu['menu_start']}'");
		$goalsObj->fetch();
		if (!empty($goalsObj->regular_guest_count_goal_for_inventory))
		{
			$Form->DefaultValues['regular_guest_count_goal'] = $goalsObj->regular_guest_count_goal_for_inventory;
			$Form->DefaultValues['taste_guest_count_goal'] = $goalsObj->taste_guest_count_goal_for_inventory;
			$Form->DefaultValues['intro_guest_count_goal'] = $goalsObj->intro_guest_count_goal_for_inventory;
		}
		else
		{
			$defaults = self::retrieveGuestCountEstimates($store_id, $targetMenu);
			if ($defaults)
			{
				$Form->DefaultValues['regular_guest_count_goal'] = $defaults['regular'];
				$Form->DefaultValues['taste_guest_count_goal'] = $defaults['taste'];
				$Form->DefaultValues['intro_guest_count_goal'] = $defaults['intro'];
			}
			else
			{
				$Form->DefaultValues['regular_guest_count_goal'] = 0;
				$Form->DefaultValues['taste_guest_count_goal'] = 0;
				$Form->DefaultValues['intro_guest_count_goal'] = 0;
			}
		}
		// ----------------------------- get Weekly Numbers
		$weekArr = CMenu::getWeeksArrayForMenu($menu_id);

		$this->getWeekDistribution($weekArr, $store_id, $menu_id);

		$salesSummary = $this->getWeeklySalesSummary($menuInfo, $weekArr, $store_id);

		$tpl->assign('weeks_inv', $weekArr);
		$tpl->assign('sales_summary', $salesSummary);

		// ------------------------------ handle submission
		if (isset($_POST['action']) && $_POST['action'] === "finalize")
		{
			if ($menu_id != $_POST['loaded_menu_id'])
			{
				$tpl->setErrorMsg('You attempted to finalize a menu that did not load properly. The page has now been reloaded. Please check the menu selector and if it is not correct select the desired menu and be certain is has completely loaded before making and finalizing any edits.');
				CApp::bounce('?page=admin_menu_editor'); //reload the page
			}
			CLog::Record("MENU_INVENTORY_MGR: Page finalized for store: " . $store_id);
			CLog::RecordDebugTrace("Menu Inventory Finalized\r\n" . print_r($_POST, true), "MENU_EDITOR");

			$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);

			// Test for existing store menu
			$storeMenuExists = CMenu::storeSpecificMenuExists($menu_id, $store_id);

			if (defined('USE_GLOBAL_SIDES_MENU') && USE_GLOBAL_SIDES_MENU)
			{
				// INVENTORY TOUCH POINT 2
				if ($storeMenuExists)
				{
					// update inventory data
					foreach ($_POST as $k => $v)
					{
						if (strpos($k, "ori_") === 0)
						{
							$menu_item_id = substr($k, 4);
							$inventory = DAO_CFactory::create('menu_item_inventory');
							//$inventory->query("select mii.*, mi.menu_item_category_id  from menu_item_inventory mii join menu_item mi on mi.recipe_id = mii.recipe_id where mi.id = $menu_item_id " .
							//					" and mii.menu_id = $menu_id and mii.store_id = {$storeObj->id} and mii.is_deleted = 0 and mi.is_deleted = 0 limit 1 ");
							$inventory->query("select mi.menu_item_category_id, mi.recipe_id,
												if(mi.menu_item_category_id = 9, mii2.override_inventory, mii.override_inventory) as override_inventory,
												if(mi.menu_item_category_id = 9, mii2.number_sold, mii.number_sold) as number_sold,
												if(mi.menu_item_category_id = 9, mii2.initial_inventory, mii.initial_inventory) as initial_inventory
												from menu_item_inventory mii
												join menu_item mi on mi.recipe_id = mii.recipe_id
												left join menu_item_inventory mii2 on mii2.recipe_id = mii.recipe_id and mii2.store_id = mii.store_id and mii2.menu_id is null
												where mi.id = $menu_item_id and mii.menu_id = $menu_id and mii.store_id = {$storeObj->id} and mii.is_deleted = 0 and mi.is_deleted = 0 limit 1");

							if ($inventory->fetch())
							{
								if (($v >= $inventory->number_sold && $v >= ($inventory->initial_inventory / 2)) || $inventory->recipe_id == 740)
								{
									if ($inventory->menu_item_category_id == 9)
									{
										$invUpdater = DAO_CFactory::create('menu_item_inventory');
										$invUpdater->query("update menu_item_inventory set override_inventory = $v where store_id = {$storeObj->id}  and is_deleted = 0 and recipe_id = {$inventory->recipe_id} and menu_id is null");
										if ($v != $inventory->override_inventory)
										{
											CMenuItemInventoryHistory::RecordEvent($storeObj->id, $inventory->recipe_id, null, CMenuItemInventoryHistory::MENU_EDIT, $v - $inventory->override_inventory);
										}
									}
									else
									{
										$invUpdater = DAO_CFactory::create('menu_item_inventory');
										$invUpdater->query("update menu_item_inventory set override_inventory = $v where store_id = {$storeObj->id}  and is_deleted = 0 and recipe_id = {$inventory->recipe_id} and menu_id = $menu_id");
									}
								}
							}
						}
					}
				}
				else
				{
					// INVENTORY TOUCH POINT 2
					if ($storeMenuExists)
					{
						// update inventory data
						foreach ($_POST as $k => $v)
						{
							if (strpos($k, "ori_") === 0)
							{
								$menu_item_id = substr($k, 4);
								$inventory = DAO_CFactory::create('menu_item_inventory');
								$inventory->query("select mi.menu_item_category_id, mi.recipe_id, mii.override_inventory, mii.number_sold, mii.initial_inventory
							from menu_item_inventory mii
							join menu_item mi on mi.recipe_id = mii.recipe_id
							where mi.id = $menu_item_id and mii.menu_id = $menu_id and mii.store_id = {$storeObj->id} and mii.is_deleted = 0 and mi.is_deleted = 0 limit 1");

								if ($inventory->fetch())
								{
									if ($v >= $inventory->number_sold && $v >= $inventory->initial_inventory / 2)
									{
										$invUpdater = DAO_CFactory::create('menu_item_inventory');
										$invUpdater->query("update menu_item_inventory set override_inventory = $v where store_id = {$storeObj->id}  and is_deleted = 0 and recipe_id = {$inventory->recipe_id} and menu_id = $menu_id");

										if ($inventory->menu_item_category_id == 9)
										{
											if ($v != $inventory->override_inventory)
											{
												CMenuItemInventoryHistory::RecordEvent($storeObj->id, $inventory->recipe_id, null, CMenuItemInventoryHistory::MENU_EDIT, $v - $inventory->override_inventory);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			//recalculate arrays and markup for display
			$storeObj->clearMarkupMultiObj();
			unset($menuInfo);
			$menuInfo = COrders::buildNewPricingMenuPlanArrays($storeObj, $menu_id, 'FeaturedFirst', true, true, false, false, false, true);
			// count "sub-entrees" for each item so the display loop can group correctly

			$qtyMap2 = array();
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
		}

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "loaded_menu_id",
			CForm::value => $menu_id
		));

		$hasLoadedMenu = $this->hasLoadedMenu($menuInfo);
		$hasSavedStoreMix = $this->hasSavedStoreMix($storeObj->id, $menu_id);
		$hasSavedOrderMetrics = $this->hasSavedOrderMetrics($storeObj->id, $menuInfo['menu_anchor_date']);
		$hasSavedPreOrder = $this->hasSavedPreOrder($storeObj->id, $menu_id, $menuInfo);
		$numberWeeksInMenu = 5;

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "regular_guest_count_goal",
			CForm::required => true,
			CForm::onChange => 'calculatePage',
			CForm::css_class => 'gt_input_count ' . ($hasSavedOrderMetrics ? "saved" : "unsaved"),
			CForm::maxlength => 3
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "taste_guest_count_goal",
			CForm::required => true,
			CForm::onChange => 'calculatePage',
			CForm::css_class => 'gt_input_count ' . ($hasSavedOrderMetrics ? "saved" : "unsaved"),
			CForm::maxlength => 3
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "intro_guest_count_goal",
			CForm::required => true,
			CForm::onChange => 'calculatePage',
			CForm::css_class => 'gt_input_count ' . ($hasSavedOrderMetrics ? "saved" : "unsaved"),
			CForm::maxlength => 3
		));

		$menuAddonArray = CMenu::buildMenuAddonArray($storeObj, $menu_id);
		$ctsArray = CMenu::buildCTSArray($storeObj, $menu_id);
		if (defined('USE_GLOBAL_SIDES_MENU') && USE_GLOBAL_SIDES_MENU)
		{
			$this->addFuturePickups($ctsArray, $storeObj->id);
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

		$this->markIntroItems($menuInfo, $menu_id);
		$this->markTasteItems($menuInfo, $menu_id);

		$menuState = array(
			'hasLoadedMenu' => $hasLoadedMenu,
			'hasSavedStoreMix' => $hasSavedStoreMix,
			'hasSavedOrderMetrics' => $hasSavedOrderMetrics,
			'hasSavedPreOrder' => $hasSavedPreOrder,
			'numberWeeksInMenu' => $numberWeeksInMenu
		);

		$tpl->assign('menu_state', $menuState);
		$tpl->assign('menuInfo', $menuInfo);
		$tpl->assign('storeSpecialsItems', json_encode($storeSpecialsItems));
		$formArray = $Form->render();
		$tpl->assign('form', $formArray);
	}

	function addFuturePickups(&$inArray, $storeID)
	{
		$tempArray = CMenu::getFutureFTPickups($storeID);
		foreach ($inArray as $id => &$thisItem)
		{
			if (isset($tempArray[$thisItem['recipe_id']]))
			{
				$thisItem['future_pickups'] = $tempArray[$thisItem['recipe_id']];
			}
			else
			{
				$thisItem['future_pickups'] = 0;
			}
		}
	}

	function hasLoadedMenu($menuInfo)
	{
		if (!empty($menuInfo['Specials']))
		{
			return true;
		}

		return false;
	}

	function hasSavedPreOrder($store_id, $menu_id, $menuInfo)
	{
		$recipe_id = null;
		if (!empty($menuInfo['Specials']))
		{
			$firstItem = array_pop($menuInfo['Specials']);
			$recipe_id = $firstItem['recipe_id'];
		}
		else
		{
			return false;
		}

		$invTester = new DAO();
		$invTester->query("select id from menu_item_inventory 
								where store_id = $store_id and menu_id = $menu_id and recipe_id = $recipe_id and week1_projection + week2_projection + week3_projection + week4_projection > 0 and is_deleted = 0");
		if ($invTester->N > 0)
		{
			return true;
		}

		return false;
	}

	function hasSavedStoreMix($store_id, $menu_id)
	{
		$mixTester = new DAO();
		$mixTester->query("select id from menu_item_inventory where store_id = $store_id and menu_id = $menu_id and sales_mix > 0 and is_deleted = 0");
		if ($mixTester->N > 0)
		{
			return true;
		}

		return false;
	}

	function hasSavedOrderMetrics($store_id, $menu_anchor_date)
	{
		$metricsTester = new DAO();
		$metricsTester->query("select id from store_monthly_goals where store_id = $store_id and date = '$menu_anchor_date' and regular_guest_count_goal_for_inventory > 0");
		if ($metricsTester->N > 0)
		{
			return true;
		}

		return false;
	}

	function markIntroItems(&$menuInfo, $menu_id)
	{

		$introItems = CBundle::getBundleItemsArrayByMenuAndBundleType($menu_id, 'TV_OFFER');

		foreach ($menuInfo as $categoryName => &$subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $item => &$menu_item)
				{
					if (is_numeric($item))
					{
						if (array_key_exists($item, $introItems))
						{
							$menu_item['is_intro'] = true;
						}
					}
				}
			}
		}
	}

	function markTasteItems(&$menuInfo, $menu_id)
	{
		$tasteItems = CBundle::getBundleItemsArrayByMenuAndBundleType($menu_id, 'DREAM_TASTE');

		foreach ($menuInfo as $categoryName => &$subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $item => &$menu_item)
				{
					if (is_numeric($item))
					{
						if (array_key_exists($item, $tasteItems))
						{
							// only FULL items are used in display array but currently all TASTE items are HALF so
							// also mark the full version using entree_id
							$menu_item['is_taste'] = true;
							$menuInfo[$categoryName][$menu_item['entree_id']]['is_taste'] = true;
						}
					}
				}
			}
		}
	}

	static function retrieveGuestCountEstimates($store_id, $targetMenu)
	{
		$targetMenuMonth = $targetMenu['menu_start'];
		$targetMonthParts = explode("-", $targetMenuMonth);
		$thisMonthLastYear = date("Y-m-d", mktime(0, 0, 0, $targetMonthParts[1], 1, $targetMonthParts[0] - 1));

		// Check for data for this month 1 year ago
		$metricsRetrieved = new DAO();
		$metricsRetrieved->query("SELECT date, orders_count_regular_existing_guests + orders_count_regular_new_guests + orders_count_regular_reacquired_guests as regular
				,  orders_count_intro_existing_guests + orders_count_intro_new_guests + orders_count_intro_reacquired_guests as intro
				, orders_count_taste_existing_guests + orders_count_taste_new_guests + orders_count_taste_reacquired_guests + orders_count_fundraiser_existing_guests + orders_count_fundraiser_new_guests + orders_count_fundraiser_reacquired_guests as taste
				FROM `dashboard_metrics_guests_by_menu`
				where store_id = $store_id and date = '$thisMonthLastYear' and is_deleted = 0
				order by date desc");

		$regularCount = 0;
		$tasteCount = 0;
		$introCount = 0;
		$monthsBack = 2;
		$numFound = 0;

		if ($metricsRetrieved->fetch())
		{
			if ($metricsRetrieved->regular > 0)
			{
				// must be at least one month of regular orders to produce average
				$regularCount += $metricsRetrieved->regular;
				$tasteCount += $metricsRetrieved->taste;
				$introCount += $metricsRetrieved->intro;
				$monthsBack - 1;
				$numFound = 1;
			}
		}

		// retrieve as many of the last 3 months counts as exist and average them.
		$cur_menu_id = CMenu::getCurrentMenuId();
		$thisMenu = CMenu::getMenuInfo($cur_menu_id);
		$thisMenuMonth = $thisMenu['menu_start'];
		$thisMonthParts = explode("-", $thisMenuMonth);
		$firstMonthToConsider = date("Y-m-d", mktime(0, 0, 0, $thisMonthParts[1] - $monthsBack, 1, $thisMonthParts[0]));

		$metricsRetrieved = new DAO();
		$metricsRetrieved->query("SELECT date, orders_count_regular_existing_guests + orders_count_regular_new_guests + orders_count_regular_reacquired_guests as regular
				,  orders_count_intro_existing_guests + orders_count_intro_new_guests + orders_count_intro_reacquired_guests as intro
				, orders_count_taste_existing_guests + orders_count_taste_new_guests + orders_count_taste_reacquired_guests + orders_count_fundraiser_existing_guests + orders_count_fundraiser_new_guests + orders_count_fundraiser_reacquired_guests as taste
				FROM `dashboard_metrics_guests_by_menu`
				where store_id = $store_id and date < '$thisMenuMonth' and date >= '$firstMonthToConsider' and is_deleted = 0
				order by date desc");

		while ($metricsRetrieved->fetch())
		{
			if ($metricsRetrieved->regular > 0)
			{
				// must be at least one month of regular orders to produce average
				$numFound++;
				$regularCount += $metricsRetrieved->regular;
				$tasteCount += $metricsRetrieved->taste;
				$introCount += $metricsRetrieved->intro;
			}
		}

		if ($numFound > 0)
		{
			return array(
				'regular' => round($regularCount / $numFound),
				'intro' => round($introCount / $numFound),
				'taste' => round($tasteCount / $numFound)
			);
		}
		else
		{
			return false;
		}
	}

	function getWeekDistribution(&$inWeekArray, $store_id, $current_menu_id)
	{
		$first_menu = $current_menu_id - 12;
		$menuCount = 0;
		$weeksInCurMenu = count($inWeekArray);

		for ($menu_iter = $first_menu; $menu_iter < $current_menu_id; $menu_iter++)
		{
			$servings = new DAO();
			$servings->query("select WEEK(s.session_start, 1) as weeknum, sum(oi.item_count * mi.servings_per_item)  as total_servings
                                    from orders o
                                    join store st on st.id = o.store_id AND st.active = 1
                                    join booking b on b.order_id = o.id and b.status = 'ACTIVE'
                                    join session s on s.id = b.session_id and s.menu_id = $menu_iter and s.store_id = $store_id
                                    join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
                                    join menu_item mi on mi.id = oi.menu_item_id and mi.menu_item_category_id < 5 and mi.is_store_special = 0
                                    group by WEEK(s.session_start, 1)");
			$first = true;
			$firstweek = 0;
			while ($servings->fetch())
			{
				if ($first)
				{
					$menuCount++;
					$first = false;
					$firstweek = $servings->weeknum - 1;
				}

				$mainarray[$menu_iter][$servings->weeknum - $firstweek] = $servings->total_servings;
			}
		}

		// TODO: question: how many menus is enough to establish a good estimate?
		if ($menuCount > 0)
		{
			$week1Total = 0;
			$week2Total = 0;
			$week3Total = 0;
			$week4Total = 0;
			$week5Total = 0;

			$absoluteTotal = 0;

			if ($weeksInCurMenu == 5)
			{
				$week1Total5Weeks = 0;
				$week2Total5Weeks = 0;
				$week3Total5Weeks = 0;
				$week4Total5Weeks = 0;
				$menusWithWeek5Data = 0;

				foreach ($mainarray as $menu_id => $data)
				{
					$has5thWeek = (!empty($data[5]));

					if ($has5thWeek)
					{
						$menusWithWeek5Data++;
					}

					if (!empty($data[1]))
					{
						$week1Total += $data[1];
						if ($has5thWeek)
						{
							$week1Total5Weeks += $data[1];
						}
					}
					if (!empty($data[2]))
					{
						$week2Total += $data[2];
						if ($has5thWeek)
						{
							$week2Total5Weeks += $data[2];
						}
					}
					if (!empty($data[3]))
					{
						$week3Total += $data[3];
						if ($has5thWeek)
						{
							$week3Total5Weeks += $data[3];
						}
					}
					if (!empty($data[4]))
					{
						$week4Total += $data[4];
						if ($has5thWeek)
						{
							$week4Total5Weeks += $data[4];
						}
					}
					if (!empty($data[5]))
					{
						$week5Total += $data[5];
					}
				}

				if ($menusWithWeek5Data > 0)
				{
					$week1Total = $week1Total5Weeks;
					$week2Total = $week2Total5Weeks;
					$week3Total = $week3Total5Weeks;
					$week4Total = $week4Total5Weeks;
				}
				// so if it is a 5 week menu and there was at least 1 menu with valid sales then use that menu and any others with 5 weeks to set distribution

			}
			else
			{
				foreach ($mainarray as $menu_id => $data)
				{
					if (!empty($data[1]))
					{
						$week1Total += $data[1];
					}
					if (!empty($data[2]))
					{
						$week2Total += $data[2];
					}
					if (!empty($data[3]))
					{
						$week3Total += $data[3];
					}
					if (!empty($data[4]))
					{
						$week4Total += $data[4];
					}
				}
			}

			$absoluteTotal = $week1Total + $week2Total + $week3Total + $week4Total + $week5Total;

			$inWeekArray[1]['distribution_percent'] = ($week1Total / $absoluteTotal) * 100;
			$inWeekArray[2]['distribution_percent'] = ($week2Total / $absoluteTotal) * 100;
			$inWeekArray[3]['distribution_percent'] = ($week3Total / $absoluteTotal) * 100;
			$inWeekArray[4]['distribution_percent'] = ($week4Total / $absoluteTotal) * 100;

			if ($weeksInCurMenu == 5)
			{
				$inWeekArray[5]['distribution_percent'] = ($week5Total / $absoluteTotal) * 100;
			}
		}
		else
		{
			if ($weeksInCurMenu == 4)
			{
				$inWeekArray[1]['distribution_percent'] = 15;
				$inWeekArray[2]['distribution_percent'] = 20;
				$inWeekArray[3]['distribution_percent'] = 30;
				$inWeekArray[4]['distribution_percent'] = 35;
			}
			else
			{
				$inWeekArray[1]['distribution_percent'] = 10;
				$inWeekArray[2]['distribution_percent'] = 15;
				$inWeekArray[3]['distribution_percent'] = 17;
				$inWeekArray[4]['distribution_percent'] = 22;
				$inWeekArray[5]['distribution_percent'] = 36;
			}
		}
	}

	function getWeeklySalesSummary(&$menuInfo, $weekArr, $store_id)
	{
		$storeQuery = "and s.store_id = " . $store_id;

		// include child distribution center sales
		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->parent_store_id = $store_id;
		$DAO_store->find();

		if (!empty($DAO_store->N))
		{
			$storeQuery = "and (s.store_id = " . $store_id;

			while ($DAO_store->fetch())
			{
				$storeQuery .= " OR s.store_id = " . $DAO_store->id;
			}

			$storeQuery .= ")";
		}

		$mainarray = array();
		if (!empty($menuInfo['Specials']))
		{
			$IDs = implode(",", array_keys($menuInfo['Specials']));

			$ServingCounter = new DAO();
			$ServingCounter->query("select mi.recipe_id, mi.pricing_type, mi.servings_per_item, WEEK(s.session_start, 1) as weeknum, sum(oi.item_count) as item_count from menu_item mi
								join order_item oi on oi.menu_item_id = mi.id and oi.is_deleted = 0
								join booking b on b.order_id = oi.order_id and b.status ='ACTIVE'
								join session s on s.id = b.session_id and s.menu_id = {$menuInfo['menu_id']} " . $storeQuery . "
								where mi.id in (" . $IDs . ") and mi.is_deleted = 0
								group by mi.id, mi.servings_per_item, WEEK(s.session_start, 1)
								order by WEEK(s.session_start, 1), mi.id, mi.servings_per_item");
			$first = true;
			$firstweek = 0;

			while ($ServingCounter->fetch())
			{
				if ($first)
				{
					$first = false;
					$firstweek = $ServingCounter->weeknum - 1;
				}

				if (!isset($mainarray[$ServingCounter->recipe_id]))
				{
					$mainarray[$ServingCounter->recipe_id] = array();
				}

				if (!isset($mainarray[$ServingCounter->recipe_id][$ServingCounter->pricing_type]))
				{
					$mainarray[$ServingCounter->recipe_id][$ServingCounter->pricing_type] = array();
				}

				$mainarray[$ServingCounter->recipe_id][$ServingCounter->pricing_type][$ServingCounter->weeknum - $firstweek] = $ServingCounter->item_count * $ServingCounter->servings_per_item;

				if (!isset($mainarray[$ServingCounter->recipe_id]['TOTAL']))
				{
					$mainarray[$ServingCounter->recipe_id]['TOTAL'] = array();
				}

				if (!isset($mainarray[$ServingCounter->recipe_id]['TOTAL'][$ServingCounter->pricing_type]))
				{
					$mainarray[$ServingCounter->recipe_id]['TOTAL'][$ServingCounter->pricing_type] = 0;
				}

				$mainarray[$ServingCounter->recipe_id]['TOTAL'][$ServingCounter->pricing_type] += ($ServingCounter->item_count * $ServingCounter->servings_per_item);
			}
		}

		return $mainarray;
	}
}

?>