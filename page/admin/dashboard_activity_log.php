<?php
require_once("includes/CPageAdminOnly.inc");

require_once 'includes/OrdersHelper.php';
require_once 'includes/DAO/BusinessObject/CSession.php';
require_once 'includes/DAO/BusinessObject/CBooking.php';

class page_admin_dashboard_activity_log extends CPageAdminOnly
{
	private $currentStore = null;
	private $multiStoreOwnerStores = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runFranchiseStaff()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runOpsSupport()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
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
		$theStores = array();
		$hasMultipleStores = CUser::getCurrentUser()->isMultiStoreOwner($theStores);

		if ($hasMultipleStores)
		{
			$this->multiStoreOwnerStores = $theStores;
		}
		else
		{
			$this->currentStore = CApp::forceLocationChoice();
		}

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
		$tpl = CApp::instance()->template();

		$tpl->assign('back', '?page=admin_main');

		if (!empty($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back']);
		}

		//------------------------------------------------set up store and menu form

		$storeForm = new CForm();
		$storeForm->Repost = true;
		$storeForm->Bootstrap = true;

		if ($this->currentStore)
		{
			$currentStoreId = $this->currentStore;
		}
		else
		{
			$storeForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : null;

			$storeForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$currentStoreId = $storeForm->value('store');
		}

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
		{
			CBrowserSession::instance()->setValue('default_store_id', $currentStoreId);
		}

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
		$options['INVENTORY'] = 'Inventory' . CForm::optGroupSeparator . 'Low Inventory';
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
		$activity = OrdersHelper::fetchStoreActivity($currentStoreId, $today, $historyDays, $filterArray, $filter_sub_orderType);

		$tpl->assign('activity', $activity);
		$tpl->assign('store', $currentStoreId);
		$tpl->assign('days_back', $historyDays);
		$tpl->assign('limit_to', $storeForm->DefaultValues['filter']);

		$formArray = $storeForm->render();
		$tpl->assign('form_array', $formArray);
	}

	static function formatUserLink($item)
	{
		return ' <span data-tooltip="' . CUser::userTypeText($item['user_type']) . '"><a href="?page=admin_user_details&amp;id=' . $item['user_id'] . '" target="_blank">' . $item['user'] . '</a></span>';
	}
}

?>