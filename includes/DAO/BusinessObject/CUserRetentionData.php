<?php
	require_once( 'DAO/User_retention_data.php' );

	class CUserRetentionData extends DAO_User_retention_data
	{

		/***
		 * Update the user retention field if it exists. TODO: does it make more sense to do a find query first?
		 */
		public static function updateUserRetentionInfo ($user_id, $order_id, $store_id)
		{
			if ($user_id > 0 && $order_id > 0) {
				$user_retention_obj = DAO_CFactory::create("user_retention_data");
				$user_retention_obj->user_id = $user_id;
				$user_retention_obj->is_active = 1;

				$rslt = $user_retention_obj->find(true);
				if ($rslt) {
					// bypass the cloning and DAO updating to speed up execution.
					// user id and order id will be 100% legit since failure would have happened earlier
					$sql = "update user_retention_data set store_id=$store_id, updated_order_id=$order_id, is_active = 0 where user_id=$user_id and updated_order_id is null and is_active = 1";
					$user_retention_obj->query($sql);

				}


			}
		}

	}
?>
