<?php
require_once 'DAO/Coupon_code.php';
require_once 'DAO/BusinessObject/COrders.php';
require_once 'DAO/BusinessObject/CCouponCodeProgram.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CCouponCode
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CCouponCode extends DAO_Coupon_code
{

	const FREE_MEAL = 'FREE_MEAL';
	const FLAT = 'FLAT';
	const FLAT_TOTAL = 'FLAT_TOTAL';
	const PERCENT = 'PERCENT';
	const BONUS_CREDIT = 'BONUS_CREDIT';
	const FREE_MENU_ITEM = 'FREE_MENU_ITEM';

	const NEW_USER = 'NEW';
	const EXISTING_USER = 'EXISTING';
	const ALL_USERS = 'ALL';
	const NEW_OR_REMISS = 'NEW_OR_REMISS'; /// means hasn't ordered or last order is older than remiss date
	const REMISS_SINCE_DATE = 'REMISS_SINCE_DATE'; /// means has ordered and last order is older the specified date
	const REMISS_N_MONTHS = 'REMISS_N_MONTHS'; /// means has ordered and last order is older N months
	const NEW_OR_REMISS_N_MONTHS = 'NEW_OR_REMISS_N_MONTHS'; /// means has ordered and last order is older N months

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		return $res;
	}

	// default values for creating new coupon
	function defaultValues()
	{
		$this->minimum_servings_count = 36;
		$this->is_order_editor_supported = 1;
		$this->use_limit_per_account = 1;
		$this->valid_for_session_type_standard = 1;
		$this->valid_for_order_type_standard = 1;
		$this->is_store_specific_ids = array();
	}

	static function getCouponDetails($coupon_id)
	{
		$coupon = DAO_CFactory::create('coupon_code');
		$coupon->id = $coupon_id;
		$coupon->find(true);

		$coupon->is_store_specific_ids = array();

		if (!empty($coupon->is_store_specific))
		{
			$couponStore = DAO_CFactory::create('coupon_to_store');
			$couponStore->coupon_code_id = $coupon->id;
			$couponStore->find();

			while ($couponStore->fetch())
			{
				$coupon->is_store_specific_ids[$couponStore->store_id] = $couponStore->store_id;
			}
		}

		$coupon->recipe = null;

		if (!empty($coupon->recipe_id))
		{
			$coupon->recipe = DAO_CFactory::create('recipe');
			$coupon->recipe->recipe_id = $coupon->recipe_id;
			$coupon->recipe->override_menu_id = 'NULL';
			$coupon->recipe->find(true);
		}

		return $coupon;
	}

	static function getCouponDetailsArray($coupon_id = false)
	{
		$where_clause = "";

		if ($coupon_id)
		{
			$where_clause = " AND cc.id = '" . $coupon_id . "'";
		}

		$coupon = DAO_CFactory::create('coupon_code');
		$coupon->query("SELECT
			ccp.program_name,
			ccp.program_owner,
			CONVERT(GROUP_CONCAT(store.home_office_id SEPARATOR  ',' ) USING 'utf8') AS store_id,
			cc.*
			FROM coupon_code AS cc
			INNER JOIN coupon_code_program AS ccp ON ccp.id = cc.program_id AND ccp.is_deleted = '0'
			LEFT JOIN coupon_to_store AS cts ON cts.coupon_code_id = cc.id
			LEFT JOIN store ON store.id = cts.store_id AND store.is_deleted = '0'
			WHERE cc.is_deleted = '0'
			" . $where_clause . "
			GROUP BY cc.id
			ORDER BY cc.valid_timespan_end ASC");

		$couponArray = array();

		while ($coupon->fetch())
		{
			$couponArray[$coupon->id] = clone($coupon);
			$couponArray[$coupon->id]->is_store_specific_ids = array();

			if (!empty($coupon->is_store_specific))
			{
				$couponStore = DAO_CFactory::create('coupon_to_store');
				$couponStore->coupon_code_id = $coupon->id;
				$couponStore->find();

				while ($couponStore->fetch())
				{
					$couponArray[$coupon->id]->is_store_specific_ids[$couponStore->store_id] = $couponStore->store_id;
				}
			}
		}

		return $couponArray;
	}

	static function getCouponProgramArray()
	{
		$program = DAO_CFactory::create('coupon_code_program');
		$program->orderBy('id DESC');
		$program->find();

		$programArray = array();

		while ($program->fetch())
		{
			$programArray[$program->id] = clone($program);
		}

		return $programArray;
	}

	static function getCorporateClientArray()
	{
		$corporateClient = DAO_CFactory::create('corporate_crate_client');
		$corporateClient->orderBy('id DESC');
		$corporateClient->find();

		$corporateClientArray = array();

		while ($corporateClient->fetch())
		{
			$corporateClientArray[$corporateClient->id] = clone($corporateClient);
		}

		return $corporateClientArray;
	}

	static function getCouponErrorUserText($errorCode)
	{
		/*
		 * If the $errorCode is an array, the first array item is the error code,
		 * subsequent array items are vsprintf replacement variables
		 * see platepoints_perk_expired for example
		 */
		$vsprintf = false;
		if (is_array($errorCode))
		{
			$vsprintf = $errorCode;
			$errorCode = $vsprintf[0]; // assigned the error code
			array_shift($vsprintf); // remove the error code from array for vsprintf
		}

		static $couponErrorsArray = array();

		if (empty($couponErrorsArray))
		{
			$couponErrorsArray["bundle_order_not_eligible"] = "This code cannot be used with the Meal Prep Starter Pack";
			$couponErrorsArray["code_does_not_exist"] = "The code entered is not valid.";
			$couponErrorsArray["coupon_menu_is_future"] = "This code is for a future menu month.";
			$couponErrorsArray["coupon_menu_is_past"] = "This code is not valid for this menu.";
			$couponErrorsArray["coupon_not_found_for_pp_level"] = "This code is not valid with PLATEPOINTS %s level.";
			$couponErrorsArray["coupon_period_is_past"] = "This code has expired.";
			$couponErrorsArray["coupon_period_is_future"] = "This code is not valid yet.";
			$couponErrorsArray["coupon_session_is_after_valid_range"] = "The code is not valid for this order date.";
			$couponErrorsArray["coupon_session_is_before_valid_range"] = "The code is not valid for this order date.";
			$couponErrorsArray["delivered_requires_curated_box"] = "This code is for a pre-built box.";
			$couponErrorsArray["delivered_requires_custom_box"] = "This code requires a Build your own box.";
			$couponErrorsArray["delivered_requires_large_box"] = "This code is for a Large box.";
			$couponErrorsArray["delivered_requires_medium_box"] = "This code is for a Medium box.";
			$couponErrorsArray["direct_order_not_supported"] = "This code cannot be applied in the order manager.";
			$couponErrorsArray["exceeds_code_use_limit"] = "This code code has been used the maximum times allowed.";
			$couponErrorsArray["excluded_by_store"] = "This code is not valid for this store. Please contact this store for more information.";
			$couponErrorsArray["free_mfy_not_supported"] = "This code is not supported by this store.";
			$couponErrorsArray["guest_is_ordering_in_DR"] = "Coupons cannot be used with a Dream Rewards order.";
			$couponErrorsArray["minimum_item_amount_not_met"] = "The minimum number of %s main menu items required to use this code has not yet been met.";
			$couponErrorsArray["minimum_order_amount_not_met"] = "The minimum purchase amount required to use this code has not yet been met.";
			$couponErrorsArray["minimum_servings_amount_not_met"] = "The minimum number of servings required to use this code has not yet been met.";
			$couponErrorsArray["not_a_corporate_crate_client"] = "This code is valid for Corporate Crate Clients only.";
			$couponErrorsArray["not_valid_corporate_crate_client_id"] = "This code is not valid for this Corporate Crate Client.";
			$couponErrorsArray["not_valid_corporate_crate_x_more_orders_needed"] = "This code is not valid for this order, %s more orders needed.";
			$couponErrorsArray["not_valid_for_delivered"] = "This code is not valid on shipped orders.";
			$couponErrorsArray["not_valid_for_delivery_session"] = "This code is not valid for home delivery.";
			$couponErrorsArray["not_valid_for_DFL_menu"] = "This promo cannot be used with the the Dinners For Life Menu";
			$couponErrorsArray["not_valid_for_Diabetic_menu"] = "This promo cannot be used with the Dinners For Life Diabetic Menu";
			$couponErrorsArray["not_valid_for_discounted_session"] = "This code is not valid for use with discounted sessions.";
			$couponErrorsArray["not_valid_for_intro_order"] = "This code is not valid on a Meal Prep Starter Pack order.";
			$couponErrorsArray["not_valid_for_private_session"] = "This code is not valid for private sessions.";
			$couponErrorsArray["not_valid_for_product"] = "Not valid for product orders.";
			$couponErrorsArray["not_valid_for_product_membership"] = "Not valid for Meal Prep+ Membership orders.";
			$couponErrorsArray["not_valid_for_sampler_order"] = "This code cannot be used on a Meal Prep Starter Pack order.";
			$couponErrorsArray["not_valid_for_standard_menu"] = "This code cannot be used with this menu.";
			$couponErrorsArray["not_valid_for_standard_order"] = "This code is not valid for this order type.";
			$couponErrorsArray["not_valid_for_standard_session"] = "This code is not valid for this session.";
			$couponErrorsArray["not_valid_for_stores"] = "This code is not supported by this store.";
			$couponErrorsArray["not_valid_for_unknown_menu"] = "This promo cannot be used with the the Dinners For Life Menu";
			$couponErrorsArray["not_valid_with_points_credits"] = "This code cannot be used with Dinner Dollars.";
			$couponErrorsArray["not_valid_with_referral_credits"] = "This code cannot be used with Referral Reward.";
			$couponErrorsArray["no_ft_items"] = "This code is only valid for Sides &amp; Sweets sides and no sides are found in the cart.";
			$couponErrorsArray["no_order"] = "There is an issue with your order, please review your cart.";
			$couponErrorsArray["menu_item_out_of_stock"] = "The menu item is currently unavailable.";
			$couponErrorsArray["online_not_supported"] = "This code cannot be used through the website.";
			$couponErrorsArray["only_valid_for_product"] = "Only valid for product orders.";
			$couponErrorsArray["only_valid_for_product_membership"] = "Only valid for Meal Prep+ Membership orders.";
			$couponErrorsArray["order_edit_not_supported"] = "This code cannot be used in the Order Editor";
			$couponErrorsArray["platepoints_level_up_not_found"] = "Unexpected error: We could not find when this guest leveled to %s, please contact support.";
			$couponErrorsArray["platepoints_perk_expired"] = "The PLATEPOINTS code expired.";
			$couponErrorsArray["todd_coupon_period_is_past"] = "This code is must be used within 72 hours of attending the Meal Prep Workshop session";
			$couponErrorsArray["todd_no_todd_order_exists"] = "This code requires a Meal Prep Workshop order";
			$couponErrorsArray["todd_order_not_eligible"] = "This code cannot be used with a Meal Prep Workshop order";
			$couponErrorsArray["todd_other_orders_exist"] = "The Meal Prep Workshop code is not valid for existing customers";
			$couponErrorsArray["user_is_preferred"] = "Preferred Guest accounts are not eligible.";
			$couponErrorsArray["user_not_existing"] = "This code is not valid for new customers. You must have previous orders in your account.";
			$couponErrorsArray["user_not_new"] = "This code is only valid for new customers.";
			$couponErrorsArray["user_not_recent"] = "This code is only valid for customers who have not ordered in over a year.";
			$couponErrorsArray["user_not_recent_since_date"] = "This code is only valid for customers who have not ordered since %s.";
			$couponErrorsArray["user_not_recent_n_months"] = "This code is only valid for customers who have not ordered in the last %s months.";
		}

		if (isset($couponErrorsArray[$errorCode]))
		{
			if ($vsprintf)
			{
				return vsprintf($couponErrorsArray[$errorCode], $vsprintf);
			}
			else
			{
				return $couponErrorsArray[$errorCode];
			}
		}

		CLog::Record("COUPON ERROR:" . $errorCode);

		return "An unexpected error occurred when validating the coupon.";
	}

	function isValidForCurrentPage()
	{

		if (isset($_REQUEST['processor']) && strpos($_REQUEST['processor'], "cart_add_payment") !== false && $this->is_customer_order_supported == "0")
		{
			return 'online_not_supported';
		}

		if (strpos($_SERVER['REQUEST_URI'], "processor?processor=couponCodeProcessor") !== false && $this->is_customer_order_supported == "0")
		{
			return 'online_not_supported';
		}

		if (strpos($_SERVER['REQUEST_URI'], "processor?processor=admin_directOrderCouponCodeProcessor") !== false && $this->is_direct_order_supported == "0")
		{
			return 'direct_order_not_supported';
		}

		if ((strpos($_SERVER['REQUEST_URI'], "processor?processor=admin_editOrderCouponCodeProcessor") !== false || strpos($_SERVER['REQUEST_URI'], "processor?processor=admin_editOrderCouponCodeProcessorDelivered") !== false) && $this->is_order_editor_supported == "0")
		{
			return 'order_edit_not_supported';
		}

		return true;
	}

	/*
* 	Returns the DAO couponCodeObject if valid else returns an array of string error codes
*/
	static function isCodeValidForDelivered($actualCouponCode, $Order, $menu_id, $editedOrder = false, $orgOrderTime = null, $orgOrderID = null)
	{
		$daoCode = DAO_CFactory::create('coupon_code');
		$daoCode->coupon_code = trim($actualCouponCode);

		$daoCode->query("select * from coupon_code where coupon_code = '{$daoCode->coupon_code}' and is_deleted = 0 and is_delivered_coupon = 1 order by id desc limit 1");

		if (!$daoCode->fetch(true))
		{
			return array('code_does_not_exist');
		}

		if (!empty($daoCode->limit_to_delivery_fee))
		{
			$daoCode->discount_var = $Order->subtotal_delivery_fee;
		}

		$validationResult = $daoCode->isValidForDelivered($Order, $menu_id, $orgOrderTime, $orgOrderID);

		if (empty($validationResult))
		{
			return $daoCode;
		}

		return $validationResult;
	}

	/*
	* 	Returns the DAO couponCodeObject if valid else returns an array of string error codes
	*/
	static function isCodeValid($actualCouponCode, $Order, $menu_id, $editedOrder = false, $orgOrderTime = null, $orgOrderID = null)
	{
		$daoCode = DAO_CFactory::create('coupon_code');
		$daoCode->coupon_code = trim($actualCouponCode);
		$daoCode->is_store_coupon = 1;

		if (!$daoCode->find(true))
		{
			return array('code_does_not_exist');
		}

		$daoCode->calculate($Order, $Order->getMarkUp());

		if (!empty($daoCode->limit_to_mfy_fee))
		{

			$daoCode->discount_var = $Order->subtotal_service_fee;

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select supports_free_assembly_promotion from store where id = {$Order->store_id}");
			$storeObj->fetch();

			if (!$storeObj->supports_free_assembly_promotion)
			{
				return array('free_mfy_not_supported');
			}

			$session = $Order->findSession();

			// session may not be available yet when coupon is added, imperative that coupon is revalidated at checkout
			if (!empty($session))
			{
				if (!$session->isMadeForYou())
				{
					return array('not_valid_for_standard_order');
				}
			}
		}

		if (!empty($daoCode->limit_to_delivery_fee))
		{

			if($daoCode->discount_var > $Order->subtotal_delivery_fee && !is_null($Order->subtotal_delivery_fee)){
				$daoCode->discount_var = $Order->subtotal_delivery_fee;
			}

			$session = $Order->findSession();
			if (!empty($session) && !$session->isMadeForYou())
			{
				return array('not_valid_for_standard_order');
			}
		}

		if ($daoCode->coupon_code == 'HOSTESS' || $daoCode->coupon_code == 'GNOINTRO15')
		{

			if ($daoCode->coupon_code == 'HOSTESS')
			{
				// override price of coupon, discount equal to the bundle price
				$bundle = $Order->getBundleObj();
				$daoCode->discount_var = $bundle->price;

				// For Corp Store Price increase Hack
				if ($menu_id > 176 && $menu_id <= 184)
				{
					$storeObj = DAO_CFactory::create('store');
					$storeObj->query("select is_corporate_owned from store where id = {$Order->store_id}");
					$storeObj->fetch();

					if ($storeObj->is_corporate_owned)
					{
						$daoCode->discount_var = 34.99;
					}
				}

				// if this is a dream taste, the hostess discount is equal to the cost of the dream taste bundle
				$session = $Order->findSession();
				if ($daoCode->coupon_code == 'HOSTESS' && $session->session_type == CSession::DREAM_TASTE)
				{
					$daoCode->discount_var = $bundle->price;
				}
			}

			// For Corp Store Price increase Hack
			if ($daoCode->coupon_code == 'GNOINTRO15' && $menu_id < 221)
			{
				$daoCode->discount_var = 84.95;
			}

			// SHORT CIRCUIT EVALUATION EMERGENMCY HACK
			if (strpos($_SERVER['REQUEST_URI'], "processor?processor=couponCodeProcessor") !== false)
			{
				return array('online_not_supported');
			}

			return $daoCode;
		}

		$validationResult = $daoCode->isValid($Order, $menu_id, $orgOrderTime, $orgOrderID);

		if (empty($validationResult))
		{
			return $daoCode;
		}

		return $validationResult;
	}

	function isValidForProduct($Order)
	{
		$errorArray = array();

		if (empty($this->is_product_coupon))
		{
			$errorArray[] = 'not_valid_for_product';
		}

		if (empty($this->valid_for_product_type_membership))
		{
			$errorArray[] = 'not_valid_for_product_membership';
		}

		// check for store exclusion
		if (!CCouponCodeProgram::isCodeAcceptedByStore($Order->store_id, $this->coupon_code))
		{
			$errorArray[] = 'excluded_by_store';
		}

		if ($Order->user_id)
		{
			$user_id = $Order->user_id;

			// GET PRODUCT HISTORICAL DATA
			$orders = DAO_CFactory::create('product_orders');
			$orders->query("select 
				po.*, 
				FROM product_orders AS po
				WHERE po.user_id = '" . $user_id . "' AND po.is_deleted = 0 
				ORDER by po.id");

			$productHistoricalData = array();
			$productOrderCount = 0;
			while ($orders->fetch())
			{
				if ($orders->booking_status == 'ACTIVE')
				{
					$productHistoricalData[$orders->id] = array(
						"coupon_id" => $orders->coupon_code_id
					);

					$productOrderCount++;
				}
			}

			$productCodeCount = 0;
			foreach ($productHistoricalData as $id => $datum)
			{
				if ($datum['coupon_id'] == $this->id)
				{
					$productCodeCount++;
				}
			}
		}
		else
		{
			$productCodeCount = 0;
		}

		if ($Order->user_id)
		{
			$user_id = $Order->user_id;

			// GET HISTORICAL DATA
			$orders = DAO_CFactory::create('orders');
			$orders->query("select 
				orders.id as 'order_id', 
				orders.is_TODD, 
				orders.timestamp_created, 
				session.session_start, 
				booking.id as 'booking_id',
				booking.status as 'booking_status', 
				orders.coupon_code_id as 'coupon_code_id'
				from orders 
				join booking on booking.order_id = orders.id 
				join session on booking.session_id = session.id 
				where orders.user_id = '" . $user_id . "' and orders.is_deleted = 0
				order by orders.id, booking.id ");

			$lastSession = strtotime('2004-01-01 00:00:00');
			$nonTODDorderCount = 0;
			$TODDOrder = false;
			$historicalData = array();
			while ($orders->fetch())
			{
				if ($orders->booking_status == 'ACTIVE')
				{
					$historicalData[$orders->order_id] = array(
						'booking_status' => $orders->booking_status,
						"coupon_id" => $orders->coupon_code_id,
						'order_time' => $orders->timestamp_created
					);
					if ($orders->is_TODD)
					{
						$TODDOrder = $orders->order_id;
					}
					else
					{
						$nonTODDorderCount++;
					}

					$sessionStartTS = strtotime($orders->session_start);
					if ($sessionStartTS > $lastSession)
					{
						$lastSession = $sessionStartTS;
					}
				}
			}

			$hasOrders = false;
			if ($nonTODDorderCount > 0)
			{
				$hasOrders = true;
			}

			$codeCount = 0;
			foreach ($historicalData as $id => $datum)
			{
				if ($datum['coupon_id'] == $this->id)
				{
					$codeCount++;
				}
			}
		}
		else
		{ // we now allow the coupon to succeed if the user is not logged in - the assumption is that they are a new guest
			// ordering/registering using the the new method. The coupn may be invalidated later when then account is created/ updated.
			$hasOrders = false;
		}

		// CUSTOMER TYPE
		if ($this->applicable_customer_type != self::ALL_USERS)
		{
			if ($this->applicable_customer_type == self::NEW_OR_REMISS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime($this->remiss_cutoff_date))
					{
						$errorArray[] = array(
							'user_not_recent_since_date',
							CTemplate::dateTimeFormat($this->remiss_cutoff_date, MONTH_DAY_YEAR)

						);
					}
				}
			}

			if ($this->applicable_customer_type == self::REMISS_SINCE_DATE && $hasOrders)
			{
				if ($lastSession > strtotime($this->remiss_cutoff_date))
				{
					$errorArray[] = 'user_not_recent';
				}
			}

			if ($this->applicable_customer_type == self::REMISS_N_MONTHS && $hasOrders)
			{
				if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
				{
					$errorArray[] = array(
						'user_not_recent_n_months',
						$this->remiss_number_of_months
					);
				}
			}

			if ($this->applicable_customer_type == self::NEW_OR_REMISS_N_MONTHS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
					{
						$errorArray[] = array(
							'user_not_recent_n_months',
							$this->remiss_number_of_months
						);
					}
				}
				// else user is new
			}

			if ($this->applicable_customer_type == self::NEW_USER && $hasOrders)
			{
				$errorArray[] = 'user_not_new';
			}

			if ($this->applicable_customer_type == self::EXISTING_USER && !$hasOrders)
			{
				$errorArray[] = 'user_not_existing';
			}
		}

		// CODE USE LIMIT
		if ((int)$this->use_limit_per_account !== 0)
		{
			if ($productCodeCount >= (int)$this->use_limit_per_account)
			{
				$errorArray[] = 'exceeds_code_use_limit';
			}
		}

		//TIME SPAN
		$now = time();

		$timeSpanError = null;

		//First adjust time span to local store time
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$Order->store_id}");
		if ($storeObj->N > 0)
		{
			$storeObj->fetch();
			$now = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $now));
			$now = strtotime($now);
		}

		// first record if now is invalid
		if ($now < strtotime($this->valid_timespan_start))
		{
			$timeSpanError = 'coupon_period_is_future';
		}
		if ($now >= strtotime($this->valid_timespan_end))
		{
			$timeSpanError = 'coupon_period_is_past';
		}

		if (isset($timeSpanError))
		{
			// if it is check if original order time was supplied (order edit)
			if (isset($orgOrderTime))
			{// if so check to see if original order time was valid
				if ($orgOrderTime < strtotime($this->valid_timespan_start))
				{
					$errorArray[] = 'coupon_period_is_future';
				}

				if ($orgOrderTime >= strtotime($this->valid_timespan_end))
				{
					$errorArray[] = 'coupon_period_is_past';
				}

				// here the current time is invalid but the order time is valid
				// fall out of test and keep going
			}
			else
			{// if not return the error
				$errorArray[] = $timeSpanError;
			}
		}

		return $errorArray;
	}

	function isValid($Order, $menu_id, $orgOrderTime = null, $orgOrderID = null)
	{
		$errorArray = array();

		// ORDER
		if (!isset($Order))
		{
			$errorArray[] = 'no_order';

			return $errorArray;
		}

		// one more check to be sure
		if (!$this->is_store_coupon)
		{
			$errorArray[] = 'not_valid_for_stores';

			return $errorArray;
		}

		if (!empty($this->is_product_coupon))
		{
			$errorArray[] = 'only_valid_for_product';
		}

		if (!empty($this->valid_for_product_type_membership))
		{
			$errorArray[] = 'only_valid_for_product_membership';
		}

		if ($Order->isNewIntroOffer() && !$this->valid_for_order_type_intro)
		{
			$errorArray[] = 'bundle_order_not_eligible';
		}

		if ($Order->isDreamTaste() && !$this->valid_for_order_type_dream_taste)
		{
			$errorArray[] = 'todd_order_not_eligible';
		}

		if ($Order->menu_program_id == 1 && !$this->valid_for_standard_menu)
		{
			$errorArray[] = 'not_valid_for_standard_menu';
		}

		if ($Order->menu_program_id > 1 && !$this->valid_for_DFL_menu)
		{
			$errorArray[] = 'not_valid_for_DFL_menu';
		}

		if ($Order->points_discount_total > 0 && !$this->valid_with_plate_points_credits)
		{
			$errorArray[] = 'not_valid_with_points_credits';
		}

		if ($Order->discount_total_customer_referral_credit > 0 && !$this->valid_with_customer_referral_credit)
		{
			$errorArray[] = 'not_valid_with_referral_credits';
		}

		if ($Order->menu_program_id > 1 && $this->valid_for_DFL_menu && $Order->menu_program_id != $this->valid_DFL_menu)
		{
			if ($Order->menu_program_id == 2)
			{
				$errorArray[] = 'not_valid_for_Diabetic_menu';
			}
			else
			{
				$errorArray[] = 'not_valid_for_unknown_menu';
			}
		}

		// check for store exclusion
		if (!CCouponCodeProgram::isCodeAcceptedByStore($Order->store_id, $this->coupon_code))
		{
			$errorArray[] = 'excluded_by_store';
		}

		$defeatTODDRulesForNow = false;
		if ($Order->user_id)
		{
			$user_id = $Order->user_id;

			// GET HISTORICAL DATA
			$orders = DAO_CFactory::create('orders');
			$orders->query("select 
				orders.id as 'order_id', 
				orders.is_TODD, 
				orders.timestamp_created, 
				session.session_start, 
				booking.id as 'booking_id',
				booking.status as 'booking_status', 
				orders.coupon_code_id as 'coupon_code_id'
				from orders 
				join booking on booking.order_id = orders.id 
				join session on booking.session_id = session.id 
				where orders.user_id = '" . $user_id . "' and orders.is_deleted = 0
				order by orders.id, booking.id ");

			$lastSession = strtotime('2004-01-01 00:00:00');
			$nonTODDorderCount = 0;
			$TODDOrder = false;
			$historicalData = array();
			while ($orders->fetch())
			{
				if ($orders->booking_status == 'ACTIVE' && $orders->order_id != $orgOrderID)
				{
					$historicalData[$orders->order_id] = array(
						'booking_status' => $orders->booking_status,
						"coupon_id" => $orders->coupon_code_id,
						'order_time' => $orders->timestamp_created
					);
					if ($orders->is_TODD)
					{
						$TODDOrder = $orders->order_id;
					}
					else
					{
						$nonTODDorderCount++;
					}

					$sessionStartTS = strtotime($orders->session_start);
					if ($sessionStartTS > $lastSession)
					{
						$lastSession = $sessionStartTS;
					}
				}
			}

			$hasOrders = false;
			if ($nonTODDorderCount > 0)
			{
				$hasOrders = true;
			}

			$codeCount = 0;
			foreach ($historicalData as $id => $datum)
			{
				if ($datum['coupon_id'] == $this->id)
				{
					$codeCount++;
				}
			}
		}
		else
		{ // we now allow the coupon to succeed if the user is not logged in - the assumption is that they are a new guest
			// ordering/registering using the the new method. The coupn may be invalidated later when then account is created/ updated.
			$hasOrders = false;
			$codeCount = 0;
			$defeatTODDRulesForNow = true;
		}
		// CUSTOMER TYPE
		if ($this->applicable_customer_type != self::ALL_USERS)
		{
			if ($this->applicable_customer_type == self::NEW_OR_REMISS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime($this->remiss_cutoff_date))
					{
						$errorArray[] = array(
							'user_not_recent_since_date',
							CTemplate::dateTimeFormat($this->remiss_cutoff_date, MONTH_DAY_YEAR)

						);
					}
				}
			}

			if ($this->applicable_customer_type == self::REMISS_SINCE_DATE && $hasOrders)
			{
				if ($lastSession > strtotime($this->remiss_cutoff_date))
				{
					$errorArray[] = 'user_not_recent';
				}
			}

			if ($this->applicable_customer_type == self::REMISS_N_MONTHS && $hasOrders)
			{
				if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
				{
					$errorArray[] = array(
						'user_not_recent_n_months',
						$this->remiss_number_of_months
					);
				}
			}

			if ($this->applicable_customer_type == self::NEW_OR_REMISS_N_MONTHS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
					{
						$errorArray[] = array(
							'user_not_recent_n_months',
							$this->remiss_number_of_months
						);
					}
				}
				else
				{
					// user is new
				}
			}

			if ($this->applicable_customer_type == self::NEW_USER && $hasOrders)
			{
				$errorArray[] = 'user_not_new';
			}

			if ($this->applicable_customer_type == self::EXISTING_USER && !$hasOrders)
			{
				$errorArray[] = 'user_not_existing';
			}
		}

		// CODE USE LIMIT
		if ((int)$this->use_limit_per_account !== 0)
		{
			if ($codeCount >= (int)$this->use_limit_per_account)
			{
				$errorArray[] = 'exceeds_code_use_limit';
			}
		}

		//TIME SPAN
		$now = time();

		$timeSpanError = null;

		//First adjust time span to local store time
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$Order->store_id}");
		if ($storeObj->N > 0)
		{
			$storeObj->fetch();
			$now = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $now));
			$now = strtotime($now);
		}

		// first record if now is invalid
		if ($now < strtotime($this->valid_timespan_start))
		{
			$timeSpanError = 'coupon_period_is_future';
		}
		if ($now >= strtotime($this->valid_timespan_end))
		{
			$timeSpanError = 'coupon_period_is_past';
		}

		if (isset($timeSpanError))
		{
			// if it is check if original order time was supplied (order edit)
			if (isset($orgOrderTime))
			{// if so check to see if original order time was valid
				if ($orgOrderTime < strtotime($this->valid_timespan_start))
				{
					$errorArray[] = 'coupon_period_is_future';
				}

				if ($orgOrderTime >= strtotime($this->valid_timespan_end))
				{
					$errorArray[] = 'coupon_period_is_past';
				}

				// here the current time is invalid but the order time is valid
				// fall out of test and keep going
			}
			else
			{// if not return the error
				$errorArray[] = $timeSpanError;
			}
		}

		//Session Date
		$session = $Order->findSession();
		if (!empty($session) && !empty($session->session_start))
		{
			if (!empty($this->valid_session_timespan_start) && strtotime($session->session_start) < strtotime($this->valid_session_timespan_start))
			{
				$errorArray[] = 'coupon_session_is_before_valid_range';
			}

			if (!empty($this->valid_session_timespan_end) && strtotime($session->session_start) > (strtotime($this->valid_session_timespan_end) + 86400))
			{
				$errorArray[] = 'coupon_session_is_after_valid_range';
			}
		}

		//MENU
		if (!empty($this->valid_menuspan_start) && $menu_id < $this->valid_menuspan_start)
		{
			$errorArray[] = 'coupon_menu_is_future';
		}

		if (!empty($this->valid_menuspan_end) && $menu_id > $this->valid_menuspan_end)
		{
			$errorArray[] = 'coupon_menu_is_past';
		}

		// SESSION TYPE
		$session = $Order->findSession();

		// session may not be available yet when coupon is added, imperative that coupon is revalidated at checkout
		if (!empty($session))
		{
			$test = $session->isStandard();

			if ($session->isStandard() && !$this->valid_for_session_type_standard)
			{
				$errorArray[] = 'not_valid_for_standard_session';
			}

			if ($session->isDiscounted() && !$this->valid_for_session_type_discounted)
			{
				$errorArray[] = 'not_valid_for_discounted_session';
			}

			if ($session->isPrivate() && !$this->valid_for_session_type_private)
			{
				$errorArray[] = 'not_valid_for_private_session';
			}

			if ($session->isDelivery() && !$this->valid_for_session_type_delivery)
			{
				$errorArray[] = 'not_valid_for_delivery_session';
			}

			if ($this->use_TODD_rules && !$defeatTODDRulesForNow)
			{
				if (!$TODDOrder)
				{
					$errorArray[] = 'todd_no_todd_order_exists';
				}

				if ($nonTODDorderCount > 0)
				{
					$errorArray[] = 'todd_other_orders_exist';
				}

				// period starts at the time of the session or the time of the order whichever is greater
				$sessionTime = strtotime($session->session_start);
				$orderTime = ($TODDOrder ? strtotime($historicalData[$TODDOrder]['order_time']) : $sessionTime);

				$validTODDPeriodBasis = ($orderTime > $sessionTime ? $orderTime : $sessionTime);
				// 72 hours after that
				$timeLimit = $validTODDPeriodBasis + 259200;

				if (time() > $timeLimit)
				{
					$errorArray[] = 'todd_coupon_period_is_past';
				}
			}
		}

		//ORDER TYPE
		if ($Order->isStandard() && !$this->valid_for_order_type_standard)
		{
			$errorArray[] = 'not_valid_for_standard_order';
		}

		if ($Order->isNewIntroOffer() && !$this->valid_for_order_type_intro)
		{
			$errorArray[] = 'not_valid_for_intro_order';
		}

		if ($Order->isSampler() && !$this->valid_for_order_type_sampler)
		{
			$errorArray[] = 'not_valid_for_sampler_order';
		}

		// MINIMUM AMOUNT
		if (!empty($this->minimum_order_amount) && (($Order->grand_total + $Order->coupon_code_discount_total) < $this->minimum_order_amount))
		{
			$errorArray[] = 'minimum_order_amount_not_met';
		}

		// MINIMUM SERVINGS
		if (!empty($this->minimum_servings_count) && $Order->servings_total_count < $this->minimum_servings_count)
		{
			$errorArray[] = 'minimum_servings_amount_not_met';
		}

		// MINIMUM ITEMS
		if (!empty($this->minimum_item_count) && $Order->menu_items_core_total_count < $this->minimum_item_count)
		{
			$errorArray[] = array(
				'minimum_item_amount_not_met',
				$this->minimum_item_count
			);
		}

		// PREFERRED USER
		if (!empty($Order->user_preferred_discount_total))
		{
			if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
			{
				$errorArray[] = 'user_is_preferred';
			}
		}

		// no coupon codes if customer is ordering a dream rewards discounted order from the front end
		if ((strpos($_SERVER['REQUEST_URI'], "processor?processor=couponCodeProcessor") !== false) && $Order->dream_rewards_level > 0)
		{
			$errorArray[] = 'guest_is_ordering_in_DR';
		}

		if ($this->limit_to_finishing_touch && empty($Order->pcal_sidedish_total))
		{
			$errorArray[] = 'no_ft_items';
		}

		// validate platepoints perk
		if (!empty($this->is_platepoints_perk))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $user_id;
			$User->find(true);
			$User->getPlatePointsSummary();

			if (empty($User->platePointsData['current_level']['rewards']['coupon_id']) || $User->platePointsData['current_level']['rewards']['coupon_id'] != $this->id)
			{
				$errorArray[] = array(
					'coupon_not_found_for_pp_level',
					$User->platePointsData['current_level']['title']
				);
			}
			else if (!empty($User->platePointsData['current_level']['rewards']['coupon_expire']))
			{
				$PUH = DAO_CFactory::create('points_user_history');
				$PUH->query("SELECT
					timestamp_created
					FROM `points_user_history`
					WHERE user_id = '" . $user_id . "'
					AND event_type = '" . CPointsUserHistory::ACHIEVEMENT_AWARD . "'
					AND json_meta LIKE '%" . $User->platePointsData['current_level']['level'] . "%'
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
						AND json_meta LIKE '%" . $User->platePointsData['current_level']['level'] . "%'
						AND is_deleted = '0'
						ORDER BY timestamp_created DESC
						LIMIT 1");

					$PUH->fetch();
				}

				if (!empty($PUH->N))
				{
					$date_expired = date("Y-m-d H:i:s", strtotime($User->platePointsData['current_level']['rewards']['coupon_expire'], strtotime($PUH->timestamp_created)));

					if (date("Y-m-d H:i:s", time()) > $date_expired)
					{
						$errorArray[] = array(
							'platepoints_perk_expired',
							CTemplate::dateTimeFormat($date_expired, MONTH_DAY_YEAR)
						);
					}
				}
				else
				{
					$errorArray[] = array(
						'platepoints_level_up_not_found',
						$User->platePointsData['current_level']['title']
					);
				}
			}
		}

		// corporate crate client validate
		if (!empty($this->valid_corporate_crate_client_id))
		{
			if (empty($User))
			{
				$User = DAO_CFactory::create('user');
				$User->id = $user_id;
				$User->find(true);
			}

			$ccDetails = false;
			if (!empty($User->secondary_email))
			{
				$ccDetails = CCorporateCrateClient::corporateCrateClientDetails($User->secondary_email);
			}
			else
			{
				$errorArray[] = 'not_a_corporate_crate_client';
			}

			if (strtoupper($this->valid_corporate_crate_client_id) == 'ALL')
			{
				// if the coupon code is
				if ($this->coupon_code == 'CC3RDFREE')
				{
					$orderInfoArray = array_reverse(COrders::getUsersOrders($User, 3, false, false, false, array('STANDARD')));

					$thirdOrder = 0;

					foreach ($orderInfoArray as $orderInfo_id => $orderInfo)
					{
						$thirdOrder++;

						if (!empty($orderInfo['coupon_code_id']) && $orderInfo['coupon_code'] == 'CC3RDFREE')
						{
							$thirdOrder = 0;
						}
					}

					if ($thirdOrder < 3)
					{
						$ordersNeeded = 3 - $thirdOrder;

						$errorArray[] = array(
							'not_valid_corporate_crate_x_more_orders_needed',
							$ordersNeeded
						);
					}
				}
			}
			else
			{
				// valid_corporate_crate_client_id - comma delimited list of valid corporate_crate_client ids
				$valid_cc_ids = explode(',', $this->valid_corporate_crate_client_id);

				if (empty($ccDetails->id) || !in_array($ccDetails->id, $valid_cc_ids))
				{
					$errorArray[] = 'not_valid_corporate_crate_client_id';
				}
			}
		}

		if (!empty($this->limit_to_recipe_id) && !empty($this->menu_item_id))
		{
			// INVENTORY TOUCH POINT
			$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
			$DAO_menu_item_inventory->menu_id = $menu_id;
			$DAO_menu_item_inventory->store_id = $Order->getStore()->id;
			$DAO_menu_item_inventory->getMenuItemInventory(array($this->menu_item_id));

			if ($DAO_menu_item_inventory->find(true))
			{
				if($DAO_menu_item_inventory->remaining_servings < $DAO_menu_item_inventory->DAO_menu_item->servings_per_item)
				{
					$errorArray[] = 'menu_item_out_of_stock';
				}
			}
			else
			{
				// just give same error if the query doesn't work for some reason
				$errorArray[] = 'menu_item_out_of_stock';
			}
		}

		$pageResult = $this->isValidForCurrentPage();

		if ($pageResult !== true)
		{
			$errorArray[] = $pageResult;
		}

		return $errorArray;
	}

	function getOrderedBoxTypes($Order)
	{
		$retVal = array(
			"has_large" => false,
			"has_medium" => false,
			"has_curated" => false,
			"has_custom" => false
		);

		$boxes = $Order->getBoxes();

		if (empty($boxes))
		{
			return $retVal;
		}

		foreach ($boxes as $box_instance_id => $thisBox)
		{
			if ($thisBox['box_instance']->box_type == CBox::DELIVERED_FIXED)
			{
				$retVal['has_curated'] = true;
			}
			else if ($thisBox['box_instance']->box_type == CBox::DELIVERED_CUSTOM)
			{
				$retVal['has_custom'] = true;
			}

			if ($thisBox['bundle']->number_servings_required / $thisBox['bundle']->number_items_required > 3.0)
			{
				$retVal['has_large'] = true;
			}
			else
			{
				$retVal['has_medium'] = true;
			}
		}

		return $retVal;
	}

	function isValidForDelivered($Order, $menu_id, $orgOrderTime = null, $orgOrderID = null)
	{
		$errorArray = array();

		// ORDER
		if (!isset($Order))
		{
			$errorArray[] = 'no_order';

			return $errorArray;
		}

		// one more check to be sure
		if (!$this->is_delivered_coupon)
		{
			$errorArray[] = 'not_valid_for_delivered';

			return $errorArray;
		}

		$OrderedBoxTypes = $this->getOrderedBoxTypes($Order);

		if ($this->delivered_requires_medium_box)
		{
			if (!$OrderedBoxTypes['has_medium'])
			{
				$errorArray[] = 'delivered_requires_medium_box';
			}
		}

		if ($this->delivered_requires_large_box)
		{
			if (!$OrderedBoxTypes['has_large'])
			{
				$errorArray[] = 'delivered_requires_large_box';
			}
		}

		if ($this->delivered_requires_custom_box)
		{
			if (!$OrderedBoxTypes['has_custom'])
			{
				$errorArray[] = 'delivered_requires_custom_box';
			}
		}

		if ($this->delivered_requires_curated_box)
		{
			if (!$OrderedBoxTypes['has_curated'])
			{
				$errorArray[] = 'delivered_requires_curated_box';
			}
		}

		if ($Order->user_id)
		{
			$user_id = $Order->user_id;

			// GET HISTORICAL DATA
			$orders = DAO_CFactory::create('orders');
			$orders->query("select 
				orders.id as 'order_id', 
				orders.is_TODD, 
				orders.timestamp_created, 
				session.session_start, 
				booking.id as 'booking_id',
				booking.status as 'booking_status', 
				orders.coupon_code_id as 'coupon_code_id'
				from orders 
				join booking on booking.order_id = orders.id 
				join session on booking.session_id = session.id 
				where orders.user_id = '" . $user_id . "' and orders.is_deleted = 0
				order by orders.id, booking.id ");

			$lastSession = strtotime('2004-01-01 00:00:00');
			$nonTODDorderCount = 0;
			$TODDOrder = false;
			$historicalData = array();
			while ($orders->fetch())
			{
				if ($orders->booking_status == 'ACTIVE' && $orders->order_id != $orgOrderID)
				{
					$historicalData[$orders->order_id] = array(
						'booking_status' => $orders->booking_status,
						"coupon_id" => $orders->coupon_code_id,
						'order_time' => $orders->timestamp_created
					);
					if ($orders->is_TODD)
					{
						$TODDOrder = $orders->order_id;
					}
					else
					{
						$nonTODDorderCount++;
					}

					$sessionStartTS = strtotime($orders->session_start);
					if ($sessionStartTS > $lastSession)
					{
						$lastSession = $sessionStartTS;
					}
				}
			}

			$hasOrders = false;
			if ($nonTODDorderCount > 0)
			{
				$hasOrders = true;
			}

			$codeCount = 0;
			foreach ($historicalData as $id => $datum)
			{
				if ($datum['coupon_id'] == $this->id)
				{
					$codeCount++;
				}
			}
		}
		else
		{ // we now allow the coupon to succeed if the user is not logged in - the assumption is that they are a new guest
			// ordering/registering using the the new method. The coupn may be invalidated later when then account is created/ updated.
			$hasOrders = false;
			$codeCount = 0;
			$defeatTODDRulesForNow = true;
		}

		// CUSTOMER TYPE
		if ($this->applicable_customer_type != self::ALL_USERS)
		{
			if ($this->applicable_customer_type == self::NEW_OR_REMISS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime($this->remiss_cutoff_date))
					{
						$errorArray[] = array(
							'user_not_recent_since_date',
							CTemplate::dateTimeFormat($this->remiss_cutoff_date, MONTH_DAY_YEAR)

						);
					}
				}
			}

			if ($this->applicable_customer_type == self::REMISS_SINCE_DATE && $hasOrders)
			{
				if ($lastSession > strtotime($this->remiss_cutoff_date))
				{
					$errorArray[] = 'user_not_recent';
				}
			}

			if ($this->applicable_customer_type == self::REMISS_N_MONTHS && $hasOrders)
			{
				if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
				{
					$errorArray[] = array(
						'user_not_recent_n_months',
						$this->remiss_number_of_months
					);
				}
			}

			if ($this->applicable_customer_type == self::NEW_OR_REMISS_N_MONTHS && $hasOrders)
			{
				if ($hasOrders)
				{
					if ($lastSession > strtotime(-$this->remiss_number_of_months . " months"))
					{
						$errorArray[] = array(
							'user_not_recent_n_months',
							$this->remiss_number_of_months
						);
					}
				}
				else
				{
					// user is new
				}
			}

			if ($this->applicable_customer_type == self::NEW_USER && $hasOrders)
			{
				$errorArray[] = 'user_not_new';
			}

			if ($this->applicable_customer_type == self::EXISTING_USER && !$hasOrders)
			{
				$errorArray[] = 'user_not_existing';
			}
		}

		// CODE USE LIMIT
		if ((int)$this->use_limit_per_account !== 0)
		{
			if ($codeCount >= (int)$this->use_limit_per_account)
			{
				$errorArray[] = 'exceeds_code_use_limit';
			}
		}

		//TIME SPAN
		$now = time();

		$timeSpanError = null;

		//First adjust time span to local store time
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$Order->store_id}");
		if ($storeObj->N > 0)
		{
			$storeObj->fetch();
			$now = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $now));
			$now = strtotime($now);
		}

		// first record if now is invalid
		if ($now < strtotime($this->valid_timespan_start))
		{
			$timeSpanError = 'coupon_period_is_future';
		}
		if ($now >= strtotime($this->valid_timespan_end))
		{
			$timeSpanError = 'coupon_period_is_past';
		}

		if (isset($timeSpanError))
		{
			// if it is check if original order time was supplied (order edit)
			if (isset($orgOrderTime))
			{// if so check to see if original order time was valid
				if ($orgOrderTime < strtotime($this->valid_timespan_start))
				{
					$errorArray[] = 'coupon_period_is_future';
				}

				if ($orgOrderTime >= strtotime($this->valid_timespan_end))
				{
					$errorArray[] = 'coupon_period_is_past';
				}

				// here the current time is invalid but the order time is valid
				// fall out of test and keep going
			}
			else
			{// if not return the error
				$errorArray[] = $timeSpanError;
			}
		}

		//Session Date NOTE: sesssion is date of delivery
		$session = $Order->findSession();
		if (!empty($session) && !empty($session->session_start))
		{
			if (!empty($this->valid_session_timespan_start) && strtotime($session->session_start) < strtotime($this->valid_session_timespan_start))
			{
				$errorArray[] = 'coupon_session_is_before_valid_range';
			}

			if (!empty($this->valid_session_timespan_end) && strtotime($session->session_start) > (strtotime($this->valid_session_timespan_end) + 86400))
			{
				$errorArray[] = 'coupon_session_is_after_valid_range';
			}
		}

		//MENU
		if (!empty($this->valid_menuspan_start) && $menu_id < $this->valid_menuspan_start)
		{
			$errorArray[] = 'coupon_menu_is_future';
		}

		if (!empty($this->valid_menuspan_end) && $menu_id > $this->valid_menuspan_end)
		{
			$errorArray[] = 'coupon_menu_is_past';
		}

		// SESSION TYPE
		// $session = $Order->findSession();

		// MINIMUM AMOUNT
		if (!empty($this->minimum_order_amount) && (($Order->grand_total + $Order->coupon_code_discount_total) < $this->minimum_order_amount))
		{
			$errorArray[] = 'minimum_order_amount_not_met';
		}

		// MINIMUM SERVINGS
		if (!empty($this->minimum_servings_count) && $Order->servings_total_count < $this->minimum_servings_count)
		{
			$errorArray[] = 'minimum_servings_amount_not_met';
		}

		// MINIMUM ITEMS
		if (!empty($this->minimum_item_count) && $Order->menu_items_core_total_count < $this->minimum_item_count)
		{
			$errorArray[] = array(
				'minimum_item_amount_not_met',
				$this->minimum_item_count
			);
		}

		$pageResult = $this->isValidForCurrentPage();

		if ($pageResult !== true)
		{
			$errorArray[] = $pageResult;
		}

		return $errorArray;
	}

	// An alternative calculation for standalone product orders with no booking or session
	function calculateForProduct($ProductOrder)
	{
		if (!$ProductOrder)
		{
			return false;
		}

		switch ($this->discount_method)
		{
			case self::FLAT:
				$discount = $this->discount_var;
				if ($discount > $ProductOrder->subtotal_products)
				{
					$discount = $ProductOrder->subtotal_products;
				}

				return $discount;

			case self::PERCENT:
				$base = $ProductOrder->subtotal_products;
				$discount = floor($base * ($this->discount_var)) / 100;

				return $discount;

			default:
				throw new Exception('unrecognized promo type');
				break;
		}

		return false;
	}

	/**
	 * Calculates the price reduction from an order.
	 * @return the dollar amount of the discount or false if the promo does
	 * not apply to the order.
	 *    CES: 1-30-07 Added $markup override: if supplied use the passed in markup
	 *    otherwise use the current store markup
	 *    ability to non-current markup added for order editing
	 */
	function calculate($Order, $markup = false)
	{
		if (!$Order)
		{
			return false;
		}

		if ($Order->getMenuID() && $Order->getMenuID() < 221)
		{
			if ($this->coupon_code == 'GNOINTRO15')
			{
				return 84.95;
			}
		}

		$DAO_session = $Order->findSession();
		$DAO_bundle = $Order->getBundleObj();

		// if this is a dream taste, the hostess discount is equal to the cost of the dream taste bundle
		if ($this->coupon_code == 'HOSTESS' && $DAO_session->session_type == CSession::DREAM_TASTE)
		{
			return $this->discount_var = $DAO_bundle->price;
		}

		switch ($this->discount_method)
		{
			case self::FLAT:
				return $this->_calculateFlat($Order, $markup);

			case self::PERCENT:
				return $this->_calculatePercent($Order, $markup);

			case self::FREE_MEAL:
				return $this->_calculateFreeMeal($Order, $markup);

			case self::BONUS_CREDIT:
				//Do nuttin
				return $Order->coupon_code_discount_total;

			case self::FREE_MENU_ITEM:
				return $this->_calculateFreeMenuItem($Order, $markup);

			default:
				throw new Exception('unrecognized promo type');
		}

		return false;
	}

	function _calculateFlat($Order, $markup)
	{
		if ($this->limit_to_core)
		{
			if ($this->discount_var > $Order->pcal_core_total)
			{
				return $Order->pcal_core_total;
			}
		}

		if ($this->limit_to_finishing_touch)
		{
			if ($this->discount_var > $Order->pcal_sidedish_total)
			{
				return $Order->pcal_sidedish_total;
			}
		}

		if ($this->limit_to_mfy_fee)
		{
			if ($this->discount_var > $Order->subtotal_service_fee)
			{
				return $Order->subtotal_service_fee;
			}
		}

		if ($this->limit_to_delivery_fee)
		{
			if ($this->discount_var > $Order->subtotal_delivery_fee && !is_null($Order->subtotal_delivery_fee))
			{
				return $Order->subtotal_delivery_fee;
			}
		}

		if ($this->limit_to_recipe_id)
		{
			$base = $this->_calculateDiscountedRecipe($Order);

			if ($this->discount_var > $base)
			{
				return $base;
			}
		}

		return $this->discount_var;
	}

	function _calculatePercent($Order, $markup)
	{
		if ($this->limit_to_core)
		{
			$base = $Order->pcal_core_total;
			$discount = CTemplate::moneyFormat(($base * ($this->discount_var)) / 100);

			return $discount;
		}

		if ($this->limit_to_finishing_touch)
		{
			$base = $Order->pcal_sidedish_total;
			$discount = CTemplate::moneyFormat(($base * ($this->discount_var)) / 100);

			return $discount;
		}

		if ($this->limit_to_mfy_fee)
		{
			$base = $Order->subtotal_service_fee;
			$discount = CTemplate::moneyFormat(($base * ($this->discount_var)) / 100);

			return $discount;
		}

		if ($this->limit_to_delivery_fee)
		{
			$base = $Order->subtotal_delivery_fee;
			$discount = CTemplate::moneyFormat(($base * ($this->discount_var)) / 100);

			return $discount;
		}

		if ($this->limit_to_recipe_id)
		{
			$base = $this->_calculateDiscountedRecipe($Order);
			$discount = CTemplate::moneyFormat(($base * ($this->discount_var)) / 100);

			return $discount;
		}

		//TODO: sanity checks
		$base = $Order->subtotal_menu_items + $Order->subtotal_home_store_markup - $Order->bundle_discount - $Order->family_savings_discount - $Order->promo_code_discount_total - $Order->volume_discount_total;
		$base = COrders::std_round($base);
		$discount = COrders::std_round(($base * ($this->discount_var)) / 100);

		return $discount;
	}

	function _calculateDiscountedRecipe($Order)
	{
		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->recipe_id = $this->recipe_id;
		$DAO_menu_item->pricing_type = $this->recipe_id_pricing_type;

		$DAO_recipe = DAO_CFactory::create('recipe');
		$DAO_recipe->whereAdd("recipe.recipe_id = menu_item.recipe_id");

		$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
		$DAO_menu_to_menu_item->store_id = $Order->store_id;
		$DAO_menu_to_menu_item->menu_id = $Order->getMenuID();
		$DAO_menu_to_menu_item->selectAdd();
		$DAO_menu_to_menu_item->selectAdd('menu_to_menu_item.*');

		$DAO_menu_to_menu_item->joinAddWhereAsOn(DAO_CFactory::create('store'));
		$DAO_menu_to_menu_item->joinAddWhereAsOn(DAO_CFactory::create('menu'));
		$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_menu_item);
		$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_recipe);

		$DAO_menu_to_menu_item->find(true);

		$this->menu_item_id = $DAO_menu_to_menu_item->menu_item_id;

		return $DAO_menu_to_menu_item->store_price;
	}

	function _calculateFreeMeal($Order, $markup)
	{
		$storeDAO = DAO_CFactory::create('store');
		$storeDAO->id = $Order->store_id;
		$storeDAO->find(true);

		$itemDAO = CMenuItem::getStoreSpecificItem($storeDAO, $this->menu_item_id);

		if (!$itemDAO)
		{
			// try it the old way - this won't pick up the override price
			$itemDAO = DAO_CFactory::create('menu_item');
			$itemDAO->id = $this->menu_item_id;
			!$itemDAO->find(true);
			$discount = COrders::getStorePrice($markup, $itemDAO, 1);
		}
		else
		{
			$discount = $itemDAO->store_price;
		}

		// Remove any existing
		$Order->removeCouponFreeMealItem();
		// add entree to order if needed
		$Order->addMenuItem($itemDAO, 1, false, true);

		return $discount;
	}

	function _calculateFreeMenuItem($Order, $markup)
	{
		$storeDAO = DAO_CFactory::create('store');
		$storeDAO->id = $Order->store_id;
		$storeDAO->find(true);

		$itemDAO = CMenuItem::getStoreSpecificItem($storeDAO, $Order->coupon_free_menu_item);

		if (!$itemDAO)
		{
			// try it the old way - this won't pick up the override price
			$itemDAO = DAO_CFactory::create('menu_item');
			$itemDAO->id = $this->menu_item_id;
			!$itemDAO->find(true);
			$discount = COrders::getStorePrice($markup, $itemDAO, 1);
		}
		else
		{
			$discount = $itemDAO->store_price;
		}

		// Remove any existing
		//$Order->removeCouponFreeMealItem();
		// add entree to order if needed
		//$Order->addMenuItem($itemDAO, 1, false, true);

		return $discount;
	}
}

?>