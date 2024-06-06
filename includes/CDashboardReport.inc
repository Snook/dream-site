<?php

require_once('CDreamReport.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/CDashboardReportMenuBased.inc');

class CDashboardNew
{

	static function getMetricsSnapShot($store_id, $date)
	{

		// TODO: test non-existent month of data

		$updateRequired = self::testForUpdateRequired($store_id);

		$dateParts = explode("-", $date);
		$year = $dateParts[0];
		$month = $dateParts[1];
		$currentMonth = $year . "-" . $month . "-" . "01";

		// current month AGR
		$currentAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
		$currentAGRMetrics->store_id = $store_id;
		$currentAGRMetrics->date = $currentMonth;
		$currentAGRMetrics->find(true);

		$currentGuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');
		$currentGuestMetrics->store_id = $store_id;
		$currentGuestMetrics->date = $currentMonth;
		$currentGuestMetrics->find(true);

		$guestMetricsArray = $currentGuestMetrics->toArray();
		self::addToDateGuestCounts($guestMetricsArray, $currentMonth, $store_id);
		$curMonthLastYear = mktime(0, 0, 0, $month, 1, $year - 1);

		$thisMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMonthLastYearAGRMetrics->store_id = $store_id;
		$thisMonthLastYearAGRMetrics->date = date("Y-m-01", $curMonthLastYear);
		$thisMonthLastYearAGRMetrics->find(true);
		if ($thisMonthLastYearAGRMetrics->total_agr != 0)
		{
			$curMonthlastYearAGRDeltaPercent = (($currentAGRMetrics->total_agr - $thisMonthLastYearAGRMetrics->total_agr) / $thisMonthLastYearAGRMetrics->total_agr) * 100;
		}
		else
		{
			$curMonthlastYearAGRDeltaPercent = 0;
		}

		$retVal = array(
			'total_agr' => $currentAGRMetrics->total_agr,
			'curMonthlastYearAGRDeltaPercent' => CTemplate::number_format($curMonthlastYearAGRDeltaPercent, 2),
			'avg_ticket_all' => $currentAGRMetrics->avg_ticket_all,
			'addon_sales_total' => $currentAGRMetrics->addon_sales_total,
			'avg_FT_sales' => CTemplate::divide_and_format($currentAGRMetrics->addon_sales_total, $guestMetricsArray['total_to_date_orders'], 2),
			'instore_signup_percent' => CTemplate::divide_and_format($currentGuestMetrics->instore_signup_total * 100, $guestMetricsArray['total_to_date_orders'], 2),
			'guest_count_total' => (empty($currentGuestMetrics->guest_count_total) ? 0 : $currentGuestMetrics->guest_count_total),
			'orders_per_session' => CTemplate::divide_and_format($currentGuestMetrics->orders_count_all, $currentGuestMetrics->sessions_count_all, 2)
		);

		return array(
			$updateRequired,
			$retVal
		);
	}

	static function updateLastMetricsUpdateTimestamp($store_id)
	{
		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->query("update store set timestamp_last_metrics_update = now() where id = $store_id");
	}

	static function getGrossSales($store, $date)
	{
		$foundentry = false;

		$dateParts = explode("-", $date);
		$day = $dateParts[2];
		$month = $dateParts[1];
		$year = $dateParts[0];
		$duration = "1 MONTH";

		$rows = array();

		CDreamReport::getOrderInfoByMonth($store, $day, $month, $year, $duration, $rows, 1);
		if (isset($rows['grand_total']) && $rows['grand_total'] > 0)
		{
			$foundentry = true;

			if (empty($rows['fundraising_total']))
			{
				$rows['fundraising_total'] = 0;
			}

			$storeobj = DAO_CFactory::create("store");
			$storeobj->id = $store;
			$storeobj->selectAdd();
			$storeobj->selectAdd('grand_opening_date');
			$storeobj->find(true);

			$performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, $store);
			$haspermanceoverride = false;
			if (isset($performance[$store]))
			{
				$haspermanceoverride = true;
			}

			$giftCertValues = CDreamReport::giftCertificatesByType($store, $day, $month, $year, $duration);
			$programdiscounts = CDreamReport::ProgramDiscounts($store, $day, $month, $year, $duration);

			$marketingFee = CRoyaltyReport::$MARKETING_SET_FEE;
			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonth($store, $day, $month, $year, $duration);

			$royaltyFee = 0;
			CDreamReport::calculateFees($rows, $store, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $rows['fundraising_total'], $rows['ltd_menu_item_value'], $rows['subtotal_delivery_fee'], $rows['delivery_tip'], $rows['subtotal_bag_fee'], 0, $marketingFee, $royaltyFee, $storeobj->grand_opening_date, $month, $year);
			$rows['marketing_total'] = $marketingFee;
			$rows['royalty'] = $royaltyFee;
			$rows['total_fees'] = $royaltyFee + $marketingFee;
			$rows['grand_total_less_taxes'] = $rows['grand_total'] - $rows['sales_tax'];

			if ($haspermanceoverride == true)
			{
				$rows['used_performance_override'] = true;
			}
		}

		if ($foundentry == false)
		{
			return null;
		}
		else
		{
			return $rows;
		}
	}

	static function getOccupiedSessionCountForMonth($thisMonthNumber, $thisYear, $type = 'DREAM_TASTE', $filter = 'all_stores')
	{

		$joinClause = "";

		if (is_numeric($filter))
		{
			$joinClause = "join store st on st.id = s.store_id and st.id = $filter";
		}
		else if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = s.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = s.store_id and store.is_corporate_owned = 0 and active = 1  and store.id <> 257 "; // remove the Pig";
		}
		else
		{
			$joinClause = " join store on store.id = s.store_id and store.active = 1  and store.id <> 257 "; // remove the Pig";
		}

		$Sessions = DAO_CFactory::create('session');

		$Sessions->query("select count(DISTINCT s.id) as occupied_count from session s
		join booking b on b.session_id = s.id and b.`status` = 'ACTIVE' and b.is_deleted = 0
		$joinClause
		where MONTH(s.session_start) = $thisMonthNumber and YEAR(s.session_start) = $thisYear and s.session_type = '$type' and s.is_deleted = 0");

		$Sessions->fetch();

		if (empty($Sessions->occupied_count))
		{
			$Sessions->occupied_count = 0;
		}

		return $Sessions->occupied_count;
	}

	static function updateGuestMetrics($store_id, $thisMonthNumber, $thisYear)
	{

		$isCurrentMonth = false;

		$thisMonth = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber, 1, $thisYear)); // '2011-09-01';

		if ($thisMonth == date("Y-m-01"))
		{
			$isCurrentMonth = true;
		}

		$Metric = DAO_CFactory::create('dashboard_metrics_guests');
		$Metric->date = $thisMonth;
		$Metric->store_id = $store_id;

		$Metrics_Exist = false;
		if ($Metric->find(true))
		{
			$Metrics_Exist = true;
		}

		// get guest_counts

		/*

		$digest = DAO_CFactory::create('orders_digest');
		$digest->query("select count( iq.user_id) as total,
				count(if(iq.states like '%NEW%', 1, null)) as new_total,
				count(if(iq.states like '%REACQUIRED%', 1, null)) as reaq_total,
				count(if(iq.states like '%REACQUIRED%' and iq.types like '%TASTE%', 1, null)) as count_reaq_taste,
				count(if(iq.states like '%REACQUIRED%' and iq.types like '%INTRO%', 1, null)) as count_reaq_intro,
				count(if(iq.states like '%NEW%' and iq.types like '%TASTE%', 1, null)) as count_new_taste,
				count(if(iq.states like '%NEW%' and iq.types like '%INTRO%', 1, null))  as count_new_intro,
				count(if(iq.states like '%EXISTING%' and iq.types like '%TASTE%', 1, null))  as count_existing_taste
				from
				(select od.user_id, GROUP_CONCAT(od.user_state) as states, GROUP_CONCAT(od.order_type) as types from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				GROUP BY od.user_id) as iq");

		$digest->fetch();

		$Metric->guest_count_total = $digest->total;
		$Metric->guest_count_existing_regular = $digest->total - ($digest->new_total + $digest->reaq_total + $digest->count_existing_taste);
		$Metric->guest_count_reacquired_regular = $digest->reaq_total - ($digest->count_reaq_taste + $digest->count_reaq_intro);
		$Metric->guest_count_reacquired_intro = $digest->count_reaq_intro;
		$Metric->guest_count_reacquired_taste = $digest->count_reaq_taste;
		$Metric->guest_count_new_regular = $digest->new_total - ($digest->count_new_taste + $digest->count_new_intro);
		$Metric->guest_count_new_taste = $digest->count_new_taste;
		$Metric->guest_count_new_intro = $digest->count_new_intro;
		$Metric->guest_count_existing_taste = $digest->count_existing_taste;
*/

		$Metric->guest_count_total = 0;
		$Metric->guest_count_existing_regular = 0;
		$Metric->guest_count_reacquired_regular = 0;
		$Metric->guest_count_reacquired_intro = 0;
		$Metric->guest_count_reacquired_taste = 0;
		$Metric->guest_count_new_regular = 0;
		$Metric->guest_count_new_taste = 0;
		$Metric->guest_count_new_intro = 0;
		$Metric->guest_count_existing_taste = 0;
		$Metric->guest_count_existing_intro = 0;
		$Metric->guest_count_new_fundraiser = 0;
		$Metric->guest_count_reacquired_fundraiser = 0;
		$Metric->guest_count_existing_fundraiser = 0;

		$digest = DAO_CFactory::create('orders_digest');
		$digest->query("select od.user_state, od.order_type, count( distinct od.user_id) as total
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.user_state, od.order_type");

		while ($digest->fetch())
		{
			if ($digest->user_state == 'NEW')
			{
				if ($digest->order_type == 'REGULAR')
				{
					$Metric->guest_count_new_regular = $digest->total;
				}
				else if ($digest->order_type == 'INTRO')
				{
					$Metric->guest_count_new_intro = $digest->total;
				}
				else if ($digest->order_type == 'TASTE')
				{
					$Metric->guest_count_new_taste = $digest->total;
				}
				else if ($digest->order_type == 'FUNDRAISER')
				{
					$Metric->guest_count_new_fundraiser = $digest->total;
				}
			}
			else if ($digest->user_state == 'REACQUIRED')
			{
				if ($digest->order_type == 'REGULAR')
				{
					$Metric->guest_count_reacquired_regular = $digest->total;
				}
				else if ($digest->order_type == 'INTRO')
				{
					$Metric->guest_count_reacquired_intro = $digest->total;
				}
				else if ($digest->order_type == 'TASTE')
				{
					$Metric->guest_count_reacquired_taste = $digest->total;
				}
				else if ($digest->order_type == 'FUNDRAISER')
				{
					$Metric->guest_count_reacquired_fundraiser = $digest->total;
				}
			}
			else if ($digest->user_state == 'EXISTING')
			{
				if ($digest->order_type == 'TASTE')
				{
					$Metric->guest_count_existing_taste = $digest->total;
				}
				else if ($digest->order_type == 'INTRO')
				{
					$Metric->guest_count_existing_intro = $digest->total;
				}
				else if ($digest->order_type == 'REGULAR')
				{
					$Metric->guest_count_existing_regular = $digest->total;
				}
				else if ($digest->order_type == 'FUNDRAISER')
				{
					$Metric->guest_count_existing_fundraiser = $digest->total;
				}
			}
		}

		$digest105 = DAO_CFactory::create('orders_digest');
		$digest105->query("select count( distinct od.user_id) as total
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear");
		$digest105->fetch();

		$Metric->guest_count_total = $digest105->total;

		// get order_counts
		$digest11 = DAO_CFactory::create('orders_digest');
		$digest11->query("select count( iq.id) as total,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'REGULAR', 1, null)) as count_reaq_regular,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'TASTE', 1, null)) as count_reaq_taste,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'INTRO', 1, null)) as count_reaq_intro,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'FUNDRAISER', 1, null)) as count_reaq_fundraiser,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'TASTE', 1, null)) as count_new_taste,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'INTRO', 1, null))  as count_new_intro,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'REGULAR', 1, null)) as count_new_regular,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'FUNDRAISER', 1, null)) as count_new_fundraiser,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'TASTE', 1, null)) as count_existing_taste,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'REGULAR', 1, null)) as count_existing_regular,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'INTRO', 1, null)) as count_existing_intro,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'FUNDRAISER', 1, null)) as count_existing_fundraiser
		    from
				(select od.id, od.user_state, od.order_type from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear) as iq");

		$digest11->fetch();

		$Metric->orders_count_regular_existing_guests = $digest11->count_existing_regular;
		$Metric->orders_count_regular_new_guests = $digest11->count_new_regular;
		$Metric->orders_count_regular_reacquired_guests = $digest11->count_reaq_regular;
		$Metric->orders_count_intro_new_guests = $digest11->count_new_intro;
		$Metric->orders_count_intro_reacquired_guests = $digest11->count_reaq_intro;
		$Metric->orders_count_intro_existing_guests = $digest11->count_existing_intro;
		$Metric->orders_count_taste_existing_guests = $digest11->count_existing_taste;
		$Metric->orders_count_taste_new_guests = $digest11->count_new_taste;
		$Metric->orders_count_taste_reacquired_guests = $digest11->count_reaq_taste;
		$Metric->orders_count_fundraiser_existing_guests = $digest11->count_existing_fundraiser;
		$Metric->orders_count_fundraiser_new_guests = $digest11->count_new_fundraiser;
		$Metric->orders_count_fundraiser_reacquired_guests = $digest11->count_reaq_fundraiser;

		//	get instore numbers
		$currentMonthClause = "";
		if ($isCurrentMonth)
		{
			if ($store_id)
			{
				$nowTime = time();
				//First adjust time span to local store time

				$storeObj = DAO_CFactory::create('store');
				$storeObj->query("select timezone_id from store where id = $store_id");

				if ($storeObj->N > 0)
				{
					$storeObj->fetch();
					$nowDate = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $nowTime));
				}

				$currentMonthClause = " and od.session_time < '$nowDate' ";
			}
			else
			{
				$currentMonthClause = " and od.session_time < now() ";
			}
		}

		$digest2 = DAO_CFactory::create('orders_digest');
		$digest2->query("select count(od.id), od.order_type as type, od.user_state, count(distinct od2.user_id) as in_store from orders_digest od
				left join orders_digest od2 on od.order_id = od2.in_store_trigger_order and od2.is_deleted = 0
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear $currentMonthClause
				GROUP BY od.order_type, od.user_state");

		$InStoreByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		while ($digest2->fetch())
		{
			if ($digest2->user_state == 'NEW')
			{
				if ($digest2->type == 'REGULAR')
				{
					$InStoreByUser['new_regular'] = $digest2->in_store;
				}
				else if ($digest2->type == 'INTRO')
				{
					$InStoreByUser['new_intro'] = $digest2->in_store;
				}
				else if ($digest2->type == 'TASTE')
				{
					$InStoreByUser['new_taste'] = $digest2->in_store;
				}
				else if ($digest2->type == 'FUNDRAISER')
				{
					$InStoreByUser['new_fundraiser'] = $digest2->in_store;
				}
			}
			else if ($digest2->user_state == 'REACQUIRED')
			{
				if ($digest2->type == 'REGULAR')
				{
					$InStoreByUser['reacquired_regular'] = $digest2->in_store;
				}
				else if ($digest2->type == 'INTRO')
				{
					$InStoreByUser['reacquired_intro'] = $digest2->in_store;
				}
				else if ($digest2->type == 'TASTE')
				{
					$InStoreByUser['reacquired_taste'] = $digest2->in_store;
				}
				else if ($digest2->type == 'FUNDRAISER')
				{
					$InStoreByUser['reacquired_fundraiser'] = $digest2->in_store;
				}
			}
			else if ($digest2->user_state == 'EXISTING')
			{
				if ($digest2->type == 'TASTE')
				{
					$InStoreByUser['existing_taste'] = $digest2->in_store;
				}
				else if ($digest2->type == 'INTRO')
				{
					$InStoreByUser['existing_intro'] = $digest2->in_store;
				}
				else if ($digest2->type == 'REGULAR')
				{
					$InStoreByUser['existing_regular'] = $digest2->in_store;
				}
				else if ($digest2->type == 'FUNDRAISER')
				{
					$InStoreByUser['existing_fundraiser'] = $digest2->in_store;
				}
			}
		}

		$instore_total = 0;
		foreach ($InStoreByUser as $theCount)
		{
			$instore_total += $theCount;
		}

		$Metric->instore_signup_total = $instore_total;
		$Metric->instore_signup_existing_regular = $InStoreByUser['existing_regular'];
		$Metric->instore_signup_existing_taste = $InStoreByUser['existing_taste'];
		$Metric->instore_signup_existing_intro = $InStoreByUser['existing_intro'];
		$Metric->instore_signup_existing_fundraiser = $InStoreByUser['existing_fundraiser'];
		$Metric->instore_signup_reacquired_regular = $InStoreByUser['reacquired_regular'];
		$Metric->instore_signup_reacquired_taste = $InStoreByUser['reacquired_taste'];
		$Metric->instore_signup_reacquired_intro = $InStoreByUser['reacquired_intro'];
		$Metric->instore_signup_reacquired_fundraiser = $InStoreByUser['reacquired_fundraiser'];
		$Metric->instore_signup_new_regular = $InStoreByUser['new_regular'];
		$Metric->instore_signup_new_intro = $InStoreByUser['new_intro'];
		$Metric->instore_signup_new_taste = $InStoreByUser['new_taste'];
		$Metric->instore_signup_new_fundraiser = $InStoreByUser['new_fundraiser'];

		// Session type counts

		$Sessions = DAO_CFactory::create('session');

		$Sessions->query("select count(s.id) as total, count(if(s.session_type = 'STANDARD', 1, null)) as std_count,
				count(if(s.session_class = 'SPECIAL_EVENT', 1, null)) as mfy_count,
				count(if(s.session_class = 'TODD', 1, null)) as taste_count,
				count(if(s.session_class = 'FUNDRAISER', 1, null)) as fundraiser_count 
                        from session s
				where s.is_deleted = 0 and s.store_id = $store_id and MONTH(s.session_start) = $thisMonthNumber and YEAR(s.session_start) = $thisYear
				and s.session_publish_state <> 'SAVED'");

		$Sessions->fetch();

		$Metric->sessions_count_all = $Sessions->total;
		$Metric->sessions_count_regular = $Sessions->std_count;
		$Metric->sessions_count_mfy = $Sessions->mfy_count;
		$Metric->sessions_count_taste = $Sessions->taste_count;
		$Metric->sessions_count_fundraiser = $Sessions->fundraiser_count;

		// Orders per session type
		$digest4 = DAO_CFactory::create('orders_digest');
		$digest4->query("select count(id) AS total,
				count(if(od.session_type = 'TASTE', 1, null)) as taste_count,
				count(if(od.session_type = 'STANDARD', 1, null)) as std_count,
				count(if(od.session_type = 'MADE_FOR_YOU', 1, null)) as mfy_count,
				count(if(od.session_type = 'FUNDRAISER', 1, null)) as fundraiser_count
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear");

		$digest4->fetch();

		$Metric->orders_count_all = $digest4->total;
		$Metric->orders_count_regular = $digest4->std_count;
		$Metric->orders_count_mfy = $digest4->mfy_count;
		$Metric->orders_count_taste = $digest4->taste_count;
		$Metric->orders_count_fundraiser = $digest4->fundraiser_count;

		// get avg servings

		$AvgServingsPerUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$digest3 = DAO_CFactory::create('orders_digest');
		$digest3->query("select od.user_state, od.order_type as type, sum(o.servings_total_count) AS servings  from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				GROUP BY od.user_state, od.order_type");

		$totalServings = 0;
		$totalRegularOrderServings = 0;

		while ($digest3->fetch())
		{

			$totalServings += $digest3->servings;

			if ($digest3->user_state == 'EXISTING')
			{
				if ($digest3->type == 'REGULAR')
				{
					$AvgServingsPerUser['existing_regular'] += $digest3->servings;
					$totalRegularOrderServings += $digest3->servings;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AvgServingsPerUser['existing_intro'] = $digest3->servings;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AvgServingsPerUser['existing_taste'] = $digest3->servings;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AvgServingsPerUser['existing_fundraiser'] = $digest3->servings;
				}
			}
			else if ($digest3->user_state == 'REACQUIRED')
			{
				if ($digest3->type == 'REGULAR')
				{
					$totalRegularOrderServings += $digest3->servings;

					$AvgServingsPerUser['reacquired_regular'] = $digest3->servings;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AvgServingsPerUser['reacquired_intro'] = $digest3->servings;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AvgServingsPerUser['reacquired_taste'] = $digest3->servings;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AvgServingsPerUser['reacquired_fundraiser'] = $digest3->servings;
				}
			}
			else if ($digest3->user_state == 'NEW')
			{
				if ($digest3->type == 'REGULAR')
				{
					$totalRegularOrderServings += $digest3->servings;

					$AvgServingsPerUser['new_regular'] = $digest3->servings;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AvgServingsPerUser['new_intro'] = $digest3->servings;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AvgServingsPerUser['new_taste'] = $digest3->servings;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AvgServingsPerUser['new_fundraiser'] = $digest3->servings;
				}
			}
		}

		$Metric->avg_servings_per_guest_all = (!empty($Metric->orders_count_all) ? $totalServings / $Metric->orders_count_all : 0);

		$regularGuestOrderCount = $Metric->orders_count_regular_existing_guests + $Metric->orders_count_regular_reacquired_guests + $Metric->orders_count_regular_new_guests;

		$Metric->avg_servings_per_guest_regular = (!empty($regularGuestOrderCount) ? $totalRegularOrderServings / $regularGuestOrderCount : 0);

		$Metric->avg_servings_per_guest_existing_regular = (!empty($Metric->orders_count_regular_existing_guests) ? $AvgServingsPerUser['existing_regular'] / $Metric->orders_count_regular_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_taste = (!empty($Metric->orders_count_taste_existing_guests) ? $AvgServingsPerUser['existing_taste'] / $Metric->orders_count_taste_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_intro = (!empty($Metric->orders_count_intro_existing_guests) ? $AvgServingsPerUser['existing_intro'] / $Metric->orders_count_intro_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_fundraiser = (!empty($Metric->orders_count_fundraiser_existing_guests) ? $AvgServingsPerUser['existing_fundraiser'] / $Metric->orders_count_fundraiser_existing_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_regular = (!empty($Metric->orders_count_regular_reacquired_guests) ? $AvgServingsPerUser['reacquired_regular'] / $Metric->orders_count_regular_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_taste = (!empty($Metric->orders_count_taste_reacquired_guests) ? $AvgServingsPerUser['reacquired_taste'] / $Metric->orders_count_taste_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_intro = (!empty($Metric->orders_count_intro_reacquired_guests) ? $AvgServingsPerUser['reacquired_intro'] / $Metric->orders_count_intro_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_fundraiser = (!empty($Metric->orders_count_fundraiser_reacquired_guests) ? $AvgServingsPerUser['reacquired_fundraiser'] / $Metric->orders_count_fundraiser_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_new_regular = (!empty($Metric->orders_count_regular_new_guests) ? $AvgServingsPerUser['new_regular'] / $Metric->orders_count_regular_new_guests : 0);
		$Metric->avg_servings_per_guest_new_taste = (!empty($Metric->orders_count_taste_new_guests) ? $AvgServingsPerUser['new_taste'] / $Metric->orders_count_taste_new_guests : 0);
		$Metric->avg_servings_per_guest_new_intro = (!empty($Metric->orders_count_intro_new_guests) ? $AvgServingsPerUser['new_intro'] / $Metric->orders_count_intro_new_guests : 0);
		$Metric->avg_servings_per_guest_new_fundraiser = (!empty($Metric->orders_count_fundraiser_new_guests) ? $AvgServingsPerUser['new_fundraiser'] / $Metric->orders_count_fundraiser_new_guests : 0);

		// Converted Guests

		/*
		 *
		 * VERSION 1
		$cutOffDate = date("Y-m-d H:i:s", mktime(0,0,0, $thisMonthNumber + 2, 1, $thisYear));
		$digest5->query("select count(distinct iq.user_id) as converted_guests from
				(select od.user_id, od.session_time from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber
				and YEAR(od.session_time) = $thisYear and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')) as iq
				join orders_digest od2 on iq.user_id = od2.user_id where od2.original_order_time > od2.session_time and od2.original_order_time < '$cutOffDate'");

		*/

		/*
		 *  VERSION 2
		 *
		$digest5->query("select count(distinct iq.user_id) as converted_guests from
		(select od.user_id, od.session_time, od.original_order_time from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber
				and YEAR(od.session_time) = $thisYear and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')) as iq
				join orders_digest od2 on iq.user_id = od2.user_id
				join orders o on od2.order_id = o.id and o.servings_total_count > 35
				where od2.original_order_time > iq.original_order_time and od2.original_order_time < DATE_ADD(iq.session_time,INTERVAL 7 DAY)");


		$digest5->fetch();
		*/

		/*
		 *
		 * version 3

           */
		$digest5 = DAO_CFactory::create('orders_digest');

		$Metric->converted_guests = $Metric->instore_signup_new_regular + $Metric->instore_signup_new_intro + $Metric->instore_signup_new_taste + $Metric->instore_signup_new_fundraiser + $Metric->instore_signup_reacquired_intro + $Metric->instore_signup_reacquired_taste + $Metric->instore_signup_reacquired_regular + $Metric->instore_signup_reacquired_fundraiser;

		$digest5->query("select count(distinct od.user_id) as to_date from orders_digest od
							where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.session_time < now()
								and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')");

		$digest5->fetch();

		// Conversion rate
		$totalNewGuest = $digest5->to_date;

		$Metric->conversion_rate = ($totalNewGuest != 0 ? $Metric->converted_guests / $totalNewGuest : 0);

		$Metric->conversion_rate *= 100;

		/*
		 *   New Version - review required prior to release
		 *

		$digest5 = DAO_CFactory::create('orders_digest');


		$digest5->query("select od.user_id, od.user_state,
                    		od2.original_order_time as fu_order_time, od2.order_id as 'follow_up_order_id', o2.servings_total_count as 'follow_up_order_servings' from orders_digest od
                    		join orders o on o.id = od.order_id
                    		left join orders_digest od2 on od.order_id = od2.in_store_trigger_order and od2.is_deleted = 0
                    		left join orders o2 on o2.id = od2.order_id
                    		where MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.store_id = $store_id and od.is_deleted = 0 and od.user_state in ('REACQUIRED', 'NEW') and not isnull(od2.id) and o2.servings_total_count  >= 36
                    		group by od.order_id");


		$Metric->converted_guests = $digest5->N;


		// Conversion rate
		$totalNewGuest = $digest5->to_date;

		$Metric->conversion_rate = ($totalNewGuest != 0 ?  $Metric->converted_guests / $totalNewGuest : 0);

		$Metric->conversion_rate *= 100;
*/

		/*
		// 1 month drop off
		$digest6 = DAO_CFactory::create('orders_digest');

		$inactivity_cutoff = date("Y-m-d H:i:s", mktime(0,0,0,$thisMonthNumber, 1, $thisYear));
		$activity_cutoff = date("Y-m-d H:i:s", mktime(0,0,0,$thisMonthNumber - 1, 1, $thisYear));


		$digest6->query("select count(distinct user_id) as one_month_dropoff from orders_digest od where od.user_id in
				(select iq.user_id from
				(select user_id, max(session_time) as max_session, max(original_order_time) as max_order, count(id) from orders_digest
				where store_id = $store_id and is_deleted = 0
				group by user_id) as iq
				where iq.max_session < '$inactivity_cutoff' and iq.max_order < '$inactivity_cutoff')
				and od.session_time > '$activity_cutoff' and od.user_state = 'EXISTING' and od.order_type = 'REGULAR' and od.store_id = $store_id");

		$digest6->fetch();

*/
		$Metric->one_month_drop_off = 0; //$digest6->one_month_dropoff;

		/*
				// 2 month drop off
				$digest7 = DAO_CFactory::create('orders_digest');

				$inactivity_cutoff = date("Y-m-d H:i:s", mktime(0,0,0,$thisMonthNumber -1, 1, $thisYear));
				$activity_cutoff = date("Y-m-d H:i:s", mktime(0,0,0,$thisMonthNumber -2 , 1, $thisYear));


				$digest7->query("select count(distinct user_id) as two_month_dropoff from orders_digest od where od.user_id in
						(select iq.user_id from
						(select user_id, max(session_time) as max_session, max(original_order_time) as max_order, count(id) from orders_digest
						where store_id = $store_id and is_deleted = 0
						group by user_id) as iq
						where iq.max_session < '$inactivity_cutoff' and iq.max_order < '$inactivity_cutoff')
						and od.session_time > '$activity_cutoff' and od.user_state = 'EXISTING' and od.order_type = 'REGULAR' and od.store_id = $store_id");

				$digest7->fetch();
		*/

		$Metric->two_month_drop_off = 0; //$digest7->two_month_dropoff;

		$AAVcutOff = "now()";
		if (!$isCurrentMonth)
		{
			$AAVcutOffTS = mktime(0, 0, 0, $thisMonthNumber + 1, 1, $thisYear);
			$AAVcutOff = date("'Y-m-d H:i:s'", $AAVcutOffTS);
		}

		// average annual visits
		$digest8 = DAO_CFactory::create('orders_digest');
		$digest8->query("select count(id) / count(DISTINCT user_id) as avg_annual_visits from orders_digest
				where session_time < $AAVcutOff and session_time > DATE_SUB($AAVcutOff, INTERVAL 1 YEAR) and is_deleted = 0 and store_id = $store_id");

		$digest8->fetch();

		$Metric->average_annual_visits = $digest8->avg_annual_visits;

		$digest801 = DAO_CFactory::create('orders_digest');
		$digest801->query("select count(id) / count(DISTINCT user_id) as average_annual_regular_visits from orders_digest
				where session_time < $AAVcutOff and session_time > DATE_SUB($AAVcutOff, INTERVAL 1 YEAR) and is_deleted = 0 and store_id = $store_id and order_type = 'REGULAR'");

		$digest801->fetch();

		$Metric->average_annual_regular_visits = $digest801->average_annual_regular_visits;

		//$lost_guests_at_45_days
		if ($isCurrentMonth)
		{
			$period_cutoff = date("Y-m-d H:i:s");
		}
		else
		{
			$period_cutoff = date("Y-m-d H:i:s", mktime(0, 0, 0, $thisMonthNumber + 1, 1, $thisYear));
		}

		$digest9 = DAO_CFactory::create('orders_digest');
		$digest9->query("select count(DISTINCT od2.user_id) as non_lost_guests from orders_digest od2 where od2.original_order_time < '$period_cutoff'
				and od2.session_time > '$period_cutoff' and od2.store_id = $store_id and od2.is_deleted = 0 and od2.user_id in
				(select distinct od.user_id from orders_digest od where session_time < '$period_cutoff'
				and session_time >= DATE_SUB('$period_cutoff' ,INTERVAL 45 DAY) and od.store_id = $store_id  and od.is_deleted = 0)");

		$digest9->fetch();

		$digest10 = DAO_CFactory::create('orders_digest');
		$digest10->query("select count(distinct od.user_id) as total_in_store from orders_digest od where session_time < '$period_cutoff'
				and session_time >= DATE_SUB('$period_cutoff' ,INTERVAL 45 DAY) and od.store_id = $store_id  and od.is_deleted = 0");

		$digest10->fetch();

		$Metric->lost_guests_at_45_days = $digest10->total_in_store - $digest9->non_lost_guests;

		$digest11 = DAO_CFactory::create('orders_digest');
		$digest11->query("select sum(o.servings_total_count) as total_servings, sum(o.menu_items_total_count) as total_items from orders_digest od
                        join orders o on o.id = od.order_id
                        where MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.store_id = $store_id and od.is_deleted = 0 ");

		$digest11->fetch();

		$Metric->total_servings_sold = $digest11->total_servings;
		$Metric->total_items_sold = $digest11->total_items;

		if ($Metrics_Exist)
		{
			$Metric->update();
		}
		else
		{
			$Metric->insert();
		}
	}

	static function addToDateGuestCounts(&$metricsArray, $date, $store)
	{

		$dateParts = explode("-", $date);

		$thisYear = $dateParts[0];
		$thisMonthNumber = intval($dateParts[1]);

		$digest = DAO_CFactory::create('orders_digest');

		if (is_numeric($store))
		{

			$digest->query("select count(od.id) as to_date, od.order_type as type, od.user_state from orders_digest od
			where od.is_deleted = 0 and od.store_id = $store and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.session_time < now()
			GROUP BY od.order_type, od.user_state");
		}
		else
		{
			if ($store == 'corp_stores')
			{
				$joinClause = " join store on store.id = od.store_id and store.is_corporate_owned = 1 and active = 1 ";
			}
			else if ($store == 'non_corp_stores')
			{
				$joinClause = " join store on store.id = od.store_id and store.is_corporate_owned = 0 and active = 1 and store.id <> 257 "; // remove the Pig
			}
			else// all stores
			{
				$joinClause = " join store on store.id = od.store_id and store.active = 1 and store.id <> 257 "; // remove the Pig
			}

			$digest->query("select count(od.id) as to_date, od.order_type as type, od.user_state from orders_digest od
						$joinClause
					where od.is_deleted = 0 and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.session_time < now()
					GROUP BY od.order_type, od.user_state");
		}

		$metricsArray["existing_regular_to_date_orders"] = 0;
		$metricsArray["existing_taste_to_date_orders"] = 0;
		$metricsArray["existing_intro_to_date_orders"] = 0;
		$metricsArray["existing_fundraiser_to_date_orders"] = 0;
		$metricsArray["reacquired_regular_to_date_orders"] = 0;
		$metricsArray["reacquired_intro_to_date_orders"] = 0;
		$metricsArray["reacquired_taste_to_date_orders"] = 0;
		$metricsArray["reacquired_fundraiser_to_date_orders"] = 0;
		$metricsArray["new_regular_to_date_orders"] = 0;
		$metricsArray["new_intro_to_date_orders"] = 0;
		$metricsArray["new_taste_to_date_orders"] = 0;
		$metricsArray["new_fundraiser_to_date_orders"] = 0;

		$totalGuests = 0;

		while ($digest->fetch())
		{
			$totalGuests += $digest->to_date;

			if ($digest->user_state == 'NEW')
			{
				if ($digest->type == 'REGULAR')
				{
					$metricsArray['new_regular_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'INTRO')
				{
					$metricsArray['new_intro_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'TASTE')
				{
					$metricsArray['new_taste_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'FUNDRAISER')
				{
					$metricsArray['new_fundraiser_to_date_orders'] = $digest->to_date;
				}
			}
			else if ($digest->user_state == 'REACQUIRED')
			{
				if ($digest->type == 'REGULAR')
				{
					$metricsArray['reacquired_regular_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'INTRO')
				{
					$metricsArray['reacquired_intro_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'TASTE')
				{
					$metricsArray['reacquired_taste_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'FUNDRAISER')
				{
					$metricsArray['reacquired_fundraiser_to_date_orders'] = $digest->to_date;
				}
			}
			else if ($digest->user_state == 'EXISTING')
			{
				if ($digest->type == 'TASTE')
				{
					$metricsArray['existing_taste_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'INTRO')
				{
					$metricsArray['existing_intro_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'REGULAR')
				{
					$metricsArray['existing_regular_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'FUNDRAISER')
				{
					$metricsArray['existing_fundraiser_to_date_orders'] = $digest->to_date;
				}
			}
		}

		$metricsArray["total_to_date_orders"] = $totalGuests;
	}

	static function updateAGRMetrics($store_id, $thisMonthNumber, $thisYear)
	{

		$thisMonth = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber, 1, $thisYear)); // '2011-09-01';

		$sales = self::getGrossSales($store_id, $thisMonth);

		if (!isset($sales) || !isset($sales['total_less_discounts']))
		{
			$sales['total_less_discounts'] = 0;
		}

		$Metric = DAO_CFactory::create('dashboard_metrics_agr');
		$Metric->date = $thisMonth;
		$Metric->store_id = $store_id;

		$Metrics_Exist = false;

		if ($Metric->find(true))
		{
			$Metrics_Exist = true;
		}

		$Metric->total_agr = $sales['total_less_discounts'];

		$adjustments = (!empty($sales['adjustments']) ? $sales['adjustments'] : 0);
		$discounts = (!empty($sales['discounts']) ? $sales['discounts'] : 0);
		$Metric->sales_adjustments_total = $adjustments - $discounts;

		// get agr by session_type

		$Metric->agr_by_session_taste = 0;
		$Metric->revenue_by_session_taste = 0;
		$Metric->agr_by_session_mfy = 0;
		$Metric->revenue_by_session_mfy = 0;
		$Metric->agr_by_session_standard = 0;
		$Metric->revenue_by_session_standard = 0;
		$Metric->agr_by_session_fundraiser = 0;
		$Metric->revenue_by_session_fundraiser = 0;

		$digest = DAO_CFactory::create('orders_digest');
		$digest->query("select od.session_type as type, sum(od.agr_total) as sum_agr, sum(o.grand_total - o.subtotal_all_taxes) as revenue from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.session_type");

		while ($digest->fetch())
		{
			if ($digest->type == 'TASTE')
			{
				$Metric->agr_by_session_taste = $digest->sum_agr;
				$Metric->revenue_by_session_taste = $digest->revenue;
			}
			else if ($digest->type == 'MADE_FOR_YOU')
			{
				$Metric->agr_by_session_mfy = $digest->sum_agr;
				$Metric->revenue_by_session_mfy = $digest->revenue;
			}
			else if ($digest->type == 'STANDARD')
			{
				$Metric->agr_by_session_standard = $digest->sum_agr;
				$Metric->revenue_by_session_standard = $digest->revenue;
			}
			else if ($digest->type == 'FUNDRAISER')
			{
				$Metric->agr_by_session_fundraiser = $digest->sum_agr;
				$Metric->revenue_by_session_fundraiser = $digest->revenue;
			}
		}

		//	get agr by order type

		$Metric->agr_by_order_regular = 0;
		$Metric->revenue_by_order_regular = 0;
		$Metric->agr_by_order_intro = 0;
		$Metric->revenue_by_order_intro = 0;
		$Metric->agr_by_order_taste = 0;
		$Metric->revenue_by_order_taste = 0;
		$Metric->agr_by_order_fundraiser = 0;
		$Metric->revenue_by_order_fundraiser = 0;

		$digest2 = DAO_CFactory::create('orders_digest');
		$digest2->query("select od.order_type as type, sum(od.agr_total) as sum_agr, sum(o.grand_total - o.subtotal_all_taxes) as revenue  from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.order_type");

		while ($digest2->fetch())
		{
			if ($digest2->type == 'REGULAR')
			{
				$Metric->agr_by_order_regular = $digest2->sum_agr;
				$Metric->revenue_by_order_regular = $digest2->revenue;
			}
			else if ($digest2->type == 'INTRO')
			{
				$Metric->agr_by_order_intro = $digest2->sum_agr;
				$Metric->revenue_by_order_intro = $digest2->revenue;
			}
			else if ($digest2->type == 'TASTE')
			{
				$Metric->agr_by_order_taste = $digest2->sum_agr;
				$Metric->revenue_by_order_taste = $digest2->revenue;
			}
			else if ($digest2->type == 'FUNDRAISER')
			{
				$Metric->agr_by_order_fundraiser = $digest2->sum_agr;
				$Metric->revenue_by_order_fundraiser = $digest2->revenue;
			}
		}

		// get agr by user state

		$AgrByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$RevenueByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$digest3 = DAO_CFactory::create('orders_digest');
		$digest3->query("select od.order_type as type, od.user_state, sum(od.agr_total) as sum_agr, sum(o.grand_total - o.subtotal_all_taxes) as revenue from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.order_type, od.user_state");

		while ($digest3->fetch())
		{
			if ($digest3->user_state == 'EXISTING')
			{

				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['existing_regular'] = $digest3->sum_agr;
					$RevenueByUser['existing_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AgrByUser['existing_intro'] = $digest3->sum_agr;
					$RevenueByUser['existing_intro'] = $digest3->revenue;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AgrByUser['existing_taste'] = $digest3->sum_agr;
					$RevenueByUser['existing_taste'] = $digest3->revenue;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AgrByUser['existing_fundraiser'] = $digest3->sum_agr;
					$RevenueByUser['existing_fundraiser'] = $digest3->revenue;
				}
			}
			else if ($digest3->user_state == 'REACQUIRED')
			{
				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['reacquired_regular'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AgrByUser['reacquired_intro'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_intro'] = $digest3->revenue;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AgrByUser['reacquired_taste'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_taste'] = $digest3->revenue;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AgrByUser['reacquired_fundraiser'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_fundraiser'] = $digest3->revenue;
				}
			}
			else if ($digest3->user_state == 'NEW')
			{
				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['new_regular'] = $digest3->sum_agr;
					$RevenueByUser['new_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'INTRO')
				{
					$AgrByUser['new_intro'] = $digest3->sum_agr;
					$RevenueByUser['new_intro'] = $digest3->revenue;
				}
				else if ($digest3->type == 'TASTE')
				{
					$AgrByUser['new_taste'] = $digest3->sum_agr;
					$RevenueByUser['new_taste'] = $digest3->revenue;
				}
				else if ($digest3->type == 'FUNDRAISER')
				{
					$AgrByUser['new_fundraiser'] = $digest3->sum_agr;
					$RevenueByUser['new_fundraiser'] = $digest3->revenue;
				}
			}
		}

		$Metric->agr_by_guest_existing_regular = $AgrByUser['existing_regular'];
		$Metric->agr_by_guest_existing_taste = $AgrByUser['existing_taste'];
		$Metric->agr_by_guest_existing_intro = $AgrByUser['existing_intro'];
		$Metric->agr_by_guest_existing_fundraiser = $AgrByUser['existing_fundraiser'];
		$Metric->agr_by_guest_reacquired_regular = $AgrByUser['reacquired_regular'];
		$Metric->agr_by_guest_reacquired_taste = $AgrByUser['reacquired_taste'];
		$Metric->agr_by_guest_reacquired_intro = $AgrByUser['reacquired_intro'];
		$Metric->agr_by_guest_reacquired_fundraiser = $AgrByUser['reacquired_fundraiser'];
		$Metric->agr_by_guest_new_regular = $AgrByUser['new_regular'];
		$Metric->agr_by_guest_new_taste = $AgrByUser['new_taste'];
		$Metric->agr_by_guest_new_intro = $AgrByUser['new_intro'];
		$Metric->agr_by_guest_new_fundraiser = $AgrByUser['new_fundraiser'];

		$Metric->revenue_by_guest_existing_regular = $RevenueByUser['existing_regular'];
		$Metric->revenue_by_guest_existing_taste = $RevenueByUser['existing_taste'];
		$Metric->revenue_by_guest_existing_intro = $RevenueByUser['existing_intro'];
		$Metric->revenue_by_guest_existing_fundraiser = $RevenueByUser['existing_fundraiser'];
		$Metric->revenue_by_guest_reacquired_regular = $RevenueByUser['reacquired_regular'];
		$Metric->revenue_by_guest_reacquired_taste = $RevenueByUser['reacquired_taste'];
		$Metric->revenue_by_guest_reacquired_intro = $RevenueByUser['reacquired_intro'];
		$Metric->revenue_by_guest_reacquired_fundraiser = $RevenueByUser['reacquired_fundraiser'];
		$Metric->revenue_by_guest_new_regular = $RevenueByUser['new_regular'];
		$Metric->revenue_by_guest_new_taste = $RevenueByUser['new_taste'];
		$Metric->revenue_by_guest_new_intro = $RevenueByUser['new_intro'];
		$Metric->revenue_by_guest_new_fundraiser = $RevenueByUser['new_fundraiser'];

		// average ticket all

		$digest4 = DAO_CFactory::create('orders_digest');
		$digest4->query("select od.order_type as type, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear");
		//and od.order_type = 'REGULAR'");
		// go ahead and get the full average per Jeb 10/3/12
		$digest4->fetch();
		$Metric->avg_ticket_all = $digest4->avg_ticket;

		// average ticket regular

		$digest401 = DAO_CFactory::create('orders_digest');
		$digest401->query("select od.order_type as type, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket_reg from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.order_type = 'REGULAR'
				group by od.order_type");
		$digest401->fetch();

		if (empty($digest401->avg_ticket_reg))
		{
			$digest401->avg_ticket_reg = 0;
		}

		$Metric->avg_ticket_regular = $digest401->avg_ticket_reg;

		// average by user type and order
		$AvgByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$digest5 = DAO_CFactory::create('orders_digest');
		$digest5->query("select od.order_type as type, od.user_state, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.order_type, od.user_state");

		while ($digest5->fetch())
		{
			if ($digest5->user_state == 'EXISTING')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['existing_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'INTRO')
				{
					$AvgByUser['existing_intro'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'TASTE')
				{
					$AvgByUser['existing_taste'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'FUNDRAISER')
				{
					$AvgByUser['existing_fundraiser'] = $digest5->avg_ticket;
				}
			}
			else if ($digest5->user_state == 'REACQUIRED')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['reacquired_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'INTRO')
				{
					$AvgByUser['reacquired_intro'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'TASTE')
				{
					$AvgByUser['reacquired_taste'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'FUNDRAISER')
				{
					$AvgByUser['reacquired_fundraiser'] = $digest5->avg_ticket;
				}
			}
			else if ($digest5->user_state == 'NEW')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['new_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'INTRO')
				{
					$AvgByUser['new_intro'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'TASTE')
				{
					$AvgByUser['new_taste'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'FUNDRAISER')
				{
					$AvgByUser['new_fundraiser'] = $digest5->avg_ticket;
				}
			}
		}

		$Metric->avg_ticket_by_guest_existing_regular = $AvgByUser['existing_regular'];
		$Metric->avg_ticket_by_guest_existing_taste = $AvgByUser['existing_taste'];
		$Metric->avg_ticket_by_guest_existing_intro = $AvgByUser['existing_intro'];
		$Metric->avg_ticket_by_guest_existing_fundraiser = $AvgByUser['existing_fundraiser'];
		$Metric->avg_ticket_by_guest_reacquired_regular = $AvgByUser['reacquired_regular'];
		$Metric->avg_ticket_by_guest_reacquired_taste = $AvgByUser['reacquired_taste'];
		$Metric->avg_ticket_by_guest_reacquired_intro = $AvgByUser['reacquired_intro'];
		$Metric->avg_ticket_by_guest_reacquired_fundraiser = $AvgByUser['reacquired_fundraiser'];
		$Metric->avg_ticket_by_guest_new_regular = $AvgByUser['new_regular'];
		$Metric->avg_ticket_by_guest_new_taste = $AvgByUser['new_taste'];
		$Metric->avg_ticket_by_guest_new_intro = $AvgByUser['new_intro'];
		$Metric->avg_ticket_by_guest_new_fundraiser = $AvgByUser['new_fundraiser'];

		// total addon sales

		$digest7 = DAO_CFactory::create('orders_digest');
		$digest7->query("select od.user_state, sum(od.addon_total) as sum_addon, avg(od.addon_total) as avg_addon from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear");

		$digest7->fetch();

		$Metric->addon_sales_total = $digest7->sum_addon;

		// addon sales by user

		$AddonByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$digest6 = DAO_CFactory::create('orders_digest');
		$digest6->query("select od.order_type as type, od.user_state, sum(od.addon_total) as sum_addon, avg(od.addon_total) as avg_addon from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear
				group by od.order_type, od.user_state ");

		while ($digest6->fetch())
		{
			if ($digest6->user_state == 'EXISTING')
			{

				if ($digest6->type == 'REGULAR')
				{
					$AddonByUser['existing_regular'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'INTRO')
				{
					$AddonByUser['existing_intro'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'TASTE')
				{
					$AddonByUser['existing_taste'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'FUNDRAISER')
				{
					$AddonByUser['existing_fundraiser'] = $digest6->sum_addon;
				}
			}
			else if ($digest6->user_state == 'REACQUIRED')
			{
				if ($digest6->type == 'REGULAR')
				{
					$AddonByUser['reacquired_regular'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'INTRO')
				{
					$AddonByUser['reacquired_intro'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'TASTE')
				{
					$AddonByUser['reacquired_taste'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'FUNDRAISER')
				{
					$AddonByUser['reacquired_fundraiser'] = $digest6->sum_addon;
				}
			}
			else if ($digest6->user_state == 'NEW')
			{
				if ($digest6->type == 'REGULAR')
				{
					$AddonByUser['new_regular'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'INTRO')
				{
					$AddonByUser['new_intro'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'TASTE')
				{
					$AddonByUser['new_taste'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'FUNDRAISER')
				{
					$AddonByUser['new_fundraiser'] = $digest6->sum_addon;
				}
			}
		}

		$Metric->addon_sales_by_guest_existing_regular = $AddonByUser['existing_regular'];
		$Metric->addon_sales_by_guest_existing_taste = $AddonByUser['existing_taste'];
		$Metric->addon_sales_by_guest_existing_intro = $AddonByUser['existing_intro'];
		$Metric->addon_sales_by_guest_existing_fundraiser = $AddonByUser['existing_fundraiser'];
		$Metric->addon_sales_by_guest_reacquired_regular = $AddonByUser['reacquired_regular'];
		$Metric->addon_sales_by_guest_reacquired_taste = $AddonByUser['reacquired_taste'];
		$Metric->addon_sales_by_guest_reacquired_intro = $AddonByUser['reacquired_intro'];
		$Metric->addon_sales_by_guest_reacquired_fundraiser = $AddonByUser['reacquired_fundraiser'];
		$Metric->addon_sales_by_guest_new_regular = $AddonByUser['new_regular'];
		$Metric->addon_sales_by_guest_new_taste = $AddonByUser['new_taste'];
		$Metric->addon_sales_by_guest_new_intro = $AddonByUser['new_intro'];
		$Metric->addon_sales_by_guest_new_fundraiser = $AddonByUser['new_fundraiser'];

		if ($Metrics_Exist)
		{
			$Metric->update();
		}
		else
		{
			$Metric->insert();
		}
	}

	static function testForUpdateRequired($store_id)
	{
		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->query("select timestamp_last_activity, timestamp_last_metrics_update from store where id = $store_id");
		$StoreObj->fetch();

		$lastActivity = (!empty($StoreObj->timestamp_last_activity) ? strtotime($StoreObj->timestamp_last_activity) : 1);
		$lastUpdate = (!empty($StoreObj->timestamp_last_metrics_update) ? strtotime($StoreObj->timestamp_last_metrics_update) : 0);

		if ($lastActivity > $lastUpdate)
		{
			return true;
		}

		return false;
	}

	static function handleCalendarMonthBasedUpdate($store_id, $testDate = false)
	{

		if ($testDate)
		{
			// for calendar based
			$thisMonth = date("n", strtotime($testDate));
			$thisYear = date("Y", strtotime($testDate));
			$dayNum = date("j", strtotime($testDate));
		}
		else
		{
			// for calendar based
			$thisMonth = date("n");
			$thisYear = date("Y");
			$dayNum = date("j");
		}

		// CES 2-9-2018 :  Always calculate the previous month so that the retention count continues to reflect current month sales
		// This should be changed back to the original logic (uncomment the line below) and a new section added that continues to update the
		// retention count until the current month closes
		//if ($dayNum < 8)
		{

			$lastMonthTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear);
			$lastMonth = date("n", $lastMonthTime);
			$lastMonthYear = date("Y", $lastMonthTime);

			if ($testDate)
			{
				echo "Cal-based prev month: " . $lastMonth . "-" . $lastMonthYear . "\r\n";
			}
			else
			{
				self::updateAGRMetrics($store_id, $lastMonth, $lastMonthYear);
				self::updateGuestMetrics($store_id, $lastMonth, $lastMonthYear);
			}
		}

		if ($testDate)
		{
			echo "Cal-based current month: " . $thisMonth . "-" . $thisYear . "\r\n";
		}
		else
		{
			self::updateAGRMetrics($store_id, $thisMonth, $thisYear);
			self::updateGuestMetrics($store_id, $thisMonth, $thisYear);
		}

		$nextMonth = $thisMonth + 1;
		if ($nextMonth > 12)
		{
			$nextMonth = 1;
			$thisYear++;
		}

		if ($testDate)
		{
			echo "Cal-based next month: " . $nextMonth . "-" . $thisYear . "\r\n";
		}
		else
		{
			self::updateAGRMetrics($store_id, $nextMonth, $thisYear);
			self::updateGuestMetrics($store_id, $nextMonth, $thisYear);
		}

		$nextMonth++;
		if ($nextMonth > 12)
		{
			$nextMonth = 1;
			$thisYear++;
		}

		if ($testDate)
		{
			echo "Cal-based future month: " . $nextMonth . "-" . $thisYear . "\r\n";
		}
		else
		{
			self::updateAGRMetrics($store_id, $nextMonth, $thisYear);
			self::updateGuestMetrics($store_id, $nextMonth, $thisYear);
		}
	}

	static function handleMenuMonthBasedUpdate($store_id, $testDate = false)
	{

		if ($testDate)
		{

			$now = date("Y-m-d", strtotime($testDate));
			$menuObj = new CMenu();
			$menuObj->query("SELECT id, menu_start FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
			$menuObj->fetch();
			$curMenuID = $menuObj->id;

			list($start_date, $interval) = CMenu::getMenuStartandInterval($curMenuID);

			$firstOfCalendarMonthTS = strtotime($menuObj->menu_start);
			$periodEndTS = strtotime(date("Y-m-08", strtotime($menuObj->menu_start)));

			$periodStartTS = strtotime($start_date);
			if ($firstOfCalendarMonthTS < $periodStartTS)
			{
				$periodStartTS = $firstOfCalendarMonthTS;
			}
		}
		else
		{
			$now = date("Y-m-d");
			$menuObj = new CMenu();
			$menuObj->query("SELECT id, menu_start FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
			$menuObj->fetch();
			$curMenuID = $menuObj->id;

			list($start_date, $interval) = CMenu::getMenuStartandInterval($curMenuID);

			$firstOfCalendarMonthTS = strtotime($menuObj->menu_start);
			$periodEndTS = strtotime(date("Y-m-08", strtotime($menuObj->menu_start)));

			$periodStartTS = strtotime($start_date);
			if ($firstOfCalendarMonthTS < $periodStartTS)
			{
				$periodStartTS = $firstOfCalendarMonthTS;
			}
		}

		if ($testDate)
		{
			$time = strtotime($testDate);
		}
		else
		{
			$time = time();
		}

		//	$firstofCalendarStr = date("Y-m-d H:i:s", $firstOfCalendarMonthTS);
		//	$periodEndStr = date("Y-m-d H:i:s", $periodEndTS);
		//	$periodStartStr = date("Y-m-d H:i:s", $periodStartTS);
		//	$timeStr = date("Y-m-d H:i:s", $time);

		// CES 2-9-2018 :  Always calculate the previous month so that the retention count continues to reflect current month sales
		// This should be changed back to the original logic (uncomment the line below) and a new section added that continues to update the
		// retention count until the current month closes
		//if ($time >= $periodStartTS && $time < $periodEndTS)
		{
			$lastMonthMenuID = $curMenuID - 1;
			list($last_month_start_date, $last_month_interval) = CMenu::getMenuStartandInterval($lastMonthMenuID);
			$tempMenuLMObj = DAO_CFactory::create('menu');
			$tempMenuLMObj->query("select menu_start from menu where id = $lastMonthMenuID");
			$tempMenuLMObj->fetch();

			if ($testDate)
			{
				echo "Menu-based prev month: " . $tempMenuLMObj->menu_start . " | " . $last_month_start_date . " interval " . $last_month_interval . "\r\n";
			}
			else
			{
				CDashboardMenuBased::updateAGRMetrics($store_id, $tempMenuLMObj->menu_start, $last_month_start_date, $last_month_interval);
				CDashboardMenuBased::updateGuestMetrics($store_id, $tempMenuLMObj->menu_start, $last_month_start_date, $last_month_interval);
			}
		}

		$tempMenuObj = DAO_CFactory::create('menu');
		$tempMenuObj->query("select menu_start from menu where id = $curMenuID");
		$tempMenuObj->fetch();

		if ($testDate)
		{
			echo "Menu-based current month: " . $tempMenuObj->menu_start . " | " . $start_date . " interval " . $interval . "\r\n";
		}
		else
		{
			CDashboardMenuBased::updateAGRMetrics($store_id, $tempMenuObj->menu_start, $start_date, $interval);
			CDashboardMenuBased::updateGuestMetrics($store_id, $tempMenuObj->menu_start, $start_date, $interval);
		}

		$nextMonthID = $curMenuID + 1;
		list($next_month_start_date, $next_month_interval) = CMenu::getMenuStartandInterval($nextMonthID);
		$tempMenuObj->query("select menu_start from menu where id = $nextMonthID");
		$tempMenuObj->fetch();

		if ($testDate)
		{
			echo "Menu-based next month: " . $tempMenuObj->menu_start . " | " . $next_month_start_date . " interval " . $next_month_interval . "\r\n";
		}
		else
		{
			CDashboardMenuBased::updateAGRMetrics($store_id, $tempMenuObj->menu_start, $next_month_start_date, $next_month_interval);
			CDashboardMenuBased::updateGuestMetrics($store_id, $tempMenuObj->menu_start, $next_month_start_date, $next_month_interval);
		}

		$nextMonthID = $nextMonthID + 1;
		list($next_month_start_date, $next_month_interval) = CMenu::getMenuStartandInterval($nextMonthID);
		$tempMenuObj->query("select menu_start from menu where id = $nextMonthID");
		$tempMenuObj->fetch();

		if ($testDate)
		{
			echo "Menu-based future month: " . $tempMenuObj->menu_start . " | " . $next_month_start_date . " interval " . $next_month_interval . "\r\n";
		}
		else
		{
			CDashboardMenuBased::updateAGRMetrics($store_id, $tempMenuObj->menu_start, $next_month_start_date, $next_month_interval);
			CDashboardMenuBased::updateGuestMetrics($store_id, $tempMenuObj->menu_start, $next_month_start_date, $next_month_interval);
		}
	}

	static function updateMetricsForStoreIfNeeded($store_id, $forceUpdate = false, $testDate = false)
	{
		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->query("select timestamp_last_activity, timestamp_last_metrics_update from store where id = $store_id");

		$lastActivity = !empty($StoreObj->timestamp_last_activity) ? strtotime($StoreObj->timestamp_last_activity) : 1;
		$lastUpdate = !empty($StoreObj->timestamp_last_metrics_update) ? strtotime($StoreObj->timestamp_last_metrics_update) : 0;

		if ($lastActivity > $lastUpdate || $forceUpdate)
		{

			if ($testDate)
			{
				echo "\r\n" . $testDate . "\r\n";
			}

			self::handleCalendarMonthBasedUpdate($store_id, $testDate);
			self::handleMenuMonthBasedUpdate($store_id, $testDate);
			self::updateLastMetricsUpdateTimestamp($store_id);

			return true;
		}

		return false;
	}

	static function customRankingforExcludedStore($rankingObj)
	{

		$excludedStore = $rankingObj->store_id;

		$parts = explode("-", $rankingObj->date);

		$thisMonthNumber = intval($parts[1]);
		$thisYear = $parts[0];
		$thisMonth = $rankingObj->date;

		// agr
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select store_id, total_agr from dashboard_metrics_agr where date = '$thisMonth'  and store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->agr = $thisMetricObj->total_agr;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, agr from dashboard_metrics_rankings where date = '{$rankingObj->date}'
					union
				select $excludedStore, {$rankingObj->agr} as agr
				order by agr desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->agr)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->agr_rank = $rank;

		// agr change
		$sameMonthLastYear = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber, 1, $thisYear - 1));

		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select dma.store_id, ((dma.total_agr - dma2.total_agr) /  dma2.total_agr) * 100 as delta from dashboard_metrics_agr dma
				join dashboard_metrics_agr dma2 on dma2.store_id = dma.store_id and dma2.date = '$sameMonthLastYear'
				where dma.date = '$thisMonth' and dma.store_id = $excludedStore ");
		$thisMetricObj->fetch();
		$rankingObj->agr_percent_change = $thisMetricObj->delta;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, agr_percent_change from dashboard_metrics_rankings where date = '{$rankingObj->date}'
					union
				select $excludedStore, {$rankingObj->agr_percent_change} as agr_percent_change
				order by agr_percent_change desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->agr_percent_change)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->agr_percent_change_rank = $rank;

		// in_store
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id,
				dmg.instore_signup_existing + dmg.instore_signup_new_regular + dmg.instore_signup_reacquired_regular as in_store_count,
				to_date.to_date_count,
				((dmg.instore_signup_existing + dmg.instore_signup_new_regular + dmg.instore_signup_reacquired_regular) / to_date.to_date_count) * 100 as sign_up_rate
				from dashboard_metrics_guests dmg
				left join (select od.store_id, count(od.id) as to_date_count from orders_digest od where od.is_deleted = 0 and MONTH(od.session_time) = $thisMonthNumber
				and YEAR(od.session_time) = $thisYear and od.session_time < now()
				and od.order_type = 'REGULAR'
				group by od.store_id) as to_date on to_date.store_id = dmg.store_id
				where dmg.date = '$thisMonth' and dmg.store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->in_store_signup = $thisMetricObj->sign_up_rate;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, in_store_signup from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->in_store_signup} as in_store_signup
		order by in_store_signup desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->in_store_signup)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->in_store_signup_rank = $rank;

		// guest visits
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id, dmg.guest_count_total  from dashboard_metrics_guests dmg
				where dmg.date = '$thisMonth'  and dmg.store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->guest_visits = $thisMetricObj->guest_count_total;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, guest_visits from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->guest_visits} as guest_visits
		order by guest_visits desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->guest_visits)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->guest_visits_rank = $rank;

		// guest per session
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id,  dmg.orders_count_all / dmg.sessions_count_all as avg_guests_per_session from dashboard_metrics_guests dmg
				where dmg.date = '$thisMonth'  and dmg.store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->avg_visits_per_session = $thisMetricObj->avg_guests_per_session;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, avg_visits_per_session from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->avg_visits_per_session} as avg_visits_per_session
		order by avg_visits_per_session desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->avg_visits_per_session)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->avg_visits_per_session_rank = $rank;

		// avg ticket
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select store_id, avg_ticket_regular from dashboard_metrics_agr where date = '$thisMonth' and store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->avg_ticket = $thisMetricObj->avg_ticket_regular;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, avg_ticket from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->avg_ticket} as avg_ticket
		order by avg_ticket desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->avg_ticket)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->avg_ticket_rank = $rank;

		// addon sales
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select store_id, addon_sales_total from dashboard_metrics_agr where date = '$thisMonth'  and store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->addon_sales = $thisMetricObj->addon_sales_total;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, addon_sales from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->addon_sales} as addon_sales
		order by addon_sales desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->addon_sales)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->addon_sales_rank = $rank;

		// servings per guest sales
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select store_id, avg_servings_per_guest_regular from dashboard_metrics_guests where date = '$thisMonth'  and store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->servings_per_guest = $thisMetricObj->avg_servings_per_guest_regular;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, servings_per_guest from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->servings_per_guest} as servings_per_guest
		order by servings_per_guest desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->servings_per_guest)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->servings_per_guest_rank = $rank;

		// converted guests
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id, dmg.converted_guests  from dashboard_metrics_guests dmg
				where dmg.date = '$thisMonth'  and store_id = $excludedStore");
		$thisMetricObj->fetch();
		$rankingObj->converted_guests = $thisMetricObj->converted_guests;

		$ranker = DAO_CFactory::create('dashboard_metrics_rankings');
		$ranker->query("select store_id, converted_guests from dashboard_metrics_rankings where date = '{$rankingObj->date}'
		union
		select $excludedStore, {$rankingObj->converted_guests} as converted_guests
		order by converted_guests desc");
		$rank = 0;
		$lastVal = false;
		while ($ranker->fetch())
		{
			if ($lastVal != $ranker->converted_guests)
			{
				++$rank;
			}

			if ($ranker->store_id == $excludedStore)
			{
				break;
			}
		}
		$rankingObj->converted_guests_rank = $rank;
	}

	static function rankStores($overrideMonth = false)
	{

		if ($overrideMonth)
		{
			$parts = explode("-", $overrideMonth);

			$thisMonthNumber = intval($parts[1]);
			$thisYear = $parts[0];
			$thisMonth = $overrideMonth;
		}
		else
		{
			$thisMonthNumber = date("n");
			$thisYear = date("Y");
			$thisMonth = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber, 1, $thisYear)); // '2011-09-01';
		}

		$storeRanksArray = array();

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select id from store where active = 1 and store_type <> 'DISTRIBUTION_CENTER'");

		while ($storeObj->fetch())
		{
			$thisStoreID = $storeObj->id;
			$thisRankingObj = DAO_CFactory::create('dashboard_metrics_rankings');
			$thisRankingObj->date = $thisMonth;
			$thisRankingObj->store_id = $thisStoreID;

			$thisRankingObj->find(true);

			$storeRanksArray[$thisStoreID] = $thisRankingObj;
		}

		// agr
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select store_id, total_agr from dashboard_metrics_agr where date = '$thisMonth'  and store_id <> 257 order by total_agr desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];

			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->agr = $thisMetricObj->total_agr;
			if ($lastVal != $thisMetricObj->total_agr)
			{
				++$rank;
			}

			$didley->agr_rank = $rank;
			$lastVal = $thisMetricObj->total_agr;
		}

		// agr change
		$sameMonthLastYear = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber, 1, $thisYear - 1));

		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select dma.store_id, ((dma.total_agr - dma2.total_agr) /  dma2.total_agr) * 100 as delta from dashboard_metrics_agr dma
				join dashboard_metrics_agr dma2 on dma2.store_id = dma.store_id and dma2.date = '$sameMonthLastYear'
				where dma.date = '$thisMonth' and dma.store_id <> 257 order by delta desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->agr_percent_change = $thisMetricObj->delta;
			if ($lastVal != $thisMetricObj->delta)
			{
				++$rank;
			}

			$didley->agr_percent_change_rank = $rank;
			$lastVal = $thisMetricObj->delta;
		}

		// in_store
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id,
							dmg.instore_signup_existing_regular as in_store_count,
							to_date.to_date_count,
							(dmg.instore_signup_existing_regular  / to_date.to_date_count) * 100 as sign_up_rate
							 from dashboard_metrics_guests dmg
							left join (select od.store_id, count(od.id) as to_date_count from orders_digest od where od.is_deleted = 0 and MONTH(od.session_time) = $thisMonthNumber
							 and YEAR(od.session_time) = $thisYear and od.session_time < now()
							and od.order_type = 'REGULAR' and od.user_state = 'EXISTING'
							group by od.store_id) as to_date on to_date.store_id = dmg.store_id
							where dmg.date = '$thisMonth' and dmg.store_id <> 257 order by sign_up_rate desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->in_store_signup = $thisMetricObj->sign_up_rate;
			if ($lastVal != $thisMetricObj->sign_up_rate)
			{
				++$rank;
			}

			$didley->in_store_signup_rank = $rank;
			$lastVal = $thisMetricObj->sign_up_rate;
		}

		// guest visits
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id, dmg.guest_count_total  from dashboard_metrics_guests dmg
				where dmg.date = '$thisMonth'  and dmg.store_id <> 257 order by guest_count_total desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->guest_visits = $thisMetricObj->guest_count_total;
			if ($lastVal != $thisMetricObj->guest_count_total)
			{
				++$rank;
			}

			$didley->guest_visits_rank = $rank;
			$lastVal = $thisMetricObj->guest_count_total;
		}

		// guest per session
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id,  dmg.orders_count_all / dmg.sessions_count_all as avg_guests_per_session from dashboard_metrics_guests dmg
				where dmg.date = '$thisMonth'  and dmg.store_id <> 257 order by avg_guests_per_session desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->avg_visits_per_session = $thisMetricObj->avg_guests_per_session;
			if ($lastVal != $thisMetricObj->avg_guests_per_session)
			{
				++$rank;
			}

			$didley->avg_visits_per_session_rank = $rank;
			$lastVal = $thisMetricObj->avg_guests_per_session;
		}

		// avg ticket
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		$thisMetricObj->query("select store_id, avg_ticket_regular from dashboard_metrics_agr where date = '$thisMonth' and store_id <> 257 order by avg_ticket_regular desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->avg_ticket = $thisMetricObj->avg_ticket_regular;
			if ($lastVal != $thisMetricObj->avg_ticket_regular)
			{
				++$rank;
			}

			$didley->avg_ticket_rank = $rank;
			$lastVal = $thisMetricObj->avg_ticket_regular;
		}

		// addon sales
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr');
		/*
		$thisMetricObj->query("select dma.store_id, dma.addon_sales_total, dmg.orders_count_all, dma.addon_sales_total / dmg.orders_count_all as avg_addon_sales from dashboard_metrics_agr dma
		join dashboard_metrics_guests dmg on dmg.date = '$thisMonth' and dmg.store_id = dma.store_id
		where dma.date = '$thisMonth' and dma.store_id <> 257 order by avg_addon_sales desc");
		*/

		$thisMetricObj->query("select dma.store_id, dma.addon_sales_total, dmg.orders_count_all, if(odq.comp_orders > 0,  dma.addon_sales_total / odq.comp_orders, 0) as avg_addon_sales, odq.comp_orders from dashboard_metrics_agr dma
		join dashboard_metrics_guests dmg on dmg.date = '$thisMonth' and dmg.store_id = dma.store_id
		join (select od.store_id, count(od.id) as comp_orders from orders_digest od where MONTH(od.session_time) = MONTH('$thisMonth') and YEAR(od.session_time) = YEAR('$thisMonth')
				and od.session_time < now() and od.is_deleted = 0 group by od.store_id) as odq on odq.store_id = dma.store_id
				where dma.date = '$thisMonth' and dma.store_id <> 257 order by avg_addon_sales desc");

		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->addon_sales = $thisMetricObj->avg_addon_sales;
			if ($lastVal != $thisMetricObj->avg_addon_sales)
			{
				++$rank;
			}

			$didley->addon_sales_rank = $rank;
			$lastVal = $thisMetricObj->avg_addon_sales;
		}

		// servings per guest sales
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select store_id, avg_servings_per_guest_regular from dashboard_metrics_guests where date = '$thisMonth'  and store_id <> 257 order by avg_servings_per_guest_regular desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->servings_per_guest = $thisMetricObj->avg_servings_per_guest_regular;
			if ($lastVal != $thisMetricObj->avg_servings_per_guest_regular)
			{
				++$rank;
			}

			$didley->servings_per_guest_rank = $rank;
			$lastVal = $thisMetricObj->avg_servings_per_guest_regular;
		}

		// converted guests
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests');
		$thisMetricObj->query("select dmg.store_id, conversion_rate as converted_percent  from dashboard_metrics_guests dmg
										where dmg.date = '$thisMonth'  and store_id <> 257 order by converted_percent desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->converted_guests = $thisMetricObj->converted_percent;
			if ($lastVal != $thisMetricObj->converted_percent)
			{
				++$rank;
			}

			$didley->converted_guests_rank = $rank;
			$lastVal = $thisMetricObj->converted_percent;
		}

		foreach ($storeRanksArray as $id => $rankObj)
		{

			$class = get_class($rankObj);

			if ($class && $class == "DAO_Dashboard_metrics_rankings")
			{

				if (!empty($rankObj->id))
				{
					$rankObj->update();
				}
				else
				{
					$rankObj->insert();
				}
			}
			else
			{

			}
		}
	}

	static function getRollupAGRbyMonthPArtial($date, $filter = 'all_stores')
	{
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr_partials.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr_partials.store_id and store.is_corporate_owned = 0 and active = 1  and store.id <> 257 "; // remove the Pig";
		}
		else
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr_partials.store_id and store.active = 1  and store.id <> 257 "; // remove the Pig";
		}

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_partials');

		$AGRMetrics->query("SELECT
				Sum(dashboard_metrics_agr_partials.total_agr) as total_agr,
				Sum(dashboard_metrics_agr_partials.sales_adjustments_total) as sales_adjustments_total
				FROM
				dashboard_metrics_agr_partials
				$joinClause
				WHERE
				date = '$date' group by date");

		$AGRMetrics->fetch();

		return $AGRMetrics->toArray();
	}

	static function getRollupAGRbyMonth($month, $filter = 'all_stores')
	{
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr.store_id and store.is_corporate_owned = 0 and active = 1 and store.id <> 257 "; // remove the Pig
		}
		else
		{
			$joinClause = " join store on store.id = dashboard_metrics_agr.store_id and store.active = 1 and store.id <> 257 "; // remove the Pig
		}

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');

		$AGRMetrics->query("SELECT
			Sum(dashboard_metrics_agr.total_agr) as total_agr,
			Sum(dashboard_metrics_agr.sales_adjustments_total) as sales_adjustments_total,
			Sum(dashboard_metrics_agr.agr_by_session_standard) as agr_by_session_standard,
			Sum(dashboard_metrics_agr.agr_by_session_taste) as agr_by_session_taste,
			Sum(dashboard_metrics_agr.agr_by_session_mfy) as agr_by_session_mfy,
		    Sum(dashboard_metrics_agr.agr_by_session_fundraiser) as agr_by_session_fundraiser,
		    
			Sum(dashboard_metrics_agr.agr_by_order_regular) as agr_by_order_regular,
			Sum(dashboard_metrics_agr.agr_by_order_intro) as agr_by_order_intro,
			Sum(dashboard_metrics_agr.agr_by_order_taste) as agr_by_order_taste,
		    Sum(dashboard_metrics_agr.agr_by_order_fundraiser) as agr_by_order_fundraiser,
		    
			Sum(dashboard_metrics_agr.agr_by_guest_existing_regular) as agr_by_guest_existing_regular,
			Sum(dashboard_metrics_agr.agr_by_guest_existing_taste) as agr_by_guest_existing_taste,
			Sum(dashboard_metrics_agr.agr_by_guest_existing_intro) as agr_by_guest_existing_intro,
		    Sum(dashboard_metrics_agr.agr_by_guest_existing_fundraiser) as agr_by_guest_existing_fundraiser,
		    
			Sum(dashboard_metrics_agr.agr_by_guest_new_regular) as agr_by_guest_new_regular,
			Sum(dashboard_metrics_agr.agr_by_guest_new_taste) as agr_by_guest_new_taste,
			Sum(dashboard_metrics_agr.agr_by_guest_new_intro) as agr_by_guest_new_intro,
		    Sum(dashboard_metrics_agr.agr_by_guest_new_fundraiser) as agr_by_guest_new_fundraiser,
		    
			Sum(dashboard_metrics_agr.agr_by_guest_reacquired_regular) as agr_by_guest_reacquired_regular,
			Sum(dashboard_metrics_agr.agr_by_guest_reacquired_taste) as agr_by_guest_reacquired_taste,
			Sum(dashboard_metrics_agr.agr_by_guest_reacquired_intro) as agr_by_guest_reacquired_intro,
		    Sum(dashboard_metrics_agr.agr_by_guest_reacquired_fundraiser) as agr_by_guest_reacquired_fundraiser,
		    
			Avg(dashboard_metrics_agr.avg_ticket_all) as avg_ticket_all,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_existing_regular) as avg_ticket_by_guest_existing_regular,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_existing_taste) as avg_ticket_by_guest_existing_taste,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_existing_intro) as avg_ticket_by_guest_existing_intro,
		    Avg(dashboard_metrics_agr.avg_ticket_by_guest_existing_fundraiser) as avg_ticket_by_guest_existing_fundraiser,
		    
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_new_regular) as avg_ticket_by_guest_new_regular,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_new_taste) as avg_ticket_by_guest_new_taste,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_new_intro) as avg_ticket_by_guest_new_intro,
		    Avg(dashboard_metrics_agr.avg_ticket_by_guest_new_fundraiser) as avg_ticket_by_guest_new_fundraiser,
		    
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_reacquired_regular) as avg_ticket_by_guest_reacquired_regular,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_reacquired_intro) as avg_ticket_by_guest_reacquired_intro,
			Avg(dashboard_metrics_agr.avg_ticket_by_guest_reacquired_taste) as avg_ticket_by_guest_reacquired_taste,
		    Avg(dashboard_metrics_agr.avg_ticket_by_guest_reacquired_fundraiser) as avg_ticket_by_guest_reacquired_fundraiser,
		    
			Sum(dashboard_metrics_agr.addon_sales_total) as addon_sales_total,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_existing_regular) as addon_sales_by_guest_existing_regular,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_existing_taste) as addon_sales_by_guest_existing_taste,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_existing_intro) as addon_sales_by_guest_existing_intro,
		    Sum(dashboard_metrics_agr.addon_sales_by_guest_existing_fundraiser) as addon_sales_by_guest_existing_fundraiser,
		    
			Sum(dashboard_metrics_agr.addon_sales_by_guest_new_regular) as addon_sales_by_guest_new_regular,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_new_taste) as addon_sales_by_guest_new_taste,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_new_intro) as addon_sales_by_guest_new_intro,
		    Sum(dashboard_metrics_agr.addon_sales_by_guest_new_fundraiser) as addon_sales_by_guest_new_fundraiser,
		    
			Sum(dashboard_metrics_agr.addon_sales_by_guest_reacquired_regular) as addon_sales_by_guest_reacquired_regular,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_reacquired_taste) as addon_sales_by_guest_reacquired_taste,
			Sum(dashboard_metrics_agr.addon_sales_by_guest_reacquired_intro) as addon_sales_by_guest_reacquired_intro,
		    Sum(dashboard_metrics_agr.addon_sales_by_guest_reacquired_fundraiser) as addon_sales_by_guest_reacquired_fundraiser,
		    
			Sum(dashboard_metrics_agr.revenue_by_guest_existing_regular) as revenue_by_guest_existing_regular,
			Sum(dashboard_metrics_agr.revenue_by_guest_existing_taste) as revenue_by_guest_existing_taste,
			Sum(dashboard_metrics_agr.revenue_by_guest_existing_intro) as revenue_by_guest_existing_intro,
		    Sum(dashboard_metrics_agr.revenue_by_guest_existing_fundraiser) as revenue_by_guest_existing_fundraiser,
		    
			Sum(dashboard_metrics_agr.revenue_by_guest_new_regular) as revenue_by_guest_new_regular,
			Sum(dashboard_metrics_agr.revenue_by_guest_new_taste) as revenue_by_guest_new_taste,
			Sum(dashboard_metrics_agr.revenue_by_guest_new_intro) as revenue_by_guest_new_intro,
		    Sum(dashboard_metrics_agr.revenue_by_guest_new_fundraiser) as revenue_by_guest_new_fundraiser,
		    
			Sum(dashboard_metrics_agr.revenue_by_guest_reacquired_regular) as revenue_by_guest_reacquired_regular,
			Sum(dashboard_metrics_agr.revenue_by_guest_reacquired_taste) as revenue_by_guest_reacquired_taste,
			Sum(dashboard_metrics_agr.revenue_by_guest_reacquired_intro) as revenue_by_guest_reacquired_intro,
		    Sum(dashboard_metrics_agr.revenue_by_guest_reacquired_fundraiser) as revenue_by_guest_reacquired_fundraiser

			FROM
				dashboard_metrics_agr
			$joinClause
			WHERE
				date = '$month' group by date");

		$AGRMetrics->fetch();

		return $AGRMetrics->toArray();
	}

	static function getAGRTrendingDataForStore($store_id, $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');

		$AGRMetrics->query("select dma.date,
			dma.store_id,
			dma.total_agr,
			dma_prev.date as prev_date,
			dma_prev.total_agr as prev_agr,
			dma.total_agr - dma_prev.total_agr as diff,
			(dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr as percent_diff,
			dma.avg_ticket_regular
			from dashboard_metrics_agr dma
			left join dashboard_metrics_agr dma_prev on dma_prev.store_id = $store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			where dma.date > '$lastYear' and dma.date < '$thisMonthDate' and dma.store_id = $store_id order by dma.date desc");

		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}

	static function getAGRTrendingDataHomeOfficeRollup($filter = 'all_stores', $trade_area_id = 0, $is_exporting = false)
	{


		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 0 ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0";
		}
		else
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 ";
		}

		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 3);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');

		$AGRMetrics->query("select dma.date,
			sum(dma.total_agr) as total_agr,
			dma_prev.date as prev_date,
			sum(dma_prev.total_agr) as prev_agr,
			sum(dma.total_agr) - sum(dma_prev.total_agr) as diff,
			(sum(dma.total_agr) - sum(dma_prev.total_agr)) / sum(dma_prev.total_agr) as percent_diff,
			avg(dma.avg_ticket_regular) as avg_ticket_regular
			from dashboard_metrics_agr dma
			left join dashboard_metrics_agr dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			$joinClause
			where dma.date > '$lastYear' and dma.date < '$thisMonthDate' group by dma.date order by dma.date desc");

		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}

	static function getAGRTrendingDataRollup($store_id, $filter = 'single_store', $trade_area_id = 0, $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$previousMonth = date("Y-m-01", mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear));

		$retVal = array();

		// single store, not done for HO rollups
		if ($store_id)
		{
			$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$AGRMetrics->query("select 1 AS ONE,
				dma.store_id,
				avg(dma.total_agr) as total_agr,
				avg(dma_prev.total_agr) as prev_agr,
				avg(dma.total_agr - dma_prev.total_agr) as diff,
				avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
				avg(dma.avg_ticket_regular) as avg_ticket_regular
				from dashboard_metrics_agr dma
				left join dashboard_metrics_agr dma_prev on dma_prev.store_id = $store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
				where dma.date > '$lastYear' and dma.date < '$thisMonthDate' and dma.store_id = $store_id and dma.total_agr > 0
				group by ONE");

			if ($AGRMetrics->fetch())
			{
				$retVal['store_avg'] = $AGRMetrics->toArray();
			}
		}

		// national rollup - depends on filter setting
		$AGRMetricsNat = DAO_CFactory::create('dashboard_metrics_agr');

		if ($filter == 'single_store')
		{
			$AGRMetricsNat->query("select 1 AS ONE,
				avg(dma.total_agr) as total_agr,
				avg(dma_prev.total_agr) as prev_agr,
				avg(dma.total_agr - dma_prev.total_agr) as diff,
				avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
				avg(dma.avg_ticket_regular) as avg_ticket_regular
				from dashboard_metrics_agr dma
				left join dashboard_metrics_agr dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
				join store st on dma.store_id = st.id and st.active = 1 and st.id <> 257
				where dma.date > '$lastYear' and dma.date < '$thisMonthDate'
				group by ONE");
		}
		else
		{
			$joinClause = "";

			if ($filter == 'corp_stores')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 1 ";
			}
			else if ($filter == 'non_corp_stores')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 0 ";
			}
			else if ($filter == 'region')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0";
			}
			else
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id <> 257 ";
			}

			$AGRMetricsNat->query("select
				avg(iq.total_agr) as total_agr,
				avg(iq.prev_agr) as prev_agr,
				avg(iq.diff) as diff,
				avg(iq.percent_diff) as percent_diff,
				avg(iq.avg_ticket_regular) as avg_ticket_regular
				from (select
						sum(dma.total_agr) as total_agr,
						sum(dma_prev.total_agr) as prev_agr,
						sum(dma.total_agr) - sum(dma_prev.total_agr) as diff,
						(sum(dma.total_agr) - sum(dma_prev.total_agr)) / sum(dma_prev.total_agr) as percent_diff,
						avg(dma.avg_ticket_regular) as avg_ticket_regular
						from dashboard_metrics_agr dma
						left join dashboard_metrics_agr dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
						$joinClause
						where dma.date > '$lastYear' and dma.date < '$thisMonthDate'
						group by dma.date) as iq");
		}

		if ($AGRMetricsNat->fetch())
		{
			$retVal['national_avg'] = $AGRMetricsNat->toArray();
		}

		$AGRMetricsTop5 = DAO_CFactory::create('dashboard_metrics_agr');

		$AGRMetricsTop5->query("select 1 AS ONE,
			avg(dma.total_agr) as total_agr,
			avg(dma_prev.total_agr) as prev_agr,
			avg(dma.total_agr - dma_prev.total_agr) as diff,
			avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
			avg(dma.avg_ticket_regular) as avg_ticket_regular
			from dashboard_metrics_agr dma
			left join dashboard_metrics_agr dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			join store st on dma.store_id = st.id and st.active = 1 and st.is_in_current_top_5 = 1
			where dma.date = '$previousMonth'
			group by ONE");

		if ($AGRMetricsTop5->fetch())
		{
			$retVal['top5_avg'] = $AGRMetricsTop5->toArray();
		}

		return $retVal;
	}

	static function addcancelledOrdersRollups($store_id, &$rollups, $rows = null, $filter = 'single_store', $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		$divisor = 12;

		if ($is_exporting)
		{
			$divisor = 24;
		}

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 1);
		}

		if ($filter == 'single_store' && !empty($rows))
		{
			// 12 month store average
			$sum = 0;
			$numMonths = 0;
			foreach ($rows as $data)
			{
				$sum += $data['num_cancelled_orders'];

				if (isset($data['total_agr']) && $data['total_agr'] > 0)
				{
					$numMonths++;
				}
			}

			$rollups['store_avg']['num_cancelled_orders'] = CTemplate::number_format($sum / $numMonths);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$nat_sum = 0;
		$GuestMetrics->query("select CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01') as date, count(b.id) as numCancelled from booking b
				join session s on s.id = b.session_id and  s.session_start >= '$lastYear' and s.session_start < '$thisMonthDate'
				join store st on st.id = s.store_id and st.active = 1 and st.id <> 257
				where b.status = 'CANCELLED' and b.is_deleted = 0
				group by CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01')
				order by s.session_start desc");

		$StoreCount = 0;

		while ($GuestMetrics->fetch())
		{
			$nat_sum += $GuestMetrics->numCancelled;
		}

		if ($filter == 'single_store')
		{
			$storeCounter = DAO_CFactory::create('store');
			$storeCounter->query("select count(id) as numStores from store where active = 1 and id <> 257");
			$storeCounter->fetch();

			$rollups['national_avg']['num_cancelled_orders'] = CTemplate::number_format(($nat_sum / $divisor) / $storeCounter->numStores);
		}
		else
		{
			$rollups['national_avg']['num_cancelled_orders'] = CTemplate::number_format($nat_sum / $divisor);
		}
	}

	static function addcancelledOrdersForStore($store_id, &$rows, $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$GuestMetrics->query("select CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01') as date, count(b.id) as numCancelled from booking b
								join session s on s.id = b.session_id and  s.session_start >= '$lastYear' and s.session_start < '$thisMonthDate'
								where b.status = 'CANCELLED' and b.is_deleted = 0 and s.store_id = $store_id
								group by CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01')
								order by s.session_start desc");

		while ($GuestMetrics->fetch())
		{

			$returnedDateTS = strtotime($GuestMetrics->date);
			$adjustedDate = date('Y-m-d', $returnedDateTS);

			if (isset($rows[$adjustedDate]))
			{
				$rows[$adjustedDate]['num_cancelled_orders'] = $GuestMetrics->numCancelled;
			}
		}
	}

	static function addGuestTrendingDataForStore($store_id, &$rows, $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$GuestMetrics->query("select dmg.date,
				dmg.store_id,
				avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy)) as orders_per_session ,
				dmg.orders_count_all,
				dmg.guest_count_total,
				(dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) / guest_count_total as percent_new,
				(dmg.guest_count_existing_regular + ifnull(dmg.guest_count_existing_taste,0) + ifnull(dmg.guest_count_existing_intro, 0) + ifnull(dmg.guest_count_existing_fundraiser, 0)) as guest_count_existing,
				(dmg.instore_signup_existing_regular + ifnull(dmg.instore_signup_existing_taste,0) + ifnull(dmg.instore_signup_existing_intro,0) + ifnull(dmg.instore_signup_existing_fundraiser,0)) as instore_signup_existing,
				(dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) as guest_count_new,
				(dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) as instore_signup_new,
				(dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser) as guest_count_reacquired,
				(dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) as instore_signup_reacquired,
				 dmg.lost_guests_at_45_days,
				 dmg.avg_servings_per_guest_regular,
				dmg.average_annual_regular_visits
				from dashboard_metrics_guests dmg
				where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' and dmg.store_id = $store_id group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}
	}

	static function addCancelledOrders(&$rows, $filter = 'all_stores', $trade_area_id = 0, $is_exporting = false)
	{


		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 0 ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.id <> 257 join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
		}
		else
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.id <> 257 ";
		}

		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 2);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$GuestMetrics->query("select CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01') as date, count(b.id) as numCancelled from booking b
									join session s on s.id = b.session_id and  s.session_start >= '$lastYear' and s.session_start < '$thisMonthDate'
									$joinClause
									where b.status = 'CANCELLED' and b.is_deleted = 0
									group by CONCAT(YEAR(s.session_start),'-',MONTH(s.session_start), '-01')
									order by s.session_start desc");

		while ($GuestMetrics->fetch())
		{

			$returnedDateTS = strtotime($GuestMetrics->date);
			$adjustedDate = date('Y-m-d', $returnedDateTS);

			if (isset($rows[$adjustedDate]))
			{
				$rows[$adjustedDate]['num_cancelled_orders'] = $GuestMetrics->numCancelled;
			}
		}
	}

	static function addGuestTrendingHomeofficeRollups(&$rows, $filter = 'all_stores', $trade_area_id = 0, $is_exporting = false)
	{


		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 0 ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
		}
		else
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 ";
		}

		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 3);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$GuestMetrics->query("select dmg.date,
				avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy)) as orders_per_session ,
				sum(dmg.orders_count_all) as orders_count_all,
				sum(dmg.guest_count_total) as guest_count_total,
				(sum(dmg.guest_count_new_regular) + sum(dmg.guest_count_new_taste) + sum(dmg.guest_count_new_intro) + sum(dmg.guest_count_new_fundraiser)) / sum(guest_count_total) as percent_new,
				(sum(dmg.guest_count_existing_regular) + sum(ifnull(dmg.guest_count_existing_taste,0)) + sum(ifnull(dmg.guest_count_existing_intro, 0))) as guest_count_existing,
				(sum(dmg.instore_signup_existing_regular) + sum(ifnull(dmg.instore_signup_existing_taste,0)) + sum(ifnull(dmg.instore_signup_existing_intro,0)) + sum(ifnull(dmg.instore_signup_existing_fundraiser,0))) as instore_signup_existing,
				(sum(dmg.guest_count_new_regular) + sum(dmg.guest_count_new_taste) + sum(dmg.guest_count_new_intro) + sum(dmg.guest_count_new_fundraiser)) as guest_count_new,
				(sum(dmg.instore_signup_new_regular) + sum(dmg.instore_signup_new_taste) + sum(dmg.instore_signup_new_intro) + sum(dmg.instore_signup_new_fundraiser)) as instore_signup_new,
				(sum(dmg.guest_count_reacquired_regular) + sum(dmg.guest_count_reacquired_taste) + sum(dmg.guest_count_reacquired_intro) + sum(dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
				(sum(dmg.instore_signup_reacquired_regular) + sum(dmg.instore_signup_reacquired_taste) + sum(dmg.instore_signup_reacquired_intro) + sum(dmg.instore_signup_reacquired_fundraiser)) as instore_signup_reacquired,
				sum(dmg.lost_guests_at_45_days) as lost_guests_at_45_days,
				avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
				avg(dmg.average_annual_regular_visits) as average_annual_regular_visits
				from dashboard_metrics_guests dmg
				$joinClause
				where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}
	}

	static function addGuestTrendingDataRollups($store_id, &$rows, $filter = 'single_store', $trade_area_id = 0, $is_exporting = false)
	{
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		if ($is_exporting)
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 3);
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$previousMonth = date("Y-m-01", mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear));

		if ($store_id)
		{
			$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');
			$GuestMetrics->query("select 1 as ONE,
					dmg.store_id,
					avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy)) as orders_per_session,
					avg(dmg.orders_count_all) orders_count_all,
					avg(dmg.guest_count_total) as guest_count_total,
					avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) / guest_count_total) as percent_new,
					avg(dmg.guest_count_existing_regular) as guest_count_existing,
					avg(dmg.instore_signup_existing_regular / guest_count_existing_regular) * 100  as instore_signup_existing,
					avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser)) as guest_count_new,
					avg(((dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) / (dmg.guest_count_new_intro + dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_fundraiser))) * 100 as instore_signup_new,
					avg((dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
					avg(((dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) / (dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_fundraiser))) * 100 as instore_signup_reacquired,
					avg( dmg.lost_guests_at_45_days) as lost_guests_at_45_days,
					avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
					avg(dmg.average_annual_regular_visits) as average_annual_regular_visits
					from dashboard_metrics_guests dmg
					where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' and dmg.store_id = $store_id and dmg.guest_count_total > 0
					group by ONE");

			if ($GuestMetrics->fetch())
			{
				if (isset($rows['store_avg']))
				{
					$rows['store_avg'] = array_merge($rows['store_avg'], $GuestMetrics->toArray());
				}
			}
		}

		$GuestMetricsNat = DAO_CFactory::create('dashboard_metrics_guests');

		if ($filter == 'single_store')
		{
			$GuestMetricsNat->query("select 1 as ONE,
					avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy))  as orders_per_session,
					avg(dmg.orders_count_all) orders_count_all,
					avg(dmg.guest_count_total) as guest_count_total,
					avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) / guest_count_total) as percent_new,
					avg(dmg.guest_count_existing_regular) as guest_count_existing,
					avg(dmg.instore_signup_existing_regular / guest_count_existing_regular) * 100 as instore_signup_existing,
					avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser)) as guest_count_new,
					avg(((dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) / (dmg.guest_count_new_intro + dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_fundraiser))) * 100 as instore_signup_new,
					avg((dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
					avg(((dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) / (dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_fundraiser))) * 100 as instore_signup_reacquired,
					avg( dmg.lost_guests_at_45_days) as lost_guests_at_45_days,
					avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
					avg(dmg.average_annual_regular_visits) as average_annual_regular_visits
					from dashboard_metrics_guests dmg
					join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257
					where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate'
					group by ONE");
		}
		else
		{
			$joinClause = "";

			if ($filter == 'corp_stores')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 1 ";
			}
			else if ($filter == 'non_corp_stores')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 and st.is_corporate_owned = 0 ";
			}
			else if ($filter == 'region')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
			}
			else
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257 ";
			}

			$GuestMetricsNat->query("select avg(iq.orders_per_session) as orders_per_session,
			avg(iq.orders_count_all) as orders_count_all,
			avg(iq.guest_count_total) as guest_count_total,
			avg(iq.percent_new) as percent_new,
			avg(iq.guest_count_existing) as guest_count_existing,
			avg(iq.instore_signup_existing) as instore_signup_existing,
			avg(iq.guest_count_new) as guest_count_new,
			avg(iq.instore_signup_new) as instore_signup_new,
			avg(iq.guest_count_reacquired) as guest_count_reacquired,
			avg(iq.instore_signup_new) as instore_signup_reacquired,
			avg(iq.lost_guests_at_45_days) as lost_guests_at_45_days,
			avg(iq.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
			avg(iq.average_annual_regular_visits) as average_annual_regular_visits
			from (select
					avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy))  as orders_per_session,
					sum(dmg.orders_count_all) as orders_count_all,
					sum(dmg.guest_count_total) as guest_count_total,
					avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) / guest_count_total) as percent_new,
					sum(dmg.guest_count_existing_regular) as guest_count_existing,
					avg(dmg.instore_signup_existing_regular / guest_count_existing_regular) * 100 as instore_signup_existing,
					sum((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser)) as guest_count_new,
					avg(((dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) / (dmg.guest_count_new_intro + dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_fundraiser))) * 100 as instore_signup_new,
					sum((dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
					avg(((dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) / (dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_fundraiser))) * 100 as instore_signup_reacquired,
					sum( dmg.lost_guests_at_45_days) as lost_guests_at_45_days,
					avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
					avg(dmg.average_annual_regular_visits) as average_annual_regular_visits
					from dashboard_metrics_guests dmg
					$joinClause
					where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' group by dmg.date) as iq");
		}

		if ($GuestMetricsNat->fetch())
		{
			if (isset($rows['national_avg']))
			{
				$rows['national_avg'] = array_merge($rows['national_avg'], $GuestMetricsNat->toArray());
			}
		}

		$GuestMetricsTop5 = DAO_CFactory::create('dashboard_metrics_guests');
		$GuestMetricsTop5->query("select 1 as ONE,
				avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy))  as orders_per_session,
				avg(dmg.orders_count_all) orders_count_all,
				avg(dmg.guest_count_total) as guest_count_total,
				avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) / guest_count_total) as percent_new,
				avg(dmg.guest_count_existing_regular) as guest_count_existing,
				avg(dmg.instore_signup_existing_regular / guest_count_existing_regular) * 100 as instore_signup_existing,
				avg((dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser)) as guest_count_new,
				avg(((dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) / (dmg.guest_count_new_intro + dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_fundraiser))) * 100 as instore_signup_new,
				avg((dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
				avg(((dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) / (dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_fundraiser))) * 100 as instore_signup_reacquired,
				avg( dmg.lost_guests_at_45_days) as lost_guests_at_45_days,
				avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular,
				avg(dmg.average_annual_regular_visits) as average_annual_regular_visits
				from dashboard_metrics_guests dmg
				join store st on st.id = dmg.store_id and st.active = 1 and st.is_in_current_top_5 = 1
				where dmg.date = '$previousMonth'
				group by ONE");

		if ($GuestMetricsTop5->fetch())
		{
			if (isset($rows['top5_avg']))
			{
				$rows['top5_avg'] = array_merge($rows['top5_avg'], $GuestMetricsTop5->toArray());
			}
		}
	}

	static function getRollupGuestNumberdByMonth($month, $filter = 'all_stores')
	{
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_guests.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = dashboard_metrics_guests.store_id and store.is_corporate_owned = 0 and active = 1 and store.id <> 257 "; // remove the Pig
		}
		else
		{
			$joinClause = " join store on store.id = dashboard_metrics_guests.store_id and store.active = 1 and store.id <> 257 "; // remove the Pig
		}

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_guests');

		$AGRMetrics->query("SELECT
				Sum(dashboard_metrics_guests.guest_count_total) as guest_count_total,
				Sum(dashboard_metrics_guests.guest_count_existing_regular) as guest_count_existing_regular,
				Sum(dashboard_metrics_guests.guest_count_existing_taste) as guest_count_existing_taste,
				Sum(dashboard_metrics_guests.guest_count_existing_intro) as guest_count_existing_intro,
				Sum(dashboard_metrics_guests.guest_count_existing_fundraiser) as guest_count_existing_fundraiser,
		    
				Sum(dashboard_metrics_guests.guest_count_reacquired_regular) as guest_count_reacquired_regular,
				Sum(dashboard_metrics_guests.guest_count_reacquired_taste) as guest_count_reacquired_taste,
				Sum(dashboard_metrics_guests.guest_count_reacquired_intro) as guest_count_reacquired_intro,
		        Sum(dashboard_metrics_guests.guest_count_reacquired_fundraiser) as guest_count_reacquired_fundraiser,
		    
				Sum(dashboard_metrics_guests.guest_count_new_regular) as guest_count_new_regular,
				Sum(dashboard_metrics_guests.guest_count_new_taste) as guest_count_new_taste,
				Sum(dashboard_metrics_guests.guest_count_new_intro) as guest_count_new_intro,
		        Sum(dashboard_metrics_guests.guest_count_new_fundraiser) as guest_count_new_fundraiser,
		    
				Sum(dashboard_metrics_guests.instore_signup_total) as instore_signup_total,
				Sum(dashboard_metrics_guests.instore_signup_existing_regular) as instore_signup_existing_regular,
				Sum(dashboard_metrics_guests.instore_signup_existing_taste) as instore_signup_existing_taste,
				Sum(dashboard_metrics_guests.instore_signup_existing_intro) as instore_signup_existing_intro,
		        Sum(dashboard_metrics_guests.instore_signup_existing_fundraiser) as instore_signup_existing_fundraiser,
		    
				Sum(dashboard_metrics_guests.instore_signup_reacquired_regular) as instore_signup_reacquired_regular,
				Sum(dashboard_metrics_guests.instore_signup_reacquired_taste) as instore_signup_reacquired_taste,
				Sum(dashboard_metrics_guests.instore_signup_reacquired_intro) as instore_signup_reacquired_intro,
		        Sum(dashboard_metrics_guests.instore_signup_reacquired_fundraiser) as instore_signup_reacquired_fundraiser,
		    
				Sum(dashboard_metrics_guests.instore_signup_new_regular) as instore_signup_new_regular,
				Sum(dashboard_metrics_guests.instore_signup_new_taste) as instore_signup_new_taste,
				Sum(dashboard_metrics_guests.instore_signup_new_intro) as instore_signup_new_intro,
		        Sum(dashboard_metrics_guests.instore_signup_new_fundraiser) as instore_signup_new_fundraiser,
		    
				Avg(dashboard_metrics_guests.avg_servings_per_guest_all) as avg_servings_per_guest_all,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_existing_regular) as avg_servings_per_guest_existing_regular,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_existing_taste) as avg_servings_per_guest_existing_taste,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_existing_intro) as avg_servings_per_guest_existing_intro,
		        Avg(dashboard_metrics_guests.avg_servings_per_guest_existing_fundraiser) as avg_servings_per_guest_existing_fundraiser,
		    
				Avg(dashboard_metrics_guests.avg_servings_per_guest_reacquired_regular) as avg_servings_per_guest_reacquired_regular,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_reacquired_taste) as avg_servings_per_guest_reacquired_taste,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_reacquired_intro) as avg_servings_per_guest_reacquired_intro,
		        Avg(dashboard_metrics_guests.avg_servings_per_guest_reacquired_fundraiser) as avg_servings_per_guest_reacquired_fundraiser,
		    
				Avg(dashboard_metrics_guests.avg_servings_per_guest_new_regular) as avg_servings_per_guest_new_regular,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_new_taste) as avg_servings_per_guest_new_taste,
				Avg(dashboard_metrics_guests.avg_servings_per_guest_new_intro) as avg_servings_per_guest_new_intro,
		        Avg(dashboard_metrics_guests.avg_servings_per_guest_new_fundraiser) as avg_servings_per_guest_new_fundraiser,
		    
				Sum(dashboard_metrics_guests.sessions_count_all) as sessions_count_all,
				Sum(dashboard_metrics_guests.sessions_count_regular) as sessions_count_regular,
				Sum(dashboard_metrics_guests.sessions_count_mfy) as sessions_count_mfy,
				Sum(dashboard_metrics_guests.sessions_count_taste) as sessions_count_taste,
				Sum(dashboard_metrics_guests.sessions_count_fundraiser) as sessions_count_fundraiser,
		    
				Sum(dashboard_metrics_guests.orders_count_all) as orders_count_all,
				Sum(dashboard_metrics_guests.orders_count_regular) as orders_count_regular,
				Sum(dashboard_metrics_guests.orders_count_mfy) as orders_count_mfy,
				Sum(dashboard_metrics_guests.orders_count_taste) as orders_count_taste,
				Sum(dashboard_metrics_guests.orders_count_fundraiser) as orders_count_fundraiser,
		    
				Sum(dashboard_metrics_guests.converted_guests) as converted_guests,
				Avg(dashboard_metrics_guests.conversion_rate) as conversion_rate,
				Avg(dashboard_metrics_guests.average_annual_visits) as average_annual_visits,
				Sum(dashboard_metrics_guests.lost_guests_At_45_days) as lost_guests_at_45_days,
				Sum(dashboard_metrics_guests.orders_count_regular_existing_guests) as orders_count_regular_existing_guests,
				Sum(dashboard_metrics_guests.orders_count_regular_new_guests) as orders_count_regular_new_guests,
				Sum(dashboard_metrics_guests.orders_count_regular_reacquired_guests) as orders_count_regular_reacquired_guests,
				Sum(dashboard_metrics_guests.orders_count_intro_existing_guests) as orders_count_intro_existing_guests,
				Sum(dashboard_metrics_guests.orders_count_intro_new_guests) as orders_count_intro_new_guests,
				Sum(dashboard_metrics_guests.orders_count_intro_reacquired_guests) as orders_count_intro_reacquired_guests,
				Sum(dashboard_metrics_guests.orders_count_taste_existing_guests) as orders_count_taste_existing_guests,
				Sum(dashboard_metrics_guests.orders_count_taste_new_guests) as orders_count_taste_new_guests,
				Sum(dashboard_metrics_guests.orders_count_taste_reacquired_guests) as orders_count_taste_reacquired_guests,
		    
		        Sum(dashboard_metrics_guests.orders_count_fundraiser_existing_guests) as orders_count_fundraiser_existing_guests,
				Sum(dashboard_metrics_guests.orders_count_fundraiser_new_guests) as orders_count_fundraiser_new_guests,
				Sum(dashboard_metrics_guests.orders_count_fundraiser_reacquired_guests) as orders_count_fundraiser_reacquired_guests
		    
		    
				FROM
				dashboard_metrics_guests
				$joinClause
				WHERE
				date = '$month' group by date");

		$AGRMetrics->fetch();

		return $AGRMetrics->toArray();
		/*
			converted_guests;                // int(8)
			one_month_drop_off;              // int(8)
			two_month_drop_off;              // int(8)
			average_annual_visits;           // real(6)
		*/
	}

}

//************************************************************************************************************************************
// CDashboardReportAggregation
// This class is used to aggregate data and display in the dashboard
//************************************************************************************************************************************

class CDashboardReportAggregation
{

	private $m_grandopening_date = null;
	public $m_months_open = null;
	private $m_store_id = null;
	public $m_age_class_id = null;

	public $m_age_class_description = null;

	public $m_monthvar = null;
	public $m_yearvar = null;
	public $m_longmonth = null;

	public $m_store_name = null;
	public $m_home_office_id = null;
	public $m_city = null;
	public $m_state_id = null;
	public $m_email_address = null;

	public $m_trade_area_id = null;
	public $m_trade_area_name = null;

	public $m_my_results_array = null;

	public $m_details_array = null;

	public $m_top_store_lists = null;

	public $m_nation_ranking_max_cnt = null;

	public $m_regional_ranking_max_cnt = null;

	private $m_monthvar_prev = null;
	private $m_yearvar_prev = null;

	private $m_my_retention = null;
	private $m_my_instore = null;

	public $m_dashboard_log_id = null;

	private $m_revenue_gross_master = null;

	public $m_dashboard_cache_date = null;

	public $min_grand_total = 0;

	const KGROSS_TYPE = 'Adjusted Gross Revenue';
	const KRET_TYPE = 'Retention';
	const KSIGN_TYPE = 'In Store Signups';
	const KCUST_TYPE = 'Guest Visits';
	const KSESS_TYPE = 'Session Average';

	function CDashboardReportAggregation($store_id, $monthvar, $yearvar)
	{

		$this->m_monthvar = $monthvar;
		$this->m_store_id = $store_id;
		$this->m_yearvar = $yearvar;

		$prevmonth = $monthvar - 1;
		if ($prevmonth == 0)
		{
			$prevmonth = 12;
			$yearvar--;
		}

		$this->m_monthvar_prev = $prevmonth;
		$this->m_yearvar_prev = $yearvar;

		$this->setMyDetails();

		$this->findStoreDetail();

		$this->storeResults();

		$this->buildMainData();
		$this->BuildInstoreSignUps();
		$this->BuildRetention();

		$this->getMyDetails();
		$this->topStoreLists();

		$this->m_nation_ranking_max_cnt = 200;
		$this->m_regional_ranking_max_cnt = 10;

		$this->findRankings();

		$this->getPriorYearGross();
	}

	static function formatCurrency($currency_value)
	{

		$currency = (float)$currency_value;
		$formatted = sprintf("%.2f", $currency);
		$english_format_number = number_format((float)$currency, 2);
		$newstr = "$" . $english_format_number;

		return $newstr;
	}

	function findActiveLogIDAndDate()
	{
		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		$obj->active = 1;
		$rslt = $obj->find(true);
		if ($obj->id == null)
		{
			// RETURN here.. failure...

		}
		$this->m_dashboard_log_id = $obj->id;
		$this->m_dashboard_cache_date = $this->reformatTime($obj->cached_date);
	}

	function reformatTime($sqltime)
	{
		$sep = explode(" ", $sqltime);

		$datesep = explode("-", $sep[0]); // split time into hr, min, sec
		$timesep = explode(":", $sep[1]); // split time into hr, min, sec
		$newTime = mktime($timesep[0], $timesep[1], $timesep[2]);
		$formatTime = date("h:i:s A", $newTime);
		$finalDate = $datesep[1] . '-' . $datesep[2] . '-' . $datesep[0] . ' ' . $formatTime;

		return $finalDate;
	}

	// Report: reports_site_store_details
	// Dashboard_report_age_class
	function findStoreDetail()
	{


		$this->findActiveLogIDAndDate();

		$sql = 'SELECT stores_detail.ageclass as months_open, stores_detail.store_id, `dashboard_report_age_class`.description, `dashboard_report_age_class`.id as age_class_id,' . 'stores_detail.`region`,stores_detail.`store_name`,stores_detail.`city`,stores_detail.`state_id`,stores_detail.`home_office_id`,stores_detail.`email_address`,stores_detail.`grand_opening_date`,' . 'stores_detail.`trade_area_id` as trade_area_id' . ' FROM `dashboard_report_age_class` ,' . ' (SELECT store.id as store_id,' . ' `trade_area`.`region`,`store`.`store_name`,`store`.`city`,`store`.`state_id`,`store`.`home_office_id`,`store`.`email_address`,`store`.`grand_opening_date`,' . ' `trade_area`.`id` AS `trade_area_id`,`trade_area`.`id` AS trade_area_state_country_id,`store_trade_area`.`id` as store_trade_area_id,' . " PERIOD_DIFF(DATE_FORMAT(now(), '%Y%m'), DATE_FORMAT(`grand_opening_date`, '%Y%m')) as ageclass " . ' FROM ' . ' `store` ' . ' Inner Join `store_trade_area` ON `store`.`id` = `store_trade_area`.`store_id` ' . ' Inner Join `trade_area` ON `store_trade_area`.`trade_area_id` = `trade_area`.`id`' . ' where store.is_deleted = 0 and store.active = 1 and `store_trade_area`.`is_active` = 1 and `store_trade_area`.`is_deleted`  = 0 and' . ' `trade_area`.`is_active` = 1  and `grand_opening_date` is not null ' . //' and trade_area_id = 1 ' .
			" and store.id = $this->m_store_id " . ' ) as stores_detail where stores_detail.ageclass >= `starting_period`   and stores_detail.ageclass <= ending_period' . ' order by `dashboard_report_age_class`.rank';

		$obj = DAO_CFactory::create("store");
		$obj->query($sql);
		while ($obj->fetch())
		{
			$this->m_store_name = $obj->store_name;
			$this->m_home_office_id = $obj->home_office_id;
			$this->m_city = $obj->city;
			$this->m_state_id = $obj->state_id;
			$this->m_email_address = $obj->email_address;
			$this->m_grandopening_date = $obj->grand_opening_date;
			$this->m_age_class_description = $obj->description;
			$this->m_age_class_id = $obj->age_class_id;
			$this->m_trade_area_id = $obj->trade_area_id;
			$this->m_trade_area_name = $obj->region;
			$this->m_months_open = $obj->months_open;
		}
	}

	function setMyDetails()
	{
		// get current revenue
		$this->m_details_array['revenue_current'] = null;
		// get percentage change
		$this->m_details_array['revenue_percentage'] = null;

		$this->m_details_array['retention'] = null;

		$this->m_details_array['retention_percentage'] = null;

		$this->m_details_array['instore'] = null;

		$this->m_details_array['instore_percentage'] = null;

		$this->m_details_array['customer'] = null;

		$this->m_details_array['customer_percentage'] = null;

		$this->m_details_array['session_average'] = null;

		$this->m_details_array['session_average_percentage'] = null;

		$this->m_details_array['last_year_gross_revenue'] = null;

		$this->m_details_array['last_year_gross_revenue_national'] = null;

		$this->m_details_array['last_year_gross_revenue_regional'] = null;

		$this->m_details_array['last_year_gross_revenue_percent_chg'] = null;

		$this->m_details_array['last_year_gross_revenue_percent_chg_national'] = null;

		$this->m_details_array['last_year_gross_revenue_percent_chg_regional'] = null;

		$this->m_details_array['revenue_historical'] = null;
	}

	function getMyDetails()
	{


		$useHistorical = false;
		$myresultsArray = isset($this->m_my_results_array[$this->m_yearvar][$this->m_monthvar][$useHistorical]) ? $this->m_my_results_array[$this->m_yearvar][$this->m_monthvar][$useHistorical] : null;
		$this->m_details_array['revenue_current'] = isset($myresultsArray['transaction_totals']) ? $myresultsArray['transaction_totals'] : null;
		$this->m_details_array['customer'] = isset($myresultsArray['distinct_users']) ? $myresultsArray['distinct_users'] : null;
		$this->m_details_array['session_average'] = isset($myresultsArray['customers_per_session']) ? $myresultsArray['customers_per_session'] : null;

		$myresultsArrayPrev = isset($this->m_my_results_array[$this->m_yearvar_prev][$this->m_monthvar_prev][$useHistorical]) ? $this->m_my_results_array[$this->m_yearvar_prev][$this->m_monthvar_prev][$useHistorical] : null;

		//  $myresultsArrayPrev = isset($this->m_my_results_array[$this->m_yearvar][$this->m_monthvar_prev][$useHistorical]) ? $this->m_my_results_array[$this->m_yearvar][$this->m_monthvar_prev][$useHistorical] : null;

		if (!empty($this->m_my_retention[$this->m_yearvar][$this->m_monthvar]['retention']))
		{
			$this->m_details_array['retention'] = $this->m_my_retention[$this->m_yearvar][$this->m_monthvar]['retention'];
			$prevvar = isset($this->m_my_retention[$this->m_yearvar_prev][$this->m_monthvar_prev]['retention']) ? $this->m_my_retention[$this->m_yearvar_prev][$this->m_monthvar_prev]['retention'] : null;
			if ($prevvar > 0)
			{
				$this->m_details_array['retention_percentage'] = sprintf("%.2f", (($this->m_details_array['retention'] - $prevvar) / $prevvar) * 100);
			}
		}

		if (!empty($this->m_my_instore[$this->m_yearvar][$this->m_monthvar]['in_store_orders']))
		{
			$this->m_details_array['instore'] = $this->m_my_instore[$this->m_yearvar][$this->m_monthvar]['in_store_orders'];
			$prevvar = isset($this->m_my_instore[$this->m_yearvar_prev][$this->m_monthvar_prev]['in_store_orders']) ? $this->m_my_instore[$this->m_yearvar_prev][$this->m_monthvar_prev]['in_store_orders'] : null;
			if (!empty($prevvar))
			{
				$this->m_details_array['instore_percentage'] = sprintf("%.2f", (($this->m_details_array['instore'] - $prevvar) / $prevvar) * 100);
			}
		}

		if (!empty($myresultsArrayPrev))
		{

			if ($myresultsArrayPrev['transaction_totals'] > 0)
			{
				$this->m_details_array['revenue_percentage'] = sprintf("%.2f", (($myresultsArray['transaction_totals'] - $myresultsArrayPrev['transaction_totals']) / $myresultsArrayPrev['transaction_totals']) * 100);
			}

			if ($myresultsArrayPrev['distinct_users'] > 0)
			{
				$this->m_details_array['customer_percentage'] = sprintf("%.2f", (($myresultsArray['distinct_users'] - $myresultsArrayPrev['distinct_users']) / $myresultsArrayPrev['distinct_users']) * 100);
			}

			if ($myresultsArrayPrev['customers_per_session'] > 0)
			{
				$this->m_details_array['session_average_percentage'] = sprintf("%.2f", (($myresultsArray['customers_per_session'] - $myresultsArrayPrev['customers_per_session']) / $myresultsArrayPrev['customers_per_session']) * 100);
			}
		}
	}

	// dashboard_report_cache_main, dashboard_report_cache_store_classification, dashboard_report_cache_log
	//called internally only
	function getPriorYearGross()
	{

		$success = true;
		$prevyear = $this->m_yearvar - 1;
		$sql = ' SELECT 	`dashboard_report_cache_main`.`transaction_totals`' . ' FROM ' . ' `dashboard_report_cache_main` ' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' WHERE ' . " `dashboard_report_cache_log`.`active` =  '1' " . " and `yearvalue` = '$prevyear' and `monthvalue` = '$this->m_monthvar' and is_historical = 1 and  store_id = $this->m_store_id ";
		$obj = DAO_CFactory::create("dashboard_report_cache_main");
		$obj->query($sql);

		$lastyearsrevenue = 0.0;
		$thisyearsRevenue = 0.0;  // could have got this from store info...
		$obj->query($sql);
		$rslt = $obj->fetch();
		if ($rslt > 0)
		{
			$lastyearsrevenue = $obj->transaction_totals;
		}
		$thisyearsRevenue = $this->m_details_array['revenue_current'];
		if ($lastyearsrevenue > 0)
		{

			$this->m_details_array['last_year_gross_revenue'] = $lastyearsrevenue;
			$this->m_details_array['last_year_gross_revenue_percent_chg'] = sprintf("%.2f", (($thisyearsRevenue - $lastyearsrevenue) / $lastyearsrevenue) * 100);
		}

		$sql = "SELECT Sum(`dashboard_report_cache_main`.`transaction_totals`) / count(`dashboard_report_cache_main`.`store_id`) as revenue,`dashboard_report_cache_main`.`monthvalue`,`dashboard_report_cache_main`.`yearvalue`" . " FROM `dashboard_report_cache_main`Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`" . " Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id`" . " WHERE `dashboard_report_cache_log`.`active` =  '1'  and (`yearvalue` = $this->m_yearvar or`yearvalue` = $prevyear  ) and `monthvalue` = '$this->m_monthvar'" . " and `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` group by `yearvalue`,`monthvalue`, `is_historical` order by is_historical";
		$obj->query($sql);

		$lastyeargross = 0;
		$thisyeargross = 0;

		while ($obj->fetch())
		{
			if (!empty($obj->revenue))
			{
				if ($obj->monthvalue == $this->m_monthvar && $obj->yearvalue == $this->m_yearvar)
				{
					$thisyeargross = $obj->revenue;
				}
				else if ($obj->monthvalue == $this->m_monthvar && $obj->yearvalue == $prevyear)
				{
					$lastyeargross = $obj->revenue;
				}
			}
		}

		if ($thisyeargross > 0 && $lastyeargross > 0)
		{
			$this->m_details_array['last_year_gross_revenue_national'] = $lastyeargross;

			$this->m_details_array['last_year_gross_revenue_percent_chg_national'] = sprintf("%.2f", (($thisyeargross - $lastyeargross) / $lastyeargross) * 100);
		}

		$sql = "SELECT Sum(`dashboard_report_cache_main`.`transaction_totals`) /  count(`dashboard_report_cache_main`.`store_id`) as revenue,`dashboard_report_cache_main`.`monthvalue`,`dashboard_report_cache_main`.`yearvalue`" . " FROM `dashboard_report_cache_main`Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`" . " Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id`" . " WHERE  `trade_area_id` = $this->m_trade_area_id and `dashboard_report_cache_log`.`active` =  '1'  and (`yearvalue` = $this->m_yearvar or`yearvalue` = $prevyear  ) and `monthvalue` = '$this->m_monthvar'" . " and `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` group by `yearvalue`,`monthvalue`, `is_historical` order by is_historical";
		$obj->query($sql);
		$lastyeargross = 0;
		$thisyeargross = 0;

		while ($obj->fetch())
		{
			if (!empty($obj->revenue))
			{
				if ($obj->monthvalue == $this->m_monthvar && $obj->yearvalue == $this->m_yearvar)
				{
					$thisyeargross = $obj->revenue;
				}
				else if ($obj->monthvalue == $this->m_monthvar && $obj->yearvalue == $prevyear)
				{
					$lastyeargross = $obj->revenue;
				}
			}
		}
		if ($thisyeargross > 0 && $lastyeargross > 0)
		{
			$this->m_details_array['last_year_gross_revenue_regional'] = $lastyeargross;

			$this->m_details_array['last_year_gross_revenue_percent_chg_regional'] = sprintf("%.2f", (($thisyeargross - $lastyeargross) / $lastyeargross) * 100);
		}
	}

	// called internally only
	function findRankings()
	{

		$sql = "SELECT `dashboard_report_cache_ranking`.`dashboard_KPI_type_id`," . " `dashboard_report_cache_ranking`.`rank`,`dashboard_report_cache_ranking`.`dashboard_report_age_class_id`,`dashboard_report_cache_ranking`.`trade_area_id`,`dashboard_report_cache_ranking`.`is_percentage`" . " FROM " . " `dashboard_report_cache_ranking` where `dashboard_report_cache_log_id` = $this->m_dashboard_log_id and `store_id` = $this->m_store_id  and `monthvar` = $this->m_monthvar and `yearvar` = $this->m_yearvar order by `is_percentage`, `dashboard_KPI_type_id`";
		$obj = DAO_CFactory::create("dashboard_report_cache_ranking_national");

		$obj->query($sql);
		while ($obj->fetch())
		{

			// a null trade area means national
			if (is_null($obj->trade_area_id))
			{
				if (is_null($obj->dashboard_report_age_class_id))
				{
					$this->m_national_ranking[$obj->dashboard_KPI_type_id]['all'][$obj->is_percentage] = array('rank' => $obj->rank);
				}
				else
				{
					$this->m_national_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage] = array('rank' => $obj->rank);
				}
			}
			else
			{
				// trade area
				if (is_null($obj->dashboard_report_age_class_id))
				{
					$this->m_regional_ranking[$obj->dashboard_KPI_type_id]['all'][$obj->is_percentage] = array('rank' => $obj->rank);
				}
				else
				{
					$this->m_regional_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage] = array('rank' => $obj->rank);
				}
			}
		}

		$sql = "SELECT `dashboard_report_cache_ranking_count`.`dashboard_KPI_type_id`,`dashboard_report_cache_ranking_count`.`total`,`dashboard_report_cache_ranking_count`.`dashboard_report_age_class_id`, " . " `dashboard_report_cache_ranking_count`.`trade_area_id`,`dashboard_report_cache_ranking_count`.`is_percentage` " . " FROM " . " `dashboard_report_cache_ranking_count` where `dashboard_report_cache_log_id` = $this->m_dashboard_log_id and  `monthvar` = $this->m_monthvar and `yearvar` = $this->m_yearvar order by is_percentage, `dashboard_KPI_type_id`";
		$obj = DAO_CFactory::create("dashboard_report_cache_ranking_national");
		$obj->query($sql);

		$maxnationalstores = array();
		$maxnationalstoresage = array();
		$maxregionalstores = array();
		$maxregionalstoresage = array();

		while ($obj->fetch())
		{
			if (is_null($obj->trade_area_id))
			{
				if (is_null($obj->dashboard_report_age_class_id))
				{
					if (!empty($this->m_national_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage]))
					{
						array_push($this->m_national_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage], $obj->total);
						if (empty($maxnationalstores[$obj->is_percentage]))
						{
							$maxnationalstores[$obj->is_percentage] = 0;
						}
						$maxnationalstores[$obj->is_percentage] = max($obj->total, $maxnationalstores[$obj->is_percentage]);
					}
				}
				else
				{
					if ($obj->dashboard_report_age_class_id == $this->m_age_class_id)
					{
						if (!empty($this->m_national_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage]))
						{
							array_push($this->m_national_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage], $obj->total);
							if (empty($maxnationalstoresage[$obj->is_percentage]))
							{
								$maxnationalstoresage[$obj->is_percentage] = 0;
							}
							$maxnationalstoresage[$obj->is_percentage] = max($obj->total, $maxnationalstoresage[$obj->is_percentage]);
						}
					}
				}
			}
			else
			{
				// trade area

				if (is_null($obj->dashboard_report_age_class_id))
				{
					if ($obj->trade_area_id == $this->m_trade_area_id)
					{
						if (!empty($this->m_regional_ranking[$obj->dashboard_KPI_type_id]['all'][$obj->is_percentage]))
						{
							array_push($this->m_regional_ranking[$obj->dashboard_KPI_type_id]['all'][$obj->is_percentage], $obj->total);
							if (empty($maxregionalstores[$obj->is_percentage]))
							{
								$maxregionalstores[$obj->is_percentage] = 0;
							}
							$maxregionalstores[$obj->is_percentage] = max($obj->total, $maxregionalstores[$obj->is_percentage]);
						}
					}
				}
				else
				{
					if ($obj->dashboard_report_age_class_id == $this->m_age_class_id && $obj->trade_area_id == $this->m_trade_area_id)
					{
						if (!empty($this->m_regional_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage]))
						{
							array_push($this->m_regional_ranking[$obj->dashboard_KPI_type_id]['age'][$obj->is_percentage], $obj->total);
							if (empty($maxregionalstoresage[$obj->is_percentage]))
							{
								$maxregionalstoresage[$obj->is_percentage] = 0;
							}
							$maxregionalstoresage[$obj->is_percentage] = max($obj->total, $maxregionalstoresage[$obj->is_percentage]);
						}
					}
				}
			}
		}

		if (!empty($this->m_regional_ranking))
		{
			foreach ($this->m_regional_ranking as $key => $element)
			{
				foreach ($element as $subkey => $subelement)
				{
					if ($subkey == 'all')
					{
						$this->m_regional_ranking[$key][$subkey][0][0] = isset($maxregionalstores[0]) ? $maxregionalstores[0] : null;
						$this->m_regional_ranking[$key][$subkey][1][0] = isset($maxregionalstores[1]) ? $maxregionalstores[1] : null;
					}
					else
					{
						$this->m_regional_ranking[$key][$subkey][0][0] = isset($maxregionalstoresage[0]) ? $maxregionalstoresage[0] : null;
						$this->m_regional_ranking[$key][$subkey][1][0] = isset($maxregionalstoresage[1]) ? $maxregionalstoresage[1] : null;
					}
				}
			}

			foreach ($this->m_national_ranking as $key => $element)
			{
				foreach ($element as $subkey => $subelement)
				{
					if ($subkey == 'all')
					{
						$this->m_national_ranking[$key][$subkey][0][0] = isset($maxnationalstores[0]) ? $maxnationalstores[0] : null;
						$this->m_national_ranking[$key][$subkey][1][0] = isset($maxnationalstores[1]) ? $maxnationalstores[1] : null;
					}
					else
					{
						$this->m_national_ranking[$key][$subkey][0][0] = isset($maxnationalstoresage[0]) ? $maxnationalstoresage[0] : null;
						$this->m_national_ranking[$key][$subkey][1][0] = isset($maxnationalstoresage[1]) ? $maxnationalstoresage[1] : null;
					}
				}
			}
		}

		if (!empty($this->m_national_ranking))
		{
			foreach ($this->m_national_ranking as $key => $element)
			{
				foreach ($element as $subkey => $subelement)
				{
					foreach ($subelement as $subkey2 => $subelement2)
					{
						if (empty($subelement2['rank']))
						{
							$this->m_national_ranking[$key][$subkey][$subkey2]['rank'] = 0;
							$this->m_national_ranking[$key][$subkey][$subkey2][0] = 0;
							$this->m_national_ranking[$key][$subkey][$subkey2][0] = 0;
						}
					}
				}
			}
		}

		if (!empty($this->m_regional_ranking))
		{

			foreach ($this->m_regional_ranking as $key => $element)
			{
				foreach ($element as $subkey => $subelement)
				{
					foreach ($subelement as $subkey2 => $subelement2)
					{
						if (empty($subelement2['rank']))
						{
							$this->m_regional_ranking[$key][$subkey][$subkey2]['rank'] = 0;
							$this->m_regional_ranking[$key][$subkey][$subkey2][0] = 0;
							$this->m_regional_ranking[$key][$subkey][$subkey2][0] = 0;
						}
					}
				}
			}
		}
	}

	// called internally only
	//dashboard_report_cache_ranking_national, dashboard_report_dashboard_kpi_type, dashboard_report_list_type
	function topStoreLists()
	{
		$obj = DAO_CFactory::create("dashboard_report_dashboard_kpi_type");
		$obj->orderBy('id');
		$obj->find();

		while ($obj->fetch())
		{
			$this->m_top_store_lists[1][$obj->kpi_type] = null;
			$this->m_top_store_lists[2][$obj->kpi_type] = null;
		}

		$sql = "SELECT dashboard_KPI_type_id, " . "dashboard_report_list_type_id,  " . "`dashboard_report_dashboard_kpi_type`.`kpi_type`,`store`.`store_name`,`store`.`home_office_id`, store.city, store.state_id," . "`dashboard_report_cache_ranking_national`.`list_value`,yearvar,monthvar" . " FROM " . " `dashboard_report_cache_ranking_national` " . " Inner Join `dashboard_report_dashboard_kpi_type` ON `dashboard_report_cache_ranking_national`.`dashboard_KPI_type_id` = `dashboard_report_dashboard_kpi_type`.`id` " . " Inner Join `dashboard_report_list_type` ON `dashboard_report_cache_ranking_national`.`dashboard_report_list_type_id` = `dashboard_report_list_type`.`id` " . " Inner Join `store` ON `dashboard_report_cache_ranking_national`.`store_id` = `store`.`id` " . " where `dashboard_report_cache_log_id` = $this->m_dashboard_log_id and `yearvar` = '$this->m_yearvar' and `monthvar` = '$this->m_monthvar' " . " order by `dashboard_report_list_type_id`, `dashboard_KPI_type_id`, `list_value` desc ";

		/*

				$sql = "SELECT dashboard_KPI_type_id, dashboard_report_list_type_id, " .
				" `dashboard_report_dashboard_kpi_type`.`kpi_type`,`store`.`store_name`,`store`.`home_office_id`, store.city, store.state_id," .
				" `dashboard_report_cache_ranking_national`.`list_value`,yearvar,monthvar " .
				" FROM " .
				"`dashboard_report_cache_ranking_national`" .
				" Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_ranking_national`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`" .
				" Inner Join `dashboard_report_dashboard_kpi_type` ON `dashboard_report_cache_ranking_national`.`dashboard_KPI_type_id` = `dashboard_report_dashboard_kpi_type`.`id`" .
				" Inner Join `store` ON `dashboard_report_cache_ranking_national`.`store_id` = `store`.`id`" .
				" where  `dashboard_report_cache_log`.`active` = 1 and monthvar = '$this->m_monthvar' and yearvar = '$this->m_yearvar'" .
				//" order by `dashboard_report_cache_ranking_national`.id asc " ;
				" order by `dashboard_KPI_type_id`, dashboard_report_list_type_id, `list_value` desc " ;
		*/

		$obj = DAO_CFactory::create("dashboard_report_cache_ranking_national");
		$obj->query($sql);
		$minkey = 0;

		while ($obj->fetch())
		{

			if ($obj->kpi_type == self::KGROSS_TYPE && $obj->dashboard_report_list_type_id == 1)
			{
				if ($minkey == 0)
				{
					$minkey = $obj->list_value;
				}
				else
				{
					$minkey = min($minkey, $obj->list_value);
				}
				$this->m_top_store_lists[$obj->dashboard_report_list_type_id][$obj->kpi_type][$obj->store_name . '   (' . $obj->city . ',   ' . $obj->state_id . '   #' . $obj->home_office_id . ')'] = 0;
			}
			else
			{
				$this->m_top_store_lists[$obj->dashboard_report_list_type_id][$obj->kpi_type][$obj->store_name . '   (' . $obj->city . ',   ' . $obj->state_id . '   #' . $obj->home_office_id . ')'] = $obj->list_value;
			}
		}

		$rnd = round($minkey);
		if ($rnd > 10000)
		{
			$rnd = round($minkey, -3);
		}

		$this->min_grand_total = $rnd;
	}

	// called internally only
	//dashboard_report_cache_main, dashboard_report_cache_log
	function buildMainData()
	{

		$sql = 'SELECT ' . '`dashboard_report_cache_main`.`transaction_totals`,' . '`dashboard_report_cache_main`.`distinct_users`,' . '`dashboard_report_cache_main`.`total_sessions`,' . '`dashboard_report_cache_main`.`customers_per_session`,' . '`dashboard_report_cache_main`.`transaction_count`,' . '`dashboard_report_cache_main`.`monthvalue`,' . '`dashboard_report_cache_main`.`yearvalue`,' . '`dashboard_report_cache_main`.`is_historical`' . ' FROM ' . '`dashboard_report_cache_main` ' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' WHERE ' . "`dashboard_report_cache_main`.`store_id` =  $this->m_store_id and  `dashboard_report_cache_log`.`active` = 1" . ' order by `yearvalue`, `monthvalue`, `is_historical`';
		$obj = DAO_CFactory::create("store");
		$obj->query($sql);
		$arr = array();

		while ($obj->fetch())
		{
			$insarr = $obj->toArray();
			$insarr = array_slice($insarr, 0, 8);
			$arr[$obj->yearvalue][$obj->monthvalue][$obj->is_historical] = $insarr;
		}
		$this->m_my_results_array = $arr;
	}

	// called internally only
	// dashboard_report_cache_retention,dashboard_report_cache_log

	function BuildRetention()
	{
		$sql = "SELECT `dashboard_report_cache_retention`.retention_percentage as retention,`dashboard_report_cache_retention`.`monthvar`,`dashboard_report_cache_retention`.`yearvar`" .

			//	$sql = "SELECT `dashboard_report_cache_retention`.`retention_visit`/`dashboard_report_cache_retention`.`anchor_month_visit` as retention,`dashboard_report_cache_retention`.`monthvar`,`dashboard_report_cache_retention`.`yearvar`" .
			" FROM `dashboard_report_cache_retention` Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_retention`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`" . " WHERE `dashboard_report_cache_log`.`active` =  '1' and `dashboard_report_cache_retention`.store_id = $this->m_store_id  order by `yearvar`, monthvar";
		$obj = DAO_CFactory::create("dashboard_report_cache_retention");
		$obj->query($sql);
		$arr = array();

		while ($obj->fetch())
		{
			$insarr = $obj->toArray();
			$arr[$obj->yearvar][$obj->monthvar] = $insarr;
		}
		$this->m_my_retention = $arr;
	}

	// called internally only
	// dashboard_report_cache_instore, dashboard_report_cache_log
	function BuildInstoreSignUps()
	{

		$sql = "SELECT `dashboard_report_cache_instore`.`monthvar`,`dashboard_report_cache_instore`.`yearvar`,`dashboard_report_cache_instore`.`in_store_orders`  " . " FROM `dashboard_report_cache_instore` Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_instore`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` " . " WHERE `dashboard_report_cache_log`.`active` =  '1' and `dashboard_report_cache_instore`.store_id = $this->m_store_id order by `yearvar`, monthvar ";
		$obj = DAO_CFactory::create("dashboard_report_cache_retention");
		$obj->query($sql);
		$arr = array();

		while ($obj->fetch())
		{
			$insarr = $obj->toArray();
			$arr[$obj->yearvar][$obj->monthvar] = $insarr;
		}
		$this->m_my_instore = $arr;
	}

	// called internally only
	// dashboard_report_cache_log, dashboard_report_cache_main
	function storeResults()
	{

		$sql = 'SELECT ' . '`dashboard_report_cache_main`.`transaction_totals`,`dashboard_report_cache_main`.`distinct_users`,`dashboard_report_cache_main`.`total_sessions`, ' . '`dashboard_report_cache_main`.`customers_per_session`,`dashboard_report_cache_main`.`transaction_count`,`dashboard_report_cache_main`.`monthvalue`, ' . '`dashboard_report_cache_main`.`yearvalue`,`dashboard_report_cache_main`.`is_historical`,`dashboard_report_cache_log`.`cached_date`, ' . '`dashboard_report_cache_main`.`first_session_date`,`dashboard_report_cache_main`.`last_session_date`,`dashboard_report_cache_main`.`dashboard_report_cache_log_id` ' . ' FROM ' . '`dashboard_report_cache_log` Inner Join `dashboard_report_cache_main` ON `dashboard_report_cache_log`.`id` = `dashboard_report_cache_main`.`dashboard_report_cache_log_id` ' . ' WHERE ' . "`dashboard_report_cache_log`.`active` =  '1' AND " . "`dashboard_report_cache_main`.`store_id` =  '$this->m_store_id' order by yearvalue, monthvalue, is_historical asc ";
		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		$obj->query($sql);

		$this->m_my_results_array = array();
		while ($obj->fetch())
		{
			$this->m_my_results_array[] = $obj->toArray();
		}
	}

}

//************************************************************************************************************************************
// CDashboardReport
// This class is used directly by the caching cron process
//************************************************************************************************************************************

class CDashboardReport
{
	private $m_cache_record = null;
	private $m_prev_cache_record = null;
	public $m_error_status = null;
	public $m_error_message = null;
	public $m_records_processed = 0;
	private $m_caching_date = null;
	private $m_prev_month = null;
	private $m_prev_year = null;
	private $m_number_months_back_to_capture = 3; // how many months should we show back...

	private $currentMonth = null;
	private $currentYear = null;
	private $m_regions = null;
	private $m_ageclass = null;
	public $m_error_array = null;

	public $m_current_starting_month_date = null;
	public $m_current_ending_month_date = null;

	private $mincapvalue = 25;

	const KSUCCESS = 'SUCCESS';
	const KFAILURE = 'FAILURE';
	const KBADCACHERECORDINSERT = 'FAILEDINSERTCACHE';

	const K3MONTHRETENTION = 1;
	const K5MONTHRETENTION = 2;
	const K10MONTHRETENTION = 3;

	const KTOPPERFORMERS = 1;
	const KMOSTIMPROVED = 2;

	const KLISTTYPE_GROSSREV = 1;
	const KLISTTYPE_RETENTION = 2;
	const KLISTTYPE_SIGNUPS = 3;
	const KLISTTYPE_COUNT = 4;
	const KLISTTYPE_AVERAGE = 5;

	function CDashboardReport($override_historical_date = null)
	{
		// start transaction here
		// update both the coach and store trade area tables to remove deleted or in active stores

		$this->disableInActiveStores();

		$this->m_prev_cache_record = $this->getCurrentCacheRecord();
		$this->DeActivateCacheRecord($this->m_prev_cache_record);
		$this->m_cache_record = $this->insertCacheRecord($override_historical_date);

		if ($this->m_cache_record == null)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not create a cache record id.  Please review the insertCacheRecord method.");
		}

		$this->fillDateVars($this->m_caching_date);

		$rslt = $this->getStoreAgeRegionInfo();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not create Store Region Age Information.");
		}

		$rslt = $this->insertMainReportData();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not insert main report data.");
		}

		$rslt = $this->updateMainReportDataAdj();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data.");
		}

		$rslt = $this->updateMainReportDataDiscountsA();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataDiscountsA.");
		}

		$rslt = $this->updateMainReportDataDiscountsB();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataDiscountsB.");
		}

		$rslt = $this->updateMainReportDataProgramDiscounts();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataProgramDiscounts.");
		}

		$rslt = $this->insertYearPlusData();
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not insert Year Plus Data.");
		}

		$rslt = $this->updateMainReportDataAdj(1);
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data=1.");
		}

		$rslt = $this->updateMainReportDataDiscountsA(1);
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataDiscountsA=1 .");
		}

		$rslt = $this->updateMainReportDataDiscountsB(1);
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataDiscountsB=1.");
		}

		$rslt = $this->updateMainReportDataProgramDiscounts(1);
		if ($rslt == false)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not update main report data updateMainReportDataProgramDiscounts=1.");
		}

		$storedatarecords = 0;
		$recordsprocessed = 0;
		for ($i = 0; $i <= $this->m_number_months_back_to_capture; $i++)
		{
			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1] - $i, '01', $dateArr[0]);
			$newdate = date("Y-m-d 00:00:00", $temp);
			$storedatarecords = $this->insertInStoreData($newdate);
			if ($storedatarecords == false)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not insert in store data");
			}

			$recordsprocessed = $this->insertRetentionData(CDashboardReport::K3MONTHRETENTION, $newdate);
			if ($recordsprocessed == false)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: Could not insert retention data");
			}
		}
		$this->updateMonthlyPercentagesRetention($this->currentYear, $this->currentMonth);

		// update in store counts to use the total guest count for the month as well

		$this->createRankingCache($newdate);

		$this->TopPerformers($this->m_current_starting_month_date);

		$this->MostImprovedList($this->m_current_starting_month_date);
	}

	// called internally only
	// store_trade_area, store
	function disableInActiveStores()
	{

		$sql1 = "update `store_trade_area` , (SELECT `store_trade_area`.`store_id` FROM `store` Inner Join `store_trade_area` ON `store`.`id` = `store_trade_area`.`store_id` " . " where `store`.`active` = 0 or `store`.`is_deleted` = 1 ) as subquery1 set `store_trade_area`.is_active = 0, `store_trade_area`.is_deleted = 1 where subquery1.`store_id` = `store_trade_area`.store_id";
		$obj = DAO_CFactory::create("store_trade_area");
		$rslt = $obj->query($sql1);
		$obj = null;

		$sql2 = "update `store_coach` , (SELECT `store_coach`.`store_id` FROM `store` Inner Join `store_coach` ON `store`.`id` = `store_coach`.`store_id` " . "where `store`.`active` = 0 or `store`.`is_deleted` = 1 ) as subquery1 set `store_coach`.is_active = 0, `store_coach`.is_deleted = 1 where subquery1.`store_id` = `store_coach`.store_id";
		$obj = DAO_CFactory::create("store_coach");
		$rslt = $obj->query($sql2);
	}

	function addError($status, $message)
	{
		$ds = date("Y-m-d H:m:s");
		$m_error_array[] = array(
			$status,
			$message,
			$ds
		);
	}


	// called internally only
	//dashboard_report_cache_main, dashboard_report_cache_log, dashboard_report_cache_store_classification
	function GenerateRetentionRanking()
	{

		$sql = "SELECT `dashboard_report_cache_main`.`transaction_totals`,`dashboard_report_cache_main`.`store_id`,`dashboard_report_cache_main`.`monthvalue`,`dashboard_report_cache_main`.`yearvalue`, " . " `dashboard_report_cache_store_classification`.`dashboard_report_age_class_id`,`dashboard_report_cache_store_classification`.`trade_area_id`" . " FROM `dashboard_report_cache_main` Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`" . " Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id`" . " WHERE `dashboard_report_cache_log`.`active` =  '1' and `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id`" . " and `dashboard_report_cache_main`.`is_historical` = 0 order by `store_id`, `yearvalue`, monthvalue";

		$obj = DAO_CFactory::create("dashboard_report_age_class");
		$rslt = $obj->query($varstr);
		if ($rslt == 0)
		{
			$success = false;
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: GenerateRetentionRanking did not have any data");
		}

		return $success;
	}

	// called internally only
	//dashboard_report_age_class, store, store_trade_area
	function getStoreAgeRegionInfo()
	{

		$success = true;
		$varstr = 'insert into dashboard_report_cache_store_classification (store_id,dashboard_report_cache_log_id , dashboard_report_age_class_id, trade_area_id, age_months)' . " SELECT  stores_detail.store_id, $this->m_cache_record as dashboard_report_cache_id, `dashboard_report_age_class`.id as age_class_id,stores_detail.`trade_area_id` as trade_area_id , stores_detail.ageclass as age_months " . ' FROM `dashboard_report_age_class` ,' . ' (SELECT store.id as store_id, `trade_area`.`region`,`store`.`store_name`,`store`.`city`,`store`.`state_id`,`store`.`home_office_id`,`store`.`email_address`,`store`.`grand_opening_date`, `trade_area`.`id` ' . " AS `trade_area_id`,`trade_area`.`id` AS trade_area_state_country_id,`store_trade_area`.`id` as franchise_store_trade_area_id, PERIOD_DIFF(DATE_FORMAT(now(), '%Y%m'), DATE_FORMAT(`grand_opening_date`, '%Y%m')) as ageclass  " . ' FROM  `store`  Inner Join `store_trade_area` ON `store`.`id` = `store_trade_area`.`store_id`  Inner Join `trade_area` ON `store_trade_area`.`trade_area_id` = `trade_area`.`id`' . ' where store.active = 1 and store.is_deleted = 0 and `store_trade_area`.`is_active` = 1 and `store_trade_area`.`is_deleted`  = 0  and `trade_area`.`is_active` = 1  ' . ' and `grand_opening_date` is not null  ) as stores_detail where stores_detail.ageclass >= `starting_period` ' . ' and stores_detail.ageclass <= ending_period order by `dashboard_report_age_class`.rank';

		$obj = DAO_CFactory::create("dashboard_report_cache_store_classification");
		$rslt = $obj->query($varstr);
		if ($rslt == 0)
		{
			$success = false;
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: getStoreAgeRegionInfo did not have any data");
		}

		return $success;
	}

	// called internally only
	function fillDateVars($datevar)
	{


		$dateArr = explode('-', $datevar);
		$this->currentMonth = $dateArr[1];
		$this->currentYear = $dateArr[0];

		$this->m_current_starting_month_date = $dateArr[0] . '-' . $dateArr[1] . '-01 00:00:00';

		$numberOfDaysInMonth = date('t', strtotime($datevar));

		$this->m_current_ending_month_date = $dateArr[0] . '-' . $dateArr[1] . '-' . $numberOfDaysInMonth . ' 23:59:59';

		$prevmonth = $dateArr[1];
		$prevyear = $dateArr[0];

		$prevmonth--;
		if ($prevmonth == 0)
		{
			$prevmonth = 12;
			$prevyear--;
		}

		$this->m_prev_month = $prevmonth;
		$this->m_prev_year = $prevyear;
	}

	function insertCacheRecord($rebuilddate = null)
	{
		$rslt = null;
		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		if (!empty($rebuilddate))
		{
			$this->m_caching_date = $rebuilddate;
		}
		else
		{
			$this->m_caching_date = date("Y-m-d H:i:s");
		}

		$obj->cached_date = $this->m_caching_date;
		$obj->active = 1;
		$rslt = $obj->insert();

		return $rslt;
	}

	function getCurrentCacheRecord()
	{
		$rslt = null;
		$id = null;
		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		$obj->active = 1;
		$obj->orderby('cached_date desc');  // just in case
		$rslt = $obj->find(true);
		if ($rslt > 0)
		{
			$id = $obj->id;
		}

		return $id;
	}

	function DeActivateCacheRecord($previousCacheID)
	{
		// set the old cache id to inactive
		$rslt = null;
		if ($previousCacheID > 0)
		{
			$obj = DAO_CFactory::create("dashboard_report_cache_log");
			$obj->id = $previousCacheID;
			$obj->active = 0;
			$rslt = $obj->update();
		}

		return $rslt;
		// set the new cache id to active

	}

	// called internally only
	// dashboard_report_cache_ranking, dashboard_report_cache_store_classification, dashboard_report_cache_ranking_count
	function createRankingCacheElement($table, $fieldname, $dashboard_KPI_type_id, $is_percentage, $currentyear, $currentmonth, $tradeareaid, $ageclassid)
	{
		$success = true;
		$obj = DAO_CFactory::create("dashboard_report_cache_main");
		$sql = "set @rank = 0";
		$obj->query($sql);

		if ($ageclassid == null)
		{
			$ageclassid = 'null';
		}
		if ($tradeareaid == null)
		{
			$tradeareaid = 'null';
		}

		$sql = "insert into dashboard_report_cache_ranking " . "(dashboard_report_cache_log_id, dashboard_KPI_type_id, store_id,monthvar, yearvar, dashboard_report_age_class_id,trade_area_id, is_percentage, rank ) " . "SELECT innerQuery.*, @rank := @rank +1 AS rank from " . "(SELECT $this->m_cache_record as dashboard_report_cache_log_id, $dashboard_KPI_type_id, `$table`.`store_id`,$currentmonth as monthvalue,$currentyear as yearvalue,$ageclassid as ageclassid,$tradeareaid as `trade_area_id`,$is_percentage as is_percentage " . "FROM `$table` Inner Join `dashboard_report_cache_store_classification` ON `$table`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` " . "where ";

		if ($tradeareaid != null && $tradeareaid != 'null')
		{
			$sql .= "`trade_area_id`= $tradeareaid and ";
		}

		if ($ageclassid != null && $ageclassid != 'null')
		{
			$sql .= "dashboard_report_age_class_id = $ageclassid and ";
		}

		if ($dashboard_KPI_type_id == CDashboardReport::KLISTTYPE_SIGNUPS || $dashboard_KPI_type_id == CDashboardReport::KLISTTYPE_RETENTION)
		{
			$sql .= "  yearvar = $currentyear and monthvar = $currentmonth";
		}
		else
		{
			$sql .= " `is_historical` = 0 and yearvalue = $currentyear and monthvalue = $currentmonth";
		}

		$sql .= " and `$table`.`dashboard_report_cache_log_id` = $this->m_cache_record and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` = $this->m_cache_record " . " and $fieldname is not null ";

		if ($dashboard_KPI_type_id == CDashboardReport::KLISTTYPE_AVERAGE || $dashboard_KPI_type_id == CDashboardReport::KLISTTYPE_GROSSREV || $dashboard_KPI_type_id == CDashboardReport::KLISTTYPE_COUNT)
		{
			$sql .= " and  `dashboard_report_cache_store_classification`.dashboard_report_cache_log_id = `dashboard_report_cache_main`.dashboard_report_cache_log_id ";
		}

		$sql .= " order by `$fieldname` desc) as innerQuery";

		$rslt = $obj->query($sql);

		if ($rslt == 0)
		{
			$success = false;
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: createRankingCacheElement could not create cache element");
		}
		else
		{
			$obj = DAO_CFactory::create("dashboard_report_cache_ranking_count");
			$sql = "INSERT INTO `dashboard_report_cache_ranking_count` (`dashboard_report_cache_log_id`,`dashboard_KPI_type_id`,`total`,`monthvar`,`yearvar`,`is_percentage`, " . " dashboard_report_age_class_id,trade_area_id) VALUES ($this->m_cache_record,$dashboard_KPI_type_id,$rslt,'$currentmonth',$currentyear,$is_percentage, $ageclassid,$tradeareaid)";
			$rslt = $obj->query($sql);
		}

		return $success;
	}

	// called internally only
	function getAgeClass()
	{
		$obj = DAO_CFactory::create("dashboard_report_age_class");
		$sql = "SELECT `dashboard_report_age_class`.`id`,`dashboard_report_age_class`.`description` FROM `dashboard_report_age_class` order by rank";
		$rslt = $obj->query($sql);
		while ($obj->fetch())
		{
			$this->m_ageclass[$obj->id] = $obj->description;
		}
	}

	// called internally only
	function getRegions()
	{
		$obj = DAO_CFactory::create("trade_area");
		$sql = "SELECT `trade_area`.`region`,`trade_area`.`id` FROM `trade_area` where `is_active` = 1";
		$rslt = $obj->query($sql);
		while ($obj->fetch())
		{
			$this->m_regions[$obj->id] = $obj->region;
		}
	}

	// called internally only

	function createRankingCache()
	{
		$this->getAgeClass();
		$this->getRegions();

		$success = true;
		$currentmonth = $this->currentMonth;
		$currentyear = $this->currentYear;

		for ($i = 0; $i <= $this->m_number_months_back_to_capture; $i++)
		{
			if ($i > 0)
			{
				$currentmonth--;
				if ($currentmonth == 0)
				{
					$currentmonth = 12;
					$currentyear--;
				}
			}

			// national rankings first [ no aging ]
			$table = 'dashboard_report_cache_main';
			$this->createRankingCacheElement($table, 'transaction_totals', CDashboardReport::KLISTTYPE_GROSSREV, 0, $currentyear, $currentmonth, null, null);
			$this->createRankingCacheElement($table, 'percent_change_transactions', CDashboardReport::KLISTTYPE_GROSSREV, 1, $currentyear, $currentmonth, null, null);

			$this->createRankingCacheElement($table, 'distinct_users', CDashboardReport::KLISTTYPE_COUNT, 0, $currentyear, $currentmonth, null, null);
			$this->createRankingCacheElement($table, 'percent_change_distinct', CDashboardReport::KLISTTYPE_COUNT, 1, $currentyear, $currentmonth, null, null);

			$this->createRankingCacheElement($table, 'customers_per_session', CDashboardReport::KLISTTYPE_AVERAGE, 0, $currentyear, $currentmonth, null, null);
			$this->createRankingCacheElement($table, 'percent_change_session_avg', CDashboardReport::KLISTTYPE_AVERAGE, 1, $currentyear, $currentmonth, null, null);

			$table = 'dashboard_report_cache_instore';
			$this->createRankingCacheElement($table, 'in_store_orders', CDashboardReport::KLISTTYPE_SIGNUPS, 0, $currentyear, $currentmonth, null, null);
			$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_SIGNUPS, 1, $currentyear, $currentmonth, null, null);

			$table = 'dashboard_report_cache_retention';
			$this->createRankingCacheElement($table, 'retention_percentage', CDashboardReport::KLISTTYPE_RETENTION, 0, $currentyear, $currentmonth, null, null);
			$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_RETENTION, 1, $currentyear, $currentmonth, null, null);

			// region

			// regional items with no aging
			foreach ($this->m_regions as $key => $element)
			{
				$table = 'dashboard_report_cache_main';

				$this->createRankingCacheElement($table, 'transaction_totals', CDashboardReport::KLISTTYPE_GROSSREV, 0, $currentyear, $currentmonth, $key, null);
				$this->createRankingCacheElement($table, 'percent_change_transactions', CDashboardReport::KLISTTYPE_GROSSREV, 1, $currentyear, $currentmonth, $key, null);

				$this->createRankingCacheElement($table, 'distinct_users', CDashboardReport::KLISTTYPE_COUNT, 0, $currentyear, $currentmonth, $key, null);
				$this->createRankingCacheElement($table, 'percent_change_distinct', CDashboardReport::KLISTTYPE_COUNT, 1, $currentyear, $currentmonth, $key, null);

				$this->createRankingCacheElement($table, 'customers_per_session', CDashboardReport::KLISTTYPE_AVERAGE, 0, $currentyear, $currentmonth, $key, null);
				$this->createRankingCacheElement($table, 'percent_change_session_avg', CDashboardReport::KLISTTYPE_AVERAGE, 1, $currentyear, $currentmonth, $key, null);

				$table = 'dashboard_report_cache_instore';
				$this->createRankingCacheElement($table, 'in_store_orders', CDashboardReport::KLISTTYPE_SIGNUPS, 0, $currentyear, $currentmonth, $key, null);
				$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_SIGNUPS, 1, $currentyear, $currentmonth, $key, null);

				$table = 'dashboard_report_cache_retention';
				$this->createRankingCacheElement($table, 'retention_percentage', CDashboardReport::KLISTTYPE_RETENTION, 0, $currentyear, $currentmonth, $key, null);
				$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_RETENTION, 1, $currentyear, $currentmonth, $key, null);
			}

			// national with age

			foreach ($this->m_ageclass as $key => $element)
			{
				$table = 'dashboard_report_cache_main';
				$this->createRankingCacheElement($table, 'transaction_totals', CDashboardReport::KLISTTYPE_GROSSREV, 0, $currentyear, $currentmonth, null, $key);
				$this->createRankingCacheElement($table, 'percent_change_transactions', CDashboardReport::KLISTTYPE_GROSSREV, 1, $currentyear, $currentmonth, null, $key);

				$this->createRankingCacheElement($table, 'distinct_users', CDashboardReport::KLISTTYPE_COUNT, 0, $currentyear, $currentmonth, null, $key);
				$this->createRankingCacheElement($table, 'percent_change_distinct', CDashboardReport::KLISTTYPE_COUNT, 1, $currentyear, $currentmonth, null, $key);

				$this->createRankingCacheElement($table, 'customers_per_session', CDashboardReport::KLISTTYPE_AVERAGE, 0, $currentyear, $currentmonth, null, $key);
				$this->createRankingCacheElement($table, 'percent_change_session_avg', CDashboardReport::KLISTTYPE_AVERAGE, 1, $currentyear, $currentmonth, null, $key);

				$table = 'dashboard_report_cache_instore';
				$this->createRankingCacheElement($table, 'in_store_orders', CDashboardReport::KLISTTYPE_SIGNUPS, 0, $currentyear, $currentmonth, null, $key);
				$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_SIGNUPS, 1, $currentyear, $currentmonth, null, $key);

				$table = 'dashboard_report_cache_retention';
				$this->createRankingCacheElement($table, 'retention_percentage', CDashboardReport::KLISTTYPE_RETENTION, 0, $currentyear, $currentmonth, null, $key);
				$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_RETENTION, 1, $currentyear, $currentmonth, null, $key);
			}

			// regional with age
			foreach ($this->m_regions as $subkey => $subelement)
			{
				foreach ($this->m_ageclass as $key => $element)
				{
					$table = 'dashboard_report_cache_main';
					$this->createRankingCacheElement($table, 'transaction_totals', CDashboardReport::KLISTTYPE_GROSSREV, 0, $currentyear, $currentmonth, $subkey, $key);
					$this->createRankingCacheElement($table, 'percent_change_transactions', CDashboardReport::KLISTTYPE_GROSSREV, 1, $currentyear, $currentmonth, $subkey, $key);

					$this->createRankingCacheElement($table, 'distinct_users', CDashboardReport::KLISTTYPE_COUNT, 0, $currentyear, $currentmonth, $subkey, $key);
					$this->createRankingCacheElement($table, 'percent_change_distinct', CDashboardReport::KLISTTYPE_COUNT, 1, $currentyear, $currentmonth, $subkey, $key);

					$this->createRankingCacheElement($table, 'customers_per_session', CDashboardReport::KLISTTYPE_AVERAGE, 0, $currentyear, $currentmonth, $subkey, $key);
					$this->createRankingCacheElement($table, 'percent_change_session_avg', CDashboardReport::KLISTTYPE_AVERAGE, 1, $currentyear, $currentmonth, $subkey, $key);

					$table = 'dashboard_report_cache_instore';
					$this->createRankingCacheElement($table, 'in_store_orders', CDashboardReport::KLISTTYPE_SIGNUPS, 0, $currentyear, $currentmonth, $subkey, $key);
					$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_SIGNUPS, 1, $currentyear, $currentmonth, $subkey, $key);

					$table = 'dashboard_report_cache_retention';
					$this->createRankingCacheElement($table, 'retention_percentage', CDashboardReport::KLISTTYPE_RETENTION, 0, $currentyear, $currentmonth, $subkey, $key);
					$this->createRankingCacheElement($table, 'percent_change', CDashboardReport::KLISTTYPE_RETENTION, 1, $currentyear, $currentmonth, $subkey, $key);
				}
			}
		}
	}

	//called internally only
	//dashboard_report_cache_ranking_national, dashboard_report_cache_log, dashboard_report_cache_store_classification, dashboard_report_cache_retention,dashboard_report_cache_instore
	//dashboard_report_cache_main, etc.
	function TopPerformers($currentdate)
	{

		// run this three times
		$dateArr = explode('-', $currentdate);
		$monthvar = $dateArr[1];
		$yearvar = $dateArr[0];

		$listtype = 1;
		$listsize = 20;
		// ***************************************
		// add in order.. we will use the natural primary key ordering since this is auto generated

		$dashboard_KPI_types = 1;
		$sql = 'insert into dashboard_report_cache_ranking_national' . " (  store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . " SELECT  `dashboard_report_cache_main`.`store_id`,`dashboard_report_cache_main`.`%s` as list_value, $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id, " . ' `monthvalue` , `yearvalue`' . ' FROM ' . ' `dashboard_report_cache_main` ' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` ' . ' WHERE ' . " `dashboard_report_cache_log`.`active` =  '1' and `is_historical` = 0 and `yearvalue` = '%s' and `monthvalue` = '%s' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  `dashboard_report_cache_main`.`dashboard_report_cache_log_id` " . ' order by `transaction_totals` desc ' . " limit $listsize ";

		$table = 'dashboard_report_cache_main';

		$fieldname = 'transaction_totals';
		$msg = $this->generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar);
		if ($msg == false)
		{
			$status = CDashboardReport::KBADCACHERECORDINSERT;
			$message = "WARNING: 001 National List data Type " . $dashboard_KPI_types;
			$this->addError($status, $message);
		}

		// ***************************************

		$dashboard_KPI_types = 2;
		$sql = 'insert into dashboard_report_cache_ranking_national' . " ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . ' SELECT  `dashboard_report_cache_retention`.`store_id`,' . ' (`dashboard_report_cache_retention`.`retention_visit`/`dashboard_report_cache_retention`.`anchor_month_visit`)*100 AS `retention_value`,' . " $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id," . ' `dashboard_report_cache_retention`.`monthvar`,`dashboard_report_cache_retention`.`yearvar`' . ' FROM `dashboard_report_cache_retention`' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_retention`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id`' . ' Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_retention`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` ' . ' WHERE ' . "`dashboard_report_cache_log`.`active` =  '1' and yearvar = '%s' and monthvar = '%s' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  `dashboard_report_cache_retention`.`dashboard_report_cache_log_id` " . ' order by retention_value desc ' . " limit $listsize";

		$fieldname = null;
		$table = 'dashboard_report_cache_retention';
		$msg = $this->generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar);
		if ($msg == false)
		{
			$status = CDashboardReport::KBADCACHERECORDINSERT;
			$message = "WARNING: 002 National List data Type " . $dashboard_KPI_types;
			$this->addError($status, $message);
		}

		// ***************************************
		$dashboard_KPI_types = 3;

		$sql = 'insert into dashboard_report_cache_ranking_national' . " ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . ' SELECT  ' . ' `dashboard_report_cache_instore`.`store_id`,' . ' `dashboard_report_cache_instore`.`in_store_orders`,' . " $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id," . ' `dashboard_report_cache_instore`.`monthvar`,`dashboard_report_cache_instore`.`yearvar`' . ' FROM ' . ' `dashboard_report_cache_instore`' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_instore`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_instore`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` ' .

			' WHERE ' . " `dashboard_report_cache_log`.`active` =  '1' and yearvar = '%s' and monthvar = '%s' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  `dashboard_report_cache_instore`.`dashboard_report_cache_log_id` " . ' order by `in_store_orders` desc ' . " limit $listsize";

		$table = 'dashboard_report_cache_instore';
		$fieldname = null;
		$msg = $this->generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar);
		if ($msg == false)
		{
			$status = CDashboardReport::KBADCACHERECORDINSERT;
			$message = "WARNING: 003 National List data Type " . $dashboard_KPI_types;
			$this->addError($status, $message);
		}
		// ***************************************

		$dashboard_KPI_types = 4;
		$sql = 'insert into dashboard_report_cache_ranking_national' . " ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . " SELECT  `dashboard_report_cache_main`.`store_id`,`dashboard_report_cache_main`.`%s` as list_value, $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id, " . ' `monthvalue` , `yearvalue`' . ' FROM ' . ' `dashboard_report_cache_main` ' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` ' . ' WHERE ' . " `dashboard_report_cache_log`.`active` =  '1' and `is_historical` = 0 and `yearvalue` = '%s' and `monthvalue` = '%s' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  `dashboard_report_cache_main`.`dashboard_report_cache_log_id` " . ' order by `distinct_users` desc ' . " limit $listsize ";

		$table = 'dashboard_report_cache_main';

		$fieldname = 'distinct_users';
		$msg = $this->generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar);
		if ($msg == false)
		{
			$status = CDashboardReport::KBADCACHERECORDINSERT;
			$message = "WARNING: 004 National List data Type " . $dashboard_KPI_types;
			$this->addError($status, $message);
		}

		// ***************************************
		$dashboard_KPI_types = 5;
		$sql = 'insert into dashboard_report_cache_ranking_national' . " ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . " SELECT  `dashboard_report_cache_main`.`store_id`,`dashboard_report_cache_main`.`%s` as list_value, $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id, " . ' `monthvalue` , `yearvalue`' . ' FROM ' . ' `dashboard_report_cache_main` ' . ' Inner Join `dashboard_report_cache_log` ON `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_log`.`id` ' . ' Inner Join `dashboard_report_cache_store_classification` ON `dashboard_report_cache_main`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` ' . ' WHERE ' . " `dashboard_report_cache_log`.`active` =  '1' and `is_historical` = 0 and `yearvalue` = '%s' and `monthvalue` = '%s' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  `dashboard_report_cache_main`.`dashboard_report_cache_log_id` " . ' order by `customers_per_session` desc ' . " limit $listsize ";
		$table = 'dashboard_report_cache_main';

		$fieldname = 'customers_per_session';
		$msg = $this->generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar);
		if ($msg == false)
		{
			$status = CDashboardReport::KBADCACHERECORDINSERT;
			$message = "WARNING: 005 National List data Type " . $dashboard_KPI_types;
			$this->addError($status, $message);
		}
	}

	// called internally only
	//dashboard_report_cache_ranking_national, dashboard_report_cache_store_classification
	function GenerateImprovedMainSQL($tablename, $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, $monthfieldname, $yearfieldname)
	{
		$listtype = 2;
		$listsize = 5;
		$prevmonth = $monthvar;
		$prevyear = $yearvar;
		for ($i = 0; $i < $this->m_number_months_back_to_capture - 1; $i++)
		{

			$prevmonth--;
			if ($prevmonth == 0)
			{
				$prevmonth = 12;
				$prevyear--;
			}
			/*
			 This query is uncessary.. way too much thought... I think

						$sql = 'insert into dashboard_report_cache_ranking_national' .
						" ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " .
						" Select subquery2.store_id,  subquery2.delta, $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id, $monthvar as monthvar,$yearvar  as yearvar from (" .
						" SELECT" .
						" querysub.$fieldname as newPercentage," .
						" `$tablename`.$fieldname as previousPercentage," .
						" if (querysub.$fieldname < `$tablename`.$fieldname,0,1) AS TREND," .
						" querysub.$fieldname  -`$tablename`.$fieldname as delta," .
						" `$tablename`.`store_id`" .
						" FROM" .
						" `$tablename`" .
						" Inner Join (" .
						" SELECT" .
						" `$tablename`.$fieldname," .
						" `$tablename`.`$monthfieldname`," .
						" `$tablename`.`$yearfieldname`," .
						" `$tablename`.`store_id`" .
						" FROM" .
						" `$tablename`" .
						" Inner Join `dashboard_report_cache_store_classification` ON `$tablename`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` " .
						" WHERE" .
						" `$tablename`.`dashboard_report_cache_log_id` =  '$this->m_cache_record'" .
						" and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  '$this->m_cache_record'" .
						' and `dashboard_report_cache_store_classification`.`age_months` > 6 ' .
						" and (`$yearfieldname` = $yearvar and `$monthfieldname` = $monthvar) ) as querysub ON querysub.store_id = `$tablename`.store_id" .
						" Inner Join `dashboard_report_cache_store_classification` ON `$tablename`.`store_id` = `dashboard_report_cache_store_classification`.`store_id` " .
						" WHERE" .
						" `$tablename`.`dashboard_report_cache_log_id` =  '$this->m_cache_record' and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  '$this->m_cache_record' " .
						" and (`$tablename`.`$yearfieldname` = $prevyear and `$tablename`.`$monthfieldname` = $prevmonth)" .
						" having TREND = 1 and previousPercentage is not null" .
						" order by delta desc " .
						" limit $listsize ) as subquery2" ;
			*/

			$sql = 'insert into dashboard_report_cache_ranking_national' . " ( store_id,list_value,dashboard_KPI_type_id,dashboard_report_cache_log_id,dashboard_report_list_type_id,monthvar,yearvar ) " . " SELECT `$tablename`.`store_id`, `$tablename`.$fieldname, $dashboard_KPI_types as dashboard_KPI_type_id, $this->m_cache_record as dashboard_report_cache_log_id, $listtype as dashboard_report_list_type_id, $monthvar as monthvar,$yearvar  as yearvar " . "  FROM `$tablename` " . " Inner Join `dashboard_report_cache_store_classification` ON `$tablename`.`store_id` = `dashboard_report_cache_store_classification`.`store_id`  " . " WHERE `$tablename`.`dashboard_report_cache_log_id` =  '$this->m_cache_record' " . " and `dashboard_report_cache_store_classification`.`dashboard_report_cache_log_id` =  '$this->m_cache_record' and $fieldname > 0 and  " . " `dashboard_report_cache_store_classification`.`age_months` > 6  and (`$yearfieldname` = $yearvar and `$monthfieldname` = $monthvar) " . "  order by $fieldname desc limit $listsize ";

			$obj = DAO_CFactory::create($tablename);
			$rslt = $obj->query($sql);

			if ($rslt == 0)
			{
				$success = false;  // just indicate we had a false but don't stop progress
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: generateSQLLists had an 0 result (called from MostImprovedList or TopPerformers)");
			}

			$monthvar = $prevmonth;
			$yearvar = $prevyear;
		}
	}

	//called internally only
	// dashboard_report_cache_main, etc..
	function MostImprovedList($currentdate)
	{

		$dateArr = explode('-', $currentdate);
		$monthvar = $dateArr[1];
		$yearvar = $dateArr[0];

		$dashboard_KPI_types = 1;
		$fieldname = 'percent_change_transactions';
		$this->GenerateImprovedMainSQL('dashboard_report_cache_main', $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, 'monthvalue', 'yearvalue');

		$dashboard_KPI_types = 4;
		$fieldname = 'percent_change_distinct';
		$this->GenerateImprovedMainSQL('dashboard_report_cache_main', $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, 'monthvalue', 'yearvalue');

		$dashboard_KPI_types = 5;
		$fieldname = 'percent_change_session_avg';
		$this->GenerateImprovedMainSQL('dashboard_report_cache_main', $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, 'monthvalue', 'yearvalue');

		$dashboard_KPI_types = 2;
		$fieldname = 'percent_change';
		$this->GenerateImprovedMainSQL('dashboard_report_cache_retention', $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, 'monthvar', 'yearvar');

		$dashboard_KPI_types = 3;
		$fieldname = 'percent_change';
		$this->GenerateImprovedMainSQL('dashboard_report_cache_instore', $dashboard_KPI_types, $fieldname, $monthvar, $yearvar, 'monthvar', 'yearvar');
	}

	// called internally only
	function generateSQLLists($table, $sql, $fieldname, $monthvar, $yearvar)
	{
		$currentmonth = $monthvar;
		$currentyear = $yearvar;
		$success = true;

		for ($i = 0; $i < $this->m_number_months_back_to_capture; $i++)
		{
			if ($i > 0)
			{
				$currentmonth--;
				if ($currentmonth == 0)
				{
					$currentmonth = 12;
					$currentyear--;
				}
			}
			if (is_null($fieldname))
			{
				$newquery = sprintf($sql, $currentyear, $currentmonth);
			}
			else
			{
				$newquery = sprintf($sql, $fieldname, $currentyear, $currentmonth);
			}
			$obj = DAO_CFactory::create($table);
			$rslt = $obj->query($newquery);

			if ($rslt == 0)
			{
				$success = false;  // just indicate we had a false but don't stop progress
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: generateSQLLists had an 0 result (called from MostImprovedList or TopPerformers)");
			}
		}

		return $success;
	}

	//*********************************************************************************************************
	// Functions below calculate the main core functionality of caching the raw data
	//*********************************************************************************************************
	// called internally only
	//dashboard_report_cache_main
	function insertMainReportData()
	{
		$success = true;
		$sql = "insert into dashboard_report_cache_main (dashboard_report_cache_log_id, store_id,transaction_totals, distinct_users, total_sessions, customers_per_session, transaction_count, first_session_date, last_session_date, monthvalue, yearvalue) " . "Select $this->m_cache_record as dashboard_report_cache_id," . 'session.store_id as store_id,' . 'SUM(subtotal_all_items) as transaction_totals,' . //'count(distinct booking.user_id) as distinct_users,'.
			'count(orders.id) as distinct_users,' .  // we don't need to use distinct users per Lori comments 5/30/2007
			'count(distinct session.id) as total_sessions,' . 'count(orders.id)/count(distinct session.id) as customers_per_session,' .  // this is okay per Lori's comments
			'count(orders.id) as transaction_count,' . 'min(session_start) as first_session_date,' . 'max(session_start) as last_session_date,' . 'month(session_start) as monthvalue, year(session_start) as yearvalue ' . 'From booking Inner Join  session  ON booking.session_id  = session.id ' . 'Inner Join orders ON  booking.order_id = orders.id ' . 'Where booking.status = "ACTIVE" ' . 'and booking.is_deleted = 0 AND session.is_deleted = 0 ' . 'and session_publish_state != "SAVED" ' . "and session.session_start >=  DATE_SUB('$this->m_current_starting_month_date',INTERVAL 3 MONTH) " . "and session.session_start <= DATE_ADD('$this->m_current_starting_month_date',INTERVAL 1 MONTH) " . 'group by session.store_id, yearvalue, monthvalue ' . 'order by store_id, yearvalue, monthvalue ';

		$obj = DAO_CFactory::create("booking");
		$rslt = $obj->query($sql);
		if ($rslt == 0)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: insertMainReportData failed to work");

			return false;
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_main
	function updateMainReportDataAdj($lastyearresults = 0)
	{
		$success = true;

		if ($lastyearresults == 0)
		{
			$start = "DATE_SUB( '$this->m_current_starting_month_date' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$this->m_current_starting_month_date',INTERVAL 1 MONTH)";
		}
		else
		{
			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1], '01', $dateArr[0] - 1);
			$newdate = date("Y-m-d 00:00:00", $temp);

			//$start = "'$newdate'";
			//$end = " DATE_ADD('$newdate',INTERVAL 1 MONTH) ";

			$start = "DATE_SUB( '$newdate' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";
		}

		$sql = "update dashboard_report_cache_main, (SELECT	 $this->m_cache_record as dashboard_report_cache_id, store_id,SUM(`store_expenses`.`total_cost`) as adjustments,MONTH(`store_expenses`.`entry_date`) as month_var, " . "YEAR(`store_expenses`.`entry_date`) as year_var	FROM " . "`store_expenses` " . "where (expense_type = 'SALES_ADJUSTMENTS') " . "and (`entry_date` >= $start and `entry_date` <= $end ) " . "and  `store_expenses`.`is_deleted` = 0 " . "group by store_id, year_var, month_var) as subquery1 set dashboard_report_cache_main.transaction_totals = dashboard_report_cache_main.transaction_totals + subquery1.adjustments " . "where dashboard_report_cache_main.dashboard_report_cache_log_id =
		$this->m_cache_record and dashboard_report_cache_main.store_id = subquery1.store_id and dashboard_report_cache_main.monthvalue = subquery1.month_var and dashboard_report_cache_main.yearvalue = subquery1.year_var";

		$obj = DAO_CFactory::create("store_expenses");
		$rslt = $obj->query($sql);
		if ($rslt == 0)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: updateMainReportDataAdj failed to work");

			return false;
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_main

	function updateMainReportDataDiscountsB($lastyearresults = 0)
	{
		$success = true;

		if ($lastyearresults == 0)
		{
			$start = "DATE_SUB( '$this->m_current_starting_month_date' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$this->m_current_starting_month_date',INTERVAL 1 MONTH)";
		}
		else
		{
			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1], '01', $dateArr[0] - 1);
			$newdate = date("Y-m-d 00:00:00", $temp);

			//$start = "'$newdate'";
			//$end = " DATE_ADD('$newdate',INTERVAL 1 MONTH) ";

			$start = "DATE_SUB( '$newdate' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";
		}

		$percentage = CDreamReport::$SCRIP_PERCENTAGE_SALE;

		$query = "update dashboard_report_cache_main, (" . " select $this->m_cache_record as dashboard_report_cache_id, subquery1.store_id , sum(subquery1.amount) as discounts,  subquery1.year_var, subquery1.month_var from ( " . " SELECT  session.store_id," . " IF (`payment`.`gift_cert_type` = 'SCRIP', Sum(`payment`.`total_amount`) * $percentage ,0 )  " . " + IF (`payment`.`gift_cert_type` = 'DONATED', Sum(`payment`.`total_amount`),0 ) + IF (`payment`.`gift_cert_type` = 'VOUCHER', Sum(`payment`.`total_amount`),0 ) as amount," . " MONTH(session.session_start) as month_var," . " YEAR(session.session_start) as year_var" . " from  session Inner Join booking ON session.id = booking.session_id Inner Join orders ON  booking.order_id = orders.id  Inner Join payment ON orders.id = payment.order_id" . " Where `gift_cert_type` != 'STANDARD'  and payment_type = 'GIFT_CERT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0   AND session.is_deleted = 0 and session_publish_state != 'SAVED'  and" . " (session.session_start >= $start  AND  session.session_start <= $end)" . " group by session.store_id,year_var, month_var,gift_cert_type ) as subquery1" . " group by subquery1.store_id, subquery1.year_var, subquery1.month_var) as subquery3" .

			" set dashboard_report_cache_main.transaction_totals = dashboard_report_cache_main.transaction_totals - subquery3.discounts " . " where dashboard_report_cache_main.dashboard_report_cache_log_id = " . " $this->m_cache_record and dashboard_report_cache_main.store_id = subquery3.store_id and dashboard_report_cache_main.monthvalue = subquery3.month_var and dashboard_report_cache_main.yearvalue = subquery3.year_var";

		$obj = DAO_CFactory::create("dashboard_report_cache_main");
		$rslt = $obj->query($query);
		if ($rslt == 0)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: updateMainReportDataDiscountsA failed to work");

			return false;
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_main

	function updateMainReportDataDiscountsA($lastyearresults = 0)
	{

		$success = true;

		if ($lastyearresults == 0)
		{
			$start = "DATE_SUB( '$this->m_current_starting_month_date' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$this->m_current_starting_month_date',INTERVAL 1 MONTH)";
		}
		else
		{
			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1], '01', $dateArr[0] - 1);
			$newdate = date("Y-m-d 00:00:00", $temp);

			//$start = "'$newdate'";
			//$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";

			$start = "DATE_SUB( '$newdate' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";
		}

		$query = "update dashboard_report_cache_main, ( SELECT $this->m_cache_record as dashboard_report_cache_id, store_id,SUM(`store_expenses`.`total_cost`) as discounts,MONTH(`store_expenses`.`entry_date`) as month_var,YEAR(`store_expenses`.`entry_date`) as year_var " . " FROM	`store_expenses` where (expense_type = 'FUNDRAISER_DOLLARS' OR expense_type = 'ESCRIP_PAYMENTS') " . " and (`entry_date` >= $start and `entry_date` <= $end) " . " and  `store_expenses`.`is_deleted` = 0 " . " group by store_id, year_var, month_var ) as subquery1 set " . "  dashboard_report_cache_main.transaction_totals = dashboard_report_cache_main.transaction_totals - subquery1.discounts " . "  where dashboard_report_cache_main.dashboard_report_cache_log_id = $this->m_cache_record " . "  and dashboard_report_cache_main.store_id = subquery1.store_id and " . "  dashboard_report_cache_main.monthvalue = subquery1.month_var and dashboard_report_cache_main.yearvalue = subquery1.year_var";

		$obj = DAO_CFactory::create("dashboard_report_cache_main");
		$rslt = $obj->query($query);
		if ($rslt == 0)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: updateMainReportDataDiscountsA failed to work");

			return false;
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_main

	function updateMainReportDataProgramDiscounts($lastyearresults = 0)
	{

		$success = true;

		if ($lastyearresults == 0)
		{
			$start = "DATE_SUB( '$this->m_current_starting_month_date' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$this->m_current_starting_month_date',INTERVAL 1 MONTH)";
		}
		else
		{
			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1], '01', $dateArr[0] - 1);
			$newdate = date("Y-m-d 00:00:00", $temp);

			//$start = "'$newdate'";
			//$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";

			$start = "DATE_SUB( '$newdate' ,INTERVAL 3 MONTH)";
			$end = "DATE_ADD('$newdate',INTERVAL 1 MONTH)";
		}

		$query = "update dashboard_report_cache_main, " . " ( SELECT {$this->m_cache_record} as dashboard_report_cache_id, `session`.store_id,  sum(`store_credit`.`amount`) as discounts, " . "MONTH(`session`.`session_start`) as month_var,YEAR(`session`.`session_start`) as year_var " . " FROM `session` Inner Join `booking` ON `session`.`id` = `booking`.`session_id` " . "Inner Join `orders` ON `booking`.`order_id` = `orders`.`id` " . "Inner Join `payment` ON `orders`.`id` = `payment`.`order_id` " . "Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id` " . "Where payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 " . "AND session.is_deleted = 0 and session_publish_state != 'SAVED'  and  ( session.session_start >= $start " . "AND  session.session_start <= $end )" . "and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3) " . "group by `session`.store_id, year_var, month_var  ) as subquery1 set " . "  dashboard_report_cache_main.transaction_totals = dashboard_report_cache_main.transaction_totals - subquery1.discounts " . "  where dashboard_report_cache_main.dashboard_report_cache_log_id = $this->m_cache_record " . "  and dashboard_report_cache_main.store_id = subquery1.store_id and " . "  dashboard_report_cache_main.monthvalue = subquery1.month_var and dashboard_report_cache_main.yearvalue = subquery1.year_var";

		$obj = DAO_CFactory::create("dashboard_report_cache_main");
		$rslt = $obj->query($query);
		if ($rslt == 0)
		{
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: updateMainReportDataProgramDiscounts failed to work");

			return false;
		}

		return $success;
	}


	// called internally only
	// dashboard_report_cache_main
	function insertYearPlusData()
	{
		$total_records_added = 0;
		$success = true;
		$query = "insert into dashboard_report_cache_main (dashboard_report_cache_log_id, store_id,transaction_totals, distinct_users, total_sessions, customers_per_session, transaction_count, first_session_date, last_session_date, monthvalue, yearvalue, is_historical) " . " Select $this->m_cache_record as dashboard_report_cache_id," . 'session.store_id as store_id,' . 'SUM(subtotal_all_items) as transaction_totals,' . 'count(distinct booking.user_id) as distinct_users,' . 'count(distinct session.id) as total_sessions,' . 'count(orders.id) /count(distinct session.id) as customers_per_session,' . 'count(orders.id) as transaction_count,' . 'min(session_start) as first_session_date,' . 'max(session_start) as last_session_date,' . 'month(session_start) as monthvalue, year(session_start) as yearvalue, 1 as is_historical ' . 'From booking Inner Join  session  ON booking.session_id  = session.id ' . 'Inner Join orders ON  booking.order_id = orders.id ' . 'Where booking.status = "ACTIVE" ' . 'and booking.is_deleted = 0 AND session.is_deleted = 0 ' . 'and session_publish_state != "SAVED" ' . "and session.session_start >=  '%s' " . "and session.session_start <= DATE_ADD('%s',INTERVAL 1 MONTH) " . 'group by session.store_id, yearvalue, monthvalue ' . 'order by store_id, yearvalue, monthvalue';

		$datesToProcess = array();
		for ($i = 0; $i <= $this->m_number_months_back_to_capture; $i++)
		{

			$dateArr = explode('-', $this->m_current_starting_month_date);
			$temp = mktime(0, 0, 0, $dateArr[1] - $i, '01', $dateArr[0] - 1);
			$newdate = date("Y-m-d 00:00:00", $temp);
			$datesToProcess[] = $newdate;
		}

		foreach ($datesToProcess as $element)
		{
			$sql = $query;
			$sql = sprintf($sql, $element . ' 00:00:00', $element . ' 00:00:00');
			$obj = DAO_CFactory::create("booking");
			$rslt = $obj->query($sql);
			if ($rslt == 0)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "FAILURE: insertYearPlusData failed to work");

				return false;
			}
			$total_records_added += $rslt;
		}
		if ($total_records_added == 0)
		{
			$success = false;
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_instore

	function insertInStoreData($currentmonthdate)
	{
		$total_records_added = 0;
		$success = true;

		$dateArr = explode('-', $currentmonthdate);

		$intervalClause = "INTERVAL 1 MONTH";

		$monthStartTS = strtotime($currentmonthdate);
		$numMonthDays = date("t", $monthStartTS);

		$extendedIntervalClause = "INTERVAL " . ($numMonthDays + 7) . " DAY";

		$endMonthTime = mktime(0, 0, 0, $dateArr[1] + 1, 1, $dateArr[0]);
		$now = time();

		$dayNum = -1;

		if ($now > $monthStartTS && $now <= $endMonthTime)
		{
			// we must shorten the inverval if searching the current month
			$dayNum = date("d", $now);

			if ($dayNum > 1)
			{
				$dayNum--;
			}

			$intervalClause = "INTERVAL $dayNum DAY";
			$extendedIntervalClause = "INTERVAL " . ($dayNum + 7) . " DAY";
		}

		$query = "insert into dashboard_report_cache_instore (dashboard_report_cache_log_id, store_id, in_store_orders, menu_id, monthvar, yearvar, in_store_count , total_orders)
			select {$this->m_cache_record} as dashboard_report_cache_id, innerQ.store_id as StoreID_outer, (sum(innerQ.in_store_orders) / sum(innerQ.total_orders)) * 100 as percentage,
			innerQ.menu_id, {$dateArr[1]} as monthvar, {$dateArr[0]} as yearvar,sum(innerQ.in_store_orders), sum(innerQ.total_orders)
			from
			(Select  o.store_id as store_id, count(DISTINCT o.id) as total_orders,  max(s.menu_id) as menu_id,
			count( if (boo.order_id, if (boo.order_id <> o.id, 1 , null), null ) ) as in_store_orders From orders o
				Inner Join booking b ON  b.order_id = o.id
				Inner Join session s ON  b.session_id = s.id
				left JOIN (select o2.id as order_id, o2.user_id as user_id, o2.timestamp_created as order_date  from orders o2 where o2.timestamp_created >= '$currentmonthdate' and
									o2.timestamp_created < DATE_ADD('$currentmonthdate', $extendedIntervalClause) and o2.in_store_order = 1 ) as boo on boo.user_id = o.user_id
									and DATE(boo.order_date) >= DATE(s.session_start) and DATE(boo.order_date) < DATE_ADD(DATE(s.session_start), INTERVAL 7 DAY) and boo.order_id <> o.id
				Where  b.status = 'ACTIVE' and b.is_deleted = 0  AND s.session_start >= '$currentmonthdate' AND
									s.session_start <= DATE_ADD('$currentmonthdate', $intervalClause)and o.servings_total_count > 35 and o.is_TODD = 0
				GROUP BY o.store_id )
 			as innerQ
			group by dashboard_report_cache_id, StoreID_outer order by StoreID_outer";

		$obj = DAO_CFactory::create("session");
		$rslt = $obj->query($query);
		$total_records_added += $rslt;

		if ($total_records_added == 0)
		{
			$success = false;
			$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: insertInStoreData did not return any values");
		}

		return $success;
	}

	// called internally only
	// dashboard_report_cache_retention

	function insertRetentionData($dashboard_report_retention_id, $starting)
	{

		$success = true;

		$obj = DAO_CFactory::create("dashboard_report_retention");
		$obj->id = $dashboard_report_retention_id;
		$obj->is_active = 1;
		$rslt = $obj->find(true);
		$visitcount = null;

		if ($rslt > 0)
		{
			$dateArr = explode('-', $starting);
			$startingmonth = $dateArr[1];
			$startingyear = $dateArr[0];

			$monthsBack = $obj->retention_span_month;
			$visitcount = $obj->retention_visits;
			$dateArr = explode('-', $starting);

			$temp = mktime(0, 0, 0, $dateArr[1] - ($monthsBack - 1), '01', $dateArr[0]);
			$startdate = date("Y-m-d 00:00:00", $temp);
			$midstartdate = date("Y-m-d", $temp);

			/*  $sql = 'insert into dashboard_report_cache_retention (dashboard_report_cache_log_id,store_id ,first_session,last_session, retention_visit,  ' .
			  'monthvar, yearvar, dashboard_report_retention_id ) ' .
			  "select $this->m_cache_record as dashboard_report_cache_id, visit_table.store_id as store_id , visit_table.firstsession, visit_table.lastsession, count(visit_table.user_id) as countvar, $startingmonth as monthvar, $startingyear as yearvar  ,$dashboard_report_retention_id as dashboard_report_retention_id from " .
			  '( Select orders.user_id, ' .
			  'session.store_id as store_id,count(distinct session.menu_id) as visit_count,count(orders.id) as transactions_completed,min( session_start ) as firstsession, max( session_start ) as lastsession ' .
			  'From booking Inner Join orders ON booking.order_id = orders.id Inner Join session ON booking.session_id = session.id Inner Join `user` ON booking.user_id = `user`.id ' .
			  'Inner Join ( ' .
			  'Select distinct booking.user_id as first_user_id ' .
			  'From booking Inner Join orders ON booking.order_id = orders.id Inner Join session ON booking.session_id = session.id Inner Join `user` ON booking.user_id = `user`.id ' .
			  'where (booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and user_type = "CUSTOMER" ) ' .
			  'and session.session_start >= ' .
			  " '$startdate' and session.session_start < DATE_ADD('$startdate',INTERVAL 1 MONTH)" .
			  ' ) as first_months_users ON orders.user_id = first_months_users.first_user_id ' .
			  ' where (booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and user_type = "CUSTOMER" )' .
			  " and session.session_start >= '$startdate'  and session.session_start < DATE_ADD('$startdate',INTERVAL 3 MONTH)" .
			  ' group by session.store_id, orders.user_id ' .
			  " having visit_count = $visitcount " .
			  ' ) as visit_table  ' .
			  ' group by store_id ' ;
*/

			$sql = 'insert into dashboard_report_cache_retention (dashboard_report_cache_log_id,store_id ,first_session,last_session, retention_visit,  monthvar, yearvar, dashboard_report_retention_id )' . " select $this->m_cache_record as dashboard_report_cache_id,  sq2.store_id, null, null, count(sq2.user_id), $startingmonth as monthvar, $startingyear as yearvar  ,$dashboard_report_retention_id as dashboard_report_retention_id from (" . ' select count(distinct sq1.menu_id) as menucounts, sq1.user_id, sq1.store_id from' . ' (SELECT' . ' orders.user_id,session.store_id,menu_id' . ' FROM `booking`' . ' Inner Join `orders` ON `booking`.`order_id` = `orders`.`id`' . ' Inner Join `session` ON `booking`.`session_id` = `session`.`id`' . ' WHERE `booking`.`status` =  "ACTIVE" and `booking`.`is_deleted` =  0 AND session.is_deleted = 0 and session_publish_state != "SAVED" ' . " and session.menu_id in (select id from menu where is_deleted = 0 and menu_start >= '$startdate' and menu_start <= DATE_ADD('$startdate',INTERVAL 2 MONTH) )" . ' group by orders.user_id, menu_id) as sq1' . ' group by sq1.store_id, sq1.user_id' . ' having menucounts = 3 ) as sq2 ' . ' group by sq2.store_id';

			$obj = DAO_CFactory::create("booking");
			$rslt = $obj->query($sql);
			if ($rslt > 0)
			{
				/*		$sql = 'update dashboard_report_cache_retention , ( '.
						'Select count(distinct booking.user_id) as retention_count, session.store_id, month(session.session_start ) as monthvar,  ' .
						'year(session.session_start ) as yearvar  From booking Inner Join orders ON booking.order_id = orders.id Inner Join session ON booking.session_id = session.id Inner Join `user` ' .
						'ON booking.user_id = `user`.id  where (booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and user_type = "CUSTOMER" )    ' .
						"and session.session_start >= '$startdate' and session.session_start < DATE_ADD('$startdate',INTERVAL 1 MONTH)  group by session.store_id, yearvar, monthvar  " .
						') as s1 ' .
						"set dashboard_report_cache_retention.anchor_month_visit = s1.retention_count " .
						"where dashboard_report_cache_retention.store_id = s1.store_id and dashboard_report_cache_retention.monthvar = '$startingmonth' and dashboard_report_cache_retention.yearvar = $startingyear";
*/

				// this has to be re-written replication

				$sql = 'update dashboard_report_cache_retention , ( ' . 'SELECT ' . " count( distinct   booking.user_id) as retention_count,session.store_id " . ' FROM `booking`' . ' Inner Join `orders` ON `booking`.`order_id` = `orders`.`id`' . ' Inner Join `session` ON `booking`.`session_id` = `session`.`id`' . ' WHERE `booking`.`status` =  "ACTIVE" and `booking`.`is_deleted` =  0 AND session.is_deleted = 0 and session_publish_state != "SAVED"' . " and session.menu_id = (select id from menu where is_deleted = 0 and menu_start = '$midstartdate')" . ' group by session.store_id ) as s1 ' . "set dashboard_report_cache_retention.anchor_month_visit = s1.retention_count " . "where dashboard_report_cache_retention.dashboard_report_cache_log_id = $this->m_cache_record and dashboard_report_cache_retention.store_id = s1.store_id and dashboard_report_cache_retention.monthvar = '$startingmonth' and dashboard_report_cache_retention.yearvar = $startingyear";

				$obj = DAO_CFactory::create("dashboard_report_cache_retention");
				$rslt = $obj->query($sql);
				if ($rslt == 0)
				{
					$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING 1556: insertRetentionData did not return any values");

					$success = false;
				}

				$sql = "update `dashboard_report_cache_retention` set `retention_percentage` = `retention_visit`/`anchor_month_visit`";
				$rslt = $obj->query($sql);
				if ($rslt == 0)
				{
					$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING 1565: insertRetentionData update did not return any values");

					$success = false;
				}
			}
			else
			{
				$success = false;
			}
		}
		else
		{
			$success = false;
		}

		return $success;
	}


	//called internally only
	//dashboard_report_cache_retention, dashboard_report_cache_instore, dashboard_report_cache_main
	function updateMonthlyPercentagesRetention($startingyear, $startingmonth)
	{

		$prevyear = $startingyear;
		$prevmonth = $startingmonth;

		for ($i = 0; $i < $this->m_number_months_back_to_capture; $i++)
		{

			$prevmonth--;
			if ($prevmonth == 0)
			{
				$prevmonth = 12;
				$prevyear--;
			}

			// 	((`dashboard_report_cache_retention`.`retention_percentage`  - subquery. `retention_percentage`)/subquery. `retention_percentage`)*100 as monthly_change," .
			$query = "update `dashboard_report_cache_retention`," . " ( SELECT
								IF (`dashboard_report_cache_retention`.`retention_percentage` > subquery. `retention_percentage`, ((`dashboard_report_cache_retention`.`retention_percentage`-subquery. `retention_percentage`)/subquery.`retention_percentage`)*100, ((-ABS(`dashboard_report_cache_retention`.`retention_percentage`-subquery. `retention_percentage`))/subquery. `retention_percentage`)*100) as monthly_change, " . " `dashboard_report_cache_retention`.`retention_percentage` as currentRetention," . " subquery. `retention_percentage` as prevretention," . " `dashboard_report_cache_retention`.store_id" . " FROM" . " `dashboard_report_cache_retention`" . " Inner JOIN (" . " SELECT" . " `dashboard_report_cache_retention`.`retention_percentage`, store_id" . " FROM" . " `dashboard_report_cache_retention`" . " where `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvar` = $prevmonth and yearvar = $prevyear" . " ) as subquery ON subquery.store_id = `dashboard_report_cache_retention`.store_id" . " where   `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvar` = $startingmonth and yearvar = $startingyear" . " ) as subquery2" . " 	set percent_change = subquery2.monthly_change" . " where `dashboard_report_cache_retention`.`dashboard_report_cache_log_id` = $this->m_cache_record and" . " `dashboard_report_cache_retention`.`monthvar` = $startingmonth and `dashboard_report_cache_retention`.yearvar = $startingyear" . " and `dashboard_report_cache_retention`.store_id = subquery2.store_id";

			$obj = DAO_CFactory::create("dashboard_report_cache_retention");
			$rslt = $obj->query($query);

			if ($rslt == 0)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: dashboard_report_cache_retention update did not return any values");

				$success = false;
			}

			// ((`dashboard_report_cache_instore`.`in_store_orders`  - subquery. `in_store_orders`)/subquery. `in_store_orders`)*100 as monthly_change,

			$query = "update `dashboard_report_cache_instore`, ( SELECT IF (`dashboard_report_cache_instore`.`in_store_orders` > subquery. `in_store_orders`, ((`dashboard_report_cache_instore`.`in_store_orders`-subquery. `in_store_orders`)/subquery. `in_store_orders`)*100, ((-ABS(`dashboard_report_cache_instore`.`in_store_orders`-subquery. `in_store_orders`))/subquery. `in_store_orders`)*100) as monthly_change," . " `dashboard_report_cache_instore`.store_id " . " FROM `dashboard_report_cache_instore` Inner JOIN ( SELECT `dashboard_report_cache_instore`.`in_store_orders`, store_id FROM `dashboard_report_cache_instore` " . " where `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvar` = '$prevmonth' and yearvar = $prevyear ) as subquery ON subquery.store_id = `dashboard_report_cache_instore`.store_id where   `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvar` ='$startingmonth' and yearvar = $startingyear )" . " as subquery2 	set percent_change = subquery2.monthly_change where `dashboard_report_cache_instore`.`dashboard_report_cache_log_id` = $this->m_cache_record  and " . " `dashboard_report_cache_instore`.`monthvar` = '$startingmonth' and `dashboard_report_cache_instore`.yearvar = $startingyear and `dashboard_report_cache_instore`.store_id = subquery2.store_id";

			$rslt = $obj->query($query);

			if ($rslt == 0)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: dashboard_report_cache_instore update did not return any values");

				$success = false;
			}

			$query = "update `dashboard_report_cache_main`, ( SELECT IF (`dashboard_report_cache_main`.`transaction_totals` > subquery. `transaction_totals`, ((`dashboard_report_cache_main`.`transaction_totals`-subquery. `transaction_totals`)/subquery. `transaction_totals`)*100, ((-ABS(`dashboard_report_cache_main`.`transaction_totals`-subquery. `transaction_totals`))/subquery. `transaction_totals`)*100) as monthly_change, " . " `dashboard_report_cache_main`.store_id " . " FROM `dashboard_report_cache_main` Inner JOIN ( SELECT `dashboard_report_cache_main`.`transaction_totals`, store_id FROM `dashboard_report_cache_main` " . " where `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` = $prevmonth and yearvalue = $prevyear ) as subquery ON subquery.store_id = `dashboard_report_cache_main`.store_id where   `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` =$startingmonth and yearvalue = $startingyear )" . "  as subquery2 	set percent_change_transactions = subquery2.monthly_change where `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $this->m_cache_record and " . " `dashboard_report_cache_main`.`monthvalue` = $startingmonth and `dashboard_report_cache_main`.yearvalue = $startingyear and `dashboard_report_cache_main`.store_id = subquery2.store_id ";

			$rslt = $obj->query($query);

			if ($rslt == 0)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: dashboard_report_cache_main update did not return any values");

				$success = false;
			}

			//((`dashboard_report_cache_main`.`customers_per_session`  - subquery. `customers_per_session`)/subquery. `customers_per_session`)*100 as monthly_change,"
			$query = "update `dashboard_report_cache_main`, ( SELECT IF (`dashboard_report_cache_main`.`customers_per_session` > subquery.`customers_per_session`, ((`dashboard_report_cache_main`.`customers_per_session`-subquery. `customers_per_session`)/subquery.`customers_per_session`)*100, ((-ABS(`dashboard_report_cache_main`.`customers_per_session`-subquery.`customers_per_session`))/subquery. `customers_per_session`)*100) as monthly_change," . " `dashboard_report_cache_main`.store_id" . " FROM `dashboard_report_cache_main` Inner JOIN ( SELECT `dashboard_report_cache_main`.`customers_per_session`, store_id FROM `dashboard_report_cache_main`" . " where `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` = $prevmonth and yearvalue = $prevyear ) as subquery ON subquery.store_id = `dashboard_report_cache_main`.store_id where   `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` =$startingmonth and yearvalue = $startingyear )" . "  as subquery2 	set percent_change_session_avg = subquery2.monthly_change where `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $this->m_cache_record and" . " `dashboard_report_cache_main`.`monthvalue` = $startingmonth and `dashboard_report_cache_main`.yearvalue = $startingyear and `dashboard_report_cache_main`.store_id = subquery2.store_id ";

			$rslt = $obj->query($query);

			if ($rslt == 0)
			{
				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: dashboard_report_cache_main 002 update did not return any values");

				$success = false;
			}

			$query = "update `dashboard_report_cache_main`, ( SELECT IF (`dashboard_report_cache_main`.`distinct_users` > subquery. `distinct_users`, ((`dashboard_report_cache_main`.`distinct_users`-subquery. `distinct_users`)/subquery. `distinct_users`)*100, ((-ABS(`dashboard_report_cache_main`.`distinct_users`-subquery. `distinct_users`))/subquery. `distinct_users`)*100) as monthly_change," . " `dashboard_report_cache_main`.store_id" . " FROM `dashboard_report_cache_main` Inner JOIN ( SELECT `dashboard_report_cache_main`.`distinct_users`, store_id FROM `dashboard_report_cache_main`" . " where `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` = $prevmonth and yearvalue = $prevyear ) as subquery ON subquery.store_id = `dashboard_report_cache_main`.store_id where   `dashboard_report_cache_log_id` = $this->m_cache_record and `monthvalue` =$startingmonth and yearvalue = $startingyear )" . "  as subquery2 	set percent_change_distinct = subquery2.monthly_change where `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $this->m_cache_record and" . " `dashboard_report_cache_main`.`monthvalue` = $startingmonth and `dashboard_report_cache_main`.yearvalue = $startingyear and `dashboard_report_cache_main`.store_id = subquery2.store_id ";

			CLog::RecordNew(CLog::DEBUG, $query);
			$rslt = $obj->query($query);

			if ($rslt == 0)
			{


				CLog::RecordNew(CLog::DEBUG, $rslt->_lastError->userinfo);

				$this->addError(CDashboardReport::KBADCACHERECORDINSERT, "WARNING: dashboard_report_cache_main 003 update did not return any values");

				$success = false;
			}

			$startingyear = $prevyear;
			$startingmonth = $prevmonth;
		}
	}

}

?>