<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");

class processor_cart_bag_fee extends CPageProcessor
{
	function runPublic()
	{
		header('Content-type: application/json');

		if(isset($_POST['op']))
		{
			if($_POST['op'] == 'set_opt_out')
			{
				$CartObj = CCart2::instance();
				$Order = $CartObj->getOrder();

				$option = null;
				if ($_POST['opt_out'] === 1 || $_POST['opt_out'] === '1')
				{
					$option = 1;
				}
				else if ($_POST['opt_out'] === 0 || $_POST['opt_out'] === '0')
				{
					$option = 0;
				}

				$Order->opted_to_bring_bags = $option;
				$CartObj->addBagOptOut($option, true);

				$Order->refresh(CUser::getCurrentUser());
				$Order->recalculate();

				// menu item was set to zero, removed item from cart
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Set bag opt out.',
					'orderInfo' => $Order->toArray()
				));
			}
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'Invalid Operation.'
			));
		}
	}


}
?>