<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/DAO/BusinessObject/CBox.php');
require_once('includes/DAO/BusinessObject/CBoxInstance.php');
require_once('includes/CLog.inc');

class page_box_menu extends CPage
{

	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runBoxItemsPage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$this->runBoxItemsPage($tpl);
	}

	/**
	 * @throws Exception
	 */
	function runBoxItemsPage()
	{
		if (!empty($_POST['view_box']) && is_numeric($_POST['view_box']) && !empty($_POST['view_bundle']) && is_numeric($_POST['view_bundle']))
		{
			$CartObj = CCart2::instance();
			$storeId = $CartObj->getStoreId();

			if (empty($storeId))
			{
				CApp::bounce('/locations');
			}

			$boxInfoArray = CBox::getBoxArray($storeId, $_POST['view_box'], true, true, false, false, true);

			$boxInfo = $boxInfoArray['box'][$_POST['view_box']];

			$boxBundleInfo = $boxInfo->bundle[$_POST['view_bundle']];

			if ($boxBundleInfo->info["out_of_stock"])
			{
				$this->Template->setStatusMsg("We are sorry requested box is currently out of stock.");
				CApp::bounce('/box-select');
			}

			$recipeIDtoInventoryMap = CBox::addDeliveredInventoryToMenuArray($boxInfoArray, $storeId, false);
		}
		else
		{
			CApp::bounce('/box-select');
		}

		$boxInstance = CBoxInstance::getIncompleteBoxInstance($boxBundleInfo->id, $boxInfo->id);

		$boxInstanceId = 0;
		if (!empty($boxInstance))
		{
			$boxInstanceId = $boxInstance->id;

			$CartObj = CCart2::instance();
			$CartObj->restoreContents();

			$orderObj = $CartObj->getOrder();
			$boxes = $orderObj->getBoxes();

			$boxInstance = $boxes[$boxInstanceId];
		}

		$boxInfoJS = array(
			'box_id' => $boxInfo->id,
			'box_type' => $boxInfo->box_type,
			'box_bundle_id' => $boxBundleInfo->id,
			'box_instance_id' => $boxInstanceId,
			'number_items_required' => intval($boxBundleInfo->number_items_required),
			'number_servings_required' => intval($boxBundleInfo->number_servings_required),
			'custom_box' => array(
				'info' => array(
					'number_items' => 0,
					'number_servings' => 0
				),
				'items' => array(),
			)
		);

		if (!empty($boxInstance))
		{
			foreach ($boxInstance['items'] AS $mid => $item)
			{
				$boxInfoJS['custom_box']['items'][$mid] = intval($item[0]);
				$boxInfoJS['custom_box']['info']['number_items'] = count($boxInstance['items']);
				$boxInfoJS['custom_box']['info']['number_servings'] += $item[1]->servings_per_item;
			}
		}
		else if ($boxInfo->box_type == CBox::DELIVERED_FIXED)
		{
			foreach ($boxBundleInfo->menu_item['items'] AS $item)
			{
				foreach ($item AS $mid => $DAO_menu_item)
				{
					if (!key_exists($mid, $boxInfoJS['custom_box']['items']))
					{
						$boxInfoJS['custom_box']['items'][$mid] = 1;
					}
					else
					{
						$boxInfoJS['custom_box']['items'][$mid] += 1;
					}

					$boxInfoJS['custom_box']['info']['number_items'] += 1;
					$boxInfoJS['custom_box']['info']['number_servings'] += $DAO_menu_item->servings_per_item;
				}
			}
		}

		$this->Template->assign('box_info', $boxInfo);
		$this->Template->assign('box_bundle_info', $boxBundleInfo);
		$this->Template->assign('box_instance', $boxInstance);
		$this->Template->assign('cart_info', CUser::getCartIfExists());

		$this->Template->setScriptVar('let menuItemInfo = ' . json_encode($boxBundleInfo->info['menuItemInfoByMID']) . ';');
		$this->Template->setScriptVar('let box_info = ' . json_encode($boxInfoJS, JSON_FORCE_OBJECT) . ';');
	}
}