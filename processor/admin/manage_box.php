<?php
require_once("CTemplate.inc");

class processor_admin_manage_box extends CPageProcessor
{

	function __construct()
	{
		// autoclean will mess up a json array
		$this->inputTypeMap['stores'] = TYPE_NOCLEAN;
		$this->doGenericInputCleaning = false;
	}

	function runSiteAdmin()
	{
		$this->runManageBundle();
	}

	function runHomeOfficeManager()
	{
		$this->runManageBundle();
	}

	function runFranchiseOwner()
	{
		$this->runManageBundle();
	}

	function runFranchiseManager()
	{
		$this->runManageBundle();
	}

	function runOpsLead()
	{
		$this->runManageBundle();
	}

	/**
	 * @throws Exception
	 */
	function runManageBundle()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (!empty($_POST['op']) && $_POST['op'] == 'deploy_box')
		{
			if (is_numeric($_POST['box_id']))
			{
				$storeArray = json_decode($_POST['stores'], true);

				foreach ($storeArray as $store_id)
				{
					$box = DAO_CFactory::create('box');
					$box->id = $_POST['box_id'];
					$box->store_id = 'NULL';
					$box->find(true);

					if (is_numeric($store_id))
					{
						$box->copyBoxToStore($store_id);
					}
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Box deployed',
					'dd_toasts' => array(
						array('message' => 'Box deployed.')
					)
				));
			}
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'expire_box')
		{
			if (is_numeric($_POST['box_id']))
			{
				$box = DAO_CFactory::create('box');
				$box->id = $_POST['box_id'];
				$box->find(true);

				$boxUpdated = clone $box;

				$boxUpdated->availability_date_end = CTemplate::formatDateTime();

				$boxUpdated->update($box);

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Box expired',
					'dd_toasts' => array(
						array('message' => 'Box expired.')
					)
				));
			}
		}

		if (!empty($_POST['_form_name']) && $_POST['_form_name'] == 'BoxForm')
		{
			// if the id is numeric, we are updating an existing box
			if ($_POST['id'] == 'new' || is_numeric($_POST['id']))
			{
				$box = DAO_CFactory::create('box');

				if (is_numeric($_POST['id']))
				{
					$box->id = $_POST['id'];
					$box->find(true);
				}

				$boxUpdated = clone($box);

				foreach (DAO_CFactory::create('box')->table() as $key => $value)
				{
					if (isset($_POST[$key]))
					{
						switch ($key)
						{
							case 'availability_date_start':
							case 'availability_date_end':
								$boxUpdated->{$key} = CTemplate::dateTimeFormat($_POST[$key], MYSQL_TIMESTAMP);
								break;
							case 'is_visible_to_customer':
								$boxUpdated->{$key} = 1;
								break;
							default:
								$boxUpdated->{$key} = $_POST[$key];
								break;
						}
					}

					if (!isset($_POST['is_visible_to_customer']))
					{
						$boxUpdated->{'is_visible_to_customer'} = 0;
					}
				}

				if ($_POST['id'] == 'new')
				{
					$boxUpdated->insert();
					$box = $boxUpdated;
				}
				else
				{
					$boxUpdated->update($box);
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Box information updated',
					'data' => array(
						'form_name' => 'BoxForm',
						'box_id' => $box->id,
						'bounce' => ($_POST['id'] == 'new')
					),
					'dd_toasts' => array(
						array('message' => 'Box information updated.')
					)
				));
			}
		}

		if (!empty($_POST['_form_name']) && ($_POST['_form_name'] == 'Bundle1Form' || $_POST['_form_name'] == 'Bundle2Form'))
		{
			// if the id is numeric, we are updating an existing box
			if (is_numeric($_POST['id']))
			{
				$bundle = DAO_CFactory::create('bundle');
				$bundle->id = $_POST['id'];

				if ($bundle->find(true))
				{
					$bundleUpdated = clone($bundle);

					foreach (DAO_CFactory::create('bundle')->table() as $key => $value)
					{
						if (isset($_POST[$key]))
						{
							switch ($key)
							{
								default:
									$bundleUpdated->{$key} = $_POST[$key];
									break;
							}
						}
					}

					$bundleUpdated->update($bundle);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Box information updated',
						'data' => array(
							'form_name' => $_POST['_form_name'],
							'bundle_id' => $bundle->id
						),
						'dd_toasts' => array(
							array('message' => 'Box information updated.')
						)
					));
				}
			}
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'set_bundle_active_state')
		{
			if (is_numeric($_POST['box_id']))
			{
				$box = DAO_CFactory::create('box');
				$box->id = $_POST['box_id'];

				if ($box->find(true))
				{
					$boxUpdated = clone $box;

					if ($_POST['bundle_id'] == 'new')
					{
						$bundle = DAO_CFactory::create('bundle');
						$bundle->bundle_type = CBundle::DELIVERED;
						$bundle->bundle_name = 'Temporary Shipping Bundle';
						$bundle->menu_id = $box->menu_id;
						$bundle->insert();

						$boxUpdated->{$_POST['box_bundle']} = $bundle->id;
					}

					$boxUpdated->{$_POST['box_bundle'] . '_active'} = (($_POST['checked'] == 'true') ? 1 : 0);
					$boxUpdated->update($box);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Box ' . (($_POST['checked'] == 'true') ? 'added.' : 'removed.'),
						'dd_toasts' => array(
							array('message' => 'Box ' . (($_POST['checked'] == 'true') ? 'added.' : 'removed.'))
						),
						'data' => array(
							'box_bundle' => $_POST['box_bundle'],
							'bundle_id' => $bundle->id
						)
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Bundle id was not supplied.'
				));
			}
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'update_bundle_menu_item')
		{
			if (is_numeric($_POST['bundle_id']) && is_numeric($_POST['bundle_menu_item_id']))
			{
				$bundleMenuItem = DAO_CFactory::create('bundle_to_menu_item');
				$bundleMenuItem->bundle_id = $_POST['bundle_id'];
				$bundleMenuItem->menu_item_id = $_POST['bundle_menu_item_id'];

				if ($bundleMenuItem->find(true))
				{
					$bundleMenuItemUpdated = clone($bundleMenuItem);
					$bundleMenuItemUpdated->current_offering = (($_POST['checked'] == 'true') ? 1 : 0);
					$bundleMenuItemUpdated->update($bundleMenuItem);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu item ' . (($_POST['checked'] == 'true') ? 'added.' : 'removed.'),
						'dd_toasts' => array(
							array('message' => 'Menu item ' . (($_POST['checked'] == 'true') ? 'added.' : 'removed.'))
						)
					));
				}
				else
				{
					$bundleMenuItem->insert();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu item added',
						'dd_toasts' => array(
							array('message' => 'Menu item added.')
						)
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Bundle id was not supplied'
				));
			}
		}
	}
}

?>