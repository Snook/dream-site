<?php

require_once 'DAO/Store_expenses.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CStoreExpenses
 *  Author: LHOOK
 *
 * -------------------------------------------------------------------------------------------------- */

// CStoreExpenses::FREE_MEAL_EMPLOYEE_MEAL_PRICE
class CStoreExpenses extends DAO_Store_expenses
{
	//const FREE_MEAL_EMPLOYEE_MEAL_PRICE = 8.00;

	//const FREE_MEAL_EMPLOYEE_MEAL_PRICE_V2 = 9.25;

	const SYSCO = 'SYSCO';
	const OTHER = 'OTHER_FOOD';  // OTHER FOOD.. .misleading
	const EMPLOYEE = 'EMPLOYEE_MEALS';
	const FUNDRAISER = 'FUNDRAISER_DOLLARS';
	const PROMOS = 'FREE_MEAL_PROMOS';
	//const ADDITIONAL_MEALS_SOLD = 'ADDITIONAL_MEALS_SOLD';
	const LABOR = 'LABOR';
	const ESCRIP = 'ESCRIP_PAYMENTS';
	const ADJUSTMENTS = 'SALES_ADJUSTMENTS';

	const SNEAK_PEEK_HELD = 'SNEAK_PEEK_HELD';
	const SNEAK_PEEK_TOTAL_GUESTS = 'SNEAK_PEEK_TOTAL_GUESTS';
	const SNEAK_PEEK_SIGNUPS = 'TOTAL_SIGN_UPS';

	const ENTRY_DAILY = 'daily';
	const ENTRY_WEEK = 'weekly';

	function __construct()
	{
		parent::__construct();
	}

	static function getEmployeeMealPrice($incomingdate)
	{
		$employeeChangePrice = mktime(0, 0, 0, 1, 1, 2008);
		$employeeChangePrice2 = mktime(0, 0, 0, 6, 1, 2008);

		if ($incomingdate < $employeeChangePrice)
		{
			return 8.00;
		}
		else if ($incomingdate < $employeeChangePrice2)
		{
			return 9.75;
		}
		else
		{
			return 12.25;
		}
	}

	static function getTypes()
	{
		// removed CStoreExpenses::ADDITIONAL_MEALS_SOLD per Cristen's suggestion 0412
		$arraylist = array(
			CStoreExpenses::SYSCO,
			CStoreExpenses::OTHER,
			CStoreExpenses::LABOR,
			CStoreExpenses::EMPLOYEE,
			CStoreExpenses::FUNDRAISER,
			CStoreExpenses::PROMOS,
			CStoreExpenses::ESCRIP,
			CStoreExpenses::ADJUSTMENTS,
			CStoreExpenses::SNEAK_PEEK_HELD,
			CStoreExpenses::SNEAK_PEEK_TOTAL_GUESTS,
			CStoreExpenses::SNEAK_PEEK_SIGNUPS
		);

		return $arraylist;
	}

	static function getPricingDetails($incomingTS)
	{
		// removed CStoreExpenses::ADDITIONAL_MEALS_SOLD per Cristen's suggestion 0412

		$pricingvalue = CStoreExpenses::getEmployeeMealPrice($incomingTS);

		$arraylist = array(
			CStoreExpenses::SYSCO => 0,
			CStoreExpenses::OTHER => 0,
			CStoreExpenses::LABOR => 0,
			CStoreExpenses::EMPLOYEE => $pricingvalue,
			CStoreExpenses::FUNDRAISER => 0,
			CStoreExpenses::PROMOS => $pricingvalue,
			CStoreExpenses::ESCRIP => 0,
			CStoreExpenses::ADJUSTMENTS => 0,
			CStoreExpenses::SNEAK_PEEK_HELD => -1,
			CStoreExpenses::SNEAK_PEEK_TOTAL_GUESTS => -1,
			CStoreExpenses::SNEAK_PEEK_SIGNUPS => -1
		);

		return $arraylist;
	}

	function findExpenseDataByMonth($storeid, $Day, $Month, $Year, $interval)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varstr = "Select store_expenses.entry_type,store_expenses.expense_type,SUM(store_expenses.units) as units,SUM(store_expenses.total_cost) as cost From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")
			and store_id = " . $storeid . " and store_expenses.is_deleted = 0 group by entry_type, expense_type order by entry_type, expense_type";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$data[$arr['expense_type']] = array(
				'entry_type' => $arr['entry_type'],
				'total_cost' => $arr['cost'],
				'units' => $arr['units']
			);
		}

		return $data;
	}

	function findExpenseDataByMonthByStore($Day, $Month, $Year, $interval, $storeList = false)
	{
		$storeFilter = "";
		if ($storeList)
		{
			$storeFilter = " and store_expenses.store_id in ($storeList) ";
		}

		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varstr = "Select store_id, store_expenses.entry_type,store_expenses.expense_type,SUM(store_expenses.units) as units,SUM(store_expenses.total_cost) as cost From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")
			and  store_expenses.is_deleted = 0 $storeFilter group by store_id,entry_type, expense_type order by entry_type, expense_type";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$data[$arr['store_id']][$arr['expense_type']] = array(
				'entry_type' => $arr['entry_type'],
				'total_cost' => $arr['cost'],
				'units' => $arr['units']
			);
		}

		return $data;
	}

	function findPromoItems($store_id, $Day, $Month, $Year, $interval = '1 DAY', $grouping = 'NONE')
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "";
		if (!empty($store_id) && is_numeric($store_id))
		{
			$storeClause = " and store_expenses.store_id = " . $store_id . "  ";
		}

		$varstr = "Select sum(units) as counter, expense_type  ";
		if ($grouping == "WEEK")
		{
			$varstr .= " , WEEK(store_expenses.entry_date) as Week_value";
		}

		$varstr .= " From store_expenses where (store_expenses.expense_type = '" . CStoreExpenses::EMPLOYEE . "' OR store_expenses.expense_type = '" . CStoreExpenses::PROMOS . "')";
		$varstr .= " and store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";
		$varstr .= $storeClause . " and store_expenses.is_deleted = 0 ";
		if ($grouping == "NONE")
		{
			$varstr .= "group by expense_type order by expense_type";
		}
		else if ($grouping == "WEEK")
		{
			$varstr .= "group by Week_value, expense_type order by Week_value, expense_type";
		}
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);
		while ($store_expenses->fetch())
		{
			if ($grouping == "NONE")
			{
				$data[$store_expenses->expense_type] = $store_expenses->counter;
			}
			else if ($grouping == "WEEK")
			{
				$data[$store_expenses->Week_value][$store_expenses->expense_type] = $store_expenses->counter;
			}
		}

		return $data;
	}

	function findExpenseDataForMonthSetWeekID($storeid, $Day, $Month, $Year, $interval)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$counter = 0;
		$varstr = "Select WEEK(store_expenses.entry_date, 0) as week,store_expenses.entry_type,store_expenses.expense_type,SUM(store_expenses.units) as units,SUM(store_expenses.total_cost) as cost From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")
			and store_id = " . $storeid . " and store_expenses.is_deleted = 0 group by week, entry_type, expense_type order by entry_type, expense_type";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$data[$counter++] = array(
				'week' => 0,
				'expense_type' => $arr['expense_type'],
				'entry_type' => $arr['entry_type'],
				'total_cost' => $arr['cost'],
				'units' => $arr['units']
			);
		}

		return $data;
	}

	function findExpenseDataByWeek($storeid, $Day, $Month, $Year, $interval)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$counter = 0;
		$varstr = "Select WEEK(store_expenses.entry_date, 0) as week,store_expenses.entry_type,store_expenses.expense_type,SUM(store_expenses.units) as units,SUM(store_expenses.total_cost) as cost From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")
			and store_id = " . $storeid . " and store_expenses.is_deleted = 0 group by week, entry_type, expense_type order by week, entry_type, expense_type";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$data[$counter++] = array(
				'week' => $arr['week'],
				'expense_type' => $arr['expense_type'],
				'entry_type' => $arr['entry_type'],
				'total_cost' => $arr['cost'],
				'units' => $arr['units']
			);
		}

		return $data;
	}

	function findExpenseData($storeid, $Day, $Month, $Year, $interval, $format = false)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$arr = null;
		$varstr = "Select store_expenses.id,store_expenses.store_id,store_expenses.entry_date,store_expenses.entry_type,
			store_expenses.expense_type,store_expenses.notes,store_expenses.units,store_expenses.total_cost
			From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") and store_id = " . $storeid . " and store_expenses.is_deleted = 0 order by entry_date, id DESC";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$ts = date("n-j-Y", strtotime($arr['entry_date']));
			if ($format == true)
			{
				$newEntity = array(
					'timestamp' => $ts,
					'id' => $arr['id'],
					'entry_type' => $arr['entry_type'],
					'total_cost' => $arr['total_cost'],
					'units' => $arr['units'],
					'notes' => $arr['notes']
				);
				$data[$arr['expense_type']] = $newEntity;
			}
			else
			{
				$newEntity = array(
					'id' => $arr['id'],
					'expense_type' => $arr['expense_type'],
					'entry_type' => $arr['entry_type'],
					'total_cost' => $arr['total_cost'],
					'units' => $arr['units'],
					'notes' => $arr['notes']
				);
				if (count($data) > 0 && isset($data[$ts]))
				{
					$varlen = count($data[$ts]);
					$data[$ts][$varlen] = $newEntity;
				}
				else
				{
					$data[$ts][0] = $newEntity;
				}
			}
		}

		return $data;
	}

	function storeAdjustment($adjustment, $note = false)
	{
		if (empty($this->session_id) || !is_numeric($this->session_id))
		{
			return array(
				'processor_success' => false,
				'processor_message' => 'The session_id parameter is missing or corrupt.'
			);
		}

		$SessionObj = DAO_CFactory::create('session');
		$SessionObj->id = $this->session_id;
		$SessionObj->find(true);

		$date_in_org_format = $this->entry_date;
		$date = date("Y-m-d", strtotime($this->entry_date));
		$month = date("n", strtotime($this->entry_date));
		$year = date("Y", strtotime($this->entry_date));

		$adjustment = (!empty($adjustment) ? $adjustment : 0);
		$note = (!empty($note) ? $note : 'null');

		if (!is_numeric($adjustment))
		{
			return array(
				'processor_success' => false,
				'processor_message' => 'The ' . $this->expense_type . ' parameter is missing or corrupt.'
			);
		}

		if ($this->find(true))
		{
			$store_expenses_org = $this->cloneObj(false);

			if (empty($adjustment))
			{
				$adjustment = 0;
			}
			if ($adjustment != $this->total_cost)
			{
				$delta = $adjustment - $this->total_cost;

				//revenue event
				$revenueEvent = DAO_CFactory::create('revenue_event');
				$revenueEvent->event_type = $this->expense_type;
				$revenueEvent->event_time = date("Y-m-d H:i:s");
				$revenueEvent->store_id = $this->store_id;
				$revenueEvent->menu_id = $SessionObj->menu_id;
				$revenueEvent->amount = $delta;
				$revenueEvent->session_amount = $delta;
				$revenueEvent->session_id = $this->session_id;
				$revenueEvent->final_session_id = $this->session_id;
				$revenueEvent->order_id = 'null';

				$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($SessionObj->session_start));
				$revenueEvent->negative_affected_month = 'null';

				$revenueEvent->insert();
			}

			if (!empty($adjustment) || (!empty($note) && $note != 'null'))
			{
				$note = stripslashes($note); // cleaned by DAO
				if ($note != 'null')
				{
					$this->notes = $note;
				}

				// update
				$this->total_cost = $adjustment;

				$this->update($store_expenses_org);
			}
			else
			{
				$this->total_cost = $adjustment;
				$this->notes = 'NULL';

				$this->update($store_expenses_org);
			}
		}
		else if (!empty($adjustment) || (!empty($note) && $note != 'null'))
		{
			// insert
			$this->total_cost = $adjustment;
			$this->notes = $note;
			$this->insert();

			//revenue event
			$revenueEvent = DAO_CFactory::create('revenue_event');
			$revenueEvent->event_type = $this->expense_type;
			$revenueEvent->event_time = date("Y-m-d H:i:s");
			$revenueEvent->store_id = $this->store_id;
			$revenueEvent->menu_id = $SessionObj->menu_id;
			$revenueEvent->amount = $adjustment;
			$revenueEvent->session_amount = $adjustment;
			$revenueEvent->session_id = $this->session_id;
			$revenueEvent->final_session_id = $this->session_id;
			$revenueEvent->order_id = 'null';

			$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($SessionObj->session_start));
			$revenueEvent->negative_affected_month = 'null';

			$revenueEvent->insert();
		}

		return array(
			'processor_success' => true,
			'processor_message' => 'The expenses were recorded.',
			'date' => $date_in_org_format,
			'month' => $month,
			'year' => $year,
			'session_id' => $this->session_id,
			'entries' => array($this->expense_type => array(
				'type' => $this->expense_type,
				'amount' => $adjustment,
				'notes' => $note
			))
		);
	}
}
?>