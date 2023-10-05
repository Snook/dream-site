<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_store_contact_information extends CPageAdminOnly {

	private $current_store_id = null;

	private $show = array(
		'store_selector' => false
	);

	function runHomeOfficeManager()
	{
		$this->show['store_selector'] = true;
		$this->runStoreContactInformation();
	}

	function runFranchiseOwner()
	{
		$this->runStoreContactInformation();
	}

	function runSiteAdmin()
	{
		$this->show['store_selector'] = true;
		$this->runStoreContactInformation();
	}

	function runStoreContactInformation()
	{
		$tpl = CApp::instance()->template();

		$tpl->assign('timestamp_updated', false);

		$Form = new CForm();
		$Form->Repost = true;

		if ($this->show['store_selector'])
		{
			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();

			$Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true) );

			$this->current_store_id = $Form->value('store');
		}
		else
		{
			$this->current_store_id =  CBrowserSession::getCurrentFadminStore();
		}

		if (!empty($_POST['submit']))
		{
			$StoreContact = DAO_CFactory::create('store_contact_information');
			$StoreContact->store_id = $this->current_store_id;
			$StoreContact->find();

			while ($StoreContact->fetch())
			{
				$StoreContact->delete();
			}

			$StoreContact = DAO_CFactory::create('store_contact_information');
			$StoreContact->store_id = $this->current_store_id;

			foreach($StoreContact AS $column => $value)
			{
				if (!empty($_POST[$column]))
				{
					if ($column == 'pkg_ship_same_as_store' || $column == 'letter_ship_same_as_store')
					{
						if ($_POST[$column] == 'on')
						{
							$_POST[$column] = 1;
						}
						else
						{
							$_POST[$column] = 0;
						}
					}

					$StoreContact->{$column} = CGPC::do_clean($_POST[$column],TYPE_STR); // I think this loops over non-int columns
					// go with TYPE_STR to be safe
				}
			}


			$StoreContact->updated_by = CUser::getCurrentUser()->id;
			$StoreContact->insert();

			$tpl->setStatusMsg('Contact information updated. Thank you.');
		}

		$pkgSameAsStore = true;
		$letterSameAsStore = true;
		$owner2Disabled = true;
		$owner3Disabled = true;
		$owner4Disabled = true;
		$manager1Disabled = true;

		$StoreContact = DAO_CFactory::create('store_contact_information');
		$StoreContact->store_id = $this->current_store_id;

		if ($StoreContact->find(true))
		{
			$tpl->assign('timestamp_updated', $StoreContact->timestamp_updated);

			$UpdatedBy = DAO_CFactory::create('user');
			$UpdatedBy->id = $StoreContact->updated_by;
			$UpdatedBy->find(true);

			$tpl->assign('updated_by_user_id', $UpdatedBy->id);
			$tpl->assign('updated_by_firstname', $UpdatedBy->firstname);
			$tpl->assign('updated_by_lastname', $UpdatedBy->lastname);

			foreach($StoreContact AS $key => $value)
			{
				$Form->DefaultValues[$key] = $value;
			}

			if (empty($StoreContact->pkg_ship_same_as_store))
			{
				$pkgSameAsStore = false;
			}

			if (empty($StoreContact->letter_ship_same_as_store))
			{
				$letterSameAsStore = false;
			}

			if (!empty($StoreContact->owner_2_name))
			{
				$owner2Disabled = false;
			}

			if (!empty($StoreContact->owner_3_name))
			{
				$owner3Disabled = false;
			}

			if (!empty($StoreContact->owner_4_name))
			{
				$owner4Disabled = false;
			}

			if (!empty($StoreContact->manager_1_name))
			{
				$manager1Disabled = false;
			}
		}

		if ($pkgSameAsStore || $letterSameAsStore)
		{
			$Store = DAO_CFactory::create('store');
			$Store->id = $this->current_store_id;
			$Store->find(true);

			if ($pkgSameAsStore)
			{
				$Form->DefaultValues['pkg_ship_address_line1'] = $Store->address_line1;
				$Form->DefaultValues['pkg_ship_address_line2'] = $Store->address_line2;
				$Form->DefaultValues['pkg_ship_city'] = $Store->city;
				$Form->DefaultValues['pkg_ship_state_id'] = $Store->state_id;
				$Form->DefaultValues['pkg_ship_postal_code'] = $Store->postal_code;
				$Form->DefaultValues['pkg_ship_telephone_day'] = $Store->telephone_day;
			}

			if ($letterSameAsStore)
			{
				$Form->DefaultValues['letter_ship_address_line1'] = $Store->address_line1;
				$Form->DefaultValues['letter_ship_address_line2'] = $Store->address_line2;
				$Form->DefaultValues['letter_ship_city'] = $Store->city;
				$Form->DefaultValues['letter_ship_state_id'] = $Store->state_id;
				$Form->DefaultValues['letter_ship_postal_code'] = $Store->postal_code;
				$Form->DefaultValues['letter_ship_telephone_day'] = $Store->telephone_day;
			}
		}

		/*
		 * Package Shipping Address
		 */
		$Form->AddElement(array(CForm::type=> CForm::CheckBox,
			CForm::checked => $pkgSameAsStore,
			CForm::name => 'pkg_ship_same_as_store',
			CForm::attribute => array('data-contact' => 'pkg_ship')));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "pkg_ship_address_line1",
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->address_line1),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "pkg_ship_address_line2",
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->address_line2),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "pkg_ship_city",
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->city),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'pkg_ship_state_id',
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->state_id),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "pkg_ship_postal_code",
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->postal_code),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "pkg_ship_attn",
			CForm::attribute => array('data-contact' => 'pkg_ship'),
			CForm::placeholder => "Attention",
			CForm::required_msg => "Please enter attention to.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'pkg_ship_telephone_day',
			CForm::attribute => array('data-contact' => 'pkg_ship', 'data-store_value' => $Store->telephone_day),
			CForm::placeholder => "*Primary Telephone",
			CForm::required_msg => "Please enter a telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $pkgSameAsStore,
			CForm::dd_required => true,
			CForm::css_class => 'telephone'));

		/*
		 * Postal (letter) Mailing Address & Legal Notices
		*/
		$Form->AddElement(array(CForm::type=> CForm::CheckBox,
			CForm::checked => $letterSameAsStore,
			CForm::name => 'letter_ship_same_as_store',
			CForm::attribute => array('data-contact' => 'letter_ship')));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "letter_ship_address_line1",
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->address_line1),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "letter_ship_address_line2",
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->address_line2),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "letter_ship_city",
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->city),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'letter_ship_state_id',
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->state_id),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "letter_ship_postal_code",
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->postal_code),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "letter_ship_attn",
			CForm::attribute => array('data-contact' => 'letter_ship'),
			CForm::placeholder => "Attention",
			CForm::required_msg => "Please enter attention to.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'letter_ship_telephone_day',
			CForm::attribute => array('data-contact' => 'letter_ship', 'data-store_value' => $Store->telephone_day),
			CForm::placeholder => "*Primary Telephone",
			CForm::required_msg => "Please enter a telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $letterSameAsStore,
			CForm::dd_required => true,
			CForm::css_class => 'telephone'));

		/*
		 * Owner #1 Personal Contact Information
		*/
		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_name",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*First &amp; Last Name",
			CForm::required_msg => "Please enter first and last name.",
			CForm::tooltip => true,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_nickname",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "Nickname",
			CForm::required_msg => "Please enter a nickname.",
			CForm::tooltip => true,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_address_line1",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_address_line2",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_city",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'owner_1_state_id',
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_1_postal_code",
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_1_telephone_primary',
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*Mobile Telephone",
			CForm::required_msg => "Please enter a mobile telephone number.",
			CForm::tooltip => true,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_1_telephone_secondary',
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "Home Telephone",
			CForm::required_msg => "Please enter a home telephone number.",
			CForm::tooltip => true,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => 'owner_1_email_address',
			CForm::attribute => array('data-contact' => 'owner_1'),
			CForm::placeholder => "*Personal Email",
			CForm::required_msg => "Please enter an email address.",
			CForm::tooltip => true,
			CForm::dd_required => true,
			CForm::email => true));

		/*
		 * Owner #2 Personal Contact Information
		*/
		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_name",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "First &amp; Last Name",
			CForm::required_msg => "Please enter first and last name.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_nickname",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "Nickname",
			CForm::required_msg => "Please enter a nickname.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_address_line1",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_address_line2",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_city",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'owner_2_state_id',
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_2_postal_code",
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_2_telephone_primary',
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "*Mobile Telephone",
			CForm::required_msg => "Please enter a mobile telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_2_telephone_secondary',
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "Home Telephone",
			CForm::required_msg => "Please enter a home telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => 'owner_2_email_address',
			CForm::attribute => array('data-contact' => 'owner_2'),
			CForm::placeholder => "*Personal Email",
			CForm::required_msg => "Please enter an email address.",
			CForm::tooltip => true,
			CForm::disabled => $owner2Disabled,
			CForm::dd_required => true,
			CForm::email => true));

		/*
		 * Owner #3 Personal Contact Information
		*/
		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_name",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "First &amp; Last Name",
			CForm::required_msg => "Please enter first and last name.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_nickname",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "Nickname",
			CForm::required_msg => "Please enter a nickname.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_address_line1",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_address_line2",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_city",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'owner_3_state_id',
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_3_postal_code",
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_3_telephone_primary',
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "*Mobile Telephone",
			CForm::required_msg => "Please enter a mobile telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_3_telephone_secondary',
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "Home Telephone",
			CForm::required_msg => "Please enter a home telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => 'owner_3_email_address',
			CForm::attribute => array('data-contact' => 'owner_3'),
			CForm::placeholder => "*Personal Email",
			CForm::required_msg => "Please enter an email address.",
			CForm::tooltip => true,
			CForm::disabled => $owner3Disabled,
			CForm::dd_required => true,
			CForm::email => true));

		/*
		 * Owner #4 Personal Contact Information
		*/
		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_name",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "First &amp; Last Name",
			CForm::required_msg => "Please enter first and last name.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_nickname",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "Nickname",
			CForm::required_msg => "Please enter a nickname.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_address_line1",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_address_line2",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "Suite / Unit",
			CForm::required_msg => "Please enter a suite number.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_city",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'owner_4_state_id',
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::required_msg => "Please select state.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "owner_4_postal_code",
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "*Postal Code",
			CForm::required_msg => "Please enter a postal code.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => true));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_4_telephone_primary',
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "*Mobile Telephone",
			CForm::required_msg => "Please enter a mobile telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'owner_4_telephone_secondary',
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "Home Telephone",
			CForm::required_msg => "Please enter a home telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => 'owner_4_email_address',
			CForm::attribute => array('data-contact' => 'owner_4'),
			CForm::placeholder => "*Personal Email",
			CForm::required_msg => "Please enter an email address.",
			CForm::tooltip => true,
			CForm::disabled => $owner4Disabled,
			CForm::dd_required => true,
			CForm::email => true));

		/*
		 * Manager #1 Personal Contact Information
		*/
		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "manager_1_name",
			CForm::attribute => array('data-contact' => 'manager_1'),
			CForm::placeholder => "First &amp; Last Name",
			CForm::required_msg => "Please enter first and last name.",
			CForm::tooltip => true,
			CForm::disabled => false,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Text,
			CForm::name => "manager_1_nickname",
			CForm::attribute => array('data-contact' => 'manager_1'),
			CForm::placeholder => "Nickname",
			CForm::required_msg => "Please enter a nickname.",
			CForm::tooltip => true,
			CForm::disabled => $manager1Disabled,
			CForm::dd_required => false));

		$Form->AddElement(array(CForm::type=> CForm::Tel,
			CForm::name => 'manager_1_telephone_primary',
			CForm::attribute => array('data-contact' => 'manager_1'),
			CForm::placeholder => "*Mobile Telephone",
			CForm::required_msg => "Please enter a mobile telephone number.",
			CForm::tooltip => true,
			CForm::disabled => $manager1Disabled,
			CForm::dd_required => false,
			CForm::css_class => 'telephone'));

		/*
		 * Save button
		*/
		$Form->AddElement(array(CForm::type=> CForm::Submit,
			CForm::name => "submit",
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => "Update Contact Information"));

		$tpl->assign('form_store_details', $Form->Render());
		$tpl->assign('show', $this->show);
	}
}
?>