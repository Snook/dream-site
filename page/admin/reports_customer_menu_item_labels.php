<?php
/*
 * Created on Nov 10, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Orders.php';
require_once 'includes/CSessionReports.inc';
require_once 'DAO/BusinessObject/CMenu.php';
require_once 'includes/CAppUtil.inc';

class page_admin_reports_customer_menu_item_labels extends CPageAdminOnly
{

	private $DATE_FORMAT_TOOLTIP = 'Checked will print the full date, e.g. Assembled {0}. <br><br>Unchecked will print the month and year only, e.g. Assembled {1}';

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
		$dateFormattedS = CTemplate::dateTimeFormat(date("Y-m-d H:i:s"), VERBOSE_MONTH_YEAR);
		$dateFormattedL = CTemplate::dateTimeFormat(date("Y-m-d H:i:s"), MONTH_DAY_YEAR);
		$this->DATE_FORMAT_TOOLTIP = str_replace('{0}', $dateFormattedL, $this->DATE_FORMAT_TOOLTIP);
		$this->DATE_FORMAT_TOOLTIP = str_replace('{1}', $dateFormattedS, $this->DATE_FORMAT_TOOLTIP);
	}

	private static function sortArrayByMenuItem($a, $b)
	{
		$strCmpStr = strcmp($a["menu_item"], $b["menu_item"]);

		if ($strCmpStr != 0)
		{
			return $strCmpStr;
		}

		$strCmpStr = strcmp($a["lastname"], $b["lastname"]);

		if ($strCmpStr != 0)
		{
			return $strCmpStr;
		}

		if ($a['item_number'] < $b['item_number'])
		{
			return -1;
		}
		else if ($a['item_number'] > $b['item_number'])
		{
			return 1;
		}

		return 0;
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
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

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		ini_set('memory_limit', '64M');
		set_time_limit(7200);

		$tpl = CApp::instance()->template();
		$suppressFastlane = isset($_REQUEST["suppressFastlane"]) ? $_REQUEST["suppressFastlane"] : 0;
		$menu_id = isset ($_REQUEST["menuid"]) ? $_REQUEST["menuid"] : 0;
		$pig = 1;
		$session_id = isset ($_REQUEST["session_id"]) ? $_REQUEST["session_id"] : 0;
		$booking_id = isset ($_REQUEST["booking_id"]) ? $_REQUEST["booking_id"] : false;
		$break = isset ($_REQUEST["break"]) ? $_REQUEST["break"] : 0;
		$store_id = isset ($_REQUEST["store_id"]) ? $_REQUEST["store_id"] : 0;

		if ($store_id == 0)
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		if (empty($store_id))
		{
			$tpl->setErrorMsg("No Store ID provided");
		}
		else
		{
			$tpl->assign('store_id', $store_id);
		}

		$tpl->assign('success', true);
		$tpl->assign('suppressFastlane', $suppressFastlane);

		$showInterface = isset ($_REQUEST["interface"]) ? $_REQUEST["interface"] : 0;
		if ($showInterface == 0)
		{
			// $store_id = 0;
			if ($session_id == 0)
			{
				$output_array = $this->create_generic_data($pig, $menu_id, $store_id);
				CLog::RecordReport('Session/Labels Generic', "Menu: $menu_id ~ Store: $store_id");
			}
			else
			{

				CLog::RecordReport('Session/Labels', "Session: $session_id");

				$other_details = false;

				$output_array = self::create_view_all_orders($session_id, $other_details, $menu_id, $store_id, $suppressFastlane, $booking_id);
				if (empty($output_array))
				{
					$tpl->setStatusMsg('No Core Menu Items available to print for the selected date or time.');
					$tpl->assign('success', false);
				}
				else
				{
					$store = DAO_CFactory::create('store');
					$store->id = $store_id;
					$store->find(true);

					$store_name = $store->store_name;
					$store_phone = $store->telephone_day;

					$tpl->assign('show_borders', 0);
				}
			}

			$tpl->assign('store_name', $store_name);
			$tpl->assign('store_phone', $store_phone);
			$tpl->assign('storeObj', $store);

			$labelsPerSheet = 4;

			$tpl->assign('break', $break);
			$tpl->assign('order_details', $output_array);
		}
		else
		{
			$pagesToPrintMax = 4;
			$Form = new CForm();
			$Form->Repost = false;

			// Get menu info
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->limit(12);
			$DAO_menu->orderBy("menu.id DESC");
			$DAO_menu->find();
			$menu_array = array();
			$ft_menu_array = array();
			$menu_months = array();

			while ($DAO_menu->fetch())
			{
				$menu_array[$DAO_menu->id] = CTemplate::dateTimeFormat($DAO_menu->menu_start, "%B %Y");

				if ($DAO_menu->id > 137)
				{
					$ft_menu_array[$DAO_menu->id] = CTemplate::dateTimeFormat($DAO_menu->menu_start, "%B %Y");
				}

				if (date('Y-m-d') >= $DAO_menu->menu_start && date('Y-m-d') <= $DAO_menu->global_menu_end_date)
				{
					$curMenu = $DAO_menu->id;
				}

				$menu_months[$DAO_menu->id] = $DAO_menu->menu_start;
			}

			if (!empty($_REQUEST["menuid"]) && !array_key_exists($_REQUEST["menuid"], $menu_array) && is_numeric($_REQUEST["menuid"]))
			{
				$menu_array[$_REQUEST["menuid"]] = $_REQUEST["menuid"];
				$ft_menu_array[$_REQUEST["menuid"]] = $_REQUEST["menuid"];
			}

			$Form->DefaultValues['menus'] = $curMenu;

			if ($curMenu < 138)
			{
				$Form->DefaultValues['ft_menus'] = 138;
			}
			else
			{
				$Form->DefaultValues['ft_menus'] = $curMenu;
			}

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::options => $menu_array,
				CForm::name => 'menus'
			));

			$menu_id = $Form->value('menus');

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::options => $ft_menu_array,
				CForm::name => 'ft_menus'
			));

			$Form->DefaultValues['ft_labels_per_sheet'] = 6;
			$labelsPerSheet = array(
				6 => '6',
				4 => '4'
			);
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => false,
				CForm::allowAllOption => true,
				CForm::options => $labelsPerSheet,
				CForm::name => 'ft_labels_per_sheet'
			));

			$Form->DefaultValues['labels_per_sheet'] = 4;
			$labelsPerSheet = array(
				//6 => '6',
				4 => '4'
			);
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => false,
				CForm::allowAllOption => true,
				CForm::options => $labelsPerSheet,
				CForm::name => 'labels_per_sheet'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'date_format',
				CForm::tooltip => $this->DATE_FORMAT_TOOLTIP,
				CForm::Label => 'Print full date'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'ft_date_format',
				CForm::tooltip => $this->DATE_FORMAT_TOOLTIP,
				CForm::Label => 'Print full date'
			));

			$ft_menu_id = $Form->value('ft_menus');

			$arr = $this->create_generic_data($pig, $menu_id, $store_id, true, 0);
			$ft_arr = $this->create_generic_data($pig, $ft_menu_id, $store_id, true, 0, true);

			CLog::RecordReport('Session/Labels Generic', "Menu: $menu_id ~ Store: $store_id");

			if (empty($arr))
			{
				$tpl->setStatusMsg("Sorry, instructional sets for this menu's entrees are not currently available.  Please check back again at a later time.");
			}
			else
			{
				$options = array();
				$options[0] = "-- Next, Pick a Menu Item --";

				foreach ($arr as $element)
				{
					$grayClause = "";
					if ($element['override_inventory'] - $element['number_sold'] <= 0)
					{
						$grayClause = "|||";
					}

					$options[$element['id']] = $element['category'] . "^^^" . $grayClause . $element['menu_item'] . " " . (($element['menu_item_category_id'] == 4) ? '(Pre-Assembled)' : '') . " - " . CMenuItem::translatePricingType($element['plan_type']);
				}
			}

			if (empty($ft_arr))
			{
				$tpl->setStatusMsg("Sorry, instructional sets for this menu's Sides &amp; Sweets items are not currently available.  Please check back again at a later time.");
			}
			else
			{
				$ft_options = array();
				$ft_options[0] = "-- Next, Pick a Sides &amp; Sweets Item --";

				foreach ($ft_arr as $element)
				{
					$name = $element['menu_item'];

					if ($element['override_inventory'] <= 0)
					{
						$name = "|||" . $name;
					}

					$ft_options[$element['id']] = $element['category'] . "^^^" . $name;
				}
			}

			if (count($options) > 1)
			{

				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::options => $options,
					CForm::name => 'menu_items',
					CForm::width => 500,
					CForm::onChangeSubmit => false
				));

				$Form->DefaultValues['labels_per_sheet'] = 4;
				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::onChangeSubmit => false,
					CForm::allowAllOption => true,
					CForm::options => array(
						//6 => '6',
						4 => '4'
					),
					CForm::name => 'labels_per_sheet'
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::name => 'date_format',
					CForm::tooltip => $this->DATE_FORMAT_TOOLTIP,
					CForm::Label => 'Print full date'
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::maxlength => 1,
					CForm::size => 3,
					CForm::default_value => 1,
					CForm::name => 'labels_to_print'
				));
				$Form->AddElement(array(
					CForm::type => CForm::Submit,
					CForm::name => 'report_submit',
					CForm::css_class => 'btn btn-primary btn-sm',
					CForm::value => 'Print Labels'
				));
			}

			if (count($ft_options) > 1)
			{

				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::options => $ft_options,
					CForm::name => 'ft_menu_items',
					CForm::width => 500,
					CForm::onChangeSubmit => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::maxlength => 1,
					CForm::size => 3,
					CForm::default_value => 1,
					CForm::name => 'ft_labels_to_print'
				));
				$Form->AddElement(array(
					CForm::type => CForm::Submit,
					CForm::name => 'ft_report_submit',
					CForm::css_class => 'btn btn-primary btn-sm',
					CForm::value => 'Print Labels'
				));
				$Form->DefaultValues['ft_labels_per_sheet'] = 4;
				$labelsPerSheet = array(
					6 => '6',
					4 => '4'
				);

				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::onChangeSubmit => false,
					CForm::allowAllOption => true,
					CForm::options => $labelsPerSheet,
					CForm::name => 'ft_labels_per_sheet'
				));
				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::name => 'ft_date_format',
					CForm::tooltip => $this->DATE_FORMAT_TOOLTIP,
					CForm::Label => 'Print full date'
				));
			}

			$showLongDate = false;

			if ((!empty($_POST['report_all']) && $_POST['report_all']) || (!empty($_POST['report_all_four']) && $_POST['report_all_four']))
			{
				$items_per_page = 6;
				if (!empty($_POST['report_all_four']))
				{
					$items_per_page = 4;
				}
				$arr = $this->create_generic_data_all($pig, $menu_id, $store_id, false);
				CLog::RecordReport('Session/Labels Generic All', "Menu: $menu_id ~ Store: $store_id");
				$mastcounter = 0;
				$counter = count($arr);
				$pages_to_print = 1;
				if ($counter > 0)
				{
					$masterarr = array();

					for ($l = 0; $l < $counter; $l++)
					{
						$tempArr = $arr[$l];

						//$items_per_page = 6;
						$total_items_to_iter = $items_per_page * $pages_to_print;
						// $total_items_to_iter--;
						for ($i = 0; $i < $total_items_to_iter; $i++)
						{
							$masterarr[$mastcounter] = $tempArr;
							$mastcounter++;
						}
					}

					$showInterface = 0;
					$tpl->assign('order_details', $masterarr);
				}
				else
				{
					$tpl->setStatusMsg('No results for the selected date or time.');
				}
				$labelsPerSheet = $items_per_page;

				if ($Form->value('date_format'))
				{
					$showLongDate = $Form->value('date_format');
				}
			}

			if ($Form->value('report_submit'))
			{
				$pages_to_print = $Form->value('labels_to_print');
				if ($pages_to_print <= 0 || $pages_to_print > $pagesToPrintMax)
				{
					$tpl->setStatusMsg("Please enter a number between 1 and 4");
				}
				else
				{
					$mid = $Form->value('menu_items');

					if ($mid == 0)
					{
						$tpl->setStatusMsg("Please select a menu item from the list");
					}
					else
					{
						$arr = $this->create_generic_data($pig, $menu_id, $store_id, false, $mid);
						CLog::RecordReport('Session/Labels Generic', "Menu: $menu_id ~ Store: $store_id");

						$mastcounter = 0;

						$counter = count($arr);

						if ($counter > 0)
						{
							$masterarr = array();

							for ($l = 0; $l < $counter; $l++)
							{
								$tempArr = $arr[$l];

								$items_per_page = 4;
								$total_items_to_iter = $items_per_page * $pages_to_print;
								// $total_items_to_iter--;
								for ($i = 0; $i < $total_items_to_iter; $i++)
								{
									$masterarr[$mastcounter] = $tempArr;
									$mastcounter++;
								}
							}

							$showInterface = 0;
							$tpl->assign('order_details', $masterarr);
						}
						else
						{
							$tpl->setStatusMsg('No results for the selected date or time.');
						}
					}
				}

				$labelsPerSheet = 4;
				if ($Form->value('date_format'))
				{
					$showLongDate = $Form->value('date_format');
				}
			}

			if (!empty($_POST['ft_report_all']) && $_POST['ft_report_all'])
			{
				$arr = $this->create_generic_data_all($pig, $ft_menu_id, $store_id, true);
				CLog::RecordReport('Session/Labels Generic All', "Menu: $menu_id ~ Store: $store_id");
				$mastcounter = 0;
				$counter = count($arr);
				$pages_to_print = 1;
				if ($counter > 0)
				{
					$masterarr = array();

					for ($l = 0; $l < $counter; $l++)
					{
						$tempArr = $arr[$l];

						$tempArr['is_finishing_touch'] = true;

						$items_per_page = isset ($_REQUEST["ft_labels_per_sheet"]) ? $_REQUEST["ft_labels_per_sheet"] : 6;
						$total_items_to_iter = $items_per_page * $pages_to_print;
						// $total_items_to_iter--;
						for ($i = 0; $i < $total_items_to_iter; $i++)
						{
							$masterarr[$mastcounter] = $tempArr;
							$mastcounter++;
						}
					}

					$showInterface = 0;
					$tpl->assign('order_details', $masterarr);
				}
				else
				{
					$tpl->setStatusMsg("Sorry, instructional sets for this menu are not currently available.  Please check back again at a later time.");
				}
				$labelsPerSheet = isset ($_REQUEST["ft_labels_per_sheet"]) ? $_REQUEST["ft_labels_per_sheet"] : null;
				if ($Form->value('ft_date_format'))
				{
					$showLongDate = $Form->value('ft_date_format');
				}
			}

			if ($Form->value('ft_report_submit'))
			{
				$pages_to_print = $Form->value('ft_labels_to_print');
				if ($pages_to_print <= 0 || $pages_to_print > $pagesToPrintMax)
				{
					$tpl->setStatusMsg("Please enter a number between 1 and 4");
				}
				else
				{
					$mid = $Form->value('ft_menu_items');

					if ($mid == 0)
					{
						$tpl->setStatusMsg("Please select a Sides &amp; Sweets item from the list");
					}
					else
					{
						$arr = $this->create_generic_data($pig, $ft_menu_id, $store_id, false, $mid, true);
						CLog::RecordReport('Session/Labels Generic', "Menu: $menu_id ~ Store: $store_id");

						$mastcounter = 0;

						$counter = count($arr);

						if ($counter > 0)
						{
							$masterarr = array();

							for ($l = 0; $l < $counter; $l++)
							{
								$tempArr = $arr[$l];
								$tempArr['is_finishing_touch'] = true;

								$items_per_page = isset ($_REQUEST["ft_labels_per_sheet"]) ? $_REQUEST["ft_labels_per_sheet"] : 6;
								$total_items_to_iter = $items_per_page * $pages_to_print;
								// $total_items_to_iter--;
								for ($i = 0; $i < $total_items_to_iter; $i++)
								{
									$masterarr[$mastcounter] = $tempArr;
									$mastcounter++;
								}
							}

							$showInterface = 0;
							$tpl->assign('order_details', $masterarr);
						}
						else
						{
							$tpl->setStatusMsg('No results for the selected date or time.');
						}
					}
				}
				$labelsPerSheet = isset ($_REQUEST["ft_labels_per_sheet"]) ? $_REQUEST["ft_labels_per_sheet"] : null;
				if ($Form->value('ft_date_format'))
				{
					$showLongDate = $Form->value('ft_date_format');
				}
			}

			$formArray = $Form->render();
			$tpl->assign('form_list', $formArray);
		}
		$tpl->assign('show_borders', 0);
		$tpl->assign('interface', $showInterface);

		$tpl->assign('showLongDate', $showLongDate);

		if (!empty($labelsPerSheet) && $labelsPerSheet == "4")
		{
			$output = $tpl->fetch('admin/reports_customer_menu_item_labels_four.tpl.php');
			echo $output;
			exit;
		}
	}

	function create_generic_data_all($pig, $menu_id, $store_id, $isFinishingTouch)
	{
		$output_array = array();
		$count = 0;
		if ($store_id != null)
		{
			$store = DAO_CFactory::create('store');
			$store->id = $store_id;
			$store->find(true);
			$store_name = $store->store_name;
			$store_phone = $store->telephone_day;
			$store_address_line1 = $store->address_line1;
			$store_address_line2 = $store->address_line2;
			$store_city = $store->city;
			$store_state = $store->state_id;
			$store_postal = $store->postal_code;
		}
		else
		{
			$store_name = "Not Available";
			$store_phone = "Not Available";
		}

		$session_start = date("F Y");

		$curMenuObj = DAO_CFactory::create('menu');
		if ($curMenuObj->findCurrentByDate())
		{
			$curMenuObj->fetch();

			if ($curMenuObj->id <= $menu_id)
			{
				$menuObj = DAO_CFactory::create('menu');
				$menuObj->id = $menu_id;

				if ($menuObj->find(true))
				{
					$session_start = $menuObj->menu_name;
				}
			}
		}

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_is_bundle' => true,
			'exclude_menu_item_category_sides_sweets' => !$isFinishingTouch,
			'exclude_menu_item_category_core' => $isFinishingTouch,
			'exclude_menu_item_category_efl' => true
		));

		while ($DAO_menu_item->fetch())
		{
			if ((!$DAO_menu_item->isMenuItem_SidesSweets() && !$isFinishingTouch) || ($DAO_menu_item->isMenuItem_SidesSweets() && $isFinishingTouch))
			{
				$output_array[$count]['id'] = $DAO_menu_item->id;
				$output_array[$count]['menu_item'] = CAppUtil::toPDFCharConversions($DAO_menu_item->menu_item_name);
				$output_array[$count]['plan_type'] = $DAO_menu_item->pricing_type;
				$output_array[$count]['servings_per_item'] = $DAO_menu_item->servings_per_item;
				$output_array[$count]['instructions'] = isset($DAO_menu_item->instructions) ? $DAO_menu_item->instructions : null;
				$output_array[$count]['prep_time'] = !empty($DAO_menu_item->prep_time) ? $DAO_menu_item->prep_time : null;
				$output_array[$count]['container_type'] = $DAO_menu_item->container_type;
				$output_array[$count]['serving_suggestions'] = isset($DAO_menu_item->serving_suggestions) ? CAppUtil::toPDFCharConversions($DAO_menu_item->serving_suggestions) : "";
				$output_array[$count]['best_prepared_by'] = isset($DAO_menu_item->best_prepared_by) ? $DAO_menu_item->best_prepared_by : "";
				$output_array[$count]['instructions'] = mb_convert_encoding($DAO_menu_item->instructions, 'Windows-1252', 'HTML-ENTITIES');
				$output_array[$count]['instructions_air_fryer'] = mb_convert_encoding($DAO_menu_item->instructions_air_fryer, 'Windows-1252', 'HTML-ENTITIES');
				$output_array[$count]['instructions_crock_pot'] = mb_convert_encoding($DAO_menu_item->instructions_crock_pot, 'Windows-1252', 'HTML-ENTITIES');
				$output_array[$count]['instructions_instant_pot'] = mb_convert_encoding($DAO_menu_item->instructions_instant_pot, 'Windows-1252', 'HTML-ENTITIES');
				$output_array[$count]['instructions_grill'] = mb_convert_encoding($DAO_menu_item->instructions_grill, 'Windows-1252', 'HTML-ENTITIES');

				$output_array[$count]['grand_total'] = 0;
				$output_array[$count]['firstname'] = null;
				$output_array[$count]['total_items'] = null;
				$output_array[$count]['menu_program_id'] = $DAO_menu_item->menu_program_id;

				$output_array[$count]['order_menu_program_id'] = $pig;

				$output_array[$count]['recipe_id'] = $DAO_menu_item->recipe_id;
				$output_array[$count]['flag_under_thirty'] = $DAO_menu_item->flag_under_thirty;
				$output_array[$count]['flag_heart_healthy'] = $DAO_menu_item->flag_heart_healthy;
				$output_array[$count]['flag_grill_friendly'] = $DAO_menu_item->flag_grill_friendly;
				$output_array[$count]['flag_cooks_from_frozen'] = $DAO_menu_item->flag_cooks_from_frozen;
				$output_array[$count]['flag_crockpot'] = $DAO_menu_item->flag_crockpot;
				$output_array[$count]['flag_instant_pot'] = $DAO_menu_item->flag_instant_pot;
				$output_array[$count]['flag_under_400'] = $DAO_menu_item->flag_under_400;
				$output_array[$count]['flag_no_added_salt'] = $DAO_menu_item->flag_no_added_salt;
				$output_array[$count]['cooking_instruction_youtube_id'] = $DAO_menu_item->cooking_instruction_youtube_id;
				$output_array[$count]['ltd_menu_item_value'] = $DAO_menu_item->ltd_menu_item_value;

				$output_array[$count]['address_line1'] = $store_address_line1;
				$output_array[$count]['address_line2'] = $store_address_line2;
				$output_array[$count]['city'] = $store_city;
				$output_array[$count]['state_id'] = $store_state;
				$output_array[$count]['postal_code'] = $store_postal;
				$output_array[$count]['store_phone'] = $store_phone;
				$output_array[$count]['store_name'] = $store_name;
				$output_array[$count]['item_number'] = null;
				$output_array[$count]['lastname'] = null;
				$output_array[$count]['session_start'] = ($session_start ? $session_start : null);

				$count++;
			}
		}

		return $output_array;
	}

	function create_generic_data($pig, $menu_id, $store_id, $menu_list_only = false, $singleItemID = false, $isFinishingTouch = false)
	{
		$output_array = array();
		$count = 0;
		if ($menu_list_only == false)
		{
			if ($store_id != null)
			{
				$store = DAO_CFactory::create('store');
				$store->id = $store_id;
				$store->find(true);
				$store_name = $store->store_name;
				$store_phone = $store->telephone_day;
				$store_address_line1 = $store->address_line1;
				$store_address_line2 = $store->address_line2;
				$store_city = $store->city;
				$store_state = $store->state_id;
				$store_postal = $store->postal_code;
			}
			else
			{
				$store_name = "Not Available";
				$store_phone = "Not Available";
			}
		}

		$session_start = date("F Y");

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_is_bundle' => true,
			'exclude_menu_item_category_sides_sweets' => !$isFinishingTouch,
			'exclude_menu_item_category_core' => $isFinishingTouch,
			'exclude_menu_item_category_efl' => $isFinishingTouch
		));

		while ($DAO_menu_item->fetch())
		{
			//TODO $singleItemID is not being used in the query but should be..this is just cleaning the results
			if (!is_null($singleItemID) && $singleItemID != 0 && $singleItemID != false && $singleItemID != $DAO_menu_item->id)
			{
				continue;
			}

			if ((!$DAO_menu_item->isMenuItem_SidesSweets() && !$isFinishingTouch) || ($DAO_menu_item->isMenuItem_SidesSweets() && $isFinishingTouch))
			{
				$output_array[$count]['id'] = $DAO_menu_item->id;
				$output_array[$count]['menu_item'] = CAppUtil::toPDFCharConversions($DAO_menu_item->menu_item_name);
				$output_array[$count]['plan_type'] = $DAO_menu_item->pricing_type;
				$output_array[$count]['menu_item_category_id'] = $DAO_menu_item->menu_item_category_id;
				$output_array[$count]['is_store_special'] = $DAO_menu_item->is_store_special;
				$output_array[$count]['servings_per_item'] = $DAO_menu_item->servings_per_item;

				if ($menu_list_only == false)
				{
					$output_array[$count]['prep_time'] = !empty($DAO_menu_item->prep_time) ? $DAO_menu_item->prep_time : null;
					$output_array[$count]['container_type'] = $DAO_menu_item->container_type;
					$output_array[$count]['serving_suggestions'] = isset($DAO_menu_item->serving_suggestions) ? $DAO_menu_item->serving_suggestions : "";
					$output_array[$count]['best_prepared_by'] = isset($DAO_menu_item->best_prepared_by) ? $DAO_menu_item->best_prepared_by : "";
					$output_array[$count]['instructions'] = mb_convert_encoding($DAO_menu_item->instructions, 'Windows-1252', 'HTML-ENTITIES');
					$output_array[$count]['instructions_air_fryer'] = mb_convert_encoding($DAO_menu_item->instructions_air_fryer, 'Windows-1252', 'HTML-ENTITIES');
					$output_array[$count]['instructions_crock_pot'] = mb_convert_encoding($DAO_menu_item->instructions_crock_pot, 'Windows-1252', 'HTML-ENTITIES');
					$output_array[$count]['instructions_instant_pot'] = mb_convert_encoding($DAO_menu_item->instructions_instant_pot, 'Windows-1252', 'HTML-ENTITIES');
					$output_array[$count]['instructions_grill'] = mb_convert_encoding($DAO_menu_item->instructions_grill, 'Windows-1252', 'HTML-ENTITIES');

					$output_array[$count]['grand_total'] = 0;
					$output_array[$count]['firstname'] = null;
					$output_array[$count]['total_items'] = null;
					$output_array[$count]['menu_program_id'] = $DAO_menu_item->menu_program_id;

					$output_array[$count]['order_menu_program_id'] = $pig;
					$output_array[$count]['icons'] = $DAO_menu_item->icons;

					$output_array[$count]['recipe_id'] = $DAO_menu_item->recipe_id;

					$output_array[$count]['address_line1'] = $store_address_line1;
					$output_array[$count]['address_line2'] = $store_address_line2;
					$output_array[$count]['city'] = $store_city;
					$output_array[$count]['state_id'] = $store_state;
					$output_array[$count]['postal_code'] = $store_postal;
					$output_array[$count]['store_phone'] = $store_phone;
					$output_array[$count]['store_name'] = $store_name;

					$output_array[$count]['item_number'] = null;
					$output_array[$count]['lastname'] = null;
					$output_array[$count]['session_start'] = ($session_start ? $session_start : null);
				}
				else
				{
					if ($isFinishingTouch)
					{
						$output_array[$count]['category'] = $DAO_menu_item->subcategory_label;
					}
					else
					{
						$output_array[$count]['category'] = ($DAO_menu_item->menu_item_category_id != 4 ? 'Core' : ($DAO_menu_item->is_store_special ? 'Extended Fast Lane' : 'Core'));
					}

					$output_array[$count]['override_inventory'] = $DAO_menu_item->override_inventory;
					$output_array[$count]['number_sold'] = $DAO_menu_item->number_sold;
				}
				$count++;
			}
		}

		return $output_array;
	}

	static function create_view_all_orders($session_id, &$other_details, $menu_id, &$store_id, $suppressFastlane = false, $booking_id = false)
	{
		if ($menu_id <= 0)
		{
			throw new Exception('Menu ID is Incorrect ' . $menu_id);
		}

		$DAO_booking = DAO_CFactory::create('booking', true);
		if ($booking_id)
		{
			$DAO_booking->id = $booking_id;
		}
		$DAO_booking->status = CBooking::ACTIVE;
		$DAO_booking->session_id = $session_id;
		$DAO_session = DAO_CFactory::create('session');
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store'));
		$DAO_booking->joinAddWhereAsOn($DAO_session);
		$DAO_booking->joinAddWhereAsOn(DAO_CFactory::create('user'));
		$DAO_booking->joinAddWhereAsOn(DAO_CFactory::create('orders'));
		$DAO_booking->orderBy("user.lastname ASC");
		$DAO_booking->find();
		$count = 0;

		$order_info = array();
		while ($DAO_booking->fetch())
		{
			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $menu_id;
			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $store_id,
				'join_order_item_order_id' => array($DAO_booking->order_id),
				'join_order_item_order' => 'INNER',
				'exclude_menu_item_is_bundle' => true,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_core_preassembled' => ($suppressFastlane == "true" ? true : false),
				// Printing EFL is also currently governed by suppressing FL
				'exclude_menu_item_category_efl' => ($suppressFastlane == "true" ? true : false),
				'exclude_menu_item_category_sides_sweets' => true,
				'groupBy' => 'order_item.id'
			));

			while ($DAO_menu_item->fetch())
			{
				for ($var = 0; $var < $DAO_menu_item->DAO_order_item->item_count; $var++)
				{
					if (empty($order_info[$count]['item_number']))
					{
						$order_info[$count]['item_number'] = 0;
					}

					$order_info[$count]['item_number'] = $var + 1;

					if (!isset($order_info[$count]['total_items']))
					{
						$order_info[$count]['total_items'] = $DAO_menu_item->DAO_order_item->item_count;
					}
					else
					{
						$order_info[$count]['total_items'] += $DAO_menu_item->DAO_order_item->item_count;
					}

					$order_info[$count]['menu_item'] = CAppUtil::toPDFCharConversions($DAO_menu_item->menu_item_name);

					$order_info[$count]['entree_id'] = $DAO_menu_item->entree_id;
					$order_info[$count]['recipe_id'] = $DAO_menu_item->recipe_id;
					$order_info[$count]['store_name'] = $DAO_menu_item->DAO_store->store_name;
					$order_info[$count]['store_phone'] = $DAO_menu_item->DAO_store->telephone_day;
					$order_info[$count]['address_line1'] = $DAO_menu_item->DAO_store->address_line1;
					$order_info[$count]['address_line2'] = $DAO_menu_item->DAO_store->address_line2;
					$order_info[$count]['city'] = $DAO_menu_item->DAO_store->city;
					$order_info[$count]['state_id'] = $DAO_menu_item->DAO_store->state_id;
					$order_info[$count]['postal_code'] = $DAO_menu_item->DAO_store->postal_code;

					$order_info[$count]['serving_suggestions'] = $DAO_menu_item->serving_suggestions;
					$order_info[$count]['best_prepared_by'] = $DAO_menu_item->best_prepared_by;

					$order_info[$count]['servings_per_item'] = $DAO_menu_item->servings_per_item;

					$order_info[$count]['order_id'] = $DAO_booking->DAO_orders->id;
					$order_info[$count]['user_id'] = $DAO_booking->DAO_user->id;

					$order_info[$count]['menu_program_id'] = $DAO_menu_item->menu_program_id;
					$order_info[$count]['order_menu_program_id'] = $DAO_booking->DAO_orders->menu_program_id;
					$order_info[$count]['instructions'] = mb_convert_encoding($DAO_menu_item->instructions, 'Windows-1252', 'HTML-ENTITIES');
					$order_info[$count]['instructions_air_fryer'] = mb_convert_encoding($DAO_menu_item->instructions_air_fryer, 'Windows-1252', 'HTML-ENTITIES');
					$order_info[$count]['instructions_crock_pot'] = mb_convert_encoding($DAO_menu_item->instructions_crock_pot, 'Windows-1252', 'HTML-ENTITIES');
					$order_info[$count]['instructions_instant_pot'] = mb_convert_encoding($DAO_menu_item->instructions_instant_pot, 'Windows-1252', 'HTML-ENTITIES');
					$order_info[$count]['instructions_grill'] = mb_convert_encoding($DAO_menu_item->instructions_grill, 'Windows-1252', 'HTML-ENTITIES');
					$order_info[$count]['prep_time'] = $DAO_menu_item->prep_time;
					$order_info[$count]['container_type'] = $DAO_menu_item->container_type;

					$order_info[$count]['plan_type'] = $DAO_menu_item->pricing_type;
					$order_info[$count]['firstname'] = $DAO_booking->DAO_user->firstname;
					$order_info[$count]['lastname'] = $DAO_booking->DAO_user->lastname;
					$order_info[$count]['meal_customization'] = '';
					$customization = OrdersCustomization::initOrderCustomizationObj($DAO_booking->DAO_orders->order_customization);
					if ($DAO_booking->DAO_orders->hasOptedToCustomize() && !is_null($customization) && ($DAO_menu_item->is_preassembled == false || ($DAO_menu_item->is_preassembled == true && $DAO_booking->DAO_store->allow_preassembled_customization)))
					{
						$order_info[$count]['meal_customization'] = $customization->getMealCustomizationObj()->toString(',', true, '<br>', false);
					}

					$order_info[$count]['icons'] = $DAO_menu_item->icons;

					$order_info[$count]['session_start'] = CTemplate::dateTimeFormat($DAO_booking->DAO_session->session_start, VERBOSE);
					$order_info[$count]['session_start_database'] = $DAO_booking->DAO_session->session_start;
					$count++;
				}
			}
		}

		if (isset($_REQUEST['order_by']) && $_REQUEST['order_by'] == 'dinner')
		{
			usort($order_info, "self::sortArrayByMenuItem");
		}

		return $order_info;
	}
}

?>