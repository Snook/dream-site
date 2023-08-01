<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");

class processor_cart_clear_category extends CPageProcessor
{
	function runPublic()
	{
		header('Content-type: application/json');

		if (isset($_POST['op']))
		{
			if ($_POST['op'] == 'get_clear_form')
			{
				$results_array = self::get_clear_form();

				echo json_encode($results_array);
				exit;
			}

			if ($_POST['op'] == 'do_clear')
			{
				$results_array = self::do_clear_cart();

				echo json_encode($results_array);
				exit;
			}

			if ($_POST['op'] == 'do_clear_session')
			{
				$results_array = self::do_clear_session();

				echo json_encode($results_array);
				exit;
			}

			echo json_encode(array(
				'processor_success' => false,
				'result_code' => 6002,
				'processor_message' => 'Invalid operation.'
			));
			exit;
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'result_code' => 6003,
				'processor_message' => 'Invalid operation.'
			));
			exit;
		}
	}

	static function do_clear_session()
	{
		$Cart = CCart2::instance(false);
		$Cart->clearSession();

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Cart was cleared'
		);
	}

	static function do_clear_cart()
	{
		if (empty($_POST['clear_items']))
		{
			echo json_encode(array(
				'processor_success' => false,
				'result_code' => 6004,
				'processor_message' => 'Clear List is empty.'
			));
		}

		$Cart = CCart2::instance(false);

		if (strpos($_POST['clear_items'], 'all') !== false)
		{
			$Cart->addSessionId(0, true);
			$Cart->addNavigationType(null, true, true);
			$Cart->clearMenuItems();
			$Cart->clearAllPayments();
		}

		if (strpos($_POST['clear_edit_order'], 'all') !== false)
		{
			$Cart->clearEditOrderCart();
			CBrowserSession::setValue('EDIT_DELIVERED_ORDER');
		}

		if (strpos($_POST['clear_items'], 'gift') !== false)
		{
			$Cart->clearGiftCards();
		}

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Cart was cleared'
		);
	}

	static function get_clear_form()
	{
		$Cart = CCart2::instance();
		$infoArray = $Cart->get_cart_info_array();
		$tpl = new CTemplate();

		if (!$infoArray['has_session'] && !$infoArray['has_food'] && !$infoArray['has_payment'] && !$infoArray['has_gift_cards'])
		{
			$tpl->assign('cartIsEssentiallyEmpty', true);
		}
		else
		{
			$tpl->assign('cartInfo', $infoArray);
		}

		$html = $tpl->fetch('customer/subtemplate/clear_cart_form.tpl.php');

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Cart clear form retrieved',
			'html' => $html
		);
	}

}

?>