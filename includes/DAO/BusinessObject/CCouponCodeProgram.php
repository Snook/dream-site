<?php

require_once 'DAO/Coupon_code_program.php';

class CCouponCodeProgram extends DAO_Coupon_code_program {


	static function isCodeAcceptedByStore($store_id, $couponCode)
	{
		$masterExclusion = DAO_CFactory::create('store_coupon_program_exclusion');
		$masterExclusion->query("select id from store_coupon_program_exclusion where store_id =" . $store_id .  " and coupon_program_id is null and is_deleted = 0");
		if ($masterExclusion->fetch())
			return false;

		$Programs = DAO_CFactory::create('coupon_code_program');
		$Programs->query("select ccp.id, cc.id, scpe.id as program_exclusion, scce.id as code_exclusion, cts.id as 'specific_to_this_store', cc.is_store_specific from coupon_code cc
				join coupon_code_program ccp on cc.program_id = ccp.id
				left join coupon_to_store cts on cts.coupon_code_id = cc.id and cts.store_id = " . $store_id . " AND cts.is_deleted = 0
				left join store_coupon_program_exclusion scpe on ccp.id = scpe.coupon_program_id and scpe.store_id = " . $store_id . " and scpe.is_deleted = 0
				left join store_coupon_code_exclusion scce on cc .id = scce.coupon_code_id and scce.store_id = " . $store_id . " and scce.is_deleted = 0
				where cc.coupon_code = '$couponCode' and ccp.is_deleted = 0 and cc.is_deleted = 0");

		if ($Programs->fetch())
		{
			if (!empty($Programs->program_exclusion) || !empty($Programs->code_exclusion) || ($Programs->is_store_specific && empty($Programs->specific_to_this_store)))
				return false;
		}

		return true;

	}
}
?>