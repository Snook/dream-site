<?php
/*
 * Created on Sep 1, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CCalendar
{
	var $numRows = 6;
	var $rows;

	/**
	 * used for customer order calendar
	 * finds the visible range for this month:
	 * sunday before the 1st spanning to the saturday after the last day
	 * returns MySQL date format
	 */
	static function calculateMonthRange($timestamp = false)
	{
		if (!$timestamp)
		{
			$timestamp = time();
		}

		//find the week for the 1st day of this month
		$firstyear = date('Y', $timestamp);
		$starttimestamp = mktime(0, 0, 0, date('m', $timestamp), 1, $firstyear);
		if (date('w', $starttimestamp) > 0)
		{ //if it's not sunday
			if (date('m', $timestamp) == '01')
			{ //if it's january
				$lastDayOfPrevious = date('z', mktime(0, 0, 0, 1, 0, $firstyear));
				$firstDay = $lastDayOfPrevious - date('w', $starttimestamp) + 2;
				$starttimestamp = mktime(0, 0, 0, 1, $firstDay, $firstyear - 1);
			}
			else
			{
				$lastDayOfPrevious = date('z', mktime(0, 0, 0, date('m', $timestamp), 0, $firstyear));
				$firstDay = $lastDayOfPrevious - date('w', $starttimestamp) + 2;
				$starttimestamp = mktime(0, 0, 0, 1, $firstDay, $firstyear);
			}
		}

		//find the week and year for the last day of the month
		$endtimestamp = mktime(0, 0, 0, date('m', $timestamp) + 1, 0, date('Y', $timestamp));
		if (date('w', $endtimestamp) < 6)
		{
			$endtimestamp = mktime(0, 0, 0, date('m', $timestamp) + 1, 6 - date('w', $endtimestamp), date('Y', $timestamp));
		}

		return array(
			date("Y-m-d 00:00:00", $starttimestamp),
			date("Y-m-d 23:59:59", $endtimestamp)
		);
	}

	static function sessionTypeNote($type, $isOpen = true)
	{
		$closed = '';
		if (!$isOpen)
		{
			$closed = ' note_closed';
		}

		switch ($type)
		{
			case CSession::WALK_IN:
				return '<span class="note_calendar note_walk_in' . $closed . '" data-tooltip="Walk-In">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::DELIVERY:
				return '<span class="note_calendar note_delivery' . $closed . '" data-tooltip="Made For You - Delivery">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::DELIVERY_PRIVATE:
				return '<span class="note_calendar note_delivery_private' . $closed . '" data-tooltip="Made For You - Delivery Private">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::REMOTE_PICKUP:
				return '<span class="note_calendar note_remote_pickup' . $closed . '" data-tooltip="Made For You - Community Pick Up">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::REMOTE_PICKUP_PRIVATE:
				return '<span class="note_calendar note_remote_pickup_private' . $closed . '" data-tooltip="Made For You - Community Pick Up - Private">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::SPECIAL_EVENT:
				return '<span class="note_calendar note_special_event' . $closed . '" data-tooltip="Made For You">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::DREAM_TASTE:
				return '<span class="note_calendar note_dream_taste' . $closed . '" data-tooltip="Meal Prep Workshop">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::FUNDRAISER:
				return '<span class="note_calendar note_fundraiser' . $closed . '" data-tooltip="Fundraiser Event">' . self::sessionTypeTiny($type) . '</span>';
			case 'FUNDRAISER_CURBSIDE': // for legends
				return '<span class="note_calendar note_fundraiser_pick_up' . $closed . '" data-tooltip="Fundraiser $10 Curbside">' . self::sessionTypeTiny('FUNDRAISER_CURBSIDE') . '</span>';
			case CSession::TODD:
				return '<span class="note_calendar note_todd' . $closed . '" data-tooltip="Taste of Dream Dinners">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::PRIVATE_SESSION:
				return '<span class="note_calendar note_private' . $closed . '" data-tooltip="Private Session">' . self::sessionTypeTiny($type) . '</span>';
			case CSession::STANDARD:
				return '<span class="note_calendar note_standard' . $closed . '" data-tooltip="Assembly session">' . self::sessionTypeTiny($type) . '</span>';
		}
	}

	static function sessionTypeTiny($type, $subtype = null)
	{
		switch ($type)
		{
			case CSession::DELIVERY:
				return 'HD';
				break;
			case CSession::DELIVERY_PRIVATE:
				return 'HDP';
				break;
			case CSession::REMOTE_PICKUP:
				return 'CP';
				break;
			case CSession::REMOTE_PICKUP_PRIVATE:
				return 'CPP';
				break;
			case CSession::SPECIAL_EVENT:
				return 'MFY';
				if(!is_null($subtypte) && $subtype ==  CSession::WALK_IN){
					return 'WI';
				}
				return 'MFY';
				break;
			case CSession::TODD:
				return 'T';
				break;
			case CSession::DREAM_TASTE:
				return 'MPW';
				break;
			case CSession::PRIVATE_SESSION:
				return 'P';
				break;
			case CSession::STANDARD:
				return 'A';
				break;
			case CSession::FUNDRAISER:
				return 'F';
				break;
			case 'FUNDRAISER_CURBSIDE':
				return 'FC';
				break;
			case CSession::WALK_IN:
				return 'WI';
				break;
			default:
				return $type;
		}
	}

	static function dayItemTypeNote($dayItem)
	{
		if (empty($dayItem['session_type_fadmin_acronym']))
		{
			return "";
		}

		return '<span class="note_calendar note_' . $dayItem['session_type_string'] . '' . (!empty($dayItem['isOpen']) ? '' : ' note_closed') . '" data-tooltip="' . $dayItem['session_type_title'] . '">' .
			$dayItem['session_type_fadmin_acronym'] . '</span>';
	}

	/**
	 * used by session manager calendar
	 * finds the visible range for this month + the last 6 days
	 * of the previous month and the first 6 days of the next month:
	 * returns MySQL date format
	 */
	static function calculateExpandedMonthRange($timestamp)
	{
		if (!$timestamp)
		{
			$timestamp = time();
		}

		// get month and year of passed in or current timestamp
		$firstyear = date('Y', $timestamp);
		$currentMonth = date('m', $timestamp);

		// get first and last day of the month
		$starttimestamp = mktime(0, 0, 0, $currentMonth, 1, $firstyear);
		$endtimestamp = mktime(0, 0, 0, $currentMonth, date("t", $starttimestamp), $firstyear);

		// expand the start and end days
		$starttimestamp -= (84600 * 6);
		$endtimestamp += (84600 * 7);

		// Get the day of the week and subtract time to get the timestamp for Sunday
		$starttimeDOW = date('w', $starttimestamp);
		$starttimestamp -= (84600 * $starttimeDOW);

		// Get the day of the week and add time to get the timestamp for Saturday
		$endtimeDOW = date('w', $endtimestamp);
		$endtimestamp += (84600 * (6 - $endtimeDOW));

		return array(
			date("Y-m-d H:i:s", $starttimestamp),
			date("Y-m-d H:i:s", $endtimestamp)
		);
	}

	/**
	 * Convert MySQL date to calendar style date format "n/j/Y" used by $ItemsFunction
	 */
	static function dateConvert($mysqlDate)
	{
		$asTime = strtotime($mysqlDate);

		return date("n", $asTime) . "/" . date("j", $asTime) . "/" . date("Y", $asTime);
	}

	/**
	 * set forward and back months links
	 */
	static function setPreviousLink($tpl, $url)
	{
		if ($tpl)
		{
			$tpl->assign('calendarPrevious', $url);
		}
	}

	/**
	 * set forward and back months links
	 */
	static function setNextLink($tpl, $url)
	{
		if ($tpl)
		{
			$tpl->assign('calendarNext', $url);
		}
	}

	/**
	 * generateDayArray - Returns an array of Rows (weeks) that are an array of columns (days) that are an array of items
	 * @param int $month If non-zero this will be the focus month, if zero the current date's month will be used.
	 * @param int $year If non-zero this will be the focus year, if zero the current year will be used.
	 * @param string $ItemsFunction This function is called for every day returned. The Callee can add items to the items array.
	 * @param unixTime $startEmphasis Emphasized dates are hilited and their borders bolded.
	 * @param unixTime $endEmphasis Emphasized dates are hilited and their borders bolded.
	 * @param Boolean $expanded Setting to true will ensure the calendar will display at least 6 days of the prior month and 6]
	 *                        days of the next month.
	 * @param mysqlDate $startDate - pass in false for $expanded and set this param and $endDate to control the date span of the calendar.
	 * @param mysqlDate $endDate
	 * @param string $HeaderFunction
	 *
	 */
	function generateDayArray($month = 0, $year = 0, $ItemsFunction = null, $startEmphasis = null, $endEmphasis = null, $expanded = false, $startDate = null, $endDate = null, $HeaderFunction = null)
	{
		//TODO: externalize so html coder can control month colors
		$color = array(
			"#EFF3E5",
			"#F7F6F6",
			"#EFF3E5",
			"#F7F6F6",
			"#EFF3E5",
			"#F7F6F6",
			"#EFF3E5",
			"#F7F6F6",
			"#EFF3E5",
			"#F7F6F6",
			"#EFF3E5",
			"#F7F6F6"
		);

		if ($month <> 0)
		{
			if ($year == 0)
			{
				$curYear = date("Y");
			}
			else
			{
				$curYear = $year;
			}

			$curMonth = $month;

			$tempTimeStamp = mktime(0, 0, 0, $curMonth, 1, $curYear);

			$anchorPointX = date("w", $tempTimeStamp);
			$curMonthDays = date("t", $tempTimeStamp);
			$curDateDay = date("j", $tempTimeStamp);
			$anchorPointY = 0;

			// enlarge the data range so the last 6 days of the previous month and first six days of the next month are shown
			if ($expanded)
			{
				if ($anchorPointX < 6)
				{
					$anchorPointY = 1;
				}

				$firstRow = 7 - $anchorPointX;
				$remainingDays = $curMonthDays - $firstRow;
				$fullRows = floor($remainingDays / 7);
				$partialRows = floor($remainingDays % 7) > 0 ? 1 : 0;
				$this->numRows = $anchorPointY + 2 + $fullRows + $partialRows;
			}
			else if ($startDate <> null)
			{
				$startDateTS = strtotime($startDate);
				//adjust start date to beginning of day
				$startDateTS = mktime(0, 0, 0, date("n", $startDateTS), date("d", $startDateTS), date("Y", $startDateTS));

				// always show from the start of the month
				if ($startDateTS > $tempTimeStamp)
				{
					$startDateTS = $tempTimeStamp;
				}

				$anchorPointY = 0;
				$anchorPointX = date("w", $startDateTS);
				$curDateDay = date("j", $startDateTS);
				$curMonth = date("n", $startDateTS);
				$curYear = date("Y", $startDateTS);

				$monthEndTS = $tempTimeStamp + ($curMonthDays * 86400);
				$endDateTS = strtotime($endDate);
				$endDateTS = mktime(0, 0, 0, date("n", $endDateTS), date("d", $endDateTS), date("Y", $endDateTS));
				$endDateTS += 86400;

				$curMonthDays = date("t", $startDateTS);

				if ($endDateTS < $monthEndTS)
				{
					$endDateTS = $monthEndTS;
				}

				$span = ($endDateTS - $startDateTS) / 86400;
				$span += $anchorPointX;

				$tempnumRows = floor($span / 7);
				if ($span % 7 > 0)
				{
					$tempnumRows++;
				}
				$this->numRows = $tempnumRows;
			}
		}
		else
		{
			$anchorPointX = date("w");
			$curMonthDays = date("t");
			$curMonth = date("n");
			$curYear = date("Y");
			$curDateDay = date("j");
			$anchorPointY = 1;
		}

		$orgDay = $curDateDay;
		$orgMonth = $curMonth;
		$orgYear = $curYear;
		$curCol = $anchorPointX;

		$rows = array();

		// calculate for anchor point forward
		for ($curRow = $anchorPointY; $curRow < $this->numRows; $curRow++)
		{
			for (; $curCol < 7; $curCol++)
			{
				$rows[$curRow][$curCol]["dayNumber"] = $curDateDay;
				$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);

				$rows[$curRow][$curCol]["date"] = date("n", $asTime) . "/" . date("j", $asTime) . "/" . date("Y", $asTime);
				$rows[$curRow][$curCol]["session_menu_date"] = date("d", $asTime) . '-' . date("m", $asTime) . '-' . date("Y", $asTime);
				$rows[$curRow][$curCol]["color"] = $color[$curMonth - 1];

				$styleOverride = null;

				if ($ItemsFunction)
				{
					list($rows[$curRow][$curCol]["items"], $styleOverride) = $ItemsFunction($rows[$curRow][$curCol]["date"]);
				}

				if ($HeaderFunction)
				{
					$rows[$curRow][$curCol]["header"] = $HeaderFunction($rows[$curRow][$curCol]["date"]);
				}

				if ($styleOverride !== null && $styleOverride !== "")
				{
					$rows[$curRow][$curCol]["styleOverride"] = $styleOverride;
				}

				if ($startEmphasis && $endEmphasis && $asTime >= $startEmphasis && $asTime <= $endEmphasis)
				{
					$rows[$curRow][$curCol]["emphasize"] = true;
				}
				else
				{
					$rows[$curRow][$curCol]["emphasize"] = false;
				}

				// set "isTargetMonth" - used to embolden the dayNumbers
				if ($month <> 0) // if month was specified
				{
					if ($curMonth == $month)
					{
						$rows[$curRow][$curCol]["isTargetMonth"] = true;
					}
					else
					{
						$rows[$curRow][$curCol]["isTargetMonth"] = false;
					}
				}
				else
				{
					$rows[$curRow][$curCol]["isTargetMonth"] = true;
				}

				$curDateDay++;

				if ($curDateDay > $curMonthDays)
				{
					$curMonth++;
					if ($curMonth == 13)
					{
						$curMonth = 1;
						$curYear++;
					}
					$curDateDay = 1;
					$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);
					$curMonthDays = date("t", $asTime);
				}
				//echo $curCol . " " . $curRow . "<br />";
			}
			$curCol = 0;
		}

		// calculate for anchor point backward
		$curMonth = $orgMonth;
		$curYear = $orgYear;

		$curCol = $anchorPointX - 1;
		if ($curCol < 0)
		{
			$curCol = 6;
			$anchorPointY -= 1;
		}

		$curDateDay = $orgDay - 1;

		if ($curDateDay == 0)
		{
			$curMonth--;
			if ($curMonth == 0)
			{
				$curMonth = 12;
				$curYear -= 1;
			}
			$asTime = mktime(0, 0, 0, $curMonth, 1, $curYear);
			$curMonthDays = date("t", $asTime);
			$curDateDay = $curMonthDays;
		}

		// calculate for anchor point backward
		for ($curRow = $anchorPointY; $curRow >= 0; $curRow--)
		{
			for (; $curCol >= 0; $curCol--)
			{
				$rows[$curRow][$curCol]["dayNumber"] = $curDateDay;
				$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);

				$rows[$curRow][$curCol]["date"] = date("n", $asTime) . "/" . date("j", $asTime) . "/" . date("Y", $asTime);
				$rows[$curRow][$curCol]["color"] = $color[$curMonth - 1];

				$styleOverride = null;

				if ($ItemsFunction)
				{
					list($rows[$curRow][$curCol]["items"], $styleOverride) = $ItemsFunction($rows[$curRow][$curCol]["date"]);
				}

				if ($HeaderFunction)
				{
					$rows[$curRow][$curCol]["header"] = $HeaderFunction($rows[$curRow][$curCol]["date"]);
				}

				if ($styleOverride !== null && $styleOverride !== "")
				{
					$rows[$curRow][$curCol]["styleOverride"] = $styleOverride;
				}

				if ($startEmphasis && $endEmphasis && $asTime >= $startEmphasis && $asTime <= $endEmphasis)
				{
					$rows[$curRow][$curCol]["emphasize"] = true;
				}
				else
				{
					$rows[$curRow][$curCol]["emphasize"] = false;
				}

				// set "isTargetMonth" - used to embolden the dayNumbers
				if ($month <> 0) // if month was specified
				{
					if ($curMonth == $month)
					{
						$rows[$curRow][$curCol]["isTargetMonth"] = true;
					}
					else
					{
						$rows[$curRow][$curCol]["isTargetMonth"] = false;
					}
				}
				else
				{
					$rows[$curRow][$curCol]["isTargetMonth"] = true;
				}

				$curDateDay--;

				if ($curDateDay == 0)
				{
					$curMonth--;
					if ($curMonth == 0)
					{
						$curMonth = 12;
					}
					$asTime = mktime(0, 0, 0, $curMonth, 1, $curYear);
					$curMonthDays = date("t", $asTime);
					$curDateDay = $curMonthDays;
				}
				//echo $curCol . " " . $curRow . "<br />";
			}
			$curCol = 6;
		}

		return $rows;
	}

	/**
	 * generateDayArray - Returns an array of Rows (weeks) that are an array of columns (days) that are an array of items
	 * @param int $month If non-zero this will be the focus month, if zero the current date's month will be used.
	 * @param int $year If non-zero this will be the focus year, if zero the current year will be used.
	 * @param Boolean $expanded Setting to true will ensure the calendar will display at least 6 days of the prior month and 6]
	 *                        days of the next month.
	 * @param mysqlDate $startDate - pass in false for $expanded and set this param and $endDate to control the date span of the calendar.
	 * @param mysqlDate $endDate
	 * @param string $DayFunction
	 *
	 */
	function generateCompactDayArray($month = 0, $year = 0, $DayFunction = null, $expanded = false, $startDate = null, $endDate = null)
	{

		if ($month <> 0)
		{
			if ($year == 0)
			{
				$curYear = date("Y");
			}
			else
			{
				$curYear = $year;
			}

			$curMonth = $month;

			$tempTimeStamp = mktime(0, 0, 0, $curMonth, 1, $curYear);

			$anchorPointX = date("w", $tempTimeStamp);
			$curMonthDays = date("t", $tempTimeStamp);
			$curDateDay = date("j", $tempTimeStamp);
			$anchorPointY = 0;

			// enlarge the data range so the last 6 days of the previous month and first six days of the next month are shown
			if ($expanded)
			{
				if ($anchorPointX < 6)
				{
					$anchorPointY = 1;
				}

				$firstRow = 7 - $anchorPointX;
				$remainingDays = $curMonthDays - $firstRow;
				$fullRows = floor($remainingDays / 7);
				$partialRows = floor($remainingDays % 7) > 0 ? 1 : 0;
				$this->numRows = $anchorPointY + 2 + $fullRows + $partialRows;
			}
			else if ($startDate <> null)
			{
				$startDateTS = strtotime($startDate);
				//adjust start date to beginning of day
				$startDateTS = mktime(0, 0, 0, date("n", $startDateTS), date("d", $startDateTS), date("Y", $startDateTS));

				// always show from the start of the month
				if ($startDateTS > $tempTimeStamp)
				{
					$startDateTS = $tempTimeStamp;
				}

				$anchorPointY = 0;
				$anchorPointX = date("w", $startDateTS);
				$curDateDay = date("j", $startDateTS);
				$curMonth = date("n", $startDateTS);
				$curYear = date("Y", $startDateTS);

				$monthEndTS = $tempTimeStamp + ($curMonthDays * 86400);
				$endDateTS = strtotime($endDate);
				$endDateTS = mktime(0, 0, 0, date("n", $endDateTS), date("d", $endDateTS), date("Y", $endDateTS));
				$endDateTS += 86400;

				$curMonthDays = date("t", $startDateTS);

				if ($endDateTS < $monthEndTS)
				{
					$endDateTS = $monthEndTS;
				}

				$span = ($endDateTS - $startDateTS) / 86400;
				$span += $anchorPointX;

				$tempnumRows = floor($span / 7);
				if ($span % 7 > 0)
				{
					$tempnumRows++;
				}
				$this->numRows = $tempnumRows;
			}
		}
		else
		{
			$anchorPointX = date("w");
			$curMonthDays = date("t");
			$curMonth = date("n");
			$curYear = date("Y");
			$curDateDay = date("j");
			$anchorPointY = 1;
		}

		$orgDay = $curDateDay;
		$orgMonth = $curMonth;
		$orgYear = $curYear;
		$curCol = $anchorPointX;

		$rows = array();

		// calculate for anchor point forward
		for ($curRow = $anchorPointY; $curRow < $this->numRows; $curRow++)
		{
			for (; $curCol < 7; $curCol++)
			{
				$rows[$curRow][$curCol]["dayNumber"] = $curDateDay;
				$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);
				$rows[$curRow][$curCol]["date"] = date("n", $asTime) . "/" . date("j", $asTime) . "/" . date("Y", $asTime);

				if ($DayFunction)
				{
					$rows[$curRow][$curCol] = array_merge($rows[$curRow][$curCol], $DayFunction($rows[$curRow][$curCol]["date"]));
				}

				$curDateDay++;

				if ($curDateDay > $curMonthDays)
				{
					$curMonth++;
					if ($curMonth == 13)
					{
						$curMonth = 1;
						$curYear++;
					}
					$curDateDay = 1;
					$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);
					$curMonthDays = date("t", $asTime);
				}
				//echo $curCol . " " . $curRow . "<br />";
			}
			$curCol = 0;
		}

		// calculate for anchor point backward
		$curMonth = $orgMonth;
		$curYear = $orgYear;

		$curCol = $anchorPointX - 1;
		if ($curCol < 0)
		{
			$curCol = 6;
			$anchorPointY -= 1;
		}

		$curDateDay = $orgDay - 1;

		if ($curDateDay == 0)
		{
			$curMonth--;
			if ($curMonth == 0)
			{
				$curMonth = 12;
				$curYear -= 1;
			}
			$asTime = mktime(0, 0, 0, $curMonth, 1, $curYear);
			$curMonthDays = date("t", $asTime);
			$curDateDay = $curMonthDays;
		}

		// calculate for anchor point backward
		for ($curRow = $anchorPointY; $curRow >= 0; $curRow--)
		{
			for (; $curCol >= 0; $curCol--)
			{
				$rows[$curRow][$curCol]["dayNumber"] = $curDateDay;
				$asTime = mktime(0, 0, 0, $curMonth, $curDateDay, $curYear);
				$rows[$curRow][$curCol]["date"] = date("n", $asTime) . "/" . date("j", $asTime) . "/" . date("Y", $asTime);

				if ($DayFunction)
				{
					$rows[$curRow][$curCol] = array_merge($rows[$curRow][$curCol], $DayFunction($rows[$curRow][$curCol]["date"]));
				}

				$curDateDay--;

				if ($curDateDay == 0)
				{
					$curMonth--;
					if ($curMonth == 0)
					{
						$curMonth = 12;
					}
					$asTime = mktime(0, 0, 0, $curMonth, 1, $curYear);
					$curMonthDays = date("t", $asTime);
					$curDateDay = $curMonthDays;
				}
				//echo $curCol . " " . $curRow . "<br />";
			}
			$curCol = 6;
		}

		return $rows;
	}
}
?>