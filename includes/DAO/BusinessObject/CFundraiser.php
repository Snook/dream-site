<?php
require_once 'DAO/Fundraiser.php';
require_once 'DAO/BusinessObject/CRecipe.php';

class CFundraiser extends DAO_Fundraiser
{
	static private $fundRaiserArray = null;


	static function fundraiserEventSessionProperties($sessionObj)
	{
		$sessionPropObj = false;

		if ($sessionObj->session_type == CSession::FUNDRAISER)
		{
			$sessionPropObj = DAO_CFactory::create('session_properties');
			$sessionPropObj->query("SELECT
				sp.dream_taste_event_id,
				sp.fundraiser_id,
				f.fundraiser_name,
				f.fundraiser_description,
				dtep.fundraiser_value,
				dtep.can_rsvp_only,
				dtep.can_rsvp_upgrade,
				dtep.bundle_id,
				b.number_items_required,
				b.number_servings_required,
				b.price,
				GROUP_CONCAT(btmi.menu_item_id) AS menu_items
				FROM session_properties AS sp
				INNER JOIN store_to_fundraiser AS stf ON stf.id = sp.fundraiser_id AND stf.is_deleted = '0'
				INNER JOIN fundraiser AS f ON f.id = stf.fundraiser_id AND f.is_deleted = '0'
				INNER JOIN dream_taste_event_properties AS dtep ON dtep.id = sp.dream_taste_event_id AND dtep.is_deleted = '0'
				INNER JOIN bundle AS b ON b.id = dtep.bundle_id AND b.is_deleted = '0'
				LEFT JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id AND btmi.is_deleted = '0'
				WHERE sp.session_id = '" . $sessionObj->id . "'
				AND sp.is_deleted = '0'");

			$sessionPropObj->fetch();
		}

		return $sessionPropObj;
	}

	/**
	 * @param      $menu_id
	 * @param bool $Store
	 * @param bool $fundraiser_theme
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	static function fundraiserProperties($menu_id, $Store = false, $fundraiser_theme = false)
	{
		if ($Store && empty($Store->supports_fundraiser))
		{
			return false;
		}

		$query_fundraiser_theme = "";
		if ($fundraiser_theme)
		{
			$query_fundraiser_theme = "AND dtp.dream_taste_event_theme = '" . $fundraiser_theme . "'";
		}

		$daoTasteTypes = DAO_CFactory::create('dream_taste_event_properties');
		$daoTasteTypes->query("SELECT
			dtp.*,
			dt.title,
       		dt.fadmin_acronym
			FROM dream_taste_event_properties AS dtp
			INNER JOIN dream_taste_event_theme AS dt ON dt.id = dtp.dream_taste_event_theme
			WHERE dtp.menu_id = '" . $menu_id . "'
			AND dt.session_type = '" . CSession::FUNDRAISER . "'
			AND dtp.is_deleted = '0'
			" . $query_fundraiser_theme);

		$fundraiserTypeInfo = array();

		while ($daoTasteTypes->fetch())
		{
			$fundraiserTypeInfo[$daoTasteTypes->id] = $daoTasteTypes->toArray();
		}

		if (empty($fundraiserTypeInfo))
		{
			return false;
		}

		return $fundraiserTypeInfo;
	}

	/**
	 * @param      $Store
	 * @param bool $store_to_fundraiser_id
	 * @param bool $active_only
	 *
	 * @return array
	 * @throws Exception
	 */
	static function storeFundraiserArray($Store, $store_to_fundraiser_id = false, $get_totals = false)
	{
		$store_to_fundraiser_id_query = '';

		if ($store_to_fundraiser_id)
		{
			$store_to_fundraiser_id_query = "AND stf.id = '" . $store_to_fundraiser_id . "'";
		}

		if ($get_totals)
		{
			$fundraiserTotal = DAO_CFactory::create('fundraiser');
			$fundraiserTotal->query("SELECT
			o.fundraiser_id,
			SUM(o.fundraiser_value) AS fundraiser_total,
			COUNT(o.order_id) AS total_orders
			FROM (SELECT
			o.id AS order_id,
			o.fundraiser_id,
			o.fundraiser_value,
			f.fundraiser_name,
			f.fundraiser_description,
			f.donation_value
			FROM orders AS o
			INNER JOIN store_to_fundraiser AS stf ON stf.id = o.fundraiser_id AND stf.store_id = '" . $Store->id . "' AND stf.is_deleted = '0' " . $store_to_fundraiser_id_query . "
			INNER JOIN fundraiser AS f ON f.id = stf.fundraiser_id AND f.is_deleted = '0'
			WHERE o.fundraiser_id IS NOT NULL AND o.is_deleted = '0') AS o
			INNER JOIN booking AS b ON b.order_id = o.order_id AND b.is_deleted = '0' AND b.status = 'ACTIVE'
			GROUP BY o.fundraiser_id");

			$fundraiserTotalArray = array();

			while ($fundraiserTotal->fetch())
			{
				$fundraiserTotalArray[$fundraiserTotal->fundraiser_id] = clone $fundraiserTotal;
			}
		}

		$fundraisers = DAO_CFactory::create('fundraiser');
		$fundraisers->query("SELECT
			stf.id,
			stf.store_id,
			stf.fundraiser_id,
			stf.active,
			f.fundraiser_name,
			f.fundraiser_description,
			f.donation_value,
			f.timestamp_created
			FROM store_to_fundraiser AS stf
			INNER JOIN fundraiser AS f ON f.id = stf.fundraiser_id AND f.is_deleted = '0'
			WHERE stf.store_id = '" . $Store->id . "'
			AND stf.is_deleted = '0'
			" . $store_to_fundraiser_id_query . "
			GROUP BY stf.id
			ORDER BY stf.id DESC");

		$fundraiserArray = array();

		while ($fundraisers->fetch())
		{
			$fundraiserArray[$fundraisers->id] = clone $fundraisers;

			if ($get_totals)
			{
				$fundraiserArray[$fundraisers->id]->fundraiser_total = (!empty($fundraiserTotalArray[$fundraisers->id]->fundraiser_total) ? $fundraiserTotalArray[$fundraisers->id]->fundraiser_total : 0);
				$fundraiserArray[$fundraisers->id]->total_orders = (!empty($fundraiserTotalArray[$fundraisers->id]->total_orders) ? $fundraiserTotalArray[$fundraisers->id]->total_orders : 0);
			}
		}

		return $fundraiserArray;
	}

	/**
	 * @param $Store
	 * @param $title
	 * @param $description
	 * @param $value
	 *
	 * @return array
	 * @throws Exception
	 */
	static function addFundraiser($Store, $title, $description, $value)
	{
		$fundraiser = DAO_CFactory::create('fundraiser');
		$fundraiser->fundraiser_name = $title;
		$fundraiser->fundraiser_description = $description;
		$fundraiser->donation_value = number_format($value, 2);
		$fundraiser->insert();

		$store_to_fundraiser = DAO_CFactory::create('store_to_fundraiser');
		$store_to_fundraiser->store_id = $Store->id;
		$store_to_fundraiser->fundraiser_id = $fundraiser->id;
		$store_to_fundraiser->insert();

		return array(
			$store_to_fundraiser->fundraiser_id,
			self::storeFundraiserArray($Store)
		);
	}

	/**
	 * @param $Store
	 * @param $id
	 * @param $title
	 * @param $description
	 * @param $value
	 *
	 * @return array
	 * @throws Exception
	 */
	static function editFundraiser($Store, $id, $title, $description, $value)
	{
		$fundraiser = DAO_CFactory::create('fundraiser');
		$fundraiser->id = $id;
		$fundraiser->find();

		$fundraiser->fundraiser_name = $title;
		$fundraiser->fundraiser_description = $description;
		$fundraiser->donation_value = number_format($value, 2);
		$fundraiser->update();

		$store_to_fundraiser = DAO_CFactory::create('store_to_fundraiser');
		$store_to_fundraiser->store_id = $Store->id;
		$store_to_fundraiser->fundraiser_id = $fundraiser->id;
		$store_to_fundraiser->find(true);

		return array(
			$store_to_fundraiser->fundraiser_id,
			self::storeFundraiserArray($Store)
		);
	}

	/**
	 * @param $session_id
	 */
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

		$pdf->SetXY(28, 130);
		$pdf->MultiCellTag(160, 6, '<hs>' . $menu_item_names . '</hs>', $draw_borders, 'C');

		// organization
		$pdf->SetXY(72, 161);
		$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['fundraiser_name'] . '</hs>', $draw_borders, 'C');

		//Session Time
		$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
		$pdf->SetXY(72, 180);
		$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');

		// Link
		$pdf->SetXY(33, 226);
		$pdf->MultiCellTag(150, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');

		// Store location
		$locStr = 'Dream Dinners' . "\n" . $sessionDetails['store_name'] . "\n" . $sessionDetails['address_line1'] . (!empty($sessionDetails['address_line2']) ? ", " . $sessionDetails['address_line2'] . "\n" : "\n") . $sessionDetails['city'] . ", " . $sessionDetails['state_id'] . " " . $sessionDetails['postal_code'] . "\n" . $sessionDetails['telephone_day'];
		$pdf->SetXY(72, 192);
		$pdf->MultiCellTag(72, 4, '<hs>' . $locStr . '</hs>', $draw_borders, 'C');

		// Password
		$pdf->SetXY(70, 246);
		$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['session_password'] . '</hs>', $draw_borders, 'C');

		$pdf->Output();
	}
}

?>