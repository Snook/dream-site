<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");

class processor_admin_calendarProcessor extends CPageProcessor
{

	static $sessionArray = array();

	function __construct()
	{
		$this->doGenericInputCleaning = false;
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
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

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$storeID = null;
		if (isset($_REQUEST['store_id']))
		{
			$storeID = $_REQUEST['store_id'];
		}

		if (!$storeID || !is_numeric($storeID))
		{
			return "error";
		}
		else
		{
			$daoStore = DAO_CFactory::create('store');
			$daoStore->id = $storeID;
			$daoStore->find(true);
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retrieve')
		{

			$tpl = new CTemplate();

			if (!empty($_REQUEST['timestamp']) && is_numeric($_REQUEST['timestamp']))
			{
				$timestamp = $_REQUEST['timestamp'];
			}
			else
			{
				$timestamp = time();
			}

			$now = CTimezones::getAdjustedServerTime($daoStore);

			// check for orders in previous month if current day is greater than 6
			$day = date("j", $now);
			$month = date("n", $now);
			$year = date("Y", $now);

			$cutOff = false;

			//		if ($day > 6)
			//		{
			//			$cutOff = mktime(0, 0, 0, $month, 1, $year);
			//		}
			//		else
			//		{
			//			$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);
			//		}

			$calendarInfo = COrders::buildDirectOrderCalendarArray($daoStore, $timestamp, false, $cutOff);
			// TODO: Remove once fully converted to UTF8
			$calendarInfo = CAppUtil::utf8ize($calendarInfo);

			$tpl->assign('rows', $calendarInfo);

			$tpl->assign('calendarTitle', date("F Y", $timestamp));

			$previoustimestamp = mktime(0, 0, 0, date('m', $timestamp) - 1, 1, date('Y', $timestamp));

			if ($previoustimestamp < $cutOff)
			{
				CCalendar::setPreviousLink($tpl, 'javascript:alert(\'Orders cannot be placed or edited in the previous month after the 6th of the current month.\' );');
			}
			else
			{
				CCalendar::setPreviousLink($tpl, 'javascript:monthChange(' . $previoustimestamp . ')');
			}

			$nexttimestamp = mktime(0, 0, 0, date('m', $timestamp) + 1, 1, date('Y', $timestamp));
			CCalendar::setNextLink($tpl, 'javascript:monthChange(' . $nexttimestamp . ')');

			$data = $tpl->fetch('admin/subtemplate/calendar.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Month retrieval Success.',
				'data' => $data
			));
		}
		else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retrieve_for_reschedule')
		{
			$tpl = new CTemplate();

			$orgSessionID = false;
			if (!empty($_REQUEST['cur_session_id']) and is_numeric($_REQUEST['cur_session_id']))
			{
				$orgSessionID = $_REQUEST['cur_session_id'];
			}

			$Org_session = DAO_CFactory::create('session');
			$Org_session->id = $orgSessionID;
			$Org_session->find(true);
			$OrgSessionType = $Org_session->session_type;
			$OrgSessionSubType = $Org_session->session_type_subtype;

			// ------------------------------- get menu month
			$Menu = DAO_CFactory::create('menu');
			$Menu->id = $Org_session->menu_id;
			$Menu->find(true);
			$menuTS = strtotime($Menu->menu_start);
			$CurMonthText = date("F", $menuTS);
			$menuMonth = date("n", $menuTS);
			$menuYear = date("Y", $menuTS);

			$timestamp = mktime(0, 0, 0, $menuMonth, 1, $menuYear);

			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $storeID;
			$storeObj->find(true);
			$today = CTimezones::getAdjustedTime($storeObj, time());
			$todayStr = date("Y-m-d H:i:s", $today);
			$currentCalendarMonth = date("n", $today);
			$currentCalendarDay = date("j", $today);
			$currentCalendarYear = date("Y", $today);
			$lockOutCutOff = 0;

			if ($currentCalendarDay > 6)
			{
				$lockOutCutOff = mktime(0, 0, 0, $currentCalendarMonth, 1, $currentCalendarYear);
			}

			if ($menuTS < mktime(0, 0, 0, $currentCalendarMonth, 1, $currentCalendarYear))
			{
				$lockOutCutOff = mktime(0, 0, 0, $menuMonth, 1, $menuYear);
			}

			$orgSessionCalendarMonth = date("n", strtotime($Org_session->session_start));

			// org session is in the current but we are after the 6th and the session belongs to last month's menu
			if ($currentCalendarMonth == $orgSessionCalendarMonth && $currentCalendarDay > 6 && $menuTS < mktime(0, 0, 0, $currentCalendarMonth, 1, $currentCalendarYear))
			{
				$lockOutCutOff = mktime(0, 0, 0, $currentCalendarMonth, 1, $currentCalendarYear);
			}

			$Sessions = DAO_CFactory::create('session');

			$typeExclusion = "";
			if ($OrgSessionType == CSession::STANDARD || $OrgSessionType == CSession::SPECIAL_EVENT)
			{
				$typeExclusion = " and session.session_type not in ('TODD', 'DREAM_TASTE', 'FUNDRAISER') ";
			}
			else if ($OrgSessionType == CSession::DREAM_TASTE)
			{
				$typeExclusion = " and session.session_type not in ('STANDARD', 'SPECIAL_EVENT', 'FUNDRAISER') ";
			}
			else if ($OrgSessionType == CSession::FUNDRAISER)
			{
				$typeExclusion = " and session.session_type not in ('STANDARD', 'SPECIAL_EVENT', 'DREAM_TASTE') ";
			}

			$query = "SELECT
 				session.*,
 				dtet.title_public as dream_taste_theme_title_public,
 				dtet.title as dream_taste_theme_title,
 				dtet.fadmin_acronym as dream_taste_theme_fadmin_acronym,
					(session.available_slots - count(booking.id)) - COUNT(distinct sr.id) as 'remaining_slots',
					(introductory_slots - (count(IF(booking.booking_type = 'INTRO', 1, NULL)) + if(count(IF(booking.booking_type = 'STANDARD', 1, NULL))
					> (available_slots - introductory_slots), count(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots),0))) as 'remaining_intro_slots',
					COUNT(distinct sr.id) as num_rsvps
					FROM session
					LEFT JOIN booking ON booking.session_id = session.id and booking.status = 'ACTIVE'
					LEFT JOIN session_rsvp sr on sr.session_id = session.id AND sr.upgrade_booking_id IS NULL and sr.is_deleted = 0
					LEFT JOIN session_properties sp on sp.session_id = session.id and sp.is_deleted = 0
					LEFT JOIN dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id and dtep.is_deleted = 0
					LEFT JOIN dream_taste_event_theme dtet on dtet.id = dtep.dream_taste_event_theme
					WHERE {$Org_session->menu_id} = session.menu_id and session.store_id = $storeID AND session.is_deleted = 0 $typeExclusion group by session.id order by session.session_start";

			// First get the session data
			$Sessions->query($query);

			$count = 0;
			while ($Sessions->fetch())
			{
				$asTime = strtotime($Sessions->session_start);
				$dateOnly = Date("n", $asTime) . "/" . Date("j", $asTime) . "/" . Date("Y", $asTime);
				$timeOnly = Date("g:ia", $asTime);

				$isOpenSession = $Sessions->isOpen($storeObj);
				$Sessions->getSessionTypeProperties();

				if ($Sessions->session_publish_state != 'SAVED')
				{

					// Session Type transition
					$transitionType = 'none';
					// Pick Up
					if ($OrgSessionType == CSession::SPECIAL_EVENT)
					{
						if (empty($OrgSessionSubType))
						{

							if ($Sessions->session_type == CSession::STANDARD)
							{
								// normal Pickup session to Assembled
								$transitionType = 'MFY_to_Assembled';
							}
							else if ($Sessions->session_type_subtype == CSession::DELIVERY)
							{
								// normal Pickup to Home Delivery
								$transitionType = 'MFY_to_HD';
							}
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
							{
								// normal Pickup to Community Pickup
								$transitionType = 'MFY_to_CPU';
							}
							else if ($Sessions->session_type_subtype == CSession::WALK_IN)
							{
								// normal Pickup to Home Delivery
								$transitionType = 'MFY_to_WI';
							}
						}
						else if ($OrgSessionSubType == CSession::WALK_IN)
						{
							if ($Sessions->session_type == CSession::STANDARD)
							{
								// WI to Assembled
								$transitionType = 'WI_to_Assembled';
							}
							else if (empty($Sessions->session_type_subtype))
							{
								// WI to Pickup
								$transitionType = 'WI_to_MFY';
							}
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
							{
								$transitionType = 'WI_to_CPU';
							}
							else if ($Sessions->session_type_subtype == CSession::DELIVERY)
							{
								$transitionType = 'WI_to_HD';
							}
						}
						else if ($OrgSessionSubType == CSession::DELIVERY)
						{
							if ($Sessions->session_type == CSession::STANDARD)
							{
								// Home Delivery session to Assembled
								$transitionType = 'HD_to_Assembled';
							}
							else if (empty($Sessions->session_type_subtype))
							{
								// normal Pickup to Home Delivery
								$transitionType = 'HD_to_MFY';
							}
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
							{
								// Home Delivery to Community Pickup
								$transitionType = 'HD_to_CPU';
							}
							else if ($Sessions->session_type_subtype == CSession::WALK_IN)
							{
								// Home Delivery to WI
								$transitionType = 'HD_to_WI';
							}
						}
						else if ($OrgSessionSubType == CSession::REMOTE_PICKUP_PRIVATE || $OrgSessionSubType == CSession::REMOTE_PICKUP)
						{
							if ($Sessions->session_type == CSession::STANDARD)
							{
								// Community Pickup session to Assembled
								$transitionType = 'CPU_to_Assembled';
							}
							else if (empty($Sessions->session_type_subtype))
							{
								// Community Pickup to Normal
								$transitionType = 'CPU_to_MFY';
							}
							else if ($Sessions->session_type_subtype == CSession::DELIVERY)
							{
								// Community Pickup to Home Delivery
								$transitionType = 'CPU_to_HD';
							}
							else if ($Sessions->session_type_subtype == CSession::WALK_IN)
							{
								// Community Pickup to WI
								$transitionType = 'CPU_to_WI';
							}
						}
					}
					else
					{
						// Standard Assembly
						if (empty($Sessions->session_type_subtype) && $Sessions->session_type == CSession::SPECIAL_EVENT)
						{
							// Assembly to normal Puck Up
							$transitionType = 'Assembly_to_MFY';
						}
						else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
						{
							// Home Delivery to Community Pickup
							$transitionType = 'Assembly_to_CPU';
						}
						else if ($Sessions->session_type_subtype == CSession::DELIVERY)
						{
							// Community Pickup to Home Delivery
							$transitionType = 'Assembly_to_HD';
						}
						else if ($Sessions->session_type_subtype == CSession::WALK_IN)
						{
							// Assembly to Walk-in
							$transitionType = 'Assembly_to_WI';
						}
					}

					self::$sessionArray[$dateOnly][$Sessions->id] = array(
						'DAO_session' => clone $Sessions,
						'time' => $timeOnly,
						'state' => $Sessions->session_publish_state,
						'id' => $Sessions->id,
						'isQ6' => $Sessions->session_type == CSession::QUICKSIX ? true : false,
						'isSpecialEvent' => $Sessions->session_type == CSession::SPECIAL_EVENT ? true : false,
						'isTODD' => ($Sessions->session_type == CSession::TODD || $Sessions->session_type == CSession::DREAM_TASTE) ? true : false,
						'transition_type' => $transitionType,
						'remainingSlots' => $Sessions->remaining_slots,
						'slots' => $Sessions->remaining_slots,
						'is_walk_in' => $Sessions->isWalkIn(),
						'is_delivery' => $Sessions->isDelivery(),
						'is_remote_pickup' => $Sessions->isRemotePickup(),
						// to match DO function
						'num_rsvps' => $Sessions->num_rsvps,
						'intro_slots' => $Sessions->remaining_intro_slots,
						'dreamTaste' => ($Sessions->session_type == CSession::DREAM_TASTE ? true : false),
						'fundraiserEvent' => ($Sessions->session_type == CSession::FUNDRAISER ? true : false),
						'is3Plan' => true,
						'details' => $daoStore->publish_session_details ? $Sessions->session_details : "",
						'capacity' => $Sessions->available_slots,
						'intro_capacity' => $Sessions->introductory_slots,
						'is_discounted' => !empty($Sessions->session_discount_id) ? true : false,
						'supportsIntro' => $daoStore->storeSupportsIntroOrders($Sessions->menu_id),
						'isOpen' => $isOpenSession,
						'publish_state' => $Sessions->session_publish_state,
						//	'isCurrentMonth' => ($currentMonth == date('m',strtotime($Sessions->menu_start))?true:false),
						'isSelected' => ($orgSessionID && ($orgSessionID == $Sessions->id)) ? true : false,
						'cutOffDate' => $lockOutCutOff,
						'remaining_intro_slots' => $Sessions->remaining_intro_slots,
						'isPrivate' => ($Sessions->session_password ? true : false),
						'session_type_fadmin_acronym' => $Sessions->session_type_fadmin_acronym,
						'session_type_string' => $Sessions->session_type_string,
						'session_type_title' => $Sessions->session_type_title,
						'dream_taste_theme_title_public' => $Sessions->dream_taste_theme_title_public,
						'dream_taste_theme_title' => $Sessions->dream_taste_theme_title
					);
				}
			}

			$calendar = new CCalendar();
			$calendarRows = $calendar->generateDayArray($menuMonth, $menuYear, "populateRescheduleCallbackNew", false, false, false);
			$calendarRows = CAppUtil::utf8ize($calendarRows);

			$tpl->assign('rows', $calendarRows);

			$tpl->assign('calendarTitle', date("F Y", $timestamp));

			$data = $tpl->fetch('admin/subtemplate/calendar.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Month retrieval Success.',
				'data' => $data
			));
		}
	}

}

function populateRescheduleCallbackNew($date)
{
	$retVal = array();

	$styleOverride = null;

	if (array_key_exists($date, processor_admin_calendarProcessor::$sessionArray))
	{
		$dateArray = processor_admin_calendarProcessor::$sessionArray;

		foreach ($dateArray[$date] as $id => $dayItem)
		{
			$ctl_message = "'" . $dayItem['transition_type'] . "'";

			if ($dayItem['is_walk_in'])
			{
				if ($ctl_message == "'none'")
				{
					$ctl_message = "'none_wi'";
				}
			}

			if ($dayItem['dreamTaste'] || $dayItem['fundraiserEvent'])
			{
				$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";
				if ($dayItem['num_rsvps'] > 0)
				{
					$numOrders = $dayItem['capacity'] - ($dayItem['slots'] + $dayItem['num_rsvps']);
					$spotsText .= "(" . $numOrders . " Orders, " . $dayItem['num_rsvps'] . " RSVPs)";
				}
			}
			else
			{
				$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";

				if ($dayItem['supportsIntro'])
				{
					$spotsText .= "; " . $dayItem['intro_slots'] . " of " . $dayItem['intro_capacity'] . " starter pack spots available";
				}
			}

			if ($dayItem['isSelected'])
			{
				$sessionAction = 'javascript:dd_message({title:\'Alert\', message:\'This is the session that is currently booked.\'});';
			}
			else
			{
				$sessionAction = 'javascript:onRescheduleClick(' . $id . ', \'' . CTemplate::dateTimeFormat($date . ' ' . $dayItem['time']) . '\', ' . ($dayItem['DAO_session']->isDiscounted() ? '1' : '0') . ', ' . $ctl_message . ')';
			}

			$retVal[] = $dayItem['DAO_session']->sessionTypeIcon(true) . '
					<a class="' . (($dayItem['isSelected']) ? 'bg-warning border border-green' : '') . '" href="' . $sessionAction . '"><span ' . ((!$dayItem['DAO_session']->isPublished()) ? 'class="text-decoration-line-through"' : '') . ' data-toggle="tooltip" title="' . ((!$dayItem['DAO_session']->isWalkIn()) ? $dayItem['DAO_session']->sessionStartDateTime()->format('g:i A') . ' - ' . $dayItem['DAO_session']->sessionEndDateTime()->format('g:i A') : 'All day') . '">' . ((!$dayItem['DAO_session']->isWalkIn()) ? $dayItem['DAO_session']->sessionStartDateTime()->format('g:i A') : 'Walk-In') . '</span></a>
					' . ((!$dayItem['DAO_session']->isWalkIn()) ? '<span data-toggle="tooltip" title="' . $spotsText . '">(' . $dayItem['slots'] . ')</span>' : '') . '
					' . $dayItem['DAO_session']->openForCustomizationIcon() . '
					' . $dayItem['DAO_session']->discountedIcon();
		}
	}

	return array(
		$retVal,
		$styleOverride
	);
}
?>