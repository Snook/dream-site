<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once ('includes/CPageAdminOnly.inc');
require_once ('includes/CSessionReports.inc');
require_once ('includes/CDreamReport.inc');
require_once ('includes/DAO/Booking.php');
require_once ('includes/DAO/Dashboard_metrics_guests.php');
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');
function sort_sessions($a, $b)
{
	$atime = $a['sessionTS'];
	$btime = $b['sessionTS'];

	if ($atime == $btime)
		return 0;

	return ($atime < $btime) ? -1 : 1;
}

function sort_days($a, $b)
{
	// get session time of any session
	$a_session = current($a);
	$b_session = current($b);

	$atime = $a_session['sessionTS'];
	$btime = $b_session['sessionTS'];

	reset($a);
	reset($b);

	if ($atime == $btime)
		return 0;

	return ($atime < $btime) ? -1 : 1;
}

function sort_weeks($a, $b)
{
	// get any day
	// get session time of any session
	$a_day = current($a);
	$b_day = current($b);

	// get session time of any session
	$a_inf_loop_prevention = 0;
	$b_inf_loop_prevention = 0;
	$a_session = current($a_day);
	while (!is_array($a_session))
	{
		$a_session = next($a_day);
		$a_inf_loop_prevention++;
		if ($a_inf_loop_prevention > 100) break;
	}
	$b_session = current($b_day);
	while (!is_array($b_session))
	{
		$b_session = next($b_day);
		$b_inf_loop_prevention++;
		if ($b_inf_loop_prevention > 100) break;
	}


	$atime = $a_session['sessionTS'];
	$btime = $b_session['sessionTS'];

	reset($a);
	reset($b);

	reset($a_day);
	reset($b_day);


	if ($atime == $btime)
		return 0;

	return ($atime < $btime) ? -1 : 1;
}

class page_admin_reports_goal_management extends CPageAdminOnly
{
	private $currentStore = null;
	private $PandLAccess = true;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	static $typeMap = array("STANDARD" => 'Standard', "SPECIAL_EVENT" => 'MFY', "DREAM_TASTE" => 'Taste', "TODD" => 'Taste', "FUNDRAISER" => 'Fundraiser');

	const LIMITED_P_AND_L_ACCESS_SECTION_ID = 8;

	function retreiveTasteHostesses($menuStart, $menuEnd)
	{


		$userObj = DAO_CFactory::create('user');
		$userObj->query("select s.id as sess_id, u.id, CONCAT(u.firstname, ' ', u.lastname) as name, s.session_start, s.timestamp_created from session s
			join session_properties tsp on tsp.session_id = s.id and tsp.is_deleted = 0
			join user u on u.id = tsp.session_host and u.is_deleted = 0
			where s.session_start > '$menuStart' and s.timestamp_created > '$menuStart' and s.timestamp_created < '$menuEnd' and s.store_id = {$this->currentStore} ");

		$retval = array();

		while($userObj->fetch())
		{
			$retval[$userObj->sess_id] = array('name' => $userObj->name, 'time' => $userObj->session_start);
		}

		return $retval;

	}

	function getTasteEventCount($menuStart, $menuEnd)
	{

		$sessionObj = DAO_CFactory::create('session');

		$sessionObj->query("select count(id) as num_tastes from session where session_class = 'TODD' and store_id = {$this->currentStore} and session_start > '$menuStart' and session_start < '$menuEnd'
								and is_deleted = 0 and session_publish_state <> 'SAVED'");

		if ($sessionObj->fetch())
			return $sessionObj->num_tastes;

		return 0;

	}


	function createEmptySessionArray($sessionFinderObj, $isPast, $sessionTS)
	{

		$retVal = array('session_id' => $sessionFinderObj->id,
		'gross_revenue' => 0.00,
		'ft_total' => 0.00,
		'new_count' => 0,
		'new_sign_ups' => 0,
		'new_sign_ups_%' => 0,
		'reac_count' => 0,
		'reac_sign_ups' => 0,
		'reac_sign_ups_%' => 0,
		'existing_count' => 0,
		'existing_sign_ups' => 0,
		'existing_sign_ups_%' => 0,
		'total_count' => 0,
		'total_sign_ups' => 0,
		'total_sign_ups_%' => 0,
		'session_type' => self::$typeMap[$sessionFinderObj->session_type],
		'session_lead' => '',
		'isPast' => $isPast,
		'out_of_month' => false,
		'sessionTS' => $sessionTS);


		if (!empty($sessionFinderObj->session_lead))
		{
			// this could occur is a session leads account is changed to customer or was deleted

			$UserObj = DAO_CFactory::create('user');
			$UserObj->query("select firstname, lastname from user where id = {$sessionFinderObj->session_lead}");
			if ($UserObj->N)
			{
				$UserObj->fetch();
				$retVal['session_lead'] = $sessionFinderObj->session_lead;
			}
			else
			{
				$retVal['session_lead'] = "0";
			}

		}

		return $retVal;


	}

	function joinEmptySessions(&$masterArray, $nowAtStore, $menu_id, &$hasSessionToday)
	{

		$todaysMonth = date("n");
		$todaysDay = date("j");

		$sessionFinderObj = DAO_CFactory::create('session');
		$sessionFinderObj->query("select s.id, s.session_start, s.session_lead, session_type
							from session s where s.store_id = {$this->currentStore} and s.menu_id = $menu_id and s.is_deleted = 0 and s.session_publish_state <> 'SAVED'");

		while($sessionFinderObj->fetch())
		{

			$sessionTimeTS = strtotime($sessionFinderObj->session_start);

			$isPast = ($sessionTimeTS < $nowAtStore);


			$weeknum = date("W", $sessionTimeTS);

			if (!isset($masterArray[$weeknum]))
				$masterArray[$weeknum] = array();

			$dayNum = date("j", $sessionTimeTS);

			if (!isset($masterArray[$weeknum][$dayNum]))
			{
				$masterArray[$weeknum][$dayNum] = array();

				$sesssionsMonth = date("n", $sessionTimeTS);
				if ($sesssionsMonth == $todaysMonth && $dayNum == $todaysDay)
				{
					$hasSessionToday = true;
				}
			}

			$sessionTime = date("l g:i A", $sessionTimeTS);

			if (!isset($masterArray[$weeknum][$dayNum][$sessionTime]))
			{
				$masterArray[$weeknum][$dayNum][$sessionTime] = $this->createEmptySessionArray($sessionFinderObj, $isPast, $sessionTimeTS);
				uasort($masterArray[$weeknum][$dayNum], 'sort_sessions');
				uasort($masterArray[$weeknum], 'sort_days');
				uasort($masterArray, 'sort_weeks');
			}

		}


	}


	function retrieveMetricsArray($month, $year, &$sessionLeads, $tpl, &$hasSessionToday, &$menu_start, &$menu_end, &$menu_id)
	{

		$retval = array();

		$todaysMonth = date("n");
		$todaysDay = date("j");
		$todaysWeek = date("W");

		$hasPreviousMonthSessions = 0;
		$hasFutureMonthSessions = 0;


		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$this->currentStore}");
		$storeObj->fetch();

		$nowAtStore = CTimezones::getAdjustedServerTime($storeObj);



			$todaysTimeStamp = mktime(0,0,0, $todaysMonth, $todaysDay, date("Y") );

		// the old method retreives data for full weeks and disables those sessions outside of the calendar month
		/*

			$range = CCalendar::calculateMonthRange(strtotime($sessionFinderObj->start_day));

		$dateClause = "and od.session_time >= '{$range[0]}' and od.session_time < '{$range[1]}' "; */

		// the new method uses menu month instead and since menu month aligns nicely with week boundaries we only need to retrieve sessions by menu id
		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->findForMonthAndYear($month, $year);
		$MenuObj->fetch();
		$menu_id = $MenuObj->id;
		$menu_start = date('Y-m-d H:i:s', strtotime($MenuObj->global_menu_start_date));
		$menu_end = date('Y-m-d 23:59:59', strtotime($MenuObj->global_menu_end_date));

		$dateClause = "and od.session_time >= '$menu_start' and od.session_time < '$menu_end' ";

		$sessionObj = DAO_CFactory::create('session');

		$sessionObj->query("select
		s.id
		, s.session_start
		, s.session_type
		, s.session_lead
		, count(iq.order_id) as total_guests
		, count(if(iq.user_state = 'NEW', 1, null)) as new_guests
		, count(if(iq.user_state = 'REACQUIRED', 1, null)) as reac_guests
		, count(if(iq.user_state = 'EXISTING', 1, null)) as existing_guests
		, count(if(iq.user_state = 'NEW' and iq.in_store is not null, 1, null)) as new_guest_signups
		, count(if(iq.user_state = 'REACQUIRED' and iq.in_store is not null, 1, null)) as reac_guest_signups
		, count(if(iq.user_state = 'EXISTING' and iq.in_store is not null, 1, null)) as existing_guest_signups
		, count(if(iq.in_store is not null, 1, null)) as total_guest_signups
		, sum(iq.addon_total) as ft_total
		, sum(iq.grand_total) - sum(iq.subtotal_all_taxes) as gross_revenue
		 from (
		select od.user_id, od.order_id, od.session_time, od.user_state, od.order_type, od2.id, od.addon_total, od2.id as in_store, o.grand_total, o.subtotal_all_taxes  from orders_digest od
		left join orders o on o.id = od.order_id
		left join orders_digest od2 on od2.in_store_trigger_order is not null and od2.in_store_trigger_order = od.order_id and od2.is_deleted = 0
		where od.store_id = {$this->currentStore} $dateClause and od.is_deleted = 0
		GROUP BY od.id) as iq
		join session s on s.session_start = iq.session_time and s.store_id = {$this->currentStore}  and s.is_deleted = 0
			group by iq.session_time");


		while($sessionObj->fetch())
		{

			$sessionTimeTS = strtotime($sessionObj->session_start);
			$weeknum = date("W", $sessionTimeTS);

			if (!isset($retval[$weeknum]))
				$retval[$weeknum] = array();

			$dayNum = date("j", $sessionTimeTS);

			if (!isset($retval[$weeknum][$dayNum]))
				$retval[$weeknum][$dayNum] = array();

			$sessionTime = date("l g:i A", $sessionTimeTS);

			if (!isset($retval[$weeknum][$dayNum][$sessionTime]))
				$retval[$weeknum][$dayNum][$sessionTime] = array();

			$sesssionsMonth = date("n", $sessionTimeTS);

			if ($sesssionsMonth == $todaysMonth && $dayNum == $todaysDay)
			{
				$hasSessionToday = true;
			}

			$thisDaysTimeStamp = mktime(0,0,0,$sesssionsMonth, $dayNum, date("Y", $sessionTimeTS));

			$isFutureDay = false;
			if ($thisDaysTimeStamp > $todaysTimeStamp) $isFutureDay = true;


			$retval[$weeknum][$dayNum][$sessionTime]['session_id'] = $sessionObj->id;


			$retval[$weeknum][$dayNum][$sessionTime]['gross_revenue'] = $sessionObj->gross_revenue;
			$retval[$weeknum][$dayNum][$sessionTime]['ft_total'] = $sessionObj->ft_total;

			$retval[$weeknum][$dayNum][$sessionTime]['new_count'] = $sessionObj->new_guests;
			$retval[$weeknum][$dayNum][$sessionTime]['new_sign_ups'] = $sessionObj->new_guest_signups;
			if ($isFutureDay)
				$retval[$weeknum][$dayNum][$sessionTime]['new_sign_ups_%'] = "0.00";
			else
				$retval[$weeknum][$dayNum][$sessionTime]['new_sign_ups_%'] = CTemplate::divide_and_format($sessionObj->new_guest_signups * 100,  $sessionObj->new_guests, 2);



			$retval[$weeknum][$dayNum][$sessionTime]['reac_count'] = $sessionObj->reac_guests;
			$retval[$weeknum][$dayNum][$sessionTime]['reac_sign_ups'] = $sessionObj->reac_guest_signups;
			if ($isFutureDay)
				$retval[$weeknum][$dayNum][$sessionTime]['reac_sign_ups_%'] = "0.00";
			else
				$retval[$weeknum][$dayNum][$sessionTime]['reac_sign_ups_%'] =  CTemplate::divide_and_format($sessionObj->reac_guest_signups * 100, $sessionObj->reac_guests, 2);


			$retval[$weeknum][$dayNum][$sessionTime]['existing_count'] = $sessionObj->existing_guests;
			$retval[$weeknum][$dayNum][$sessionTime]['existing_sign_ups'] = $sessionObj->existing_guest_signups;
			if ($isFutureDay)
				$retval[$weeknum][$dayNum][$sessionTime]['existing_sign_ups_%'] = "0.00";
			else
				$retval[$weeknum][$dayNum][$sessionTime]['existing_sign_ups_%'] = CTemplate::divide_and_format($sessionObj->existing_guest_signups * 100,  $sessionObj->existing_guests, 2);

			$retval[$weeknum][$dayNum][$sessionTime]['total_count'] = $sessionObj->total_guests;
			$retval[$weeknum][$dayNum][$sessionTime]['total_sign_ups'] = $sessionObj->total_guest_signups;
			if ($isFutureDay)
				$retval[$weeknum][$dayNum][$sessionTime]['total_sign_ups_%'] = "0.00";
			else
				$retval[$weeknum][$dayNum][$sessionTime]['total_sign_ups_%'] = CTemplate::divide_and_format($sessionObj->total_guest_signups * 100, $sessionObj->total_guests, 2);

			$retval[$weeknum][$dayNum][$sessionTime]['session_type'] = self::$typeMap[$sessionObj->session_type];
			$retval[$weeknum][$dayNum][$sessionTime]['session_lead'] = $sessionObj->session_lead;

			$retval[$weeknum][$dayNum][$sessionTime]['isPast'] = ($sessionTimeTS < $nowAtStore);
			$retval[$weeknum][$dayNum][$sessionTime]['sessionTS'] = $sessionTimeTS;


			if (!empty($sessionObj->session_lead) && !array_key_exists($sessionObj->session_lead, $sessionLeads))
			{
				// this could occur is a session leads account is changed to customer or was deleted

				$UserObj = DAO_CFactory::create('user');
				$UserObj->query("select firstname, lastname from user where id = {$sessionObj->session_lead}");
				if ($UserObj->N)
				{
					$sessionLeads[$sessionObj->session_lead] = $UserObj->firstname . " (inactive)";
				}
				else
				{
					$sessionLeads[$sessionObj->session_lead] = "Unknown";
				}

			}

			$retval[$weeknum][$dayNum][$sessionTime]['out_of_month'] = false;
		}

		$this->joinEmptySessions($retval, $nowAtStore, $MenuObj->id, $hasSessionToday);


		if ($hasSessionToday && isset($retval[$todaysWeek][$todaysDay]))
		{
			$retval[$todaysWeek][$todaysDay]['isToday'] = true;
		}


		$tpl->assign('hasPreviousMonthSessions', 'false');
		$tpl->assign('hasFutureMonthSessions', 'false');


		$trimWeeks = array();

		foreach($retval as $weekNumber => $thisWeek )
		{
			$hasAtLeastOneInMonthSession = false;

			foreach($thisWeek as $date => $thisDay)
			{
				foreach($thisDay as $sessionTime => $thisSession)
				{
					if(!$thisSession['out_of_month'])
					{
						$hasAtLeastOneInMonthSession = true;
						break;
					}
				}

				if ($hasAtLeastOneInMonthSession)
					break;
			}

			if (!$hasAtLeastOneInMonthSession)
				$trimWeeks[] = $weekNumber;
		}


		foreach($trimWeeks as $weekNumber)
		{
			unset($retval[$weekNumber]);
		}

		return $retval;

	}


	function getFoodCosts($menuStart, $menuEnd)
	{

		$retVal = array();
		$dataObj = DAO_CFactory::create('store_expenses');

		$year = date("Y", strtotime($menuStart));

		$incrementWeekNum = 0;
        if ($year == 2018 || $year == 2019 || $year == 2020)
		{
			$incrementWeekNum = 1;
		}

		$dataObj->query("select WEEK(store_expenses.entry_date) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
							From store_expenses Where store_expenses.entry_date >= '$menuStart' and store_expenses.entry_date < '$menuEnd' and store_id = {$this->currentStore} and store_expenses.is_deleted = 0 and
							store_expenses.expense_type in ('SYSCO', 'OTHER_FOOD') order by entry_date, id DESC");


		while($dataObj->fetch())
		{
			if (isset($retVal[$dataObj->weekNum + $incrementWeekNum]))
	        {
	        	$retVal[$dataObj->weekNum + $incrementWeekNum] += $dataObj->total_cost;
	        }
	        else
	        {
	        	$retVal[$dataObj->weekNum + $incrementWeekNum] = $dataObj->total_cost;
	        }
	    }

		return $retVal;

	}

	function getLaborCosts($menuStart, $menuEnd)
	{

		$retVal = array();
		$dataObj = DAO_CFactory::create('store_expenses');

		$dataObj->query("select WEEK(store_expenses.entry_date) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
							From store_expenses Where store_expenses.entry_date >= '$menuStart' and store_expenses.entry_date < '$menuEnd' and store_id = {$this->currentStore} and store_expenses.is_deleted = 0 and
							store_expenses.expense_type = 'LABOR' order by entry_date, id DESC");

		$year = date("Y", strtotime($menuStart));

		$incrementWeekNum = 0;
        if ($year == 2018 || $year == 2019 || $year == 2020)
		{
			$incrementWeekNum = 1;
		}

		while($dataObj->fetch())
		{
			if (isset($retVal[$dataObj->weekNum + $incrementWeekNum]))
	        {
	        	$retVal[$dataObj->weekNum + $incrementWeekNum] += $dataObj->total_cost;
	        }
	        else
	        {
	        	$retVal[$dataObj->weekNum + $incrementWeekNum] = $dataObj->total_cost;
	        }
	    }

		return $retVal;

	}


	function getGoalDefaultsFromLastYear($month, $year)
	{

		$retval = array();


		$tempTime = mktime(0,0,0,$month - 1 , 1, $year);
		$lastMonth = date("n", $tempTime);
		$lastMonthYear = date("Y", $tempTime);

		$year--;


		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->findForMonthAndYear($month, $year);
		$MenuObj->fetch();
		$menu_start = date('Y-m-d H:i:s', strtotime($MenuObj->global_menu_start_date));
		$menu_end = date('Y-m-d 23:59:59', strtotime($MenuObj->global_menu_end_date));

		$ordersObj = DAO_CFactory::create('orders_digest');
		$ordersObj->query("select sum(o.grand_total - o.subtotal_all_taxes)* 1.1 as gross_revenue, sum(od.addon_total) * 1.1 as ft_revenue from orders_digest od
							join orders o on o.id = od.order_id
							where od.store_id = {$this->currentStore} and  od.session_time >= '$menu_start' and od.session_time < '$menu_end' and od.is_deleted = 0
							GROUP BY od.store_id");

		if ($ordersObj->fetch())
		{
			if (empty($ordersObj->gross_revenue))
				return false;

			$retval['gross_revenue_goal'] = CTemplate::moneyFormat($ordersObj->gross_revenue);
			$retval['finishing_touch_goal'] = CTemplate::moneyFormat($ordersObj->ft_revenue);
			$retval['taste_sessions_goal'] = 4;

			$MenuObj2 = DAO_CFactory::create('menu');
			$MenuObj2->findForMonthAndYear($lastMonth, $lastMonthYear);
			$MenuObj2->fetch();
			$menu_start2 = date('Y-m-d H:i:s', strtotime($MenuObj2->global_menu_start_date));
			$menu_end2 = date('Y-m-d 23:59:59', strtotime($MenuObj2->global_menu_end_date));


			$ordersObj2 = DAO_CFactory::create('orders_digest');
			$ordersObj2->query("select AVG(if (od.order_type <> 'TASTE' and od.order_type <> 'FUNDRAISER', (o.grand_total - o.subtotal_all_taxes), null)) as avg_ticket from orders_digest od
								join orders o on o.id = od.order_id
								where od.store_id = {$this->currentStore} and od.session_time >= '$menu_start2' and od.session_time < '$menu_end2'  and od.is_deleted = 0
								GROUP BY od.store_id");

			if ($ordersObj2->fetch())
			{

				$retval['avg_ticket_goal'] = CTemplate::moneyFormat($ordersObj2->avg_ticket);

				return $retval;
			}

		}


		return false;

	}


	function getGoalDefaultsFromLastMonth($month, $year)
	{


		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->findForMonthAndYear($month, $year);
		$MenuObj->fetch();
		$menu_start = date('Y-m-d H:i:s', strtotime($MenuObj->global_menu_start_date));
		$menu_end = date('Y-m-d 23:59:59', strtotime($MenuObj->global_menu_end_date));

		$retval = array();

		$ordersObj = DAO_CFactory::create('orders_digest');
		$ordersObj->query("select sum(o.grand_total - o.subtotal_all_taxes)* 1.1 as gross_revenue, sum(od.addon_total) * 1.1 as ft_revenue,
									AVG(if (od.order_type <> 'TASTE', (o.grand_total - o.subtotal_all_taxes), null)) as avg_ticket from orders_digest od
										join orders o on o.id = od.order_id
										where od.store_id = {$this->currentStore} and od.session_time >= '$menu_start' and od.session_time < '$menu_end'  and od.is_deleted = 0
												GROUP BY od.store_id");

		if ($ordersObj->fetch())
		{
			if (empty($ordersObj->gross_revenue))
				return false;

			$retval['gross_revenue_goal'] = CTemplate::moneyFormat($ordersObj->gross_revenue);
			$retval['finishing_touch_goal'] = CTemplate::moneyFormat($ordersObj->ft_revenue);
			$retval['avg_ticket_goal'] = CTemplate::moneyFormat($ordersObj->avg_ticket);
			$retval['taste_sessions_goal'] = 4;


			return $retval;
		}

		return false;
	}


	function flagNearestDayToToday(&$weeks)
	{
		$todaysWeek = date("W");
		$todaysDay = date("j");

		foreach($weeks as $weekNumber => &$thisWeek )
		{
			if ($weekNumber == $todaysWeek)
			{
				foreach($thisWeek as $date => &$thisDay)
				{
					if ($date > $todaysDay)
					{
						$thisDay['first_future_session'] = true;
						return;
					}

				}
			}

		}
	}



	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}


	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->PandLAccess = false;

		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
	    $hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

	    if (!$hasPandLAccess)
	    {
	    	$this->PandLAccess = false;
	    }

	    $this->currentStore = CApp::forceLocationChoice();
	    $this->runSiteAdmin();
	}

	function runFranchiseManager()
	{

	    $hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

	    if (!$hasPandLAccess)
	    {
	        $this->PandLAccess = false;
	    }

		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

 	function runFranchiseOwner()
 	{
	 	$this->currentStore = CApp::forceLocationChoice();


		$this->runSiteAdmin();
	}

 	function runSiteAdmin()
 	{

 		CApp::bounce("/backoffice/reports_goal_management_v2");

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


 		// Special privileges handling
 		if ($storeObj->is_corporate_owned && (CUser::getCurrentUser()->user_type == CUser::FRANCHISE_MANAGER || CUser::getCurrentUser()->user_type == CUser::OPS_LEAD))
 		{
             $this->PandLAccess = true;
 		}

 		$tpl->assign('PandLAccess', $this->PandLAccess);

 		$Form->AddElement(array (CForm::type => CForm::Submit,
 				CForm::name => 'report_submit',
 				CForm::css_class => 'btn btn-primary btn-sm',
 				CForm::value => 'Run Report'));


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

		$goalObj = DAO_CFactory::create('store_monthly_goals');
		$goalObj->store_id = $this->currentStore;
		$goalObj->date = $date;

		if ($goalObj->find(true))
		{
			$Form->DefaultValues['gross_revenue_goal'] = $goalObj->gross_revenue_goal;
			$Form->DefaultValues['avg_ticket_goal'] = $goalObj->average_ticket_goal;
			$Form->DefaultValues['finishing_touch_goal'] = $goalObj->finishing_touch_revenue_goal;
			$Form->DefaultValues['taste_sessions_goal'] = $goalObj->taste_sessions_goal;
			$Form->DefaultValues['regular_guest_count_goal'] = $goalObj->regular_guest_count_goal;
			$Form->DefaultValues['taste_guest_count_goal'] = $goalObj->taste_guest_count_goal;
			$Form->DefaultValues['intro_guest_count_goal'] = $goalObj->intro_guest_count_goal;


		    CLog::Record("Found : "  . $goalObj->average_ticket_goal . " | " . $goalObj->finishing_touch_revenue_goal);

			if (empty($goalObj->average_ticket_goal) || empty($goalObj->finishing_touch_revenue_goal)
			    || $goalObj->average_ticket_goal == "0.00" || $goalObj->finishing_touch_revenue_goal == "0.00")
			{
			    $fb_defaults = $this->getGoalDefaultsFromLastYear($month, $year);
				CLog::Record("Need added defaults: " . print_r($fb_defaults, true));

			    if (!$fb_defaults)
			    {
			        $fb_defaults = $this->getGoalDefaultsFromLastMonth($month, $year);
					CLog::Record("Need added defaults from last month: " . print_r(fb_defaults, true));

			    }

			    if (empty($goalObj->average_ticket_goal) || $goalObj->average_ticket_goal == "0.00")
			        $Form->DefaultValues['avg_ticket_goal'] = $fb_defaults['avg_ticket_goal'];
			    if (empty($goalObj->finishing_touch_revenue_goal) || $goalObj->finishing_touch_revenue_goal == "0.00")
			        $Form->DefaultValues['finishing_touch_goal'] = $fb_defaults['finishing_touch_goal'];

			}

		}
		else
		{
			$defaults = $this->getGoalDefaultsFromLastYear($month, $year);

			$Form->DefaultValues['regular_guest_count_goal'] = 0;
			$Form->DefaultValues['taste_guest_count_goal'] = 0;
			$Form->DefaultValues['intro_guest_count_goal'] = 0;


			if (!$defaults)
			{
				$defaults = $this->getGoalDefaultsFromLastMonth($month, $year);

				if (!$defaults)
				{
					$Form->DefaultValues['gross_revenue_goal'] = 40000;
					$Form->DefaultValues['avg_ticket_goal'] = 200;
					$Form->DefaultValues['finishing_touch_goal'] = 2000;
					$Form->DefaultValues['taste_sessions_goal'] = 4;
				}
				else
				{
					$Form->DefaultValues['gross_revenue_goal'] = $defaults['gross_revenue_goal'];
					$Form->DefaultValues['avg_ticket_goal'] = $defaults['avg_ticket_goal'];
					$Form->DefaultValues['finishing_touch_goal'] = $defaults['finishing_touch_goal'];
					$Form->DefaultValues['taste_sessions_goal'] = $defaults['taste_sessions_goal'];
				}
			}
			else
			{
				$Form->DefaultValues['gross_revenue_goal'] = $defaults['gross_revenue_goal'];
				$Form->DefaultValues['avg_ticket_goal'] = $defaults['avg_ticket_goal'];
				$Form->DefaultValues['finishing_touch_goal'] = $defaults['finishing_touch_goal'];
				$Form->DefaultValues['taste_sessions_goal'] = $defaults['taste_sessions_goal'];
			}
		}

 		// Monthly Goals

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 		        CForm::value => "$0.00",
 				CForm::name => "gross_revenue_goal"));

 		$Form->AddElement(array(CForm::type=> CForm::Text,
 				CForm::name => "avg_ticket_goal",
 				CForm::required => true,
 				CForm::onChange => 'calculatePage',
 				CForm::css_class => 'gt_input',
 				CForm::length => 10));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "guests_goal",
 				CForm::value => ""));

 		$Form->AddElement(array(CForm::type=> CForm::Text,
 				CForm::name => "finishing_touch_goal",
 				CForm::required => true,
 				CForm::onChange => 'calculatePage',
 				CForm::css_class => 'gt_input',
 				CForm::length => 10));


 		$Form->AddElement(array(CForm::type=> CForm::Text,
 				CForm::name => "taste_sessions_goal",
 				CForm::required => true,
 				CForm::onChange => 'calculatePage',
 				CForm::css_class => 'gt_input_count',
 				CForm::maxlength => 2));

 		$Form->AddElement(array(CForm::type=> CForm::Text,
 		    CForm::name => "regular_guest_count_goal",
 		    CForm::required => true,
 		    CForm::onChange => 'calculatePage',
 		    CForm::css_class => 'gt_input_count',
 		    CForm::maxlength => 3));

 		$Form->AddElement(array(CForm::type=> CForm::Text,
 		    CForm::name => "taste_guest_count_goal",
 		    CForm::required => true,
 		    CForm::onChange => 'calculatePage',
 		    CForm::css_class => 'gt_input_count',
 		    CForm::maxlength => 3));

 		$Form->AddElement(array(CForm::type=> CForm::Text,
 		    CForm::name => "intro_guest_count_goal",
 		    CForm::required => true,
 		    CForm::onChange => 'calculatePage',
 		    CForm::css_class => 'gt_input_count',
 		    CForm::maxlength => 3));



 		// Session Goals

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "sg_gross_revenue_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "sg_avg_ticket_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "sg_guests_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "sg_finishing_touch_goal",
 				CForm::value => 0));

 		// Average Actual MTD

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "aam_gross_revenue_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "aam_avg_ticket_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "aam_guests_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "aam_finishing_touch_goal",
 				CForm::value => 0));

 		// Revised Goal for Remaining Sessions:

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "rgrs_gross_revenue_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "rgrs_avg_ticket_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "rgrs_guests_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "rgrs_finishing_touch_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "mat_gross_revenue_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "mat_avg_ticket_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "mat_guests_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "mat_finishing_touch_goal",
 				CForm::value => 0));


 		// Monthly Actuals:

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "ma_gross_revenue_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "ma_avg_ticket_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "ma_guests_goal",
 				CForm::value => 0));

 		$Form->AddElement(array(CForm::type=> CForm::Label,
 				CForm::name => "ma_finishing_touch_goal",
 				CForm::value => 0));



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

 		$sessionLeads = CSession::retreiveSessionLeadArray($this->currentStore);

 		$hasSessionToday = false;

 		$menuStart = false;
 		$menuEnd = false;
		$menu_id = false;

 		$rows = $this->retrieveMetricsArray($month, $year, $sessionLeads, $tpl, $hasSessionToday, $menuStart, $menuEnd, $menu_id);


 		$membershipFeeRevenue = CDreamReport::getMembershipFeeRevenueByMenuID($this->currentStore, $menu_id);
		$tpl->assign('membership_fee_revenue', $membershipFeeRevenue);


		if ($isCurrentMonth && !$hasSessionToday)
 		{
			$this->flagNearestDayToToday($rows);
 		}


 		$tpl->assign('weeks', $rows);


 		$foodCostArray = $this->getFoodCosts($menuStart, $menuEnd);
 		$tpl->assign('food_costs', $foodCostArray);

 		$laborCostArray = $this->getLaborCosts($menuStart, $menuEnd);
 		$tpl->assign('labor_costs', $laborCostArray);

 		$tpl->assign('taste_event_count', $this->getTasteEventCount($menuStart, $menuEnd));

 		$tpl->assign('leads', $sessionLeads);

 		$tpl->assign('form_session_list', $formArray);

 		$this->setLivesChangedMetric($tpl, $this->currentStore, $month, $year);

 		$tpl->assign('hostesses', $this->retreiveTasteHostesses($menuStart, $menuEnd));
 		CLog::RecordReport("Session Summary and Goal Tracking Report", "Store: {$this->currentStore}" );








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
}
?>