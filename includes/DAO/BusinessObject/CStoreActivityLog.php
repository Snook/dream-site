<?php
require_once 'DAO/Store_activity_log.php';
require_once 'DAO/Store_activity_type.php';
require_once('includes/CTemplate.inc');


class CStoreActivityLog extends DAO_Store_activity_log
{

	//Activity Type
	const GENERIC = 'GENERIC';
	const INVENTORY = 'INVENTORY';
	const ORDER = 'ORDER';
	const SIDES_ORDER = 'SIDES_ORDER';
	const SESSION = 'SESSION';
	const USER = 'USER';

	//Activity Subtype
	//General
	const SUBTYPE_ALERT = 'ALERT';
	const SUBTYPE_GENERIC = 'GENERIC';
	//Inventory
	const SUBTYPE_LOW = 'LOW';
	const SUBTYPE_NORMAL = 'NORMAL';
	const SUBTYPE_HIGH = 'HIGH';
	//Orders
	const SUBTYPE_ADDED= 'ADDED';
	const SUBTYPE_MODIFIED = 'MODIFIED';
	const SUBTYPE_PLACED = 'PLACED';
	const SUBTYPE_CANCELLED= 'CANCELLED';
	const SUBTYPE_SIDES_FORM= 'SIDES';
	//Session
	const SUBTYPE_CREATED= 'CREATED';
	const SUBTYPE_DELETED = 'DELETED';

	const TEST_ALERT_ONLY = false;





	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param      $store_id
	 * @param      $description
	 * @param      $date
	 * @param null $store_activity_type_id
	 * @param null $compositeKey
	 *
	 * @throws Exception
	 */
	public static function addEvent($store_id, $description, $date, $store_activity_type_id = null, $compositeKey = null){

		if(is_null($store_activity_type_id)){
			$store_activity_type_id = self::determineStoreActivityType(CStoreActivityLog::GENERIC,CStoreActivityLog::SUBTYPE_GENERIC);
		}

		$activity_log_DAO = DAO_CFactory::create('store_activity_log');
		$activity_log_DAO->store_id = $store_id;
		$activity_log_DAO->store_activity_type_id = $store_activity_type_id;
		$activity_log_DAO->date = $date;
		$activity_log_DAO->description = $description;
		$activity_log_DAO->comp_key = $compositeKey;


		$activity_log_DAO->insert();
	}

	/**
	 * Helper function to fetch all activity events within the given timefram
	 *
	 * @param $store_id
	 * @param $start  'yyyy-mm-dd hh:mm:ss'
	 * @param $end 'yyyy-mm-dd hh:mm:ss'
	 *
	 * @return array matching records
	 * @throws Exception
	 */
	public static function fetchAllEventsInTimeframe($store_id, $start, $end){


		$activity_log_DAO = DAO_CFactory::create('store_activity_log');
		$query = "select *
		from store_activity_log 
		where date >= '$start' and date <= '$end'
		and is_deleted = 0";
		$activity_log_DAO->query($query);

		$rows = array();

		while ($activity_log_DAO->fetch())
		{
			$tarray = $activity_log_DAO->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	/**
	 *
	 * This looks to see if a unique key exists in the table. The key can be of any format that uniquely identifies
	 * an event. Useful for avoiding duplicate data based on some logical criteria other that a primary key.
	 *
	 * for example, to only create a singe event per day for an activity that may reoccur multiple times per day,
	 * create a key using the date+some unique data from the re-occurring event.
	 *
	 * @param $compositeKey
	 *
	 * @return bool if a match is found
	 * @throws Exception
	 */
	public static function doesKeyExist($compositeKey){


		$activity_log_DAO = DAO_CFactory::create('store_activity_log');
		$query = "select *
		from store_activity_log 
		where comp_key = '$compositeKey'
		and is_deleted = 0";
		$activity_log_DAO->query($query);

		return $activity_log_DAO->N > 0;
	}

	/**
	 *
	 * Return events in timeframe from a specified starting date to N number of days before that date.
	 * @param $store_id
	 * @param $store_activity_type_id
	 * @param $startingDate
	 * @param $daysBack number of days worth of data to include...going backward from starting date
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function fetchSpecificEventsInTimeframe($store_id,$store_activity_type_id, $startingDate, $daysBack){

		//Handle Order/Booking
		$dateRangeClause = "  ( date >= '$startingDate 00:00:01' and date <= '$startingDate 23:59:59')";
		if ($daysBack > 1)
		{
			$dateRangeClause = "  ( date >= DATE_SUB('" . $startingDate . "', INTERVAL " . $daysBack . " DAY) and date <= '$startingDate 23:59:59')";
		}


		$activity_log_DAO = DAO_CFactory::create('store_activity_log');
		$query = "select *
		from store_activity_log 
		where $dateRangeClause
		and store_activity_type_id = $store_activity_type_id
		and store_id = $store_id
		and is_deleted = 0";
		$activity_log_DAO->query($query);

		$retVal = array();


		while ($activity_log_DAO->fetch())
		{
			$typeInfo = self::fetchStoreActivityTypeAndSubTypeById($activity_log_DAO->store_activity_type_id);

			$retVal[] = array(
				'time' => $activity_log_DAO->date,
				'action' => $typeInfo[1],
				'user' => '',
				'user_id' => '',
				'user_type' => '',
				'type' => $typeInfo[0],
				'description' => $activity_log_DAO->description
			);
		}

		return $retVal;
	}

	/**
	 *
	 * Return the id of the matching Store Activty Type for the passed activity type/subtype
	 *
	 * @param $activityType string
	 * @param $activitySubType string
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function determineStoreActivityTypeId($activityType, $activitySubType)
	{

		$id = null;
		if(is_null($activityType)){
			$activityType = CStoreActivityLog::GENERIC;
		}
		if(is_null($activitySubType)){
			$activitySubType = CStoreActivityLog::SUBTYPE_GENERIC;
		}
		$activity_type_DAO = DAO_CFactory::create('store_activity_type');
		$activity_type_DAO->type = $activityType;
		$activity_type_DAO->subtype = $activitySubType;

		if ($activity_type_DAO->find(true))
		{
			$id = $activity_type_DAO->id;
		}else{
			$id = self::determineStoreActivityTypeId(CStoreActivityLog::GENERIC,CStoreActivityLog::SUBTYPE_GENERIC);
		}

		return $id;
	}

	/**
	 * Given a type id, translate it to the ActivityType/ActivitySubType
	 *
	 * @param $activityTypeId int
	 *
	 * @return array|null[]
	 * @throws Exception
	 */
	public static function fetchStoreActivityTypeAndSubTypeById($activityTypeId)
	{
		$activity_type_DAO = DAO_CFactory::create('store_activity_type');
		$activity_type_DAO->id = $activityTypeId;

		if ($activity_type_DAO->find(true))
		{
			return [$activity_type_DAO->type,$activity_type_DAO->subtype];
		}else{
			return [null,null];
		}

	}

	/**
	 * Email notice helper
	 *
	 * @param $store_name
	 * @param $primary_email
	 * @param $subject
	 * @param $templateText
	 * @param $templateHtml
	 */
	public static function sendStoreAlertEmail($store_name, $primary_email, $subject, $templateText, $templateHtml){
		require_once 'CMail.inc';

		$Mail = new CMail();
		$Mail->from_name = 'Store Activity Alert';
		$Mail->to_id = false;
		$Mail->to_name = $store_name;

		if (self::TEST_ALERT_ONLY)
		{
			$Mail->to_email = 'ryan.snook@dreamdinners.com';
		}
		else
		{
			$Mail->to_email = $primary_email;
		}


		$Mail->subject = $subject;
		$Mail->body_html = $templateHtml;
		$Mail->body_text = $templateText;
		$Mail->template_name = '';

		$Mail->sendEmail();

	}

	static function renderTemplate($filename, $tokens)
	{
		$templateEngine = new CTemplate();

		$templateEngine->assign($tokens);

		$contents = $templateEngine->render('/admin/subtemplate/store_activity/data/' . $filename);

		return $contents;
	}
}

?>