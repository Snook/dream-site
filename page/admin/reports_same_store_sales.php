<?php

/**
 * @author Carl Samuelson
 */
require_once ("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');
require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once ('phplib/PHPExcel/PHPExcel.php');
require_once ('ExcelExport.inc');

function yLabelFormat($inNumber)
{
	return "$" . number_format($inNumber, 0);
}

function xLabelFormat($inText)
{
	return $inText;
}

function dollarFormatter($inNumber)
{
	return "$" . number_format($inNumber, 0);
}

class page_admin_reports_same_store_sales extends CPageAdminOnly
{

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	private $show_store_selectors = true;

	private $store_details = null;

	function runHomeOfficeManager()
	{
		$this->show_store_selectors = true;
		
		$this->runSiteAdmin();
	}

	/*
	 * function runFranchiseOwner(){
	 * $this->show_store_selectors = false;
	 *
	 * $this->runSiteAdmin();
	 * }
	 */
	function exportExcel($tpl, $title)
	{
		if (empty($this->store_details))
		{
			return false;
		}
		
		$labels = array(
			"HO ID",
			"Name",
			"City",
			"State",
			"Revenue",
			"Revenue",
			"Diff",
			"% Diff",
			"Revenue",
			"Diff",
			"% Diff"
		);
		$sections = array(
			"Store" => 4,
			"Current Revenue" => 1,
			"Revenue Same Month Last Year" => 3,
			"Revenue Last Month" => 3
		);
		
		$columnDescs['A'] = array(
			'align' => 'left',
			'width' => 6
		);
		$columnDescs['B'] = array(
			'align' => 'left',
			'width' => 16
		);
		$columnDescs['C'] = array(
			'align' => 'left',
			'width' => 16
		);
		$columnDescs['D'] = array(
			'align' => 'left',
			'width' => 5
		);
		$columnDescs['E'] = array(
			'align' => 'left',
			'width' => 14,
			'type' => 'currency',
			'decor' => 'subtotal'
		);
		$columnDescs['F'] = array(
			'align' => 'center',
			'width' => 14,
			"type" => 'currency'
		);
		$columnDescs['G'] = array(
			'align' => 'center',
			'width' => 14,
			"type" => 'currency'
		);
		$columnDescs['H'] = array(
			'align' => 'center',
			'width' => 10,
			"type" => 'precent'
		);
		$columnDescs['I'] = array(
			'align' => 'center',
			'width' => 14,
			"type" => 'currency'
		);
		$columnDescs['J'] = array(
			'align' => 'center',
			'width' => 14,
			"type" => 'currency'
		);
		$columnDescs['K'] = array(
			'align' => 'center',
			'width' => 10,
			"type" => 'precent'
		);
		
		$numRows = count($this->store_details);
		$tpl->assign('labels', $labels);
		$tpl->assign('rows', $this->store_details);
		$tpl->assign('rowcount', $numRows);
		$tpl->assign('sectionHeader', $sections);
		$tpl->assign('col_descriptions', $columnDescs);
		
		$titleRows[] = array(
			"",
			"Sane Store Sales Report"
		);
		$titleRows[] = array(
			"",
			$title
		);
		$titleRows[] = array(
			"",
			"Report Run On",
			date("F j, Y, g:i:a")
		);
		
		$tpl->assign('title_rows', $titleRows);
		
		$_GET['export'] = 'xlsx';
	}

	function getStoreDetail($storeClause, $month_or_menu, $last_year, $last_month, $ly_cutOffTime = '2030-01-01 00:00:00', $lm_cutOffTime = '2030-01-01 00:00:00')
	{
		if (is_numeric($month_or_menu) && intval($month_or_menu) > 1)
		{
			// by menu
			$queryObj = DAO_CFactory::create('revenue_event');
			
			$queryObj->query("select iq2.*, sum(re3.amount) as last_month_total from
	        (select iq.*, sum(re2.amount) as last_year_total from
	            (select  re.store_id, st.home_office_id,  st.store_name, st.city, st.state_id, sum(re.amount) as month_total
	                from revenue_event re
	                $storeClause
	                where re.menu_id = $month_or_menu and re.is_deleted = 0
	                group by re.store_id order by re.store_id) as iq
	            left join revenue_event re2 on re2.store_id = iq.store_id and re2.menu_id = $last_year and re2.is_deleted = 0 and re2.event_time < '$ly_cutOffTime'
	            group by iq.store_id) as iq2
	            left join revenue_event re3 on re3.store_id = iq2.store_id and re3.menu_id = $last_month and re3.is_deleted = 0 and re3.event_time < '$lm_cutOffTime'
	                group by iq2.store_id");
		} else
		{
			// by month
			$queryObj = DAO_CFactory::create('revenue_event');
			
			$queryObj->query("select iq2.*, sum(re3.amount) as last_month_total from
                	        (select iq.*, sum(re2.amount) as last_year_total from
                	            (select  re.store_id, st.home_office_id, st.store_name, st.city, st.state_id, sum(re.amount) as month_total
                	                from revenue_event re
                	                $storeClause
                	                where (re.positive_affected_month = '$month_or_menu' or re.negative_affected_month = '$month_or_menu') and re.is_deleted = 0
                	                group by re.store_id order by re.store_id) as iq
                	            left join revenue_event re2 on re2.store_id = iq.store_id and (re2.positive_affected_month = '$last_year' or
                	                re2.negative_affected_month = '$last_year') and re2.is_deleted = 0 and re2.event_time < '$ly_cutOffTime'
                	                    group by iq.store_id) as iq2
                	            left join revenue_event re3 on re3.store_id = iq2.store_id and (re3.positive_affected_month = '$last_month' or
                	                re3.negative_affected_month = '$last_month') and re3.is_deleted = 0 and re3.event_time < '$lm_cutOffTime'
                	                group by iq2.store_id");
		}
		
		$retVal = array();
		
		while ($queryObj->fetch())
		{
			
			$retVal[$queryObj->store_id] = array(
				'hoid' => $queryObj->home_office_id,
				'name' => $queryObj->store_name,
				'city' => $queryObj->city,
				'state' => $queryObj->state_id,
				'cur_month' => (empty($queryObj->month_total) ? 0 : $queryObj->month_total),
				'last_year_month' => (empty($queryObj->last_year_total) ? 0 : $queryObj->last_year_total),
				'ly_diff' => '',
				'ly_percent_diff' => '',
				'last_month_month' => (empty($queryObj->last_month_total) ? 0 : $queryObj->last_month_total),
				'lm_diff' => '',
				'lm_percent_diff' => ''
			);
			
			$LYDiff = $queryObj->month_total - $queryObj->last_year_total;
			$LYSign = ($LYDiff < 0 ? "-" : "+");
			$retVal[$queryObj->store_id]['ly_diff'] = $LYSign . " $" . CTemplate::number_format(abs($LYDiff), 2);

			if ($queryObj->last_year_total > 0)
			{
				$LYpercentDiff = (($queryObj->month_total - $queryObj->last_year_total) / $queryObj->last_year_total) * 100;
			}
			else
			{
				$LYpercentDiff = 0;
			}

			$retVal[$queryObj->store_id]['ly_percent_diff'] = CTemplate::number_format($LYpercentDiff, 2) . "%";
			
			$LMDiff = $queryObj->month_total - $queryObj->last_month_total;
			$LMSign = ($LMDiff < 0 ? "-" : "+");
			$retVal[$queryObj->store_id]['lm_diff'] = $LMSign . " $" . CTemplate::number_format(abs($LMDiff), 2);

			if ($queryObj->last_month_total > 0)
			{
				$LMpercentDiff = (($queryObj->month_total - $queryObj->last_month_total) / $queryObj->last_month_total) * 100;
			}
			else
			{
				$LMpercentDiff = 0;
			}


			$retVal[$queryObj->store_id]['lm_percent_diff'] = CTemplate::number_format($LMpercentDiff, 2) . "%";
		}
		
		return $retVal;
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = TRUE;
		
		$tpl->assign('show_store_selectors', $this->show_store_selectors);
		
		$month = 0;
		$year = 0;
		
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'report_submit',
			CForm::value => 'Run Web Report'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'report_export',
			CForm::value => 'Export Excel Report'
		));
		
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
		
		// Date Selection Type
		$Form->DefaultValues['date_type'] = 'current_menu';
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'current_month'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'current_menu'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'other_month'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_other_month",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_other_month'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'other_menu'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_other_menu",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_other_menu'
		));
		
		$Form->DefaultValues['store_type'] = 'corporate_stores';
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'corporate_stores'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'franchise_stores'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'region'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RegionDropDown,
			CForm::name => "regions"
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'selected_stores'
		));
		
		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'requested_stores'
		));
		
		$tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));
		
		if ($Form->value('report_submit') || $Form->value('report_export'))
		{
			$reportTitle = "";
			$month_str = "";
			$ly_month_str = "";
			$lm_month_str = "";
			
			$export = false;
			if ($Form->value('report_export'))
			{
				$export = true;
			}
			
			$getStoreDetail = false;
			if (isset($_POST['get_store_detail']))
			{
				$getStoreDetail = true;
			}
			
			$other_menu_is_current = false;
			$other_menu_is_future = false;
			$other_month_is_future = false;
			$other_month_is_current = false;
			
			if ($Form->value('date_type') == 'other_month')
			{
				$curMonth = $Form->value('month_other_month') + 1;
				$curYear = $Form->value('year_other_month');
				
				if ($curMonth == date("n") && $curYear == date("Y"))
				{
					$other_month_is_current = true;
				} else 
					if (($curYear == date("Y") && $curMonth > date("n")) || $curYear == date("Y") > date("Y"))
					{
						$other_month_is_future = true;
					}
			}
			
			if ($Form->value('date_type') == 'other_menu')
			{
				$curMonth = $Form->value('month_other_menu') + 1;
				$curYear = $Form->value('year_other_menu');
				
				$currentMenuObj = DAO_CFactory::create('menu');
				$currentMenuObj->findForMonthAndYear($curMonth, $curYear);
				$currentMenuObj->fetch();
				$selected_menu_id = $currentMenuObj->id;
				
				$curMenuID = CMenu::getCurrentMenuId();
				
				if ($selected_menu_id == $curMenuID)
				{
					$other_menu_is_current = true;
				} else 
					if ($selected_menu_id > $curMenuID)
					{
						$other_menu_is_future = true;
					}
			}
			
			if ($Form->value('date_type') == 'current_month' || $other_month_is_current)
			{
				
				// current month
				$queryObj = DAO_CFactory::create('revenue_event');
				$storeClause = $this->getStoreClause($Form);
				
				$curMonth = date("n");
				$curYear = date("Y");
				$lastYear = $curYear - 1;
				$curDay = date("j");
				
				// $curYear--;
				// $lastYear--;
				
				$selectMonth = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $curYear));
				$ly_Month = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $curYear - 1));
				$lm_Month = date("Y-m-d", mktime(0, 0, 0, $curMonth - 1, 1, $curYear));
				$month_str = date("F Y", strtotime($selectMonth));
				$ly_month_str = date("F Y", strtotime($ly_Month));
				$lm_month_str = date("F Y", strtotime($lm_Month));
				
				$reportName = "To Date Revenue for " . $month_str;
				
				$reportTitle .= "Current Month: " . $month_str;
				
				$queryObj->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re 
                  $storeClause
                  where (re.positive_affected_month = '$selectMonth' or re.negative_affected_month = '$selectMonth') and re.is_deleted = 0
                  group by DATE(re.event_time) order by re.event_time");
				
				$revenueSum = 0;
				$dayArray = array();
				while ($queryObj->fetch())
				{
					$dayArray[$queryObj->day] = $queryObj->day_total;
					$revenueSum += $queryObj->day_total;
				}
				
				// current month last year
				$queryObjLastYear = DAO_CFactory::create('revenue_event');
				
				$selectMonthLY = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $lastYear));
				$cutoffTime = date("$lastYear-m-d H:i:s");
				
				$queryObjLastYear->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re 
                  $storeClause
                  where (re.positive_affected_month = '$selectMonthLY' or re.negative_affected_month = '$selectMonthLY') and re.is_deleted = 0 and re.event_time < '$cutoffTime'
                  group by DATE(re.event_time) order by re.event_time");
				
				$LYrevenueSum = 0;
				$LYdayArray = array();
				while ($queryObjLastYear->fetch())
				{
					$LYdayArray[$queryObjLastYear->day] = $queryObjLastYear->day_total;
					$LYrevenueSum += $queryObjLastYear->day_total;
				}
				
				// Last Month
				
				$selectMonthLM = date("Y-m-d", mktime(0, 0, 0, $curMonth - 1, 1, $curYear));
				$lastMonthMonth = date("m", strtotime($selectMonthLM));
				$lastMonthYear = date("Y", strtotime($selectMonthLM));
				$cutoffTimeLM = date("$lastMonthYear-$lastMonthMonth-d H:i:s");
				
				$queryObjLastMonth = DAO_CFactory::create('revenue_event');
				
				$queryObjLastMonth->query("select  sum(re.amount) as month_total from revenue_event re
                                        $storeClause                              
                                where (re.positive_affected_month = '$selectMonthLM' or re.negative_affected_month = '$selectMonthLM')
                                and re.is_deleted = 0 and re.event_time < '$cutoffTimeLM'");
				
				$queryObjLastMonth->fetch();
				$LMRevenueSum = $queryObjLastMonth->month_total;
				
				if (! $export)
				{
					$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, false, $reportName);
					$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, true, $reportName . " (acc)");
				}
				
				if ($getStoreDetail || $export)
				{
					$this->store_details = $this->getStoreDetail($storeClause, $selectMonth, $selectMonthLY, $selectMonthLM, $cutoffTime, $cutoffTimeLM);
				}
				
				// $title = "Same Store Sales Report for the Current Month of ";
			} else if ($Form->value('date_type') == 'current_menu' || $other_menu_is_current)
				{
					$curYear = date("Y");
					$lastYear = $curYear - 1;
					
					$queryObj = DAO_CFactory::create('revenue_event');
					$storeClause = $this->getStoreClause($Form);
					
					$cur_menu_id = CMenu::getCurrentMenuId();
					$last_year_menu_id = $cur_menu_id - 12;
					$last_month_menu_id = $cur_menu_id - 1;
					$menuObj = DAO_CFactory::create('menu');
					$menuObj->id = $cur_menu_id;
					$menuObj->find(true);
					
					$ly_MenuObj = DAO_CFactory::create('menu');
					$ly_MenuObj->id = $last_year_menu_id;
					$ly_MenuObj->find(true);
					
					$lm_MenuObj = DAO_CFactory::create('menu');
					$lm_MenuObj->id = $last_month_menu_id;
					$lm_MenuObj->find(true);
					
					$month_str = $menuObj->menu_name;
					$ly_month_str = $ly_MenuObj->menu_name;
					$lm_month_str = $lm_MenuObj->menu_name;
					
					$reportName = "To Date Revenue for " . $menuObj->menu_name . " Menu";
					
					$reportTitle .= "Current Menu: " . $menuObj->menu_name;
					
					$queryObj->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                     $storeClause
                     where re.menu_id = $cur_menu_id and re.is_deleted = 0
                     group by DATE(re.event_time) order by re.event_time");
					
					$revenueSum = 0;
					$dayArray = array();
					while ($queryObj->fetch())
					{
						$dayArray[$queryObj->day] = $queryObj->day_total;
						$revenueSum += $queryObj->day_total;
					}
					
					// for menus the cutoff time should be relative to the menu start
					$curMenuSeconds = time() - strtotime($menuObj->global_menu_start_date);
					$cutoffTime = strtotime($ly_MenuObj->global_menu_start_date) + $curMenuSeconds;
					$cutoffTime = date("Y-m-d H:i:s", $cutoffTime);
					
					$queryObjLastYear = DAO_CFactory::create('revenue_event');
					
					$queryObjLastYear->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                     $storeClause
                     where re.menu_id = $last_year_menu_id and re.is_deleted = 0 and re.event_time < '$cutoffTime'
                     group by DATE(re.event_time) order by re.event_time");
					
					$LYrevenueSum = 0;
					$LYdayArray = array();
					while ($queryObjLastYear->fetch())
					{
						$LYdayArray[$queryObjLastYear->day] = $queryObjLastYear->day_total;
						$LYrevenueSum += $queryObjLastYear->day_total;
					}
					
					// Last Month
					$queryObjLastMonth = DAO_CFactory::create('revenue_event');
					
					// for menus the cutoff time should be relative to the menu start
					$cutoffTimeLM = strtotime($lm_MenuObj->global_menu_start_date) + $curMenuSeconds;
					$cutoffTimeLM = date("Y-m-d H:i:s", $cutoffTimeLM);
					
					$queryObjLastMonth->query(" select sum(re.amount) as menu_total from revenue_event re
                     $storeClause
                     where re.menu_id = $last_month_menu_id and re.is_deleted = 0 and re.event_time < '$cutoffTimeLM'");
					
					$queryObjLastMonth->fetch();
					$LMRevenueSum = $queryObjLastMonth->menu_total;
					
					if (! $export)
					{
						$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, false, $reportName, - 1);
						$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, true, $reportName . " (acc)", - 1);
					}
					
					if ($getStoreDetail || $export)
					{
						$this->store_details = $this->getStoreDetail($storeClause, $cur_menu_id, $last_year_menu_id, $last_month_menu_id, $cutoffTime, $cutoffTimeLM);
					}
				} else 
					if ($Form->value('date_type') == 'other_month')
					{
						$cutoffTime = $cutoffTimeLM = '2030-01-01 00:00:00';
						
						// other calendar month
						$queryObj = DAO_CFactory::create('revenue_event');
						$storeClause = $this->getStoreClause($Form);
						
						$curMonth = $Form->value('month_other_month') + 1;
						$curYear = $Form->value('year_other_month');
						$lastYear = $curYear - 1;
						
						$selectMonth = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $curYear));
						$ly_Month = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $curYear - 1));
						$lm_Month = date("Y-m-d", mktime(0, 0, 0, $curMonth - 1, 1, $curYear));
						$month_str = date("F Y", strtotime($selectMonth));
						$ly_month_str = date("F Y", strtotime($ly_Month));
						$lm_month_str = date("F Y", strtotime($lm_Month));
						
						$reportName = "To Date Revenue for " . $month_str;
						
						$reportTitle .= "Month: " . $month_str;
						
						$queryObj->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                  $storeClause
                  where (re.positive_affected_month = '$selectMonth' or re.negative_affected_month = '$selectMonth') and re.is_deleted = 0
                  group by DATE(re.event_time) order by re.event_time");
						
						$revenueSum = 0;
						$dayArray = array();
						while ($queryObj->fetch())
						{
							$dayArray[$queryObj->day] = $queryObj->day_total;
							$revenueSum += $queryObj->day_total;
						}
						
						// current month last year
						$queryObjLastYear = DAO_CFactory::create('revenue_event');
						
						$selectMonthLY = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $lastYear));
						
						$cutOffClause = "";
						if ($other_month_is_future)
						{
							$cutoffTime = date("$lastYear-m-d H:i:s");
							$cutOffClause = " and re.event_time < '$cutoffTime' ";
						}
						
						$queryObjLastYear->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                      $storeClause
                  where (re.positive_affected_month = '$selectMonthLY' or re.negative_affected_month = '$selectMonthLY') and re.is_deleted = 0 $cutOffClause
                  group by DATE(re.event_time) order by re.event_time");
						
						$LYrevenueSum = 0;
						$LYdayArray = array();
						while ($queryObjLastYear->fetch())
						{
							$LYdayArray[$queryObjLastYear->day] = $queryObjLastYear->day_total;
							$LYrevenueSum += $queryObjLastYear->day_total;
						}
						
						// Last Month
						
						$queryObjLastMonth = DAO_CFactory::create('revenue_event');
						$selectMonthLM = date("Y-m-d", mktime(0, 0, 0, $curMonth - 1, 1, $curYear));
						$lastMonthMonth = date("m", strtotime($selectMonthLM));
						$lastMonthYear = date("Y", strtotime($selectMonthLM));
						
						$cutOffClauseLM = "";
						if ($other_month_is_future)
						{
							$cutoffTimeLM = date("$lastMonthYear-$lastMonthMonth-d H:i:s");
							$cutOffClauseLM = " and re.event_time < '$cutoffTime' ";
						}
						
						$queryObjLastMonth->query("select  sum(re.amount) as month_total from revenue_event re
                      $storeClause
                      where (re.positive_affected_month = '$selectMonthLM' or re.negative_affected_month = '$selectMonthLM') $cutOffClauseLM
                      and re.is_deleted = 0");
						
						$queryObjLastMonth->fetch();
						$LMRevenueSum = $queryObjLastMonth->month_total;
						
						if (! $export)
						{
							
							$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, false, $reportName);
							$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, true, $reportName . " (acc)");
						}
						
						if ($getStoreDetail || $export)
						{
							$this->store_details = $this->getStoreDetail($storeClause, $selectMonth, $selectMonthLY, $selectMonthLM, $cutoffTime, $cutoffTimeLM);
						}
					} else 
						if ($Form->value('date_type') == 'other_menu')
						{
							
							$cutoffTime = $cutoffTimeLM = '2030-01-01 00:00:00';
							
							$curMonth = $Form->value('month_other_menu') + 1;
							$targetYear = $Form->value('year_other_menu');
							$lastYear = $targetYear - 1;
							
							$queryObj = DAO_CFactory::create('revenue_event');
							$storeClause = $this->getStoreClause($Form);
							
							$currentMenuObj = DAO_CFactory::create('menu');
							$currentMenuObj->findForMonthAndYear($curMonth, $targetYear);
							$currentMenuObj->fetch();
							$cur_menu_id = $currentMenuObj->id;
							
							$last_year_menu_id = $cur_menu_id - 12;
							$ly_MenuObj = DAO_CFactory::create('menu');
							$ly_MenuObj->id = $last_year_menu_id;
							$ly_MenuObj->find(true);
							
							$menuObj = DAO_CFactory::create('menu');
							$menuObj->id = $cur_menu_id;
							$menuObj->find(true);
							
							$last_month_menu_id = $cur_menu_id - 1;
							$lm_MenuObj = DAO_CFactory::create('menu');
							$lm_MenuObj->id = $last_month_menu_id;
							$lm_MenuObj->find(true);
							
							$month_str = $currentMenuObj->menu_name;
							$ly_month_str = $menuObj->menu_name;
							$lm_month_str = $lm_MenuObj->menu_name;
							
							$reportName = "To Date Revenue for " . $menuObj->menu_name . " Menu";
							
							$reportTitle .= "Menu: " . $menuObj->menu_name;
							
							$selectMonth = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $targetYear));
							
							$queryObj->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                      $storeClause
                      where re.menu_id = $cur_menu_id and re.is_deleted = 0
                      group by DATE(re.event_time) order by re.event_time");
							
							$revenueSum = 0;
							$dayArray = array();
							while ($queryObj->fetch())
							{
								$dayArray[$queryObj->day] = $queryObj->day_total;
								$revenueSum += $queryObj->day_total;
							}
							
							// Last Year
							
							$cutOffClause = "";
							if ($other_menu_is_future)
							{
								// for menus the cutoff time should be relative to the menu start
								$curMenuSeconds = time() - strtotime($menuObj->global_menu_start_date);
								$cutoffTime = strtotime($ly_MenuObj->global_menu_start_date) + $curMenuSeconds;
								$cutoffTime = date("Y-m-d H:i:s", $cutoffTime);
								
								$cutOffClause = " and re.event_time < '$cutoffTime' ";
							}
							
							$queryObjLastYear = DAO_CFactory::create('revenue_event');
							
							$queryObjLastYear->query("select DATE(re.event_time) as day, sum(re.amount) as day_total from revenue_event re
                      $storeClause
                      where re.menu_id = $last_year_menu_id and re.is_deleted = 0 $cutOffClause
                      group by DATE(re.event_time) order by re.event_time");
							
							$LYrevenueSum = 0;
							$LYdayArray = array();
							while ($queryObjLastYear->fetch())
							{
								$LYdayArray[$queryObjLastYear->day] = $queryObjLastYear->day_total;
								$LYrevenueSum += $queryObjLastYear->day_total;
							}
							
							// Last Month
							$queryObjLastMonth = DAO_CFactory::create('revenue_event');
							
							$cutOffClauseLM = "";
							if ($other_menu_is_future)
							{
								// for menus the cutoff time should be relative to the menu start
								$cutoffTimeLM = strtotime($lm_MenuObj->global_menu_start_date) + $curMenuSeconds;
								$cutoffTimeLM = date("Y-m-d H:i:s", $cutoffTimeLM);
								$cutOffClauseLM = " and re.event_time < '$cutoffTimeLM' ";
							}
							
							$queryObjLastMonth->query(" select sum(re.amount) as menu_total from revenue_event re
                      $storeClause
                      where re.menu_id = $last_month_menu_id and re.is_deleted = 0 $cutOffClauseLM ");
							
							$queryObjLastMonth->fetch();
							$LMRevenueSum = $queryObjLastMonth->menu_total;
							
							if (! $export)
							{
								$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, false, $reportName, - 1);
								$this->renderGraphMonth($dayArray, $LYdayArray, $tpl, true, $reportName . " (acc)", - 1);
							}
							
							if ($getStoreDetail || $export)
							{
								$this->store_details = $this->getStoreDetail($storeClause, $cur_menu_id, $last_year_menu_id, $last_month_menu_id, $cutoffTime, $cutoffTimeLM);
							}
						}
			
			$storeType = "";
			if (! $this->show_store_selectors)
			{
				$store_id = CBrowserSession::getCurrentFadminStore();
				$tempStoreObj = DAO_CFactory::create('store');
				$tempStoreObj->query("select store_name from store where id = $store_id");
				$tempStoreObj->fetch();
				
				$storeType = $tempStoreObj->store_name;
			} else 
				if ($Form->value('store_type') == 'corporate_stores')
				{
					$storeType = "Corporate Stores";
				} else 
					if ($Form->value('store_type') == 'franchise_stores')
					{
						$storeType = "Franchise Stores";
					} else 
						if ($Form->value('store_type') == 'region')
						{
							$region = $Form->value('regions');
							
							if (! is_numeric($region))
							{
								throw new Exception("Invalid region submitted");
							}
							
							$Regions = DAO_CFactory::create('trade_area');
							$Regions->query("select region from trade_area where is_active = 1 and id = $region");
							$Regions->fetch();
							
							$storeType = $Regions->region . " Region Stores";
						} else 
							if ($Form->value('store_type') == 'selected_stores')
							{
								$storeType = "the Selected Stores";
							}
			
			$reportTitle .= " for " . $storeType;
			
			if ($export)
			{
				$this->exportExcel($tpl, $reportTitle);
				return;
			}
			
			if ($getStoreDetail)
			{
				$tpl->assign('store_details', $this->store_details);
			}
			
			$tpl->assign('revenue_sum', $revenueSum);
			$tpl->assign('day_array', $dayArray);
			
			$tpl->assign('ly_revenue_sum', $LYrevenueSum);
			$tpl->assign('ly_day_array', $LYdayArray);
			
			$LYDiff = $revenueSum - $LYrevenueSum;
			$LYSign = ($LYDiff < 0 ? "-" : "+");
			$tpl->assign('ly_diff', $LYSign . " $" . CTemplate::number_format(abs($LYDiff)), 2);
			
			$LYpercentDiff = (($revenueSum - $LYrevenueSum) / $LYrevenueSum) * 100;
			$tpl->assign('ly_percent_diff', CTemplate::number_format($LYpercentDiff, 2) . "%");
			
			$tpl->assign('lm_revenue_sum', $LMRevenueSum);
			
			$LMDiff = $revenueSum - $LMRevenueSum;
			$LMSign = ($LMDiff < 0 ? "-" : "+");
			$tpl->assign('lm_diff', $LMSign . " $" . CTemplate::number_format(abs($LMDiff)), 2);
			
			$LMpercentDiff = (($revenueSum - $LMRevenueSum) / $LMRevenueSum) * 100;
			$tpl->assign('lm_percent_diff', CTemplate::number_format($LMpercentDiff, 2) . "%");
			
			$tpl->assign('month_str', $month_str);
			$tpl->assign('ly_month_str', $ly_month_str);
			$tpl->assign('lm_month_str', $lm_month_str);
			
			$tpl->assign('report_title', $reportTitle);
		}
		
		$tpl->assign('query_form', $Form->render());
		$tpl->assign('page_title', 'Same Store Sales Report');
	}

	function getStoreClause($Form)
	{
		if (! $this->show_store_selectors)
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
			return "INNER JOIN store st on re.store_id = st.id and st.active = 1 and st.id = $store_id ";
		}
		
		$retVal = false;
		if ($Form->value('store_type') == 'corporate_stores')
		{
			$retVal = "INNER JOIN store st on re.store_id = st.id and st.is_corporate_owned = 1 and st.active = 1";
		} else 
			if ($Form->value('store_type') == 'franchise_stores')
			{
				$retVal = "INNER JOIN store st on re.store_id = st.id and st.is_corporate_owned = 0 and st.active = 1";
			} else 
				if ($Form->value('store_type') == 'region')
				{
					$region = $Form->value('regions');
					
					if (empty($region) or ! is_numeric($region))
					{
						throw new Exception("Invalid region id");
					}
					
					$retVal = "
                INNER JOIN store st on re.store_id = st.id and st.active = 1 
                INNER JOIN store_trade_area str on str.store_id = st.id and str.trade_area_id = $region and str.is_deleted = 0 and str.is_active = 1";
				} else 
					if ($Form->value('store_type') == 'selected_stores')
					{
						if ($_POST['requested_stores'] != 'all')
						{
							
							$tarr = explode(",", $_POST['requested_stores']);
							$newTarr = array();
							foreach ($tarr as $storeID)
							{
								if (is_numeric($storeID))
									$newTarr[] = $storeID;
							}
							
							$storeList = implode(",", $newTarr);
						} else
						{
							throw new Exception("TODO");
						}
						
						$retVal = "INNER JOIN store st on re.store_id = st.id and st.active = 1 and st.id in ($storeList) ";
					}
		
		return $retVal;
	}

	function formTimeSpanArray($firstDay, $lastDay)
	{
		$curDayTS = strtotime($firstDay);
		$lastDayTS = strtotime($lastDay) + 86400;
		
		$retValMainArray = array(
			$firstDay
		);
		$retValMajorTicks = array();
		$retValMinorTicks = array();
		$retValMajorlabels = array();
		
		$count = 1;
		while ($curDayTS < $lastDayTS)
		{
			++ $count;
			$curDayTS += 86400;
			
			if (date("H:i:s", $curDayTS) != "00:00:00")
			{
				// date light savings time change
				if (date("H:i:s", $curDayTS) == "23:00:00")
				{
					// fell back
					$curDayTS += 3600;
				} else
				{
					$curDayTS -= 3600;
				}
			}
			
			if (date("j", $curDayTS) == 1 || date("D", $curDayTS) == "Mon" || date("D", $curDayTS) == "Thu" || date("D", $curDayTS) == "Sun")
			{
				if (date("D", $curDayTS) == "Sun" || date("j", $curDayTS) == 1)
				{
					$retValMajorTicks[] = $count;
					$retValMajorlabels[] = date("Y-m-d", $curDayTS);
				} else
				{
					$retValMajorTicks[] = $count;
					$retValMajorlabels[] = date("D", $curDayTS);
				}
			} else
			{
				$retValMinorTicks[] = $count;
			}
			
			$retValMainArray[] = date("Y-m-d", $curDayTS);
		}
		
		return array(
			$retValMainArray,
			$retValMajorTicks,
			$retValMinorTicks,
			$retValMajorlabels
		);
	}

	function shiftDataOneYearForward($dayArray, $offset = 0)
	{
		$retVal = array();
		foreach ($dayArray as $date => $amount)
		{
			$arr = explode("-", $date);
			$arr[0] += 1;
			$date = implode("-", $arr);
			if ($offset != 0)
			{
				$dateTS = strtotime($date);
				$dateTS += 86400 * $offset;
				$date = date("Y-m-d", $dateTS);
			}
			
			$retVal[$date] = $amount;
		}
		
		return $retVal;
	}
	
	
	
	function renderGraphMonth($dayArray, $LYdayArray, $tpl, $accumulate, $reportName, $offset = 0)
	{
		$hasGD = false;
		if (function_exists('imagetypes'))
			$hasGD = true;
			
			$tpl->assign('hasGD', $hasGD);
			
			if ($hasGD)
			{
				
				try
				{
					require_once ('jpgraph/jpgraph.php');
					require_once ('jpgraph/jpgraph_line.php');
					require_once ('jpgraph/jpgraph_utils.inc.php');
					
					// $dayArray can have discontiguous days so make a well formed time line
					// also convert Last Year Dates to This year for direct comparsion
					
					$LYdayArray = $this->shiftDataOneYearForward($LYdayArray, $offset);
					
					$keysArr = array_keys($dayArray);
					$LYKeysArr = array_keys($LYdayArray);
					
					$firstDayLY = current($LYKeysArr);
					$lastDayLY = end($LYKeysArr);
					
					$firstDay = current($keysArr);
					$lastDay = end($keysArr);
					
					if (strtotime($firstDayLY) < strtotime($firstDay))
						$firstDay = $firstDayLY;
						if (strtotime($lastDayLY) > strtotime($lastDay))
							$lastDay = $lastDayLY;
							
							list ($days, $majorTicks, $minorTicks, $majorLabels) = $this->formTimeSpanArray($firstDay, $lastDay);
							$mags = array();
							$magsLY = array();
							
							$runningTotal = 0;
							$runningTotalLY = 0;
							
							if ($accumulate)
							{
								foreach ($days as $thisDay)
								{
									if (isset($dayArray[$thisDay]))
										$runningTotal += $dayArray[$thisDay];
										$mags[] = $runningTotal;
										
										if (isset($LYdayArray[$thisDay]))
											$runningTotalLY += $LYdayArray[$thisDay];
											$magsLY[] = $runningTotalLY;
								}
							} else
							{
								foreach ($days as $thisDay)
								{
									if (isset($dayArray[$thisDay]))
									{
										$mags[] = $dayArray[$thisDay];
									} else
									{
										$mags[] = 0;
									}
									
									if (isset($LYdayArray[$thisDay]))
									{
										$magsLY[] = $LYdayArray[$thisDay];
									} else
									{
										$magsLY[] = 0;
									}
								}
							}
							
							$graph = new Graph(1000, 250);
							$graph->SetScale("linlin");
							
							$theme_class = new DreamDinnersTheme();
							
							$graph->SetTheme($theme_class);
							$graph->img->SetAntiAliasing(true);
							$graph->title->Set($reportName);
							$graph->SetBox(false);
							
							$graph->img->SetAntiAliasing();
							
							$graph->yaxis->HideZeroLabel();
							$graph->yaxis->HideLine(false);
							$graph->yaxis->HideTicks(false, false);
							// $graph->yaxis->SetLabelFormatCallback('yLabelFormat');
							// $graph->yaxis->SetTextLabelInterval(40);
							
							$graph->xgrid->Show();
							$graph->xgrid->SetLineStyle("solid");
							$graph->xgrid->SetColor('#B3B3B3');
							
							// $graph->xaxis->SetTickLabels($days);
							// $graph->xaxis->SetTextTickInterval(10);
							// $graph->xaxis->SetTextLabelInterval(10);
							
							$graph->xaxis->scale->ticks->SupressMinorTickMarks(false);
							
							// $graph->xaxis->scale->ticks->SetMajTickPositions($majorTicks,$majorLabels);
							$graph->xaxis->scale->ticks->SetTickPositions($majorTicks, $minorTicks, $majorLabels);
							$graph->xaxis->SetLabelFormatCallback('xLabelFormat');
							
							$graph->xaxis->SetLabelAngle(90);
							
							// Create the first line
							$p1 = new LinePlot($mags);
							$graph->Add($p1);
							$p1->SetColor("#008800");
							$p1->SetLegend('Current Year');
							// $p1->value->Show();
							// /$p1->value->SetFormat("%d");
							$p1->value->SetFormatCallback('dollarFormatter');
							$p1->value->SetColor('#008800');
							// $p1->AddArea(20,50,LP_AREA_NOT_FILLED,"indianred1", true);
							
							// Create the second line
							$p2 = new LinePlot($magsLY);
							$graph->Add($p2);
							$p2->SetColor("#B22222");
							$p2->SetLegend('Previous Year');
							// $p2->SetFillColor("#B22222");
							// $p2->value->Show();
							$p2->value->SetFormatCallback('dollarFormatter');
							$p2->value->SetColor('#B22222');
							
							$graph->legend->SetFrameWeight(1);
							$graph->SetMarginColor(array(
								222,
								214,
								203
							));
							$graph->legend->Pos(0.05, 0.05);
							
							// $graph->SetMarginColor('#DED6CB');
							
							// add trendline
							
							// Create the regression line
							// $lplot = new LinePlot($yd);
							
							// Add the pltos to the line
							// $graph->Add($lplot);
							// $lplot->SetWeight(2);
							// $lplot->SetColor("blue");
							
							// Output line
							$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
							
							// create file name
							if ($accumulate)
							{
								$varName = "chart_image_path_acc";
								$agrName = "acc_toots";
							} else
							{
								$varName = "chart_image_path";
								$agrName = "toots";
							}
							
							$agrName = md5($agrName) . ".png";
							
							$tpl->assign($varName, IMAGES_PATH . "/charts/agr/" . $agrName);
							
							$agrPath = APP_BASE . "www/theme/" . THEME . "/images/charts/agr/" . $agrName;
							
							$graph->img->Stream($agrPath);
				} catch (Exception $e)
				{
					CLog::RecordNew(CLog::DEBUG, $e->getMessage(), "", "", true);
				}
			}
	}
	

}

?>