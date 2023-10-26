<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CMail.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CUserData.php");
require_once("includes/DAO/BusinessObject/CFoodTesting.php");
require_once("includes/DAO/BusinessObject/CEmail.php");
require_once("page/admin/main.php");
require_once("page/admin/create_session.php");
require_once("processor/admin/processMetrics.php");

class processor_admin_fadmin_home extends CPageProcessor
{

	private $emergency_mode = false;
    function __construct()
    {
        $this->inputTypeMap['note'] = TYPE_NOCLEAN;
    }


    function runEventCoordinator()
	{
		$this->mainProcessor();
	}

	function runOpsLead()
	{
		$this->mainProcessor();
	}

	function runFranchiseStaff()
	{
		$this->mainProcessor();
	}

	function runFranchiseLead()
	{
		$this->mainProcessor();
	}

	function runOpsSupport()
	{
		$this->mainProcessor();
	}

	function runFranchiseManager()
	{
		$this->mainProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->mainProcessor();
	}

	function runFranchiseOwner()
	{
		$this->mainProcessor();
	}

	function runSiteAdmin()
	{
		$this->mainProcessor();
	}

	function mainProcessor()
	{
		if (defined('DESIGNATE_AS_EMERGENCY_REPORTING_SERVER') && DESIGNATE_AS_EMERGENCY_REPORTING_SERVER)
		{
			$this->emergency_mode = true;
		}


		if (!empty($_POST['op']))
		{
			// Get user plateploints tooltip
			if ($_POST['op'] == 'platepoints_tooltip' && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
			{
				$user_id = $_POST['user_id'];

				$tpl = new CTemplate();

				$User = DAO_CFactory::create('user');
				$User->id = $user_id;
				$User->find(true);

				$User->getPlatePointsSummary();

				$tpl->assign('user', $User);

				$tooltip_html = $tpl->fetch('admin/subtemplate/user_details_platepoints_tooltip.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved User Details.',
					'user_id' => $user_id,
					'tooltip_html' => $tooltip_html
				));
			}

			// Get dashboard details
			if ($_POST['op'] == 'dashboard_details' && !empty($_POST['dashboard_date']))
			{

				$curMenuObj = CMenu::getMenuByDate($_POST['dashboard_date']);
				$menuMonth = $curMenuObj['menu_start'];


				list($dashboard_update_required, $dashboard_metrics_info) = CDashboardMenuBased::getMetricsSnapShot($_POST['store_id'], $menuMonth);

				if ($dashboard_update_required)
				{
					$proccessor = new processor_admin_processMetrics();
					$proccessor->processMetricsLocal($_POST['store_id']);

					list($dashboard_update_required, $dashboard_metrics_info) = CDashboardMenuBased::getMetricsSnapShot($_POST['store_id'], $menuMonth);
				}

				$title = CTemplate::dateTimeFormat($menuMonth, VERBOSE_MONTH_YEAR);

				$tpl = new CTemplate();

				$tpl->assign('dashboard_metrics', $dashboard_metrics_info);


				if( CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER){
					$dashboard_metrics = $tpl->fetch('admin/subtemplate/main_dashboard_summary_delivered.tpl.php');
				}else{
					$dashboard_metrics = $tpl->fetch('admin/subtemplate/main_dashboard_summary.tpl.php');
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved Metrics Details.',
					'dashboard_update_required' => $dashboard_update_required,
					'dashboard_title' => $title,
					'dashboard_metrics' => $dashboard_metrics
				));
			}

			// Get agenda details
			if ($_POST['op'] == 'agenda_details' && !empty($_POST['agenda_date']))
			{
				$Store = DAO_CFactory::create('store');
				$Store->id = $_POST['store_id'];
				$Store->find(true);

				$date = strtotime($_POST['agenda_date']);

				$tmpName = 'admin/subtemplate/main_agenda.tpl.php';
				$sessionsArray = array();

				if($Store->store_type === CStore::DISTRIBUTION_CENTER){
					$tmpName = 'admin/subtemplate/main_agenda_delivered.tpl.php';
					$sessionsArray = CSession::getMonthlySessionInfoArrayForDelivered($Store, $date, false, false, false, true, false);
				}else{
					$sessionsArray = CSession::getMonthlySessionInfoArray($Store, $date, false, false, false, false, false, false,false);
				}

				$tpl = new CTemplate();

				$tpl->assign('sessions', $sessionsArray['sessions']);

				$agenda_info = $tpl->fetch($tmpName);

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved Session Details.',
					'session_info' => $agenda_info,
					'date' => date('Y-m-d', strtotime($_POST['agenda_date']))
				));
			}

			// Get session and booking details
			if ($_POST['op'] == 'session_details' && is_numeric($_POST['session_id']))
			{
				$tpl = new CTemplate();

				$session_id = $_POST['session_id'];


				$sessionData = CSession::getSessionDetail($session_id, false);
				if( $sessionData['session_type'] === CSession::DELIVERED)
				{
					$session_info_array = CSession::getDeliveredSessionDetailArray($session_id, true);
				}else{
					$session_info_array = CSession::getSessionDetailArray($session_id);
				}
				$minimum = COrderMinimum::fetchInstance( COrderMinimum::STANDARD_ORDER_TYPE, $sessionData['store_id'], $sessionData['menu_id']);
				$tpl->assign('order_minimum', $minimum);

				foreach ($session_info_array[$session_id]['bookings'] as $id => &$data)
				{
					if (isset($data['order_admin_notes']))
					{
						$data['order_admin_notes'] = htmlentities($data['order_admin_notes'], ENT_QUOTES, 'utf-8', false);
					}

					if (isset($data['order_user_notes']))
					{
						$data['order_user_notes'] = htmlentities($data['order_user_notes'], ENT_QUOTES, 'utf-8', false);
					}

					if (isset($data['user_data_values']))
					{
						$data['user_data_values'] = htmlentities($data['user_data_values'], ENT_QUOTES, 'utf-8', false);
					}
				}

				if (($session_info_array[$session_id]['session_type'] == CSession::SPECIAL_EVENT
					|| $session_info_array[$session_id]['session_type'] == CSession::STANDARD)
					&& $session_info_array[$session_id]['remaining_intro_slots'] > 0)
				{
					$DAO_menu = DAO_CFactory::create('menu', true);
					$DAO_menu->id = $sessionData["menu_id"];

					if ($DAO_menu->isEnabled_Starter_Pack_Bundle())
					{
						$tpl->assign('show_start_pack_link', true);
					}
					else
					{
						$tpl->assign('show_start_pack_link', false);
					}
				}

				$tpl->assign('food_testing_recipes', CFoodTesting::getRecipesForStore($session_info_array[$session_id]['store_id']));
				$tpl->assign('session_info', $session_info_array[$session_id]);
				$tpl->assign('date', array(
					"this_M" => date("M", time()),
					"next_M" => date("M", strtotime('first day of next month')),
					"next_M_time" => strtotime('first day of next month')
				));

				$tpl->assign('store_supports_plate_points', CStore::storeSupportsPlatePoints($session_info_array[$session_id]['store_id']));

				if ($this->emergency_mode)
				{
					$session_details = $tpl->fetch('admin/subtemplate/emergency_main_session_details.tpl.php');
					$booking_details = $tpl->fetch('admin/subtemplate/emergency_main_booked_guests.tpl.php');
				}
				else if( $sessionData['session_type'] === CSession::DELIVERED)
				{
					$session_details = $tpl->fetch('admin/subtemplate/main_session_details_delivered.tpl.php');
					$booking_details = $tpl->fetch('admin/subtemplate/main_booked_guests_delivered.tpl.php');
				}
				else if( $sessionData['session_type_subtype'] === CSession::WALK_IN)
				{
					$session_details = $tpl->fetch('admin/subtemplate/main_session_details_walk_in.tpl.php');
					$booking_details = $tpl->fetch('admin/subtemplate/main_booked_guests.tpl.php');
				}
				else
				{
					$session_details = $tpl->fetch('admin/subtemplate/main_session_details.tpl.php');
					$booking_details = $tpl->fetch('admin/subtemplate/main_booked_guests.tpl.php');
				}


				$booking_details = mb_convert_encoding($booking_details, 'UTF-8', 'UTF-8');

				$session_info = $session_info_array[$session_id];
				unset($session_info['bookings']); // don't need all this info back in json

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved Session Details.',
					'session_details' => $session_details,
					'booking_details' => $booking_details,
					'session_info' => $session_info,
					'session_data'=> $sessionData
				));
			}

			// Get details about the selected date
			if ($_POST['op'] == 'date_details' && !empty($_POST['date']))
			{
				$tpl = new CTemplate();

				list ($date_info_array, $session_info_array) = CSession::getSessionDetailArrayByDate($_POST['store_id'], $_POST['date']);

				$tpl->assign('food_testing_recipes', CFoodTesting::getRecipesForStore($_POST['store_id']));
				$tpl->assign('date_info', $date_info_array);
				$tpl->assign('date', array(
					"this_M" => date("M", time()),
					"next_M" => date("M", strtotime('first day of next month')),
					"next_M_time" => strtotime('first day of next month')
				));

				//$date_details = $tpl->fetch('admin/subtemplate/main_date_details.tpl.php');

				if( CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER){
					reset($session_info_array);
					$session_id = key($session_info_array);
					$session_info_delivered = CSession::getDeliveredSessionDetailArray($session_id, true);
					$tpl->assign('session_info_delivered', $session_info_delivered[$session_id]);
					$date_details = $tpl->fetch('admin/subtemplate/main_date_details_delivered.tpl.php');
				}else{
					$date_details = $tpl->fetch('admin/subtemplate/main_date_details.tpl.php');
				}

				$booking_details = '';

				$tpl->assign('store_supports_plate_points', CStore::storeSupportsPlatePoints($_POST['store_id']));

				$minimum = COrderMinimum::fetchInstance( COrderMinimum::STANDARD_ORDER_TYPE, $_POST['store_id'], $date_info_array['menu_id']);
				$tpl->assign('order_minimum', $minimum);
				foreach ($session_info_array AS $session_id => $session_info)
				{
					$tpl->assign('session_info', $session_info_array[$session_id]);
					if ($this->emergency_mode)
					{
						$booking_details .= $tpl->fetch('admin/subtemplate/emergency_main_booked_guests.tpl.php');
					}else if( $session_info_array[$session_id]['session_type'] === CSession::DELIVERED){
						$tpl->assign('session_info', $session_info_delivered[$session_id]);
						$booking_details = $tpl->fetch('admin/subtemplate/main_booked_guests_delivered.tpl.php');
					}
					else
					{
						$booking_details .= $tpl->fetch('admin/subtemplate/main_booked_guests.tpl.php');
					}
				}

				if (!empty($booking_details) && !empty($date_info_array['booked_count']))
				{
					$booking_details .= $tpl->fetch('admin/subtemplate/main_booked_guests_legend.tpl.php');
				}

				$booking_details = mb_convert_encoding($booking_details, 'UTF-8', 'UTF-8');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved Date Details.',
					'date_details' => $date_details,
					'booking_details' => $booking_details,
					'date_info' => $date_info_array
				));
			}

			// Assign food testing recipe to guest
			if ($_POST['op'] == 'assign_test_recipe' && is_numeric($_POST['session_id']) && is_numeric($_POST['survey_id']))
			{
				$User = DAO_CFactory::create('user');
				$User->id = $_POST['user_id'];

				if ($User->find(true))
				{
					$surveyInfo = DAO_CFactory::create('food_testing_survey');
					$surveyInfo->query("SELECT
						ft.title
						FROM food_testing_survey AS fts
						INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
						WHERE fts.id = '" . $_POST['survey_id'] . "'
						AND fts.is_deleted = '0'");
					$surveyInfo->fetch();

					$ftSurvey = DAO_CFactory::create('food_testing_survey_submission');
					$ftSurvey->food_testing_survey_id = $_POST['survey_id'];
					$ftSurvey->user_id = $User->id;
					$ftSurvey->session_id = $_POST['session_id'];
					$ftSurvey->menu_id = $_POST['menu_id'];
					$ftSurvey->serving_size = ((strtolower($_POST['survey_size']) == 'medium') ? 'HALF' : 'FULL');
					$ftSurvey->timestamp_received = CTemplate::unix_to_mysql_timestamp(time());

					if ($ftSurvey->insert())
					{
						// send email to user
						$Mail = new CMail();

						$invite_array['primary_email'] = $User->primary_email;
						$invite_array['firstname'] = $User->firstname;
						$invite_array['store_id'] = $User->home_store_id;
						$invite_array['recipe_name'] = $surveyInfo->title;

						$contentsHtml = CMail::mailMerge('food_testing/guest_received_recipe.html.php', $invite_array);
						$contentsText = CMail::mailMerge('food_testing/guest_received_recipe.txt.php', $invite_array);

						$Mail->send(null, null, $User->firstname . ' ' . $User->lastname, $User->primary_email, "Dream Dinners Food Testing", $contentsHtml, $contentsText, '', '', 0, 'food_test_guest_received_recipe');

						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Assigned test recipe.',
							'user_id' => $User->id
						));
					}
					else
					{
						echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'Could not assign test recipe.'
						));
					}
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Could not find user.'
					));
				}
			}

			// Resend dream taste email to host
			if ($_POST['op'] == 'resend_dream_taste_email' && is_numeric($_POST['session_id']))
			{
				$session_id = $_POST['session_id'];

				$session_info_array = CSession::getSessionDetailArray($session_id);

				// array to object for sendHostessNotification
				$Session = json_decode(json_encode($session_info_array[$session_id]), false);

				$Store = DAO_CFactory::create('store');
				$Store->id = $_POST['store_id'];
				$Store->find(true);

				CEmail::sendHostessNotification($Session->session_host_primary_email, $Session->session_host_firstname . ' ' . $Session->session_host_lastname, $Session);

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Resent Host Notification.'
				));
			}

			// Guest carryover note
			if ($_POST['op'] == 'guest_carryover_note' && is_numeric($_POST['store_id']))
			{
				if ($_POST['do'] == 'get')
				{
					$guestnote = CUserData::userCarryoverNote($_POST['user_id'], $_POST['store_id']);

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Guest carryover notes retrieved.',
						'guest_note' => $guestnote
					));
				}
				else if ($_POST['do'] == 'save')
				{
					$guestnote = CUserData::userCarryoverNote($_POST['user_id'], $_POST['store_id'], $_POST['note']);

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Guest carryover notes updated.',
						'guest_note' => $guestnote
					));
				}
			}

			// Order admin note
			if ($_POST['op'] == 'order_admin_note' && is_numeric($_POST['order_id']))
			{
				if ($_POST['do'] == 'get')
				{
					$orderNote = DAO_CFactory::create('orders');
					$orderNote->query("SELECT order_admin_notes FROM orders WHERE id = '" . $_POST['order_id'] . "' AND is_deleted = '0'");
					$orderNote->fetch();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Order admin notes retrieved.',
						'admin_note' => $orderNote->order_admin_notes
					));
				}
				else if ($_POST['do'] == 'save')
				{
					require_once('includes/class.inputfilter_clean.php');

					$xssFilter = new InputFilter();
					$set_note = $xssFilter->process($_POST['note']);

					$note = str_replace(array(
						"\r",
						"\r\n",
						"\n"
					), ' ', strip_tags($set_note));

					$orderNote = DAO_CFactory::create('orders');
					$orderNote->id = $_POST['order_id'];
					$orderNote->fetch();

					$orderNote->order_admin_notes = $note;
					$orderNote->update();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Order admin notes updated.',
						'admin_note' => $orderNote->order_admin_notes
					));
				}
			}

			// Resend dream taste email to host
			if ($_POST['op'] == 'session_publish_state' && is_numeric($_POST['session_id']))
			{
				$Session = DAO_CFactory::create('session');
				$Session->id = $_POST['session_id'];

				if ($Session->find(true))
				{
					if (strtolower($_POST['open_close_submit']) == 'close')
					{
						$Session->session_publish_state = CSession::CLOSED;
						$Session->update();

						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Session set to closed.',
							'session_publish_state' => CSession::CLOSED,
							'dd_toasts' => array(
								array('message' => 'Session set to closed.')
							)
						));
					}
					else if (strtolower($_POST['open_close_submit']) == 'open')
					{
						$Session->session_publish_state = CSession::PUBLISHED;
						$Session->update();

						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Session set to open.',
							'session_publish_state' => CSession::PUBLISHED,
							'dd_toasts' => array(
								array('message' => 'Session set to open.')
							)
						));
					}
				}
				else
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Session not found.'
					));
				}
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'No operation.'
			));
		}
	}
}

?>