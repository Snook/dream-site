<?php
require_once 'DAO/Dream_taste_event_properties.php';
require_once 'DAO/BusinessObject/CRecipe.php';

class CDreamTasteEvent extends DAO_Dream_taste_event_properties
{

	static private $_sessionProperties = array();

	// rather than duplicate as used in eventList() and sessionProperties()
	//
	static private $master_query = "SELECT
		tsp.id AS todd_id,
		tsp.session_host,
		tsp.session_id,
		tsp.message,
		IFNULL(tsp.informal_host_name, u.firstname) AS informal_host_name,
		tsp.dream_taste_event_id,
		tsp.menu_pricing_method,
		dtet.theme_string,
		dtp.host_required,
		dtp.available_on_customer_site,
		dtp.password_required,
		dtp.can_rsvp_only,
		dtp.can_rsvp_upgrade,
		dtp.customer_coupon_eligible,
		CASE s.session_type
		WHEN 'DREAM_TASTE' THEN 'taste'
		WHEN 'FUNDRAISER' THEN 'fundraiser'
		WHEN 'TODD' THEN 'ref2'
		END AS session_ref,
		CASE s.session_type
		WHEN 'DREAM_TASTE' THEN 'Meal Prep Workshop'
		WHEN 'FUNDRAISER' THEN 'Fundraiser Event'
		WHEN 'TODD' THEN 'Taste of Dream Dinners'
		END AS session_type_text,
		s.*,
		b.id AS bundle_id,
		b.number_items_required,
		b.number_servings_required,
		b.price,
		GROUP_CONCAT(btmi.menu_item_id) AS menu_items,
		u.firstname,
		u.lastname,
		u.primary_email,
		st.id AS store_id,
		st.address_line1,
		st.address_line2,
		st.city,
		st.state_id,
		st.county,
		st.postal_code,
		st.email_address,
		st.telephone_day,
		st.telephone_evening,
		st.store_name,
		st.store_description,
		st.timezone_id,
	    st.is_corporate_owned
		FROM session_properties AS tsp
		INNER JOIN `session` AS s ON s.id = tsp.session_id AND s.is_deleted = '0'
		LEFT JOIN `user` AS u ON u.id = tsp.session_host AND u.is_deleted = '0'
		INNER JOIN store AS st ON st.id = s.store_id AND st.is_deleted = '0'
		LEFT JOIN dream_taste_event_properties AS dtp ON dtp.menu_id = s.menu_id AND dtp.id = tsp.dream_taste_event_id AND dtp.is_deleted = '0'
		LEFT JOIN bundle AS b ON b.id = dtp.bundle_id AND b.is_deleted = '0'
		LEFT JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id AND btmi.is_deleted = '0'
		LEFT JOIN dream_taste_event_theme AS dtet ON dtp.dream_taste_event_theme = dtet.id
		WHERE tsp.is_deleted = '0'";

	static function generatePDF($session_id)
	{
		$sessionDetails = CSession::getSessionDetail($session_id, false);

		if (!$sessionDetails)
		{
			$tpl = CApp::instance()->template();

			if (!$sessionDetails)
			{
				$tpl->setErrorMsg('Session details not found, please contact your store.');
			}

			CApp::bounce();
		}

		require_once('fpdf/class_multicelltag.php');
		$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
			215.9,
			279.4
		));

		$pdf->SetStyle("p", "times", "", 8, "130,0,30");
		$pdf->SetStyle("pb", "times", "B", 8, "130,0,30");
		$pdf->SetStyle("t1", "times", "", 8, "0,0,0");
		$pdf->SetStyle("t8", "times", "B", 8, "0,0,0");
		$pdf->SetStyle("t9", "times", "BI", 8, "0,0,0");
		$pdf->SetStyle("t5", "times", "", 5, "0,0,0");
		$pdf->SetStyle("tt", "times", "", 5, "0,0,0");
		$pdf->SetStyle("ts", "times", "B", 6, "0,0,0");

		$pdf->SetStyle("t1b", "times", "B", 8, "0,0,0");
		$pdf->SetStyle("t2b", "times", "B", 9, "0,0,0");
		$pdf->SetStyle("t3b", "times", "B", 11, "0,0,0");
		$pdf->SetStyle("t4b", "times", "B", 13, "0,0,0");

		$pdf->SetStyle("t2", "helvetica", "", 9, "0,0,0");
		$pdf->SetStyle("t3", "helvetica", "B", 9, "0,0,0");
		$pdf->SetStyle("t4", "times", "B", 7, "0,0,0");
		$pdf->SetStyle("hh", "times", "B", 11, "255,189,12");
		$pdf->SetStyle("font", "helvetica", "", 10, "0,0,255");
		$pdf->SetStyle("style", "helvetica", "BI", 10, "0,0,220");
		$pdf->SetStyle("size", "times", "BI", 13, "0,0,120");
		$pdf->SetStyle("color", "times", "BI", 13, "0,0,255");

		$pdf->SetStyle("hs", "helvetica", "", 11, "0,0,0");

		//$testPath =  "/stores/" . strtolower($basis) . "_zoom.gif";
		//$szFullFileName =  $testPath;

		$bg_image_path = APP_BASE . "www/theme/" . THEME . '/images/event_theme/' . $sessionDetails['dream_taste_theme_string'] . '/pdf_bg.png';

		// try falling back to default folder if file for the month doesn't exist
		if (!file_exists($bg_image_path))
		{
			$bg_image_path = APP_BASE . "www/theme/" . THEME . '/images/event_theme/' . $sessionDetails['dream_taste_theme_string_default'] . '/pdf_bg.png';
		}

		$pdf->AddPage();
		$pdf->Image($bg_image_path, 0, 0, 215.9, 279.4);
		$pdf->SetFont('helvetica', 'B', 16);

		$draw_borders = 0;

		// menu items
		if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
		{
			$menu_items = CRecipe::fetch_nutrition_data_by_mid($sessionDetails['menu_id'], $sessionDetails['dream_taste_menu_items'], $sessionDetails['store_id']);

			$item_name_array = array();

			foreach ($menu_items AS $recipe_id => $recipe)
			{
				if ($recipe['info']['is_visible'] == 1 && $recipe['info']['is_hidden_everywhere'] == 0)
				{
					$item_name_array[] = $recipe['info']['menu_item_name'];
				}
			}

			$menu_item_names = implode(' * ', $item_name_array);
		}

		// dream taste layout
		if (!empty($sessionDetails['dream_taste_host_required']) && empty($sessionDetails['dream_taste_can_rsvp_only']))
		{
			// menu items
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$pdf->SetXY(18, 139);
				$pdf->MultiCellTag(180, 6, '<hs>' . $menu_item_names . '</hs>', $draw_borders, 'C');
			}

			// Message
			$pdf->SetXY(15, 156);
			$pdf->MultiCellTag(187, 6, '<hs>' . $sessionDetails['session_host_message'] . '</hs>', $draw_borders, 'C');

			//Host name
			$pdf->SetXY(15, 204);
			$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['session_host_informal_name'] . '</hs>', $draw_borders, 'C');

			//Session Time
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
				$pdf->SetXY(130, 204);
				$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');
			}
			else
			{
				$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
				$pdf->SetXY(130, 139);
				$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');
			}

			// Link
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$pdf->SetXY(5, 224);
				$pdf->MultiCellTag(92, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');
			}
			else
			{
				$pdf->SetXY(5, 224);
				$pdf->MultiCellTag(92, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');
			}

			// Store location
			$locStr = $sessionDetails['store_name'] . "\n" . $sessionDetails['address_line1'] . (!empty($sessionDetails['address_line2']) ? ", " . $sessionDetails['address_line2'] . "\n" : "") . ' ' . $sessionDetails['city'] . ", " . $sessionDetails['state_id'] . " " . $sessionDetails['postal_code'] . "\n" . $sessionDetails['telephone_day'];
			$pdf->SetXY(130, 238);
			$pdf->MultiCellTag(72, 4, '<hs>' . $locStr . '</hs>', $draw_borders, 'C');

			// Password
			$pdf->SetXY(15, 244);
			$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['session_password'] . '</hs>', $draw_borders, 'C');
		}
		// Friends night out layout
		else if (!empty($sessionDetails['dream_taste_host_required']) && !empty($sessionDetails['dream_taste_can_rsvp_only']))
		{
			//Session Time
			$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
			$pdf->SetXY(62, 126);
			$pdf->MultiCellTag(90, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');

			// Store location
			$locStr = 'Dream Dinners' . "\n" . $sessionDetails['store_name'] . "\n" . $sessionDetails['address_line1'] . (!empty($sessionDetails['address_line2']) ? ", " . $sessionDetails['address_line2'] . "\n" : "") . ' ' . $sessionDetails['city'] . ", " . $sessionDetails['state_id'] . " " . $sessionDetails['postal_code'] . "\n" . $sessionDetails['telephone_day'];
			$pdf->SetXY(63, 152);
			$pdf->MultiCellTag(90, 4, '<hs>' . $locStr . '</hs>', $draw_borders, 'C');

			// Link
			$pdf->SetXY(58, 189);
			$pdf->MultiCellTag(100, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');

			// Password
			$pdf->SetXY(62, 208);
			$pdf->MultiCellTag(90, 6, '<hs>' . $sessionDetails['session_password'] . '</hs>', $draw_borders, 'C');
		}
		// open house layout
		else
		{
			// menu items
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$pdf->SetXY(18, 145);
				$pdf->MultiCellTag(180, 6, '<hs>' . $menu_item_names . '</hs>', $draw_borders, 'C');
			}

			//Session Time
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
				$pdf->SetXY(72, 168);
				$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');
			}
			else
			{
				$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
				$pdf->SetXY(72, 145);
				$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');
			}

			// Link
			if (!empty($sessionDetails['dream_taste_menu_used_with_theme']))
			{
				$pdf->SetXY(32, 226);
				$pdf->MultiCellTag(150, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');
			}
			else
			{
				$pdf->SetXY(32, 168);
				$pdf->MultiCellTag(150, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');
			}

			// Store location
			$locStr = 'Dream Dinners' . "\n" . $sessionDetails['store_name'] . "\n" . $sessionDetails['address_line1'] . (!empty($sessionDetails['address_line2']) ? ", " . $sessionDetails['address_line2'] . "\n" : "\n") . ' ' . $sessionDetails['city'] . ", " . $sessionDetails['state_id'] . " " . $sessionDetails['postal_code'] . "\n" . $sessionDetails['telephone_day'];
			$pdf->SetXY(72, 192);
			$pdf->MultiCellTag(72, 4, '<hs>' . $locStr . '</hs>', $draw_borders, 'C');

			// Password
			$pdf->SetXY(70, 246);
			$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['session_password'] . '</hs>', $draw_borders, 'C');
		}

		$pdf->Output();
	}

	static function sendThirdOrderTasteReminder($dataObj)
	{
		$subject = 'Host a Meal Prep Workshop, Get Perks';

		require_once 'CMail.inc';

		$Mail = new CMail();

		$contentsText = CMail::mailMerge('event_theme/invite_hostess_to_host_dream_taste.txt.php', $dataObj, false);
		$contentsHtml = CMail::mailMerge('event_theme/invite_hostess_to_host_dream_taste.html.php', $dataObj, false);

		$Mail->send(null, null, $dataObj->firstname . ' ' . $dataObj->lastname, $dataObj->primary_email, $subject, $contentsHtml, $contentsText, '', '', $dataObj->user_id, 'Chef Meal Prep Workshop reminder');

		$guestnote = DAO_CFactory::create('user_data');
		$guestnote->user_id = $dataObj->user_id;
		$guestnote->store_id = $dataObj->home_store_id;
		$guestnote->user_data_field_id = 16;

		$dreamTasteNote = 'Guest leveled up to Chef on ' . CTemplate::dateTimeFormat(date($dataObj->timestamp_created), VERBOSE_DATE) . ' and was sent a reminder to host a Meal Prep Workshop on ' . CTemplate::dateTimeFormat(date('Y-m-d H:i:s'), VERBOSE_DATE);

		if ($guestnote->find(true))
		{
			$guestnote->user_data_value .= "\n\n" . $dreamTasteNote;
			$guestnote->update();
		}
		else
		{
			$guestnote->user_data_value = $dreamTasteNote;
			$guestnote->insert();
		}

		CUser::updateUserDigest($dataObj->user_id, 'dream_taste_third_order_invite', date("Y-m-d H:i:s"));
	}

	static function dreamTasteProperties($menu_id, $Store = false, $dream_taste_event_theme = false)
	{
		if ($Store && $Store->dream_taste_opt_out)
		{
			return false;
		}

		$query_dream_taste_event_theme = "";
		if ($dream_taste_event_theme)
		{
			$query_dream_taste_event_theme = "AND dtp.dream_taste_event_theme = '" . $dream_taste_event_theme . "'";
		}

		$daoTasteTypes = DAO_CFactory::create('dream_taste_event_properties');
		$daoTasteTypes->query("SELECT
			dtp.*,
			dt.title,
			dt.fadmin_acronym
			FROM
			dream_taste_event_properties AS dtp
			INNER JOIN dream_taste_event_theme AS dt ON dt.id = dtp.dream_taste_event_theme
			WHERE dtp.menu_id = '" . $menu_id . "'
			AND dt.session_type = '" . CSession::DREAM_TASTE . "'
			AND dtp.is_deleted = '0'
			" . $query_dream_taste_event_theme . "
			ORDER BY dt.sort ASC");

		$tasteTypeInfo = array();

		while ($daoTasteTypes->fetch())
		{
			// donated dream taste only at corporate + 3 others
			if (($daoTasteTypes->fadmin_acronym == 'DDT' || $daoTasteTypes->fadmin_acronym == 'DMPW' || $daoTasteTypes->fadmin_acronym == 'MPWD') && (!$Store->is_corporate_owned && $Store->id != 127 && $Store->id != 204 && $Store->id != 280 && $Store->id != 291))
			{
				continue;
			}

			$tasteTypeInfo[$daoTasteTypes->id] = $daoTasteTypes->toArray();
		}

		if (empty($tasteTypeInfo))
		{
			return false;
		}

		return $tasteTypeInfo;
	}

	static function eventList($user_id)
	{
		$sessionProperties = DAO_CFactory::create('session');
		$sessionProperties->query(self::$master_query . "
			AND tsp.session_host = '" . $user_id . "'
			GROUP BY tsp.id
			ORDER BY s.session_start ASC");

		return $sessionProperties;
	}

	static function getUsersFuturePastEvents($user_id)
	{
		$EventsList = self::eventSessionList($user_id);
		$sessionArray = CSession::getSessionDetailArray($EventsList, true);

		//
		$manageEvent = false;
		$manageInviteOnly = false;
		$eventReferrals = array();
		$guestsAttending = array();
		$referredGuestEmails = array();
		$attendingGuestEmails = array();
		$pastEvents = array();
		$currentEvents = array();

		if (!empty($_GET['sid']) && is_numeric($_GET['sid']))
		{
			$manageSessionID = $_GET['sid'];

			if (!empty($sessionArray[$manageSessionID]))
			{
				// the guest is hosting this event, they can see all how are attending
				$manageEvent = $sessionArray[$manageSessionID];
			}
			else
			{
				// guest isn't the host of the session, so they get a generic invite form
				$manageInviteOnly = true;

				$sessionArray = CSession::getSessionDetailArray($manageSessionID, true);

				$manageEvent = $sessionArray[$manageSessionID];
			}

			$referrals = DAO_CFactory::create('customer_referral');
			$referrals->referrer_session_id = $manageSessionID;
			$referrals->referring_user_id = $user_id;
			$referrals->find();

			while ($referrals->fetch())
			{
				$eventReferrals[$referrals->id] = array(
					'referred_user_name' => $referrals->referred_user_name,
					'referred_user_email' => $referrals->referred_user_email,
					'referred_user_send_email' => false
				);

				$referredGuestEmails[] = $referrals->referred_user_email;
			}
		}

		// load up guests who are attending based on if the hostess can view all
		if (!empty($manageEvent['bookings']))
		{
			foreach ($manageEvent['bookings'] AS $booking)
			{
				if ($booking['status'] == CBooking::ACTIVE)
				{
					$attendingGuestEmails[] = $booking['primary_email'];

					if (!$manageInviteOnly || $booking['user_id'] == $user_id || ($manageInviteOnly && in_array($booking['primary_email'], $referredGuestEmails)))
					{
						$guestsAttending[] = $booking;
					}
				}
			}
		}

		if (!empty($manageEvent['session_rsvp']))
		{
			foreach ($manageEvent['session_rsvp'] AS $booking)
			{
				$attendingGuestEmails[] = $booking->user->primary_email;

				if (!$manageInviteOnly || $booking->user->id == $user_id || ($manageInviteOnly && in_array($booking->user->primary_email, $referredGuestEmails)))
				{
					$guestsAttending[] = $booking->user;
				}
			}
		}

		// bookings and rsvp are mixed between arrays and objects, this forces them all to be arrays
		$guestsAttending = json_decode(json_encode($guestsAttending), true);

		// my events landing page page/present events
		foreach ($sessionArray AS $session_id => $sessionDetail)
		{
			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($sessionDetail['timezone_id']);

			if (strtotime($sessionDetail['session_close_scheduling']) > $now)
			{
				$currentEvents[$session_id] = $sessionDetail;
			}
			else
			{
				$pastEvents[$session_id] = $sessionDetail;
			}
		}

		return array(
			'remainingTotalAttending' => count($attendingGuestEmails) - count($guestsAttending),
			'guestsAttending' => $guestsAttending,
			'attendingGuestEmails' => $attendingGuestEmails,
			'manageInviteOnly' => $manageInviteOnly,
			'upcomingEvents' => $currentEvents,
			'pastEvents' => $pastEvents,
			'manageEvent' => $manageEvent,
			'eventReferrals' => $eventReferrals,
			'eventReferralsJS' => ((!empty($eventReferrals)) ? json_encode($eventReferrals) : '{}')
		);
	}

	static function eventSessionList($user_id)
	{
		$sessionProperties = DAO_CFactory::create('session');
		$sessionProperties->query("SELECT
			GROUP_CONCAT(tsp.session_id) AS session_ids
			FROM session_properties AS tsp
			INNER JOIN `session` AS s ON s.id = tsp.session_id AND s.session_publish_state = 'PUBLISHED' AND s.is_deleted = '0'
			WHERE tsp.is_deleted = '0'
			AND tsp.session_host = '" . $user_id . "'
			ORDER BY s.session_start ASC");
		$sessionProperties->fetch();

		return $sessionProperties->session_ids;
	}

	static function sessionProperties($session_id)
	{
		if (!empty(self::$_sessionProperties[$session_id]))
		{
			return self::$_sessionProperties[$session_id];
		}

		$sessionProperties = DAO_CFactory::create('session');
		$sessionProperties->query(self::$master_query . "
			AND tsp.session_id = '" . $session_id . "'
			GROUP BY tsp.id
			ORDER BY s.session_start ASC");

		// Bundle Price Intercept Point

		if ($sessionProperties->fetch())
		{

			if ($sessionProperties->menu_id > 176 && $sessionProperties->is_corporate_owned)
			{
				$sessionProperties->price = 34.99;
			}

			return self::$_sessionProperties[$session_id] = $sessionProperties;
		}
		else
		{
			return false;
		}
	}
}

?>