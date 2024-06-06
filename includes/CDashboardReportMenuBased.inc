<?php

require_once('CDreamReport.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/CDashboardReport.inc');

class CDashboardMenuBased
{

	static function updateGrowthScoreCard($store_id, $curMenuID, $curMenuStart, $cutoffTime, $calcLastMonth = true)
	{

		// current Month
		$monthObj = new DAO();

		$monthObj->query("select iq.*, dm.total_agr from (
            select m.id as menu_id, m.menu_name, count(distinct b.user_id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list, m.menu_start from session s
            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
            join orders o on o.id = b.order_id and o.servings_total_count > 35
            join menu m on m.id = s.menu_id
            where s.menu_id = $curMenuID 
            and s.store_id = $store_id and s.session_start < '$cutoffTime'
            group by s.menu_id
            order by s.session_start) as iq
            join dashboard_metrics_agr_by_menu dm on dm.date = iq.menu_start and dm.store_id = $store_id and dm.is_deleted = 0
            order by iq.menu_id");

		if ($monthObj->fetch())
		{

			$last_month_menu_id = $monthObj->menu_id - 1;
			$lastMonth = new DAO();
			$lastMonth->query("select distinct b.user_id from session s
                join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$monthObj->guest_list})
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

			$guests_last_month = $lastMonth->N;

			$next_month_menu_id = $monthObj->menu_id + 1;
			$nextMonth = new DAO();
			$nextMonth->query("select distinct b.user_id from session s
                join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$monthObj->guest_list})
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

			$guests_next_month = $nextMonth->N;

			$growthValue = $guests_next_month - $guests_last_month;
		}
		else
		{
			$growthValue = 0;
		}

		$Metric = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$Metric->date = $curMenuStart;
		$Metric->store_id = $store_id;
		if ($Metric->find(true))
		{
			$MetricOld = clone($Metric);
			$Metric->growth_scorecard_value = $growthValue;

			$Metric->update($MetricOld);
		}
		else
		{
			$Metric->growth_scorecard_value = $growthValue;
			$Metric->insert();
		}

		//lastMonth
		if ($calcLastMonth)
		{
			$LastMonthObj = new DAO();
			$lastMonthMenuID = $curMenuID - 1;

			$LastMonthObj->query("select iq.*, dm.total_agr from (
                select m.id as menu_id, m.menu_name, count(distinct b.user_id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list, m.menu_start from session s
                join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                join menu m on m.id = s.menu_id
                where s.menu_id = $lastMonthMenuID
                and s.store_id = $store_id and s.session_start < '$cutoffTime'
                group by s.menu_id
                order by s.session_start) as iq
                join dashboard_metrics_agr_by_menu dm on dm.date = iq.menu_start and dm.store_id = $store_id and dm.is_deleted = 0
                order by iq.menu_id");

			if ($LastMonthObj->fetch())
			{

				$last_month_menu_id = $LastMonthObj->menu_id - 1;
				$lastMonth = new DAO();
				$lastMonth->query("select distinct b.user_id from session s
                    join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$LastMonthObj->guest_list})
                    join orders o on o.id = b.order_id and o.servings_total_count > 35
                    where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

				$guests_last_month = $lastMonth->N;

				$next_month_menu_id = $LastMonthObj->menu_id + 1;
				$nextMonth = new DAO();
				$nextMonth->query("select distinct b.user_id from session s
                    join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$LastMonthObj->guest_list})
                    join orders o on o.id = b.order_id and o.servings_total_count > 35
                    where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

				$guests_next_month = $nextMonth->N;

				$growthValue = $guests_next_month - $guests_last_month;
			}
			else
			{
				$growthValue = 0;
			}

			$lastMenu = new DAO();
			$lastMenu->query("select menu_start from menu where id = $lastMonthMenuID");
			$lastMenu->fetch();

			$LMMetric = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
			$LMMetric->date = $lastMenu->menu_start;
			$LMMetric->store_id = $store_id;
			if ($LMMetric->find(true))
			{
				$MetricOld = clone($LMMetric);
				$LMMetric->growth_scorecard_value = $growthValue;

				$LMMetric->update($MetricOld);
			}
			else
			{
				$LMMetric->growth_scorecard_value = $growthValue;
				$LMMetric->insert();
			}
		}
	}

	static function getMetricsSnapShot($store_id, $date)
	{

		// TODO: test non-existent month of data

		$updateRequired = self::testForUpdateRequired($store_id);

		$dateParts = explode("-", $date);
		$year = $dateParts[0];
		$month = $dateParts[1];
		$currentMonth = $year . "-" . $month . "-" . "01";

		// current month AGR
		$currentAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
		$currentAGRMetrics->store_id = $store_id;
		$currentAGRMetrics->date = $currentMonth;
		$currentAGRMetrics->find(true);

		$currentGuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$currentGuestMetrics->store_id = $store_id;
		$currentGuestMetrics->date = $currentMonth;
		$currentGuestMetrics->find(true);

		$guestMetricsArray = $currentGuestMetrics->toArray();
		list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $currentMonth);
		self::addToDateGuestCounts($guestMetricsArray, $store_id, $menu_start_date, $interval);
		$curMonthLastYear = mktime(0, 0, 0, $month, 1, $year - 1);

		$thisMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
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

	static function getGrossSales($store, $startDate, $interval)
	{
		$foundentry = false;

		$dateParts = explode("-", $startDate);
		$day = $dateParts[2];
		$month = $dateParts[1];
		$year = $dateParts[0];
		$duration = "$interval DAY";

		$rows = array();

		CDreamReport::getOrderInfoByMonth($store, $day, $month, $year, $duration, $rows, 1);
		$rows['membership_fees'] = CDreamReport::getMembershipFeeRevenue($store, $day, $month, $year, $duration);
		$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store);
		$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store);

		if (isset($rows['grand_total']) && $rows['grand_total'] > 0)
		{
			$foundentry = true;

			$rows['grand_total'] += $rows['membership_fees'];
			$rows['total_sales'] += $rows['membership_fees'];

			$rows['grand_total'] += $DoorDashRevenue;
			$rows['total_sales'] += $DoorDashRevenue;

			if (empty($rows['fundraising_total']))
			{
				$rows['fundraising_total'] = 0;
			}

			if (empty($rows['ltd_menu_item_value']))
			{
				$rows['ltd_menu_item_value'] = 0;
			}

			if (empty($rows['subtotal_delivery_fee']))
			{
				$rows['subtotal_delivery_fee'] = 0;
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
			CDreamReport::calculateFees($rows, $store, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $rows['fundraising_total'], $rows['ltd_menu_item_value'], $rows['subtotal_delivery_fee'], $rows['delivery_tip'], $rows['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $storeobj->grand_opening_date, $month, $year);
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

	static function addMonthStartRevenue($store, $anchorDate, &$AGRMetrics, $fieldName)
	{
		// get the first day of the menu
		$menuInfo = CMenu::getMenuByAnchorDate($anchorDate);
		$menuStart = $menuInfo['global_menu_start_date'];

		if (strtotime($menuStart) > time())
		{
			$AGRMetrics[$fieldName] = "";

			return;
		}

		$snapShots = new DAO();
		$snapShots->query("select agr_menu_month from dashboard_metrics_agr_snapshots where store_id = $store and month = '$anchorDate' and date = '$menuStart' and is_deleted = 0 limit 1");
		if ($snapShots->fetch())
		{
			$revenue = (!empty($snapShots->agr_menu_month) ? $snapShots->agr_menu_month : 0);
			$AGRMetrics[$fieldName] = $revenue;
		}
		else
		{
			$AGRMetrics[$fieldName] = "";
		}
	}

	static function getOccupiedSessionCountForMonth($startDate, $interval, $type = 'DREAM_TASTE', $filter = 'all_stores', $storeArr = false)
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
			$joinClause = " join store on store.id = s.store_id and store.is_corporate_owned = 0 and active = 1 ";
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store on store.id = s.store_id and store.id in (" . implode(",", $storeArr) . ") ";
		}
		else
		{
			$joinClause = " join store on store.id = s.store_id and store.active = 1 ";
		}

		$Sessions = DAO_CFactory::create('session');

		$Sessions->query("select count(DISTINCT s.id) as occupied_count from session s
		join booking b on b.session_id = s.id and b.`status` = 'ACTIVE' and b.is_deleted = 0
		$joinClause
		where s.session_start>= '$startDate' and s.session_start < DATE_ADD('$startDate', INTERVAL $interval DAY)
		and s.session_type = '$type' and s.is_deleted = 0");

		$Sessions->fetch();

		if (empty($Sessions->occupied_count))
		{
			$Sessions->occupied_count = 0;
		}

		return $Sessions->occupied_count;
	}

	static function updateGuestMetrics($store_id, $thisMonth, $startDate, $interval)
	{
		$isDistributionCenter = false;
		$storeTypeGetter = new DAO();
		$storeTypeGetter->query("select id from store where id = $store_id and store_type = 'DISTRIBUTION_CENTER'");
		if ($storeTypeGetter->N > 0)
		{
			$isDistributionCenter = true;
		}

		if (is_null($startDate) || is_null($interval))
		{
			return;
		}

		$isCurrentMonth = false;

		$nowTS = time();
		$startOfCurrentMonthTS = strtotime($startDate);
		$endOfCurrentMonthTS = $startOfCurrentMonthTS + ($interval * 86400);

		if ($nowTS >= $startOfCurrentMonthTS && $nowTS < $endOfCurrentMonthTS)
		{
			$isCurrentMonth = true;
		}

		$Metric = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$Metric->date = $thisMonth;
		$Metric->store_id = $store_id;

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->menu_start = $thisMonth;
		if (!$MenuObj->find(true))
		{
			//CLog::RecordIntense("Menu not found in updateGuestMetrics", "ryan.snook@dreamdinners.com");
		}

		$Metrics_Exist = false;
		if ($Metric->find(true))
		{
			$Metrics_Exist = true;
		}

		// get guest_counts

		$Metric->guest_count_total = 0;

		$Metric->guest_count_existing_regular = 0;
		$Metric->guest_count_existing_additional = 0;
		$Metric->guest_count_existing_taste = 0;
		$Metric->guest_count_existing_intro = 0;
		$Metric->guest_count_existing_delivered = 0;
		$Metric->guest_count_existing_fundraiser = 0;

		$Metric->guest_count_reacquired_regular = 0;
		$Metric->guest_count_reacquired_additional = 0;
		$Metric->guest_count_reacquired_intro = 0;
		$Metric->guest_count_reacquired_taste = 0;
		$Metric->guest_count_reacquired_fundraiser = 0;
		$Metric->guest_count_reacquired_delivered = 0;

		$Metric->guest_count_new_regular = 0;
		$Metric->guest_count_new_additional = 0;
		$Metric->guest_count_new_taste = 0;
		$Metric->guest_count_new_intro = 0;
		$Metric->guest_count_new_fundraiser = 0;
		$Metric->guest_count_new_delivered = 0;

		$digest = DAO_CFactory::create('orders_digest');
		//
		//		$sql = "select od.user_state, if(od.session_type='DELIVERED', 'DELIVERED', od.order_type) as order_type, count( distinct od.user_id) as total
		//				from orders_digest od
		//				where od.is_deleted = 0 and od.store_id = $store_id
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		//				group by od.user_state, od.order_type";

		$sql = "SELECT
					od.user_state,
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS order_type,
					count( DISTINCT od.user_id ) AS total 
				FROM
					orders_digest od 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.user_state,
					od.order_type
				UNION
				SELECT
					od.user_state,
				  'ADDITIONAL' AS order_type,
					count( DISTINCT od.user_id ) AS total 
				FROM
					orders_digest od 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.user_state,
					od.order_type";
		$digest->query($sql);

		while ($digest->fetch())
		{
			if ($digest->user_state == 'NEW')
			{
				if ($digest->order_type == 'REGULAR')
				{
					$Metric->guest_count_new_regular = $digest->total;
				}
				else if ($digest->order_type == 'ADDITIONAL')
				{
					$Metric->guest_count_new_additional = $digest->total;
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
				else if ($digest->order_type == 'DELIVERED')
				{
					$Metric->guest_count_new_delivered = $digest->total;
				}
			}
			else if ($digest->user_state == 'REACQUIRED')
			{
				if ($digest->order_type == 'REGULAR')
				{
					$Metric->guest_count_reacquired_regular = $digest->total;
				}
				else if ($digest->order_type == 'ADDITIONAL')
				{
					$Metric->guest_count_reacquired_additional = $digest->total;
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
				else if ($digest->order_type == 'DELIVERED')
				{
					$Metric->guest_count_reacquired_delivered = $digest->total;
				}
			}
			else if ($digest->user_state == 'EXISTING')
			{
				if ($digest->order_type == 'TASTE')
				{
					$Metric->guest_count_existing_taste = $digest->total;
				}
				else if ($digest->order_type == 'ADDITIONAL')
				{
					$Metric->guest_count_existing_additional = $digest->total;
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
				else if ($digest->order_type == 'DELIVERED')
				{
					$Metric->guest_count_existing_delivered = $digest->total;
				}
			}
		}

		$digestRSVP = new DAO();
		$digestRSVP->query("select sr.* from session_rsvp sr
							inner join session s on s.id = sr.session_id and s.store_id = $store_id and s.menu_id = {$MenuObj->id} and s.is_deleted = 0
							left join booking b on b.user_id = sr.user_id and b.session_id = sr.session_id and b.status = 'ACTIVE' and b.is_deleted = 0
							where sr.is_deleted = 0");

		$Metric->guest_count_rsvp = 0;
		$Metric->guest_count_rsvp_upgraded = 0;

		while ($digestRSVP->fetch())
		{
			$Metric->guest_count_rsvp = $digestRSVP->N;

			// if the row is deleted but has upgrade_booking_id it was converted to an upgrade
			if (!empty($digestRSVP->upgrade_booking_id))
			{
				$Metric->guest_count_rsvp_upgraded++;
			}
		}

		$digest105 = DAO_CFactory::create('orders_digest');
		$digest105->query("select count( distinct od.user_id) as total
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

		$digest105->fetch();

		$Metric->guest_count_total = $digest105->total;

		// Unique New and Reacquired Guests
		$digestUniqueNew = DAO_CFactory::create('orders_digest');
		//		$q = "select count( distinct od.user_id) as total_unique_new
		//				from orders_digest od
		//				where od.is_deleted = 0 and od.store_id = $store_id and od.user_state = 'NEW'
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)";

		$q = "select sum(iq.total) 	as total_unique_new from (SELECT
					od.user_state,
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS order_type,
					count( DISTINCT od.user_id ) AS total 
				FROM
					orders_digest od 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
					and od.user_state = 'NEW'
				GROUP BY
					od.user_state,
					od.order_type ) as iq";

		$digestUniqueNew->query($q);

		$digestUniqueNew->fetch();

		$Metric->unique_new_guest_count = $digestUniqueNew->total_unique_new;

		$digestUniqueReac = DAO_CFactory::create('orders_digest');
		$digestUniqueReac->query("select count( distinct od.user_id) as total_unique_reac
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id and od.user_state = 'REACQUIRED'
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

		$digestUniqueReac->fetch();

		$Metric->unique_reacquired_guest_count = $digestUniqueReac->total_unique_reac;

		// get order_counts
		$digest11 = DAO_CFactory::create('orders_digest');
		$sql = "select count( iq.id) as total,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'REGULAR', 1, null)) as count_reaq_regular,
       			count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'ADDITIONAL', 1, null)) as count_reaq_additional,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'TASTE', 1, null)) as count_reaq_taste,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'INTRO', 1, null)) as count_reaq_intro,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'FUNDRAISER', 1, null)) as count_reaq_fundraiser,
				count(if(iq.user_state = 'REACQUIRED' and iq.order_type = 'DELIVERED', 1, null)) as count_reaq_delivered,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'TASTE', 1, null)) as count_new_taste,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'INTRO', 1, null))  as count_new_intro,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'REGULAR', 1, null)) as count_new_regular,
       			count(if(iq.user_state = 'NEW' and iq.order_type = 'ADDITIONAL', 1, null)) as count_new_additional,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'FUNDRAISER', 1, null)) as count_new_fundraiser,
				count(if(iq.user_state = 'NEW' and iq.order_type = 'DELIVERED', 1, null)) as count_new_delivered,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'TASTE', 1, null)) as count_existing_taste,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'REGULAR', 1, null)) as count_existing_regular,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'ADDITIONAL', 1, null)) as count_existing_additional,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'INTRO', 1, null)) as count_existing_intro,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'FUNDRAISER', 1, null)) as count_existing_fundraiser,
				count(if(iq.user_state = 'EXISTING' and iq.order_type = 'DELIVERED', 1, null)) as count_existing_delivered
		    from
				(SELECT
						od.id,
						od.user_state,
					IF
						( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS order_type 
					FROM
						orders_digest od 
					WHERE
						od.is_deleted = 0 
						AND od.qualifying_order_id IS NULL
						AND od.store_id = $store_id 
						AND od.session_time >= '$startDate' 
						AND od.session_time < DATE_ADD('$startDate',INTERVAL $interval DAY)
					UNION
					SELECT
						od.id,
						od.user_state,
					  'ADDITIONAL' AS order_type 
					FROM
						orders_digest od 
					WHERE
						od.is_deleted = 0 
						AND od.qualifying_order_id IS NOT NULL
						AND od.store_id = $store_id 
						AND od.session_time >= '$startDate' 
						AND od.session_time < DATE_ADD('$startDate',INTERVAL $interval DAY)) as iq";

		$digest11->query($sql);

		$digest11->fetch();

		$Metric->orders_count_regular_existing_guests = $digest11->count_existing_regular;
		$Metric->orders_count_regular_new_guests = $digest11->count_new_regular;
		$Metric->orders_count_regular_reacquired_guests = $digest11->count_reaq_regular;
		$Metric->orders_count_additional_existing_guests = $digest11->count_existing_additional;
		$Metric->orders_count_additional_new_guests = $digest11->count_new_additional;
		$Metric->orders_count_additional_reacquired_guests = $digest11->count_reaq_additional;
		$Metric->orders_count_intro_new_guests = $digest11->count_new_intro;
		$Metric->orders_count_intro_reacquired_guests = $digest11->count_reaq_intro;
		$Metric->orders_count_intro_existing_guests = $digest11->count_existing_intro;
		$Metric->orders_count_taste_existing_guests = $digest11->count_existing_taste;
		$Metric->orders_count_taste_new_guests = $digest11->count_new_taste;
		$Metric->orders_count_taste_reacquired_guests = $digest11->count_reaq_taste;
		$Metric->orders_count_fundraiser_existing_guests = $digest11->count_existing_fundraiser;
		$Metric->orders_count_fundraiser_new_guests = $digest11->count_new_fundraiser;
		$Metric->orders_count_fundraiser_reacquired_guests = $digest11->count_reaq_fundraiser;
		$Metric->orders_count_delivered_existing_guests = $digest11->count_existing_delivered;
		$Metric->orders_count_delivered_new_guests = $digest11->count_new_delivered;
		$Metric->orders_count_delivered_reacquired_guests = $digest11->count_reaq_delivered;

		if (!$isDistributionCenter)
		{
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

			//			$sql = "select count(od.id), od.order_type as type, od.user_state, count(distinct od2.user_id) as in_store from orders_digest od
			//					left join orders_digest od2 on od.order_id = od2.in_store_trigger_order and od2.is_deleted = 0
			//					where od.is_deleted = 0 and od.store_id = $store_id
			//					and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
			//					$currentMonthClause
			//					GROUP BY od.order_type, od.user_state";

			$sql = "SELECT
					count( od.id ),
					od.order_type AS type,
					od.user_state,
					count( DISTINCT od2.user_id ) AS in_store 
				FROM
					orders_digest od
					LEFT JOIN orders_digest od2 ON od.order_id = od2.in_store_trigger_order 
					AND od2.is_deleted = 0 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) $currentMonthClause 
				GROUP BY
					od.order_type,
					od.user_state
				UNION
				SELECT
					count( od.id ),
					'ADDITIONAL' AS type,
					od.user_state,
					count( DISTINCT od2.user_id ) AS in_store 
				FROM
					orders_digest od
					LEFT JOIN orders_digest od2 ON od.order_id = od2.in_store_trigger_order 
					AND od2.is_deleted = 0 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) $currentMonthClause 
				GROUP BY
					od.order_type,
					od.user_state";
			$digest2->query($sql);

			$InStoreByUser = array(
				"existing_regular" => 0,
				"existing_taste" => 0,
				"existing_additional" => 0,
				"existing_intro" => 0,
				"existing_fundraiser" => 0,
				"reacquired_regular" => 0,
				"reacquired_additional" => 0,
				"reacquired_intro" => 0,
				"reacquired_taste" => 0,
				"reacquired_fundraiser" => 0,
				"new_regular" => 0,
				"new_additional" => 0,
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
					else if ($digest2->type == 'ADDITIONAL')
					{
						$InStoreByUser['new_additional'] = $digest2->in_store;
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
					else if ($digest2->type == 'ADDITIONAL')
					{
						$InStoreByUser['reacquired_additional'] = $digest2->in_store;
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
					else if ($digest2->type == 'ADDITIONAL')
					{
						$InStoreByUser['existing_additional'] = $digest2->in_store;
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
			$Metric->instore_signup_existing_additional = $InStoreByUser['existing_additional'];
			$Metric->instore_signup_existing_taste = $InStoreByUser['existing_taste'];
			$Metric->instore_signup_existing_intro = $InStoreByUser['existing_intro'];
			$Metric->instore_signup_existing_fundraiser = $InStoreByUser['existing_fundraiser'];
			$Metric->instore_signup_reacquired_regular = $InStoreByUser['reacquired_regular'];
			$Metric->instore_signup_reacquired_additional = $InStoreByUser['reacquired_additional'];
			$Metric->instore_signup_reacquired_taste = $InStoreByUser['reacquired_taste'];
			$Metric->instore_signup_reacquired_intro = $InStoreByUser['reacquired_intro'];
			$Metric->instore_signup_reacquired_fundraiser = $InStoreByUser['reacquired_fundraiser'];
			$Metric->instore_signup_new_regular = $InStoreByUser['new_regular'];
			$Metric->instore_signup_new_additional = $InStoreByUser['new_additional'];
			$Metric->instore_signup_new_intro = $InStoreByUser['new_intro'];
			$Metric->instore_signup_new_taste = $InStoreByUser['new_taste'];
			$Metric->instore_signup_new_fundraiser = $InStoreByUser['new_fundraiser'];
		}

		// Session type counts

		$Sessions = DAO_CFactory::create('session');

		$Sessions->query("select 
				count(if(s.session_type <> 'DELIVERED' or (s.session_type = 'DELIVERED' && delivered_supports_delivery > 0),s.id,null)) as total,
				count(s.id) as total2,
				count(if(s.session_type = 'STANDARD', 1, null)) as std_count,
				count(if(s.session_class = 'SPECIAL_EVENT' and (s.session_type_subtype NOT IN ('DELIVERY','WALK_IN','REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE') or isnull(s.session_type_subtype)), 1, null)) as mfy_count,
				count(if(s.session_class = 'SPECIAL_EVENT' and s.session_type_subtype IN ('REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE'), 1, null)) as community_count,
      			count(if(s.session_class = 'SPECIAL_EVENT' and s.session_type_subtype = 'WALK_IN', 1, null)) as walk_in_count,
				count(if(s.session_class = 'SPECIAL_EVENT' and s.session_type_subtype = 'DELIVERY', 1, null)) as delivery_count,
				count(if(s.session_class = 'TODD', 1, null)) as taste_count,
				count(if(s.session_class = 'FUNDRAISER', 1, null)) as fundraiser_count,
				count(if(s.session_type = 'DELIVERED' and delivered_supports_delivery > 0, 1, null)) as delivered_count
												from session s
				where s.is_deleted = 0 and s.store_id = $store_id
					and s.session_start >= '$startDate' and s.session_start < DATE_ADD('$startDate', INTERVAL $interval DAY)
				and s.session_publish_state <> 'SAVED'");

		$Sessions->fetch();

		$Metric->sessions_count_all = $Sessions->total;
		$Metric->sessions_count_regular = $Sessions->std_count;
		$Metric->sessions_count_mfy = $Sessions->mfy_count;
		$Metric->sessions_count_mfy_walk_in = $Sessions->walk_in_count;
		$Metric->sessions_count_mfy_community_pickup = $Sessions->community_count;
		$Metric->sessions_count_mfy_delivery = $Sessions->delivery_count;
		$Metric->sessions_count_taste = $Sessions->taste_count;
		$Metric->sessions_count_fundraiser = $Sessions->fundraiser_count;
		$Metric->sessions_count_delivered = $Sessions->delivered_count;

		// Orders per session type
		$digest4 = DAO_CFactory::create('orders_digest');
		$sql = "select count(od.id) AS total,
				count(if(od.session_type = 'TASTE', 1, null)) as taste_count,
				count(if(od.session_type = 'STANDARD', 1, null)) as std_count,		
       			count(if(od.session_type = 'MADE_FOR_YOU' and (s.session_type_subtype NOT IN ('DELIVERY','WALK_IN','REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE') or isnull(s.session_type_subtype)), 1, null)) as mfy_count,
       			count(if(od.session_type = 'MADE_FOR_YOU' and s.session_type_subtype IN ('REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE'), 1, null)) as community_count,
       			count(if(od.session_type = 'MADE_FOR_YOU' and s.session_type_subtype = 'WALK_IN', 1, null)) as walk_in_count,
       			count(if(od.session_type = 'MADE_FOR_YOU' and s.session_type_subtype = 'DELIVERY', 1, null)) as delivery_count,			
				count(if(od.session_type = 'FUNDRAISER', 1, null)) as fundraiser_count,
       			count(if(od.session_type = 'DELIVERED', 1, null)) as delivered_count
				from orders_digest od
				join booking b on b.order_id = od.order_id and b.status = 'ACTIVE' and b.is_deleted = 0
				join session s on s.id = b.session_id
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)";

		$digest4->query($sql);

		$digest4->fetch();

		$Metric->orders_count_all = $digest4->total;
		$Metric->orders_count_regular = $digest4->std_count;
		$Metric->orders_count_mfy = $digest4->mfy_count;
		$Metric->orders_count_mfy_delivery = $digest4->delivery_count;
		$Metric->orders_count_mfy_community_pickup = $digest4->community_count;
		$Metric->orders_count_mfy_walk_in = $digest4->walk_in_count;
		$Metric->orders_count_taste = $digest4->taste_count;
		$Metric->orders_count_fundraiser = $digest4->fundraiser_count;
		$Metric->orders_count_delivered = $digest4->delivered_count;

		// get avg servings

		$AvgServingsPerUser = array(
			"existing_regular" => 0,
			"existing_additional" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"existing_delivered" => 0,
			"reacquired_regular" => 0,
			"reacquired_additional" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"reacquired_delivered" => 0,
			"new_regular" => 0,
			"new_additional" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0,
			"new_delivered" => 0
		);

		$digest3 = DAO_CFactory::create('orders_digest');
//		$sql = "select od.user_state, if(od.session_type='DELIVERED', 'DELIVERED', od.order_type) as type, sum(o.servings_total_count) AS servings  from orders_digest od
//				join orders o on o.id = od.order_id
//				where od.is_deleted = 0 and od.store_id = $store_id
//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
//				GROUP BY od.user_state, od.order_type";

		$sql = "SELECT
					od.user_state,
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
					sum( o.servings_total_count ) AS servings 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.user_state,
					od.order_type
				UNION
				SELECT
					od.user_state,
				  'ADDITIONAL' AS type,
					sum( o.servings_total_count ) AS servings 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.user_state,
					od.order_type";

		$digest3->query($sql);

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
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AvgServingsPerUser['existing_additional'] = $digest3->servings;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AvgServingsPerUser['existing_delivered'] = $digest3->servings;
				}
			}
			else if ($digest3->user_state == 'REACQUIRED')
			{
				if ($digest3->type == 'REGULAR')
				{
					$totalRegularOrderServings += $digest3->servings;

					$AvgServingsPerUser['reacquired_regular'] = $digest3->servings;
				}
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AvgServingsPerUser['reacquired_additional'] = $digest3->servings;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AvgServingsPerUser['reacquired_delivered'] = $digest3->servings;
				}
			}
			else if ($digest3->user_state == 'NEW')
			{
				if ($digest3->type == 'REGULAR')
				{
					$totalRegularOrderServings += $digest3->servings;

					$AvgServingsPerUser['new_regular'] = $digest3->servings;
				}
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AvgServingsPerUser['new_additional'] = $digest3->servings;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AvgServingsPerUser['new_delivered'] = $digest3->servings;
				}
			}
		}

		$Metric->avg_servings_per_guest_all = (!empty($Metric->orders_count_all) ? $totalServings / $Metric->orders_count_all : 0);

		$regularGuestOrderCount = $Metric->orders_count_regular_existing_guests + $Metric->orders_count_additional_existing_guests + $Metric->orders_count_regular_reacquired_guests + $Metric->orders_count_regular_new_guests;

		$Metric->avg_servings_per_guest_regular = (!empty($regularGuestOrderCount) ? $totalRegularOrderServings / $regularGuestOrderCount : 0);

		$Metric->avg_servings_per_guest_existing_regular = (!empty($Metric->orders_count_regular_existing_guests) ? $AvgServingsPerUser['existing_regular'] / $Metric->orders_count_regular_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_additional = (!empty($Metric->orders_count_additional_existing_guests) ? $AvgServingsPerUser['existing_additional'] / $Metric->orders_count_additional_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_taste = (!empty($Metric->orders_count_taste_existing_guests) ? $AvgServingsPerUser['existing_taste'] / $Metric->orders_count_taste_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_intro = (!empty($Metric->orders_count_intro_existing_guests) ? $AvgServingsPerUser['existing_intro'] / $Metric->orders_count_intro_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_fundraiser = (!empty($Metric->orders_count_fundraiser_existing_guests) ? $AvgServingsPerUser['existing_fundraiser'] / $Metric->orders_count_fundraiser_existing_guests : 0);
		$Metric->avg_servings_per_guest_existing_delivered = (!empty($Metric->orders_count_delivered_existing_guests) ? $AvgServingsPerUser['existing_delivered'] / $Metric->orders_count_delivered_existing_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_regular = (!empty($Metric->orders_count_regular_reacquired_guests) ? $AvgServingsPerUser['reacquired_regular'] / $Metric->orders_count_regular_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_additional = (!empty($Metric->orders_count_additional_reacquired_guests) ? $AvgServingsPerUser['reacquired_additional'] / $Metric->orders_count_additional_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_taste = (!empty($Metric->orders_count_taste_reacquired_guests) ? $AvgServingsPerUser['reacquired_taste'] / $Metric->orders_count_taste_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_intro = (!empty($Metric->orders_count_intro_reacquired_guests) ? $AvgServingsPerUser['reacquired_intro'] / $Metric->orders_count_intro_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_fundraiser = (!empty($Metric->orders_count_fundraiser_reacquired_guests) ? $AvgServingsPerUser['reacquired_fundraiser'] / $Metric->orders_count_fundraiser_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_reacquired_delivered = (!empty($Metric->orders_count_delivered_reacquired_guests) ? $AvgServingsPerUser['reacquired_delivered'] / $Metric->orders_count_delivered_reacquired_guests : 0);
		$Metric->avg_servings_per_guest_new_regular = (!empty($Metric->orders_count_regular_new_guests) ? $AvgServingsPerUser['new_regular'] / $Metric->orders_count_regular_new_guests : 0);
		$Metric->avg_servings_per_guest_new_additional = (!empty($Metric->orders_count_additional_new_guests) ? $AvgServingsPerUser['new_additional'] / $Metric->orders_count_additional_new_guests : 0);
		$Metric->avg_servings_per_guest_new_taste = (!empty($Metric->orders_count_taste_new_guests) ? $AvgServingsPerUser['new_taste'] / $Metric->orders_count_taste_new_guests : 0);
		$Metric->avg_servings_per_guest_new_intro = (!empty($Metric->orders_count_intro_new_guests) ? $AvgServingsPerUser['new_intro'] / $Metric->orders_count_intro_new_guests : 0);
		$Metric->avg_servings_per_guest_new_fundraiser = (!empty($Metric->orders_count_fundraiser_new_guests) ? $AvgServingsPerUser['new_fundraiser'] / $Metric->orders_count_fundraiser_new_guests : 0);
		$Metric->avg_servings_per_guest_new_delivered = (!empty($Metric->orders_count_delivered_new_guests) ? $AvgServingsPerUser['new_delivered'] / $Metric->orders_count_delivered_new_guests : 0);

		$digest11 = DAO_CFactory::create('orders_digest');
		$digest11->query("select sum(o.servings_total_count) as total_servings, sum(o.menu_items_total_count) as total_items, COUNT(DISTINCT box_instance.id) AS total_boxes 
							from orders_digest od
							join orders o on o.id = od.order_id
							LEFT JOIN box_instance ON box_instance.order_id = od.order_id AND box_instance.is_complete = 1 AND box_instance.is_deleted = 0
							where  od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
								and od.store_id = $store_id and od.is_deleted = 0 ");

		$digest11->fetch();

		$Metric->total_servings_sold = $digest11->total_servings;
		$Metric->total_items_sold = $digest11->total_items;
		$Metric->total_boxes_sold = $digest11->total_boxes;

		if (!$isDistributionCenter)
		{

			// Converted Guests
			/*
			 * version 4
			 */

			// 1) Get count to-date count of new or reacquired guests
			$digest5 = DAO_CFactory::create('orders_digest');
			$digest5->query("select count(od.user_id) from orders_digest od
															where od.is_deleted = 0 and od.store_id = $store_id
																and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
															and od.session_time < now()
																and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')
																group by od.user_id");
			$totalNew = $digest5->N;

			// 2) Get the last day of a valid follow up
			$futureMenu = DAO_CFactory::create('menu');
			$futureMenu->id = $MenuObj->id + 1;
			$futureMenu->find(true);
			$followUpOrderCap = $futureMenu->global_menu_end_date;
			$followUpOrderCap = date("Y-m-d 00:00:00", strtotime($followUpOrderCap) + 86400);

			// TODO: Need to calculate last possible date for converting session
			// and ask about "to date" or not
			// 3) Get the number of of these new guests that have a follow up order.
			$digest51 = DAO_CFactory::create('orders_digest');
			$digest51->query("select iq.*, count(distinct od2.id) from (
							select od.user_id as new_users, min(od.session_time) as first_session from orders_digest od
															where od.is_deleted = 0 and od.store_id = $store_id
																and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
															and od.session_time < now()
																and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')
																group by od.user_id) as iq
							join orders_digest od2 on od2.user_id = iq.new_users and od2.session_time > iq.first_session and od2.session_time < '$followUpOrderCap'
							group by iq.new_users");

			// 34) Calculate the rate
			$Metric->converted_guests = $digest51->N;
			$Metric->conversion_rate = ($totalNew != 0 ? $Metric->converted_guests / $totalNew : 0);
			$Metric->conversion_rate *= 100;

			$Metric->one_month_drop_off = 0; //$digest6->one_month_dropoff;
			$Metric->two_month_drop_off = 0; //$digest7->two_month_dropoff;

			$AAVcutOff = "now()";
			if (!$isCurrentMonth)
			{
				//$AAVcutOffTS = mktime(0, 0, 0, $thisMonthNumber + 1, 1, $thisYear);
				$AAVcutOffTS = strtotime($startDate) + (86400 * $interval);
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
				$period_cutoff = date("Y-m-d H:i:s", strtotime($startDate) + (86400 * $interval));
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

			// retention count
			$digest12 = DAO_CFactory::create('orders_digest');
			$thisMenuID = $MenuObj->id;
			$nextMenuID = $thisMenuID + 1;
			$digest12->query("select distinct iq.* from (
				select distinct od.user_id from orders_digest od
				join booking b on b.order_id = od.order_id and b.status = 'ACTIVE'
				join session s on s.id = b.session_id and s.menu_id = $thisMenuID
				where od.is_deleted = 0 and od.store_id = $store_id and od.order_type = 'REGULAR' and od.user_state = 'EXISTING') as iq
				join booking b2 on b2.user_id = iq.user_id and b2.status = 'ACTIVE'
				join session s2 on s2.id = b2.session_id and s2.menu_id = $nextMenuID
				join orders o on o.id = b2.order_id and o.type_of_order = 'STANDARD'");

			$Metric->retention_count = $digest12->N;

			$endDate = date("Y-m-d H:i:s", strtotime($startDate) + (86400 * $interval));

			$Metric->lifestyle_guest_count = self::getLifestyleGuestCount($store_id, $startDate, $interval, $endDate);

			// Dinner Dollars Spent
			$DDsUsed = new DAO();
			$DDsUsed->query("select sum(o.points_discount_total) as dinner_dollars_used from booking b
										join orders o on b.order_id = o.id
										join session s on s.id = b.session_id and s.menu_id = $thisMenuID and s.store_id = $store_id
										where  b.status = 'ACTIVE' 
										group by s.store_id");
			$DDsUsed->fetch();

			if (empty($DDsUsed->dinner_dollars_used))
			{
				$DDsUsed->dinner_dollars_used = 0;
			}

			$Metric->dinner_dollars_used = $DDsUsed->dinner_dollars_used;

			// New Guests with follow up
			$newWithFollowUp = new DAO();
			$newWithFollowUp->query("select iq.*, min(s2.session_start) from (
									select distinct b.user_id, s.session_start as session_new from booking b
									join session s on s.id = b.session_id and s.menu_id = $thisMenuID and s.store_id = $store_id
									join orders_digest od on od.order_id = b.order_id and od.user_state = 'NEW'
									where  b.status = 'ACTIVE') as iq
									join booking b2 on b2.user_id = iq.user_id and b2.`status` = 'ACTIVE'
									join session s2 on b2.session_id = s2.id and  s2.session_start > iq.session_new
									join orders o on o.id = b2.order_id and o.servings_total_count >= 36
									group by iq.user_id");

			$Metric->new_guests_with_follow_up = $newWithFollowUp->N;

			if (empty($Metric->new_guests_with_follow_up))
			{
				$Metric->new_guests_with_follow_up = 0;
			}

			// retained guest count -  This is the Maintenance opportunity guests from the Growth Dashboard Report
			// It is the count of guests with a standard order in this month that also had a standard order last month
			$Metric->retained_guest_count = self::getRetainedGuestCount($store_id, $MenuObj->id);
		}

		if ($Metrics_Exist)
		{
			$Metric->update();
		}
		else
		{
			$Metric->insert();
		}
	}

	static function getRetainedGuestCount($store_id, $menu_id)
	{
		$currentMonthGuests = new DAO();
		$currentMonthGuests->query("select count(distinct b.id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list from session s
                                        join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
                                        join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD'
                                        where s.menu_id = $menu_id and s.store_id = $store_id");
		$currentMonthGuests->fetch();

		$last_month_menu_id = $menu_id - 1;

		$lastMonth = new DAO();
		if (!empty($currentMonthGuests->guest_list))
		{
			$lastMonth->query("select count(distinct b.user_id) as retained_count, GROUP_CONCAT(distinct b.user_id) as guest_list  from session s
                                		join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$currentMonthGuests->guest_list})
                                		join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD'
            		                      where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

			$lastMonth->fetch();
		}

		if (empty($lastMonth->retained_count))
		{
			$lastMonth->retained_count = 0;
		}

		return $lastMonth->retained_count;
	}

	static function getLifestyleGuestCount($store_id, $startDate, $interval, $endDate)
	{
		$digest = DAO_CFactory::create('orders_digest');
		$digest->query("select distinct od.user_id from orders_digest od
	        where od.is_deleted = 0 and od.store_id = $store_id
	        and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

		$count = 0;

		while ($digest->fetch())
		{
			if (self::wasAdoptedAtTime($digest->user_id, $startDate, $endDate))
			{
				$count++;
			}
		}

		return $count;
	}

	static function wasAdoptedAtTime($user_id, $startDate, $endDate)
	{
		// get all orders by this user in last 180 days

		$orders = new DAO();
		$orders->query("select od.session_time, b.session_id from booking b
	        join session s on s.id = b.session_id and s.session_start < '$endDate' and s.session_start > DATE_SUB('$endDate', INTERVAL 135 DAY)
	        join orders_digest od on od.order_id = b.order_id and od.order_type = 'REGULAR'
	        where b.user_id = $user_id and b.status = 'ACTIVE' order by s.session_start desc");

		if ($orders->N > 2)
		{
			return true;
		}

		return false;
	}

	static function addToDateGuestCounts(&$metricsArray, $store, $startDate, $interval, $storeArr = false)
	{

		$digest = DAO_CFactory::create('orders_digest');

		if (is_numeric($store))
		{

			$sql = "SELECT
								count( od.id ) AS to_date,
								IF
								( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
									od.user_state 
							FROM
								orders_digest od 
							WHERE
								od.is_deleted = 0
								AND od.qualifying_order_id IS NULL
								AND od.store_id = $store
								AND od.session_time >= '$startDate'
								AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
								AND od.session_time < now() 
							GROUP BY
							IF
							( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ),
								od.user_state
							UNION
							SELECT
								count( od.id ) AS to_date,
							  	'ADDITIONAL' AS type,
								od.user_state 
							FROM
								orders_digest od 
							WHERE
								od.is_deleted = 0
								AND od.qualifying_order_id IS NOT NULL
								AND od.store_id = $store
								AND od.session_time >= '$startDate'
								AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
								AND od.session_time < now() 
							GROUP BY
								od.user_state";
			$digest->query($sql);
		}
		else
		{
			if ($store == 'corp_stores')
			{
				$joinClause = " join store on store.id = od.store_id and store.is_corporate_owned = 1 and active = 1 ";
			}
			else if ($store == 'non_corp_stores')
			{
				$joinClause = " join store on store.id = od.store_id and store.is_corporate_owned = 0 and active = 1 ";
			}
			else if ($store == 'custom')
			{
				$joinClause = " join store on store.id = od.store_id and store.id in (" . implode(",", $storeArr) . ") ";
			}
			else// all stores
			{
				$joinClause = " join store on store.id = od.store_id and store.active = 1 ";
			}

			$sql = "SELECT
						count( od.id ) AS to_date,
						IF
							( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
						od.user_state 
					FROM
						orders_digest od
						$joinClause 
					WHERE
						od.is_deleted = 0 
						AND od.qualifying_order_id IS NULL
						AND od.session_time >= '$startDate' 
						AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
						AND od.session_time < now() 
					GROUP BY
					IF
						( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ),
						od.user_state
					UNION
					SELECT
						count( od.id ) AS to_date,
					  'ADDITIONAL' AS type,
						od.user_state 
					FROM
						orders_digest od
						$joinClause 
					WHERE
						od.is_deleted = 0 
						AND od.qualifying_order_id IS NOT NULL
						AND od.session_time >= '$startDate' 
						AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
						AND od.session_time < now() 
					GROUP BY
						od.user_state";

			$digest->query($sql);
		}

		$metricsArray["existing_regular_to_date_orders"] = 0;
		$metricsArray["existing_additional_to_date_orders"] = 0;
		$metricsArray["existing_taste_to_date_orders"] = 0;
		$metricsArray["existing_intro_to_date_orders"] = 0;
		$metricsArray["existing_fundraiser_to_date_orders"] = 0;
		$metricsArray["existing_delivered_to_date_orders"] = 0;
		$metricsArray["reacquired_regular_to_date_orders"] = 0;
		$metricsArray["reacquired_additional_to_date_orders"] = 0;
		$metricsArray["reacquired_intro_to_date_orders"] = 0;
		$metricsArray["reacquired_taste_to_date_orders"] = 0;
		$metricsArray["reacquired_fundraiser_to_date_orders"] = 0;
		$metricsArray["reacquired_delivered_to_date_orders"] = 0;
		$metricsArray["new_regular_to_date_orders"] = 0;
		$metricsArray["new_additional_to_date_orders"] = 0;
		$metricsArray["new_intro_to_date_orders"] = 0;
		$metricsArray["new_taste_to_date_orders"] = 0;
		$metricsArray["new_fundraiser_to_date_orders"] = 0;
		$metricsArray["new_delivered_to_date_orders"] = 0;

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
				else if ($digest->type == 'ADDITIONAL')
				{
					$metricsArray['new_additional_to_date_orders'] = $digest->to_date;
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
				else if ($digest->type == 'DELIVERED')
				{
					$metricsArray['new_delivered_to_date_orders'] = $digest->to_date;
				}
			}
			else if ($digest->user_state == 'REACQUIRED')
			{
				if ($digest->type == 'REGULAR')
				{
					$metricsArray['reacquired_regular_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'ADDITIONAL')
				{
					$metricsArray['reacquired_additioinal_to_date_orders'] = $digest->to_date;
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
				else if ($digest->type == 'DELIVERED')
				{
					$metricsArray['reacquired_delivered_to_date_orders'] = $digest->to_date;
				}
			}
			else if ($digest->user_state == 'EXISTING')
			{
				if ($digest->type == 'TASTE')
				{
					$metricsArray['existing_taste_to_date_orders'] = $digest->to_date;
				}
				else if ($digest->type == 'ADDITIONAL')
				{
					$metricsArray['existing_additional_to_date_orders'] = $digest->to_date;
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
				else if ($digest->type == 'DELIVERED')
				{
					$metricsArray['existing_delivered_to_date_orders'] = $digest->to_date;
				}
			}
		}

		$metricsArray["total_to_date_orders"] = $totalGuests;
	}

	static function getDoorDashAGR($store_id, $thisMonth, $startDate, $interval)
	{
		// TODO: some charges may be deducted from from revenue to get AGR - waiting on decision from Finance

		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT d.store_id, SUM(d.subtotal) - SUM(d.commission) as doordashRev FROM door_dash_orders_and_payouts d
									 where d.store_id = $store_id and d.timestamp_local_date >= '$startDate' and d.timestamp_local_date < DATE_ADD('$startDate', INTERVAL $interval DAY) and d.is_deleted = 0
									  and d.final_order_status in ('Delivered', 'Picked up')
									 GROUP BY d.store_id");
		$DoorDashEntries->fetch();
		if (empty($DoorDashEntries->doordashRev))
		{
			$DoorDashEntries->doordashRev = 0;
		}

		return $DoorDashEntries->doordashRev;
	}

	static function getDoorDashRevenue($store_id, $thisMonth, $startDate, $interval)
	{
		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT d.store_id, SUM(d.subtotal) as doordashRev FROM door_dash_orders_and_payouts d
									 where d.store_id = $store_id and d.timestamp_local_date >= '$startDate' and d.timestamp_local_date < DATE_ADD('$startDate', INTERVAL $interval DAY) and is_deleted = 0
  										and d.final_order_status in ('Delivered', 'Picked up')
									 GROUP BY d.store_id");
		$DoorDashEntries->fetch();
		if (empty($DoorDashEntries->doordashRev))
		{
			$DoorDashEntries->doordashRev = 0;
		}

		return $DoorDashEntries->doordashRev;
	}

	static function updateAGRMetrics($store_id, $thisMonth, $startDate, $interval)
	{

		if (is_null($startDate) || is_null($interval))
		{
			return;
		}

		$sales = self::getGrossSales($store_id, $startDate, $interval);

		if (!isset($sales) || !isset($sales['total_less_discounts']))
		{
			$sales['total_less_discounts'] = 0;
		}

		if (!isset($sales) || !isset($sales['total_less_discounts']))
		{
			$sales['subtotal_delivery_fee'] = 0;
		}

		if (!isset($sales['membership_fees']))
		{
			$sales['membership_fees'] = 0;
		}

		$Metric = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
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

		$Metric->agr_from_membership_fees = $sales['membership_fees'];
		$Metric->revenue_from_membership_fees = $sales['membership_fees'];

		$Metric->agr_from_door_dash = self::getDoorDashAGR($store_id, $thisMonth, $startDate, $interval);

		$Metric->revenue_from_door_dash = self::getDoorDashRevenue($store_id, $thisMonth, $startDate, $interval);

		// get agr by session_type
		$Metric->agr_by_session_taste = 0;
		$Metric->revenue_by_session_taste = 0;
		$Metric->agr_by_session_mfy = 0;
		$Metric->revenue_by_session_mfy = 0;
		$Metric->agr_by_session_standard = 0;
		$Metric->revenue_by_session_standard = 0;
		$Metric->agr_by_session_fundraiser = 0;
		$Metric->revenue_by_session_fundraiser = 0;
		$Metric->agr_by_session_delivered = 0;
		$Metric->revenue_by_session_delivered = 0;

		$Metric->agr_by_session_mfy_walk_in = 0;
		$Metric->revenue_by_session_mfy_walk_in = 0;
		$Metric->agr_by_session_mfy_community_pickup = 0;
		$Metric->revenue_by_session_mfy_community_pickup = 0;

		$digest = DAO_CFactory::create('orders_digest');
		$sql = "select od.session_type as type, sum(od.agr_total) as sum_agr, sum(if (s.session_type_subtype = 'DELIVERY' ,od.agr_total, null)) as sum_delivery_agr,
							sum(if (s.session_type_subtype = 'WALK_IN' ,od.agr_total, null)) as sum_walk_in_agr , 
							sum(if (s.session_type_subtype IN ( 'REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE')  ,od.agr_total, null)) as sum_community_agr,  
       						sum(o.grand_total - o.subtotal_all_taxes) as revenue,
       						sum(if(s.session_type_subtype = 'DELIVERY', o.grand_total - o.subtotal_all_taxes, null)) as delivery_revenue,
       						sum(if(s.session_type_subtype = 'WALK_IN', o.grand_total - o.subtotal_all_taxes, null)) as walk_in_revenue,
       						sum(if(s.session_type_subtype IN ( 'REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE') , o.grand_total - o.subtotal_all_taxes, null)) as community_revenue
					from orders_digest od				
							join booking b on b.order_id = od.order_id and b.status = 'ACTIVE' and b.is_deleted = 0
							join session s on s.id = b.session_id
							join orders o on o.id = od.order_id
							where od.is_deleted = 0 and od.store_id = $store_id
							and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
							group by od.session_type";

		$digest->query($sql);

		while ($digest->fetch())
		{
			if ($digest->type == 'TASTE')
			{
				$Metric->agr_by_session_taste = $digest->sum_agr;
				$Metric->revenue_by_session_taste = $digest->revenue;
			}
			else if ($digest->type == 'MADE_FOR_YOU')
			{
				$Metric->agr_by_session_mfy = $digest->sum_agr - $sales['subtotal_delivery_fee'];
				$Metric->revenue_by_session_mfy = $digest->revenue;
				$Metric->agr_by_session_mfy_walk_in = $digest->sum_walk_in_agr;
				$Metric->agr_by_session_mfy_community_pickup = $digest->sum_community_agr;
				$Metric->agr_by_session_mfy_delivery = $digest->sum_delivery_agr;
				$Metric->revenue_by_session_mfy_delivery = $digest->delivery_revenue;
				$Metric->revenue_by_session_mfy_walk_in = $digest->walk_in_revenue;
				$Metric->revenue_by_session_mfy_community_pickup = $digest->community_revenue;
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
			else if ($digest->type == 'DELIVERED')
			{
				$Metric->agr_by_session_delivered = $digest->sum_agr;
				$Metric->revenue_by_session_delivered = $digest->revenue;
			}
		}

		//	get agr by order type

		$Metric->agr_by_order_regular = 0;
		$Metric->revenue_by_order_regular = 0;
		$Metric->agr_by_order_additional = 0;
		$Metric->revenue_by_order_additional = 0;
		$Metric->agr_by_order_intro = 0;
		$Metric->revenue_by_order_intro = 0;
		$Metric->agr_by_order_taste = 0;
		$Metric->revenue_by_order_taste = 0;
		$Metric->agr_by_order_fundraiser = 0;
		$Metric->revenue_by_order_fundraiser = 0;
		$Metric->agr_by_order_delivered = 0;
		$Metric->revenue_by_order_delivered = 0;

		$digest2 = DAO_CFactory::create('orders_digest');
		//		$sqlOrig = "select if(od.session_type='DELIVERED', 'DELIVERED', od.order_type) as type, sum(od.agr_total) as sum_agr, sum(o.grand_total - o.subtotal_all_taxes) as revenue  from orders_digest od
		//				join orders o on o.id = od.order_id
		//				where od.is_deleted = 0 and od.store_id = $store_id
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		//				group by od.order_type";

		$sql = "SELECT
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
					sum( od.agr_total ) AS sum_agr,
					sum( o.grand_total - o.subtotal_all_taxes ) AS revenue 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type
				UNION
				SELECT
					'ADDITIONAL' AS type,
					sum( od.agr_total ) AS sum_agr,
					sum( o.grand_total - o.subtotal_all_taxes ) AS revenue 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
				  	AND od.order_type = 'REGULAR'
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type";
		$digest2->query($sql);

		while ($digest2->fetch())
		{
			if ($digest2->type == 'REGULAR')
			{
				$Metric->agr_by_order_regular = $digest2->sum_agr;
				$Metric->revenue_by_order_regular = $digest2->revenue;
			}
			if ($digest2->type == 'ADDITIONAL')
			{
				$Metric->agr_by_order_additional = $digest2->sum_agr;
				$Metric->revenue_by_order_additional = $digest2->revenue;
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
			else if ($digest2->type == 'DELIVERED')
			{
				$Metric->agr_by_order_delivered = $digest2->sum_agr;
				$Metric->revenue_by_order_delivered = $digest2->revenue;
			}
		}

		// get agr by user state

		$AgrByUser = array(
			"existing_regular" => 0,
			"existing_additional" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"existing_delivered" => 0,
			"reacquired_regular" => 0,
			"reacquired_additional" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"reacquired_delivered" => 0,
			"new_regular" => 0,
			"new_additional" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0,
			"new_delivered" => 0
		);

		$RevenueByUser = array(
			"existing_regular" => 0,
			"existing_additional" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"existing_delivered" => 0,
			"reacquired_regular" => 0,
			"reacquired_additional" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"reacquired_delivered" => 0,
			"new_regular" => 0,
			"new_additional" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0,
			"new_delivered" => 0
		);

		$digest3 = DAO_CFactory::create('orders_digest');

		//		$sqlOrig = "select if(od.session_type='DELIVERED', 'DELIVERED', od.order_type) as type, od.user_state, sum(od.agr_total) as sum_agr, sum(o.grand_total - o.subtotal_all_taxes) as revenue from orders_digest od
		//				join orders o on o.id = od.order_id
		//				where od.is_deleted = 0 and od.store_id = $store_id
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		//				group by od.order_type, od.user_state";

		$sql = "SELECT
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
					od.user_state,
					sum( od.agr_total ) AS sum_agr,
					sum( o.grand_total - o.subtotal_all_taxes ) AS revenue 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type,
					od.user_state
				UNION
				SELECT
				  'ADDITIONAL' AS type,
					od.user_state,
					sum( od.agr_total ) AS sum_agr,
					sum( o.grand_total - o.subtotal_all_taxes ) AS revenue 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) ";
		$digest3->query($sql);

		while ($digest3->fetch())
		{
			if ($digest3->user_state == 'EXISTING')
			{

				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['existing_regular'] = $digest3->sum_agr;
					$RevenueByUser['existing_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AgrByUser['existing_additional'] = $digest3->sum_agr;
					$RevenueByUser['existing_additional'] = $digest3->revenue;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AgrByUser['existing_delivered'] = $digest3->sum_agr;
					$RevenueByUser['existing_delivered'] = $digest3->revenue;
				}
			}
			else if ($digest3->user_state == 'REACQUIRED')
			{
				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['reacquired_regular'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AgrByUser['reacquired_additional'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_additional'] = $digest3->revenue;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AgrByUser['reacquired_delivered'] = $digest3->sum_agr;
					$RevenueByUser['reacquired_delivered'] = $digest3->revenue;
				}
			}
			else if ($digest3->user_state == 'NEW')
			{
				if ($digest3->type == 'REGULAR')
				{
					$AgrByUser['new_regular'] = $digest3->sum_agr;
					$RevenueByUser['new_regular'] = $digest3->revenue;
				}
				else if ($digest3->type == 'ADDITIONAL')
				{
					$AgrByUser['new_additional'] = $digest3->sum_agr;
					$RevenueByUser['new_additional'] = $digest3->revenue;
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
				else if ($digest3->type == 'DELIVERED')
				{
					$AgrByUser['new_delivered'] = $digest3->sum_agr;
					$RevenueByUser['new_delivered'] = $digest3->revenue;
				}
			}
		}

		$Metric->agr_by_guest_existing_regular = $AgrByUser['existing_regular'];
		$Metric->agr_by_guest_existing_additional = $AgrByUser['existing_additional'];
		$Metric->agr_by_guest_existing_taste = $AgrByUser['existing_taste'];
		$Metric->agr_by_guest_existing_intro = $AgrByUser['existing_intro'];
		$Metric->agr_by_guest_existing_fundraiser = $AgrByUser['existing_fundraiser'];
		$Metric->agr_by_guest_existing_delivered = $AgrByUser['existing_delivered'];

		$Metric->agr_by_guest_reacquired_regular = $AgrByUser['reacquired_regular'];
		$Metric->agr_by_guest_reacquired_additional = $AgrByUser['reacquired_additional'];
		$Metric->agr_by_guest_reacquired_taste = $AgrByUser['reacquired_taste'];
		$Metric->agr_by_guest_reacquired_intro = $AgrByUser['reacquired_intro'];
		$Metric->agr_by_guest_reacquired_fundraiser = $AgrByUser['reacquired_fundraiser'];
		$Metric->agr_by_guest_reacquired_delivered = $AgrByUser['reacquired_delivered'];

		$Metric->agr_by_guest_new_regular = $AgrByUser['new_regular'];
		$Metric->agr_by_guest_new_additional = $AgrByUser['new_additional'];
		$Metric->agr_by_guest_new_taste = $AgrByUser['new_taste'];
		$Metric->agr_by_guest_new_intro = $AgrByUser['new_intro'];
		$Metric->agr_by_guest_new_fundraiser = $AgrByUser['new_fundraiser'];
		$Metric->agr_by_guest_new_delivered = $AgrByUser['new_delivered'];

		$Metric->revenue_by_guest_existing_regular = $RevenueByUser['existing_regular'];
		$Metric->revenue_by_guest_existing_additional = $RevenueByUser['existing_additional'];
		$Metric->revenue_by_guest_existing_taste = $RevenueByUser['existing_taste'];
		$Metric->revenue_by_guest_existing_intro = $RevenueByUser['existing_intro'];
		$Metric->revenue_by_guest_existing_fundraiser = $RevenueByUser['existing_fundraiser'];
		$Metric->revenue_by_guest_existing_delivered = $RevenueByUser['existing_delivered'];

		$Metric->revenue_by_guest_reacquired_regular = $RevenueByUser['reacquired_regular'];
		$Metric->revenue_by_guest_reacquired_additional = $RevenueByUser['reacquired_additional'];
		$Metric->revenue_by_guest_reacquired_taste = $RevenueByUser['reacquired_taste'];
		$Metric->revenue_by_guest_reacquired_intro = $RevenueByUser['reacquired_intro'];
		$Metric->revenue_by_guest_reacquired_fundraiser = $RevenueByUser['reacquired_fundraiser'];
		$Metric->revenue_by_guest_reacquired_delivered = $RevenueByUser['reacquired_delivered'];

		$Metric->revenue_by_guest_new_regular = $RevenueByUser['new_regular'];
		$Metric->revenue_by_guest_new_additional = $RevenueByUser['new_additional'];
		$Metric->revenue_by_guest_new_taste = $RevenueByUser['new_taste'];
		$Metric->revenue_by_guest_new_intro = $RevenueByUser['new_intro'];
		$Metric->revenue_by_guest_new_fundraiser = $RevenueByUser['new_fundraiser'];
		$Metric->revenue_by_guest_new_delivered = $RevenueByUser['new_delivered'];

		// average ticket all

		$digest4 = DAO_CFactory::create('orders_digest');
		$digest4->query("select od.order_type as type, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id
					and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");
		//and od.order_type = 'REGULAR'");
		// go ahead and get the full average per Jeb 10/3/12
		$digest4->fetch();
		$Metric->avg_ticket_all = $digest4->avg_ticket;

		// average ticket regular

		$digest401 = DAO_CFactory::create('orders_digest');
		$digest401->query("select od.order_type as type, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket_reg from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
				and od.order_type = 'REGULAR'
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
			"existing_additional" => 0,
			"existing_taste" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"existing_delivered" => 0,
			"reacquired_regular" => 0,
			"reacquired_additional" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"reacquired_delivered" => 0,
			"new_regular" => 0,
			"new_additional" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0,
			"new_delivered" => 0
		);

		$digest5 = DAO_CFactory::create('orders_digest');

		//		$sql = "select if(od.session_type='DELIVERED', 'DELIVERED', od.order_type) as type, od.user_state, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket from orders_digest od
		//				join orders o on o.id = od.order_id
		//				where od.is_deleted = 0 and od.store_id = $store_id
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		//				group by od.order_type, od.user_state";

		$sql = "SELECT
				IF
					( od.session_type = 'DELIVERED', 'DELIVERED', od.order_type ) AS type,
					od.user_state,
					avg( o.grand_total - o.subtotal_all_taxes ) AS avg_ticket 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type,
					od.user_state
				UNION
				SELECT
				'ADDITIONAL' AS type,
					od.user_state,
					avg( o.grand_total - o.subtotal_all_taxes ) AS avg_ticket 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type,
					od.user_state";
		$digest5->query($sql);

		while ($digest5->fetch())
		{
			if ($digest5->user_state == 'EXISTING')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['existing_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'ADDITIONAL')
				{
					$AvgByUser['existing_additional'] = $digest5->avg_ticket;
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
				else if ($digest5->type == 'DELIVERED')
				{
					$AvgByUser['existing_delivered'] = $digest5->avg_ticket;
				}
			}
			else if ($digest5->user_state == 'REACQUIRED')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['reacquired_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'ADDITIONAL')
				{
					$AvgByUser['reacquired_additional'] = $digest5->avg_ticket;
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
				else if ($digest5->type == 'DELIVERED')
				{
					$AvgByUser['reacquired_delivered'] = $digest5->avg_ticket;
				}
			}
			else if ($digest5->user_state == 'NEW')
			{
				if ($digest5->type == 'REGULAR')
				{
					$AvgByUser['new_regular'] = $digest5->avg_ticket;
				}
				else if ($digest5->type == 'ADDITIONAL')
				{
					$AvgByUser['new_additional'] = $digest5->avg_ticket;
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
				else if ($digest5->type == 'DELIVERED')
				{
					$AvgByUser['new_delivered'] = $digest5->avg_ticket;
				}
			}
		}

		$Metric->avg_ticket_by_guest_existing_regular = $AvgByUser['existing_regular'];
		$Metric->avg_ticket_by_guest_existing_additional = $AvgByUser['existing_additional'];
		$Metric->avg_ticket_by_guest_existing_taste = $AvgByUser['existing_taste'];
		$Metric->avg_ticket_by_guest_existing_intro = $AvgByUser['existing_intro'];
		$Metric->avg_ticket_by_guest_existing_fundraiser = $AvgByUser['existing_fundraiser'];
		$Metric->avg_ticket_by_guest_existing_delivered = $AvgByUser['existing_delivered'];
		$Metric->avg_ticket_by_guest_reacquired_regular = $AvgByUser['reacquired_regular'];
		$Metric->avg_ticket_by_guest_reacquired_additional = $AvgByUser['reacquired_additional'];
		$Metric->avg_ticket_by_guest_reacquired_taste = $AvgByUser['reacquired_taste'];
		$Metric->avg_ticket_by_guest_reacquired_intro = $AvgByUser['reacquired_intro'];
		$Metric->avg_ticket_by_guest_reacquired_fundraiser = $AvgByUser['reacquired_fundraiser'];
		$Metric->avg_ticket_by_guest_reacquired_delivered = $AvgByUser['reacquired_delivered'];
		$Metric->avg_ticket_by_guest_new_regular = $AvgByUser['new_regular'];
		$Metric->avg_ticket_by_guest_new_additional = $AvgByUser['new_additional'];
		$Metric->avg_ticket_by_guest_new_taste = $AvgByUser['new_taste'];
		$Metric->avg_ticket_by_guest_new_intro = $AvgByUser['new_intro'];
		$Metric->avg_ticket_by_guest_new_fundraiser = $AvgByUser['new_fundraiser'];
		$Metric->avg_ticket_by_guest_new_delivered = $AvgByUser['new_delivered'];

		// total addon sales

		$digest7 = DAO_CFactory::create('orders_digest');
		$digest7->query("select od.user_state, sum(od.addon_total) as sum_addon, avg(od.addon_total) as avg_addon from orders_digest od
				join orders o on o.id = od.order_id
				where od.is_deleted = 0 and od.store_id = $store_id
					and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

		$digest7->fetch();

		$Metric->addon_sales_total = $digest7->sum_addon;

		// addon sales by user

		$AddonByUser = array(
			"existing_regular" => 0,
			"existing_taste" => 0,
			"existing_additional" => 0,
			"existing_intro" => 0,
			"existing_fundraiser" => 0,
			"reacquired_regular" => 0,
			"reacquired_additional" => 0,
			"reacquired_intro" => 0,
			"reacquired_taste" => 0,
			"reacquired_fundraiser" => 0,
			"new_regular" => 0,
			"new_additional" => 0,
			"new_intro" => 0,
			"new_taste" => 0,
			"new_fundraiser" => 0
		);

		$digest6 = DAO_CFactory::create('orders_digest');

		//		$sql = "select od.order_type as type, od.user_state, sum(od.addon_total) as sum_addon, avg(od.addon_total) as avg_addon from orders_digest od
		//				join orders o on o.id = od.order_id
		//				where od.is_deleted = 0 and od.store_id = $store_id
		//				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		//				group by od.order_type, od.user_state ";

		$sql = "SELECT
					od.order_type AS type,
					od.user_state,
					sum( od.addon_total ) AS sum_addon,
					avg( od.addon_total ) AS avg_addon 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
				GROUP BY
					od.order_type,
					od.user_state
				UNION
				SELECT
					'ADDITIONAL' AS type,
					od.user_state,
					sum( od.addon_total ) AS sum_addon,
					avg( od.addon_total ) AS avg_addon 
				FROM
					orders_digest od
					JOIN orders o ON o.id = od.order_id 
				WHERE
					od.is_deleted = 0 
					AND od.qualifying_order_id IS NOT NULL
					AND od.store_id = $store_id 
					AND od.session_time >= '$startDate' 
					AND od.session_time < DATE_ADD( '$startDate', INTERVAL $interval DAY ) 
					GROUP BY
					od.order_type,
					od.user_state";
		$digest6->query($sql);

		while ($digest6->fetch())
		{
			if ($digest6->user_state == 'EXISTING')
			{

				if ($digest6->type == 'REGULAR')
				{
					$AddonByUser['existing_regular'] = $digest6->sum_addon;
				}
				else if ($digest6->type == 'ADDITIONAL')
				{
					$AddonByUser['existing_additional'] = $digest6->sum_addon;
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
				else if ($digest6->type == 'ADDITIONAL')
				{
					$AddonByUser['reacquired_additional'] = $digest6->sum_addon;
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
				else if ($digest6->type == 'ADDITIONAL')
				{
					$AddonByUser['new_additional'] = $digest6->sum_addon;
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
		$Metric->addon_sales_by_guest_existing_additional = $AddonByUser['existing_additional'];
		$Metric->addon_sales_by_guest_existing_taste = $AddonByUser['existing_taste'];
		$Metric->addon_sales_by_guest_existing_intro = $AddonByUser['existing_intro'];
		$Metric->addon_sales_by_guest_existing_fundraiser = $AddonByUser['existing_fundraiser'];
		$Metric->addon_sales_by_guest_reacquired_regular = $AddonByUser['reacquired_regular'];
		$Metric->addon_sales_by_guest_reacquired_additional = $AddonByUser['reacquired_additional'];
		$Metric->addon_sales_by_guest_reacquired_taste = $AddonByUser['reacquired_taste'];
		$Metric->addon_sales_by_guest_reacquired_intro = $AddonByUser['reacquired_intro'];
		$Metric->addon_sales_by_guest_reacquired_fundraiser = $AddonByUser['reacquired_fundraiser'];
		$Metric->addon_sales_by_guest_new_regular = $AddonByUser['new_regular'];
		$Metric->addon_sales_by_guest_new_additional = $AddonByUser['new_additional'];
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

	static function rankStores($overrideMonth = false)
	{
		$retVal = false;

		if ($overrideMonth)
		{
			$parts = explode("-", $overrideMonth);

			$thisMonthNumber = intval($parts[1]);
			$thisYear = $parts[0];
			$thisMonth = $overrideMonth;
		}
		else
		{

			$now = date("Y-m-d");
			$menuObj = new CMenu();
			$menuObj->query("SELECT id, menu_start, global_menu_start_date FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
			$menuObj->fetch();
			$thisMonth = $menuObj->menu_start;

			$thisMonthNumber = date("n", strtotime($thisMonth));
			$thisYear = date("Y", strtotime($thisMonth));

			$firstOfCalendarMonthTS = strtotime($menuObj->menu_start);
			$periodEndTS = strtotime(date("Y-m-08", strtotime($menuObj->menu_start)));

			$periodStartTS = strtotime($menuObj->global_menu_start_date);
			if ($firstOfCalendarMonthTS < $periodStartTS)
			{
				$periodStartTS = $firstOfCalendarMonthTS;
			}

			if (time() >= $periodStartTS && time() < $periodEndTS)
			{
				$retVal = date("Y-m-d", mktime(0, 0, 0, $thisMonthNumber - 1, 1, $thisYear));
			}
		}

		$storeRanksArray = array();

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select id from store where active = 1 and store_type <> 'DISTRIBUTION_CENTER'");

		while ($storeObj->fetch())
		{
			$thisStoreID = $storeObj->id;
			$thisRankingObj = DAO_CFactory::create('dashboard_metrics_rankings_by_menu');
			$thisRankingObj->date = $thisMonth;
			$thisRankingObj->store_id = $thisStoreID;

			$thisRankingObj->find(true);

			$storeRanksArray[$thisStoreID] = $thisRankingObj;
		}

		// agr
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
		$thisMetricObj->query("select store_id, total_agr from dashboard_metrics_agr_by_menu where date = '$thisMonth' and store_id <> 257 order by total_agr desc");
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

		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
		$thisMetricObj->query("select dma.store_id, ((dma.total_agr - dma2.total_agr) /  dma2.total_agr) * 100 as delta from dashboard_metrics_agr_by_menu dma
				join dashboard_metrics_agr_by_menu dma2 on dma2.store_id = dma.store_id and dma2.date = '$sameMonthLastYear'
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

		list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $thisMonth);

		// in_store
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select dmg.store_id,
							dmg.instore_signup_existing_regular as in_store_count,
							to_date.to_date_count,
							(dmg.instore_signup_existing_regular  / to_date.to_date_count) * 100 as sign_up_rate
							 from dashboard_metrics_guests_by_menu dmg
							left join (select od.store_id, count(od.id) as to_date_count from orders_digest od where od.is_deleted = 0 
							and od.session_time > '$menu_start_date' and od.session_time < DATE_ADD('$menu_start_date', INTERVAL $interval DAY) and od.session_time < now()
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

		//  new guest visits
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select dmg.store_id, dmg.unique_new_guest_count
										from dashboard_metrics_guests_by_menu dmg
										where dmg.date = '$thisMonth' order by unique_new_guest_count desc");
		$rank = 0;
		$lastVal = false;
		while ($thisMetricObj->fetch())
		{
			$didley = &$storeRanksArray[$thisMetricObj->store_id];
			if (empty($didley))
			{
				continue; // there was a metric for a now inactive store so skip it
			}

			$didley->new_guest_visits = $thisMetricObj->unique_new_guest_count;
			if ($lastVal != $thisMetricObj->unique_new_guest_count)
			{
				++$rank;
			}

			$didley->new_guest_visits_rank = $rank;
			$lastVal = $thisMetricObj->unique_new_guest_count;
		}

		// guest visits
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select dmg.store_id, dmg.guest_count_total  from dashboard_metrics_guests_by_menu dmg
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
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select dmg.store_id,  dmg.orders_count_all / dmg.sessions_count_all as avg_guests_per_session from dashboard_metrics_guests_by_menu dmg
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
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
		$thisMetricObj->query("select store_id, avg_ticket_regular from dashboard_metrics_agr_by_menu where date = '$thisMonth' and store_id <> 257 order by avg_ticket_regular desc");
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
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
		/*
		$thisMetricObj->query("select dma.store_id, dma.addon_sales_total, dmg.orders_count_all, dma.addon_sales_total / dmg.orders_count_all as avg_addon_sales from dashboard_metrics_agr dma
		join dashboard_metrics_guests dmg on dmg.date = '$thisMonth' and dmg.store_id = dma.store_id
		where dma.date = '$thisMonth' and dma.store_id <> 257 order by avg_addon_sales desc");
		*/

		$thisMetricObj->query("select dma.store_id, dma.addon_sales_total, dmg.orders_count_all, if(odq.comp_orders > 0,  dma.addon_sales_total / odq.comp_orders, 0) as avg_addon_sales, odq.comp_orders from dashboard_metrics_agr_by_menu dma
		join dashboard_metrics_guests_by_menu dmg on dmg.date = '$thisMonth' and dmg.store_id = dma.store_id
		join (select od.store_id, count(od.id) as comp_orders from orders_digest od where 
		od.session_time > '$menu_start_date' and od.session_time < DATE_ADD('$menu_start_date', INTERVAL $interval DAY) and od.session_time < now()
		and od.is_deleted = 0 group by od.store_id) as odq on odq.store_id = dma.store_id
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
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select store_id, avg_servings_per_guest_regular from dashboard_metrics_guests_by_menu where date = '$thisMonth'  and store_id <> 257 order by avg_servings_per_guest_regular desc");
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
		$thisMetricObj = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$thisMetricObj->query("select dmg.store_id, conversion_rate as converted_percent  from dashboard_metrics_guests_by_menu dmg
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

			if ($class && $class == "DAO_Dashboard_metrics_rankings_by_menu")
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

		return $retVal;
	}

	static function getRollupAGRbyMonth($month, $filter = 'all_stores', $storeArr = false)
	{
		$joinClause = "";
		$monthStartJoinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = dmabm.store_id and store.is_corporate_owned = 1 and active = 1 ";
			$monthStartJoinClause = " join store on store.id = dmas.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = dmabm.store_id and store.is_corporate_owned = 0 and active = 1 ";
			$monthStartJoinClause = " join store on store.id = dmas.store_id and store.is_corporate_owned = 0 and active = 1 ";
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store on store.id = dmabm.store_id and store_id in (" . implode(",", $storeArr) . ") "; //
			$monthStartJoinClause = " join store on store.id = dmas.store_id and store_id in (" . implode(",", $storeArr) . ") "; //
		}
		else
		{
			$joinClause = " join store on store.id = dmabm.store_id and store.active = 1 ";
			$monthStartJoinClause = " join store on store.id = dmas.store_id and store.active = 1 ";
		}

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');

		$AGRMetrics->query("SELECT
			Sum(dmabm.total_agr) as total_agr,
			Sum(dmabm.sales_adjustments_total) as sales_adjustments_total,
       		Sum(dmabm.agr_by_session_standard) as agr_by_session_standard,
			Sum(dmabm.agr_by_session_taste) as agr_by_session_taste,
			Sum(dmabm.agr_by_session_mfy) as agr_by_session_mfy,
       		Sum(dmabm.agr_by_session_mfy_delivery) as agr_by_session_mfy_delivery,
       		Sum(dmabm.agr_by_session_mfy_walk_in) as agr_by_session_mfy_walk_in,
       		Sum(dmabm.agr_by_session_mfy_community_pickup) as agr_by_session_mfy_community_pickup,
		    Sum(dmabm.agr_by_session_fundraiser) as agr_by_session_fundraiser,
		    Sum(dmabm.agr_by_session_delivered) as agr_by_session_delivered,

			Sum(dmabm.agr_by_order_regular) as agr_by_order_regular,
       		Sum(dmabm.agr_by_order_additional) as agr_by_order_additional,
			Sum(dmabm.agr_by_order_intro) as agr_by_order_intro,
			Sum(dmabm.agr_by_order_taste) as agr_by_order_taste,
		    Sum(dmabm.agr_by_order_fundraiser) as agr_by_order_fundraiser,
		    Sum(dmabm.agr_by_order_delivered) as agr_by_order_delivered,

			Sum(dmabm.agr_by_guest_existing_regular) as agr_by_guest_existing_regular,
       		Sum(dmabm.agr_by_guest_existing_additional) as agr_by_guest_existing_additional,
			Sum(dmabm.agr_by_guest_existing_taste) as agr_by_guest_existing_taste,
			Sum(dmabm.agr_by_guest_existing_intro) as agr_by_guest_existing_intro,
		    Sum(dmabm.agr_by_guest_existing_fundraiser) as agr_by_guest_existing_fundraiser,
		    Sum(dmabm.agr_by_guest_existing_delivered) as agr_by_guest_existing_delivered,
       

			Sum(dmabm.agr_by_guest_new_regular) as agr_by_guest_new_regular,
      		 Sum(dmabm.agr_by_guest_new_additional) as agr_by_guest_new_additional,
			Sum(dmabm.agr_by_guest_new_taste) as agr_by_guest_new_taste,
			Sum(dmabm.agr_by_guest_new_intro) as agr_by_guest_new_intro,
		    Sum(dmabm.agr_by_guest_new_fundraiser) as agr_by_guest_new_fundraiser,
		    Sum(dmabm.agr_by_guest_new_delivered) as agr_by_guest_new_delivered,

			Sum(dmabm.agr_by_guest_reacquired_regular) as agr_by_guest_reacquired_regular,
       		Sum(dmabm.agr_by_guest_reacquired_additional) as agr_by_guest_reacquired_additional,
			Sum(dmabm.agr_by_guest_reacquired_taste) as agr_by_guest_reacquired_taste,
			Sum(dmabm.agr_by_guest_reacquired_intro) as agr_by_guest_reacquired_intro,
		    Sum(dmabm.agr_by_guest_reacquired_fundraiser) as agr_by_guest_reacquired_fundraiser,
       		Sum(dmabm.agr_by_guest_reacquired_delivered) as agr_by_guest_reacquired_delivered,

		    Sum(dmabm.agr_from_membership_fees) as agr_from_membership_fees,		    
       		Sum(dmabm.agr_from_door_dash) as agr_from_door_dash,


			Avg(dmabm.avg_ticket_all) as avg_ticket_all,
			Avg(dmabm.avg_ticket_by_guest_existing_regular) as avg_ticket_by_guest_existing_regular,
       		Avg(dmabm.avg_ticket_by_guest_existing_additional) as avg_ticket_by_guest_existing_additional,
			Avg(dmabm.avg_ticket_by_guest_existing_taste) as avg_ticket_by_guest_existing_taste,
			Avg(dmabm.avg_ticket_by_guest_existing_intro) as avg_ticket_by_guest_existing_intro,
		    Avg(dmabm.avg_ticket_by_guest_existing_fundraiser) as avg_ticket_by_guest_existing_fundraiser,
		    Avg(dmabm.avg_ticket_by_guest_existing_delivered) as avg_ticket_by_guest_existing_delivered,
       
			Avg(dmabm.avg_ticket_by_guest_new_regular) as avg_ticket_by_guest_new_regular,
       		Avg(dmabm.avg_ticket_by_guest_new_additional) as avg_ticket_by_guest_new_additional,
			Avg(dmabm.avg_ticket_by_guest_new_taste) as avg_ticket_by_guest_new_taste,
			Avg(dmabm.avg_ticket_by_guest_new_intro) as avg_ticket_by_guest_new_intro,
		    Avg(dmabm.avg_ticket_by_guest_new_fundraiser) as avg_ticket_by_guest_new_fundraiser,
		    Avg(dmabm.avg_ticket_by_guest_new_delivered) as avg_ticket_by_guest_new_delivered,
       

			Avg(dmabm.avg_ticket_by_guest_reacquired_regular) as avg_ticket_by_guest_reacquired_regular,
       		Avg(dmabm.avg_ticket_by_guest_reacquired_additional) as avg_ticket_by_guest_reacquired_additional,
			Avg(dmabm.avg_ticket_by_guest_reacquired_intro) as avg_ticket_by_guest_reacquired_intro,
			Avg(dmabm.avg_ticket_by_guest_reacquired_taste) as avg_ticket_by_guest_reacquired_taste,
		    Avg(dmabm.avg_ticket_by_guest_reacquired_fundraiser) as avg_ticket_by_guest_reacquired_fundraiser,
		    Avg(dmabm.avg_ticket_by_guest_reacquired_delivered) as avg_ticket_by_guest_reacquired_delivered,

			Sum(dmabm.addon_sales_total) as addon_sales_total,
			Sum(dmabm.addon_sales_by_guest_existing_regular) as addon_sales_by_guest_existing_regular,
       		Sum(dmabm.addon_sales_by_guest_existing_additional) as addon_sales_by_guest_existing_additional,
			Sum(dmabm.addon_sales_by_guest_existing_taste) as addon_sales_by_guest_existing_taste,
			Sum(dmabm.addon_sales_by_guest_existing_intro) as addon_sales_by_guest_existing_intro,
		    Sum(dmabm.addon_sales_by_guest_existing_fundraiser) as addon_sales_by_guest_existing_fundraiser,

			Sum(dmabm.addon_sales_by_guest_new_regular) as addon_sales_by_guest_new_regular,
       		Sum(dmabm.addon_sales_by_guest_new_additional) as addon_sales_by_guest_new_additional,
			Sum(dmabm.addon_sales_by_guest_new_taste) as addon_sales_by_guest_new_taste,
			Sum(dmabm.addon_sales_by_guest_new_intro) as addon_sales_by_guest_new_intro,
		    Sum(dmabm.addon_sales_by_guest_new_fundraiser) as addon_sales_by_guest_new_fundraiser,

			Sum(dmabm.addon_sales_by_guest_reacquired_regular) as addon_sales_by_guest_reacquired_regular,
       		Sum(dmabm.addon_sales_by_guest_reacquired_additional) as addon_sales_by_guest_reacquired_additional,
			Sum(dmabm.addon_sales_by_guest_reacquired_taste) as addon_sales_by_guest_reacquired_taste,
			Sum(dmabm.addon_sales_by_guest_reacquired_intro) as addon_sales_by_guest_reacquired_intro,
		    Sum(dmabm.addon_sales_by_guest_reacquired_fundraiser) as addon_sales_by_guest_reacquired_fundraiser,

			Sum(dmabm.revenue_by_guest_existing_regular) as revenue_by_guest_existing_regular,
       		Sum(dmabm.revenue_by_guest_existing_additional) as revenue_by_guest_existing_additional,
			Sum(dmabm.revenue_by_guest_existing_taste) as revenue_by_guest_existing_taste,
			Sum(dmabm.revenue_by_guest_existing_intro) as revenue_by_guest_existing_intro,
		    Sum(dmabm.revenue_by_guest_existing_fundraiser) as revenue_by_guest_existing_fundraiser,
		    Sum(dmabm.revenue_by_guest_existing_delivered) as revenue_by_guest_existing_delivered,
       
			Sum(dmabm.revenue_by_guest_new_regular) as revenue_by_guest_new_regular,
       		Sum(dmabm.revenue_by_guest_new_additional) as revenue_by_guest_new_additional,
			Sum(dmabm.revenue_by_guest_new_taste) as revenue_by_guest_new_taste,
			Sum(dmabm.revenue_by_guest_new_intro) as revenue_by_guest_new_intro,
		    Sum(dmabm.revenue_by_guest_new_fundraiser) as revenue_by_guest_new_fundraiser,
		    Sum(dmabm.revenue_by_guest_new_delivered) as revenue_by_guest_new_delivered,

			Sum(dmabm.revenue_by_guest_reacquired_regular) as revenue_by_guest_reacquired_regular,
       		Sum(dmabm.revenue_by_guest_reacquired_additional) as revenue_by_guest_reacquired_additional,
			Sum(dmabm.revenue_by_guest_reacquired_taste) as revenue_by_guest_reacquired_taste,
			Sum(dmabm.revenue_by_guest_reacquired_intro) as revenue_by_guest_reacquired_intro,
		    Sum(dmabm.revenue_by_guest_reacquired_fundraiser) as revenue_by_guest_reacquired_fundraiser,
		    Sum(dmabm.revenue_by_guest_reacquired_delivered) as revenue_by_guest_reacquired_delivered,       
       
		    Sum(dmabm.revenue_from_membership_fees) as revenue_from_membership_fees,       	
       		Sum(dmabm.revenue_from_door_dash) as revenue_from_door_dash


			FROM
				dashboard_metrics_agr_by_menu dmabm
			$joinClause
			WHERE
				date = '$month' 
			    and dmabm.total_agr > 0
			    and dmabm.is_deleted = 0 group by date");

		$AGRMetrics->fetch();
		$AGRMetricsArray = $AGRMetrics->toArray();

		// get the first day of the menu
		$menuInfo = CMenu::getMenuByAnchorDate($month);
		$menuStart = $menuInfo['global_menu_start_date'];

		if (strtotime($menuStart) > time())
		{
			$AGRMetricsArray['month_start_total_agr'] = "-";
		}

		$snapShots = new DAO();
		$snapShots->query("select sum(dmas.agr_menu_month) as agr_menu_month from dashboard_metrics_agr_snapshots dmas
					$monthStartJoinClause
		where dmas.month = '$month' and dmas.date = '$menuStart' and dmas.is_deleted = 0 group by dmas.date");
		if ($snapShots->fetch())
		{
			$revenue = (!empty($snapShots->agr_menu_month) ? $snapShots->agr_menu_month : 0);
			$AGRMetricsArray['month_start_total_agr'] = $revenue;
		}
		else
		{
			$AGRMetricsArray['month_start_total_agr'] = "-";
		}

		return $AGRMetricsArray;
	}

	static function getAGRTrendingDataForStore($store_id, $anchorDate, $is_exporting = false)
	{
		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 3);
			}
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');

		$AGRMetrics->query("select dma.date,
			dma.store_id,
			dma.total_agr,
			dma_prev.date as prev_date,
			dma_prev.total_agr as prev_agr,
			dma.total_agr - dma_prev.total_agr as diff,
			(dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr as percent_diff,
			dma.avg_ticket_regular
			from dashboard_metrics_agr_by_menu dma
			left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = $store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			where dma.date > '$lastYear' and dma.date < '$anchorDate' and dma.store_id = $store_id order by dma.date desc");

		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}

	static function getAGRTrendingDataHomeOfficeRollup($filter, $anchorDate, $trade_area_id = 0, $is_exporting = false, $customList = false)
	{
		if(is_null($filter)){
			$filter = 'all_stores';;
		}
		//PHP 8 $filter = $filter ??'all_stores';
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.is_corporate_owned = 0  and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.store_type <> 'DISTRIBUTION_CENTER' join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id in ($customList) ";
		}
		else
		{
			$joinClause = " join store st on st.id = dma.store_id and st.active = 1 ";
		}

		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 3);
			}
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');

		$AGRMetrics->query("select dma.date,
			sum(dma.total_agr) as total_agr,
			dma_prev.date as prev_date,
			sum(dma_prev.total_agr) as prev_agr,
			sum(dma.total_agr) - sum(dma_prev.total_agr) as diff,
			(sum(dma.total_agr) - sum(dma_prev.total_agr)) / sum(dma_prev.total_agr) as percent_diff,
			avg(if (dma.total_agr > 0,dma.avg_ticket_regular,null)) as avg_ticket_regular
			from dashboard_metrics_agr_by_menu dma
			left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			$joinClause
			where dma.date > '$lastYear' and dma.date < '$anchorDate'
            group by dma.date order by dma.date desc");

		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}

	static function getAGRTrendingDataRollup($store_id, $anchorDate, $filter = 'single_store', $trade_area_id = 0, $is_exporting = false, $customList = false)
	{
		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear - 3);
			}
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
			$AGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$AGRMetrics->query("select 1 AS ONE,
				dma.store_id,
				avg(dma.total_agr) as total_agr,
				avg(dma_prev.total_agr) as prev_agr,
				avg(dma.total_agr - dma_prev.total_agr) as diff,
				avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
				avg(dma.avg_ticket_regular) as avg_ticket_regular
				from dashboard_metrics_agr_by_menu dma
				left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = $store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
				where dma.date > '$lastYear' and dma.date < '$anchorDate' and dma.store_id = $store_id and dma.total_agr > 0
				group by ONE");

			if ($AGRMetrics->fetch())
			{
				$retVal['store_avg'] = $AGRMetrics->toArray();
			}
		}

		// national rollup - depends on filter setting
		$AGRMetricsNat = DAO_CFactory::create('dashboard_metrics_agr_by_menu');

		if ($filter == 'single_store')
		{
			$AGRMetricsNat->query("select 1 AS ONE,
				avg(dma.total_agr) as total_agr,
				avg(dma_prev.total_agr) as prev_agr,
				avg(dma.total_agr - dma_prev.total_agr) as diff,
				avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
				avg(dma.avg_ticket_regular) as avg_ticket_regular
				from dashboard_metrics_agr_by_menu dma
				left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
				join store st on dma.store_id = st.id and st.active = 1
				where dma.date > '$lastYear' and dma.date < '$anchorDate'
				group by ONE");
		}
		else
		{
			$joinClause = "";

			if ($filter == 'corp_stores')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
			}
			else if ($filter == 'non_corp_stores')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
			}
			else if ($filter == 'region')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.store_type <> 'DISTRIBUTION_CENTER' join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0";
			}
			else if ($filter == 'custom')
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 and st.id in ($customList) ";
			}
			else
			{
				$joinClause = " join store st on st.id = dma.store_id and st.active = 1 ";
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
						avg(if(dma.total_agr > 0,dma.avg_ticket_regular,null)) as avg_ticket_regular
						from dashboard_metrics_agr_by_menu dma
						left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
						$joinClause
						where dma.date > '$lastYear' and dma.date < '$anchorDate'
						group by dma.date) as iq");
		}

		if ($AGRMetricsNat->fetch())
		{
			$retVal['national_avg'] = $AGRMetricsNat->toArray();
		}

		$AGRMetricsTop5 = DAO_CFactory::create('dashboard_metrics_agr_by_menu');

		$AGRMetricsTop5->query("select 1 AS ONE,
			avg(dma.total_agr) as total_agr,
			avg(dma_prev.total_agr) as prev_agr,
			avg(dma.total_agr - dma_prev.total_agr) as diff,
			avg((dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr) as percent_diff,
			avg(if(dma.total_agr > 0,dma.avg_ticket_regular,null)) as avg_ticket_regular
			from dashboard_metrics_agr_by_menu dma
			left join dashboard_metrics_agr_by_menu dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			join store st on dma.store_id = st.id and st.active = 1 and st.is_in_current_top_5 = 1 
			where dma.date = '$previousMonth'
			group by ONE");

		if ($AGRMetricsTop5->fetch())
		{
			$retVal['top5_avg'] = $AGRMetricsTop5->toArray();
		}

		return $retVal;
	}

	static function addcancelledOrdersRollups($store_id, $menu_id, &$rollups, $rows = null, $filter = 'single_store', $is_exporting = false)
	{
		$divisor = 12;

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$divisor = $menu_id - 125;
			}
			else
			{
				$divisor = 36;
			}
		}

		$last_year_menu_id = $menu_id - 12;
		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$last_year_menu_id = 125;
			}
			else
			{
				$last_year_menu_id -= 24;
			}
		}

		if ($filter == 'single_store' && !empty($rows))
		{
			//we need to know many months the store was open so let's get the number of non-Zero AGR rows for the store

			// 12 month store average
			$sum = 0;
			$monthCount = 0;
			foreach ($rows as $data)
			{
				$sum += $data['num_cancelled_orders'];

				if (isset($data['total_agr']) && $data['total_agr'] > 0)
				{
					$monthCount++;
				}
			}

			$rollups['store_avg']['num_cancelled_orders'] = CTemplate::number_format($sum / $monthCount);
		}

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

		$nat_sum = 0;
		$GuestMetrics->query("select m.menu_start as date, count(b.id) as numCancelled from booking b
				join session s on s.id = b.session_id and  s.menu_id >= $last_year_menu_id and s.menu_id < $menu_id
				join store st on st.id = s.store_id and st.active = 1 and st.store_type <> 'DISTRIBUTION_CENTER' 
				join menu m on m.id = s.menu_id
				where b.status = 'CANCELLED' and b.is_deleted = 0
				group by s.menu_id
				order by s.menu_id desc");

		$StoreCount = 0;

		while ($GuestMetrics->fetch())
		{
			$nat_sum += $GuestMetrics->numCancelled;
		}

		if ($filter == 'single_store')
		{
			$storeCounter = DAO_CFactory::create('store');
			$storeCounter->query("select count(id) as numStores from store where active = 1");
			$storeCounter->fetch();

			$rollups['national_avg']['num_cancelled_orders'] = CTemplate::number_format(($nat_sum / $divisor) / $storeCounter->numStores);
		}
		else
		{
			$rollups['national_avg']['num_cancelled_orders'] = CTemplate::number_format($nat_sum / $divisor);
		}
	}

	static function addcancelledOrdersForStore($store_id, &$rows, $menu_id, $is_exporting = false)
	{
		$last_year_menu_id = $menu_id - 12;
		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$last_year_menu_id = 125;
			}
			else
			{
				$last_year_menu_id -= 24;
			}
		}

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
		$GuestMetrics->query("select 
								m.menu_start as date,
								count(b.id) as numCancelled 
								from booking b							
								join session s on s.id = b.session_id and s.menu_id >= $last_year_menu_id and s.menu_id < $menu_id
								join menu m on m.id = s.menu_id								
								where b.status = 'CANCELLED' and b.is_deleted = 0 and s.store_id = $store_id
								group by s.menu_id
								order by s.menu_id desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date]['num_cancelled_orders'] = $GuestMetrics->numCancelled;
			}
		}
	}

	static function addGuestTrendingDataForStore($store_id, &$rows, $anchorDate, $is_exporting = false)
	{
		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 3);
			}
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

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
				from dashboard_metrics_guests_by_menu dmg
				where dmg.date > '$lastYear' and dmg.date < '$anchorDate' and dmg.store_id = $store_id group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}
	}

	static function addCancelledOrders(&$rows, $menu_id, $filter = 'all_stores', $trade_area_id = 0, $is_exporting = false, $customList = false)
	{


		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1  and st.store_type <> 'DISTRIBUTION_CENTER' join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1 and st.id in ($customList) ";
		}
		else
		{
			$joinClause = " join store st on st.id = s.store_id and st.active = 1  ";
		}

		$last_year_menu_id = $menu_id - 12;
		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$last_year_menu_id = 125;
			}
			else
			{
				$last_year_menu_id -= 24;
			}
		}

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

		$GuestMetrics->query("select m.menu_start as date, count(b.id) as numCancelled from booking b
									join session s on s.id = b.session_id and  s.menu_id >= $last_year_menu_id and s.menu_id < $menu_id
									join menu m on m.id = s.menu_id
									$joinClause
									where b.status = 'CANCELLED' and b.is_deleted = 0
									group by s.menu_id
									order by s.menu_id desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date]['num_cancelled_orders'] = $GuestMetrics->numCancelled;
			}
		}
	}

	static function addGuestTrendingHomeofficeRollups(&$rows, $anchorDate, $filter = 'all_stores', $trade_area_id = 0, $is_exporting = false, $customList = false)
	{


		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.store_type <> 'DISTRIBUTION_CENTER' join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id in ($customList) ";
		}
		else
		{
			$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 ";
		}

		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 3);
			}
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

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
				from dashboard_metrics_guests_by_menu dmg
				$joinClause
				where dmg.date > '$lastYear' and dmg.date < '$anchorDate'  and dmg.guest_count_total > 0  group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}
	}

	static function addGuestTrendingDataRollups($store_id, $anchorDate, &$rows, $filter = 'single_store', $trade_area_id = 0, $is_exporting = false, $customList = false)
	{
		$anchorDateTS = strtotime($anchorDate);

		$thisMonth = date("n", $anchorDateTS);
		$thisYear = date("Y", $anchorDateTS);

		if ($is_exporting)
		{
			if (isset($_REQUEST['tr_all_time']))
			{
				$lastYearTime = mktime(0, 0, 0, 1, 1, 2012);
			}
			else
			{
				$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 3);
			}
		}
		else
		{
			$lastYearTime = mktime(0, 0, 0, $thisMonth - 2, 1, $thisYear - 1);
		}

		$lastYear = date("Y-m-01", $lastYearTime);

		$previousMonth = date("Y-m-01", mktime(0, 0, 0, $thisMonth - 1, 1, $thisYear));

		if ($store_id)
		{
			$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
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
					from dashboard_metrics_guests_by_menu dmg
					where dmg.date > '$lastYear' and dmg.date < '$anchorDate' and dmg.store_id = $store_id and dmg.guest_count_total > 0
					group by ONE");

			if ($GuestMetrics->fetch())
			{
				if (isset($rows['store_avg']))
				{
					$rows['store_avg'] = array_merge($rows['store_avg'], $GuestMetrics->toArray());
				}
			}
		}

		$GuestMetricsNat = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

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
					from dashboard_metrics_guests_by_menu dmg
					join store st on st.id = dmg.store_id and st.active = 1 and st.id <> 257
					where dmg.date > '$lastYear' and dmg.date < '$anchorDate'
					group by ONE");
		}
		else
		{
			$joinClause = "";

			if ($filter == 'corp_stores')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
			}
			else if ($filter == 'non_corp_stores')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
			}
			else if ($filter == 'region')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.store_type <> 'DISTRIBUTION_CENTER' join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 ";
			}
			else if ($filter == 'custom')
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 and st.id in ($customList) ";
			}
			else
			{
				$joinClause = " join store st on st.id = dmg.store_id and st.active = 1 ";
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
					from dashboard_metrics_guests_by_menu dmg
					$joinClause
					where dmg.date > '$lastYear' and dmg.date < '$anchorDate'  and dmg.guest_count_total > 0  group by dmg.date) as iq");
		}

		if ($GuestMetricsNat->fetch())
		{
			if (isset($rows['national_avg']))
			{
				$rows['national_avg'] = array_merge($rows['national_avg'], $GuestMetricsNat->toArray());
			}
		}

		$GuestMetricsTop5 = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
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
				from dashboard_metrics_guests_by_menu dmg
				join store st on st.id = dmg.store_id and st.active = 1 and st.is_in_current_top_5 = 1
				where dmg.date = '$previousMonth'  and dmg.guest_count_total > 0 
				group by ONE");

		if ($GuestMetricsTop5->fetch())
		{
			if (isset($rows['top5_avg']))
			{
				$rows['top5_avg'] = array_merge($rows['top5_avg'], $GuestMetricsTop5->toArray());
			}
		}
	}

	static function getRollupGuestNumberdByMonth($month, $filter = 'all_stores', $storeArr = false)
	{
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store on store.id = dmgdm.store_id and store.is_corporate_owned = 1 and active = 1 ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store on store.id = dmgdm.store_id and store.is_corporate_owned = 0 and active = 1 and store.id <> 257 "; // remove the Pig
		}
		else if ($filter == 'custom')
		{
			$joinClause = " join store on store.id = dmgdm.store_id and store_id in (" . implode(",", $storeArr) . ") "; //
		}
		else
		{
			$joinClause = " join store on store.id = dmgdm.store_id and store.active = 1 and store.id <> 257 "; // remove the Pig
		}

		$AGRMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

		$AGRMetrics->query("SELECT
				Sum(dmgdm.guest_count_total) as guest_count_total,
				Sum(dmgdm.guest_count_existing_regular) as guest_count_existing_regular,
       			Sum(dmgdm.guest_count_existing_additional) as guest_count_existing_additional,
				Sum(dmgdm.guest_count_existing_taste) as guest_count_existing_taste,
				Sum(dmgdm.guest_count_existing_intro) as guest_count_existing_intro,
				Sum(dmgdm.guest_count_existing_fundraiser) as guest_count_existing_fundraiser,
				Sum(dmgdm.guest_count_existing_delivered) as guest_count_existing_delivered,

				Sum(dmgdm.guest_count_reacquired_regular) as guest_count_reacquired_regular,
       			Sum(dmgdm.guest_count_reacquired_additional) as guest_count_reacquired_additional,
				Sum(dmgdm.guest_count_reacquired_taste) as guest_count_reacquired_taste,
				Sum(dmgdm.guest_count_reacquired_intro) as guest_count_reacquired_intro,
		        Sum(dmgdm.guest_count_reacquired_fundraiser) as guest_count_reacquired_fundraiser,
		        Sum(dmgdm.guest_count_reacquired_delivered) as guest_count_reacquired_delivered,

				Sum(dmgdm.guest_count_new_regular) as guest_count_new_regular,
       			Sum(dmgdm.guest_count_new_additional) as guest_count_new_additional,
				Sum(dmgdm.guest_count_new_taste) as guest_count_new_taste,
				Sum(dmgdm.guest_count_new_intro) as guest_count_new_intro,
		        Sum(dmgdm.guest_count_new_fundraiser) as guest_count_new_fundraiser,
		        Sum(dmgdm.guest_count_new_delivered) as guest_count_new_delivered,
       
  		        Sum(dmgdm.guest_count_rsvp) as guest_count_rsvp,
		        Sum(dmgdm.guest_count_rsvp_upgraded) as guest_count_rsvp_upgraded,
		        Sum(dmgdm.unique_new_guest_count) as unique_new_guest_count,
		        Sum(dmgdm.unique_reacquired_guest_count) as unique_reacquired_guest_count,

				Sum(dmgdm.instore_signup_total) as instore_signup_total,
				Sum(dmgdm.instore_signup_existing_regular) as instore_signup_existing_regular,
       			Sum(dmgdm.instore_signup_existing_additional) as instore_signup_existing_additional,
				Sum(dmgdm.instore_signup_existing_taste) as instore_signup_existing_taste,
				Sum(dmgdm.instore_signup_existing_intro) as instore_signup_existing_intro,
		        Sum(dmgdm.instore_signup_existing_fundraiser) as instore_signup_existing_fundraiser,

				Sum(dmgdm.instore_signup_reacquired_regular) as instore_signup_reacquired_regular,
       			Sum(dmgdm.instore_signup_reacquired_additional) as instore_signup_reacquired_additional,
				Sum(dmgdm.instore_signup_reacquired_taste) as instore_signup_reacquired_taste,
				Sum(dmgdm.instore_signup_reacquired_intro) as instore_signup_reacquired_intro,
		        Sum(dmgdm.instore_signup_reacquired_fundraiser) as instore_signup_reacquired_fundraiser,

				Sum(dmgdm.instore_signup_new_regular) as instore_signup_new_regular,
       			Sum(dmgdm.instore_signup_new_additional) as instore_signup_new_additional,
				Sum(dmgdm.instore_signup_new_taste) as instore_signup_new_taste,
				Sum(dmgdm.instore_signup_new_intro) as instore_signup_new_intro,
		        Sum(dmgdm.instore_signup_new_fundraiser) as instore_signup_new_fundraiser,

				Avg(dmgdm.avg_servings_per_guest_all) as avg_servings_per_guest_all,
				Avg(dmgdm.avg_servings_per_guest_existing_regular) as avg_servings_per_guest_existing_regular,
       			Avg(dmgdm.avg_servings_per_guest_existing_additional) as avg_servings_per_guest_existing_additional,
				Avg(dmgdm.avg_servings_per_guest_existing_taste) as avg_servings_per_guest_existing_taste,
				Avg(dmgdm.avg_servings_per_guest_existing_intro) as avg_servings_per_guest_existing_intro,
		        Avg(dmgdm.avg_servings_per_guest_existing_fundraiser) as avg_servings_per_guest_existing_fundraiser,
		        Avg(dmgdm.avg_servings_per_guest_existing_delivered) as avg_servings_per_guest_existing_delivered,

				Avg(dmgdm.avg_servings_per_guest_reacquired_regular) as avg_servings_per_guest_reacquired_regular,
       			Avg(dmgdm.avg_servings_per_guest_reacquired_additional) as avg_servings_per_guest_reacquired_additional,
				Avg(dmgdm.avg_servings_per_guest_reacquired_taste) as avg_servings_per_guest_reacquired_taste,
				Avg(dmgdm.avg_servings_per_guest_reacquired_intro) as avg_servings_per_guest_reacquired_intro,
		        Avg(dmgdm.avg_servings_per_guest_reacquired_fundraiser) as avg_servings_per_guest_reacquired_fundraiser,
		        Avg(dmgdm.avg_servings_per_guest_reacquired_delivered) as avg_servings_per_guest_reacquired_delivered,

				Avg(dmgdm.avg_servings_per_guest_new_regular) as avg_servings_per_guest_new_regular,
       			Avg(dmgdm.avg_servings_per_guest_new_additional) as avg_servings_per_guest_new_additional,
				Avg(dmgdm.avg_servings_per_guest_new_taste) as avg_servings_per_guest_new_taste,
				Avg(dmgdm.avg_servings_per_guest_new_intro) as avg_servings_per_guest_new_intro,
		        Avg(dmgdm.avg_servings_per_guest_new_fundraiser) as avg_servings_per_guest_new_fundraiser,
		        Avg(dmgdm.avg_servings_per_guest_new_delivered) as avg_servings_per_guest_new_delivered,

				Sum(dmgdm.sessions_count_all) as sessions_count_all,
				Sum(dmgdm.sessions_count_regular) as sessions_count_regular,
				Sum(dmgdm.sessions_count_mfy) as sessions_count_mfy,
				Sum(dmgdm.sessions_count_mfy_walk_in) as sessions_count_mfy_walk_in,
				Sum(dmgdm.sessions_count_mfy_community_pickup) as sessions_count_mfy_community_pickup,
       			Sum(dmgdm.sessions_count_mfy_delivery) as sessions_count_mfy_delivery,
				Sum(dmgdm.sessions_count_taste) as sessions_count_taste,
				Sum(dmgdm.sessions_count_fundraiser) as sessions_count_fundraiser,
				Sum(dmgdm.sessions_count_delivered) as sessions_count_delivered,

				Sum(dmgdm.orders_count_all) as orders_count_all,
				Sum(dmgdm.orders_count_regular) as orders_count_regular,
       			Sum(dmgdm.orders_count_additional) as orders_count_additional,
				Sum(dmgdm.orders_count_mfy) as orders_count_mfy,
       			Sum(dmgdm.orders_count_mfy_delivery) as orders_count_mfy_delivery,
       			Sum(dmgdm.orders_count_mfy_walk_in) as orders_count_mfy_walk_in,
       			Sum(dmgdm.orders_count_mfy_community_pickup) as orders_count_mfy_community_pickup,
				Sum(dmgdm.orders_count_taste) as orders_count_taste,
				Sum(dmgdm.orders_count_fundraiser) as orders_count_fundraiser,
				Sum(dmgdm.orders_count_delivered) as orders_count_delivered,
				
				Sum(dmgdm.retention_count) as retention_count,
				Sum(dmgdm.converted_guests) as converted_guests,
				Avg(dmgdm.conversion_rate) as conversion_rate,
				Avg(dmgdm.average_annual_visits) as average_annual_visits,
				Sum(dmgdm.lost_guests_At_45_days) as lost_guests_at_45_days,
				Sum(dmgdm.orders_count_regular_existing_guests) as orders_count_regular_existing_guests,
				Sum(dmgdm.orders_count_regular_new_guests) as orders_count_regular_new_guests,
				Sum(dmgdm.orders_count_regular_reacquired_guests) as orders_count_regular_reacquired_guests,
       			Sum(dmgdm.orders_count_additional_existing_guests) as orders_count_additional_existing_guests,
				Sum(dmgdm.orders_count_additional_new_guests) as orders_count_additional_new_guests,
				Sum(dmgdm.orders_count_additional_reacquired_guests) as orders_count_additional_reacquired_guests,
				Sum(dmgdm.orders_count_intro_existing_guests) as orders_count_intro_existing_guests,
				Sum(dmgdm.orders_count_intro_new_guests) as orders_count_intro_new_guests,
				Sum(dmgdm.orders_count_intro_reacquired_guests) as orders_count_intro_reacquired_guests,
				Sum(dmgdm.orders_count_taste_existing_guests) as orders_count_taste_existing_guests,
				Sum(dmgdm.orders_count_taste_new_guests) as orders_count_taste_new_guests,
				Sum(dmgdm.orders_count_taste_reacquired_guests) as orders_count_taste_reacquired_guests,

		        Sum(dmgdm.orders_count_fundraiser_existing_guests) as orders_count_fundraiser_existing_guests,
				Sum(dmgdm.orders_count_fundraiser_new_guests) as orders_count_fundraiser_new_guests,
				Sum(dmgdm.orders_count_fundraiser_reacquired_guests) as orders_count_fundraiser_reacquired_guests,
		        Sum(dmgdm.orders_count_delivered_existing_guests) as orders_count_delivered_existing_guests,
				Sum(dmgdm.orders_count_delivered_new_guests) as orders_count_delivered_new_guests,
				Sum(dmgdm.orders_count_delivered_reacquired_guests) as orders_count_delivered_reacquired_guests


				FROM
				dashboard_metrics_guests_by_menu dmgdm
				$joinClause
				WHERE
				date = '$month' and dmgdm.guest_count_total > 0 group by date");

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

?>