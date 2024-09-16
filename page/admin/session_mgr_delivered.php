<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/CCalendar.inc';
require_once 'includes/DAO/BusinessObject/CSession.php';

class page_admin_session_mgr_delivered extends CPageAdminOnly
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
		if (!$this->CurrentBackOfficeStore->isDistributionCenter())
		{
			CApp::bounce('/backoffice/session-mgr');
		}

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
			$storeMenuForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : null;

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

		CBrowserSession::instance()->setValue('sm_current_page', '/backoffice/session-mgr');

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
			$storeMenuForm->DefaultValues['menus'] = CGPC::do_clean($_POST["menus"], TYPE_STR);
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

		$Sessions->findCalendarRangeForDeliveredSessionMgr($currentStore, $rangeStart, $rangeEnd);

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

			$bookings = $Sessions->getDeliveredBookingsForSession();
			$delivered_bookings_count = count($bookings);
			$shipping_bookings = $Sessions->getDeliveredBookingsForSession(true);
			$shipping_bookings_count = count($shipping_bookings);
			$remaining_slots = $Sessions->available_slots - $shipping_bookings_count;

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

			self::$sessionArray[$dateOnly][$Sessions->id] = array(
				'time' => $timeOnly,
				'state' => $Sessions->session_publish_state,
				'id' => $Sessions->id,
				'session_type' => $Sessions->session_type,
				'capacity' => $Sessions->available_slots,
				'isQ6' => $Sessions->session_type == CSession::QUICKSIX ? true : false,
				'isSpecialEvent' => $Sessions->session_type == CSession::SPECIAL_EVENT ? true : false,
				'isTODD' => $Sessions->session_type == CSession::TODD ? true : false,
				'dreamTaste' => $Sessions->session_type == CSession::DREAM_TASTE ? true : false,
				'fundraiserEvent' => $Sessions->session_type == CSession::FUNDRAISER ? true : false,
				'remainingSlots' => $remaining_slots,
				'remainingIntroSlots' => $Sessions->remaining_intro_slots,
				'num_rsvps' => $Sessions->num_rsvps,
				'supportsIntro' => $Store->storeSupportsIntroOrders($Sessions->menu_id),
				'isOpen' => $isOpen,
				'isPrivate' => ($Sessions->session_password ? true : false),
				'isCurrent' => $isCurrent,
				'delivered_supports_delivery' => ($Sessions->delivered_supports_delivery > 0 ? true : false),
				'delivered_supports_shipping' => ($Sessions->delivered_supports_shipping > 0 ? true : false),
				'delivered_bookings_count' => $delivered_bookings_count,
				'shipping_bookings_count' => $shipping_bookings_count
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

		$todayMarkerRangeStart = mkTime(0, 0, 0, $todaysMonth, $todaysDay, $todaysYear);
		$todayMarkerRangeEnd = mkTime(0, 0, 0, $todaysMonth, $todaysDay, $todaysYear);

		$calendar = new CCalendar();
		$calendarRows = $calendar->generateDayArray($currentMonth, $currentYear, "populateCallback", $todayMarkerRangeStart, $todayMarkerRangeEnd, true);

		$CurMonthText = date("F", $currentMenuTS);

		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $currentMenu;
		$DAO_menu->find(true);

		$tpl->assign('DAO_menu', $DAO_menu);
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

	if (isset(page_admin_session_mgr_delivered::$dayRangeMarkersArray[$Date]))
	{
		$styleOverride = page_admin_session_mgr_delivered::$dayRangeMarkersArray[$Date];
	}

	if (isset(page_admin_session_mgr_delivered::$sessionArray[$Date]) && page_admin_session_mgr_delivered::$sessionArray[$Date])
	{
		usort(page_admin_session_mgr_delivered::$sessionArray[$Date], 'dateCompare');

		foreach (page_admin_session_mgr_delivered::$sessionArray[$Date] as $dayItem)
		{
			$image = "";
			$anchorStart = "";
			$anchorEnd = "";
			$editClick = "";

			if ($dayItem['isOpen'])
			{
				$linkClass = "";
			}
			else
			{
				$linkClass = "text-muted";
			}

			if (!$dayItem['isCurrent'])
			{
				$linkClass = "font-size-extra-small text-muted font-italic";
			}

			$id = 0;
			$time12Hour = date("g:i a", strtotime($dayItem['time']));

			$IntroSlots = "-";

			if ($dayItem['supportsIntro'])
			{
				$IntroSlots = $dayItem['remainingIntroSlots'];
			}

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

			if ($dayItem['delivered_supports_shipping'])
			{
				$dayItem['session_type_fadmin_acronym'] = 'P';
				$dayItem['session_type_string'] = 'pickup';
				$dayItem['session_type_title'] = 'Pickup';
				$sessionTypeNote = CCalendar::dayItemTypeNote($dayItem);

				$itemList[$count++] = '<a href="/backoffice/edit-session-delivered?session=' . $dayItem['id'] . '">
				<img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign">
				</a>' . $sessionTypeNote . '<a href="/backoffice/main_delivered?session=' . $dayItem['id'] . '" class="' . $linkClass . '" data-tooltip="Remaining Pick Ups: ' . $dayItem['remainingSlots'] . '">&nbsp;' . $dayItem['remainingSlots'] . '</a>';
			}

			if (!$dayItem['delivered_supports_delivery'] && !$dayItem['delivered_supports_shipping'])
			{
				$dayItem['session_type_fadmin_acronym'] = 'PB';
				$dayItem['session_type_string'] = 'pickup_blackout';
				$dayItem['session_type_title'] = 'Pickup Blackout';
				$sessionNotePickup = CCalendar::dayItemTypeNote($dayItem);

				$dayItem['session_type_fadmin_acronym'] = 'DB';
				$dayItem['session_type_string'] = 'delivery_blackout';
				$dayItem['session_type_title'] = 'Delivery Blackout';
				$sessionNoteDelivery = CCalendar::dayItemTypeNote($dayItem);

				$itemList[$count++] = '<a href="/backoffice/edit-session-delivered?session=' . $dayItem['id'] . '">
				<img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign">
				</a>' . $sessionNotePickup . $sessionNoteDelivery;
			}
			else if (!$dayItem['delivered_supports_delivery'])
			{
				$dayItem['session_type_fadmin_acronym'] = 'DB';
				$dayItem['session_type_string'] = 'delivery_blackout';
				$dayItem['session_type_title'] = 'Delivery Blackout';
				$sessionNoteDelivery = CCalendar::dayItemTypeNote($dayItem);

				$itemList[$count++] = '<a href="/backoffice/edit-session-delivered?session=' . $dayItem['id'] . '">
				<img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign">
				</a>' . $sessionNoteDelivery;
			}
			else if (!$dayItem['delivered_supports_shipping'])
			{
				$dayItem['session_type_fadmin_acronym'] = 'PB';
				$dayItem['session_type_string'] = 'pickup_blackout';
				$dayItem['session_type_title'] = 'Pickup Blackout';
				$sessionNotePickup = CCalendar::dayItemTypeNote($dayItem);

				$itemList[$count++] = '<a href="/backoffice/edit-session-delivered?session=' . $dayItem['id'] . '">
				<img name="' . $dayItem['time'] . '" id="' . $dayItem['id'] . '" src="' . $image . '" ' . $editClick . ' class="img_valign">
				</a>' . $sessionNotePickup;
			}
		}
	}

	return array(
		$itemList,
		$styleOverride
	);
}