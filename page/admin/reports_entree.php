<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_entree extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsSupport()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$storeTypeGetter = new DAO();
		$storeTypeGetter->query("select id from store where id = {$this->currentStore} and store_type = 'DISTRIBUTION_CENTER'");
		if ($storeTypeGetter->N > 0)
		{
			CApp::bounce('/?page=admin_reports_entree_delivered');
		}


		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(3600 * 24);

		$showdflitems = false;
		$store = null;
		$export = false;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$total_count = 0;
		$report_submitted = false;

		if ($this->currentStore)
		{ // fadmins
			$store = $this->currentStore;
		}
		else
		{ // site admin
			// does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			// CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		if ($store != 'all')
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $store;
			$storeObj->find(true);
			if ($storeObj != null && $storeObj->supports_dinners_for_life)
			{
				$showdflitems = true;
			}
		}

		if ($store == 'all')
		{
			$store = false;
			$showdflitems = true;
		}



		$sessionTypes = array(
			'ALL' => 'All',
			CSession::STANDARD => 'Assembly',
			CSession::SPECIAL_EVENT => 'Pickup',
			CSession::DELIVERY => 'Delivery',
		);
		$tpl->assign('sessionTypes', $sessionTypes);

		$selectedSessionType = array();
		$selectedSessionTypeArgs = '';
		$sessionFilterStr = '';
		$Form->DefaultValues['session_type_all'] = 'true';
		foreach ( $sessionTypes as $type => $label)
		{
			$key ='session_type_'.strtolower($type);
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => $key,
				CForm::label => $label,
				CForm::css_class => 'session_type_filter'
			));
			if(array_key_exists( $key, $_REQUEST)){
				$selectedSessionType[] = $type;
				$selectedSessionTypeArgs .= '&'.$key.'=on';
				$sessionFilterStr .= $label .', ';
				$Form->DefaultValues[$key] = array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : '';
			}
		}
		$sessionFilterStr = rtrim($sessionFilterStr,', ');
		$tpl->assign('selectedSessionTypeArgs', $selectedSessionTypeArgs);

		//$store = $Form->value('store');

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = false;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Run Report'
		));
		// $month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$month_array = array(
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December'
		);

		$year = date("Y");

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_001",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_002",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_popup'
		));

		$Form->DefaultValues['menu_or_calendar'] = 'menu';
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'cal'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'menu'
		));

		if (isset ($_REQUEST["single_date"]))
		{
			$day_start = $_REQUEST["single_date"];
			$tpl->assign('day_start_set', $day_start);
		}

		if (isset ($_REQUEST["range_day_start"]))
		{
			$range_day_start = $_REQUEST["range_day_start"];
			$tpl->assign('range_day_start_set', $range_day_start);
		}
		if (isset ($_REQUEST["range_day_end"]))
		{
			$range_day_end = $_REQUEST["range_day_end"];
			$tpl->assign('range_day_end_set', $range_day_end);
		}

		if (isset($_REQUEST["report_type"]) && isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
		{
			$export = true;
			$report_type_to_run = $_REQUEST["report_type"];
		}

		$timeSpanStr = "";

		if ($Form->value('report_submit') || $export == true)
		{
			$bundleData = null;
			$report_submitted = true;
			$sessionArray = null;
			$menu_array_object = null;
			if ($report_type_to_run == 1)
			{

				if ($export == false)
				{
					$implodedDateArray = explode("-", $day_start);
					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$sessionArray = $SessionReport->getEntreeCounts($store, $day, $month, $year, '1 DAY',$selectedSessionType);
					$menuids = $SessionReport->findAllMenuIDs($sessionArray);
					$tpl->assign('menu_ids', $menuids);
					$bundleData = $SessionReport->getTVOfferCounts($store, $day, $month, $year, '1 DAY',$selectedSessionType);
					$timeTS = mktime(0, 0, 0, $month, $day, $year);
					$timeSpanStr = "Report for day of " . date("l, M jS, Y", $timeTS);
				}
				else
				{
					$sessionArray = $SessionReport->getEntreeCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], '1 DAY',$selectedSessionType);
					$bundleData = $SessionReport->getTVOfferCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], '1 DAY',$selectedSessionType);
					$timeTS = mktime(0, 0, 0, $_REQUEST["month"], $_REQUEST["day"], $_REQUEST["year"]);
					$timeSpanStr = "Report for day of " . date("l, M jS, Y", $timeTS);
				}
				// get the single date
			}
			else if ($report_type_to_run == 2)
			{

				// process for an entire year
				if ($export == false)
				{
					$rangeReversed = false;
					$implodedDateArray = null;
					$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
					$diff++; // always add one for SQL to work correctly
					if ($rangeReversed == true)
					{
						$implodedDateArray = explode("-", $range_day_end);
					}
					else
					{
						$implodedDateArray = explode("-", $range_day_start);
					}

					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = $diff . ' DAY';

					$diffMonth = $SessionReport->datediff("m", $range_day_start, $range_day_end, $rangeReversed);
					if ($diffMonth > 1)
					{
						$spansMenu = true;
					}

					$sessionArray = $SessionReport->getEntreeCounts($store, $day, $month, $year, $duration,$selectedSessionType);
					$menuids = $SessionReport->findAllMenuIDs($sessionArray);
					$tpl->assign('menu_ids', $menuids);
					$bundleData = $SessionReport->getTVOfferCounts($store, $day, $month, $year, $duration);

					$timeTS1 = mktime(0, 0, 0, $month, intval($day), $year);
					$timeTS2 = $timeTS1 + (($diff - 1) * 86400) + 3601;

					$timeSpanStr = "Report for " . date("M jS, Y", $timeTS1) . " through " . date("M jS, Y", $timeTS2);
				}
				else
				{
					$sessionArray = $SessionReport->getEntreeCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"],$selectedSessionType);
					$bundleData = $SessionReport->getTVOfferCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"]);

					$timeTS1 = mktime(0, 0, 0, $_REQUEST["month"], $_REQUEST["day"], $_REQUEST["year"]);

					$diffParts = explode(" ", $_REQUEST["duration"]);

					$timeTS2 = $timeTS1 + (($diffParts[0] - 1) * 86400) + 3601;
					$timeSpanStr = "Report for " . date("M jS, Y", $timeTS1) . " through " . date("M jS, Y", $timeTS2);
				}
			}
			else if ($report_type_to_run == 3)
			{
				//PHP 8 $month = $_REQUEST["month_popup"] ?? null;
				$month = array_key_exists( "month_popup", $_REQUEST)? $_REQUEST["month_popup"]: null;
				$month++;
				//PHP 8 	$year = $_REQUEST["year_field_001"] ?? null;
				$year = array_key_exists( "year_field_001", $_REQUEST)? $_REQUEST["year_field_001"]: null;

				if ($Form->value('menu_or_calendar') == 'menu')
				{
					// menu month
					// process for a given month
					if ($export == false)
					{

						$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
						list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
						$start_date = strtotime($menu_start_date);
						$year = date("Y", $start_date);
						$month = date("n", $start_date);
						$day = date("j", $start_date);
						$duration = $interval . " DAY";

						$sessionArray = $SessionReport->getEntreeCounts($store, $day, $month, $year, $duration,$selectedSessionType);
						$menuids = $SessionReport->findAllMenuIDs($sessionArray);
						$tpl->assign('menu_ids', $menuids);
						$bundleData = $SessionReport->getTVOfferCounts($store, $day, $month, $year, $duration);
						$timeSpanStr = "Report for Menu Month of " . date("F Y", strtotime($anchorDay));
					}
					else
					{

						$sessionArray = $SessionReport->getEntreeCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"],$selectedSessionType);
						$bundleData = $SessionReport->getTVOfferCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"]);
						$timeTS = mktime(0, 0, 0, $_REQUEST["month"], 1, $_REQUEST["year"]);
						$timeSpanStr = "Report for Menu Month of " . date("F Y", $timeTS);
					}
				}
				else
				{
					// process for a given month
					if ($export == false)
					{
						$day = "01";
						$duration = '1 MONTH';
						$sessionArray = $SessionReport->getEntreeCounts($store, $day, $month, $year, '1 MONTH',$selectedSessionType);
						$menuids = $SessionReport->findAllMenuIDs($sessionArray);
						$tpl->assign('menu_ids', $menuids);
						$bundleData = $SessionReport->getTVOfferCounts($store, $day, $month, $year, '1 MONTH');
						$timeTS = mktime(0, 0, 0, $month, 1, $year);
						$timeSpanStr = "Report for Calendar Month of " . date("F Y", $timeTS);
					}
					else
					{
						$sessionArray = $SessionReport->getEntreeCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], '1 MONTH',$selectedSessionType);
						$bundleData = $SessionReport->getTVOfferCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], '1 MONTH');
						$timeTS = mktime(0, 0, 0, $_REQUEST["month"], 1, $_REQUEST["year"]);
						$timeSpanStr = "Report for Calendar Month of " . date("F Y", $timeTS);
					}
				}
			}
			else if ($report_type_to_run == 4)
			{
				set_time_limit(120);
				$spansMenu = true;
				if ($export == false)
				{
					$year = $_REQUEST["year_field_002"];
					$month = "01";
					$day = "01";
					$duration = '1 YEAR';
					$sessionArray = $SessionReport->getEntreeCounts($store, $day, $month, $year, $duration,$selectedSessionType);
					$menuids = $SessionReport->findAllMenuIDs($sessionArray);
					$tpl->assign('menu_ids', $menuids);
					$bundleData = $SessionReport->getTVOfferCounts($store, $day, $month, $year, $duration);
					$timeTS = mktime(0, 0, 0, 1, 1, $year);
					$timeSpanStr = "Report for year of " . date("Y", $timeTS);
				}
				else
				{
					$sessionArray = $SessionReport->getEntreeCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"],$selectedSessionType);
					$bundleData = $SessionReport->getTVOfferCounts($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"]);
					$timeTS = mktime(0, 0, 0, 1, 1, $_REQUEST["year"]);
					$timeSpanStr = "Report for year of " . date("Y", $timeTS);
				}
			}

			$problem_sessions = array();

			if (count($bundleData) > 0)
			{
				$tpl->assign('numBundles', $bundleData);
			}

			$tpl->assign('timeSpanStr', $timeSpanStr);
			$tpl->assign('sessionFilterStr', $sessionFilterStr);

			if (count($sessionArray) > 0)
			{
				$menu_name_ids = array();
				$menuids = $SessionReport->findAllMenuIDs($sessionArray);
				$containerTypesArr = $SessionReport->getContainerTypes($menuids, $store);

				$menu_array_object = $SessionReport->generateMenuArrayNItems($menuids, $menu_name_ids, $containerTypesArr, $store);

				$tpl->assign('menu_ids', $menuids);
				$tpl->assign('menu_order_array', $menu_name_ids);
				if (count($menu_array_object) > 0)
				{
					$SessionReport->joinEntreesWithMenus($sessionArray, $menu_array_object, $containerTypesArr);
					$total_count = $SessionReport->getTotalEntreeCount();
				}
				else
				{
					$menu_array_object = array();
				} // error

				$tpl->assign('total_order_count', $SessionReport->total_orders);
				$tpl->assign('percent_total_orders_greater_than_72_servings', CTemplate::divide_and_format($SessionReport->total_orders_greater_than_72_servings * 100, $SessionReport->total_orders, 2));

				$sum = 0;
				$sumDR = 0;
				if ($export == true)
				{
					$promo_items = $SessionReport->getPromoCountsPerItemByProgram($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"], $sum);
					$obj = new CStoreExpenses();
					$dr_items = $obj->findPromoItems($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"]);
					$SessionReport->getGuestCountsForMenuItems($menu_array_object, $store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"], $sessionArray);
				}
				else
				{
					$promo_items = $SessionReport->getPromoCountsPerItemByProgram($store, $day, $month, $year, $duration, $sum);
					$obj = new CStoreExpenses();
					$dr_items = $obj->findPromoItems($store, $day, $month, $year, $duration);
					$SessionReport->getGuestCountsForMenuItems($menu_array_object, $store, $day, $month, $year, $duration, $sessionArray);
				}
				$sumDR = array_sum($dr_items);

				$rows = array();
				$counter = 0;
				$itemcounter = 0;
				$ServingsServed = 0;
				$DiabeticServingsServed = 0; // Diabetic Section call out

				if ($store)
				{
					$inventoryData = $SessionReport->getInventoryDataForMenuArray($menuids, $store);
				}
				else
				{
					$inventoryData = array();
				}

				$counterIntoMenuIds = 0;
				foreach ($menu_name_ids as $menu_entity)
				{

					$menu_prices = CMenu::getStorePricingForMenu($menuids[$counterIntoMenuIds++], $store);
					$temp_count = 0;
					foreach ($menu_array_object as $entity)
					{
						$arr = $entity->getAllReportData($menu_entity);

						if (is_array($arr)&& $arr[3] === $menu_entity)
						{
							$format = "0";

							if ($entity->is_side_dish == 0 && $entity->is_kids_choice == 0 && $entity->is_menu_addon == 0 && $entity->is_bundle == 0 || $entity->is_chef_touched == 0)
							{
								$temp_total_count = $total_count;
								$temp_percentage = 0;
								$temp_count = $arr[1];
								$itemcounter = $itemcounter + $temp_count;
							}
							$curMenuID = $arr[4];

							$temparr = array();

							$tempArr['id'] = $temparr['menu_month'] = $menu_entity;
							$temparr['menu_name'] = $arr[2]; // menu name
							$temparr['recipe_id'] = $entity->recipe_id;

							if ($entity->category_name == "Fast Lane" && !$entity->is_store_special)
							{
								$entity->category_name = "Specials";
								$entity->category_ordering = 1;
								$entity->menu_item_category_id = 1;
							}

							if ($entity->category_name == "Fast Lane")
							{
								$entity->category_name = "Extended Fast Lane";
							}

							$programtype = 1;

							if ($entity->category_name == "Diabetic")
							{
								$programtype = 2;
							}

							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
							{
								$temparr['two'] = $arr[14];
								$temparr['four'] = $arr[15];
							}

							$temparr['medium'] = $arr[7];
							if ($arr[7] > 0)
							{
								if (isset($promo_items[$arr[2]][$programtype][CMenuItem::HALF]))
								{
									$temparr['medium'] -= $promo_items[$arr[2]][$programtype][CMenuItem::HALF];
								}
							}

							$temparr['large'] = $arr[6];
							if ($entity->menu_item_id != 1940)
							{
								if ($arr[6] > 0)
								{
									if (isset($promo_items[$arr[2]][$programtype][CMenuItem::FULL]))
									{
										$temparr['large'] -= $promo_items[$arr[2]][$programtype][CMenuItem::FULL];
									}

									if (isset($promo_items[$arr[2]][$programtype][CMenuItem::LEGACY]))
									{
										$temparr['large'] -= $promo_items[$arr[2]][$programtype][CMenuItem::LEGACY];
									}
								}
							}

							$temparr[CMenuItem::INTRO] = $arr[5];

							$temparr['station_number'] = $entity->station_number;
							$temparr['percentages'] = 0;

							if ($entity->is_side_dish == 0 && $entity->is_kids_choice == 0 && $entity->is_menu_addon == 0 && $entity->is_bundle == 0 && $entity->is_chef_touched == 0)
							{

								// why wasn't this removed!!!   // $ServingsServed += $temparr['total_servings'];
								// Diabetic Section call out
								if ($entity->category_name == "Diabetic")
								{
									$temparr['total_servings'] = $arr[11];
									$DiabeticServingsServed += $temparr['total_servings'];
								}
								else
								{
									$temparr['total_servings'] = $arr[11];
									$ServingsServed += $temparr['total_servings'];
								}
							}

							// TODO: if we ever show FT items here we must subtract future pickups from the remaining servings to get a true available for sale number
							if (isset($inventoryData[$arr[4]][$arr[0]]))
							{
								$temparr['remaining_inv'] = $inventoryData[$arr[4]][$arr[0]];
							}
							else
							{
								$temparr['remaining_inv'] = 9999;
							}

							if ($entity->is_side_dish == 0 && $entity->is_kids_choice == 0 && $entity->is_menu_addon == 0 && $entity->is_bundle == 0 && $entity->is_chef_touched == 0)
							{

								// $temparr['total_dinners_for_ordering'] = ($arr[5]) + ($arr[6]) + ($arr[7] * .5);  // wasn't counting correctly  (LMH)
								$temparr['total_dinners_for_ordering'] = $arr[12];
							}

							$temparr['line_total'] = $temp_count; // line totals
							if ($entity->is_side_dish == 0 && $entity->is_kids_choice == 0 && $entity->is_menu_addon == 0 && $entity->is_bundle == 0 && $entity->is_chef_touched == 0)
							{
								if ($export == false)
								{
									$temparr['all_totals'] = $temp_total_count;
								} // all totals
								// $temparr['percentages'] = $format;  // percentage
							}

							/*
														$temparr['large'] = $arr[6];
														if ($entity->menu_item_id != 1940) {
															if ($arr[6] > 0) {
																if (isset($promo_items[$arr[2]][CMenuItem::FULL])) {
																	$temparr['large'] -= $promo_items[$arr[2]][CMenuItem::FULL];
																}

																if (isset($promo_items[$arr[2]][CMenuItem::LEGACY])) {
																	$temparr['large'] -= $promo_items[$arr[2]][CMenuItem::LEGACY];
																}
															}
														}

														$temparr['medium'] = $arr[7];
														if ($arr[7] > 0) {
															if (isset($promo_items[$arr[2]][CMenuItem::HALF])) {
																$temparr['medium'] -= $promo_items[$arr[2]][CMenuItem::HALF];
															}
														}

														$temparr[CMenuItem::INTRO] = $arr[5];
							*/

							if ($export == false)
							{
								$temparr['curMenuID'] = $arr[4];
							}

							$temparr['promo_full'] = 0;
							$temparr['promo_half'] = 0;

							/*
														if (isset($promo_items[$arr[2]])) {
															if (isset($promo_items[$arr[2]][CMenuItem::FULL])) {
																$temparr['promo_full'] = $promo_items[$arr[2]][CMenuItem::FULL];
															}

															if (isset($promo_items[$arr[2]][CMenuItem::LEGACY])) {
																$temparr['promo_full'] = $promo_items[$arr[2]][CMenuItem::LEGACY];
															}

															if (isset($promo_items[$arr[2]][CMenuItem::HALF])) {
																$temparr['promo_half'] = $promo_items[$arr[2]][CMenuItem::HALF];
															}
														}

							*/

							if (isset($promo_items[$arr[2]]))
							{
								if (isset($promo_items[$arr[2]][$programtype][CMenuItem::FULL]))
								{
									$temparr['promo_full'] = $promo_items[$arr[2]][$programtype][CMenuItem::FULL];
								}

								if (isset($promo_items[$arr[2]][$programtype][CMenuItem::LEGACY]))
								{
									$temparr['promo_full'] = $promo_items[$arr[2]][$programtype][CMenuItem::LEGACY];
								}

								if (isset($promo_items[$arr[2]][$programtype][CMenuItem::HALF]))
								{
									$temparr['promo_half'] = $promo_items[$arr[2]][$programtype][CMenuItem::HALF];
								}
							}

							$temparr['category_ordering'] = $entity->category_ordering;

							$temparr['is_side_dish'] = $entity->is_side_dish;
							$temparr['is_kids_choice'] = $entity->is_kids_choice;
							$temparr['is_menu_addon'] = $entity->is_menu_addon;
							$temparr['is_preassembled'] = $entity->is_preassembled;
							$temparr['is_bundle'] = $entity->is_bundle;
							$temparr['is_chef_touched'] = $entity->is_chef_touched;

							if (isset($menu_prices[$entity->menu_item_id]['HALF']))
							{
								$temparr['half_price'] = $menu_prices[$entity->menu_item_id]['HALF'];
							}
							else
							{
								$temparr['half_price'] = "";
							}

							if (isset($menu_prices[$entity->menu_item_id]['FULL']))
							{
								$temparr['full_price'] = $menu_prices[$entity->menu_item_id]['FULL'];
							}
							else
							{
								$temparr['full_price'] = "";
							}
							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
							{
								if (isset($menu_prices[$entity->menu_item_id]['TWO']))
								{
									$temparr['two_price'] = $menu_prices[$entity->menu_item_id]['TWO'];
								}
								else
								{
									$temparr['two_price'] = "";
								}

								if (isset($menu_prices[$entity->menu_item_id]['FOUR']))
								{
									$temparr['four_price'] = $menu_prices[$entity->menu_item_id]['FOUR'];
								}
								else
								{
									$temparr['four_price'] = "";
								}
							}

							$temparr['item_revenue'] = $arr[13];

							$temparr['num_purchasers'] = $entity->num_purchasers;

							$temparr['category_name'] = $entity->category_name;

							$rows[$counter] = $temparr;
							$counter++;
						}
					}

					if ($ServingsServed > 0)
					{
						$currentMenu = $menu_entity;
						foreach ($rows as $key => $element)
						{
							if ($element['menu_month'] == $currentMenu)
							{
								$rows[$key]['diabetic_percentages'] = 0;
								$category = $element['category_name'];
								if ($category == "Diabetic" && $DiabeticServingsServed > 0 && !empty($element['total_servings']))
								{
									$servingsPercentage = $element['total_servings'] / $DiabeticServingsServed;
									$format = sprintf("%.2f", $servingsPercentage * 100);
									$rows[$key]['diabetic_percentages'] = $format;
								}

								if ($ServingsServed > 0 && !empty($element['total_servings']))
								{
									$servingsPercentage = $element['total_servings'] / $ServingsServed;
									$format = sprintf("%.2f", $servingsPercentage * 100);
									$rows[$key]['percentages'] = $format;
								}
							}
						}
						$ServingsServed = 0;
						$DiabeticServingsServed = 0; // Diabetic Section call out reset
					}

					$tpl->assign('promo_items_total', $sum);
					$tpl->assign('promo_items_total_dr', $sumDR);
					$tpl->assign('promo_items', $promo_items);
					$tpl->assign('promo_dr_items', $dr_items);
				}
			}

			if (!empty($rows) && count($rows) > 0)
			{
				if (!empty($rows[0]['category_name']))
				{
					$temparr = array();

					foreach ($rows as $key => $element)
					{
						$temparr[$element['category_ordering']][] = $rows[$key];
					}
					if ($export == true)
					{
						foreach ($menu_name_ids as $menu_entity)
						{

							if (!isset($bundleData[$menu_entity]))
							{
								$count = 0;
							}
							else
							{
								$count = $bundleData[$menu_entity];
							}

							$bundleRow = array();
							$bundleRow['menu_month'] = $menu_entity;
							$bundleRow['menu_name'] = "Meal Prep Starter Pack Bundle";
							$bundleRow['recipe_id'] = "-";
							$bundleRow['station'] = "-";
							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids)){
								$bundleRow['two'] = "-";
								$bundleRow['four'] = "-";
							}
							$bundleRow['large'] = "-";
							$bundleRow['medium'] = "-";
							$bundleRow['remaining_inv'] = "-";
							$bundleRow['line_total'] = $count;
							$bundleRow['percentages'] = "-";
							$bundleRow['total_servings'] = "-";
							$bundleRow['total_dinners_for_ordering'] = $count;
							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
							{
								$bundleRow['two_price'] = "-";
								$bundleRow['four_price'] = "-";
							}
							$bundleRow['half_price'] = "-";
							$bundleRow['full_price'] = "-";
							$bundleRow['item_revenue'] = "-";
							$bundleRow['num_purchasers'] = "-";
							$bundleRow['category_name'] = "Starter Pack";
							$bundleRow['category_ordering'] = 5;
							$bundleRow['is_side_dish'] = 0;
							$bundleRow['is_kids_choice'] = 0;
							$bundleRow['is_menu_addon'] = 0;
							$bundleRow['is_preassembled'] = 0;
							$bundleRow['is_bundle'] = 0;
							$bundleRow['is_chef_touched'] = 0;
							$bundleRow['diabetic_percentages'] = 0;

							$temparr[5][] = $bundleRow;
						}
					}

					ksort($temparr);
					$rows = null;
					foreach ($temparr as $key => $element)
					{
						foreach ($element as $skey => $selement)
						{
							$rows[] = $selement;
						}
					}
				}
			}

			if ($export == true)
			{
				$sidearray = array();
				$counter = 0;

				foreach ($rows as $key => $element)
				{
					$category = $element['category_name'];

					if ($category == "Specials" && $rows[$key]['station_number'] == 0)
					{
						$rows[$key]['station_number'] = 'FL';
					}

					if ($category == "Diabetic" && $showdflitems == false)
					{
						unset($rows[$key]);
					}
					else
					{

						if (!empty($rows[$key]['promo_full']))
						{
							$rows[$key]['large'] += $rows[$key]['promo_full'];
						}

						if (!empty($rows[$key]['promo_half']))
						{
							$rows[$key]['medium'] += $rows[$key]['promo_half'];
						}

						unset($rows[$key]['INTRO']);
						unset($rows[$key]['category_ordering']);
						unset($rows[$key]['is_side_dish']);
						unset($rows[$key]['is_kids_choice']);
						unset($rows[$key]['is_menu_addon']);
						unset($rows[$key]['is_bundle']);
						unset($rows[$key]['is_chef_touched']);
						unset($rows[$key]['is_preassembled']);
						unset($rows[$key]['promo_full']);
						unset($rows[$key]['promo_half']);

						if ($element['is_chef_touched'] != 1)
						{
							unset($rows[$key]['line_total']);
						}

						// Diabetic Section call out
						if ($rows[$key]['diabetic_percentages'] > 0)
						{
							$rows[$key]['percentages'] = $rows[$key]['diabetic_percentages'];
						}

						//   if ($rows[$key]['category_name'] == "Extended Fast Lane")
						//  {
						//   	$rows[$key]['half_price'] = "";
						//   	$rows[$key]['full_price'] = "";
						//   	$rows[$key]['item_revenue'] = "";
						//    }

						unset($rows[$key]['diabetic_percentages']);

						if ($element['is_side_dish'] == 1 || $element['is_kids_choice'] == 1 || $element['is_menu_addon'] == 1 || $element['is_bundle'] == 1 || $element['is_chef_touched'] == 1)
						{
							$temp = $rows[$key];
							$rev = $temp['item_revenue'];

							$temp = array();

							$temp[] = $rows[$key]['menu_month'];
							$temp[] = $rows[$key]['menu_name'];
							$temp[] = $rows[$key]['recipe_id'];
							$temp['large'] = null;
							$temp['medium'] = null;
							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
							{
								$temp['two'] = null;
								$temp['four'] = null;
							}
							$temp['station'] = null;
							$temp['percentages'] = null;
							$temp['line_total'] = $rows[$key]['large'] + $rows[$key]['medium'];
							$temp['remaining_inv'] = $rows[$key]['remaining_inv'];
							$temp['total_for_ordering'] = null;
							$temp['half_price'] = null;
							$temp['full_price'] = $rows[$key]['full_price'];
							if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
							{
								$temp['two_price'] = null;
								$temp['four_price'] = null;
							}
							$temp['item_revenue'] = null;
							$temp['num_purchasers'] = $rows[$key]['num_purchasers'];
							$temp['category_name'] = $category;

							$temp['item_revenue'] = $rev;

							if ($temp['category_name'] == "Chef Touched Selections")
							{
								$temp['category_name'] = "Sides &amp; Sweets";
							}

							unset($rows[$key]);

							$rows[] = $temp;
						}
						$counter++;
					}
				}

				if (true /*$_GET['export'] != 'csv'*/)
				{
					/** Include PHPExcel */
					require_once("phplib/PHPExcel/PHPExcel.php");

					// Create new PHPExcel object
					$objPHPExcel = new PHPExcel();

					$AdminUser = CUser::getCurrentUser();

					// Set document properties
					$objPHPExcel->getProperties()->setCreator($AdminUser->firstname . " " . $AdminUser->lastname)->setLastModifiedBy($AdminUser->firstname . " " . $AdminUser->lastname)->setTitle($timeSpanStr)->setKeywords("office 2007 openxml php")->setCategory("entree reprot file");

					if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
					{
						$columns = array(
							array(
								'label' => 'Menu Name',
								'width' => 'Auto'
							),
							array(
								'label' => 'Menu Item',
								'width' => 40
							),
							array(
								'label' => 'Recipe ID',
								'width' => 'Auto'
							),
							array(
								'label' => "Items Sold\nSm",
								'width' => 8
							),
							array(
								'label' => "Items Sold\nMd (4)",
								'width' => 8
							),
							array(
								'label' => "Items Sold\nMd (3)",
								'width' => 8
							),
							array(
								'label' => "Items Sold\nLrg",
								'width' => 8
							),
							array(
								'label' => "Station",
								'width' => 8
							),
							array(
								'label' => "Sales\nMix",
								'width' => 8
							),
							array(
								'label' => "Total\nServings",
								'width' => 10
							),
							array(
								'label' => "Remaining\nServings",
								'width' => 12
							),
							array(
								'label' => "Total\nDinners for\nOrdering",
								'width' => 12
							),
							array(
								'label' => "Retail\nPrice\nMd (3)",
								'width' => 8
							),
							array(
								'label' => "Retail\nPrice\nLrg",
								'width' => 8
							),
							array(
								'label' => "Retail\nPrice\nSm",
								'width' => 8
							),
							array(
								'label' => "Retail\nPrice\nMd (4)",
								'width' => 8
							),
							array(
								'label' => "Total\nSold",
								'width' => 'Auto'
							),
							array(
								'label' => "Unique Guest Orders",
								'width' => 10
							),
							array(
								'label' => "Category",
								'width' => 'Auto'
							)
						);
					}else{
						$columns = array(
							array(
								'label' => 'Menu Name',
								'width' => 'Auto'
							),
							array(
								'label' => 'Menu Item',
								'width' => 40
							),
							array(
								'label' => 'Recipe ID',
								'width' => 'Auto'
							),
							array(
								'label' => "Items Sold\nMd",
								'width' => 8
							),
							array(
								'label' => "Items Sold\nLrg",
								'width' => 8
							),
							array(
								'label' => "Station",
								'width' => 8
							),
							array(
								'label' => "Sales\nMix",
								'width' => 8
							),
							array(
								'label' => "Total\nServings",
								'width' => 10
							),
							array(
								'label' => "Remaining\nServings",
								'width' => 12
							),
							array(
								'label' => "Total\nDinners for\nOrdering",
								'width' => 12
							),
							array(
								'label' => "Retail\nPrice\nMd",
								'width' => 8
							),
							array(
								'label' => "Retail\nPrice\nLrg",
								'width' => 8
							),
							array(
								'label' => "Total\nSold",
								'width' => 'Auto'
							),
							array(
								'label' => "Unique Guest Orders",
								'width' => 10
							),
							array(
								'label' => "Category",
								'width' => 'Auto'
							)
						);
					}


					$objPHPExcel->setActiveSheetIndex(0);
					$row = 1;
					$col = "A";
					foreach ($columns as $thisColumn)
					{
						$objPHPExcel->getActiveSheet()->setCellValue($col . $row, $thisColumn['label']);

						$styleArray = array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$objPHPExcel->getActiveSheet()->getStyle($col . $row)->applyFromArray($styleArray);

						$col = chr(ord($col) + 1);
					}

					$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(44);

					$styleArray = array(
						'font' => array('bold' => true),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'wrap' => true
						),
						'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK)),
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
							'rotation' => 90,
							'startcolor' => array('argb' => 'FFE3F072',),
							'endcolor' => array('argb' => 'FFA8B355')
						)
					);

					$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->applyFromArray($styleArray);

					$styleArray = array(
						'font' => array('bold' => true),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'wrap' => true
						),
						'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK)),
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
							'rotation' => 90,
							'startcolor' => array('argb' => 'FFDCE6F1',),
							'endcolor' => array('argb' => 'FFBDD0E5')
						)
					);
					$objPHPExcel->getActiveSheet()->getStyle('A2:S2')->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->setCellValue("A2", "Session Types Included: $sessionFilterStr");

					if(CStore::isCoreTestStoreAcrossMenus($store, $menuids))
					{

						$row = 3;
						foreach ($rows as &$data)
						{

							$data['category_name'] = str_replace("&amp;", "&", $data['category_name']);

							$col = "A";
							foreach ($data as $datum)
							{
								if ($col == 'I' && $datum > 0)
								{
									$datum /= 100;
								}

								$objPHPExcel->getActiveSheet()->setCellValue($col . $row, $datum);

								if ($col == 'I' && $datum > 0)
								{
									$objPHPExcel->getActiveSheet()->getStyle($col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
								}
								else if ($col == 'M' || $col == 'N' || $col == 'O' || $col == 'P' || $col == 'Q')
								{
									$objPHPExcel->getActiveSheet()->getStyle($col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
								}

								$col = chr(ord($col) + 1);
							}
							$row++;
						}
					}else{
						$row = 3;
						foreach ($rows as &$data)
						{

							$data['category_name'] = str_replace("&amp;", "&", $data['category_name']);

							$col = "A";
							foreach ($data as $datum)
							{
								if ($col == 'G' && $datum > 0)
								{
									$datum /= 100;
								}

								$objPHPExcel->getActiveSheet()->setCellValue($col . $row, $datum);

								if ($col == 'G' && $datum > 0)
								{
									$objPHPExcel->getActiveSheet()->getStyle($col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
								}
								else if ($col == 'M' || $col == 'K' || $col == 'L')
								{
									$objPHPExcel->getActiveSheet()->getStyle($col . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
								}

								$col = chr(ord($col) + 1);
							}
							$row++;
						}
					}

					$col = 'A';
					foreach ($columns as $thisColumn)
					{
						if ($thisColumn['width'] == "Auto")
						{
							$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
						}
						else
						{
							$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($thisColumn['width']);
						}

						$col = chr(ord($col) + 1);
					}

					$rowCount = count($rows);

					// Rename worksheet
					$objPHPExcel->getActiveSheet()->setTitle('Entree Report');
					$objPHPExcel->getActiveSheet()->freezePane('A2');

					// Set active sheet index to the first sheet, so Excel opens this as the first sheet
					$objPHPExcel->setActiveSheetIndex(0);

					// Redirect output to a client�s web browser (Excel2007)
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header("Content-Disposition: attachment;filename=\"Entree $timeSpanStr.xlsx\"");
					header('Cache-Control: max-age=0');

					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					$objWriter->save('php://output');
					exit;
				}
			}
			else
			{
				if (!empty($rows))
				{
					$tpl->assign('table_data', $rows);
				}
			}

			if (!empty($rows))
			{
				$numRows = count($rows);
			}
			else
			{
				$numRows = 0;
			}

			$Dest = ($export ? "Excel" : "Screen");

			CLog::RecordReport("Entree", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run ~ Dest: $Dest");

			$tpl->assign('report_type', $report_type_to_run);
			$tpl->assign('report_day', $day);
			$tpl->assign('report_month', $month);
			$tpl->assign('report_year', $year);
			$tpl->assign('report_duration', $duration);
			$tpl->assign('store', $store);
		}

		$formArray = $Form->render();
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('spans_menus', $spansMenu);
		$tpl->assign('total_count', $total_count);
		$tpl->assign('showdfl', $showdflitems); // dfl call out
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Entree Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function addRevenueNumbers(&$itemArray)
	{
		//get array of entree IDs
		$IDs = array_keys($itemArray);

		$IDList = implode(",", $IDs);
	}
}

?>