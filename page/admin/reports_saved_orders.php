<?php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once( "phplib/PHPExcel/PHPExcel.php");
 require_once('ExcelExport.inc');

 class page_admin_reports_saved_orders extends CPageAdminOnly {

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

 	function runFranchiseLead()
 	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
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

 	function runFranchiseOwner() {
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

		$store = NULL;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;

		if ( $this->currentStore ) { //fadmins
				$store = $this->currentStore;
			} else {
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

				$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
										CForm::onChangeSubmit => true,
										CForm::allowAllOption => false,
										CForm::showInactiveStores => true,
										CForm::name => 'store'));


				$store = $Form->value('store');
			}


		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = FALSE;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"]) $report_type_to_run = $_REQUEST["pickDate"];


		$Form->AddElement(array (CForm::type => CForm::Submit,
 		CForm::name => 'report_submit', CForm::value => 'Run Report'));

		//$month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");
		$monthnum = date("n");
		$monthnum--;
		$Form->AddElement(array(CForm::type=> CForm::Text,
						CForm::name => "year_field_001",
						CForm::required => true,
						CForm::default_value => $year,
						CForm::length => 6));

		$Form->AddElement(array(CForm::type=> CForm::Text,
						CForm::name => "year_field_002",
						CForm::required => true,
						CForm::default_value => $year,
						CForm::length => 6));


		$Form->AddElement(array(CForm::type=> CForm::DropDown,
					CForm::onChangeSubmit => false,
					CForm::allowAllOption => false,
					CForm::options => $month_array,
					CForm::default_value => $monthnum,
					CForm::name => 'month_popup'));


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
		}


		if (isset ($_REQUEST["range_day_start"])) {
			  $range_day_start = $_REQUEST["range_day_start"];
		}
		if (isset ($_REQUEST["range_day_end"])) {
			$range_day_end = $_REQUEST["range_day_end"];
		}

		if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"])  && isset ($_REQUEST["duration"])) {
				$day = $_REQUEST["day"];
				$month = $_REQUEST["month"];
				$year = $_REQUEST["year"];
				$duration = $_REQUEST["duration"];
		}

		if (isset ($_REQUEST["report_submit"])) {


			if ($report_type_to_run == 1) {
				$implodedDateArray = explode("-",$day_start) ;
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';

			}
			else if ($report_type_to_run == 2) {
				$rangeReversed = false;
				$implodedDateArray = null;
				$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
				$diff++;  // always add one for SQL to work correctly
				if ($rangeReversed == true)
					$implodedDateArray = explode("-",$range_day_end);
				else
					$implodedDateArray = explode("-",$range_day_start) ;

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';
			}
			else if ($report_type_to_run == 3) {

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

			}
			else if ($report_type_to_run == 4) {
				$spansMenu = TRUE;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';

			}


			$greatestNumberCancelled = 1;

	    	$rows = $this->findCustomers($store, $day, $month, $year,  $duration, $greatestNumberCancelled);
	    	$numRows = count($rows);

	    	CLog::RecordReport("Saved Orders (Excel Export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration" );

	    	if ($numRows)
	    	{
				$labels = array("ID", "Last Name", "First Name", "PLATEPOINTS Status", "Last Session",  "Primary Email",  "Primary Telephone", "Secondary Telephone", "Orders Canceled" );

				$columnDescs = array();

				$columnDescs['A'] = array('align' => 'left', 'width' => 'auto'); //id
				$columnDescs['B'] = array('align' => 'left', 'width' => 'auto'); //first
				$columnDescs['C'] = array('align' => 'left', 'width' => 'auto'); //last
				$columnDescs['D'] = array('align' => 'left', 'width' => 'auto'); //DR status
				$columnDescs['E'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto'); // next session
				$columnDescs['F'] = array('align' => 'left', 'width' => 'auto'); //primary email
				$columnDescs['G'] = array('align' => 'left', 'width' => '12'); //telephone 1
				$columnDescs['H'] = array('align' => 'left', 'width' => '12'); //telephone 2
				$columnDescs['I'] = array('align' => 'center', 'width' => 7); // order count

				$col = 'J';
				$colSecondChar = '';
				$thirdSecondChar = '';

				//session type

				for($x = 0; $x < $greatestNumberCancelled; $x++)
				{
					$labels = array_merge($labels, array("Session", "Order Type", "Order Total", "Order Link", "Time of Cancellation", "Admin Notes"));

					$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto', 'decor' => 'left_border'); // session
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 'auto'); // session
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'type' => 'currency', 'width' => 'auto'); //
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'type' => 'URL', 'width' => 'auto'); //
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto'); //
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar.$col] = array('align' => 'left', 'type' => 'text', 'width' => '40'); //
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

				}


				$this->postProcess($rows, $store);

				$tpl->assign('col_descriptions', $columnDescs );

				$tpl->assign('labels', $labels);
				$tpl->assign('rows', $rows);
				$tpl->assign('rowcount', $numRows);

				$_GET['export'] = 'xlsx';
	    	}
	    	else
	    	{

	    		$tpl->assign('empty_result', true);
	    	}

		}

		$formArray = $Form->render();


		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Saved Order Report');
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}

	function postProcess(&$rows, $store)
	{

		foreach($rows as &$data)
		{
			if (($data['dream_reward_status'] == 1 || $data['dream_reward_status'] == 3) && $data['dream_rewards_version']  == 3)
			{

			    $dataObj = DAO_CFactory::create('user');
			    $dataObj->query("select max(total_points) as point_level from points_user_history where user_id = {$data['id']} and is_deleted = 0 group by user_id");
			    $dataObj->fetch();

			    $total_points = $dataObj->point_level;
			    list($cur_level, $next_level) = CPointsUserHistory::getLevelDetailsByPoints($total_points);


				$data['dream_reward_status'] = $total_points . " / "  . $cur_level['title'];
			}
			else
			{
				$data['dream_reward_status'] = "n/a";
			}

			unset($data['dream_reward_level']);
			unset($data['dream_rewards_version']);

			$sessionObj = DAO_CFactory::create('booking');
			$sessionObj->query("select max(s.session_start) as last_session from booking b join session s on s.id = b.session_id where b.user_id = {$data['id']} and b.status = 'ACTIVE'  group by b.user_id");

			if ($sessionObj->fetch())
			{
				$data['last_session'] = PHPExcel_Shared_Date::stringToExcel($sessionObj->last_session);
			}
			else
			{
				$data['last_session'] = "none";
			}
		}
	}


	function findCustomers ($store_id, $Day, $Month, $Year, $Interval, &$greatestNumberCancelled)
	{
		$booking = DAO_CFactory::create("booking");


		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);


		$colcount = 18;  // MAKE SURE YOU UPDATE THIS IF YOU ADD MORE SELECT STATEMENT COLS

		$querystr = "select user.id, `user`.lastname,`user`.firstname, user.dream_reward_level,
		user.dream_rewards_version, user.dream_reward_status, 0 as last_session, `user`.primary_email,`user`.telephone_1,`user`.telephone_2, 
		Count(orders.id) AS orders_made,
		GROUP_CONCAT(session_start) AS sessions_attended,
		GROUP_CONCAT(session_type) as session_type,
		GROUP_CONCAT(orders.type_of_order) as order_type,
		GROUP_CONCAT(orders.grand_total) AS grand_total,
		GROUP_CONCAT(orders.id) as order_link,
		GROUP_CONCAT(booking.timestamp_updated) as saved_times,
		GROUP_CONCAT(orders.order_admin_notes) as notes
		From booking
		Inner Join orders ON booking.order_id = orders.id
		Inner Join session ON booking.session_id = session.id Inner Join `user` ON booking.user_id = `user`.id
		Inner Join address ON `user`.id = address.user_id
		Where orders.store_id = $store_id AND  session_start >= '$current_date_sql' AND
		session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval ) AND  booking.status = 'SAVED' and session_publish_state != 'SAVED' and booking.is_deleted = 0 group by user.id order by user.lastname, user.firstname";



		$booking->query($querystr);
		$rows = array();
		$count = 0;

		while ($booking->fetch()) {

		  $tarray = $booking->toArray();
		  array_splice($tarray, $colcount, count($tarray));
		  $tempDate = $tarray['sessions_attended'];
		  $newSessionStr = "";

		  if (count($tempDate) > 0) {

			  $sep = explode(",",$tempDate);

			  $countarr = count($sep);

			  if ($countarr > 1)
			  {

			  		$session_types = explode(",",$tarray['session_type']);
			  		$order_types = explode(",",$tarray['order_type']);
			  		$grand_totals = explode(",",$tarray['grand_total']);
			  		$IDs = explode(",",$tarray['order_link']);
			  		$savedTimes = explode(",",$tarray['saved_times']);
			  		$adminNotes = explode(",",$tarray['notes']);

			  		if ($countarr > $greatestNumberCancelled) $greatestNumberCancelled = $countarr;

			    	for ($var=0; $var < $countarr; $var++)
			    	{
			      		$ts = $sep[$var];
			      		$session_type = $session_types[$var];
			      		$order_type = $order_types[$var];
			      		$total = $grand_totals[$var];
			      		$ID = $IDs[$var];
			      		$savedTime = $savedTimes[$var];
			      		$thisNote = $adminNotes[$var];


						$convertTimeStamp = PHPExcel_Shared_Date::stringToExcel($sep[$var]);
						$savedTime = PHPExcel_Shared_Date::stringToExcel($savedTime);

						if ($var == 0)
						{
						    	$tarray['sessions_attended'] = $convertTimeStamp;
						    	$tarray['session_type'] = $session_type;
						    	$tarray['order_type'] = $order_type;
						    	$tarray['grand_total'] = $total;
						    	$tarray['order_link'] =  "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_order_details&order=" . $ID ."\", \"" . 'Link to Order' . "\")";
						    	$tarray['saved_times'] = $savedTime;
						    	$tarray['notes'] = $thisNote;

						    	if ($tarray['session_type'] == 'SPECIAL_EVENT')
						    	{
						    		if ($tarray['order_type'] == 'INTRO')
						    			$tarray['order_type'] = "MFY - STARTER PACK";
						    		else
						    			$tarray['order_type'] = "MFY";
						    	}
						    	else if ($tarray['order_type'] == 'INTRO')
						    	{
                                    $tarray['order_type'] = 'STARTER PACK';
                                }

						    	unset($tarray['session_type'] );


						}
						else
						{

								if ($session_type == 'SPECIAL_EVENT')
								{
									if ($order_type == 'INTRO')
										$order_type = "MFY - INTRO";
									else
										$order_type = "MFY";
								}
                                else if ($order_type == 'INTRO')
                                {
                                    $order_type = 'STARTER PACK';
                                }

								array_push($tarray,$convertTimeStamp);
								array_push($tarray,$order_type);
								array_push($tarray,$total);
								array_push($tarray, "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_order_mgr&order=" . $ID ."\", \"" . 'Link to Order'. "\")");
								array_push($tarray, $savedTime);
								array_push($tarray, $thisNote);


						}
				 	}
			 	}
			 else
			 {
			 	$tarray['sessions_attended'] = PHPExcel_Shared_Date::stringToExcel($tempDate);

 				if ($tarray['session_type'] == 'SPECIAL_EVENT')
			    {
			    	if ($tarray['order_type'] == 'INTRO')
			    		$tarray['order_type'] = "MFY - INTRO";
			    	else
			    		$tarray['order_type'] = "MFY";
			    }
                else if ($tarray['order_type'] == 'INTRO')
                {
                    $tarray['order_type'] = 'STARTER PACK';
                }

                 $tarray['order_link'] = "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_order_mgr&order=" . $tarray['order_link'] ."\", \"" . 'Link to Order' . "\")";
			    $tarray['saved_times'] = PHPExcel_Shared_Date::stringToExcel($tarray['saved_times']);


			 	unset($tarray['session_type'] );


			 }
		  }

		  $rows [$count++]=  $tarray;
		}


		return ($rows);

	}


}



?>