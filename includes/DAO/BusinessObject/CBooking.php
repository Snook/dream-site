<?php
require_once('DAO/Booking.php');
require_once('DAO/BusinessObject/CSession.php');
require_once('CMail.inc');

class CBooking extends DAO_Booking
{
	const HOLD = 'HOLD';
	const SAVED = 'SAVED';
	const ACTIVE = 'ACTIVE';
	const CANCELLED = 'CANCELLED';
	const RESCHEDULED = 'RESCHEDULED';
	const STANDARD = 'STANDARD';
	const INTRO = 'INTRO';
	const PENDING = 'PENDING';
	const COMPLETED = 'COMPLETED';
	static private $isReschedulingLockedOut_StoreObj = null;
	static private $isReschedulingLockedOut_MenuObj = null;

	public $leveled_up_since_last_session = null;
	public $leveled_up_details = null;
	public $json_meta = null;
	public $last_booking_type = null;
	public $last_session_type = null;
	public $last_session_type_subtype = null;
	public $last_session_attended = null;
	public $last_session_id_attended = null;
	public $bookings_made = null;
	public $bookings_made_at_store = null;

	public $DAO_session;
	public $DAO_user;
	public $DAO_store;
	public $DAO_menu;
	public $DAO_orders;

	function find($n = false)
	{
		return parent::find($n);
	}

	function find_DAO_booking($n = false)
	{
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('menu', true));
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store', true));
		$this->joinAddWhereAsOn($DAO_session);
		$DAO_user = DAO_CFactory::create('user', true);
		$DAO_user->joinAddWhereAsOn(DAO_CFactory::create('user_digest', true), 'LEFT');
		$this->joinAddWhereAsOn($DAO_user);

		$DAO_orders = DAO_CFactory::create('orders', true);
		$DAO_orders->joinAddWhereAsOn(DAO_CFactory::create('orders_digest', true), 'LEFT');
		$this->joinAddWhereAsOn($DAO_orders);

		$DAO_user_created_by = DAO_CFactory::create('user', true);
		$DAO_user_created_by->whereAdd("user_created_by.id=booking.created_by");
		$this->joinAddWhereAsOn($DAO_user_created_by, array(
			'joinType' => 'LEFT',
			'useLinks' => false
		), 'user_created_by');

		$DAO_user_updated_by = DAO_CFactory::create('user', true);
		$DAO_user_updated_by->whereAdd("user_updated_by.id=booking.updated_by");
		$this->joinAddWhereAsOn($DAO_user_updated_by, array(
			'joinType' => 'LEFT',
			'useLinks' => false
		), 'user_updated_by');

		return parent::find($n);
	}

	static public function getCancellationReasonArray()
	{
		return array(
			"" => "Select the main reason this guest canceled",
			'TOO_MANY_MEALS_IN_FREEZER' => "Too many meals in the freezer",
			'DIETARY_RESTRICTIONS' => "Dietary restrictions or changes",
			'SESSION_TIMES_DID_NOT_WORK' => "Session times didn't work for them",
			'FINANCIAL_CONCERNS' => "Financial concerns",
			'DID_NOT_LIKE_PROCESS_OR_MEALS' => "Didn't like the process or meals",
			'OTHER' => "Other"
		);
	}

	static public function getCustomerCancellationReasonArray()
	{
		return array(
			"" => "Select the main reason you are cancelling this order",
			'SESSION_TIMES_DID_NOT_WORK' => "I will no longer be home on my Delivery Date",
			'DIETARY_RESTRICTIONS' => "Dietary restrictions or changes",
			'FINANCIAL_CONCERNS' => "Too expensive",
			'DID_NOT_LIKE_PROCESS_OR_MEALS' => "I didn't like the meals in my previous order",
			'OTHER' => "Other"
		);
	}

	static public function getBookingCancellationReasonDisplayString($reasonEnum)
	{
		if (empty($reasonEnum))
		{
			return null;
		}

		$map = self::getCancellationReasonArray();

		return $map[$reasonEnum];
	}

	static public function getCancellationReasonAPINameArray()
	{
		return array(
			'TOO_MANY_MEALS_IN_FREEZER' => "ADDS_TOO_MANY_MEALS",
			'DIETARY_RESTRICTIONS' => "ADDS_DIETARY_RESTRICT",
			'SESSION_TIMES_DID_NOT_WORK' => "ADDS_SESSION_TIMES",
			'FINANCIAL_CONCERNS' => "NO_EMAIL_FINANCIAL",
			'DID_NOT_LIKE_PROCESS_OR_MEALS' => "NO_EMAIL_DIDNT_LIKE",
			'OTHER' => "NO_EMAIL_OTHER"
		);
	}

	static public function getBookingTypeDisplayString($type)
	{
		if ($type == "INTRO")
		{
			return "Starter Pack";
		}

		return ucfirst(strtolower($type));
	}

	function __construct()
	{
		parent::__construct();
	}

	function delete($useWhere = false, $forceDelete = false)
	{
		// Deleting SAVED bookings, delete associated data
		if ($this->status == CBooking::SAVED)
		{
			if ($this->id)
			{
				$DAO_orders = DAO_CFactory::create('orders');
				$DAO_orders->id = $this->order_id;
				if ($DAO_orders->find())
				{
					$DAO_orders->delete();
				}

				$DAO_order_item = DAO_CFactory::create('order_item');
				$DAO_order_item->order_id = $this->order_id;
				if ($DAO_order_item->find())
				{
					while ($DAO_order_item->fetch())
					{
						$DAO_order_item->delete();
					}
				}

				$DAO_orders_shipping = DAO_CFactory::create('orders_shipping');
				$DAO_orders_shipping->order_id = $this->order_id;
				if ($DAO_orders_shipping->find())
				{
					while ($DAO_orders_shipping->fetch())
					{
						$DAO_orders_shipping->delete();
					}
				}

				$DAO_orders_address = DAO_CFactory::create('orders_address');
				$DAO_orders_address->order_id = $this->order_id;
				if ($DAO_orders_address->find())
				{
					while ($DAO_orders_address->fetch())
					{
						$DAO_orders_address->delete();
					}
				}
			}

			return parent::delete($useWhere, $forceDelete);
		}
		// Deleting HOLD bookings
		if ($this->status == CBooking::HOLD)
		{
			return parent::delete($useWhere, $forceDelete);
		}

		return false;
	}

	function send_reminder_email()
	{
		try
		{
			$Mail = new CMail();
			$Mail->to_name = $this->DAO_user->firstname . ' ' . $this->DAO_user->lastname;
			$Mail->to_email = $this->DAO_user->primary_email;
			$Mail->to_id = $this->DAO_user->id;
			$Mail->from_email = $this->DAO_store->email_address;

			if ($this->DAO_session->isPickUp())
			{
				$Mail->subject = 'Your Order is Almost Ready for Pick Up';
				$Mail->body_html = CMail::mailMerge('session_reminder/session_reminder_special_event.html.php', $this);
				$Mail->body_text = CMail::mailMerge('session_reminder/session_reminder_special_event.txt.php', $this);
				$Mail->template_name = 'session_reminder_special_event';
			}
			else if ($this->DAO_session->isDreamTaste())
			{
				$Mail->subject = 'Your Event is Almost Here';
				$Mail->body_html = CMail::mailMerge('event_theme/session_reminder_dream_taste.html.php', $this);
				$Mail->body_text = CMail::mailMerge('event_theme/session_reminder_dream_taste.txt.php', $this);
				$Mail->template_name = 'session_reminder_dream_taste';
			}
			else if ($this->DAO_session->isDelivery())
			{
				$Mail->subject = 'Your Order is Almost Ready for Delivery';
				$Mail->body_html = CMail::mailMerge('session_reminder/session_reminder_special_event_delivery.html.php', $this);
				$Mail->body_text = CMail::mailMerge('session_reminder/session_reminder_special_event_delivery.txt.php', $this);
				$Mail->template_name = 'session_reminder_special_event_delivery';
			}
			else if ($this->DAO_session->isRemotePickup())
			{
				$this->DAO_session->setSessionObjects();

				$Mail->subject = 'Your Community Pick Up is Almost Here';
				$Mail->body_html = CMail::mailMerge('session_reminder/session_reminder_special_event_remote_pickup.html.php', $this);
				$Mail->body_text = CMail::mailMerge('session_reminder/session_reminder_special_event_remote_pickup.txt.php', $this);
				$Mail->template_name = 'session_reminder_special_event_remote_pickup';
			}
			else
			{
				$Mail->subject = 'Your Store Assembly Visit is Almost Here!';
				$Mail->body_html = CMail::mailMerge('session_reminder/session_reminder.html.php', $this);
				$Mail->body_text = CMail::mailMerge('session_reminder/session_reminder.txt.php', $this);
				$Mail->template_name = 'session_reminder';
			}

			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				CLog::Record('CRON_TEST: ' . print_r($this, true));
			}
			else
			{
				$Mail->sendEmail();
			}
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}

	function send_2_day_todd_reminder_email()
	{
		try
		{
			$Mail = new CMail();
			$data = array(
				'firstname' => $this->firstname,
				'lastname' => $this->lastname,
				'store_name' => $this->store_name,
				'session_start' => $this->session_start,
				'session_end' => date("Y-m-d H:i:s", strtotime($this->session_start) + $this->duration_minutes * 60),
				'store_id' => $this->store_id,
				'user_id' => $this->user_id,
				'session_id' => $this->session_id,
				'next_menu_id' => $this->menu_id + 1,
				'order_id' => $this->order_id
			);
			if (isset($this->session_type) && $this->session_type == CSession::TODD)
			{
				$contentsText = CMail::mailMerge('session_reminder_todd.txt.php', $data);
				$contentsHtml = CMail::mailMerge('session_reminder_todd.html.php', $data);
			}
			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				CLog::Record('CRON_TEST: ' . print_r($data, true));
			}
			else if (isset($this->session_type) && $this->session_type == CSession::TODD)
			{
				$Mail->send(null, $this->store_email, $this->firstname . ' ' . $this->lastname, $this->primary_email, 'Taste of Dream Dinners Session Reminder', $contentsHtml, $contentsText, '', '', $this->user_id, 'session_reminder_1day_todd');
			}
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}

	function send_reminder_retry_email()
	{
		try
		{
			$Mail = new CMail();
			$data = array(
				'firstname' => $this->firstname,
				'lastname' => $this->lastname,
				'store_name' => $this->store_name,
				'session_start' => $this->session_start,
				'store_id' => $this->store_id,
				'user_id' => $this->user_id,
				'session_id' => $this->session_id,
				'next_menu_id' => $this->menu_id + 1,
				'order_id' => $this->order_id,
				'is_intro' => (($this->booking_type == 'INTRO' && empty($this->bundle_id)) ? true : false)
			);
			$contentsText = CMail::mailMerge('session_reminder_retry.txt.php', $data);
			$contentsHtml = CMail::mailMerge('session_reminder_retry.html.php', $data);
			$Mail->send(null, $this->store_email, $this->firstname . ' ' . $this->lastname, $this->primary_email, 'Session Reminder', $contentsHtml, $contentsText, '', '', $this->user_id, 'session_reminder');
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}

	function bookingHistory()
	{
		$userHistory = DAO_CFactory::create('booking');
		$userHistory->query("SELECT
				b.user_id,
				MAX(s.session_start) AS last_session_attended,
				GROUP_CONCAT(s.session_start ORDER BY s.session_start DESC) AS session_starts,
				GROUP_CONCAT(s.id ORDER BY s.session_start DESC) AS session_ids_attended,
				GROUP_CONCAT(b.booking_type ORDER BY s.session_start DESC) AS booking_types,
				GROUP_CONCAT(s.session_type ORDER BY s.session_start DESC) AS session_types,
				GROUP_CONCAT(s.session_type_subtype ORDER BY s.session_start DESC) AS session_type_subtypes,
				COUNT(b.id) AS bookings_made,
				SUM(IF(s.store_id = '" . $this->store_id . "',1,0)) AS bookings_made_at_store
				FROM booking AS b
				INNER JOIN `session` AS s ON b.session_id = s.id AND s.session_start < '" . $this->session_start . "' AND s.session_publish_state != 'SAVED' AND s.is_deleted = '0'
				WHERE b.user_id = '" . $this->user_id . "'
				AND b.`status` = 'ACTIVE'
				AND b.is_deleted = '0'");
		if ($userHistory->fetch())
		{
			$booking_types = explode(',', $userHistory->booking_types);
			$session_types = explode(',', $userHistory->session_types);
			$session_type_subtypes = explode(',', $userHistory->session_type_subtypes);
			$session_ids = explode(',', $userHistory->session_ids_attended);
			$this->last_booking_type = $booking_types[0];
			$this->last_session_type = $session_types[0];
			$this->last_session_type_subtype = $session_type_subtypes[0];
			$this->last_session_id_attended = $session_ids[0];
			$this->last_session_attended = $userHistory->last_session_attended;
			$this->bookings_made = $userHistory->bookings_made;
			$this->bookings_made_at_store = $userHistory->bookings_made_at_store;
		}

		$this->first_standard = false;
		for ($i = count($session_ids) - 1; $i >= 0; $i--)
		{
			if (($session_types[$i] == 'STANDARD' || $session_types[$i] == 'SPECIAL_EVENT') && $booking_types[$i] != CBooking::INTRO)
			{
				$this->first_standard = $session_ids[$i];
				break;
			}
		}
	}

	static function userBookingHistory($user_ids, $store_id, $session_start)
	{
		if (empty($user_ids))
		{
			return false;
		}

		$userHistory = DAO_CFactory::create('booking');
		/*
		 * TODO: last_session_id_attended, booking_type, session_type are not accurate!
		 */
		$userHistory->query("SELECT
					iq.*,
					MIN(s2.session_start) as future_session
					FROM (SELECT
					b.user_id,
					MAX(s.session_start) AS last_session_attended,
					COUNT(b.id) AS bookings_made,
					SUM(IF(s.store_id = '" . $store_id . "',1,0)) AS bookings_made_at_store
					FROM booking AS b
					INNER JOIN `session` AS s ON b.session_id = s.id
					WHERE b.user_id IN (" . $user_ids . ")
					AND s.session_start < '" . $session_start . "'
					AND b.`status` = 'ACTIVE'
					AND b.is_deleted = '0'
					AND s.is_deleted = '0'
					AND s.session_publish_state != 'SAVED'
					GROUP BY b.user_id
					ORDER BY b.user_id ASC,	s.session_start ASC) as iq
				LEFT JOIN booking b2 ON b2.user_id = iq.user_id AND b2.status = 'ACTIVE'
				LEFT JOIN session s2 ON s2.id = b2.session_id AND s2.session_start > '" . $session_start . "'
				GROUP BY iq.user_id");
		$user_info_array = array();
		while ($userHistory->fetch())
		{
			$user_info_array[$userHistory->user_id] = array(
				'last_session_attended' => $userHistory->last_session_attended,
				'bookings_made' => $userHistory->bookings_made,
				'bookings_made_at_store' => $userHistory->bookings_made_at_store,
				'future_session' => $userHistory->future_session
			);
		}

		return $user_info_array;
	}

	static function hasFutureBooking($user_id, $session_start)
	{
		$userHistory = DAO_CFactory::create('booking');
		$userHistory->query("select s.id from booking b
		join session s on b.session_id = s.id and s.session_start > '$session_start' and s.is_deleted = 0 and s.session_publish_state <> 'SAVED'
		where b.user_id = $user_id and b.is_deleted = 0 and b.`status` = 'ACTIVE'");
		if ($userHistory->N > 0)
		{
			return true;
		}

		return false;
	}

	function leveledUpSinceLastSession(&$theBooking)
	{
		$levelUpDetails = CPointsUserHistory::leveledUpSinceLastSession($theBooking['user_id'], $theBooking['session_start']);
		$theBooking['leveled_up_since_last_session'] = $levelUpDetails['leveled_up_since_last_session'];
		$theBooking['leveled_up_details'] = $levelUpDetails['leveled_up_details'];
	}

	function bookingUserData(&$theBooking)
	{
		if (!empty($this->user_data_field_ids))
		{
			$ud_ids = explode('||', $this->user_data_field_ids);
			$ud_values = explode('||', $this->user_data_values);
			foreach ($ud_ids as $id => $ud_id)
			{
				if (!empty($ud_values[$id]))
				{
					$theBooking['user_data'][$ud_id] = $ud_values[$id];

					if ($ud_id == 1)
					{

						if (is_numeric($ud_values[$id]))
						{
							if ($ud_values[$id] == date("n", strtotime($theBooking['session_start'])))
							{
								$theBooking['is_birthday_month'] = true;
							}
						}
						else
						{
							if ($ud_values[$id] == date("F", strtotime($theBooking['session_start'])))
							{
								$theBooking['is_birthday_month'] = true;
							}
						}
					}
				}
			}
		}
	}

	function bookingUser(&$theBooking)
	{
		if (!empty($theBooking['user_id']))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $theBooking['user_id'];
			$User->find(true);
			$theBooking['user'] = $User;
			$theBooking['user']->getUserPreferences();

			$orderObj = false;
			if (!empty($theBooking['order_id']))
			{
				$orderObj = DAO_CFactory::create('orders');
				$orderObj->id = $theBooking['order_id'];
				$orderObj->find(true);
			}

			$theBooking['user']->getMembershipStatus($orderObj->id);
			$theBooking['user']->getPlatePointsSummary($orderObj);
			$theBooking['user']->nextSession($theBooking['session_start']);
			$theBooking['user']->userData = CUserData::getSFIDataForDisplayNew($User->id, false, $orderObj->store_id);
		}
	}

	function bookingBalanceDue(&$theBooking)
	{
		require_once("includes/DAO/BusinessObject/CPayment.php");
		if ($this->status == CBooking::CANCELLED || $this->status == CBooking::RESCHEDULED || $this->status == CBooking::SAVED)
		{
			$theBooking['balance_due'] = 0.00;
			$theBooking['balance_due_text'] = 'N/A';
			$theBooking['balance_due_css'] = '';

			return;
		}
		$types = explode(",", $this->payment_types);
		$amounts = explode(",", $this->payment_amounts);
		$isDelayed = explode(",", $this->is_delayed);
		$delayedStatus = explode(",", $this->delayed_status);
		$LTD_donation = 0;
		if (!empty($this->ltd_round_up_value) and is_numeric($this->ltd_round_up_value))
		{
			$LTD_donation = $this->ltd_round_up_value;
		}
		$grand_total = $this->grand_total + $LTD_donation;
		$grand_total = $grand_total + .000000001;
		$grand_total = (int)($grand_total * 100);
		$payment_total = 0;
		foreach ($types as $thisType)
		{
			$this_amount = array_shift($amounts);
			$thisDelayed = array_shift($isDelayed);
			$thisDelayedStatus = array_shift($delayedStatus);
			$this_amount = floatval($this_amount) + .000000001;
			$this_amount = intval($this_amount * 100);
			switch ($thisType)
			{
				case CPayment::REFUND:
				case CPayment::REFUND_CASH:
				case CPayment::REFUND_STORE_CREDIT:
				case CPayment::REFUND_GIFT_CARD:
					$payment_total -= $this_amount;
					break;
				default:
				{
					$validPayment = true;
					if ($thisDelayed && ($thisDelayedStatus == 'FAIL' || $thisDelayedStatus == 'CANCELLED'))
					{
						$validPayment = false;
					}
					if ($validPayment)
					{
						$payment_total += $this_amount;
					}
				}
			}
		}
		$Diff = $grand_total - $payment_total;
		$theBooking['balance_due'] = number_format($Diff / 100, 2);
		$theBooking['balance_due_text'] = '$' . $theBooking['balance_due'];
		$theBooking['balance_due_css'] = 'bal_green';
		if ($theBooking['balance_due'] > 0)
		{
			$theBooking['balance_due_css'] = 'bal_red';
		}
		else if ($theBooking['balance_due'] < 0)
		{
			$theBooking['balance_due_text'] = '-$' . substr($theBooking['balance_due_text'], 2);
			$theBooking['balance_due_css'] = 'bal_blue';
		}
	}

	function can_reschedule(&$theBooking)
	{
		if ($theBooking['status'] != CBooking::ACTIVE && $theBooking['status'] != CBooking::SAVED)
		{
			return $theBooking['can_reschedule'] = false;
		}
		if (empty($theBooking['session_id']))
		{
			CLog::Record('N_NOTICE:: Session not found in CBooking::can_reschedule');

			return $theBooking['can_reschedule'] = false;
		}
		if (empty($theBooking['store_id']))
		{
			CLog::Record('N_NOTICE:: Store not found in CBooking::can_reschedule');

			return $theBooking['can_reschedule'] = false;
		}
		// After the 6th a session in last month cannot be rescheduled
		if (self::isReschedulingLockedOut($theBooking['store_id'], $theBooking['menu_id']))
		{
			return $theBooking['can_reschedule'] = false;
		}

		return $theBooking['can_reschedule'] = true;
	}

	function isActive()
	{
		if ($this->status === self::ACTIVE)
		{
			return true;
		}

		return false;
	}

	function isCancelled()
	{
		if ($this->status === self::CANCELLED)
		{
			return true;
		}

		return false;
	}

	function isRescheduled()
	{
		if ($this->status === self::RESCHEDULED)
		{
			return true;
		}

		return false;
	}

	function isSaved()
	{
		if ($this->status === self::SAVED)
		{
			return true;
		}

		return false;
	}

	function isHold()
	{
		if ($this->status === self::HOLD)
		{
			return true;
		}

		return false;
	}

	function isReschedulingLockedOut($store_id, $menu_id)
	{
		if (self::$isReschedulingLockedOut_StoreObj === null)
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $store_id;
			$storeObj->selectAdd("timezone_id");
			$storeObj->find(true);
			self::$isReschedulingLockedOut_StoreObj = $storeObj;
		}
		else
		{
			$storeObj = self::$isReschedulingLockedOut_StoreObj;
		}

		if (self::$isReschedulingLockedOut_MenuObj === null)
		{
			$menuObj = DAO_CFactory::create('menu');
			$menuObj->id = $menu_id;
			$menuObj->find(true);
			self::$isReschedulingLockedOut_MenuObj = $menuObj;
		}
		else
		{
			$menuObj = self::$isReschedulingLockedOut_MenuObj;
		}

		return !$menuObj->areSessionsOrdersEditable($storeObj);
	}
}

?>