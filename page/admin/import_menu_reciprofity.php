<?php
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/CFactory.php");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once("CLog.inc");
require_once 'includes/class.Diff.php';
require_once("DAO/BusinessObject/CImportReciprofity.php");

class page_admin_import_menu_reciprofity extends CPageAdminOnly
{

	static public $changelog = array();
	static public $testMode = false;
	static public $updateExistingMenu = false;
	static private $itemIDtoEntreeIDMap = array();

	// Note: to keep the determination of the Entree ID independent of order in the handoff (now with each size having it's own line in the handoff) we record all inserts in this array
	// and then after all items are inserted we update the items with the entree id. (Entree ID associates Items of the same Recipe but different sizes)

	public $ImportForm = null;

	public function __construct()
	{
		parent::__construct();

		$this->ImportForm = new CForm();
		$this->ImportForm->Repost = true;
		$this->ImportForm->Bootstrap = true;
		$this->ImportForm->ElementID = true;
	}

	function runSiteAdmin()
	{
		$this->importRecipes();
	}

	function importRecipes()
	{
		ini_set('memory_limit', '2000M');

		$tpl = CApp::instance()->template();

		if (isset($_POST['testmode']))
		{
			self::$testMode = true;
		}

		$tpl->assign('import_success', false);
		$tpl->assign('testmode', self::$testMode);

		if (!empty($_POST['menu']) && !empty($_FILES['base_menu_import']) && $_FILES['base_menu_import']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['base_menu_import']['tmp_name']))
		{


			// check if we're updating an existing menu
			$menuArray = self::getMenuOptionsArray();
			if (!empty($menuArray[$_POST['menu']]['data']['data-imported']) && $menuArray[$_POST['menu']]['data']['data-imported'] == 'true')
			{
				self::$updateExistingMenu = true;
			}

			set_time_limit(100000);
			$menu_item_id_array = array();

			$tpl->assign('menu_id', CGPC::do_clean($_POST['menu'], TYPE_INT));

			try
			{
				$uberObject = DAO_CFActory::create('menu_item');
				$uberObject->query('START TRANSACTION;');

				$rows = CImportReciprofity::distillCSVImport($_FILES['base_menu_import'], $tpl);

				// check data for problems
				// Note Sanity check also adds 2 columns: 'recipe_id' (recipe_id without the size suffix) and 'pricing_type' - (currently FULL and HALF)
				$sanityResult = CImportReciprofity::sanityCheck($rows, self::$updateExistingMenu);
				if ($sanityResult !== true)
				{
					throw new Exception($sanityResult);
				}

				$existingItemsForMenu = CMenuItem::getMenuItemsArray(CGPC::do_clean($_POST['menu'], TYPE_INT));
				$count = 0;
				$countForUninsertedItems = 100000;

				// passed sanity checks, now go over the array again and insert the information
				foreach ($rows as $row => $col)
				{
					// skip the first row, it's all the column headers. Skip any menu item that has the class of "App" (appetizer, these are for store use)
					if (strtolower($col[MENU_CLASS]) != 'prep day')
					{
						$doUpdate = false;

						if (self::$testMode)
						{
							if (strpos($col[RECIPE_ID], "_M") !== false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['HALF']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							if (strpos($col[RECIPE_ID], "_L") !== false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['FULL']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							// Sides have no size extension
							if (strpos($col[RECIPE_ID], "_") === false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['FULL']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							if ($doUpdate)
							{
								$menu_item_id = self::compareIt(CGPC::do_clean($_POST['menu'], TYPE_INT), $col, $tpl, $doUpdate);
							}
							else
							{
								$menu_item_id = self::logIt(CGPC::do_clean($_POST['menu'], TYPE_INT), $col, $tpl, $countForUninsertedItems++, $doUpdate);
							}
						}
						else
						{
							if (strpos($col[RECIPE_ID], "_M") !== false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['HALF']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							if (strpos($col[RECIPE_ID], "_L") !== false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['FULL']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							// Sides have no size extension
							if (strpos($col[RECIPE_ID], "_") === false && !empty($existingItemsForMenu['recipe_ids'][$col['recipe_id']]['FULL']))
							{
								$doUpdate = $existingItemsForMenu['recipe_ids'][$col['recipe_id']];
							}

							$menu_item_id = self::stickIt(CGPC::do_clean($_POST['menu'], TYPE_INT), $col, $tpl, $doUpdate);
						}

						//$menu_item_id_array[$menu_item_id] = $menu_item_id;

						$count++;
					}
				}

				self::updateEntreeIDs();

				$menu_item_id_array = array();
				foreach (self::$changelog as $change)
				{
					if (!empty($change['build_ssm']))
					{
						$menu_item_id_array[$change['menu_item_id']] = $change['menu_item_id'];
					}
				}

				$tpl->assign('import_success', true);
				$tpl->assign('changelog', self::$changelog);

				$uberObject->query('COMMIT;');

				if (!self::$testMode)
				{
					if (!self::$updateExistingMenu)
					{
						$tpl->setStatusMsg('<p>Menu imported: <a class="btn btn-primary btn-sm" href="/backoffice/menu_inspector?menus=' . CGPC::do_clean($_POST['menu'], TYPE_INT) . '" target="_blank">Review</a></p><p>Next Step: <a class="btn btn-primary btn-sm" href="/backoffice/import-nutritionals-reciprofity">Import Nutritionals</a></p>');
					}
					else
					{
						$tpl->setStatusMsg('<p>Menu imported: <a class="btn btn-primary btn-sm" href="/backoffice/menu_inspector?menus=' . CGPC::do_clean($_POST['menu'], TYPE_INT) . '" target="_blank">Review</a></p><p>Next: <a class="btn btn-primary btn-sm" href="/backoffice/import-nutritionals-reciprofity">Import Nutritionals</a></p>');
					}
				}

				$commit = true;
			}
			catch (Exception $e)
			{
				$commit = false;

				$uberObject->query('ROLLBACK;');
				$tpl->setErrorMsg('Menu import failed: exception occurred</br>Reason: ' . $e->getMessage());
				CLog::RecordException($e);
			}

			if ($commit && !self::$testMode)
			{
				self::SSM_builder($tpl, CGPC::do_clean($_POST['menu'], TYPE_INT), $menu_item_id_array);
			}
		}

		/* Import menu fadmin template stuff below */
		// get fresh menu array state
		$menuArray = self::getMenuOptionsArray();

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "menu",
			CForm::options => $menuArray
		));

		$storeGet = new DAO();
		$storeGet->query("SELECT GROUP_CONCAT(id) AS ids FROM `store` WHERE (store_type = 'FRANCHISE' OR store_type = 'DISTRIBUTION_CENTER') AND (ssm_builder = '1' OR active = '1' OR show_on_customer_site = '1')  AND is_deleted = '0'");
		$storeGet->fetch();

		$this->ImportForm->DefaultValues['multi_store_select'] = $storeGet->ids;

		$this->ImportForm->addElement(array(
			CForm::type => CForm::ButtonMultiStore,
			CForm::name => 'multi_store_select',
			CForm::text => 'Stores',
			CForm::disabled => false,
			CForm::css_class => 'btn btn-primary'
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_menu_item_name",
			CForm::label => "Menu item title (A)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_menu_item_description",
			CForm::label => "Menu item description (B)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_menu_class",
			CForm::label => 'Menu class (E) - Must be labeled "Core", "EFL" or "Sides & Sweets"',
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_sub_category",
			CForm::label => "Sub-category (F) - For use with categorizing Sides &amp; Sweets",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_food_cost",
			CForm::label => "Food cost (G) - Changing the food cost on an existing menu will affect reporting, should not cause any technical issue",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price",
			CForm::label => "Base price (H) - Only for Sides &amp; Sweets. ",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_servings_per_order",
			CForm::label => "Servings per order (I) - Serving number that is used for inventory and sales requirements",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_1_md",
			CForm::label => "Tier 1 MD price (L) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_1_lg",
			CForm::label => "Tier 1 LG price (M) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_2_md",
			CForm::label => "Tier 2 MD price (N) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_2_lg",
			CForm::label => "Tier 2 LG price (O) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_3_md",
			CForm::label => "Tier 3 MD price (P) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_price_tier_3_lg",
			CForm::label => "Tier 3 LG price (Q) - Tier pricing reference. Updates do not affect existing store override price for already imported items, only updates the pricing reference.",
			CForm::css_class => 'import_option update_tier_pricing',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_serving_suggestions",
			CForm::label => "Serving suggestions (R)",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_station_number",
			CForm::label => "Station number (T) - Anything marked as FL will be auto labeled as Pre-Assembled",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_initial_menu",
			CForm::label => "Initial menu offer (W) - Set to yes if this is the first ever menu for this item, auto labeled as New",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_nutritional_serverings_per_container",
			CForm::label => "Nutritional Label Servings Per Container (AC) - Not used in inventory or order requirements, only for display regarding nutrition.",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_prep_time",
			CForm::label => "Prep time (AD)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_instructions",
			CForm::label => "Cooking instructions (AE)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_instructions_air_fryer",
			CForm::label => "Cooking instructions - Air Fryer (AG)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_instructions_crock_pot",
			CForm::label => "Cooking instructions - Crock Pot (AI)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_instructions_grill",
			CForm::label => "Cooking instructions - Grill (AK)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "import_best_prepared_by",
			CForm::label => "Best prepared by (AL)",
			CForm::css_class => 'import_option update_default',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "option_update_menu_label",
			CForm::label => "Update menu label - This is defined by (N), (Q) and (AH)",
			CForm::css_class => 'import_option',
			CForm::checked => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "option_update_pricing_tier_override_price",
			CForm::label => "Update override price - Force update override price for the selected tier pricing above, this will overwrite store's current pricing for that tier.",
			CForm::checked => false
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::File,
			CForm::name => "base_menu_import",
			CForm::css_class => 'btn btn-primary w-100',
			CForm::disabled => true
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "testmode",
			CForm::label => "Dry run only (preview what will happen during actual import)",
			CForm::checked => true,
		));

		$this->ImportForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "submit_menu_import",
			CForm::value => 'Submit',
			CForm::css_class => 'btn btn-primary',
			CForm::disabled => true
		));

		$tpl->assign('ImportForm', $this->ImportForm->Render());
	}

	private static function updateEntreeIDs()
	{
		foreach (self::$itemIDtoEntreeIDMap as $recipe_id => $versions)
		{
			// get all current sizes for the month that may have been entered and add them to the current imported items
			// this is to support the possibility of an HALF size being entered and then later a FULL size being entered
			$DAO_menu_item = DAO_CFactory::create('menu_item', true);
			$DAO_menu_item->recipe_id = $recipe_id;
			$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
			$DAO_menu_to_menu_item->menu_id = $_POST['menu'];
			$DAO_menu_to_menu_item->store_id = 'NULL';
			$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_menu_item->orderBy("FullFirst");

			if ($DAO_menu_item->find())
			{
				while ($DAO_menu_item->fetch())
				{
					$versions[$DAO_menu_item->id] = $DAO_menu_item->id;
				}
			}

			// get the affected menu items
			$DAO_menu_item = DAO_CFactory::create('menu_item');
			$DAO_menu_item->recipe_id = $recipe_id;
			$DAO_menu_item->whereAdd("menu_item.id IN (" . implode(',', $versions) . ")");
			$DAO_menu_item->orderBy("FullFirst");
			$DAO_menu_item->find();

			$entreeId = false;
			while ($DAO_menu_item->fetch())
			{
				// first result is the entree id, should be FULL at this point by the sort above
				if (empty($entreeId))
				{
					$entreeId = $DAO_menu_item->id;
				}

				if ($DAO_menu_item->entree_id != $entreeId)
				{
					$DA0_update_menu_item = $DAO_menu_item->cloneobj(false);
					$DA0_update_menu_item->entree_id = $entreeId;
					$DA0_update_menu_item->update($DAO_menu_item);
				}
			}
		}
	}

	private static function getMenuOptionsArray()
	{
		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT
			DISTINCT m.id,
			m.menu_name,
			COUNT(mtmi.menu_id) AS count
			FROM menu AS m
			LEFT JOIN menu_to_menu_item AS mtmi ON mtmi.menu_id = m.id
			WHERE m.is_deleted = '0'
			GROUP BY m.id
			ORDER BY m.id DESC
			LIMIT 6");

		$menuArray = array(0 => 'Select Menu');
		$menu_count = 0;

		while ($Menu->fetch())
		{
			$menuArray[$Menu->id] = array(
				'title' => $Menu->menu_name . (!empty($Menu->count) ? ' (imported)' : ''),
				'data' => array(
					'data-imported' => (!empty($Menu->count) ? 'true' : 'false')
				)
			);

			$menu_count++;
		}

		return $menuArray;
	}

	private static function stickIt($menu_id, $fields, $tpl, $doUpdate = false)
	{
		$menu_order_priority = 0;

		try
		{
			$targetItem = DAO_CFactory::create('menu_item');

			if (!empty($doUpdate))
			{
				$targetItem->id = $doUpdate[$fields['pricing_type']]['menu_item_id'];
				$targetItem->find(true);

				$orgItemFull = $targetItem->cloneObj(false);
			}

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if (CImportReciprofity::categoryLookup($fields[MENU_CLASS]))
				{
					$targetItem->menu_item_category_id = CImportReciprofity::categoryLookup($fields[MENU_CLASS]);

					$isStoreSpecial = false;
					if ($targetItem->menu_item_category_id == 4 && strtolower($fields[MENU_CLASS]) == 'efl')
					{
						$isStoreSpecial = true;
					}

					// Core item and station is FL (Pre-Assembled), set category to 4
					if ($targetItem->menu_item_category_id == 1 && strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_item_category_id = 4;
					}
				}
				else
				{
					throw new Exception("Category lookup failed: " . $fields[RECIPE_NAME] . " - " . $fields[MENU_CLASS]);
				}
			}

			// First setup pricing for reference if not S&S
			if ($targetItem->menu_item_category_id != 9)
			{
				$DAO_pricing = DAO_CFactory::create('pricing');
				$DAO_pricing->menu_id = $menu_id;
				$DAO_pricing->recipe_id = $fields['recipe_id'];
				$DAO_pricing->pricing_type = $fields['pricing_type'];

				// If they aren't found, insert them no matter what
				if (!$DAO_pricing->find())
				{
					switch ($fields['pricing_type'])
					{
						case CMenuItem::FULL:
							$DAO_pricing->tier = 1;
							$DAO_pricing->price = trim($fields[PRICE_TIER_1_LG]);
							$DAO_pricing->insert();
							$DAO_pricing->tier = 2;
							$DAO_pricing->price = trim($fields[PRICE_TIER_2_LG]);
							$DAO_pricing->insert();
							$DAO_pricing->tier = 3;
							$DAO_pricing->price = trim($fields[PRICE_TIER_3_LG]);
							$DAO_pricing->insert();
							break;
						case CMenuItem::HALF:
							$DAO_pricing->tier = 1;
							$DAO_pricing->price = trim($fields[PRICE_TIER_1_MD]);
							$DAO_pricing->insert();
							$DAO_pricing->tier = 2;
							$DAO_pricing->price = trim($fields[PRICE_TIER_2_MD]);
							$DAO_pricing->insert();
							$DAO_pricing->tier = 3;
							$DAO_pricing->price = trim($fields[PRICE_TIER_3_MD]);
							$DAO_pricing->insert();
							break;
					}
				}
				else
				{
					while ($DAO_pricing->fetch())
					{
						$DAO_pricing_org = $DAO_pricing->cloneObj();

						switch ($DAO_pricing->tier)
						{
							case 1:
								if ($fields['pricing_type'] == CMenuItem::FULL)
								{
									if (!empty($_POST['import_price_tier_1_lg']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_1_LG]);
									}
								}
								else if ($fields['pricing_type'] == CMenuItem::HALF)
								{
									if (!empty($_POST['import_price_tier_1_md']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_1_MD]);
									}
								}
								break;
							case 2:
								if ($fields['pricing_type'] == CMenuItem::FULL)
								{
									if (!empty($_POST['import_price_tier_2_lg']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_2_LG]);
									}
								}
								else if ($fields['pricing_type'] == CMenuItem::HALF)
								{
									if (!empty($_POST['import_price_tier_2_md']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_2_MD]);
									}
								}
								break;
							case 3:
								if ($fields['pricing_type'] == CMenuItem::FULL)
								{
									if (!empty($_POST['import_price_tier_3_lg']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_3_LG]);
									}
								}
								else if ($fields['pricing_type'] == CMenuItem::HALF)
								{
									if (!empty($_POST['import_price_tier_3_md']))
									{
										$DAO_pricing->price = trim($fields[PRICE_TIER_3_MD]);
									}
								}
								break;
						}

						$DAO_pricing->update($DAO_pricing_org);
					}
				}
			}

			$targetItem->recipe_id = $fields['recipe_id'];
			$targetItem->pricing_type = $fields['pricing_type'];
			$targetItem->sales_mix = !empty($fields[SALES_MIX]) ? str_replace('%', '', $fields[SALES_MIX]) / 100 : 0;

			// Determine menu label
			if (!$doUpdate || !empty($_POST['option_update_menu_label']))
			{
				if ($targetItem->menu_item_category_id == 1) // Core item
				{
					if (strtolower($fields[INITIAL_MENU_OFFER]) == 'yes' || (is_bool($fields[INITIAL_MENU_OFFER]) && $fields[INITIAL_MENU_OFFER]))
					{
						$targetItem->menu_label = 'New';
					}
					else if (strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_label = 'Pre-Assembled';
					}
					else if ($targetItem->sales_mix >= 0.08)
					{
						$targetItem->menu_label = 'Guest Favorite';
					}
					else
					{
						$targetItem->menu_label = 'NULL';
					}
				}
				else if ($targetItem->menu_item_category_id == 4) // efl item
				{
					$targetItem->menu_label = 'Pre-Assembled';
				}
				else
				{
					$targetItem->menu_label = 'NULL';
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_menu_item_name']))
			{
				$targetItem->menu_item_name = trim(CImportReciprofity::charConversions($fields[RECIPE_NAME]), '" ');

				// strip off size marker
				$sizeStrPos = strpos($targetItem->menu_item_name, "|");
				//Rule: all characters after the first pipe symbol is stripped off
				if ($sizeStrPos !== false)
				{
					$targetItem->menu_item_name = substr($targetItem->menu_item_name, 0, $sizeStrPos);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_menu_item_description']))
			{
				$targetItem->menu_item_description = CImportReciprofity::charConversions(trim($fields[DESCRIPTION], '" '));
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_food_cost']))
			{
				if (is_numeric($fields[FOOD_COST]))
				{
					// food cost is a percentage
					$targetItem->food_cost = CTemplate::moneyFormat($fields[FOOD_COST]);
				}
				else
				{
					throw new Exception("Problem with Food Cost : " . $fields[RECIPE_NAME] . " - " . $fields[FOOD_COST]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_price']))
			{
				$targetItem->price = 0.00;

				if ($targetItem->menu_item_category_id == 9)
				{
					if (is_numeric($fields[PRICE_6]))
					{
						$targetItem->price = $fields[PRICE_6];

						// If updating an existing item and the new price is higher than the old price,
						// unset override price for stores who have set their price below the new price
						if ($doUpdate && $targetItem->price > $orgItemFull->price)
						{
							$updateStore = DAO_CFactory::create('store');

							$updatePrice = DAO_CFactory::create('menu_to_menu_item', true);
							$updatePrice->menu_item_id = $orgItemFull->id;
							$updatePrice->selectAdd('store.store_name, store.email_address');
							$updatePrice->joinAdd($updateStore);
							$updatePrice->whereAdd("menu_to_menu_item.override_price < '" . $targetItem->price . "'");
							$updatePrice->find();

							while ($updatePrice->fetch())
							{
								$orgUpdatePrice = $updatePrice->cloneObj(false);
								$updatePrice->override_price = 'NULL';

								$updatePrice->update($orgUpdatePrice);

								$tpl->setStatusMsg("<p>Due to price increase, Override Price for <b>" . $targetItem->menu_item_name . "</b> has been reset for <b>" . $updatePrice->store_name . "</b>.</p>");
							}
						}
					}
					else
					{
						throw new Exception("Problem with Price : " . $fields[RECIPE_NAME] . " - " . $fields[PRICE_6]);
					}
				}
			}

			$targetItem->container_type = CMenuItem::NONE;

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_optional = 1;
			}
			else
			{
				$targetItem->is_optional = 0;
			}

			// Change: category 9 is no longer always controllable. Now only the 5 marked as is_preferred are controllable and they are visible by default
			if ($targetItem->menu_item_category_id != 9)
			{
				$targetItem->is_visibility_controllable = 1;
			}

			if ($targetItem->menu_item_category_id == 9)
			{
				//if (strtolower($fields[STATION_NUMBER]) == 'web')
				//{
				$targetItem->is_visibility_controllable = 1;
				//}
				if (!$doUpdate || !empty($_POST['import_sub_category']))
				{
					if (!empty($fields[SUB_CATEGORY]))
					{
						$targetItem->subcategory_label = CImportReciprofity::charConversions(trim($fields[SUB_CATEGORY], '" '));
					}
				}

				$menu_order_priority += 5;
				$targetItem->menu_order_priority = $menu_order_priority;

				$targetItem->is_chef_touched = 1;
			}
			else
			{
				$targetItem->is_chef_touched = 0;
			}

			if ($targetItem->menu_item_category_id == 10)
			{
				$targetItem->menu_program_id = 2;
			}
			else
			{
				$targetItem->menu_program_id = 1;
			}

			$targetItem->is_price_controllable = 1;

			if ($targetItem->menu_item_category_id == 4)
			{
				$targetItem->is_preassembled = 1;
			}
			else
			{
				$targetItem->is_preassembled = 0;
			}

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if ($isStoreSpecial)
				{
					$targetItem->is_store_special = 1;
				}
				else
				{
					$targetItem->is_store_special = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_side_dish = 1;
			}
			else
			{
				$targetItem->is_side_dish = 0;
			}

			if (!$doUpdate)
			{
				if ($targetItem->menu_item_category_id == 8)
				{
					$targetItem->is_bundle = 1;
				}
				else
				{
					$targetItem->is_bundle = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 6)
			{
				$targetItem->is_kids_choice = 1;
			}
			else
			{
				$targetItem->is_kids_choice = 0;
			}

			if ($targetItem->menu_item_category_id == 7)
			{
				$targetItem->is_menu_addon = 1;
			}
			else
			{
				$targetItem->is_menu_addon = 0;
			}

			$targetItem->SUPC_number = 'NULL';

			if (!$doUpdate || !empty($_POST['import_servings_per_order']))
			{
				if ($targetItem->menu_item_category_id == 9)
				{
					// sides & sweets servings is always 1
					$targetItem->servings_per_item = 1;
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_item = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_item = 6;
				}
			}

			if (!$doUpdate || !empty($_POST['import_nutritional_serverings_per_container']))
			{
				if (!empty($fields[DISPLAY_SERVINGS_PER_CONTAINER]) && is_numeric($fields[DISPLAY_SERVINGS_PER_CONTAINER]))
				{
					$targetItem->servings_per_container_display = $fields[DISPLAY_SERVINGS_PER_CONTAINER];
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_container_display = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_container_display = $targetItem->servings_per_item;
				}
			}

			if (!$doUpdate || !empty($_POST['import_station_number']))
			{
				if (!empty($fields[STATION_NUMBER]) && is_numeric($fields[STATION_NUMBER]))
				{
					$targetItem->station_number = $fields[STATION_NUMBER];
				}
				else
				{
					$targetItem->station_number = 'NULL';
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_instructions']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS]))
				{
					$targetItem->instructions = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_air_fryer']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_AIR_FRYER]))
				{
					$targetItem->instructions_air_fryer = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_AIR_FRYER], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_crock_pot']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_CROCK_POT]))
				{
					$targetItem->instructions_crock_pot = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_CROCK_POT], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_grill']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_GRILL]))
				{
					$targetItem->instructions_grill = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_GRILL], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_prep_time']))
			{
				if (!empty($fields[COOKING_TIME]))
				{
					$targetItem->prep_time = CImportReciprofity::charConversions($fields[COOKING_TIME], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_serving_suggestions']))
			{
				if (!empty($fields[SERVE_WITH]))
				{
					$targetItem->serving_suggestions = CImportReciprofity::charConversions($fields[SERVE_WITH], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_best_prepared_by']))
			{
				if (!empty($fields[PREPARE_BY]))
				{
					$targetItem->best_prepared_by = CImportReciprofity::charConversions($fields[PREPARE_BY], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			//
			// do insert or update of menu_item
			//

			if (empty($doUpdate))
			{
				// set temporary entree id
				$targetItem->entree_id = 0;
				// insert new item
				$targetItem->insert();

				if (!isset(self::$itemIDtoEntreeIDMap[$targetItem->recipe_id]))
				{
					self::$itemIDtoEntreeIDMap[$targetItem->recipe_id] = array();
				}

				self::$itemIDtoEntreeIDMap[$targetItem->recipe_id][$targetItem->id] = $targetItem->id;

				self::$changelog[$targetItem->id] = array(
					'menu_item_id' => $targetItem->id,
					'event' => 'added',
					'message' => $targetItem->menu_item_name,
					'build_ssm' => true
				);

				//always add an inventory row - we will no longer run the menu import so they should be created here with initial values all zero
				self::addInventoryRowsIfNeeded($targetItem, $menu_id);
			}
			else
			{
				// existing item so update the existing row
				$targetItem->update($orgItemFull);

				// update any EFL menu items copied from this menu item
				$updateCopiedEFL = DAO_CFactory::create('menu_item');
				$updateCopiedEFL->copied_from = $orgItemFull->id;
				$updateCopiedEFL->find();

				while ($updateCopiedEFL->fetch())
				{
					$orgUpdateCopiedEFL = $updateCopiedEFL->cloneObj(false);

					foreach (DAO_CFactory::create('menu_item')->table() as $key => $value)
					{
						switch ($key)
						{
							// leave the these as they were set from CMenuItem::duplicate()
							case 'entree_id':
							case 'copied_from':
							case 'menu_item_category_id':
							case 'is_store_special':
							case 'station_number':
							case 'is_preassembled':
							case 'menu_label':
							case 'is_key_menu_push_item':
							case 'is_visibility_controllable':
							case 'timestamp_created':
							case 'created_by':
								// can't update menu item id, it would break the update
							case 'id':
								break;
							// otherwise update with the target item settings
							default:
								$updateCopiedEFL->{$key} = $targetItem->{$key};
								break;
						}
					}

					$updateCopiedEFL->update($orgUpdateCopiedEFL);
				}

				self::$changelog[$targetItem->id] = array(
					'menu_item_id' => $targetItem->id,
					'event' => 'updated',
					'message' => $targetItem->menu_item_name,
					'build_ssm' => true
				);

				//always add an inventory row - we will no longer run the menu import so they should be created here with initial values all zero
				self::addInventoryRowsIfNeeded($targetItem, $menu_id);
			}

			if (empty($doUpdate))
			{
				// if added entree item, add menu to menu item
				$menuMapFull = DAO_CFactory::create('menu_to_menu_item');
				$menuMapFull->menu_id = $menu_id;
				$menuMapFull->menu_item_id = $targetItem->id;

				if ($targetItem->menu_item_category_id == 8)
				{
					$menuMapFull->is_visible = 0;
				}

				if ($targetItem->menu_item_category_id == 9)
				{
					if (strtolower($fields[STATION_NUMBER]) == 'web')
					{
						$menuMapFull->is_visible = 1;
					}
					else
					{
						$menuMapFull->is_visible = 0;
					}
				}

				// determine subsort
				if (!empty($fields[MENU_SUBSORT]))
				{
					$subsort_full = $fields[MENU_SUBSORT] * 6; // *6 leaves gaps in menu_order_value to accommodate menu adjustments
				}
				else
				{
					$subsort_full = 0;
				}

				if ($targetItem->menu_item_category_id == 1 || strtolower($fields[STATION_NUMBER]) == 'fl') // Core items
				{
					$subsort_full += 100;
				}
				else if ($targetItem->menu_item_category_id == 4) // EFL
				{
					$subsort_full += 1000;
				}
				else if ($targetItem->menu_item_category_id == 9) // Finishing Touch or Side Station
				{
					$subsort_full += 10000;
				}

				// TODO:  use pricing type for relative sort of different sizes of the same recipe
				if ($targetItem->pricing_type == CMenuItem::FULL)
				{
					$subsort_full++;
				}

				$menuMapFull->menu_order_value = $subsort_full;
				$menuMapFull->featuredItem = 0;

				$menuMapFull->insert();
			}

			return $targetItem->id;
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Menu import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}

	private static function addInventoryRowsIfNeeded($targetItem, $menu_id)
	{
		// Check for menu version
		// INVENTORY TOUCH POINT 15

		$storeSet = explode(',', $_POST['multi_store_select']);

		foreach ($storeSet as $thisStore)
		{
			$menuInv = DAO_CFactory::create('menu_item_inventory');

			$menuInv->menu_id = $menu_id;
			$menuInv->recipe_id = $targetItem->recipe_id;
			$menuInv->store_id = $thisStore;
			$menuInv->find(true);

			if ($menuInv->N == 0)
			{
				$menuInv->recipe_id = $targetItem->recipe_id;
				$menuInv->menu_id = $menu_id;
				$menuInv->store_id = $thisStore;
				$menuInv->initial_inventory = 0;
				$menuInv->override_inventory = 0;

				$menuInv->insert();
			}
		}
	}

	private static function SSM_builder($tpl, $menu_id, $menu_item_id_array)
	{
		$transaction_object = DAO_CFActory::create('menu_item');
		$transaction_object->query('START TRANSACTION;');

		try
		{
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->whereAdd("store.id in (" . $_POST['multi_store_select'] . ")");
			$DAO_store->whereAdd("store.store_type = '" . CStore::FRANCHISE . "' OR store.store_type = '" . CStore::DISTRIBUTION_CENTER . "'");
			$DAO_store->whereAdd("store.ssm_builder = '1' OR store.active = '1' OR store.show_on_customer_site = '1'");
			$DAO_store->find();

			while ($DAO_store->fetch())
			{
				$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item', true);
				$DAO_menu_to_menu_item->menu_id = $menu_id;
				$DAO_menu_to_menu_item->store_id = 'NULL';
				$DAO_menu_item = DAO_CFactory::create('menu_item');
				$DAO_menu_item->whereAdd("menu_item.entree_id IN (" . implode(',', $menu_item_id_array) . ")");
				$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_menu_item);
				$DAO_menu_to_menu_item->find();

				while ($DAO_menu_to_menu_item->fetch())
				{
					$new_DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item', true);
					$new_DAO_menu_to_menu_item->menu_id = $menu_id;
					$new_DAO_menu_to_menu_item->menu_item_id = $DAO_menu_to_menu_item->menu_item_id;
					$new_DAO_menu_to_menu_item->store_id = $DAO_store->id;
					$new_DAO_menu_to_menu_item->joinAddWhereAsOn(DAO_CFactory::create('menu_item'));

					if (!$new_DAO_menu_to_menu_item->find(true))
					{
						$new_DAO_menu_to_menu_item->menu_order_value = $DAO_menu_to_menu_item->menu_order_value;
						$new_DAO_menu_to_menu_item->featuredItem = 0;

						if ($DAO_menu_to_menu_item->DAO_menu_item->menu_item_category_id != 9)
						{
							$DAO_pricing = DAO_CFactory::create('pricing');
							$DAO_pricing->menu_id = $menu_id;
							$DAO_pricing->recipe_id = $DAO_menu_to_menu_item->DAO_menu_item->recipe_id;
							$DAO_pricing->tier = $DAO_store->core_pricing_tier;
							$DAO_pricing->pricing_type = $DAO_menu_to_menu_item->DAO_menu_item->pricing_type;

							if ($DAO_pricing->find(true))
							{
								$new_DAO_menu_to_menu_item->override_price = $DAO_pricing->price;
							}
						}

						if ($DAO_store->store_type == CStore::DISTRIBUTION_CENTER)
						{
							$new_DAO_menu_to_menu_item->is_visible = 1;
						}
						else
						{
							if ($DAO_menu_to_menu_item->DAO_menu_item->is_store_special)
							{
								$new_DAO_menu_to_menu_item->is_visible = 0;
							}
							else
							{
								$new_DAO_menu_to_menu_item->is_visible = $DAO_menu_to_menu_item->DAO_menu_item->is_visible;
							}
						}

						$new_DAO_menu_to_menu_item->insert();
					}
					else if (isset($_POST['option_update_pricing_tier_override_price']))
					{
						$org_DAO_menu_to_menu_item = $new_DAO_menu_to_menu_item->cloneObj();

						$DAO_pricing = DAO_CFactory::create('pricing');
						$DAO_pricing->menu_id = $menu_id;
						$DAO_pricing->recipe_id = $DAO_menu_to_menu_item->DAO_menu_item->recipe_id;
						$DAO_pricing->tier = $DAO_store->core_pricing_tier;
						$DAO_pricing->pricing_type = $DAO_menu_to_menu_item->DAO_menu_item->pricing_type;

						if ($DAO_pricing->find(true))
						{
							$updateOverride = false;

							switch ($new_DAO_menu_to_menu_item->DAO_menu_item->pricing_type)
							{
								case CMenuItem::FULL:
									switch ($DAO_store->core_pricing_tier)
									{
										case 1:
											if (isset($_POST['import_price_tier_1_lg']))
											{
												$updateOverride = true;
											}
											break;
										case 2:
											if (isset($_POST['import_price_tier_2_lg']))
											{
												$updateOverride = true;
											}
											break;
										case 3:
											if (isset($_POST['import_price_tier_3_lg']))
											{
												$updateOverride = true;
											}
											break;
									}
									break;
								case CMenuItem::HALF:
									switch ($DAO_store->core_pricing_tier)
									{
										case 1:
											if (isset($_POST['import_price_tier_1_md']))
											{
												$updateOverride = true;
											}
											break;
										case 2:
											if (isset($_POST['import_price_tier_2_md']))
											{
												$updateOverride = true;
											}
											break;
										case 3:
											if (isset($_POST['import_price_tier_3_md']))
											{
												$updateOverride = true;
											}
											break;
									}
									break;
							}

							if ($updateOverride)
							{
								if ($DAO_pricing->price > $new_DAO_menu_to_menu_item->override_price)
								{
								//	$tpl->setStatusMsg("<p>Due to price increase, Override Price for <b>" . $new_DAO_menu_to_menu_item->DAO_menu_item->menu_item_name . "</b> has been reset for <b>" . $DAO_pricing->price . "</b>.</p>");
								}

								$new_DAO_menu_to_menu_item->override_price = $DAO_pricing->price;

								$new_DAO_menu_to_menu_item->update($org_DAO_menu_to_menu_item);
							}
						}
					}
				}
			}

			$transaction_object->query('COMMIT;');
			//$tpl->setStatusMsg('SSM build completed successfully.');
		}
		catch (exception $e)
		{
			$transaction_object->query('ROLLBACK;');
			$tpl->setErrorMsg('SSM build failed: exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}

	private static function compareIt($menu_id, $fields, $tpl, $doUpdate = false)
	{
		$menu_order_priority = 0;

		try
		{
			$targetItem = DAO_CFactory::create('menu_item');

			if (!empty($doUpdate))
			{
				$targetItem->id = $doUpdate[$fields['pricing_type']]['menu_item_id'];
				$targetItem->find(true);

				$orgItemFull = $targetItem->cloneObj(false);
			}

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if (CImportReciprofity::categoryLookup($fields[MENU_CLASS]))
				{
					$targetItem->menu_item_category_id = CImportReciprofity::categoryLookup($fields[MENU_CLASS]);

					$isStoreSpecial = false;
					if ($targetItem->menu_item_category_id == 4 && strtolower($fields[MENU_CLASS]) == 'efl')
					{
						$isStoreSpecial = true;
					}

					// Core item and station is FL (Pre-Assembled), set category to 4
					if ($targetItem->menu_item_category_id == 1 && strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_item_category_id = 4;
					}
				}
				else
				{
					throw new Exception("Category lookup failed: " . $fields[RECIPE_NAME] . " - " . $fields[MENU_CLASS]);
				}
			}

			$targetItem->recipe_id = $fields['recipe_id'];
			$targetItem->pricing_type = $fields['pricing_type'];
			$targetItem->sales_mix = str_replace('%', '', $fields[SALES_MIX]) / 100;

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_menu_item_name']))
			{
				$targetItem->menu_item_name = trim(CImportReciprofity::charConversions($fields[RECIPE_NAME]), '" ');
			}

			// strip off size marker
			$sizeStrPos = strpos($targetItem->menu_item_name, "|");
			//Rule: all characters after the first pipe symbol is stripped off
			if ($sizeStrPos !== false)
			{
				$targetItem->menu_item_name = substr($targetItem->menu_item_name, 0, $sizeStrPos);
			}

			// Determine menu label
			if (!$doUpdate || !empty($_POST['option_update_menu_label']))
			{
				if ($targetItem->menu_item_category_id == 1) // Core item
				{
					if (strtolower($fields[INITIAL_MENU_OFFER]) == 'yes' || (is_bool($fields[INITIAL_MENU_OFFER]) && $fields[INITIAL_MENU_OFFER]))
					{
						$targetItem->menu_label = 'New';
					}
					else if (strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_label = 'Pre-Assembled';
					}
					else if ($targetItem->sales_mix >= 0.08)
					{
						$targetItem->menu_label = 'Guest Favorite';
					}
					else
					{
						$targetItem->menu_label = 'NULL';
					}
				}
				else if ($targetItem->menu_item_category_id == 4) // efl item
				{
					$targetItem->menu_label = 'Pre-Assembled';
				}
				else
				{
					$targetItem->menu_label = 'NULL';
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_menu_item_description']))
			{
				$targetItem->menu_item_description = CImportReciprofity::charConversions(trim($fields[DESCRIPTION], '" '));
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_food_cost']))
			{
				if (is_numeric($fields[FOOD_COST]))
				{
					// food cost is a percentage
					$targetItem->food_cost = CTemplate::moneyFormat($fields[FOOD_COST]);
				}
				else
				{
					throw new Exception("Problem with Food Cost : " . $fields[RECIPE_NAME] . " - " . $fields[FOOD_COST]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_price']))
			{
				$targetItem->price = 0.00;

				if ($targetItem->menu_item_category_id == 9)
				{
					if (is_numeric($fields[PRICE_6]))
					{
						$targetItem->price = $fields[PRICE_6];
					}
					else
					{
						throw new Exception("Problem with Price : " . $fields[RECIPE_NAME] . " - " . $fields[PRICE_6]);
					}
				}
			}

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_optional = 1;
			}
			else
			{
				$targetItem->is_optional = 0;
			}

			// Change: category 9 is no longer always controllable. Now only the 5 marked as is_preferred are controllable and they are visible by default
			if ($targetItem->menu_item_category_id != 9)
			{
				$targetItem->is_visibility_controllable = 1;
			}

			if ($targetItem->menu_item_category_id == 9)
			{
				$targetItem->is_visibility_controllable = 1;

				if (!$doUpdate || !empty($_POST['import_sub_category']))
				{
					if (!empty($fields[SUB_CATEGORY]))
					{
						$targetItem->subcategory_label = CImportReciprofity::charConversions(trim($fields[SUB_CATEGORY], '" '));
					}
				}

				$menu_order_priority += 5;
				$targetItem->menu_order_priority = $menu_order_priority;

				$targetItem->is_chef_touched = 1;
			}
			else
			{
				$targetItem->is_chef_touched = 0;
			}

			if ($targetItem->menu_item_category_id == 10)
			{
				$targetItem->menu_program_id = 2;
			}
			else
			{
				$targetItem->menu_program_id = 1;
			}

			$targetItem->is_price_controllable = 1;

			if ($targetItem->menu_item_category_id == 4)
			{
				$targetItem->is_preassembled = 1;
			}
			else
			{
				$targetItem->is_preassembled = 0;
			}

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if ($isStoreSpecial)
				{
					$targetItem->is_store_special = 1;
				}
				else
				{
					$targetItem->is_store_special = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_side_dish = 1;
			}
			else
			{
				$targetItem->is_side_dish = 0;
			}

			if (!$doUpdate)
			{
				if ($targetItem->menu_item_category_id == 8)
				{
					$targetItem->is_bundle = 1;
				}
				else
				{
					$targetItem->is_bundle = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 6)
			{
				$targetItem->is_kids_choice = 1;
			}
			else
			{
				$targetItem->is_kids_choice = 0;
			}

			if ($targetItem->menu_item_category_id == 7)
			{
				$targetItem->is_menu_addon = 1;
			}
			else
			{
				$targetItem->is_menu_addon = 0;
			}

			$targetItem->SUPC_number = 'NULL';

			if (!$doUpdate || !empty($_POST['import_servings_per_order']))
			{
				if ($targetItem->menu_item_category_id == 9)
				{
					$targetItem->servings_per_item = 1;
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_item = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_item = 6;
				}
			}

			if (!$doUpdate || !empty($_POST['import_nutritional_serverings_per_container']))
			{
				if (!empty($fields[DISPLAY_SERVINGS_PER_CONTAINER]) && is_numeric($fields[DISPLAY_SERVINGS_PER_CONTAINER]))
				{
					$targetItem->servings_per_container_display = $fields[DISPLAY_SERVINGS_PER_CONTAINER];
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_container_display = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_container_display = $targetItem->servings_per_item;
				}
			}

			if (!$doUpdate || !empty($_POST['import_station_number']))
			{
				if (!empty($fields[STATION_NUMBER]) && is_numeric($fields[STATION_NUMBER]))
				{
					$targetItem->station_number = $fields[STATION_NUMBER];
				}
				else
				{
					$targetItem->station_number = 'NULL';
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_instructions']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS]))
				{
					$targetItem->instructions = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_air_fryer']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_AIR_FRYER]))
				{
					$targetItem->instructions_air_fryer = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_AIR_FRYER], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_crock_pot']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_CROCK_POT]))
				{
					$targetItem->instructions_crock_pot = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_CROCK_POT], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			if (!$doUpdate || !empty($_POST['import_instructions_grill']))
			{
				if (!empty($fields[COOKING_INSTRUCTIONS_GRILL]))
				{
					$targetItem->instructions_grill = CImportReciprofity::charConversions($fields[COOKING_INSTRUCTIONS_GRILL], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_prep_time']))
			{
				if (!empty($fields[COOKING_TIME]))
				{
					$targetItem->prep_time = CImportReciprofity::charConversions($fields[COOKING_TIME], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_serving_suggestions']))
			{
				if (!empty($fields[SERVE_WITH]))
				{
					$targetItem->serving_suggestions = CImportReciprofity::charConversions($fields[SERVE_WITH], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			// must be inserted if it's not an update
			if (!$doUpdate || !empty($_POST['import_best_prepared_by']))
			{
				if (!empty($fields[PREPARE_BY]))
				{
					$targetItem->best_prepared_by = CImportReciprofity::charConversions($fields[PREPARE_BY], $fields[RECIPE_ID], $fields[RECIPE_NAME]);
				}
			}

			/*
			require_once 'includes/class.Diff.php';

			$a = "I have a rather large request\nsame";
			$b = "I have a rather big request\nsame";

			$outEqual = Diff::toHTML_DD(Diff::compare($a, $b, true));
			$this->_template->setStatusMsg($outEqual);
			*/

			//
			// do insert or update of menu_item
			//

			// existing item so update the existing row
			$targetItem->id = $doUpdate[$targetItem->pricing_type]['menu_item_id'];
			$targetItem->has_included_side = 0; // unused so set to match org item
			$targetItem->entree_id = $doUpdate[$targetItem->pricing_type]['entree_id'];

			$orgFullArr = DAO::getCompressedArrayFromDAO($orgItemFull, true, true);
			$targetFullArr = DAO::getCompressedArrayFromDAO($targetItem, true, true);
			$diff = self::compareMenuItems($orgFullArr, $targetFullArr);

			if (empty($diff))
			{
				self::$changelog[$targetItem->id] = array(
					'menu_item_id' => $targetItem->id,
					'event' => "{$orgItemFull->pricing_type} version exists - no changes",
					'item' => $targetItem->menu_item_name,
					'build_ssm' => false
				);
			}
			else
			{
				self::$changelog[$targetItem->id] = array(
					'menu_item_id' => $targetItem->id,
					'event' => "{$orgItemFull->pricing_type} version exists - will update",
					'item' => $targetItem->menu_item_name,
					'diff' => $diff,
					'build_ssm' => false
				);
			}

			return $targetItem->id;
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Menu import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}

	static $fieldFilter = array(
		'timestamp_updated',
		'timestamp_created',
		'created_by',
		'updated_by'
	);
	static $fullDiffList = array(
		'menu_item_name',
		'menu_item_description',
		'subcategory_label'
	);

	static function hasDiff($diff_result)
	{
		foreach ($diff_result as $thisChar)
		{
			if ($thisChar[1] != 0)
			{
				return true;
			}
		}

		return false;
	}

	static function compareMenuItems($orgItem, $newItem)
	{
		$retVal = array();

		foreach ($orgItem as $k => $v)
		{

			if (!in_array($k, self::$fieldFilter))
			{

				if (in_array($k, self::$fullDiffList))
				{

					$res = Diff::compare($v, $newItem[$k], true);
					if (self::hasDiff($res))
					{
						$fullDiff = Diff::toHTML_DD($res);
						$retVal[] = array(
							'name' => $k,
							'org' => $v,
							'diff' => $fullDiff
						);
					}
				}
				else if ($v != $newItem[$k])
				{
					$retVal[] = array(
						'name' => $k,
						'org' => $v,
						'new' => $newItem[$k]
					);
				}
			}
		}

		return $retVal;
	}

	private static function logIt($menu_id, $fields, $tpl, $count, $doUpdate = false)
	{
		$menu_order_priority = 0;

		try
		{
			$targetItem = DAO_CFactory::create('menu_item');

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if (CImportReciprofity::categoryLookup($fields[MENU_CLASS]))
				{
					$targetItem->menu_item_category_id = CImportReciprofity::categoryLookup($fields[MENU_CLASS]);

					$isStoreSpecial = false;
					if ($targetItem->menu_item_category_id == 4 && strtolower($fields[MENU_CLASS]) == 'efl')
					{
						$isStoreSpecial = true;
					}

					// Core item and station is FL (Pre-Assembled), set category to 4
					if ($targetItem->menu_item_category_id == 1 && strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_item_category_id = 4;
					}
				}
				else
				{
					throw new Exception("Category lookup failed: " . $fields[RECIPE_NAME] . " - " . $fields[MENU_CLASS]);
				}
			}

			$targetItem->recipe_id = $fields['recipe_id'];
			$targetItem->pricing_type = $fields['pricing_type'];

			// Determine menu label
			if (!$doUpdate || !empty($_POST['option_update_menu_label']))
			{
				if ($targetItem->menu_item_category_id == 1) // Core item
				{
					if (strtolower($fields[INITIAL_MENU_OFFER]) == 'yes' || (is_bool($fields[INITIAL_MENU_OFFER]) && $fields[INITIAL_MENU_OFFER]))
					{
						$targetItem->menu_label = 'New';
					}
					else if (strtolower($fields[STATION_NUMBER]) == 'fl')
					{
						$targetItem->menu_label = 'Pre-Assembled';
					}
					else if ($targetItem->sales_mix >= 0.08)
					{
						$targetItem->menu_label = 'Guest Favorite';
					}
					else
					{
						$targetItem->menu_label = 'NULL';
					}
				}
				else if ($targetItem->menu_item_category_id == 4) // efl item
				{
					$targetItem->menu_label = 'Pre-Assembled';
				}
				else
				{
					$targetItem->menu_label = 'NULL';
				}
			}

			$targetItem->menu_item_name = trim(CImportReciprofity::charConversions($fields[RECIPE_NAME]), '" ');
			// strip off size marker
			$sizeStrPos = strpos($targetItem->menu_item_name, "|");
			//Rule: all characters after the first pipe symbol is stripped off
			if ($sizeStrPos !== false)
			{
				$targetItem->menu_item_name = substr($targetItem->menu_item_name, 0, $sizeStrPos);
			}

			$targetItem->menu_item_description = CImportReciprofity::charConversions(trim($fields[DESCRIPTION], '" '));

			if (is_numeric($fields[FOOD_COST]))
			{
				$targetItem->food_cost = CTemplate::moneyFormat(($fields[FOOD_COST] / 100) * $fields[PRICE_6]);
			}
			else
			{
				throw new Exception("Problem with Food Cost : " . $fields[RECIPE_NAME] . " - " . $fields[FOOD_COST]);
			}

			if (is_numeric($fields[PRICE_6]))
			{
				$targetItem->price = $fields[PRICE_6];
			}
			else
			{
				throw new Exception("Problem with Price : " . $fields[RECIPE_NAME] . " - " . $fields[PRICE_6]);
			}

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_optional = 1;
			}
			else
			{
				$targetItem->is_optional = 0;
			}

			// Change: category 9 is no longer always controllable. Now only the 5 marked as is_preferred are controllable and they are visible by default
			if ($targetItem->menu_item_category_id == 4 || $targetItem->menu_item_category_id == 5 || $targetItem->menu_item_category_id == 7 || $targetItem->menu_item_category_id == 8)
			{
				$targetItem->is_visibility_controllable = 1;
			}
			else
			{
				$targetItem->is_visibility_controllable = 0;
			}

			if ($targetItem->menu_item_category_id == 9)
			{
				$targetItem->is_visibility_controllable = 1;

				if (!$doUpdate || !empty($_POST['import_sub_category']))
				{
					if (!empty($fields[SUB_CATEGORY]))
					{
						$targetItem->subcategory_label = CImportReciprofity::charConversions(trim($fields[SUB_CATEGORY], '" '));
					}
				}

				$menu_order_priority += 5;
				$targetItem->menu_order_priority = $menu_order_priority;

				$targetItem->is_chef_touched = 1;
			}
			else
			{
				$targetItem->is_chef_touched = 0;
			}

			if ($targetItem->menu_item_category_id == 10)
			{
				$targetItem->menu_program_id = 2;
			}
			else
			{
				$targetItem->menu_program_id = 1;
			}

			$targetItem->is_price_controllable = 1;

			if ($targetItem->menu_item_category_id == 4)
			{
				$targetItem->is_preassembled = 1;
			}
			else
			{
				$targetItem->is_preassembled = 0;
			}

			if (!$doUpdate || !empty($_POST['import_menu_class']))
			{
				if ($isStoreSpecial)
				{
					$targetItem->is_store_special = 1;
				}
				else
				{
					$targetItem->is_store_special = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 5)
			{
				$targetItem->is_side_dish = 1;
			}
			else
			{
				$targetItem->is_side_dish = 0;
			}

			if (!$doUpdate)
			{
				if ($targetItem->menu_item_category_id == 8)
				{
					$targetItem->is_bundle = 1;
				}
				else
				{
					$targetItem->is_bundle = 0;
				}
			}

			if ($targetItem->menu_item_category_id == 6)
			{
				$targetItem->is_kids_choice = 1;
			}
			else
			{
				$targetItem->is_kids_choice = 0;
			}

			if ($targetItem->menu_item_category_id == 7)
			{
				$targetItem->is_menu_addon = 1;
			}
			else
			{
				$targetItem->is_menu_addon = 0;
			}

			$targetItem->SUPC_number = 'NULL';

			if (!$doUpdate || !empty($_POST['import_servings_per_order']))
			{
				if ($targetItem->menu_item_category_id == 9)
				{
					$targetItem->servings_per_item = 1;
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_item = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_item = 6;
				}
			}

			if (!$doUpdate || !empty($_POST['import_nutritional_serverings_per_container']))
			{
				if (!empty($fields[DISPLAY_SERVINGS_PER_CONTAINER]) && is_numeric($fields[DISPLAY_SERVINGS_PER_CONTAINER]))
				{
					$targetItem->servings_per_container_display = $fields[DISPLAY_SERVINGS_PER_CONTAINER];
				}
				else if (!empty($fields[SERVING_PER_ORDER]) && is_numeric($fields[SERVING_PER_ORDER]))
				{
					$targetItem->servings_per_container_display = $fields[SERVING_PER_ORDER];
				}
				else
				{
					$targetItem->servings_per_container_display = $targetItem->servings_per_item;
				}
			}

			if (!$doUpdate || !empty($_POST['import_station_number']))
			{
				if (!empty($fields[STATION_NUMBER]) && is_numeric($fields[STATION_NUMBER]))
				{
					$targetItem->station_number = $fields[STATION_NUMBER];
				}
				else
				{
					$targetItem->station_number = 'NULL';
				}
			}

			$targetItem->has_included_side = 0; // unused so set to match org item
			$targetFullArr = DAO::getCompressedArrayFromDAO($targetItem, true, true);

			$sizeStr = "";
			if ($targetItem->pricing_type == CMenuItem::FULL)
			{
				$sizeStr = 'large version - will insert';
			}
			else if ($targetItem->pricing_type == CMenuItem::HALF)
			{
				$sizeStr = 'medium version - will insert';
			}
			else if ($targetItem->pricing_type == CMenuItem::TWO)
			{
				$sizeStr = '2-serving version - will insert';
			}
			else if ($targetItem->pricing_type == CMenuItem::FOUR)
			{
				$sizeStr = '4-serving version - will insert';
			}
			else
			{
				$sizeStr = 'full version - will insert';
			}

			self::$changelog[$count] = array(
				'menu_item_id' => $targetItem->id,
				'item' => $targetItem->menu_item_name,
				'event' => $sizeStr,
				'message' => print_r($targetFullArr, true),
				'build_ssm' => false
			);

			return $targetItem->id;
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Menu import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}
}

?>