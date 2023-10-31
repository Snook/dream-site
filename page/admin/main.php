<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/CDashboardReport.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_admin_main extends CPageAdminOnly
{

	private $current_store_id = null;

	private $show = array(
		'store_selector' => false,
		'dashboard_snapshot' => false,
		'session_edit' => false
	);

	function runEventCoordinator()
	{
		$this->runPageMain();
	}

	function runOpsLead()
	{
		$this->show['dashboard_snapshot'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runOpsSupport()
	{
		$this->runPageMain();
	}

	function runManufacturerStaff()
	{
		// they only have one option to do in the fadmin, so for now just send them there
		CApp::bounce('/backoffice/reports_manufacturer_labels');
	}

	function runFranchiseStaff()
	{
		$this->runPageMain();
	}

	function runFranchiseLead()
	{
		$this->show['session_edit'] = true;
		$this->runPageMain();
	}

	function runFranchiseManager()
	{
		$this->show['dashboard_snapshot'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runFranchiseOwner()
	{
		$this->show['dashboard_snapshot'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runHomeOfficeStaff()
	{
		$this->show['store_selector'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runHomeOfficeManager()
	{
		$this->show['store_selector'] = true;
		$this->show['dashboard_snapshot'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runSiteAdmin()
	{
		$this->show['store_selector'] = true;
		$this->show['dashboard_snapshot'] = true;
		$this->show['session_edit'] = true;

		$this->runPageMain();
	}

	function runPageMain()
	{
		$tpl = CApp::instance()->template();
		$request_date = time();

		if (empty($_REQUEST['day']) && empty($_REQUEST['session']))
		{
			//CApp::bounce('/backoffice?day=' . date('Y-m-d', $request_date));
		}
		else if (!empty($_REQUEST['day']))
		{
			$request_date = strtotime(CGPC::do_clean($_REQUEST['day'],TYPE_STR));
		}
		else if (!empty($_REQUEST['session']) && is_numeric($_REQUEST['session']))
		{
			$session_info_array = CSession::getSessionDetailArray($_REQUEST['session']);

			if ($session_info_array[$_REQUEST['session']]['store_id'] != CBrowserSession::getCurrentFadminStoreID())
			{
				$storeInfo = CStore::getStoreAndOwnerInfo($session_info_array[$_REQUEST['session']]['store_id']);

				$tpl->setStatusMsg('Your current BackOffice store has been changed to ' . $storeInfo[0]['store_name'] . ' because a session was specified in the URL.');
			}

			if (!empty($session_info_array))
			{
				$request_date = strtotime($session_info_array[$_REQUEST['session']]['session_start']);
			}
		}

		$Form = new CForm();
		$Form->Repost = true;

		if ($this->show['store_selector'])
		{
			if (!empty($_POST['store']) && is_numeric($_POST['store']))
			{
				CBrowserSession::setCurrentFadminStore($_POST['store']);
			}

			if (!empty($session_info_array))
			{
				$Form->DefaultValues['store'] = $session_info_array[$_REQUEST['session']]['store_id'];
			}
			else
			{
				$Form->DefaultValues['store'] = CBrowserSession::getCurrentFadminStoreID();
			}

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => false,
				CForm::onChange => 'if (this.options[this.selectedIndex].value != \'\'){form.submit();}',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::css_class => 'custom-select'
			));

			$this->current_store_id = $Form->value('store');
		}
		else
		{
			$this->current_store_id = CBrowserSession::getCurrentFadminStoreID();
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $this->current_store_id;
		$Store->find(true);
		if (!empty($Store->hide_fadmin_home_dashboard) && (CUser::getCurrentUser()->user_type == CUser::FRANCHISE_MANAGER || CUser::getCurrentUser()->user_type == CUser::FRANCHISE_OWNER || CUser::getCurrentUser()->user_type == CUser::OPS_LEAD))
		{
			$this->show['dashboard_snapshot'] = false;
		}

		if ($Store->store_type === CStore::DISTRIBUTION_CENTER)
		{
			$passParams = array();
			if (!empty($_GET["session"]))
			{
				$passParams['session'] = $_GET["session"];
			}
			if (!empty($_GET["order"]))
			{
				$passParams['order'] = $_GET["order"];
			}

			CApp::bounce('/backoffice/main-delivered' . ((!empty($passParams)) ? '?' . http_build_query($passParams) : ''));
		}

		// temp hack for Sean Harris
		if (CUser::getCurrentUser()->id == 658891)
        {
            $this->show['dashboard_snapshot'] = true;
        }


		$tpl->assign('storeSupportsPlatePoints', CStore::storeSupportsPlatePoints($Store));

		$sessionsArray = CSession::getMonthlySessionInfoArray($Store, $request_date);

		$menu_array = CMenu::menuInfoArray($request_date);
		$tpl->assign('selected_menu_id', 0);

		if (!empty($session_info_array) && !empty($_REQUEST['session']))
		{
			$tpl->assign('selected_menu_id', $session_info_array[$_REQUEST['session']]['menu_id']);
		}
		else
		{
			foreach ($menu_array as $menu)
			{
				if ($menu['selected_date'])
				{
					$tpl->assign('selected_menu_id', $menu['id']);
					break;
				}
			}
		}

		$curMenuObj = CMenu::getMenuByDate(date("Y-m-d", $request_date));
		$menuMonth = $curMenuObj['menu_start'];

		list($dashboard_update_required, $dashboard_metrics) = CDashboardMenuBased::getMetricsSnapShot($Store->id, $menuMonth);

		$tpl->assign('menu_month', CTemplate::dateTimeFormat($menuMonth, VERBOSE_MONTH_YEAR));
		$tpl->assign('dashboard_metrics', $dashboard_metrics);
		$tpl->assign('dashboard_update_required', ($dashboard_update_required ? 'true' : 'true'));
		$tpl->assign('adjusted_server_time', CTemplate::unix_to_mysql_timestamp($request_date));
		$tpl->assign('form', $Form->render());
		$tpl->assign('menu_info_array', $menu_array);
		$tpl->assign('selected_date', date('Y-m-d', $request_date));
		$tpl->assign('selected_agenda_month', date('Y-m', $request_date));
		$tpl->assign('sessions', $sessionsArray['sessions']);
		$tpl->assign('show', $this->show);
		$tpl->assign('store', $Store);
	}
}

?>