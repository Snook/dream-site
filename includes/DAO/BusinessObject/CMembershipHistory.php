<?php
	require_once 'DAO/Membership_history.php';



	class CMembershipHistory extends DAO_Membership_history {


		const ERROR_OR_EXCEPTION = 'ERROR_OR_EXCEPTION';
		const MEMBERSHIP_PURCHASED = 'MEMBERSHIP_PURCHASED';
		const SOFT_SKIP = 'SOFT_SKIP';
		const HARD_SKIP = 'HARD_SKIP';
		const UNSKIP = 'UNSKIP';
		const SKIP_REVOKED = 'SKIP_REVOKED';

		const MEMBERSHIP_CANCELLED = 'MEMBERSHIP_CANCELLED';
		const MEMBERSHIP_COMPLETED = 'MEMBERSHIP_COMPLETED';
		const MEMBERSHIP_TERMINATED = 'MEMBERSHIP_TERMINATED';

		static function recordEvent($user_id, $membership_id,  $event_type, $add_data)
		{

			$historyDAO = DAO_CFactory::create('membership_history');
			$historyDAO->user_id = $user_id;
			$historyDAO->membership_id = $membership_id;
			$historyDAO->event_type = $event_type;
			$historyDAO->json_meta = json_encode($add_data);

			$historyDAO->insert();
		}

		static function deleteHardSkipEvent($user_id, $menu_id)
		{
			$historyDAO = new DAO();
			$historyDAO->query("udpate membership_history set is_deleted = 1 where user_id = $user_id and event_type = 'HARD_SKIP' and json_meta like '%$menu_id%'");


		}

	}

?>