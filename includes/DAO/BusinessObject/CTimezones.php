<?php

// timestamp formats
define("TS_NORMAL", 1);

/**
 * A collection of static methods for getting the list of timezones and GMT offsets and for localizing time
 *
 */
class CTimezones
{

	private static $zones = array(
		1 => "UTC",
		2 => "US/Alaska",
		3 => "US/Aleutian",
		4 => "US/Arizona",
		5 => "US/Central",
		6 => "US/East-Indiana",
		7 => "US/Eastern",
		8 => "US/Hawaii",
		9 => "US/Indiana-Starke",
		10 => "US/Michigan",
		11 => "US/Mountain",
		12 => "US/Pacific",
		13 => "US/Samoa",
		14 => "Canada/Atlantic",
		15 => "Canada/Central",
		16 => "Canada/East-Saskatchewan",
		17 => "Canada/Eastern",
		18 => "Canada/Mountain",
		19 => "Canada/Newfoundland",
		20 => "Canada/Pacific",
		21 => "Canada/Saskatchewan",
		22 => "Canada/Yukon"
	);

	static public function zones()
	{
		return self::$zones;
	}

	static public function zone_by_id($id)
	{
		return self::$zones[$id];
	}

	// http://www.smeter.net/forums/about27.html&highlight=
	private static $offsets = array(
		1 => 0,
		//utc
		2 => -9,
		//"US/Alaska",
		3 => -10,
		// "US/Aleutian",
		4 => -7,
		//"US/Arizona",
		5 => -6,
		//"US/Central",
		6 => -5,
		//6 => "US/East-Indiana",
		7 => -5,
		//7 => "US/Eastern",
		8 => -10,
		//8 => "US/Hawaii",
		9 => -5,
		//9 => "US/Indiana-Starke",
		10 => -5,
		//10 => "US/Michigan",
		11 => -7,
		//11 => "US/Mountain",
		12 => -8,
		//12 => "US/Pacific",
		13 => -11,
		//13 => "US/Samoa",
		14 => -4,
		//14 => "Canada/Atlantic",
		15 => -6,
		//15 => "Canada/Central",
		16 => -6,
		//16 => "Canada/East-Saskatchewan",
		17 => -6,
		//17 => "Canada/Eastern",
		18 => -7,
		//18 => "Canada/Mountain",
		19 => -3.5,
		//19 => "Canada/Newfoundland",
		20 => -8,
		//20 => "Canada/Pacific"
		21 => -6,
		//21 => "Canada/Saskatchewan",
		22 => -8
	);    //22 => "Canada/Yukon"

	private static $DDToPHPTimeZoneMap = array(
		1 => 'UTC',
		2 => 'America/Anchorage',
		3 => 'America/Adak',
		4 => 'America/Los_Angeles',
		5 => 'America/Chicago',
		6 => 'America/New_York',
		7 => 'America/New_York',
		8 => 'Pacific/Honolulu',
		9 => 'America/Chicago',
		10 => 'America/Detroit',
		11 => 'America/Denver',
		12 => 'America/Los_Angeles'
	);

	public static function getPHPTimeZoneFromID($tzid)
	{
		return self::$DDToPHPTimeZoneMap[$tzid];
	}

	public static function getOffset($tzid)
	{
		if (isset(self::$offsets[$tzid]))
		{
			$rawOffset = self::$offsets[$tzid];

			return $rawOffset;
		}

		CLog::RecordNew(CLOG::WARNING, "No Time Zone offset found for index: " . $tzid, "", "", false);

		return self::$offsets[7]; // eastern us returned if tzid is invalid

	}

	static $storeTimeZoneCache = array();

	/*
	 *  pass in a mysql datetime with either a store id or a store object with at least the timezone_id set
	 *  this function will return a localized time with the local timezone
	 */
	public static function localizeAndFormatTimeStamp($time, $store, $format = NORMAL, $toolTipFormat = false)
	{

		if (!is_object($store))
		{

			if (!isset(self::$storeTimeZoneCache[$store]))
			{

				if (!is_numeric($store))
				{
					throw new Exception("invalid store passed to localizeAndFormatTimeStamp");
				}

				$storeObj = DAO_CFactory::create('store');
				$storeObj->query("select timezone_id from store where id = $store");
				$storeObj->fetch();

				self::$storeTimeZoneCache[$store] = self::$DDToPHPTimeZoneMap[$storeObj->timezone_id];
			}

			$store_id = $store;
		}
		else
		{
			$store_id = $store->id;

			if (!isset(self::$storeTimeZoneCache[$store->id]))
			{
				self::$storeTimeZoneCache[$store->id] = self::$DDToPHPTimeZoneMap[$store->timezone_id];
			}
		}

		if ($toolTipFormat)
		{
			$serverTime = CTemplate::dateTimeFormat($time, $toolTipFormat) . " (Server Time)";
		}

		$LocallPHPTimeZone = self::$storeTimeZoneCache[$store_id];

		if (is_numeric($time))
		{
			$time = date("Y-m-d H:i:s", $time);
		}
		else
		{
			$time = date("Y-m-d H:i:s", strtotime($time));
		}

		$dateObj = new DateTime($time, new DateTimeZone(date_default_timezone_get()));
		$dateObj->setTimezone(new DateTimeZone($LocallPHPTimeZone));

		switch ($format)
		{
			case VERBOSE:
				$formatStr = "D n/j/Y g:i:s A T";//Sat 1/3/2015 4:35:22 PM PST
				break;
			case NORMAL:
				$formatStr = "M j, Y - g:i A T"; //Feb 3, 2015 - 7:14 PM
				break;
			case TIME:
				$formatStr = "g:i:s A T"; //Feb 3, 2015 - 7:14 PM
				break;
			case MYSQL:
				$formatStr = "Y-m-d H:i:s"; // 2020-04-23 13:11:00
				break;
			default:
				$formatStr = "D n/j/Y g:i:s A T"; //verbose
		}

		$retVal = $dateObj->format($formatStr);

		if ($toolTipFormat)
		{
			$retVal = '<span data-toggle="tooltip" title="' . $serverTime . '">' . $retVal . '</span>';
		}

		return $retVal;
	}

	// returns the server's current time after adjusting to the passed in store's time zone
	// IE, the returned time can be compared directly to the time at a store.
	public static function getAdjustedServerTime($storeObj)
	{
		// If the passed in variable is not an object, it is the timezone_id
		if (!is_object($storeObj))
		{
			$timezone_id = $storeObj;
		}
		else
		{
			$timezone_id = $storeObj->timezone_id;
		}

		$hour = 3600;    //3600 seconds per hour
		$offset = self::getOffset($timezone_id); // offset in hours
		$DSTOffset = self::getSystemDSTOffset(null); // will the current close time be in DST ?
		$currentTimeGMT = gmdate("M d Y H:i:s", time());

		$currentTimeGMT_TimeZone = strtotime($currentTimeGMT);
		$currentTimeGMT_TimeZone = $currentTimeGMT_TimeZone + $DSTOffset + ($offset * $hour);

		return $currentTimeGMT_TimeZone;
	}

	// returns the passed in time (a unix time in seconds)- considered to be local to the server- adjusted for the store's time zone
	// IE, the returned time can be compared directly to the time at a store.
	public static function getAdjustedTime($storeObj, $time)
	{
		$hour = 3600;    //3600 seconds per hour
		$offset = self::getOffset($storeObj->timezone_id); // offset in hours
		$DSTOffset = self::getSystemDSTOffset(null); // will the current close time be in DST ?

		$currentTimeGMT = gmdate("M d Y H:i:s", $time);
		$currentTimeGMT_TimeZone = strtotime($currentTimeGMT);
		$currentTimeGMT_TimeZone = $currentTimeGMT_TimeZone + $DSTOffset + ($offset * $hour);

		return $currentTimeGMT_TimeZone;
	}

	// returns the server's current time after adjusting to the passed in time zone id
	// IE, the returned time can be compared directly to the time at a store.
	public static function getAdjustedServerTimeWithTimeZoneID($timeZoneID)
	{
		$hour = 3600;    //3600 seconds per hour
		$offset = self::getOffset($timeZoneID); // offset in hours
		$DSTOffset = self::getSystemDSTOffset(null); // will the current close time be in DST ?

		$currentTimeGMT = gmdate("M d Y H:i:s", time());
		$currentTimeGMT_TimeZone = strtotime($currentTimeGMT);
		$currentTimeGMT_TimeZone = $currentTimeGMT_TimeZone + $DSTOffset + ($offset * $hour);

		return $currentTimeGMT_TimeZone;
	}

	// this method will return in milliseconds a DST offset if the current
	public static function getSystemDSTOffset($dateTime = null)
	{


		if ($dateTime == null)
		{
			$isDST = date("I");
		}
		else
		{
			$timestamp = strtotime($dateTime);
			$isDST = date("I", $timestamp);
		}
		if ($isDST)
		{
			return 3600;
		}
		else
		{
			return 0;
		}
	}

}

?>