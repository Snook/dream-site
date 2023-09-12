<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once ('includes/CPageAdminOnly.inc');
require_once ('includes/CSessionReports.inc');
require_once ('includes/CDreamReport.inc');
require_once ('includes/DAO/Booking.php');
require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');



class page_admin_reports_p_and_l_input extends CPageAdminOnly
{
	private $currentStore = null;
	private $PandLAccess = true;

	const LIMITED_P_AND_L_ACCESS_SECTION_ID = 8;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}


	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();

		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);


		if (!$hasPandLAccess)
		{
			CApp::bounce('/?page=admin_access_error&pagename=Profit and Loss Input Form');
		}

		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();

		$isCorporate = false;
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select is_corporate_owned from store where id = {$this->currentStore}");
		$storeObj->fetch();

		// Special privileges handling
		if ($storeObj->is_corporate_owned && CUser::getCurrentUser()->user_type == CUser::OPS_LEAD)
		{
			$isCorporate = true;
		}
		else
		{
			$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);
		}

		if (!$hasPandLAccess && !$isCorporate)
		{
			CApp::bounce('/?page=admin_access_error&pagename=Profit and Loss Input Form');
		}

		$this->runSiteAdmin();
	}


	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();

		$isCorporate = false;
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select is_corporate_owned from store where id = {$this->currentStore}");
		$storeObj->fetch();

		// Special privileges handling
		if ($storeObj->is_corporate_owned && CUser::getCurrentUser()->user_type == CUser::FRANCHISE_MANAGER)
		{
			$isCorporate = true;
		}
		else
		{
	   		 $hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);
		}

	    if (!$hasPandLAccess && !$isCorporate)
	    {
	        CApp::bounce('/?page=admin_access_error&pagename=Profit and Loss Input Form');
	    }

		$this->runSiteAdmin();
	}


 	function runFranchiseOwner()
 	{
	 	$this->currentStore = CApp::forceLocationChoice();


		$this->runSiteAdmin();
	}

 	function runSiteAdmin()
 	{
 		$tpl = CApp::instance()->template();

 		$Form = new CForm();
 		$Form->Repost = TRUE;

 		$isHomeOfficeAccess  = true;

 		if (CUser::getCurrentUser()->isFranchiseAccess())
 			$isHomeOfficeAccess = false;

 		$tpl->assign('isHomeOfficeAccess', $isHomeOfficeAccess);


 		if ( $this->currentStore )
 		{ //fadmins
 			$store = $this->currentStore;
 		}
 		else
 		{
 			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

 			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
 					CForm::allowAllOption => false,
 					CForm::showInactiveStores => false,
 					CForm::name => 'store'));

 			$store = $Form->value('store');
 			$this->currentStore = $store;
 		}

 		$storeObj = DAO_CFactory::create('store');
 		$storeObj->query("select is_corporate_owned from store where id = {$this->currentStore}");
 		$storeObj->fetch();

 		$month_array = array (1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');

 		if (isset($_REQUEST['date']))
 		{
			$dateParts = explode("-", $_REQUEST['date']);
			$year = $dateParts[0];
			$month = $dateParts[1];
			$monthnum = intval($month);
 		}
		else
		{
			$year = date("Y");
			$monthnum = date("n");
		}


 	 	$Form->AddElement(array(CForm::type=> CForm::Text,
 				CForm::name => "year_field_001",
 				CForm::required => true,
 				CForm::default_value => $year,
 				CForm::length => 6));


 		$Form->AddElement(array(CForm::type=> CForm::DropDown,
 				CForm::onChangeSubmit => false,
 				CForm::allowAllOption => false,
 				CForm::options => $month_array,
 				CForm::default_value => $monthnum,
 				CForm::name => 'month_popup'));


 		$month = $Form->value('month_popup');
 		$year = $Form->value('year_field_001');

 		$date = date("Y-m-d", mktime(0,0,0,$month, 1, $year));

 		$isCurrentMonth = false;
 		if ($month == date("n") && $year == date("Y"))
 			$isCurrentMonth = true;

 		$monthAsTS = mktime(0,0,0,$month, 1, $year);
 		$currentMonthAsTS = mktime(0,0,0,date("n"), 1, date("Y"));

 		$monthIsPassed = 'false';
 		if ($monthAsTS < $currentMonthAsTS)
 			$monthIsPassed = 'true';

 		$tpl->assign('monthIsPassed', $monthIsPassed);


 		$monthIsFuture = false;
 		if ($monthAsTS > $currentMonthAsTS)
 			$monthIsFuture = true;

 		$tpl->assign('isFutureMonth', $monthIsFuture);
  		$tpl->assign('isCurrentMonth', $isCurrentMonth);

 		$tpl->assign('month', $month);
 		$tpl->assign('year', $year);
 		$tpl->assign('store_id', $this->currentStore);

 		$formArray = $Form->render();
 		$tpl->assign('form_session_list', $formArray);


        $this->createAndSignFinancialForm($tpl, $this->currentStore, $month, $year);
	}



	function createAndSignFinancialForm($tpl, $store_id, $month, $year)
	{

	    $form = new CForm();
	    $form->Repost = TRUE;

	    $financialsObj = DAO_CFactory::create('store_monthly_profit_and_loss');

	    $financialsObj->date = date("Y-m-d", mktime(0,0,0,$month, 1, $year));
	    $financialsObj->store_id = $store_id;

        if ($financialsObj->find(true))
        {
            $form->DefaultValues = DAO::getCompressedArrayFromDAO($financialsObj, true, true);
        }
        else
        {
        	$form->DefaultValues["cost_of_goods_and_services"] = 0;
        	$form->DefaultValues["employee_wages"] = 0;
        	$form->DefaultValues["manager_salaries"] = 0;
        	$form->DefaultValues["owner_salaries"] = 0;
        	$form->DefaultValues["employee_hours"] = 0;
        	$form->DefaultValues["manager_hours"] = 0;
        	$form->DefaultValues["owner_hours"] = 0;
        	$form->DefaultValues["payroll_taxes"] = 0;
        	$form->DefaultValues["bank_card_merchant_fees"] = 0;
        	$form->DefaultValues["kitchen_and_office_supplies"] = 0;
        	$form->DefaultValues["total_marketing_and_advertising_expense"] = 0;
        	$form->DefaultValues["rent_expense"] = 0;
        	$form->DefaultValues["repairs_and_maintenance"] = 0;
        	$form->DefaultValues["utilities"] = 0;
        	$form->DefaultValues["monthly_debt_service"] = 0;
        	$form->DefaultValues["other_expenses"] = 0;
        	$form->DefaultValues["net_income"] = 0;
        }

        if (!isset($form->DefaultValues['cost_of_goods_and_services']) || $form->DefaultValues['cost_of_goods_and_services'] == 0)
        {

            $menuDate = date("Y-m-d", mktime(0,0,0,$month, 1, $year));
            list($menu_start, $interval) = CMenu::getMenuStartandInterval(false, $menuDate);


            $weeklyExpenses = DAO_CFactory::create('store_expenses');
            $weeklyExpenses->query("select  sum(store_expenses.total_cost) as total
                From store_expenses Where store_expenses.entry_date >= '$menu_start' and store_expenses.entry_date < DATE_ADD('$menu_start', INTERVAL $interval DAY) and store_id = $store_id and store_expenses.is_deleted = 0 and
                store_expenses.expense_type in ('SYSCO', 'OTHER_FOOD')");

            $weeklyExpenses->fetch();
            $form->DefaultValues['cost_of_goods_and_services'] = $weeklyExpenses->total;

            $tpl->assign('COGsMsg', "<span  id='cogs_msg' style='color:red;'>Note:  Update this field to tie to COGS on your Profit & Loss Statement from QuickBooks.  It auto-populates with the data you enter into your goal management report, but this is only an estimate.  Your P&L has your final COGS amount after all month-end adjustments have been made.</span>");

        }

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "cost_of_goods_and_services",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	        CForm::org_value => $form->DefaultValues['cost_of_goods_and_services'],
	        CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "employee_wages",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['employee_wages'],
	        CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "manager_salaries",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['manager_salaries'],
	        CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "owner_salaries",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['owner_salaries'],
	        CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "employee_hours",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input_count',
	    	CForm::org_value => $form->DefaultValues['employee_hours'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "manager_hours",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input_count',
	    	CForm::org_value => $form->DefaultValues['manager_hours'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "owner_hours",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input_count',
	    	CForm::org_value => $form->DefaultValues['owner_hours'],
	    	CForm::length => 10));


	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "payroll_taxes",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['payroll_taxes'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "bank_card_merchant_fees",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['bank_card_merchant_fees'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "kitchen_and_office_supplies",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['kitchen_and_office_supplies'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "total_marketing_and_advertising_expense",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => 'expense',
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['total_marketing_and_advertising_expense'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "rent_expense",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['rent_expense'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "repairs_and_maintenance",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['repairs_and_maintenance'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "utilities",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::dd_subtype => "expense",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['utilities'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "monthly_debt_service",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['monthly_debt_service'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "other_expenses",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['other_expenses'],
	    	CForm::length => 10));

	    $form->AddElement(array(CForm::type=> CForm::Text,
	        CForm::name => "net_income",
	        CForm::required => false,
	        CForm::dd_type => "p&l_widget",
	        CForm::css_class => 'gt_input',
	    	CForm::org_value => $form->DefaultValues['net_income'],
	    	CForm::length => 10));

	    $storeInfo = self::getStoreInfo($store_id, $month, $year);

	    $tpl->assign('storeInfo', $storeInfo);

	    $tpl->assign('p_and_l_form', $form->Render());


	}



	function setLivesChangedMetric($tpl, $store_id, $month, $year)
	{

	    $date = date("Y-m-d", mktime(0,0,0,$month, 1, $year));

	    $guestsDAO = DAO_CFactory::create('dashboard_metrics_guests');
	    $guestsDAO->query("select iq.*, dmg2.total_servings_sold as store_servings, dmg2.guest_count_total as store_guests from
	           (select avg(dmg.total_servings_sold) as nat_avg_servings, avg(dmg.guest_count_total) as nat_avg_guests from dashboard_metrics_guests dmg
	            join store s on s.id = dmg.store_id and s.active = 1
	           where date = '$date' and dmg.is_deleted = 0) as iq
	            join dashboard_metrics_guests dmg2 on dmg2.store_id = $store_id and dmg2.date = '$date' and dmg2.is_deleted = 0");
	    $guestsDAO->fetch();

        $livesChanged = array('store_guests' => $guestsDAO->store_guests,
	                		   'national_avg_guests' => CTemplate::number_format($guestsDAO->nat_avg_guests,2),
	                		  'percent_of_avg_guests' => CTemplate::divide_and_format($guestsDAO->store_guests, $guestsDAO->nat_avg_guests, 4) * 100,
	                		  'store_servings' => $guestsDAO->store_servings,
	                		   'national_avg_servings' => CTemplate::number_format($guestsDAO->nat_avg_servings,2),
	                		   'percent_of_avg_servings' => CTemplate::divide_and_format($guestsDAO->store_servings, $guestsDAO->nat_avg_servings, 4) * 100);

        $tpl->assign('lives_changed',$livesChanged);
	}

	static function getStoreInfo($store_id, $month, $year)
	{

	    $menuMonth = $month;
	    $menuYear = $year;

	    $rows = false;
        $retVal = array();

        $day = 1;
        $duration = "1 MONTH";
        $isTransitionMonth = false;
        $isMenuMonthBased = false;

        if ($year == 2017 && $month == 6)
        {
        	//transition month that requires a custom range
        	$duration = "32 DAY";
        	$isTransitionMonth = true;
        }
        else
        {
        	$menuMonthStart = strtotime("2017-07-01");

        	$curMonthTS = mktime(0,0,0,$month,1, $year);

        	if ($curMonthTS >= $menuMonthStart)
        	{
        		// new method using menu month
        		$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
        		list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
        		$start_date = strtotime($menu_start_date);
        		$year = date("Y", $start_date);
        		$month = date("n", $start_date);
        		$day = date("j", $start_date);

        		$duration = $interval . " DAY";

        		$isMenuMonthBased = true;
        	}
        }

	    CDreamReport::getOrderInfoByMonth ($store_id, $day, $month, $year ,$duration, $rows, 1);

		$rows['membership_fees'] = CDreamReport::getMembershipFeeRevenue($store_id, $day, $month, $year ,$duration);
		$rows['grand_total'] += $rows['membership_fees'];
		$rows['total_sales'] += $rows['membership_fees'];
		$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store_id);
		$rows['grand_total']  += $DoorDashRevenue;
		$rows['total_sales']  += $DoorDashRevenue;

	    $storeInfo = DAO_CFactory::create('store');
	    $storeInfo->query("select home_office_id, store_name, state_id, grand_opening_date, is_corporate_owned, opco_id, str.trade_area_id, scc.class from store
                        LEFT JOIN store_trade_area str on str.store_id = store.id and str.is_deleted = 0 and str.is_active = 1
	                       LEFT JOIN store_class_cache scc on scc.store_id = store.id
                        where store.id = $store_id");
	    $storeInfo->fetch();

	    $performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, $store_id);
	    $haspermanceoverride = false;
	    if (isset($performance[$store_id]))
	    {
	        $haspermanceoverride = true;
	    }

	    $giftCertValues = CDreamReport::giftCertificatesByType ($store_id, $day, $month, $year, $duration);
	    $programdiscounts = CDreamReport::ProgramDiscounts ($store_id, $day, $month, $year, $duration);

	    if (empty($rows['fundraising_total'])) $rows['fundraising_total'] = 0;
	    if (empty($rows['ltd_round_up_value'])) $rows['ltd_round_up_value'] = 0;
	    if (empty($rows['ltd_menu_item_value'])) $rows['ltd_menu_item_value'] = 0;
		if (empty($rows['subtotal_delivery_fee'])) $rows['subtotal_delivery_fee'] = 0;
		if (empty($rows['subtotal_bag_fee'])) $rows['subtotal_bag_fee'] = 0;

		$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan( $year . "-" . $month . "-" . $day,  $duration, $store_id);


		$royaltyFee = 0;
	    $marketingFee = 0;

	    $instance = new CStoreExpenses();
	    $expenseData = $instance->findExpenseDataByMonth ($store_id, $day, $month, $year, $duration);
	    CDreamReport::calculateFees ($rows, $store_id, $haspermanceoverride, $expenseData, $giftCertValues , $programdiscounts, $rows['fundraising_total'], $rows['ltd_menu_item_value'], $rows['subtotal_delivery_fee'], $rows['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $storeInfo->grand_opening_date, $month, $year);


	    if (empty($rows['grand_total'])) {$rows['grand_total'] = 0;}
	    if (empty($rows['sales_tax'])) {$rows['sales_tax'] = 0;}
	    if (empty($rows['total_discounts'])) {$rows['total_discounts'] = 0;}
	    if (empty($rows['mark_up'])) {$rows['mark_up'] = 0;}

        $base_sales =  $rows['grand_total'] - $rows['sales_tax'];
	    $gross_sales = $base_sales + $rows['total_discounts'] -  $rows['mark_up'];

	    $salesForceFee = 0;
	    if (($menuYear == 2018 && $menuMonth >= 9) || $menuYear > 2018 )
	    {
	        $salesForceFee = 250;
	    }



	    $retVal['salesforce_fee'] = $salesForceFee;
	    $retVal['marketing_total'] = $marketingFee;
	    $retVal['royalty'] = $royaltyFee;

	    if ($storeInfo->is_corporate_owned)
	    {
	    	$retVal['royalty'] = 0;
	   	}

	    $retVal['home_office_id'] = $storeInfo->home_office_id;
	    $retVal['store_name'] = $storeInfo->store_name;
	    $retVal['state_id'] = $storeInfo->state_id;
	    $retVal['mark_up'] = $rows['mark_up'];
	    $retVal['gross_sales'] = $gross_sales;
	    $retVal['adjustments_and_discounts'] =  $rows['total_discounts'] + $rows['discounts'] + ($rows['adjustments'] * -1 ) + $rows['fundraising_total'] + $rows['ltd_menu_item_value'] + $rows['subtotal_delivery_fee'] + $DoorDashFees;
	    $retVal['adjusted_gross_revenue'] = $gross_sales + $rows['mark_up'] - $retVal['adjustments_and_discounts'];
	    $retVal['trade_area_id'] = $storeInfo->trade_area_id;
	    $retVal['store_class'] = $storeInfo->class;
	    $retVal['opco_id'] = $storeInfo->opco_id;

	    return $retVal;

	}
}
?>