<?php // page_admin_create_store.php
/**
 *
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CSessionReports.inc");

class page_admin_reports_goal_tracking extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
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

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->runOutput();
	}

	function runOutput()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;

		// fadmins
		$store = null;
		if ($this->currentStore)
		{
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		$SessionReport = new CSessionReports();
		$session_rows = $SessionReport->createAvailableSessionsArray($store);

		$storeTransitionHasExpired = false;

		if (isset($_REQUEST['print']) && $_REQUEST['print'] == true)
		{
			unset($_GET['export']);
			$tpl->assign('print_view', true);
		}
		else
		{
			$tpl->assign('print_view', false);
		}

		$showPlatePointsOnReport = false;
		if (CStore::storeSupportsPlatePoints($store))
		{
			$showPlatePointsOnReport = true;

			$storeTransitionHasExpired = CStore::hasPlatePointsTransitionPeriodExpired($store);
		}

		$tpl->assign('storeSupportsPlatePoints', $showPlatePointsOnReport);

		// Daily aggregate output
		if (isset($_GET['multi_session']))
		{
			$dateValues = explode("-", $_GET['multi_session']);
			$multi_session = $SessionReport->createSessionTabsArray($store, $dateValues[2], $dateValues[1], $dateValues[0]);
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::default_value => 0,
			CForm::options => $session_rows,
			CForm::name => 'sessionpopup'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Download Report'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'print',
			CForm::value => 'Print Report'
		));

		if ((isset($_REQUEST["report_submit"]) && $_REQUEST["report_submit"] == true) || (isset($_REQUEST["print"]) && $_REQUEST["print"] == true))
		{
			$storeObj = new DAO();
			$storeObj->query('select supports_membership from store where id = ' . $store);
			$storeObj->fetch();
			$supports_membership = $storeObj->supports_membership;
			$tpl->assign('supports_membership', $supports_membership);

			$session_id = array();
			if (isset($multi_session))
			{
				foreach ($multi_session as $m_session)
				{
					$session_id[] = $m_session['id'];
				}
			}
			else if (isset($_REQUEST['session_id']) && is_numeric($_REQUEST['session_id']))
			{
				$session_id[] = $_REQUEST['session_id'];
			}
			else
			{
				$session_id[] = $Form->value('sessionpopup');
			}

			$rows = array();
			$rowCount = 0;
			$sessionGoalSheetArray = array();

			foreach ($session_id as $session_id)
			{
				$Sessions = DAO_CFactory::create('booking');
				$q = "(SELECT
					b.booking_type,
					b.id AS booking_id,
					b.order_id,
					s.session_start,
					s.id AS session_id,
					s.session_type,
					s.store_id,
					u.id AS user_id,
					o.grand_total,
					u.dream_reward_status,
					u.dream_rewards_version,
					u.has_opted_out_of_plate_points,
					u.telephone_1,
					o.is_sampler,
					o.in_store_order,
					o.dream_rewards_level AS orders_dream_rewards_level,
					o.timestamp_created AS order_date,
					o.is_in_plate_points_program,
					o.order_user_notes,
					o.order_admin_notes,
					o.servings_total_count,
					u.firstname,
					u.lastname,
					u.secondary_email,
					Sum(sc.amount) AS referral_store_credit,
					u.dream_reward_level,
					o.menu_items_total_count,
					o.menu_items_core_total_count,
					GROUP_CONCAT(ud.user_data_field_id SEPARATOR '||') AS user_data_field_ids,
					GROUP_CONCAT(ud.user_data_value SEPARATOR '||') AS user_data_values
					FROM
					`session` AS s
					INNER JOIN store AS st ON s.store_id = st.id AND st.is_deleted = '0'
					INNER JOIN booking AS b ON b.is_deleted = '0' AND s.id = b.session_id
					INNER JOIN orders AS o ON b.order_id = o.id AND o.is_deleted = '0'
					INNER JOIN `user` AS u ON b.user_id = u.id AND b.`status` = 'ACTIVE' AND b.is_deleted = '0'
					LEFT JOIN store_credit AS sc ON sc.user_id = u.id AND sc.store_id = s.store_id AND (sc.credit_type = '2' OR sc.credit_type = '3') AND sc.is_expired = '0' AND sc.is_redeemed = '0' AND sc.is_deleted = '0'
					LEFT JOIN user_data AS ud ON ud.user_id = u.id AND ud.is_deleted = '0'
					WHERE s.id = '" . $session_id . "'
					GROUP BY booking_id
					ORDER BY u.lastname ASC)
				UNION
					(SELECT
					'RSVP',
					CONCAT('rsvp_', sr.id) AS booking_id,
					0,
					s2.session_start,
					s2.id AS session_id,
					s2.session_type,
					s2.store_id,
					u2.id AS user_id,
					0,
					u2.dream_reward_status,
					u2.dream_rewards_version,
					u2.has_opted_out_of_plate_points,
					u2.telephone_1,
					0,
					0,
					0 AS orders_dream_rewards_level,
					sr.timestamp_created AS order_date,
					0,
					0,
					'',
					0,
					u2.firstname,
					u2.lastname,
					u2.secondary_email,
					0,
					u2.dream_reward_level,
					0,
					0,
					'' AS user_data_field_ids,
					'' AS user_data_values
					FROM
					`session` AS s2
					INNER JOIN session_rsvp AS sr ON sr.session_id = s2.id AND sr.upgrade_booking_id IS NULL AND sr.is_deleted = '0'
					INNER JOIN `user` AS u2 ON sr.user_id = u2.id
					WHERE s2.id =  '" . $session_id . "'
					ORDER BY u2.lastname ASC)
					ORDER BY lastname";

				$Sessions->query($q);

				$userarray = array();
				$sessionGuestDetails = array();
				$counter = 0;

				$defaultSessionInfo = $SessionReport->findSessionInfo($session_id);

				if($defaultSessionInfo[0]['session_type_subtype'] == CSession::WALK_IN){
					continue;
				}

				while ($Sessions->fetch())
				{
					// add our first row
					if ($counter == 0)
					{
						$SessionDetails = $SessionReport->findSessionDetails($session_id);

						$sessionGoalSheetArray[$session_id] = array();
						$sessionGoalSheetArray[$session_id]['session_start'] = $SessionDetails['0']['session_start'];
						$sessionGoalSheetArray[$session_id]['session_type_subtype'] = $SessionDetails['0']['session_type_subtype'];

						$rows[] = array(
							"",
							"Session:",
							CTemplate::sessionTypeDateTimeFormat($SessionDetails['0']['session_start'],$SessionDetails['0']['session_type_subtype'], VERBOSE),
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
							"",
							"",
							"",
							"",
							"",
							"",
							""
						);
						$rows[] = array(
							"",
							"Notes:"
						);

						$rows[] = array("");

						$rows[] = array(
							"#",
							"Name",
							"Telephone",
							"PP Reward Due",
							"Total Ticket",
							"Balance Due",
							"Last Visit",
							"Last Visit Type",
							"Total Visits",
							"Dream Rewards Status",
							"DR Account Level",
							"DR Order Level",
							"Referral Credit on Account",
							"Order Type",
							"Item Count",
							"In-Store Signup (Current Order)",
							"In-Store Signup",
							"Add on Sales Amount",
							"Check out By",
							"Notes",
							"Carryover Notes",
							"Special Instructions",
							"Order Notes"
						);

						$counter++;
					}

					$Sessions->bookingHistory();

					$name = $Sessions->firstname . " " . $Sessions->lastname;
					$totalTicket = $Sessions->grand_total;
					$BalanceDue = 0.0;
					$LastVisit = "Never";
					$TotalVisits = 0;
					$DreamRewardsStatus = "No";
					if ($Sessions->dream_reward_status == 1 || $Sessions->dream_reward_status == 3 || $Sessions->dream_reward_status == 5)
					{
						$DreamRewardsStatus = "Yes";
					}
					$DRAccountLevel = $Sessions->dream_reward_level;
					$DROrderLevel = $Sessions->orders_dream_rewards_level;
					$ReferralCreditonAccount = (empty($Sessions->referral_store_credit) ? 0 : $Sessions->referral_store_credit);
					if ($Sessions->is_in_plate_points_program)
					{

						$ReferralCreditonAccount += CPointsCredits::getAvailableCreditForUser($Sessions->user_id);
					}

					$ItemCount = $Sessions->menu_items_total_count;
					$CoreItemCount = $Sessions->menu_items_core_total_count;
					$OtherItemCount = $ItemCount - $CoreItemCount ;
					$InStoreSignup = "No";
					if ($Sessions->in_store_order)
					{
						$InStoreSignup = "Yes";
					}

					$currentInStoreSignup = "";
					$AddonSalesAmount = "";
					$CheckoutBy = "";
					$Notes = "";
					$consecutiveOrderStatus = null;

					$UserObj = DAO_CFactory::create('user');

					$UserObj->id = $Sessions->user_id;

					$UserObj->getUserPreferences();

					$consecutiveOrderStatus = COrders::getOrdersSequenceStatus($Sessions->order_id, $Sessions->user_id, $Sessions->session_type, $Sessions->session_start, $Sessions->servings_total_count);

					if ($showPlatePointsOnReport)
					{
						if ($DreamRewardsStatus == 'Yes')
						{
							$UserObj->dream_reward_status = $Sessions->dream_reward_status;

							if ($Sessions->dream_rewards_version < 3)
							{

								if ($storeTransitionHasExpired)
								{
									$DreamRewardsStatus = "DR " . $DROrderLevel;
									$DRAccountLevel = "";
								}
								else
								{
									$DreamRewardsStatus = "DR " . $DROrderLevel;
									$conversionData = CPointsUserHistory::getDR2ConversionData($UserObj);
									$DRAccountLevel = "Converts to <br />" . $conversionData['points_award_display_value'] . " / $" . $conversionData['credit_award_display_value'];
								}
							}
							else
							{
								$UserObj->dream_rewards_version = 3;

								$orderObj = DAO_CFactory::create('orders');
								$orderObj->id = $Sessions->order_id;
								$orderObj->find(true);

								$pp_summary = $UserObj->getPlatePointsSummary($orderObj, true);
								$DreamRewardsStatus = $pp_summary['current_level']['title'];
								if ($pp_summary['userIsOnHold'])
								{
									$DreamRewardsStatus = "On Hold (" . $DreamRewardsStatus . ")";
								}

								$DRAccountLevel = $pp_summary['lifetime_points'];


								/*
								if ($pp_summary['current_level']['level'] == 'enrolled' && !empty($pp_summary['orderBasedGiftData']))
								{
									$dueNowGiftStr = "";
									$dueLaterGiftStr = "";
									foreach ($pp_summary['orderBasedGiftData'] as $thisGift)
									{
										if ($thisGift['rewardDue'])
										{
											$dueNowGiftStr .= CPointsUserHistory::getOrderBasedGiftDisplayString($thisGift['gift_id']) . "; ";
										}
										else
										{
											$dueLaterGiftStr .= $thisGift['display_str'] . "; ";
										}
									}

									$pp_summary['gift_display_str'] = $dueNowGiftStr . " " . $dueLaterGiftStr;
									$UserObj->platePointsData['gift_display_str'] = $dueNowGiftStr . " " . $dueLaterGiftStr;
								}
								*/
								$pp_summary['gift_display_str'] = "";
								$UserObj->platePointsData['gift_display_str'] = "";
							}
						}
						else
						{
							$DreamRewardsStatus = "";
							$DRAccountLevel = "0";
						}

						if ($Sessions->has_opted_out_of_plate_points)
						{
							$DRAccountLevel = 'Opted Out';
						}
					}

					if (!empty($Sessions->secondary_email))
					{
						$CorpClientData = CCorporateCrateClient::corporateCrateClientDetails($Sessions->secondary_email);
					}
					else
					{
						$CorpClientData = false;
					}

					$guestnote = DAO_CFactory::create('user_data');
					$guestnote->user_data_field_id = GUEST_CARRY_OVER_NOTE;
					$guestnote->user_id = $UserObj->id;
					$guestnote->store_id = CBrowserSession::getCurrentFadminStore();
					$guestnote->find(true);

					if (!$Sessions->first_standard)
					{
						// no first standard session so check if this is it
						if ($Sessions->booking_type == STANDARD && ($Sessions->session_type == CSession::STANDARD || $Sessions->session_type == CSession::SPECIAL_EVENT))
						{
							$Sessions->first_standard = $Sessions->session_id;
						}
					}

					$membershipData = null;
					if ($supports_membership)
					{
						$membershipData = $UserObj->getMembershipStatus($Sessions->order_id, true);
					}


					$isBirthdayMonth = $this->isBirthdayMonth($Sessions);
					$all_expiring_credits = CPointsCredits::getAllExpiringCredit($UserObj->id);

					$sum_all_expiring_credits = 0;
					foreach ($all_expiring_credits as $creditIndex => $credit){
						$sum_all_expiring_credits +=  $credit->dollar_value;
					}


					$sessionGuestDetails[$Sessions->booking_id] = array(
						'counter' => $counter,
						'name' => $name,
						'corporate_client_data' => $CorpClientData,
						'telephone_1' => $Sessions->telephone_1,
						'gift_display_str' => (($UserObj->platePointsData['due_reward_for_current_level'] && $UserObj->dream_rewards_version == 3) ? $UserObj->platePointsData['gift_display_str'] : ''),
						'totalTicket' => ((!empty($totalTicket)) ? $totalTicket : 0),
						'BalanceDue' => $BalanceDue,
						'LastVisitType' => ((!empty($Sessions->last_session_type)) ? $Sessions->last_session_type : '-'),
						'LastVisitTypeSubType' => ((!empty($Sessions->last_session_type_subtype)) ? $Sessions->last_session_type_subtype : '-'),
						'LastBookingType' => ((!empty($Sessions->last_booking_type)) ? $Sessions->last_booking_type : '-'),
						'LastVisit' => $LastVisit,
						'TotalVisits' => $TotalVisits,
						'DreamRewardsStatus' => $DreamRewardsStatus,
						'DRAccountLevel' => $DRAccountLevel,
						'DROrderLevel' => $DROrderLevel,
						'ReferralCreditonAccount' => $ReferralCreditonAccount,
						'BookingType' => $Sessions->booking_type,
						'SessionType' => $Sessions->session_type,
						'ItemCount' => $ItemCount,
						'CoreItemCount' => $CoreItemCount,
						'OtherItemCount' => $OtherItemCount,
						'InStoreSignup' => $InStoreSignup,
						'currentInStoreSignup' => $currentInStoreSignup,
						'AddonSalesAmount' => $AddonSalesAmount,
						'CheckoutBy' => $CheckoutBy,
						'Notes' => $Notes,
						'guest_carryover_notes' => $guestnote->user_data_value,
						'order_user_notes' => $Sessions->order_user_notes,
						'order_admin_notes' => $Sessions->order_admin_notes,
						'first_standard' => ($Sessions->first_standard == $session_id),
						'consecutive_order_status' => $consecutiveOrderStatus,
						'membership_status' => $membershipData,
						'is_birthday_month' => $isBirthdayMonth,
						'all_expiring_credits' => $all_expiring_credits,
						'sum_all_expiring_credits' => $sum_all_expiring_credits
					);

					if (empty($_GET['export']))
					{
						$sessionGuestDetails[$Sessions->booking_id]['due_reward_for_current_level'] = $UserObj->platePointsData['due_reward_for_current_level'];
						$sessionGuestDetails[$Sessions->booking_id]['UserPreferences'] = $UserObj->preferences;
					}

					$userarray[$Sessions->user_id] = array(
						"customer_id" => $Sessions->user_id,
						"booking_id" => $Sessions->booking_id
					);
					$counter++;
				}

				if (count($userarray) > 0)
				{
					$payment_array = $SessionReport->getPaymentArrays($session_id);
					$payment_failed_balance_due_array = $SessionReport->isBalanceDueOrPaymentsFailed($payment_array);
					$history_list = $SessionReport->getCustomerHistory($userarray, $Sessions->session_id, $store);

					if (!empty($history_list))
					{
						foreach ($history_list as $id => $element)
						{
							if (!empty($userarray[$id]))
							{
								$sessionGuestDetails[$userarray[$id]["booking_id"]]['LastVisit'] = CSessionReports::newDayTimeFormat($element["last_session_attended"]);
								$sessionGuestDetails[$userarray[$id]["booking_id"]]['TotalVisits'] = $element["bookings_made"];
							}
						}
					}

					foreach ($payment_failed_balance_due_array as $id => $element)
					{
						$balanceDue = 0;
						foreach ($element as $subkeyid => $subelement)
						{
							if (!empty($subelement["balance_due"]))
							{
								$balanceDue += $subelement["balance_due"];
							}
						}
						if ($balanceDue != 0)
						{
							$sessionGuestDetails[$userarray[$id]["booking_id"]]['BalanceDue'] = $balanceDue;
						}
					}
				}

				$sessionGoalSheetArray[$session_id]['guests'] = $sessionGuestDetails;
				$sessionGoalSheetArray[$session_id]['session_start'] = $defaultSessionInfo['0']['session_start'];
				$sessionGoalSheetArray[$session_id]['session_type_subtype'] = $defaultSessionInfo['0']['session_type_subtype'];

				$rows = $rows + $sessionGuestDetails;

				// Pad next section
				if (isset($multi_session))
				{
					$rows[] = array("");
					$rows[] = array("");
				}

				if ($rowCount < count($rows))
				{
					$rowCount = count($rows);
				}
			}

			if (!empty($_GET['export']))
			{
				$exportSetting = ($_GET['export'] ? "Excel" : "Screen");
				CLog::RecordReport("Session Goal Sheet ", "Rows:$rowCount ~ Store: $store ~ Session: {$sessionGoalSheetArray[$session_id]['session_start']} ~ Dest: $exportSetting");
			}

			if ($rowCount > 0)
			{
				$tpl->assign('rowcount', $rowCount);
				$tpl->assign('rows', $rows);
				$tpl->assign('goal_sheet_array', $sessionGoalSheetArray);
			}
			else
			{
				$tpl->setErrorMsg("Sorry, there isn't any session data for your query.");
				unset($_GET['export']);
			}
		}
		else
		{
			unset($_GET['export']);
		}

		$formArray = $Form->render();

		$tpl->assign('filename', "SessionGoalSheet");
		$tpl->assign('form_session_list', $formArray);
	}


	function isBirthdayMonth($Sessions){
		$isBirthdayMonth = false;
		if (!empty($Sessions->user_data_field_ids))
		{
			$ud_ids = explode('||', $Sessions->user_data_field_ids);
			$ud_values = explode('||', $Sessions->user_data_values);
			foreach ($ud_ids AS $id => $ud_id)
			{
				if (!empty($ud_values[$id]))
				{
					if ($ud_id == 1)
					{

						if (is_numeric( $ud_values[$id]))
						{
							if ($ud_values[$id] == date("n", strtotime($Sessions->session_start)))
							{
								$isBirthdayMonth = true;
							}
						}
						else
						{
							if ($ud_values[$id] == date("F", strtotime($Sessions->session_start)))
							{
								$isBirthdayMonth = true;
							}
						}
					}

				}
			}
		}
		return $isBirthdayMonth;
	}
}

?>