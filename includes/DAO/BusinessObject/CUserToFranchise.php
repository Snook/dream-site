<?php
require_once 'DAO/User_to_franchise.php';

class CUserToFranchise extends DAO_User_to_franchise
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Deletes user from user_to_franchise and from user_to_store and returns them to Customer user_type if no longer an owner or staff at any store
	 */
	function delete($useWhere = false, $forceDelete = false)
	{
		$deleted = parent::delete($useWhere, $forceDelete);

		if (!empty($deleted))
		{
			//get franchise stores
			$store = DAO_CFactory::create('store');
			$store->franchise_id = $this->franchise_id;
			$store->find();

			while($store->fetch())
			{

				// remove from user to store
				$uts = DAO_CFactory::create('user_to_store');
				$uts->user_id = $this->user_id;
				$uts->store_id = $store->id;
				$uts->find();

				while($uts->fetch())
				{
					$uts->delete();
				}
			}

			// set user to customer if no longer member of any other stores or franchises
			$utf = DAO_CFactory::create('user_to_franchise');
			$utf->user_id = $this->user_id;

			$uts = DAO_CFactory::create('user_to_store');
			$uts->user_id = $this->user_id;

			if (!$utf->find() && !$uts->find())
			{
				$user = DAO_CFactory::create('user');
				$user->id = $this->user_id;
				$user->user_type = CUser::FRANCHISE_OWNER;
				$user->find(true);
				$userUpdated = clone($user);
				$userUpdated->fadmin_nda_agree = 0;
				$userUpdated->update($user, CUser::CUSTOMER);
			}
		}

		return $deleted;
	}
}
?>