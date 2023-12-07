<?php
/*
 * Created on August 08, 2011
 * project_name guestCarryoverNotes
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/Store_weekly_data.php");
require_once("includes/DAO/BusinessObject/CStoreExpenses.php");

class processor_admin_expensesData extends CPageProcessor
{
	function runFranchiseManager()
	{
		$this->update();
	}

	function runFranchiseOwner()
	{
		$this->update();
	}

	function runOpsLead()
	{
		$this->update();
	}

	function runHomeOfficeStaff()
	{
		CAppUtil::processorMessageEcho(array(
			'processor_success' => false,
			'processor_message' => 'No permission'
		));
	}

	function runHomeOfficeManager()
	{
		$this->update();
	}

	function runSiteAdmin()
	{
		$this->update();
	}

	function update()
	{
		// Process post
		if (isset($_POST['store_id']) && is_numeric($_POST['store_id']) && isset($_POST['date']) && (isset($_POST['op'])))
		{
			switch ($_POST['op'])
			{
				case 'load':
					$this->loadExpensesDataForDate($_POST['date'], $_POST['store_id']);
					break;
				case 'store':
					$this->storeExpensesDataForDate($_POST['date'], $_POST['store_id']);
					break;
				case 'retrieveForMonth':
					$this->retrieveStoreExpensesForMonth($_POST['date'], $_POST['store_id']);
					break;
				case 'load_adjustment':
					$this->loadAdjustmentForDateAndSession($_POST['date'], $_POST['store_id']);
					break;
				case 'store_adjustment':
					$this->storeAdjustmentForDateAndSession($_POST['date'], $_POST['store_id'], $_POST['session_id'], $_POST['sales_adjustment'], $_POST['sales_adjustment_note'], $_POST['fundraising'], $_POST['fundraising_note']);
					break;
				default:
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Invalid operation parameter'
					));
			}
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'Invalid parameter'
			));
		}
	}

	function retrieveStoreExpensesForMonth($date, $store_id)
	{

		$dateParts = explode("-", $date);

		$month = $dateParts[0];
		$year = $dateParts[2];

		$food_costs = array();
		$dataObj = DAO_CFactory::create('store_expenses');

		$dataObj->query("select WEEK(store_expenses.entry_date) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
				From store_expenses Where MONTH(store_expenses.entry_date) = $month and YEAR(store_expenses.entry_date) = $year and store_id = $store_id and store_expenses.is_deleted = 0 and
				store_expenses.expense_type in ('SYSCO', 'OTHER_FOOD') order by entry_date, id DESC");

		$incrementWeekNum = 0;
		if ($year == 2018 || $year == 2019 || $year == 2020)
		{
			$incrementWeekNum = 1;
		}

		while ($dataObj->fetch())
		{
			if (isset($food_costs[$dataObj->weekNum + $incrementWeekNum]))
			{
				$food_costs[$dataObj->weekNum + $incrementWeekNum] += $dataObj->total_cost;
			}
			else
			{
				$food_costs[$dataObj->weekNum + $incrementWeekNum] = $dataObj->total_cost;
			}
		}

		$labor_costs = array();
		$dataObj = DAO_CFactory::create('store_expenses');

		$dataObj->query("select WEEK(store_expenses.entry_date) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
				From store_expenses Where MONTH(store_expenses.entry_date) = $month and YEAR(store_expenses.entry_date) = $year and store_id = $store_id and store_expenses.is_deleted = 0 and
				store_expenses.expense_type = 'LABOR' order by entry_date, id DESC");

		while ($dataObj->fetch())
		{

			if (isset($labor_costs[$dataObj->weekNum + $incrementWeekNum]))
			{
				$labor_costs[$dataObj->weekNum + $incrementWeekNum] += $dataObj->total_cost;
			}
			else
			{
				$labor_costs[$dataObj->weekNum + $incrementWeekNum] = $dataObj->total_cost;
			}
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The retrieval was successful.',
			'food_costs' => $food_costs,
			'labor_costs' => $labor_costs
		));
	}

	function loadAdjustmentForDateAndSession($date, $store_id)
	{

		if (empty($_POST['session_id']) || !is_numeric($_POST['session_id']))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'Invalid parameter'
			));
		}

		$session_id = $_POST['session_id'];

		// expecting 5/6/2012
		$dateParts = explode("/", $date);

		$store_expenses = DAO_CFactory::create("store_expenses");
		$current_date = mktime(0, 0, 0, $dateParts[0], $dateParts[1], $dateParts[2]);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$selectStr = "select se.entry_date, se.expense_type, se.notes, se.total_cost, se.units
						from store_expenses se
						where se.store_id = $store_id and se.entry_date >= '$current_date_sql'
						and se.entry_date <  DATE_ADD('$current_date_sql', INTERVAL 1 DAY ) and se.expense_type in ('SALES_ADJUSTMENTS', 'FUNDRAISER_DOLLARS') and se.session_id = $session_id
						and se.is_deleted = 0";
		$store_expenses->query($selectStr);
		$rows = array();

		while ($store_expenses->fetch())
		{
			$ts = explode("-", $store_expenses->entry_date);
			$current_date_sql = date("n/j/Y", mktime(0, 0, 0, $ts[1], $ts[2], $ts[0]));
			$thisItem = array(
				'time' => $current_date_sql,
				'type' => $store_expenses->expense_type,
				'amount' => $store_expenses->total_cost,
				'notes' => $store_expenses->notes
			);
			$rows[$store_expenses->expense_type] = $thisItem;
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The load was successful.',
			'formattedDate' => CTemplate::dateTimeFormat($current_date_sql, VERBOSE_DATE),
			'entries' => $rows
		));
	}

	function loadExpensesDataForDate($date, $store_id)
	{
		// expecting 5/6/2012
		$dateParts = explode("/", $date);
		$current_date = mktime(0, 0, 0, $dateParts[0], $dateParts[1], $dateParts[2]);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$Store = DAO_CFactory::create('store');
		$Store->query("select timezone_id from store where id = $store_id");
		$Store->fetch();

		$adjustedServerTime = CTimezones::getAdjustedServerTime($Store);

		// check for orders in previous month if current day is greater than 6
		$day = date("j", $adjustedServerTime);
		$month = date("n", $adjustedServerTime);
		$year = date("Y", $adjustedServerTime);
		/*

		$lockedDownDate = false;

		if ($day > 6)
		{
			$cutOff = mktime(0, 0, 0, $month, 1, $year);
			if ($current_date < $cutOff)
				$lockedDownDate = true;
		}
		else
		{
			$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);
			if ($current_date < $cutOff)
				$lockedDownDate = true;
		}

		$lockEditing = ($lockedDownDate and CUser::getCurrentUser()->isFranchiseAccess());
		*/

		$lockEditing = false;
		// CES 10/22/13: removed lock per Jeb

		$store_expenses = DAO_CFactory::create("store_expenses");

		$selectStr = "select se.entry_date, se.expense_type, se.notes, se.total_cost, se.units
							from store_expenses se
							where se.store_id = $store_id and se.entry_date >= '$current_date_sql' and expense_type not in ('SALES_ADJUSTMENTS', 'FUNDRAISER_DOLLARS')
							and se.entry_date <  DATE_ADD('$current_date_sql', INTERVAL 1 DAY )
							and se.is_deleted = 0";
		$store_expenses->query($selectStr);
		$rows = array();

		while ($store_expenses->fetch())
		{
			$ts = explode("-", $store_expenses->entry_date);
			$current_date_sql = date("n/j/Y", mktime(0, 0, 0, $ts[1], $ts[2], $ts[0]));
			$thisItem = array(
				'time' => $current_date_sql,
				'type' => $store_expenses->expense_type,
				'amount' => $store_expenses->total_cost,
				'notes' => $store_expenses->notes
			);
			$rows[$store_expenses->expense_type] = $thisItem;
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The load was successful.',
			'formattedDate' => CTemplate::dateTimeFormat($current_date_sql, VERBOSE_DATE),
			'can_edit' => !$lockEditing,
			'entries' => $rows
		));
	}

	function storeAdjustmentForDateAndSession($date, $store_id, $session_id, $sales_adjustment = false, $sales_adjustment_note = false, $fundraising = false, $fundraising_note = false)
	{
		if (empty($session_id) || !is_numeric($session_id))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'The session_id parameter is missing or corrupt.'
			));
		}

		$DAO_store_expenses = DAO_CFactory::create("store_expenses", true);
		$DAO_store_expenses->entry_date = date("Y-m-d", strtotime($date));
		$DAO_store_expenses->store_id = $store_id;
		$DAO_store_expenses->session_id = $session_id;

		$rows = array();

		if (isset($sales_adjustment))
		{
			$DAO_store_expenses->expense_type = 'SALES_ADJUSTMENTS';
			$result = $DAO_store_expenses->storeAdjustment($sales_adjustment, $sales_adjustment_note);

			if ($result['processor_success'])
			{
				$rows = array_merge($rows, $result['entries']);
			}
			else
			{
				CAppUtil::processorMessageEcho($result);
			}
		}

		if (isset($fundraising))
		{
			$DAO_store_expenses->expense_type = 'FUNDRAISER_DOLLARS';
			$result = $DAO_store_expenses->storeAdjustment($fundraising, $fundraising_note);

			if ($result['processor_success'])
			{
				$rows = array_merge($rows, $result['entries']);
			}
			else
			{
				CAppUtil::processorMessageEcho($result);
			}
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The expenses were recorded.',
			'date' => date("n/j/Y", strtotime($result['date'])),
			'month' => $result['month'],
			'year' => $result['year'],
			'session_id' => $session_id,
			'entries' => $rows
		));
	}

	function storeExpensesDataForDate($date, $store_id)
	{

		$foodCost = (!empty($_POST['food']) ? $_POST['food'] : 0);
		$foodNote = (!empty($_POST['food_note']) ? $_POST['food_note'] : 'null');

		$hasFoodCost = false;
		if (!empty($foodCost) || (!empty($foodNote) && $foodNote != 'null'))
		{
			$hasFoodCost = true;
		}

		$foodCost = trim($foodCost);

		if (!is_numeric($foodCost))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'The food cost parameter is missing or corrupt.'
			));
		}

		$date_in_org_format = $date;
		$date = date("Y-m-d", strtotime($date));
		$month = date("n", strtotime($date));
		$year = date("Y", strtotime($date));

		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query("select * from store_expenses where entry_date = '$date' and expense_type = 'SYSCO' and store_id = $store_id and is_deleted  = 0");
		if ($store_expenses->N > 0)
		{
			// update
			$foodUpdater = DAO_CFactory::create("store_expenses");
			if (!empty($foodCost) || (!empty($foodNote) && $foodNote != 'null'))
			{
				$foodNote = stripslashes($foodNote); // cleaned by DAO
				if ($foodNote != 'null')
				{
					$foodNote = "'$foodNote'";
				}

				$foodUpdater->query("update store_expenses set total_cost = $foodCost, notes = $foodNote where entry_date = '$date' and expense_type = 'SYSCO' and store_id = $store_id and is_deleted  = 0");
			}
			else
			{
				$foodUpdater->query("update store_expenses set is_deleted = 1 where entry_date = '$date' and expense_type = 'SYSCO' and store_id = $store_id and is_deleted  = 0");
			}
		}
		else if (!empty($foodCost) || (!empty($foodNote) && $foodNote != 'null'))
		{
			// insert but only if either the note or amount is non-empty
			$foodInserter = DAO_CFactory::create("store_expenses");
			$foodInserter->entry_date = $date;
			$foodInserter->store_id = $store_id;
			$foodInserter->expense_type = 'SYSCO';
			$foodInserter->total_cost = $foodCost;
			$foodInserter->notes = $foodNote;
			$foodInserter->insert();
		}

		$laborCost = (!empty($_POST['labor']) ? $_POST['labor'] : 0);
		$laborNote = (!empty($_POST['labor_note']) ? $_POST['labor_note'] : 'null');

		$hasLaborCost = false;
		if (!empty($laborCost) || (!empty($laborNote) && $laborNote != 'null'))
		{
			$hasLaborCost = true;
		}

		$laborCost = trim($laborCost);

		if (!is_numeric($laborCost))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'The labor cost parameter is missing or corrupt.'
			));
		}

		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query("select * from store_expenses where entry_date = '$date' and expense_type = 'LABOR' and store_id = $store_id and is_deleted  = 0");
		if ($store_expenses->N > 0)
		{
			// update
			$laborUpdater = DAO_CFactory::create("store_expenses");

			if (!empty($laborCost) || (!empty($laborNote) && $laborNote != 'null'))
			{
				$laborNote = stripslashes($laborNote); // cleaned by DAO
				if ($laborNote != 'null')
				{
					$laborNote = "'$laborNote'";
				}

				$laborUpdater->query("update store_expenses set total_cost = $laborCost, notes = $laborNote where entry_date = '$date' and expense_type = 'LABOR' and store_id = $store_id and is_deleted  = 0");
			}
			else
			{
				$laborUpdater->query("update store_expenses set is_deleted = 1 where entry_date = '$date' and expense_type = 'LABOR' and store_id = $store_id and is_deleted  = 0");
			}
		}
		else if (!empty($laborCost) || (!empty($laborNote) && $laborNote != 'null'))
		{
			// insert
			$laborInserter = DAO_CFactory::create("store_expenses");
			$laborInserter->entry_date = $date;
			$laborInserter->store_id = $store_id;
			$laborInserter->expense_type = 'LABOR';
			$laborInserter->total_cost = $laborCost;
			$laborInserter->notes = $laborNote;
			$laborInserter->insert();
		}

		$rows = array();
		if ($hasFoodCost)
		{
			$rows['SYSCO'] = array(
				'type' => 'SYSCO',
				'amount' => $foodCost,
				'notes' => $foodNote
			);
		}

		if ($hasLaborCost)
		{
			$rows['LABOR'] = array(
				'type' => 'LABOR',
				'amount' => $laborCost,
				'notes' => $laborNote
			);
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The expenses were recorded.',
			'date' => $date_in_org_format,
			'month' => $month,
			'year' => $year,
			'entries' => $rows
		));
	}

}

?>