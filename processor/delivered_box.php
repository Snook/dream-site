<?php
require_once("includes/CPageProcessor.inc");
require_once('includes/DAO/BusinessObject/CBox.php');
require_once('includes/DAO/BusinessObject/CBoxInstance.php');

class processor_delivered_box extends CPageProcessor
{
	function runPublic()
	{
		$this->runProcessor();
	}

	function runCustomer()
	{
		$this->runProcessor();
	}

	function runProcessor()
	{

		header('Content-type: application/json');

		if (isset($_POST['op']) && $_POST['op'] == 'remove')
		{
			if (!empty($_POST['box_instance_id']) && is_numeric($_POST['box_instance_id']))
			{
				$post_box_id = $_POST['box_instance_id'];
			}

			if (!empty($post_box_id))
			{
				$CartObj = CCart2::instance();

				$orderBoxes = $CartObj->getOrder()->getBoxes();
				$removedBoxInfo = $orderBoxes[$post_box_id];

				$box_data = array(
					'bundle_obj' => $removedBoxInfo['bundle']
				);

				$CartObj->removeDeliveredBox($post_box_id, $box_data);

				$CartObj = CCart2::instance(true, false, true);

				// check if the box is now available in case it was marked as out of stock
				$boxAvailable = CBox::quickCheckForBoxAvailable($CartObj->getStoreId(), $removedBoxInfo['box_instance']->box_id, $CartObj);

				$cart_info = $CartObj->getCartArrays();

				$retVal = array(
					'total_items_price' => CTemplate::moneyFormat($cart_info['cart_info_array']['total_items_price']),
					'box_info' => array(
						'box_id' => $removedBoxInfo['box_instance']->box_id,
						'bundle_id' => $removedBoxInfo['box_instance']->bundle_id,
						'price' => $removedBoxInfo['bundle']->price,
						'bundle_name' => $removedBoxInfo['bundle']->bundle_name,
						'out_of_stock' => !$boxAvailable
					)
				);

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Box removed from cart.',
					'data' => $retVal

				));
			}
		}

		if (isset($_POST['op']) && $_POST['op'] == 'update_address')
		{
			$CartObj = CCart2::instance();
			$UserObj = CUser::getCurrentUser();

			if (isset($_POST['id']) && is_numeric($_POST['id']))
			{
				$addressBook = $UserObj->getAddressBookArray();

				if (empty($addressBook[$_POST['id']]))
				{
					$billingAddress = DAO_CFactory::create('address');
					$billingAddress->query("select * from address where location_type = 'BILLING' and id = {$_POST['id']} and is_deleted = 0");
					if ($billingAddress->fetch())
					{
						$address = $billingAddress;
					}
					else
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Unexpected Error:  Billing address not found.',
							'status' => 'unknown error',
						));
					}
				}
				else
				{
					$address = $addressBook[$_POST['id']];
				}

				$addressData = array(
					'id' => $address->id,
					'location_type' => $address->location_type,
					'firstname' => $address->firstname,
					'lastname' => $address->lastname,
					'address_line1' => $address->address_line1,
					'address_line2' => $address->address_line2,
					'city' => $address->city,
					'state_id' => $address->state_id,
					'postal_code' => $address->postal_code,
					'telephone_1' => $address->telephone_1,
					'email_address' => $address->email_address,
					'address_note' => $address->address_note
				);

				$ckDistro = DAO_CFactory::create('zipcodes');
				$ckDistro->zip = $address->postal_code;
				$ckDistro->whereAdd("zipcodes.distribution_center IS NOT NULL");

				// check that zip code is deliverable
				if ($ckDistro->find(true))
				{
					// distro is the same, no problem
					if ($CartObj->getStoreId() == $ckDistro->distribution_center)
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Address updated, dc same.',
							'status' => 'address_update',
							'address' => json_encode($addressData)
						));
					}
					else
						// distro is different, change the store and catch inventory issue
					{
						// TODO: inventory check AND delivery day
						// inventory good, change the distro and keep them on the checkout page
						if (COrdersDelivered::cartInventoryCheck($CartObj, $ckDistro->distribution_center))
						{
							// TODO: Once DCs have their own bundles we'll need to switch the bundle as well

							$CartObj->storeChangeEvent($ckDistro->distribution_center);

							CAppUtil::processorMessageEcho(array(
								'processor_success' => true,
								'processor_message' => 'Address updated, dc changed.',
								'status' => 'store_change',
								'address' => json_encode($addressData)
							));
						}
						else
							// Inventory not available, need to send them to the start of the order process
						{
							CAppUtil::processorMessageEcho(array(
								'processor_success' => true,
								'processor_message' => 'Inventory not available, restart order process.',
								'status' => 'no_inventory',
								'address' => json_encode($addressData)
							));
						}
					}
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Address not eligible for delivery.',
						'status' => 'not_eligible',
						'address' => json_encode($addressData)
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Update to address not in address book.',
					'status' => 'unknown error',
				));
			}
		}

		if (isset($_POST['op']) && $_POST['op'] == 'add')
		{
			try
			{
				$CartObj = CCart2::instance(true);

				if (!empty($_POST['box_id']) && is_numeric($_POST['box_id']) && !empty($_POST['bundle_id']) && is_numeric($_POST['bundle_id']))
				{
					$post_box_id = $_POST['box_id'];
					$post_bundle_id = $_POST['bundle_id'];
				}

				$DAO_box = DAO_CFactory::create('box', true);
				$DAO_box->id = $post_box_id;
				$DAO_box->find(true);

				$DAO_bundle = DAO_CFactory::create('bundle', true);
				$DAO_bundle->id = $post_bundle_id;
				$DAO_bundle->find(true);

				$items = CBundle::getDeliveredBundleByID($DAO_bundle->id, true);

				$boxInfoArray = CBox::getBoxArray($CartObj->getStoreId(), $post_box_id, true, true, false, false, true);

				$boxInfo = $boxInfoArray['box'][$post_box_id];

				$boxBundleInfo = $boxInfo->bundle[$post_bundle_id];

				if ($boxBundleInfo->info["out_of_stock"])
				{
					$this->Template->setStatusMsg("We are sorry requested box is currently out of stock.");

					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'We are sorry requested box is currently out of stock',
						'bounce_to' => '/box-select'
					));
				}

				// add fixed box
				if (empty($_POST['box_instance_id']))
				{
					$boxInstanceID = CBoxInstance::getNewEmptyBoxForBundle($DAO_bundle->id, $post_box_id, false, false, true);

					foreach ($items['bundle'] as $mid => $item)
					{
						$itemArray[$mid] = 1;
					}

					$data = array(
						'bundle_obj' => $DAO_bundle,
						'bundle_id' => $DAO_bundle->id,
						'items' => $itemArray
					);

					$CartObj->addDeliveredBox($boxInstanceID, $data);
					$CartObj->addMenuId($DAO_bundle->menu_id);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Box added to cart.'
					));
				}
				// add custom box
				else if (is_numeric($_POST['box_instance_id']))
				{

					$boxInstanceObj = DAO_CFactory::create('box_instance');
					$boxInstanceObj->id = $_POST['box_instance_id'];
					$boxInstanceObj->find(true);
					$boxInstanceObjOrig = clone($boxInstanceObj);
					$boxInstanceObj->is_in_edit_mode = 0;
					$boxInstanceObj->is_complete = 1;
					$boxInstanceObj->update($boxInstanceObjOrig);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Box added to cart.'
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Error processing delivered box request: Invalid box instance ID'
					));
				}
			}
			catch (Exception $e)
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 3,
					'processor_message' => 'Unexpected error when updating menu item.'
				));
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'The item was successfully updated.'
			));
		}

		if (isset($_POST['op']) && $_POST['op'] == 'update')
		{
			if (empty($_POST['qty']) || $_POST['qty'] === "" || $_POST['qty'] < 0)
			{
				$_POST['qty'] = 0;
			}

			if (is_numeric($_POST['qty']))
			{
				$post_qty = $_POST['qty'];
			}

			if (!empty($_POST['menu_item_id']) && is_numeric($_POST['menu_item_id']))
			{
				$post_menu_item_id = $_POST['menu_item_id'];
			}

			try
			{
				$CartObj = CCart2::instance(true);

				$DAO_box = DAO_CFactory::create('box');
				$DAO_box->id = $_POST['box_id'];
				$DAO_box->find(true);

				$DAO_bundle = DAO_CFactory::create('bundle');
				$DAO_bundle->id = $_POST['bundle_id'];
				$DAO_bundle->find(true);

				$CartObj->addMenuId($DAO_bundle->menu_id);

				$items = CBundle::getDeliveredBundleByID($DAO_bundle->id);

				$boxInstanceID = false;
				$boxAddNew = false;
				if (empty($_POST['box_instance_id']))
				{
					$boxInstanceID = CBoxInstance::getNewEmptyBoxForBundle($DAO_bundle->id, $DAO_box->id, false, true);
					$boxAddNew = true;
				}
				else if (is_numeric($_POST['box_instance_id']))
				{
					$boxInstanceID = $_POST['box_instance_id'];
					$boxAddNew = false;
				}

				if (!empty($boxInstanceID))
				{

					if (!empty($post_qty))
					{
						if ($boxAddNew)
						{
							$data = array(
								'bundle_id' => $DAO_bundle->id,
								'items' => array()
							);
							$CartObj->addDeliveredBox($boxInstanceID, $data);
						}

						$CartObj->addItemToDeliveredBox($boxInstanceID, $post_menu_item_id, $post_qty);

						$CartObj = CCart2::instance(true, false, true);

						$tpl = new CTemplate();

						$orderObj = $CartObj->getOrder();
						$boxes = $orderObj->getBoxes();

						$cart_info_item_info = $boxes[$boxInstanceID]['items'][$post_menu_item_id];

						$tpl->assign('processor_cart_info_item_info', $cart_info_item_info);

						$cart_update = $tpl->fetch('customer/subtemplate/box_menu/box_menu_menu_cart_item.tpl.php');

						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Update box item.',
							'cart_box_instance_id' => $boxInstanceID,
							'cart_update' => $cart_update
						));
					}
					else
					{
						// menu item was set to zero, removed item from cart
						$CartObj->addItemToDeliveredBox($boxInstanceID, $post_menu_item_id, $post_qty);

						// menu item was set to zero, removed item from cart
						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Removed box item.',
							'cart_box_instance_id' => $boxInstanceID,
						));
					}
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Error processing delivered box request: Invalid box instance ID'
					));
				}
			}
			catch (Exception $e)
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 3,
					'processor_message' => 'Unexpected error when updating menu item.'
				));
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'The item was successfully updated.'
			));
		}
	}

	function setEditMode($boxInstanceID, $newMode)
	{
		if (empty($newMode))
		{
			$newMode = 0;
		}
		else
		{
			$newMode = 1;
		}

		$instUpdater = new DAO();
		$instUpdater->query("update box_instance set is_in_edit_mode = $newMode where id = {$boxInstanceID} and is_deleted = 0");
	}

	function setCompleteStatus($boxInstanceID, $completeStatus)
	{
		if (empty($completeStatus))
		{
			$newStatus = 0;
		}
		else
		{
			$newStatus = 1;
		}

		$instUpdater = new DAO();
		$instUpdater->query("update box_instance set is_complete = $newStatus where id = {$boxInstanceID} and is_deleted = 0");
	}

}