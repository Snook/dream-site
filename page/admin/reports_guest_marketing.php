<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_guest_marketing extends CPageAdminOnly
{
	/**
	 * @var CForm
	 */
	private $Form;
	private $allowStoreSelect = false;
	private $report_user_preferred = array(
		'AllStores' => false
	);

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runSiteAdmin()
	{
		$this->allowStoreSelect = true;
		$this->report_user_preferred['AllStores'] = true;
		$this->guestMarketingReport();
	}

	function runHomeOfficeManager()
	{
		$this->allowStoreSelect = true;
		$this->report_user_preferred['AllStores'] = true;
		$this->guestMarketingReport();
	}

	function runHomeOfficeStaff()
	{
		$this->allowStoreSelect = true;
		$this->report_user_preferred['AllStores'] = true;
		$this->guestMarketingReport();
	}

	function runFranchiseOwner()
	{
		$this->guestMarketingReport();
	}

	function runFranchiseManager()
	{
		$this->guestMarketingReport();
	}

	function runOpsLead()
	{
		$this->guestMarketingReport();
	}

	function guestMarketingReport()
	{
		$this->Form = new CForm();
		$this->Form->Repost = true;
		$this->Form->Bootstrap = true;

		$this->Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'marketing_report',
			CForm::options => array(
				'' => 'Select Report',
				'report_guest_birthdays' => array(
					'title' => 'Guest Birthdays',
					'data' => array(
						'data-description' => 'Guests with a birthday in the selected month. Year not applicable.',
						'data-month-start' => 'true',
						'data-month-end' => 'false',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'report_guest_with_dinner_dollars' => array(
					'title' => 'Guests with Expiring Dinner Dollars',
					'data' => array(
						'data-description' => 'Guests with available Dinner Dollars expiring between Date and Date End.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'true',
						'data-date-end' => 'true',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				),
				'report_user_preferred' => array(
					'title' => 'Preferred Users',
					'data' => array(
						'data-description' => 'List of guests assigned for preferred user discounts.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'false',
						'data-date-end' => 'false',
						'data-multi-store-select' => $this->allowStoreSelect
					)
				)
			)
		));

		$this->Form->DefaultValues['month_start'] = date("Y-m");
		$this->Form->DefaultValues['month_end'] = date("Y-m");
		$this->Form->DefaultValues['datetime_start'] = date("Y-m-d");
		$this->Form->DefaultValues['datetime_end'] = date("Y-m-d", strtotime('+1 year'));
		$this->Form->DefaultValues['multi_store_select'] = $this->CurrentBackOfficeStore->id;

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

		$this->Template->assign('form', $this->Form->render());

		if ($this->Form->value('report_submit'))
		{
			switch ($this->Form->value('marketing_report'))
			{
				case 'report_guest_birthdays':
					$this->exportGuestBirthdays();
					break;
				case 'report_guest_with_dinner_dollars':
					$this->exportGuestDinnerDollars();
					break;
				case 'report_user_preferred':
					$this->exportUserPreferred();
					break;
			}
		}
	}

	function exportGuestBirthdays()
	{
		if ($this->Form->value('month_start'))
		{
			$DateTime_month_start = new DateTime($this->Form->value('month_start'));

			$DAO_user = DAO_CFactory::create('user', true);

			$DAO_user->selectAdd();
			$DAO_user->selectAdd("user.id");
			$DAO_user->selectAdd("CONCAT(`user`.firstname,' ',`user`.lastname) as `name`");
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
				'First and Last',
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
					$DAO_user->name,
					$DAO_user->primary_email,
					$DAO_user->store_name,
					$DAO_user->state_id,
					$DAO_user->birth_month,
					$DAO_user->share_url
				);
			}

			$_GET['export'] = 'csv';
			$_GET['csvfilename'] = 'report_guest_birthdays';

			$this->Template->assign('labels', $labels);
			$this->Template->assign('rows', $rows);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Month selection');
		}
	}

	function exportGuestDinnerDollars()
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

			$_GET['export'] = 'csv';
			$_GET['csvfilename'] = 'guest_with_dinner_dollars';

			$this->Template->assign('labels', $labels);
			$this->Template->assign('rows', $rows);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Date and Date End');
		}
	}

	function exportUserPreferred()
	{
		if ($this->Form->value('multi_store_select') && $this->Form->value('multi_store_select'))
		{
			$DAO_user_preferred = DAO_CFactory::create('user_preferred', true);
			$DAO_user_preferred->joinAddWhereAsOn(DAO_CFactory::create('user', true));
			$DAO_user_preferred->joinAddWhereAsOn(DAO_CFactory::create('store', true), 'LEFT');
			$DAO_user_preferred->whereAdd("user_preferred.store_id IN(" . $this->Form->value('multi_store_select') . ")");
			if ($this->report_user_preferred['AllStores'])
			{
				$DAO_user_preferred->whereAdd("user_preferred.all_stores = 1", 'OR');
			}
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
				"City"
			);

			if ($this->report_user_preferred['AllStores'])
			{
				$labels[] = "All Store";
			}

			$rows = array();

			while ($DAO_user_preferred->fetch())
			{
				$rowData = array(
					'id' => $DAO_user_preferred->user_id,
					'firstname' => $DAO_user_preferred->DAO_user->firstname,
					'lastname' => $DAO_user_preferred->DAO_user->lastname,
					'primary_email' => $DAO_user_preferred->DAO_user->primary_email,
					'user_type' => $DAO_user_preferred->DAO_user->user_type,
					'preferred_type' => $DAO_user_preferred->preferred_type,
					'preferred_value' => $DAO_user_preferred->preferred_value,
					'user_preferred_start' => CSessionReports::reformatTime($DAO_user_preferred->user_preferred_start),
					'store_name' => $DAO_user_preferred->DAO_store->store_name,
					'state' => $DAO_user_preferred->DAO_store->state_id,
					'city' => $DAO_user_preferred->DAO_store->city
				);

				if ($this->report_user_preferred['AllStores'])
				{
					$rowData['all_stores'] = $DAO_user_preferred->all_stores;
				}

				$rows[] = $rowData;
			}

			$_GET['export'] = 'csv';
			$_GET['csvfilename'] = 'report_user_preferred';

			$this->Template->assign('labels', $labels);
			$this->Template->assign('rows', $rows);
		}
		else
		{
			$this->Template->setErrorMsg('Report requires Store selection');
		}
	}
}

?>