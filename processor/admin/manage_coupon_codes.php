<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CCouponCode.php');

class processor_admin_manage_coupon_codes extends CPageProcessor
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

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'edit_coupon_update')
		{
			if ($_REQUEST['coupon_id'] && (is_numeric($_REQUEST['coupon_id']) || $_REQUEST['coupon_id'] == 'new'))
			{
				if ($_REQUEST['coupon_id'] == 'new')
				{
					$coupon = DAO_CFactory::create('coupon_code');
				}
				else
				{
					$coupon = CCouponCode::getCouponDetails($_REQUEST['coupon_id']);
					$orgCoupon = clone($coupon);

					$couponArray = CCouponCode::getCouponDetailsArray($coupon->id);

					$coupon->is_store_specific_ids = $couponArray[$coupon->id]->is_store_specific_ids;
				}

				$corporateCrateClientArray = array();
				$storeSpecificArray = array();

				foreach ($_REQUEST['data'] AS $key => $couponProp)
				{
					switch (true)
					{
						case $couponProp['name'] === 'valid_timespan_start':
							$coupon->$couponProp['name'] = $couponProp['value'] . ' 00:00:00';
							break;
						case $couponProp['name'] === 'valid_timespan_end':
							$coupon->$couponProp['name'] = $couponProp['value'] . ' 23:59:59';
							break;
						case stristr($couponProp['name'], 'valid_corporate_crate_client_id'):
							$client_id = substr($couponProp['name'], 32, -1);
							$corporateCrateClientArray[] = $client_id;
							break;
						case stristr($couponProp['name'], 'is_store_specific_id'):
							$store_id = substr($couponProp['name'], 21, -1);
							$storeSpecificArray[] = $store_id;
							break;
						case stristr($couponProp['name'], 'valid_for_product_type_membership'):
							$coupon->valid_for_product = $couponProp['value'];
							$coupon->$couponProp['name'] = $couponProp['value'];
							break;
						default:
							$coupon->$couponProp['name'] = $couponProp['value'];
							break;
					}
				}

				if (!empty($corporateCrateClientArray))
				{
					if (in_array('ALL', $corporateCrateClientArray))
					{
						$coupon->valid_corporate_crate_client_id = 'ALL';
					}
					else
					{
						$coupon->valid_corporate_crate_client_id = implode(',', $corporateCrateClientArray);
					}
				}
				else
				{
					$coupon->valid_corporate_crate_client_id = null;
				}

				// both stores and is_store_specific need to be selected
				if (empty($storeSpecificArray) || empty($coupon->is_store_specific))
				{
					$coupon->is_store_specific = 0;
					$storeSpecificArray = array();
				}

				// do insert/update
				if ($_REQUEST['coupon_id'] == 'new')
				{
					$existingCheck = DAO_CFactory::create('coupon_code');
					$existingCheck->coupon_code = $coupon->coupon_code;
					$existingCheck->find();

					if (!empty($existingCheck->N))
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Coupon code "' . $existingCheck->coupon_code . '" already exists.'
						));
					}
					else
					{
						$coupon->insert();
					}
				}
				else
				{
					$existingCheck = DAO_CFactory::create('coupon_code');
					$existingCheck->coupon_code = $coupon->coupon_code;
					$existingCheck->whereAdd("id != " . $coupon->id);
					$existingCheck->find();

					if (!empty($existingCheck->N))
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Coupon code "' . $existingCheck->coupon_code . '" already exists.'
						));
					}
					else
					{
						$coupon->update($orgCoupon);
					}

				}

				// not is_store_specific, delete any that exist
				if ($coupon->is_store_specific == 0)
				{
					$couponStore = DAO_CFactory::create('coupon_to_store');
					$couponStore->coupon_code_id = $coupon->id;
					$couponStore->find();

					while ($couponStore->fetch())
					{
						$couponStore->delete();
					}
				}
				else
				{
					// add new IDs
					$newStoreIDs = array_diff($storeSpecificArray, $coupon->is_store_specific_ids);

					if (!empty($newStoreIDs))
					{
						foreach ($newStoreIDs AS $store_id)
						{
							$couponToStore = DAO_CFactory::create('coupon_to_store');
							$couponToStore->coupon_code_id = $coupon->id;
							$couponToStore->store_id = $store_id;
							$couponToStore->insert();
						}
					}

					// remove old IDs
					$oldStoreIDs = array_diff($coupon->is_store_specific_ids, $storeSpecificArray);

					if (!empty($oldStoreIDs))
					{
						foreach ($oldStoreIDs AS $store_id)
						{
							$couponToStore = DAO_CFactory::create('coupon_to_store');
							$couponToStore->coupon_code_id = $coupon->id;
							$couponToStore->store_id = $store_id;
							$couponToStore->find();

							while ($couponToStore->fetch())
							{
								$couponToStore->delete();
							}
						}
					}
				}

				if ($_REQUEST['coupon_id'] == 'new')
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Coupon details added.',
						'bounce_to' => '/?page=admin_manage_coupon_codes'
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Coupon details updated.',
						'bounce_to' => '/?page=admin_manage_coupon_codes'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Coupon id was not supplied'
				));
			}
		}

		if (!empty($_POST['op']) && $_POST['op'] == 'add_coupon')
		{
			$tpl = new CTemplate();

			$coupon = DAO_CFactory::create('coupon_code');
			$coupon->defaultValues();

			$programArray = CCouponCode::getCouponProgramArray();
			$corporateClientArray = CCouponCode::getCorporateClientArray();
			$storeListArray = CStore::getListOfStores(true);
			$menuArray = CMenu::menuInfoArray(false, false, 'DESC', 5);

			$tpl->assign('coupon', $coupon);
			$tpl->assign('programArray', $programArray);
			$tpl->assign('corporateClientArray', $corporateClientArray);
			$tpl->assign('storeListArray', $storeListArray);
			$tpl->assign('menuArray', $menuArray);

			$coupon_html = $tpl->fetch('admin/subtemplate/manage_coupon_codes_edit_coupon.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Coupon details',
				'coupon_html' => $coupon_html
			));
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'edit_coupon')
		{
			if ($_REQUEST['coupon_id'] && is_numeric($_REQUEST['coupon_id']))
			{
				$coupon_id = $_REQUEST['coupon_id'];

				$tpl = new CTemplate();

				$couponArray = CCouponCode::getCouponDetailsArray($coupon_id);
				$programArray = CCouponCode::getCouponProgramArray();
				$corporateClientArray = CCouponCode::getCorporateClientArray();
				$storeListArray = CStore::getListOfStores(true);
				$menuArray = CMenu::menuInfoArray(false, false, 'DESC', 5);

				$tpl->assign('coupon', $couponArray[$coupon_id]);
				$tpl->assign('programArray', $programArray);
				$tpl->assign('corporateClientArray', $corporateClientArray);
				$tpl->assign('storeListArray', $storeListArray);
				$tpl->assign('menuArray', $menuArray);

				$coupon_html = $tpl->fetch('admin/subtemplate/manage_coupon_codes_edit_coupon.tpl.php');

				if (!empty($couponArray))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Coupon details',
						'coupon_html' => $coupon_html
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Coupon info not found'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Coupon id was not supplied'
				));
			}
		}
	}
}

?>