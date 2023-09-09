<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");
require_once("includes/DAO/BusinessObject/CPayment.php");

class processor_cart_remove_payment extends CPageProcessor
{
	function runPublic()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		if (isset($_POST['payment_type']))
		{
			if ($_POST['payment_type'] == 'coupon')
			{
				$results_array = self::remove_coupon_code();

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'store_credit' && $_POST['payment_number'] == 'all')
			{
				$results_array = self::remove_all_store_credit();

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'cc_ref')
			{


				$results_array = self::remove_credit_card_reference();

				CAppUtil::processorMessageEcho($results_array);
			}

			if (isset($_POST['payment_type']))
			{
				$results_array = self::remove_payment($_POST['payment_type']);

				CAppUtil::processorMessageEcho($results_array);
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'result_code' => 2,
				'processor_message' => 'Payment type not valid.'
			));
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'result_code' => 2,
				'processor_message' => 'Invalid operation specified.'
			));
		}
	}

	static function remove_credit_card_reference()
	{

		if (empty($_POST['cc_ref_id']) || !is_numeric($_POST['cc_ref_id']))
		{
			return array(
				'processor_success' => false,
				'processor_message' => 'Invalid Credt Card Reference number.',
			);
		}

		$user_id = CUser::getCurrentUser()->id;

		if (empty($user_id))
		{
			return array(
				'processor_success' => false,
				'processor_message' => 'Invalid user. Guest may be logged out.',
			);
		}

		if (CPayment::removePaymentStoredForReference($_POST['cc_ref_id'], $user_id))
		{
			return array(
				'processor_success' => true,
				'processor_message' => 'The card has been removed from our records.',
			);
		}
		else
		{
			return array(
				'processor_success' => false,
				'processor_message' => 'Invalid CredIt Card Reference number or guest.',
			);
		}
	}

	static function remove_all_store_credit()
	{
		$cart = CCart2::instance(false);
		$cart->removeAllStoreCredit();

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'The payment was successfully removed.'
		);
	}

	static function remove_payment($payment_type)
	{
		if (empty($_POST['payment_number']) || !is_numeric($_POST['payment_number']))
		{
			return array(
				'processor_success' => false,
				'result_code' => 2002,
				'processor_message' => 'Invalid Payment specified.'
			);
		}

		$cart = CCart2::instance(false);
		$cart->removePayment($_POST['payment_number']);

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'The payment was successfully removed.'
		);
	}

	static function remove_coupon_code()
	{
		$CartObj = CCart2::instance();
		$DAO_orders = $CartObj->getOrder();
		$DAO_coupon_code = $DAO_orders->getCoupon();

		$editOrderId = $CartObj->getEditOrderId();

		$DAO_orders->removeCoupon();

		$DAO_orders->refresh(CUser::getCurrentUser());
		$DAO_orders->recalculate();

		$CartObj->addOrder($DAO_orders);

		$bounce_to = false;
		if (isset($DAO_coupon_code) && $DAO_coupon_code->limit_to_recipe_id && !empty($_POST['page']) && $_POST['page'] == 'checkout')
		{
			$bounce_to = '/checkout';
		}

		$menu_item_id = false;
		$totalQty = 0;
		if (isset($DAO_coupon_code) && $DAO_coupon_code->limit_to_recipe_id)
		{
			$menu_item_id = $DAO_coupon_code->menu_item_id;

			$order_items = $DAO_orders->getItems();

			if (!empty($order_items[$menu_item_id]))
			{
				$totalQty = $order_items[$menu_item_id][0];
			}
		}

		$results_array = array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Coupon removed from order.',
			'limit_to_recipe_id' => $menu_item_id,
			'qty_in_cart' => $totalQty,
			'orderInfo' => $DAO_orders->toArray(),
			'is_edit_order' => false
		);

		if ($bounce_to)
		{
			$results_array['bounce_to'] = '/checkout';
		}

		if (!empty($editOrderId))
		{
			$results_array['is_edit_order'] = true;
		}

		return $results_array;
	}

}

?>