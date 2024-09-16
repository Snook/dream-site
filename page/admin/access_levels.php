<?php // page_admin_access_levels.php

require_once('includes/ValidationRules.inc');
require_once('includes/DAO/Address.php');
require_once("includes/CForm.inc");
require_once("includes/CPageAdminOnly.inc");
require_once('page/customer/account.php');
require_once('CMail.inc');
require_once('includes/class.inputfilter_clean.php');

class page_admin_access_levels extends CPageAdminOnly
{

	private static $special_privileges = array(
		CUser::FRANCHISE_MANAGER => array(
			8 => array(
				'id' => 8,
				'title' => 'Access to Profit and Loss Form',
				'active' => false
			),
			9 => array(
				'id' => 9,
				'title' => 'Access to Weekly Report',
				'active' => false
			)
		),
		CUser::EVENT_COORDINATOR => array(
			8 => array(
				'id' => 8,
				'title' => 'Access to Profit and Loss Form',
				'active' => false
			)
		),
		CUser::OPS_LEAD => array(
			8 => array(
				'id' => 8,
				'title' => 'Access to Profit and Loss Form',
				'active' => false
			)
		)
	);

	private $isAdmin = true;

	private function getSpecialPrivileges($user_id, $access_level)
	{
		if (array_key_exists($access_level, self::$special_privileges))
		{

			$tempArr = self::$special_privileges[$access_level];
			$id_array = array();
			if (!empty($tempArr))
			{
				foreach ($tempArr as $k => $data)
				{
					$id_array[] = $k;
				}

				$usersAccess = DAO_CFactory::create('access_control_page_user');
				$usersAccess->query("select * from access_control_page_user where user_id = $user_id and access_control_page_id in (" . implode(",", $id_array) . ") and is_deleted = 0");

				while ($usersAccess->fetch())
				{
					$tempArr[$usersAccess->access_control_page_id]['active'] = true;
				}

				return $tempArr;
			}
		}

		return array();
	}

	private function removeSpecialPrivilegesofUserType($user_id, $prevUserType)
	{
		if (array_key_exists($prevUserType, self::$special_privileges))
		{
			$tempArr = self::$special_privileges[$prevUserType];
			$id_array = array();
			if (!empty($tempArr))
			{
				foreach ($tempArr as $k => $data)
				{
					$id_array[] = $k;
				}

				$usersAccess = DAO_CFactory::create('access_control_page_user');
				$usersAccess->query("update access_control_page_user set is_deleted = 1 where user_id = $user_id and access_control_page_id in (" . implode(",", $id_array) . ") and is_deleted = 0");
			}
		}
	}

	private function updatePrivilegesofUserType($user_id, $userType)
	{
		// first delete them all
		$this->removeSpecialPrivilegesofUserType($user_id, $userType);
		// then add the current set

		if (array_key_exists($userType, self::$special_privileges))
		{
			$tempArr = self::$special_privileges[$userType];
			$id_array = array();
			if (!empty($tempArr))
			{
				foreach ($tempArr as $k => $data)
				{
					if (!empty($_POST['priv_' . $k]))
					{
						$usersAccess = DAO_CFactory::create('access_control_page_user');
						$usersAccess->user_id = $user_id;
						$usersAccess->access_control_page_id = $k;
						$usersAccess->insert();
					}
				}
			}
		}
	}

	function runFranchiseOwner()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runAccessLevels();
	}

	function runFranchiseManager()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runAccessLevels();
	}

	function runOpsLead()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runAccessLevels();
	}

	function runHomeOfficeManager()
	{
		$this->runAccessLevels();
	}

	function runSiteAdmin()
	{
		$this->isAdmin = true;
		$this->runAccessLevels();
	}

	function runAccessLevels()
	{

		$store = null;
		$assignedStoreID = null;
		$usersStoreArray = array();
		form_account::$forwardTo = '/backoffice/list_users';
		$prevUserType = null;
		$id = null;
		$user_type = null;

		if ((array_key_exists('id', $_GET) !== false) && ($_GET['id']) && is_numeric($_GET['id']))
		{
			$id = $_GET['id'];
		}

		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$AdminUser = CUser::getCurrentUser();

		if (isset($this->currentStore) && $this->currentStore)
		{
			$store = $this->currentStore;  // this is for fadmins
		}

		if (array_key_exists('user_type', $_POST) === true)
		{
			$user_type = CGPC::do_clean($_POST['user_type'], TYPE_STR);
			$tpl->assign('new_user_type', $user_type);

			if ($user_type === CUser::FRANCHISE_OWNER || $user_type === CUser::FRANCHISE_MANAGER || $user_type === CUser::FRANCHISE_STAFF || $user_type == CUser::GUEST_SERVER || $user_type === CUser::FRANCHISE_LEAD || $user_type == CUser::MANUFACTURER_STAFF || $user_type == CUser::EVENT_COORDINATOR || $user_type == CUser::OPS_SUPPORT || $user_type == CUser::OPS_LEAD || $user_type == CUser::DISHWASHER || $user_type == CUser::NEW_EMPLOYEE)
			{
				$tpl->assign('mapping_required', true);
			}
		}

		$deletedStoreID = null;

		if ((isset($_POST['al_action']) && $_POST['al_action'] === "delete") || isset($_POST['deleteCurrentStore']))
		{
			$utf = DAO_CFactory::create('user_to_franchise');
			$utf->user_id = $id;

			if (is_numeric($_POST['which_store']))
			{
				$utf->store_id = $_POST['which_store'];

				if ($utf->find())
				{
					$tpl->setStatusMsg('This user is a franchise owner of this store and must be managed from the franchise details.');
				}
				else
				{
					$User = DAO_CFactory::create('user');
					$User->id = $id;
					$found = $User->find(true);
					$prevUserType = $User->user_type;

					$allUTS = DAO_CFactory::create('user_to_store');
					$allUTS->user_id = $id;

					$allUTS->find();

					if ($allUTS->N == 1)
					{
						// if we are deleting the last store then set the access level for this user back to CUSTOMER
						$Customer = DAO_CFactory::create('user');
						$Customer->id = $id;

						if ($Customer->find(true))
						{
							// does prev user type have special accesses
							$this->removeSpecialPrivilegesofUserType($id, $prevUserType);

							$Customer->user_type = CUser::CUSTOMER;

							$Customer->update(false, CUser::CUSTOMER);

							$Mail = new CMail();

							$Mail->send(ADMINISTRATOR_EMAIL, ADMINISTRATOR_EMAIL, ADMINISTRATOR_EMAIL, ADMINISTRATOR_EMAIL, $Customer->firstname . ' ' . $Customer->lastname . ' has lost BackOffice access', '<a href="' . HTTPS_SERVER . '/backoffice/user-details?id=' . $Customer->id . '">' . $Customer->firstname . ' ' . $Customer->lastname . '</a> has been set to Customer status and no longer has access to the BackOffice. BackOffice NDA has been reset to "unaccepted".', null, '', '', null, 'admin_generic');
						}
					}

					$deleteThisUTS = DAO_CFactory::create('user_to_store');
					$deleteThisUTS->user_id = $id;

					if (is_numeric($_POST['which_store']))
					{
						$deleteThisUTS->store_id = $_POST['which_store'];

						$deleteThisUTS->find(true);
						$deleteThisUTS->delete();
						// need to set the current store to something reasonable, for now set to the store just deleted
						if (isset($_POST['store']) && is_numeric($_POST['store']))
						{
							$_POST['store'] = $deletedStoreID;
						}
					}
					else
					{
						$tpl->setStatusMsg('The store ID is invalid.');
					}
				}
			}
			else
			{
				$tpl->setStatusMsg('The store ID is invalid.');
			}
		}

		// *******************************************************************************
		// The user pressed the Save Changes button (after selecting a new access level and possibly choosing a store
		// *******************************************************************************
		if (!is_null($user_type) && (isset($_POST['submit_account']) || isset($_POST['addCurrentStore'])))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $id;
			$found = $User->find(true);
			if ((!$found) || ($found > 1))
			{
				throw new Exception("User not found in access levels.");
			}
			$prevUserType = $User->user_type;
			$User->user_type = $user_type;

			if ($user_type == "")
			{
				$user_type = CUser::CUSTOMER; // oops, an error occurred, just set to the basic account
			}

			$enrollmentRequired = false;
			if ($prevUserType == CUser::CUSTOMER && $user_type != CUser::DISHWASHER && $user_type != CUser::CUSTOMER)
			{
				$enrollmentRequired = true;
			}

			// Update the User Type in the user table
			$User->update(false, $user_type);

			// Update the user_to_store table
			if ($user_type == CUser::FRANCHISE_OWNER || $user_type == CUser::FRANCHISE_STAFF || $user_type == CUser::GUEST_SERVER || $user_type == CUser::FRANCHISE_MANAGER || $user_type == CUser::FRANCHISE_LEAD || $user_type == CUser::MANUFACTURER_STAFF || $user_type == CUser::EVENT_COORDINATOR || $user_type == CUser::OPS_SUPPORT || $user_type == CUser::OPS_LEAD || $user_type == CUser::DISHWASHER || $user_type == CUser::NEW_EMPLOYEE)
			{
				$store_id = !is_null($store) ? $store : (!empty($_REQUEST['store']) ? $_REQUEST['store'] : null);

				if (is_null($store_id))
				{
					throw new Exception("Could not locate the stores Franchise ID from the Dream Dinners system.");
				}
			}

			// first check to see if we are changed to a user type that does not map to stores
			// and delete the user_to_store mapping
			if ($user_type == CUser::CUSTOMER)
			{
				$utf = DAO_CFactory::create('user_to_franchise');
				$utf->user_id = $id;
				$utf->find();

				while ($utf->fetch())
				{
					$utf->delete();
				}

				$uts = DAO_CFactory::create('user_to_store');
				$uts->user_id = $id;
				$uts->find();

				while ($uts->fetch())
				{
					$uts->delete();
				}

				// also delete and special priveleges
				$acpu = DAO_CFactory::create('access_control_page_user');
				$acpu->user_id = $id;
				$acpu->find();

				while ($acpu->fetch())
				{
					$acpu->delete();
				}
			}
			else if ($user_type != CUser::HOME_OFFICE_STAFF && $user_type != CUser::HOME_OFFICE_MANAGER && $user_type != CUser::SITE_ADMIN)
			{
				$uts = DAO_CFactory::create('user_to_store');
				$uts->user_id = $id;
				$uts->store_id = $store_id;
				$found = $uts->find(true);

				// **********************************************
				// Add the new details to the owner table here
				// **********************************************
				if (!$found)
				{
					$uts = DAO_CFactory::create('user_to_store');
					$uts->user_id = $id;
					$uts->active = 1;

					$uts->display_text = 'NULL';
					$uts->display_to_public = 0;
					$uts->store_id = $store_id;
					$rslt = $uts->insert();

					if ($rslt == 0)
					{
						throw new exception("Could not add the user account to the Dream Dinners system.");
					}
				}
			}

			if ($AdminUser->user_type != CUser::FRANCHISE_MANAGER && $AdminUser->user_type != CUser::OPS_LEAD)
			{
				if ($user_type != $prevUserType)
				{
					// does prev user type have special accesses
					$this->removeSpecialPrivilegesofUserType($id, $prevUserType);
					$this->updatePrivilegesofUserType($id, $user_type);
				}
				else
				{
					$this->updatePrivilegesofUserType($id, $user_type);
				}
			}

			$tpl->setStatusMsg('This user account has been changed to ' . CUser::userTypeText($user_type));
		}

		// **********************************************
		// check basic user details.
		// this will be called every time through the page
		// **********************************************
		$User = new CUser();
		if ($id)
		{
			$User->id = $id;
			$found = $User->find(true);
			$franchiseArray = array();

			if (!$found)
			{
				$tpl->setErrorMsg('That guest could not be found, or you do not have permission to edit that account.');
				CApp::bounce('/backoffice/list_users');
			}

			if (is_null($user_type))
			{
				$user_type = $User->user_type;
			}

			$tpl->assign('current_user_type', $User->user_type);

			if ($user_type === CUser::FRANCHISE_OWNER || $user_type === CUser::FRANCHISE_MANAGER || $user_type === CUser::FRANCHISE_STAFF || $user_type === CUser::FRANCHISE_LEAD || $user_type == CUser::MANUFACTURER_STAFF || $user_type == CUser::EVENT_COORDINATOR || $user_type == CUser::GUEST_SERVER || $user_type == CUser::OPS_SUPPORT || $user_type == CUser::OPS_LEAD || $user_type == CUser::DISHWASHER || $user_type == CUser::NEW_EMPLOYEE)

			{
				$Form->AddElement(array(
					CForm::type => CForm::StoreDropDown,
					CForm::name => 'store',
					CForm::showInactiveStores => true,
					CForm::onChangeSubmit => true,
					CForm::required => true
				));
			}

			// if the current store and user already has a mapping then set the default values
			$current_store_id = !is_null($store) ? $store : (isset($_REQUEST['store']) ? $_REQUEST['store'] : null);

			if (is_null($current_store_id))
			{
				$current_store_ = $Form->value('store');
			}

			$tpl->assign('user_type', $User->user_type);
			$tpl->assign('firstname', $User->firstname);
			$tpl->assign('lastname', $User->lastname);
			$tpl->assign('email', $User->primary_email);
			$tpl->assign('id', $User->id);
			$tpl->assign('page_title', 'Access Levels');

			$foundCurrentStore = false;

			$uts = DAO_CFactory::create('user_to_store');
			$uts->query("SELECT
					s.store_name,
					s.id,
					uts.id AS uts_id,
					uts.store_id,
					uts.display_to_public,
					uts.display_text
					FROM store AS s
					INNER JOIN user_to_store AS uts ON uts.store_id = s.id AND uts.user_id = '" . $id . "' AND uts.is_deleted = '0'
					WHERE s.is_deleted = '0'");

			while ($uts->fetch())
			{
				$usersStoreArray[$uts->store_id] = array(
					'id' => $uts->store_id,
					'name' => $uts->store_name,
					'display' => $uts->display_to_public,
					'text' => $uts->display_text,
					'uts_id' => $uts->uts_id
				);
			}

			$tpl->assign('users_stores', $usersStoreArray);

			$utf = DAO_CFactory::create('user_to_franchise');
			$utf->query("SELECT
				utf.franchise_id,
				f.franchise_name,
				f.active,
				f.timestamp_created,
				f.timestamp_updated
				FROM user_to_franchise AS utf
				INNER JOIN franchise AS f ON f.id = utf.franchise_id AND f.is_deleted = '0'
				WHERE utf.user_id = '" . $id . "'
				AND utf.is_deleted = '0'
				ORDER BY f.franchise_name ASC");

			$usersFranchiseArray = array();

			while ($utf->fetch())
			{
				$usersFranchiseArray[$utf->franchise_id] = array(
					'id' => $utf->franchise_id,
					'franchise_name' => $utf->franchise_name,
					'active' => $utf->active,
					'timestamp_created' => $utf->timestamp_created,
					'timestamp_updated' => $utf->timestamp_updated
				);
			}

			$tpl->assign('users_franchises', $usersFranchiseArray);
		}
		//***********************************************
		//build user_type drop downs  for different account types
		//***********************************************
		$Form->DefaultValues['user_type'] = $User->user_type;  // set to the current user type.

		if ($id && (CUser::getCurrentUser()->id != $id))
		{
			if ($this->isAdmin == true)
			{

				// also, if not fadmin - get current store name and ID
				$currentStore = CStore::getFranchiseStore();

				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::name => "user_type",
					CForm::required => false,
					CForm::onChangeSubmit => true,
					CForm::options => array(
						CUser::CUSTOMER => CUser::userTypeText(CUser::CUSTOMER),
						CUser::HOME_OFFICE_MANAGER => CUser::userTypeText(CUser::HOME_OFFICE_MANAGER),
						CUser::HOME_OFFICE_STAFF => CUser::userTypeText(CUser::HOME_OFFICE_STAFF),
						// Owners need to now be add/removed in franchise tool, RCS : Jul 25, 2014 // CUser::FRANCHISE_OWNER => CUser::userTypeText(CUser::FRANCHISE_OWNER),
						CUser::FRANCHISE_MANAGER => CUser::userTypeText(CUser::FRANCHISE_MANAGER),
						CUser::FRANCHISE_LEAD => CUser::userTypeText(CUser::FRANCHISE_LEAD),
						CUser::GUEST_SERVER => CUser::userTypeText(CUser::GUEST_SERVER),
						CUser::EVENT_COORDINATOR => CUser::userTypeText(CUser::EVENT_COORDINATOR),
						CUser::OPS_SUPPORT => CUser::userTypeText(CUser::OPS_SUPPORT),
						CUser::OPS_LEAD => CUser::userTypeText(CUser::OPS_LEAD),
						CUser::DISHWASHER => CUser::userTypeText(CUser::DISHWASHER),
						CUser::MANUFACTURER_STAFF => CUser::userTypeText(CUser::MANUFACTURER_STAFF)

					)
				));
			}
			else
			{
				// also, if not fadmin - get current store name and ID
				$currentStore = CStore::getFranchiseStore();

				$tpl->assign('currentStoreName', $currentStore->store_name);
				$tpl->assign('currentStoreID', $currentStore->id);

				$tpl->assign('store_is_linked', array_key_exists($currentStore->id, $usersStoreArray));
				$tpl->assign('has_other_links', count($usersStoreArray) > 0 && !array_key_exists($currentStore->id, $usersStoreArray));

				if ($AdminUser->user_type == CUser::FRANCHISE_MANAGER)
				{

					$Form->AddElement(array(
						CForm::type => CForm::DropDown,
						CForm::name => "user_type",
						CForm::required => false,
						CForm::onChangeSubmit => true,
						CForm::options => array(
							CUser::CUSTOMER => CUser::userTypeText(CUser::CUSTOMER),
							CUser::FRANCHISE_LEAD => CUser::userTypeText(CUser::FRANCHISE_LEAD),
							CUser::GUEST_SERVER => CUser::userTypeText(CUser::GUEST_SERVER),
							CUser::EVENT_COORDINATOR => CUser::userTypeText(CUser::EVENT_COORDINATOR),
							CUser::OPS_SUPPORT => CUser::userTypeText(CUser::OPS_SUPPORT),
							CUser::OPS_LEAD => CUser::userTypeText(CUser::OPS_LEAD),
							CUser::DISHWASHER => CUser::userTypeText(CUser::DISHWASHER)
						)
					));
				}
				else if ($AdminUser->user_type == CUser::OPS_LEAD)
				{

					$Form->AddElement(array(
						CForm::type => CForm::DropDown,
						CForm::name => "user_type",
						CForm::required => false,
						CForm::onChangeSubmit => true,
						CForm::options => array(
							CUser::CUSTOMER => CUser::userTypeText(CUser::CUSTOMER),
							CUser::FRANCHISE_LEAD => CUser::userTypeText(CUser::FRANCHISE_LEAD),
							CUser::GUEST_SERVER => CUser::userTypeText(CUser::GUEST_SERVER),
							CUser::EVENT_COORDINATOR => CUser::userTypeText(CUser::EVENT_COORDINATOR),
							CUser::OPS_SUPPORT => CUser::userTypeText(CUser::OPS_SUPPORT),
							CUser::DISHWASHER => CUser::userTypeText(CUser::DISHWASHER)
						)
					));
				}
				else
				{
					$Form->AddElement(array(
						CForm::type => CForm::DropDown,
						CForm::name => "user_type",
						CForm::required => false,
						CForm::onChangeSubmit => true,
						CForm::options => array(
							CUser::CUSTOMER => CUser::userTypeText(CUser::CUSTOMER),
							CUser::FRANCHISE_LEAD => CUser::userTypeText(CUser::FRANCHISE_LEAD),
							CUser::FRANCHISE_MANAGER => CUser::userTypeText(CUser::FRANCHISE_MANAGER),
							CUser::GUEST_SERVER => CUser::userTypeText(CUser::GUEST_SERVER),
							CUser::EVENT_COORDINATOR => CUser::userTypeText(CUser::EVENT_COORDINATOR),
							CUser::OPS_SUPPORT => CUser::userTypeText(CUser::OPS_SUPPORT),
							CUser::OPS_LEAD => CUser::userTypeText(CUser::OPS_LEAD),
							CUser::DISHWASHER => CUser::userTypeText(CUser::DISHWASHER)
						)
					));
				}
			}
		}
		else
		{
			// Don't allow user to change their own level, only show their currently set level.
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => "user_type",
				CForm::required => false,
				CForm::disabled => true,
				CForm::options => array(
					$User->user_type => CUser::userTypeText($User->user_type)
				)
			));
		}

		$tpl->assign('user', $User);

		if (($AdminUser->user_type == CUser::SITE_ADMIN || $AdminUser->user_type == CUser::HOME_OFFICE_MANAGER) && $user_type == CUser::FRANCHISE_MANAGER)
		{
			$tpl->assign('add_mgr_privileges', $this->getSpecialPrivileges($User->id, CUser::FRANCHISE_MANAGER));
		}
		else if (($AdminUser->user_type == CUser::SITE_ADMIN || $AdminUser->user_type == CUser::HOME_OFFICE_MANAGER) && $user_type == CUser::EVENT_COORDINATOR)
		{
			$tpl->assign('add_mgr_privileges', $this->getSpecialPrivileges($User->id, CUser::EVENT_COORDINATOR));
		}
		else if (($AdminUser->user_type == CUser::SITE_ADMIN || $AdminUser->user_type == CUser::HOME_OFFICE_MANAGER) && $user_type == CUser::OPS_LEAD)
		{
			$tpl->assign('add_mgr_privileges', $this->getSpecialPrivileges($User->id, CUser::OPS_LEAD));
		}
		else
		{
			$tpl->assign('add_mgr_privileges', array());
		}

		$tpl->assign('target_user_type', $user_type);

		$tpl->assign('form_account', $Form->Render());
		$tpl->assign('isAdmin', $this->isAdmin);
	}
}

?>