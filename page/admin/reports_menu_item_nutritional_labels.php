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
require_once 'DAO/BusinessObject/CRecipe.php';
require_once 'includes/CAppUtil.inc';

class page_admin_reports_menu_item_nutritional_labels extends CPageAdminOnly
{

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	private function sortArrayByMenuItem($a, $b)
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

		$menu_id = isset ($_REQUEST["menuid"]) ? $_REQUEST["menuid"] : 0;
		$pig = 1;
		$break = isset ($_REQUEST["break"]) ? $_REQUEST["break"] : 0;
		$store_id = isset ($_REQUEST["store_id"]) ? $_REQUEST["store_id"] : 0;

		$labelsPerSheet = 6;

		if ($store_id == 0)
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $store_id;
		$Store->find(true);

		$tpl->assign('store', $Store);

		$showInterface = 1;

		$tpl->assign('success', true);

		$pagesToPrintMax = 4;
		$Form = new CForm();
		$Form->Repost = true;

		$Form->DefaultValues['label_action'] = "none";

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'label_action'
		));

		$lastMenu = 0;
		$curMenu = 100000;
		$activeMenus = CMenu::getCurrentAndFutureMenuArray();
		foreach ($activeMenus as $id => $thisMenu)
		{
			if ($id > $lastMenu)
			{
				$lastMenu = $id;
			}
			if ($id < $curMenu)
			{
				$curMenu = $id;
			}
		}

		// Get menu info
		$Menu = DAO_CFactory::create('menu');

		$Menu->selectAdd();
		$Menu->selectAdd("menu.*");

		$firstMenu = $lastMenu - 12;

		if ($menu_id > 0 && $menu_id < $firstMenu)
		{
			$firstMenu = $menu_id;
		}

		$Menu->whereAdd("menu.id <= $lastMenu and menu.id >= $firstMenu");
		$Menu->orderBy("menu.id DESC");
		$Menu->find();

		$menu_array = array();
		$ft_menu_array = array();
		$menu_months = array();

		while ($Menu->fetch())
		{
			$menu_array[$Menu->id] = CTemplate::dateTimeFormat($Menu->menu_start, "%B %Y");

			if ($Menu->id > 137)
			{
				$ft_menu_array[$Menu->id] = CTemplate::dateTimeFormat($Menu->menu_start, "%B %Y");
			}

			$menu_months[$Menu->id] = $Menu->menu_start;
		}

		if ($menu_id > 0)
		{
			$curMenu = $menu_id;
		}
		else
		{
			$curMenu = CMenu::getCurrentMenuId();
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
			CForm::onChangeSubmit => false,
			CForm::onChange => 'changeMenu',
			CForm::allowAllOption => true,
			CForm::options => $menu_array,
			CForm::name => 'menus'
		));

		$menu_id = $Form->value('menus');

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::onChange => 'changeMenu',
			CForm::allowAllOption => true,
			CForm::options => $ft_menu_array,
			CForm::name => 'ft_menus'
		));

		$ft_menu_id = $Form->value('ft_menus');

		$Form->DefaultValues['labels_per_sheet'] = 4;
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => array(
				6 => '6',
				4 => '4'
			),
			CForm::name => 'labels_per_sheet'
		));

		$Form->DefaultValues['ft_labels_per_sheet'] = 4;
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => array(
				6 => '6',
				4 => '4'
			),
			CForm::name => 'ft_labels_per_sheet'
		));

		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_efl' => false
		));

		$arr = array();
		while ($daoMenuItem->fetch())
		{
			$arr[$daoMenuItem->id] = clone $daoMenuItem;
		}

		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $ft_menu_id;
		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_sides_sweets' => false,
			'exclude_menu_item_category_core' => true,
			'exclude_menu_item_category_efl' => true
		));

		$ft_arr = array();
		while ($daoMenuItem->fetch())
		{
			$ft_arr[$daoMenuItem->id] = clone $daoMenuItem;
		}

		if (empty($arr))
		{
			$tpl->setStatusMsg("Sorry, nutrition information for this menu's entrees are not currently available.  Please check back again at a later time.");
		}
		else
		{
			$options = array();
			$options[0] = "-- Next, Pick a Menu Item --";

			foreach ($arr as $element)
			{
				$grayClause = "";
				if ($element->override_inventory - $element->number_sold <= 0)
				{
					$grayClause = "|||";
				}

				$options[$element->id] = ($element->menu_item_category_id != 4 ? 'Core' : ($element->is_store_special ? 'Extended Fast Lane' : 'Core')) . "^^^" . $grayClause . $element->menu_item_name . " " . (($element->menu_item_category_id == 4) ? '(Pre-Assembled)' : '')  . " - " . CMenuItem::translatePricingType($element->pricing_type);
			}
		}

		if (empty($ft_arr))
		{
			$tpl->setStatusMsg("Sorry, nutritional sets for this menu's Sides &amp; Sweets items are not currently available.  Please check back again at a later time.");
		}
		else
		{
			$ft_options = array();
			$ft_options[0] = "-- Next, Pick a Sides &amp; Sweets Item --";

			foreach ($ft_arr as $element)
			{
				$name = $element->menu_item_name;

				if ($element->override_inventory <= 0)
				{
					$name = "|||" . $name;
				}

				$ft_options[$element->id] = $element->subcategory_label . "^^^" . $name;
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

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::maxlength => 1,
				CForm::size => 3,
				CForm::default_value => 1,
				CForm::name => 'labels_to_print'
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
		}

		if (!empty($_POST['report_all']) && $_POST['report_all'])
		{
			$labelsPerSheet = $Form->value('labels_per_sheet');
			$menu_item_ids = "'" . implode("','", array_keys($options)) . "'";

			$arr = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, false, true);

			$masterarr = $this->prep_labels_per_sheet($arr, 1, $labelsPerSheet);

			CLog::RecordReport('Session/Labels Nutritionals All Entree', "Menu: $menu_id ~ Store: $store_id");

			if (!empty($masterarr))
			{
				$showInterface = 0;
				$tpl->assign('label_action', 'report_std');
				$tpl->assign('order_details', $masterarr);
			}
			else
			{
				$tpl->setStatusMsg("Sorry, nutritional sets for this menu are not currently available.  Please check back again at a later time.");
			}
		}
		else if ($Form->value('label_action') && $Form->value('label_action') == 'report_std')
		{
			$pages_to_print = $Form->value('labels_to_print');
			$labelsPerSheet = $Form->value('labels_per_sheet');
			if ($pages_to_print <= 0 || $pages_to_print > $pagesToPrintMax)
			{
				$tpl->setStatusMsg("Please enter a number between 1 and 4");
			}
			else
			{
				$menu_item_ids = $Form->value('menu_items');

				if ($menu_item_ids == 0)
				{
					$tpl->setStatusMsg("Please select a menu item from the list");
				}
				else
				{
					$arr = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, false, true);

					$masterarr = $this->prep_labels_per_sheet($arr, $pages_to_print, $labelsPerSheet);

					CLog::RecordReport('Session/Labels Nutritionals Single Entree', "Menu: $menu_id ~ Store: $store_id");

					if (!empty($masterarr))
					{
						$showInterface = 0;
						$tpl->assign('label_action', 'report_std');
						$tpl->assign('order_details', $masterarr);
					}
					else
					{
						$tpl->setStatusMsg("Sorry, nutritional sets for this menu are not currently available.  Please check back again at a later time.");
					}
				}
			}
		}

		if (!empty($_POST['ft_report_all']) && $_POST['ft_report_all'])
		{
			$menu_item_ids = "'" . implode("','", array_keys($ft_options)) . "'";
			$labelsPerSheet = $Form->value('ft_labels_per_sheet');

			$arr = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, false, true);

			$masterarr = $this->prep_labels_per_sheet($arr, 1, $labelsPerSheet);

			CLog::RecordReport('Session/Labels Nutritionals All FT', "Menu: $menu_id ~ Store: $store_id");

			if (!empty($masterarr))
			{
				$showInterface = 0;
				$tpl->assign('label_action', 'report_ft');
				$tpl->assign('order_details', $masterarr);
			}
			else
			{
				$tpl->setStatusMsg("Sorry, nutritional sets for this menu are not currently available.  Please check back again at a later time.");
			}
		}
		else if ($Form->value('label_action') && $Form->value('label_action') == 'report_ft')
		{
			$pages_to_print = $Form->value('ft_labels_to_print');
			$labelsPerSheet = $Form->value('ft_labels_per_sheet');
			if ($pages_to_print <= 0 || $pages_to_print > $pagesToPrintMax)
			{
				$tpl->setStatusMsg("Please enter a number between 1 and 4");
			}
			else
			{
				$menu_item_ids = $Form->value('ft_menu_items');

				if ($menu_item_ids == 0)
				{
					$tpl->setStatusMsg("Please select a Sides &amp; Sweets item from the list");
				}
				else
				{
					$arr = CRecipe::fetch_nutrition_data_by_mid($ft_menu_id, $menu_item_ids, false, true);

					$masterarr = $this->prep_labels_per_sheet($arr, $pages_to_print, $labelsPerSheet);

					CLog::RecordReport('Session/Labels Nutritionals Single FT', "Menu: $menu_id ~ Store: $store_id");

					if (!empty($masterarr))
					{
						$showInterface = 0;
						$tpl->assign('label_action', 'report_ft');
						$tpl->assign('order_details', $masterarr);
					}
					else
					{
						$tpl->setStatusMsg("Sorry, nutritional sets for this menu are not currently available.  Please check back again at a later time.");
					}
				}
			}
		}

		$formArray = $Form->render();
		$tpl->assign('form_list', $formArray);

		$tpl->assign('show_borders', 0);
		$tpl->assign('interface', $showInterface);

		if (!empty($labelsPerSheet) && $labelsPerSheet == "4")
		{
			$output = $tpl->fetch('admin/reports_menu_item_nutritional_labels_four.tpl.php');
			echo $output;
			exit;
		}
	}

	static function prep_labels_per_sheet($item_array, $pages_to_print = 1, $items_per_page = 6)
	{
		$masterarr = array();
		$mastcounter = 0;

		$total_items_to_iter = $items_per_page * $pages_to_print;

		foreach ($item_array as $arr_mid => $arr)
		{
			for ($i = 0; $i < $total_items_to_iter; $i++)
			{
				$masterarr[$mastcounter++] = $arr;
			}
		}

		return $masterarr;
	}
}

?>