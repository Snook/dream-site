<?php
require_once 'DAO/Points_user_history.php';
require_once 'CMailHandlers.inc';

class CPointsUserHistory extends DAO_Points_user_history
{
	const REWARD_INTERVAL = 200;

	// Desired outcome is 45 days however to reduce confusion we will set to 46 days from this morning. 12:00am of the 46th produces a span of from
	//45.99 days to 45.00 days and the time of expiration will be consistent and predictable
	const DINNER_DOLLAR_EXPIRATION_DAYS = 46;

	/// events
	const ERROR_OR_EXCEPTION = 'ERROR_OR_EXCEPTION';
	const CREDIT_CONSUMED = 'CREDIT_CONSUMED';
	const CREDIT_EXPIRED = 'CREDIT_EXPIRED';
	const REFERRAL_COMPLETED = 'REFERRAL_COMPLETED';
	const SESSION_HOSTED = 'SESSION_HOSTED';
	const SESSION_ATTENDED_FOR_MENU = 'SESSION_ATTENDED_FOR_MENU';
	const BIRTHDAY_MONTH = 'BIRTHDAY_MONTH';
	const REWARD_CREDIT = 'REWARD_CREDIT';
	const PHYSICAL_REWARD_RECEIVED = 'PHYSICAL_REWARD_RECEIVED';
	const ACHIEVEMENT_AWARD = 'ACHIEVEMENT_AWARD';
	const MY_MEALS_RATED = 'MY_MEALS_RATED';
	const ORDER_RESCHEDULED = 'ORDER_RESCHEDULED';
	const ORDER_EDITED = 'ORDER_EDITED';
	const ORDER_CANCELLED = 'ORDER_CANCELLED';
	const ORDERED = 'ORDERED';
	const ORDER_CONFIRMED = 'ORDER_CONFIRMED';
	const CONVERSION = 'CONVERSION';
	const OPT_IN = 'OPT_IN';
	const SUSPEND_MEMBERSHIP = 'SUSPEND_MEMBERSHIP';
	const REACTIVATE_MEMBERSHIP = 'REACTIVATE_MEMBERSHIP';
	const POINT_RETRACTION = 'POINT_RETRACTION';
	const OTHER = 'OTHER';
	const SOCIAL_CONNECT = 'SOCIAL_CONNECT';
	const SOCIAL_SHARING = 'SOCIAL_SHARING';

	/*
	const SOCIAL_SHARE_BADGE = 'SOCIAL_SHARE_BADGE';
	const SOCIAL_SHARE_MY_MEALS = 'SOCIAL_SHARE_MY_MEALS';
	const SOCIAL_SHARE_SESSION_STANDARD = 'SOCIAL_SHARE_SESSION_STANDARD';
	const SOCIAL_SHARE_SESSION_DREAM_TASTE = 'SOCIAL_SHARE_SESSION_DREAM_TASTE';
	const SOCIAL_SHARE_EVENT_STANDARD = 'SOCIAL_SHARE_EVENT_STANDARD';
	const SOCIAL_SHARE_EVENT_DREAM_TASTE = 'SOCIAL_SHARE_EVENT_DREAM_TASTE';
	*/

	const INITIAL_DINNER_DOLLAR_AWARD = 'INITIAL_DINNER_DOLLAR_AWARD';

	static $eventMetaData = array(
		self::ERROR_OR_EXCEPTION => array('points' => 0),
		self::CREDIT_CONSUMED => array('points' => 0),
		self::CREDIT_EXPIRED => array('points' => 0),
		self::REFERRAL_COMPLETED => array('points' => 0),
		self::SESSION_HOSTED => array('points' => 500),
		self::SESSION_ATTENDED_FOR_MENU => array('points' => 0),
		self::BIRTHDAY_MONTH => array(
			'points' => 0,
			'credit' => 10.00
		),
		self::REWARD_CREDIT => array('points' => 0),
		self::PHYSICAL_REWARD_RECEIVED => array('points' => 0),
		self::ACHIEVEMENT_AWARD => array(
			'points' => 0,
			'credit' => 10.00
		),
		self::MY_MEALS_RATED => array('points' => 5),
		self::ORDER_RESCHEDULED => array('points' => 0),
		self::ORDER_EDITED => array('points' => 0),
		self::ORDER_CANCELLED => array('points' => 0),
		self::ORDERED => array('points' => 0),
		self::ORDER_CONFIRMED => array('points' => 0),
		self::CONVERSION => array('points' => 0),
		self::OPT_IN => array('points' => 0),
		self::SUSPEND_MEMBERSHIP => array('points' => 0),
		self::REACTIVATE_MEMBERSHIP => array('points' => 0),
		self::OTHER => array('points' => 0),
		self::SOCIAL_CONNECT => array('points' => 0),
		self::SOCIAL_SHARING => array('points' => 0)
		/*,
		self::SOCIAL_SHARE_BADGE => array('points' => 0),
		self::SOCIAL_SHARE_MY_MEALS => array('points' => 0),
		self::SOCIAL_SHARE_SESSION_STANDARD => array('points' => 0),
		self::SOCIAL_SHARE_SESSION_DREAM_TASTE => array('points' => 0),
		self::SOCIAL_SHARE_EVENT_STANDARD => array('points' => 0),
		self::SOCIAL_SHARE_EVENT_DREAM_TASTE => array('points' => 0)*/
	);

	private $userIsPreferred = false;
	private $suppressCredit = false;

	private static $deferredProcessQueue = array();
	// Note: store an array here as follows:
	// $deferredProcessQueue[event_id] = array(event data))
	// event data is any data needed for deferred processing

	private static $emailQueue = array();

	static function getEventMetaData($eventID)
	{
		return self::$eventMetaData[$eventID];
	}

	// must be in order from least amount of req_points to most
	static $platePointLevelData = array(

		'not_enrolled' => array(
			'level' => 'not_enrolled',
			'rank' => 1,
			'title' => 'Not Enrolled',
			'image' => 'not_enrolled',
			'req_points' => false,
			'max_points' => false,
			'rewards' => array(
				'in_store_multiplier' => 1,
				// point multiplier
				'birthday_credit' => false,
				// dollar ammount for birthday credit
				'surprise_gift' => false,
				// number of surprise gifts
				'food_testing' => 0,
				'gift_id' => 'none',
				'old_gift_id' => array()
			),
		),
		'enrolled' => array(
			'level' => 'enrolled',
			'rank' => 1,
			'title' => 'Member',
			'image' => 'enrolled',
			'req_points' => 0,
			'max_points' => PHP_INT_MAX,
			'rewards' => array(
				'in_store_multiplier' => 1,
				'birthday_credit' => 0,
				'surprise_gift' => 0,
				'food_testing' => 0,
				'gift_id' => 'order_based',
				// placeholder
				'old_gift_id' => array()
			),
		)
	);

	private static $lastOperationResult = array();

	static function getLastOperationResult()
	{
		return self::$lastOperationResult;
	}

	static function clearLastOperationResult()
	{
		self::$lastOperationResult = array();
	}

	static function hasAttendedThirdStandardOrder($user_id, $storeObj_or_ID)
	{
		$timeZoneID = false;
		if (is_object($storeObj_or_ID))
		{
			$timeZoneID = $storeObj_or_ID->timezone_id;
		}
		else
		{
			$storeObj = new DAO();
			$storeObj->query("select timezone_id from store where id = $storeObj_or_ID");
			$storeObj->fetch();
			$timeZoneID = $storeObj->timezone_id;
		}

		$adjustedServerTime = CTimezones::getAdjustedServerTimeWithTimeZoneID($timeZoneID);
		$cutOff = date("Y-m-d H:i:s", $adjustedServerTime);

		$orderHistory = new DAO();
		$orderHistory->query("select o.id, o.type_of_order, o.is_in_plate_points_program, o.in_store_order, s.menu_id from booking b 
                            join session s on s.id = b.session_id and s.session_start < '$cutOff'
                            join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD' and o.servings_total_count > 35 and o.is_in_plate_points_program = 1
                            where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 order by s.session_start asc");

		if ($orderHistory->N >= 3)
		{
			return true;
		}

		return false;
	}

	static function isElgibleForBirthdayRewardAtEnrollment($store_id, $monthNumber, $user_id)
	{
		if (date("m") > 7 || date("Y") > 2023)
		{
			//No longer awarding DD for Birthdays
			return false;
		}
		else
		{
			$rewardObj = DAO_CFactory::create('points_user_history');
			$rewardObj->query("select id from points_user_history where user_id = $user_id and event_type = 'BIRTHDAY_MONTH' and is_deleted = 0");
			if ($rewardObj->N > 0)
			{
				return false;
			}

			if (empty($monthNumber) || !is_numeric($monthNumber))
			{
				return false;
			}

			if ($monthNumber > 12)
			{
				return false;
			}

			$adjTime = false;
			if (empty($store_id) || !is_numeric($store_id))
			{
				$adjTime = time();
			}
			else
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->query("select id, timezone_id from store where id = $store_id");
				$storeObj->fetch();
				$adjTime = CTimezones::getAdjustedServerTime($storeObj);
			}

			$curMonthNum = date("n", $adjTime);

			if ($monthNumber == $curMonthNum)
			{
				if (date("j", $adjTime) >= 15)
				{
					// with new expiration period of 1 month we want to exclude those registering after the 15th day of their Bday month
					// to reduce confusion about the short vaild period or why it is some other value.
					return false;
				}

				return true;
			}

			return false;
		}
	}

	static function getLevelDetailsByPoints($points)
	{
		if ($points === false)
		{
			$levelDetail = self::getLevelDetailsByLevel('not_enrolled');
			$nextLevelDetail = self::getLevelDetailsByLevel('enrolled');
		}
		else
		{
			foreach (self::$platePointLevelData as $level => $detail)
			{
				if ($detail['level'] == 'not_enrolled')
				{
					continue;
				}

				if ($points >= $detail['req_points'] && $points <= $detail['max_points'])
				{
					$top_number = $points - $detail['req_points'];
					$bot_number = ($detail['max_points'] + 1) - $detail['req_points'];

					$detail['percent_complete'] = floor(($top_number / $bot_number) * 100);

					$levelDetail = $detail;

					$nextLevelDetail = current(self::$platePointLevelData);

					break;
				}
			}

			// they reached the end of supported levels, set current level and next level to the last supported level
			if (empty($levelDetail))
			{
				$detail = end(self::$platePointLevelData);
				$detail['percent_complete'] = 100;

				$levelDetail = $detail;
				$nextLevelDetail = $detail;
			}
		}

		if (!isset($nextLevelDetail))
		{
			$nextLevelDetail = array('rewards' => array('in_store_multiplier' => 4));
		}

		if (!isset($levelDetail))
		{
			$levelDetail = array('rewards' => array('in_store_multiplier' => 4));
		}

		return array(
			$levelDetail,
			$nextLevelDetail
		);
	}

	static function getOrdersSequenceStatus($user_id, $focusOrder = false)
	{
		$orderHistory = new DAO();
		$orderHistory->query("select o.id, o.type_of_order, o.is_in_plate_points_program, o.in_store_order, s.menu_id from booking b 
                            join session s on s.id = b.session_id 
                            join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD' and o.servings_total_count > 35
                            where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 order by s.session_start asc");

		if ($orderHistory->N == 0)
		{
			// no orders
			return array(
				'focusOrderInOriginalStreak' => false,
				'focusOrderInFollowUpStreak' => false,
				'focusOrderStreakOrderNumber' => 0,
				'currentMenuInOriginalStreak' => false,
				'currentMenuInFollowUpStreak' => false,
				'currentMenuStreakOrderCount' => 0,
				'hasCompletedInitialStreak' => false,
				'focusOrderHasFollowUp' => false,
				'focusOrderFollowUpIsInStore' => false,
				'InitialStreakOrderCount' => false,
				'InitialStreakInStoreStatus' => false
			);
		}

		$currentMenuID = CMenu::getCurrentMenuId();

		$focusOrderStreak = false;
		$focusOrderOrderNumber = false;
		$currentMenuStreak = false;
		$currentMenuOrderNumber = false;
		$is_disqualified = false;
		$focusOrderHasFollowUp = false;
		$focusOrderFollowUpIsInStore = false;
		$InititalStrealInStoreStatus = array();

		$streakNumber = 0;
		$orderInStreakCount = 0;
		$overall_count = 0;
		$streaks = array();
		while ($orderHistory->fetch())
		{
			$overall_count++;
			$orderData = DAO::getCompressedArrayFromDAO($orderHistory);
			$orderData['is_focus'] = ($orderHistory->id == $focusOrder);
			$orderData['overall_count'] = $overall_count;

			if ($overall_count == 1)
			{
				if (!$orderData['is_in_plate_points_program'])
				{
					$is_disqualified = true;
				}

				$streakNumber = 1;
				$streaks[$streakNumber] = array();
				$lastMenuID = $orderData['menu_id'];
				$orderInStreakCount = 1;
				$streaks[$streakNumber][$orderInStreakCount] = $orderData;

				$InititalStrealInStoreStatus[1] = $orderData['in_store_order'];

				if ($orderData['menu_id'] == $currentMenuID)
				{
					$currentMenuStreak = $streakNumber;
					$currentMenuOrderNumber = 1;
				}
				if ($orderData['id'] == $focusOrder)
				{
					$focusOrderStreak = $streakNumber;
					$focusOrderOrderNumber = 1;
				}
				continue;
			}

			if ($orderData['menu_id'] == $lastMenuID)
			{
				// 2 std orders in same menu so ignore the second one
				continue;
			}
			else if ($orderData['menu_id'] == $lastMenuID + 1)
			{
				// contiguous!!
				$orderInStreakCount++;
				$lastMenuID = $orderData['menu_id'];
				$streaks[$streakNumber][$orderInStreakCount] = $orderData;

				if ($focusOrderOrderNumber > 0 && $focusOrderOrderNumber + 1 == $orderInStreakCount)
				{
					$focusOrderHasFollowUp = true;

					if ($orderData['in_store_order'])
					{
						$focusOrderFollowUpIsInStore = true;
					}
				}

				if ($streakNumber == 1)
				{
					$InititalStrealInStoreStatus[$orderInStreakCount] = $orderData['in_store_order'];
				}
			}
			else
			{
				//skipped
				$orderInStreakCount = 1;
				$lastMenuID = $orderData['menu_id'];
				$streakNumber++;
				$streaks[$streakNumber][$orderInStreakCount] = $orderData;
			}

			if ($orderData['menu_id'] == $currentMenuID)
			{
				$currentMenuStreak = $streakNumber;
				$currentMenuOrderNumber = $orderInStreakCount;
			}
			if ($orderData['id'] == $focusOrder)
			{
				$focusOrderStreak = $streakNumber;
				$focusOrderOrderNumber = $orderInStreakCount;
			}
		}

		if ($is_disqualified)
		{
			return array(
				'focusOrderInOriginalStreak' => false,
				'focusOrderInFollowUpStreak' => false,
				'focusOrderStreakOrderNumber' => 0,
				'currentMenuInOriginalStreak' => false,
				'currentMenuInFollowUpStreak' => false,
				'currentMenuStreakOrderCount' => false,
				'hasCompletedInitialStreak' => false,
				'focusOrderHasFollowUp' => false,
				'focusOrderFollowUpIsInStore' => false,
				'InitialStreakOrderCount' => false,
				'InitialStreakInStoreStatus' => false
			);
		}

		return array(
			'focusOrderInOriginalStreak' => ($focusOrderStreak == 1),
			'focusOrderInFollowUpStreak' => ($focusOrderStreak && $focusOrderStreak > 1),
			'focusOrderStreakOrderNumber' => $focusOrderOrderNumber,
			'currentMenuInOriginalStreak' => ($currentMenuStreak == 1),
			'currentMenuInFollowUpStreak' => ($currentMenuStreak && $currentMenuStreak > 1),
			'currentMenuStreakOrderCount' => $currentMenuOrderNumber,
			'hasCompletedInitialStreak' => (!empty($streaks[1]) && count($streaks[1]) > 3),
			'focusOrderHasFollowUp' => $focusOrderHasFollowUp,
			'focusOrderFollowUpIsInStore' => $focusOrderFollowUpIsInStore,
			'InitialStreakOrderCount' => count($streaks[1]),
			'InitialStreakInStoreStatus' => $InititalStrealInStoreStatus
		);
	}

	static function getLevelDetailsByLevel($level)
	{
		if (array_key_exists($level, self::$platePointLevelData))
		{
			return self::$platePointLevelData[$level];
		}

		return false;
	}

	static function getLastOperationResultAsHTML()
	{
		$retVal = "";

		$firstResult = true;
		foreach (self::$lastOperationResult as $thisResult)
		{
			if (!$firstResult)
			{
				$retVal .= "<br />";
			}

			if ($thisResult['success'])
			{
				$retVal .= "SUCCESS: " . $thisResult['message'];
			}
			else
			{
				$retVal .= "FAILED: " . $thisResult['message'];
			}

			$firstResult = false;
		}

		return $retVal;
	}

	static function getPlatePointsStatus($DAO_store, $DAO_user)
	{
		if (!is_object($DAO_store))
		{
			throw new Exception("Invalid store object passed to getPlatePointsStatus");
		}

		if (!is_object($DAO_user))
		{
			throw new Exception("Invalid user object passed to getPlatePointsStatus");
		}

		return array(
			'storeSupportsPlatePoints' => $DAO_store->supports_plate_points,
			'transitionPeriodHasExpired' => CStore::hasPlatePointsTransitionPeriodExpired($DAO_store->id),
			'userIsEnrolled' => self::userIsActiveInProgram($DAO_user),
			'userIsEligibleForDRConversion' => ($DAO_user->dream_rewards_version == 2 && $DAO_user->dream_reward_status > 0),
			'userIsPreferred' => $DAO_user->isUserPreferred(),
			'userIsNotInRewardProgram' => ($DAO_user->dream_reward_status != 1 && $DAO_user->dream_reward_status != 3),
			'userIsOnHold' => $DAO_user->dream_reward_status == 5,
			'user_has_opted_out_of_plate_points' => $DAO_user->has_opted_out_of_plate_points == 1,
			'userHasHomeStore' => !empty($DAO_user->home_store_id),
			'userAtHomeStore' => ($DAO_store->id == $DAO_user->home_store_id)
		);
	}

	static function getReceivedOrderBasedGifts($user_id)
	{
		$retVal = array();

		$eventRecord = DAO_CFactory::create('points_user_history');
		$eventRecord->query("select * from points_user_history where user_id = $user_id and event_type = 'PHYSICAL_REWARD_RECEIVED' and json_meta like '%\"level\":\"enrolled\"%'  and is_deleted = 0");

		while ($eventRecord->fetch())
		{
			$meta = json_decode($eventRecord->json_meta, true);
			$retVal[] = $meta['physical_reward']['reward_id'];
		}

		return $retVal;
	}

	/*
	    If the guests's level is enrolled we may have multiple gifts due
	    so we return an array based on this logic

	 * If a gift has been marked as delivered then always ignore
	 * Otherwise return all that should been given based on their progress
	        1 order ->   booklet and Free Chicken
	        2 orders ->  booklet, Free Chicken and Thaw bin
	        3 orders ->  booklet, Free Chicken, Thaw bin and Apron

	    Button text depends on whether follow up order was in_store or not
	*/
	static function getOrderBasedGiftData($user_id, $order_id, $streakData = false)
	{
		$retVal = array();

		if (!$streakData)
		{
			$streakData = self::getOrdersSequenceStatus($user_id, $order_id);
		}

		$received_gifts = self::getReceivedOrderBasedGifts($user_id);

		if ($streakData['focusOrderInOriginalStreak'])
		{
			for ($x = 1; $x <= $streakData['InitialStreakOrderCount']; $x++)
			{
				if ($x == 1)
				{
					if (!in_array('booklet', $received_gifts))
					{
						$retVal['booklet'] = array(
							'gift_id' => 'booklet',
							'display_str' => 'Dream Dinners Playbook',
							'orderBasedRewardOrderNumber' => 1,
							'rewardDue' => true
						);
					}

					if (!in_array('free_chicken', $received_gifts))
					{
						if ($streakData['InitialStreakOrderCount'] > 1)
						{
							if ($streakData['InitialStreakInStoreStatus'][2])
							{
								//$displayStr = 'Free Medium Chicken Dinner with 2nd in-store sign up';
								$displayStr = 'Thaw Bin with 2nd in-store sign up';
							}
							else
							{
								// $displayStr = 'Free Medium Chicken Dinner earned with 2nd order online';
								$displayStr = 'Thaw Bin earned with 2nd order online';
							}
						}
						else
						{
							$displayStr = 'Thaw Bin with 2nd in-store sign up';
							//$displayStr = 'Free Medium Chicken Dinner with 2nd in-store sign up';
						}
						$retVal['free_chicken'] = array(
							'gift_id' => 'free_chicken',
							'display_str' => $displayStr,
							'orderBasedRewardOrderNumber' => 1,
							'rewardDue' => ($streakData['InitialStreakOrderCount'] > 1)
						);
					}
				}

				if ($x == 2)
				{
					if (!in_array('thaw_bin', $received_gifts))
					{
						if ($streakData['InitialStreakOrderCount'] > 2)
						{
							if ($streakData['InitialStreakInStoreStatus'][3])
							{
								//$displayStr = 'Thaw Bin with 3rd in-store sign up';
								$displayStr = 'Free Medium Chicken Dinner with 3rd in-store sign up';
							}
							else
							{
								//$displayStr = 'Thaw Bin earned with 3rd order online';
								$displayStr = 'Free Medium Chicken Dinner earned with 3rd order online';
							}
						}
						else
						{
							//$displayStr = 'Thaw Bin with 3rd in-store sign up';
							$displayStr = 'Free Medium Chicken Dinner with 3rd in-store sign up';
						}
						$retVal['thaw_bin'] = array(
							'gift_id' => 'thaw_bin',
							'display_str' => $displayStr,
							'orderBasedRewardOrderNumber' => 2,
							'rewardDue' => ($streakData['InitialStreakOrderCount'] > 2)
						);
					}
				}

				if ($x == 3)
				{
					if (!in_array('apron', $received_gifts))
					{
						if ($streakData['InitialStreakOrderCount'] > 3)
						{
							if ($streakData['InitialStreakInStoreStatus'][4])
							{
								$displayStr = 'Apron with 4th in-store sign up';
							}
							else
							{
								$displayStr = 'Apron earned with 4th order online';
							}
						}
						else
						{
							$displayStr = 'Apron with 4th in-store sign up';
						}
						$retVal['apron'] = array(
							'gift_id' => 'apron',
							'display_str' => $displayStr,
							'orderBasedRewardOrderNumber' => 3,
							'rewardDue' => ($streakData['InitialStreakOrderCount'] > 3)
						);
					}
				}

				if ($x > 3)
				{
					break;
				}
			}
		}

		return $retVal;
	}

	static function getOrderBasedGiftDisplayString($giftID)
	{
		$retVal = "";

		switch ($giftID)
		{
			case "thaw_bin":
				$retVal = "Thaw Bin";
				break;
			case "apron":
				$retVal = "Apron";
				break;
			case "free_chicken":
				$retVal = "Free Medium Chicken Dinner";
				break;
			case "booklet":
				$retVal = "Dream Dinners Playbook";
				break;
			default:
				$retVal = false;
		}

		return $retVal;
	}

	static function getGiftDisplayString($giftID)
	{
		$retVal = "";

		if (strpos($giftID, 'optional_gift') === 0)
		{
			$gift_str = explode('_', $giftID);

			return 'Executive Rank ' . $gift_str[2] . ' Bonus Credit';
		}

		switch ($giftID)
		{
			case "thaw_bin":
				$retVal = "Thaw Bin";
				break;
			case "apron":
				$retVal = "Station Chef Apron";
				break;
			case "surprise":
				$retVal = "Sous Chef Gift Set";
				break;
			case "head_chef_lapel_pin":
				$retVal = "Head Chef Lapel Pin";
				break;
			case "new_apron":
				$retVal = "Executive Apron & Gift Set";
				break;
			case "tote_&_lapel_pin":
				$retVal = "Tote & Lapel Pin";
				break;
			case "gift_&_lapel_pin":
				$retVal = "Gift & Lapel Pin";
				break;
			case "gift_&_head_chef_lapel_pin":
				$retVal = "Gift & Lapel Pin";
				break;
			case "bakeware_&_lapel_pin":
				$retVal = "Bakeware & Lapel Pin";
				break;
			case "lunch_bag":
				$retVal = "Lunch Bag";
				break;
			case "featured_finishing_touch_side":
				$retVal = "Featured Sides & Sweets Side";
				break;
			case "water_bottle":
				$retVal = "Water Bottle";
				break;
			case "featured_finishing_touch_entree":
				$retVal = "Extended Fast Lane Meal";
				break;
			case "thermal_cooler_bag":
				$retVal = "Thermal Cooler Bag";
				break;
			default:
				$retVal = false;
		}

		return $retVal;
	}

	static function forceGuestToLevel_NoReward($level, $user_id = false, $email = false)
	{
		if (!$user_id)
		{
			$UserObjIDFetcher = new DAO();
			$UserObjIDFetcher->query("select u.id from dreamsite.user u where u.primary_email = '$email'");
			if ($UserObjIDFetcher->N == 0)
			{
				$UserObjIDFetcher2 = new DAO();
				$UserObjIDFetcher2->query("select u.id from dreamsite.user u where u.secondary_email = '$email'");
				if ($UserObjIDFetcher2->N == 0)
				{
					return 'email_not_found';
				}

				$UserObjIDFetcher2->fetch();
				$user_id = $UserObjIDFetcher2->id;
			}

			if (!$user_id)
			{
				$UserObjIDFetcher->fetch();
				$user_id = $UserObjIDFetcher->id;
			}
		}

		$UserObj = new DAO();
		$UserObj->query("select u.dream_rewards_version, u.dream_reward_status, u.primary_email from dreamsite.user u where u.id = $user_id");
		if ($UserObj->N == 0)
		{
			return 'user_ID_not_found';
		}
		$UserObj->fetch();
		if (!$email)
		{
			$email = $UserObj->primary_email;
		}

		if ($UserObj->dream_rewards_version == 3 and ($UserObj->dream_reward_status == 1 || $UserObj->dream_reward_status == 3))
		{
			$secondUserObj = new DAO();
			$secondUserObj->query("select puh.user_id, puh.total_points from dreamsite.points_user_history puh
    	        where puh.user_id = $user_id order by id desc limit 1");
			$secondUserObj->fetch();

			$currentPoints = $secondUserObj->total_points;
			$neededPoints = self::$platePointLevelData[$level]['req_points'];

			if ($currentPoints >= $neededPoints)
			{
				return 'already_at_level';
			}

			$totalPoints = $neededPoints;
			$neededPoints -= $currentPoints;

			$puhObj = DAO_CFactory::create('points_user_history');

			$metaArr = array("comments" => "Congratulations, as a CorporateCrate guest you have been automatically promoted to Station Chef.");

			$puhObj->user_id = $user_id;
			$puhObj->event_type = 'OTHER';
			$puhObj->points_allocated = $neededPoints;
			$puhObj->points_converted = $neededPoints;
			$puhObj->total_points = $totalPoints;
			$puhObj->json_meta = json_encode($metaArr);
			$puhObj->insert();

			echo $email . " was awarded $neededPoints.\r\n";

			return "success";
		}
		else
		{
			return 'not_enrolled';
		}
	}

	static function getGiftIDReceivedForLevel($levelDetail, $user_id)
	{
		$eventRecord = DAO_CFactory::create('points_user_history');
		$eventRecord->query("select * from points_user_history where event_type = 'PHYSICAL_REWARD_RECEIVED' and is_deleted = 0 and user_id = $user_id");
		while ($eventRecord->fetch())
		{
			/*note: Event PHYSICAL_REWARD_RECEIVED meta has this format:
			 array
			 physical_reward   (array)
			 level		(str - level denomination)
			 reward_id	(str - reward denomnation)
			 */

			$meta = json_decode($eventRecord->json_meta, true);

			if ($meta['physical_reward']['level'] == $levelDetail['level'])
			{
				if (!empty($meta['physical_reward']['reward_id']))
				{
					return $meta['physical_reward']['reward_id'];
				}
				else
				{
					return false;
				}
			}
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	static function userDuePhysicalRewardForLevel($levelDetail, $user_id, $orderObj = false, $streakData = false)
	{
		$userCurrentlyDueReward = true;
		$userReceivedReward = false;
		$userReceivedRewardNotes = false;

		if (empty($levelDetail['rewards']['gift_id']) || $levelDetail['rewards']['gift_id'] == 'none')
		{
			return array(
				false,
				false,
				false
			);
		}

		if ($levelDetail['level'] == 'enrolled')
		{
			$neededGifts = $streakData['InitialStreakOrderCount'] + 1;
			if ($neededGifts > 4)
			{
				$neededGifts = 4;
			}

			$received_gifts = self::getReceivedOrderBasedGifts($user_id);

			if (count($received_gifts) >= $neededGifts)
			{
				$userCurrentlyDueReward = false;
				$userReceivedReward = true;
			}

			return array(
				$userCurrentlyDueReward,
				$userReceivedReward,
				$userReceivedRewardNotes
			);
		}
		$didAchieveLevelThroughActivity = false;
		$testEvent = DAO_CFactory::create('points_user_history');
		$testEvent->query("select json_meta from points_user_history where event_type = 'ACHIEVEMENT_AWARD' and is_deleted = 0 and user_id = $user_id order by id desc");
		while ($testEvent->fetch())
		{
			/*note: Event PHYSICAL_REWARD_RECEIVED meta has this format:
			 array
				comment: string  "Achieved a new badge, Sous Chef!"
			*/
			$meta = json_decode($testEvent->json_meta, true);
			if (isset($meta['level_info']))
			{
				if ($levelDetail['level'] == 'executive_chef')
				{
					if ($levelDetail['rank'] == $meta['level_info']['rank'])
					{
						$didAchieveLevelThroughActivity = true;
						break;
					}
				}
				else if ($meta['level_info']['title'] == $levelDetail['title'])
				{
					$didAchieveLevelThroughActivity = true;
					break;
				}
			}
			else if (isset($meta['comments']))
			{
				$arr = explode(",", $meta['comments']);
				$level = trim($arr[1], " !");
				if ($levelDetail['title'] == $level)
				{
					$didAchieveLevelThroughActivity = true;
					break;
				}
			}
		}
		if (!$didAchieveLevelThroughActivity && $levelDetail['level'] != 'chef')
		{//note: enrolled level is handled above
			return array(
				false,
				false,
				false
			);
		}

		$eventRecord = DAO_CFactory::create('points_user_history');
		$eventRecord->query("select * from points_user_history where event_type = 'PHYSICAL_REWARD_RECEIVED' and is_deleted = 0 and user_id = $user_id");

		while ($eventRecord->fetch())
		{
			/*note: Event PHYSICAL_REWARD_RECEIVED meta has this format:
			array
				physical_reward   (array)
					level		(str - level denomination)
					reward_id	(str - reward denomnation)
			 */

			$meta = json_decode($eventRecord->json_meta, true);

			if ($levelDetail['level'] == 'executive_chef')
			{
				if (isset($meta['physical_reward']['reward_id']) && ($meta['physical_reward']['reward_id'] == $levelDetail['rewards']['gift_id']) || in_array($meta['physical_reward']['reward_id'], $levelDetail['rewards']['old_gift_id']))
				{
					$userCurrentlyDueReward = false;
					$userReceivedReward = true;
				}
			}
			else
			{
				if (isset($meta['physical_reward']['level']) && $meta['physical_reward']['level'] == $levelDetail['level'])
				{
					$userCurrentlyDueReward = false;
					$userReceivedReward = true;
				}
			}
		}

		// check coupon_expire
		if (!empty($levelDetail['rewards']['coupon_expire']))
		{
			$PUH = DAO_CFactory::create('points_user_history');
			$PUH->query("SELECT
					timestamp_created
					FROM `points_user_history`
					WHERE user_id = '" . $user_id . "'
					AND event_type = '" . CPointsUserHistory::ACHIEVEMENT_AWARD . "'
					AND json_meta LIKE '%" . $levelDetail['level'] . "%'
					AND is_deleted = '0'
					ORDER BY timestamp_created DESC
					LIMIT 1");

			if (!$PUH->fetch())
			{
				// No achievement award found, try looking to see if the user converted into the level
				$PUH = DAO_CFactory::create('points_user_history');
				$PUH->query("SELECT
						timestamp_created
						FROM `points_user_history`
						WHERE user_id = '" . $user_id . "'
						AND event_type = '" . CPointsUserHistory::CONVERSION . "'
						AND json_meta LIKE '%" . $levelDetail['level'] . "%'
						AND is_deleted = '0'
						ORDER BY timestamp_created DESC
						LIMIT 1");

				$PUH->fetch();
			}

			if (!empty($PUH->N))
			{
				$date_expired = date("Y-m-d H:i:s", strtotime($levelDetail['rewards']['coupon_expire'], strtotime($PUH->timestamp_created)));

				if (time() > strtotime($date_expired))
				{
					$userCurrentlyDueReward = false;
					$userReceivedReward = false;
					$userReceivedRewardNotes = $levelDetail['title'] . ' coupon expired on ' . CTemplate::dateTimeFormat($date_expired);
				}
			}
		}

		return array(
			$userCurrentlyDueReward,
			$userReceivedReward,
			$userReceivedRewardNotes
		);
	}

	/**
	 * @throws Exception
	 */
	static function wind_down_PlatePoints(): bool
	{
		// No rewards after Nov 1 2024
		if (CTemplate::formatDateTime(timeStamp: TIMENOW) >= '2024-11-01 00:00:00')
		{
			return true;
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	static function userIsActiveInProgram($userObj): bool
	{
		if (($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3) && $userObj->dream_rewards_version == 3)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param        $user_id
	 * @param false  $limit can be comma seperated to return a limit range
	 * @param string $sort
	 *
	 * @return array|false
	 * @throws Exception
	 */
	static function getHistory($user_id, $limit = false, $sort = 'DESC')
	{
		$retVal = array();

		$eventRecord = DAO_CFactory::create('points_user_history');
		$eventRecord->user_id = $user_id;
		$eventRecord->orderBy('id ' . $sort);

		if ($limit)
		{
			if (strpos($limit, ',') !== false)
			{
				$limit_arr = explode(",", $limit);
				$eventRecord->limit($limit_arr[0], $limit_arr[1]);
			}
			else
			{
				$eventRecord->limit($limit);
			}
		}

		$eventRecord->find();

		while ($eventRecord->fetch())
		{
			$thisEvent = $eventRecord->toArray();

			$thisEvent['event_title'] = ucwords(strtolower(str_replace("_", " ", $eventRecord->event_type)));
			$thisEvent['event_order_id'] = (empty($eventRecord->order_id) ? "-" : $eventRecord->order_id);
			$thisEvent['meta_array'] = json_decode($eventRecord->json_meta, true);

			$retVal[] = $thisEvent;
		}

		if (!empty($retVal))
		{
			return $retVal;
		}

		return false;
	}

	static function addToEmailQueue($email_ID, $emailData)
	{
		if (array_key_exists($email_ID, self::$emailQueue))
		{
			// it possible to receive more than one credit reward email request - simply add the credit amount of the
			// existing request
			if ($email_ID == 'credit_reward')
			{
				self::$emailQueue[$email_ID]['amount'] += $emailData['amount'];
			}
			else
			{
				throw new Exception('Duplicate email request.');
			}
		}
		else
		{
			self::$emailQueue[$email_ID] = $emailData;
		}
	}

	static private function addToDeferredProcessQueue($inEventName, $inEventData)
	{
		self::$deferredProcessQueue[$inEventName] = $inEventData;
	}

	/**
	 * @throws Exception
	 */
	static function handleEvent($user_id_or_obj, $event, $meta_array = false, $orderObj = null)
	{
		if (CPointsUserHistory::wind_down_PlatePoints())
		{
			return false;
		}

		if (!empty($orderObj->menu_id) && $orderObj->menu_id <= 278)
		{
			return false;
		}

		$eventRecord = DAO_CFactory::create('points_user_history');

		if (is_object($user_id_or_obj) && get_class($user_id_or_obj) == 'CUser')
		{
			$userObj = $user_id_or_obj;
		}
		else if (is_numeric($user_id_or_obj) && $user_id_or_obj == CUser::getCurrentUser()->id)
		{
			$userObj = CUser::getCurrentUser();
		}
		else
		{
			$userObj = DAO_CFactory::create('user');
			$userObj->id = $user_id_or_obj;
			if (!$userObj->find(true))
			{
				throw new Exception('user not found in CPointsUserHistory::handleEvent: ' . $userObj->id);
			}
		}

		// need to know in advance what level they are at
		$userObj->getPlatePointsSummary();

		// if the user is not in, do nothing
		// if in the future we allow opt-out, suggest setting any existing credits to consumed/deleted at time of opt-out
		if ($event != self::OPT_IN && $event != self::CONVERSION && $event != self::REACTIVATE_MEMBERSHIP && !self::userIsActiveInProgram($userObj))
		{
			return false;
		}

		// Preferred User Check
		$userIsPreferred = false;
		if ($userObj->isUserPreferred())
		{
			$eventRecord->userIsPreferred = true;
			$userIsPreferred = true;
		}

		$result = false;

		switch ($event)
		{
			case self::CONVERSION:
				{
					$result = $eventRecord->handleConversionEvent($userObj);
				}
				break;

			case self::OPT_IN:
				{
					$hasHomeStore = !(empty($userObj->home_store_id));

					$tranistionHasExpired = false;
					if ($hasHomeStore)
					{
						$tranistionHasExpired = CStore::hasPlatePointsTransitionPeriodExpired($userObj->home_store_id);
					}

					if ($userIsPreferred && $hasHomeStore && !$tranistionHasExpired)
					{
						$result = $eventRecord->handleConversionEvent($userObj);
					}
					else if ($userObj->dream_rewards_version < 3 && $userObj->dream_reward_status > 0 && $hasHomeStore && !$tranistionHasExpired)
					{
						// Note: 4/8/2014 CES: Also allow deactivated guests
						$result = $eventRecord->handleConversionEvent($userObj);
					}
					else
					{
						$result = $eventRecord->handleOptInEvent($userObj);
					}
				}
				break;

			case self::MY_MEALS_RATED:
				{
					$result = $eventRecord->handleMyMealsRatedEvent($userObj, $meta_array);
				}
				break;

			case self::SOCIAL_CONNECT:
				{
					$result = $eventRecord->handleSocialConnectEvent($userObj, $meta_array);
				}
				break;

			case self::SOCIAL_SHARING:
				{
					$result = $eventRecord->handleSocialSharingEvent($userObj, $meta_array);
				}
				break;

			case self::ORDERED:
				{
					$result = $eventRecord->handleOrderPlacedEvent($userObj, $orderObj);
				}
				break;

			case self::ORDER_CONFIRMED:
				{
					$result = $eventRecord->handleOrderConfirmedEvent($userObj, $orderObj);
				}
				break;

			case self::ORDER_CANCELLED:
				{
					$result = $eventRecord->handleOrderCancelledEvent($userObj, $orderObj, $meta_array);
				}
				break;

			case self::ORDER_EDITED:
				{
					$result = $eventRecord->handleOrderEditedEvent($userObj, $orderObj, $meta_array);
				}
				break;

			case self::ORDER_RESCHEDULED:
				{
					$result = $eventRecord->handleOrderRescheduledEvent($userObj, $orderObj);
				}
				break;

			case self::REFERRAL_COMPLETED:
				{
					$result = $eventRecord->handleReferralCompleted($userObj, $meta_array);
				}
				break;

			case self::CREDIT_EXPIRED:
				{
					$result = $eventRecord->handleCreditExpired($userObj, $meta_array);
				}
				break;

			case self::BIRTHDAY_MONTH:
				{
					$result = $eventRecord->handleBirthdayMonthAward($userObj, $meta_array);
				}

				break;

			case self::PHYSICAL_REWARD_RECEIVED:
				{
					$result = $eventRecord->handlePhysicalRewardReceived($userObj, $meta_array);
				}
				break;

			case self::SESSION_HOSTED:
				{
					$result = $eventRecord->handleRewardDreamTasteHost($userObj, $meta_array);
				}
				break;
			case self::SUSPEND_MEMBERSHIP:
				{
					$result = $eventRecord->handleSuspendMembership($userObj, $meta_array);
				}
				break;
			case self::REACTIVATE_MEMBERSHIP:
				{
					$result = $eventRecord->handleReactivateMembership($userObj, $meta_array);
				}
				break;

			case self::OTHER:
				{
					$result = $eventRecord->handleOtherEvent($userObj, $meta_array);
				}
				break;
		}

		// Deferred Events cannot return data - they happen as a
		// side effect and are inserted after the main event

		if (!empty(self::$deferredProcessQueue))
		{
			foreach (self::$deferredProcessQueue as $thisDeferredEvent => $eventData)
			{
				switch ($thisDeferredEvent)
				{
					case self::ACHIEVEMENT_AWARD:
						{
							$deferredEventObj = DAO_CFactory::create('points_user_history');
							$deferredEventObj->userIsPreferred = $userIsPreferred;

							unset(self::$deferredProcessQueue[$thisDeferredEvent]);

							$deferredEventObj->handleAchievementAwardEvent($eventData, $userObj);
						}
						break;
				}
			}
		}

		// determine unique emails needed from requests and send them
		if (!empty(self::$emailQueue))
		{
			// first gather any that can be combined
			$final_list = array();

			$hasWelcomeEMail = false;
			$hasAchievementAward = false;
			foreach (self::$emailQueue as $id => $data)
			{
				$final_list[$id] = $data;
				if ($id == 'welcome')
				{
					$hasWelcomeEMail = true;
				}

				if ($id == 'achievement')
				{
					$hasAchievementAward = true;
				}
			}

			// clear  the email queue
			self::$emailQueue = array();

			if ($hasWelcomeEMail)
			{
				// merge any credit_rewards and level achievements to the welcome emai;

				$final_list['welcome']['user_is_preferred'] = $userIsPreferred;

				foreach ($final_list as $id => $data)
				{
					$final_list[$id] = $data;
					if ($id == 'credit_reward')
					{
						unset($final_list[$id]);
						$final_list['welcome']['hasCreditReward'] = true;
					}

					if ($id == 'achievement')
					{
						unset($final_list[$id]);
						$final_list['welcome']['hasAchievement'] = true;
					}
				}
			}
			else if ($hasAchievementAward)
			{
				foreach ($final_list as $id => $data)
				{
					$final_list[$id] = $data;
					if ($id == 'credit_reward')
					{
						unset($final_list[$id]);
						$final_list['achievement']['hasCreditReward'] = true;
					}
				}
			}

			foreach ($final_list as $id => $data)
			{
				$data['user_is_preferred'] = $userIsPreferred;

				switch ($id)
				{
					case "credit_reward":

						$data['total_available_credit'] = CPointsCredits::getAvailableCreditForUser($data['userObj']->id);
						// Now only logs that an email would have been sent
						plate_points_mail_handlers::sendPlatePointsCreditRewardEmail($data);
						break;
					case 'welcome':
						$data['total_available_credit'] = CPointsCredits::getAvailableCreditForUser($data['userObj']->id);
						$data['program_summary'] = $data['userObj']->getPlatePointsSummary();

						plate_points_mail_handlers::sendPlatePointsWelcomeEmail($data);
						break;

					case 'achievement':
						$data['total_available_credit'] = CPointsCredits::getAvailableCreditForUser($data['userObj']->id);
						$data['program_summary'] = $data['userObj']->getPlatePointsSummary();
						// Now only logs that an email would have been sent
						plate_points_mail_handlers::sendPlatePointslevelUpEmail($data);

						break;
				}
			}
		}

		return $result;
	}

	static function userSocialConnectAwarded($user_id)
	{
		// this can only be rewarded once

		$historyObj = DAO_CFactory::create('points_user_history');
		$historyObj->user_id = $user_id;
		$historyObj->event_type = self::SOCIAL_CONNECT;

		if ($historyObj->find(true))
		{
			return true;
		}

		return false;
	}

	static function getCurrentPointsLevel($user_id)
	{
		$historyObj = DAO_CFactory::create('points_user_history');
		$historyObj->query("select sum(points_allocated) as total_points_allocated from points_user_history
				where user_id = $user_id and is_deleted = 0");
		$historyObj->fetch();

		return $historyObj->total_points_allocated;
	}

	static function getOptInDateTime($user_id)
	{
		$historyObj = DAO_CFactory::create('points_user_history');
		$historyObj->query("select timestamp_created as opt_in_date from points_user_history
				where user_id = $user_id and is_deleted = 0 and event_type = 'OPT_IN'");
		if ($historyObj->fetch())
		{
			return $historyObj->opt_in_date;
		}

		return false;
	}

	static function getPendingPoints($user_id, $currentPointsLevel)
	{
		$pendingTotal = 0;

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("select o.id, o.grand_total - (o.subtotal_all_taxes + o.subtotal_service_fee + o.subtotal_delivery_fee + o.subtotal_products) as basis, s.session_start, o.in_store_order from booking b
							join orders o on o.id = b.order_id and o.points_are_actualized = 0 and o.is_in_plate_points_program = 1
							join session s on s.id = b.session_id
							where b.user_id = $user_id and b.status = 'ACTIVE' and DATEDIFF(DATE(now()),DATE(s.session_start)) < 7
							order by s.session_start");

		while ($bookingObj->fetch())
		{
			if ($bookingObj->basis < 0)
			{
				$bookingObj->basis = 0;
			}

			$orderObj = DAO_CFactory::create('orders');
			$orderObj->id = $bookingObj->id;
			$orderObj->find(true);

			$thisTotal = self::getPointsForOrder($currentPointsLevel, $orderObj, $bookingObj->basis, $bookingObj->in_store_order);

			$currentPointsLevel += $thisTotal;
			$pendingTotal += $thisTotal;
		}

		return $pendingTotal;
	}

	static function getPointsUntilNextCredit($user_id)
	{
		$historyObj = DAO_CFactory::create('points_user_history');
		$historyObj->query("select sum(points_allocated) as total_allocated, sum(points_converted) total_converted from points_user_history where user_id = $user_id and is_deleted = 0 group by user_id");
		if ($historyObj->fetch())
		{
			$unconverted = $historyObj->total_allocated - $historyObj->total_converted;
			if ($unconverted > 200)
			{
				$retVal = 0;
			}
			else
			{
				$retVal = 200 - $unconverted;
			}

			return $retVal;
		}

		return 0;
	}

	function currentPlatePointsStatus($userObj)
	{
		list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints(floor($this->total_points));

		return array(
			'user_id' => $userObj->id,
			'lifetime_points' => floor($this->total_points),
			'pending_points' => floor(self::getPendingPoints($userObj->id, floor($this->total_points))),
			'current_level' => $levelDetail,
			'next_level' => $nextLevelDetail
		);
	}

	function setTotalPoints($suppressAchievementReward = false)
	{
		$historyObj = DAO_CFactory::create('points_user_history');
		$historyObj->query("select sum(points_allocated) as total_points_allocated from points_user_history
												where user_id = {$this->user_id} and is_deleted = 0");
		$historyObj->fetch();

		$this->total_points = (isset($historyObj->total_points_allocated) ? $historyObj->total_points_allocated : 0);

		// get current level

		list($currentLevelDetail, $currentNextLevelDetail) = self::getLevelDetailsByPoints($this->total_points);
		$this->total_points += $this->points_allocated;
		list($nextlevelDetail, $nextNextLevelDetail) = self::getLevelDetailsByPoints($this->total_points);
		if ($currentLevelDetail['level'] == 'executive_chef')
		{
			if (!$suppressAchievementReward && $currentLevelDetail['rank'] != $nextlevelDetail['rank'])
			{
				self::addToDeferredProcessQueue(self::ACHIEVEMENT_AWARD, array(
					'current_level' => $nextlevelDetail,
					'next_level' => $nextNextLevelDetail,
					'user_id' => $this->user_id
				));
			}
		}
		else
		{
			if (!$suppressAchievementReward && $currentLevelDetail['level'] != $nextlevelDetail['level'])
			{
				self::addToDeferredProcessQueue(self::ACHIEVEMENT_AWARD, array(
					'current_level' => $nextlevelDetail,
					'next_level' => $nextNextLevelDetail,
					'user_id' => $this->user_id
				));
			}
		}

		if ($this->userIsPreferred)
		{
			$this->points_converted = $this->points_allocated;
		}
	}

	static function getPreferredUserConversionData($userObj)
	{
		$retVal = array();

		$orders = DAO_CFactory::create('orders');
		$orders->query("select o.id, o.grand_total, o.in_store_order, s.session_start, o2.order_id as in_store, (o.grand_total - o.subtotal_all_taxes) as points_basis from booking b
				join session s on b.session_id = s.id
				join orders o on o.id = b.order_id
				left join orders_digest o2 on o2.in_store_trigger_order = o.id and o2.is_deleted = 0
				where b.status = 'ACTIVE' and b.user_id = {$userObj->id} and b.is_deleted = 0 and o.timestamp_created > '2008-09-01 00:00:00' and o.user_preferred_discount_total > 0
				order by s.session_start");

		$totalSpend = 0;
		$numOrders = 0;
		$pointsAward = 0;

		while ($orders->fetch())
		{
			$numOrders++;
			$totalSpend += $orders->grand_total;

			$newPoints = self::getPointsForOrder($pointsAward, null, $orders->points_basis, !empty($orders->in_store));

			$pointsAward += $newPoints;
		}

		$retVal['total_spend'] = $totalSpend;
		$retVal['points_award'] = $pointsAward;
		$retVal['points_award_display_value'] = $pointsAward + self::$eventMetaData[self::OPT_IN]['points'];
		$retVal['num_orders'] = $numOrders;

		list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($retVal['points_award_display_value']);
		$retVal['level_details'] = $levelDetail;

		$retVal['credit_award'] = 0;
		$retVal['credit_award_display_value'] = 0;

		return $retVal;
	}

	// Note: if any other $userObj fields are needed check client code to be sure they are providing the added fields
	static function getDR2ConversionData($userObj)
	{
		$retVal = array();
		$orders = DAO_CFactory::create('orders');
		$orders->query("select o.id, o.grand_total, o.in_store_order, s.session_start, o.in_store_order as in_store, (o.grand_total - o.subtotal_all_taxes) as points_basis from booking b
							join session s on b.session_id = s.id
							join orders o on o.id = b.order_id
							where b.status = 'ACTIVE' and b.user_id = {$userObj->id} and b.is_deleted = 0 and o.dream_rewards_level > 0
							order by s.session_start");

		$totalSpend = 0;
		$numOrders = 0;
		$pointsAward = 0;

		while ($orders->fetch())
		{
			$numOrders++;
			$totalSpend += $orders->grand_total;

			$newPoints = self::getPointsForOrder($pointsAward, null, $orders->points_basis, !empty($orders->in_store));

			$pointsAward += $newPoints;
		}

		$retVal['total_spend'] = $totalSpend;
		$retVal['points_award'] = $pointsAward;
		$retVal['points_award_display_value'] = $pointsAward + self::$eventMetaData[self::OPT_IN]['points'];
		$retVal['num_orders'] = $numOrders;

		list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($retVal['points_award_display_value']);
		$retVal['level_details'] = $levelDetail;
		$retVal['next_level_details'] = $nextLevelDetail;
		if ($userObj->id == 449045)
		{
			$retVal['points_award'] = 23200;
			$retVal['points_award_display_value'] = $retVal['points_award'] + self::$eventMetaData[self::OPT_IN]['points'];
			$retVal['num_orders'] = $numOrders;
			list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($retVal['points_award_display_value']);
			$retVal['level_details'] = $levelDetail;
			$retVal['next_level_details'] = $nextLevelDetail;
		}
		else if ($userObj->id == 1)
		{
			$retVal['points_award'] = 10;
			$retVal['points_award_display_value'] = $retVal['points_award'] + self::$eventMetaData[self::OPT_IN]['points'];
			$retVal['num_orders'] = $numOrders;
			list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($retVal['points_award_display_value']);
			$retVal['level_details'] = $levelDetail;
			$retVal['next_level_details'] = $nextLevelDetail;
		}
		$creditAward = 0;
		$pointsAward += self::$eventMetaData[self::OPT_IN]['points'];
		// CES 4/23/14 per marketing, include the 500 point enrollment award when determining the credit.

		if ($pointsAward < 1000)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 2500)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 5000)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 10000)
		{
			$creditAward = 10;
		}
		else if ($pointsAward < 15000)
		{
			$creditAward = 10;
		}
		else if ($pointsAward < 20000)
		{
			$creditAward = 10;
		}
		else
		{
			$creditAward = 20;
		}

		$retVal['credit_award'] = $creditAward;
		$retVal['credit_award_display_value'] = $creditAward + 10;

		return $retVal;
	}

	// Note: if any other $userObj fields are needed check client code to be sure they are providing the added fields
	static function getMFYConversionData($userObj)
	{
		$retVal = array();
		$orders = DAO_CFactory::create('orders');
		$orders->query("select o.id, o.grand_total, o.in_store_order, s.session_start, o.in_store_order as in_store, (o.grand_total - o.subtotal_all_taxes) as points_basis from booking b
				join session s on b.session_id = s.id
				join orders o on o.id = b.order_id
				where b.status = 'ACTIVE' and b.user_id = {$userObj->id} and b.is_deleted = 0 and o.timestamp_created > '2008-09-01 00:00:00'
				order by s.session_start");
		$totalSpend = 0;
		$numOrders = 0;
		$pointsAward = 0;
		while ($orders->fetch())
		{
			$numOrders++;
			$totalSpend += $orders->grand_total;
			$newPoints = self::getPointsForOrder($pointsAward, null, $orders->points_basis, !empty($orders->in_store));
			$pointsAward += $newPoints;
		}
		$retVal['total_spend'] = $totalSpend;
		$retVal['points_award'] = $pointsAward;
		$retVal['points_award_display_value'] = $pointsAward + self::$eventMetaData[self::OPT_IN]['points'];
		$retVal['num_orders'] = $numOrders;
		list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($retVal['points_award_display_value']);
		$retVal['level_details'] = $levelDetail;
		$retVal['next_level_details'] = $nextLevelDetail;
		$creditAward = 0;
		$pointsAward += self::$eventMetaData[self::OPT_IN]['points'];
		// CES 4/23/14 per marketing, include the 500 point enrollment award when determining the credit.
		if ($pointsAward < 1000)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 2500)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 5000)
		{
			$creditAward = 0;
		}
		else if ($pointsAward < 10000)
		{
			$creditAward = 10;
		}
		else if ($pointsAward < 15000)
		{
			$creditAward = 10;
		}
		else if ($pointsAward < 20000)
		{
			$creditAward = 10;
		}
		else
		{
			$creditAward = 20;
		}
		$retVal['credit_award'] = $creditAward;
		$retVal['credit_award_display_value'] = $creditAward + 10;

		return $retVal;
	}

	static function leveledUpSinceLastSession($user_id, $session_start)
	{
		$levelUpDetails = array();
		$levelUpDetails['leveled_up_since_last_session'] = false;
		$levelUpDetails['leveled_up_details'] = false;

		$booking = DAO_CFactory::create('booking');
		$booking->query("SELECT
					s.session_start AS last_session,
					IF(puh.id, TRUE, FALSE) AS leveled_up_since_last_session,
					puh.json_meta
					FROM booking AS b
					INNER JOIN `session` AS s ON s.id = b.session_id
					LEFT JOIN (SELECT * FROM points_user_history AS puh WHERE puh.user_id = '" . $user_id . "' AND puh.event_type = '" . CPointsUserHistory::ACHIEVEMENT_AWARD . "' AND puh.is_deleted = '0' ORDER BY puh.id DESC LIMIT 1) AS puh ON puh.user_id = b.user_id AND puh.timestamp_created > s.session_start
					WHERE b.user_id = '" . $user_id . "'
					AND b.`status` = 'ACTIVE'
					AND s.session_start < '" . $session_start . "'
					AND b.is_deleted = '0'
					AND s.is_deleted = '0'
					ORDER BY s.session_start DESC
					LIMIT 1");

		if ($booking->fetch())
		{
			$levelUpDetails['leveled_up_since_last_session'] = $booking->leveled_up_since_last_session;
			$levelUpDetails['leveled_up_details'] = json_decode($booking->json_meta);
		}

		return $levelUpDetails;
	}

	/*
	 *  Either pass in an order object or both the points_basis and in-store flag
	 */
	static function getPointsForOrder($currentPointsLevel, $OrderObj = null, $PointsBasis = 0, $isInStore = false)
	{
		if (!is_object($OrderObj) && is_numeric($OrderObj))
		{
			$order_id = $OrderObj;

			$OrderObj = DAO_CFactory::create('orders');
			$OrderObj->id = $order_id;
			$OrderObj->find(true);
		}

		if (!empty($OrderObj) && !$OrderObj->is_in_plate_points_program)
		{
			return 0;
		}

		$isEligible = false;

		if ($OrderObj)
		{
			if (!empty($OrderObj->id))
			{
				$tempEvent = DAO_CFactory::create('points_user_history');
				$tempEvent->query("select id, points_allocated from points_user_history where event_type = 'ORDER_CONFIRMED' and is_deleted = 0 and order_id = {$OrderObj->id}");
				if ($tempEvent->N > 0)
				{
					$tempEvent->fetch();

					return $tempEvent->points_allocated;
				}
			}

			$PointsBasis = $OrderObj->grand_total - ($OrderObj->subtotal_all_taxes + $OrderObj->subtotal_service_fee + $OrderObj->subtotal_delivery_fee + $OrderObj->subtotal_products);
			if ($PointsBasis < 0)
			{
				$PointsBasis = 0;
			}
			$isEligible = $OrderObj->is_multiplier_eligible;
		}

		// check for auxiliary events
		if (!empty($isEligible))
		{
			$multiplier = 2;

			return floor($PointsBasis * $multiplier);
		}

		return floor($PointsBasis);
	}

	function handleSuspendMembership($userObj, $meta_array)
	{
		// prerequisites
		// 1)  already in program

		if (!self::userIsActiveInProgram($userObj))
		{
			$this->handleProgramExceptionEvent($userObj, 'Attempt to suspend inactive membership');
			self::$lastOperationResult[] = array(
				'success' => false,
				'message' => 'The guest is not enrolled in the PLATEPOINTS program.'
			);

			return false;
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::SUSPEND_MEMBERSHIP;
		$this->points_allocated = self::$eventMetaData[self::SUSPEND_MEMBERSHIP]['points'];
		$this->points_converted = 0;

		$this->json_meta = json_encode(array(
			'comments' => 'Guest placed on hold in PLATEPOINTS rewards program'
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleSuspendMemberhip.');
		}

		// TODO
		// ??
		//	self::addToEmailQueue('suspend', array('userObj' => $userObj));

		// convert any eligible points
		$this->convertPointsToCredit($userObj);

		$userObj->dream_reward_status = 5;

		// Can't use update here since a check for email change will trigger a failed attempt to update the user_login table
		//$userObj->update($oldUser);
		$tempUserObj = DAO_CFactory::create('user');
		$tempUserObj->query("update user set dream_reward_status = 5 where id = {$userObj->id}");

		self::$lastOperationResult[] = array(
			'success' => true,
			'message' => 'The guest was successfully placed on hold.'
		);

		// TODO: confirm any eligible orders

		return true;
	}

	function handleReactivateMembership($userObj, $meta_array)
	{
		// prerequisites
		// 1)  not already in program

		if (self::userIsActiveInProgram($userObj))
		{
			$this->handleProgramExceptionEvent($userObj, 'Attempt to reactivate active membership');
			self::$lastOperationResult[] = array(
				'success' => false,
				'message' => 'The guest is already enrolled in the PLATEPOINTS program.'
			);

			return false;
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::REACTIVATE_MEMBERSHIP;
		$this->points_allocated = self::$eventMetaData[self::REACTIVATE_MEMBERSHIP]['points'];
		$this->points_converted = 0;

		$this->json_meta = json_encode(array(
			'comments' => 'Reactivated into PLATEPOINTS rewards program'
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleReactivateMembership.');
		}

		// TODO
		// ??
		//	self::addToEmailQueue('suspend', array('userObj' => $userObj));

		$userObj->dream_reward_status = 3;

		// Can't use update here since a check for email change will trigger a failed attempt to update the user_login table
		//$userObj->update($oldUser);
		$tempUserObj = DAO_CFactory::create('user');
		$tempUserObj->query("update user set dream_reward_status = 3 where id = {$userObj->id}");

		self::$lastOperationResult[] = array(
			'success' => true,
			'message' => 'The guest was successfully reactivated into the PLATEPOINTS program.'
		);

		return true;
	}

	function handleRewardDreamTasteHost($userObj, $meta_array)
	{
		// prerequisites
		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::SESSION_HOSTED;
		$this->points_allocated = self::$eventMetaData[self::SESSION_HOSTED]['points'];
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		/// PLATEPOINTS enhancement ... double points if session is hosted in a specific month
		$session_id = $meta_array['session_id'];

		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->query("SELECT
 			s.id
 			FROM session AS s
 			JOIN store AS st ON st.id = s.store_id AND st.supports_plate_points_enhancements = 1
 			WHERE s.id = $session_id
 			AND MONTH (s.session_start) = 9
 			AND YEAR(s.session_start) = 2019");

		if ($sessionObj->N > 0)
		{
			$this->points_allocated = self::$eventMetaData[self::SESSION_HOSTED]['points'] * 2;
		}

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleRewardDreamTasteHost.');
		}

		$TSPObj = DAO_CFactory::create('session_properties');
		$TSPObj->query("update session_properties set points_user_history_id = {$this->id} where session_host = {$userObj->id} and session_id = {$meta_array['session_id']} and is_deleted = 0");

		return true;
	}

	function handleAchievementAwardEvent($eventData, $userObj)
	{
		// add to history
		$this->user_id = $eventData['user_id'];
		$this->event_type = self::ACHIEVEMENT_AWARD;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode(array(
			'comments' => 'Achieved a new badge, ' . $eventData['current_level']['title'] . '!',
			'level_info' => array(
				'title' => $eventData['current_level']['title'],
				'level' => $eventData['current_level']['level'],
				'rank' => (!empty($eventData['current_level']['rank']) ? $eventData['current_level']['rank'] : 0)
			)
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleAchievementAwardEvent.');
		}

		if ($eventData['current_level']['rank'] == 1)
		{
			self::addToEmailQueue('achievement', array('userObj' => $userObj));
		}

		if (!$this->userIsPreferred)
		{
			self::addNonPointsBasedCredit($eventData['user_id'], self::$eventMetaData[self::ACHIEVEMENT_AWARD]['credit'], $this->id);

			$rewardObj = DAO_CFactory::create('points_user_history');
			$rewardObj->userIsPreferred = $this->userIsPreferred;
			$rewardObj->handlePointsToCreditConversionEvent(self::$eventMetaData[self::ACHIEVEMENT_AWARD]['credit'], $eventData['user_id'], ', ' . $eventData['current_level']['title'] . ' badge reward!');
		}

		// update user_digest
		CUser::updateUserDigest($eventData['user_id'], 'last_achievement_achieved_id', $this->id);
	}

	static function addNonPointsBasedCredit($user_id, $amount, $event_record_id, $expiration_date = false)
	{
		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->dollar_value = $amount;
		$creditObj->user_id = $user_id;
		if (!$expiration_date)
		{
			$expiration_date = mktime(0, 0, 0, date("n"), date("j") + CPointsUserHistory::DINNER_DOLLAR_EXPIRATION_DAYS, date("Y"));
		}

		$creditObj->expiration_date = date("Y-m-d 03:00:00", $expiration_date);
		if (!$creditObj->insert())
		{
			throw new Exception('Error inserting creditObj in CPointsUserHistory::addNonPointsBasedCredit.');
		}

		$pointsToPointsCredits = DAO_CFactory::create('points_to_points_credits');
		$pointsToPointsCredits->points_user_history_id = $event_record_id;
		$pointsToPointsCredits->points_credit_id = $creditObj->id;
		if (!$pointsToPointsCredits->insert())
		{
			throw new Exception('Error inserting pointsToPointsCredits in CPointsUserHistory::addNonPointsBasedCredit.');
		}
	}

	function handlePhysicalRewardReceived($userObj, $meta_array)
	{
		// prerequisites
		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::PHYSICAL_REWARD_RECEIVED;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode(array(
			'physical_reward' => $meta_array
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handlePhysicalRewardReceived.');
		}

		return true;
	}

	function handleBirthdayMonthAward($userObj, $meta_array)
	{
		// prerequisites
		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not

		if ($this->userIsPreferred)
		{
			return;
		}

		if (empty($meta_array))
		{
			$userData = DAO_CFactory::create('user_data');
			$userData->query("select ud1.user_data_value as month, ud2.user_data_value as year from user_data ud1
								left join user_data ud2 on ud2.user_id = {$userObj->id} and ud2.user_data_field_id = 15 and ud2.is_deleted = 0
									where ud1.user_id = {$userObj->id} and ud1.user_data_field_id = 1 and ud1.is_deleted = 0");
			if ($userData->N > 0)
			{
				$userData->fetch();
				$meta_array = array(
					'year' => $userData->year,
					'month' => $userData->month
				);
			}
			else
			{
				$this->handleProgramExceptionEvent($userObj, 'Attempt to opt in when already in program');
				self::$lastOperationResult[] = array(
					'success' => false,
					'message' => 'The guest is already enrolled in the PLATEPOINTS program.'
				);

				return false;
			}
		}

		$expiration_date = false;
		if (isset($meta_array['year']) && is_numeric($meta_array['year']) && isset($meta_array['month']) && is_numeric($meta_array['month']))
		{
			$birthday_month_award_expire_month = $meta_array['month'] + 1;
			$year = date("Y");
			$current_month = date("m");
			if ($birthday_month_award_expire_month < $current_month)
			{
				//expiration is for next year...this case only really would have happened in December
				//for example, if guest has b-day of Jan 5
				//then the point awarded in Dec of 2022 should have an expiry Feb 15 2023, not Feb 15 2022
				$year = date("Y") + 1;
			}
			$expiration_date = mktime(3, 0, 0, $birthday_month_award_expire_month, 15, $year);
		}

		//End birthday points starting August 2023
		if ($meta_array['month'] >= 8 || $meta_array['year'] > 2023)
		{
			return false;
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::BIRTHDAY_MONTH;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleBirthdayMonthAward.');
		}

		self::addNonPointsBasedCredit($userObj->id, self::$eventMetaData[self::BIRTHDAY_MONTH]['credit'], $this->id, $expiration_date);

		$rewardObj = DAO_CFactory::create('points_user_history');
		$rewardObj->handlePointsToCreditConversionEvent(self::$eventMetaData[self::BIRTHDAY_MONTH]['credit'], $userObj->id, ', birthday month reward!', true);

		// Now only logs that an email would have been sent
		plate_points_mail_handlers::sendPlatePointsBirthdayRewardEmail(array('userObj' => $userObj));

		return true;
	}

	function handleCreditExpired($userObj, $meta_array)
	{
		// prerequisites
		// user is in program
		//if (!self::userIsActiveInProgram($userObj))
		//	return;  // quietly return if not

		// Note: the above does not apply here as credits will expire regardless of current status of the guest

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::CREDIT_EXPIRED;
		$this->json_meta = json_encode(array(
			'credit list' => $meta_array
		));

		$this->points_allocated = 0;
		$this->points_converted = 0;

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleCreditExpired.');
		}

		return true;
	}

	function handleReferralCompleted($userObj, $meta_array)
	{
		// prerequisites
		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not
		$default_points_allocated = 400;
		$requested_points = array_key_exists('points_earned', $meta_array) ? $meta_array['points_earned'] : $default_points_allocated;
		if ($requested_points <= 0 || trim($requested_points) == '')
		{
			$requested_points = $default_points_allocated;
		}
		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::REFERRAL_COMPLETED;
		$this->points_allocated = $requested_points;
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleReferralCompleted.');
		}

		if (!$this->userIsPreferred)
		{
			$this->convertPointsToCredit($userObj);

			return $this->id;
		}

		return false;
	}

	function handleOrderPlacedEvent($userObj, $orderObj)
	{
		// prerequisites
		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ORDERED;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->order_id = $orderObj->id;
		$this->json_meta = json_encode(array(
			'admin_comments' => 'Placed order.',
			'order_id' => $orderObj->id
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOrderPlacedEvent.');
		}

		$this->convertPointsToCredit($userObj);
	}

	function handleOrderConfirmedEvent($userObj, $orderObj)
	{
		// prerequisites
		$result = false;

		// user is in program
		if (!self::userIsActiveInProgram($userObj))
		{
			return;
		}  // quietly return if not

		$currentPoints = self::getCurrentPointsLevel($userObj->id);

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ORDER_CONFIRMED;
		$this->points_allocated = self::getPointsForOrder($currentPoints, $orderObj);
		$this->points_converted = 0;
		$this->order_id = $orderObj->id;
		$this->json_meta = json_encode(array(
			'comments' => 'Earned ' . number_format($this->points_allocated) . ' for order.',
			'order_id' => $orderObj->id
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOrderConfirmedEvent.');
		}

		$this->convertPointsToCredit($userObj);

		list($levelDetail, $nextLevelDetail) = self::getLevelDetailsByPoints($this->total_points);

		self::$lastOperationResult[] = array(
			'success' => true,
			'message' => 'The order was successfully confirmed.',
			'points_awarded' => $this->points_allocated,
			'lifetime_points' => $this->total_points,
			'pending_points' => self::getPendingPoints($this->user_id, $this->total_points),
			'level_title' => $levelDetail['title']
		);

		$result = self::getLastOperationResult();

		// current platepoints status
		$platePointsStatus = $this->currentPlatePointsStatus($userObj);

		// return results
		return array(
			$result,
			$platePointsStatus
		);
	}

	function handleMyMealsRatedEvent($userObj, $meta_array)
	{
		// prerequisites
		$result = false;

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::MY_MEALS_RATED;
		$this->points_allocated = self::$eventMetaData[self::MY_MEALS_RATED]['points'];
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleMyMealsRatedEvent.');
		}
		else
		{
			$result = self::$eventMetaData[self::MY_MEALS_RATED];
		}

		$this->convertPointsToCredit($userObj);

		// current platepoints status
		$platePointsStatus = $this->currentPlatePointsStatus($userObj);

		// return results
		return array(
			$result,
			$platePointsStatus
		);
	}

	function handleSocialConnectEvent($userObj, $meta_array)
	{
		// prerequisites
		$result = false;

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::SOCIAL_CONNECT;

		// this can only be rewarded once
		if (!$this->find(true))
		{
			$this->points_allocated = self::$eventMetaData[self::SOCIAL_CONNECT]['points'];
			$this->points_converted = 0;
			$this->json_meta = json_encode($meta_array);

			// actualize points
			$this->setTotalPoints();

			// record
			if (!$this->insert())
			{
				throw new Exception('Error inserting event in CPointsUserHistory::handleSocialConnectEvent.');
			}
			else
			{
				$result = self::$eventMetaData[self::SOCIAL_CONNECT];
			}

			// reward for previously sharing connection message on facebook
			self::handleEvent($userObj, self::SOCIAL_SHARING, array(
				'comments' => 'Earned ' . self::$eventMetaData[CPointsUserHistory::SOCIAL_SHARING]['points'] . ' points for connecting your Facebook account.',
				'facebook_id' => $userObj->facebook_id
			));
		}
		else
		{
			$this->points_allocated = 0;
			$this->setTotalPoints();

			$platePointsStatus = $this->currentPlatePointsStatus($userObj);

			return array(
				$result,
				$platePointsStatus
			);
		}

		// convert any eligible points
		$this->convertPointsToCredit($userObj);

		// current platepoints status
		$platePointsStatus = $this->currentPlatePointsStatus($userObj);

		// return results
		return array(
			$result,
			$platePointsStatus
		);
	}

	function handleSocialSharingEvent($userObj, $meta_array, $event = false)
	{
		if (!$event)
		{
			$event = self::SOCIAL_SHARING;
		}

		// prerequisites
		$result = false;

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = $event;
		$this->points_allocated = self::$eventMetaData[$event]['points'];
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleSocialSharingEvent.');
		}
		else
		{
			$result = self::$eventMetaData[$event];
		}

		// convert any eligible points
		$this->convertPointsToCredit($userObj);

		// current platepoints status
		$platePointsStatus = $this->currentPlatePointsStatus($userObj);

		// return results
		return array(
			$result,
			$platePointsStatus
		);
	}

	function handleOrderCancelledEvent($userObj, $orderObj, $meta_array)
	{
		// prerequisites

		$meta_array['order_id'] = $orderObj->id;

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ORDER_CANCELLED;
		$this->order_id = $orderObj->id;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode(array($meta_array));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOrderCancelledEvent.');
		}

		$this->convertPointsToCredit($userObj);
	}

	function handleOrderEditedEvent($userObj, $orderObj, $meta_array)
	{
		// prerequisites

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ORDER_EDITED;
		$this->order_id = $orderObj->id;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode(array(
			'admin_comments' => 'Order edited',
			'order_id' => $orderObj->id,
			'details' => $meta_array
		));

		// actualize points

		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOrderEditedEvent.');
		}

		// convert any eligible points
		$this->convertPointsToCredit($userObj);
	}

	function handleOrderRescheduledEvent($userObj, $orderObj)
	{
		// prerequisites

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ORDER_RESCHEDULED;
		$this->order_id = $orderObj->id;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode(array(
			'admin_comments' => 'Order rescheduled',
			'order_id' => $orderObj->id
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOrderRescheduledEvent.');
		}

		// convert any eligible points
		$this->convertPointsToCredit($userObj);
	}

	function handleOtherEvent($userObj, $meta_array)
	{
		// prerequisites
		$pointAward = 0;
		if (!empty($meta_array['debug_points'])) // for debug
		{
			$pointAward = $meta_array['debug_points'];
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::OTHER;
		$this->json_meta = json_encode($meta_array);

		$this->points_allocated = $pointAward;
		$this->points_converted = 0;

		$suppress_other_rewards = false;
		if (isset($meta_array['suppress_other_awards']) && $meta_array['suppress_other_awards'])
		{
			$suppress_other_rewards = true;
		}

		$noCreditForThesePoints = false;
		if (isset($meta_array['no_credit']) && $meta_array['no_credit'])
		{
			$noCreditForThesePoints = true;
		}

		$oldPreferredStatus = $this->userIsPreferred;
		if ($suppress_other_rewards || $noCreditForThesePoints)
		{
			$this->userIsPreferred = true;
			//force immediate consumption of points
		}

		// actualize points
		$this->setTotalPoints($suppress_other_rewards);

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOtherEvent.');
		}
		else
		{
			$result = self::$eventMetaData[self::OTHER];
		}

		// convert any eligible points
		$this->convertPointsToCredit($userObj);

		// current platepoints status
		$platePointsStatus = $this->currentPlatePointsStatus($userObj);

		if (!empty($meta_array['debug_points'])) // for debug
		{
			$result['points'] = $meta_array['debug_points'];
		}
		$result['event_id'] = $this->id;

		// return results
		return array(
			$result,
			$platePointsStatus
		);
	}

	function handleConversionEvent($userObj)
	{
		$optin_event = DAO_CFactory::create('points_user_history');
		$optin_event->userIsPreferred = $this->userIsPreferred;
		$optin_event->handleOptInEvent($userObj);

		if ($optin_event->userIsPreferred)
		{
			$conversionData = self::getPreferredUserConversionData($userObj);
			$comments = 'Converted Preferred User to PLATEPOINTS';
		}
		else
		{
			$conversionData = self::getDR2ConversionData($userObj);
			$comments = 'Converted from Dream Rewards';
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::CONVERSION;
		$this->points_allocated = $conversionData['points_award'];
		$this->points_converted = $this->points_allocated;
		$this->total_points = $this->points_allocated;

		$this->json_meta = json_encode(array(
			'comments' => $comments,
			'conversion_data' => $conversionData
		));

		$this->setTotalPoints(true); // true = suppress achievement reward

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleConversionEvent.');
		}

		// set user record to reflect new membership
		$userObj->dream_reward_status = 1;
		$userObj->dream_rewards_version = 3;

		// Can't use update here since a check for email change will trigger a failed attempt to update the user_login table
		//$userObj->update($oldUser);
		$tempUserObj = DAO_CFactory::create('user');
		$tempUserObj->query("update user set dream_reward_status = 1, dream_rewards_version = 3, has_opted_out_of_plate_points = 0 where id = {$userObj->id}");

		if (!$this->userIsPreferred && $conversionData['credit_award'] > 0)
		{
			$reward_id = $this->handlePointsToCreditConversionEvent($conversionData['credit_award'], $userObj->id);
			self::addNonPointsBasedCredit($userObj->id, $conversionData['credit_award'], $reward_id);
		}

		self::$lastOperationResult[] = array(
			'success' => true,
			'message' => 'The guest was successfully converted to the PLATEPOINTS program.'
		);

		return true;
	}

	function handlePointsToCreditConversionEvent($amount, $user_id_or_obj, $additional_comments = "", $suppressEmail = false)
	{
		if ($amount == 0)
		{
			return false;
		}

		if (is_object($user_id_or_obj) && get_class($user_id_or_obj) == 'CUser')
		{
			$userObj = $user_id_or_obj;
		}
		else if (is_numeric($user_id_or_obj) && $user_id_or_obj == CUser::getCurrentUser()->id)
		{
			$userObj = CUser::getCurrentUser();
		}
		else
		{
			$userObj = DAO_CFactory::create('user');
			$userObj->id = $user_id_or_obj;
			if (!$userObj->find(true))
			{
				throw new Exception('user not found in CPointsUserHistory::handlePointsToCreditConversionEvent.');
			}
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::REWARD_CREDIT;
		$this->points_allocated = 0;
		$this->points_converted = 0;

		if (!empty($additional_comments))
		{
			$comments = $amount . ' Dinner Dollars awarded' . $additional_comments;
		}
		else
		{
			$comments = $amount . ' Dinner Dollars awarded, ' . self::REWARD_INTERVAL . ' point milestone!';
		}

		$this->json_meta = json_encode(array(
			'comments' => $comments,
			'amount' => $amount
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handlePointsToCreditConversionEvent.');
		}

		if (!$suppressEmail)
		{
			self::addToEmailQueue('credit_reward', array(
				'userObj' => $userObj,
				'amount' => $amount,
			));
		}

		return $this->id;
	}

	static function convertUnconvertedPoints($userObj, $dryRun = false)
	{
		$eventRecord = DAO_CFactory::create('points_user_history');

		if ($userObj->isUserPreferred())
		{
			$eventRecord->userIsPreferred = true;
		}

		if ($eventRecord->userIsPreferred)
		{
			return "Is Preferred User\r\n";
		}

		$infoArr = array(
			'points_avail' => 0,
			'dollars_added' => 0
		);
		$eventRecord->convertPointsToCredit($userObj, $dryRun, $infoArr);

		return $infoArr;
	}

	function convertPointsToCredit($userObj, $dryRun = false, &$infoArray = false)
	{
		try
		{
			$pointsArray = array();

			$breakAfterUpdate = false;

			// get all unconverted points
			$historyObj = DAO_CFactory::create('points_user_history');
			$historyObj->query("select * from points_user_history where (points_allocated - points_converted) > 0 and is_deleted = 0 and user_id = {$userObj->id} order by id");

			$convertableTally = 0;

			while ($historyObj->fetch())
			{
				$pointsArray[$historyObj->id] = clone($historyObj);
				$convertableTally += ($historyObj->points_allocated - $historyObj->points_converted);
			}
			$totalAvailable = $convertableTally;

			$convertableTally = self::REWARD_INTERVAL * floor($convertableTally / self::REWARD_INTERVAL);
			if (is_array($infoArray))
			{
				$infoArray['points_avail'] = $convertableTally;
				$infoArray['unconverted_points'] = $totalAvailable;
			}

			if (is_array($userObj->platePointsData['current_level']))
			{
				$curLevel = $userObj->platePointsData['current_level']['level'];
			}
			else
			{
				$curLevel = $userObj->platePointsData['current_level'];
			}

			if ($convertableTally >= self::REWARD_INTERVAL)
			{
				if ($this->userIsPreferred)
				{
					$this->handleProgramExceptionEvent($userObj, 'Preferred user was awarded points but credit allocation was skipped.');

					return;
				}

				$creditObj = DAO_CFactory::create('points_credits');
				$creditObj->dollar_value = $convertableTally / 40; // 200 div by 40 = $5
				$creditObj->user_id = $userObj->id;
				$expiration_date = mktime(0, 0, 0, date("n"), date("j") + CPointsUserHistory::DINNER_DOLLAR_EXPIRATION_DAYS, date("Y"));

				$creditObj->expiration_date = date("Y-m-d 03:00:00", $expiration_date);

				if (!$dryRun)
				{
					$creditObj->insert();
				}

				if (is_array($infoArray))
				{
					$infoArray['dollars_added'] += $creditObj->dollar_value;
					$infoArray['dollars_expire'] = $creditObj->expiration_date;
				}

				$convertedSoFar = 0;
				$handledCreditConversionEvent = false;
				foreach ($pointsArray as $id => $obj)
				{
					$preChangeObj = clone($obj);
					$convertableThisObj = $obj->points_allocated - $obj->points_converted;
					$convertedSoFar += $convertableThisObj;
					if ($convertedSoFar <= $convertableTally)
					{
						$obj->points_converted = $obj->points_allocated;
					}
					else
					{
						$remainder = $convertedSoFar - $convertableTally;
						$partialConversion = $obj->points_allocated - $remainder;
						$obj->points_converted = $partialConversion;

						$pointsConversionEventObj = DAO_CFactory::create('points_user_history');

						// in this case the available points are not a multiple of 200 and there is a remainder
						if (!$dryRun)
						{
							$pointsConversionEventObj->handlePointsToCreditConversionEvent($creditObj->dollar_value, $userObj->id);
						}

						$handledCreditConversionEvent = true;
						CLog::Assert($obj->points_converted <= $obj->points_allocated, "Too many converted points for Event: {$obj->id}");

						$breakAfterUpdate = true;
					}

					if (!$dryRun)
					{
						$obj->update($preChangeObj);

						$pointsToPointsCredits = DAO_CFactory::create('points_to_points_credits');
						$pointsToPointsCredits->points_user_history_id = $obj->id;
						$pointsToPointsCredits->points_credit_id = $creditObj->id;
						$pointsToPointsCredits->insert();
					}

					if ($breakAfterUpdate)
					{
						break;
					}
				}

				if (!$handledCreditConversionEvent && !$dryRun)
				{
					// in this case the available points where an even multiple of 200
					$pointsConversionEventObj = DAO_CFactory::create('points_user_history');
					$pointsConversionEventObj->handlePointsToCreditConversionEvent($creditObj->dollar_value, $userObj->id);
				}
			}
		}
		catch (Exception $e)
		{
			CLog::RecordException($e);
		}
	}

	function handleProgramExceptionEvent($userObj, $meta_array)
	{
		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::ERROR_OR_EXCEPTION;
		$this->points_allocated = 0;
		$this->points_converted = 0;
		$this->json_meta = json_encode($meta_array);

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOptInEvent.');
		}
	}

	function handleOptInEvent($userObj)
	{
		// prerequisites
		// 1) not already in program

		if (self::userIsActiveInProgram($userObj))
		{
			$this->handleProgramExceptionEvent($userObj, 'Attempt to opt in when already in program');
			self::$lastOperationResult[] = array(
				'success' => false,
				'message' => 'The guest is already enrolled in the PLATEPOINTS program.'
			);

			return false;
		}

		// 2 cannnot be preferred user
		if ($this->userIsPreferred)
		{
			$this->handleProgramExceptionEvent($userObj, 'Attempt to opt in when already a Preferred Guest');
			self::$lastOperationResult[] = array(
				'success' => false,
				'message' => 'The guest is already a Preferred Guest and cannot be enrolled in Plate Points.  If you still wish to enroll the guest the Preferred Status must be removed.'
			);

			return false;
		}

		// add to history
		$this->user_id = $userObj->id;
		$this->event_type = self::OPT_IN;
		$this->points_allocated = self::$eventMetaData[self::OPT_IN]['points'];
		$this->points_converted = 0;

		$this->json_meta = json_encode(array(
			'comments' => 'Enrolled in PLATEPOINTS and earned ' . self::$eventMetaData[self::OPT_IN]['points'] . ' points.'
		));

		// actualize points
		$this->setTotalPoints();

		// record
		if (!$this->insert())
		{
			throw new Exception('Error inserting event in CPointsUserHistory::handleOptInEvent.');
		}

		self::addToEmailQueue('welcome', array('userObj' => $userObj));

		// convert any eligible points
		$this->convertPointsToCredit($userObj);

		$userObj->dream_reward_status = 1;
		$userObj->dream_rewards_version = 3;

		// Can't use update here since a check for email change will trigger a failed attempt to update the user_login table
		//$userObj->update($oldUser);
		$tempUserObj = DAO_CFactory::create('user');
		$tempUserObj->query("update user set dream_reward_status = 1, dream_rewards_version = 3, has_opted_out_of_plate_points = 0 where id = {$userObj->id}");

		self::$lastOperationResult[] = array(
			'success' => true,
			'message' => 'The guest was successfully enrolled in the PLATEPOINTS program.'
		);

		// enroll previous 7 days Orders in program
		$orders_since = date('Y-m-d H:i:s', strtotime('-7 days'));

		$ordersArray = COrders::getUsersOrders($userObj, false, false, false, $orders_since);

		foreach ($ordersArray as $order_id => $order)
		{
			if (empty($order['is_in_plate_points_program']))
			{
				$OrderObj = DAO_CFactory::create('orders');
				$OrderObj->id = $order_id;
				$OrderObj->find(true);
				$updateOrder = clone($OrderObj);
				$updateOrder->is_in_plate_points_program = 1;
				$updateOrder->update($OrderObj);
			}
		}

		return true;
	}
}