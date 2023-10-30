<?php // page_admin_create_store.php

/**
 * @author Carl Samuelson
 */


 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');
require_once ('includes/CDashboardReportWeekBased.inc');

require_once('ExcelExport.inc');


 class page_admin_home_office_reports_guest_metrics extends CPageAdminOnly
{
    var  $header;
    var  $rows;
    var  $colDescriptions;

    static $DollarMetricsList = array('average_standard_ticket');
    static $DollarNoCentsMetricsList = array('total_agr');

    static $metricsList = array(
        "none" => "Select a Metric",
        "lifestyle_guest_count" => "Critical^^^Lifestyle Guest Count",
        "total_agr" => "Critical^^^Total Agr",
        "total_new_guest_count" => "Critical^^^Total New Guest Count",
        "total_food_cost" => "Weekly Only^^^Total Food Cost",
        "average_standard_ticket" => "Weekly Only^^^Average Standard Ticket",
        "guest_count_total" => "Guest Counts^^^Guest Count Total",
        "guest_count_existing_regular" => "Guest Counts^^^Guest Count Existing Regular",
        "guest_count_existing_taste" => "Guest Counts^^^Guest Count Existing Taste",
        "guest_count_existing_intro" => "Guest Counts^^^Guest Count Existing Intro",
        "guest_count_existing_fundraiser" => "Guest Counts^^^Guest Count Existing Fundraiser",
        "guest_count_reacquired_regular" => "Guest Counts^^^Guest Count Reacquired Regular",
        "guest_count_reacquired_taste" => "Guest Counts^^^Guest Count Reacquired Taste",
        "guest_count_reacquired_intro" => "Guest Counts^^^Guest Count Reacquired Intro",
        "guest_count_reacquired_fundraiser" => "Guest Counts^^^Guest Count Reacquired Fundraiser",
        "guest_count_new_regular" => "Guest Counts^^^Guest Count New Regular",
        "guest_count_new_taste" => "Guest Counts^^^Guest Count New Taste",
        "guest_count_new_intro" => "Guest Counts^^^Guest Count New Intro",
        "guest_count_new_fundraiser" => "Guest Counts^^^Guest Count New Fundraiser",
        "instore_signup_total" => "In Store^^^Instore Signup Total",
        "instore_signup_existing_regular" => "In Store^^^Instore Signup Existing Regular",
        "instore_signup_existing_taste" => "In Store^^^Instore Signup Existing Taste",
        "instore_signup_existing_intro" => "In Store^^^Instore Signup Existing Intro",
        "instore_signup_existing_fundraiser" => "In Store^^^Instore Signup Existing Fundraiser",
        "instore_signup_reacquired_regular" => "In Store^^^Instore Signup Reacquired Regular",
        "instore_signup_reacquired_taste" => "In Store^^^Instore Signup Reacquired Taste",
        "instore_signup_reacquired_intro" => "In Store^^^Instore Signup Reacquired Intro",
        "instore_signup_reacquired_fundraiser" => "In Store^^^Instore Signup Reacquired Fundraiser",
        "instore_signup_new_regular" => "In Store^^^Instore Signup New Regular",
        "instore_signup_new_taste" => "In Store^^^Instore Signup New Taste",
        "instore_signup_new_intro" => "In Store^^^Instore Signup New Intro",
        "instore_signup_new_fundraiser" => "In Store^^^Instore Signup New Fundraiser",
        "avg_servings_per_guest_all" => "Servings Per Guest^^^Avg Servings Per Guest All",
        "avg_servings_per_guest_existing_regular" => "Servings Per Guest^^^Avg Servings Per Guest Existing Regular",
        "avg_servings_per_guest_existing_taste" => "Servings Per Guest^^^Avg Servings Per Guest Existing Taste",
        "avg_servings_per_guest_existing_intro" => "Servings Per Guest^^^Avg Servings Per Guest Existing Intro",
        "avg_servings_per_guest_existing_fundraiser" => "Servings Per Guest^^^Avg Servings Per Guest Existing Fundraiser",
        "avg_servings_per_guest_regular" => "Servings Per Guest^^^Avg Servings Per Guest Regular",
        "avg_servings_per_guest_reacquired_regular" => "Servings Per Guest^^^Avg Servings Per Guest Reacquired Regular",
        "avg_servings_per_guest_reacquired_taste" => "Servings Per Guest^^^Avg Servings Per Guest Reacquired Taste",
        "avg_servings_per_guest_reacquired_intro" => "Servings Per Guest^^^Avg Servings Per Guest Reacquired Intro",
        "avg_servings_per_guest_reacquired_fundraiser" => "Servings Per Guest^^^Avg Servings Per Guest Reacquired Fundraiser",
        "avg_servings_per_guest_new_regular" => "Servings Per Guest^^^Avg Servings Per Guest New Regular",
        "avg_servings_per_guest_new_taste" => "Servings Per Guest^^^Avg Servings Per Guest New Taste",
        "avg_servings_per_guest_new_intro" => "Servings Per Guest^^^Avg Servings Per Guest New Intro",
        "avg_servings_per_guest_new_fundraiser" => "Servings Per Guest^^^Avg Servings Per Guest New Fundraiser",
        "total_servings_sold" => "Behavior^^^Total Servings Sold",
        "converted_guests" => "Behavior^^^Converted Guests",
        "conversion_rate" => "Behavior^^^Conversion Rate",
        "one_month_drop_off" => "Behavior^^^One Month Drop Off",
        "two_month_drop_off" => "Behavior^^^Two Month Drop Off",
        "average_annual_visits" => "Behavior^^^Average Annual Visits",
        "average_annual_regular_visits" => "Behavior^^^Average Annual Regular Visits",
        "lost_guests_at_45_days" => "Behavior^^^Lost Guests At 45 Days",
        "retention_count" => "Behavior^^^Retention Count",
        "sessions_count_all" => "Session Counts^^^Sessions Count All",
        "sessions_count_regular" => "Session Counts^^^Sessions Count Regular",
        "sessions_count_mfy" => "Session Counts^^^Sessions Count Mfy",
        "sessions_count_taste" => "Session Counts^^^Sessions Count Taste",
        "sessions_count_fundraiser" => "Session Counts^^^Sessions Count Fundraiser",
        "orders_count_all" => "Order Counts^^^Orders Count All",
        "orders_count_regular" => "Order Counts^^^Orders Count Regular",
        "orders_count_mfy" => "Order Counts^^^Orders Count Mfy",
        "orders_count_taste" => "Order Counts^^^Orders Count Taste",
        "orders_count_fundraiser" => "Order Counts^^^Orders Count Fundraiser",
        "orders_count_regular_existing_guests" => "Order Counts^^^Orders Count Regular Existing Guests",
        "orders_count_regular_new_guests" => "Order Counts^^^Orders Count Regular New Guests",
        "orders_count_regular_reacquired_guests" => "Order Counts^^^Orders Count Regular Reacquired Guests",
        "orders_count_intro_existing_guests" => "Order Counts^^^Orders Count Intro Existing Guests",
        "orders_count_intro_new_guests" => "Order Counts^^^Orders Count Intro New Guests",
        "orders_count_intro_reacquired_guests" => "Order Counts^^^Orders Count Intro Reacquired Guests",
        "orders_count_taste_existing_guests" => "Order Counts^^^Orders Count Taste Existing Guests",
        "orders_count_taste_new_guests" => "Order Counts^^^Orders Count Taste New Guests",
        "orders_count_taste_reacquired_guests" => "Order Counts^^^Orders Count Taste Reacquired Guests",
        "orders_count_fundraiser_existing_guests" => "Order Counts^^^Orders Count Fundraiser Existing Guests",
        "orders_count_fundraiser_new_guests" => "Order Counts^^^Orders Count Fundraiser New Guests",
        "orders_count_fundraiser_reacquired_guests" => "Order Counts^^^Orders Count Fundraiser Reacquired Guests"
    );

    public function runSiteAdmin()
    {
        $this->run();
    }

    public function runHomeOfficeManager()
    {
        $this->run();
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


    function safeDivide($dividend, $divisor)
    {
        if (empty($divisor))
        {
            return 0;
        }

        return $dividend / $divisor;
    }

    private function run()
    {


        ini_set('memory_limit','-1');
        set_time_limit(3600 * 24);

        $tpl = CApp::instance()->template();

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


        $Form->DefaultValues['focus_type'] = 'week';
        $Form->DefaultValues['metric'] = 'none';

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "focus_type",
            CForm::onChange => 'weekClick',
            CForm::required => true,
            CForm::value => 'week'));

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "focus_type",
            CForm::onChange => 'weekClick',
            CForm::required => true,
            CForm::value => 'month'));

        $Form->addElement(array(CForm::type=> CForm::DropDown,
            CForm::name => "metric",
            CForm::required => true,
            CForm::options => self::$metricsList));


        $Form->AddElement(array (CForm::type => CForm::Submit,
            CForm::name => 'report_submit',
            CForm::css_class => 'btn btn-primary btn-sm',
            CForm::value => 'Run Report'));


        $Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => 'requested_stores'));

        $tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));
        $tpl->assign('query_form', $Form->render());

      if (isset($_POST['report_submit']) && $_POST['report_submit'] == "Run Report")
        {

            $error = false;

            $title = $this->getTitle($Form);


            $reportType = $Form->value('focus_type');

            $metric = $Form->value('metric');

            if (strtotime($range_day_start) > strtotime($range_day_end))
            {
                $temp = $range_day_start;
                $range_day_start = $range_day_end;
                $range_day_end = $temp;
            }

            if (!$error)
            {

                $_GET['export'] = 'xlsx';


                // header
                $titleRows = array();
                $titleRows[] = array("Dream Dinners Retail - " . $title);
                $titleRows[] = array("Metric: " . $metric);
            // TODO    $titleRows[] = array("Week of ", date("n/j/Y", strtotime($this->focusWeek['week_start'])));

                if ($reportType == 'week')
                {
                    list($rows, $labels, $colDesc) = $this->retrieveRangeForMetricByWeek($range_day_start, $range_day_end, $metric, $Form);
                }
                else
                {
                    // month

                    list($rows, $labels, $colDesc) = $this->retrieveRangeForMetricByMonth($range_day_start, $range_day_end, $metric, $Form);

                }


                // spit out excel sheet
                $tpl->assign('rows', $rows);

            //    $callbacks = array('row_callback' => 'weeklyMetricReportRowsCallback');
             //   $tpl->assign('excel_callbacks', $callbacks);

                $tpl->assign('title_rows', $titleRows);

                $tpl->assign('col_descriptions', $colDesc);
                $tpl->assign('labels', $labels);
            }
        }



        header_remove('Set-Cookie');
    }

    function  retrieveRangeForMetricByWeek($range_day_start, $range_day_end, $metric, $Form)
    {
        $retArr = array();


        $colDesc = array(
            'A' => array('width' => 8),
            'B' => array('width' => 15),
            'C' => array('width' => 15),
            'D' => array('width' => 8));



        $col = 'E';
        $colSecondChar = '';
        $thirdSecondChar = '';

        $metricType = 'number';
        if (in_array($metric, self::$DollarMetricsList))
        {
            $metricType = "currency";
        }
        else if (in_array($metric, self::$DollarNoCentsMetricsList))
        {
            $metricType = "currency_no_cents";
        }

        // get start week
        $startWeekDate = new DateTime($range_day_start);
        $startWeek = $startWeekDate->format('W');
        $startYear = $startWeekDate->format('o');
        $startWeekData = CDashboardWeekBased::getWeekTimeData(false, $startWeek, $startYear);


        // get end week
        $endWeekDate = new DateTime($range_day_end);
        $endWeek = $endWeekDate->format('W');
        $endYear = $endWeekDate->format('o');
        $endWeekData = CDashboardWeekBased::getWeekTimeData(false, $endWeek, $endYear);


        if ($metric == 'total_new_guest_count')
        {
            $metricClause = "dmw.guest_count_new_regular + dmw.guest_count_new_taste + dmw.guest_count_new_intro + dmw.guest_count_new_fundraiser as total_new_guest_count";
        }
        else
        {
            $metricClause = "dmw." . $metric;
        }

        $storeClause = $this->getStoreClause($Form);
        $metricsObj = new DAO();
        $metricsObj->query("select dmw.start_date, dmw.store_id, $metricClause, st.store_name, st.city, st.home_office_id, st.state_id from dashboard_metrics_guests_by_week dmw
                             $storeClause 
                            where dmw.start_date >= '{$startWeekData['week_start']}' and dmw.start_date < '{$endWeekData['week_end']}' and dmw.is_deleted = 0
                              order by dmw.start_date, dmw.store_id");

        $foundStoreArr = array();
        $foundDatesArr = array();
        $storeInfo = array();
        $labels = array("Home Office ID", "Store Name", "City", "State");

        while($metricsObj->fetch())
        {
            if (!isset($retArr[$metricsObj->store_id]))
            {
                $retArr[$metricsObj->store_id] = array();
            }

            if (!in_array($metricsObj->store_id, $foundStoreArr))
            {
                $foundStoreArr[] = $metricsObj->store_id;
                $storeInfo[$metricsObj->store_id] = array($metricsObj->home_office_id, $metricsObj->store_name, $metricsObj->city, $metricsObj->state_id);
            }

            if (!in_array($metricsObj->start_date, $foundDatesArr))
            {
                $foundDatesArr[] = $metricsObj->start_date;


                $thisWeekData = CDashboardWeekBased::getWeekTimeData($metricsObj->start_date);

                $tmp = explode(" ", $metricsObj->start_date);
                $thisLabel = $tmp[0];

                $labels[] = $thisLabel . " Q" . $thisWeekData['quarter'] . "W" . $thisWeekData['quarter_week'];

                $colDesc[$thirdSecondChar.$colSecondChar.$col] =  array(
                    'align' => 'center',
                    'type' => $metricType,
                    'width' => '12');

                incrementColumn($thirdSecondChar, $colSecondChar, $col);

            }


            $retArr[$metricsObj->store_id][$metricsObj->start_date] = $metricsObj->$metric;
        }

        // normalize

        foreach($retArr as $thisStore => $data)
        {
            foreach($foundDatesArr as $thisDate)
            {
                if (!isset($data[$thisDate]))
                {
                    $retArr[$thisStore][$thisDate] = 0;
                }
            }
        }


        foreach($storeInfo as $id => $data)
        {
            $retArr[$id] = array_merge($storeInfo[$id],  $retArr[$id]);
        }

        $numStores = count($storeInfo);

        $sumRow = array("","","", "Total");
        $avgRow = array("","","", "Average");
        $col = 'E';
        $colSecondChar = '';
        $thirdSecondChar = '';

        foreach($foundDatesArr as $thisDate)
        {
            $sumRow[] = "=SUM(" . $thirdSecondChar.$colSecondChar.$col . "4:" . $thirdSecondChar.$colSecondChar.$col . ($numStores + 3) . ")";
            $avgRow[] = "=AVERAGE(" . $thirdSecondChar.$colSecondChar.$col . "4:" . $thirdSecondChar.$colSecondChar.$col . ($numStores + 3) . ")";
            incrementColumn($thirdSecondChar, $colSecondChar, $col);
        }


       $retArr[] = $sumRow;
       $retArr[] = $avgRow;
        return array($retArr, $labels, $colDesc);



    }

    function  retrieveRangeForMetricByMonth($range_day_start, $range_day_end, $metric, $Form)
    {
        $retArr = array();


        $colDesc = array(
            'A' => array('width' => 8),
            'B' => array('width' => 15),
            'C' => array('width' => 15),
            'D' => array('width' => 8));



        $col = 'E';
        $colSecondChar = '';
        $thirdSecondChar = '';

        $metricType = 'number';
        if (in_array($metric, self::$DollarMetricsList))
        {
            $metricType = "currency";
        }
        else if (in_array($metric, self::$DollarNoCentsMetricsList))
        {
            $metricType = "currency_no_cents";
        }


        $startMenu = CMenu::getMenuByDate($range_day_start);
        $startAnchor = $startMenu['menu_start'];

        $endMenu = CMenu::getMenuByDate($range_day_end);
        $endAnchor = $endMenu['menu_start'];

        $targetTable = 'dashboard_metrics_guests_by_menu';

        if ($metric == 'total_agr')
        {
            $targetTable = 'dashboard_metrics_agr_by_menu';
        }


        if ($metric == 'total_new_guest_count')
        {
            $metricClause = "dmw.guest_count_new_regular + dmw.guest_count_new_taste + dmw.guest_count_new_intro + dmw.guest_count_new_fundraiser as total_new_guest_count";
        }
        else
        {
            $metricClause = "dmw." . $metric;
        }


        // get end week
        $storeClause = $this->getStoreClause($Form);
        $metricsObj = new DAO();
        $metricsObj->query("select dmw.date, dmw.store_id, $metricClause, st.store_name, st.city, st.home_office_id, st.state_id from $targetTable dmw
            $storeClause
            where dmw.date >= '$startAnchor' and dmw.date <= '$endAnchor' and dmw.is_deleted = 0
            order by dmw.date, dmw.store_id");


            $foundStoreArr = array();
            $foundDatesArr = array();
            $storeInfo = array();
            $labels = array("Home Office ID", "Store Name", "City", "State");

            while($metricsObj->fetch())
            {
                if (!isset($retArr[$metricsObj->store_id]))
                {
                    $retArr[$metricsObj->store_id] = array();
                }

                if (!in_array($metricsObj->store_id, $foundStoreArr))
                {
                    $foundStoreArr[] = $metricsObj->store_id;
                    $storeInfo[$metricsObj->store_id] = array($metricsObj->home_office_id, $metricsObj->store_name, $metricsObj->city, $metricsObj->state_id);
                }

                if (!in_array($metricsObj->date, $foundDatesArr))
                {
                    $foundDatesArr[] = $metricsObj->date;


                    $labels[] = $metricsObj->date;

                    $colDesc[$thirdSecondChar.$colSecondChar.$col] =  array(
                        'align' => 'center',
                        'type' => $metricType,
                        'width' => '12');

                    incrementColumn($thirdSecondChar, $colSecondChar, $col);

                }


                $retArr[$metricsObj->store_id][$metricsObj->date] = $metricsObj->$metric;
            }

            // normalize

            foreach($retArr as $thisStore => $data)
            {
                foreach($foundDatesArr as $thisDate)
                {
                    if (!isset($data[$thisDate]))
                    {
                        $retArr[$thisStore][$thisDate] = 0;
                    }
                }
            }


            foreach($storeInfo as $id => $data)
            {
                $retArr[$id] = array_merge($storeInfo[$id],  $retArr[$id]);
            }

            $numStores = count($storeInfo);

            $sumRow = array("","","", "Total");
            $avgRow = array("","","", "Average");
            $col = 'E';
            $colSecondChar = '';
            $thirdSecondChar = '';

            foreach($foundDatesArr as $thisDate)
            {
                $sumRow[] = "=SUM(" . $thirdSecondChar.$colSecondChar.$col . "4:" . $thirdSecondChar.$colSecondChar.$col . ($numStores + 3) . ")";
                $avgRow[] = "=AVERAGE(" . $thirdSecondChar.$colSecondChar.$col . "4:" . $thirdSecondChar.$colSecondChar.$col . ($numStores + 3) . ")";
                incrementColumn($thirdSecondChar, $colSecondChar, $col);
            }


            $retArr[] = $sumRow;
            $retArr[] = $avgRow;
            return array($retArr, $labels, $colDesc);



    }

}
?>