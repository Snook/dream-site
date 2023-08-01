<?php
/*
 * Created on Sep 1, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/CCalendar.inc';
require_once 'includes/DAO/BusinessObject/CSession.php';

class page_admin_session_mgr extends CPageAdminOnly
{

	public static $sessionArray = array();
	public static $dayRangeMarkersArray = array();

	private $currentStore = null;

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

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
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
		//if no store is chosen, bounce to the choose store page
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		//------------------------------------------------set up store and menu form

		$storeMenuForm = new CForm();
		$storeMenuForm->Repost = true;

		if ($this->currentStore)
		{
			//fadmins
			$currentStore = $this->currentStore;
		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$storeMenuForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'],TYPE_INT) : null;

			$storeMenuForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$currentStore = $storeMenuForm->value('store');
		}

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
		{
			CBrowserSession::instance()->setValue('default_store_id', $currentStore);
		}

		$currentMenu = CBrowserSession::instance()->getValue('sm_current_menu');

		CBrowserSession::instance()->setValue('sm_current_page', 'main.php?page=admin_session_mgr');

		$todaysMonth = date("n");
		$todaysYear = date("Y");
		$todaysDay = date("j");
		$currentMonthDate = date("Y-m-01");

		// ------------------------------create the menu array for the dropdown
		//TODO: how far back do we go
		$startMonth = $todaysMonth - 15;
		$startYear = $todaysYear;
		if ($startMonth <= 0)
		{
			$startMonth += 12;
			$startYear--;
		}

		// Get menu info
		$Menu = DAO_CFactory::create('menu');

		$Menu->selectAdd();
		$Menu->selectAdd("menu.*");

		$start_date = mktime(0, 0, 0, $startMonth, 1, $startYear);
		$start_date_sql = date("Y-m-d", $start_date);

		$Menu->whereAdd("menu.menu_start >= '$start_date_sql'");
		$Menu->orderBy('menu.menu_start DESC');
		$Menu->find();

		$menu_array = array();
		$menu_months = array();

		$defaultMonth = ""; // default month will be set here if session variable is not set or the variable is invalid
		$lastMonth = "";
		$foundCurrentMonth = "";
		while ($Menu->fetch())
		{
			$menu_array[$Menu->id] = CTemplate::dateTimeFormat($Menu->menu_start, "%B %Y");
			$menu_months[$Menu->id] = $Menu->menu_start;

			if ($currentMenu == $Menu->id)
			{
				$foundCurrentMonth = $currentMenu;
			}

			if (strtotime($Menu->menu_start) == strtotime($currentMonthDate))
			{
				$defaultMonth = $Menu->id;
			}

			$lastMonth = $Menu->id;
		}

		if ($foundCurrentMonth != "")
		{
			$defaultMonth = $foundCurrentMonth;
		}

		if ($defaultMonth == "")
		{
			$defaultMonth = $lastMonth;
		}

		if ($defaultMonth == "")
		{
			throw new Exception("Unable to load a valid menu");
		}

		if (isset($_POST["menus"]))
		{
			$storeMenuForm->DefaultValues['menus'] = CGPC::do_clean($_POST["menus"],TYPE_INT);
		}
		else
		{
			$storeMenuForm->DefaultValues['menus'] = $defaultMonth;
		}

		$storeMenuForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::allowAllOption => true,
			CForm::options => $menu_array,
			CForm::name => 'menus'
		));

		// the $currentMenu from the cookie will match the default month unless someone selected a menu with the popup
		if ($currentMenu != $storeMenuForm->value('menus'))
		{
			CBrowserSession::instance()->setValue('sm_current_menu', $storeMenuForm->value('menus'));
			$currentMenu = $storeMenuForm->value('menus');
		}

		// TAG
		$currentMenuTS = strtotime($menu_months[$currentMenu]);
		$currentMonthSuffix = '_' . strtolower(date('M', $currentMenuTS));
		$currentMonth = date("n", $currentMenuTS);
		$currentYear = date("Y", $currentMenuTS);

		// DavidB: 11/14/2006
		// fetch the current month's global end date *if* it exists
		$tsCurrentMenuGlobalEndDate = CMenu::getGlobalMenuEndDate($menu_months[$currentMenu]);

		// make the start date for the previous menu/month
		$prevMonthArray = explode("-", $menu_months[$currentMenu]);
		$prevMonthArray[1]--;
		if ($prevMonthArray[1] < 1)
		{
			$prevMonthArray[1] = 12;
			$prevMonthArray[0]--;
		}
		$prevMonthStart = implode("-", $prevMonthArray);
		// fetch the previous month's global end date *if* it exists
		$tsPreviousMenuGlobalEndDate = CMenu::getGlobalMenuEndDate($prevMonthStart);

		$storeMenuFormArray = $storeMenuForm->render(true);

		$Sessions = DAO_CFactory::create('session');

		$curMonthStartTS = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
		$cueMonthMidPointTS = mktime(0, 0, 0, $currentMonth, 15, $currentYear);

		list($rangeStart, $rangeEnd) = CCalendar::calculateExpandedMonthRange($curMonthStartTS);

		$Sessions->findCalendarRangeForSessionMgr($currentStore, $rangeStart, $rangeEnd);

		/*
		$query1 = 'SELECT session.id, session.session_publish_state, session.session_start, session.session_close_scheduling, ' .
					' session.session_type, session.available_slots, session.session_password, count(booking.id) as filled FROM session ' .
					' LEFT JOIN booking ON booking.session_id = session.id  and booking.status = \'ACTIVE\' '.
					' WHERE ' . $currentMenu . ' = menu_id and store_id = '. $currentStore . ' AND session.is_deleted = 0 group by session.id';

		// First get the session data
		$Sessions->query($query1);
		*/

		$Store = DAO_CFactory::create('store');
		$Store->id = $currentStore;
		$Store->find(true);

		$isCurrent = false;
		$hasCurrentMenuSessions = false;
		$prevMenuEndSessionTS = 0;
		$nextMenuStartSessionTS = strtotime('2030-01-01 00:00:00');
		$lastCurrentTS = null;
		$firstCurrentTS = null;
		$registeredOverlapError = false;

		$hasPreviousMenuSessions = false;
		$hasNextMenuSessions = false;

		// set limits
		$firstLegalSessionDayTS = $curMonthStartTS - (86400 * 6);
		$nextMonth = $currentMonth + 1;
		$theYear = $currentYear;
		if ($nextMonth > 12)
		{
			$nextMonth = 1;
			$theYear++;
		}
		$lastLegalSessionDayTS = mktime(0, 0, 0, $nextMonth, 1, $theYear) + (86400 * 5);

		$count = 0;

		//CARL LOOP
		while ($Sessions->fetch())
		{
			$isCurrent = $Sessions->menu_id == $currentMenu;
			$thisSessionTimeTS = strtotime($Sessions->session_start);

			if (!$isCurrent)
			{
				if ($thisSessionTimeTS < $cueMonthMidPointTS) // previous menu
				{
					$hasPreviousMenuSessions = true;
					if ($thisSessionTimeTS > $prevMenuEndSessionTS)
					{
						$prevMenuEndSessionTS = $thisSessionTimeTS;
					}

					if ($hasCurrentMenuSessions && !$registeredOverlapError)
					{
						$tpl->setErrorMsg('There is an overlapping menu. (That is, a session is using an older menu after a new menu has begun).');
						//CLog::RecordNew(CLog::ERROR,'Menu overlap issue- store:'.$currentStore.' menu: '.$currentMenu, "", "", true );
						$registeredOverlapError = true;
					}
				}
				else // next menu
				{
					$hasNextMenuSessions = true;
					if ($thisSessionTimeTS < $nextMenuStartSessionTS)
					{
						$nextMenuStartSessionTS = $thisSessionTimeTS;
					}
				}
			}
			else
			{
				$hasCurrentMenuSessions = true;
			}

			if ($isCurrent && $hasNextMenuSessions && !$registeredOverlapError)
			{
				$tpl->setErrorMsg('There is an overlapping menu. (That is, a session is using an older menu after a new menu has begun).');
				//CLog::RecordNew(CLog::ERROR,'Menu overlap issue- store:'.$currentStore.' menu: '.$currentMenu, "", "", true );
				$registeredOverlapError = true;
			}

			$dateOnly = Date("n", $thisSessionTimeTS) . "/" . Date("j", $thisSessionTimeTS) . "/" . Date("Y", $thisSessionTimeTS);
			$timeOnly = Date("G", $thisSessionTimeTS) . ":" . Date("i", $thisSessionTimeTS);
			$isOpen = $Sessions->isOpen($Store);
			$isOpenForCustomization = $Sessions->isOpenForCustomization($Store);
			$allowedCustomization = $Sessions->allowedCustomization($Store);

			// TODO: better not use time as key as multiple sessions at the same time could exist

			// $remaining_slots = $Sessions->available_slots - $Sessions->filled;

			self::$sessionArray[$dateOnly][$Sessions->id] = array(
				'time' => $timeOnly,
				'state' => $Sessions->session_publish_state,
				'id' => $Sessions->id,
				'session_type' => $Sessions->session_type,
				'session_type_subtype' => $Sessions->session_type_subtype,
				'capacity' => $Sessions->available_slots,
				'isQ6' => $Sessions->session_type == CSession::QUICKSIX ? true : false,
				'isSpecialEvent' => $Sessions->session_type == CSession::SPECIAL_EVENT ? true : false,
				'isTODD' => $Sessions->session_type == CSession::TODD ? true : false,
				'dreamTaste' => $Sessions->session_type == CSession::DREAM_TASTE ? true : false,
				'fundraiserEvent' => $Sessions->session_type == CSession::FUNDRAISER ? true : false,
				'remainingSlots' => $Sessions->remaining_slots,
				'remainingIntroSlots' => $Sessions->remaining_intro_slots,
				'num_rsvps' => $Sessions->num_rsvps,
				'supportsIntro' => $Sessions->introductory_slots > 0 ? true : false,
				'isOpen' => $isOpen,
				'isOpenForCustomization' => $isOpenForCustomization,
				'allowedCustomization' => $allowedCustomization,
				'isPrivate' => ($Sessions->session_password ? true : false),
				'isCurrent' => $isCurrent
			);

			$Sessions->getTasteEventProperties();

			list (self::$sessionArray[$dateOnly][$Sessions->id]['session_type_title'], self::$sessionArray[$dateOnly][$Sessions->id]['session_type_title_public'], self::$sessionArray[$dateOnly][$Sessions->id]['session_type_title_short'], self::$sessionArray[$dateOnly][$Sessions->id]['session_type_fadmin_acronym'], self::$sessionArray[$dateOnly][$Sessions->id]['session_type_string']) = $Sessions->getSessionTypeProperties();

			$lastMenu = $Sessions->menu_id;
		}

		if ($hasPreviousMenuSessions)
		{
			$firstCurrentTS = $prevMenuEndSessionTS + 86400;
		}
		else
		{
			// set to 6 days prior to month begin
			$firstCurrentTS = $firstLegalSessionDayTS;
		}

		if ($hasNextMenuSessions)
		{
			$lastCurrentTS = $nextMenuStartSessionTS - 86400;
		}
		else
		{
			$lastCurrentTS = $lastLegalSessionDayTS;
		}

		// TAG
		// OVERRIDE HERE

		// DavidB: 11/14/2006
		// override the calculated begin/end timestamps if applicable
		// If there is a global menu end date for the previous month use it otherwise use the calculated timestamp
		if (!empty($tsPreviousMenuGlobalEndDate))
		{
			$firstCurrentTS = strtotime($tsPreviousMenuGlobalEndDate) + 86400;
		}
		// If there is a global menu end date for the current month use it otherwise use the calculated timestamp
		if (!empty($tsCurrentMenuGlobalEndDate))
		{
			$lastCurrentTS = strtotime($tsCurrentMenuGlobalEndDate);
		}

		if ($firstCurrentTS != null)
		{
			$firstCurrentDate = Date("n", $firstCurrentTS) . "/" . Date("j", $firstCurrentTS) . "/" . Date("Y", $firstCurrentTS);
			self::$dayRangeMarkersArray[$firstCurrentDate] = 'style="background-image: url(' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_start' . $currentMonthSuffix . '.gif); background-repeat: no-repeat; background-position:left 24px top 1px;"';
		}

		if ($lastCurrentTS != null)
		{
			$lastCurrentDate = Date("n", $lastCurrentTS) . "/" . Date("j", $lastCurrentTS) . "/" . Date("Y", $lastCurrentTS);
			self::$dayRangeMarkersArray[$lastCurrentDate] = 'style="background-image: url(' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_end' . $currentMonthSuffix . '.gif); background-repeat: no-repeat; background-position:right -40px top 1px;"';
		}

		$todayMarkerRangeStart = mkTime(0, 0, 0, $todaysMonth, $todaysDay, $todaysYear);
		$todayMarkerRangeEnd = mkTime(0, 0, 0, $todaysMonth, $todaysDay, $todaysYear);

		$calendar = new CCalendar();
		$calendarRows = $calendar->generateDayArray($currentMonth, $currentYear, "populateCallback", $todayMarkerRangeStart, $todayMarkerRangeEnd, true);

		$CurMonthText = date("F", $currentMenuTS);

		$tpl->assign('page_title', 'Session Management');
		$tpl->assign('rows', $calendarRows);
		//	$tpl->assign('dbg', self::$sessionArray );
		$tpl->assign('calendarTitle', $CurMonthText . " " . $currentYear);
		$tpl->assign('calendarName', "sessionsManagerCalendar");
		$tpl->assign('storeAndMenu', $storeMenuFormArray);
		$tpl->assign('calWidth', '100%');
		$tpl->assign('support_cell_selection', true);
		$tpl->assign('currentMenuID', $currentMenu);
	}

}

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

function populateCallback($Date)
{
	$itemList = array();
	$styleOverride = null;

	$count = 0;

	if (isset(page_admin_session_mgr::$dayRangeMarkersArray[$Date]))
	{
		$styleOverride = page_admin_session_mgr::$dayRangeMarkersArray[$Date];
	}

	if (isset(page_admin_session_mgr::$sessionArray[$Date]) && page_admin_session_mgr::$sessionArray[$Date])
	{
		usort(page_admin_session_mgr::$sessionArray[$Date], 'dateCompare');

		foreach (page_admin_session_mgr::$sessionArray[$Date] as $dayItem)
		{
			$image = "";
			$anchorStart = "";
			$anchorEnd = "";
			$editClick = "";



			//Walk-in sessions are created for every day in the month when the menu is created. They are not shown on the session calendar
			//because there is no need to edit them
			if (array_key_exists('session_type_subtype', $dayItem) && $dayItem['session_type_subtype'] == CSession::WALK_IN){
				continue;
			}
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

			$IntroSlots = "-";

			if ($dayItem['supportsIntro'])
			{
				$IntroSlots = $dayItem['remainingIntroSlots'];
			}

			$sessionTypeNote = CCalendar::dayItemTypeNote($dayItem);

			if (!$dayItem['isOpen'])
			{
				$image = ADMIN_IMAGES_PATH . '/calendar/session_closed.png';
				$editClick = 'onmouseover="hiliteIcon(' . $dayItem['id'] . ', 4);" onmouseout="unHiliteIcon(' . $dayItem['id'] . ', 4);"';
			}
			else if ($dayItem['state'] == "SAVED")
			{
				$image = ADMIN_IMAGES_PATH . '/calendar/session_saved.png';
				$editClick = 'onmouseover="hiliteIcon(' . $dayItem['id'] . ', 2);" onmouseout="unHiliteIcon(' . $dayItem['id'] . ', 2);"';
			}
			else if ($dayItem['state'] == "PUBLISHED")// published
			{
				$image = ADMIN_IMAGES_PATH . '/calendar/session_pub.png';
				$editClick = 'onmouseover="hiliteIcon(' . $dayItem['id'] . ', 1);" onmouseout="unHiliteIcon(' . $dayItem['id'] . ', 1);"';
			}
			else
			{
				$image = ADMIN_IMAGES_PATH . '/calendar/session_closed.png';
				$editClick = 'onmouseover="hiliteIcon(' . $dayItem['id'] . ', 4);" onmouseout="unHiliteIcon(' . $dayItem['id'] . ', 4);"';
			}
			$customizable = '';
			if($dayItem['allowedCustomization'])
			{
				if ($dayItem['isOpenForCustomization'])
				{
					$customizable = '<i class="dd-icon icon-customize text-orange" style="font-size: 65%;" data-tooltip="Session Open for Customization"></i>';
				}
				else
				{
					$customizable = '<i class="dd-icon icon-customize text-black" style="font-size: 65%;" data-tooltip="Session Closed for Customization"></i>';
				}
			}


			if ($dayItem['dreamTaste'] || $dayItem['fundraiserEvent'])
			{
				$numOrders = $dayItem['capacity'] - ($dayItem['num_rsvps'] + $dayItem['remainingSlots']);
				$breakdown = "";
				if ($dayItem['num_rsvps'] > 0)
				{
					$breakdown = "<br />($numOrders Orders, {$dayItem['num_rsvps']} RSVPs)";
				}

				$itemList[$count++] = '<a href="main.php?page=admin_edit_session&amp;session=' . $dayItem['id'] . '&amp;back=main.php?page=admin_session_mgr"><img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign"></a>' . $sessionTypeNote . '<a href="main.php?page=admin_main&amp;session=' . $dayItem['id'] . '" class="' . $linkClass . '" data-tooltip="Remaining Slots: ' . $dayItem['remainingSlots'] . $breakdown . '">' . $time12Hour . '&nbsp;(' . $dayItem['remainingSlots'] . '/' . $IntroSlots . ')</a>'. $customizable;
			}
			else
			{
				$itemList[$count++] = '<a href="main.php?page=admin_edit_session&amp;session=' . $dayItem['id'] . '&amp;back=main.php?page=admin_session_mgr"><img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign"></a>' . $sessionTypeNote . '<a href="main.php?page=admin_main&amp;session=' . $dayItem['id'] . '" class="' . $linkClass . '" data-tooltip="Remaining Slots: ' . $dayItem['remainingSlots'] . '<br />Remaining Starter Pack Slots: ' . $IntroSlots . '">' . $time12Hour . '&nbsp;(' . $dayItem['remainingSlots'] . '/' . $IntroSlots . ')</a>'. $customizable;
			}
		}
	}

	return array(
		$itemList,
		$styleOverride
	);
}

?>