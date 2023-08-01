<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");


class page_admin_archive_store extends CPageAdminOnly {

	function runSiteAdmin() {
		$tpl = CApp::instance()->template();
		$form = new CForm();
		$form->Repost = TRUE;


		if (isset($_GET['store'])) {


			$storeid = CGPC::do_clean($_GET['store'],TYPE_INT);
			$month_array = array ("Select a Month", 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');


			$form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::onChangeSubmit => false,
								CForm::allowAllOption => false,
								CForm::options => $month_array,
								CForm::name => 'grandOpeningMonth'));

			$form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::maxlength => 2,
			CForm::name => 'grandOpeningDay'));


			$form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => true,
			CForm::maxlength => 4,
			CForm::name => 'grandOpeningYear'));


			$form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::onChangeSubmit => false,
								CForm::allowAllOption => false,
								CForm::options => $month_array,
								CForm::name => 'storeClosedMonth'));

			$form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => false,
			CForm::number => true,
			CForm::maxlength => 2,
			CForm::name => 'storeClosedDay'));


			$form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => false,
			CForm::number => true,
			CForm::maxlength => 4,
			CForm::name => 'storeClosedYear'));



			$form->AddElement(array (CForm::type => CForm::Text,
			CForm::required => true,
			CForm::number => false,
			CForm::maxlength => 10,
			CForm::name => 'homeOfficeID'));


			$form->AddElement(array (CForm::type => CForm::Submit,
			CForm::name => 'user_submit', CForm::value => 'Update Store'));


			 $form->AddElement(array (CForm::type => CForm::TextArea,
			 CForm::rows => '4',
			 CForm::cols => '40',
			 CForm::name => 'admin_notes'));


			$store = DAO_CFactory::create("store");
			$store->id =$storeid;
			$store->find(true);

			$tpl->assign('store', $store);


		}

		if (isset($_REQUEST['user_submit'])) {

			$gday = isset($_REQUEST['grandOpeningDay']) ? $_REQUEST['grandOpeningDay'] : null;
			$gmon = isset($_REQUEST['grandOpeningMonth']) ? $_REQUEST['grandOpeningMonth'] : null;
			$gyear = isset($_REQUEST['grandOpeningYear']) ? $_REQUEST['grandOpeningYear'] : null;
			$gday = CGPC::do_clean($gday, TYPE_STR);
			$gmon = CGPC::do_clean($gmon, TYPE_STR);
			$gyear = CGPC::do_clean($gyear, TYPE_STR);

			$eday = isset($_REQUEST['storeClosedDay']) ? $_REQUEST['storeClosedDay'] : 0;
			$emon = isset($_REQUEST['storeClosedMonth']) ? $_REQUEST['storeClosedMonth'] : 0;
			$eyear = isset($_REQUEST['storeClosedYear']) ? $_REQUEST['storeClosedYear'] : 0;
			$eday = CGPC::do_clean($eday, TYPE_INT);
			$emon = CGPC::do_clean($emon, TYPE_INT);
			$eyear = CGPC::do_clean($eyear, TYPE_INT);

			$details = isset($_REQUEST['admin_notes']) ? $_REQUEST['admin_notes'] : null;
			$details = CGPC::do_clean($details, TYPE_STR);


			$homeOfficeID = isset($_REQUEST['homeOfficeID']) ? $_REQUEST['homeOfficeID'] : null;
			$homeOfficeID = CGPC::do_clean($homeOfficeID, TYPE_INT);

			if ($gmon == 0) {
				$tpl->setErrorMsg('Please select a valid month.');
			}

			if (empty($eday) || empty($emon) || empty($eyear)) {
				$eday = '00';
				$emon = '00';
				$eyear = '0000';
			}



			if ($homeOfficeID <= 0 ) {
				$tpl->setErrorMsg('Please select a valid home office id.');
			}


			$storeopendate = $gyear . '-' . $gmon . '-' . $gday;
			$enddate = $eyear . '-' . $emon . '-' . $eday;

// start transaction

			if ($store) {

				$transactionSuccess = false;

				$storeUpdate = DAO_CFactory::create("store_closure_history");

				$storeUpdate->query('START TRANSACTION;');

				$storeUpdate->store_id =$storeid;
				$storeUpdate->store_closure_date =$enddate;
				$storeUpdate->recorded_grand_opening_date =$store->grand_opening_date;
				$storeUpdate->recorded_home_office_id =$store->home_office_id;
				$storeUpdate->details =$details;
				$AdminUser = CUser::getCurrentUser();
				$storeUpdate->info_recorded_by=$AdminUser->id;
				$transactionSuccess = $storeUpdate->insert();

				if ($transactionSuccess > 0) {
					$storeMod = DAO_CFactory::create("store");
					$storeMod->id = $storeid;
					$storeMod->grand_opening_date =$storeopendate;
					$storeMod->home_office_id =$homeOfficeID;
					$rslt = $storeMod->update();

					if ($rslt > 0) {
						$storeUpdate->query('COMMIT;');
						$tpl->setStatusMsg('The store has been correctly archived and re-opening values have been set.');
					}
					else {
						
					    if (is_object($storeUpdate->_lastError))
					    {
					        CLog::Record("ARCHIVE DEBUG: " . print_r($storeUpdate->_lastError->message, true));
					    }
					    
					    $storeUpdate->query('ROLLBACK;');
						
						$tpl->setErrorMsg('An error occurred, please recheck your values and try again.');

					}

				}

			}

			// take the grand opening and home office id from the store object
// end transaction

		}

		$archivedStores = DAO_CFactory::create("store_closure_history");
		$archivedStores->find();
		$archStores=array();
		while($archivedStores->fetch()){
			$archStores[$archivedStores->id] = $archivedStores->toArray();
		} // while
		$tpl->assign('archived_stores', $archStores);



		$FormArray = $form->render(true);
		$tpl->assign('form_create', $FormArray);



	}

}

?>
