<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_coupon extends CPageAdminOnly
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

    function runOpsLead()
    {
        $this->runFranchiseOwner();
    }

    function runHomeOfficeManager()
    {
        $this->runSiteAdmin();
    }

    function runFranchiseOwner()
    {
        //$Owner = DAO_CFactory::create('user_to_franchise');
        //$Owner->user_id = CUser::getCurrentUser()->id;
        //if ( !$Owner->find(true) )
        //	throw new Exception('not a franchise owner, or store not found for current user');
        //$this->_franchise_id = $Owner->franchise_id;
        $this->currentStore = CApp::forceLocationChoice();
        $this->runSiteAdmin();
    }

    function runSiteAdmin()
    {
        $store = null;
        $export = false;
        $tpl = CApp::instance()->template();
        $Form = new CForm();
        $Form->Repost = false;
        $total_count = 0;
        $report_submitted = false;
        if ($this->currentStore)
        { //fadmins
            $store = $this->currentStore;
        }
        else
        { //site admin
            //does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
            //CForm ::storedropdown always sets the default to the last chosen store
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

        if ($store === "all")
        {
            $tpl->assign("store_name", "All Stores");
        }
        else if (is_numeric($store))
        {
            $storeDAO = DAO_CFactory::create('store');
            $storeDAO->id = $store;
            $storeDAO->selectAdd();
            $storeDAO->selectAdd('store_name, city');
            $storeDAO->find(true);

            $tpl->assign("store_name", $storeDAO->city . "--" . $storeDAO->store_name);
        }
        else
        {
            $tpl->assign("store_name", "N/A");
        }

        $day = 0;
        $month = 0;
        $year = 0;
        $duration = "1 DAY";
        $spansMenu = false;

        $report_type_to_run = 1;
        if (isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
        {
            $report_type_to_run = $_REQUEST["pickDate"];
        }

        $report_array = array();

        $Form->AddElement(array(
            CForm::type => CForm::Submit,
            CForm::name => 'report_submit',
            CForm::css_class => 'btn btn-primary btn-sm',
            CForm::value => 'Run Report'
        ));

        //$month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
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

        if (isset($_GET['coupon_detail']) && $_GET['coupon_detail'] === "true")
        {

            $day = $_REQUEST["day"];
            $month = $_REQUEST["month"];
            $year = $_REQUEST["year"];
            $duration = $_REQUEST["duration"];

            $current_detail_date = mktime(0, 0, 0, $month, $day, $year);
            $current_detail_date_sql = date("Y-m-d 00:00:00", $current_detail_date);

            $coupon = $_GET['coupon'];

            $Detail = DAO_CFactory::create('coupon_code');

            $Detail->query("select  o.store_id, st.store_name, st.home_office_id,  o.coupon_code_id, cc.coupon_code_title, cc.coupon_code, " . " SUM(o.grand_total) - SUM(o.subtotal_all_taxes) as total_spend, SUM(o.coupon_code_discount_total) as total_save, COUNT(o.id) as num_trans from orders o " . " join booking b on o.id = b.order_id and status = 'ACTIVE' " . " join session s on b.session_id = s.id and s.session_start >= '" . $current_detail_date_sql . "'" . " and s.session_start <  DATE_ADD('" . $current_detail_date_sql . "', INTERVAL " . $duration . ")" . " join coupon_code cc on cc.id = o.coupon_code_id " . " join store st on o.store_id = st.id " . " where o.coupon_code_id = $coupon group by o.store_id");

            $detailRows = array();

            while ($Detail->fetch())
            {
                $detailRows[$Detail->store_id] = array(
                    'store_name' => $Detail->store_name,
                    'home_office_id' => $Detail->home_office_id,
                    'title' => $Detail->coupon_code_title,
                    'code' => $Detail->coupon_code,
                    'total_spend' => $Detail->total_spend,
                    'total_save' => $Detail->total_save,
                    'num_trans' => $Detail->num_trans
                );
            }

            $labels = array(
                "Store Name",
                "Home Office ID",
                "Coupon Title",
                "Coupon Code",
                "Total Spend",
                "Total Saved",
                "Num Transactions"
            );

            $tpl->assign('labels', $labels);
            $tpl->assign('rows', $detailRows);
            $tpl->assign('rowcount', count($detailRows));

            CLog::RecordReport("Coupon (Detail)", "Rows:$detailRows ~ Coupon: $Detail->coupon_code_title ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration");

            return;
        }

        // make coupon checkbox
        $Coupons = DAO_CFactory::create('coupon_code');
        $CouponsExpired = DAO_CFactory::create('coupon_code');

        $date = date('Y/m/d h:i:s', time());

        if ($store === 'all')
        {
            $Coupons->query("select cc.id, cc.coupon_code, cc.coupon_code_title from coupon_code cc where cc.is_deleted = 0 and cc.valid_timespan_end >= '$date'");
            $tpl->assign('all_stores', true);
        }
        else
        {
            $Coupons->query("select cc.id, cc.coupon_code, cc.coupon_code_title from coupon_code cc
					where cc.is_store_specific = 0 and cc.is_deleted = 0 and cc.valid_timespan_end >= '$date'
					union
					select cc2.id, cc2.coupon_code, cc2.coupon_code_title from coupon_code cc2
					join coupon_to_store cts on cts.store_id = $store and cts.coupon_code_id = cc2.id
					where cc2.is_store_specific = 1 and cc2.is_deleted = 0 and cc2.valid_timespan_end >= '$date'");

            $tpl->assign('all_stores', false);
        }

        // expired

        if ($store === 'all')
        {
            $CouponsExpired->query("select cc.id, cc.coupon_code, cc.coupon_code_title from coupon_code cc where cc.is_deleted = 0 and cc.valid_timespan_end < '$date'");
            $tpl->assign('all_stores', true);
        }
        else
        {
            $CouponsExpired->query("select cc.id, cc.coupon_code, cc.coupon_code_title from coupon_code cc
					where cc.is_store_specific = 0 and cc.is_deleted = 0 and cc.valid_timespan_end < '$date'
					union
					select cc2.id, cc2.coupon_code, cc2.coupon_code_title from coupon_code cc2
					join coupon_to_store cts on cts.store_id = $store and cts.coupon_code_id = cc2.id
					where cc2.is_store_specific = 1 and cc2.is_deleted = 0 and cc2.valid_timespan_end < '$date'");

            $tpl->assign('all_stores', false);
        }



        $counter = 0;
        $coupon_html_refs = array();
        $coupon_html_refs2 = array();
        while ($Coupons->fetch())
        {
            $Form->AddElement(array(
                CForm::type => CForm::CheckBox,
                CForm::name => "coupon_" . $Coupons->id,
				CForm::dd_type => 'current'
            ));

            $coupon_html_refs[$Coupons->id] = array(
                'box' => "coupon_" . $Coupons->id . "_html",
                'title' => $Coupons->coupon_code_title,
                'code' => $Coupons->coupon_code
            );
            $counter++;
        }


        while ($CouponsExpired->fetch())
        {
            $Form->AddElement(array(
                CForm::type => CForm::CheckBox,
                CForm::name => "coupon_" . $CouponsExpired->id,
				CForm::dd_type => 'expired'

			));
            $coupon_html_refs2[$CouponsExpired->id] = array(
                'box' => "coupon_" . $CouponsExpired->id . "_html",
                'title' => $CouponsExpired->coupon_code_title,
                'code' => $CouponsExpired->coupon_code
            );
            $counter++;
        }


// -------------------


        $tpl->assign('coupon_html_refs', $coupon_html_refs);
        $tpl->assign('coupon_html_refs2', $coupon_html_refs2);

        $title_range = "";

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

        if ($Form->value('report_submit') || (isset($_GET['export']) && $_GET['export'] === "xlsx"))
        {
            $report_submitted = true;
            $sessionArray = null;
            $menu_array_object = null;

            if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
            {
                // these guys are set if the export link was clicked

                $day = $_REQUEST["day"];
                $month = $_REQUEST["month"];
                $year = $_REQUEST["year"];
                $duration = $_REQUEST["duration"];
            }
            else if ($report_type_to_run == 1)
            {
                $implodedDateArray = explode("-", $day_start);
                $day = $implodedDateArray[2];
                $month = $implodedDateArray[1];
                $year = $implodedDateArray[0];
                $duration = '1 DAY';

                $title_range = date('l F jS, Y', mktime(0, 0, 0, $month, $day, $year));
            }
            else if ($report_type_to_run == 2)
            {
                $SessionReport = new CSessionReports();
                $rangeReversed = false;
                $implodedDateArray = null;
                $implodedEndDataArray = null;
                $diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
                $diff++;  // always add one for SQL to work correctly

                if ($rangeReversed == true)
                {
                    $implodedDateArray = explode("-", $range_day_end);
                    $implodedEndDataArray = explode("-", $range_day_start);
                }
                else
                {
                    $implodedDateArray = explode("-", $range_day_start);
                    $implodedEndDataArray = explode("-", $range_day_end);
                }

                $day = $implodedDateArray[2];
                $month = $implodedDateArray[1];
                $year = $implodedDateArray[0];
                $duration = $diff . ' DAY';

                $title_range = " " . date('l F jS, Y', mktime(0, 0, 0, $month, $day, $year)) . " to " . date('l F jS, Y', mktime(0, 0, 0, $implodedEndDataArray[1], $implodedEndDataArray[2], $implodedEndDataArray[0]));
            }
            else if ($report_type_to_run == 3)
            {

                $month = $_REQUEST["month_popup"];
                $month++;
                $year = $_REQUEST["year_field_001"];

                if ($Form->value('menu_or_calendar') == 'menu')
                {
                    // menu month
                    $anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
                    list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
                    $start_date = strtotime($menu_start_date);
                    $year = date("Y", $start_date);
                    $month = date("n", $start_date);
                    $day = date("j", $start_date);

                    $duration = $interval . " DAY";

                    $title_range = "Menu Month of " . date("F", strtotime($anchorDay));
                }
                else
                {

                    // process for a given month
                    $day = "01";
                    $duration = '1 MONTH';

                    $title_range = "Month of " . date("F", mktime(0, 0, 0, $month, 1, $year));
                }
            }
            else if ($report_type_to_run == 4)
            {
                $spansMenu = true;
                $year = $_REQUEST["year_field_002"];
                $month = "01";
                $day = "01";
                $duration = '1 YEAR';

                $title_range = "Year of " . $year;
            }

            $tpl->assign('report_title_range', $title_range);

            // make coupon list
            $couponList = "";
            $requestedCouponArray = array();
            if (isset($_GET['coupons']))
            {
                $couponList = str_replace("|", ",", $_GET['coupons']);
                $requestedCouponArray = explode(",", $couponList);
            }
            else
            {
                foreach ($_POST as $k => $v)
                {
                    if (strpos($k, 'coupon_') === 0)
                    {
                        $couponList .= substr($k, 7) . ",";
                        $requestedCouponArray[] = substr($k, 7);
                    }
                }
                if (!empty($couponList))
                {
                    $couponList = substr($couponList, 0, strlen($couponList) - 1);
                }

                $exportCouponList = str_replace(",", "|", $couponList);
            }

            $tpl->assign('report_day', $day);
            $tpl->assign('report_month', $month);
            $tpl->assign('report_year', $year);
            $tpl->assign('report_duration', $duration);
            $tpl->assign('export_list', $exportCouponList);

            $store_clause = "";
            if ($store !== "all")
            {
                $store_clause = " and s.store_id = $store ";
            }

            $Orders = DAO_CFactory::create("orders");
            $current_date = mktime(0, 0, 0, $month, $day, $year);
            $current_date_sql = date("Y-m-d 00:00:00", $current_date);
            $Orders->query("select  o.coupon_code_id, cc.coupon_code_title, cc.coupon_code, SUM(o.grand_total) as total_spend, 
            SUM(o.coupon_code_discount_total) 
            as total_save, COUNT(o.id) as num_trans from orders o " . "
            join booking b on o.id = b.order_id and status = 'ACTIVE' " . " join session s on b.session_id = s.id $store_clause and 
            s.session_start >= '" . $current_date_sql . "'" . " and s.session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $duration . ")" . " 
            join coupon_code cc on cc.id = o.coupon_code_id " . " where o.coupon_code_id in ($couponList) group by o.coupon_code_id");

            $rows = array();
            $temprows = array();

            while ($Orders->fetch())
            {
                $temprows[$Orders->coupon_code_id] = array(
                    'id' => $Orders->coupon_code_id,
                    'title' => $Orders->coupon_code_title,
                    'code' => $Orders->coupon_code,
                    'total_spend' => $Orders->total_spend,
                    'total_save' => $Orders->total_save,
                    'num_trans' => $Orders->num_trans
                );
            }

            $count = 0;

            foreach ($requestedCouponArray as $couponID)
            {
                if(key_exists($couponID, $coupon_html_refs)) {
                    if (isset($temprows[$couponID])) {
                        $rows[$count++] = $temprows[$couponID];
                    } else {
                        $rows[$count++] = array(
                            'id' => $couponID,
                            'title' => $coupon_html_refs[$couponID]['title'],
                            'code' => $coupon_html_refs[$couponID]['code'],
                            'total_spend' => 0,
                            'total_save' => 0,
                            'num_trans' => 0
                        );
                    }
                }
            }

            foreach ($requestedCouponArray as $couponID)
            {
                if(key_exists($couponID, $coupon_html_refs2)) {
                    if (isset($temprows[$couponID])) {
                        $rows[$count++] = $temprows[$couponID];
                    } else {
                        $rows[$count++] = array(
                            'id' => $couponID,
                            'title' => $coupon_html_refs2[$couponID]['title'],
                            'code' => $coupon_html_refs2[$couponID]['code'],
                            'total_spend' => 0,
                            'total_save' => 0,
                            'num_trans' => 0
                        );
                    }
                }
            }

            if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
            {


                $labels = array(
                    "Coupon Title",
                    "Coupon Code",
                    "Total Spend",
                    "Total Saved",
                    "Num Transactions"
                );

                foreach ($rows as &$detail)
                {
                    unset($detail['id']);
                }

                $tpl->assign('labels', $labels);
                $tpl->assign('rows', $rows);
                $tpl->assign('rowcount', count($rows));
                $numRows = count($rows);
                CLog::RecordReport("Coupon (Excel export)", "Rows:$numRows ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration");
            }
            else
            {
                $report_submitted = true;
                $tpl->assign('report_data', $rows);
                $tpl->assign('report_count', count($rows));

                $numRows = count($rows);
                CLog::RecordReport("Coupon", "Rows:$numRows ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration");
            }
        }

        $formArray = $Form->render();
        $tpl->assign('report_submitted', $report_submitted);
        $tpl->assign('spans_menus', $spansMenu);
        $tpl->assign('report_type', $report_type_to_run);
        $tpl->assign('total_count', $total_count);
        $tpl->assign('report_type_to_run', $report_type_to_run);
        $tpl->assign('form_session_list', $formArray);
        $tpl->assign('store', $store);
        $tpl->assign('page_title', 'Coupon Report');
        if (defined('HOME_SITE_SERVER'))
        {
            $tpl->assign('HOME_SITE_SERVER', true);
        }
    }

}

?>