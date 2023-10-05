<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStoreHistory.php");
require_once("includes/DAO/Opco.php");

class page_admin_store_details_delivered extends CPageAdminOnly
{

	private $defaultTime = null;

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
		$ty = CBrowserSession::getCurrentFadminStoreType();
		if ($ty !== CStore::DISTRIBUTION_CENTER)
		{
			CApp::bounce('/backoffice/store_details');
		}
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$siteadminoverride = false; // LMH: OVERRIDE
		$user_type = CUser::getCurrentUser()->user_type;
		$disabledForm = true;

		if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::FRANCHISE_OWNER)
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

		//$this->defaultTime = mktime(0,0,0,01,01,1970);
		$label = "Date Not Set";

		if (isset($_REQUEST["selectedCell"]) && $_REQUEST["selectedCell"])
		{
			$startTS = strtotime(CGPC::do_clean($_REQUEST["selectedCell"], TYPE_STR));
		}

		if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] && is_numeric($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
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
					$store = DAO_CFactory::create('store');
					$store->id = $id;
					$store->delete();
					$tpl->setStatusMsg('The store has been deleted');

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

			$store = DAO_CFactory::create('store');
			$store->query("SELECT
				s.*,
				m1.firstname AS manager_1_firstname,
				m1.lastname AS manager_1_lastname,
				m1.primary_email AS manager_1_primary_email,
				m1.telephone_1 AS manager_1_telephone_1
				FROM store AS s
				LEFT JOIN `user` AS m1 ON m1.id = s.manager_1_user_id AND m1.is_deleted = '0'
				WHERE s.id = '" . $id . "' AND s.is_deleted = '0'
				GROUP BY s.id");
			$store->fetch();
			$store_already_opted_into_plate_points = false;
			$current_retention_program_setting = $store->supports_retention_programs;

			if ($store->supports_plate_points)
			{
				$store_already_opted_into_plate_points = true;
			}

			if ($store->franchise_id)
			{
				$franchise = DAO_CFactory::create('franchise');
				$franchise->id = $store->franchise_id;
				$franchise->find(true);
				$tpl->assign('franchise_name', $franchise->franchise_name);
			}
			else
			{
				$tpl->assign('franchise_name', null);
			}

			$Form->DefaultValues = array_merge($Form->DefaultValues, $store->toArray());

			if (empty($store->manager_1_user_id))
			{
				$Form->DefaultValues['manager_1_user_id'] = '';
			}

			//set credit card check default values
			$Form->DefaultValues['credit_card_discover'] = false;
			$Form->DefaultValues['credit_card_amex'] = false;
			$ccTypes = $store->getCreditCardTypes();

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

			$Form->DefaultValues['medium_ship_cost'] = $store->medium_ship_cost;
			$Form->DefaultValues['large_ship_cost'] = $store->large_ship_cost;
			$Form->DefaultValues['default_delivered_sessions'] = $store->default_delivered_sessions;

			if ($user_type == CUser::SITE_ADMIN)
			{
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "store_name",
					CForm::dd_required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "vertical_response_code",
					CForm::dd_required => false,
					CForm::size => 12
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "email_address",
					CForm::dd_required => true,
					CForm::email => true,
					CForm::length => 50
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "address_line1",
					CForm::dd_required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "address_line2",
					CForm::dd_required => false,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => "city",
					CForm::dd_required => true,
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
					CForm::dd_required => true,
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
					CForm::dd_required => false,
					CForm::height => 100,
					CForm::width => 400
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'address_latitude',
					CForm::css_class => 'gllpLatitude',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'address_longitude',
					CForm::css_class => 'gllpLongitude',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_day',
					CForm::dd_required => true,
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_evening',
					CForm::css_class => 'telephone',
					CForm::dd_required => true,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_sms',
					CForm::css_class => 'telephone',
					CForm::dd_required => false,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'fax',
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'google_place_id',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_twitter',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_facebook',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_instagram',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::StatesProvinceDropDown,
					CForm::disabled => false,
					CForm::name => 'state_id',
					CForm::dd_required => true
				));

				$Form->AddElement(array(
					CForm::type => CForm::OpcoDropDown,
					CForm::disabled => false,
					CForm::name => 'opco_id',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'medium_ship_cost',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'large_ship_cost',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'default_delivered_sessions',
					CForm::dd_required => false
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
			}
			else
			{

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'medium_ship_cost',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'large_ship_cost',
					CForm::dd_required => false
				));
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'default_delivered_sessions',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "store_name",
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "email_address",
					CForm::size => 50
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "address_line1",
					CForm::dd_required => true,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "address_line2",
					CForm::dd_required => false,
					CForm::size => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => "city",
					CForm::dd_required => true,
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
					CForm::dd_required => true,
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
					CForm::dd_required => false,
					CForm::height => 100,
					CForm::width => 400
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => 'address_latitude',
					CForm::css_class => 'gllpLatitude',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => true,
					CForm::name => 'address_longitude',
					CForm::css_class => 'gllpLongitude',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => true,
					CForm::name => 'telephone_day',
					CForm::dd_required => true,
					CForm::css_class => 'telephone',
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => true,
					CForm::name => 'telephone_evening',
					CForm::css_class => 'telephone',
					CForm::dd_required => true,
					CForm::length => 18
				));

				$Form->AddElement(array(
					CForm::type => CForm::Tel,
					CForm::disabled => false,
					CForm::name => 'telephone_sms',
					CForm::css_class => 'telephone',
					CForm::dd_required => false,
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
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_twitter',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_facebook',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => false,
					CForm::name => 'social_instagram',
					CForm::dd_required => false
				));

				$Form->AddElement(array(
					CForm::type => CForm::StatesProvinceDropDown,
					CForm::disabled => true,
					CForm::name => 'state_id',
					CForm::dd_required => true
				));
			}

			if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
			{
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "home_office_id",
					CForm::dd_required => true,
					CForm::length => 40
				));

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "gp_account_id",
					CForm::dd_required => true,
					CForm::length => 40
				));
			}

			$Form->AddElement(array(
				CForm::type => CForm::TextArea,
				CForm::disabled => $disabledForm,
				CForm::name => "store_description",
				CForm::dd_required => false,
				CForm::height => 100,
				CForm::width => 400
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

			if ($store->grand_opening_date != null)
			{
				$startTS = strtotime($store->grand_opening_date);
				$label = date("m/d/Y", $startTS);
				$tpl->assign('initDate', $label);
			}
			else
			{
				$tpl->assign('initDate', date("m/d/Y"));
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
				CForm::dd_required => true,
				CForm::value => CStore::HOURS
			));

			$Form->AddElement(array(
				CForm::type => CForm::RadioButton,
				CForm::disabled => $disabledForm,
				CForm::name => 'close_interval_type',
				CForm::dd_required => true,
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
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'observes_DST'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
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
				CForm::name => 'dream_taste_opt_out'
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::disabled => $disabledForm,
				CForm::name => 'publish_session_details'
			));

			$Form->AddElement(array(
				CForm::type => CForm::FranchiseDropDown,
				CForm::disabled => $disabledForm,
				CForm::name => 'franchise_id',
				CForm::dd_required => true
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
				CForm::type => CForm::Submit,
				CForm::name => "updateStore",
				CForm::disabled => $disabledForm,
				CForm::css_class => 'btn btn-primary btn-sm',
				CForm::value => "Save"
			));

			if (CStore::storeSupportsStoreSpecificDeposit($store->id))
			{
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::disabled => $disabledForm,
					CForm::name => "default_delayed_payment_deposit",
					CForm::number => true,
					CForm::dd_required => true
				));
			}

			$pkgSameAsStore = true;
			$letterSameAsStore = true;

			if (empty($store->pkg_ship_same_as_store))
			{
				$pkgSameAsStore = false;
			}

			if (empty($store->letter_ship_same_as_store))
			{
				$letterSameAsStore = false;
			}

			if ($pkgSameAsStore || $letterSameAsStore)
			{
				if ($pkgSameAsStore)
				{
					$Form->DefaultValues['pkg_ship_address_line1'] = $store->address_line1;
					$Form->DefaultValues['pkg_ship_address_line2'] = $store->address_line2;
					$Form->DefaultValues['pkg_ship_city'] = $store->city;
					$Form->DefaultValues['pkg_ship_state_id'] = $store->state_id;
					$Form->DefaultValues['pkg_ship_postal_code'] = $store->postal_code;
					$Form->DefaultValues['pkg_ship_telephone_day'] = $store->telephone_day;
				}

				if ($letterSameAsStore)
				{
					$Form->DefaultValues['letter_ship_address_line1'] = $store->address_line1;
					$Form->DefaultValues['letter_ship_address_line2'] = $store->address_line2;
					$Form->DefaultValues['letter_ship_city'] = $store->city;
					$Form->DefaultValues['letter_ship_state_id'] = $store->state_id;
					$Form->DefaultValues['letter_ship_postal_code'] = $store->postal_code;
					$Form->DefaultValues['letter_ship_telephone_day'] = $store->telephone_day;
				}
			}

			/*
			 * Package Shipping Address
			*/
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $pkgSameAsStore,
				CForm::name => 'pkg_ship_same_as_store',
				CForm::attribute => array('data-contact' => 'pkg_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $pkgSameAsStore,
				CForm::name => 'pkg_ship_is_commercial',
				CForm::disabled => $pkgSameAsStore,
				CForm::attribute => array('data-contact' => 'pkg_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_address_line1",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->address_line1
				),
				CForm::placeholder => "*Street Address",
				CForm::required_msg => "Please enter a street address.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_address_line2",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->address_line2
				),
				CForm::placeholder => "Suite / Unit",
				CForm::required_msg => "Please enter a suite number.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_city",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->city
				),
				CForm::placeholder => "*City",
				CForm::required_msg => "Please enter a city.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::StatesProvinceDropDown,
				CForm::name => 'pkg_ship_state_id',
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->state_id
				),
				CForm::required_msg => "Please select state.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_postal_code",
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->postal_code
				),
				CForm::placeholder => "*Postal Code",
				CForm::required_msg => "Please enter a postal code.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "pkg_ship_attn",
				CForm::attribute => array('data-contact' => 'pkg_ship'),
				CForm::placeholder => "Attention",
				CForm::required_msg => "Please enter attention to.",
				CForm::tooltip => true,
				CForm::disabled => false,
				CForm::dd_required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'pkg_ship_telephone_day',
				CForm::attribute => array(
					'data-contact' => 'pkg_ship',
					'data-store_value' => $store->telephone_day
				),
				CForm::placeholder => "*Primary Telephone",
				CForm::required_msg => "Please enter a telephone number.",
				CForm::tooltip => true,
				CForm::disabled => $pkgSameAsStore,
				CForm::dd_required => true,
				CForm::css_class => 'telephone'
			));

			/*
			 * Postal (letter) Mailing Address & Legal Notices
			*/
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $letterSameAsStore,
				CForm::name => 'letter_ship_same_as_store',
				CForm::attribute => array('data-contact' => 'letter_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::checked => $letterSameAsStore,
				CForm::name => 'letter_ship_is_commercial',
				CForm::disabled => $letterSameAsStore,
				CForm::attribute => array('data-contact' => 'letter_ship')
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_address_line1",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->address_line1
				),
				CForm::placeholder => "*Street Address",
				CForm::required_msg => "Please enter a street address.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_address_line2",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->address_line2
				),
				CForm::placeholder => "Suite / Unit",
				CForm::required_msg => "Please enter a suite number.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_city",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->city
				),
				CForm::placeholder => "*City",
				CForm::required_msg => "Please enter a city.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::StatesProvinceDropDown,
				CForm::name => 'letter_ship_state_id',
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->state_id
				),
				CForm::required_msg => "Please select state.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_postal_code",
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->postal_code
				),
				CForm::placeholder => "*Postal Code",
				CForm::required_msg => "Please enter a postal code.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "letter_ship_attn",
				CForm::attribute => array('data-contact' => 'letter_ship'),
				CForm::placeholder => "Attention",
				CForm::required_msg => "Please enter attention to.",
				CForm::tooltip => true,
				CForm::disabled => false,
				CForm::dd_required => false
			));

			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'letter_ship_telephone_day',
				CForm::attribute => array(
					'data-contact' => 'letter_ship',
					'data-store_value' => $store->telephone_day
				),
				CForm::placeholder => "*Primary Telephone",
				CForm::required_msg => "Please enter a telephone number.",
				CForm::tooltip => true,
				CForm::disabled => $letterSameAsStore,
				CForm::dd_required => true,
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
				CForm::dd_required => false
			));

			// Trade Area assignment
			$defaultTradeArea = 0;
			$tdarea = DAO_CFactory::create('store_trade_area');
			$tdarea->is_active = 1;
			$tdarea->store_id = $id;
			$tdarea->find(true);

			if (!empty($tdarea->trade_area_id))
			{
				$defaultTradeArea = $tdarea->trade_area_id;
			}

			$tradeareaarray = array();
			$tradeareas = DAO_CFactory::create('trade_area');
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
					CForm::dd_required => true,
					CForm::name => "regiondropdown",
					CForm::default_value => $defaultTradeArea,
					CForm::options => $tradeareaarray
				));
			}
			// Coach assignment

			$coachdefault = 0;
			$coachassign = DAO_CFactory::create('store_coach');
			$coachassign->is_active = 1;
			$coachassign->store_id = $id;
			$coachassign->find(true);

			if (!empty($coachassign->id))
			{
				$coachdefault = $coachassign->coach_id;
			}

			$sql = 'SELECT coach.id, `user`.`primary_email`,`user`.`firstname`,`user`.`lastname`, administrator FROM `coach` INNER JOIN `user` ON `coach`.`user_id` = `user`.`id` WHERE `coach`.`active` = 1 AND user.is_deleted = 0 ORDER BY lastname';
			$coacharr = array();
			$coach = DAO_CFactory::create('coach');
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
					CForm::dd_required => true,
					CForm::default_value => $coachdefault,
					CForm::options => $coacharr
				));
			}

			// handle form submit
			if ($Form->value('updateStore'))
			{
				$hadError = false;
				$storeUpdated = clone($store);
				$storeUpdated->setFrom($Form->values());

				// cleanup
				$storeUpdated->store_description = trim($storeUpdated->store_description);
				$storeUpdated->address_directions = trim($storeUpdated->address_directions);

				if ($storeUpdated->supports_plate_points && !$store_already_opted_into_plate_points)
				{
					if (empty($_POST['supports_plate_points_signature']))
					{
						$tpl->setErrorMsg('Opting in to PLATEPOINTS requires the full name of the person opting the store in.');
						$hadError = true;
					}
					else
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $store->id, 'null', 300, 'null', 'null', 'null', CGPC::do_clean($_POST['supports_plate_points_signature'], TYPE_STR));
					}
				}

				if ((int)$storeUpdated->supports_retention_programs != (int)$current_retention_program_setting)
				{
					if ($storeUpdated->supports_retention_programs)
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $store->id, 'null', 301, 'null', 'null', 'null', "Store has opted into retention program");
					}
					else
					{
						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $store->id, 'null', 302, 'null', 'null', 'null', "Store has opted out of retention program");
					}
				}

				if (!$hadError)
				{
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
						$tpl->assign('initDate', $label);
					}

					if (isset($_POST['default_delayed_payment_deposit']) && $_POST['default_delayed_payment_deposit'] < 20)
					{
						$storeUpdated->default_delayed_payment_deposit = 20;
						$tpl->setStatusMsg("Delayed Payment deposit must be at least $20. The deposit was set to $20.");
					}

					// store is changing franchise entity, so remove old owners and assign new
					if ($storeUpdated->franchise_id != $store->franchise_id)
					{
						$oldOwners = DAO_CFactory::create('user_to_franchise');
						$oldOwners->franchise_id = $store->franchise_id;
						$oldOwners->find();
						while ($oldOwners->fetch())
						{
							$removeOwner = DAO_CFactory::create('user_to_store');
							$removeOwner->user_id = $oldOwners->user_id;
							$removeOwner->store_id = $store->id;
							$removeOwner->find();
							while ($removeOwner->fetch())
							{
								$removeOwner->delete();
							}
						}

						$newOwners = DAO_CFactory::create('user_to_franchise');
						$newOwners->franchise_id = $storeUpdated->franchise_id;
						$newOwners->find();
						while ($newOwners->fetch())
						{
							$addOwner = DAO_CFactory::create('user_to_store');
							$addOwner->user_id = $newOwners->user_id;
							$addOwner->store_id = $store->id;
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
						$MerchantInfo = DAO_CFactory::create('merchant_accounts');
						$MerchantInfo->store_id = $store->id;
						$MerchantInfo->franchise_id = $store->franchise_id;
						$found = $MerchantInfo->find(true);
						if ($found > 1)
						{
							throw new Exception('more than one merchant account tow found');
						}
						$originalInfo = clone($MerchantInfo);
						$MerchantInfo->franchise_id = $storeUpdated->franchise_id;
						$MerchantInfo->update($originalInfo);

						CStoreHistory::recordStoreEvent(CUser::getCurrentUser()->id, $store->id, 'null', 400, 'null', 'null', 'null', 'merchant_accounts.id ' . $MerchantInfo->id . ' franchise_id changed from ' . $store->franchise_id . ' to ' . $storeUpdated->franchise_id);
					}
					$storeUpdated->update($store);
					$store = $storeUpdated;

					//update credit card choices
					if ($Form->value('credit_card_discover') != $Form->DefaultValues['credit_card_discover'])
					{
						$ccTypeObj = DAO_CFactory::create('payment_credit_card_type');
						$ccTypeObj->credit_card_type = CPayment::DISCOVERCARD;
						$ccTypeObj->store_id = $store->id;
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
						$ccTypeObj = DAO_CFactory::create('payment_credit_card_type');
						$ccTypeObj->credit_card_type = CPayment::AMERICANEXPRESS;
						$ccTypeObj->store_id = $store->id;
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
							$trade = DAO_CFactory::create('store_trade_area');
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
							$trade = DAO_CFactory::create('store_trade_area');
							$trade->store_id = $id;
							if ($trade->find(true))
							{
								$trade->delete();
							}
						}
						$currentCoachID = $Form->value('coachdropdown');
						if ($currentCoachID > 0)
						{
							$coachassign = DAO_CFactory::create('store_coach');
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
							$coachassign = DAO_CFactory::create('store_coach');
							$coachassign->store_id = $id;
							if ($coachassign->find(true))
							{
								$coachassign->delete();
							}
						}
					}

					// update job positions
					$job_array = CStore::setAvailableJobs($id, CGPC::do_clean($_POST['job_position'], TYPE_ARRAY));

					$tpl->setToastMsg(array('message' => 'The store properties have been updated.'));
					CApp::bounce('/backoffice/store_details?id=' . $id);
				}
			}

			// store available job positions
			$job_array = CStore::getStoreJobArray($id);

			$storeArray = $store->toArray();
			$storeArray['map'] = $store->generateMapLink();
			$storeArray['linear_address'] = $store->generateLinearAddress();

			$storeArray['personnel'] = CStore::getStorePersonnel($id);

			$tpl->assign('job_array', $job_array);
			$tpl->assign('siteadminoverride', $siteadminoverride);
			$tpl->assign('grand_opening_label', $label);
			$tpl->assign('store', $storeArray);
			$tpl->assign('form_store_details', $Form->Render());
		}

		$back = '/backoffice/list_stores';

		if (array_key_exists('back', $_GET) && $_GET['back'])
		{
			$back = $_GET['back'];
		}

		$tpl->assign('back', $back);
	}
}

?>