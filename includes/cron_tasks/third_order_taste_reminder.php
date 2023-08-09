<?php
require_once("../Config.inc");
require_once("CLog.inc");
require_once("DAO/BusinessObject/CDreamTasteEvent.php");
require_once("DAO/BusinessObject/CUser.php");

exit;

restore_error_handler();

try
{

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: third_order_taste_reminders called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::THIRD_ORDER_TASTE_REMINDERS, "third_order_taste_reminders called but cron is disabled.");

		exit;
	}

	/*
	$bookingObj = DAO_CFactory::create('booking');
	$bookingObj->query("#The outermost query joins pertinent data and returns the list
							select oq3.user_id, u.firstname, u.lastname, u.primary_email, u.home_store_id, ud.dream_taste_third_order_invite from
									#oq3 thens trims out all but those whose 3 sessions occur in the 90 day window
									(select oq2.user_id, min(s3.session_start) as first_session, max(s3.session_start) as last_session from
												 # oq2 selects out on those users with 3 sessions
												(select oq.user_id from
															# oq finds the count of session attended or about to be attended by the users of the inner query
															(select iq.user_id, count(b2.id) as total_sessions from
																			# innner most query finds all guests attending a session 5 days in the future
																			(select b.user_id from booking b
																			join session s on s.id = b.session_id
																			join orders o on b.order_id = o.id and o.type_of_order = 'STANDARD'
																			join store st on st.id = s.store_id and st.dream_taste_opt_out = 0
																			where DATEDIFF(NOW(), s.session_start ) = -5  and b.status = 'ACTIVE') as iq
															join booking b2 on b2.user_id = iq.user_id and b2.status = 'ACTIVE'
															join orders o2 on o2.id = b2.order_id and (o2.type_of_order = 'STANDARD' or o2.type_of_order = 'INTRO')
															group by iq.user_id) as oq
												where oq.total_sessions = 3) as oq2
									join booking b3 on b3.user_id = oq2.user_id and b3.status = 'ACTIVE'
									join session s3 on b3.session_id = s3.id
									join orders o3 on o3.id = b3.order_id and (o3.type_of_order = 'STANDARD' or o3.type_of_order = 'INTRO')
									group by oq2.user_id) as oq3
							join user u on u.id = oq3.user_id
							left join user_digest ud on ud.user_id = u.id
							where DATEDIFF(NOW(),oq3.first_session) <= 85 and isnull(ud.dream_taste_third_order_invite) and DATEDIFF(NOW(), oq3.last_session) = -5");
	*/

	$bookingObj = DAO_CFactory::create('booking');
	$bookingObj->query("SELECT count(s2.id) AS scount,
			iq.* FROM
			(SELECT
			b.user_id,
			u.firstname,
			u.lastname,
			u.primary_email,
			u.home_store_id,
			ud.dream_taste_third_order_invite,
			puh.event_type,
			puh.json_meta,
			puh.timestamp_created,
			b.session_id,
			s.session_start,
			s.menu_id,
			st.is_corporate_owned
			FROM booking AS b
			INNER JOIN `session` AS s ON s.id = b.session_id AND s.is_deleted = '0'
			INNER JOIN orders AS o ON b.order_id = o.id AND o.type_of_order = 'STANDARD' AND o.is_deleted = '0'
			INNER JOIN store AS st ON st.id = s.store_id AND st.dream_taste_opt_out = 0
			INNER JOIN `user` AS u ON u.id = b.user_id AND u.is_deleted = '0'
			INNER JOIN user_digest AS ud ON ud.user_id = u.id AND ISNULL(ud.dream_taste_third_order_invite)
			INNER JOIN points_user_history AS puh ON puh.id = ud.last_achievement_achieved_id AND puh.event_type = 'ACHIEVEMENT_AWARD' AND puh.json_meta LIKE '%\"level\":\"chef\"%' AND puh.timestamp_created > '2015-08-30 00:00:00' AND puh.is_deleted = '0'
			WHERE DATEDIFF(NOW(), s.session_start) = - 5
			AND b.`status` = 'ACTIVE'
			AND b.is_deleted = '0') AS iq
			LEFT JOIN booking b2 ON b2.user_id = iq.user_id AND b2.`status` = 'ACTIVE' AND b2.is_deleted = '0'
			LEFT JOIN session s2 ON s2.id = b2.session_id AND s2.session_start > iq.timestamp_created AND s2.session_start < iq.session_start
			GROUP BY iq.user_id
			HAVING scount = '0'");

	$totalCount = 0;
	while ($bookingObj->fetch())
	{
		CDreamTasteEvent::sendThirdOrderTasteReminder($bookingObj);
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::THIRD_ORDER_TASTE_REMINDERS, " $totalCount Chef Meal Prep Workshop reminder emails processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::THIRD_ORDER_TASTE_REMINDERS, "third_order_taste_reminders: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}
?>