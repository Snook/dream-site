<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CSRF.inc");

class processor_admin_reports_map_activity extends CPageProcessor
{
	function runSiteAdmin()
	{
		$this->sseRun();
	}

	function sseRun()
	{
		$user = CUser::getCurrentUser();

		if (is_numeric($_POST['last_time']))
		{
			$sessionObj = DAO_CFactory::create('orders');
			$sessionObj->query("SELECT
				b.id,
				b.timestamp_updated,
				b.timestamp_created,
				u.user_type,
				u.firstname,
				u.lastname,
				u.facebook_id,
				u.facebook_oauth_token,
				o.servings_total_count,
				o.ltd_round_up_value,
				o.subtotal_ltd_menu_item_value,
				store.store_name,
				store.city,
				store.state_id,
				store.address_latitude,
				store.address_longitude
				FROM booking AS b
				INNER JOIN `session` AS s ON s.id = b.session_id
				INNER JOIN `orders` AS o ON o.id = b.order_id
				INNER JOIN store ON store.id = s.store_id
				INNER JOIN `user` AS u ON u.id = b.user_id
				WHERE b.is_deleted = '0'
				AND b.`status` = 'ACTIVE'
				AND b.timestamp_updated > '" . CTemplate::unix_to_mysql_timestamp($_POST['last_time']) . "'
				ORDER BY b.timestamp_updated DESC");

			$dataArray = array();

			while ($sessionObj->fetch())
			{
				$dataArray[$sessionObj->id] = array(
					'id' => $sessionObj->id,
					'lat' => $sessionObj->address_latitude,
					'lon' => $sessionObj->address_longitude,
					'user_type' => $sessionObj->user_type,
					'city' => $sessionObj->city,
					'state' => $sessionObj->state_id,
					'store_name' => $sessionObj->store_name,
					'firstname' => $sessionObj->firstname,
					'servings_total_count' => (!empty($sessionObj->servings_total_count) ? $sessionObj->servings_total_count : 0),
					'ltd_round_up_value' => (!empty($sessionObj->ltd_round_up_value) ? $sessionObj->ltd_round_up_value : 0),
					'subtotal_ltd_menu_item_value' => (!empty($sessionObj->subtotal_ltd_menu_item_value) ? $sessionObj->subtotal_ltd_menu_item_value : 0),
					'lastname_letter' => (!empty($sessionObj->lastname) ? substr($sessionObj->lastname, 0, 1) . '.' : ''),
					'facebook_id' => (!empty($sessionObj->facebook_id) ? $sessionObj->facebook_id : false),
					'facebook_oauth_token' => (!empty($sessionObj->facebook_oauth_token) ? $sessionObj->facebook_oauth_token : false),
					'date_time' => CTemplate::dateTimeFormat($sessionObj->timestamp_updated, NORMAL, $user->home_store_id),
					'timestamp_updated_unix' => strtotime($sessionObj->timestamp_updated)
				);
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Activity returned.',
				'data' => $dataArray
			), false, JSON_NUMERIC_CHECK);
		}
	}

}

?>