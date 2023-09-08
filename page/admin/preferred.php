<?php // preferred.php

require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUserPreferred.php");

class page_admin_preferred extends CPageAdminOnly
{

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
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

		$id = false;

		if (isset($_GET['id']))
		{
			$id = CGPC::do_clean($_GET['id'], TYPE_INT);
		}

		if (!$id)
		{
			if (CUser::getCurrentUser()->user_type != CUser::SITE_ADMIN && CUser::getCurrentUser()->user_type != CUser::HOME_OFFICE_MANAGER)
			{
				CApp::bounce('?page=admin_list_users');
			}

			$id = null;
		}

		if (isset($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back']);
		}
		else if ($id)
		{
			$tpl->assign('back', '?page=admin_user_details&amp;id=' . $id);
		}
		else
		{
			$tpl->assign('back', '?page=admin_list_users');
		}

		//get customer's name
		if ($id)
		{
			$Customer = DAO_CFactory::create('user');
			$Customer->id = $id;
			$Customer->find(true);
			$tpl->assign('user', $Customer);
		}

		//we actually want to always use a GET instead of a post for reports
		//not sure if we'll use much of the CForm functionality
		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		// $allowAll = false;
		// if ( CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN )
		$allowAll = true;

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER)
		{
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::allowAllOption => $allowAll,
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
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "preferred_flat",
			CForm::number => true,
			CForm::required => false
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "preferred_percent",
			CForm::number => true,
			CForm::required => false
		));
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "preferred_type",
			CForm::required => false,
			CForm::options => array(
				CUserPreferred::FLAT => 'Flat Rate',
				CUserPreferred::PERCENTAGE => 'Percent Discount'
			)
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "preferred_cap_servings",
			CForm::number => true,
			CForm::required => false
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "preferred_cap_items",
			CForm::number => true,
			CForm::required => false
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "preferred_cap_type",
			CForm::required => false,
			CForm::options => array(
				CUserPreferred::PREFERRED_CAP_NONE => 'None',
				CUserPreferred::PREFERRED_CAP_SERVINGS => 'Servings',
				CUserPreferred::PREFERRED_CAP_ITEMS => 'Items'
			)
		));

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "exclude",
			CForm::required => false
		));

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "include_sides",
			CForm::required => false,
			CForm::disabled => true,
			CForm::Label => 'Sides &amp; Sweets Included'
		));

		if (empty($store))
		{
			$store = $Form->value('store');
		}

		if ((CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER) && !empty($id))
		{
			//fetch others for this guest
			$AllUserPreferred = DAO_CFactory::create("user_preferred");
			$AllUserPreferred->query("select s.store_name, s.state_id, count(*) as records from user_preferred up
								join store s on s.id = up.store_id and s.id <> $store
								where up.user_id = $id
								and up.is_deleted = 0
								group by s.store_name, s.state_id
								order by s.state_id, s.store_name ");
			$otherUPS = array();

			while ($AllUserPreferred->fetch())
			{
				$otherUPS[] = $AllUserPreferred->toArray();
			}

			$tpl->assign('ups_at_other_stores', $otherUPS);
		}



		//handle deletions
		if (isset($_POST['action']) && ($_POST['action'] == 'delete'))
		{
			if (isset($_POST['upid']) && ($_POST['upid']))
			{
				$UserPreferred = DAO_CFactory::create("user_preferred");
				$UserPreferred->id = CGPC::do_clean($_POST['upid'], TYPE_INT);
				$UserPreferred->find(true);
				// only delete if the creator or site admin
				// TODO: account for multiple franchise owners
				// if ( ($UserPreferred->created_by == CUser::getCurrentUser()->id) or (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN) ) {
				$UserPreferred->delete();
				$tpl->setStatusMsg("That customer discount has been deleted");
				// }
			}
		}

		$invalidData = false;
		//handle inserts
		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'create'))
		{
			if ($id)
			{
				$InsertUserPreferred = DAO_CFactory::create("user_preferred");
				$InsertUserPreferred->user_id = $id;

				if ($_POST && ((($_POST['preferred_type'] == CUserPreferred::FLAT) && isset($_POST['preferred_flat'])) || (($_POST['preferred_type'] == CUserPreferred::PERCENTAGE) && isset($_POST['preferred_percent']))) && ((($_POST['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_NONE)) || (($_POST['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_SERVINGS) && isset($_POST['preferred_cap_servings'])) || (($_POST['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_ITEMS) && isset($_POST['preferred_cap_items']))))
				{
					switch ($_POST['preferred_type'])
					{
						case CUserPreferred::PERCENTAGE:
							if ($_POST['preferred_percent'] <= 0 || $_POST['preferred_percent'] > 100)
							{
								$tpl->setErrorMsg("The percent value must be a number greater than 0 and not more than 100.");
								$invalidData = true;
							}
							$InsertUserPreferred->preferred_value = CGPC::do_clean($_POST['preferred_percent'], TYPE_NUM);
							$InsertUserPreferred->preferred_type = CUserPreferred::PERCENTAGE;
							break;

						case CUserPreferred::FLAT:
							if ($_POST['preferred_flat'] <= 0 || $_POST['preferred_flat'] > 99)
							{
								$tpl->setErrorMsg("The flat rate value must be a number greater than 0 and less than 99.");
								$invalidData = true;
							}

							$InsertUserPreferred->preferred_value = CGPC::do_clean($_POST['preferred_flat'], TYPE_NUM);
							$InsertUserPreferred->preferred_type = CUserPreferred::FLAT;
							break;
					}

					switch ($_POST['preferred_cap_type'])
					{
						case CUserPreferred::PREFERRED_CAP_NONE:
							$InsertUserPreferred->preferred_cap_value = 0;
							$InsertUserPreferred->preferred_cap_type = CUserPreferred::PREFERRED_CAP_NONE;
							break;

						case CUserPreferred::PREFERRED_CAP_ITEMS:
							if ($_POST['preferred_cap_items'] <= 0 || $_POST['preferred_cap_items'] > 99)
							{
								$tpl->setErrorMsg("The preferred item cap must be a number greater than 0 and less than 100.");
								$invalidData = true;
							}

							$InsertUserPreferred->preferred_cap_value = CGPC::do_clean($_POST['preferred_cap_items'], TYPE_NUM);
							$InsertUserPreferred->preferred_cap_type = CUserPreferred::PREFERRED_CAP_ITEMS;
							break;
						case CUserPreferred::PREFERRED_CAP_SERVINGS:
							if ($_POST['preferred_cap_servings'] <= 0 || $_POST['preferred_cap_servings'] > 99)
							{
								$tpl->setErrorMsg("The preferred serving cap must be a number greater than 0 and less than 100.");
								$invalidData = true;
							}

							$InsertUserPreferred->preferred_cap_value = CGPC::do_clean($_POST['preferred_cap_servings'], TYPE_NUM);
							$InsertUserPreferred->preferred_cap_type = CUserPreferred::PREFERRED_CAP_SERVINGS;
							break;
					}

					if ($store == 'all')
					{
						$InsertUserPreferred->all_stores = 1;
					}
					else if ($store)
					{
						$InsertUserPreferred->store_id = $store;
						$InsertUserPreferred->all_stores = 0;
					}

					if (isset($_POST['include_sides']))
					{
						$InsertUserPreferred->include_sides = 1;
					}
					else
					{
						$InsertUserPreferred->include_sides = 0;
					}

					$InsertUserPreferred->user_preferred_start = date("Y-m-d 00:00:00");

					if (isset($_POST['exclude']))
					{
						$InsertUserPreferred->exclude_from_reports = 1;
					}
					else
					{
						$InsertUserPreferred->exclude_from_reports = 0;
					}

					if (!$invalidData && $InsertUserPreferred->insert())
					{
						$tpl->setStatusMsg('The customer discount has been added.');
					}
					else
					{
						$tpl->setErrorMsg('An error occurred adding the preferred price.');
					}
				}
			}
		}

		$UserPreferred = DAO_CFactory::create("user_preferred");
		$UserPreferred->is_deleted = 0;
		$UserPreferred->user_id = $id;
		if ($store == 'all')
		{
			$store = null;
		}
		else
		{
		}
		$UserPreferred->findActive($store);

		$rows = array();
		while ($UserPreferred->fetch())
		{
			$rows[$UserPreferred->id] = $UserPreferred->toArray();

			if ($rows[$UserPreferred->id]['preferred_type'] == CUserPreferred::PERCENTAGE)
			{
				$rows[$UserPreferred->id]['preferred_value'] = $rows[$UserPreferred->id]['preferred_value'] . '%';
			}
			else
			{
				$rows[$UserPreferred->id]['preferred_value'] = '$' . $rows[$UserPreferred->id]['preferred_value'];
			}

			if ($UserPreferred->exclude_from_reports)
			{
				$rows[$UserPreferred->id]['exclude'] = 'YES';
			}
			else
			{
				$rows[$UserPreferred->id]['exclude'] = 'NO';
			}
		}

		$tpl->assign('rows', $rows);
		$tpl->assign('customer_id', $id);
		$tpl->assign('form_preferred', $Form->render());
	}
}

?>