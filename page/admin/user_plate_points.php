<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('fpdf/class_multicelltag.php');
require_once('includes/class.inputfilter_clean.php');

class page_admin_user_plate_points extends CPageAdminOnly
{

	function runManufacturerStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	static function generateEnrollmentFormPDF($pdf, $user_id_or_obj = false)
	{
		$generateBlank = true;
		$conversionData = false;

		if ($user_id_or_obj)
		{

			if (is_object($user_id_or_obj) && get_class($user_id_or_obj) == 'CUser')
			{
				$userObj = $user_id_or_obj;
			}
			else if (is_numeric($user_id_or_obj) && $user_id_or_obj == CUser::getCurrentUser()->id)
			{
				$userObj = CUser::getCurrentUser();
			}
			else
			{
				$userObj = DAO_CFactory::create('user');
				$userObj->id = $user_id_or_obj;
				if (!$userObj->find(true))
				{
					throw new Exception('user not found in CPointsUserHistory::handleEvent.');
				}
			}

			$address = $userObj->getPrimaryAddress();

			$Store = CBrowserSession::getCurrentFadminStore();

			$profile_data = CUserData::getSFIDataForDisplayNew($userObj->id, false, $Store);

			$arSources = array();

			$UserRefSource = DAO_CFactory::create('user_referral_source');
			$UserRefSource->user_id = $userObj->id;
			$UserRefSource->find();

			while ($UserRefSource->fetch())
			{
				$arSources[$UserRefSource->source] = $UserRefSource->meta;
			}
			$generateBlank = false;

			if ($userObj->isUserPreferred())
			{
				$conversionData = CPointsUserHistory::getPreferredUserConversionData($userObj);
			}
			else if (($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3) && $userObj->dream_rewards_version < 3)
			{
				$conversionData = CPointsUserHistory::getDR2ConversionData($userObj);
			}
		}



		$pdf->SetAutoPageBreak(false);

		$pdf->SetStyle("hs", "helvetica", "", 11, "0,0,0");
		$pdf->SetStyle("hsb", "helvetica", "B", 11, "0,0,0");
		$pdf->SetStyle("hsi", "helvetica", "BI", 10, "0,0,0");

		$pdf->SetLineWidth(.2);

		$leftMargin = 25.4;
		$topMargin = 16;

		$bg_image_path = APP_BASE . "www/theme/" . THEME . '/images/admin/style/platepoints/platepoints-status-bar-background-no_transparency.png';

		$pdf->AddPage();
		$pdf->Image($bg_image_path, $leftMargin, $topMargin, 77.19, 20.88);
		//$pdf->SetFont('helvetica','B',11);

		$draw_borders = 0;

		$pdf->SetXY($leftMargin, $topMargin + 23);
		$pdf->MultiCellTag(200, 6, '<hsb>Enroll in PLATEPOINTS today to start getting perks!</hsb>', $draw_borders, 'L');

		$addressStartY = $topMargin + 30;
		$addressLineHeight = 7;

		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Name:</hs>', $draw_borders, 'L');

		if (!$generateBlank && !empty($userObj->firstname))
		{
			$pdf->SetXY($leftMargin + 14, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $userObj->firstname . " " . $userObj->lastname . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 200, $addressStartY + 4);
		}

		$addressStartY += $addressLineHeight;
		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Address:</hs>', $draw_borders, 'L');

		if (!$generateBlank && !empty($address->address_line1))
		{
			$addressStr = $address->address_line1 . (!empty($address->address_line2) ? ", " . $address->address_line2 : "");
			$pdf->SetXY($leftMargin + 18, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $addressStr . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 200, $addressStartY + 4);
		}

		$addressStartY += $addressLineHeight;
		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>City, State, Zip:</hs>', $draw_borders, 'L');

		if (!$generateBlank && !empty($address->city))
		{
			$addressStr = $address->city . " " . $address->state_id . ", " . $address->postal_code;
			$pdf->SetXY($leftMargin + 32, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $addressStr . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 200, $addressStartY + 4);
		}

		$addressStartY += $addressLineHeight;
		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Phone Number:</hs>', $draw_borders, 'L');

		if (!$generateBlank && (!empty($userObj->telephone_1) || !empty($userObj->telephone_1)))
		{

			$phoneTypeMap = array(
				"LAND_LINE" => 'land line',
				"MOBILE" => "cell"
			);
			$callTimeMap = array(
				"NEVER" => "never",
				"ALWAYS" => "anytime",
				"MORNING" => "am",
				"AFTERNOON" => "pm",
				"EVENING" => "pm"
			);

			$phoneStr1 = $userObj->telephone_1 . ": " . $phoneTypeMap[$userObj->telephone_1_type] . " (" . $callTimeMap[$userObj->telephone_1_call_time] . ")";

			if (!empty($userObj->telephone_2))
			{

				$callTime2 = (!empty($userObj->telephone_2_call_time) ? "(" . $callTimeMap[$userObj->telephone_2_call_time] . ")" : "");
				$phoneStr1 .= "   " . $userObj->telephone_2 . ": " . $phoneTypeMap[$userObj->telephone_2_type] . $callTime2;
			}

			$pdf->SetXY($leftMargin + 32, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $phoneStr1 . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 200, $addressStartY + 4);
		}

		$addressStartY += $addressLineHeight;
		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Email:</hs>', $draw_borders, 'L');

		if (!$generateBlank && !empty($userObj->primary_email))
		{
			$pdf->SetXY($leftMargin + 14, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $userObj->primary_email . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 200, $addressStartY + 4);
		}

		$addressStartY += $addressLineHeight;
		$pdf->SetXY($leftMargin, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Birthday Month:</hs>', $draw_borders, 'L');

		$pdf->SetXY($leftMargin + 60, $addressStartY);
		$pdf->MultiCellTag(200, 6, '<hs>Year:</hs>', $draw_borders, 'L');

		if (!$generateBlank && !empty($profile_data[BIRTH_MONTH_FIELD_ID]))
		{
			$pdf->SetXY($leftMargin + 30, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $profile_data[BIRTH_MONTH_FIELD_ID] . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(57, $addressStartY + 4, 84, $addressStartY + 4);
		}

		if (!$generateBlank && !empty($profile_data[BIRTH_YEAR_FIELD_ID]) && $profile_data[BIRTH_YEAR_FIELD_ID] != "none")
		{
			$pdf->SetXY($leftMargin + 72, $addressStartY);
			$pdf->MultiCellTag(200, 6, '<hsi>' . $profile_data[BIRTH_YEAR_FIELD_ID] . '</hsi>', $draw_borders, 'L');
		}
		else
		{
			$pdf->Line(97, $addressStartY + 4, 114, $addressStartY + 4);
		}

	}

	function print_days_forms()
	{
		$count = 0;

		if (isset($_REQUEST['report_date']))
		{
			$dateValues = explode("-", CGPC::do_clean($_REQUEST['report_date'],TYPE_STR));
		}
		else
		{
			return 0;
		}

		$store_id = (isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : false);

		if (empty($store_id) || !is_numeric($store_id))
		{
			throw new Exception('Invalid store id in user_plate_points::print_days_forms.');
		}

		$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
			215.9,
			279.4
		));
		CLog::RecordReport("Enrollment Forms per Day", "Date:{$_REQUEST['report_date']}");

		$Session = DAO_CFactory::create("session");
		$Session->findDailySessions($store_id, $dateValues[2], $dateValues[1], $dateValues[0]);
		while ($Session->fetch())
		{
			$count += $this->print_sessions_forms($pdf, $Session->id, false);
		}

		if ($count == 0)
		{
			$pdf->AddPage();
			$pdf->SetStyle("hsi", "helvetica", "BI", 10, "0,0,0");
			$pdf->SetXY(0, 25.4);
			$pdf->MultiCellTag(215.9, 6, '<hsi>There are no eligible guests for this day.</hsi>', false, 'C');
		}

		$pdf->Output();
	}

	function print_sessions_forms($pdf, $session_id, $doOutput = true)
	{
		$count = 0;

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("select u.id as non_pp_user_id, u.dream_reward_status, u.dream_rewards_version from booking b
							join user u on u.id = b.user_id
							where b.session_id = $session_id and b.status = 'ACTIVE' and u.is_deleted = 0 and b.is_deleted = 0 and (dream_rewards_version < 3 or (dream_rewards_version = 3 && dream_reward_status = 2)) ");
		if (!$pdf)
		{
			$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
				215.9,
				279.4
			));
		}

		while ($bookingObj->fetch())
		{
			self::generateEnrollmentFormPDF($pdf, $bookingObj->non_pp_user_id);

			$count++;
		}

		if ($doOutput && $count > 0)
		{
			$pdf->Output();
		}

		return $count;
	}

	function runFranchiseOwner()
	{
		$tpl = CApp::instance()->template();
		$currentStore = CBrowserSession::getCurrentFadminStore();

		if (isset($_REQUEST['print_days_forms']))
		{
			$this->print_days_forms();
			exit;
		}

		if (isset($_REQUEST['print_blank_form']))
		{

			$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
				215.9,
				279.4
			));
			self::generateEnrollmentFormPDF($pdf, false);
			CLog::RecordReport("Enrollment Form Blank", "");
			$pdf->output();

			exit;
		}

		if (isset($_REQUEST['print_sessions_forms']))
		{
			$session_id = (isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : false);

			if (empty($session_id) || !is_numeric($session_id))
			{
				throw new Exception('Invalid session id in user_plate_points::print_sessions_forms.');
			}

			$count = $this->print_sessions_forms(false, $session_id);
			CLog::RecordReport("Enrollment Forms per Session", "Session:$session_id");

			if ($count == 0)
			{
				$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
					215.9,
					279.4
				));
				$pdf->AddPage();
				$pdf->SetStyle("hsi", "helvetica", "BI", 10, "0,0,0");
				$pdf->SetXY(0, 25.4);
				$pdf->MultiCellTag(215.9, 6, '<hsi>There are no eligible guests for this session.</hsi>', false, 'C');
				$pdf->Output();
			}

			exit;
		}

		$userID = (isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : false);

		if (empty($userID) || !is_numeric($userID))
		{
			$userID = (isset($_REQUEST['id']) ? $_REQUEST['id'] : false);

			if (empty($userID) || !is_numeric($userID))
			{
				throw new Exception('Invalid user id in user_plate_points.');
			}
		}

		$userObj = DAO_CFactory::create('user');
		$userObj->id = $userID;

		$userObj->getMembershipStatus();


		if (!$userObj->find(true))
		{
			throw new Exception('User not found in user_plate_points.');
		}

		if (isset($_REQUEST['print_enrollment_form']))
		{
			$pdf = new FPDF_MULTICELLTAG('P', 'mm', array(
				215.9,
				279.4
			));
			CLog::RecordReport("Enrollment Form per User", "");

			self::generateEnrollmentFormPDF($pdf, $userObj);

			$pdf->Output();

			exit;
		}

		if (isset($_POST['add_points']))
		{
			$error = false;
			$points = CGPC::do_clean($_POST['points_amount'],TYPE_NUM);
			if (empty($points) || !is_numeric($points) || $points > 20000)
			{
				$tpl->setErrorMsg('Please supply a valid numnber of points ( 1 to 20000 ).');
				$error = true;
			}

			$points = floor($points);

			if (!$error)
			{
				$xssFilter = new InputFilter();

				$comments = stripslashes(CGPC::do_clean((!empty($_REQUEST['comments']) ? $_REQUEST['comments'] : false), TYPE_STR, $xssFilter));
				$admin_comments = stripslashes(CGPC::do_clean((!empty($_REQUEST['admin_comments']) ? $_REQUEST['admin_comments'] : false), TYPE_STR, $xssFilter));

				$metaArray = array(
					'comments' => $comments,
					'debug_points' => $points
				);

				if (!empty($admin_comments))
				{
					$metaArray['admin_comments'] = $admin_comments;
				}

				if (isset($_POST['suppress_DD_reward']))
				{
					$metaArray['no_credit'] = true;
				}

				try
				{
					list($eventMetaData, $platePointsStatus) = CPointsUserHistory::handleEvent($userObj, CPointsUserHistory::OTHER, $metaArray);
					$results = CPointsUserHistory::getLastOperationResult();

					if (empty($results))
					{
						$tpl->setStatusMsg("$points successfully added.");
					}
					else
					{
						$tpl->setErrorMsg('An unexpected error occurred.');
					}
				}
				catch (Exception $e)
				{
					$tpl->setErrorMsg('An unexpected error occurred' . $e->getMessage());
				}
			}
		}

		if (isset($_POST['convert_points']))
		{
			CPointsUserHistory::convertUnconvertedPoints($userObj);
			$tpl->setStatusMsg("Any unconverted points up to the maximum multiple of 500 has been converted to credit.");
		}

		if (isset($_POST['join_plate_points']))
		{
			$clearToEnroll = true;

			if ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3)
			{
				$clearToEnroll = false;
				if ($userObj->dream_rewards_version == 3)
				{
					$tpl->setErrorMsg('User is already enrolled in PLATEPOINTS');
				}
				else
				{
					$tpl->setErrorMsg('User is enrolled in Dream Rewards version 2. Preparing for conversion.');
				}
			}

			if ($clearToEnroll)
			{
				CPointsUserHistory::handleEvent($userObj->id, CPointsUserHistory::OPT_IN);
				$result = CPointsUserHistory::getLastOperationResultAsHTML();

				$tpl->setStatusMsg($result);
			}
		}

		if (isset($_POST['action']) && $_POST['action'] == 'suspend_member')
		{

			if ($userObj->dream_reward_status != 1 && $userObj->dream_reward_status != 3)
			{
				$tpl->setErrorMsg('User is not enrolled in PLATEPOINTS');
			}
			else
			{

				$confirmResult = $this->confirmAnyConfirmableOrders($userObj);
				CPointsUserHistory::clearLastOperationResult();
				CPointsUserHistory::handleEvent($userObj, CPointsUserHistory::SUSPEND_MEMBERSHIP);
				$result = CPointsUserHistory::getLastOperationResultAsHTML();

				CEmail::platePointsOnHoldSuspend($userObj);

				$tpl->setStatusMsg($result . '<br />' . $confirmResult);
			}
		}

		if (isset($_POST['action']) && $_POST['action'] == 'reactivate_member')
		{
			if (false)
			{
				$tpl->setErrorMsg('User is already enrolled in Meal Prep+');
			}

			if ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3)
			{
				$tpl->setErrorMsg('User is already enrolled in PLATEPOINTS');
			}
			else
			{
				CPointsUserHistory::handleEvent($userObj, CPointsUserHistory::REACTIVATE_MEMBERSHIP);
				$result = CPointsUserHistory::getLastOperationResultAsHTML();

				CEmail::platePointsOnHoldReactivate($userObj);

				$tpl->setStatusMsg($result);
			}
		}

		$userObj->getPlatePointsSummary(false, true);

		$tpl->assign('userObj', $userObj);

		$data = CPointsUserHistory::getHistory($userID,'0,15');
		$shouldPage = count($data) > 14;
		$tpl->assign('rows', $data);
		$tpl->assign('pagination', $shouldPage);
		$tpl->assign('pagination_prev', false);
		$tpl->assign('pagination_next', true);
		$tpl->assign('page_cur', 0);
		$tpl->assign('user_id', $userObj->id);

		$infoArr = array('points_avail' => 0, 'dollars_added' => 0, 'unconverted_points'=>0,'total_points' =>0,'pending_points' =>0);
		$eventRecord = DAO_CFactory::create('points_user_history');
		$eventRecord->convertPointsToCredit($userObj, true, $infoArr);
		$infoArr['total_points'] = CPointsUserHistory::getCurrentPointsLevel($userID);

		$infoArr['pending_points'] = CPointsUserHistory::getPendingPoints($userID, $infoArr['total_points']);
		$tpl->assign('plate_point_summary', $infoArr);




		$totalCreditValue = 0;
		$ddrows = CStoreCredit::fetchDinnerDollarHistoryByUser($userObj->id, 'rowcount','0,15',$totalCreditValue);
		$tpl->assign('totalCredit', $totalCreditValue);
		$tpl->assign('dd_history', $ddrows);

		$ddShouldPage = count($ddrows) > 14;
		$tpl->assign('dinner_dollar_pagination', $ddShouldPage);
		$tpl->assign('dinner_dollar_pagination_prev', false);
		$tpl->assign('dinner_dollar_pagination_next', true);
		$tpl->assign('dinner_dollar_page_cur', 0);



		if (!$userObj->platePointsData['userIsOnHold'] && (DD_SERVER_NAME != 'LIVE' || (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER)))
		{
			$Form = new CForm();

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "points_amount",
				CForm::required => true,
				CForm::length => 6
			));

			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::name => "comments",
				CForm::required => true,
				CForm::maxlength => 256,
				CForm::rows => '3',
				CForm::cols => '96',
			));

			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::name => "admin_comments",
				CForm::maxlength => 256,
				CForm::rows => '3',
				CForm::cols => '96',
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => "suppress_DD_reward"
			));

			$Form->AddElement(array(
				CForm::type => CForm::Submit,
				CForm::name => "add_points",
				CForm::css_class => "btn btn-primary btn-sm",
				CForm::value => "Add Points"
			));

			$ConvertForm = new CForm();
			$ConvertForm->AddElement(array(
				CForm::type => CForm::Submit,
				CForm::name => "convert_points",
				CForm::css_class => "btn btn-primary btn-sm",
				CForm::value => "Convert Points"
			));

			$tpl->assign('points_form', $Form->Render());
			$tpl->assign('convert_form', $ConvertForm->Render());
		}

		$SuspendForm = new CForm();

		$tpl->assign('numConfirmableOrders', 0);

		if ($userObj->platePointsData['status'] == 'active')
		{
			$numConfirmableOrders = $this->confirmAnyConfirmableOrders($userObj, true);

			$tpl->assign('numConfirmableOrders', $numConfirmableOrders);

			$SuspendForm->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => "suspend_member",
				CForm::css_class => "btn btn-primary btn-sm",
				CForm::value => "Place Status on Hold"
			));
		}
		else if ($userObj->platePointsData['userIsOnHold'])
		{
			$SuspendForm->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => "reactivate_member",
				CForm::css_class => "btn btn-primary btn-sm",
				CForm::value => "Reinstate"
			));

			$tpl->assign('numConfirmableOrders', 0);
		}

		$tpl->assign('suspend_form', $SuspendForm->Render());
	}

	function confirmAnyConfirmableOrders($userObj, $countOnly = false)
	{
		$Orders = DAO_CFactory::create('customer_referral');
		$returnVal = false;

		$now = date("Y-m-d H:i:s");
		if (!empty($userObj->home_store_id))
		{
			$now = CTimezones::localizeAndFormatTimeStamp($now, $userObj->home_store_id, MYSQL);
		}

		$Orders->query("select b.order_id as order_id, s.session_start, s.id as session_id from booking b
			join user u on u.id = b.user_id and u.dream_rewards_version = 3 and (u.dream_reward_status = 1 or u.dream_reward_status = 3) and u.is_deleted = 0
			join session s on s.id = b.session_id and s.session_start <= '$now' and DATEDIFF('$now',s.session_start) < 45
			join orders o on o.id = b.order_id and o.is_in_plate_points_program = 1 and o.is_deleted = 0 and o.points_are_actualized = 0
			where b.`status` = 'ACTIVE' and b.no_show = 0 and b.user_id = {$userObj->id}");

		if ($countOnly)
		{
			return $Orders->N;
		}
		else if ($Orders->N > 0)
		{
			$returnVal = "";
		}

		while ($Orders->fetch())
		{

			$orderObj = DAO_CFactory::create('orders');
			$orderObj->id = $Orders->order_id;
			$orderObj->find(true);

			$oldOrder = clone($orderObj);
			$orderObj->points_are_actualized = 1;
			$orderObj->update($oldOrder);

			list($results, $platePointsStatus) = CPointsUserHistory::handleEvent($orderObj->user_id, CPointsUserHistory::ORDER_CONFIRMED, false, $orderObj);

			if (isset($results))
			{
				if ($results[0]['success'])
				{
					$returnVal .= "Order# " . $orderObj->id . " was successfully confirmed. ";
				}
			}
		}

		return $returnVal;
	}
}