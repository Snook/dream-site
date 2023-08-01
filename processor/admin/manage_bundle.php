<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('payment/PayPalProcess.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CStore.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('page/admin/fundraiser.php');

class processor_admin_manage_bundle extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->runManageBundle();
	}

	function runHomeOfficeManager()
	{
		$this->runManageBundle();
	}

	function runManageBundle()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_menu_items')
		{
			if ($_REQUEST['menu_id'])
			{
				$tpl = new CTemplate();

				$menuItems = CMenuItem::getMenuItems($_REQUEST['menu_id']);

				$tpl->assign('menuItems', $menuItems);

				$menu_item_html = $tpl->fetch('admin/subtemplate/manage_bundle_menu_items.tpl.php');

				if (!empty($menuItems))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Menu items',
						'menu_item_html' => $menu_item_html
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Fundraiser info not found'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Fundraiser id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_bundle_menu_item')
		{
			if ($_REQUEST['bundle_id'] && $_REQUEST['menu_item_id'])
			{
				$DAO_bundle = DAO_CFactory::create('bundle');
				$DAO_bundle->id = $_REQUEST['bundle_id'];
				$DAO_bundle->find('true');

				$bundleMenuItem = DAO_CFactory::create('bundle_to_menu_item');
				$bundleMenuItem->bundle_id = $_REQUEST['bundle_id'];
				$bundleMenuItem->menu_item_id = $_REQUEST['menu_item_id'];

				if ($bundleMenuItem->find(true))
				{
					if ($_REQUEST['state'] == 0)
					{
						$bundleMenuItem->delete();

						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Menu item removed',
							'dd_toasts' => array(
								array('message' => 'Menu item removed.')
							)
						));
						exit;
					}
				}
				else if ($_REQUEST['state'] == 1)
				{
					$bundleMenuItem->insert();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Menu item added',
						'dd_toasts' => array(
							array('message' => 'Menu item added.')
						)
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Bundle id and/or Menu Item id was not supplied'
				));
				exit;
			}
		}
	}
}

?>