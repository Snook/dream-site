<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CCouponCode.php");
require_once("includes/DAO/BusinessObject/CPayment.php");
require_once("includes/DAO/BusinessObject/CPointsCredits.php");
require_once("includes/CCart2.inc");

class processor_cart_modify_plate_points_credits extends CPageProcessor
{
	function runPublic()
	{
		header('Content-type: application/json');

		if (isset($_POST['op']) && $_POST['op'] == 'modify')
		{
			self::modify_plate_points_credits();
		}
		else
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 2,  'processor_message' => 'Invalid operation specified.'));
			exit;
		}
	}


	static function modify_plate_points_credits()
	{

		if (!isset($_POST['new_credit_value']) || !is_numeric($_POST['new_credit_value']))
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 2,  'processor_message' => 'Invalid PLATEPOINTS Dinner Dollars amount.'));
			exit;
		}
		else
		{
			$amountRequested = $_POST['new_credit_value'];
		}

		$CartObj = CCart2::instance();
		$DAO_orders = $CartObj->getOrder();
		$DAO_store = $DAO_orders->getStoreObj();
		$DAO_user = $DAO_orders->getUser();

		$coupon = $DAO_orders->getCoupon();

		if (!empty($coupon) && !$coupon->valid_with_plate_points_credits && $amountRequested > 0)
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 2,  'processor_message' => 'PLATEPOINTS Dinner Dollars cannot be used with the coupon attached to this order.'));
			exit;
		}


		$maxAvailableCredit = CPointsCredits::getAvailableCreditForUser($DAO_orders->user_id);
		$maxDeduction = $DAO_orders->getPointsDiscountableAmount();


		if ($amountRequested > $maxAvailableCredit)
		{
			$amountRequested = $maxAvailableCredit;
		}

		if ($amountRequested > $maxDeduction)
		{
			$amountRequested = $maxDeduction;
		}

		$platePointsStatus = CPointsUserHistory::getPlatePointsStatus($DAO_store, $DAO_user);

		$DAO_orders->points_discount_total = $amountRequested;
		$DAO_orders->refresh($DAO_user);
		$DAO_orders->recalculate(false, false, false, $platePointsStatus['userIsOnHold']);
		$CartObj->addOrder($DAO_orders);

		$results_array = array(
					'processor_success' => true,
					'result_code' => 1,
					'processor_message' => 'Modification to PLATEPOINTS Dinner Dollars is successful.',
					'orderInfo' => $DAO_orders->toArray());

		echo json_encode($results_array);
		exit;
	}
}
?>