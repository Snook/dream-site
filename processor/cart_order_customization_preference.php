<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");

class processor_cart_order_customization_preference extends CPageProcessor
{
	function runPublic()
	{
		header('Content-type: application/json');

		if(isset($_POST['op']))
		{
			if($_POST['op'] == 'set_customization_applies_to_order')
			{
				$defaultCustomizations = null;
				if(!empty($_POST['customizations'])){
					$defaultCustomizations = $_POST['customizations'];
				}
				$results_array = self::set_customization_applies_to_order($_POST['allow_customization'],$defaultCustomizations);

				echo json_encode($results_array);
				exit;
			}

			if($_POST['op'] == 'update_order_meal_customization')
			{

				$results_array = self::update_order_based_on_customization_preferences($_POST['customizations']);

				echo json_encode($results_array);
				exit;
			}

			echo json_encode(array('processor_success' => false, 'result_code' => 6002,  'processor_message' => 'Invalid operation.'));
			exit;
		}
		else
		{
			echo json_encode(array('processor_success' => false, 'result_code' => 6003,  'processor_message' => 'Invalid operation.'));
			exit;
		}
	}

	static function set_customization_applies_to_order($allow_customization, $defaultCustomizations = null)
	{
		$CartObj = CCart2::instance();
		$Order = $CartObj->getOrder();


		$Order->opted_to_customize_recipes = ($allow_customization == "false" ? 0 : 1);
		$CartObj->addMealCustomizationOptOut($Order->opted_to_customize_recipes , true);

		if($Order->opted_to_customize_recipes  && !is_null($defaultCustomizations)){
			self::update_order_based_on_customization_preferences($defaultCustomizations);
		}else{
			$Order->refresh(CUser::getCurrentUser());
			$Order->recalculate();
			$CartObj->addOrder($Order);

			// menu item was set to zero, removed item from cart
			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Set Customization opt in/out.',
				'orderInfo' => $Order->toArray(),
				'cost' => $Order->subtotal_meal_customization_fee,
				'opted_to_customize_recipes' => ($Order->opted_to_customize_recipes?true:false),
				'total_customized_meal_count' => $Order->total_customized_meal_count
			));

			return array('processor_success' => true, 'result_code' => 1,  'processor_message' => 'Cart was cleared');
		}


	}

	static function update_order_based_on_customization_preferences($mealCustomUpdates)
	{
		$CartObj = CCart2::instance();
		$Order = $CartObj->getOrder();


		$mealCustomUpdates = json_decode($mealCustomUpdates);
		$orderCustomizations = OrdersCustomization::getInstance($Order);
		$Order = $orderCustomizations->updateMealCustomization($mealCustomUpdates);

		$CartObj->addMealCustomizationOptionToOrder($Order->order_customization , true);

		$Order->opted_to_customize_recipes = $orderCustomizations->hasMealCustomizationPreferencesSet();

		//If prefs have never been saved to account the save these
		if($Order->opted_to_customize_recipes){
			$userObj = $Order->getUser();

			if(!is_null($userObj->id) && $userObj->needsToSetMealCustomizationPreferencesSet()){
				$userObj->setMealCustomizationPreference($mealCustomUpdates,true);
			}
		}

		$CartObj->addMealCustomizationOptOut($Order->opted_to_customize_recipes , true);

		$Order->refresh(CUser::getCurrentUser());
		$Order->recalculate();
		$CartObj->addOrder($Order);

		// menu item was set to zero, removed item from cart
		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'Update Order Customization Fee.',
			'orderInfo' => $Order->toArray(),
			'cost' => $Order->subtotal_meal_customization_fee,
			'opted_to_customize_recipes' => ($Order->opted_to_customize_recipes?true:false),
			'total_customized_meal_count' => $Order->total_customized_meal_count
		));

		return array('processor_success' => true, 'result_code' => 1,  'processor_message' => 'Cart was cleared');
	}
}
?>