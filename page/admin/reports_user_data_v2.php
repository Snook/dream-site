<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once("phplib/PHPExcel_1.8/PHPExcel.php");
require_once('ExcelExport.inc');

class page_admin_reports_user_data_v2 extends CPageAdminOnly
{

	private $currentStore = null;
	private $clientData = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$store = null;
		$export = false;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;

		ini_set('memory_limit', '-1');
		set_time_limit(3600);

		$profileDataTemplate = array(
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			"",
			""
		);
		$profilePositionMap = array(
			1 => 0,
			15 => 1,
			2 => 2,
			10 => 3,
			17 => 4,
			18 => 5,
			20 => 6,
			3 => 7,
			4 => 8,
			5 => 9,
			6 => 10,
			7 => 11,
			11 => 12,
			16 => 13
		);

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{ //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
			{
				$Form->addElement(array(
					CForm::type => CForm::AdminStoreDropDown,
					CForm::onChangeSubmit => true,
					CForm::allowAllOption => true,
					CForm::showInactiveStores => true,
					CForm::name => 'store'
				));
			}
			else
			{
				$Form->addElement(array(
					CForm::type => CForm::AdminStoreDropDown,
					CForm::onChangeSubmit => true,
					CForm::showInactiveStores => true,
					CForm::name => 'store'
				));
			}

			$store = $Form->value('store');
		}

		$supportsPlatePoints = false;
		$supportsMemberships = false;
		$storeDAO = false;

		if ($store === "all")
		{
			if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
			{
				$supportsPlatePoints = true;
			}
			$tpl->assign("store_name", "All Stores");
		}
		else if (is_numeric($store))
		{
			$storeDAO = DAO_CFactory::create('store');
			$storeDAO->id = $store;
			$storeDAO->selectAdd();
			$storeDAO->selectAdd('store_name, city, supports_plate_points, supports_corporate_crate, supports_membership');
			$storeDAO->find(true);

			if ($storeDAO->supports_plate_points)
			{
				$supportsPlatePoints = true;
			}
			if ($storeDAO->supports_membership)
			{
				$supportsMemberships = true;
			}

			$tpl->assign("store_name", $storeDAO->city . "--" . $storeDAO->store_name);
		}
		else
		{
			$tpl->assign("store_name", "N/A");
		}

		$tpl->assign('supportsPlatePoints', $supportsPlatePoints);
		$tpl->assign('supportsMemberships', $supportsMemberships);

		$rowCount = 0;

		if (isset($_POST['submit_report']))
		{

			$nowTime = "now()";
			$nowDate = "'" . date("Y-m-d H:i:s", time()) . "'";
			if (is_numeric($store))
			{
				$nowTime = time(); // could add some tolerance here
				$nowDate = "'" . date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeDAO, $nowTime)) . "'";
			}

			$sectionSwitches = array();

			$sectionSwitches['df_ACTIVE'] = !empty($_POST['df_ACTIVE']);
			$sectionSwitches['df_ACCT_CREATE_DATE'] = !empty($_POST['df_ACCT_CREATE_DATE']);
			$sectionSwitches['df_MEMBERSHIP_STATUS'] = !empty($_POST['df_MEMBERSHIP_STATUS']);
			$sectionSwitches['df_DR_STATUS'] = !empty($_POST['df_DR_STATUS']);
			$sectionSwitches['df_DR_LEVEL'] = !empty($_POST['df_DR_LEVEL']);
			$sectionSwitches['df_EMAIL'] = !empty($_POST['df_EMAIL']);
			$sectionSwitches['df_PHONE'] = !empty($_POST['df_PHONE']);
			$sectionSwitches['df_ADDRESS'] = !empty($_POST['df_ADDRESS']);
			$sectionSwitches['df_LAST_SESSION_ATTENDED'] = !empty($_POST['df_LAST_SESSION_ATTENDED']);
			$sectionSwitches['df_DAYS_INACTIVE'] = !empty($_POST['df_DAYS_INACTIVE']);
			$sectionSwitches['df_NEXT_SESSION'] = !empty($_POST['df_NEXT_SESSION']);
			$sectionSwitches['df_PROFILE_DATA'] = !empty($_POST['df_PROFILE_DATA']);
			$sectionSwitches['df_INSTRUCTIONS'] = !empty($_POST['df_INSTRUCTIONS']);
			$sectionSwitches['df_CUSTOMIZATIONS'] = !empty($_POST['df_CUSTOMIZATIONS']);

			$sectionSwitches['df_USER_ACCOUNT_NOTE'] = !empty($_POST['df_USER_ACCOUNT_NOTE']);
			$sectionSwitches['df_TEXT_MESSAGE_OPT_IN'] = !empty($_POST['df_TEXT_MESSAGE_OPT_IN']);
			$sectionSwitches['df_USER_SHARE_URL'] = !empty($_POST['df_USER_SHARE_URL']);

			$innerOptionalColumns = "";

			$columnCount = 3; // always 3

			if ($sectionSwitches['df_ACTIVE'])
			{
				$innerOptionalColumns .= ", u.is_deleted";
				$columnCount++;
			}

			if ($sectionSwitches['df_ACCT_CREATE_DATE'])
			{
				$innerOptionalColumns .= ", u.timestamp_created";
				$columnCount++;
			}

			if ($sectionSwitches['df_MEMBERSHIP_STATUS'])
			{
				$innerOptionalColumns .= ", 1 as membership_status";
				$columnCount++;
			}

			if ($supportsPlatePoints)
			{
				if ($sectionSwitches['df_DR_STATUS'] || $sectionSwitches['df_DR_LEVEL'])
				{
					$innerOptionalColumns .= ", u.dream_reward_status, u.dream_rewards_version, u.dream_reward_level, u.dr_downgraded_order_count";
					$columnCount += 4;
				}

				if ($sectionSwitches['df_DR_LEVEL'])
				{
					$innerOptionalColumns .= ", '' as plate_points";
					$columnCount++;
				}
			}
			else
			{

				if ($sectionSwitches['df_DR_STATUS'] || $sectionSwitches['df_DR_LEVEL'])
				{
					$innerOptionalColumns .= ", u.dream_reward_status, u.dream_reward_level, u.dream_rewards_version";
					$columnCount += 3;
				}
			}

			if ($sectionSwitches['df_EMAIL'])
			{
				$innerOptionalColumns .= ", u.primary_email";
			}

			if ($sectionSwitches['df_PHONE'])
			{
				$innerOptionalColumns .= " , u.telephone_1, u.telephone_1_type, u.telephone_1_call_time, u.telephone_2, u.telephone_2_type, u.telephone_2_call_time";
			}

			if ($sectionSwitches['df_ADDRESS'])
			{
				$innerOptionalColumns .= " , a.address_line1, a.address_line2, a.city, a.state_id, a.postal_code";
			}

			if ($sectionSwitches['df_LAST_SESSION_ATTENDED'])
			{
				$innerOptionalColumns .= ", max(s2.session_start) as most_recent_session";

				$innerOptionalColumns .= ", '' as most_recent_session_type";
				$columnCount++;
			}

			if ($sectionSwitches['df_NEXT_SESSION'])
			{
				$innerOptionalColumns .= ", min(s3.session_start) as next_session";
			}

			if ($sectionSwitches['df_USER_ACCOUNT_NOTE'])
			{
				$innerOptionalColumns .= ", up.pvalue as user_account_notes";
			}

			if ($sectionSwitches['df_TEXT_MESSAGE_OPT_IN'])
			{
				$innerOptionalColumns .= ", if(up_text.pvalue is not null, up_text.pvalue, 'UNANSWERED') as text_message_opt_in";
			}

			if ($sectionSwitches['df_USER_SHARE_URL'])
			{
				$innerOptionalColumns .= ", CONCAT('" . HTTPS_BASE . "share/', u.id) as user_share_url";
			}

			if ($store === "all")
			{
				$basicInnerWhereClause = " ";
			}
			else
			{
				$basicInnerWhereClause = " where u.home_store_id = $store ";
			}

			$innerSelectClause = "select u.id, u.firstname, u.lastname, u.secondary_email, count(if (s1.session_start > $nowDate, 1, null)) as future_orders, count(s1.id) as total_orders, max(s1.session_start) as max_session " . $innerOptionalColumns . " from user u ";

			$outerSelectClause = "select iq.* ";

			$outerOptionalColumns = "";

			if ($sectionSwitches['df_DAYS_INACTIVE'])
			{
				$outerOptionalColumns .= ", DATEDIFF($nowDate,iq.max_session) as inactive_days";
			}

			if ($sectionSwitches['df_PROFILE_DATA'])
			{
				$outerOptionalColumns .= " , GROUP_CONCAT(ud.user_data_field_id SEPARATOR '|') as field_ids, GROUP_CONCAT(ud.user_data_value SEPARATOR '|') as field_values";
			}

			$outerSelectClause .= $outerOptionalColumns . " from (";

			$innerQueryJoins = "left join booking b1 on b1.user_id = u.id and b1.status = 'ACTIVE'
								left join session s1 on s1.id = b1.session_id";

			if ($sectionSwitches['df_LAST_SESSION_ATTENDED'])
			{
				$innerQueryJoins .= " left join session s2 on s2.id = b1.session_id and s2.session_start < $nowDate ";
			}

			if ($sectionSwitches['df_NEXT_SESSION'])
			{
				$innerQueryJoins .= " left join session s3 on s3.id = b1.session_id and s3.session_start >= $nowDate ";
			}

			if ($sectionSwitches['df_ADDRESS'])
			{
				$innerQueryJoins .= " left join address a on a.user_id = u.id and a.location_type = 'BILLING' ";
			}

			$outerQueryJoins = "";
			if ($sectionSwitches['df_PROFILE_DATA'])
			{
				$outerQueryJoins .= " left join user_data ud on ud.user_id = iq.id and ud.user_data_field_id in (1,2,3,4,5,6,7,10,11,15,16,17,18,20)";
			}

			if ($sectionSwitches['df_USER_ACCOUNT_NOTE'])
			{
				$innerQueryJoins .= " left join user_preferences as up on up.user_id = u.id and up.pkey = 'USER_ACCOUNT_NOTE'";
			}

			if ($sectionSwitches['df_TEXT_MESSAGE_OPT_IN'])
			{
				$innerQueryJoins .= " left join user_preferences as up_text on up_text.user_id = u.id and up_text.pkey = 'TEXT_MESSAGE_OPT_IN'";
			}

			$outerWhereClause = "";

			switch ($_POST['guest_type'])
			{
				case 'all':
					break;

				case 'has_future_sessions':
					//$outerWhereClause = "where iq.future_orders > 0 and iq.id = 697884 ";
					$outerWhereClause = "where iq.future_orders > 0 ";
					break;

				case 'never_attended':
					$outerWhereClause = "where iq.total_orders = 0";
					break;

				case 'inactive':
					if ($store != "all")
					{
						$basicInnerWhereClause .= " and u.is_deleted = 1";
					}
					else
					{
						$basicInnerWhereClause .= " where u.is_deleted = 1";
					}
					break;

				case '45_day_lost_guest':

					if ($store != "all")
					{
						$basicInnerWhereClause .= " and u.id in (select distinct od.user_id as total_in_store from orders_digest od where session_time < $nowDate
							and session_time >= DATE_SUB($nowDate ,INTERVAL 45 DAY) and od.store_id = $store and od.is_deleted = 0
							and od.user_id not in (
								select DISTINCT od2.user_id as non_lost_guests from orders_digest od2 where od2.original_order_time < $nowDate
							and od2.session_time > $nowDate and od2.store_id = $store and od2.is_deleted = 0 and od2.user_id in
							(select distinct od.user_id from orders_digest od where session_time < $nowDate
							and session_time >= DATE_SUB($nowDate ,INTERVAL 45 DAY) and od.store_id = $store and od.is_deleted = 0)))";
					}
					else
					{
						$basicInnerWhereClause .= " where u.id in (select distinct od.user_id as total_in_store from orders_digest od where session_time < $nowDate
							and session_time >= DATE_SUB($nowDate ,INTERVAL 45 DAY) and od.is_deleted = 0
							and od.user_id not in (
								select DISTINCT od2.user_id as non_lost_guests from orders_digest od2 where od2.original_order_time < $nowDate
							and od2.session_time > $nowDate and od2.is_deleted = 0 and od2.user_id in
							(select distinct od.user_id from orders_digest od where session_time < $nowDate
							and session_time >= DATE_SUB($nowDate ,INTERVAL 45 DAY) and od.is_deleted = 0)))";
					}
					break;
			}

			$query = $outerSelectClause . $innerSelectClause . $innerQueryJoins . $basicInnerWhereClause . " group by u.id) as iq " . $outerQueryJoins . $outerWhereClause . " group by iq.id";

			if ($sectionSwitches['df_PROFILE_DATA'])
			{

				$query = "select GROUP_CONCAT(urs.source ORDER BY urs.id SEPARATOR '|') as referral_source, GROUP_CONCAT(urs.meta ORDER BY urs.id SEPARATOR '|') as ref_source_data, oq.* from ( " . $query . ") as oq left join user_referral_source urs on oq.id = urs.user_id and urs.is_deleted = 0 group by oq.id";
			}

			$lastMidnight = date("Y-m-d 00:00:00");

			if (($sectionSwitches['df_INSTRUCTIONS'] || $sectionSwitches['df_CUSTOMIZATIONS']) && $_POST['guest_type'] == 'has_future_sessions')
			{

				$instructionsClause = '';
				if ($sectionSwitches['df_INSTRUCTIONS'])
				{
					$instructionsClause = " GROUP_CONCAT(o4.order_user_notes SEPARATOR '|') as instructions, ";
				}
				$customizationsClause = '';
				if ($sectionSwitches['df_CUSTOMIZATIONS'])
				{
					$customizationsClause = "GROUP_CONCAT( IF( o4.opted_to_customize_recipes, o4.id, NULL ) SEPARATOR '|' ) AS customization_orders, ";
				}
				$query = "select " . $instructionsClause . $customizationsClause . "oq2.* from ( " . $query . ") as oq2
					left join booking b4 on b4.user_id = oq2.id and b4.status = 'ACTIVE'
					join session s4 on s4.id = b4.session_id and s4.session_start > '$lastMidnight'
					join orders o4 on o4.id = b4.order_id and o4.is_deleted = 0
					group by oq2.id";
			}

			$columnDescs = array();

			$labels = array(
				"User ID",
				"First Name",
				"Last Name"
			);
			$columnDescs['A'] = array('align' => 'center');
			$columnDescs['B'] = array('align' => 'left');
			$columnDescs['C'] = array(
				'align' => 'left',
				'decor' => 'fixed'
			);

			$col = 'D';
			$colSecondChar = '';
			$thirdSecondChar = '';

			if ($sectionSwitches['df_ACTIVE'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Account Status"));
			}

			if ($sectionSwitches['df_ACCT_CREATE_DATE'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'type' => 'datetime',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Account Created"));
			}

			if ($sectionSwitches['df_MEMBERSHIP_STATUS'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'type' => 'text',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Meal Prep+ Member"));
			}

			if ($sectionSwitches['df_DR_STATUS'])
			{

				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'width' => 14
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				if ($supportsPlatePoints)
				{
					$labels = array_merge($labels, array("PLATEPOINTS Status"));
				}
				else
				{
					$labels = array_merge($labels, array("DR Status"));
				}
			}

			if ($sectionSwitches['df_DR_LEVEL'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'width' => 8
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				if ($supportsPlatePoints)
				{
					$labels = array_merge($labels, array("PLATEPOINTS"));
				}
				else
				{
					$labels = array_merge($labels, array("DR Level"));
				}
			}

			if ($sectionSwitches['df_EMAIL'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Email Address"));
				$columnCount++;
			}

			if ($sectionSwitches['df_PHONE'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$labels = array_merge($labels, array(
					"Telephone 1",
					"Telephone 1 Type",
					"Telephone 1 Call Time",
					"Telephone 2",
					"Telephone 2 Type",
					"Telephone 2 Call Time"
				));

				$columnCount += 6;
			}

			if ($sectionSwitches['df_ADDRESS'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 15
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'width' => 6
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$labels = array_merge($labels, array(
					"Address 1",
					"Apt",
					"City",
					"State",
					"Postal Code"
				));

				$columnCount += 5;
			}

			if ($sectionSwitches['df_LAST_SESSION_ATTENDED'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'type' => 'datetime',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Last Session Attended"));
				$columnCount++;

				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Last Session Attended Type"));
				$columnCount++;
			}

			if ($sectionSwitches['df_NEXT_SESSION'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'type' => 'datetime',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Next Session"));
				$columnCount++;
			}

			if ($sectionSwitches['df_USER_ACCOUNT_NOTE'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("User Account Notes"));
				$columnCount++;
			}

			if ($sectionSwitches['df_TEXT_MESSAGE_OPT_IN'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("SMS Message Opt-In"));
				$columnCount++;
			}

			if ($sectionSwitches['df_USER_SHARE_URL'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("User Share URL"));
				$columnCount++;
			}

			if ($sectionSwitches['df_DAYS_INACTIVE'])
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'center',
					'width' => 10
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Number Days Inactive"));
				$columnCount++;
			}

			$DAO_user = DAO_CFactory::create('user');
			$DAO_user->query($query);

			$exportAsExcel = true;
			if ($DAO_user->N > 400)
			{
				$exportAsExcel = false;
			}

			$rows = array();

			$optionalColumns = array(
				3 => false,
				4 => false,
				5 => false,
				6 => false,
				7 => false,
				12 => false
			);
			$customization_order_ids = null;

			while ($DAO_user->fetch())
			{
				$thisGuest = $DAO_user->toArray();
				$thisGuest['postal_code'] = "=\"" . $thisGuest['postal_code'] . "\"";
				unset($thisGuest['future_orders']);
				unset($thisGuest['total_orders']);
				unset($thisGuest['max_session']);

				// 2nd email is always present: record it and remove - if appropriate
				// add it later as part of the profile info
				$corporate_crate_email = $thisGuest['secondary_email'];
				unset($thisGuest['secondary_email']);

				if ($sectionSwitches['df_INSTRUCTIONS'] && $_POST['guest_type'] == 'has_future_sessions')
				{
					// instructions fields are necessarily at the begin so pull them off to place with other profile data
					$instructions = array_shift($thisGuest);
				}

				if ($sectionSwitches['df_CUSTOMIZATIONS'] && $_POST['guest_type'] == 'has_future_sessions')
				{
					// $customizations fields are necessarily at the begin so pull them off to place with other profile data
					$customization_order_ids = array_shift($thisGuest);
				}

				if ($sectionSwitches['df_PROFILE_DATA'])
				{
					// user referral source fields are necessarily at the begin so pull them off to place with other profile data
					$referralSource = array_shift($thisGuest);
					$referralSourceData = array_shift($thisGuest);
				}

				if ($sectionSwitches['df_LAST_SESSION_ATTENDED'])
				{
					if (!empty($thisGuest['most_recent_session']))
					{
						$DAO_orders_digest = DAO_CFactory::create('orders_digest');
						$DAO_orders_digest->user_id = $DAO_user->id;
						$DAO_orders_digest->session_time = $thisGuest['most_recent_session'];

						$thisGuest['most_recent_session'] = ($exportAsExcel ? PHPExcel_Shared_Date::stringToExcel($thisGuest['most_recent_session']) : date("n/j/Y g:i a", strtotime($thisGuest['most_recent_session'])));

						if ($DAO_orders_digest->find(true))
						{
							$Session = DAO_CFactory::create('session');
							$Session->id = $DAO_orders_digest->session_id;

							if ($Session->find(true))
							{
								list ($thisGuest['most_recent_session_type'], $thisGuest['session_type_title_public'], $thisGuest['session_type_title_short'], $thisGuest['session_type_fadmin_acronym'], $thisGuest['session_type_string']) = $Session->getSessionTypeProperties();

								if ($DAO_orders_digest->order_type == COrders::INTRO)
								{
									$thisGuest['most_recent_session_type'] .= ' - Starter Pack';
								}
							}
						}
					}
					else
					{
						$thisGuest['most_recent_session'] = "";
						$thisGuest['most_recent_session_type'] = "";
					}
				}

				if ($sectionSwitches['df_NEXT_SESSION'])
				{
					if (!empty($thisGuest['next_session']))
					{
						$thisGuest['next_session'] = ($exportAsExcel ? PHPExcel_Shared_Date::stringToExcel($thisGuest['next_session']) : date("n/j/Y g:i a", strtotime($thisGuest['next_session'])));
					}
					else
					{
						$thisGuest['next_session'] = "";
					}
				}

				$thisGuest = array_slice($thisGuest, 0, $columnCount);

				if ($sectionSwitches['df_PROFILE_DATA'])
				{
					$IDs = explode("|", $DAO_user->field_ids);
					$Values = explode("|", $DAO_user->field_values);

					$thisDataSet = $profileDataTemplate;

					if (!empty($DAO_user->field_ids))
					{
						foreach ($IDs as $thisFieldID)
						{
							$position = $profilePositionMap[$thisFieldID];

							if (array_key_exists($thisFieldID, $optionalColumns))
							{
								$optionalColumns[$thisFieldID] = true;
							}

							if ($thisFieldID == 15)
							{
								$test = current($Values);
								if ($test == "none")
								{
									$test = "";
								}

								$thisDataSet[$position] = $test;
							}
							else if ($thisFieldID == 1)
							{

								$month = current($Values);

								if (is_numeric($month))
								{
									$marr = CUserData::monthArray();
									$month = $marr[$month];
								}

								$thisDataSet[$position] = $month;
							}
							else
							{
								$thisDataSet[$position] = current($Values);
							}

							next($Values);
						}
					}

					$thisGuest = array_merge($thisGuest, $thisDataSet);
				}

				if ($sectionSwitches['df_ACTIVE'])
				{
					$thisGuest['is_deleted'] = ($thisGuest['is_deleted'] == '0' ? 'active' : 'INACTIVE');
				}

				if ($sectionSwitches['df_ACCT_CREATE_DATE'])
				{
					$thisGuest['timestamp_created'] = ($exportAsExcel ? PHPExcel_Shared_Date::stringToExcel($thisGuest['timestamp_created']) : date("n/j/Y g:i a", strtotime($thisGuest['timestamp_created'])));
				}

				if ($sectionSwitches['df_MEMBERSHIP_STATUS'])
				{
					$thisGuest['membership_status'] = ($DAO_user->hasCurrentMembership() ? "Yes" : "No");
				}

				$isInPlatePoints = false;

				if ($sectionSwitches['df_DR_STATUS'])
				{
					if ($supportsPlatePoints)
					{
						$thisGuest['dream_reward_status'] = $this->getLoyaltyStatusString($thisGuest, $isInPlatePoints);
					}
					else
					{
						$thisGuest['dream_reward_status'] = CDreamRewardsHistory::$DRShortDescriptiveNameMap[$thisGuest['dream_reward_status']];
					}
				}
				else if ($sectionSwitches['df_DR_LEVEL'])
				{
					$isInPlatePoints = (($thisGuest['dream_reward_status'] == 1 || $thisGuest['dream_reward_status'] == 3) && $thisGuest['dream_rewards_version'] == 3);
				}

				if ($sectionSwitches['df_DR_LEVEL'])
				{
					if ($supportsPlatePoints && $isInPlatePoints)
					{
						$thisGuest['plate_points'] = $this->getPPUnconvertedPoints($thisGuest);
					}
				}

				unset($thisGuest['dream_rewards_version']);

				unset($thisGuest['field_ids']);

				if ($supportsPlatePoints)
				{
					unset($thisGuest['dream_reward_level']);
					unset($thisGuest['dr_downgraded_order_count']);

					if (empty($sectionSwitches['df_DR_STATUS']))
					{
						unset($thisGuest['dream_reward_status']);
					}
				}
				else
				{
					if (empty($sectionSwitches['df_DR_STATUS']))
					{
						unset($thisGuest['dream_reward_status']);
					}

					if (empty($sectionSwitches['df_DR_LEVEL']))
					{
						unset($thisGuest['dream_reward_level']);
					}
				}

				if ($sectionSwitches['df_PROFILE_DATA'])
				{
					$refArr = explode("|", $referralSource);
					$thisGuest['referral_source'] = array_pop($refArr);

					$refDataArr = explode("|", $referralSourceData);
					$thisGuest['ref_source_data'] = array_pop($refDataArr);

					if (!$storeDAO || $storeDAO->supports_corporate_crate)
					{
						if (!$this->clientData)
						{
							$this->clientData = CCorporateCrateClient::getArrayOfAllClients();
						}

						if (!empty($corporate_crate_email))
						{
							$emailParts = explode("@", $corporate_crate_email);
							$domain = array_pop($emailParts);

							if (isset($this->clientData[$domain]))
							{
								$thisGuest['corp_crate_client'] = $this->clientData[$domain]['company_name'];
							}
							else
							{
								$thisGuest['corp_crate_client'] = "-";
							}
						}
						else
						{
							$thisGuest['corp_crate_client'] = "-";
						}
					}
				}

				if ($sectionSwitches['df_INSTRUCTIONS'] && $_POST['guest_type'] == 'has_future_sessions')
				{
					$thisGuest['instructions'] = $instructions;
				}

				if ($sectionSwitches['df_CUSTOMIZATIONS'] && $_POST['guest_type'] == 'has_future_sessions')
				{

					if (!empty($customization_order_ids))
					{
						$orderIds = explode('|', $customization_order_ids);
						$str = '';
						foreach ($orderIds as $orderId)
						{
							$order = new COrders();
							$order->id = $orderId;
							$order->find(true);
							$customization = OrdersCustomization::initOrderCustomizationObj($order->order_customization);
							$cust = $customization->getMealCustomizationObj()->toString(',', true, ' with ');
							if ($cust != '')
							{
								$str .= 'Order ' . $orderId . ' has no added: ' . $cust . '.   ';
							}
						}
						$thisGuest['customizations'] = $str;
					}
				}

				$rows[] = $thisGuest;
			}

			foreach ($rows as &$thisRow)
			{
				foreach ($optionalColumns as $colNum => $hasData)
				{
					if (!$hasData)
					{
						if (array_key_exists($colNum, $profilePositionMap) && array_key_exists($profilePositionMap[$colNum], $thisRow))
						{
							unset($thisRow[$profilePositionMap[$colNum]]);
						}
					}
				}
			}

			if ($sectionSwitches['df_PROFILE_DATA'])
			{
				//	array(1 => 1, 15 => 2, 2 => 3, 10 => 4, 17 => 5, 18 => 6, 20 => 7, 3 => 8, 4 => 9, 5 => 10, 6 => 11, 7 => 12, 11 => 13, 16 => 14);

				$profileMetaArray = array(
					1 => "Birthday Month",
					15 => "Year of Birth",
					2 => "Number of Children",
					10 => "Number of Adults",
					17 => "Contributes Income",
					18 => "Uses Lists",
					20 => "Number Nights Dine Out",
					3 => "Favorite Dream Dinners Meal",
					4 => "Why does DD work for you?",
					5 => "Guest Employer Details",
					6 => "Upcoming Events",
					7 => "Misc Notes",
					11 => "Spouse's Employment Details",
					16 => "Carryover Notes"
				);

				foreach ($profileMetaArray as $data_id => $columnName)
				{
					if (array_key_exists($data_id, $optionalColumns) && !$optionalColumns[$data_id])
					{
						continue;
					}

					if ($data_id == 16)
					{
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
							'align' => 'left',
							'width' => 60,
							'wrap' => true
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else
					{
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					$labels = array_merge($labels, array($columnName));
				}

				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 30,
					'wrap' => true
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$labels = array_merge($labels, array(
					"Referral Type",
					"Referral Data"
				));

				if (!$storeDAO || $storeDAO->supports_corporate_crate)
				{
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$labels = array_merge($labels, array("Corporate Crate"));
				}
			}

			if ($sectionSwitches['df_INSTRUCTIONS'] && $_POST['guest_type'] == 'has_future_sessions')
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 60,
					'wrap' => true
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Special Instructions"));
				$columnCount++;
			}

			if ($sectionSwitches['df_CUSTOMIZATIONS'] && $_POST['guest_type'] == 'has_future_sessions')
			{
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 60,
					'wrap' => true
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$labels = array_merge($labels, array("Meal Customizations"));
				$columnCount++;
			}

			if ($storeDAO)
			{
				$tpl->assign('file_name', makeTitle("Guest Details", $storeDAO));
			}
			else
			{
				$tpl->assign('file_name', makeTitle("Guest Details", "All_Stores"));
			}

			$tpl->assign('useLib1_8', true);
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);
			$tpl->assign('rowcount', count($rows));
			$tpl->assign('col_descriptions', $columnDescs);

			$rowCount = count($rows);

			if ($exportAsExcel)
			{
				$_GET['export'] = 'xlsx';
			}
			else
			{
				$_GET['export'] = 'csv';
			}

			CLog::RecordReport("Guest Details Report", "Rows:$rowCount ~ Store: $store");
		}

		if ($rowCount == 0 && isset($_GET['export']))
		{
			$tpl->assign('no_results', true);
			unset($_GET['export']);
		}

		$tpl->assign('rowcount', $rowCount);

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);
		$tpl->assign('store', $store);
		$tpl->assign('page_title', "Guest Details Report");
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function getLoyaltyStatusString($thisRow, &$isInPlatePoints)
	{
		$newStatus = "";

		if ($thisRow['dream_reward_status'] == 1 || $thisRow['dream_reward_status'] == 3 || $thisRow['dream_reward_status'] == 5)
		{

			if ($thisRow['dream_rewards_version'] == 3)
			{
				$isInPlatePoints = true;
				$unconvertedPoints = CPointsUserHistory::getCurrentUnconvertedPoints($thisRow['id']);
				$lifetimePoints = CPointsUserHistory::getCurrentPointsLevel($thisRow['id']);

				list($curLevel, $nextLevel) = CPointsUserHistory::getLevelDetailsByPoints($lifetimePoints);
				$newStatus = $curLevel['title'];

				if ($thisRow['dream_reward_status'] == 5)
				{
					$newStatus = "On Hold (" . $curLevel['title'] . ")";
				}

				$thisRow['points_status'] = $unconvertedPoints;
			}
			else
			{
				$newStatus = "DR " . $thisRow['dream_reward_level'];
				if ($thisRow['dr_downgraded_order_count'] > 0)
				{
					$newStatus . " (downgrade: {$thisRow['dr_downgraded_order_count']} remain)";
				}
			}
		}
		else
		{
			$newStatus = "No Program";
		}

		return $newStatus;
	}

	function getPPLifeTimePoints($thisRow)
	{
		return CPointsUserHistory::getCurrentPointsLevel($thisRow['id']);
	}

	function getPPUnconvertedPoints($thisRow)
	{
		return CPointsUserHistory::getCurrentUnconvertedPoints($thisRow['id']);
	}
}