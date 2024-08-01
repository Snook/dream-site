<?php
require_once 'DAO/Transient_data_store.php';
require_once 'includes/CLog.inc';

/**
 * Class TransientDataStore: a storage and retrieval mechanism for transient data
 *
 * Data is referenced by class and ID and can be up to 4GB in size.
 *
 * Data is automatically deleted after the specified number of days.
 *
 * The actual storage mechanism is opaque to the calling code,
 * could be backed up by a file system, for example, or a database
 *
 */
class TransientDataStore extends DAO_Transient_data_store
{
	const SHIPPING_RATE_CACHE = 'SHIPPING_RATE_CACHE';
	const SHIPPING_SHIPMENT_CACHE = 'SHIPPING_SHIPMENT_CACHE';
	const SHIPPING_SHIP_NOTIFICATION_NEW = 'SHIPPING_SHIP_NOTIFICATION_NEW';
	const SHIPPING_SHIP_NOTIFICATION_DONE = 'SHIPPING_SHIP_NOTIFICATION_DONE';
	const SHIPPING_SHIP_NOTIFICATION_FAILED = 'SHIPPING_SHIP_NOTIFICATION_FAILED';
	const SHIPPING_TRACKING_CACHE = 'SHIPPING_TRACKING_CACHE';
	const TAX_RATE_CACHE = 'TAX_RATE_CACHE';

	private $_db = false;

	/**
	 * @param $data_class enum (SHIPPING_RATE_CACHE,TAX_RATE_CACHE,SHIPPING_TRACKING_CACHE,SHIPPING_SHIP_NOTIFICATION_DONE,SHIPPING_SHIP_NOTIFICATION_NEW)
	 * @param $data_reference
	 * @param $limit      number of rows to fetch
	 *
	 * @return array (successful=>boolean, error_message=>string, data_class=string, data_id=long, data=blob,
	 * expires=timestamp)
	 * @throws Exception
	 */
	public static function retrieveData($data_class, $data_reference = null, $limit = 1): array
	{
		$DAO_transient_data_store = DAO_CFactory::create('transient_data_store', true);
		$DAO_transient_data_store->data_class = $data_class;
		if (!is_null($data_reference))
		{
			$DAO_transient_data_store->data_reference = $data_reference;
		}
		$DAO_transient_data_store->limit($limit);

		if ($DAO_transient_data_store->find(true))
		{
			$result['data_class'] = $DAO_transient_data_store->data_class;
			$result['id'] = $DAO_transient_data_store->id;
			$result['data'] = $DAO_transient_data_store->data;
			$result['data_reference'] = $DAO_transient_data_store->data_reference;
			$result['expires'] = $DAO_transient_data_store->expires;
			$result['created'] = $DAO_transient_data_store->timestamp_created;
			$result['successful'] = true;
		}
		else
		{
			$result['successful'] = false;//no match
		}

		return $result;
	}

	/**
	 * @param $data_class
	 * @param $data_reference unique key by class
	 * @param $data
	 * @param $howManyDaysUntilExpires
	 *
	 * @return associative array (successful=>boolean, error_message=>string
	 * @throws Exception
	 */
	public static function storeData($data_class, $data_reference, $data, $howManyDaysUntilExpires, $forceUnique = false)
	{
		$db = self::connect();
		$result = array();

		if ($forceUnique)
		{
			//check if record with same data_class and data_reference already exists,
			//if it does do not insert duplicate record
			$sql = "select * from transient_data_store where data_class = '{$data_class}' and data_reference =  '{$data_reference}'";
			$dbresult = mysqli_query($db, $sql);

			if ($dbresult && mysqli_num_rows($dbresult) > 0)
			{
				$result['successful'] = true;
				$result['message'] = "Matching record, no need to insert\n" . $sql;
				mysqli_free_result($dbresult);
				mysqli_close($db);

				return $result;
			}
		}

		//Set up an expiration date
		$expiration_date = new DateTime("now");
		date_add($expiration_date, new DateInterval("P" . $howManyDaysUntilExpires . "D"));
		$formattedDate = $expiration_date->format("Y-m-d");
		$sql = "insert into transient_data_store ( data_class, data_reference, data, expires) values ('{$data_class}', '{$data_reference}','{$data}', '{$formattedDate}')";
		$dbresult = mysqli_query($db, $sql);

		if (!$dbresult)
		{
			$result['successful'] = false;
			$result['error_message'] = "Error in " . __METHOD__ . ": " . mysqli_error($db) . "\n" . $sql;

			CLog::RecordNew(CLog::ERROR, $result['error_message'], "", "", false);
		}
		else
		{
			$result['successful'] = true;
		}

		mysqli_close($db);

		return $result;
	}

	/**
	 * @param $recordId
	 * @param $data_class
	 *
	 * @throws Exception
	 */
	public static function updateDataClass($recordId, $data_class): void
	{
		$DAO_transient_data_store = DAO_CFactory::create('transient_data_store', true);
		$DAO_transient_data_store->id = $recordId;
		$DAO_transient_data_store->find(true);

		$org_DAO_transient_data_store = clone $DAO_transient_data_store;
		$DAO_transient_data_store->data_class = $data_class;
		$DAO_transient_data_store->update($org_DAO_transient_data_store);
	}

	/**
	 * True deletes all records from Transient_data_store that have an expiry before today's date
	 * @return array
	 */
	public static function cleanupStale()
	{
		$db = self::connect();
		$result = array();

		//Set up an expiration date

		$sql = "delete from transient_data_store where expires < CURDATE()";
		$dbresult = mysqli_query($db, $sql);

		if (!$dbresult)
		{
			$result['successful'] = false;
			$result['error_message'] = "Error in " . __METHOD__ . ": " . mysqli_error($db) . "\n" . $sql;

			CLog::RecordNew(CLog::ERROR, $result['error_message'], "", "", false);

			mysqli_close($db);

			return $result;
		}

		$result['successful'] = true;
		mysqli_close($db);

		return $result;
	}

	static function connect()
	{

		$_db = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_DATABASE);

		return $_db;
	}

}

?>