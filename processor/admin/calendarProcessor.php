<?php

/**
 *
 *
 * @version   $Id$
 * @copyright 2007
 */
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
       				session.id, 
					session.menu_id, 
					session.session_publish_state, 
       				session.session_start, 
       				session.session_close_scheduling, 
       				session.session_details,
					session.session_type, 
					session.session_type_subtype, 
       				session.available_slots, 
       				session.introductory_slots, 
       				session.session_password,
       				dtet.title_public as dream_taste_theme_title_public,
       				dtet.title as dream_taste_theme_title,
       				dtet.fadmin_acronym as dream_taste_theme_fadmin_acronym,
					(session.available_slots - count(booking.id)) - COUNT(distinct sr.id) as 'remaining_slots',
					(introductory_slots -  (count(IF(booking.booking_type = 'INTRO', 1, NULL)) + if(count(IF(booking.booking_type = 'STANDARD', 1, NULL))
					> (available_slots - introductory_slots), count(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots),0))) as 'remaining_intro_slots',
					COUNT(distinct sr.id) as num_rsvps
					FROM session
					LEFT JOIN booking ON booking.session_id = session.id  and booking.status = 'ACTIVE'
					LEFT JOIN session_rsvp sr on sr.session_id = session.id AND sr.upgrade_booking_id IS NULL and sr.is_deleted  = 0
					LEFT JOIN session_properties sp on sp.session_id = session.id and sp.is_deleted  = 0
					LEFT JOIN dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id and dtep.is_deleted  = 0
					LEFT JOIN dream_taste_event_theme dtet on dtet.id = dtep.dream_taste_event_theme
					WHERE {$Org_session->menu_id} = session.menu_id and session.store_id = $storeID  AND session.is_deleted = 0 $typeExclusion group by session.id order by session.session_start";

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
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE )
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
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE )
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
							else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE )
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
								// Community Pickup  to WI
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
						else if ($Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE )
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

function populateRescheduleCallbackNew($date, $isDirect = true)
{
	$retVal = array();

	$styleOverride = null;

	if (array_key_exists($date, processor_admin_calendarProcessor::$sessionArray))
	{
		$dateArray = processor_admin_calendarProcessor::$sessionArray;
		foreach ($dateArray[$date] as $id => $dayItem)
		{

			$ctl_message = "'" . $dayItem['transition_type'] . "'";
			$linkClass = 'calendar_on_text_on';
			$cellTS = strtotime($date);

			CSession::prepareSessionDetailsForDisplay($dayItem['details']);

			$timeText = $dayItem['time'];
			//if it's 4 chars ( '9 am' ), then add extra space to the front so they line up
			if (strlen($timeText) == 4)
			{
				$timeText = ' ' . $timeText;
			}
			//$timeText = str_replace(' ', '&nbsp;',$timeText);

			//if($dayItem[])

			if ($dayItem['is_walk_in']){
				$timeText = 'Walk-In';
				$spotsText = "";
				if($ctl_message == "'none'"){
					$ctl_message = "'none_wi'";
				}

			}

			$discountText = "";
			$is_discounted = false;
			if (isset($dayItem['is_discounted']) && $dayItem['is_discounted'])
			{
				$discountText = '<font style="color:green">-$</font>';
				$is_discounted = true;
			}

			$contents = null;

			$dayItemTypeNote = CCalendar::dayItemTypeNote($dayItem);

			//fullness: all_avail, 25percent_full, half_full, almost_full,
			$image_howfull = 'all_avail';
			if ($dayItem['capacity'] == 0)
			{
				$percentFull = 100;
			}
			else
			{
				$percentFull = ($dayItem['capacity'] - $dayItem['slots']) * 100 / $dayItem['capacity'];
			}
			if ($percentFull > 10)
			{
				$image_howfull = '25percent_full';
			}
			if ($percentFull > 40)
			{
				$image_howfull = 'half_full';
			}
			if ($percentFull > 65)
			{
				$image_howfull = 'almost_full';
			}
			if ($percentFull >= 100)
			{
				$image_howfull = 'full';
			}

			if ($dayItem['isPrivate'])
			{
				$alt_text_base = "Private Session";
				$full_session_text = "Full Private Session";

				if ($dayItem['isSpecialEvent'])
				{
					$alt_text_base = "Made for You - Private Session";
					$full_session_text = "Full Session - Made for You";
				}

				if ($dayItem['isTODD'])
				{
					$alt_text_base = "Taste of Dream Dinners - Private Session";
					$full_session_text = "Full Session - Taste of Dream Dinners";
				}

				if ($dayItem['dreamTaste'])
				{
					$alt_text_base = "Meal Prep Workshop - Private Session";
					$full_session_text = "Full Session - Meal Prep Workshop";
				}

				if ($dayItem['fundraiserEvent'])
				{
					$alt_text_base = "Fundraiser Event - Private Session";
					$full_session_text = "Full Session - Fundraiser Event";
				}


				if ($dayItem['isOpen'] || $isDirect)
				{
					if ($isDirect)
					{
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
							$sessionAction = 'javascript:onRescheduleClick(' . $id . ', \'' . CTemplate::dateTimeFormat($date . ' ' . $dayItem['time']) . '\', ' . ($is_discounted ? '1' : '0') . ', ' . $ctl_message . ')';
						}

						$hilight = ' ';
						if ($dayItem['isSelected'])
						{
							$hilight = ' style="background-color: #eeeeff; border:1px solid black;" ';
							$spotsText = "This is the guest's current session";
						}

						$contents = $dayItemTypeNote . ' <a class="' . strtolower($dayItem['publish_state']) . '" ' . $hilight . ' data-tooltip="' . $spotsText . '" href="' . $sessionAction . '">' . $timeText . '</a>' . $discountText;
					}
					else
					{
						if ($dayItem['slots'] > 0)
						{
							$hilight = ' ';
							$contents = $dayItemTypeNote . ' <a ' . $hilight . ' data-tooltip="' . $alt_text_base . '" href="javascript:onPrivateSessionClick(\'' . $date . '\', ' . $id . ')">' . $timeText . '</a>' . $discountText;
						}
						else
						{
							$contents = $dayItemTypeNote . ' <span style="color: #909090;" data-tooltip="' . $full_session_text . '">' . $timeText . '</span> ' . $discountText;
						}
					}
				}
				else
				{
					$contents = $dayItemTypeNote . ' <span style="color: #909090;" data-tooltip="Closed ' . $alt_text_base . '">' . $timeText . '</span> ' . $discountText;
				}
			}
			else if ((!$dayItem['isOpen']))
			{
				if ($dayItem['isSelected'])
				{
					$sessionAction = 'javascript:dd_message({title:\'Alert\', message:\'This is the session that is currently booked.\'});';
				}
				else
				{
					$sessionAction = 'javascript:onRescheduleClick(' . $id . ', \'' . CTemplate::dateTimeFormat($date . ' ' . $dayItem['time']) . '\', ' . ($is_discounted ? '1' : '0') . ', ' . $ctl_message . ')';
				}

				$hilight = " ";
				if ($dayItem['isSelected'])
				{
					$hilight = ' style="background-color: #eeeeff; border:1px solid black" ';
				}

				if ($isDirect)
				{

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
						$spotsText = "This is the guest's current session";
					}

					$contents = '<img src="' . ADMIN_IMAGES_PATH . '/calendar/past.gif" style="float:right;" />' . $dayItemTypeNote . '<span style="color: #909090;" data-tooltip="Session Closed">';
					$contents .= '<a id="sessionlink' . $id . '" class="' . strtolower($dayItem['publish_state']) . '" ' . $hilight . ' title="' . $spotsText . '" href="' . $sessionAction . '">' . $timeText . '</a> ' . $discountText . '</span>';
				}
				else
				{
					$contents = $dayItemTypeNote . '<span style="color: #909090;" data-tooltip="Session Closed"> ' . $timeText . $discountText . '</span>';
				}
			}
			else if ($isDirect || ($dayItem['slots'] > 0)) //remaining slots
			{
				if ($dayItem['dreamTaste'] || $dayItem['fundraiserEvent'])
				{
					if ($dayItem['slots'] < 0)
					{
						$spotsText = "Overbooked by " . (0 - $dayItem['slots']);
						if ($dayItem['num_rsvps'] > 0)
						{
							$numOrders = $dayItem['capacity'] - ($dayItem['slots'] + $dayItem['num_rsvps']);
							$spotsText .= "(" . $numOrders . " Orders, " . $dayItem['num_rsvps'] . " RSVPs)";
						}
					}
					else
					{
						$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";
						if ($dayItem['num_rsvps'] > 0)
						{
							$numOrders = $dayItem['capacity'] - ($dayItem['slots'] + $dayItem['num_rsvps']);
							$spotsText .= "(" . $numOrders . " Orders, " . $dayItem['num_rsvps'] . " RSVPs)";
						}
					}
				}
				else
				{
					if ($dayItem['slots'] < 0)
					{
						$spotsText = "Standard slots overbooked by " . (0 - $dayItem['slots']);

						if ($dayItem['supportsIntro'])
						{
							$spotsText .= "; " . $dayItem['intro_slots'] . " of " . $dayItem['intro_capacity'] . " starter pack spots available";
						}
					}
					else
					{
						$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";
						if ($isDirect && $dayItem['supportsIntro'])
						{
							$spotsText .= "; " . $dayItem['intro_slots'] . " of " . $dayItem['intro_capacity'] . " starter pack spots available";
						}
					}
				}

				if ($dayItem['is_walk_in']){
					$timeText = 'Walk-In';
					$spotsText = "";
					if($ctl_message == "'none'"){
						$ctl_message = "'none_wi'";
					}

				}

				if ($dayItem['isSelected'])
				{
					$sessionAction = 'javascript:dd_message({title:\'Alert\', message:\'This is the session that is currently booked.\'});';
				}
				else
				{
					$sessionAction = 'javascript:onRescheduleClick(' . $id . ', \'' . CTemplate::dateTimeFormat($date . ' ' . $dayItem['time']) . '\', ' . ($is_discounted ? '1' : '0') . ', ' . $ctl_message . ')';
				}



				$imgPath = ADMIN_IMAGES_PATH . '/calendar/' . ($dayItem['isQ6'] ? '6_' : ($dayItem['is3Plan'] ? '' : '12_')) . $image_howfull . '.gif';
				$percentimgPath = '<img class="img_valign" onclick="' . $sessionAction . '" alt="' . $spotsText . '" data-tooltip="' . $spotsText . '" src="' . $imgPath . '" style="float:right;" />';
				$contents = $percentimgPath . $dayItemTypeNote;

				$hilight = " ";
				if ($dayItem['isSelected'])
				{
					$hilight = ' style="background-color:yellow; border:1px solid black" ';
					$spotsText = "This is the guest's current session";
				}



				$contents .= ' <a id="sessionlink' . $id . '" class="' . strtolower($dayItem['publish_state']) . '" ' . $hilight . ' data-tooltip="' . $spotsText . '" href="' . $sessionAction . '">' . $timeText . '</a>' . $discountText;
			}
			else //full
			{
				$percentimgPath = '<img class="img_valign" alt="Session Full" data-tooltip="Session Full" src="' . ADMIN_IMAGES_PATH . '/calendar/full.gif" style="float:right;" />';
				$contents = $percentimgPath . $dayItemTypeNote;
				$contents .= ' <span data-tooltip="Session Full" style="color:#909090;">' . $timeText . '</span>';
			}

			if ($contents)
			{
				$notesImg = '';
				if (!empty($dayItem['details']))
				{
					$dayItem['details'] = str_replace("&quot;", "&amp;quot;", $dayItem['details']);

					$notesImg = '<img src="' . ADMIN_IMAGES_PATH . '/calendar/notes.gif" data-tooltip="' . $dayItem['details'] . '" class="img_valign" />';
				}

				$contents = '<div id="sesssioncell' . $id . '">' . $contents . $notesImg . '</div>';

				$retVal [] = $contents;
			}
		}
	}

	return array(
		$retVal,
		$styleOverride
	);
}

?>