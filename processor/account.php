<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CSalesforceLink.inc");
require_once("includes/CSFMCLink.inc");
require_once("DAO/BusinessObject/CTaskRetryQueue.php");

class processor_account extends CPageProcessor
{
	private $can = array(
		'can_post_user_id' => true,
	);

	function runPublic()
	{
		echo json_encode(array(
			'processor_success' => false,
			'processor_message' => 'Not logged in.',
		));
	}

	function runCustomer()
	{
		$this->can['can_post_user_id'] = false;

		$this->runAccount();
	}

	function runFranchiseStaff()
	{
		$this->runAccount();
	}

	function runFranchiseLead()
	{
		$this->runAccount();
	}

	function runFranchiseManager()
	{
		$this->runAccount();
	}

	function runOpsLead()
	{
		$this->runAccount();
	}

	function runEventCoordinator()
	{
		$this->runAccount();
	}

	function runHomeOfficeManager()
	{
		$this->runAccount();
	}

	function runFranchiseOwner()
	{
		$this->runAccount();
	}

	function runSiteAdmin()
	{
		$this->runAccount();
	}

	function duplicateSMSNumberExists($User, $normalizedTargetNumber)
	{
		$pkey = CUser::TEXT_MESSAGE_TARGET_NUMBER;
		$prefDAO = new DAO();
		$prefDAO->query("select id from user_preferences where user_id <> {$User->id} and pkey = $pkey and pvalue = '$normalizedTargetNumber' and is_deleted = 0");

		if ($prefDAO->N > 0)
		{
			return true;
		}

		return false;
	}

	function setAllSMSPreferences($User, $targetNumber)
	{

		$SMSPrefs = CUser::$SMSPrefsDefaults;
		$SMSNewValuesInternal = array();
		$SMSNewValuesExternal = array();
		$newPrefs = array();
		foreach ($SMSPrefs as $key => $value)
		{

			if ($key != CUser::TEXT_MESSAGE_TARGET_NUMBER)
			{
				$keyLC = strtolower($key);
				if (isset($_POST['selectedCategories'][$keyLC]))
				{
					$SMSNewValuesInternal[$key] = CUser::OPTED_IN;
					$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = false;
					$newPrefs[$keyLC] = 'checked';
				}
				else
				{

					$SMSNewValuesInternal[$key] = CUser::OPTED_OUT;
					$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = true;
					$newPrefs[$keyLC] = false;
				}
			}
			else
			{
				$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = $targetNumber;
			}
		}

		// Set Internally - pretty much guaranteed to succeed
		foreach ($SMSNewValuesInternal as $key => $value)
		{
			$User->setUserPreference($key, $value);
		}

		// then attempt to update salesforce
		// if something goes wrong here queue a retry attempt
		$salesForce = new CSalesforceLink();
		$result = $salesForce->setPrefArrayInSalesforce($User->id, $SMSNewValuesExternal);

		if (!empty($result["error_occurred"]))
		{
			return array(
				'salesforce_result' => false,
				'new_prefs' => $newPrefs
			);
		}

		return array(
			'salesforce_result' => true,
			'new_prefs' => $newPrefs
		);
	}

	function getMobileNumberSFMCStatus($normalizedNumber)
	{
		$SalesforceStatus = 'unanswered';
		// we should have a number to test against SFMC
		$salesForce = new CSFMCLink();        // get salesforce versions
		$prefs = $salesForce->getSMSSubscriptionStatus(CAppUtil::normalizePhoneNumber($normalizedNumber));

		if (!empty($prefs['error_occurred']))
		{
			// most likely a brand new account with no SF object ... so ignore
			$SalesforceStatus = 'error';
		}
		else
		{
			// there should be an active keyword - fadmin has them as active so what do we do?
			$activeInSF = false;
			if (isset($prefs['count']) && $prefs['count'] > 0)
			{
				// a non-zero count indicates some history for the phone number though all keywords may be inactive
				foreach ($prefs['contacts'] as $thisContact)
				{
					if ($thisContact['status'] == "active")
					{
						$SalesforceStatus = 'active';
						break;
					}
					else if ($thisContact['status'] == "pending")
					{
						$SalesforceStatus = 'pending';
						// don't break because there may be an active keyword further on
						// Any active keyword wins even if a pending keyword exists
					}
				}
			}
		}

		return $SalesforceStatus;
	}

	function reconcileSMSOptinStatus($User)
	{
		if ($User)
		{
			$User->getUserPreferences();
		}

		$results = array('status' => "none");
		$FadminStatus = null;
		$SalesforceStatus = 'unanswered';
		$FadminNumberNormalized = null;
		$currentSMSPhoneSetting = $User->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];

		if (empty($currentSMSPhoneSetting))
		{
			$FadminStatus = 'none';
		}
		if ($currentSMSPhoneSetting == CUser::UNANSWERED)
		{
			$FadminStatus = 'unanswered';
		}
		else if (strpos($currentSMSPhoneSetting, "PENDING ") !== false)
		{
			// 2 step in progress
			$currentSMSPhoneSetting = str_replace("PENDING ", "", $currentSMSPhoneSetting);
			$FadminNumberNormalized = CAppUtil::normalizePhoneNumber($currentSMSPhoneSetting);
			$FadminStatus = 'pending';
		}
		else
		{
			$FadminNumberNormalized = CAppUtil::normalizePhoneNumber($currentSMSPhoneSetting);
			if ($FadminNumberNormalized)
			{
				$FadminStatus = 'active';
			}
			else
			{
				$FadminStatus = 'unanswered';
			}
		}

		if ($FadminStatus == 'pending' || $FadminStatus == 'active')
		{
			$SalesforceStatus = $this->getMobileNumberSFMCStatus($FadminNumberNormalized);
		}

		// What follows is matrix of handlers for the possible sync states
		if ($FadminStatus == 'active')
		{
			if ($SalesforceStatus == 'active')
			{
				$results['status'] = 'active';
			}
			else if ($SalesforceStatus == 'pending')
			{
				// MC has us as pending but we are set to active - update us to pending
				$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, "PENDING " . CTemplate::telephoneFormat($FadminNumberNormalized));
				$results['status'] = 'pending';
			}
			else if ($SalesforceStatus == 'unanswered')
			{
				// MC has no active or pending keywords for the number we list as active - force Fadmin to opted out
				$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CUser::UNANSWERED);
				$results['status'] = 'unanswered';
			}
		}
		else if ($FadminStatus == 'pending')
		{
			if ($SalesforceStatus == 'active')
			{
				// MC has us as active but we are set to PENDING - just update us to the use the current number
				$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($FadminNumberNormalized));
				$results['status'] = 'active';
			}
			else if ($SalesforceStatus == 'pending')
			{
				$results['status'] = 'pending';
			}
			else if ($SalesforceStatus == 'unanswered')
			{
				$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CUser::UNANSWERED);
				$results['status'] = 'unanswered';
			}
		}
		else if ($FadminStatus == 'unanswered')
		{
			//Since we don't have a number we could not have queried salesforce yet
			$primaryPhone = (ValidationRules::telephoneCheck($User->telephone_1) ? CTemplate::telephoneFormat($User->telephone_1) : false);
			$secondaryPhone = (ValidationRules::telephoneCheck($User->telephone_2) ? CTemplate::telephoneFormat($User->telephone_2) : false);

			$primaryPhoneNormalized = CAppUtil::normalizePhoneNumber($primaryPhone);
			$secondaryPhoneNormalized = CAppUtil::normalizePhoneNumber($secondaryPhone);

			//try primary number
			if ($primaryPhoneNormalized)
			{
				$primaryNumberSFMCStatus = $this->getMobileNumberSFMCStatus($primaryPhoneNormalized);
				if ($primaryNumberSFMCStatus == 'active')
				{
					$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($primaryPhoneNormalized));
					$results['status'] = 'active';
				}
				else if ($primaryNumberSFMCStatus == 'pending')
				{
					$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, "PENDING " . CTemplate::telephoneFormat($primaryPhoneNormalized));
					$results['status'] = 'pending';
				}
				else
				{ // TODO: handle secondary if no primary
					if ($secondaryPhoneNormalized)
					{
						$primaryNumberSFMCStatus = $this->getMobileNumberSFMCStatus($secondaryPhoneNormalized);
						if ($primaryNumberSFMCStatus == 'active')
						{
							$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($secondaryPhoneNormalized));
							$results['status'] = 'active';
						}
						else if ($primaryNumberSFMCStatus == 'pending')
						{
							$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, "PENDING " . CTemplate::telephoneFormat($secondaryPhoneNormalized));
							$results['status'] = 'pending';
						}
						else
						{
							// neither primary or secondary are pending or active in SFMC so
							// we are truly opted out
							$results['status'] = 'unanswered';
						}
					}
				}
			}
		}

		return $results;
	}

	/*
	function reconcileSMSOptinStatus($User)
	{
		if ($User)
		{
			$User->getUserPreferences();
		}


		$results = array('status' => "none", 'org_state' => "", "new_state" => "");

		$internal_pending_2nd_Step = false;
		$external_pending_2nd_Step = false;

		$currentSMSPhoneSetting = $User->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];
		$results['org_state'] = $currentSMSPhoneSetting;
		$results['new_state'] = $currentSMSPhoneSetting;

		if (!empty($currentSMSPhoneSetting) && $currentSMSPhoneSetting != CUser::UNANSWERED)
		{
			if (strpos($currentSMSPhoneSetting, "PENDING ") !== false)
			{
				// 2 step in progress
				$currentSMSPhoneSetting = str_replace("PENDING ", "", $currentSMSPhoneSetting);
				$internal_pending_2nd_Step = true;
			}

			$salesForce = new CSFMCLink();        // get salesforce versions
			$prefs= $salesForce->getSMSSubscriptionStatus(CAppUtil::normalizePhoneNumber($currentSMSPhoneSetting));

			if (!empty($prefs['error_occurred']))
			{
				// most likely a brand new account with no SF object ... so ignore
				$results['status'] = 'error';
			}
			else
			{
				// there should be an active keyword - fadmin has them as active so what do we do?
				$activeInSF = false;
				if (isset($prefs['count']) && $prefs['count'] > 0)
				{
					// a non-zero count indicates some history for the phone number though all keywords may be inactive
					foreach ($prefs['contacts'] as $thisContact)
					{
						if ($thisContact['status'] == "active")
						{
							$activeInSF = true;
							break;
						}
						else if ($thisContact['status'] == "pending")
						{
							$external_pending_2nd_Step = true;
						}
					}
				}

				if (!$activeInSF)
				{
					if ($external_pending_2nd_Step && $internal_pending_2nd_Step)
					{
						// we are in sync and we should either type YES to the number of use our dialog to cancel the 2 step and send the single step keyword
						$results['status'] = 'pending';
					}
					else if ($external_pending_2nd_Step)
					{
						// Mobile Connect has us as pending but we are set to "UNANSWERED"
						// Either the guest was able to guess and send the keyword or the store opted them in but it wasn't recorded somehow (should be very rare)
						// TODO - Ask team
						$results['status'] = 'sf_pending';

					}
					else if ($internal_pending_2nd_Step)
					{
						// we have them as pending but MC has them opted out completely.
						// TODO - Ask Team
						$results['status'] = 'sf_inactive_dd_pending';
					}
					else
					{
						// problem: need to reconcile
						// for now alert
						CLog::RecordNew(CLog::ERROR, "We have active SMS number but none found in SFMC: $currentSMSPhoneSetting", "", "", true);
						$results['status'] = 'sf_ianctive_dd_active';
					}


				}
				else if ($internal_pending_2nd_Step)
				{
					// MC has us as active but we are set to PENDING - just update us to the use the current number
					$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($currentSMSPhoneSetting));
					$results['new_state'] = $currentSMSPhoneSetting;
					$results['status'] = 'active';
				}
				else
				{
					$results['status'] = 'active';
				}

			}
		}
		else
		{
			$results['status'] = 'inactive';

		}

		return $results;
	}
*/

	function runAccount()
	{
		if(!isset($_POST['op'])){
			$_POST['op'] = '';
		}
		if (defined('ENABLE_EMAIL_PREFERENCE') && ENABLE_EMAIL_PREFERENCE == true)
		{
			if (isset($_POST['reconcile_email_prefs']))
			{
				// Get internal prefs
				if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
				{
					$User = DAO_CFactory::create('user');
					$User->id = $_POST['user_id'];
					$User->find(true);

					$update_guest_pref = true;
				}
				else
				{
					$User = CUser::getCurrentUser();
				}

				if ($User)
				{
					$User->getUserPreferences();
				}

				// get salesforce versions
				$salesForce = new CSalesforceLink();
				$prefs = $salesForce->retrieveEmailPreferences($User->id);

				// if no account in salesforce we can ignore that- the preferences will be updated by the nightly build
				if (empty($prefs) || !empty($prefs['error_occurred']))
				{
					if (!empty($prefs['status']) && $prefs['status'] == 404)
					{
						// account is not in salesforce yet - todo?
					}
				}
				else
				{

					foreach ($prefs as $prefName => $prefVal)
					{
						if (isset(CUser::$SalesforceToInternalPrefNameMap[$prefName]))
						{
							$val = CUser::OPTED_IN;
							if ($prefVal)
							{
								// true means opted out
								$val = CUser::OPTED_OUT;
							}

							$User->setUserPreference(CUser::$SalesforceToInternalPrefNameMap[$prefName], $val);
						}
					}
				}
			}
		}

		if (defined('ENABLE_SMS_PREFERENCE') && ENABLE_SMS_PREFERENCE == true)
		{
			if (isset($_POST['sms_phone_status']))
			{
				// UNUSED - use or delete

				// Get internal prefs
				if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
				{
					$User = DAO_CFactory::create('user');
					$User->id = $_POST['user_id'];
					$User->find(true);

					$update_guest_pref = true;
				}
				else
				{
					$User = CUser::getCurrentUser();
				}

				$results = $this->reconcileSMSOptinStatus($User);
			}

			if ($_POST['op'] == 'remove_cur_number')
			{
				// Get internal prefs
				if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
				{
					$User = DAO_CFactory::create('user');
					$User->id = $_POST['user_id'];
					$User->find(true);

					$update_guest_pref = true;
				}
				else
				{
					$User = CUser::getCurrentUser();
				}

				if ($User)
				{
					$User->getUserPreferences();
				}

				$currentSMSPhoneSetting = CAppUtil::normalizePhoneNumber($User->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value']);

				if (strpos($currentSMSPhoneSetting, "PENDING ") !== false)
				{
					// 2 step in progress
					$currentSMSPhoneSetting = str_replace("PENDING ", "", $currentSMSPhoneSetting);
				}

				$SFMC = new CSFMCLink();
				$result = $SFMC->optoutOfAllKeywords($currentSMSPhoneSetting);

				$this->setAllSMSPreferences($User, CUser::UNANSWERED);

				if (!empty($result["error_occurred"]))
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'A Network issue occurred when opting out. Please try again.'
					));
					exit;
				}

				$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CUser::UNANSWERED);
				$User->setUserPreference(CUser::TEXT_MESSAGE_PROMO_PRIMARY, CUser::UNANSWERED);
				$User->setUserPreference(CUser::TEXT_MESSAGE_REMINDER_SESSION_PRIMARY, CUser::UNANSWERED);
				$User->setUserPreference(CUser::TEXT_MESSAGE_THAW_PRIMARY, CUser::UNANSWERED);

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Success removing number.'
				));
				exit;
			}

			if ($_POST['op'] == 'add_or_change_sms_number')
			{
				if (!empty($_POST['method']))
				{

					// Get internal prefs
					if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
					{
						$User = DAO_CFactory::create('user');
						$User->id = $_POST['user_id'];
						$User->find(true);

						$update_guest_pref = true;
					}
					else
					{
						$User = CUser::getCurrentUser();
					}

					if ($User)
					{
						$User->getUserPreferences();
					}

					$isFadmin = isset($_POST['is_fadmin']);

					$currentSMSPhoneSetting = $User->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];

					if (!empty($currentSMSPhoneSetting) && $currentSMSPhoneSetting != CUser::UNANSWERED)
					{
						$currentSMSPhoneSetting = CAppUtil::normalizePhoneNumber($currentSMSPhoneSetting);
					}

					if (strpos($currentSMSPhoneSetting, "PENDING ") !== false)
					{
						// 2 step in progress
						$currentSMSPhoneSetting = str_replace("PENDING ", "", $currentSMSPhoneSetting);
					}

					$SFMC = new CSFMCLink();

					$MCIsActive = false;
					$MCIsPending = false;
					$current_prefs = false;
					$mustStopCurrentNumber = false;
					if (!empty($currentSMSPhoneSetting) && is_numeric($currentSMSPhoneSetting))
					{
						// previous number exists
						$current_prefs = $SFMC->retrieveSMSPreferences($currentSMSPhoneSetting);

						if (isset($current_prefs['count']) && $current_prefs['count'] > 0)
						{
							// a non-zero count indicates some history for the phone number though all keywords may be inactive
							foreach ($current_prefs['contacts'] as $thisContact)
							{
								if ($thisContact['status'] == 'active')
								{
									$mustStopCurrentNumber = true;
									$MCIsActive = true;
									break;
								}
								else
								{
									$MCIsPending = true;
								}
							}
						}
					}

					if (isset($_POST['special_case']) && $_POST['special_case'] == 'pending_second_step')
					{
						//just send stop and opt them in directly
						$mustStopCurrentNumber = true;
					}

					$primaryPhone = (ValidationRules::telephoneCheck($User->telephone_1) ? CTemplate::telephoneFormat($User->telephone_1) : false);
					$secondaryPhone = (ValidationRules::telephoneCheck($User->telephone_2) ? CTemplate::telephoneFormat($User->telephone_2) : false);

					$primaryPhoneNormalized = CAppUtil::normalizePhoneNumber($primaryPhone);
					$secondaryPhoneNormalized = CAppUtil::normalizePhoneNumber($secondaryPhone);

					$prefPrefix = "";
					$keyword = SFMC_MAIN_KEYWORD;
					if ($isFadmin)
					{
						$keyword = SFMC_MAIN_KEYWORD_2_STEP;
						$prefPrefix = "PENDING ";
					}

					$clearedForUpdate = false;

					if ($_POST['method'] == 'new')
					{
						if (!empty($_POST['number']))
						{
							$normalNumber = CAppUtil::normalizePhoneNumber($_POST['number']);
							if ($normalNumber)
							{
								$clearedForUpdate = true;
							}
							else
							{
								echo json_encode(array(
									'processor_success' => false,
									'processor_message' => 'Invalid phone number.'
								));
								exit;
							}
						}
					}
					else if ($_POST['method'] == 'primary')
					{
						if ($primaryPhoneNormalized)
						{
							$normalNumber = $primaryPhoneNormalized;
							$clearedForUpdate = true;
						}
						else
						{
							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => 'The Primary number is invalid. Please add a new mobile number.'
							));
							exit;
						}
					}
					else if ($_POST['method'] == 'secondary')
					{
						if ($secondaryPhoneNormalized)
						{
							$normalNumber = $secondaryPhoneNormalized;
							$clearedForUpdate = true;
						}
						else
						{
							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => 'The Secondary number is invalid. Please add a new mobile number.'
							));
							exit;
						}
					}
					else
					{
						echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'Invalid method for adding new number.'
						));
						exit;
					}

					if ($clearedForUpdate)
					{
						if ($MCIsActive && $normalNumber == $currentSMSPhoneSetting)
						{

							echo json_encode(array(
								'processor_success' => true,
								'processor_message' => 'Success adding new number.',
								'new_number' => $prefPrefix . CTemplate::telephoneFormat($normalNumber),
								'override_message' => "This number is already activated. Make sure your preferences are selected."
							));
							exit;
						}

						if ($this->duplicateSMSNumberExists($User, $normalNumber))
						{
							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => 'This phone number is in use by another account. If you are unable to remove the number, please <a href="/?static=contact_us">contact Dream Dinners support.</a>'
							));
							exit;
						}

						$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, $prefPrefix . CTemplate::telephoneFormat($normalNumber));

						if ($mustStopCurrentNumber)
						{
							$SFMC->optoutOfAllKeywords($currentSMSPhoneSetting);
							sleep(10); // need to give processing time to SFMC or out may arrive after optin sent below
						}

						$result = $SFMC->optinToKeyword($normalNumber, $keyword);

						if (!empty($result["error_occurred"]))
						{
							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => 'A Network issue occurred when opting in. Please try again.'
							));
							exit;
						}

						if ($keyword == SFMC_MAIN_KEYWORD_2_STEP)
						{
							CTaskRetryQueue::queueTask(CTaskRetryQueue::DETECT_2ND_SMS_OPT_IN_STEP, array(
								'user_id' => $User->id,
								'number' => $normalNumber
							), 3600, 300);
						}

						$prefsStoreResult = $this->setAllSMSPreferences($User, $prefPrefix . CTemplate::telephoneFormat($normalNumber));
						if (!$prefsStoreResult['salesforce_results'])
						{
							//  false means the salesforce update failed, queue a retry
							CTaskRetryQueue::queueTask(CTaskRetryQueue::SYNC_SALES_FORCE_PREF, array(
								'user_id' => $User->id,
								'pref' => 'all_sms'
							), 3600, 300);
						}

						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Success adding new number.',
							'new_number' => $prefPrefix . CTemplate::telephoneFormat($normalNumber),
							'newPrefsState' => $prefsStoreResult['new_prefs']
						));
						exit;
					}
					else
					{
						echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'Unexpected Error.'
						));
						exit;
					}
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Invalid method for adding new number.'
					));
					exit;
				}
			}
		}

		if ($_POST['op'] == 'update_pref')
		{
			if (!empty($_POST['key']))
			{
				$update_guest_pref = false;

				if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
				{
					$User = DAO_CFactory::create('user');
					$User->id = $_POST['user_id'];
					$User->find(true);

					$update_guest_pref = true;
				}
				else
				{
					$User = CUser::getCurrentUser();
				}

				if ($User)
				{
					$User->setUserPreference($_POST['key'], $_POST['value']);
				}
			}

			if (defined('ENABLE_EMAIL_PREFERENCE') && ENABLE_EMAIL_PREFERENCE == true)
			{
				if (isset(CUser::$EMailPrefsDefaults[$_POST['key']]) || isset(CUser::$SMSPrefsDefaults[$_POST['key']]))
				{
					$SalesForce = new CSalesforceLink();

					$SFValue = true;
					if ($_POST['value'] == 'PENDING_OPT_IN' || $_POST['value'] == 'OPTED_IN' || $_POST['value'] == 'OPT_IN')
					{
						$SFValue = false;
					}

					$result = $SalesForce->setPrefInSalesforce($User->id, CUser::$InternalToSalesforcePrefNameMap[$_POST['key']], $SFValue);

					if (!empty($result['error_occurred']))
					{
						// schedule retry
						CTaskRetryQueue::queueTask(CTaskRetryQueue::SYNC_SALES_FORCE_PREF, array(
							'user_id' => $User->id,
							'pref' => $_POST['key'],
							'value' => $SFValue
						), 3600, 300);
					}
				}
			}

			if ($_POST['key'] == CUser::TC_DELAYED_PAYMENT_AGREE)
			{
				if ($_POST['value'] == 'true')
				{
					$_POST['value'] = 1;
				}
				else
				{
					$_POST['value'] = 0;
				}
			}

			// Handle delayed payment decline
			/*
			 * Disabled for now CES 4/1/2020
			if ($_POST['key'] == CUser::TC_DELAYED_PAYMENT_AGREE && $_POST['value'] == 0)
			{
				$Payment = DAO_CFactory::create('payment');
				$Payment->user_id = $User->id;
				$Payment->is_delayed_payment = 1;
				$Payment->delayed_payment_status = CPayment::PENDING;
				$Payment->find();

				while($Payment->fetch())
				{
					$Payment->payment_note = $Payment->payment_note . ' (Canceled due to Delayed Payment Opt-out)';
					$Payment->delayed_payment_status = CPayment::CANCELLED;
					$Payment->update();
				}
			}
			*/

			if (!empty($update_guest_pref))
			{
				$return_prefs = array($User->id => $User->preferences);
			}
			else
			{
				$return_prefs = $User->preferences;
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Preference updated.',
				(!empty($update_guest_pref) ? 'guest_preferences' : 'user_preferences') => $return_prefs,
				'date_updated' => CTemplate::dateTimeFormat($User->preferences[$_POST['key']]['timestamp_updated'])
			));
		}

		if ($_POST['op'] == 'get_pref')
		{
			$update_guest_pref = false;

			if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
			{
				$User = DAO_CFactory::create('user');
				$User->id = $_POST['user_id'];
				$User->find(true);

				$update_guest_pref = true;
			}
			else
			{
				$User = CUser::getCurrentUser();
			}

			if ($User)
			{
				$User->getUserPreferences();

				if (!empty($update_guest_pref))
				{
					$return_prefs = array($User->id => $User->preferences);
				}
				else
				{
					$return_prefs = $User->preferences;
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Preferences retrieved.',
					(!empty($update_guest_pref) ? 'guest_preferences' : 'user_preferences') => $return_prefs
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Could not find user.'
				));
			}
		}

		if ($_POST['op'] == 'request_data')
		{
			CEmail::accountRequestData(CUser::getCurrentUser());

			CUserAccountManagement::createTask(CUser::getCurrentUser()->id,CUserAccountManagement::ACTION_SEND_ACCOUNT_INFORMATION);

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Data request submitted.'
			));
		}

		if ($_POST['op'] == 'request_delete' && !empty($_POST['challenge']))
		{
			$User = new CUser();
			$User->id = CUser::getCurrentUser()->id;
			$User->fetch();
			$authenticateResult = $User->Authenticate(CUser::getCurrentUser()->primary_email, $_POST['challenge'], false, false, false, true);

			if ($authenticateResult === true)
			{
				// delete account information
				$result = CUser::getCurrentUser()->handleDeleteAccountRequest();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Delete request accepted.',
					'delete_success' => true
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Incorrect account password.',
					'delete_success' => false
				));
			}
		}
	}
}

?>