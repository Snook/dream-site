<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CUser.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPointsCredits.php');
require_once ('includes/CSessionReports.inc');
require_once( "phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');



require_once('fpdf/class_multicelltag.php');

class page_admin_reports_points_status_by_date extends CPageAdminOnly {

	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runFranchiseStaff()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}
	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}


	function getStatusDisplayString($inStatus, $User)
	{
		$retVal = $inStatus;

		if ($inStatus == "in_DR2")
		{
			$retVal = "Dream Rewards 2 ";
			if ($User->dream_reward_level > 12)
				$retVal .= " (VIP)";


			$retVal .= " Level " . $User->dream_reward_level;

		}

		return $retVal;

	}

	function runSiteAdmin()
	{
 		ini_set('memory_limit','-1');
 		set_time_limit(3600);


		$store = NULL;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = TRUE;
		$total_count = 0;
		$report_submitted = FALSE;

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = FALSE;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"]) $report_type_to_run = $_REQUEST["pickDate"];

		$Form->AddElement(array (CForm::type => CForm::Submit,
	 		CForm::name => 'report_submit', CForm::value => 'Run Report'));

		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");
		$monthnum = date("n");
		$monthnum--;

		$defaultMonthValue = 0;
		$requestDatesFound = false;
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"])  && isset($_REQUEST["duration"]) && isset($_REQUEST["report_type"]))
		{
			$year = $_REQUEST["year"];
			$month = $_REQUEST["month"];
			$day = "01";
			$duration = $_REQUEST["duration"];
			$report_type_to_run = $_REQUEST["report_type"];
			$start_date = mktime(0, 0, 0, $month, $day, $year);
			$enddate = strtotime("+1 month", $start_date);
			$defaultMonthValue = $month-1;
			$requestDatesFound = true;
		}

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

		$Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => 'requested_stores'));


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

		$isHomeOfficeAccess = !CUser::getCurrentUser()->isFranchiseAccess();

		global $lastColumn;


 		if ( $this->currentStore )
		{
			//fadmins
			$currentStore = $this->currentStore;
		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
									CForm::onChangeSubmit => false,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => false,
									CForm::name => 'store'));

			$currentStore = $Form->value('store');
		}

		$report_submit = isset($_REQUEST['submit_report'])? $_REQUEST['submit_report'] : NULL;
		$exportData = isset($_REQUEST['datainput'])? $_REQUEST['datainput'] : NULL;
		if ($exportData == "null" )
		{
			$exportData = NULL;
		}
		if ($report_submit != NULL)
		{
			$exportData = NULL;
		}
		$showPaymentType = isset($_REQUEST['payment_type']) ? $_REQUEST['payment_type'] : NULL;

		if ( $report_submit != NULL || $exportData != NULL  || $requestDatesFound == true)
		{
			$report_submitted = TRUE;
			$sessionArray = null;
			$menu_array_object = NULL;

				if ($report_type_to_run == 1)
				{
					$implodedDateArray = explode("-",$day_start) ;
					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = '1 DAY';
				}
				else if ($report_type_to_run == 2)
				{
					// process for an entire year
					$rangeReversed = false;
					$implodedDateArray = null;
					$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
					$diff++;  // always add one for SQL to work correctly
					if ($rangeReversed == true)
					{
						$implodedDateArray = explode("-",$range_day_end);
					}
					else
					{
					 	$implodedDateArray = explode("-",$range_day_start) ;
					}

					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = $diff . ' DAY';
				}
				else if ($report_type_to_run == 3)
				{
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
				else if ($report_type_to_run == 4)
				{
					$spansMenu = TRUE;
					$year = $_REQUEST["year_field_002"];
					$month = "01";
					$day = "01";
					$duration = '1 YEAR';
				}


				$start_date = mktime(0, 0, 0, $month, $day, $year);
				$enddate = strtotime("+" . $duration, $start_date);

				if ($isHomeOfficeAccess)
					$header = array ('Store', 'Guest ID', 'Last Name', 'First Name', 'Primary Email', 'Telephone 1', 'Tel 1 Call Time', 'Telephone 2', 'Tel 2 Call Time',
							 'Loyalty Status','PLATEPOINTS', 'Award Due', 'Next Session', 'PlatePoints to Next Award', 'Next Award', 'Birthday Month', 'Address Line 1',
								'Address line 2', 'City', 'State', 'Postal Code', 'Conversion Points', 'Conversion Credit');
				else
					$header = array ( 'Guest ID', 'Last Name', 'First Name', 'Primary Email', 'Telephone 1', 'Tel 1 Call Time', 'Telephone 2', 'Tel 2 Call Time',
							 'Loyalty Status','PLATEPOINTS', 'Award Due', 'Next Session', 'PlatePoints to Next Award', 'Next Award', 'Birthday Month', 'Address Line 1',
							'Address line 2', 'City', 'State', 'Postal Code', 'Conversion Points', 'Conversion Credit');


				$tpl->assign('labels', $header);

				$columnDescs = array();

			if ($isHomeOfficeAccess)
			{
				$columnDescs['A'] = array('align' => 'center', 'width' => 30);
				$columnDescs['B'] = array('align' => 'center');
				$columnDescs['C'] = array('align' => 'center');
				$columnDescs['D'] = array('align' => 'center');
				$columnDescs['E'] = array('align' => 'center');
				$columnDescs['F'] = array('align' => 'center');
				$columnDescs['G'] = array('align' => 'center');
				$columnDescs['H'] = array('align' => 'center');
				$columnDescs['I'] = array('align' => 'center');
				$columnDescs['J'] = array('align' => 'center', 'width' => 20);
				$columnDescs['K'] = array('align' => 'center');
				$columnDescs['L'] = array('align' => 'center', 'width' => 22);
				$columnDescs['M'] = array('align' => 'center', 'type' => 'datetime', 'width' => 20);
				$columnDescs['N'] = array('align' => 'center');
				$columnDescs['O'] = array('align' => 'center', 'width' => 22);
				$columnDescs['P'] = array('align' => 'center', 'width' => 12);
				$columnDescs['Q'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['R'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['S'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['T'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['U'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['V'] = array('align' => 'center', 'width' => 'auto');
				$columnDescs['W'] = array('align' => 'center', 'width' => 'auto');
			}
			else
			{
				$columnDescs['A'] = array('align' => 'center');
				$columnDescs['B'] = array('align' => 'center');
				$columnDescs['C'] = array('align' => 'center');
				$columnDescs['D'] = array('align' => 'center');
				$columnDescs['E'] = array('align' => 'center');
				$columnDescs['F'] = array('align' => 'center');
				$columnDescs['G'] = array('align' => 'center');
				$columnDescs['H'] = array('align' => 'center');
				$columnDescs['I'] = array('align' => 'center', 'width' => 20);
				$columnDescs['J'] = array('align' => 'center');
				$columnDescs['K'] = array('align' => 'center', 'width' => 22);
				$columnDescs['L'] = array('align' => 'center', 'type' => 'datetime', 'width' => 20);
				$columnDescs['M'] = array('align' => 'center');
				$columnDescs['N'] = array('align' => 'center', 'width' => 22);
				$columnDescs['O'] = array('align' => 'center', 'width' => 12);
				$columnDescs['P'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['Q'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['R'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['S'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['T'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['U'] = array('align' => 'center', 'width' => 'auto');
				$columnDescs['V'] = array('align' => 'center', 'width' => 'auto');
			}

			$excessiveData = false;

			$rows = $this->findRewardsData($currentStore, $day, $month, $year,  $duration, $isHomeOfficeAccess, $excessiveData);

			// TODO: this hack removes confusing info that musat be corrected by a follow up modification to this report so
			// that is supports the new rules The hope is to complete this by 4/7/2022.
			$rows = $this->tempHack($rows);

			$tpl->assign('rows', $rows);
			$numRows = count($rows);
			$tpl->assign('report_count', $numRows);
			$tpl->assign('col_descriptions', $columnDescs );


			//$tpl->assign('excel_callbacks', $callbacks );


			if ($numRows)
			{
				if ($excessiveData)
					$_GET['export'] = 'csv';
				else
					$_GET['export'] = 'xlsx';
			}
			else
			{
				$tpl->setErrorMsg('There are no results for this query.');
			}

			CLog::RecordReport("PLATEPOINTS Awards Report", "Rows:$numRows ~ Store: $store" );
		}

		$formArray = $Form->render();

		if ($showPaymentType == 'STORE_CREDIT' || $showPaymentType == 'GIFT_CARD')  $tpl->assign('supress_delayed_payment_info', true);
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form', $formArray);

		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', TRUE);
		}
	}

	function tempHack(&$rows)
	{
		foreach ($rows as $uid => &$data)
		{
			if (!empty($data['Award Due']) && !in_array($data['Award Due'], array("Nothing currently due.")))
			{
				$data['Award Due'] = "Nothing currently due.";
			}

			$data['PlatePoints to Next Award'] = "";
		}

		return $rows;


	}

	function findRewardsData($store_id, $Day, $Month, $Year, $Interval = '1 DAY', $isHomeOfficeAccess = true, &$excessiveData = false)
	{

		$storeTransitionHasExpired = CStore::hasPlatePointsTransitionPeriodExpired($store_id);
		$user = DAO_CFactory::create("user");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$query = "select u.*, a.address_line1, a.address_line2, a.city, a.state_id, a.postal_code, s.session_start, iq.next_session, st.store_name, ud.user_data_value, max(b.order_id) as focusOrderID from booking b
					join user u on u.id = b.user_id
					join session s on s.id = b.session_id and s.session_start >= '$current_date_sql' AND  s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
					join store st on st.id = s.store_id
					left join address a on a.user_id = u.id and a.is_deleted = 0 and a.location_type = 'BILLING' 
					left join (select b2.user_id, min(s2.session_start) as next_session from booking b2
						join session s2 on s2.id = b2.session_id where s2.session_start > now() and b2.status  = 'ACTIVE' group by b2.user_id) as iq on iq.user_id = b.user_id
					left join user_data ud on ud.user_id = u.id and ud.user_data_field_id = 1 and ud.is_deleted = 0
					where b.status = 'ACTIVE' and s.store_id = $store_id and u.is_deleted = 0 and b.is_deleted = 0
					group by u.id";

		$user->query($query);


		if ($user->N > 3000)
		{
			$excessiveData = true;
		}


		$rows = array();

		while ($user->fetch())
		{

			if ($isHomeOfficeAccess)
			{

				$thisRow = array('store' => $user->store_name, 'Guest ID' => $user->id, 'Last Name' => $user->lastname,  'First Name' => $user->firstname, 'Primary Email' => $user->primary_email,
						'Telephone 1' => $user->telephone_1, 'Telephone 1 Call Time' => $user->telephone_1_call_time, 'Telephone 2' => $user->telephone_2, 'Telephone 2 Call Time' => $user->telephone_2_call_time,
						'Loyalty Status' => "",'PlatePoints' => "",
						'Award Due' => "", 'Next Session' => $user->next_session, 'PlatePoints to Next Award' => "", 'Next Award' => "", "Birthday Month" => "",
						'Address Line 1' => $user->address_line1, 'Address line 2' => $user->address_line2, 'City' => $user->city, 'State' => $user->state_id,
						 'Postal Code' => $user->postal_code, 'Conversion Points' => "", 'Conversion Credit' => "");
			}
			else
			{
				$thisRow = array( 'Guest ID' => $user->id, 'Last Name' => $user->lastname, 'First Name' => $user->firstname, 'Primary Email' => $user->primary_email,
						'Telephone 1' => $user->telephone_1, 'Telephone 1 Call Time' => $user->telephone_1_call_time, 'Telephone 2' => $user->telephone_2, 'Telephone 2 Call Time' => $user->telephone_2_call_time,
									'Loyalty Status' => "",'PlatePoints' => "",
									 'Award Due' => "", 'Next Session' => $user->next_session, 'PlatePoints to Next Award' => "", 'Next Award' => "", "Birthday Month" => "",
									'Address Line 1' => $user->address_line1, 'Address line 2' => $user->address_line2, 'City' => $user->city, 'State' => $user->state_id,
						 'Postal Code' => $user->postal_code, 'Conversion Points' => "", 'Conversion Credit' => "" );
			}

			if ($user->isUserPreferred())
			{
				$userObj = DAO_CFactory::create('user');
				$userObj->id = $user->id;
				$conv_data = CPointsUserHistory::getPreferredUserConversionData($userObj);
				$thisRow['Loyalty Status'] = "Preferred";
				$thisRow['PlatePoints'] = $conv_data['points_award_display_value'] . " ($" . $conv_data['credit_award_display_value']  . ")";
				$thisRow['PlatePoints to Next Award'] = $conv_data['next_level_details']['req_points']  - $conv_data['points_award_display_value'] ;
				$thisRow['Next Award'] = $conv_data['next_level_details']['rewards']['gift_id'];
			}
			else if ($user->dream_reward_status == 1 || $user->dream_reward_status == 3 || $user->dream_reward_status == 5)
			{

				//active
				if ($user->dream_rewards_version < 3)
				{
					//DR
					$thisRow['Loyalty Status'] = "DR " . $user->dream_reward_level;
					if (!$storeTransitionHasExpired)
					{

						$userObj = DAO_CFactory::create('user');
						$userObj->id = $user->id;
						$conv_data = CPointsUserHistory::getDR2ConversionData($userObj);

						$thisRow['PlatePoints'] = $conv_data['points_award_display_value'] . " ($" . $conv_data['credit_award_display_value']  . ")";
						$thisRow['PlatePoints to Next Award'] = $conv_data['next_level_details']['req_points']  - $conv_data['points_award_display_value'] ;
						$thisRow['Next Award'] = $conv_data['next_level_details']['rewards']['gift_id'];
						$thisRow['Conversion Points'] = $conv_data['points_award_display_value'];
						$thisRow['Conversion Credit'] = $conv_data['credit_award_display_value'];
					}

				}
				else
				{
					//PP
					$pp_data = $user->getPlatePointsSummary(false, true);

					if ($pp_data['userIsOnHold'])
					{
						$thisRow['Loyalty Status'] = "ON HOLD (" . $pp_data['current_level']['title'] . ")";
					}
					else
					{
						$thisRow['Loyalty Status'] = $pp_data['current_level']['title'];
					}

					$thisRow['PlatePoints'] = $pp_data['lifetime_points'];

                    $thisRow['PlatePoints to Next Award'] = $pp_data['points_until_next_level'];

                    if ($pp_data['current_level']['level'] == 'enrolled') {
                        // when a member (enrolled) the gifts are driven by order
                        $streakData = CPointsUserHistory::getOrdersSequenceStatus($user->id, $user->focusOrderID);

                        if ($streakData['focusOrderInOriginalStreak']) {
                            $pp_data['habitStreakOrderNumberForThisOrder'] = $streakData['focusOrderStreakOrderNumber'];
                            $pp_data['habitStreakOrderCount'] = $streakData['InitialStreakOrderCount'];
                        }

                        $pp_data['orderBasedGiftData'] = CPointsUserHistory::getOrderBasedGiftData($user->id, $user->focusOrderID, $streakData);

                        $dueStr = "";

                        if (!empty($pp_data['orderBasedGiftData'])) {
                            $dueStr = "Due ";

                            foreach ($pp_data['orderBasedGiftData'] as $thisGift)
                            {
                                if ($thisGift['rewardDue'])
                                {
                                    $dueStr .= $thisGift['gift_id'] . "; ";
                                }
                            }
                        }
                        else
                        {
                            $dueStr = "Nothing currently due.";
                        }

                        $thisRow['Award Due'] = $dueStr;

                    }
					else
                    {
                        list ($needsCurrentReward, $currentRewardReceived, $currentRewardReceivedNotes) = CPointsUserHistory::userDuePhysicalRewardForLevel($pp_data['current_level'], $user->id);

                        if ($needsCurrentReward)
                        {
                            $thisRow['Award Due'] = CPointsUserHistory::getGiftDisplayString($pp_data['current_level']['rewards']['gift_id']);
                        }
                        else
                        {
                            $thisRow['Award Due'] = "Received for this level";
                        }


                        $thisRow['Next Award'] = CPointsUserHistory::getGiftDisplayString($pp_data['next_level']['rewards']['gift_id']);
                    }
				}

			}
			else if ($user->dream_rewards_version < 3 && $user->dream_reward_status == 2)
			{
				// deactivated DR user
				//DR
				$thisRow['Loyalty Status'] = "DR " . $user->dream_reward_level . "(Deact)";
				if (!$storeTransitionHasExpired)
				{
					$userObj = DAO_CFactory::create('user');
					$userObj->id = $user->id;
					$conv_data = CPointsUserHistory::getDR2ConversionData($userObj);
					$thisRow['PlatePoints'] = $conv_data['points_award_display_value'] . " ($" . $conv_data['credit_award_display_value']  . ")";
					$thisRow['PlatePoints to Next Award'] = $conv_data['next_level_details']['req_points']  - $conv_data['points_award_display_value'] ;
					$thisRow['Next Award'] = $conv_data['next_level_details']['rewards']['gift_id'];
					$thisRow['Conversion Points'] = $conv_data['points_award_display_value'];
					$thisRow['Conversion Credit'] = $conv_data['credit_award_display_value'];
				}

			}
			else
			{
				//inactive
				$thisRow['Loyalty Status'] = "No Program";
			}
			if (is_numeric($user->user_data_value) && $user->user_data_value > 0)
			{
				$months = CUserData::monthArray();
				$thisRow['Birthday Month'] = $months[$user->user_data_value];
			}
			else if (!empty($user->user_data_value) && is_string($user->user_data_value))
			{
				$thisRow['Birthday Month'] = $user->user_data_value;
			}
			else
			{
				$thisRow['Birthday Month'] = "";
			}
			if (!$excessiveData)
			{
				$thisRow['Next Session'] = PHPExcel_Shared_Date::stringToExcel($thisRow['Next Session']);
			}
			else
			{
				$thisRow['Next Session'] = CSessionReports::reformatTime($thisRow['Next Session']);
			}
			$thisRow['Guest ID'] = $user->id;


			if (empty($thisRow['Next Session']))
				$thisRow['Next Session'] = "none";

			$rows[$user->id] = $thisRow;


		}



		return ($rows);
	}
}
?>