<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_user_event_log extends CPageAdminOnly
{

	function runHomeOfficeManager()
	{
		$this->userEventLog();
	}

	function runSiteAdmin()
	{
		$this->userEventLog();
	}

	function userEventLog()
	{
		$tpl = CApp::instance()->template();

		if ($_GET['id'] && is_numeric($_GET['id']))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_GET['id'];

			if ($User->find(true))
			{
				$type = false;
				if (!empty($_REQUEST['log_type']) && ctype_alpha($_REQUEST['log_type']))
				{
					$type = CGPC::do_clean($_REQUEST['log_type'],TYPE_STR);
				}

				$conn_res = CLog::instance()->connect();

				$event_log_table_query = "SELECT t.table_name AS `event_log_table` FROM INFORMATION_SCHEMA.TABLES t WHERE TABLE_SCHEMA = 'dreamlog' AND t.table_name LIKE 'event_log_%'";

				$result = mysqli_query($conn_res, $event_log_table_query);

				$unionQuery = "SELECT * FROM ((SELECT recipient_user_id AS user_id, `subject` AS description, CONCAT('EMAIL') AS log_type, timestamp_created FROM `email_log` WHERE `recipient_user_id` = '" . $User->id . "' OR recipient_email_address = '" . $User->primary_email . "')";

				while ($row = mysqli_fetch_assoc($result))
				{
					$unionQuery .= " UNION (SELECT user_id, description, log_type, timestamp_created FROM `" . $row['event_log_table'] . "` WHERE `user_id` = '" . $User->id . "')";
				}

				$unionQuery .= ") AS iq";

				if ($type)
				{
					$unionQuery .= " WHERE iq.log_type = '" . $type . "'";
				}

				$unionQuery .= " ORDER BY iq.timestamp_created DESC LIMIT 100";

				$result = mysqli_query($conn_res, $unionQuery);

				$resultsArray = array();

				while ($row = mysqli_fetch_assoc($result))
				{
					$resultsArray[] = $row;
				}

				$tpl->assign('user', $User);
				$tpl->assign('events', $resultsArray);
			}
		}

		if (empty($tpl->user))
		{
			CApp::bounce('main.php?page=admin_list_users');
		}
	}
}

?>