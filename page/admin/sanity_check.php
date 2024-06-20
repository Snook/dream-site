<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_sanity_check extends CPageAdminOnly {

	function runSiteAdmin()
	{

		$tpl = CApp::instance()->template();

		$tpl->assign('current_user_id', CUser::getCurrentUser()->id);

		$menus = CMenu::getActiveMenuArray();

		$firstMenu = array_shift($menus);
		if (isset($firstMenu)) {
			$tpl->assign('current_menu_id', $firstMenu['id']);
			$tpl->assign('current_menu_name', $firstMenu['name']);
		}

		$nextMenu = array_shift($menus);

		if (isset($nextMenu)) {
			$tpl->assign('next_menu_id', $nextMenu['id']);
			$tpl->assign('next_menu_name', $nextMenu['name']);
		}

		$this->doSetupForEntreeReport($tpl,$firstMenu);
		$this->doSetupForDashboard($tpl,$firstMenu);
		$this->doSetupForGrowthScoreboard($tpl,$firstMenu);
		$this->doSetupForNutritionalLabels($tpl,$firstMenu);
		$this->doSetupForCookingInstructions($tpl,$firstMenu);;


	}

	function doSetupForEntreeReport($tpl,$firstMenu)
	{
		$currentMenuStartElements =  explode('-',$firstMenu['menu_start']);
		$year = $currentMenuStartElements[0];
		$month = intval($currentMenuStartElements[1]) - 1;
		$obj = new stdClass();
		$obj->store = 175;
		$obj->session_type_all = 'on';
		$obj->page = 'admin_reports_entree';
		$obj->pickDate='3';
		$obj->month_popup=$month;
		$obj->year_field_001= $year;
		$obj->menu_or_calendar='menu';
		$obj->report_submit='Run Report';

		$JSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

		$tpl->assign('params_entree_report', $JSON);
	}

	function doSetupForDashboard($tpl,$firstMenu)
	{
		$obj = new stdClass();
		$obj->store_id = 175;
		$obj->monthMode = 'current';
		$obj->curMonthStr = $firstMenu['menu_start'];
		$obj->override_month = $firstMenu['menu_start'];
		$obj->report_type = 'dt_single_store';
		$obj->store = 175;
		$obj->hide_inactive = 'on';

		$JSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

		$tpl->assign('params_dashboard', $JSON);
	}

	function doSetupForGrowthScoreboard($tpl,$firstMenu)
	{

		$currentMenuStartElements =  explode('-',$firstMenu['menu_start']);
		$year = $currentMenuStartElements[0];
		$month = intval($currentMenuStartElements[1]) - 1;
		$obj = new stdClass();
		$obj->store = 175;
		$obj->export = '';
		$obj->month = $month;
		$obj->year = $year;
		$obj->report_type = 'session';
		$obj->report_submit = 'Run Web Report';

		$JSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

		$tpl->assign('params_growth_scoreboard', $JSON);
	}



	function doSetupForNutritionalLabels($tpl,$firstMenu)
	{
		$obj = new stdClass();
		$obj->label_action = 'none';
		$obj->menus = $firstMenu['id'];
		$obj->report_all = 'Generate All';
		$obj->menu_items = 0;
		$obj->labels_to_print = 1;
		$obj->labels_per_sheet = 4;
		$obj->ft_menus = $firstMenu['id'];
		$obj->ft_menu_items = 0;
		$obj->ft_labels_to_print = 1;
		$obj->ft_labels_per_sheet = 4;
		$JSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

		$tpl->assign('params_nutritional_labels', $JSON);
	}

	function doSetupForCookingInstructions($tpl,$firstMenu)
	{
		$obj = new stdClass();
		$obj->label_action = 'none';
		$obj->menus = $firstMenu['id'];
		$obj->report_all_four = 'Generate All';
		$obj->menu_items = 0;
		$obj->labels_to_print = 1;
		$obj->labels_per_sheet = 4;
		$obj->ft_menus = $firstMenu['id'];
		$obj->ft_menu_items = 0;
		$obj->ft_labels_to_print = 1;
		$obj->ft_labels_per_sheet = 4;
		$JSON = json_encode($obj,JSON_UNESCAPED_SLASHES);

		$tpl->assign('params_cooking_instructions', $JSON);
	}
}

?>