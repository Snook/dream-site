<?php // page_admin_create_store.php
/**
 *
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');

class page_admin_reports_customer_referrals_revenue extends CPageAdminOnly {
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
    
    function runFranchiseManager()
    {
    	$this->runFranchiseOwner();
    }
    
    function runOpsLead()
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

    function runFranchiseOwner()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runSiteAdmin();
    }

    function runSiteAdmin()
    {
    	if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
    	{
    		ini_set('memory_limit','-1');
    		set_time_limit(3600 * 24);
    	}
    	
        $store = null;
        $SessionReport = new CSessionReports();
        $report_type_to_run = 1;
        $tpl = CApp::instance()->template();
        $Form = new CForm();
        $Form->Repost = false;
        $report_submitted = false;

        if ($this->currentStore) { // fadmins
            $store = $this->currentStore;

            $groupbyfilter = null;
        } else { // site admin
            $Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

            if (!defined('HOME_SITE_SERVER')) {
                $Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
                        CForm::onChangeSubmit => true,
                        CForm::allowAllOption => true,
                        CForm::showInactiveStores => true,
                        CForm::name => 'store'));
            } else {
                $Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
                        CForm::onChangeSubmit => true,
                        CForm::allowAllOption => true,
                        CForm::showInactiveStores => true,
                        CForm::name => 'store'));
            }
            $store = $Form->value('store');

            $groupbyfilter = $store;
        }

        $day = 0;
        $month = 0;
        $year = 0;
        $duration = "1 DAY";

        if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"]) $report_type_to_run = $_REQUEST["pickDate"];

        $Form->AddElement(array (CForm::type => CForm::Submit,
                CForm::name => 'report_submit', CForm::value => 'Run Report'));

        $month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

        $year = date("Y");
        $monthnum = date("n");
        $monthnum--;
        $Form->AddElement(array(CForm::type => CForm::Text,
                CForm::name => "year_field_001",
                CForm::required => true,
                CForm::default_value => $year,
                CForm::length => 6));

        $Form->AddElement(array(CForm::type => CForm::Text,
                CForm::name => "year_field_002",
                CForm::required => true,
                CForm::default_value => $year,
                CForm::length => 6));

        $Form->AddElement(array(CForm::type => CForm::DropDown,
                CForm::onChangeSubmit => false,
                CForm::allowAllOption => false,
                CForm::options => $month_array,
                CForm::default_value => $monthnum,
                CForm::name => 'month_popup'));

        $Form->AddElement(array(CForm::type => CForm::DropDown,
                CForm::onChangeSubmit => false,
                CForm::allowAllOption => false,
                CForm::options => array("all" => "Show All Referrals Types" , "todd" => "Event Referrals", "iaf" => "IAF Referrals", "direct" => "Web Referrals", "override"=>"Direct Override"),
                CForm::default_value => 0,
                CForm::name => 'filter'));
        
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

        if (isset ($_REQUEST["single_date"])) {
            $day_start = $_REQUEST["single_date"];
            $tpl->assign('day_start_set', $day_start);
        }

        if (isset ($_REQUEST["range_day_start"])) {
            $range_day_start = $_REQUEST["range_day_start"];
            $tpl->assign('range_day_start_set', $range_day_start);
        }
        if (isset ($_REQUEST["range_day_end"])) {
            $range_day_end = $_REQUEST["range_day_end"];
            $tpl->assign('range_day_end_set', $range_day_end);
        }

        $discountFilter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "all";

        $labels = array();
        if ($groupbyfilter == null) {
            if ($discountFilter == "all")
                $labels = array("User ID", "First Name", "Last Name", "Primary Email", "Address 1", "Address 2", "City", "State", "Zip Code", "Referral Type", "Successful Referrals",
                		 "Revenue Generated", "Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days", "Credit Expired", "Referred Users");

            else
                $labels = array("User ID", "First Name", "Last Name", "Primary Email", "Address 1", "Address 2", "City", "State", "Zip Code", "Successful Referrals",
                		 "Revenue Generated", "Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days", "Credit Expired", "Referred Users");
        } else {
            if ($groupbyfilter == "all") {
                if ($discountFilter == "all")
                    $labels = array("User ID", "Primary Email", "Home Office ID", "Store ID", "Store Name", "Store City", "Store State", "Referral Type", "Successful Referrals",
                    		 "Revenue Generated", "Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days","Credit Expired", "Referred Users");
                else
                    $labels = array("User ID", "Primary Email", "Home Office ID", "Store ID", "Store Name", "Store City", "Store State", "Successful Referrals", "Revenue Generated",
                    		"Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days","Credit Expired", "Referred Users");
            } else {
                if ($discountFilter == "all")
                    $labels = array("User ID", "Primary Email", "Home Office ID", "Store ID", "Store Name", "Store City", "Store State", "Referral Type", "Successful Referrals",
                    		 "Revenue Generated", "Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days","Credit Expired", "Referred Users");
                else
                    $labels = array("User ID", "Primary Email", "Home Office ID", "Store ID", "Store Name", "Store City", "Store State", "Successful Referrals", "Revenue Generated",
                    		 "Referral Credit Available", "Referral Credit Used", "Credit Due to Expire in next 60 days","Credit Expired", "Referred Users");
            }
        }

        if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"])) {
            $day = $_REQUEST["day"];
            $month = $_REQUEST["month"];
            $year = $_REQUEST["year"];
            $duration = $_REQUEST["duration"];

            $rows = $this->GetDataByCustomer($groupbyfilter, $store, $discountFilter, $duration, $day, $month, $year);

            $this->FinishProcessing($rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year);

            $this->ConsolidateRows($rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year);
            $numRows = count($rows);
            CLog::RecordReport("Customer Referral Revenue (Excel Export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run ~ Filter: $discountFilter");


            $tpl->assign('labels', $labels);
            $tpl->assign('rows', $rows);
            $tpl->assign('rowcount', $numRows);
        }

        if ($Form->value('report_submit')) {
            $report_submitted = true;

            if ($report_type_to_run == 1) {
                $implodedDateArray = explode("-", $day_start) ;
                $day = $implodedDateArray[2];
                $month = $implodedDateArray[1];
                $year = $implodedDateArray[0];
                $duration = '1 DAY';
            } else if ($report_type_to_run == 2) {
                // process for an entire year
                $rangeReversed = false;
                $implodedDateArray = null;
                $diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
                $diff++; // always add one for SQL to work correctly
                if ($rangeReversed == true)
                    $implodedDateArray = explode("-", $range_day_end);
                else
                    $implodedDateArray = explode("-", $range_day_start) ;

                $day = $implodedDateArray[2];
                $month = $implodedDateArray[1];
                $year = $implodedDateArray[0];
                $duration = $diff . ' DAY';
            } else if ($report_type_to_run == 3) {
            	
            	
            	$month = $_REQUEST["month_popup"];
            	$month++;
            	$year = $_REQUEST["year_field_001"];
            	 
            	
            	if ($Form->value('menu_or_calendar') == 'menu')
            	{
            		// menu month
            		$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
            		list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
            		$start_date = strtotime($menu_start_date);
            		$year = date("Y", $start_date);
            		$month = date("n", $start_date);
            		$day = date("j", $start_date);
            			
            		$duration = $interval . " DAY";
            	}
            	else
            	{
                	// process for a given month
               		$day = "01";
             		$duration = '1 MONTH';
            	}
            } else if ($report_type_to_run == 4) {
                $year = $_REQUEST["year_field_002"];
                $month = "01";
                $day = "01";
                $duration = '1 YEAR';
            }

            $rows = $this->GetDataByCustomer($groupbyfilter, $store, $discountFilter, $duration, $day, $month, $year);

            $this->FinishProcessing($rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year);

            $this->ConsolidateRows($rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year);

            $tpl->assign('labels', $labels);

            $tpl->assign('report_day', $day);
            $tpl->assign('report_month', $month);
            $tpl->assign('report_year', $year);
            $tpl->assign('report_duration', $duration);

            $tpl->assign('referraltypefilter', $discountFilter);
            $tpl->assign('groupbyfilter', $groupbyfilter);

            $numRows = count($rows);
            CLog::RecordReport("Customer Referral Revenue", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run ~ Filter: $discountFilter");

            $tpl->assign('report_data', $rows);
            $tpl->assign('report_count', $numRows);
        }

        $formArray = $Form->render();

        $tpl->assign('report_submitted', $report_submitted);
        $tpl->assign('report_type_to_run', $report_type_to_run);
        $tpl->assign('form_session_list', $formArray);
        $tpl->assign('page_title', 'Customer Referral Revenue Reporting');
        if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', true);
    }

    function ConsolidateRows (&$rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year)
    {
        foreach($rows as $key => $element) {
            unset($rows[$key]['store_credit']);
            unset($rows[$key]['DateEnd']);
            unset($rows[$key]['is_deleted']);

        }
    }



    function GetDataByCustomer($groupbyfilter, $storeid, $programType, $Interval, $day, $month, $year)
    {
        $rows = array();
        $current_date = mktime(0, 0, 0, $month, $day, $year);
        $current_date_sql = date("Y-m-d 00:00:00", $current_date);

        $Referrals = DAO_CFactory::create('customer_referral');

        /*
        $var = "SELECT `customer_referral`.id as rID, DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) as DateEnd,  group_concat(distinct customer_referral.store_credit_id) as creditids, " .
        "`user`.`id`  as `UserID`, " . "`user`.`firstname` as `FirstName`," . "`user`.`lastname` as  `LastName` ," . "`user`.`primary_email` as `PrimaryEmail`," . "`store`.`city` as `StoreCity`," .
         "`store`.`state_id` as `StoreState`," . "`store`.`id` as `StoreID`," . "`store`.`store_name` as `store_name`," . "`store`.`home_office_id` as `home_office_id`," .
        "`address`.`address_line1` as `Address1`," . "`address`.`address_line2` as `Address2`," . "`address`.`city` as City," . "`address`.`state_id` as State," .
        "`address`.`postal_code` as `ZipCode`," . "`origination_type_code` `ReferralType`," . "sum(`orders`.`grand_total`) AS `RevenueGenerated`," .
         "count(`customer_referral`.id) as `SuccessfulReferrals` , GROUP_CONCAT(CONCAT(new_user.firstname, ' ', new_user.lastname)) as referred_user" . " FROM " . " `customer_referral`" . " Left Join `user` ON `customer_referral`.`referring_user_id` = `user`.`id`" .
         " " .
         " Left Join `orders` ON `customer_referral`.`first_order_id` = `orders`.`id`" . " Left Join `booking` ON `orders`.`id` = `booking`.`order_id`" .
         " Left Join `session` ON `booking`.`session_id` = `session`.`id`" . " Left Join `address` ON `customer_referral`.`referring_user_id` = `address`.`user_id` LEFT JOIN user new_user on new_user.id =  `customer_referral`.`referred_user_id`" .
         " Left Join `store` ON `orders`.`store_id` = `store`.`id`" . "
         where `referral_status` = 4 and `customer_referral`.is_deleted = 0" . " and `booking`.`status` = 'ACTIVE' and  session_publish_state != 'SAVED' " ;

        if ($groupbyfilter != "all") {
            $var .= " and `orders`.`store_id` = $storeid  " ;
        }

        $var .= " and (customer_referral.timestamp_created >= '" . $current_date_sql . "' AND" . " customer_referral.timestamp_created <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) )  " ;

        if ($programType == "iaf")
            $var .= " and origination_type_code = 1";
        else if ($programType == "direct")
            $var .= " and origination_type_code = 3";
        else if ($programType == "todd")
            $var .= " and origination_type_code = 4";
		else if ($programType == "override")
            $var .= " and origination_type_code = 5";

        $var .= " group by user.id, `origination_type_code`";


 */

        $var = "SELECT
        `customer_referral`.id as rID
        ,DATE_ADD('$current_date_sql'
        , INTERVAL $Interval ) as DateEnd
        ,  group_concat(distinct customer_referral.store_credit_id) as creditids
        , `user`.`id`  as `UserID`
        ,`user`.`firstname` as `FirstName`
        ,`user`.`lastname` as  `LastName`
        ,`user`.`primary_email` as `PrimaryEmail`
        ,`store`.`city` as `StoreCity`
        ,`store`.`state_id` as `StoreState`
        ,`store`.`id` as `StoreID`
        ,`store`.`store_name` as `store_name`
        ,`store`.`home_office_id` as `home_office_id`
        ,`origination_type_code` as `ReferralType`
        ,sum(`orders`.`grand_total`) AS `RevenueGenerated`
        ,count(`customer_referral`.id) as `SuccessfulReferrals`
        , GROUP_CONCAT(CONCAT(new_user.firstname, ' ', new_user.lastname)) as referred_user
        FROM  `customer_referral`
        Left Join `user` ON `customer_referral`.`referring_user_id` = `user`.`id` and `user`.`is_deleted` = 0
        Left Join `orders` ON `customer_referral`.`first_order_id` = `orders`.`id` and `orders`.`is_deleted` = 0
        Left Join `booking` ON `orders`.`id` = `booking`.`order_id` and `booking`.`status` = 'ACTIVE' and `booking`.`is_deleted` = 0
        JOIN user new_user on new_user.id =  `customer_referral`.`referred_user_id` ";

        if ($groupbyfilter != "all") {
            $var .= " and `new_user`.`home_store_id` = $storeid  " ;
        }

        $var .= " Left Join `store` ON `new_user`.`home_store_id` = `store`.`id`
            where  `referral_status` = 4 and `customer_referral`.is_deleted = 0
            and (customer_referral.timestamp_created >= '$current_date_sql'
            AND customer_referral.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )) ";


        if ($programType == "iaf")
            $var .= " and origination_type_code = 1 ";
        else if ($programType == "direct")
            $var .= " and origination_type_code = 3 ";
        else if ($programType == "todd")
            $var .= " and origination_type_code = 4 ";
        else if ($programType == "override")
            $var .= " and origination_type_code = 5 ";


        $var .= " group by user.id, `origination_type_code`";


        $Referrals->query($var);

		$curretUserId = '';
        while ($Referrals->fetch()) {
            $tarray = $Referrals->toArray();
            $temparray = array();
            $temparray['DateEnd'] = $tarray['DateEnd'];
            $temparray['store_credit'] = $tarray['creditids'];



            if ($groupbyfilter == null) {
                $temparray['First Name'] = $tarray['FirstName'];
                $temparray['Last Name'] = $tarray['LastName'];
            }

			if($curretUserId == '' || $curretUserId != $tarray['UserID']){
				$temparray['User ID'] = $tarray['UserID'];
				$temparray['Primary Email'] = $tarray['PrimaryEmail'];


				$temparray['Store ID'] = $tarray['StoreID'];
				$temparray['Home Office ID'] = $tarray['home_office_id'];
				$temparray['Store Name'] = $tarray['store_name'];

				$temparray['Store City'] = $tarray['StoreCity'];
				$temparray['Store State'] = $tarray['StoreState'];
			}else{
				$temparray['User ID'] = '';
				$temparray['Primary Email'] = '';

				$temparray['Store ID'] = '';
				$temparray['Home Office ID'] = '';
				$temparray['Store Name'] = '';

				$temparray['Store City'] = '';
				$temparray['Store State'] = '';

			}

			$curretUserId = $tarray['UserID'];

            if ($tarray['ReferralType'] == 1)
                $temparray['Referral Type'] = "IAF";
            else if ($tarray['ReferralType'] == 3)
                $temparray['Referral Type'] = "Web";
            else if ($tarray['ReferralType'] == 4)
                $temparray['Referral Type'] = "Event";
            else if ($tarray['ReferralType'] == 5)
                $temparray['Referral Type'] = "Direct Override";
			else if ($tarray['ReferralType'] == 6)
				$temparray['Referral Type'] = "Session Invite URL";
			else if ($tarray['ReferralType'] == 7)
				$temparray['Referral Type'] = "Personal Referral URL";
			else
				$temparray['Referral Type'] = $tarray['ReferralType'];

			$temparray['Successful Referrals'] = $tarray['SuccessfulReferrals'];

            $temparray['Revenue Generated'] =  (empty($tarray['RevenueGenerated']) ? "Unknown" : $tarray['RevenueGenerated']);

            $temparray['Referral Credit Available'] = 0;

            $temparray['Referral Credit Used'] = 0;

            $temparray['Credit Due to Expire in next 60 days'] = 0;

            $temparray['Credit Expired'] = 0;

            $temparray['Referred Users'] =  $tarray['referred_user'];

			$refid = $tarray['rID'];

			$this->BuildCreditReportForReferrer($refid,$temparray);

            $rows[$refid] = $temparray;
        }
        return $rows;
    }

	function FinishProcessing(&$rows, $groupbyfilter, $discountFilter, $duration, $day, $month, $year)
	{
		$storecreditlist = "";
		foreach($rows as $key => $element) {
			$storecreditlist .= $element['store_credit'] . ',';
		}
		$storecreditlist = substr_replace($storecreditlist, "", -1);

		$var = "SELECT  `store_credit`.`store_id`,`store_credit`.`user_id`,`store_credit`.`amount`,`store_credit`.`credit_type`,`store_credit`.`is_redeemed`,`store_credit`.`is_expired`,`store_credit`.`timestamp_created`,`store_credit`.`date_original_credit`,`store_credit`.`original_credit_id`,`store_credit`.`id`" . " FROM `store_credit` where  original_credit_id in ($storecreditlist) or id in ($storecreditlist) " . " and `store_credit`.`is_deleted` = 0 ";

		$Referrals = DAO_CFactory::create('store_credit');
		$Referrals->query($var);

		while ($Referrals->fetch()) {
			//$key = $Referrals->user_id;
			$key = null;
			//

			$tarray = $Referrals->toArray();


			foreach($rows  as  $tempkey => $element){
				$creditlist = $element['store_credit'];

				$creditarray =  explode(",", $creditlist);

				$temparrayCredit = $tarray["id"];  // local credit id
				//	$id = $element['store_credit'];

				if (in_array($temparrayCredit, $creditarray)) {
					$key = $tempkey;
					break;

				}


				//	if ($id == $temparrayCredit) {
				//			$key = $tempkey;
				//			break;
				//	}


			}
			if (!empty($key)) {


				if ($tarray['is_redeemed'] == 1)
					$rows[$key]['Referral Credit Used'] += $tarray['amount'];
				else if ($tarray['is_expired'] == 1)
					$rows[$key]['Credit Expired'] += $tarray['amount'];
				else {
					$rows[$key]['Referral Credit Available'] += $tarray['amount'];

					$tarray['origination_date'] = empty($tarray['date_original_credit']) ? $tarray['timestamp_created'] : $tarray['date_original_credit'];

					$expirationTS = strtotime($tarray['origination_date']); // this is 60 days out...

					$ts = strtotime($rows[$key]['DateEnd']) - (86400 * 60);

					if ($expirationTS <= $ts) {
						$rows[$key]['Credit Due to Expire in next 60 days'] += $tarray['amount'];
					}
				}
			}

		}
	}

	function BuildCreditReportForReferrer($refid, &$record)
	{
		$Store_Credit = DAO_CFactory::create('store_credit');
		$varStr = "Select store_credit.is_redeemed, store_credit.is_expired, store_credit.user_id, store_credit.credit_card_number,  
			store_credit.date_original_credit, store_credit.amount, store_credit.payment_transaction_number,  store_credit.timestamp_created,  
			store_credit.credit_type, cr.origination_type_code 
			From store_credit 
			Inner join customer_referral cr on cr.store_credit_id = store_credit.id 
			where cr.id = $refid and store_credit.is_deleted = 0  order by store_credit.timestamp_created";
		$Store_Credit->query($varStr);

		while ($Store_Credit->fetch()) {
			$tarray = $Store_Credit->toArray();

			if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 0) {
				if ($tarray['credit_type'] == 2) {
					$record['Referral Credit Available'] += $tarray['amount'];
				}
			}
			if ($tarray['is_redeemed'] == 1 && $tarray['is_expired'] == 0) {
				if ($tarray['credit_type'] == 2) {
					$record['Referral Credit Used'] += $tarray['amount'];
				}
			}

			if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 1) {
				if ($tarray['credit_type'] == 2) {
					$record['Credit Expired'] += $tarray['amount'];
				}
			}
		}
	}

    function BuildCreditReport (&$user_array, $userlistarr, $reportType, $startDay, $endDay, $store_id)
    {
        $userlist = implode(',', $userlistarr);

        if (empty($userlist)) return;

        $Store_Credit = DAO_CFactory::create('store_credit');

        $varStr = "Select store_credit.is_redeemed, store_credit.is_expired, store.id as store_id, store.store_name, store_credit.user_id, store_credit.credit_card_number, " . " store_credit.date_original_credit, store_credit.amount,store_credit.payment_transaction_number,  store_credit.timestamp_created, " . " store_credit.credit_type, cr.origination_type_code " . " From store_credit Inner Join `store` ON store_credit.store_id = `store`.id left join customer_referral cr on cr.store_credit_id = store_credit.id " . " where store_credit.user_id  in ($userlist)  and store_credit.is_deleted = 0  order by store_credit.timestamp_created";


        $Store_Credit->query($varStr);

        while ($Store_Credit->fetch()) {
            $tarray = $Store_Credit->toArray();

            $userid = $tarray['user_id'];
            $retentionid = array_search($userid, $userlistarr);
            // break up the user array...
            if ($reportType == 1) { // we are looking for available credit
                if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 0) {
                    if ($tarray['credit_type'] == 2) {
                        if (!empty($user_array[$retentionid])) {
                            $user_array[$retentionid]['ReferralCredit'] += $tarray['amount'];
                        }
                    } else if ($tarray['credit_type'] == 3) {
                        if (!empty($user_array[$retentionid])) {
                            $user_array[$retentionid]['DirectCredit'] += $tarray['amount'];
                        }
                    }
                }
            } else if ($reportType == 2) { // we are looking for available credit
                if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 1) {
                    if ($tarray['credit_type'] == 2) {
                        if (!empty($user_array[$retentionid])) {
                            $user_array[$retentionid]['ReferralCreditExpired'] += $tarray['amount'];
                        }
                    } else if ($tarray['credit_type'] == 3) {
                        if (!empty($user_array[$retentionid])) {
                            $user_array[$retentionid]['DirectCreditExpired'] += $tarray['amount'];
                        }
                    }
                }
            }
        }
    }
}

?>