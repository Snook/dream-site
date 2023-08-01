<?php
require_once 'DAO/Franchise.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CFranchise
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CFranchise extends DAO_Franchise
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Deletes the franchise's stores and owners as well
	 */
	function delete($useWhere = false, $forceDelete = false)
	{
		//find stores
		$store = DAO_CFactory::create('store');
		$store->franchise_id = $this->id;
		$store->find();

		while ($store->fetch())
		{
			$store->delete();
		}

		//find owners
		$owner = DAO_CFactory::create('user_to_franchise');
		$owner->franchise_id = $this->id;
		$owner->find();

		while ($owner->fetch())
		{
			$owner->delete($useWhere);
		}

		return parent::delete($useWhere, $forceDelete);
	}

	function exists()
	{
	 	$Franchise = DAO_CFactory::create('franchise');
	 	$Franchise->franchise_name = $this->franchise_name;

	 	if ( $Franchise->find(true))
	 	{
	 		return true;
	 	}

	 	return false;
	}
}
?>