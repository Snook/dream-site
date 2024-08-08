<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/OrdersHelper.php");
require_once("includes/DAO/BusinessObject/CSession.php");
require_once("includes/DAO/BusinessObject/CEmail.php");
require_once("create_session.php");

class page_admin_edit_session extends CPageAdminOnly
{
	public function __construct()
	{
		parent::__construct();

		$this->Template->setScript('foot', SCRIPT_PATH . '/admin/create_edit_session.min.js');
		$this->Template->assign('page_title', 'Edit Session');
		$this->Template->assign('topnav', 'sessions');
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$tpl = CApp::instance()->template();

		// set up create session form
		$SessionForm = new CForm();
		$SessionForm->Repost = true;
		$SessionForm->Bootstrap = true;

		if (isset($_REQUEST["session"]) && $_REQUEST["session"])
		{
			$session_id = CGPC::do_clean($_REQUEST["session"], TYPE_INT);
		}
		else
		{
			throw new Exception('No session id requested');
		}

		$DAO_session = DAO_CFactory::create('session');
		$DAO_session->id = $session_id;
		$DAO_session->find(true);

		$sessionDetails = CSession::getSessionDetail($session_id, false);

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $DAO_session->menu_id;
		$DAO_menu->find(true);

		if (!$DAO_menu->isEnabled_Backoffice_SessionEditing())
		{
			$tpl->setStatusMsg('This menu is not available for editing yet.');
			CApp::bounce('/backoffice');
		}

		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->id = $DAO_session->store_id;
		$DAO_store->find(true);

		if ($DAO_store->isDistributionCenter())
		{
			CApp::bounce('/backoffice/edit-session-delivered?session=' . $DAO_session->id);
		}

		$DAO_store->getStorePickupLocations();

		$tpl->assign('allowsMealCustomization', $DAO_store->supports_meal_customization);

		// Set up values in form
		$SessionForm->DefaultValues = array_merge($SessionForm->DefaultValues, $DAO_session->toArray());
		if ($SessionForm->DefaultValues["session_password"] == "NULL")
		{
			$SessionForm->DefaultValues["session_password"] = "";
		}

		$markup = $DAO_store->getMarkUpMultiObj($DAO_session->menu_id);

		if (!empty($DAO_session->session_assembly_fee) || $DAO_session->session_assembly_fee == '0.00')
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = $DAO_session->session_assembly_fee;
		}
		else if ($markup)
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = $markup->assembly_fee;
		}

		if (!empty($DAO_session->session_delivery_fee) || $DAO_session->session_delivery_fee == '0.00')
		{
			$SessionForm->DefaultValues["session_delivery_fee"] = $DAO_session->session_delivery_fee;
		}
		else if ($DAO_store->delivery_fee)
		{
			$SessionForm->DefaultValues["session_delivery_fee"] = $DAO_store->delivery_fee;
		}

		$tpl->assign('allow_assembly_fee', OrdersHelper::allow_assembly_fee($DAO_session->menu_id));
		if (!OrdersHelper::allow_assembly_fee($DAO_session->menu_id))
		{
			$SessionForm->DefaultValues['session_assembly_fee'] = 0;
			if ($markup)
			{
				$markup->assembly_fee = '0.00';
			}
			$DAO_session->session_assembly_fee = '0.00';
		}

		$SessionForm->DefaultValues["close_interval_type"] = $DAO_session->determineSessionCloseEnum();
		$SessionForm->DefaultValues["custom_close_interval"] = $DAO_session->getScheduleCloseInterval();

		$SessionForm->DefaultValues["meal_customization_close_interval_type"] = $DAO_session->determineSessionMealCustomizationCloseEnum();
		if ($SessionForm->DefaultValues["meal_customization_close_interval_type"] == CSession::FOUR_FULL_DAYS)
		{
			$SessionForm->DefaultValues["meal_customization_close_interval"] = 96;
		}
		else
		{
			$SessionForm->DefaultValues["meal_customization_close_interval"] = $DAO_session->getScheduleMealCustomizationCloseInterval();
		}

		$SessionForm->DefaultValues["session_sneak_peak"] = $DAO_session->sneak_peak == 1 ? true : false;

		$numBookings = $DAO_session->get_num_bookings();

		$canEditSessionType = false;
		if ($DAO_session->isStandard() || $DAO_session->isStandardPrivate() || $DAO_session->isMadeForYou())
		{
			$canEditSessionType = true;
		}

		$DAO_session_to_menu_item = DAO_CFactory::create('session_to_menu_item');
		$DAO_session_to_menu_item->session_id = $DAO_session->id;
		$DAO_session_to_menu_item->find();
		$stmi_array = array();
		while ($DAO_session_to_menu_item->fetch())
		{
			$stmi_array[$DAO_session_to_menu_item->menu_item_id] = $DAO_session_to_menu_item->menu_item_id;
		}

		// Session host
		$DAO_session_properties = DAO_CFactory::create('session_properties');
		$DAO_session_properties->session_id = $DAO_session->id;
		$DAO_session_properties->find(true);

		if (!empty($DAO_session_properties->session_host))
		{
			$hostInfo = DAO_CFactory::create('user');
			$hostInfo->id = $DAO_session_properties->session_host;
			$hostInfo->selectAdd();
			$hostInfo->selectAdd("CONCAT(firstname,' ',lastname) as fullname, primary_email");
			$hostInfo->find(true);
			$tpl->assign('pp_hostInfo', $hostInfo->fullname . ' ( #' . $hostInfo->id . ' )');
			$SessionForm->DefaultValues['session_host'] = $hostInfo->primary_email;
		}

		if ($DAO_session_properties->menu_pricing_method)
		{
			$SessionForm->DefaultValues["menu_pricing_method"] = $DAO_session_properties->menu_pricing_method;
		}
		else
		{
			$SessionForm->DefaultValues["menu_pricing_method"] = 'USE_CURRENT';
		}

		if (isset($_POST['submit_delete']))
		{
			if ($numBookings)
			{
				$tpl->setErrorMsg('The session could not be deleted because there are orders booked against it.');
			}
			else
			{
				$DAO_session_to_menu_item = DAO_CFactory::create('session_to_menu_item');
				$DAO_session_to_menu_item->session_id = $DAO_session->id;
				$DAO_session_to_menu_item->find();
				while ($DAO_session_to_menu_item->fetch())
				{
					$DAO_session_to_menu_item->delete();
				}

				$DAO_session->delete();

				if (!empty($DAO_session->is_deleted) && !empty($DAO_session_properties->id))
				{
					$DAO_session_properties->delete();
				}

				// delete any session_rsvp
				CSession::deleteSessionRSVP($DAO_session->id);

				$tpl->setStatusMsg('The session was successfully deleted.');
				CApp::bounce('/backoffice/session-mgr');

				return;
			}
		}

		// handle opening(publishing) and closing the session
		if (isset($_POST['open_close_submit']))
		{
			if ($_POST['open_close_submit'] == 'Close Session')
			{
				$DAO_session->session_publish_state = CSession::CLOSED;

				$rslt = $DAO_session->update();
				if ($rslt)
				{
					$tpl->setStatusMsg('The session was closed');
				}
				else
				{
					$tpl->setErrorMsg('The session could not be saved');
				}

				$SessionForm->DefaultValues['session_publish_state'] = CSession::CLOSED;
			}
			else
			{
				$DAO_session->session_publish_state = CSession::PUBLISHED;

				$rslt = $DAO_session->update();
				if ($rslt)
				{
					$tpl->setStatusMsg('The session was opened');
				}
				else
				{
					$tpl->setErrorMsg('The session could not be saved');
				}

				$SessionForm->DefaultValues['session_publish_state'] = CSession::PUBLISHED;
			}

			if (isset($_GET['back']))
			{
				CApp::bounce($_GET['back']);
			}
		}

		$SessionForm->DefaultValues["session_type_subtype"] = $DAO_session->session_type_subtype;
		$SessionForm->DefaultValues["session_pickup_location"] = $DAO_session_properties->store_pickup_location_id;
		$SessionForm->DefaultValues["fundraiser_recipient"] = $DAO_session_properties->fundraiser_id;
		$SessionForm->DefaultValues["dream_taste_theme"] = $DAO_session_properties->dream_taste_event_id;
		$SessionForm->DefaultValues["session_date"] = CTemplate::dateTimeFormat($DAO_session->session_start, YEAR_MONTH_DAY);
		$SessionForm->DefaultValues["session_time"] = CTemplate::dateTimeFormat($DAO_session->session_start, HH_MM);
		$session_end_time = date("Y-m-d H:i:s", strtotime($DAO_session->session_start) + ($DAO_session->duration_minutes * 60));
		$SessionForm->DefaultValues["session_end_time"] = CTemplate::dateTimeFormat($session_end_time, HH_MM);

		$SessionForm->DefaultValues["introductory_slots"] = ($DAO_store->storeSupportsIntroOrders($DAO_menu->id)) ? $DAO_session->introductory_slots : 0;

		$SessionForm->DefaultValues["standard_session_type_subtype"] = ((!empty($DAO_session->session_password) ? CSession::PRIVATE_SESSION : CSession::STANDARD));
		$session_types = array(CSession::STANDARD => 'Assembly');

		if ($DAO_session->isMadeForYou() || $DAO_store->supports_special_events)
		{
			$session_types = array_merge($session_types, array(CSession::MADE_FOR_YOU => 'Pick Up & Home Delivery'));
		}

		$DreamTasteTypeInfoArray = CDreamTasteEvent::dreamTasteProperties($DAO_session->menu_id, $DAO_store);
		$dream_taste_themes = array('' => 'Select Theme'); // just here to satisfy CForm, overwritten if $DreamTasteTypeInfoArray is true

		if ($DreamTasteTypeInfoArray)
		{
			$session_types = array_merge($session_types, array(
				CSession::DREAM_TASTE => array(
					'title' => 'Workshops',
					CForm::disabled => (($canEditSessionType) ? true : false)
				)
			));

			$dream_taste_themes = array();

			foreach ($DreamTasteTypeInfoArray as $id => $tasteTypeInfo)
			{
				$dream_taste_themes[$tasteTypeInfo['id']] = array(
					'title' => $tasteTypeInfo['title'],
					'data' => array(
						'data-host_required' => $tasteTypeInfo['host_required'],
						'data-password_required' => $tasteTypeInfo['password_required']
					)
				);
			}
		}

		$fundraiserSelectArray = array('' => 'Select Fundraiser');
		$fundraiser_themes = array('' => 'Select Fundraiser');

		if ($DAO_session->isFundraiser() || $DAO_store->supports_fundraiser)
		{
			// get list of fundraiser themes available for the month
			$FundraiserTypeInfoArray = CFundraiser::fundraiserProperties($DAO_session->menu_id, $DAO_store);

			if ($FundraiserTypeInfoArray)
			{
				$session_types = array_merge($session_types, array(
					CSession::FUNDRAISER => array(
						'title' => 'Fundraiser',
						CForm::disabled => (($canEditSessionType) ? true : false)
					)
				));

				$fundraiser_themes = array();

				foreach ($FundraiserTypeInfoArray as $id => $fundraiserTypeInfo)
				{
					$fundraiser_themes[$fundraiserTypeInfo['id']] = array(
						'title' => $fundraiserTypeInfo['title'],
						'data' => array(
							'data-host_required' => $fundraiserTypeInfo['host_required'],
							'data-password_required' => $fundraiserTypeInfo['password_required']
						)
					);
				}

				// list of fundraisers
				$fundraiserArray = CFundraiser::storeFundraiserArray($DAO_store);

				if (!empty($fundraiserArray))
				{
					foreach ($fundraiserArray as $id => $fundraiserArray)
					{
						if (!empty($fundraiserArray->active))
						{
							$fundraiserSelectArray[$id] = $fundraiserArray->fundraiser_name;
						}
					}

					if (!empty($fundraiserSelectArray))
					{
						$fundraiserSelectArray += $fundraiserSelectArray;
					}
				}
			}

			if (!empty($sessionDetails['dream_taste_event_prop_id']))
			{
				$SessionForm->DefaultValues["fundraiser_theme"] = $sessionDetails['dream_taste_event_prop_id'];
			}
		}

		$leadOpts = CSession::retreiveSessionLeadArray($DAO_store->id, true);

		if (!empty($DAO_session->session_lead))
		{
			$SessionForm->DefaultValues["session_lead"] = $DAO_session->session_lead;

			if (!array_key_exists($DAO_session->session_lead, $leadOpts))
			{
				$leadObj = DAO_CFactory::create('user');
				$leadObj->id = $DAO_session->session_lead;
				$leadObj->selectAdd();
				$leadObj->selectAdd('firstname, lastname');
				if ($leadObj->find(true))
				{
					$leadOpts[$DAO_session->session_lead] = $leadObj->firstname;
				}
				else
				{
					$SessionForm->DefaultValues["session_lead"] = 0;
				}
			}
		}
		else
		{
			$SessionForm->DefaultValues["session_lead"] = 0;
		}

		$leadOpts = array_reverse($leadOpts, true);
		$leadOpts[0] = "Please Select a Lead";
		$leadOpts = array_reverse($leadOpts, true);

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::checked => ((!empty($DAO_session->session_assembly_fee) || ($DAO_session->session_assembly_fee == '0.00')) ? true : false),
			CForm::label => '$',
			CForm::tooltip => 'Enable Override',
			CForm::name => 'session_assembly_fee_enable'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'session_assembly_fee',
			CForm::attribute => array('data-valdefault' => ((!empty($markup->assembly_fee)) ? $markup->assembly_fee : '0.00'))
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::checked => ((!empty($DAO_session->session_delivery_fee) || ($DAO_session->session_delivery_fee == '0.00')) ? true : false),
			CForm::label => '$',
			CForm::tooltip => 'Enable Override',
			CForm::name => 'session_delivery_fee_enable'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'session_delivery_fee',
			CForm::min => '0',
			CForm::step => '0.01',
			CForm::attribute => array('data-valdefault' => ((!empty($DAO_store->delivery_fee)) ? $DAO_store->delivery_fee : '0.00'))
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'session_lead',
			CForm::options => $leadOpts
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => true,
			CForm::options => array($DAO_menu->id => $DAO_menu->menu_name),
			CForm::name => 'menu'
		));

		$tpl->assign('hasDreamtTasteType', array_key_exists(CSession::DREAM_TASTE, $session_types));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => (($canEditSessionType) ? false : true),
			CForm::options => $session_types,
			CForm::name => 'session_type'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => (($numBookings > 0 || !$canEditSessionType) ? true : false),
			CForm::dd_type => 'disable_locked',
			CForm::options => $dream_taste_themes,
			CForm::name => 'dream_taste_theme'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => (($numBookings > 0 || !$canEditSessionType) ? true : false),
			CForm::dd_type => 'disable_locked',
			CForm::options => $fundraiser_themes,
			CForm::name => 'fundraiser_theme'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::dd_type => 'disable_locked',
			CForm::options => $fundraiserSelectArray,
			CForm::name => 'fundraiser_recipient'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::date => true,
			CForm::required => true,
			CForm::min => CTemplate::dateTimeFormat($DAO_menu->global_menu_start_date, YEAR_MONTH_DAY),
			CForm::max => CTemplate::dateTimeFormat($DAO_menu->global_menu_end_date, YEAR_MONTH_DAY),
			CForm::name => 'session_date'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::required => true,
			CForm::name => 'session_time'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::required => true,
			CForm::name => 'session_end_time'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::label => 'Hours Prior',
			CForm::value => CSession::HOURS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::label => '1 Day Prior',
			CForm::value => CSession::ONE_FULL_DAY
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::label => 'Hours Prior',
			CForm::value => CSession::HOURS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::label => '4 Day Prior',
			CForm::value => CSession::FOUR_FULL_DAYS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => 'Email address or user id',
			CForm::autocomplete => false,
			CForm::name => 'session_host'
		));

		$hoursCloseOptions = array();
		for ($x = 0; $x < 168; $x++)
		{
			$hoursCloseOptions[] = $x;
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'custom_close_interval',
			CForm::options => $hoursCloseOptions
		));

		$hoursCloseOptionsCustomization = array();
		for ($x = 0; $x < 241; $x++)
		{
			$hoursCloseOptionsCustomization[] = $x;
		}
		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'meal_customization_close_interval',
			CForm::options => $hoursCloseOptionsCustomization
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::min => 0,
			CForm::name => 'available_slots'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::min => 0,
			CForm::name => 'introductory_slots'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::min => 0,
			CForm::name => 'duration_minutes'
		));

		$storePickupLocation = array('' => 'Select Location');

		foreach ($DAO_store->remoteLocations as $id => $location)
		{
			$storePickupLocation[$id] = array(
				'title' => $location->location_title . ' - ' . $location->address_line1 . (!empty($location->address_line2) ? ' ' . $location->address_line2 : '') . ', ' . $location->city . ', ' . $location->state_id . ' ' . $location->postal_code,
				'data' => array(
					'data-location_title' => $location->location_title,
					'data-default_session_override' => (($location->default_session_override === null) ? 'false' : $location->default_session_override)
				)
			);
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::required => false,
			CForm::options => $storePickupLocation,
			CForm::name => 'session_pickup_location'
		));

		$subtypeArray = array(
			'' => 'Pick Up'
		);

		if ($DAO_store->supports_delivery || $DAO_session->session_type_subtype == CSession::DELIVERY || $DAO_session->session_type_subtype == CSession::DELIVERY_PRIVATE)
		{
			$subtypeArray[CSession::DELIVERY] = 'Home Delivery';
			$subtypeArray[CSession::DELIVERY_PRIVATE] = 'Home Delivery - Private';
		}

		if (!empty($DAO_store->remoteLocations) && ($DAO_store->supports_offsite_pickup || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
		{
			$subtypeArray[CSession::REMOTE_PICKUP] = 'Community Pick Up';
			$subtypeArray[CSession::REMOTE_PICKUP_PRIVATE] = 'Community Pick Up - Private';
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::options => $subtypeArray,
			CForm::name => 'session_type_subtype'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => array(
				CSession::STANDARD => 'Standard',
				CSession::PRIVATE_SESSION => 'Private Party'
			),
			CForm::name => 'standard_session_type_subtype'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::placeholder => 'Optional administrative notes are only viewable by franchise personnel.',
			CForm::name => 'admin_notes',
			CForm::css_class => 'dd-strip-tags'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 50,
			CForm::placeholder => "Optional. Shows on the store locations' calendar and in the order process. E.g. Delivery to Seattle addresses only.",
			CForm::name => 'session_title',
			CForm::css_class => 'dd-strip-tags'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::placeholder => 'Optional session details. Viewable by everyone.',
			CForm::name => 'session_details',
			CForm::css_class => 'dd-strip-tags'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'do_send_pp_notification',
			CForm::checked => true,
			CForm::label => 'Send Email'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'session_sneak_peak'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => true,
			CForm::name => 'session_password'
		));

		// ---------------------------------------------- Session Discount
		$SessionForm->DefaultValues["discount_value"] = 0;

		if ($DAO_session->session_discount_id != null)
		{
			$discount = DAO_CFactory::create('session_discount');
			$discount->id = $DAO_session->session_discount_id;
			if ($discount->find(true))
			{
				$SessionForm->DefaultValues["discount_value"] = $discount->discount_var;
			}
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::number => true,
			CForm::min => 0,
			CForm::max => 10,
			CForm::name => 'discount_value'
		));

		if ($SessionForm->value('session_publish_state') == 'PUBLISHED')
		{
			$SessionForm->AddElement(array(
				CForm::type => CForm::Submit,
				CForm::name => 'open_close_submit',
				CForm::css_class => 'btn btn-primary',
				CForm::value => 'Close Session'
			));
		}
		else
		{
			$SessionForm->AddElement(array(
				CForm::type => CForm::Submit,
				CForm::name => 'open_close_submit',
				CForm::css_class => 'btn btn-primary',
				CForm::value => 'Publish Session'
			));
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'session_submit',
			CForm::disabled => ((!empty($sessionDetails['is_past']) && $sessionDetails['session_type'] == CSession::TODD) ? true : false),
			// disable todd session editing
			CForm::css_class => 'btn btn-primary',
			CForm::value => 'Save Edits'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::name => 'submit_delete',
			CForm::css_class => 'btn btn-danger',
			CForm::value => 'Delete Session'
		));

		$SessionFormArray = $SessionForm->render(true);

		$tpl->assign('canDelete', (($numBookings > 0) ? false : true));
		$tpl->assign('Menu', $DAO_menu);
		$tpl->assign('form_create_session', $SessionFormArray);

		if ($SessionForm->value('session_submit'))
		{
			$oldSession = clone($DAO_session);

			$sessionFormValuesArray = $SessionForm->values();
			$DAO_session->setFrom($sessionFormValuesArray);

			$DAO_session->sneak_peak = 0;
			if ($SessionForm->value('session_sneak_peak') == true)
			{
				$DAO_session->sneak_peak = 1;
			}

			if (empty($DAO_session->session_type))
			{
				$DAO_session->session_type = CSession::STANDARD;
			}

			$leadVal = $SessionForm->value('session_lead');
			if (!empty($leadVal))
			{
				$DAO_session->session_lead = $SessionForm->value('session_lead');
			}
			else
			{
				$DAO_session->session_lead = 'null';
			}

			$DAO_session->session_class = $DAO_session->session_type;
			if ($DAO_session->session_type == CSession::DREAM_TASTE)
			{
				$DAO_session->session_class = CSession::TODD;
			}

			if (!isset($_POST['session_password']))
			{
				$DAO_session->session_password = "";
			}

			if (!empty($_POST['session_assembly_fee_enable']) && OrdersHelper::allow_assembly_fee($DAO_session->menu_id))
			{
				$DAO_session->session_assembly_fee = CGPC::do_clean($_POST['session_assembly_fee'], TYPE_NUM);
			}
			else
			{
				$DAO_session->session_assembly_fee = 'NULL';
			}

			if (!empty($_POST['session_delivery_fee_enable']))
			{
				$DAO_session->session_delivery_fee = CGPC::do_clean($_POST['session_delivery_fee'], TYPE_NUM);
			}
			else
			{
				$DAO_session->session_delivery_fee = 'NULL';
			}

			$PP_foundUserAccount = false;
			$PP_hostessName = false;
			$PP_hostessEmail = false;
			if (!empty($_POST['session_host']))
			{
				$userDAO = DAO_CFactory::create('user');
				if (is_numeric($_POST['session_host']))
				{
					$userDAO->id = $_POST['session_host'];
					if ($userDAO->find(true))
					{
						$PP_foundUserAccount = $userDAO->id;
						$PP_hostessName = $userDAO->firstname . ' ' . $userDAO->lastname;
						$PP_hostessEmail = $userDAO->primary_email;
					}
				}
				else
				{
					$userDAO->primary_email = trim(CGPC::do_clean($_POST['session_host'], TYPE_STR));
					if ($userDAO->find(true))
					{
						$PP_foundUserAccount = $userDAO->id;
						$PP_hostessName = $userDAO->firstname . ' ' . $userDAO->lastname;
						$PP_hostessEmail = $userDAO->primary_email;
					}
				}

				if (!$PP_foundUserAccount)
				{
					$tpl->setErrorMsg('We cannot find the specified user in our system. Please try again or clear the session host field');

					return;
				}
			}

			if (!isset($DAO_session->store_id))
			{
				throw new Exception("Store not set for edited session.");
			}

			if ($numBookings == 0) // can't set the time if there are bookings
			{
				if (!isset($_POST['session_date']))
				{
					throw new Exception("Session date was not posted.");
				}

				$DAO_session->session_start = date("Y-m-d H:i:s", strtotime(CGPC::do_clean($_POST['session_date'], TYPE_STR) . ' ' . CGPC::do_clean($_POST['session_time'], TYPE_STR)));

				if (!$DAO_menu->isTimeStampLegalForMenu(strtotime($DAO_session->session_start)))
				{
					$tpl->setErrorMsg('The session time is outside of the valid range for this menu');

					return;
				}
			}

			$DAO_session->setCloseSchedulingTime($SessionForm->value("close_interval_type"), $SessionForm->value("custom_close_interval"));
			if ($DAO_session->session_type == CSession::SPECIAL_EVENT)
			{
				$DAO_session->setMealCustomizationCloseSchedulingTime($SessionForm->value("meal_customization_close_interval_type"), $SessionForm->value("meal_customization_close_interval"));
			}
			else
			{
				$DAO_session->setMealCustomizationCloseSchedulingTime($SessionForm->value("meal_customization_close_interval_type"), -1);
			}
			// determine new type and theme
			$fadminAcronym = false;
			if ($DAO_session->session_type == CSession::DREAM_TASTE)
			{
				$newDTThemeID = CGPC::do_clean($_POST['dream_taste_theme'], TYPE_STR);
				$fadminAcronym = $DreamTasteTypeInfoArray[$newDTThemeID]['fadmin_acronym'];
			}
			else if ($DAO_session->session_type == CSession::FUNDRAISER)
			{
				$newFundraiserThemeID = CGPC::do_clean($_POST['dream_taste_theme'], TYPE_STR);
				$fadminAcronym = $FundraiserTypeInfoArray[$newFundraiserThemeID]['fadmin_acronym'];
			}

			if ($DAO_session->doesTimeConflict($fadminAcronym))
			{
				$tpl->setErrorMsg('The session time and duration conflict with another session.');
			}
			else
			{
				if (!empty($_POST['discount_value']))
				{
					$needNewDiscount = true;

					if ($oldSession->session_discount_id != null && $oldSession->session_discount_id != 0)
					{ // a session discount already exists, check for a change

						$org_discount = DAO_CFactory::create('session_discount');
						$org_discount->id = $oldSession->session_discount_id;
						if ($org_discount->find())
						{
							if (/*$org_discount->discount_type != $SessionForm->value("discount_type") || */ $org_discount->discount_var != $SessionForm->value("discount_value"))
							{
								$org_discount->delete();
							}
							else
							{    // old discount matches so no need for new row
								$needNewDiscount = false;
							}
						}
					}

					if ($needNewDiscount)
					{
						$discount = DAO_CFactory::create('session_discount');

						//$discount->discount_type = $SessionForm->value("discount_type");
						$discount->discount_type = 'PERCENT';
						$discount->discount_var = $SessionForm->value("discount_value");

						if ($discount->discount_var <= 0.0 || $discount->discount_var > 10.0)
						{
							$tpl->setErrorMsg('The session discount must be greater than 0 and less than or equal to 10.0');

							return;
						}

						$rslt = $discount->insert();
						if (!$rslt)
						{
							$tpl->setErrorMsg('The session discount could not be created');
							$DAO_session->session_discount_id = null;
						}

						$DAO_session->session_discount_id = $rslt;
					}
				}
				else if ($numBookings == 0)
				{
					if ($oldSession->session_discount_id != null && $oldSession->session_discount_id != 0)
					{ // a session discount already exists, delete it
						$org_discount = DAO_CFactory::create('session_discount');
						$org_discount->id = $oldSession->session_discount_id;
						if ($org_discount->find())
						{
							$org_discount->delete();
						}
						$DAO_session->session_discount_id = 'null';
					}
				}

				//Hack alert: NULL created_by field is converted to empty string in setFrom()
				// and therefore differs from the NULL value in $oldSession
				// set them equal here
				$oldSession->created_by = "";

				if ($DAO_session->session_discount_id === "" || $DAO_session->session_discount_id === 0)
				{
					$DAO_session->session_discount_id = 'null';
				}

				// Make sure mfy and standard don't have passwords if they aren't private sub types
				if (($DAO_session->isMadeForYou() && $DAO_session->session_type_subtype != CSession::REMOTE_PICKUP_PRIVATE && $DAO_session->session_type_subtype != CSession::DELIVERY_PRIVATE) || ($_POST['standard_session_type_subtype'] == CSession::STANDARD && $DAO_session->session_type_subtype != CSession::PRIVATE_SESSION))
				{
					$DAO_session->session_password = "";
				}

				if (empty($_POST["session_type_subtype"]))
				{
					$DAO_session->session_type_subtype = "null";
				}

				$rslt = $DAO_session->update($oldSession);

				if ($rslt || ($rslt === 0 && !$DAO_session->_lastError))
				{
					if ($PP_foundUserAccount)
					{
						if (isset($DAO_session_properties) && isset($DAO_session_properties->id))
						{
							$DAO_session_properties->session_id = $DAO_session->id;
							$DAO_session_properties->menu_pricing_method = 'USE_CURRENT';

							if ($PP_foundUserAccount && $PP_foundUserAccount !== $DAO_session_properties->session_host)
							{
								$DAO_session_properties->session_host = $PP_foundUserAccount;
							}

							$DAO_session_properties->store_pickup_location_id = 0;
							if (empty($DAO_session->session_type_subtype) && ($DAO_session->session_type_subtype == CSession::REMOTE_PICKUP || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
							{
								$DAO_session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'], TYPE_INT);
							}

							$DAO_session_properties->update();
						}
						else
						{
							$DAO_session_properties = DAO_CFactory::create('session_properties');
							$DAO_session_properties->session_id = $DAO_session->id;
							$DAO_session_properties->session_host = $PP_foundUserAccount; // this is validated above
							$DAO_session_properties->menu_pricing_method = 'USE_CURRENT';

							$DAO_session_properties->store_pickup_location_id = 0;
							if (empty($DAO_session->session_type_subtype) && ($DAO_session->session_type_subtype == CSession::REMOTE_PICKUP || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
							{
								$DAO_session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'], TYPE_INT);
							}

							$DAO_session_properties->insert();
						}
					}

					if ($DAO_session->isMadeForYou())
					{
						if (!empty($DAO_session->session_type_subtype) && ($DAO_session->session_type_subtype == CSession::REMOTE_PICKUP || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
						{
							$DAO_session_properties = DAO_CFactory::create('session_properties');
							$DAO_session_properties->session_id = $DAO_session->id;

							if ($DAO_session_properties->find(true))
							{
								$DAO_session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'], TYPE_INT);
								$DAO_session_properties->menu_pricing_method = 'USE_CURRENT';
								$DAO_session_properties->update();
							}
							else
							{
								$DAO_session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'], TYPE_INT);
								$DAO_session_properties->menu_pricing_method = 'USE_CURRENT';
								$DAO_session_properties->insert();
							}
						}
					}

					if (isset($_POST['do_send_pp_notification']))
					{
						$didSendHostessNotification = true;
						CEmail::sendHostessNotification($PP_hostessEmail, $PP_hostessName, $DAO_session);
					}

					$tpl->setStatusMsg('The session has been saved');

					if (isset($_GET['back']))
					{
						CApp::bounce($_GET['back']);
					}
				}
				else
				{
					$tpl->setErrorMsg('The session could not be saved');
				}
			}
		}
	}
}

?>