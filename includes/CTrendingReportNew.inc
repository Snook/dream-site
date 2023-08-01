<?php

require_once('CDreamReport.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/CDashboardReport.inc');


class CTrendingReportNew
{

	// check
	static function getAGRTrendingDataForStore($store_id, $useCalendarMonth = false)
	{
		
		$tableName = 'dashboard_metrics_agr_by_menu';
		if ($useCalendarMonth)
		{
			$tableName = 'dashboard_metrics_agr';
		}
		
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		$lastYearTime = mktime(0,0,0, $thisMonth-2, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create($tableName);

		$AGRMetrics->query("select dma.date,
			dma.store_id,
			dma.total_agr,
			dma_prev.date as prev_date,
			dma_prev.total_agr as prev_agr,
			dma.total_agr - dma_prev.total_agr as diff,
			(dma.total_agr - dma_prev.total_agr) / dma_prev.total_agr as percent_diff,
			dma.avg_ticket_regular,
			dma.addon_sales_total,
			dma.addon_sales_total / dma_prev.total_agr as percent_addon_of_total,
			dma.avg_ticket_all
			from $tableName dma
			left join $tableName dma_prev on dma_prev.store_id = $store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			where dma.date > '$lastYear' and dma.date < '$thisMonthDate' and dma.store_id = $store_id order by dma.date desc");

		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}

	// check
	static function getAGRTrendingDataHomeOfficeRollup($filter = 'all_stores', $trade_area_id = 0, $includeInactiveStores = false, $useCalendarMonth = false)
	{
		
		$tableName = 'dashboard_metrics_agr_by_menu';
		if ($useCalendarMonth)
		{
			$tableName = 'dashboard_metrics_agr';
		}
		
		$activeClause = "";
		if (!$includeInactiveStores)
		{
			$activeClause = "and st.active = 1";
		}

		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id $activeClause and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dma.store_id $activeClause and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dma.store_id $activeClause  join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0  and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else
		{
			$joinClause = " join store st on st.id = dma.store_id $activeClause  and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}

		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		
		$lastYearTime = mktime(0,0,0, $thisMonth-2, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$retVal = array();

		$AGRMetrics = DAO_CFactory::create($tableName);

		$AGRMetrics->query("select dma.date,
			count(distinct dma.store_id) as num_stores,
			sum(dma.total_agr) as total_agr,
			dma_prev.date as prev_date,
			sum(dma_prev.total_agr) as prev_agr,
			sum(dma.total_agr) - sum(dma_prev.total_agr) as diff,
			(sum(dma.total_agr) - sum(dma_prev.total_agr)) / sum(dma_prev.total_agr) as percent_diff,
			avg(dma.avg_ticket_regular) as avg_ticket_regular,
			sum(dma.addon_sales_total) as addon_sales_total ,
			sum(dma.addon_sales_total) / sum(dma_prev.total_agr) as percent_addon_of_total,
			avg(dma.avg_ticket_all) as avg_ticket_all
			
			from $tableName dma
			left join $tableName dma_prev on dma_prev.store_id = dma.store_id and dma_prev.date = DATE_SUB(dma.date,INTERVAL 1 YEAR)
			$joinClause
			where dma.date > '$lastYear' and dma.date < '$thisMonthDate'
			and dma.total_agr > 0
			group by dma.date order by dma.date desc");

			
		while ($AGRMetrics->fetch())
		{
			$retVal[$AGRMetrics->date] = $AGRMetrics->toArray();
		}

		return $retVal;
	}


// check --
	static function addcancelledOrdersForStore($store_id, &$rows, $useCalendarMonth = false)
	{
		
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		$lastYearTime = mktime(0,0,0, $thisMonth - 1, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');


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

// check
	static function addGuestTrendingDataForStore($store_id, &$rows, $useCalendarMonth = false)
	{
		
		$tableName = 'dashboard_metrics_guests_by_menu';
		if ($useCalendarMonth)
		{
			$tableName = 'dashboard_metrics_guests';
		}
		
		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);

		$lastYearTime = mktime(0,0,0, $thisMonth-2, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create($tableName);

		$GuestMetrics->query("select dmg.date,
				dmg.store_id,
				avg( (dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy)) as orders_per_session ,
				dmg.orders_count_all,
				dmg.guest_count_total,
				dmg_prev.guest_count_total as prev_guest_count_total,
				dmg.orders_count_mfy / dmg.orders_count_all as MFY_percent_of_total,
				dmg.guest_count_total / dmg.sessions_count_all as guests_per_session,
				dmg.total_servings_sold,
				dmg.total_servings_sold / dmg.guest_count_total as avg_servings_per_guest,
				(dmg.instore_signup_existing_regular + ifnull(dmg.instore_signup_existing_taste,0) + ifnull(dmg.instore_signup_existing_intro,0) + ifnull(dmg.instore_signup_existing_fundraiser,0)) as instore_signup_existing,
				(dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser) as instore_signup_new,
				(dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser) as instore_signup_reacquired,
				(dmg.guest_count_existing_regular + ifnull(dmg.guest_count_existing_taste,0) + ifnull(dmg.guest_count_existing_intro,0) + ifnull(dmg.guest_count_existing_fundraiser,0)) as guest_count_existing,
				(dmg.guest_count_new_regular + dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_fundraiser) as guest_count_new,
				(dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser) as guest_count_reacquired,
				dmg.instore_signup_total,
				dmg.conversion_rate,
				dmg.avg_servings_per_guest_regular
				from $tableName dmg
				left join $tableName dmg_prev on dmg_prev.store_id = $store_id and dmg_prev.date = DATE_SUB(dmg.date,INTERVAL 1 MONTH)
				where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' and dmg.store_id = $store_id group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}

	}

	// check
	static function addCancelledOrders(&$rows, $filter = 'all_stores', $trade_area_id = 0, $includeInactiveStores = false, $useCalendarMonth = false)
	{
		
		$tableName = 'dashboard_metrics_agr_by_menu';
		if ($useCalendarMonth)
		{
			$tableName = 'dashboard_metrics_agr';
		}
		
		$activeClause = "";
		if (!$includeInactiveStores)
		{
			$activeClause = "and st.active = 1";
		}
	
		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id $activeClause and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = s.store_id $activeClause and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = s.store_id $activeClause join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else
		{
			$joinClause = " join store st on st.id = s.store_id $activeClause and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}


		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		
		$lastYearTime = mktime(0,0,0, $thisMonth - 2, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

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

 // check
	static function addGuestTrendingHomeofficeRollups(&$rows, $filter = 'all_stores', $trade_area_id = 0, $includeInactiveStores = false, $useCalendarMonth = false)
	{
		
		$tableName = 'dashboard_metrics_guests_by_menu';
		if ($useCalendarMonth)
		{
			$tableName = 'dashboard_metrics_guests';
		}
		
		$activeClause = "";
		if (!$includeInactiveStores)
		{
			$activeClause = "and st.active = 1";
		}
		

		$joinClause = "";

		if ($filter == 'corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id $activeClause and st.is_corporate_owned = 1 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'non_corp_stores')
		{
			$joinClause = " join store st on st.id = dmg.store_id $activeClause and st.is_corporate_owned = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else if ($filter == 'region')
		{
			$joinClause = " join store st on st.id = dmg.store_id $activeClause join store_trade_area sta on sta.store_id = st.id and sta.trade_area_id = $trade_area_id  and sta.is_deleted = 0 and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}
		else
		{
			$joinClause = " join store st on st.id = dmg.store_id $activeClause and st.store_type <> 'DISTRIBUTION_CENTER' ";
		}


		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear);
		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		
		$lastYearTime = mktime(0,0,0, $thisMonth-2, 1, $thisYear - 1);
		$lastYear = date("Y-m-01", $lastYearTime);

		$GuestMetrics = DAO_CFactory::create($tableName);

		$GuestMetrics->query("select dmg.date,
				avg((dmg.orders_count_regular + dmg.orders_count_mfy) / (dmg.sessions_count_regular + dmg.sessions_count_mfy)) as orders_per_session,
				sum(dmg.orders_count_all) as orders_count_all,
				sum(dmg.guest_count_total) as guest_count_total,
				sum(dmg_prev.guest_count_total) as prev_guest_count_total,
				sum(dmg.orders_count_mfy) / sum(dmg.orders_count_all) as MFY_percent_of_total,
				sum(dmg.guest_count_total) / sum(dmg.sessions_count_all) as guests_per_session,
				sum(dmg.total_servings_sold) as total_servings_sold,
				sum(dmg.total_servings_sold) / sum(dmg.guest_count_total) as avg_servings_per_guest,
						
				(sum(dmg.guest_count_existing_regular) + sum(ifnull(dmg.guest_count_existing_taste,0)) + sum(ifnull(dmg.guest_count_existing_intro, 0))) as guest_count_existing,
				(sum(dmg.instore_signup_existing_regular) + sum(ifnull(dmg.instore_signup_existing_taste,0)) + sum(ifnull(dmg.instore_signup_existing_intro,0)) + sum(ifnull(dmg.instore_signup_existing_fundraiser,0))) as instore_signup_existing,
				(sum(dmg.guest_count_new_regular) + sum(dmg.guest_count_new_taste) + sum(dmg.guest_count_new_intro) + sum(dmg.guest_count_new_fundraiser)) as guest_count_new,
			
				(sum(dmg.instore_signup_new_regular) + sum(dmg.instore_signup_new_taste) + sum(dmg.instore_signup_new_intro) + sum(dmg.instore_signup_new_fundraiser)) as instore_signup_new,
				(sum(dmg.guest_count_reacquired_regular) + sum(dmg.guest_count_reacquired_taste) + sum(dmg.guest_count_reacquired_intro) + sum(dmg.guest_count_reacquired_fundraiser)) as guest_count_reacquired,
				(sum(dmg.instore_signup_reacquired_regular) + sum(dmg.instore_signup_reacquired_taste) + sum(dmg.instore_signup_reacquired_intro) + sum(dmg.instore_signup_reacquired_fundraiser)) as instore_signup_reacquired,
			    sum(dmg.instore_signup_total) as instore_signup_total,
				avg(dmg.conversion_rate) as conversion_rate,
				avg(dmg.avg_servings_per_guest_regular) as avg_servings_per_guest_regular
				from $tableName dmg
			
				left join $tableName dmg_prev on dmg_prev.store_id = dmg.store_id and dmg_prev.date = DATE_SUB(dmg.date,INTERVAL 1 MONTH)
			
				$joinClause
				where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' 
			 and dmg.guest_count_total > 0
			group by dmg.date order by dmg.date desc");

		while ($GuestMetrics->fetch())
		{
			if (isset($rows[$GuestMetrics->date]))
			{
				$rows[$GuestMetrics->date] = array_merge($rows[$GuestMetrics->date], $GuestMetrics->toArray());
			}
		}

	}

}

?>