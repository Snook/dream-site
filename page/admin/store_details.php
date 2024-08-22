<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStoreHistory.php");
require_once("includes/DAO/BusinessObject/CStoreFee.php");
require_once("includes/DAO/Opco.php");

class page_admin_store_details extends CPageAdminOnly
{
	function runHomeOfficeManager()
	{
		$this->runStoreDetails();
	}

	function runFranchiseOwner()
	{
		$this->runStoreDetails(CStore::getFranchiseStore()->id);
	}

	function runSiteAdmin()
	{
		$this->runStoreDetails();
	}

	function runStoreDetails($id = null)
	{
		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$siteadminoverride = false; // LMH: OVERRIDE
		$user_type = CUser::getCurrentUser()->user_type;
		$disabledForm = true;

		if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::FRANCHISE_OWNER || $user_type == CUser::HOME_OFFICE_MANAGER)
		{
			$disabledForm = false;
		}

		// LMH: OVERRIDE
		// allow home office managers identified in the dreamsite database access_control_page_user
		// for this specific page specific edit access on the form.  ie. .Rachael Sandifier request 8/20/2009
		// only check if the user type is of home office manager.. everyone else.. sorry
		if ($user_type == CUser::HOME_OFFICE_MANAGER)
		{
			$siteadminoverride = CApp::overrideAdminPage();

			if ($siteadminoverride == true)
			{
				$disabledForm = false;
				$user_type = CUser::SITE_ADMIN;
			}
		}

		$label = "Date Not Set";

		if (isset($_REQUEST["selectedCell"]) && $_REQUEST["selectedCell"])
		{
			$startTS = strtotime(CGPC::do_clean($_REQUEST["selectedCell"], TYPE_STR));
		}

		if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'])
		{
			$id = CGPC::do_clean($_REQUEST['id'], TYPE_INT);
		}
		else if (!$id)
		{
			CApp::bounce('/backoffice/list_stores');
		}

		if (!empty($id) && is_numeric($id))
		{
			if (isset($_REQUEST['action']))
			{
				if ($_POST['action'] == 'deleteStore')
				{
					$DAO_store = DAO_CFactory::create('store', true);
					$DAO_store->id = $id;
					$DAO_store->delete();
					$this->Template->setStatusMsg('The store has been deleted');

					// jump to same page without deleteStore action

					if (!empty($_POST['back']))
					{
						CApp::bounce(urldecode($_POST['back']));
					}
					else
					{
						CApp::bounce('/backoffice/list_stores');
					}
				}
			}

			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->id = $id;
			$DAO_store->find_DAO_store(true);
			$this->Template->assign('DAO_store', $DAO_store);
			$this->Template->assign('shortURLArray', $DAO_store->getVanityUrlArray());

			$manager_DAO_user = DAO_CFactory::create('user', true);
			$manager_DAO_user->id = $DAO_store->manager_1_user_id;
			$manager_DAO_user->find(true);
			$this->Template->assign('manager_DAO_user', $manager_DAO_user);

			$store_already_opted_into_plate_points = false;
			$current_retention_program_setting = $DAO_store->supports_retention_programs;

			if ($DAO_store->supports_plate_points)
			{
				$store_already_opted_into_plate_points = true;
			}

			if ($DAO_store->franchise_id)
			{
				$franchise = DAO_CFactory::create('franchise', true);
				$franchise->id = $DAO_store->franchise_id;
				$franchise->find(true);
				$this->Template->assign('franchise_name', $franchise->franchise_name);
			}
			else
			{
				$this->Template->assign('franchise_name', null);
			}

			$Form->DefaultValues = array_merge($Form->DefaultValues, $DAO_store->toArray());
			list($sales_tax_id, $food_tax, $total_tax, $service_tax, $enrollment_tax, $deliverytax, $bagFeeTax) = $DAO_store->getCurrentSalesTax();
			$Form->DefaultValues['food_tax'] = $food_tax;
			$Form->DefaultValues['total_tax'] = $total_tax;
			$Form->DefaultValues['other1_tax'] = $service_tax;
			$Form->DefaultValues['other2_tax'] = $enrollment_tax;
			$Form->DefaultValues['other3_tax'] = $deliverytax;
			$Form->DefaultValues['other4_tax'] = $bagFeeTax;

			$Form->DefaultValues['short_url'] = $DAO_store->getStoreId();

			if (empty($manager_DAO_user->id))
			{
				$Form->DefaultValues['manager_1_user_id'] = '';
			}
			else
			{
				$Form->DefaultValues['manager_1_user_id'] = $manager_DAO_user->id;
			}

			//set credit card check default values
			$Form->DefaultValues['credit_card_discover'] = false;
			$Form->DefaultValues['credit_card_amex'] = false;
			$ccTypes = $DAO_store->getCreditCardTypes();

			foreach ($ccTypes as $card)
			{
				switch ($card)
				{
					case CPayment::DISCOVERCARD:
						$Form->DefaultValues['credit_card_discover'] = true;
						break;
					case CPayment::AMERICANEXPRESS:
						$Form->DefaultValues['credit_card_amex'] = true;
						break;
				}
			}

			$sessionDescs = CStore::getStoreSessionTypeDescriptions($DAO_store);
			if (!empty($sessionDescs))
			{
				foreach ($sessionDescs as $title => $message)
				{
					switch ($title)
					{
						case 'STANDARD':
							$Form->DefaultValues['assembly_session_desc'] = $message;
							break;
						case 'PICKUP':
							$Form->DefaultValues['pickup_session_desc'] = $message;
							break;
						case 'DELIVERY':
							$Form->DefaultValues['delivery_session_desc'] = $message;
							break;
						case 'REMOTE_PICKUP':
							$Form->DefaultValues['remote_pickup_session_desc'] = $message;
							break;
					}
				}
			}

			$Form->DefaultValues['medium_ship_cost'] = $DAO_store->medium_ship_cost;
			$Form->DefaultValues['large_ship_cost'] = $DAO_store->large_ship_cost;
			$Form->DefaultValues['default_delivered_sessions'] = $DAO_store->default_delivered_sessions;

			if ($user_type == CUser::SITE_ADMIN)
			{
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "store_name",
					CForm::required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "vertical_response_code",
					CForm::required => false,
					CForm::size => 12
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "dailystory_tenant_uid",
					CForm::required => false,
					CForm::size => 12
				));

				$Form->AddElement(array(
					CForm::type => CForm::EMail,
					CForm::disabled => false,
					CForm::name => "email_address",
					CForm::required => true,
					CForm::email => true,
					CForm::length => 50
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::pattern => "[a-z0-9\-]+",
					// only numbers, hyphens and lower case letters allowed
					CForm::name => "short_url"
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "address_line1",
					CForm::required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "address_line2",
					CForm::required => false,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "city",
					CForm::required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "county",
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "postal_code",
					CForm::required => true,
					CForm::size => 10
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'usps_adc',
					CForm::size => 10
				));

				$Form->AddElement(array(
					CForm::type => CForm::TextArea,
					CForm::disabled => false,
					CForm::name => "address_directions",
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'address_latitude',
					CForm::css_class => 'gllpLatitude',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'address_longitude',
					CForm::css_class => 'gllpLongitude',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_day',
					CForm::required => true,
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_evening',
					CForm::css_class => 'telephone',
					CForm::required => false,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_sms',
					CForm::css_class => 'telephone',
					CForm::required => false,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'fax',
					CForm::css_class => 'telephone',
					CForm::required => false,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'google_place_id',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_twitter',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_facebook',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_instagram',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::StatesProvinceDropDown,
					CForm::disabled => false,
					CForm::name => 'state_id',
					CForm::required => true
				));

				$Form->AddElement(array(
					CForm::type => CForm::OpcoDropDown,
					CForm::disabled => false,
					CForm::name => 'opco_id',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'medium_ship_cost',
					CForm::step => '.01',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'large_ship_cost',
					CForm::step => '.01',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'default_delivered_sessions',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_ltd_roundup'
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_delivery'
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'delivery_fee',
					CForm::min => 0,
					CForm::step => 0.01,
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::disabled => false,
					CForm::name => 'delivery_radius',
					CForm::options => array(
						'0' => 'Do not display',
						'10' => '10 Miles',
						'15' => '15 Miles',
						'20' => '20 Miles',
						'25' => '25 Miles',
						'30' => '30 Miles'
					),
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'default_bag_fee',
					CForm::min => 0,
					CForm::step => 0.01,
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_intro_orders'
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_fundraiser'
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_offsite_pickup'
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::onClick => 'onPlatePointsOptinChange',
					CForm::name => 'supports_plate_points'
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_plate_points_signature',
					CForm::length => 36
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_bag_fee'
				));
			}
			else
			{
				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'medium_ship_cost',
					CForm::step => '.01',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'large_ship_cost',
					CForm::step => '.01',
					CForm::required => false
				));
				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'default_delivered_sessions',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'delivery_fee',
					CForm::min => 0,
					CForm::step => 0.01,
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => 'supports_bag_fee'
				));

				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => 'default_bag_fee',
					CForm::min => 0,
					CForm::step => 0.01,
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "store_name",
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::EMail,
					CForm::disabled => true,
					CForm::name => "email_address",
					CForm::size => 50
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::pattern => "[a-z0-9\-]+",
					// only numbers, hyphens and lower case letters allowed
					CForm::name => "short_url"
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "address_line1",
					CForm::required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "address_line2",
					CForm::required => false,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "city",
					CForm::required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "county",
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "postal_code",
					CForm::required => true,
					CForm::size => 10
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => 'usps_adc',
					CForm::size => 10
				));

				$Form->AddElement(array(
					CForm::type => CForm::TextArea,
					CForm::disabled => true,
					CForm::name => "address_directions",
					CForm::required => false,
					CForm::css_class => 'previewable'
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => 'address_latitude',
					CForm::css_class => 'gllpLatitude',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => 'address_longitude',
					CForm::css_class => 'gllpLongitude',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => true,
					CForm::name => 'telephone_day',
					CForm::required => true,
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => true,
					CForm::name => 'telephone_evening',
					CForm::css_class => 'telephone',
					CForm::required => true,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_sms',
					CForm::css_class => 'telephone',
					CForm::required => false,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => true,
					CForm::name => 'fax',
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'google_place_id',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_twitter',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_facebook',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_instagram',
					CForm::required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::StatesProvinceDropDown,
					CForm::disabled => true,
					CForm::name => 'state_id',
					CForm::required => true
				));
			}

			if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
			{
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "home_office_id",
					CForm::required => true,
					CForm::length => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::disabled => $disabledForm,
					CForm::name => "ssm_builder"
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "gp_account_id",
					CForm::required => true,
					CForm::length => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "door_dash_id",
					CForm::required => false,
					CForm::length => 20
				));
			}

			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::name => "store_description",
				CForm::required => false,
				CForm::css_class => 'previewable'
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::disabled => $disabledForm,
				//CForm::default_value => 1,
				CForm::name => "active",
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				)
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::disabled => $disabledForm,
				CForm::name => "show_on_customer_site",
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				)
			));

			$Form->AddElement(array(
				CForm::type => CForm::TimezoneDropDown,
				CForm::disabled => $disabledForm,
				CForm::name => 'timezone_id'
			));

			if ($DAO_store->grand_opening_date != null)
			{
				$startTS = strtotime($DAO_store->grand_opening_date);
				$label = date("m/d/Y", $startTS);
				$this->Template->assign('initDate', $label);
			}
			else
			{
				$this->Template->assign('initDate', date("m/d/Y"));
			}

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::onChange => 'update_grand_opening_function',
				CForm::name => 'update_grandopeningdate'
			));

			$Form->AddElement(array(
				CForm::type => CForm::RadioButton,
				CForm::disabled => $disabledForm,
				CForm::name => 'close_interval_type',
				CForm::required => true,
				CForm::value => CStore::HOURS
			));

			$Form->AddElement(array(
				CForm::type => CForm::RadioButton,
				CForm::disabled => $disabledForm,
				CForm::name => 'close_interval_type',
				CForm::required => true,
				CForm::value => CStore::ONE_FULL_DAY
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => 'close_session_hours',
				CForm::length => 4,
				CForm::number => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::RadioButton,
				CForm::disabled => false,
				CForm::name => 'meal_customization_close_interval_type',
				CForm::required => true,
				CForm::value => CStore::HOURS
			));

			$Form->AddElement(array(
				CForm::type => CForm::RadioButton,
				CForm::disabled => false,
				CForm::name => 'meal_customization_close_interval_type',
				CForm::required => true,
				CForm::value => CStore::FOUR_FULL_DAYS
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => false,
				CForm::name => 'meal_customization_close_interval',
				CForm::length => 4,
				CForm::number => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => false,
				CForm::name => 'close_customization_session_hours',
				CForm::length => 4,
				CForm::number => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'observes_DST'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Number,
				CForm::disabled => $disabledForm,
				CForm::name => 'default_intro_slots',
				CForm::length => 4,
				CForm::number => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'supports_special_events'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				//CForm::disabled => $disabledForm,
				CForm::name => 'supports_free_assembly_promotion'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'supports_plate_points_enhancements'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'supports_retention_programs'
			));
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'serving_tabindex_vertical'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'hide_carryover_notes'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'hide_fadmin_home_dashboard'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'receive_low_inv_alert'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'show_print_menu_pre_assembled_label'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'dream_taste_opt_out'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'publish_session_details'
			));

			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::rows => '4',
				CForm::cols => '40',
				CForm::placeholder => 'Current Default Message: We look forward to seeing you in our store to assemble your meals. Be sure to bring a cooler and arrive on time.',
				CForm::css_class => 'dd-strip-tags',
				CForm::name => 'assembly_session_desc'
			));
			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::rows => '4',
				CForm::cols => '40',
				CForm::placeholder => 'Current Default Message: Be sure to bring a cooler to take home your meals. A store service fee may apply. Fees will be displayed at checkout and may vary by location.',
				CForm::css_class => 'dd-strip-tags',
				CForm::name => 'pickup_session_desc'
			));
			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::rows => '4',
				CForm::cols => '40',
				CForm::placeholder => 'Current Default Message: Select a delivery window from the list below. At checkout, a delivery fee applies to orders delivered within 15 miles of the store. Orders may be canceled for any distance beyond 15 miles. If the store can accommodate, an additional fee may be added to your order before delivery. Fees vary by location.',
				CForm::css_class => 'dd-strip-tags',
				CForm::name => 'delivery_session_desc'
			));
			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::rows => '5',
				CForm::cols => '40',
				CForm::placeholder => 'Current Default Message: Pick up your order at a local business or residents house located in the community instead of at the store location. Additional service fee may apply. Fees will be displayed at checkout and may vary by location.',
				CForm::css_class => 'dd-strip-tags',
				CForm::name => 'remote_pickup_session_desc'
			));

			$Form->AddElement(array(
				CForm::type => CForm::FranchiseDropDown,
				CForm::disabled => $disabledForm,
				CForm::name => 'franchise_id',
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => 'food_tax',
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => "total_tax",
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => "other1_tax",
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => "other2_tax",
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => "other3_tax",
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::disabled => $disabledForm,
				CForm::name => "other4_tax",
				CForm::step => '0.00001',
				CForm::number => true,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'credit_card_amex'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'credit_card_discover'
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::disabled => $disabledForm,
				CForm::name => 'core_pricing_tier',
				CForm::options => array(
					1 => 'One',
					2 => 'Two',
					3 => 'Three',
				),
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => false,
				CForm::name => 'supports_meal_customization'
			));

			//Order Customization
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => false,
				CForm::name => 'supports_meal_customization'
			));

			$customizationFees = CStoreFee::fetchCustomizationFees($DAO_store->id);
			$this->Template->assign('customization_fees', $customizationFees);
			$this->Template->assign('store_supports_meal_customization', CStore::storeSupportsMealCustomization($DAO_store->id));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => false,
				CForm::css_class => 'customization_fields',
				CForm::name => 'allow_preassembled_customization'
			));

			foreach ($customizationFees as $fee)
			{
				$Form->DefaultValues[$fee['name']] = $fee['cost'];
				$Form->AddElement(array(
					CForm::type => CForm::Number,
					CForm::disabled => false,
					CForm::name => $fee['name'],
					CForm::min => 0,
					CForm::step => 0.01,
					CForm::css_class => 'customization_fields',
					CForm::required => false
				));
			}

			$Form->AddElement(array(
				CForm::type => CForm::Submit,
				CForm::name => "updateStore",
				CForm::disabled => $disabledForm,
				CForm::css_class => 'btn btn-primary btn-sm',
				CForm::value => "Save"
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'supports_delayed_payment',
				CForm::label => 'Support Delayed Payment'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Number,
				CForm::disabled => !$DAO_store->supportsDelayedPayment(),
				CForm::name => "default_delayed_payment_deposit",
				CForm::min => 20,
				CForm::step => 0.01,
				CForm::number => true,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Number,
				CForm::disabled => !$DAO_store->supportsDelayedPayment(),
				CForm::name => "delayed_payment_order_minimum",
				CForm::min => 0,
				CForm::step => 0.01,
				CForm::number => true,
				CForm::required => false
			));

			$pkgSameAsStore = true;
			$letterSameAsStore = true;

			if (empty($DAO_store->pkg_ship_same_as_store))
			{
				$pkgSameAsStore = false;
			}

			if (empty($DAO_store->letter_ship_same_as_store))
			{
				$letterSameAsStore = false;
			}

			if ($pkgSameAsStore || $letterSameAsStore)
			{
				if ($pkgSameAsStore)
				{
					$Form->DefaultValues['pkg_ship_address_line1'] = $DAO_store->address_line1;
					$Form->DefaultValues['pkg_ship_address_line2'] = $DAO_store->address_line2;
					$Form->DefaultValues['pkg_ship_city'] = $DAO_store->city;
					$Form->DefaultValues['pkg_ship_state_id'] = $DAO_store->state_id;
					$Form->DefaultValues['pkg_ship_postal_code'] = $DAO_store->postal_code;
					$Form->DefaultValues['pkg_ship_telephone_day'] = $DAO_store->telephone_day;
				}

				if ($letterSameAsStore)
				{
					$Form->DefaultValues['letter_ship_address_line1'] = $DAO_store->address_line1;
					$Form->DefaultValues['letter_ship_address_line2'] = $DAO_store->address_line2;
					$Form->DefaultValues['letter_ship_city'] = $DAO_store->city;
					$Form->DefaultValues['letter_ship_state_id'] = $DAO_store->state_id;
					$Form->DefaultValues['letter_ship_postal_code'] = $DAO_store->postal_code;
					$Form->DefaultValues['letter_ship_telephone_day'] = $DAO_store->telephone_day;
				}
			}

			/*
			 * Package Shipping Address
			*/
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $pkgSameAsStore,
				CForm::name => 'pkg_ship_same_as_store',
				CForm::label => 'Same as store address',
				CForm::attribute => array('data-contact' => 'pkg_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $pkgSameAsStore,
				CForm::name => 'pkg_ship_is_commercial',
				CForm::label => 'Commercial address',
				CForm::disabled => $pkgSameAsStore,
				CForm::attribute => array('data-contact' => 'pkg_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_address_line1",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->address_line1
				),
				CForm::placeholder => "*Street Address",
				CForm::required_msg => "Please enter a street address.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_address_line2",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->address_line2
				),
				CForm::placeholder => "Suite / Unit",
				CForm::required_msg => "Please enter a suite number.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_city",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->city
				),
				CForm::placeholder => "*City",
				CForm::required_msg => "Please enter a city.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::StatesProvinceDropDown,
				CForm::name => 'pkg_ship_state_id',
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->state_id
				),
				CForm::required_msg => "Please select state.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_postal_code",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->postal_code
				),
				CForm::placeholder => "*Postal Code",
				CForm::required_msg => "Please enter a postal code.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_attn",
				CForm::attribute => array('data-contact' => 'pkg_ship'),
				CForm::placeholder => "Attention",
				CForm::required_msg => "Please enter attention to.",
				CForm::tooltip => true,
				CForm::disabled => false,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'pkg_ship_telephone_day',
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $DAO_store->telephone_day
				),
				CForm::placeholder => "*Primary Telephone",
				CForm::required_msg => "Please enter a telephone number.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::required => true,
				CForm::css_class => 'telephone'
			));

			/*
			 * Postal (letter) Mailing Address & Legal Notices
			*/
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $letterSameAsStore,
				CForm::name => 'letter_ship_same_as_store',
				CForm::label => 'Same as store address',
				CForm::attribute => array('data-contact' => 'letter_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $letterSameAsStore,
				CForm::name => 'letter_ship_is_commercial',
				CForm::label => 'Commercial address',
				CForm::disabled => $letterSameAsStore,
				CForm::attribute => array('data-contact' => 'letter_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_address_line1",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->address_line1
				),
				CForm::placeholder => "*Street Address",
				CForm::required_msg => "Please enter a street address.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_address_line2",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->address_line2
				),
				CForm::placeholder => "Suite / Unit",
				CForm::required_msg => "Please enter a suite number.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_city",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->city
				),
				CForm::placeholder => "*City",
				CForm::required_msg => "Please enter a city.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::StatesProvinceDropDown,
				CForm::name => 'letter_ship_state_id',
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->state_id
				),
				CForm::required_msg => "Please select state.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_postal_code",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->postal_code
				),
				CForm::placeholder => "*Postal Code",
				CForm::required_msg => "Please enter a postal code.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_attn",
				CForm::attribute => array('data-contact' => 'letter_ship'),
				CForm::placeholder => "Attention",
				CForm::required_msg => "Please enter attention to.",
				CForm::tooltip => true,
				CForm::disabled => false,
				CForm::required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'letter_ship_telephone_day',
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $DAO_store->telephone_day
				),
				CForm::placeholder => "*Primary Telephone",
				CForm::required_msg => "Please enter a telephone number.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::required => true,
				CForm::css_class => 'telephone'
			));

			/*
			 * Manager #1 Personal Contact Information
			*/
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "manager_1_user_id",
				CForm::attribute => array('data-contact' => 'manager_1'),
				CForm::placeholder => "User ID",
				CForm::required_msg => "Please enter a user id.",
				CForm::tooltip => true,
				CForm::disabled => false,
				CForm::required => false
			));

			// Trade Area assignment
			$defaultTradeArea = 0;
			$tdarea = DAO_CFactory::create('store_trade_area', true);
			$tdarea->is_active = 1;
			$tdarea->store_id = $id;
			$tdarea->find(true);

			if (!empty($tdarea->trade_area_id))
			{
				$defaultTradeArea = $tdarea->trade_area_id;
			}

			$tradeareaarray = array();
			$tradeareas = DAO_CFactory::create('trade_area', true);
			$tradeareas->find();
			$tradeareaarray[] = '-- Select a region --';

			while ($tradeareas->fetch())
			{
				$tradeareaarray[$tradeareas->id] = $tradeareas->region;
			}

			if ($user_type == CUser::FRANCHISE_OWNER || $user_type == CUser::HOME_OFFICE_MANAGER)
			{
				$tradeareaname = !isset($tradeareaarray[$defaultTradeArea]) ? 'Un-assigned' : $tradeareaarray[$defaultTradeArea];
				$Form->AddElement(array(
					CForm::type => CForm::Label,
					CForm::name => 'regiondropdown',
					CForm::value => $tradeareaname
				));
			}
			else if ($user_type == CUser::SITE_ADMIN)
			{
				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::disabled => $disabledForm,
					CForm::required => true,
					CForm::name => "regiondropdown",
					CForm::default_value => $defaultTradeArea,
					CForm::options => $tradeareaarray
				));
			}
			// Coach assignment

			$coachdefault = 0;
			$coachassign = DAO_CFactory::create('store_coach', true);
			$coachassign->is_active = 1;
			$coachassign->store_id = $id;
			$coachassign->find(true);

			if (!empty($coachassign->id))
			{
				$coachdefault = $coachassign->coach_id;
			}

			$sql = 'SELECT coach.id, `user`.`primary_email`,`user`.`firstname`,`user`.`lastname`, administrator FROM `coach` INNER JOIN `user` ON `coach`.`user_id` = `user`.`id` WHERE `coach`.`active` = 1 AND user.is_deleted = 0 ORDER BY lastname';
			$coacharr = array();
			$coach = DAO_CFactory::create('coach', true);
			$coach->query($sql);
			$coacharr[] = '-- Assign a coach --';

			while ($coach->fetch())
			{
				$coacharr[$coach->id] = $coach->lastname . ', ' . $coach->firstname . ' (' . $coach->primary_email . ')';
			}

			if ($user_type == CUser::FRANCHISE_OWNER)
			{
				$coachname = !isset($coacharr[$coachdefault]) ? 'Un-assigned' : $coacharr[$coachdefault];
				$Form->AddElement(array(
					CForm::type => CForm::Label,
					CForm::name => 'coachdropdown',
					CForm::value => $coachname
				));
			}
			else if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::HOME_OFFICE_MANAGER)
			{
				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::disabled => false,
					CForm::name => "coachdropdown",
					CForm::required => true,
					CForm::default_value => $coachdefault,
					CForm::options => $coacharr
				));
			}

			self::setupStoreBioFormFields($Form, $this->Template);

			// handle form submit
			if ($Form->value('updateStore'))
			{
				$hadError = false;
				$storeUpdated = clone($DAO_store);
				$storeUpdated->setFrom($Form->values());

				// cleanup
				$storeUpdated->store_description = trim($storeUpdated->store_description);
				$storeUpdated->address_directions = trim($storeUpdated->address_directions);

				if ($storeUpdated->supports_plate_points && !$store_already_opted_into_plate_points)
				{
					if (empty($_POST['supports_plate_points_signature']))
					{
						$this->Template->setErrorMsg('Opting in to PLATEPOINTS requires the full name of the person opting the store in.');
						$hadError = true;
					}
					else
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $DAO_store->id, 'null', 300, 'null', 'null', 'null', CGPC::do_clean($_POST['supports_plate_points_signature'], TYPE_STR));
					}
				}

				if ((int)$storeUpdated->supports_retention_programs != (int)$current_retention_program_setting)
				{
					if ($storeUpdated->supports_retention_programs)
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $DAO_store->id, 'null', 301, 'null', 'null', 'null', "Store has opted into retention program");
					}
					else
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $DAO_store->id, 'null', 302, 'null', 'null', 'null', "Store has opted out of retention program");
					}
				}

				if (!$hadError)
				{
					$DescsriptionsArray = array();
					$DescsriptionsArray['STANDARD'] = CGPC::do_clean($_POST['assembly_session_desc'], TYPE_STR);
					$DescsriptionsArray['PICKUP'] = CGPC::do_clean($_POST['pickup_session_desc'], TYPE_STR);
					$DescsriptionsArray['DELIVERY'] = CGPC::do_clean($_POST['delivery_session_desc'], TYPE_STR);
					$DescsriptionsArray['REMOTE_PICKUP'] = CGPC::do_clean($_POST['remote_pickup_session_desc'], TYPE_STR);

					CStore::setStoreSessionTypeDescriptions($DAO_store, $DescsriptionsArray);

					if (empty($_POST['pkg_ship_is_commercial']))
					{
						$storeUpdated->pkg_ship_is_commercial = 0;
					}

					if (empty($_POST['letter_ship_is_commercial']))
					{
						$storeUpdated->letter_ship_is_commercial = 0;
					}

					if (!empty($_POST['pkg_ship_same_as_store']))
					{
						$storeUpdated->pkg_ship_is_commercial = 1;
						$storeUpdated->pkg_ship_address_line1 = 'null';
						$storeUpdated->pkg_ship_address_line2 = 'null';
						$storeUpdated->pkg_ship_city = 'null';
						$storeUpdated->pkg_ship_state_id = 'null';
						$storeUpdated->pkg_ship_postal_code = 'null';
						$storeUpdated->pkg_ship_telephone_day = 'null';
					}

					if (!empty($_POST['letter_ship_same_as_store']))
					{
						$storeUpdated->letter_ship_is_commercial = 1;
						$storeUpdated->letter_ship_address_line1 = 'null';
						$storeUpdated->letter_ship_address_line2 = 'null';
						$storeUpdated->letter_ship_city = 'null';
						$storeUpdated->letter_ship_state_id = 'null';
						$storeUpdated->letter_ship_postal_code = 'null';
						$storeUpdated->letter_ship_telephone_day = 'null';
					}

					$checkbox = $Form->value('update_grandopeningdate');
					if (isset($_POST['grand_opening_date']) && $Form->value('update_grandopeningdate'))
					{
						$dateParts = explode("/", CGPC::do_clean($_POST['grand_opening_date'], TYPE_STR));
						$grandAsTS = mktime(0, 0, 0, $dateParts[0], $dateParts[1], $dateParts[2]);
						$storeUpdated->grand_opening_date = date("Y-m-d", $grandAsTS);
						$label = date("m/d/Y", $grandAsTS);
						$this->Template->assign('initDate', $label);
					}

					if (isset($_POST['default_delayed_payment_deposit']) && $_POST['default_delayed_payment_deposit'] < 20)
					{
						$storeUpdated->default_delayed_payment_deposit = 20;
						$this->Template->setStatusMsg("Delayed Payment deposit must be at least $20. The deposit was set to $20.");
					}
					else if (isset($_POST['default_delayed_payment_deposit']))
					{
						$storeUpdated->default_delayed_payment_deposit = CGPC::do_clean($_POST['default_delayed_payment_deposit'], TYPE_NUM);
					}

					if (isset($_POST['delayed_payment_order_minimum']) && $_POST['delayed_payment_order_minimum'] > 0)
					{
						$storeUpdated->delayed_payment_order_minimum = CGPC::do_clean($_POST['delayed_payment_order_minimum'], TYPE_NUM);
					}
					else if (isset($_POST['delayed_payment_order_minimum']))
					{
						$storeUpdated->delayed_payment_order_minimum = 0;
					}

					// store is changing franchise entity, so remove old owners and assign new
					if ($storeUpdated->franchise_id != $DAO_store->franchise_id)
					{
						$oldOwners = DAO_CFactory::create('user_to_franchise', true);
						$oldOwners->franchise_id = $DAO_store->franchise_id;
						$oldOwners->find();
						while ($oldOwners->fetch())
						{
							$removeOwner = DAO_CFactory::create('user_to_store', true);
							$removeOwner->user_id = $oldOwners->user_id;
							$removeOwner->store_id = $DAO_store->id;
							$removeOwner->find();
							while ($removeOwner->fetch())
							{
								$removeOwner->delete();
							}
						}

						$newOwners = DAO_CFactory::create('user_to_franchise', true);
						$newOwners->franchise_id = $storeUpdated->franchise_id;
						$newOwners->find();
						while ($newOwners->fetch())
						{
							$addOwner = DAO_CFactory::create('user_to_store', true);
							$addOwner->user_id = $newOwners->user_id;
							$addOwner->store_id = $DAO_store->id;
							$addOwner->display_to_public = 0;
							$addOwner->insert();
						}

						if ($storeUpdated->franchise_id == 220) // 220 is now and forever the id of the owning entity of all corporate stores
						{
							$storeUpdated->is_corporate_owned = 1;
						}
						else
						{
							$storeUpdated->is_corporate_owned = 0;
						}

						// update merch account
						$MerchantInfo = DAO_CFactory::create('merchant_accounts', true);
						$MerchantInfo->store_id = $DAO_store->id;
						$MerchantInfo->franchise_id = $DAO_store->franchise_id;
						$found = $MerchantInfo->find(true);
						if ($found > 1)
						{
							throw new Exception('more than one merchant account tow found');
						}
						$originalInfo = clone($MerchantInfo);
						$MerchantInfo->franchise_id = $storeUpdated->franchise_id;
						$MerchantInfo->update($originalInfo);

						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $DAO_store->id, 'null', 400, 'null', 'null', 'null', 'merchant_accounts.id ' . $MerchantInfo->id . ' franchise_id changed from ' . $DAO_store->franchise_id . ' to ' . $storeUpdated->franchise_id);
					}
					$storeUpdated->update($DAO_store);
					$DAO_store = $storeUpdated;
					$DAO_store->setCurrentSalesTax($Form->value('food_tax'), $Form->value('total_tax'), $Form->value('other1_tax'), $Form->value('other2_tax'), $Form->value('other3_tax'), $Form->value('other4_tax'));
					foreach ($customizationFees as $fee)
					{
						$cost = 0;
						if ($Form->value('supports_meal_customization'))
						{
							$cost = $Form->value($fee['name']);
						}
						$DAO_store->setCustomizationFee($DAO_store->id, $fee, $cost);
					}

					//update credit card choices
					if ($Form->value('credit_card_discover') != $Form->DefaultValues['credit_card_discover'])
					{
						$ccTypeObj = DAO_CFactory::create('payment_credit_card_type', true);
						$ccTypeObj->credit_card_type = CPayment::DISCOVERCARD;
						$ccTypeObj->store_id = $DAO_store->id;
						if ($Form->value('credit_card_discover'))
						{
							$ccTypeObj->is_default_card = 0;
							$ccTypeObj->insert();
						}
						else if (!$Form->value('credit_card_discover'))
						{
							$ccTypeObj->find();
							while ($ccTypeObj->fetch())
							{
								$ccTypeObj->delete();
							}
						}
					}
					if ($Form->value('credit_card_amex') != $Form->DefaultValues['credit_card_amex'])
					{
						$ccTypeObj = DAO_CFactory::create('payment_credit_card_type', true);
						$ccTypeObj->credit_card_type = CPayment::AMERICANEXPRESS;
						$ccTypeObj->store_id = $DAO_store->id;
						if ($Form->value('credit_card_amex'))
						{
							$ccTypeObj->is_default_card = 0;
							$ccTypeObj->insert();
						}
						else if (!$Form->value('credit_card_amex'))
						{
							$ccTypeObj->find();
							while ($ccTypeObj->fetch())
							{
								$ccTypeObj->delete();
							}
						}
					}
					// only site admin can edit this information
					if ($user_type == CUser::SITE_ADMIN)
					{
						$tradeAreaID = $Form->value('regiondropdown');
						if ($tradeAreaID > 0)
						{
							$trade = DAO_CFactory::create('store_trade_area', true);
							$trade->store_id = $id;
							if (!$trade->find(true))
							{
								$trade->is_active = 1;
								$trade->trade_area_id = $tradeAreaID;
								$trade->insert();
							}
							else
							{
								if ($trade->trade_area_id != $tradeAreaID)
								{
									$trade->trade_area_id = $tradeAreaID;
									$trade->is_active = 1;
									$trade->update();
								}
							}
						}
						else if ($tradeAreaID == 0)
						{
							$trade = DAO_CFactory::create('store_trade_area', true);
							$trade->store_id = $id;
							if ($trade->find(true))
							{
								$trade->delete();
							}
						}
						$currentCoachID = $Form->value('coachdropdown');
						if ($currentCoachID > 0)
						{
							$coachassign = DAO_CFactory::create('store_coach', true);
							$coachassign->store_id = $id;
							if ($coachassign->find(true))
							{
								$coachassign->is_active = 1;
								$coachassign->coach_id = $currentCoachID;
								$coachassign->insert();
							}
							else
							{
								if ($coachassign->coach_id != $currentCoachID)
								{
									$coachassign->coach_id = $currentCoachID;
									$coachassign->is_active = 1;
									$coachassign->update();
								}
							}
						}
						else if ($currentCoachID == 0)
						{
							$coachassign = DAO_CFactory::create('store_coach', true);
							$coachassign->store_id = $id;
							if ($coachassign->find(true))
							{
								$coachassign->delete();
							}
						}
					}

					// update Vanity URL
					if (!empty($_POST['short_url']))
					{
						$DAO_short_url = DAO_CFactory::create('short_url', true);
						$DAO_short_url->store_id = $DAO_store->id;
						$DAO_short_url->page = 'location';
						$DAO_short_url->short_url = $_POST['short_url'];

						if (!$DAO_short_url->find_includeDeleted(true))
						{
							// Delete existing
							$delete_DAO_short_url = DAO_CFactory::create('short_url', true);
							$delete_DAO_short_url->store_id = $DAO_store->id;
							$delete_DAO_short_url->find();
							while ($delete_DAO_short_url->fetch())
							{
								$delete_DAO_short_url->delete();
							}

							// Insert the new one
							$DAO_short_url->insert();
						}
						else
						{
							// Delete existing
							$delete_DAO_short_url = DAO_CFactory::create('short_url', true);
							$delete_DAO_short_url->store_id = $DAO_store->id;
							$delete_DAO_short_url->find();
							while ($delete_DAO_short_url->fetch())
							{
								$delete_DAO_short_url->delete();
							}

							// Undelete existing
							$DAO_short_url->is_deleted = 0;
							$DAO_short_url->update();
						}
					}

					// update job positions
					if (!empty($_POST['job_position']))
					{
						$job_array = CStore::setAvailableJobs($id, CGPC::do_clean($_POST['job_position'], TYPE_ARRAY));
					}
					else
					{
						// No jobs available
						$job_array = CStore::setAvailableJobs($id, false);
					}

					$this->Template->setToastMsg(array('message' => 'The store properties have been updated.'));
					CApp::bounce('/backoffice/store_details?id=' . $id);
				}
			}

			// store available job positions
			$job_array = CStore::getStoreJobArray($id);

			$storeArray = $DAO_store->toArray();
			$storeArray['map'] = $DAO_store->generateMapLink();
			$storeArray['linear_address'] = $DAO_store->generateLinearAddress();

			$storeArray['personnel'] = CStore::getStorePersonnel($id);

			$this->Template->assign('job_array', $job_array);
			$this->Template->assign('siteadminoverride', $siteadminoverride);
			$this->Template->assign('grand_opening_label', $label);
			$this->Template->assign('store', $storeArray);
			$this->Template->assign('form_store_details', $Form->Render());
		}

		$back = '/backoffice/list_stores';

		if (array_key_exists('back', $_GET) && $_GET['back'])
		{
			$back = $_GET['back'];
		}

		$this->Template->assign('back', $back);
	}

	private function setupStoreBioFormFields(&$Form, $tpl, $disabledForm = false)
	{

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_store_name",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_primary_party_name",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_primary_party_title",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_primary_party_story",
			CForm::required => false,
			CForm::size => 40,
			CForm::css_class => 'previewable'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_secondary_party_name",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_secondary_party_title",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_secondary_party_story",
			CForm::required => false,
			CForm::size => 40,
			CForm::css_class => 'previewable'
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_team_description",
			CForm::required => false,
			CForm::size => 40,
			CForm::css_class => 'previewable'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_store_hours",
			CForm::required => false,
			CForm::size => 40
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::disabled => $disabledForm,
			CForm::name => "bio_store_holiday_hours",
			CForm::required => false,
			CForm::size => 40,
			CForm::css_class => 'previewable'
		));

		$this->Template->assign('time_picker_hours', json_encode(self::hoursRange()));
	}

	private function hoursRange($lower = 0, $upper = 86400, $step = 1800, $format = '')
	{
		$times = array();

		if (empty($format))
		{
			$format = 'g:i a';
		}

		foreach (range($lower, $upper, $step) as $increment)
		{
			$increment = gmdate('H:i', $increment);

			list($hour, $minutes) = explode(':', $increment);

			$date = new DateTime($hour . ':' . $minutes);

			$times[(string)$increment] = $date->format($format);
		}

		return $times;
	}
}

?>