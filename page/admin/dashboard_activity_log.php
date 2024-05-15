<?php
require_once("includes/CPageAdminOnly.inc");

require_once 'includes/OrdersHelper.php';
require_once 'includes/DAO/BusinessObject/CSession.php';
require_once 'includes/DAO/BusinessObject/CBooking.php';

class page_admin_dashboard_activity_log extends CPageAdminOnly
{
	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runEventCoordinator()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseStaff()
	{
		$this->runSiteAdmin();
	}

	function runOpsSupport()
	{
		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		// is this person a coach?  If they are, then need to create a customized store drop down
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->Template->assign('back', '/backoffice/main');

		if (!empty($_REQUEST['back']))
		{
			$this->Template->assign('back', $_REQUEST['back']);
		}

		$storeForm = new CForm();
		$storeForm->Repost = true;
		$storeForm->Bootstrap = true;

		//main filter control
		$filter = array_key_exists('filter', $_REQUEST) ? CGPC::do_clean($_REQUEST['filter'], TYPE_STR) : '';
		$storeForm->DefaultValues['filter'] = $filter;
		$options = array();
		$options[''] = '-' . CForm::optGroupSeparator . 'All';
		$options['SAVED'] = 'Order' . CForm::optGroupSeparator . 'Order Saved';
		$options['PLACED'] = 'Order' . CForm::optGroupSeparator . 'Order Placed';
		$options['EDITED'] = 'Order' . CForm::optGroupSeparator . 'Order Edited';
		$options['RESCHEDULED'] = 'Order' . CForm::optGroupSeparator . 'Order Rescheduled';
		$options['CANCELLED'] = 'Order' . CForm::optGroupSeparator . 'Order Canceled';
		$options[CStoreActivityLog::SIDES_ORDER] = 'Order' . CForm::optGroupSeparator . 'S&S Request Form';
		$options['SESSION CREATED'] = 'Session' . CForm::optGroupSeparator . 'Session Created';
		if (!$this->CurrentBackOfficeStore->isDistributionCenter())
		{
			$options['INVENTORY'] = 'Inventory' . CForm::optGroupSeparator . 'Low Inventory';
		}
		// On hold for stores until what changed can be displayed
		// This same statement in OrdersHelper.php
		if (false)
		{
			$options['RECIPE_UPDATED'] = 'Recipe' . CForm::optGroupSeparator . 'Recipe Updated';
		}

		$storeForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::name => 'filter',
			CForm::options => $options

		));

		$filter_sub_orderType = null;

		if ($filter != 'SESSION CREATED' && $filter != 'RECIPE_UPDATED' && $filter != 'INVENTORY' && $filter != CStoreActivityLog::SIDES_ORDER)
		{
			//sub filter control
			$filter_sub_ot = array_key_exists('filter_sub_ot', $_REQUEST) ? CGPC::do_clean($_REQUEST['filter_sub_ot'], TYPE_STR) : '';
			$storeForm->DefaultValues['filter_sub_ot'] = $filter_sub_ot;
			$options = array();
			$options[''] = 'All';
			$options['WEB'] = 'Customer';
			$options['DIRECT'] = 'BackOffice';

			$storeForm->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChangeSubmit => true,
				CForm::name => 'filter_sub_ot',
				CForm::options => $options
			));

			$filter_sub_orderType = '' == $filter_sub_ot ? null : array($filter_sub_ot);
		}
		else if ($filter == CStoreActivityLog::SIDES_ORDER)
		{

			$filter_sub_orderType = 'SIDES';
		}

		//history ui control
		$historyDays = 4;
		$historyDays = array_key_exists('timeframe', $_REQUEST) ? CGPC::do_clean($_REQUEST['timeframe'], TYPE_STR) : $historyDays;
		$storeForm->DefaultValues['timeframe'] = $historyDays;
		$storeForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::name => 'timeframe',
			CForm::options => array(
				'1' => '1',
				'2' => '3',
				'4' => '5',
				'9' => '10',
				'14' => '15',
				'29' => '30',
				'49' => '50',
				'99' => '100',
				'199' => '200'
			)
		));

		$filterArray = '' == $filter ? null : array($filter);

		$dateToday = new DateTime();
		$today = $dateToday->format('Y-m-d');
		$activity = OrdersHelper::fetchStoreActivity($this->CurrentBackOfficeStore->id, $today, $historyDays, $filterArray, $filter_sub_orderType);

		$this->Template->assign('activity', $activity);
		$this->Template->assign('store', $this->CurrentBackOfficeStore->id);
		$this->Template->assign('days_back', $historyDays);
		$this->Template->assign('limit_to', $storeForm->DefaultValues['filter']);

		$formArray = $storeForm->render();
		$this->Template->assign('form_array', $formArray);
	}
}
?>