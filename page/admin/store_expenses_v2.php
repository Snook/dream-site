<?php

/**
 * @author Lynn Hook
 */
 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/CCalendar.inc');
 require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');


 function dateCompare($a, $b)
 {
 	$aTime = strtotime($a['time']);
 	$bTime = strtotime($b['time']);

 	if ($aTime == $bTime)
 	{
 		return 0;
 	}
 	return ($aTime < $bTime) ? -1 : 1;
 }

 function isUnitBased($inType)
 {
 	switch($inType)
 	{
 		case 'SNEAK_PEEK_HELD':
 		case 'SNEAK_PEEK_TOTAL_GUESTS':
 		case 'TOTAL_SIGN_UPS':
 			return true;
 		default:
 			return false;
 	}
 }

 function mapDBnameToHuman($inName)
 {
 	switch($inName)
 	{
 		case 'LABOR':
 			return 'Labor Costs';
 		case 'SYSCO':
 			return 'Food & Packaging Costs';
 		case 'EMPLOYEE_MEALS':
 			return 'Employee Meals';
 		case 'FUNDRAISER_DOLLARS':
 			return 'Fundraising Costs';
 		case 'FREE_MEAL_PROMOS':
 			return 'Free Meal Promos';
 		case 'ESCRIP_PAYMENTS':
 			return 'eScrip Payments';
 		case 'SALES_ADJUSTMENTS':
 			return 'Sales Adjustment';
 		case 'SNEAK_PEEK_HELD':
 			return 'Sneak Peek';
 		case 'SNEAK_PEEK_TOTAL_GUESTS':
 			return 'Sneak Peek';
 		case 'TOTAL_SIGN_UPS':
 			return 'Total Signups';
 		default:
 			return 'unknown';

 	}
 }

 function populateExpensesCalendarHO($Date)
 {
 		$itemList = array();
 		$styleOverride = NULL;

 		$count = 0;

 		if (isset(page_admin_store_expenses_v2::$dayRangeMarkersArray[$Date]))
 		{
 			$styleOverride = page_admin_store_expenses_v2::$dayRangeMarkersArray[$Date];
 		}

 		if (isset(page_admin_store_expenses_v2::$sessionArray[$Date]) && page_admin_store_expenses_v2::$sessionArray[$Date])
 		{
 			usort(page_admin_store_expenses_v2::$sessionArray[$Date], 'dateCompare' );

 			foreach (page_admin_store_expenses_v2::$sessionArray[$Date] as $dayItem)
 			{
 				$image = "";
 				$anchorStart = "";
 				$anchorEnd = "";
 				$editClick = "";

 				if ($dayItem['isOpen'])
 				{
 					$linkClass = "calendar_on_text_on";
 				}
 				else
 				{
 					$linkClass = "calendar_on_text_off";
 				}

 				if (!$dayItem['isCurrent'])
 				{
 					$linkClass = "calendar_on_text_not_current";
 				}

 				$id = 0;
 				$time12Hour = date("g:i a", strtotime($dayItem['time']));

 				$image = "";

 				if (isset(page_admin_store_expenses_v2::$calendarItems[$Date]))
 				{
 					foreach (page_admin_store_expenses_v2::$calendarItems[$Date] as $type => $item )
 					{
 						if (!empty($item['session_id']) && $dayItem['id'] ==$item['session_id'] )
 						{

							if (isUnitBased($type))
 								$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": " .$item['units'] . " - " . $item['notes'] . "\" ";
							else
								$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": $" .$item['amount'] . " - " . $item['notes'] . "\" ";

 						 	if ($type == 'SALES_ADJUSTMENTS')
			 				{
 								$image .=  "<img $dataToolTip name=\"{$dayItem['time']}\"  width=\"12\" height=\"12\" src=\"" . ADMIN_IMAGES_PATH . "/icon/dollar_sign.png\" class=\"img_valign\">";
			 				}
			 				else if ($type == 'FUNDRAISER_DOLLARS')
			 				{
 								$image .=  "<img $dataToolTip name=\"{$dayItem['time']}\"  width=\"12\" height=\"12\" src=\"" . ADMIN_IMAGES_PATH . "/icon/star_gold.png\" class=\"img_valign\">";
			 				}
			 				else
			 				{
 								$image .=  "<img $dataToolTip name=\"{$dayItem['time']}\"  width=\"12\" height=\"12\" src=\"" . ADMIN_IMAGES_PATH . "/icon/star_grey.png\" class=\"img_valign\">";
			 				}

  						}
 					}
 				}

 				$itemList[$count++] = '<a data-session_id="' . $dayItem['id'] . '" href="javascript:onSessionClick(' . $dayItem['id'] . ', \'' .$Date .  '\', \'' . $time12Hour . '\');" class="'	. $linkClass . '">' . $time12Hour . '&nbsp; ' . $image . '</a>';
 			}
 		}

 		return array($itemList,$styleOverride);
 	}

 	function populateExpensesCalendar($Date)
 	{
 		$itemList = array();
 		$styleOverride = NULL;

 		$count = 0;

 		if (isset(page_admin_store_expenses_v2::$dayRangeMarkersArray[$Date]))
 		{
 			$styleOverride = page_admin_store_expenses_v2::$dayRangeMarkersArray[$Date];
 		}

 		if (isset(page_admin_store_expenses_v2::$calendarItems[$Date]))
 		{

 			foreach (page_admin_store_expenses_v2::$calendarItems[$Date] as $type => $item )
 			{

				if (isUnitBased($type))
 					$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": " .$item['units'] . " - " . $item['notes'] . "\" ";
				else
					$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": $" .$item['amount'] . " - " . $item['notes'] . "\" ";


 				if ($type == 'SYSCO')
 				{
 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/turkey.png\" class=\"img_valign\">";
 				}
 				else if ($type == 'LABOR')
 				{
 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/user.png\" class=\"img_valign\">";
 				}
 				else
 				{
 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/star_grey.png\" class=\"img_valign\">";
 				}

 				$itemList[$count++] = $image . '$' . $item['amount'];
 			}
 		}

 		return array($itemList, $styleOverride);
 	}


 	function populateExpensesCalendarHeaderHO($Date)
 	{
 		$images = "";

 		if (isset(page_admin_store_expenses_v2::$calendarItems[$Date]))
 		{

 			foreach (page_admin_store_expenses_v2::$calendarItems[$Date] as $type => $item )
 			{

 				if (empty($item['session_id']))
 				{

					if (isUnitBased($type))
 						$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": " .$item['units'] . " - " . $item['notes'] . "\" ";
					else
						$dataToolTip = "data-tooltip=\"" . mapDBnameToHuman($type) . ": $" .$item['amount'] . " - " . $item['notes'] . "\" ";

	 				if ($type == 'SYSCO')
	 				{
	 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/turkey.png\" class=\"img_valign\">";
	 				}
	 				else if ($type == 'LABOR')
	 				{
	 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/user.png\" class=\"img_valign\">";
	 				}
	 				else
	 				{
	 					$image =  "<img $dataToolTip src=\"" . ADMIN_IMAGES_PATH . "/icon/star_grey.png\" class=\"img_valign\">";
	 				}
	 				$images .= $image;
	 			}
 			}
 		}

 		$images = '<span data-header>' . $images . '</span>';

 		return $images;
 	}



 class page_admin_store_expenses_v2 extends CPageAdminOnly {

	private $currentStore = null;

	public static $sessionArray = array();
	public static $dayRangeMarkersArray = array();
	public static $calendarItems = array();


 	function runFranchiseOwner() {
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	 }
	 function runFranchiseManager() {
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	 }
	 function runOpsLead() {
	 	$this->currentStore = CApp::forceLocationChoice();
	 	$this->runSiteAdmin();
	 }

	 function runHomeOfficeManager() {
		$this->runSiteAdmin();
	}


	 function runSiteAdmin() {
		$store = NULL;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;
		$yearMonthDayArr = null;
		$rowSelected = "";
		$explodedDateArray = NULL;
		$report_submitted = FALSE;


		$store = $_REQUEST['store_id'];
		$month = $_REQUEST['month'];
		$year = $_REQUEST['year'];

		if (empty($store))
		{
			throw new Exception("No store provided for store expenses form");
		}

		if (empty($month) || !is_numeric($month))
		{
			throw new Exception("Invalid month provided for store expenses form");
		}

		if (empty($year) || !is_numeric($year))
		{
			throw new Exception("Invalid month provided for store expenses form");
		}

		$storeDAO = DAO_CFactory::create('store');
		$storeDAO->id = $store;
		$storeDAO->find(true);


		$isHomeOfficeAccess = true;

		if (CUser::getCurrentUser()->isFranchiseAccess())
			$isHomeOfficeAccess = false;

		$tpl->assign('isHomeOfficeAccess', $isHomeOfficeAccess);



		if ($isHomeOfficeAccess)
		{

			$Sessions = DAO_CFactory::create('session');
			$curMonthStartTS = mktime(0, 0, 0, $month, 1, $year);
			list($rangeStart, $rangeEnd) = CCalendar::calculateMonthRange($curMonthStartTS);
			$Sessions->findCalendarRangeForMonth($store, $month, $year);
			$calendar = new CCalendar();

			while ($Sessions->fetch())
			{
				$thisSessionTimeTS = strtotime($Sessions->session_start);

				$dateOnly = Date("n", $thisSessionTimeTS). "/" . Date("j", $thisSessionTimeTS) . "/" . Date("Y", $thisSessionTimeTS);
				$timeOnly = Date("G", $thisSessionTimeTS). ":" . Date("i", $thisSessionTimeTS);
				$isOpen = $Sessions->isOpen($storeDAO);

				// TODO: better not use time as key as multiple sessions at the same time could exist

				// $remaining_slots = $Sessions->available_slots - $Sessions->filled;

				self::$sessionArray[$dateOnly][$Sessions->id] = array(
						'time' => $timeOnly,
						'state' => $Sessions->session_publish_state,
						'id' => $Sessions->id,
						'isQ6' => $Sessions->session_type == CSession::QUICKSIX ? true : false,
						'isSpecialEvent' => $Sessions->session_type == CSession::SPECIAL_EVENT ? true : false,
						'isTODD' => $Sessions->session_type == CSession::TODD ? true : false,
						'dreamTaste' => $Sessions->session_type == CSession::DREAM_TASTE ? true : false,
						'isOpen' => $isOpen,
						'isPrivate' => ($Sessions->session_password ? true : false),
						'isCurrent' => true);

			}


			$calendar_items = $this->FindDates ($store, "01", $month, $year) ;
			self::$calendarItems = $calendar_items;

			$calendarRows = $calendar->generateDayArray($month, $year, 'populateExpensesCalendarHO', false, false, false, $rangeStart, $rangeEnd, 'populateExpensesCalendarHeaderHO');
		}
		else
		{
			$curMonthStartTS = mktime(0, 0, 0, $month, 1, $year);
			list($rangeStart, $rangeEnd) = CCalendar::calculateMonthRange($curMonthStartTS);
			$calendar = new CCalendar();

			$calendar_items = $this->FindDates ($store, "01", $month, $year) ;
			self::$calendarItems = $calendar_items;

			$calendarRows = $calendar->generateDayArray($month, $year, 'populateExpensesCalendar', false, false, false, $rangeStart, $rangeEnd, NULL);
		}

		$monthTS = mktime(0, 0, 0, $month, 1, $year);
		$monthtitle = date("F", $monthTS );




		$tpl->assign('currentYear', $year );
		$tpl->assign('currentMonth', $month );
		$tpl->assign('store', $store );
		$tpl->assign('monthtitle', $monthtitle );
		$tpl->assign('calendarName', "Expense Tracker" );
		$tpl->assign('rows', $calendarRows );
		$tpl->assign('calHeight', 20 );
		$tpl->assign('calWidth', 60 );
		$formArray = $Form->render();
		$tpl->assign('page_title','Dream Report Data Entry');
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('support_row_selection',FALSE);
		$tpl->assign('support_cell_selection', TRUE);

		$currentPrice = CStoreExpenses::getEmployeeMealPrice(mktime(0,0,0, $month, 1, $year));

		$tpl->assign('free_meal_price_per_unit', $currentPrice);

	}


	// find all dates where the entry matches
	function FindDates ($store_id, $Day, $Month, $Year, $Interval = '1 MONTH')
	{
		$store_expenses = DAO_CFactory::create("store_expenses");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y/m/d 00:00:00", $current_date);

		$selectStr = "select se.entry_date, se.expense_type, se.notes, se.total_cost, se.units, session_id
							from store_expenses se
							where se.store_id = $store_id and se.entry_date >= '$current_date_sql'
							and se.entry_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
							and se.is_deleted = 0";
		$store_expenses->query($selectStr);
		$rows = array();

		while ($store_expenses->fetch()) {
			$ts = explode("-", $store_expenses->entry_date);
			$current_date_sql = date("n/j/Y", mktime(0, 0, 0, $ts[1], $ts[2], $ts[0]));
			$thisItem = array('time' => $current_date_sql, 'type' => $store_expenses->expense_type, 'amount' => $store_expenses->total_cost, 'units' => $store_expenses->units, 'notes' => $store_expenses->notes, 'session_id' => $store_expenses->session_id);
			$rows[$current_date_sql][$store_expenses->expense_type] = $thisItem;
		}
		return $rows;
	}
}

?>