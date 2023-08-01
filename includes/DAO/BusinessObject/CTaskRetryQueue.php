<?php
require_once("includes/CSalesforceLink.inc");
require_once("includes/CSFMCLink.inc");
require_once ("processor/account.php");

class CTaskRetryQueue
{
	const DETECT_2ND_SMS_OPT_IN_STEP = 'DETECT_2ND_SMS_OPT_IN_STEP';
	const SYNC_SALES_FORCE_PREF = 'SYNC_SALES_FORCE_PREF';

	const PROCESS_STATUS_INTERNAL_ERROR = -1;
	const PROCESS_STATUS_NOT_RUN = 0;
	const PROCESS_STATUS_SUCCESS = 1;
	const PROCESS_STATUS_FAILED = 2;
	const PROCESS_STATUS_REQUEUE = 3;
	const PROCESS_STATUS_FAILED_AND_EXPIRED = 4;


	static function hasIntervalPassed($task)
	{
		if (!empty($task->last_run_time))
		{
			$lastProcessedTS = strtotime($task->last_run_time);
		}
		else
		{
			$lastProcessedTS = strtotime($task->start_time);
		}
		if (time() - $lastProcessedTS > $task->pause_interval)
		{
			return true;
		}

		return false;
	}

	static function shouldRunAgain($task)
	{
		// TODO: what about DST faLL back? Petition your Congressman!
		$expireTimeTS = strtotime($task->start_time) + $task->run_time;
		$now = time();

		if ($expireTimeTS > $now)
		{
				return true;
		}

		return false;
	}

	// handle a task whose completion code is PROCESS_STATUS_NOT_RUN or PROCESS_STATUS_REQUEUE
	static function process($task_id)
	{
		$result = null;


		$task = DAO_CFactory::create('task_retry_queue');
		$task->id = $task_id;
		if (!$task->find(true))
		{
			return; // should never happen
		}

		//The pause_interval is the number of seconds minimum between attempts
		// Check if either time since the last run (or start time on first run) is greater than the pause_interval do nothing if not.
		// This means that task may process after the full run time allowed but only by less than the pause_interval
		// Also the task is guaranteed to run once

		if (!self::hasIntervalPassed($task))
		{
			return;
		}

		switch($task->task_type)
		{
			case self::DETECT_2ND_SMS_OPT_IN_STEP:
				$result = self::handle_DETECT_2ND_SMS_OPT_IN_STEP($task);
				break;
			case self::SYNC_SALES_FORCE_PREF:
				$result = self::handle_SYNC_SALES_FORCE_PREF($task);
				break;
			default:
				CLog::RecordNew(CLog::ERROR, "Illegal Task Type in CTaskRetryQueue::process(): " . $task->task_type, "", "", true);
				$result = self::PROCESS_STATUS_INTERNAL_ERROR;
				break;
		}

		if ($result['code'] == self::PROCESS_STATUS_SUCCESS)
		{
			$orgState = clone($task);
			$task->last_run_time = date("Y-m-d H:i:s");
			$task->completion_code = self::PROCESS_STATUS_SUCCESS;
			$task->update($orgState);
		}
		else if ($result['code'] == self::PROCESS_STATUS_FAILED)
		{
			$orgState = clone($task);

			$shouldRunAgain = self::shouldRunAgain($task);
			$task->last_run_time = date("Y-m-d H:i:s");

			if ($shouldRunAgain)
			{
				$task->completion_code = self::PROCESS_STATUS_REQUEUE;
			}
			else
			{
				$task->completion_code = self::PROCESS_STATUS_FAILED_AND_EXPIRED;
			}

			$task->update($orgState);
		}
	}

	static function queueTask($type, $dataArray, $runTimeInSeconds, $intervalInSeconds)
	{
		if ($runTimeInSeconds < 300)
		{
			// run time must be at least 5 minutes -
			throw new Exception("Retry Queue Task run time must be at least 5 minutes");
		}

		if ($intervalInSeconds < 120)
		{
			// interval must be at least 2 minutes -
			throw new Exception("Retry Queue Task interval must be at least 2 minutes");
		}


		$taskObj = DAO_CFactory::create('task_retry_queue');
		$taskObj->task_type = $type;
		$taskObj->data = json_encode($dataArray);
		$taskObj->start_time = date('Y-m-d H:i:s');
		$taskObj->run_time = $runTimeInSeconds;
		$taskObj->pause_interval = $intervalInSeconds;
		$taskObj->completion_code = self::PROCESS_STATUS_NOT_RUN;

		$taskObj->insert();
	}

	static function handle_SYNC_SALES_FORCE_PREF($task)
	{

		$salesForce = new CSalesforceLink();
		$data = json_decode($task->data, true);

		$User = DAO_CFactory::create('user');
		$User->id = $data['user_id'];
		$User->find(true);
		$User->getUserPreferences();

		if ($data['pref'] == 'all_sms')
		{
			$SMSNewValuesExternal = array();
			$SMSPrefs = CUser::$SMSPrefsDefaults;
			foreach ($SMSPrefs as $key => $value)
			{
				if ($key != CUser::TEXT_MESSAGE_TARGET_NUMBER) {

					if (CUser::OPTED_IN) {
						$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = false;
					} else {
						$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = true;
					}
				}
				else
				{
					$SMSNewValuesExternal[CUser::$InternalToSalesforcePrefNameMap[$key]] = $value;
				}
			}

			$result = $salesForce->setPrefArrayInSalesforce($User->id, $SMSNewValuesExternal);

			if (!empty($result["error_occurred"]))
			{
				return array('code' => self::PROCESS_STATUS_FAILED);
			}

			return array('code' => self::PROCESS_STATUS_SUCCESS);

		}
		else
		{

			$result = $salesForce->setPrefInSalesforce($User->id, CUser::$InternalToSalesforcePrefNameMap[$data['pref']], $data['value']);
			if (!empty($result["error_occurred"]))
			{
				return array('code' => self::PROCESS_STATUS_FAILED);
			}

			return array('code' => self::PROCESS_STATUS_SUCCESS);

		}
	}

	static function handle_DETECT_2ND_SMS_OPT_IN_STEP($task)
	{
		$salesForce = new CSFMCLink();
		$data = json_decode($task->data, true);

		$User = DAO_CFactory::create('user');
		$User->id = $data['user_id'];
		$User->find(true);
		$User->getUserPreferences();

		$currentSMSPhoneSetting = $User->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];

		if (!empty($currentSMSPhoneSetting) && strpos($currentSMSPhoneSetting, "PENDING ") !== false)
		{
			$currentSMSPhoneSetting = str_replace("PENDING ", "", $currentSMSPhoneSetting);

			$sfmcSettings = $salesForce->retrieveSMSPreferences($currentSMSPhoneSetting);

			if (!empty($sfmcSettings['error_occurred'])) {
				// most likely a brand new account with no SF object so ignore
				return array('code' => self::PROCESS_STATUS_FAILED);
			}
			else
			{
				// there should be a active keyword - BackOffice has them as active so what do we do?
				if (isset($sfmcSettings['count']) && $sfmcSettings['count'] > 0)
				{
					// a non-zero count indicates some history for the phone number though all keywords may be inactive
					foreach ($sfmcSettings['contacts'] as $thisContact)
					{
						if ($thisContact['status'] == "active" && $thisContact['keyword'] == SFMC_MAIN_KEYWORD_2_STEP)
						{
							// BINGO -if active then the guest has sent 2nd step
							// Update Pref
							$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($currentSMSPhoneSetting));
							$prefsProcessor = new processor_account();
							$prefsProcessor->setAllSMSPreferences($User, CTemplate::telephoneFormat($currentSMSPhoneSetting));
							return array('code' => self::PROCESS_STATUS_SUCCESS);
						}
						else if ($thisContact['status'] == "active" && $thisContact['keyword'] == SFMC_MAIN_KEYWORD)
						{
							// user must of activated directly from edit account
							$User->setUserPreference(CUser::TEXT_MESSAGE_TARGET_NUMBER, CTemplate::telephoneFormat($currentSMSPhoneSetting));
							$prefsProcessor = new processor_account();
							$prefsProcessor->setAllSMSPreferences($User, CTemplate::telephoneFormat($currentSMSPhoneSetting));
							return array('code' => self::PROCESS_STATUS_SUCCESS);
						}
					}
					return array('code' => self::PROCESS_STATUS_FAILED);
				}
				else
				{
					return array('code' => self::PROCESS_STATUS_FAILED);
				}
			}
		}
	}
}

?>
