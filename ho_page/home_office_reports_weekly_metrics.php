<?php // page_admin_create_store.php

/**
 * @author Carl Samuelson
 */


require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
require_once ('includes/CSessionReports.inc');
require_once ('includes/CDashboardReportWeekBased.inc');
 

/*
  •	Average customers attending per session by week broken out by:
        o	All types of sessions (total)
        o	Standard sessions 
        o	MFY sessions
        o	Fundraising sessions
        o	Introduction order sessions – If possible 
 */


function weeklyMetricReportRowsCallback($sheet, $data, $row, $bottomRightExtent)
{
    
    if (in_array($row, page_admin_home_office_reports_weekly_metrics::$BoldedRows))
    {
        $styleArray = array(
            'font' => array( 'bold' => true));
            
        $sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
    }
    
    if (in_array($row, page_admin_home_office_reports_weekly_metrics::$BorderedRows))
    {
        $styleArray = array(
            'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
        
        $sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
    }
    
}

 
 class page_admin_home_office_reports_weekly_metrics extends CPageAdminOnly 
{
    var  $header;
    var  $rows;
    var  $colDescriptions;
    var  $focusWeek;
    var  $focusWeekLastYear;
    var  $trailing1Month;
    var  $trailing1MonthLastYear;
    static  $BoldedRows = array();
    static  $BorderedRows = array();
    
    public function runSiteAdmin()
    {
        $this->run();
    }
    
    public function runHomeOfficeManager()
    {
        $this->run();
    }
            
    function printMetricAtRow($label, $metric, &$row, $type = false)
    {
        $typeClause = "";
        $diffType = 'number_w_parens';
        
        if ($type == 'currency')
        {
            $typeClause = "|=>currency";
            $diffType = 'currency';
        }
        else if ($type == 'percent')
        {
            $typeClause = "|=>percent";
            $diffType = 'percent';
        }
        else if ($type == 'decimal_2')
        {
            $typeClause = "|=>decimal_2";
            $diffType = 'decimal_2';
        }
        
        $thisYear = $this->focusWeek['year'];
        $lastYear = $this->focusWeekLastYear['year'];
        
        $storeArray = array();
        
        //Subheader Rows 1
        if (!isset($this->rows[$row]))
        {
            $this->rows[$row] = array($label, "Week Beginning|->4", "", "Trailing 1 Month|->5", "", "$lastYear Comparison|->5", "$lastYear to $thisYear", "$lastYear to $thisYear");
            
            page_admin_home_office_reports_weekly_metrics::$BoldedRows[] = $row + 5;
            page_admin_home_office_reports_weekly_metrics::$BorderedRows[] = $row + 5;
            
        }
        
        
        //Subheader Rows 1
        if (!isset($this->rows[$row + 1]))
        {
            $this->rows[++$row] = array("Store", 
                date("n/j/Y", strtotime($this->focusWeek['week_start'])),
                date("n/j/Y", strtotime($this->focusWeekLastYear['week_start'])),
                "Diff",
                "%age Diff",
                "",
               date("n/j/Y", strtotime($this->trailing1Month[3]['week_start'])),
               date("n/j/Y", strtotime($this->trailing1Month[2]['week_start'])),
               date("n/j/Y", strtotime($this->trailing1Month[1]['week_start'])),
               date("n/j/Y", strtotime($this->trailing1Month[0]['week_start'])),
                "Total",
                "", 
                date("n/j/Y", strtotime($this->trailing1MonthLastYear[3]['week_start'])),
                date("n/j/Y", strtotime($this->trailing1MonthLastYear[2]['week_start'])),
                date("n/j/Y", strtotime($this->trailing1MonthLastYear[1]['week_start'])),
                date("n/j/Y", strtotime($this->trailing1MonthLastYear[0]['week_start'])),
                "Total",
                "Diff $",
                "Diff %age");
            
                page_admin_home_office_reports_weekly_metrics::$BoldedRows[] = $row + 5;
                page_admin_home_office_reports_weekly_metrics::$BorderedRows[] = $row + 5;
                
                
        }
        
        
        foreach($this->focusWeek['data'] as $store_id => $thisData)
        {
            $storeArray[$store_id] = $store_id;
        }
        
        
        $firstDataRow = false;
        $lastDataRow = false;
        
        foreach($storeArray as $store_id)
        {
            if (!isset($this->rows[$row + 1]))
            {
                $this->rows[++$row] = array();
            }
            
            $actualExcelRow = $row + 5;
            
            if (!$firstDataRow)
            {
                $firstDataRow = $actualExcelRow;
            }
            
            $lastDataRow = $actualExcelRow;
            
            $this->rows[$row][] = $this->focusWeek['data'][$store_id]['store_name']; // A
            $this->rows[$row][] = $this->focusWeek['data'][$store_id][$metric] . $typeClause; // B
            $this->rows[$row][] = $this->focusWeekLastYear['data'][$store_id][$metric] . $typeClause;  // C
            $this->rows[$row][] = "=B$actualExcelRow - C$actualExcelRow|=>$diffType"; // D
            $this->rows[$row][] = "=D$actualExcelRow / B$actualExcelRow|=>percent"; // E
            
            $this->rows[$row][] = "";
            
            $this->rows[$row][] = $this->trailing1Month[3]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1Month[2]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1Month[1]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1Month[0]['data'][$store_id][$metric] . $typeClause;
            
            $this->rows[$row][] = ($this->trailing1Month[3]['data'][$store_id][$metric] + $this->trailing1Month[2]['data'][$store_id][$metric] + $this->trailing1Month[1]['data'][$store_id][$metric] + $this->trailing1Month[0]['data'][$store_id][$metric])  . $typeClause;
            
            $this->rows[$row][] = "";
            $this->rows[$row][] = $this->trailing1MonthLastYear[3]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1MonthLastYear[2]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1MonthLastYear[1]['data'][$store_id][$metric] . $typeClause;
            $this->rows[$row][] = $this->trailing1MonthLastYear[0]['data'][$store_id][$metric] . $typeClause;
            
            $this->rows[$row][] = ($this->trailing1MonthLastYear[3]['data'][$store_id][$metric] + $this->trailing1MonthLastYear[2]['data'][$store_id][$metric] + $this->trailing1MonthLastYear[1]['data'][$store_id][$metric] + $this->trailing1MonthLastYear[0]['data'][$store_id][$metric])  . $typeClause;
            
            $this->rows[$row][] = "=K$actualExcelRow - Q$actualExcelRow|=>$diffType";
            $this->rows[$row][] = "=R$actualExcelRow / K$actualExcelRow|=>percent";
            
        }
        
        $row++;
        
        $actualSummaryRow = $lastDataRow + 1;
        
        //Summary Row
        $this->rows[$row][] = "Total Corp Stores Avg";
        $this->rows[$row][] = "=ROUND(Average(B$firstDataRow:B$lastDataRow), 2)" . $typeClause;;
        $this->rows[$row][] = "=ROUND(Average(C$firstDataRow:C$lastDataRow), 2)" . $typeClause;;
        $this->rows[$row][] = "=B$actualSummaryRow - C$actualSummaryRow|=>$diffType"; // D
        $this->rows[$row][] = "=D$actualSummaryRow / B$actualSummaryRow|=>percent"; // E
        
        $this->rows[$row][] = "";
        
        $this->rows[$row][] = "=ROUND(Average(G$firstDataRow:G$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(H$firstDataRow:H$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(I$firstDataRow:I$lastDataRow), 2)". $typeClause;
        $this->rows[$row][] = "=ROUND(Average(J$firstDataRow:J$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(K$firstDataRow:K$lastDataRow), 2)" . $typeClause;
        
        
        $this->rows[$row][] = "";
        $this->rows[$row][] = "=ROUND(Average(M$firstDataRow:M$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(N$firstDataRow:N$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(O$firstDataRow:O$lastDataRow), 2)". $typeClause;
        $this->rows[$row][] = "=ROUND(Average(P$firstDataRow:P$lastDataRow), 2)" . $typeClause;
        $this->rows[$row][] = "=ROUND(Average(Q$firstDataRow:Q$lastDataRow), 2)" . $typeClause;
        
        $this->rows[$row][] = "=ROUND(Average(R$firstDataRow:R$lastDataRow), 2)|=>$diffType";
        $this->rows[$row][] = "=R$actualSummaryRow / K$actualSummaryRow|=>percent";
        
        
        page_admin_home_office_reports_weekly_metrics::$BoldedRows[] = $row + 5;
        page_admin_home_office_reports_weekly_metrics::$BorderedRows[] = $row + 4;
        

    }
    
    function getStoreClause($Form)
    {
        $retVal = false;
        if ($Form->value('store_type') == 'corporate_stores')
        {
            $retVal = "INNER JOIN store st on dmw.store_id = st.id and st.is_corporate_owned = 1 and st.active = 1";
        }
        else if ($Form->value('store_type') == 'franchise_stores')
        {
            $retVal = "INNER JOIN store st on dmw.store_id = st.id and st.is_corporate_owned = 0 and st.active = 1";
        }
        else if ($Form->value('store_type') == 'region')
        {
            $region = $Form->value('regions');
            
            if (empty($region) or !is_numeric($region))
            {
                throw new Exception("Invalid region id");
            }
            
            $retVal = "
            INNER JOIN store st on dmw.store_id = st.id and st.is_corporate_owned = 0 and st.active = 1
            INNER JOIN store_trade_area str on str.store_id = st.id and str.trade_area_id = $region and str.is_deleted = 0 and str.is_active = 1";
        }
        else if ($Form->value('store_type') == 'store_class')
        {
            $classNum = $Form->value('store_class');
            $retVal = "
            INNER JOIN store st on dmw.store_id = st.id  and st.active = 1
            INNER JOIN store_class_cache stc on stc.store_id = st.id and stc.class_v2 = $classNum ";
        }
        else if ($Form->value('store_type') == 'selected_stores')
        {
            if ($_POST['requested_stores'] != 'all')
            {
                
                $tarr = explode(",", $_POST['requested_stores']);
                $newTarr = array();
                foreach($tarr as $storeID)
                {
                    if (is_numeric($storeID))
                        $newTarr[] = $storeID;
                }
                
                $storeList = implode(",", $newTarr);
            }
            else
            {
                throw new Exception("TODO");
            }
            
            $retVal = "INNER JOIN store st on dmw.store_id = st.id and st.active = 1 and st.id in ($storeList) ";
        }
        
        return $retVal;
        
        
    }
    
    function getTitle($Form)
    {
        $retVal = false;
        if ($Form->value('store_type') == 'corporate_stores')
        {
            $retVal = "Corporate Stores";
        }
        else if ($Form->value('store_type') == 'franchise_stores')
        {
            $retVal = "Franchise Stores";
        }
        else if ($Form->value('store_type') == 'store_class')
        {
            $retVal = "Stores in Class " . $Form->value('store_class') ;
        }
        else if ($Form->value('store_type') == 'region')
        {
            $region = $Form->value('regions');
            
            $RegionDAO = new DAO();
            $RegionDAO->query("select ta.region from trade_area ta 
                    where ta.id = $region");
            $RegionDAO->fetch();
            
            $retVal = "{$RegionDAO->region} Region Stores";
        }
        else if ($Form->value('store_type') == 'selected_stores')
        {
            $retVal = "Selected Stores";
        }
        
        return $retVal;
        
        
    }
    
    function retrieveDataForWeek(&$weekData,$Form)
    {
        
        
        $storeClause = $this->getStoreClause($Form);
       
        
        $metrics = DAO_CFactory::create('dashboard_metrics_guests_by_week');
        
        $metrics->query("select st.id, st.store_name, st.city, st.state_id, st.home_office_id, st.active, st.is_corporate_owned, dmw.* from dashboard_metrics_guests_by_week dmw
                                $storeClause
                                where dmw.week_number = {$weekData['week_number']} and dmw.year = {$weekData['year']} and dmw.is_deleted = 0");
        
        $data = array();
        
        while($metrics->fetch())
        {
            $data[$metrics->store_id] = $metrics->toArray();
        }
        
        $weekData['data'] = $data;
    }
    
    
    private function precalculateInStoreNumbers()
    {
        
        foreach($this->focusWeek['data'] as &$thisData)
        {
			
			if (empty($thisData['orders_count_all']))
			{
				$thisData['instore_signup_total_calc'] = 0;
			}
			else
			{
				$thisData['instore_signup_total_calc'] = $thisData['instore_signup_total'] / $thisData['orders_count_all'];
			}

        }
        
        foreach($this->focusWeekLastYear['data'] as &$thisData)
        {
			if (empty($thisData['orders_count_all']))
			{
				$thisData['instore_signup_total_calc'] = 0;
			}
			else
			{
				$thisData['instore_signup_total_calc'] = $thisData['instore_signup_total'] / $thisData['orders_count_all'];
			}
        }
        
        foreach($this->trailing1Month as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
				if (empty($thisData['orders_count_all']))
				{
					$thisData['instore_signup_total_calc'] = 0;
				}
				else
				{
					$thisData['instore_signup_total_calc'] = $thisData['instore_signup_total'] / $thisData['orders_count_all'];
				}

            }
        }    
            
        foreach($this->trailing1MonthLastYear as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
				if (empty($thisData['orders_count_all']))
				{
					$thisData['instore_signup_total_calc'] = 0;
				}
				else
				{
					$thisData['instore_signup_total_calc'] = $thisData['instore_signup_total'] / $thisData['orders_count_all'];
				}
			}
        }
        
    }
    
    
    private function precalculateNewGuestCount()
    {
        foreach($this->focusWeek['data'] as &$thisData)
        {
            $thisData['new_guest_count_calc'] = $thisData['guest_count_new_regular'] + $thisData['guest_count_new_intro'] +  $thisData['guest_count_new_taste'] + $thisData['guest_count_new_fundraiser'];
        }
        
        foreach($this->focusWeekLastYear['data'] as &$thisData)
        {
            $thisData['new_guest_count_calc'] = $thisData['guest_count_new_regular'] + $thisData['guest_count_new_intro'] +  $thisData['guest_count_new_taste'] + $thisData['guest_count_new_fundraiser'];
        }
        
        foreach($this->trailing1Month as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
                $thisData['new_guest_count_calc'] = $thisData['guest_count_new_regular'] + $thisData['guest_count_new_intro'] +  $thisData['guest_count_new_taste'] + $thisData['guest_count_new_fundraiser'];
            }
        }
        
        foreach($this->trailing1MonthLastYear as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
                $thisData['new_guest_count_calc'] = $thisData['guest_count_new_regular'] + $thisData['guest_count_new_intro'] +  $thisData['guest_count_new_taste'] + $thisData['guest_count_new_fundraiser'];
            }
        }
        
    }
    
    function safeDivide($dividend, $divisor)
    {
        if (empty($divisor))
        {
            return 0;
        }
         
        return $dividend / $divisor;
    }
    
    private function precalculateAverageSessionAttendance()
    {
        foreach($this->focusWeek['data'] as &$thisData)
        {
            $thisData['average_guests_per_session'] = $this->safeDivide($thisData['orders_count_all'], $thisData['sessions_count_all']);
            $thisData['average_guests_per_standard_session'] = $this->safeDivide($thisData['orders_count_regular'], $thisData['sessions_count_regular']);
            $thisData['average_guests_per_MFY_session'] = $this->safeDivide($thisData['orders_count_mfy'], $thisData['sessions_count_mfy']);
            $thisData['average_guests_per_fundraiser_session'] = $this->safeDivide($thisData['orders_count_fundraiser'], $thisData['sessions_count_fundraiser']);
            $thisData['average_guests_per_taste_session'] = $this->safeDivide($thisData['orders_count_taste'], $thisData['sessions_count_taste']);
        }
        
        foreach($this->focusWeekLastYear['data'] as &$thisData)
        {
            $thisData['average_guests_per_session'] = $this->safeDivide($thisData['orders_count_all'], $thisData['sessions_count_all']);
            $thisData['average_guests_per_standard_session'] = $this->safeDivide($thisData['orders_count_regular'], $thisData['sessions_count_regular']);
            $thisData['average_guests_per_MFY_session'] = $this->safeDivide($thisData['orders_count_mfy'], $thisData['sessions_count_mfy']);
            $thisData['average_guests_per_fundraiser_session'] = $this->safeDivide($thisData['orders_count_fundraiser'], $thisData['sessions_count_fundraiser']);
            $thisData['average_guests_per_taste_session'] = $this->safeDivide($thisData['orders_count_taste'], $thisData['sessions_count_taste']);
        }
        
        foreach($this->trailing1Month as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
                $thisData['average_guests_per_session'] = $this->safeDivide($thisData['orders_count_all'], $thisData['sessions_count_all']);
                $thisData['average_guests_per_standard_session'] = $this->safeDivide($thisData['orders_count_regular'], $thisData['sessions_count_regular']);
                $thisData['average_guests_per_MFY_session'] = $this->safeDivide($thisData['orders_count_mfy'], $thisData['sessions_count_mfy']);
                $thisData['average_guests_per_fundraiser_session'] = $this->safeDivide($thisData['orders_count_fundraiser'], $thisData['sessions_count_fundraiser']);
                $thisData['average_guests_per_taste_session'] = $this->safeDivide($thisData['orders_count_taste'], $thisData['sessions_count_taste']);
            }
        }
        
        foreach($this->trailing1MonthLastYear as &$thisWeek)
        {
            foreach($thisWeek['data'] as &$thisData)
            {
                $thisData['average_guests_per_session'] = $this->safeDivide($thisData['orders_count_all'], $thisData['sessions_count_all']);
                $thisData['average_guests_per_standard_session'] = $this->safeDivide($thisData['orders_count_regular'], $thisData['sessions_count_regular']);
                $thisData['average_guests_per_MFY_session'] = $this->safeDivide($thisData['orders_count_mfy'], $thisData['sessions_count_mfy']);
                $thisData['average_guests_per_fundraiser_session'] = $this->safeDivide($thisData['orders_count_fundraiser'], $thisData['sessions_count_fundraiser']);
                $thisData['average_guests_per_taste_session'] = $this->safeDivide($thisData['orders_count_taste'], $thisData['sessions_count_taste']);
            }
        }
    }
    
    private function run()
    {
        
        
        ini_set('memory_limit','-1');
        set_time_limit(3600 * 24);
        
        $tpl = CApp::instance()->template();
        
        $Form = new CForm();
        $Form->Repost = TRUE;
        $Form->DefaultValues['store_type'] = 'corporate_stores';
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "store_type",
            CForm::onChange => 'storeTypeClick',
            CForm::required => true,
            CForm::value => 'corporate_stores'));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "store_type",
            CForm::onChange => 'storeTypeClick',
            CForm::required => true,
            CForm::value => 'franchise_stores'));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "store_type",
            CForm::onChange => 'storeTypeClick',
            CForm::required => true,
            CForm::value => 'region'));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "store_type",
            CForm::onChange => 'storeTypeClick',
            CForm::required => true,
            CForm::value => 'store_class'));
        
        $Form->AddElement(array(CForm::type=> CForm::DropDown,
            CForm::name => "store_class",
            CForm::options => array(1 => "Class 1 (> $50,000)", 2 => "Class 2 ($30,001 to $49,999)", 3 => "Class 3 (<= $30,000)")));
        
        
        $Form->AddElement(array(CForm::type=> CForm::RegionDropDown,
            CForm::name => "regions"));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "store_type",
            CForm::onChange => 'storeTypeClick',
            CForm::required => true,
            CForm::value => 'selected_stores'));
        
        
        $Form->DefaultValues['focus_week'] = 'current_week';
        
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "focus_week",
            CForm::onChange => 'weekClick',
            CForm::required => true,
            CForm::value => 'current_week'));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "focus_week",
            CForm::onChange => 'weekClick',
            CForm::required => true,
            CForm::value => 'last_week'));
        
        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "focus_week",
            CForm::onChange => 'weekClick',
            CForm::required => true,
            CForm::value => 'other_week'));
        
        $Form->AddElement(array (CForm::type => CForm::Submit,
            CForm::name => 'report_submit',
            CForm::css_class => 'button',
            CForm::value => 'Run Report'));
        
        
        $Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => 'requested_stores'));
        
        $tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));
        $tpl->assign('query_form', $Form->render());
        
        
      if (isset($_POST['report_submit']) && $_POST['report_submit'] == "Run Report")
        {
            
            // Determine Time Spans
            // Week Beginning (focus week) = the previous week and the corresponding week 1 year ago
            // Trailing 1 Month = 4 weeks previous to the current week
            // Previous year comparison = 4 weeks corresponding to Trailing 1 Month but from previous year
            
            $error = false;
            
            $title = $this->getTitle($Form);
            
            
            
            if ($Form->value('focus_week') == 'current_week')
            {
                $Now = time();
            }
            else if ($Form->value('focus_week') == 'last_week')
            {
                $Now = time() - ((86400 * 7));
            }
            else if ($Form->value('focus_week') == 'other_week')
            {
                $cutOff = strtotime("2018-01-01 00:00:00");
                $Now = strtotime($_POST['single_date']);
                if ($Now < $cutOff)
                {
                    $tpl->setErrorMsg("Please choose a time after Jan. 1st 2018");
                    $error = true;
                }
            }
            
            if (!$error)
            {
                $this->focusWeek = CDashboardWeekBased::getWeekTimeData(date("Y-m-d 00:00:00", $Now));
                $this->focusWeekLastYear = CDashboardWeekBased::getWeekTimeData(false, $this->focusWeek['week_number'], $this->focusWeek['year'] - 1);
                
                $this->trailing1Month = array();
                $weekTS = $Now;
                
                for($x = 0; $x < 4; $x++)
                {
                  $weekTS -= (86400 * 7);
                  $aWeek = CDashboardWeekBased::getWeekTimeData(date("Y-m-d 00:00:00", $weekTS));
                  $this->trailing1Month[] = $aWeek;
                }
                
                $this->trailing1MonthLastYear = array();
                $weekTS = strtotime($this->focusWeekLastYear['week_start']);
                
                for($x = 0; $x < 4; $x++)
                {
                    $weekTS -= (86400 * 7);
                    $aWeek = CDashboardWeekBased::getWeekTimeData(date("Y-m-d 00:00:00", $weekTS));
                    $this->trailing1MonthLastYear[] = $aWeek;
                }
                
            
                
                // Retrieve Data
                $this->retrieveDataForWeek($this->focusWeek, $Form);
                $this->retrieveDataForWeek($this->focusWeekLastYear, $Form);
                
                foreach($this->trailing1Month as &$thisWeek)
                {
                    $this->retrieveDataForWeek($thisWeek, $Form);
                }
                
                foreach($this->trailing1MonthLastYear as &$thisWeek)
                {
                    $this->retrieveDataForWeek($thisWeek, $Form);
                }				
                
                $_GET['export'] = 'xlsx';
                
                
                $labels = array("","","","","","","","","","","","","","","","","","","");
                
                
                // header 
                $titleRows = array();
                
                
                
                $titleRows[] = array("Dream Dinners Retail - " . $title);
                $titleRows[] = array("Leading Metrics");
                $titleRows[] = array("Week of ", date("n/j/Y", strtotime($this->focusWeek['week_start'])));
                
                $curRow = 0;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Total Unique Guest Count', 'guest_count_total', $curRow);
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Ticket','average_standard_ticket', $curRow, 'currency');
                
                
                $this->precalculateInStoreNumbers();
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('In Store Order Percentage', 'instore_signup_total_calc', $curRow, 'percent');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Conversion Rate', 'conversion_rate', $curRow, 'percent');
                
                
                $this->precalculateNewGuestCount();
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('New Guest Count','new_guest_count_calc', $curRow);
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Lifestyle Guest Count', 'lifestyle_guest_count', $curRow);
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Revenue', 'total_agr', $curRow, 'currency');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Total Servings Sold', 'total_servings_sold', $curRow);
                
                
                $this->precalculateAverageSessionAttendance();
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Guests Per Session ', 'average_guests_per_session', $curRow, 'decimal_2');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Guests Per Standard Session', 'average_guests_per_standard_session', $curRow, 'decimal_2');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Guests Per MFY Session', 'average_guests_per_MFY_session', $curRow, 'decimal_2');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Guests Per Fundraiser Session', 'average_guests_per_fundraiser_session', $curRow, 'decimal_2');
                
                $curRow++;
                $this->rows[$curRow] = $labels;  // just a blank row
                $curRow++;
                $this->printMetricAtRow('Average Guests Per DreamTaste Session', 'average_guests_per_taste_session', $curRow, 'decimal_2');
                
                
                $tpl->assign('suppressLabelsDisplay', true);
                
                // spit out excel sheet
                $tpl->assign('rows', $this->rows);
                
                $colDesc = array(
                    'A' => array('width' => 30),
                    'B' => array('width' => 11),
                    'C' => array('width' => 11),
                    'D' => array('width' => 11),
                    'E' => array('width' => 11),
                    'F' => array('width' => 2),
                    'G' => array('width' => 11),
                    'H' => array('width' => 11),
                    'I' => array('width' => 11),
                    'J' => array('width' => 11),
                    'K' => array('width' => 11),
                    'L' => array('width' => 2),
                    'M' => array('width' => 11),
                    'N' => array('width' => 11),
                    'O' => array('width' => 11),
                    'P' => array('width' => 11),
                    'Q' => array('width' => 11),
                    'R' => array('width' => 12),
                    'S' => array('width' => 12));
                
                $callbacks = array('row_callback' => 'weeklyMetricReportRowsCallback');
                $tpl->assign('excel_callbacks', $callbacks);
                
                $tpl->assign('title_rows', $titleRows);
                
                $tpl->assign('col_descriptions', $colDesc);
                $tpl->assign('labels', $labels);
            }
        }
        
        
        
        header_remove('Set-Cookie');
    }
}
?>