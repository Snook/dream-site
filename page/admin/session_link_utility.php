<?php
/*
 * Created on Sep 1, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/CCalendar.inc';
require_once 'includes/DAO/BusinessObject/CSession.php';

class page_admin_session_link_utility extends CPageAdminOnly
{

	public static $sessionArray = array();
	private $currentStore = null;

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{

		//if no store is chosen, bounce to the choose store page
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{

		$tpl = CApp::instance()->template();

		//------------------------------------------------set up store and menu form

		$storeMenuForm = new CForm();
		$storeMenuForm->Repost = true;

		if ($this->currentStore)
		{ //fadmins
			$currentStore = $this->currentStore;
		}
		else
		{ //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$storeMenuForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : null;

			$storeMenuForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$currentStore = $storeMenuForm->value('store');
		}

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
		{
			CBrowserSession::instance()->setValue('default_store_id', $currentStore);
		}

		$todaysMonth = date("n");
		$todaysYear = date("Y");
		$todaysDay = date("j");
		$currentMonthDate = date("Y-m-01");

		$storeMenuFormArray = $storeMenuForm->render(true);

		$Sessions = DAO_CFactory::create('session');

		$query1 = "SELECT session.id, session.session_publish_state, session.session_start, session.session_close_scheduling, " . " session.session_type, session.available_slots, session.session_password, count(booking.id) as filled,  session.available_slots - count(booking.id) as remaining FROM session " . " LEFT JOIN booking ON booking.session_id = session.id  and booking.status = 'ACTIVE' " . " WHERE session.session_start > now() and store_id = $currentStore AND session.is_deleted = 0 and session.session_type <> 'SPECIAL_EVENT' group by session.id order by session.session_start";

		// First get the session data
		$Sessions->query($query1);

		$Store = DAO_CFactory::create('store');
		$Store->id = $currentStore;
		$Store->find(true);

		$daterArray = array();
		while ($Sessions->fetch())
		{
			$daterArray[$Sessions->id] = array(
				'state' => $Sessions->session_publish_state,
				'start' => CTemplate::dateTimeFormat($Sessions->session_start),
				'slots_available' => $Sessions->available_slots,
				'slots_remaining' => $Sessions->remaining
			);
		}

		$tpl->assign('sessions', $daterArray);
		$tpl->assign('form', $storeMenuFormArray);
	}

}
?>