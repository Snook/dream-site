<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_franchise_details extends CPageAdminOnly
{
	private $permission = array(
		'can_set_active' => false,
		'can_delete_franchise' => false,
		'can_add_store' => false,
		'can_remove_store' => false,
		'can_add_owner' => false,
		'can_remove_owner' => false
	);


	function runHomeOfficeManager()
	{
		$this->permission['can_add_owner'] = true;
		$this->permission['can_remove_owner'] = true;

		$this->runFranchiseDetails();
	}

	function runSiteAdmin()
	{
		$this->permission['can_set_active'] = true;
		$this->permission['can_add_store'] = true;
		$this->permission['can_remove_store'] = true;
		$this->permission['can_add_owner'] = true;
		$this->permission['can_remove_owner'] = true;

		$this->runFranchiseDetails();
	}

	function runFranchiseDetails()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		if (!empty($_GET['id']) && is_numeric($_GET['id']))
		{
			$id = $_GET['id'];
		}
		else
		{
			$tpl->setErrorMsg('No franchise id specified.');

			CApp::bounce('main.php?page=admin_list_franchise');
		}

		$franchise = DAO_CFactory::create('franchise');
		$franchise->id = $id;
		$franchise->find(true);
		$tpl->assign('franchise', $franchise->toArray());

		$user_type = CUser::getCurrentUser()->user_type;

		$tpl->assign('user_type', $user_type);

		$tpl->assign('permission', $this->permission);

		// Delete
		if (!empty($_POST['action']))
		{
			// delete owner
			if ($this->permission['can_remove_owner'] && $_POST['action'] == 'deleteOwner' && !empty($_POST['owner_id']) && is_numeric($_POST['owner_id']))
			{
				// remove from user to franchise
				$utf = DAO_CFactory::create('user_to_franchise');
				$utf->franchise_id = $id;
				$utf->user_id = $_POST['owner_id'];
				$utf->find();

				while ($utf->fetch())
				{
					$utf->delete();
					// This delete call will also delete the user_to_store rows for this franchise only
					// the user_type will be set to CUSTOMER only if all user_to_store rows are gone.
				}

				$tpl->setToastMsg('The owner has been removed.');
			}

			if ($_POST['action'] == 'deleteFranchise')
			{
				$franchise = DAO_CFactory::create('franchise');
				$franchise->id = $id;
				$franchise->delete();

				$tpl->setStatusMsg('The franchise has been deleted');
				CApp::bounce('main.php?page=admin_list_franchisees');
			}

			if ($_POST['action'] == 'deleteStore' && isset($_POST['store_id']) && $_POST['store_id'])
			{
				$store = DAO_CFactory::create('store');
				$store->id = CGPC::do_clean($_REQUEST['store_id'], TYPE_INT);
				$store->delete();

				$tpl->setStatusMsg('The store has been deleted');
				//jump to same page without deleteStore action
				CApp::bounce('main.php?page=admin_franchise_details&id=' . $id . '&back=' . $_REQUEST['back']);
			}
		}

		// Update
		if (!empty($_REQUEST['updateFranchise']))
		{
			$franchise = DAO_CFactory::create('franchise');
			$franchise->id = $id;
			$franchise->find(true);

			$franchiseUpdated = clone($franchise);
			$franchiseUpdated->franchise_name = CGPC::do_clean($_REQUEST['franchise_name'], TYPE_STR);
			$franchiseUpdated->franchise_description = CGPC::do_clean($_REQUEST['franchise_description'], TYPE_STR);
			$franchiseUpdated->active = CGPC::do_clean($_REQUEST['active'], TYPE_INT);
			$franchiseUpdated->update($franchise);

			$tpl->assign('franchise', $franchiseUpdated->toArray());

			$tpl->setStatusMsg('The franchise properties have been updated');
		}

		// Add owner
		if ($this->permission['can_add_owner'] && !empty($_REQUEST['addOwner']))
		{
			if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']))
			{
				//check that the user and make them owner user_type
				$newOwner = DAO_CFactory::create('user');
				$newOwner->id = $_POST['user_id'];

				if (!$newOwner->find(true))
				{
					$tpl->setErrorMsg('Could not locate a customer with an ID of ' . $_POST['user_id']);
				}
				else
				{
					if ($newOwner->user_type != CUser::SITE_ADMIN && $newOwner->user_type != CUser::HOME_OFFICE_MANAGER && $newOwner->user_type != CUser::HOME_OFFICE_STAFF)
					{
						// set new user_type
						$newOwner->update(clone($newOwner), CUser::FRANCHISE_OWNER);
					}

					// add owner to user_to_franchise
					$insertOwner = DAO_CFactory::create('user_to_franchise');
					$insertOwner->franchise_id = $id;
					$insertOwner->user_id = $_POST['user_id'];

					if (!$insertOwner->find())
					{
						$insertOwner->insert();
					}

					// add owner to user_to_store for each store in franchise
					$store = DAO_CFactory::create('store');
					$store->franchise_id = $id;
					$store->find();

					while ($store->fetch())
					{
						$insertOwner = DAO_CFactory::create('user_to_store');
						$insertOwner->store_id = $store->id;
						$insertOwner->user_id = $_POST['user_id'];
						if (!$insertOwner->find())
						{
							$insertOwner->insert();
						}
					}
				}
			}
			else
			{
				$tpl->setErrorMsg('Please enter a valid Owner ID.');
			}
		}

		//get stores and personell
		$store = DAO_CFactory::create('store');
		$store->franchise_id = $id;
		$store->orderBy('active DESC, state_id ASC');
		$store->find();

		$storesArray = array();
		while ($store->fetch())
		{
			$storesArray[$store->id] = $store->toArray();
			$storesArray[$store->id]['personnel'] = CStore::getStorePersonnel($store->id);
		}

		$sortClause = "ORDER BY u.lastname ASC, u.firstname ASC";
		// HACK ALERT:  detect Redlands store and reverse sorting
		if (array_key_exists(229, $storesArray))
		{
			$sortClause = "ORDER BY u.lastname DESC, u.firstname DESC";
		}

		$tpl->assign('stores', $storesArray);

		//get owners
		$owner = DAO_CFactory::create('user_to_franchise');
		$owner->query("SELECT
			utf.user_id,
			utf.owner_description,
			u.firstname,
			u.lastname,
			u.primary_email,
			u.telephone_1,
			u.telephone_2,
			a.address_line1,
			a.address_line2,
			a.city,
			a.state_id,
		    a.postal_code,
			a.country_id
			FROM `user_to_franchise` AS utf
			INNER JOIN `user` AS u ON u.id = utf.user_id AND u.is_deleted = '0'
			LEFT JOIN address AS a ON a.user_id = u.id AND a.is_deleted = '0' and a.location_type = 'BILLING'
			WHERE utf.franchise_id = '" . $id . "' AND utf.is_deleted = '0'
			GROUP BY utf.id
			$sortClause");

		while($owner->fetch())
		{
			$ownerArray[$owner->user_id] = $owner->toArray();
		}

		$tpl->assign('owners', $ownerArray);
	}
}
?>