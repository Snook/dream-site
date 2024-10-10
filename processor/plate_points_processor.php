<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("includes/DAO/BusinessObject/CCustomerReferral.php");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('page/customer/my_events.php');
require_once('DAO/BusinessObject/CPointsUserHistory.php');
require_once('CMailHandlers.inc');

class processor_plate_points_processor extends CPageProcessor
{
	private $user_id = null;

	function runCustomer()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		// always require a user_id
		if (empty($_POST['user_id']) || !is_numeric($_POST['user_id']))
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The user_id is invalid. '
			));

			exit;
		}

		$this->user_id = $_POST['user_id'];

		// always require a user_id
		if (empty($_POST['op']))
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The operation parameter is invalid. '
			));

			exit;
		}

		switch ($_POST['op'])
		{
			case 'opt_in':
				$this->opt_in_user();
				break;
			case 'opt_in_with_birthday':
				$this->opt_in_user_with_birthday();
				break;
			case 'convert_user_from_DR2':
				$this->convert_user();
				break;
			case 'confirm_order':
				$this->confirm_order();
				break;
			case 'confirm_convert_user':
				$this->confirm_convert_user();
				break;
			case 'confirm_convert_PUD_user':
				$this->confirm_convert_PUD_user();
				break;

			case 'add_test_points':
				$this->add_test_points();
				break;
			case 'opt_out_user':
				$this->opt_out_user();
				break;
			case 'mark_gift_received':
				$this->mark_gift_received();
				break;
			default:
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Unknown operation parameter'
				));
				exit;
		}
	}

	function mark_gift_received()
	{

		/*note: Event PHYSICAL_REWARD_RECEIVED meta has this format:
		  array
		 physical_reward   (array)
		 level		(str - level denomination)
		 reward_id	(str - reward denomnation)
		 */

		$level = (isset($_POST['level']) ? $_POST['level'] : false);
		$gift_id = (isset($_POST['gift_id']) ? $_POST['gift_id'] : false);

		if (!$level)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'Invalid Level parameter.'
			));
			exit;
		}

		if (!$gift_id)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'Invalid gift parameter.'
			));
			exit;
		}

		$specifics = array(
			'level' => $level,
			'reward_id' => $gift_id
		);


        if ($level == 'enrolled')
        {
            $specifics['order_sequence_number'] = (isset($_POST['order_sequence_number']) ? $_POST['order_sequence_number'] : false);
        }

		CPointsUserHistory::handleEvent($this->user_id, CPointsUserHistory::PHYSICAL_REWARD_RECEIVED, $specifics);

		if ($level == 'enrolled')
        {

            $receivedGifts = CPointsUserHistory::getReceivedOrderBasedGifts($this->user_id);
            $receivedGiftsFragment = "";

            foreach($receivedGifts as $thisGift)
            {
                $receivedGiftsFragment .= "<div>Guest has received " . CPointsUserHistory::getOrderBasedGiftDisplayString($thisGift) . "</div>";
            }

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The gift was marked as received.',
				'user_id' => $this->user_id,
                'received_gifts' =>    $receivedGiftsFragment,
                'giftDisplayString' => CPointsUserHistory::getOrderBasedGiftDisplayString($gift_id)));
        }
        else
        {
            echo json_encode(array(
                'processor_success' => true,
                'processor_message' => 'The gift was marked as received.',
                'user_id' => $this->user_id,
			'giftDisplayString' => CPointsUserHistory::getGiftDisplayString($gift_id)
			));
        }
		exit;
	}

	function opt_out_user()
	{
		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		$orgObj = clone($userObj);
		$userObj->has_opted_out_of_plate_points = 1;
		$userObj->update($orgObj);

		$storeObj = DAO_CFactory::create('store');

		$storeObj->id = $userObj->home_store_id;

		if (!empty($storeObj->id))
		{
			$storeObj->find(true);
		}
		else
		{
			$storeObj = false;
		}

		plate_points_mail_handlers::sendOptOutEmail(array(
			'userObj' => $userObj,
			'storeObj' => $storeObj
		));

		CUserHistory::recordUserEvent($this->user_id, 'null', 'null', 900);

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The guest was opted out of PLATEPOINTS.'
		));
	}

	function add_test_points()
	{

		return; // just in case

		if (!CUser::isLoggedIn())
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'Not logged in. '
			));

			exit;
		}

		$this->user_id = CUser::getCurrentUser()->id;
		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		list($eventMetaData, $platePointsStatus) = CPointsUserHistory::handleEvent($userObj, CPointsUserHistory::OTHER, array(
			'comments' => 'Added test points',
			'debug_points' => 200
		));
		$results = CPointsUserHistory::getLastOperationResult();

		$result = array(
			'processor_success' => true,
			'processor_message' => 'Rating updated.'
		);

		if (!empty($eventMetaData) && $userObj->hasEnrolledInPlatePoints())
		{
			$result['platepoints_status'] = $platePointsStatus;

			$result['dd_toasts'] = array(
				array(
					'message' => 'You earned ' . $eventMetaData['points'] . ' PLATEPOINTS!',
					'position' => 'topcenter',
					'css_style' => 'platepoints'
				)
			);
		}

		echo json_encode($result);
	}

	function confirm_order()
	{

		// always require a user_id
		if (empty($_POST['order_id']) || !is_numeric($_POST['order_id']))
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The order id is invalid. '
			));

			exit;
		}

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->id = $_POST['order_id'];
		if (!$orderObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The order could not be found. '
			));

			exit;
		}

		if ($orderObj->points_are_actualized)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The order has already been confirmed. '
			));

			exit;
		}

		$oldOrder = clone($orderObj);
		$orderObj->points_are_actualized = 1;
		$orderObj->update($oldOrder);

		try
		{
			list($results, $platePointsStatus) = CPointsUserHistory::handleEvent($this->user_id, CPointsUserHistory::ORDER_CONFIRMED, "", $orderObj);

			if ($results)
			{
				$result = array_pop($results);

				$credits = CPointsCredits::getAvailableCreditForUser($this->user_id);

				echo json_encode(array(
					'processor_success' => $result['success'],
					'processor_message' => $result['message'],
					'platepoints_status' => $platePointsStatus,
					'points_this_order' => $result['points_awarded'],
					'pending_points' => $result['pending_points'],
					'total_credit' => $credits
				));
			}
		}
		catch (Exception $e)
		{
			CLog::RecordException($e);

			echo json_encode(array(
				'processor_success' => $result['success'],
				'processor_message' => "Exception occurred: " . $e->getMessage()
			));
		}
	}

	function confirm_convert_user()
	{
		$tpl = new CTemplate();

		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		$tpl->assign('user_name', $userObj->firstname . " " . $userObj->lastname);
		$tpl->assign('user_id', $userObj->id);

		$conversionData = CPointsUserHistory::getDR2ConversionData($userObj);

		$tpl->assign('conversion_data', $conversionData);
		$tpl->assign('has_opted_out', $userObj->has_opted_out_of_plate_points);

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => "Conversion data was retrieved.",
			'user_id' => $userObj->id,
			'data' => $tpl->fetch('admin/subtemplate/dr2_conversion_confirm.tpl.php')
		));
	}

	function confirm_convert_PUD_user()
	{
		$tpl = new CTemplate();

		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		$tpl->assign('user_name', $userObj->firstname . " " . $userObj->lastname);
		$tpl->assign('user_id', $userObj->id);

		$conversionData = CPointsUserHistory::getPreferredUserConversionData($userObj);

		$tpl->assign('conversion_data', $conversionData);
		$tpl->assign('has_opted_out', $userObj->has_opted_out_of_plate_points);

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => "Conversion data was retrieved.",
			'user_id' => $userObj->id,
			'data' => $tpl->fetch('admin/subtemplate/dr2_conversion_confirm.tpl.php')
		));
	}

	function opt_in_user()
	{
		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		$clearToEnroll = true;

		if ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3)
		{
			$clearToEnroll = false;
			if ($userObj->dream_rewards_version == 3)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'User is already enrolled in PLATEPOINTS.'
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'User is enrolled in Dream Rewards version 2. Please use conversion method'
				));
			}
		}

		if ($clearToEnroll)
		{
			CPointsUserHistory::handleEvent($userObj->id, CPointsUserHistory::OPT_IN);
			$result = CPointsUserHistory::getLastOperationResult();

			echo json_encode(array(
				'processor_success' => $result['success'],
				'processor_message' => $result['message']
			));
		}
	}

	function opt_in_user_with_birthday()
	{
		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;

		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'User not found.'
			));
		}

		$clearToEnroll = true;

		if ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3)
		{
			$clearToEnroll = false;
			if ($userObj->dream_rewards_version == 3)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'User is already enrolled in PLATEPOINTS.'
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'User is enrolled in Dream Rewards version 2. Please use conversion method'
				));
			}
		}

		if ($clearToEnroll)
		{
			CPointsUserHistory::handleEvent($userObj->id, CPointsUserHistory::OPT_IN);
			$result = CPointsUserHistory::getLastOperationResult();

			if ($result)
			{
				$month = $_POST['birth_month'];
				$year = $_POST['birth_year'];
				CUserData::saveBirthdayForPlatePoint($month, $year, $userObj);

				if (CPointsUserHistory::isElgibleForBirthdayRewardAtEnrollment($userObj->home_store_id, $month, $userObj->id))
				{
					$metaData = CPointsUserHistory::getEventMetaData(CPointsUserHistory::BIRTHDAY_MONTH);
					$eventComment = 'Earned $' . $metaData['credit'] . ' birthday Dinner Dollars!';

					CPointsUserHistory::handleEvent($userObj, CPointsUserHistory::BIRTHDAY_MONTH, array(
						'comments' => $eventComment,
						'year' => $year,
						'month' => $month
					));
				}
			}
			$result = CPointsUserHistory::getLastOperationResult();
			echo json_encode(array(
				'processor_success' => $result[0]['success'],
				'processor_message' => $result[0]['message']
			));
		}
	}
}

?>