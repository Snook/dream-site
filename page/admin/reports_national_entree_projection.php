<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('page/admin/menu_inventory_mgr.php');

class page_admin_reports_national_entree_projection extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}


	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$Form = new CForm();
		$Form->Repost = false;
		$tpl = CApp::instance()->template();

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => 'Run Report'
		));

		$month_array = array(
			'1' => 'January',
			'2' => 'February',
			'3' => 'March',
			'4' => 'April',
			'5' => 'May',
			'6' => 'June',
			'7' => 'July',
			'8' => 'August',
			'9' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		$monthnum = date("n");
		$year = date("Y");

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_001",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::default_value => $monthnum,
			CForm::options => $month_array,
			CForm::name => 'month_popup'
		));

		if ($Form->value('report_submit') || (!empty($_REQUEST["print_all"]) && $_REQUEST["print_all"]) || (!empty($_REQUEST["print"]) && $_REQUEST["print"]))
		{

			$dry_run = false;
			if (isset($_POST['dry_run']))
			{
				$dry_run = true;
				$statsArray = array('finalized' => array(), 'not_final' => array());
				$arrTemplate = array('store_name' => "", 'state' => "", "email" => "", 'saved_guest_projection' => 'no', 'standard_orders' => 0, 'ws_orders' => 0, 'starter_pack_orders' => 0, 'has_inventory' => false);

			}

			$test_stores = false;
			if (isset($_POST['test_stores']))
			{
				$test_stores = true;
			}


			$nationalProjection = array();

			$month = $_REQUEST["month_popup"];
			$year = $_REQUEST["year_field_001"];

			$anchorDate = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
			$chosenMenu = CMenu::getMenuByAnchorDate($anchorDate);
			$menu_id = $chosenMenu['id'];

			if (!empty($menu_id) && $menu_id > 244)
			{


				$menuInfoArray = array();
				$recipeIDs = array();
				$natSalesMix = array();

				// step 1:  Get recipe ids for the core menu

				$testStoreArray = CStore::getTestStoresArray($menu_id);

				if (empty($testStoreArray) && !$test_stores)
				{
					$nonTestStoreDAO = new DAO();
					$nonTestStoreDAO->query("select id from store where active = 1 limit 1");
					$nonTestStoreDAO->fetch();
					$key_store = $nonTestStoreDAO->id;
				}
				else if (!empty($testStoreArray) && !$test_stores)
				{
					$nonTestStoreDAO = new DAO();
					$nonTestStoreDAO->query("select id from store where active = 1 and id not in (" . implode(",", $testStoreArray) . ") limit 1");
					$nonTestStoreDAO->fetch();
					$key_store = $nonTestStoreDAO->id;
				}
				else if (empty($testStoreArray) && $test_stores)
				{
					$nonTestStoreDAO = new DAO();
					$nonTestStoreDAO->query("select id from store where active = 1 limit 1");
					$nonTestStoreDAO->fetch();
					$key_store = $nonTestStoreDAO->id;
					$test_stores = false;
				}
				else
				{
					$key_store = array_shift($testStoreArray);
				}

				// Note:  the query uses Mill Creek rather than a null store since using NULL will return both the Test and non-test menu items.
				$coreMenu = new DAO();
				$coreMenu->query("select mi.id, mi.recipe_id, 
						mi.menu_item_name,
						mi.entree_id, 
						mi.sales_mix,
			       		mi.pricing_type,
						bmi.id as in_intro,
       					bmi2.id as in_taste,
       					mi.servings_per_item
					from menu_item mi
				join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and mmi.store_id = $key_store and mmi.menu_id = $menu_id and mmi.is_deleted = 0
								and mi.is_store_special = 0 and mi.menu_item_category_id <> 9
				left join bundle b on b.menu_id = $menu_id and b.bundle_type = 'TV_OFFER' and b.is_deleted = 0
				left join bundle_to_menu_item bmi on bmi.bundle_id = b.id and bmi.menu_item_id = mi.id
				left join bundle b2 on b2.menu_id = $menu_id and b2.bundle_type = 'DREAM_TASTE' and b2.is_deleted = 0 and b2.bundle_name = 'Meal Prep Workshop'
				left join bundle_to_menu_item bmi2 on bmi2.bundle_id = b2.id and bmi2.menu_item_id = mi.id
					where mi.is_deleted = 0
						order by entree_id, pricing_type");
				while ($coreMenu->fetch())
				{
					if (!isset($menuInfoArray[$coreMenu->recipe_id]))
					{
						$menuInfoArray[$coreMenu->recipe_id] = array();
						$menuInfoArray[$coreMenu->recipe_id]['sizes'] = array();
						$menuInfoArray[$coreMenu->recipe_id]['in_intro'] = false;
						$menuInfoArray[$coreMenu->recipe_id]['in_taste'] = false;
					}

					$menuInfoArray[$coreMenu->recipe_id]['name'] = $coreMenu->menu_item_name;
					$menuInfoArray[$coreMenu->recipe_id]['entree_id'] = $coreMenu->entree_id;
					if ($coreMenu->pricing_type == CMenuItem::FULL)
					{
						$menuInfoArray[$coreMenu->recipe_id]['nat_sales_mix'] = $coreMenu->sales_mix;
					}

					if (!$menuInfoArray[$coreMenu->recipe_id]['in_intro'] && $coreMenu->in_intro && !$test_stores)
					{
						$menuInfoArray[$coreMenu->recipe_id]['in_intro'] = true;
					}

					if (!$menuInfoArray[$coreMenu->recipe_id]['in_taste'] && $coreMenu->in_taste &&  !$test_stores)
					{
						$menuInfoArray[$coreMenu->recipe_id]['in_taste'] = true;
					}

					$menuInfoArray[$coreMenu->recipe_id]['sizes'][$coreMenu->pricing_type]['id'] = $coreMenu->id;
					$menuInfoArray[$coreMenu->recipe_id]['sizes'][$coreMenu->pricing_type]['servings_per_item'] = $coreMenu->servings_per_item;

					if ($coreMenu->pricing_type == CMenuItem::FULL)
					{
						$natSalesMix[$coreMenu->recipe_id] = $coreMenu->sales_mix;
					}

					if (!in_array($coreMenu->recipe_id, $recipeIDs))
					{
						$recipeIDs[] = $coreMenu->recipe_id;
					}
				}

				$recipeIDsStr = implode(",", $recipeIDs);

				//step 2:  Loop over all active stores
				$stores = new DAO();

				if (empty($testStoreArray))
				{
					$stores->query("select id, store_name, state_id, email_address from store where active = 1 and store_type <> 'DISTRIBUTION_CENTER' and is_deleted = 0");
				}
				else
				{
					if ($test_stores)
					{
						$stores->query("select id, store_name, state_id, email_address from store where active = 1 and store_type <> 'DISTRIBUTION_CENTER' and is_deleted = 0 and id in (" . implode(",", $testStoreArray) . ")");
					}
					else
					{
						$stores->query("select id, store_name, state_id, email_address from store where active = 1 and store_type <> 'DISTRIBUTION_CENTER' and is_deleted = 0 and id not in (" . implode(",", $testStoreArray) . ")");
					}
				}

				while ($stores->fetch())
				{
					$thisStoresProjections = array();

					if ($dry_run)
					{
						$thisStoreStats = $arrTemplate;
						$thisStoreStats['store_name'] = $stores->store_name;
						$thisStoreStats['state'] = $stores->state_id;
						$thisStoreStats['email'] = $stores->email_address;

					}

					$store_id = $stores->id;
					// step 3: check is all initial_inventory values are set for the store
					// if so the store has already finalized the pre-order
					$projectionTest = new DAO();
				//	$projectionTest->query("select * from menu_item_inventory where menu_id = $menu_id and store_id = $store_id and is_deleted = 0 and override_inventory > 0
				//						and recipe_id in ($recipeIDsStr)");


					$projectionTest->query("select * from menu_item_inventory mii
							join menu_item mi on mi.recipe_id = mii.recipe_id
							join menu_to_menu_item mmi on mmi.menu_item_id = mi.entree_id and mmi.menu_id = $menu_id and mmi.store_id = $store_id and mmi.is_deleted = 0
							where mii.menu_id = $menu_id and mii.store_id = $store_id and mii.is_deleted = 0 and mii.override_inventory > 0
									and mii.recipe_id in ($recipeIDsStr)
							group by mii.recipe_id
							order by mmi.menu_order_value");

					if ($projectionTest->N > (int)(count($recipeIDs) * 0.8))
					{
						// more than 80% of recipes have set inventory so use current values

						while ($projectionTest->fetch())
						{
							$thisStoresProjections[$projectionTest->recipe_id] = $projectionTest->override_inventory;
						}
						if ($dry_run)
						{
							$thisStoreStats['has_inventory'] = true;
							$this->AddProjectionStats($thisStoreStats, $store_id, $anchorDate, $menu_id);
						}
					}
					else
					{
						$usedSalesMix = array();

						// step 4: if not we need to run the projection
						// get store menu mix percentages or if they are not present get national values
						$salesMixTest = new DAO();
						$salesMixTest->query("select recipe_id, sales_mix from menu_item_inventory where menu_id = $menu_id and store_id = $store_id  and is_deleted = 0 and sales_mix > 0
										and recipe id in ($recipeIDsStr)");

						if ($salesMixTest->N == count($recipeIDs))
						{
							while ($salesMixTest->fetch())
							{
								$usedSalesMix[$salesMixTest->recipe_id] = $salesMixTest->sales_mix;
							}
						}
						else
						{
							$usedSalesMix = $natSalesMix;
						}

						// step 5:  get estimated order count numbers
						// get if explicitly set or
						// get based on as much history as is present
						$goalsObj = DAO_CFactory::create('store_monthly_goals');
						$goalsObj->query("select * from store_monthly_goals where store_id = $store_id and date = '$anchorDate'");
						$goalsObj->fetch();
						if (!empty($goalsObj->regular_guest_count_goal_for_inventory))
						{
							$regular_guest_count_goal = $goalsObj->regular_guest_count_goal_for_inventory;
							$taste_guest_count_goal = $goalsObj->taste_guest_count_goal_for_inventory;
							$intro_guest_count_goal = $goalsObj->intro_guest_count_goal_for_inventory;
							if ($dry_run)
							{
								$thisStoreStats['saved_guest_projection'] = "yes";
							}
						}
						else
						{
							$defaults = page_admin_menu_inventory_mgr::retrieveGuestCountEstimates($store_id, $chosenMenu);
							if ($defaults)
							{
								$regular_guest_count_goal = $defaults['regular'];
								$taste_guest_count_goal = $defaults['taste'];
								$intro_guest_count_goal = $defaults['intro'];
							}
							else
							{
								$regular_guest_count_goal = 0;
								$taste_guest_count_goal = 0;
								$intro_guest_count_goal = 0;
							}
						}

						if ($dry_run)
						{
							$thisStoreStats['standard_orders'] = $regular_guest_count_goal;
							$thisStoreStats['ws_orders'] = $taste_guest_count_goal;
							$thisStoreStats['starter_pack_orders'] = $intro_guest_count_goal;
						}

						// step 6: calculate projections
						$averageServingsPerOrder = 41;

						foreach ($menuInfoArray as $id => $data)
						{
							$total_servingsNeeded = $regular_guest_count_goal * $averageServingsPerOrder * $usedSalesMix[$id];

							if ($data['in_intro'])
							{
								$total_servingsNeeded += $intro_guest_count_goal * 18 * $usedSalesMix[$id];
							}

							if ($data['in_taste'])
							{
								$total_servingsNeeded += $intro_guest_count_goal * 9 * $usedSalesMix[$id];
							}

							$total_servingsNeeded = round($total_servingsNeeded);
							$thisStoresProjections[$id] = $total_servingsNeeded;
						}
					}

					// step 7: add to national rollup
					foreach ($thisStoresProjections as $recipe_id => $total_projected)
					{
						$two_serving = 0;
						$four_serving = 0;
						$full_or_six_serving = 0;
						$med_or_three_serving = o;

						$numSizes = count($menuInfoArray[$recipe_id]['sizes']);
						//TODO: 2-4-6 will require additional rules - for now just assume a count of 2 = 70% half and 30% full
						foreach ($menuInfoArray[$recipe_id]['sizes'] as $size => $sizeData)
						{
							if ($size == CMenuItem::FULL)
							{
								$key = $recipe_id . "_" . "L";
								if ($numSizes == 1)
								{
									$portion = 1.0;
								}
								else if ($numSizes == 2)
								{
									$portion = 0.3;
								}
								else if ($numSizes == 4)
								{
									//TODO: 2-4-6 will require additional rules
									$portion = 0.3;
								}
							}
							else if ($size == CMenuItem::HALF)
							{
								$key = $recipe_id . "_" . "M";
								if ($numSizes == 1)
								{
									throw new Exception("Med without FULL is not yet supported by this export");
								}
								else if ($numSizes == 2)
								{
									$portion = 0.7;
								}
								else if ($numSizes == 4)
								{
									//TODO: 2-4-6 will require additional rules
									$portion = 0.3;
								}
							}
							else if ($size == CMenuItem::TWO)
							{
								$key = $recipe_id . "_" . "2";
								if ($numSizes == 1)
								{
									throw new Exception("Med without FULL is not yet supported by this export");
								}
								else if ($numSizes == 2)
								{
									$portion = 0.7;
								}
								else if ($numSizes == 4)
								{
									//TODO: 2-4-6 will require additional rules
									$portion = 0.3;
								}
							}
							else if ($size == CMenuItem::FOUR)
							{
								$key = $recipe_id . "_" . "4";
								if ($numSizes == 1)
								{
									throw new Exception("Med without FULL is not yet supported by this export");
								}
								else if ($numSizes == 2)
								{
									$portion = 0.7;
								}
								else if ($numSizes == 4)
								{
									//TODO: 2-4-6 will require additional rules
									$portion = 0.3;
								}
								}
							else
							{
								// TODO: evanl extend for 2-4-6 but need new percentages
								throw new Exception("Unsupported size/percent");
							}

							if (!isset($nationalProjection[$recipe_id]))
							{
								$nationalProjection[$recipe_id] = array();
							}

							if (!isset($nationalProjection[$recipe_id][$size]))
							{
								$nationalProjection[$recipe_id][$size] = round($thisStoresProjections[$recipe_id] * $portion);
							}
							else
							{
								$nationalProjection[$recipe_id][$size] += round($thisStoresProjections[$recipe_id] * $portion);
							}
						}
					}

					if ($dry_run)
					{
						if ($thisStoreStats['has_inventory'])
						{
							$statsArray['finalized'][$store_id] = $thisStoreStats;
						}
						else
						{
							$statsArray['not_final'][$store_id] = $thisStoreStats;
						}
					}

				}

				// step 8: convert to items and export data
				$exportArr = array();
				foreach ($nationalProjection as $recipe_id => $sizes)
				{
					if (count($sizes) == 2)
					{
						if ($sizes[CMenuItem::FULL])
						{
							$large_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::FULL]['servings_per_item'];
							$large_val = $sizes[CMenuItem::FULL];
							$large_val_in_items = (int)($large_val / $large_servings_per_item);
							$leftOverLargeServings = $large_val % $large_servings_per_item;
							$largeKey = $recipe_id . "_L";
						}

						if ($sizes[CMenuItem::HALF])
						{
							$half_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::HALF]['servings_per_item'];
							$half_val = $sizes[CMenuItem::HALF];
							$half_val_in_items = (int)($half_val / $half_servings_per_item);
							$leftOverHalfServings = $half_val % $half_servings_per_item;
							$halfKey = $recipe_id . "_M";
						}

						//push remainder servings into half number
						$leftOverHalfServings += $leftOverLargeServings;
						$leftOverHalfItems = ceil($leftOverHalfServings / $half_servings_per_item);
						$exportArr[] = array(
							$halfKey,
							$half_val_in_items + $leftOverHalfItems,
							$menuInfoArray[$recipe_id]['name']

						);
						$exportArr[] = array(
							$largeKey,
							$large_val_in_items,
							$menuInfoArray[$recipe_id]['name']
						);
					}
					else if (count($sizes) == 1)
					{
						if ($sizes[CMenuItem::FULL])
						{
							$servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::FULL]['servings_per_item'];
							$val = $sizes[CMenuItem::FULL];
							$val_in_items = ceil($val / $servings_per_item);
							$key = $recipe_id . "_L";
						}

						if ($sizes[CMenuItem::HALF])
						{
							$servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::HALF]['servings_per_item'];
							$val = $sizes[CMenuItem::HALF];
							$val_in_items = (int)($val / $servings_per_item);
							$key = $recipe_id . "_M";
						}

						$exportArr[] = array(
							$key,
							$val_in_items,
							$menuInfoArray[$recipe_id]['name']
						);
					}
					else if (count($sizes) == 4)
					{
						if ($sizes[CMenuItem::FULL])
						{
							$large_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::FULL]['servings_per_item'];
							$large_val = $sizes[CMenuItem::FULL];
							$large_val_in_items = (int)($large_val / $large_servings_per_item);
							$leftOverLargeServings = $large_val % $large_servings_per_item;
							$largeKey = $recipe_id . "_L";
						}

						if ($sizes[CMenuItem::HALF])
						{
							$half_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::HALF]['servings_per_item'];
							$half_val = $sizes[CMenuItem::HALF];
							$half_val_in_items = (int)($half_val / $half_servings_per_item);
							$leftOverHalfServings = $half_val % $half_servings_per_item;
							$halfKey = $recipe_id . "_M";
						}

						if ($sizes[CMenuItem::FOUR])
						{
							$four_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::FOUR]['servings_per_item'];
							$four_val = $sizes[CMenuItem::FOUR];
							$four_val_in_items = (int)($four_val / $four_servings_per_item);
							$leftOverFourServings = $four_val % $four_val_in_items;
							$fourKey = $recipe_id . "_4";
						}

						if ($sizes[CMenuItem::TWO])
						{
							$two_servings_per_item = $menuInfoArray[$recipe_id]['sizes'][CMenuItem::TWO]['servings_per_item'];
							$two_val = $sizes[CMenuItem::TWO];
							$two_val_in_items = (int)($two_val / $two_servings_per_item);
							$leftOverTwoServings = $two_val % $two_servings_per_item;
							$twoKey = $recipe_id . "_2";
						}

						//TODO: evanl - what to do with remainder when variasous sizes available 2-4-6
						//push remainder servings into two
						$leftOverTwoServings += $leftOverFourServings;
						$leftOverTwoItems = ceil($leftOverTwoServings / $two_servings_per_item);
						$exportArr[] = array(
							$twoKey,
							$two_val_in_items + $leftOverTwoItems,
							$menuInfoArray[$recipe_id]['name']

						);
						$exportArr[] = array(
							$fourKey,
							$four_val_in_items,
							$menuInfoArray[$recipe_id]['name']
						);

						//push remainder servings into half number
						$leftOverHalfServings += $leftOverLargeServings;
						$leftOverHalfItems = ceil($leftOverHalfServings / $half_servings_per_item);
						$exportArr[] = array(
							$halfKey,
							$half_val_in_items + $leftOverHalfItems,
							$menuInfoArray[$recipe_id]['name']
						);
						$exportArr[] = array(
							$largeKey,
							$large_val_in_items,
							$menuInfoArray[$recipe_id]['name']
						);
					}
				}

				if ($dry_run)
				{
					$tpl->assign('stats', $statsArray);
				}
				else
				{
					require_once('CSV.inc');
					$csvObj = new CSV();
					$printable_menu = $chosenMenu['menu_name'];
					$fileName = "Projected Sales numbers for National Projections for month of " . $printable_menu;
					$csvObj->writeCSVFile($fileName, array(
						"item",
						"count",
						"name"
					), $exportArr, false, false, false);
					exit;
				}
			}
			else
			{
				$tpl->setErrorMsg("This menu is too old or does not yet exist.");
			}

		}

		$formArray = $Form->render();
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('report_type_to_run', 3);
		$tpl->assign('page_title', 'Master Preorder Export');
	}


	function AddProjectionStats(&$thisStoreStats, $store_id, $anchorDate, $chosenMenu)
	{
		$goalsObj = DAO_CFactory::create('store_monthly_goals');
		$goalsObj->query("select * from store_monthly_goals where store_id = $store_id and date = '$anchorDate'");
		$goalsObj->fetch();

		if (!empty($goalsObj->regular_guest_count_goal_for_inventory))
		{
			$regular_guest_count_goal = $goalsObj->regular_guest_count_goal_for_inventory;
			$taste_guest_count_goal = $goalsObj->taste_guest_count_goal_for_inventory;
			$intro_guest_count_goal = $goalsObj->intro_guest_count_goal_for_inventory;
			$thisStoreStats['saved_guest_projection'] = "yes";
		}
		else
		{
			$defaults = page_admin_menu_inventory_mgr::retrieveGuestCountEstimates($store_id, $chosenMenu);
			if ($defaults)
			{
				$regular_guest_count_goal = $defaults['regular'];
				$taste_guest_count_goal = $defaults['taste'];
				$intro_guest_count_goal = $defaults['intro'];
			}
			else
			{
				$regular_guest_count_goal = 0;
				$taste_guest_count_goal = 0;
				$intro_guest_count_goal = 0;
			}
		}

		$thisStoreStats['standard_orders'] = $regular_guest_count_goal;
		$thisStoreStats['ws_orders'] = $taste_guest_count_goal;
		$thisStoreStats['starter_pack_orders'] = $intro_guest_count_goal;


	}
}

?>