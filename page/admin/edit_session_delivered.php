<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/OrdersHelper.php");
require_once("includes/DAO/BusinessObject/CSession.php");
require_once("includes/DAO/BusinessObject/CEmail.php");
require_once("create_session.php");

class page_admin_edit_session_delivered extends CPageAdminOnly
{

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
			$session_id = CGPC::do_clean($_REQUEST["session"],TYPE_INT);
		}
		else
		{
			throw new Exception('No session id requested');
		}

		$Session = DAO_CFactory::create('session');
		$Session->id = $session_id;
		$Session->find(true);

		$sessionDetails = CSession::getDeliveredSessionDetailArray($session_id, true);

		$Menu = DAO_CFactory::create('menu');
		$Menu->id = $Session->menu_id;
		$Menu->find(true);

		$Store = DAO_CFactory::create('store');
		$Store->id = $Session->store_id;
		$Store->find(true);

		$Store->getStorePickupLocations();



		// Set up values in form
		$SessionForm->DefaultValues = array_merge($SessionForm->DefaultValues, $Session->toArray());
		if ($SessionForm->DefaultValues["session_password"] == "NULL")
		{
			$SessionForm->DefaultValues["session_password"] = "";
		}

		$markup = $Store->getMarkUpMultiObj($Session->menu_id);



		if (!empty($Session->session_assembly_fee) || $Session->session_assembly_fee == '0.00')
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = $Session->session_assembly_fee;
		}
		else if ($markup)
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = $markup->assembly_fee;
		}
		else
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = '25';
		}

		$tpl->assign('allow_assembly_fee',OrdersHelper::allow_assembly_fee($Session->menu_id));
		if(!OrdersHelper::allow_assembly_fee($Session->menu_id)){
			$SessionForm->DefaultValues['session_assembly_fee'] = 0;
			if ($markup) $markup->assembly_fee = '0.00';
			$Session->session_assembly_fee = '0.00';
		}

		$SessionForm->DefaultValues["close_interval_type"] = $Session->determineSessionCloseEnum();
		$SessionForm->DefaultValues["custom_close_interval"] = $Session->getScheduleCloseInterval();

		$SessionForm->DefaultValues["session_sneak_peak"] = $Session->sneak_peak == 1 ? true : false;

		$numBookings = $Session->get_num_bookings();

		$canEditSessionType = false;
		if ($Session->isStandard() || $Session->isStandardPrivate() || $Session->isMadeForYou())
		{
			$canEditSessionType = true;
		}

		$stmi = DAO_CFactory::create('session_to_menu_item');
		$stmi->session_id = $Session->id;
		$stmi->find();
		$stmi_array = array();
		while ($stmi->fetch())
		{
			$stmi_array[$stmi->menu_item_id] = $stmi->menu_item_id;
		}

		// Session host
		$session_properties = DAO_CFactory::create('session_properties');
		$session_properties->session_id = $Session->id;
		$session_properties->find(true);

		if (!empty($session_properties->session_host))
		{
			$hostInfo = DAO_CFactory::create('user');
			$hostInfo->id = $session_properties->session_host;
			$hostInfo->selectAdd();
			$hostInfo->selectAdd("CONCAT(firstname,' ',lastname) as fullname, primary_email");
			$hostInfo->find(true);
			$tpl->assign('pp_hostInfo', $hostInfo->fullname . ' ( #' . $hostInfo->id . ' )');
			$SessionForm->DefaultValues['session_host'] = $hostInfo->primary_email;
		}

		if ($session_properties->menu_pricing_method)
		{
			$SessionForm->DefaultValues["menu_pricing_method"] = $session_properties->menu_pricing_method;
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
				$stmi = DAO_CFactory::create('session_to_menu_item');
				$stmi->session_id = $Session->id;
				$stmi->find();
				while ($stmi->fetch())
				{
					$stmi->delete();
				}

				$Session->delete();

				if (!empty($Session->is_deleted) && !empty($session_properties->id))
				{
					$session_properties->delete();
				}

				// delete any session_rsvp
				CSession::deleteSessionRSVP($Session->id);

				$tpl->setStatusMsg('The session was successfully deleted.');
				CApp::bounce('/backoffice/session-mgr-delivered');

				return;
			}
		}

		// handle opening(publishing) and closing the session
		if (isset($_POST['open_close_submit']))
		{
			if ($_POST['open_close_submit'] == 'Close Session')
			{
				$Session->session_publish_state = CSession::CLOSED;

				$rslt = $Session->update();
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
				$Session->session_publish_state = CSession::PUBLISHED;

				$rslt = $Session->update();
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
		//flip for blackout on UI
		$SessionForm->DefaultValues["delivered_supports_shipping"] = ($Session->delivered_supports_shipping === '0') ? true : false;
		$SessionForm->DefaultValues["delivered_supports_delivery"] = ($Session->delivered_supports_delivery === '0') ? true : false;

		$SessionForm->DefaultValues["session_type_subtype"] = $Session->session_type_subtype;
		$SessionForm->DefaultValues["session_pickup_location"] = $session_properties->store_pickup_location_id;
		$SessionForm->DefaultValues["session_date"] = CTemplate::dateTimeFormat($Session->session_start, YEAR_MONTH_DAY);
		$SessionForm->DefaultValues["session_time"] = CTemplate::dateTimeFormat($Session->session_start, HH_MM);
		$SessionForm->DefaultValues["standard_session_type_subtype"] = ((!empty($Session->session_password) ? CSession::PRIVATE_SESSION : CSession::STANDARD));
		$session_types = array(CSession::DELIVERED => 'Delivered');
		$session_types = array_merge($session_types, array(
			'PB' => array(
				'title' => 'Pickup Blackout'
			)
		));
		$session_types = array_merge($session_types, array(
			'DB' => array(
				'title' => 'Delivery Blackout'

			)
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::checked => ((!empty($Session->session_assembly_fee) || ($Session->session_assembly_fee == '0.00')) ? true : false),
			CForm::label => '$',
			CForm::tooltip => 'Enable Override',
			CForm::name => 'session_assembly_fee_enable'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'session_assembly_fee',
			CForm::attribute => array('data-valdefault' => ((!empty($markup->assembly_fee)) ? $markup->assembly_fee : '25'))
		));


		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::disabled => true,
			CForm::options => array($Menu->id => $Menu->menu_name),
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
			CForm::type => CForm::CheckBox,
			CForm::name => 'delivered_supports_shipping'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'delivered_supports_delivery'
		));


		$startDate = new DateTime($Menu->global_menu_start_date);
		$endDate = new DateTime($Menu->global_menu_end_date);

		//Not sure what the thought was here, but seems to be limiting the ability to edit sessions
		//$startDate->modify("+ 3 days");
		$endDate->modify("+ 3 days");

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => (($numBookings > 0) ? true : false),
			CForm::date => true,
			CForm::required => true,
			CForm::min => CTemplate::dateTimeFormat($startDate->format("Y-m-d"), YEAR_MONTH_DAY),
			CForm::max => CTemplate::dateTimeFormat($endDate->format("Y-m-d"), YEAR_MONTH_DAY),
			CForm::name => 'session_date'
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

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::min => 0,
			CForm::name => 'available_slots'
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

		if ($Session->session_discount_id != null)
		{
			$discount = DAO_CFactory::create('session_discount');
			$discount->id = $Session->session_discount_id;
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
		$tpl->assign('store', $Store);
		$tpl->assign('form_create_session', $SessionFormArray);

		if ($SessionForm->value('session_submit'))
		{
			$oldSession = clone($Session);

			$sessionFormValuesArray = $SessionForm->values();
			$Session->setFrom($sessionFormValuesArray);



			$Session->sneak_peak = 0;
			if ($SessionForm->value('session_sneak_peak') == true)
			{
				$Session->sneak_peak = 1;
			}

			//values appear reversed because the checkbox and database values are opposite


			$date = DateTime::createFromFormat('Y-m-d H:i:s', $Session->session_start);
			$Session->delivered_supports_shipping = $Session::getShippingServiceDays($date);
			if ($SessionForm->value('delivered_supports_shipping') == true)
			{
				$Session->delivered_supports_shipping = 0;
			}
			$Session->delivered_supports_delivery = $Session::getDeliveryServiceDays($date);
			if ($SessionForm->value('delivered_supports_delivery') == true)
			{
				$Session->delivered_supports_delivery = 0;
			}


			$leadVal = $SessionForm->value('session_lead');
			if (!empty($leadVal))
			{
				$Session->session_lead = $SessionForm->value('session_lead');
			}
			else
			{
				$Session->session_lead = 'null';
			}

			$Session->session_class = $Session->session_type;
			if ($Session->session_type == CSession::DREAM_TASTE)
			{
				$Session->session_class = CSession::TODD;
			}

			if (!isset($_POST['session_password']))
			{
				$Session->session_password = "";
			}

			if (!empty($_POST['session_assembly_fee_enable']) && OrdersHelper::allow_assembly_fee($Session->menu_id))
			{
				$Session->session_assembly_fee = CGPC::do_clean($_POST['session_assembly_fee'],TYPE_NUM);
			}
			else
			{
				$Session->session_assembly_fee = 'NULL';
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
					$userDAO->primary_email = trim(CGPC::do_clean($_POST['session_host'],TYPE_STR));
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

			if (!isset($Session->store_id))
			{
				throw new Exception("Store not set for edited session.");
			}

			if ($numBookings == 0) // can't set the time if there are bookings
			{
				if (!isset($_POST['session_date']))
				{
					throw new Exception("Session date was not posted.");
				}
				$incoming_session_start = date("Y-m-d", strtotime(CGPC::do_clean($_POST['session_date'],TYPE_STR)));
				$original_session_start = date("Y-m-d", strtotime($Session->session_start));

				if( $original_session_start != $incoming_session_start)
				{
					$Session->session_start = date("Y-m-d H:i:s", strtotime(CGPC::do_clean($_POST['session_date'],TYPE_STR) . ' ' . CGPC::do_clean($_POST['session_time'],TYPE_STR)));

					if (!$Menu->isTimeStampLegalForMenu(strtotime($Session->session_start)))
					{
						$tpl->setErrorMsg('The session time is outside of the valid range for this menu');

						return;
					}
				}

			}

			$Session->setCloseSchedulingTime($SessionForm->value("close_interval_type"), $SessionForm->value("custom_close_interval"));

			if ($Session->doesTimeConflict())
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
							$Session->session_discount_id = null;
						}

						$Session->session_discount_id = $rslt;
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
						$Session->session_discount_id = 'null';
					}
				}

				//Hack alert: NULL created_by field is converted to empty string in setFrom()
				// and therefore differs from the NULL value in $oldSession
				// set them equal here
				$oldSession->created_by = "";

				if ($Session->session_discount_id === "" || $Session->session_discount_id === 0)
				{
					$Session->session_discount_id = 'null';
				}

				// Make sure mfy and standard don't have passwords if they aren't private sub types
				if (($Session->isMadeForYou() && $Session->session_type_subtype != CSession::REMOTE_PICKUP_PRIVATE) || ($_POST['standard_session_type_subtype'] == CSession::STANDARD && $Session->session_type_subtype != CSession::PRIVATE_SESSION))
				{
					$Session->session_password = "";
				}

				$rslt = $Session->update($oldSession);

				if ($rslt || ($rslt === 0 && !$Session->_lastError))
				{
					if ($PP_foundUserAccount)
					{
						if (isset($session_properties) && isset($session_properties->id))
						{
							$session_properties->session_id = $Session->id;
							$session_properties->menu_pricing_method = 'USE_CURRENT';

							if ($PP_foundUserAccount && $PP_foundUserAccount !== $session_properties->session_host)
							{
								$session_properties->session_host = $PP_foundUserAccount;
							}

							$session_properties->store_pickup_location_id = 0;
							if (empty($Session->session_type_subtype) && ($Session->session_type_subtype == CSession::REMOTE_PICKUP || $Session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
							{
								$session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'],TYPE_INT);
							}

							$session_properties->update();
						}
						else
						{
							$session_properties = DAO_CFactory::create('session_properties');
							$session_properties->session_id = $Session->id;
							$session_properties->session_host = $PP_foundUserAccount; // this is validated above
							$session_properties->menu_pricing_method = 'USE_CURRENT';

							$session_properties->store_pickup_location_id = 0;
							if (empty($Session->session_type_subtype) && ($Session->session_type_subtype == CSession::REMOTE_PICKUP || $Session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
							{
								$session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'],TYPE_INT);
							}

							$session_properties->insert();
						}
					}

					if ( $Session->isMadeForYou())
					{
						if (!empty($Session->session_type_subtype) && ($Session->session_type_subtype == CSession::REMOTE_PICKUP || $Session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
						{
							$session_properties = DAO_CFactory::create('session_properties');
							$session_properties->session_id = $Session->id;

							if ($session_properties->find(true))
							{
								$session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'],TYPE_INT);
								$session_properties->menu_pricing_method = 'USE_CURRENT';
								$session_properties->update();
							}
							else
							{
								$session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'],TYPE_INT);
								$session_properties->menu_pricing_method = 'USE_CURRENT';
								$session_properties->insert();
							}
						}
					}

					if (isset($_POST['do_send_pp_notification']))
					{
						$didSendHostessNotification = true;
						CEmail::sendHostessNotification($PP_hostessEmail, $PP_hostessName, $Session);
					}

					$tpl->setStatusMsg('The session has been saved');

					if (isset($_GET['back']))
					{
						CApp::bounce($_GET['back']);
					}
				}
				else
				{
					CLog::RecordIntense('session could not be saved: ' . $Session->_query_string, 'ryan.snook@dreamdinners.com');

					$tpl->setErrorMsg('The session could not be saved');
				}
			}
		}
	}
}

?>