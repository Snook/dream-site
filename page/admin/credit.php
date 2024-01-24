<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/class.inputfilter_clean.php');

class page_admin_credit extends CPageAdminOnly
{

	private $storeSpecificStoreCreditView = false;
	private $canAddStoreCredit = true;

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->canAddStoreCredit = false;
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->canAddStoreCredit = false;
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->canAddStoreCredit = false;
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

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		if (isset($_GET['id']))
		{
			$id = CGPC::do_clean($_GET['id'],TYPE_INT);
		}
		else
		{
			CApp::bounce('/backoffice/list_users');
			$id = null;
		}

		$tpl->assign('canAddStoreCredit', $this->canAddStoreCredit);

		$Customer = DAO_CFactory::create('user');
		$Customer->id = $id;
		$Customer->find(true);
		$tpl->assign('customer_first', $Customer->firstname);
		$tpl->assign('customer_last', $Customer->lastname);

		$Form = new CForm();
		$Form->Repost = true;

		$store = false;

		$adminUser = CUser::getCurrentUser();

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));
		}
		else
		{
			$Store = CStore::getFranchiseStore();

			$Form->addElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'store',
				CForm::default_value => $Store->id
			));

			$this->storeSpecificStoreCreditView = CBrowserSession::getCurrentFadminStore();
		}

		if (empty($store))
		{
			$store = $Form->value('store');
		}

		$tpl->assign('store_id', $store);

		$storeSupportsPlatePoints = CStore::storeSupportsPlatePoints($store);
		$tpl->assign('storeSupportsPlatePoints', $storeSupportsPlatePoints);

		$editKey = COrders::generateConfirmationNum();
		if (!isset($_POST['submit_credit']))
		{
			// page is loaded but not submitted
			CBrowserSession::instance()->setValue('credit_key', $editKey);
		}

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::default_value => $editKey,
			CForm::name => 'page_credit_key'
		));

		// is submit state being reposted?, stop it if it is
		if (isset($_POST['submit_credit']))
		{

			$CookiesVersion = CBrowserSession::instance()->getValue('credit_key');

			if ($_POST['page_credit_key'] != $CookiesVersion)
			{
				unset($_POST['submit_credit']);

				// no resubmitting now but need to sync up cookie and post
				CBrowserSession::instance()->setValue('credit_key', $editKey);

				// add again but set the value directly override posted value
				$Form->AddElement(array(
					CForm::type => CForm::Hidden,
					CForm::value => $editKey,
					CForm::name => 'page_credit_key'
				));
			}
		}

		if (isset($_POST['submit_credit']))
		{

			CBrowserSession::instance()->setValue('credit_key', 'none');
			if (isset($_POST['newCreditAmount']))
			{
				if (is_numeric($_POST['newCreditAmount']) && $_POST['newCreditAmount'] > 0 && $_POST['newCreditAmount'] <= 500)
				{
					$newCredit = DAO_CFactory::create('store_credit');
					$newCredit->amount = $_POST['newCreditAmount'];
					$newCredit->user_id = $id;
					$newCredit->store_id = $store;
					$newCredit->credit_type = 3;// 3 = direct store credit

					$xssFilter = new InputFilter();
					$_POST['newCreditDesc'] = $xssFilter->process($_POST['newCreditDesc']);

					$newCredit->description = $_POST['newCreditDesc'];
					$newCredit->ip_address = $_SERVER['REMOTE_ADDR'];

					$newCredit->insert();
					unset($_POST['newCreditAmount']);
					unset($_POST['newCreditDesc']);

					$tpl->setStatusMsg('The Store Credit was successfully added');

					CBrowserSession::instance()->setValue('credit_key', $editKey);
					// add again but set the value directly override posted value
					$Form->AddElement(array(
						CForm::type => CForm::Hidden,
						CForm::value => $editKey,
						CForm::name => 'page_credit_key'
					));
				}
				else
				{
					$tpl->setErrorMsg('Please provide a credit amount that is greater than 0 and not more than 500');
				}
			}
			else
			{
				$tpl->setErrorMsg('Please provide a valid credit amount');
			}
		}

		$rows = CStoreCredit::getActiveCreditByUser($id, false, true);

		$IAFArray = array();
		$TODDArray = array();
		$DirectArray = array();
		$GCArray = array();
		$totalsArray = array();
		$goneArray = array();

		foreach ($rows as $thisCredit)
		{
			if ($this->storeSpecificStoreCreditView && $thisCredit['store_id'] != $this->storeSpecificStoreCreditView)
			{
				continue;
			}

			if ($thisCredit['is_expired'] || $thisCredit['is_redeemed'])
			{
				$program = "";

				if ($thisCredit['credit_type'] == 2)
				{
					if ($thisCredit['origination_type_code'] == 1 || $thisCredit['origination_type_code'] == 3)
					{
						$program = "IAF";
					}
					else
					{
						$program = "TODD";
					}
				}
				else if ($thisCredit['credit_type'] == 3)
				{
					$program = "Direct";
				}

				if ($thisCredit['credit_type'] == 3)
				{
					$thisCredit['user_description'] = "Direct - " . $thisCredit['description'];
					$thisCredit['other'] = "Date Added: " . $thisCredit['timestamp_created'];
				}
				else
				{
					$thisCredit['user_description'] = $program . " - " . $thisCredit['referred_user'] . " - " . $thisCredit['description'];
					$thisCredit['other'] = "na";
				}

				$goneArray[] = $thisCredit;
				continue;
			}

			if (!isset($totalsArray[$thisCredit['store_name']]))
			{
				$totalsArray[$thisCredit['store_name']] = array();
			}

			if (!isset($totalsArray[$thisCredit['store_name']]['available']))
			{
				$totalsArray[$thisCredit['store_name']]['available'] = 0;
			}

			if (!isset($totalsArray[$thisCredit['store_name']]['used']))
			{
				$totalsArray[$thisCredit['store_name']]['used'] = 0;
			}

			if (!isset($totalsArray[$thisCredit['store_name']]['store_name']))
			{
				$totalsArray[$thisCredit['store_name']]['store_name'] = "";
			}

			if (!isset($totalsArray[$thisCredit['store_name']]['store_id']))
			{
				$totalsArray[$thisCredit['store_name']]['store_id'] = false;
			}

			$totalsArray[$thisCredit['store_name']]['available'] += $thisCredit['amount'];
			$totalsArray[$thisCredit['store_name']]['store_name'] = $thisCredit['store_name'];
			$totalsArray[$thisCredit['store_name']]['store_id'] = $thisCredit['store_id'];

			if ($thisCredit['credit_type'] == 2)
			{
				if ($thisCredit['origination_type_code'] == 1 || $thisCredit['origination_type_code'] == 3)
				{
					$IAFArray[] = $thisCredit;
				}
				else
				{
					$TODDArray[] = $thisCredit;
				}
			}
			else if ($thisCredit['credit_type'] == 3)
			{
				$DirectArray[] = $thisCredit;
			}
			else if ($thisCredit['credit_type'] == 1)
			{
				$GCArray[] = $thisCredit;
			}
		}

		foreach ($TODDArray as &$thisTODD)
		{

			if (!empty($thisTODD['referrer_session_id']))
			{
				$Session = DAO_CFactory::create('session');
				$Session->id = $thisTODD['referrer_session_id'];
				$Session->selectAdd();
				$Session->selectAdd("session_start");
				if ($Session->find(true))
				{
					$thisTODD["event_date"] = CTemplate::dateTimeFormat($Session->session_start);
				}
				else
				{
					$thisTODD["event_date"] = "unknown";
				}
			}
		}

		$pendingArray = CStoreCredit::getPendingReferralCreditPerUser($this->storeSpecificStoreCreditView, $id);

		if (!empty($pendingArray))
		{
			foreach ($pendingArray as $id => $data)
			{
				$totalsArray[$data['store_name']]['pending'] += $data['amount'];
			}
		}

		$tpl->assign('pendingArray', $pendingArray);

		// amass totals by store

		if (!empty($totalsArray))
		{
			$StoreCredit = DAO_CFactory::create('store_credit');
			$StoreCredit->query("select sc.store_id, st.store_name, SUM(sc.amount)as amount_total from store_credit sc " . " join store st on st.id = sc.store_id " . " where sc.user_id = $id and sc.is_deleted = 0 and sc.is_expired = 0 and sc.is_redeemed = 1 " . " group by sc.store_id ");

			while ($StoreCredit->fetch())
			{
				$totalsArray[$StoreCredit->store_name]['used'] = $StoreCredit->amount_total;
				$totalsArray[$StoreCredit->store_name]['store_name'] = $StoreCredit->store_name;
			}
		}

		if ($storeSupportsPlatePoints)
		{

			$totalCredit = 0;
			$rows = array();
			$creditsObj = DAO_CFactory::create('points_credits');
			$creditsObj->query("select pc.id, pc.timestamp_created, pc.timestamp_updated, pc.credit_state, pc.dollar_value, pc.expiration_date, GROUP_CONCAT(puh.event_type) as events from points_credits pc
									left join points_to_points_credits ppc on ppc.points_credit_id = pc.id
									left join points_user_history puh on puh.id = ppc.points_user_history_id AND puh.is_deleted = '0'
									where pc.user_id = $id and pc.is_deleted = 0
									group by pc.id
									order by CAST(pc.credit_state as CHAR), pc.id desc");
			// DD EXP DATE DISPLAY 4
			while ($creditsObj->fetch())
			{
				$rows[$creditsObj->id] = array(
					"timestamp" => $creditsObj->timestamp_created,
					"timestamp_updated" => $creditsObj->timestamp_updated,
					'expires' => CPointsCredits::formatExpirationDateForGuest($creditsObj->expiration_date),
					'amount' => $creditsObj->dollar_value,
					'state' => $creditsObj->credit_state,
					'events' => $creditsObj->events
				);

				if ($creditsObj->credit_state == 'AVAILABLE')
				{
					$totalCredit += $creditsObj->dollar_value;
				}
			}

			$tpl->assign('totalCredit', $totalCredit);

			$tpl->assign('PP_Credits', $rows);
		}

		$tpl->assign('TODDArray', $TODDArray);
		$tpl->assign('IAFArray', $IAFArray);
		$tpl->assign('DirectArray', $DirectArray);
		$tpl->assign('GCArray', $GCArray);
		$tpl->assign('TotalsArray', $totalsArray);
		$tpl->assign('goneArray', $goneArray);

		$tpl->assign('customer_id', $id);

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "newCreditAmount",
			CForm::maxlength => 6,
			CForm::required => true,
			CForm::min => 0,
			CForm::max => 500,
			CForm::step => .01,
			CForm::size => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "newCreditDesc",
			CForm::required => true,
			CForm::maxlength => 255,
			CForm::size => 60
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "submit_credit",
			CForm::css_class => "btn btn-primary btn-sm",
			CForm::value => "Submit New Store Credit"
		));

		$tpl->assign('customer_id', $id);
		$tpl->assign('form', $Form->render());
	}
}

?>