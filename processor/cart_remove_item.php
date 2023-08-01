<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");

class processor_cart_remove_item extends CPageProcessor
{
	function runPublic()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');


		if(isset($_POST['item_type']))
		{
			if($_POST['item_type'] == 'gift_card')
			{
				$results_array = self::remove_gift_card_purchase();

				echo json_encode($results_array);
				exit;
			}

			echo json_encode(array('processor_success' => false, 'result_code' => 3002,  'processor_message' => 'Item type not valid.'));
			exit;
		}
		else
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 3003,  'processor_message' => 'Invalid item type specified.'));
			exit;
		}
	}


	static function remove_gift_card_purchase()
	{
		if (empty($_POST['gc_order_id']) || !is_numeric($_POST['gc_order_id']))
		{
			return array('processor_success' => false, 'result_code' => 3004,  'processor_message' => 'Invalid gift card order id specified.');
		}


		$cart = CCart2::instance(false);
		$cart->removeGiftCardOrder($_POST['gc_order_id']);

		return array('processor_success' => true, 'result_code' => 1,  'processor_message' => 'The gift card was successfully removed.');

	}


}
?>