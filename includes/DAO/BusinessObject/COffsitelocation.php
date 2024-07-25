<?php
require_once 'DAO/Store_pickup_location.php';
require_once 'DAO/BusinessObject/CRecipe.php';

class COffsitelocation extends DAO_Store_pickup_location
{
	static private $OffsitelocationArray = null;

	static function addUpdatePickupLocation($Store, $data, $edit_id = false)
	{
		$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location');

		if ($edit_id)
		{
			$DAO_store_pickup_location->id = $edit_id;
			$DAO_store_pickup_location->find(true);

			$DAO_store_pickup_location->contact = '';

			if (!empty($DAO_store_pickup_location->contact_user_id))
			{
				$contactUser = DAO_CFactory::create('user');
				$contactUser->id = $DAO_store_pickup_location->contact_user_id;
				$contactUser->find(true);

				$DAO_store_pickup_location->contact = $contactUser->primary_email;
			}

			$pickupLocationOrg = clone $DAO_store_pickup_location;
		}

		if (!empty($data["contact"]))
		{
			$contactUser = DAO_CFactory::create('user');
			$contactUser->primary_email = $data["contact"];
			$contactUser->find(true);

			$DAO_store_pickup_location->contact_user_id = $contactUser->id;
			$DAO_store_pickup_location->contact = $contactUser->primary_email;
		}
		else
		{
			$DAO_store_pickup_location->contact_user_id = 'NULL';
			$DAO_store_pickup_location->contact = 'NULL';
		}

		$DAO_store_pickup_location->store_id = $Store->id;
		$DAO_store_pickup_location->location_title = $data["location"];
		$DAO_store_pickup_location->address_line1 = $data["address_line1"];
		$DAO_store_pickup_location->address_line2 = strlen($data["address_line2"]) ? $data["address_line2"] : "";
		$DAO_store_pickup_location->city = $data["city"];
		$DAO_store_pickup_location->state_id = $data["state"];
		$DAO_store_pickup_location->postal_code = $data["postal_code"];

		if (empty($data["address_latitude"]) || empty($data["address_longitude"]))
		{
			$DAO_zipcodes = DAO_CFactory::create('zipcodes', true);
			$DAO_zipcodes->zip = $DAO_store_pickup_location->postal_code;
			$DAO_zipcodes->find(true);

			$DAO_store_pickup_location->address_latitude = $DAO_zipcodes->latitude;
			$DAO_store_pickup_location->address_longitude = $DAO_zipcodes->longitude;
		}
		else
		{
			$DAO_store_pickup_location->address_latitude = $data["address_latitude"];
			$DAO_store_pickup_location->address_longitude = $data["address_longitude"];
		}


		$DAO_store_pickup_location->default_session_override = 'NULL';

		if ($edit_id)
		{
			$DAO_store_pickup_location->update($pickupLocationOrg);
		}
		else
		{
			$DAO_store_pickup_location->insert();
		}

		return $DAO_store_pickup_location;
	}

	static function storeOffsitelocationArray($Store, $store_to_Offsitelocation_id = false, $get_totals = false)
	{
		$store_to_Offsitelocation_id_query = '';

		if ($store_to_Offsitelocation_id)
		{
			$store_to_Offsitelocation_id_query = "AND stf.id = '" . $store_to_Offsitelocation_id . "'";
		}

		if ($get_totals)
		{
			$OffsitelocationTotal = DAO_CFactory::create('store_pickup_location');
			$OffsitelocationTotal->query("SELECT * FROM store_pickup_location");

			$OffsitelocationTotalArray = array();

			while ($OffsitelocationTotal->fetch())
			{
				$OffsitelocationTotalArray[$OffsitelocationTotal->id] = clone $OffsitelocationTotal;
			}
		}

		$Offsitelocations = DAO_CFactory::create('store_pickup_location');
		$Offsitelocations->query("SELECT 
			spl.*,
			u.firstname,
			u.lastname,
			u.primary_email
			FROM store_pickup_location AS spl 
			LEFT JOIN user AS u ON u.id = spl.contact_user_id
			WHERE spl.store_id='" . $Store->id . "' 
			ORDER BY spl.active DESC, spl.id DESC");

		$OffsitelocationArray = array();

		while ($Offsitelocations->fetch())
		{
			$OffsitelocationArray[$Offsitelocations->id] = clone $Offsitelocations;

			if ($get_totals)
			{
				$OffsitelocationArray[$Offsitelocations->id]->Offsitelocation_total = (!empty($OffsitelocationTotalArray[$Offsitelocations->id]->Offsitelocation_total) ? $OffsitelocationTotalArray[$Offsitelocations->id]->Offsitelocation_total : 0);
				$OffsitelocationArray[$Offsitelocations->id]->total_orders = (!empty($OffsitelocationTotalArray[$Offsitelocations->id]->total_orders) ? $OffsitelocationTotalArray[$Offsitelocations->id]->total_orders : 0);
			}
		}

		return $OffsitelocationArray;
	}

	/**
	 * @param $session_id
	 *
	 * @throws Exception
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

		$bg_image_path = APP_BASE . "www/theme/" . THEME . '/images/event_theme/standard/made_for_you/remote_pickup_private/' . $sessionDetails['menu_directory'] . '/pdf_bg.png';

		// try falling back to default folder if file for the month doesn't exist
		if (!file_exists($bg_image_path))
		{
			$bg_image_path = APP_BASE . "www/theme/" . THEME . '/images/event_theme/standard/made_for_you/remote_pickup_private/default/pdf_bg.png';
		}

		$pdf->AddPage();
		$pdf->Image($bg_image_path, 0, 0, 215.9, 279.4);
		$pdf->SetFont('helvetica', 'B', 16);

		$draw_borders = 0;

		// Link
		$pdf->SetXY(10, 188);
		$pdf->MultiCellTag(84, 6, '<hs>' . HTTPS_BASE . 'session/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');

		// Link
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $sessionDetails['menu_id'];

		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->id = $sessionDetails['store_id'];
		$DAO_store->find(true);

		if ($DAO_menu->isEnabled_Starter_Pack($DAO_store))
		{
			$pdf->SetXY(10, 212);
			$pdf->MultiCellTag(84, 6, '<hs>' . HTTPS_BASE . 'starter/' . $sessionDetails['id'] . '</hs>', $draw_borders, 'C');
		}

		// Password
		$pdf->SetXY(10, 232);
		$pdf->MultiCellTag(84, 6, '<hs>' . $sessionDetails['session_password'] . '</hs>', $draw_borders, 'C');

		// host
		$pdf->SetXY(130, 188);
		$pdf->MultiCellTag(72, 6, '<hs>' . $sessionDetails['session_host_firstname'] . '</hs>', $draw_borders, 'C');

		//Session Time
		$timeStr = CTemplate::dateTimeFormat($sessionDetails['session_start'], VERBOSE);
		$pdf->SetXY(130, 210);
		$pdf->MultiCellTag(72, 6, '<hs>' . $timeStr . '</hs>', $draw_borders, 'C');

		// Store location
		$locStr = $sessionDetails['session_remote_location']->location_title . "\n" . $sessionDetails['session_remote_location']->address_line1 . (!empty($sessionDetails['session_remote_location']->address_line2) ? ", " . $sessionDetails['session_remote_location']->address_line2 : "") . "\n" . $sessionDetails['session_remote_location']->city . ", " . $sessionDetails['session_remote_location']->state_id . " " . $sessionDetails['session_remote_location']->postal_code . "\n" . $sessionDetails['telephone_day'];
		$pdf->SetXY(130, 232);
		$pdf->MultiCellTag(72, 4, '<hs>' . $locStr . '</hs>', $draw_borders, 'C');

		$pdf->Output();
	}

}

?>