<?php
require_once ('DAO/Booking.php');
class CUserRetention
{
	// ************************************************************
	// MISC
	// ************************************************************

	// call this from the database if need be.. but for now... let's just hard code..
	static function getFollowUpTypes($reportType)
	{
		$arr = null;
		if ($reportType == 1) $arr = array(1=>'Phone Call', 2=>'Post Card');
		else if ($reportType == 2)$arr = array(1=>'Phone Call', 3=>'Greeting Card');
		return $arr;

	}

	static function newDayTimeFormat ( $sqltime, $showDateOnly=false )
	{

		$sep = explode(" ",$sqltime);
		$daysep = explode("-",$sep[0]);

		if ($showDateOnly == false)
		{
			$timesep = explode(":",$sep[1]); // split time into hr, min, sec
			$newTime = mktime ( $timesep[0], $timesep[1], $timesep[2] );
			$formatTime = date("h:i:s A", $newTime);
		}

		$formateDate = $daysep[1] . "/" . $daysep[2] . "/" . $daysep[0];

		if ($showDateOnly == false)
			$newstr = $formateDate . " " . $formatTime;
		else
			$newstr = $formateDate;
		return $newstr;
	}

	// ************************************************************
	// CRON TASKS
	// ************************************************************

	// Cron task functional call
	static function expire_inactive_customers ()
	{
		$numDays = 121;
		$rslt = 0;

		$startingTime = date( "Y-m-d G:i:s");

		$sql = "update user_retention_data,booking,session set user_retention_data.is_active = 0 where user_retention_data.booking_id = booking.id and session.id = booking.session_id and session_start <= DATE_SUB('" . $startingTime . "', INTERVAL $numDays DAY) and user_retention_data.is_active = 1 and user_retention_data.updated_order_id is null";
		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$rslt = $user_retention_obj->query($sql);
		return $rslt;


	}
	static function disable_cancellation_edge_cases (&$additionalInfo)
	{
		$rslt = 0;
		$deployment_date = '2007-08-22 23:59:59';
		$totalrecords = 0;

		$sql = "SELECT ssub2.retention_id,".
		"ssub2.second_order, booking.order_id as initial_order, session.store_id,`user_retention_data`.`user_id`,session.session_start,ssub2.session_start as t4, ".
		"`booking`.id, ssub2.booking_id, ".
		"`booking`.`status`, ssub2.innerstatus ,`booking`.`timestamp_created` as t1, ssub2.timestamp_created as t2 ".
		"FROM ".
		"`user_retention_data` ".
		"Inner Join `booking` ON `user_retention_data`.`booking_id` = `booking`.`id` ".
		"Inner Join `session` ON `booking`.`session_id` = `session`.`id` ".
		"Inner Join ( ".
		"select sq1.second_order, sq1.innerstatus, sq1.booking_id, sq1.user_id, sq1.retention_id, sq1.timestamp_created, sq1.session_start from  ".
		"(SELECT booking.order_id as second_order,`booking`.id as booking_id, session.session_start, booking.status as innerstatus, ".
		"`user_retention_data`.`id` as retention_id, ".
		"`user_retention_data`.`user_id`, ".
		"booking.timestamp_created ".
		"FROM ".
		"`user_retention_data` ".
		"Inner Join `booking` ON `user_retention_data`.`user_id` = `booking`.`user_id` ".
		"Inner Join `session` ON `booking`.`session_id` = `session`.`id` ".
		"where is_active = 1 and `updated_order_id` is null and `number_days_inactivity` < 120  ".
		"order by user_id, booking.timestamp_created desc) as sq1 ".
		"group by sq1.user_id)  ".
		"as ssub2 ON `user_retention_data`.`user_id` = ssub2.user_id ".
		"where is_active = 1 and `updated_order_id` is null and `number_days_inactivity` < 120  ".
		"having t2 > t1 and (t2 >= '$deployment_date' ) and innerstatus = 'ACTIVE' ".
		"order by t2";



		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);

		$outputArray = array();
		while ($user_retention_obj->fetch()) {
			$neworderid = $user_retention_obj->second_order;
			$retentionid = $user_retention_obj->retention_id;
			$totalrecords++;
			$update_obj = DAO_CFactory::create("user_retention_data");
			$update_obj->id = $retentionid;
			$update_obj->is_active = 0;
			$update_obj->updated_order_id = $neworderid;
			$error = $update_obj->update();
			if ($error > 0 ) {
				$rslt++;
			}
		}

		$additionalInfo = "Records found: " . $totalrecords . " Records updated: " . $rslt;


		return $rslt;


	}

// this method is not currently in use anywhere
	static function maxDays_showInactiveStoreCounts ()
	{

		$sql = "Select store.home_office_id,store.store_name,store.city,store.state_id,store.postal_code,region.region_name,Count(user_retention_data.id) " .
		"From user_retention_data Inner Join store ON user_retention_data.store_id = store.id Left Join region ON store.region_id = region.id " .
		"where is_active = 0 and number_days_inactivity > 120 Group By store_id order by store.region_id, state_id";
		$obj = DAO_CFactory::create("user_retention_data");

		$outputArray = array();
		while ($obj->fetch()) {
			$outputArray= $booking->toArray();
		}
		return $outputArray;
	}

	static function cron_task_find_cancelled_sessions ()
	{

		$startingTime = date( "Y-m-d G:i:s");

		$sql = "SELECT `user_retention_data`.id,s2.session_start,s2.is_deleted,s2.session_publish_state,TO_DAYS('$startingTime') - TO_DAYS(s2.session_start) as timespan, `user_retention_data`.`user_id`, booking.status FROM `user_retention_data` " .
		" Inner Join `booking`  as b1 ON `user_retention_data`.`booking_id` = b1.`id` Inner Join `session`  as s2  ON b1.`session_id` = s2.`id` Inner Join `booking` ON `user_retention_data`.`updated_order_id` = `booking`.`order_id` " .
		" where `updated_order_id` is not null and booking.status = 'CANCELLED' having timespan >= 60 and timespan <= 119 ";

		$obj = DAO_CFactory::create("user_retention_data");
		$outputArray = array();
		$rslt = $obj->query($sql);
		$arr = array();
		$sucesses = 0;
		while ($obj->fetch()) {
			$arr[] = $obj->id;
		}
		foreach($arr as $element){

			$sql2 = "UPDATE `user_retention_data` SET `updated_order_id`=NULL,is_active=1 WHERE (`id`='$element')";
			$obj2 = DAO_CFactory::create("user_retention_data");
			$rslt2 = $obj2->query($sql2);
			if ($rslt2 > 0) $sucesses++;
		}
		return $sucesses;
	}
	// Cron task functional call
 	static function cron_task_add_new_inactive_customers ()
	{
		try {

		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query('START TRANSACTION;');

		$user_retention_obj->query('SET group_concat_max_len = 100000;');

		$startingTime = date( "Y-m-d G:i:s");

		$sql =  "Select TO_DAYS('" . $startingTime . "') - TO_DAYS(max(session.session_start)) as days_inactive,GROUP_CONCAT(booking.id) as booking_ids,  " .
			    " GROUP_CONCAT(session.store_id) as store_ids, GROUP_CONCAT(session.session_start) as session_ids, user_retention_data.user_id as retention_id,user_retention_data.is_active as " .
				" retention_active_bit,booking.user_id,count(booking.id) as bookings_attended " .
				" From booking Inner Join session ON booking.session_id = session.id " .
				" Left join user_retention_data ON user_retention_data.user_id = booking.user_id " .
				" Where user_retention_data.is_active is null and   status = 'ACTIVE' and session.is_deleted = 0 and booking.is_deleted = 0 and session_publish_state != 'SAVED' " .
				" group by booking.user_id  " .
				" having   (max(session.session_start)<   DATE_SUB('" . $startingTime . "', INTERVAL 60 day )) " .
				//" having   (max(session.session_start)<   DATE_SUB(now(), INTERVAL 60 day )) and (max(session.session_start) >=  DATE_SUB(now(), INTERVAL 120 day)) " .
				" order by session.session_start desc";


		$templateString ="insert into user_retention_data (id, user_id, store_id, booking_id, booking_count, timestamp_created, updated_order_id, number_days_inactivity) values ";
		$insertIntoString = $templateString;

		$insertArry = array();
		$booking = DAO_CFactory::create("booking");
		$booking->query($sql);
		$blockCount = 2500;
		$recordsProcessed = 0;
		$counter = 0;
		$blocksProcess=false;



		while ($booking->fetch()) {
				$number_days_inactivity = 0;
				$blocksProcess = true;
				$arr= $booking->toArray();
				$booking_arr = explode(",", $booking->booking_ids);
				$store_arr = explode(",", $booking->store_ids);
				$session_ids = explode(",", $booking->session_ids);

				$tempArr = array();
				foreach ($booking_arr as $key => $ele)
					$tempArr[$session_ids[$key]][$booking_arr[$key]] = $store_arr[$key];

				krsort($tempArr);
				$tarr = array_shift($tempArr);
				$keyvar = key($tarr);
				$store_id = $tarr[$keyvar];
				$booking_id = $keyvar;


				// TODO.. research why mysql delivers the string in on direction
				//$booking_id = $booking_arr[count($booking_arr)-1];
				//$store_id = $store_arr[count($store_arr)-1];


				$number_days_inactivity =  $booking->days_inactive;

				$user_id = $arr['user_id'];
				$booking_count = $arr['bookings_attended'];

				if ($user_id > 0 && $store_id > 0 && $booking_id > 0 ) {
					$newtimespan = date( "Y-m-d G:i:s");
				    $insertIntoString .= "(NULL, $user_id, $store_id, $booking_id, $booking_count, '" . $newtimespan . "' ,NULL,$number_days_inactivity),";
				}

				$counter += 1;
				if ($counter >= $blockCount) {
				    $len = strlen($insertIntoString);
					$insertIntoString = substr_replace($insertIntoString, ' ', $len-1, 1);

					$rslt = $user_retention_obj->query($insertIntoString);
					if ($rslt > 0)
					    $recordsProcessed += $rslt;
					else {
						// should we cancel all processing?
						$user_retention_obj->query('ROLLBACK;');
						return 0;
					}

					$insertIntoString = $templateString;
					$counter = 0;
				}

		}
		$len = strlen($insertIntoString);
		if ($len > 0 && $blocksProcess == true) {
			$insertIntoString = substr_replace($insertIntoString, ' ', $len-1, 1);
			$rslt = $user_retention_obj->query($insertIntoString);
			if ($rslt > 0)
			    $recordsProcessed += $rslt;
			else {
				// should we cancel all processing?
				$user_retention_obj->query('ROLLBACK;');
				return 0;
			}
		}

		} catch (exception $e) {
			CLog::RecordException($e);
			$user_retention_obj->query('ROLLBACK;');
			return 0;
		}
		if ($blocksProcess == true) $user_retention_obj->query('COMMIT;'); //end the transaction
		return $recordsProcessed;
		// remove the last instance of the comma with a closing ) since we don't want to have to run the query twice to figure out the row count
	}




	static function cron_task_returning_customers ()
	{
		try {

		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query('START TRANSACTION;');

		$startingTime = date( "Y-m-d G:i:s");

/*

		$sql = "update `user_retention_data` ,  ( " .
		"Select TO_DAYS('" . $startingTime . "') - TO_DAYS(max(session.session_start)) as days_inactive," .
		"user_retention_data.is_active as  retention_active_bit," .
		"user_retention_data.updated_order_id as updatedorderid,booking.user_id" .
		" From booking Inner Join session ON booking.session_id = session.id  " .
		" Left join user_retention_data ON user_retention_data.user_id = booking.user_id  " .
		" Where booking.status = 'ACTIVE' and session.is_deleted = 0 " .
		" and booking.is_deleted = 0 and session_publish_state != 'SAVED'  group by booking.user_id  " .
		" having  user_retention_data.is_archived = 0 and updatedorderid is not null and retention_active_bit = 0 and (max(session.session_start) " .
		" <   DATE_SUB('" . $startingTime . "', INTERVAL 60 day ))  " .
		" order by days_inactive ) as sq1 " .
		" set user_retention_data.is_archived = 0 where sq1.user_id = user_retention_data.user_id ";

		$user_retention_data_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_data_obj->query($sql);

*/



		$user_retention_obj->query('SET group_concat_max_len = 100000;');

		$sql =  "Select user_retention_data.is_archived, user_retention_data.id as t_retention_id, TO_DAYS('" . $startingTime . "') - TO_DAYS(max(session.session_start)) as days_inactive,GROUP_CONCAT(booking.id) as booking_ids,  " .
			    " GROUP_CONCAT(session.store_id) as store_ids, GROUP_CONCAT(session.session_start) as session_ids, user_retention_data.user_id as retention_id,user_retention_data.is_active as " .
				" retention_active_bit,booking.user_id,count(booking.id) as bookings_attended , user_retention_data.updated_order_id as updatedorderid" .
				" From booking Inner Join session ON booking.session_id = session.id " .
				" Left join user_retention_data ON user_retention_data.user_id = booking.user_id " .
				" Where user_retention_data.is_archived = 0 and user_retention_data.updated_order_id is not null and user_retention_data.is_active = 0 and booking.status = 'ACTIVE' and session.is_deleted = 0 and booking.is_deleted = 0 and session_publish_state != 'SAVED' " .
				" group by booking.user_id  " .
			//	" having  updatedorderid is not null and retention_active_bit = 0 and (max(session.session_start)<   DATE_SUB('" . $startingTime . "', INTERVAL 60 day )) " .
			//	" and   (max(session.session_start)<   DATE_SUB(now(), INTERVAL 60 day )) and (max(session.session_start) >=  DATE_SUB(now(), INTERVAL 120 day)) " .

				" having  (max(session.session_start)<   DATE_SUB('" . $startingTime . "', INTERVAL 60 day )) " .
				" order by session.session_start desc";


		$templateupdatestring = "update `user_retention_data` set user_retention_data.is_archived = 1 where user_retention_data.id in (";
		$updatestring = $templateupdatestring;

		$templateString ="insert into user_retention_data (id, user_id, store_id, booking_id, booking_count, timestamp_created, updated_order_id, number_days_inactivity, is_reactivated) values ";
		$insertIntoString = $templateString;

		$insertArry = array();
		$booking = DAO_CFactory::create("booking");
		$booking->query($sql);
		$blockCount = 400;
		$recordsProcessed = 0;
		$counter = 0;
		$blocksProcess=false;

		$super_id_arr = array();


		while ($booking->fetch()) {
				$number_days_inactivity = 0;
				$blocksProcess = true;
				$arr= $booking->toArray();
				$booking_arr = explode(",", $booking->booking_ids);
				$store_arr = explode(",", $booking->store_ids);
				$session_ids = explode(",", $booking->session_ids);

				$tempArr = array();
				foreach ($booking_arr as $key => $ele)
					$tempArr[$session_ids[$key]][$booking_arr[$key]] = $store_arr[$key];

				krsort($tempArr);
				$tarr = array_shift($tempArr);
				$keyvar = key($tarr);
				$store_id = $tarr[$keyvar];
				$booking_id = $keyvar;


				// TODO.. research why mysql delivers the string in on direction
				//$booking_id = $booking_arr[count($booking_arr)-1];
				//$store_id = $store_arr[count($store_arr)-1];


				$number_days_inactivity =  $booking->days_inactive;

				$user_id = $arr['user_id'];
				$booking_count = $arr['bookings_attended'];

				if ($user_id > 0 && $store_id > 0 && $booking_id > 0 ) {
					$newtimespan = date( "Y-m-d G:i:s");
				    $insertIntoString .= "(NULL, $user_id, $store_id, $booking_id, $booking_count, '" . $newtimespan . "' ,NULL,$number_days_inactivity, 1),";
					$super_id_arr[] = $arr['t_retention_id'];
				}

				$counter += 1;
				if ($counter >= $blockCount) {


				    $len = strlen($insertIntoString);
					$insertIntoString = substr_replace($insertIntoString, ' ', $len-1, 1);

					$rslt = $user_retention_obj->query($insertIntoString);

					$tempstrs = implode(",", $super_id_arr);

					$updatestring .= $tempstrs;
					$updatestring .= ")";

					$rslt2 = $user_retention_obj->query($updatestring);


					if ($rslt > 0)
					    $recordsProcessed += $rslt;
					else {
						// should we cancel all processing?
						$user_retention_obj->query('ROLLBACK;');
						return 0;
					}

					$insertIntoString = $templateString;
					$updatestring = $templateupdatestring;
					$super_id_arr = array();
					$counter = 0;
				}

		}
		$len = strlen($insertIntoString);
		if ($len > 0 && $blocksProcess == true) {
			$insertIntoString = substr_replace($insertIntoString, ' ', $len-1, 1);
			$rslt = $user_retention_obj->query($insertIntoString);


			$tempstrs = implode(",", $super_id_arr);

			$updatestring .= $tempstrs;
			$updatestring .= ")";



			$rslt2 = $user_retention_obj->query($updatestring);


			if ($rslt > 0)
			    $recordsProcessed += $rslt;
			else {
				// should we cancel all processing?
				$user_retention_obj->query('ROLLBACK;');
				return 0;
			}
		}

		} catch (exception $e) {
			CLog::RecordException($e);
			$user_retention_obj->query('ROLLBACK;');
			return 0;
		}
		if ($blocksProcess == true) $user_retention_obj->query('COMMIT;'); //end the transaction
		return $recordsProcessed;
		// remove the last instance of the comma with a closing ) since we don't want to have to run the query twice to figure out the row count
	}



		// set the is_deleted bit to 1 for any customers that are out of the ping program but placed an order
	static function cron_task_disable_ping_customers ()
	{

		$startingTime = date( "Y-m-d G:i:s");
		$rslt = 0;


		$sql = "update `user_retention_data` , " .
		"( SELECT " .
		"`user_retention_data`.id as user_retention_id, " .
		"`user_retention_data`.`user_id`, " .
		"`user_retention_data`.`booking_id`, " .
		"`booking`.`order_id`, " .
		"`session`.`session_start`, " .
		"subsessions.last_session " .
		"FROM " .
		"`user_retention_data` " .
		"Inner Join `booking` ON `user_retention_data`.`booking_id` = `booking`.`id` " .
		"Inner Join `session` ON `booking`.`session_id` = `session`.`id` " .
		"Inner Join " .
		"(SELECT " .
		"max(`session`.`session_start`) as last_session, user_id " .
		"FROM " .
	   "`booking` " .
		"Inner Join `session` ON `booking`.`session_id` = `session`.`id` " .
		"where status = 'ACTIVE' and session.is_deleted = 0  and booking.is_deleted = 0 and session_publish_state != 'SAVED' " .
		"group by booking.user_id) as subsessions ON subsessions.user_id = `user_retention_data`.`user_id` " .
		"where `user_retention_data`.`updated_order_id` is null and is_active = 0 and `has_placed_order_after_120_days` = 0 " .
		"having subsessions.last_session > `session`.`session_start`) as supersubquery " .
		"set `has_placed_order_after_120_days` = 1, user_retention_data.timestamp_updated=now() where supersubquery.user_retention_id = `user_retention_data`.id " ;

		$obj = DAO_CFactory::create("user_retention_data");
		$rslt = $obj->query($sql);
		return $rslt;


/*		$sql =  "SELECT user_retention_data.id, `user_retention_data`.`user_id`,session.session_start as last_registered_session " .
		" FROM `user_retention_data` " .
		" Inner Join `booking` ON `user_retention_data`.`booking_id` = `booking`.`id` " .
		" Inner Join `session` ON `booking`.`session_id` = `session`.`id` " .
		" Inner Join (Select max(session.session_start) as last_session, user_id  From booking Inner Join session ON booking.session_id = session.id   " .
		" Where  status = 'ACTIVE' and session.is_deleted = 0  " .
		" and booking.is_deleted = 0 and session_publish_state != 'SAVED'  group by booking.user_id    " .
		" having   (max(session.session_start) <   DATE_SUB('$startingTime', INTERVAL 60 day ))  order by session.session_start desc) as subqueryA ON  subqueryA.user_id = `user_retention_data`.`user_id` " .
		" where subqueryA.last_session > session.session_start and  is_active = 0 and updated_order_id is null and `user_retention_data`.has_placed_order_after_120_days = 0";
*/






/*
		$sql =  "SELECT " .
		"`user_retention_data`.id, `session`.`session_start` as ping_session_entry, `user_retention_data`.user_id, current_session_tbl.last_session, current_session_tbl.order_id " .
		"FROM " .
		"`user_retention_data`" .
		"Inner Join `booking` ON `user_retention_data`.`booking_id` = `booking`.`id`" .
		"Inner Join `session` ON `booking`.`session_id` = `session`.`id`" .
		"Inner Join " .
		"(" .
		"select tblview.last_session, tblview.order_id,tblview.user_id from (" .
		"Select session.session_start as last_session, order_id, user_id  From booking Inner Join session ON booking.session_id = session.id   " .
		"Where  status = 'ACTIVE' and session.is_deleted = 0   and booking.is_deleted = 0 and session_publish_state != 'SAVED'" .
		"order by session.session_start desc) tblview " .
		"limit 1 " .
		") as current_session_tbl ON  current_session_tbl.user_id =  `user_retention_data`." .
		"where booking.status = 'ACTIVE' and session.is_deleted = 0   and booking.is_deleted = 0 and session.session_publish_state != 'SAVED' " .
		"and `user_retention_data`.`updated_order_id` is null and `user_retention_data`.`has_placed_order_after_120_days` = " .
		"having last_session > ping_session_entry" ;
*/



/*

		$obj = DAO_CFactory::create("user_retention_data");
		$obj->query($sql);

		while ($obj->fetch()) {
			$id = $obj->id;
			$sql2 = "update user_retention_data set has_placed_order_after_120_days = 1 where user_retention_data.id = '$id'";

			$obj2 = DAO_CFactory::create("user_retention_data");

			$rslt2 = $obj2->query($sql2);
			if ($rslt2 > 0) $sucesses++;

		}

		return $sucesses;
*/


	}


	// ************************************************************
	// DB1 REPORTING
	// ************************************************************
	static function maxDays_showCustomers( $startDate, $endDate, $store_id,  $rowNum, $rowCount  )
	{

		/*

		Select  user_retention_data.user_id,`user`.lastname,`user`.firstname,address.address_line1, address.address_line2,address.city,address.state_id,
		address.postal_code,`user`.primary_email,`user`.telephone_1,`user`.telephone_2,`user`.telephone_1_call_time,session.session_start,session.session_type,session.sneak_peak,
		session.session_password,TO_DAYS(now()) - TO_DAYS(session.session_start) as days_inactive,user_retention_data.booking_count, GROUP_CONCAT(payment.payment_type SEPARATOR ',') as payment_type,
		 booking.booking_type  From user_retention_data Inner Join `user` ON user_retention_data.user_id = `user`.id

		 Inner Join address ON `user`.id = address.user_id

		 Inner Join booking ON user_retention_data.booking_id = booking.id Inner Join session ON booking.session_id = session.id
		 Inner Join store ON user_retention_data.store_id = store.id Inner Join orders ON booking.order_id = orders.id
		 Inner Join payment ON booking.order_id = payment.order_id Where user_retention_data.is_active = 0 AND  user_retention_data.store_id = 152 AND   user_retention_data.updated_order_id Is Null and user_type = 'CUSTOMER'  group by user_retention_data.user_id having days_inactive >= 120 and days_inactive <= 179 order by session.session_start limit 0,50


		*/


		if ($store_id === "all") $store_id = null;

		//$label = "Guest ID,Last Name,First Name,Street Name,City,State,Postal Code,Country,email,Day Phone,Evening Phone," .
		//"Call Time,Last Session,Last Session Type,Sessions Attended,Days Inactive When Identified,Gift Card Used";


		$sql = "Select ";

		if (is_null($store_id)) $sql .= " store.store_name, store.home_office_id, " ;


		$sql .= " user_retention_data.user_id,`user`.lastname,`user`.firstname,address.address_line1," .
		" address.address_line2,address.city,address.state_id,address.postal_code,`user`.primary_email,`user`.telephone_1,`user`.telephone_2,`user`.telephone_1_call_time,session.session_start," .
		"session.session_type,session.sneak_peak," .
		"session.session_password,user_retention_data.booking_count, " ;

		if (is_null($startDate) && is_null($endDate))
			$sql .= "user_retention_data.number_days_inactivity as days_inactive," ;
		else
			$sql .= "TO_DAYS(now()) - TO_DAYS(session.session_start) as days_inactive," ;

		$sql .= "GROUP_CONCAT(payment.payment_type SEPARATOR ',') as payment_type, booking.booking_type, orders.bundle_id ";


		$sql .= " From user_retention_data" .
		" Inner Join `user` ON user_retention_data.user_id = `user`.id Inner Join address ON `user`.id = address.user_id" .
		" Inner Join booking ON user_retention_data.booking_id = booking.id Inner Join session ON booking.session_id = session.id" .
		" Inner Join store ON user_retention_data.store_id = store.id" .
		" Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join payment ON booking.order_id = payment.order_id" .
		" Where user_retention_data.is_active = 0 AND ";

		if (!is_null($store_id))  $sql .= " user_retention_data.store_id = $store_id AND ";


		$sql .=  "  has_placed_order_after_120_days=0 and user_retention_data.updated_order_id Is Null and user_type = 'CUSTOMER' " ;

		if (is_null($startDate) && is_null($endDate))
			$sql .= " and number_days_inactivity >= 120" ;

		$sql .= " group by user_retention_data.user_id" ;

		if (!is_null($endDate) && !is_null($startDate))
			$sql .= " having days_inactive >= $startDate and days_inactive <= $endDate" ;
		else if (is_null($endDate) && !is_null($startDate))
			$sql .= " having days_inactive >= $startDate" ;



		if (!is_null($store_id))
			$sql .= " order by session.session_start";
		else
			$sql .= " order by store.store_name";

		if (!is_null($store_id) && $rowCount >0)  $sql .= " limit $rowNum,$rowCount ";

		$obj = DAO_CFactory::create("user_retention_data");
		$obj->query($sql);


		$outputArray = array();
		while ($obj->fetch()) {

			$sessionTypeStr = $obj->session_type;

			$temp = $obj->toArray();

			unset($temp['fax']);
			unset($temp['usps_adc']);
			unset($temp['id']);
			unset($temp['store_id']);
			unset($temp['booking_id']);
			unset($temp['updated_order_id']);
			unset($temp['timestamp_created']);
			unset($temp['timestamp_updated']);
			unset($temp['is_active']);

			$paymentType = explode(',', $temp['payment_type']);
			$temp['payment_type'] = "No";
			if (in_array('GIFT_CERT', $paymentType)) $temp['payment_type'] = "yes";
			if (in_array('STORE_CREDIT', $paymentType)) $temp['payment_type'] = "yes**";

			$session_type = 'Regular';
			if ($temp['booking_type'] == "INTRO") $session_type = "Intro Offer";
			if ($temp['sneak_peak'] == 1) $session_type = "Sneak Peek";
			if (!empty($temp['session_password'])) $session_type = "Private Party";
			if ($session_type == "Intro Offer" && $obj->bundle_id > 0) $session_type = "TV Offer";

			if ($sessionTypeStr == CSession::DREAM_TASTE) $session_type = "Meal Prep Workshop";

			unset($temp['booking_type']);
			unset($temp['sneak_peak']);
			unset($temp['session_password']);

			$temp['session_start'] = CUserRetention::newDayTimeFormat ( $temp['session_start'] );

			if (!empty($temp['address_line2'])) $temp['address_line1'] .= ", " . $temp['address_line2'];
			unset($temp['address_line2']);

			if (empty($temp['number_days_inactivity']))  unset($temp['number_days_inactivity']);
			$outputArray[] = $temp;
		}
		return $outputArray;

	}

	static function maxDays_showCustomersCount( $startDate, $endDate, $store_id)
	{
		/*
		Select count(*) as total_records From user_retention_data Inner Join `user` ON user_retention_data.user_id = `user`.id
		Inner Join address ON `user`.id = address.user_id Inner Join booking ON user_retention_data.booking_id = booking.id
		Inner Join session ON booking.session_id = session.id Inner Join store ON user_retention_data.store_id = store.id
		Inner Join orders ON booking.order_id = orders.id Inner Join payment ON booking.order_id = payment.order_id
		Where user_retention_data.is_active = 0 AND  user_retention_data.store_id = 152 AND
		user_retention_data.updated_order_id Is Null and user_type = 'CUSTOMER'
		and ( TO_DAYS(now()) - TO_DAYS(session.session_start)  >= 120 and TO_DAYS(now()) - TO_DAYS(session.session_start)  <= 179 )

		*/

		if ($store_id === "all") $store_id = null;
		$sql = "select count(sq1.total_records) as total_records from ( Select user_retention_data.user_id as total_records";
		$sql .= " From user_retention_data" .
		" Inner Join `user` ON user_retention_data.user_id = `user`.id Inner Join address ON `user`.id = address.user_id" .
		" Inner Join booking ON user_retention_data.booking_id = booking.id Inner Join session ON booking.session_id = session.id" .
		" Inner Join store ON user_retention_data.store_id = store.id" .
		" Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join payment ON booking.order_id = payment.order_id" .
		" Where has_placed_order_after_120_days= 0 and user_retention_data.is_active = 0 AND ";
		if (!is_null($store_id))  $sql .= " user_retention_data.store_id = $store_id AND ";
		$sql .=  "  user_retention_data.updated_order_id Is Null and user_type = 'CUSTOMER' " ;
		if (is_null($startDate) && is_null($endDate))
			$sql .= " and number_days_inactivity >= 120" ;
		if (!is_null($endDate) && !is_null($startDate))
			$sql .= " and ( TO_DAYS(now()) - TO_DAYS(session.session_start)  >= $startDate and TO_DAYS(now()) - TO_DAYS(session.session_start)  <= $endDate )  group by user_retention_data.user_id ) as sq1" ;
		else if (is_null($endDate) && !is_null($startDate))
			$sql .= " and TO_DAYS(now()) - TO_DAYS(session.session_start)  >= $startDate group by user_retention_data.user_id ) as sq1" ;
		$obj = DAO_CFactory::create("user_retention_data");
		$obj->query($sql);
		$obj->fetch();
		$count = $obj->total_records;
		return $count;
	}

	// ************************************************************
	// RETENTION REPORTS
	// ************************************************************

	static function LocateInactivityRecords ($reportType, $startDay, $endDay, $store_id, &$follow_up_array, $re_sort_string="session.session_start, user_retention_data.booking_count, user.lastname")
	{


	    /*
	     * report type 1 = 60 to 89 day inactivity
	     * report type 2 = 90 to 119 day inactivity
	     */



		/*
		$sql = "Select user.id as user_id, `user`.lastname,`user`.firstname, address.address_line1 , address.address_line2,  " .
		"address.city,address.state_id,address.postal_code,user.primary_email, `user`.telephone_1," .
		"`user`.telephone_2,`user`.telephone_1_call_time,session.session_start,booking.booking_type,session.session_type,session.sneak_peak," .
		"session.session_password," .
		"user_retention_data.booking_count,TO_DAYS(now()) - TO_DAYS(session.session_start) as days_inactive," .
		"GROUP_CONCAT(payment.payment_type SEPARATOR ',') as payment_type, GROUP_CONCAT(IF(user_retention_data_follow_up.follow_up_date is null, 'null', " .
		" user_retention_data_follow_up.follow_up_date)  SEPARATOR '^^^') as followupdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_date is null, 'null', user_retention_data_follow_up.results_date) SEPARATOR '^^^') as resultdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_comments is null, 'null', user_retention_data_follow_up.results_comments) SEPARATOR '^^^') as followNotes," .
		"GROUP_CONCAT(IF(user_retention_action_type.Action_type is null , 'null', user_retention_action_type.Action_type) SEPARATOR '^^^') as followTypes," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.id is null , 'null', user_retention_data_follow_up.id) SEPARATOR '^^^') as followIds," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.leading_report_identifier is null, 'null', user_retention_data_follow_up.leading_report_identifier) SEPARATOR '^^^') as report_identifiers," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.timestamp_created is null, 'null', user_retention_data_follow_up.timestamp_created) SEPARATOR '^^^') as report_entries_timestamp, " .
		"user_retention_data.id as retention_id" .
		" From user_retention_data" .
		" Inner Join booking ON user_retention_data.booking_id = booking.id" .
		" Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join session ON booking.session_id = session.id" .
		" Inner Join `user` ON user_retention_data.user_id = `user`.id" .
		" Inner Join address ON booking.user_id = address.user_id" .
		" Inner Join payment ON booking.order_id = payment.order_id" .
		" Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id" .
		" Left Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id" .
		" where session.store_id = $store_id  and user_retention_data.is_active = 1 and user_retention_data.updated_order_id is null" .
		" group by user_retention_data.user_id" .
		" having days_inactive >= $startDay and days_inactive <= $endDay" .
		" order by $re_sort_string";
		*/

		// should we based this on the time zone?


		$sql = "Select user.id as user_id, `user`.lastname,`user`.firstname, address.address_line1 , address.address_line2,  " .
		"address.city,address.state_id,address.postal_code,user.primary_email, `user`.telephone_1, " .
		"`user`.telephone_2,`user`.telephone_1_call_time,session.session_start,booking.booking_type,session.session_type,session.sneak_peak," .
		"session.session_password," .
		"user_retention_data.booking_count,TO_DAYS(now()) - TO_DAYS(session.session_start) as days_inactive, `user`.dream_reward_status, `user`.dream_reward_level, " ;

		if ($reportType==1) {
			$sql .= " 0 as ReferralCredit, 0 as DirectCredit, ";
		}

		else if ($reportType == 2) {
			$sql .= " 0 as ReferralCreditExpired, 0 as DirectCreditExpired,  " ;
		}


		$sql .= "GROUP_CONCAT(payment.payment_type SEPARATOR ',') as payment_type, orders.bundle_id, " .
		"user_retention_data.id as retention_id, `is_reactivated`" .
		" From user_retention_data" .
		" Inner Join booking ON user_retention_data.booking_id = booking.id" .
		" Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join session ON booking.session_id = session.id" .
		" Inner Join `user` ON user_retention_data.user_id = `user`.id" .
		" Inner Join address ON booking.user_id = address.user_id" .
		" Inner Join payment ON booking.order_id = payment.order_id" .
		" Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id" .
		" Left Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id" .
		" where `user_retention_data`.`is_archived` = 0  and session.store_id = $store_id  and user_retention_data.is_active = 1 and user_retention_data.updated_order_id is null" .
		" group by user_retention_data.user_id" .
		" having days_inactive >= $startDay and days_inactive <= $endDay" .
		" order by $re_sort_string";


		$user_array = array();
		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);
		$userlist = array();
		while ($user_retention_obj->fetch()) {
			$arr = $user_retention_obj->toArray();

			CUserRetention::processActiveRecords ($user_array, $arr, $reportType, $user_retention_obj);
			$userlist[$arr['retention_id']]= $arr['user_id'];

		}


		$follow_up_array = CUserRetention::LocateFollowUpRecords ($reportType, $startDay, $endDay, $store_id, $re_sort_string);

		CUserRetention::BuildCreditReport ($user_array,$userlist, $reportType, $startDay, $endDay, $store_id);



		return $user_array;
	}

	static function BuildCreditReport (&$user_array,$userlistarr, $reportType, $startDay, $endDay, $store_id)
	{

	$userlist = implode(',', $userlistarr);

	if (empty($userlist)) return;

	$rows = NULL;
			$Store_Credit = DAO_CFactory::create('store_credit');
///$userlist = "572738," . $userlist;

		$varStr = "Select store_credit.is_redeemed, store_credit.is_expired, store.id as store_id, store.store_name, store_credit.user_id, store_credit.credit_card_number, " .
			" store_credit.date_original_credit, store_credit.amount,store_credit.payment_transaction_number,  store_credit.timestamp_created, " .
			" store_credit.credit_type, cr.origination_type_code " .
			 " From store_credit Inner Join `store` ON store_credit.store_id = `store`.id left join customer_referral cr on cr.store_credit_id = store_credit.id " .
			" where store_credit.user_id  in ($userlist)  and store_credit.is_deleted = 0  order by store_credit.timestamp_created";


			$Store_Credit->query($varStr);
			$rows = array();
			while ($Store_Credit->fetch()) {
			  	$tarray = $Store_Credit->toArray();

$userid = $tarray['user_id'];
					$retentionid = array_search($userid, $userlistarr);

			  	// break up the user array...


				if ($reportType == 1) {  // we are looking for available credit




					if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 0) {
						if ($tarray['credit_type'] == 2){

							if (!empty($user_array[$retentionid])) {
								$user_array[$retentionid]['ReferralCredit'] += $tarray['amount'];
							}


						}
						else if ($tarray['credit_type'] == 3) {
						if (!empty($user_array[$retentionid])) {
								$user_array[$retentionid]['DirectCredit'] += $tarray['amount'];
							}

						}
					}

				}
				else if ($reportType == 2) {  // we are looking for available credit

					if ($tarray['is_redeemed'] == 0 && $tarray['is_expired'] == 1) {


						if ($tarray['credit_type'] == 2){

							if (!empty($user_array[$retentionid])) {
								$user_array[$retentionid]['ReferralCreditExpired'] += $tarray['amount'];
							}


						}
						else if ($tarray['credit_type'] == 3) {
						if (!empty($user_array[$retentionid])) {
								$user_array[$retentionid]['DirectCreditExpired'] += $tarray['amount'];
							}

						}


					}
			  	}

}


	}


	static function LocateFollowUpRecords ($reportType, $startDay, $endDay, $store_id, $re_sort_string)
	{
		$follow_up_array = null;

		$sql = "Select user.id as user_id," .
		"user_retention_data.booking_count,TO_DAYS(now()) - TO_DAYS(session.session_start) as days_inactive," .
		" GROUP_CONCAT(IF(user_retention_data_follow_up.follow_up_date is null, 'null', " .
		" user_retention_data_follow_up.follow_up_date)  SEPARATOR '^^^') as followupdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_date is null, 'null', user_retention_data_follow_up.results_date) SEPARATOR '^^^') as resultdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_comments is null, 'null', user_retention_data_follow_up.results_comments) SEPARATOR '^^^') as followNotes," .
		"GROUP_CONCAT(IF(user_retention_action_type.Action_type is null , 'null', user_retention_action_type.Action_type) SEPARATOR '^^^') as followTypes," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.id is null , 'null', user_retention_data_follow_up.id) SEPARATOR '^^^') as followIds," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.leading_report_identifier is null, 'null', user_retention_data_follow_up.leading_report_identifier) SEPARATOR '^^^') as report_identifiers," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.timestamp_created is null, 'null', user_retention_data_follow_up.timestamp_created) SEPARATOR '^^^') as report_entries_timestamp, " .
		"GROUP_CONCAT(user_retention_data_follow_up.is_deleted SEPARATOR '^^^') as deleted_items, " .
		"user_retention_data.id as retention_id" .
		" From user_retention_data" .
		" Inner Join booking ON user_retention_data.booking_id = booking.id" .
		" Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join session ON booking.session_id = session.id" .
		" Inner Join `user` ON user_retention_data.user_id = `user`.id" .
		" Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id" .
		" Left Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id" .
		" where `user_retention_data`.`is_archived` = 0  and session.store_id = $store_id  and user_retention_data.is_active = 1 and user_retention_data.updated_order_id is null" .
		" group by user_retention_data.user_id" .
		" having days_inactive >= $startDay and days_inactive <= $endDay and followupdates != 'null' " .
		" order by $re_sort_string";


		$user_array = array();
		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);
		while ($user_retention_obj->fetch()) {
			$arr = $user_retention_obj->toArray();
			if (!empty($arr['followupdates']))
				 CUserRetention::processFollowUps ($user_array, $arr, $reportType, $user_retention_obj, $follow_up_array);
		}

		return $follow_up_array;
	}



	static function LocateInactivityToActivityRecordsSummation ($reportType, $startDay, $endDay, $store_id)
	{
		// NOTE: Bad that have to iterate through each record, mysql wouldn't do a group by on the store id and return the proper counts...
	//	$sql = "Select Count(user_retention_data.id) as countvalue From user_retention_data	Inner Join `booking`
	// ON `user_retention_data`.updated_order_id = `booking`.`order_id` Inner Join `session` ON `booking`.`session_id` =
	// `session`.`id` Where  status = 'ACTIVE' and session.is_deleted = 0 and booking.is_deleted = 0 and session_publish_state
	// != 'SAVED' and user_retention_data.updated_order_id = `booking`.order_id  and user_retention_data.store_id = $store_id
	// and number_days_inactivity >= $startDay and number_days_inactivity <= $endDay and user_retention_data.updated_order_id
	// Is Not Null and is_active = 0";


		$sql = "select count(*) as countvalue from ( select  TO_DAYS(orders.timestamp_created) - TO_DAYS(session.session_start) as days_inactive" .
		" From user_retention_data	" .
		" Inner Join `booking` as previous_booking ON `user_retention_data`.booking_id = previous_booking.id" .
		" Inner Join `session` ON previous_booking.`session_id` = `session`.`id` " .
		" Inner Join orders ON user_retention_data.updated_order_id = orders.id " .
		" Where `user_retention_data`.`is_archived` = 0 and user_retention_data.store_id = $store_id and previous_booking.status = 'ACTIVE' and session.is_deleted = 0 and previous_booking.is_deleted = 0 " .
		" and session.session_publish_state != 'SAVED'  and user_retention_data.store_id = $store_id" .
		" and user_retention_data.updated_order_id Is Not Null and is_active = 0" .
		" having days_inactive >=$startDay and days_inactive <= $endDay) as sq1";


		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);
		$total_count = 0; $report_count = 0; $results_count = 0;
		$user_retention_obj->fetch();
		return array('total_records' => $user_retention_obj->countvalue);
	}




	static function processActiveRecords (&$user_array, &$arr, $reportType, $user_retention_obj)
	{

			$session_type = 'Regular';
			if ($arr['booking_type'] == "INTRO") $session_type = "Intro Offer";
			if ($arr['sneak_peak'] == 1) $session_type = "Sneak Peek";
			if (!empty($arr['session_password'])) $session_type = "Private Party";
			if ($session_type == "Intro Offer" && $arr['bundle_id'] > 0) $session_type = "TV Offer";



			// group concating is a bit more processing but will save us n trips to the database (where n = number of rows matching)
			// given the current ui requirements, we will only see two array elements for each of the following types
			// for 60-89 reports (1) and 90-119 followups (2)

			/*
			$result_dates = explode('^^^', $arr['resultdates']);

			$time_created = explode('^^^', $arr['report_entries_timestamp']);
			$follow_dates = explode('^^^', $arr['followupdates']);
			$comments = explode('^^^', $arr['followNotes']);
			$identifiers = explode('^^^', $arr['report_identifiers']);
			$followTypes = explode('^^^', $arr['followTypes']);
			$followids = explode('^^^', $arr['followIds']);

			*/


			$paymentType = explode(',', $arr['payment_type']);
			$arr['payment_type'] = "No";
			if (in_array('GIFT_CERT', $paymentType)) $arr['payment_type'] = "yes";
			if (in_array('STORE_CREDIT', $paymentType)) $arr['payment_type'] = "yes**";

			// less work than copying over to new array?
			if (!empty($arr['address_line2'])) $arr['address_line1'] .= ", " . $arr['address_line2'];
			unset($arr['address_line2']);
		/*	unset($arr['resultdates']);
			unset($arr['report_entries_timestamp']);
			unset($arr['followupdates']);
			unset($arr['followNotes']);
			unset($arr['report_identifiers']);
			unset($arr['followTypes']);
			unset($arr['followIds']);
			unset($arr['retention_id']);
		*/

			unset($arr['id']);
			unset($arr['session_password']);
			unset($arr['sneak_peak']);
			unset($arr['booking_type']);
			unset($arr['store_id']);
			unset($arr['booking_id']);
			unset($arr['is_active']);
			unset($arr['updated_order_id']);
			unset($arr['timestamp_updated']);
			unset($arr['timestamp_created']);
	/*
			$arr['follow_action'] = null;
			$arr['follow_date'] = null;
			$arr['comments'] = null;
			$arr['results_date'] = null;
	*/
			$arr['session_start'] = CUserRetention::newDayTimeFormat ( $arr['session_start']  );

			unset($arr['number_days_inactivity']) ;

			//$arr['retention_id'] = $rid;

/*
			$sortArray = array();
			if ($identifiers[0] != "null") {
				foreach ($identifiers as $key => $elements) {
					//$sortArray[$follow_dates[$key]][$elements][$followids[$key]] =
					$tarrd = array('key'=>$followids[$key], 'report_id' => $elements, 'result_dates' => $result_dates[$key], 'time_created' => $time_created[$key], 'follow_types' => $followTypes[$key] , 'comment' => $comments[$key], 'follow_dates' => $follow_dates[$key]);
					$sortArray[$elements][] = $tarrd;


				}
			}
			// now that everything is resort... find the top most item.. if this was a potentially long array, we should do this above.. but it will only be 1 to 2 elements
			if (count($sortArray) > 0) {

				$follow_up_array[$user_retention_obj->user_id][$user_retention_obj->retention_id] = $sortArray;
			}
		*/

			$arr['session_type'] = $session_type;
			$user_array[$user_retention_obj->retention_id] = $arr;
			//$user_array[$user_retention_obj->user_id] = $arr;
	}

	static function processFollowUps (&$user_array, &$arr, $reportType, $user_retention_obj, &$follow_up_array)
	{



			$result_dates = explode('^^^', $arr['resultdates']);

			$time_created = explode('^^^', $arr['report_entries_timestamp']);
			$follow_dates = explode('^^^', $arr['followupdates']);
			$comments = explode('^^^', $arr['followNotes']);
			$identifiers = explode('^^^', $arr['report_identifiers']);
			$followTypes = explode('^^^', $arr['followTypes']);
			$followids = explode('^^^', $arr['followIds']);


			$deletedItems =  explode('^^^', $arr['deleted_items']);


			unset($arr['resultdates']);
			unset($arr['report_entries_timestamp']);
			unset($arr['followupdates']);
			unset($arr['followNotes']);
			unset($arr['report_identifiers']);
			unset($arr['followTypes']);
			unset($arr['followIds']);
			unset($arr['deleted_items']);

			//unset($arr['retention_id']);



			$arr['follow_action'] = null;
			$arr['follow_date'] = null;
			$arr['comments'] = null;
			$arr['results_date'] = null;


			unset($arr['number_days_inactivity']) ;

			//$arr['retention_id'] = $rid;


			$sortArray = array();
			if ($identifiers[0] != "null") {
				foreach ($identifiers as $key => $elements) {
					//$sortArray[$follow_dates[$key]][$elements][$followids[$key]] =

					$isDeletedItem = $deletedItems[$key];
					if ($isDeletedItem == 1) continue;

					$newresultdate = $result_dates[$key];
					if ($newresultdate != "null") {
						$resultsDateKey = explode("-", $newresultdate);
						$newresultdate = $resultsDateKey[1] . '/' . $resultsDateKey[2] . '/'  . $resultsDateKey[0];

					}
					else
						$newresultdate = null;

					$followup = $follow_dates[$key];
					if ($followup != "null") {
						$resultsDateKey = explode("-", $followup);
						$followup = $resultsDateKey[1] . '/' . $resultsDateKey[2] . '/'  . $resultsDateKey[0];

					}
					else
						$followup = null;


					if ($comments[$key] == "null")
						$comments[$key] = null;


					$tarrd = array('key'=>$followids[$key], 'report_id' => $elements, 'result_dates' => $newresultdate, 'time_created' => $time_created[$key], 'follow_types' => $followTypes[$key] , 'comment' => $comments[$key], 'follow_dates' => $followup);
					$sortArray[$elements][] = $tarrd;


				}
			}
			// now that everything is resort... find the top most item.. if this was a potentially long array, we should do this above.. but it will only be 1 to 2 elements
			if (count($sortArray) > 0) {

				$follow_up_array[$user_retention_obj->user_id][$user_retention_obj->retention_id] = $sortArray;
			}



			$user_array[$user_retention_obj->user_id] = $arr;
	}

	static function LocateFollowUpRecordsSuccesses ($reportType, $startDay, $endDay, $store_id, $re_sort_string)
	{
		$follow_up_array = null;

		$sql = "Select " .
		"`user`.id as user_id,TO_DAYS(orders.timestamp_created) - TO_DAYS(previous_sessions.session_start) as days_inactive, " .
		" GROUP_CONCAT(IF(user_retention_data_follow_up.follow_up_date is null, 'null', " .
		" user_retention_data_follow_up.follow_up_date)  SEPARATOR '^^^') as followupdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_date is null, 'null', user_retention_data_follow_up.results_date) SEPARATOR '^^^') as resultdates," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.results_comments is null, 'null', user_retention_data_follow_up.results_comments) SEPARATOR '^^^') as followNotes," .
		"GROUP_CONCAT(IF(user_retention_action_type.Action_type is null , 'null', user_retention_action_type.Action_type) SEPARATOR '^^^') as followTypes," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.id is null , 'null', user_retention_data_follow_up.id) SEPARATOR '^^^') as followIds," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.leading_report_identifier is null, 'null', user_retention_data_follow_up.leading_report_identifier) SEPARATOR '^^^') as report_identifiers," .
		"GROUP_CONCAT(IF(user_retention_data_follow_up.timestamp_created is null, 'null', user_retention_data_follow_up.timestamp_created) SEPARATOR '^^^') as report_entries_timestamp, " .
		"GROUP_CONCAT(user_retention_data_follow_up.is_deleted SEPARATOR '^^^') as deleted_items, " .
		"user_retention_data.id as retention_id " .
		"From " .
		"user_retention_data " .
		"Inner Join orders ON user_retention_data.updated_order_id = orders.id " .
		"Inner Join booking ON user_retention_data.updated_order_id = booking.order_id " .
		"Inner Join session ON booking.session_id = session.id " .
		"Inner Join `user` ON user_retention_data.user_id = `user`.id " .
		"Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id " .
		"Left Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id " .
		"Left Join address ON `user`.id = address.id and address.location_type = 'BILLING' " .
		"Inner Join (Select session.session_start,user_retention_data.user_id From " .
		"user_retention_data Inner Join booking ON user_retention_data.booking_id = booking.id Inner Join session ON booking.session_id = session.id " .
		"Where user_retention_data.is_archived = 0 and user_retention_data.updated_order_id Is Not Null AND user_retention_data.store_id  = $store_id order by user_id " .
		") as previous_sessions ON user_retention_data.user_id = previous_sessions.user_id " .

		"where user_retention_data.store_id = $store_id  " .
		"group by booking.user_id  having days_inactive >= $startDay and days_inactive <= $endDay " .
		"order by $re_sort_string";



		$user_array = array();
		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);
		while ($user_retention_obj->fetch()) {
			$arr = $user_retention_obj->toArray();
			if (!empty($arr['followupdates']))
				 CUserRetention::processFollowUps ($user_array, $arr, $reportType, $user_retention_obj, $follow_up_array);
		}

		return $follow_up_array;
	}

	static function LocateInactivityToActivityRecords ($reportType, $startDay, $endDay, $store_id, &$follow_up_array, $re_sort_string="session.session_start, user_retention_data.booking_count, user.lastname")
	{
		$user_array = array();
		$sql = "Select " .
		"`user`.id as user_id,`user`.lastname,`user`.firstname,address.address_line1, address.address_line2, " .
		"address.city,address.state_id,address.postal_code,`user`.primary_email, " .
		"`user`.telephone_1,`user`.telephone_2,`user`.telephone_1_call_time, " .
		//"session.session_start,'' as 'session_type', booking.booking_type,session.sneak_peak, " .

		"last_session_time.final_session as session_start,'' as 'session_type', booking.booking_type,session.sneak_peak, " .

		"session.session_password,user_retention_data.booking_count,orders.grand_total,TO_DAYS(orders.timestamp_created) - TO_DAYS(previous_sessions.session_start) as days_inactive, " .
		" `user`.dream_reward_status, `user`.dream_reward_level, " .
		"GROUP_CONCAT(payment.payment_type SEPARATOR ',') as payment_type, orders.bundle_id, " .
	// "GROUP_CONCAT(IF(user_retention_data_follow_up.follow_up_date is null, 'null', " .
		//" user_retention_data_follow_up.follow_up_date)  SEPARATOR '^^^') as followupdates," .
	//	"GROUP_CONCAT(IF(user_retention_data_follow_up.results_date is null, 'null', user_retention_data_follow_up.results_date) SEPARATOR '^^^') as resultdates," .
	//	"GROUP_CONCAT(IF(user_retention_data_follow_up.results_comments is null, 'null', user_retention_data_follow_up.results_comments) SEPARATOR '^^^') as followNotes," .
	//	"GROUP_CONCAT(IF(user_retention_action_type.Action_type is null , 'null', user_retention_action_type.Action_type) SEPARATOR '^^^') as followTypes," .
	//	"GROUP_CONCAT(IF(user_retention_data_follow_up.id is null , 'null', user_retention_data_follow_up.id) SEPARATOR '^^^') as followIds," .
	//	"GROUP_CONCAT(IF(user_retention_data_follow_up.leading_report_identifier is null, 'null', user_retention_data_follow_up.leading_report_identifier) SEPARATOR '^^^') as report_identifiers," .
	//	"GROUP_CONCAT(IF(user_retention_data_follow_up.timestamp_created is null, 'null', user_retention_data_follow_up.timestamp_created) SEPARATOR '^^^') as report_entries_timestamp, " .
		"user_retention_data.id as retention_id " .
		"From " .
		"user_retention_data " .
		"Inner Join orders ON user_retention_data.updated_order_id = orders.id " .
		"Inner Join booking ON user_retention_data.updated_order_id = booking.order_id " .
		"Inner Join session ON booking.session_id = session.id " .
		"Inner Join `user` ON user_retention_data.user_id = `user`.id " .
		"Inner Join payment ON booking.order_id = payment.order_id " .
	//	"Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id " .
	//	"Left Join user_retention_action_type ON user_retention_data_follow_up.user_retention_action_type_id = user_retention_action_type.id " .
		"Left Join address ON `user`.id = address.user_id  and address.location_type = 'BILLING' " .
		"Inner Join (Select session.session_start,user_retention_data.user_id From " .
		"user_retention_data Inner Join booking ON user_retention_data.booking_id = booking.id Inner Join session ON booking.session_id = session.id " .
		"Where user_retention_data.is_archived = 0 and user_retention_data.updated_order_id Is Not Null AND user_retention_data.store_id  = $store_id order by user_id " .
		") as previous_sessions ON user_retention_data.user_id = previous_sessions.user_id " .

		"Left Join (Select max(session.session_start) as final_session,user_retention_data.user_id From " .
		"user_retention_data Inner Join booking ON user_retention_data.user_id = booking.user_id Inner Join session ON booking.session_id = session.id " .
		"Where user_retention_data.is_archived = 0 and user_retention_data.updated_order_id Is Not Null AND user_retention_data.store_id  = $store_id and booking.status = 'ACTIVE' " .
		" and session.is_deleted = 0 and booking.is_deleted = 0 and session_publish_state != 'SAVED'  group by user_id " .
		") as last_session_time ON user_retention_data.user_id = last_session_time.user_id " .

		"where  booking.status = 'ACTIVE' and session.is_deleted = 0 and booking.is_deleted = 0 and session_publish_state != 'SAVED' and user_retention_data.store_id = $store_id  " .
		"group by booking.user_id  having days_inactive >= $startDay and days_inactive <= $endDay " .
		"order by $re_sort_string";


		$user_array = array();
		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);

		while ($user_retention_obj->fetch()) {
			$arr = $user_retention_obj->toArray();
			CUserRetention::processActiveRecords ($user_array, $arr, $reportType, $user_retention_obj);
		}

		$follow_up_array = CUserRetention::LocateFollowUpRecordsSuccesses ($reportType, $startDay, $endDay, $store_id, $re_sort_string);

		return $user_array;


	}


	static function LocateInactivityRecordsSummation ($reportType, $startDay, $endDay,$storeid)
	{


		// NOTE: Bad that have to iterate through each record, mysql wouldn't do a group by on the store id and return the proper counts...
		$sql = "Select user_retention_data.user_id, TO_DAYS(now()) - TO_DAYS(session.session_start) as days,
		IF (user_retention_data_follow_up.leading_report_identifier = $reportType, 1, NULL) as report_count,
		IF (user_retention_data_follow_up.results_date is not null, 1, NULL) as results_count, user_retention_data_follow_up.is_deleted
		From
		user_retention_data
		Inner Join booking ON user_retention_data.booking_id = booking.id
		Inner Join session ON booking.session_id = session.id
		Left Join user_retention_data_follow_up ON user_retention_data.id = user_retention_data_follow_up.user_retention_data_id
		where user_retention_data.is_archived = 0 and session.store_id = $storeid and user_retention_data.is_active = 1 and user_retention_data.updated_order_id is null
		having days >= $startDay and days <= $endDay";

		$deletedfollowups = 0;
		$deletedresults = 0;


		$user_retention_obj = DAO_CFactory::create("user_retention_data");
		$user_retention_obj->query($sql);
		$total_count = 0; $report_count = 0; $results_count = 0;
		$userArray = array();
		while ($user_retention_obj->fetch()) {
			$userArray[$user_retention_obj->user_id] = 1;
			if ($user_retention_obj->is_deleted == 0) {
				if ($user_retention_obj->report_count == 1) $report_count++;
				if ($reportType == 1) {
					if ($user_retention_obj->results_count == 1) $results_count++;
				}
				if ($reportType == 2) {
					if ($user_retention_obj->report_count != null && $user_retention_obj->results_count == 1)
						$results_count++;
				}

			}
		}

		$userCount = count($userArray);
		$resultsSum = $userCount - $results_count;
		$followupSum = $userCount - $report_count;

		return array('total_records' => $userCount, 'needing_attention' =>$followupSum, 'resutls_followup_needed' =>$resultsSum);
	}


}

?>