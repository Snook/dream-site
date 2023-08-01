<?php
require_once 'DAO/Store_fee.php';


class CStoreFee extends DAO_Store_fee
{

	//Fee Type
	const DEFAULT_MEAL_CUSTOMIZATION = 'DEFAULT_MEAL_CUSTOMIZATION';
	const MEAL_CUSTOMIZATION = 'MEAL_CUSTOMIZATION';

	function __construct()
	{
		parent::__construct();
	}


	private static function wrapFees($storeFeeObj){
		$fees = array();
		$storeFeeObj->find();
		while($storeFeeObj->fetch())
		{
			$fees[$storeFeeObj->id] = $storeFeeObj->toArray();
		}

		return $fees;
	}

	public static function defaultCustomizationFees(){
		$storeFeeObj = DAO_CFactory::create('store_fee');
		$storeFeeObj->type = CStoreFee::DEFAULT_MEAL_CUSTOMIZATION;

		return self::wrapFees($storeFeeObj);
	}

	static function fetchCustomizationFees($store){
		$store_id = null;
		if (is_object($store))
		{
			$store_id = $store->id;
		}

		if (is_numeric($store))
		{
			$store_id = $store;
		}

		if(!is_null($store_id)){
			$storeFeeObj = DAO_CFactory::create('store_fee');
			$storeFeeObj->store_id = $store_id;
			$storeFeeObj->type = CStoreFee::MEAL_CUSTOMIZATION;
			$storeFeeObj->find();

			if($storeFeeObj->N == 0){
				return CStoreFee::defaultCustomizationFees();
			}

			return self::wrapFees($storeFeeObj);

		}else{
			return CStoreFee::defaultCustomizationFees();
		}
	}

}

?>