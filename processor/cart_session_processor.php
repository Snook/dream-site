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

				$DAO_session = DAO_CFactory::create('session');
				$DAO_session->id = $_POST['sid'];
				if (!$DAO_session->find(true))
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

				if ($cart_session_type == CSession::INTRO && $DAO_session->session_type != CSession::DREAM_TASTE)
				{
					$numSlots = $DAO_session->getRemainingIntroSlots();
				}
				else
				{
					$user_id = CUser::getCurrentUser()->id;
					if (empty($user_id))
					{
						$user_id = false;
					}

					// capacity for RSVP determination
					$activeRSVPs = $DAO_session->get_RSVP_count($user_id);

					$numSlots = $DAO_session->getRemainingSlots() - $activeRSVPs;
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

				$DAO_store = DAO_CFactory::create('store', true);
				$DAO_store->id = $DAO_session->store_id;
				$DAO_store->selectAdd('timezone_id');
				$DAO_store->find(true);

				if ($DAO_session->session_publish_state != 'PUBLISHED' || !$DAO_session->isOpen($DAO_store))
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

				if (!empty($DAO_session->session_password))
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

					if (trim($_POST['pwd']) !== trim($DAO_session->session_password))
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
					$CartObj->addMenuId($DAO_session->menu_id, true);
					$CartObj->storeChangeEvent($DAO_session->store_id, true);

					if ($cart_session_type == CSession::INTRO)
					{
						// may need to update the bundle_id
						$Bundle = CBundle::getActiveBundleForMenu($DAO_session->menu_id, $DAO_store);
						$CartObj->addBundleId($Bundle->id, true);
						$CartObj->removeDeliveryTip();
						$CartObj->addNavigationType(CSession::INTRO, true);
					}
					else if ($DAO_session->session_type == CSession::STANDARD)
					{
						if ($DAO_session->isPrivate())
						{
							$CartObj->addNavigationType(CSession::EVENT, true);
						}
						else
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
						}

						$CartObj->removeDeliveryTip();
						$CartObj->removeBundleId();
						$CartObj->removeMealCustomizationOptOut();
					}
					else if ($DAO_session->isMadeForYou())
					{
						if ($DAO_session->isPrivate())
						{
							$CartObj->addNavigationType(CSession::EVENT, true);
							$CartObj->removeDeliveryTip();
						}
						else if ($DAO_session->isDelivery())
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
							if ($CartObj->getOrder()->eligibleForDeliveryTip())
							{
								$CartObj->addDeliveryTip($CartObj->getOrder()->getStoreObj()->default_delivery_tip, true);
							}
						}
						else
						{
							$CartObj->addNavigationType(CSession::ALL_STANDARD, true);
							$CartObj->removeDeliveryTip();
						}

						$CartObj->removeBundleId();
					}
					else if ($DAO_session->session_type == CSession::FUNDRAISER || $DAO_session->session_type == CSession::DREAM_TASTE)
					{
						$session = CSession::getSessionDetail($DAO_session->id, false);
						$CartObj->addBundleId($session['bundle_id'], true);
						$CartObj->addNavigationType(CSession::EVENT, true);
						$CartObj->removeDeliveryTip();
						$CartObj->removeMealCustomizationOptOut();
					}

					$CartObj->addSessionId($DAO_session->id, true);
				}

				if ($testMode)
				{
					return "Success";
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'result_code' => 1,
					'bounce_to' => '/checkout',
					'result_day' => CTemplate::dateTimeFormat($DAO_session->session_start, YEAR_MONTH_DAY),
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