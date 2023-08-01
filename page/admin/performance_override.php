<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");


class page_admin_performance_override extends CPageAdminOnly {

	function runHomeOfficeManager() {
		$canoverride = false;
		$canoverride = CApp::overrideAdminPage();
		if ($canoverride == true)
			$this->runSiteAdmin();
		else
			CApp::bounce('main.php?page=admin_access_error&topnavname=reports&pagename=Performance Override Form');
	}


	function runSiteAdmin() {
		$tpl = CApp::instance()->template();
		$form = new CForm();
		$form->Repost = FALSE;


		$form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
								CForm::name => 'performance_store',
								CForm::showInactiveStores => true ));


		$month_array = array ("Select a Month", 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');


		$form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::options => $month_array,
							CForm::name => 'month_dropdown'));

		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::maxlength => 2,
		CForm::name => 'starting_day'));


		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::maxlength => 4,
		CForm::name => 'starting_year'));



		$form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::options => $month_array,
							CForm::name => 'end_month_dropdown'));

		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::maxlength => 2,
		CForm::name => 'ending_day'));


		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::maxlength => 4,
		CForm::name => 'ending_year'));



		$obj = DAO_CFactory::create("access_control_page");
		$obj->find();
		$controlpages = array();

		while ($obj->fetch()) {
			$controlpages[$obj->id] = $obj->page_name;
		}

		$form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::options => $controlpages,
							CForm::name => 'page_dropdown'));






		$form->AddElement(array (CForm::type => CForm::Text,
		CForm::required => true,
		CForm::number => true,
		CForm::name => 'user_id_entry'));


		$userpages = $this->runControls ();


		if (isset($_REQUEST['user_submit'])) {

			CLog::RecordReport("Performance Override", "submitting data" );

			$per_store_id = CGPC::do_clean($_REQUEST['performance_store'],TYPE_INT);
		//	if (!isset($userpages[$per_store_id])) {
				$AdminUser = CUser::getCurrentUser();

				if ($_REQUEST['month_dropdown'] == 0 || $_REQUEST['end_month_dropdown'] == 0) {
					$tpl->setErrorMsg('Please enter valid months for the starting and ending dates.');
				}
				else {

					$startdate = CGPC::do_clean($_REQUEST['starting_year'],TYPE_INT) . "-" . CGPC::do_clean($_REQUEST['month_dropdown'],TYPE_INT) . "-" . '01';


					$enddate = CGPC::do_clean($_REQUEST['ending_year'],TYPE_INT) . "-" . CGPC::do_clean($_REQUEST['end_month_dropdown'],TYPE_INT) . "-01";

					$sql = "insert into performance_royalty_override (performance_entered_by, `performance_start_date`,`performance_end_date`, store_id,performance_date_entered ) values ($AdminUser->id, '$startdate', DATE_SUB(DATE_ADD('$enddate', INTERVAL 1 MONTH), INTERVAL 1 SECOND), $per_store_id , now())";
					$obj = DAO_CFactory::create("performance_royalty_override");
					$rslt = $obj->query($sql);
					if ($rslt > 0) {
							$tpl->setStatusMsg('The store has been successfully added');
							$userpages = $this->runControls (); // inefficient


					}
				}
		//	}
		//	else
		//	{
		//		$tpl->setStatusMsg('There already exists a one-time performance standard override for this store.');
		//	}

			unset ($_REQUEST['user_submit']);

		}


		if (isset($_REQUEST['remove_access'])) {

			CLog::RecordReport("Performance Override", "removing access" );

			$updatelist = array();
			foreach($userpages as $element) {
				$str = "ch_" . $element['id'];
				if (isset($_REQUEST[$str]) && $_REQUEST[$str] == "on") {
					$updatelist[] = $element['id'];
				}

			}

			if (count($updatelist) > 0) {
				$explodedlist = implode($updatelist, ",");

				$sql = "update performance_royalty_override set is_deleted = 1 where performance_royalty_override.id in ($explodedlist)";
				$obj = DAO_CFactory::create("access_control_page_user");
				$rslt = $obj->query($sql);

				if ($rslt > 0)
					$userpages = $this->runControls ();

			}

			unset ($_REQUEST['remove_access']);
		}


		$form->AddElement(array (CForm::type => CForm::Submit,
			CForm::name => 'user_submit', CForm::value => 'Add'));


		$form->AddElement(array (CForm::type => CForm::Submit,
			CForm::name => 'remove_access', CForm::value => 'Remove Override'));


		$tpl->assign('controlpages', $controlpages);
		$tpl->assign('userpages', $userpages);

		$FormArray = $form->render(true);
		$tpl->assign('form_create', $FormArray);



	}

	function runControls ($store_id=NULL)
	{
		$sql = "SELECT `store`.`home_office_id`,`performance_royalty_override`.`store_id`,`store`.`store_name`,`store`.`city`, " .
		" `store`.`state_id`,`performance_royalty_override`.`id`,`performance_royalty_override`.`performance_start_date`,`performance_royalty_override`.`performance_end_date`, " .
		" `performance_royalty_override`.`performance_date_entered`,concat(`user`.`lastname`, ', ', `user`.`firstname`) as user_changed " .
		" FROM `performance_royalty_override` " ;

		$sql .= " Inner Join `store` ON `performance_royalty_override`.`store_id` = `store`.`id` " .
		" Inner Join `user` ON `performance_royalty_override`.`performance_entered_by` = `user`.`id` where performance_royalty_override.is_deleted = 0 ";


		if (!empty($store_id)) {
			$sql .=	 " and  performance_royalty_override.store_id = $store_id ";

		}

		$pages = array();
		$obj = DAO_CFactory::create("performance_royalty_override");
		$obj->query($sql);
		while ($obj->fetch()) {
			$pages[] = $obj->toArray();
		}
		return $pages;

	}


}

?>
