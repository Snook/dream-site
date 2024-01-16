<?php

/* ------------------------------------------------------------------------------------------------
 *	Class: DAO_CFactory
 *
 *	Data:
 *
 *	Description:
 *		Todd Wallar
 *		A basic factory for DAO classes and subclasses
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class DAO_CFactory
{

	private function __construct()
	{
	}

	//This is a list of dataobjects that have subclasses

	static $subclasses = array(
		'CAddress' => 'DAO_Address',
		'CBooking' => 'DAO_Booking',
		'CBox' => 'DAO_Box',
		'CBoxInstance' => 'DAO_Box_instance',
		'CBrowserSession' => 'DAO_Browser_sessions',
		'CBundle' => 'DAO_Bundle',
		'CCorporateCrateClient' => 'DAO_Corporate_crate_client',
		'CCouponCode' => 'DAO_Coupon_code',
		'CCouponCodeProgram' => 'DAO_Coupon_code_program',
		'CCustomerReferral' => 'DAO_Customer_referral',
		'CDreamRewardsHistory' => 'DAO_Dream_rewards_history',
		'CDreamTasteEvent' => 'DAO_Dream_taste_event_properties',
		'CEnrollmentPackage' => 'DAO_Enrollment_package',
		'CFoodSurvey' => 'DAO_Food_survey',
		'CFoodTesting' => 'DAO_Food_testing',
		'CFranchise' => 'DAO_Franchise',
		'CFundraiser' => 'DAO_Fundraiser',
		'CGiftCard' => 'DAO_Gift_card_transaction',
		'CMarkUp' => 'DAO_Mark_up',
		'CMarkUpMulti' => 'DAO_Mark_up_multi',
		'CMembershipHistory' => 'DAO_Membership_history',
		'CMenu' => 'DAO_Menu',
		'CMenuItem' => 'DAO_Menu_item',
		'CMenuItemInventory' => 'DAO_Menu_item_inventory',
		'CMenuItemInventoryHistory' => 'DAO_Menu_item_inventory_history',
		'CMenuItemNutrition' => 'DAO_Menu_item_nutrition',
		'CMenuToMenuItem' => 'DAO_Menu_to_menu_item',
		'COrderMinimum' => 'DAO_Order_minimum',
		'COrders' => 'DAO_Orders',
		'COrdersDigest' => 'DAO_Orders_digest',
		'CPayment' => 'DAO_Payment',
		'CPointsCredits' => 'DAO_Points_credits',
		'CPointsUserHistory' => 'DAO_Points_user_history',
		'CPremium' => 'DAO_Premium',
		'CProduct' => 'DAO_Product',
		'CProductOrders' => 'DAO_Product_orders',
		'CProductPayment' => 'DAO_Product_payment',
		'CRecipe' => 'DAO_Recipe',
		'CSalesTax' => 'DAO_Sales_tax',
		'CSession' => 'DAO_Session',
		'CShortUrl' => 'DAO_Short_url',
		'CStatesAndProvinces' => 'DAO_State_province',
		'CStore' => 'DAO_Store',
		'CStoreCredit' => 'DAO_Store_credit',
		'CStoreExpenses' => 'DAO_Store_expenses',
		'CStorePickUpLocation' => 'DAO_Store_pickup_location',
		'CUser' => 'DAO_User',
		'CUserData' => 'DAO_User_data',
		'CUserPreferred' => 'DAO_User_preferred',
		'CUserReferralSource' => 'DAO_User_referral_source',
		'CUserToFranchise' => 'DAO_User_to_franchise'
	);

	/**
	 * $objName is of the format: 'tablename' or 'DAO_Tablename'
	 */
	static function create($objName, $dataSelectTable = false)
	{

		//add the prefix if it aint there
		$filename = str_replace('DAO_', '', $objName);
		$filename = ucfirst($filename);
		$objName = 'DAO_' . $filename;

		//check for a subclass
		$subclass = array_search($objName, self::$subclasses);

		if ($subclass !== false)
		{
			$objName = $subclass;
			$filename = $objName;
		}

		if ($subclass === false)
		{
			require_once('DAO/' . $filename . '.php');
		}
		else
		{
			require_once('DAO/BusinessObject/' . $filename . '.php');
		}

		$rtn = new $objName;

		if (!$rtn)
		{
			throw new Exception("class not found in factory: $objName");
		}

		if ($dataSelectTable)
		{
			$tableName = $rtn->tableName();
			if ($rtn->_query['data_select'] === '*' && !empty($tableName))
			{
				$rtn->selectAdd();
				$rtn->selectAdd($tableName . '.*');
			}
		}

		return $rtn;
	}

}

?>