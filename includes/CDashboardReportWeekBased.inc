<?php

require_once('CDreamReport.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/DAO/Dashboard_metrics_guests_by_week.php');

require_once('includes/CDashboardReport.inc');

class CDashboardWeekBased
{

	static function updateGrowthScoreCard($store_id, $curWeek, $curYear, $cutoffTime)
	{

		// current Month
		$weekObj = new DAO();

		$weekObj->query("select  m.menu_name, m.id as this_menu_id, WEEK(s.session_start, 3) as week_num, count(distinct b.user_id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list from session s
            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
            join orders o on o.id = b.order_id and o.servings_total_count > 35
            join menu m on m.id = s.menu_id
            where WEEK(s.session_start, 3) >= $curWeek and YEAR(s.session_start) = $curYear
            and s.store_id = $store_id and s.session_start < '$cutoffTime'
            group by WEEK(s.session_start, 3)
            order by s.session_start");

		if ($weekObj->fetch())
		{
			$last_month_menu_id = $weekObj->this_menu_id - 1;
			$lastMonth = new DAO();
			$lastMonth->query("select distinct b.user_id from session s
                join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$weekObj->guest_list})
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

			$guests_last_month = $lastMonth->N;

			$next_month_menu_id = $weekObj->this_menu_id + 1;
			$nextMonth = new DAO();
			$nextMonth->query("select distinct b.user_id from session s
                join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$weekObj->guest_list})
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

			$guests_next_month = $nextMonth->N;

			$growthValue = $guests_next_month - $guests_last_month;
		}
		else
		{
			$growthValue = 0;
		}

		$arr = self::getWeekTimeData(false, $curWeek, $curYear);
		$startDate = $arr['week_start'];
		$endDate = $arr['week_end'];
		$menu_id = $arr['menu_id'];
		$quarter = $arr['quarter'];
		$quarter_week = $arr['quarter_week'];
		$week_number = $arr['week_number'];
		$calc_year = $arr['year'];

		$Metric = DAO_CFactory::create('dashboard_metrics_guests_by_week');
		$Metric->start_date = $startDate;
		$Metric->end_date = $endDate;
		$Metric->menu_id = $menu_id;
		$Metric->store_id = $store_id;
		$Metric->quarter = $quarter;
		$Metric->quarter_week = $quarter_week;
		$Metric->week_number = $week_number;
		$Metric->year = $calc_year;

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
	}

	static function getFullActiveDateRange()
	{
		$now = date("Y-m-d");
		$menuObj = new CMenu();
		$menuObj->query("SELECT * FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
		$menuObj->fetch();
		$curMenuID = $menuObj->id;

		$SpecialperiodEndTS = strtotime(date("Y-m-08", strtotime($menuObj->menu_start)));

		if (time() < $SpecialperiodEndTS)
		{
			// must also build previous menu weeks
			$previousMenuID = $curMenuID - 1;
			$daoPrevMenu = DAO_CFactory::create('menu');
			$daoPrevMenu->id = $previousMenuID;
			$daoPrevMenu->find(true);
			$periodStartTS = strtotime($daoPrevMenu->global_menu_end_date);
		}
		else
		{
			$periodStartTS = strtotime($menuObj->global_menu_start_date);
		}

		$menuObj2 = new CMenu();
		$menuObj2->query("SELECT id, global_menu_end_date FROM menu ORDER BY id desc  LIMIT 1");
		$menuObj2->fetch();

		$periodEndTS = strtotime($menuObj2->global_menu_end_date);

		return array(
			$periodStartTS,
			$periodEndTS
		);
	}

	static function getQuarterWeek($weekNumber, $year)
	{
		$menu = new CMenu();
		$menu->findForMonthAndYear(1, $year);
		if ($menu->fetch())
		{
			$yearStart = $menu->global_menu_start_date;
			$dateDT = new DateTime($yearStart);
			$firstWeekNum = $dateDT->format('W');

			if ($firstWeekNum == 53)
			{
				$firstWeekNum = 1;
			}

			$interval = $weekNumber - $firstWeekNum;

			$retVal = ($interval % 13) + 1;

			return $retVal;
		}

		return 0;
	}

	static function getWeekTimeData($date = false, $weekNumber = false, $year = false)
	{
		$ret = array(
			'week_start' => false,
			'week_end' => false,
			'menu_id' => false,
			'quarter' => false,
			'quarter_week' => false,
			'week_number' => false,
			'year' => false
		);

		if ($date)
		{
			$dateDT = new DateTime($date);
			$weekNumber = $dateDT->format("W");
			$ret['week_number'] = $weekNumber;
			$year = $dateDT->format("o"); // ??? Use "o"?
			$ret['year'] = $year;
		}

		$weekDate = new DateTime();
		$weekDate->setISODate($year, $weekNumber);

		$ret['week_start'] = $weekDate->format('Y-m-d 00:00:00');
		$weekDate->modify('+7 days');
		$ret['week_end'] = $weekDate->format('Y-m-d 00:00:00');

		$tempDate = (!empty($date) ? $date : $ret['week_start']);

		$menuArr = CMenu::getMenuByDate(date("Y-m-d", strtotime($tempDate)));

		$ret['menu_id'] = $menuArr['id'];
		$month = date("n", strtotime($menuArr['menu_start']));
		$ret['quarter'] = floor(($month - 1) / 3) + 1;
		$ret['quarter_week'] = self::getQuarterWeek($weekNumber, $year);
		$ret['week_number'] = $weekNumber;
		$ret['year'] = $year;

		return $ret;
	}

	static function updateGuestMetrics($store_id, $week, $year)
	{

		$arr = self::getWeekTimeData(false, $week, $year);
		$startDate = $arr['week_start'];
		$endDate = $arr['week_end'];
		$menu_id = $arr['menu_id'];
		$quarter = $arr['quarter'];
		$quarter_week = $arr['quarter_week'];
		$week_number = $arr['week_number'];
		$calc_year = $arr['year'];

		$Metric = DAO_CFactory::create('dashboard_metrics_guests_by_week');
		$Metric->start_date = $startDate;
		$Metric->end_date = $endDate;
		$Metric->menu_id = $menu_id;
		$Metric->store_id = $store_id;
		$Metric->quarter = $quarter;
		$Metric->quarter_week = $quarter_week;
		$Metric->week_number = $week_number;
		$Metric->year = $calc_year;

		$isCurrentMonth = false;

		$curMenuID = CMenu::getCurrentMenuId();

		if ($curMenuID == $menu_id)
		{
			$isCurrentMonth = true;
		}

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->id = $menu_id;
		if (!$MenuObj->find(true))
		{
			CLog::RecordIntense("Menu not found in updateGuestMetrics", "ryan.snook@dreamdinners.com");
		}

		$interval = 7;

		$Metrics_Exist = false;
		if ($Metric->find(true))
		{
			$Metrics_Exist = true;
			$beforeState = clone($Metric);
		}

		$tempArr = explode(" ", $startDate);
		$justDate = $tempArr[0];

		$sales = CDashboardMenuBased::getGrossSales($store_id, $justDate, 7);

		if (!isset($sales) || !isset($sales['total_less_discounts']))
		{
			$sales['total_less_discounts'] = 0;
		}

		$Metric->total_agr = $sales['total_less_discounts'];

		// get guest_counts

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
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
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
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

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
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)) as iq");

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
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
				$currentMonthClause
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

		$Sessions->query("select iq.session_type, count(iq.id) as theCount from (
                        select s.id, s.session_type from session s
                        join booking b on b.session_id = s.id and b.is_deleted = 0 and b.status = 'ACTIVE'
                        where s.is_deleted = 0 and s.store_id = $store_id
                        and s.session_start >= '$startDate' and s.session_start < DATE_ADD('$startDate', INTERVAL 7 DAY)
                        and s.session_publish_state <> 'SAVED' 
                        GROUP BY s.id) as iq
                        GROUP BY iq.session_type");

		$Metric->sessions_count_all = 0;
		$Metric->sessions_count_regular = 0;
		$Metric->sessions_count_mfy = 0;
		$Metric->sessions_count_taste = 0;
		$Metric->sessions_count_fundraiser = 0;

		while ($Sessions->fetch())
		{
			$Metric->sessions_count_all += $Sessions->theCount;

			if ($Sessions->session_type == 'STANDARD')
			{
				$Metric->sessions_count_regular = $Sessions->theCount;
			}
			else if ($Sessions->session_type == 'SPECIAL_EVENT')
			{
				$Metric->sessions_count_mfy += $Sessions->theCount;
			}
			else if ($Sessions->session_type == 'DREAM_TASTE')
			{
				$Metric->sessions_count_taste += $Sessions->theCount;
			}
			else if ($Sessions->session_type == 'FUNDRAISER')
			{
				$Metric->sessions_count_fundraiser += $Sessions->theCount;
			}
		}

		// Orders per session type
		$digest4 = DAO_CFactory::create('orders_digest');
		$digest4->query("select count(id) AS total,
				count(if(od.session_type = 'TASTE', 1, null)) as taste_count,
				count(if(od.session_type = 'STANDARD', 1, null)) as std_count,
				count(if(od.session_type = 'MADE_FOR_YOU', 1, null)) as mfy_count,
				count(if(od.session_type = 'FUNDRAISER', 1, null)) as fundraiser_count
				from orders_digest od
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)");

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
				where od.is_deleted = 0 and od.store_id = $store_id
				and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
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
		// version 3

		$digest5 = DAO_CFactory::create('orders_digest');

		$Metric->converted_guests = $Metric->instore_signup_new_regular + $Metric->instore_signup_new_intro + $Metric->instore_signup_new_taste + $Metric->instore_signup_new_fundraiser + $Metric->instore_signup_reacquired_intro + $Metric->instore_signup_reacquired_taste + $Metric->instore_signup_reacquired_regular + $Metric->instore_signup_reacquired_fundraiser;

		$digest5->query("select count(distinct od.user_id) as to_date from orders_digest od
							where od.is_deleted = 0 and od.store_id = $store_id
								and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
								and od.session_time < now()
								and (od.user_state = 'NEW' or od.user_state = 'REACQUIRED')");

		$digest5->fetch();

		// Conversion rate
		$totalNewGuest = $digest5->to_date;

		$Metric->conversion_rate = ($totalNewGuest != 0 ? $Metric->converted_guests / $totalNewGuest : 0);

		//	$Metric->conversion_rate *= 100;

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

		// retention count
		$digest12 = DAO_CFactory::create('orders_digest');
		$thisMenuID = $MenuObj->id;
		$nextMenuID = $thisMenuID + 1;
		$digest12->query("select iq.* from (
			select distinct od.user_id from orders_digest od
			join booking b on b.order_id = od.order_id and b.status = 'ACTIVE'
			join session s on s.id = b.session_id and s.menu_id = $thisMenuID
			where od.is_deleted = 0 and od.store_id = $store_id and od.order_type = 'REGULAR' and od.user_state = 'EXISTING') as iq
			join booking b2 on b2.user_id = iq.user_id and b2.status = 'ACTIVE'
			join session s2 on s2.id = b2.session_id and s2.menu_id = $nextMenuID
			join orders o on o.id = b2.order_id and o.type_of_order = 'STANDARD'");

		$Metric->retention_count = $digest12->N;

		// average ticket regular

		$digest401 = DAO_CFactory::create('orders_digest');
		$digest401->query("select od.order_type as type, avg(o.grand_total - o.subtotal_all_taxes) as avg_ticket_reg from orders_digest od
		    join orders o on o.id = od.order_id and o.servings_total_count >= 36
		    where od.is_deleted = 0 and od.store_id = $store_id
		    and od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY)
		    and od.order_type = 'REGULAR'
		    group by od.order_type");
		$digest401->fetch();

		if (empty($digest401->avg_ticket_reg))
		{
			$digest401->avg_ticket_reg = 0;
		}

		$Metric->average_standard_ticket = $digest401->avg_ticket_reg;

		$Metric->lifestyle_guest_count = self::getLifestyleGuestCount($store_id, $startDate, $interval, $endDate);

		$digest402 = DAO_CFactory::create('orders_digest');
		$digest402->query("select sum(iq.total_cost) as total_food_cost from (
		    select od.order_id, oi.menu_item_id, mi.food_cost as cost, mi.food_cost * oi.item_count as total_cost, oi.item_count from orders_digest od
		    join order_item oi on od.order_id = oi.order_id and oi.is_deleted = 0
		    join menu_item mi on mi.id = oi.menu_item_id
		    where od.session_time >= '$startDate' and od.session_time < DATE_ADD('$startDate', INTERVAL $interval DAY) and od.is_deleted = 0 and od.store_id = $store_id) as iq");

		$digest402->fetch();

		$Metric->total_food_cost = $digest402->total_food_cost;

		if ($Metrics_Exist)
		{
			$Metric->update($beforeState);
		}
		else
		{
			$Metric->insert();
		}
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

}

?>