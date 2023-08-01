<?php
require_once 'DAO/Product.php';

class CProduct extends DAO_Product
{
	const NON_FOOD_ITEM = 'NON_FOOD_ITEM';
	const ENROLLMENT = 'ENROLLMENT';
	const MEMBERSHIP = 'MEMBERSHIP';

	static function getProductMembership()
	{
		$membershipArray = array();

		$memberships = DAO_CFactory::create('product');
		$memberships->query("SELECT 
			p.*, 
			pm.term_months, 
			pm.discount_type, 
			pm.discount_var
			FROM product AS p 
			JOIN product_membership AS pm ON pm.product_id = p.id AND pm.is_deleted = 0
			WHERE p.item_type = 'MEMBERSHIP'
			AND p.is_deleted = 0");

		while ($memberships->fetch())
		{
			$membershipArray[$memberships->id] = clone $memberships;
		}

		return $membershipArray;
	}

}

?>
