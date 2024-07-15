<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_guest extends CPageAdminOnly
{
	/**
	 * @var CForm
	 */
	private $Form;
	private $allowStoreSelect = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runSiteAdmin()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runHomeOfficeManager()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runHomeOfficeStaff()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runFranchiseOwner()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runFranchiseManager()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runFranchiseLead()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function runOpsLead()
	{
		$this->allowStoreSelect = $this->CurrentUser->hasMultiStoreAccess();
		$this->guestReport();
	}

	function guestReport()
	{
		$this->Form = new CForm();
		$this->Form->Repost = true;
		$this->Form->Bootstrap = true;

		if (!empty($_GET['report']))
		{
			$this->Form->DefaultValues['guest_report'] = $_GET['report'];
		}

		$this->Form->DefaultValues['month_start'] = date("Y-m");
		$this->Form->DefaultValues['month_end'] = date("Y-m");
		$this->Form->DefaultValues['date_start'] = date("Y-m-01");
		$this->Form->DefaultValues['date_end'] = date("Y-m-d", strtotime("last day of this month"));
		$this->Form->DefaultValues['datetime_start'] = date("Y-m-d");
		$this->Form->DefaultValues['datetime_end'] = date("Y-m-d", strtotime('+1 year'));
		$this->Form->DefaultValues['query_set'] = 'query_with_sessions';
		$this->Form->DefaultValues['filter_guest_info'] = true;
		$this->Form->DefaultValues['multi_store_select'] = $this->CurrentBackOfficeStore->id;

		$this->Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'guest_report',
			CForm::options => array(
				'' => 'Select Report',
				'power-bi' => array(
					'title' => 'Power BI Dashboard Export',
					'data' => array(
						'data-description' => 'Power BI dashboard data export',
						'data-month-start' => 'true',
						'data-month-end' => 'true',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'driver-tip' => array(
					'title' => 'Driver Tip Report',
					'data' => array(
						'data-description' => 'Home Delivery orders in period',
						'data-month-start' => 'true',
						'data-month-end' => 'true',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'guest-details' => array(
					'title' => 'Guest Details',
					'data' => array(
						'data-description' => 'Guest details report.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'true',
						'data-date-end' => 'true',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-query-with-sessions' => 'true',
						'data-query-without-sessions' => 'true',
						'data-query-all-guests' => 'true',
						'data-filter-guest-info' => 'true',
						'data-filter-guest-orders' => 'true',
						'data-filter-guest-loyalty' => 'true',
						'data-filter-guest-additional-info' => 'true',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'guest-birthdays' => array(
					'title' => 'Guest Birthdays',
					'data' => array(
						'data-description' => 'Guests with a birthday in the selected month. Year not applicable.',
						'data-month-start' => 'true',
						'data-month-end' => 'false',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'guest-with-dinner-dollars' => array(
					'title' => 'Guests with Expiring Dinner Dollars',
					'data' => array(
						'data-description' => 'Guests with available Dinner Dollars expiring between Date and Date End.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'true',
						'data-date-end' => 'true',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'preferred-user' => array(
					'title' => 'Preferred Users',
					'data' => array(
						'data-description' => 'List of guests assigned for preferred user discounts.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-datetime-start' => 'false',
						'data-datetime-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				)
			)
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'export',
			CForm::value => 'csv'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => 'report_submit',
			CForm::css_class => 'btn btn-primary btn-block',
			CForm::value => '<i class="fas fa-download"></i> Download results'
		));

		$this->Form->addElement(array(
			CForm::type => CForm::ButtonMultiStore,
			CForm::name => 'multi_store_select',
			CForm::text => 'Stores',
			CForm::disabled => !$this->allowStoreSelect,
			CForm::css_class => 'btn btn-primary'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'date_start'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Date,
			CForm::name => 'date_end'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::DateTimeLocal,
			CForm::name => 'datetime_start'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::DateTimeLocal,
			CForm::name => 'datetime_end'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Month,
			CForm::name => 'month_end'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::Month,
			CForm::name => 'month_start'
		));

		/* Query options */
		$this->Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "query_set",
			CForm::value => 'query_with_sessions',
			CForm::label => 'Guests with sessions scheduled in date range'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "query_set",
			CForm::value => 'query_without_sessions',
			CForm::label => 'Accounts created in date range with no orders'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "query_set",
			CForm::value => 'query_all_guests',
			CForm::label => 'Accounts created in date range'
		));

		/* Result filters */
		$this->Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'filter_guest_info',
			CForm::label => 'Guest information'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'filter_guest_orders',
			CForm::label => 'Order information'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'filter_guest_loyalty',
			CForm::label => 'Loyalty and referral status'
		));

		$this->Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'filter_guest_additional_info',
			CForm::label => 'Additional information'
		));

		$this->Template->assign('form', $this->Form->render());

		if ($this->Form->value('report_submit'))
		{
			switch ($this->Form->value('guest_report'))
			{
				case 'power-bi':
					$this->export_DashboardMetrics();
					break;
				case 'driver-tip':
					$this->export_Bookings_DriverTips();
					break;
				case 'guest-details':
					$this->export_User_Details();
					break;
				case 'guest-birthdays':
					$this->export_User_Birthdays();
					break;
				case 'guest-with-dinner-dollars':
					$this->export_PointsCredits();
					break;
				case 'preferred-user':
					$this->export_UserPreferred();
					break;
			}
		}
	}

	function export_User_Details()
	{
		if ($this->Form->value('date_start') && $this->Form->value('date_end'))
		{
			$DAO_user = DAO_CFactory::create('user', true);

			$DAO_user->selectAdd("JSON_OBJECTAGG(user_data.user_data_field_id, user_data.user_data_value) as json_user_data");
			$DAO_user->selectAdd("JSON_OBJECTAGG(user_preferences.pkey, user_preferences.pvalue) as json_user_preferences");

			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->whereAdd("store.id IN(" . $this->Form->value('multi_store_select') . ")");
			$DAO_user->joinAddWhereAsOn($DAO_store);

			$DAO_address = DAO_CFactory::create('address', true);;
			$DAO_address->location_type = CAddress::BILLING;
			$DAO_user->joinAddWhereAsOn($DAO_address, 'LEFT');

			$DAO_user->joinAddWhereAsOn(DAO_CFactory::create('user_digest', true), 'LEFT');
			$DAO_user->joinAddWhereAsOn(DAO_CFactory::create('user_data', true), 'LEFT');
			$DAO_user->joinAddWhereAsOn(DAO_CFactory::create('user_preferences', true), 'LEFT');
			$DAO_user->joinAddWhereAsOn(DAO_CFactory::create('user_referral_source', true), 'LEFT');

			if ($this->Form->value('query_set') == 'query_with_sessions')
			{
				$DAO_booking = DAO_CFactory::create('booking', true);
				$DAO_booking->status = CBooking::ACTIVE;
				$DAO_session = DAO_CFactory::create('session', true);
				$DAO_session->whereAdd("session.session_start >= '" . CTemplate::formatDateTime(timeStamp: $this->Form->value('date_start')) . "'");
				$DAO_session->whereAdd("session.session_start <= '" . CTemplate::formatDateTime(timeStamp: $this->Form->value('date_end')) . "'", format: 'Y-m-d 23:59:59');
				$DAO_booking->joinAddWhereAsOn($DAO_session, joinSubDAO: false);
				$DAO_user->joinAddWhereAsOn($DAO_booking, joinSubDAO: false);
			}

			if ($this->Form->value('query_set') == 'query_without_sessions')
			{
				$DAO_user->whereAdd("user.timestamp_created >= '" . CTemplate::formatDateTime('Y-m-d H:i:s', $this->Form->value('date_start')) . "'");
				$DAO_user->whereAdd("user.timestamp_created <= '" . CTemplate::formatDateTime('Y-m-d 23:59:59', $this->Form->value('date_end')) . "'");
				$DAO_user->whereAdd("user_digest.visit_count < '1' OR user_digest.visit_count IS NULL");
			}

			if ($this->Form->value('query_set') == 'query_all_guests')
			{
				$DAO_user->whereAdd("user.timestamp_created >= '" . CTemplate::formatDateTime('Y-m-d H:i:s', $this->Form->value('date_start')) . "'");
				$DAO_user->whereAdd("user.timestamp_created <= '" . CTemplate::formatDateTime('Y-m-d 23:59:59', $this->Form->value('date_end')) . "'");
			}

			$DAO_user->groupBy("user.id");
			$DAO_user->orderBy("store.store_type, store.state_id, store.city, store.store_name, user.lastname, user.firstname");

			$DAO_user->find_includeDeleted();

			$labels = array();

			$multiStoreArray = explode(',', $this->Form->value('multi_store_select'));
			if (count($multiStoreArray) > 1)
			{
				$labels = array_merge($labels, array(
					"Store ID",
					"Store Type",
					"Store City",
					"Store State",
					"Store Name"
				));
			}

			$labels = array_merge($labels, array(
				"User ID",
				"Account Status",
				"First Name",
				"Last Name"
			));

			if ($this->Form->value('filter_guest_info'))
			{
				$labels = array_merge($labels, array(
					"Email Address",
					"Telephone 1",
					"Telephone 1 Type",
					"Telephone 1 Call Time",
					"Telephone 2",
					"Telephone 2 Type",
					"Telephone 2 Call Time",
					"SMS Message Opt-In",
					"Address 1",
					"Unit",
					"City",
					"State",
					"Postal Code",
					"User Account Notes",
					"Account Created"
				));
			}

			if ($this->Form->value('filter_guest_orders'))
			{
				$labels = array_merge($labels, array(
					"Number Days Inactive",
					"Last Session",
					"Last Session Type",
					"Next Session",
					"Next Session Type",
					"Next Special Instructions",
					"Next Meal Customizations"
				));
			}

			if ($this->Form->value('filter_guest_loyalty'))
			{
				$labels = array_merge($labels, array(
					"PLATEPOINTS Status",
					"User Share URL",
					"Referral Source",
					"Referral Data",
					"Referral Customer ID"
				));
			}

			if ($this->Form->value('filter_guest_additional_info'))
			{
				$labels = array_merge($labels, array(
					"Carryover Notes"
				));
			}

			$rows = array();
			$rowCount = 0;

			while ($DAO_user->fetch())
			{
				$rows[$rowCount] = array();

				if (count($multiStoreArray) > 1)
				{
					$rows[$rowCount] = array_merge($rows[$rowCount], array(
						"Store ID" => $DAO_user->DAO_store->id,
						"Store Type" => $DAO_user->DAO_store->store_type,
						"Store City" => $DAO_user->DAO_store->city,
						"Store State" => $DAO_user->DAO_store->state_id,
						"Store Name" => $DAO_user->DAO_store->store_name
					));
				}

				$rows[$rowCount] = array_merge($rows[$rowCount], array(
					"User ID" => $DAO_user->id,
					"Account Status" => $DAO_user->accountStatus(),
					"First Name" => $DAO_user->firstname,
					"Last Name" => $DAO_user->lastname
				));

				if ($this->Form->value('filter_guest_info'))
				{
					$rows[$rowCount] = array_merge($rows[$rowCount], array(
						"Email Address" => $DAO_user->primary_email,
						"Telephone 1" => $DAO_user->telephone_1,
						"Telephone 1 Type" => $DAO_user->telephone_1_type,
						"Telephone 1 Call Time" => $DAO_user->telephone_1_call_time,
						"Telephone 2" => $DAO_user->telephone_2,
						"Telephone 2 Type" => $DAO_user->telephone_2_type,
						"Telephone 2 Call Time" => $DAO_user->telephone_2_call_time,
						"SMS Message Opt-In" => $DAO_user->get_JSON_UserPreferenceValue(CUser::TEXT_MESSAGE_OPT_IN),
						"Address 1" => $DAO_user->DAO_address->address_line1,
						"Unit" => $DAO_user->DAO_address->address_line2,
						"City" => $DAO_user->DAO_address->city,
						"State" => $DAO_user->DAO_address->state_id,
						"Postal Code" => $DAO_user->DAO_address->postal_code,
						"User Account Notes" => $DAO_user->get_JSON_UserPreferenceValue(CUser::USER_ACCOUNT_NOTE),
						"Account Created" => $DAO_user->timestamp_created,
					));
				}

				if ($this->Form->value('filter_guest_orders'))
				{
					$rows[$rowCount] = array_merge($rows[$rowCount], array(
						"Number Days Inactive" => $DAO_user->getDaysInactive(),
						"Last Session" => CTemplate::formatDateTime('Y-m-d h:i A', $DAO_user->get_Booking_Last()?->get_DAO_session()?->session_start),
						"Last Session Type" => $DAO_user->get_Booking_Last()?->get_DAO_session()?->sessionTypeToText(),
						"Next Session" => CTemplate::formatDateTime('Y-m-d h:i A', $DAO_user->get_Booking_Next()?->get_DAO_session()?->session_start),
						"Next Session Type" => $DAO_user->get_Booking_Next()?->get_DAO_session()?->sessionTypeToText(),
						"Next Special Instructions" => $DAO_user->get_Booking_Next()?->get_DAO_orders()->order_user_notes,
						"Next Meal Customizations" => $DAO_user->get_Booking_Next()?->get_DAO_orders()->getOrderCustomizationString(),
					));
				}

				if ($this->Form->value('filter_guest_loyalty'))
				{
					$rows[$rowCount] = array_merge($rows[$rowCount], array(
						"PLATEPOINTS Status" => $DAO_user->getPlatePointsStatus(),
						"User Share URL" => $DAO_user->getShareURL(),
						"Referral Source" => $DAO_user->DAO_user_referral_source->source,
						"Referral Data" => $DAO_user->DAO_user_referral_source->meta,
						"Referral Customer ID" => $DAO_user->DAO_user_referral_source->customer_referral_id
					));
				}

				if ($this->Form->value('filter_guest_additional_info'))
				{
					$rows[$rowCount] = array_merge($rows[$rowCount], array(
						"Carryover Notes" => $DAO_user->get_JSON_UserDataValue(CUserData::GUEST_CARRY_OVER_NOTE)
					));
				}

				$rowCount++;
			}

			$this->Template->downloadReport('guest-details', $rows, $labels);
		}
	}

	function export_Bookings_DriverTips()
	{
		if ($this->Form->value('month_start') && $this->Form->value('month_end'))
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->whereAdd("menu.menu_start >= '" . $this->Form->value('month_start') . "-01'");
			$DAO_menu->whereAdd("menu.menu_start <= '" . $this->Form->value('month_end') . "-01'");
			$DAO_menu->find();

			$menuIdArray = array();

			while ($DAO_menu->fetch())
			{
				$menuIdArray[] = $DAO_menu->id;
			}

			$DAO_booking = DAO_CFactory::create('booking', true);
			$DAO_booking->status = CBooking::ACTIVE;
			$DAO_booking->joinAddWhereAsOn(DAO_CFactory::create('user', true));
			$DAO_orders = DAO_CFactory::create('orders', true);
			$DAO_orders->joinAddWhereAsOn(DAO_CFactory::create('coupon_code', true), 'LEFT');
			$DAO_orders->joinAddWhereAsOn(DAO_CFactory::create('orders_digest', true));
			$DAO_booking->joinAddWhereAsOn($DAO_orders);
			$DAO_session = DAO_CFactory::create('session', true);
			$DAO_session->whereAdd("session.session_type_subtype IN ('" . CSession::DELIVERY . "','" . CSession::DELIVERY_PRIVATE . "')");
			$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store', true));
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->whereAdd("menu.id IN(" . implode(',', $menuIdArray) . ")");
			$DAO_session->joinAddWhereAsOn($DAO_menu);
			$DAO_booking->joinAddWhereAsOn($DAO_session);
			$DAO_booking->orderBy("menu.id, store.state_id, store.city, store.store_name, session.session_start");
			$DAO_booking->find();

			$labels = array(
				'Menu ID',
				'Menu Date',
				'Store ID',
				'State',
				'City',
				'Store Name',
				'User ID',
				'First Name',
				'Session ID',
				'Session Type',
				'Session Start',
				'Order ID',
				'Order Placed',
				'Driver Tip',
				'Coupon Code',
				'Dinner Dollars',
				'User State'
			);

			$rows = array();

			while ($DAO_booking->fetch())
			{
				$rows[] = array(
					'Menu ID' => $DAO_booking->DAO_menu->id,
					'Menu Date' => $DAO_booking->DAO_menu->menu_name,
					'Store ID' => $DAO_booking->store_id,
					'State' => $DAO_booking->DAO_store->id,
					'City' => $DAO_booking->DAO_store->city,
					'Store Name' => $DAO_booking->DAO_store->store_name,
					'User ID' => $DAO_booking->DAO_user->id,
					'First Name' => $DAO_booking->DAO_user->firstname,
					'Session ID' => $DAO_booking->DAO_session->id,
					'Session Type' => $DAO_booking->DAO_session->session_type_subtype,
					'Session Start' => $DAO_booking->DAO_session->session_start,
					'Order ID' => $DAO_booking->DAO_orders->order_id,
					'Order Placed' => $DAO_booking->DAO_orders->timestamp_created,
					'Driver Tip' => $DAO_booking->DAO_orders->delivery_tip,
					'Coupon Code' => $DAO_booking->DAO_coupon_code->coupon_code,
					'Dinner Dollars' => $DAO_booking->DAO_orders->points_discount_total,
					'User State' => $DAO_booking->DAO_orders_digest->user_state
				);
			}

			$this->Template->downloadReport('report_power_bi', $rows, $labels);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Month and Month End selections');
		}
	}

	function export_DashboardMetrics()
	{
		if ($this->Form->value('month_start') && $this->Form->value('month_end'))
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->whereAdd("menu.menu_start >= '" . $this->Form->value('month_start') . "-01'");
			$DAO_menu->whereAdd("menu.menu_start <= '" . $this->Form->value('month_end') . "-01'");
			$DAO_menu->find();

			$menuIdArray = array();

			while ($DAO_menu->fetch())
			{
				$menuIdArray[] = $DAO_menu->id;
			}

			$DAO_dashboard_metrics_agr_by_menu = DAO_CFactory::create('dashboard_metrics_agr_by_menu', true);
			$DAO_dashboard_metrics_agr_by_menu->selectAdd();
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("menu.id AS menu_id");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("menu.menu_name");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_agr_by_menu.store_id");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("store.store_type");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("store.state_id");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("store.city");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("store.store_name");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_agr_by_menu.total_agr");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("IFNULL(dashboard_metrics_agr_snapshots.agr_menu_month, 0) AS total_agr_month_start");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("IFNULL(dashboard_metrics_agr_by_menu.avg_ticket_all, 0) AS avg_ticket_all");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_guests_by_menu.orders_count_all");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("(dashboard_metrics_guests_by_menu.orders_count_additional_new_guests + dashboard_metrics_guests_by_menu.orders_count_additional_existing_guests + dashboard_metrics_guests_by_menu.orders_count_additional_reacquired_guests) AS orders_count_additional");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_guests_by_menu.total_items_sold");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_guests_by_menu.total_boxes_sold");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("IFNULL(ROUND((dashboard_metrics_guests_by_menu.total_items_sold /dashboard_metrics_guests_by_menu.orders_count_all), 2), 0)   AS avg_items_per_order");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("dashboard_metrics_guests_by_menu.guest_count_total");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("(dashboard_metrics_guests_by_menu.guest_count_new_intro + dashboard_metrics_guests_by_menu.guest_count_new_taste + dashboard_metrics_guests_by_menu.guest_count_new_regular + dashboard_metrics_guests_by_menu.guest_count_new_delivered + dashboard_metrics_guests_by_menu.guest_count_new_additional + dashboard_metrics_guests_by_menu.guest_count_new_fundraiser) AS guest_count_new");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("(dashboard_metrics_guests_by_menu.guest_count_reacquired_intro + dashboard_metrics_guests_by_menu.guest_count_reacquired_taste + dashboard_metrics_guests_by_menu.guest_count_reacquired_regular + dashboard_metrics_guests_by_menu.guest_count_reacquired_delivered + dashboard_metrics_guests_by_menu.guest_count_reacquired_additional + dashboard_metrics_guests_by_menu.guest_count_reacquired_fundraiser) AS guest_count_reacquired");
			$DAO_dashboard_metrics_agr_by_menu->selectAdd("(dashboard_metrics_guests_by_menu.guest_count_existing_intro + dashboard_metrics_guests_by_menu.guest_count_existing_taste + dashboard_metrics_guests_by_menu.guest_count_existing_regular + dashboard_metrics_guests_by_menu.guest_count_existing_delivered + dashboard_metrics_guests_by_menu.guest_count_existing_additional + dashboard_metrics_guests_by_menu.guest_count_existing_fundraiser) AS guest_count_existing");
			$DAO_dashboard_metrics_guests_by_menu = DAO_CFactory::create('dashboard_metrics_guests_by_menu', true);
			$DAO_dashboard_metrics_guests_by_menu->whereAdd("dashboard_metrics_agr_by_menu.date = dashboard_metrics_guests_by_menu.date AND dashboard_metrics_guests_by_menu.store_id = dashboard_metrics_agr_by_menu.store_id");
			$DAO_dashboard_metrics_agr_by_menu->joinAddWhereAsOn($DAO_dashboard_metrics_guests_by_menu, array(
				'joinType' => 'INNER',
				'useLinks' => false
			), false, false, false);
			$DAO_dashboard_metrics_agr_snapshots = DAO_CFactory::create('dashboard_metrics_agr_snapshots', true);
			$DAO_dashboard_metrics_agr_snapshots->whereAdd("dashboard_metrics_agr_snapshots.date = dashboard_metrics_agr_by_menu.date AND dashboard_metrics_agr_snapshots.`month` = dashboard_metrics_agr_by_menu.date AND dashboard_metrics_agr_snapshots.store_id = dashboard_metrics_agr_by_menu.store_id");
			$DAO_dashboard_metrics_agr_by_menu->joinAddWhereAsOn($DAO_dashboard_metrics_agr_snapshots, array(
				'joinType' => 'LEFT',
				'useLinks' => false
			), false, false, false);
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->whereAdd("store.id IN(" . $this->Form->value('multi_store_select') . ")");
			$DAO_dashboard_metrics_agr_by_menu->joinAddWhereAsOn($DAO_store);
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->whereAdd("menu.menu_start = dashboard_metrics_agr_by_menu.date");
			$DAO_menu->whereAdd("menu.id IN(" . implode(',', $menuIdArray) . ")");
			$DAO_dashboard_metrics_agr_by_menu->joinAddWhereAsOn($DAO_menu);
			$DAO_dashboard_metrics_agr_by_menu->orderBy("menu.id DESC, store.store_type, store.state_id, store.city, store.store_name");
			$DAO_dashboard_metrics_agr_by_menu->find();

			$labels = array(
				'Menu ID',
				'Menu Date',
				'Store ID',
				'Store Type',
				'State',
				'City',
				'Store Name',
				'Total AGR',
				'Total AGR Month Start',
				'Average Ticket',
				'Total Orders',
				'Total Additional Orders',
				'Total Items Sold',
				'Total Boxes Sold',
				'Average Items Per Order',
				'Guest Total',
				'New Guest Total',
				'Reacquired Guest Total',
				'Existing Guest Total'
			);

			$rows = array();

			while ($DAO_dashboard_metrics_agr_by_menu->fetch())
			{
				$rows[] = array(
					'Menu ID' => $DAO_dashboard_metrics_agr_by_menu->menu_id,
					'Menu Date' => $DAO_dashboard_metrics_agr_by_menu->menu_name,
					'Store ID' => $DAO_dashboard_metrics_agr_by_menu->store_id,
					'Store Type' => $DAO_dashboard_metrics_agr_by_menu->store_type,
					'State' => $DAO_dashboard_metrics_agr_by_menu->state_id,
					'City' => $DAO_dashboard_metrics_agr_by_menu->city,
					'Store Name' => $DAO_dashboard_metrics_agr_by_menu->store_name,
					'Total AGR' => $DAO_dashboard_metrics_agr_by_menu->total_agr,
					'Total AGR Month Start' => $DAO_dashboard_metrics_agr_by_menu->total_agr_month_start,
					'Average Ticket' => $DAO_dashboard_metrics_agr_by_menu->avg_ticket_all,
					'Total Orders' => $DAO_dashboard_metrics_agr_by_menu->orders_count_all,
					'Total Additional Orders' => $DAO_dashboard_metrics_agr_by_menu->orders_count_additional,
					'Total Items Sold' => $DAO_dashboard_metrics_agr_by_menu->total_items_sold,
					'Total Boxes Sold' => $DAO_dashboard_metrics_agr_by_menu->total_boxes_sold,
					'Average Items Per Order' => $DAO_dashboard_metrics_agr_by_menu->avg_items_per_order,
					'Guest Total' => $DAO_dashboard_metrics_agr_by_menu->guest_count_total,
					'New Guest Total' => $DAO_dashboard_metrics_agr_by_menu->guest_count_new,
					'Reacquired Guest Total' => $DAO_dashboard_metrics_agr_by_menu->guest_count_reacquired,
					'Existing Guest Total' => $DAO_dashboard_metrics_agr_by_menu->guest_count_existing
				);
			}

			$this->Template->downloadReport('report_power_bi', $rows, $labels);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Month and Month End selections');
		}
	}

	function export_User_Birthdays()
	{
		if ($this->Form->value('month_start'))
		{
			$DateTime_month_start = new DateTime($this->Form->value('month_start'));

			$DAO_user = DAO_CFactory::create('user', true);

			$DAO_user->selectAdd();
			$DAO_user->selectAdd("user.id");
			$DAO_user->selectAdd("user.firstname");
			$DAO_user->selectAdd("user.lastname");
			$DAO_user->selectAdd("user.primary_email");
			$DAO_user->selectAdd("store.store_name");
			$DAO_user->selectAdd("store.state_id");
			$DAO_user->selectAdd("user_data_month.user_data_value as birth_month");
			$DAO_user->selectAdd("CONCAT('https://dreamdinners.com/share/',`user`.id) as share_url");

			$DAO_user_data_month = DAO_CFactory::create('user_data', true);
			$DAO_user_data_month->user_data_field_id = 1;
			$DAO_user_data_month->user_data_value = $DateTime_month_start->format('n');
			$DAO_user_data_month->whereAdd("user_data_month.user_id = `user`.id");
			$DAO_user->joinAddWhereAsOn($DAO_user_data_month, array(
				'joinType' => 'INNER',
				'useLinks' => false
			), 'user_data_month', false, false);

			$DAO_user_data_year = DAO_CFactory::create('user_data', true);
			$DAO_user_data_month->user_data_field_id = 15;
			$DAO_user_data_year->whereAdd("user_data_year.user_id = `user`.id");
			$DAO_user->joinAddWhereAsOn($DAO_user_data_year, array(
				'joinType' => 'INNER',
				'useLinks' => false
			), 'user_data_year', false, false);

			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->active = 1;
			$DAO_store->whereAdd("store.id IN(" . $this->Form->value('multi_store_select') . ")");
			$DAO_store->whereAdd("store.id = user.home_store_id");
			$DAO_user->joinAddWhereAsOn($DAO_store, array(
				'joinType' => 'INNER',
				'useLinks' => false
			), false, false, false);

			$DAO_user->whereAdd("`user`.primary_email <> ''");
			$DAO_user->groupBy("user.id");
			$DAO_user->orderBy("store.state_id, store.store_name, `user`.firstname");
			$DAO_user->find();

			$labels = array(
				'User ID',
				'First name',
				'Last name',
				'Primary Email',
				'Store Name',
				'State',
				'Birth Month',
				'Share URL'
			);

			$rows = array();

			while ($DAO_user->fetch())
			{
				$rows[] = array(
					$DAO_user->id,
					$DAO_user->firstname,
					$DAO_user->lastname,
					$DAO_user->primary_email,
					$DAO_user->store_name,
					$DAO_user->state_id,
					$DAO_user->birth_month,
					$DAO_user->share_url
				);
			}

			$this->Template->downloadReport('report_guest_birthdays', $rows, $labels);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Month selection');
		}
	}

	function export_PointsCredits()
	{
		if ($this->Form->value('datetime_start') && $this->Form->value('datetime_end'))
		{
			$DateTime_date_start = new DateTime($this->Form->value('datetime_start'));
			$DateTime_date_end = new DateTime($this->Form->value('datetime_end'));

			$DAO_points_credits = DAO_CFactory::create('points_credits', true);
			$DAO_points_credits->credit_state = CPointsCredits::AVAILABLE;
			$DAO_points_credits->selectAdd();
			$DAO_points_credits->selectAdd("user.id");
			$DAO_points_credits->selectAdd("user.firstname");
			$DAO_points_credits->selectAdd("user.primary_email");
			$DAO_points_credits->selectAdd("store.store_name");
			$DAO_points_credits->selectAdd("store.state_id");
			$DAO_points_credits->selectAdd("sum(points_credits.dollar_value) as credits_available");
			$DAO_points_credits->selectAdd("GROUP_CONCAT(CONCAT(points_credits.dollar_value,' expires ', DATE_FORMAT(date_sub(points_credits.expiration_date, INTERVAL 1 DAY), '%m-%d-%Y')) order by points_credits.expiration_date) as credit_expiration");
			$DAO_user = DAO_CFactory::create('user', true);
			$DAO_user->whereAdd("points_credits.user_id = user.id");
			$DAO_user->whereAdd("user.primary_email <> ''");
			$DAO_points_credits->joinAddWhereAsOn($DAO_user, array(
				'joinType' => 'INNER',
				'useLinks' => false
			), false, false, false);
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->whereAdd("store.id IN(" . $this->Form->value('multi_store_select') . ")");
			$DAO_store->whereAdd("store.id = user.home_store_id");
			$DAO_points_credits->joinAddWhereAsOn($DAO_store, array(
				'joinType' => 'LEFT',
				'useLinks' => false
			), false, false, false);
			$DAO_points_credits->whereAdd("points_credits.expiration_date >= '" . $DateTime_date_start->format('Y-m-d h:i:s') . "' AND points_credits.expiration_date <= '" . $DateTime_date_end->format('Y-m-d h:i:s') . "'");
			$DAO_points_credits->groupBy("user.id");
			$DAO_points_credits->orderBy("store.state_id, store.store_name, `user`.firstname");
			$DAO_points_credits->find();

			$labels = array(
				'User ID',
				'First Name',
				'Primary Email',
				'Store Name',
				'State',
				'Credits Available',
				'Credit Expiration'
			);

			$rows = array();

			while ($DAO_points_credits->fetch())
			{
				$rows[] = array(
					$DAO_points_credits->id,
					$DAO_points_credits->firstname,
					$DAO_points_credits->primary_email,
					$DAO_points_credits->store_name,
					$DAO_points_credits->state_id,
					$DAO_points_credits->credits_available,
					$DAO_points_credits->credit_expiration
				);
			}

			$this->Template->downloadReport('guest_with_dinner_dollars', $rows, $labels);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Date and Date End');
		}
	}

	function export_UserPreferred()
	{
		if ($this->Form->value('multi_store_select') && $this->Form->value('multi_store_select'))
		{
			$DAO_user_preferred = DAO_CFactory::create('user_preferred', true);
			$DAO_user_preferred->joinAddWhereAsOn(DAO_CFactory::create('user', true));
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->whereAdd("store.id IN(" . $this->Form->value('multi_store_select') . ") OR user_preferred.all_stores = 1");
			$DAO_user_preferred->joinAddWhereAsOn($DAO_store);
			$DAO_user_preferred->groupBy("user_preferred.id");
			$DAO_user_preferred->orderBy("store.state_id, store.store_name, `user`.firstname");
			$DAO_user_preferred->find();

			$labels = array(
				"User ID",
				"First Name",
				"Last Name",
				"Primary Email",
				"User Type",
				"Type",
				"Value",
				"Start Date",
				"Store Name",
				"State",
				"City",
				"All Stores"
			);

			$rows = array();

			while ($DAO_user_preferred->fetch())
			{
				$rowData = array(
					'User ID' => $DAO_user_preferred->user_id,
					'First Name' => $DAO_user_preferred->DAO_user->firstname,
					'Last Name' => $DAO_user_preferred->DAO_user->lastname,
					'Primary Email' => $DAO_user_preferred->DAO_user->primary_email,
					'User Type' => $DAO_user_preferred->DAO_user->user_type,
					'Type' => $DAO_user_preferred->preferred_type,
					'Value' => $DAO_user_preferred->preferred_value,
					'Start Date' => CSessionReports::reformatTime($DAO_user_preferred->user_preferred_start),
					'Store Name' => $DAO_user_preferred->DAO_store->store_name,
					'State' => $DAO_user_preferred->DAO_store->state_id,
					'City' => $DAO_user_preferred->DAO_store->city,
					'All Stores' => (!empty($DAO_user_preferred->all_stores) ? 'Yes' : 'No')
				);

				$rows[] = $rowData;
			}

			$this->Template->downloadReport('report_user_preferred', $rows, $labels);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Store selection');
		}
	}

	function getMonthStartEndIdArray()
	{
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->whereAdd("menu.menu_start >= '" . $this->Form->value('month_start') . "-01'");
		$DAO_menu->whereAdd("menu.menu_start <= '" . $this->Form->value('month_end') . "-01'");
		$DAO_menu->find();

		$menuIdArray = array();

		while ($DAO_menu->fetch())
		{
			$menuIdArray[] = $DAO_menu->id;
		}

		return $menuIdArray;
	}
}

?>