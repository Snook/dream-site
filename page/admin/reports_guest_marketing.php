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

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->guestMarketingReport();
	}

	function runSiteAdmin()
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
						'data-date-end' => 'false'
					)
				),
				'report_guest_with_dinner_dollars' => array(
					'title' => 'Guests with Expiring Dinner Dollars',
					'data' => array(
						'data-description' => 'Guests with available Dinner Dollars expiring between Date and Date End.',
						'data-month-start' => 'false',
						'data-month-end' => 'false',
						'data-date-start' => 'true',
						'data-date-end' => 'true'
					)
				)
			)
		));

		$this->Form->DefaultValues['month_start'] = date("Y-m");
		$this->Form->DefaultValues['month_end'] = date("Y-m");
		$this->Form->DefaultValues['datetime_start'] = date("Y-m-d");
		$this->Form->DefaultValues['datetime_end'] = date("Y-m-d", strtotime('+1 year'));

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
			}

		}
	}

	function exportGuestBirthdays()
	{
		if ($this->Form->value('month_start'))
		{
			$DateTime_month_start = new DateTime($this->Form->value('month_start'));

			$DAO_users = DAO_CFactory::create('user', true);
			$DAO_users->query("SELECT
				`user`.id,
				CONCAT(`user`.firstname,' ',`user`.lastname) as `name`,
				`user`.primary_email,
				store.store_name,
				store.state_id,
				user_data_month.user_data_value as birth_month,
				CONCAT('https://dreamdinners.com/share/',`user`.id) as share_url
				from `user`
				join user_data as user_data_month on user_data_month.user_id = `user`.id and user_data_month.user_data_field_id = 1 and user_data_month.user_data_value = " . $DateTime_month_start->format('n') . " and user_data_month.is_deleted = 0
				join user_data as user_data_year on user_data_year.user_id = `user`.id and user_data_year.user_data_field_id = 15 and user_data_year.is_deleted = 0
				join store on store.id=`user`.home_store_id and store.active = 1 and store.is_deleted = 0
				where `user`.primary_email <> ''
				order by store.state_id, store.store_name, `user`.firstname");

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

			while ($DAO_users->fetch())
			{
				$rows[] = array(
					$DAO_users->id,
					$DAO_users->name,
					$DAO_users->primary_email,
					$DAO_users->store_name,
					$DAO_users->state_id,
					$DAO_users->birth_month,
					$DAO_users->share_url
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
}

?>