<?php
require_once 'DAO/Pricing.php';
require_once 'CLog.inc';
require_once 'includes/CResultToken.inc';

/* ------------------------------------------------------------------------------------------------
*	Class: CPricing
*
*	Data:
*
*	Methods:
*
* Properties:
*
*	Dynamic Properties:
*
*
*	Description:
*		Placeholder for DB storing user object. Including this file
*		creates a global $Pricing object.
*
*	Requires:
*
* -------------------------------------------------------------------------------------------------- */


class CPricing extends DAO_Pricing
{

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param       $menu_id required month to get reference pricing for
	 * @param false $includeDetails joins to menu_item and menu_to_menu_item to get details about each price
	 * @param false $doMenuOrderBy result will be in core menu sort order, this forces includeDetails to be true
	 *
	 * @return array
	 * @throws Exception
	 */
	static function buildReferencePricingArray($menu_id, $includeDetails = false, $doMenuOrderBy = false)
	{
		$daoPricing = DAO_CFactory::create('pricing');

		if($doMenuOrderBy)
		{
			$includeDetails = true;
		}

		$addDetailsSqlJoin = "";
		$addSelectSqlJoin = "";
		$orderBy = "";

		if( $includeDetails )
		{
			$addSelectSqlJoin = ", mi.menu_item_name, mi.id as menu_item_id, mi.menu_item_category_id, mmi.menu_order_value  ";
			$addDetailsSqlJoin = " join menu_item mi on mi.recipe_id = p.recipe_id and mi.pricing_type = p.pricing_type and mi.is_deleted = 0
			join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and mmi.menu_id = $menu_id and mmi.store_id is null ";
			$orderBy = " order by
				CASE 
					WHEN mi.menu_item_category_id = 1 OR (mi.menu_item_category_id = 4 AND mi.is_store_special = 0) THEN 1
					WHEN mi.menu_item_category_id = 4 AND mi.is_store_special = 1 THEN 2
					WHEN mi.menu_item_category_id = 9 THEN 3
				END ASC,
				mmi.featuredItem DESC,
				CASE WHEN mi.menu_item_category_id = 1 OR (mi.menu_item_category_id = 4 AND mi.is_store_special = 0) THEN mmi.menu_order_value END ASC,
				CASE WHEN mi.menu_item_category_id = 4 AND mi.is_store_special = 1 THEN mi.menu_item_name END ASC,
				CASE mi.pricing_type
					WHEN 'TWO' THEN 1
					WHEN 'HALF' THEN 2
					WHEN 'FOUR' THEN 3
					WHEN 'FULL' THEN 4
				END ASC ";
		}

		$daoPricing->query(
			"select p.menu_id, p.recipe_id, p.tier, p.pricing_type,p.price $addSelectSqlJoin
			from pricing p
			$addDetailsSqlJoin
			where p.is_deleted = 0
			and p.menu_id = $menu_id
			$orderBy
			"
		);


		$menuPricingInfo = array();

		$tempRecipeIdArray = array();
		$prevRecipeId = null;

		if($doMenuOrderBy)
		{
			while ($daoPricing->fetch())
			{
				if(is_null($prevRecipeId) || $prevRecipeId == $daoPricing->recipe_id){
					$tempRecipeIdArray[] = $daoPricing->toArray();
				}

				if(!is_null($prevRecipeId) && $prevRecipeId != $daoPricing->recipe_id)
				{
					usort($tempRecipeIdArray, 'sort_by_tier_and_pricing');

					$menuPricingInfo = array_merge($menuPricingInfo, $tempRecipeIdArray);
					$tempRecipeIdArray = array();
					$tempRecipeIdArray[] = $daoPricing->toArray();
				}
				$prevRecipeId = $daoPricing->recipe_id;
			}

			if(count($tempRecipeIdArray) > 0)//catch the last on
			{
				usort($tempRecipeIdArray, 'sort_by_tier_and_pricing');
				$menuPricingInfo = array_merge($menuPricingInfo, $tempRecipeIdArray);
			}
		}
		else{
			while ($daoPricing->fetch())
			{
				$menuPricingInfo[] = $daoPricing->toArray();
			}
		}

		return $menuPricingInfo;
	}
}

function sort_by_tier_and_pricing($a,$b){
	$aTier = intval($a['tier']);
	$bTier = intval($b['tier']);
	if ($aTier == $bTier ) {
		if($a['pricing_type'] == 'FULL')
		{
			return 1;
		}
		if($b['pricing_type'] == 'FULL')
		{
			return -1;
		}
	}
	return ($aTier < $bTier ) ? -1 : 1;
}
?>