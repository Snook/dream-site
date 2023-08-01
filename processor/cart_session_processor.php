<?php
/*
 * Created on August 08, 2011
 * project_name processor_cart_session_processor
 *
 * Copyright 2011 DreamDinners
 * @author CarlS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");
require_once("includes/DAO/BusinessObject/CBundle.php");

class processor_cart_session_processor extends CPageProcessor
{

	function logPasswordAccess($succeeded)
	{

		$PostVars = print_r($_POST, true);

		if ($succeeded)
		{
			CLog::RecordNew(CLog::DEBUG, "Session Password Access Success | " . $PostVars);
		}
		else
		{
			CLog::RecordNew(CLog::DEBUG, "Session Password Access Failed | " . $PostVars);
		}
	}

	function runPublic()
	{

		$testMode = false;
		if (isset($_POST['test']))
		{
			$testMode = true;
		}

		if (!$testMode)
		{
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');
		}

		if (isset($_POST['op']) && $_POST['op'] == 'clear')
		{
			$CartObj = CCart2::instance();
			$CartObj->clearSession(true);

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Session cleared'
			));
		}

		if (isset($_POST['op']) && $_POST['op'] == 'save')
		{
			if (isset($_POST['sid']) && is_numeric($_POST['sid']))
			{
				$CartObj = CCart2::instance();
				$cart_session_type = $CartObj->getNavigationType();

				$Session = DAO_CFactory::create('session');
				$Session->id = $_POST['sid'];
				if (!$Session->find(true))
				{
					if ($testMode)
					{
						return "The session cannot be found.";
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'result_code' => 1000,
						'processor_message' => 'The session cannot be found.'
					));
				}

				if ($cart_session_type == CSession::INTRO && $Session->session_type != CSession::DREAM_TASTE)
				{
					$numSlots = $Session->getRemainingIntroSlots();
				}
				else
				{
					$user_id = CUser::getCurrentUser()->id;
					if (empty($user_id))
					{
						$user_id = false;
					}

					// capacity for RSVP determination
					$activeRSVPs = $Session->get_RSVP_count($user_id);

					$numSlots = $Session->getRemainingSlots() - $activeRSVPs;
				}

				if ($numSlots <= 0)
				{
					if ($testMode)
					{
						return "The session is full.";
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'result_code' => 1001,
						'processor_message' => 'The session is full.'
					));
				}

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $Session->store_id;
				$StoreObj->selectAdd('timezone_id');
				$StoreObj->find(true);

				if ($Session->session_publish_state != 'PUBLISHED' || !$Session->isOpen($StoreObj))
				{
					if ($testMode)
					{
						return "The session is closed.";
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'result_code' => 1002,
						'processor_message' => 'The session is closed.'
					));
				}

				if (!empty($Session->session_password))
				{
					if (empty($_POST['pwd']))
					{
						$this->logPasswordAccess(false);

						if ($testMode)
						{
							return "The password is incorrect.";
						}

						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'result_code' => 1003,
							'processor_message' => 'The password is incorrect. Please try again or contact the session coordinator.'
						));
					}

					if (trim($_POST['pwd']) !== trim($Session->session_password))
					{
						$this->logPasswordAccess(false);

						if ($testMode)
						{
							return "The password is incorrect.";
						}

						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'result_code' => 1003,
							'processor_message' => 'The password is incorrect. Please try again or contact the session coordinator.'
						));
					}

					$this->logPasswordAccess(true);
				}

				if (!$testMode)
				{
					$CartObj->addMenuId($Session->menu_id, true);
					$CartObj->storeChangeEvent($Session->store_id, true);

					if ($cart_session_type == CSession::INTRO)
					{
						// may need to update the bundle_id
						$Bundle = CBundle::getActiveBundleForMenu($Session->menu_id, $StoreObj);
						$CartObj->addBundleId($Bundle->id, true);
						$CartObj->addNavigationType(CSession::INTRO, true);
					}
					else if ($Session->session_type == CSession::STANDARD)
					{
						if ($Session->isPrivate())
						{
							$CartObj->addNavigationType(CSession::EVENT, true);
						}
						else
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
							//$CartObj->addNavigationType(CSession::STANDARD, true);
						}
						$CartObj->removeBundleId();
						$CartObj->removeMealCustomizationOptOut();
					}
					else if ($Session->isMadeForYou())
					{
						if ($Session->isPrivate())
						{
							$CartObj->addNavigationType(CSession::EVENT, true);
						}
						else if ($Session->isDelivery())
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
							//$CartObj->addNavigationType(CSession::DELIVERY, true);
						}
						else
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
							//$CartObj->addNavigationType(CSession::MADE_FOR_YOU, true);
						}

						$CartObj->removeBundleId();
					}
					else if ($Session->session_type == CSession::FUNDRAISER || $Session->session_type == CSession::DREAM_TASTE)
					{
						$session = CSession::getSessionDetail($Session->id, false);
						$CartObj->addBundleId($session['bundle_id'], true);
						$CartObj->addNavigationType(CSession::EVENT, true);
						$CartObj->removeMealCustomizationOptOut();
					}

					$CartObj->addSessionId($Session->id, true);
				}

				if ($testMode)
				{
					return "Success";
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'result_code' => 1,
					'bounce_to' => 'main.php?page=checkout',
					'result_day' => CTemplate::dateTimeFormat($Session->session_start, YEAR_MONTH_DAY),
					'processor_message' => 'The session was successfully selected.'
				));
			}
			else
			{
				if ($testMode)
				{
					return "The session cannot be found.";
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 1000,
					'processor_message' => 'The session cannot be found.'
				));
			}
		}
	}
}

?>