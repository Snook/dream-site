<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CCouponCode.php');

class page_admin_manage_coupon_codes extends CPageAdminOnly
{
	public $CouponForm = null;

	private $singleStore = false;

	public function __construct()
	{
		parent::__construct();

		$this->CouponForm = new CForm();
		$this->CouponForm->Repost = true;
		$this->CouponForm->Bootstrap = true;
		$this->CouponForm->ElementID = true;
	}

	function runHomeOfficeManager()
	{
		$this->manageCoupons();
	}

	function runSiteAdmin()
	{
		$this->manageCoupons();
	}

	function manageCoupons()
	{
		$editCoupon = CGPC::do_clean((!empty($_GET['edit']) ? $_GET['edit'] : false), TYPE_INT);
		$createCoupon = CGPC::do_clean((!empty($_GET['create']) ? $_GET['create'] : false), TYPE_BOOL);
		$searchCode = CGPC::do_clean((!empty($_POST['search_code']) ? $_POST['search_code'] : false), TYPE_STR);

		if (!empty($searchCode))
		{
			$couponSearch = DAO_CFactory::create('coupon_code');
			$couponSearch->coupon_code = $searchCode;

			if ($couponSearch->find(true))
			{
				CApp::bounce('/backoffice/manage_coupon_codes?edit=' . $couponSearch->id);
			}
			else
			{
				$this->Template->setErrorMsg('Coupon not found.');
			}
		}

		$couponOrders = array(
			'count' => 0,
			'hasOrders' => false
		);

		if (!empty($_POST['submit']))
		{
			$DAO_coupon_code = DAO_CFactory::create('coupon_code');

			$corporateCrateClientArray = array();

			foreach ($_POST as $key => $couponValue)
			{
				// clean up any extra spaces
				$couponValue = trim($couponValue);

				switch ($key)
				{
					case 'limit_coupon':
						$DAO_coupon_code->$couponValue = 1;
						break;
					case 'valid_timespan_start':
						$DAO_coupon_code->$key = $couponValue . ' 00:00:00';
						break;
					case 'valid_timespan_end':
						$DAO_coupon_code->$key = $couponValue . ' 23:59:59';
						break;
					case 'valid_corporate_crate_client_id':
						$client_id = substr($key, 32, -1);
						$corporateCrateClientArray[] = $client_id;
						break;
					case 'multi_store_select':
						if (!empty($_POST['is_store_specific']))
						{
							$DAO_coupon_code->is_store_specific_ids = explode(',', $couponValue);
						}
						break;
					case 'is_store_specific':
						if (empty($_POST['multi_store_select']))
						{
							$DAO_coupon_code->$key = 0;
						}
						else
						{
							$DAO_coupon_code->$key = $couponValue;
						}
						break;
					case 'recipe_id_pricing_type':
						if (empty($_POST['recipe_id']))
						{
							$DAO_coupon_code->recipe_id_pricing_type = 'NULL';
						}
						else
						{
							$DAO_coupon_code->$key = $couponValue;
						}
						break;
					default:
						$DAO_coupon_code->$key = $couponValue;
						break;
				}
			}

			if (!empty($corporateCrateClientArray))
			{
				if (in_array('ALL', $corporateCrateClientArray))
				{
					$DAO_coupon_code->valid_corporate_crate_client_id = 'ALL';
				}
				else
				{
					$DAO_coupon_code->valid_corporate_crate_client_id = implode(',', $corporateCrateClientArray);
				}
			}
			else
			{
				$DAO_coupon_code->valid_corporate_crate_client_id = null;
			}

			// do insert/update
			$couponUpsert = false;
			$orgCouponStoreSpecificIds = array();

			if ($_POST['id'] == 'new_coupon')
			{
				$existing_DAO_coupon_code = DAO_CFactory::create('coupon_code');
				$existing_DAO_coupon_code->coupon_code = $DAO_coupon_code->coupon_code;
				$existing_DAO_coupon_code->find();

				if (!empty($existing_DAO_coupon_code->N))
				{
					$this->Template->SetErrorMsg('Coupon code ' . $existing_DAO_coupon_code->coupon_code . ' already exists.');
				}
				else
				{
					$couponUpsert = true;
					$DAO_coupon_code->insert();
				}
			}
			else
			{
				$orgCoupon = CCouponCode::getCouponDetails($DAO_coupon_code->id);

				$orgCouponStoreSpecificIds = $orgCoupon->is_store_specific_ids;

				$existing_DAO_coupon_code = DAO_CFactory::create('coupon_code');
				$existing_DAO_coupon_code->coupon_code = $DAO_coupon_code->coupon_code;
				$existing_DAO_coupon_code->whereAdd("id != " . $DAO_coupon_code->id);
				$existing_DAO_coupon_code->find();

				if (!empty($existing_DAO_coupon_code->N))
				{
					$this->Template->SetErrorMsg('Coupon code ' . $existing_DAO_coupon_code->coupon_code . ' already exists.');
				}
				else
				{
					$couponUpsert = true;
					$DAO_coupon_code->update($orgCoupon);
				}
			}

			// handle store specific coupons
			if ($couponUpsert)
			{
				// not is_store_specific, delete any that exist
				if (empty($DAO_coupon_code->is_store_specific))
				{
					$couponStore = DAO_CFactory::create('coupon_to_store');
					$couponStore->coupon_code_id = $DAO_coupon_code->id;
					$couponStore->find();

					while ($couponStore->fetch())
					{
						$couponStore->delete();
					}
				}
				else
				{
					// add new IDs
					$newStoreIDs = array_diff($DAO_coupon_code->is_store_specific_ids, $orgCouponStoreSpecificIds);

					if (!empty($newStoreIDs))
					{
						foreach ($newStoreIDs as $store_id)
						{
							$couponToStore = DAO_CFactory::create('coupon_to_store');
							$couponToStore->coupon_code_id = $DAO_coupon_code->id;
							$couponToStore->store_id = $store_id;
							$couponToStore->insert();
						}
					}

					// remove old IDs
					$oldStoreIDs = array_diff($orgCouponStoreSpecificIds, $DAO_coupon_code->is_store_specific_ids);

					if (!empty($oldStoreIDs))
					{
						foreach ($oldStoreIDs as $store_id)
						{
							$couponToStore = DAO_CFactory::create('coupon_to_store');
							$couponToStore->coupon_code_id = $DAO_coupon_code->id;
							$couponToStore->store_id = $store_id;
							$couponToStore->find();

							while ($couponToStore->fetch())
							{
								$couponToStore->delete();
							}
						}
					}
				}
			}

			CApp::bounce('/backoffice/manage_coupon_codes');
		}

		$programArray = CCouponCode::getCouponProgramArray();
		$menuArray = CMenu::getAllMenus();

		if ($editCoupon)
		{
			$DAO_coupon_code = CCouponCode::getCouponDetails($editCoupon);

			$orders = DAO_CFactory::create('orders');
			$orders->coupon_code_id = $DAO_coupon_code->id;

			$booking = DAO_CFactory::create('booking');
			$booking->joinAddWhereAsOn($orders);
			$booking->find();
			$couponOrders = array(
				'count' => $booking->N,
				'hasOrders' => (!empty($booking->N))
			);

			$this->CouponForm->DefaultValues['limit_coupon'] = 'limit_to_grand_total';

			foreach (DAO_CFactory::create('coupon_code')->table() as $key => $value)
			{
				switch ($key)
				{
					case 'valid_session_timespan_start':
					case 'valid_session_timespan_end':
					case 'valid_timespan_start':
					case 'valid_timespan_end':
					case 'remiss_cutoff_date':
						if (!empty($DAO_coupon_code->{$key}))
						{
							$this->CouponForm->DefaultValues[$key] = CTemplate::formatDateTime('Y-m-d', $DAO_coupon_code->{$key}); // remove seconds to reduce form complexity
						}
						break;
					case 'limit_to_grand_total':
					case 'limit_to_core':
					case 'limit_to_core_and_efl':
					case 'limit_to_finishing_touch':
					case 'limit_to_mfy_fee':
					case 'limit_to_recipe_id':
					case 'limit_to_delivery_fee':
						if (!empty($DAO_coupon_code->{$key}))
						{
							$this->CouponForm->DefaultValues['limit_coupon'] = $key;
						}
						break;
					default:
						$this->CouponForm->DefaultValues[$key] = $DAO_coupon_code->{$key};
						break;
				}
			}

			if (!empty($DAO_coupon_code->is_store_specific))
			{
				$this->CouponForm->DefaultValues['multi_store_select'] = implode(',', $DAO_coupon_code->is_store_specific_ids);
			}

			$this->Template->assign('couponOrders', $couponOrders);
			$this->Template->assign('createCoupon', false);
			$this->Template->assign('editCoupon', true);
			$this->Template->assign('singleStore', $this->singleStore);
		}
		else if (!empty($createCoupon))
		{
			$this->CouponForm->DefaultValues['id'] = 'new_coupon';
			$this->CouponForm->DefaultValues['use_limit_per_account'] = '1';
			$this->CouponForm->DefaultValues['limit_coupon'] = 'limit_to_grand_total';
			$this->CouponForm->DefaultValues['valid_for_session_type_standard'] = '1';
			$this->CouponForm->DefaultValues['valid_for_order_type_standard'] = '1';
			$this->CouponForm->DefaultValues['valid_for_product_type_membership'] = '1';
			$this->CouponForm->DefaultValues['is_order_editor_supported'] = '1';
			$this->CouponForm->DefaultValues['is_customer_order_supported'] = '1';

			$this->Template->assign('couponOrders', $couponOrders);
			$this->Template->assign('createCoupon', true);
			$this->Template->assign('editCoupon', false);
			$this->Template->assign('singleStore', $this->singleStore);
		}
		else
		{
			$couponArray = CCouponCode::getCouponDetailsArray();

			foreach ($couponArray as $DAO_coupon_code)
			{
				if ($DAO_coupon_code->valid_timespan_end <= CTemplate::unix_to_mysql_timestamp(time()))
				{
					$couponArray['expired'][$DAO_coupon_code->id] = clone($DAO_coupon_code);
				}
				else
				{
					$couponArray['current'][$DAO_coupon_code->id] = clone($DAO_coupon_code);
				}
			}

			$this->Template->assign('couponArray', $couponArray);
			$this->Template->assign('programArray', $programArray);
		}

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'id'
		));

		$programOptions = array('null' => 'Select Program');

		foreach ($programArray as $id => $program)
		{
			$programOptions[$id] = $program->program_name;
		}

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'program_id',
			CForm::required => true,
			CForm::options => $programOptions
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_store_coupon',
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_store_specific',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$isStoreSpecific = $this->CouponForm->Value('is_store_specific');
		$this->CouponForm->addElement(array(
			CForm::type => CForm::ButtonMultiStore,
			CForm::name => 'multi_store_select',
			CForm::text => 'Stores',
			CForm::disabled => (empty($isStoreSpecific) ? true : false),
			CForm::css_class => 'btn btn-primary'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_delivered_coupon',
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'coupon_code',
			CForm::required => true,
			CForm::maxlength => 36,
			CForm::pattern => '^([a-zA-Z0-9\$]){3,36}$'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::name => 'coupon_code_title'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::name => 'coupon_code_short_title'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'coupon_code_description'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'comments'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'applicable_customer_type',
			CForm::options => array(
				CCouponCode::ALL_USERS => 'All guests',
				CCouponCode::EXISTING_USER => 'Existing guests',
				CCouponCode::NEW_USER => 'New guests',
				CCouponCode::REMISS_SINCE_DATE => 'Existing guest but absent since specified date',
				CCouponCode::REMISS_N_MONTHS => 'Existing guest but absent specified number of months',
				CCouponCode::NEW_OR_REMISS => 'New or existing guest absent since specified date',
				CCouponCode::NEW_OR_REMISS_N_MONTHS => 'New or existing guest absent specified number of months'
			)
		));

		$thisRemissCuttoff = $this->CouponForm->Value('remiss_cutoff_date');
		$this->CouponForm->addElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'remiss_cutoff_date',
			CForm::disabled => (empty($thisRemissCuttoff) ? true : false),
		));

		$thisRemissNoMonths = $this->CouponForm->Value('remiss_number_of_months');
		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'remiss_number_of_months',
			CForm::disabled => (empty($thisRemissNoMonths) ? true : false),
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'use_limit_per_account'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'valid_timespan_start',
			CForm::required => true
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'valid_timespan_end',
			CForm::required => true
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'valid_session_timespan_start'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'valid_session_timespan_end'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_product_coupon',
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_product_type_membership',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$menuOptions = array('null' => 'All Menus');

		foreach ($menuArray as $id => $menu)
		{
			$menuOptions[$id] = $menu->menu_name;
		}

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_menuspan_start',
			CForm::options => $menuOptions
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_menuspan_end',
			CForm::options => $menuOptions
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'minimum_order_amount',
			CForm::step => '0.01',
			CForm::min => 0
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'minimum_servings_count'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'minimum_item_count'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_session_type_standard',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_session_type_private',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_session_type_discounted',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_session_type_delivery',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_order_type_standard',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_order_type_intro',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_for_order_type_dream_taste',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'valid_with_plate_points_credits',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_store_specific',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_order_editor_supported',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_customer_order_supported',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'is_platepoints_perk',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_grand_total',
			CForm::label => 'Grand total'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_core',
			CForm::label => 'Core menu'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_core_and_efl',
			CForm::label => 'Core and EFL menu'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_finishing_touch',
			CForm::label => 'Sides &amp; Sweets'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_mfy_fee',
			CForm::label => 'Limit to MFY fee'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_recipe_id',
			CForm::label => 'Limit to Recipe'
		));

		$limit_to_recipe_id = $this->CouponForm->Value('limit_to_recipe_id');
		$this->CouponForm->addElement(array(
			CForm::type => CForm::ButtonHidden,
			CForm::name => 'recipe_id',
			CForm::text => ((!empty($DAO_coupon_code->recipe_id)) ? $DAO_coupon_code->recipe->recipe_name . ' (' . CMenuItem::translatePricingType($DAO_coupon_code->recipe_id_pricing_type, true) . ')' : '<i class="fas fa-search font-size-medium-small"></i>'),
			CForm::disabled => empty($limit_to_recipe_id),
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::css_class => 'btn btn-primary ' . ($couponOrders['hasOrders'] ? 'disabled' : '')
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'recipe_id_pricing_type'
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "limit_coupon",
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::value => 'limit_to_delivery_fee',
			CForm::label => 'Limit to Delivery fee'
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'discount_method',
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::options => array(
				CCouponCode::FLAT => '$ off (Flat)',
				CCouponCode::PERCENT => '% off (Percent)'
				//CCouponCode::FLAT_TOTAL => '$ total (Flat)'
				//CCouponCode::FREE_MEAL => 'Free meal',
				//CCouponCode::FREE_MENU_ITEM => 'Free menu item'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'discount_var',
			CForm::readonly => $couponOrders['hasOrders'],
			CForm::required => true,
			CForm::step => '0.01',
			CForm::min => 0
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'delivered_requires_medium_box',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'delivered_requires_large_box',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'delivered_requires_custom_box',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'delivered_requires_curated_box',
			CForm::options => array(
				0 => 'No',
				1 => 'Yes'
			)
		));

		$this->CouponForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'submit',
			CForm::value => 'Save coupon',
			CForm::css_class => 'btn btn-primary w-100'
		));

		$this->Template->assign('CouponForm', $this->CouponForm->Render());
	}
}

?>