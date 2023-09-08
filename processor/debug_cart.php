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
require_once("includes/DAO/BusinessObject/CUser.php");
require_once('includes/class.inputfilter_clean.php');

class processor_debug_cart extends CPageProcessor
{
	function runPublic()
	{
		$AccessingUser = CUser::getCurrentUser();

		if (true /*(defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')*/)
		{
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');

			if (isset($_POST['op']) && $_POST['op'] == 'view')
			{
				if (isset($_POST['method']) && $_POST['method'] == 'MINE')
				{

					$CartObj = CCart2::instance(false);
					$debugInfo = $CartObj->getDebugInfo();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Returning debug info.',
						'data' => $debugInfo
					));
				}
				else if (isset($_POST['method']) && $_POST['method'] == 'USER_ID')
				{
					if (isset($_POST['key']) && is_numeric($_POST['key']))
					{
						$user_id = $_POST['key'];
						$contentsFinder = new DAO();
						$contentsFinder->query("select cc.id, c.cart_key  from dreamcart.cart_contents cc
                                                    join dreamsite.cart c on c.cart_contents_id = cc.id
                                                    where cc.user_id = $user_id
                                                    order by cc.timestamp_updated desc limit 1");

						if ($contentsFinder->N == 0)
						{
							CAppUtil::processorMessageEcho(array(
								'processor_success' => false,
								'processor_message' => 'Cart not found for User ID.',
							));
						}

						$contentsFinder->fetch();

						$CartObj = CCart2::instance(false, $contentsFinder->cart_key);
						$debugInfo = $CartObj->getDebugInfo();

						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Returning debug info.',
							'data' => $debugInfo
						));
					}
					else
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Valid user id not provided.',
						));
					}
				}
				else if (isset($_POST['method']) && $_POST['method'] == 'CART_ID')
				{
					if (isset($_POST['key']))
					{
						$cart_id = $_POST['key'];

						$CartObj = CCart2::instance(false, $cart_id);
						$debugInfo = $CartObj->getDebugInfo();

						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Returning debug info.',
							'data' => $debugInfo
						));
					}
					else
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Valid cart id not provided.',
						));
					}
				}
			}
			else if (isset($_POST['op']) && $_POST['op'] == 'return_fauid')
			{
				$sessionKey = CBrowserSession::instance()->browser_session_key;
				$csrf_protection = new CSRF($sessionKey);
				$csrf_protection->logout();

				CBrowserSession::instance()->ExpireSession();

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'The session is full. Please contact your host or store.',
					'bounce' => '?page=admin_user_details&id=' . $_POST['dduid']
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Invalid operation specified.',
					'result_code' => 2
				));
			}
		}
	}
}

?>