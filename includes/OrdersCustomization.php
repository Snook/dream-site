<?php

class OrdersCustomization
{
	private static $instance = null;

	private static $order = null;

	const SPECIAL_REQUEST_MAX_CHARS = 90;

	const RECIPE_DESCRIPTION = "Don't like the taste of some ingredients? Let us know and we won't add it when we make your meals. Substitution is not an option. Customization is charged based on the number of meals you order. Additional details below*.";

	const RECIPE_LEGAL = "Dream Dinners is not an allergen-free facility. If you have food allergies, please consider a store assembly date/time where you customize your own meals. Customization of meals will not update the meal nutritional information. The Dream Dinners Team makes all customization orders outside the normal process to adjust for these special requests, therefore, customization fees are charged on the total number of meals ordered. Sides and Sweets items including those that are paired with meals and Pre-assembled Dinners cannot be customized. Contact your local store with additional questions.";
	// The constructor is private
	// to prevent initiation with outer code.
	private function __construct($orderObj)
	{
		self::$order = $orderObj;
	}

	// The object is created from within the class itself
	// only if the class has no instance.
	/**
	 * @param $orderObj
	 *
	 * @return OrdersCustomization|null
	 */
	public static function getInstance($orderObj)
	{
		if (self::$instance == null || self::$order == null || self::$order->id != $orderObj->id)
		{
			self::$instance = new OrdersCustomization($orderObj);
		}

		return self::$instance;
	}

	/**
	 * @return mixed|OrderCustomizationObj
	 */
	public function orderCustomizationToObj()
	{
		$customizations = self::$order->order_customization;
		$oco = new OrderCustomizationObj();
		if (!empty($customizations))
		{
			return self::initOrderCustomizationObj($customizations);
		}

		return $oco;
	}

	/**
	 * @return false|string json versoin of the cusomizations
	 */
	public function orderCustomizationToJson()
	{
		$customizations = $this->orderCustomizationToObj();

		return json_encode($customizations);
	}

	/**
	 * @return false|string
	 */
	public function mealCustomizationToJson()
	{
		$customizations = $this->mealCustomizationToObj();

		return json_encode($customizations);
	}

	/**
	 * @param $userObj      will use the users defaults if not already available form the order,
	 *                      if not user then it will just return empty defaulst
	 *
	 * @return MealCustomizationObj
	 */
	public function mealCustomizationToObj($userObj = null)
	{
		$customizations = self::$order->order_customization;

		if (!empty($customizations))
		{
			$orderCustomizations = $this->initOrderCustomizationObj($customizations);

			return $orderCustomizations->meal;
		}
		if (!is_null($userObj))
		{
			return $userObj->getMealCustomizationPreferences();
		}

		return new MealCustomizationObj();
	}

	/**
	 * @return false|string
	 */
	public function mealCustomizationToString()
	{
		$customizations = $this->mealCustomizationToObj();

		return $customizations->toString('');
	}

	public function mealCustomizationToStringSelectedOnly($separator, $newLine = '<br>', $in_parans = true)
	{
		$customizations = $this->mealCustomizationToObj();

		return $customizations->toString($separator, true, $newLine, $in_parans);
	}

	/**
	 * @param      $updatedCustomizationsObj
	 * @param bool $doPersist
	 *
	 * @return false|null
	 */
	public function updateMealCustomization($updatedCustomizationsObj, $doPersist = true)
	{
		if (empty($updatedCustomizationsObj))
		{
			return false;
		}

		$existingCustomizations = self::mealCustomizationToObj();

		//for each property that exist in update set into existing
		$mealOpts = $updatedCustomizationsObj;
		foreach ($mealOpts as $key => $value)
		{
			$existingCustomizations->{$key} = $value;

			if (!empty($mealOpts->{$key}->details))
			{
				$existingCustomizations->{$key}->details = $value->details;
			}
		}

		//save order customization
		$this->setMealCustomization($existingCustomizations);

		if (!empty(self::$order->id) && $doPersist)
		{//Order may not have been created yet
			$orderObj = DAO_CFactory::create('orders');
			$orderObj->query("update orders set order_customization = '" . self::$order->order_customization . "' 
							where id = " . self::$order->id);

			$editOrderObj = DAO_CFactory::create('edited_orders');
			$orderObj->query("update edited_orders set order_customization = '" . self::$order->order_customization . "' 
							where original_order_id = " . self::$order->id);
		}

		return self::$order;
	}

	//Mainly for BACKOFFICE new orders to capture the users defaults onto the new order
	//This is handled through the cart on the front end
	public function persistOrderCustomizationOnNewOrder()
	{
		if (!empty(self::$order->id))
		{//Order may not have been created yet
			$orderObj = DAO_CFactory::create('orders');
			$orderObj->query("update orders set order_customization = '" . self::$order->order_customization . "' 
							where id = " . self::$order->id);

			$editOrderObj = DAO_CFactory::create('edited_orders');
			$orderObj->query("update edited_orders set order_customization = '" . self::$order->order_customization . "' 
							where original_order_id = " . self::$order->id);
		}
	}

	/**
	 * @param $mealCustomizationObj
	 */
	public function setMealCustomization($mealCustomizationObj)
	{
		$customizations = $this->orderCustomizationToObj();
		$customizations->meal = $mealCustomizationObj;

		self::$order->order_customization = json_encode($customizations);;
	}

	/**
	 * @return bool
	 */
	public function hasMealCustomizationPreferencesSet()
	{
		$existingCustomizations = self::mealCustomizationToObj();

		//for each property that exist in update set into existing
		foreach ($existingCustomizations as $key => $value)
		{
			if (!empty($value))
			{
				$subvalue = $value->value;
				if (!empty($subvalue) && $subvalue != CUser::UNANSWERED && $subvalue != CUser::OPTED_OUT)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param $mealCustomizationObj
	 *
	 * @return bool true if any are set7
	 */
	public static function determineIfMealCustomizationPreferencesSetOn($existingCustomizations)
	{

		//for each property that exist in update set into existing
		foreach ($existingCustomizations as $key => $value)
		{
			if (!empty($value))
			{
				$subvalue = $value->value;
				if (!empty($subvalue) && $subvalue != CUser::UNANSWERED && $subvalue != CUser::OPTED_OUT)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function unsetAllMealCustomizations()
	{
		$existingCustomizations = self::orderCustomizationToObj();

		//for each property that exist in update set into existing
		foreach ($existingCustomizations->meal as $key => $data)
		{
			if (!empty($data))
			{
				switch ($data->type)
				{
					case 'INPUT' :
						$data->value = '';
						break;
					case 'RADIO' :
					case 'CHECKBOX' :
						$data->value = CUser::OPTED_OUT;
						break;
					default:
						$data->value = null;
				}
			}
		}

		self::$order->order_customization = json_encode($existingCustomizations);
	}

	/**
	 *
	 * Gets the store Customization setting Obj wich reflects the store's
	 * settings at the time the order was place
	 *
	 *
	 * @return StoreSettingsObj
	 */
	public function storeCustomizationSettingsToObj()
	{
		$customizations = self::$order->order_customization;

		if (!empty($customizations))
		{
			$orderCustomizations = $this->initOrderCustomizationObj($customizations);

			return $orderCustomizations->storeSettings;
		}

		return new StoreSettingsObj();;
	}

	/**
	 * @param StoreSettingsObj
	 */
	public function setStoreCustomizationSetting($storeSettingsObj)
	{
		$customizations = $this->orderCustomizationToObj();
		$customizations->storeSettings = $storeSettingsObj;

		self::$order->order_customization = json_encode($customizations);;
	}

	public function setStoreAllowsMealCustomization($val)
	{
		$existingCustomizations = self::orderCustomizationToObj();
		$storeSettings = $existingCustomizations->getStoreSettingsObj();
		$storeSettings->setAllowsMealCustomization($val);
		$existingCustomizations->setStoreCustomizationSetting($storeSettings);

		self::$order->order_customization = json_encode($existingCustomizations);
	}

	public function setStoreAllowsPreassembledCustomization($val)
	{
		$existingCustomizations = self::orderCustomizationToObj();
		$storeSettings = $existingCustomizations->getStoreSettingsObj();
		$storeSettings->setAllowsPreAssembledCustomization($val);
		$existingCustomizations->setStoreCustomizationSetting($storeSettings);

		self::$order->order_customization = json_encode($existingCustomizations);
	}

	/**
	 *
	 * Create from json
	 *
	 * @param $orderCustomizations json usual from order
	 *
	 * @return OrderCustomizationObj
	 */
	public static function initOrderCustomizationObj($orderCustomizations)
	{
		$result = new OrderCustomizationObj();
		$templateMealCustomization = new MealCustomizationObj();
		$templateStoreSettings = new StoreSettingsObj();
		if (!is_null($orderCustomizations))
		{
			$stdObt = json_decode($orderCustomizations);
			if (!is_null($stdObt->meal))
			{
				foreach ($stdObt->meal as $key => $value)
				{
					$templateMealCustomization->{$key}->value = $value->value;

					if (property_exists($value, 'details'))
					{
						$templateMealCustomization->{$key}->details = $value->details;
					}
				}
			}
			if (!is_null($stdObt->storeSettings))
			{
				foreach ($stdObt->storeSettings as $key => $value)
				{
					$templateStoreSettings->{$key} = $value;
				}
			}
		}

		$result->meal = $templateMealCustomization;
		$result->storeSettings = $templateStoreSettings;

		return $result;
	}

	/**
	 *
	 * Create from json
	 *
	 * @param $mealCustomizationsObj MealCustomizationObj
	 *
	 * @return OrderCustomizationObj
	 */
	public static function initOrderCustomizationObjFromMealCustomizationObj($mealCustomizationsObj)
	{
		$result = new OrderCustomizationObj();
		$result->meal = $mealCustomizationsObj;

		return $result;
	}

	/**
	 *
	 * * Create from array of user preferences
	 *
	 *
	 * @param $arrayUserPreference
	 *
	 * @return OrderCustomizationObj
	 */
	public static function createOrderCustomizationObj($arrayUserPreference)
	{
		$result = new OrderCustomizationObj();
		$result->meal = new MealCustomizationObj();
		foreach ($arrayUserPreference as $key => $pref)
		{
			if (substr($key, 0, 13) === "MEAL_EXCLUDE_")
			{
				$result->meal->{$key}->value = $pref['value'];

				$detailsKey = self::determineDetailsKey($key);
				if (array_key_exists($detailsKey, $arrayUserPreference))
				{
					$result->meal->{$key}->details = $arrayUserPreference[$detailsKey]['value'];
				}
			}
		}

		return $result;
	}

	public static function determineDetailsKey($key)
	{
		$detailsKey = '';
		if (substr($key, 0, 13) === "MEAL_EXCLUDE_")
		{
			$detailsPrefName = substr($key, 13);
			$detailsKey = "MEAL_" . $detailsPrefName . "_DETAILS";
		}

		return $detailsKey;
	}

	public static function createDefaultMealCustomizationObj()
	{

		return new MealCustomizationObj();
	}
}

class OrderCustomizationObj
{
	public $meal = null;
	public $storeSettings = null;

	public function __construct()
	{
		$this->meal = new MealCustomizationObj();
		$this->storeSettings = new StoreSettingsObj();
	}

	public function getMealCustomizationObj()
	{
		return $this->meal;
	}

	public function getStoreSettingsObj()
	{
		return $this->storeSettings;
	}

	public function setStoreCustomizationSetting($storeSettingsObj)
	{
		$this->storeSettings = $storeSettingsObj;
	}

}

/**
 * This represents the store setting for customization
 * at the time of the order so that if the settings change
 * they will not impact previous orders
 *
 */
class StoreSettingsObj
{
	public $allowsPreAssembledCustomization = null;
	public $allowsMealCustomization = null;

	public function __construct()
	{

	}

	/**
	 * Allow default values to match current values set at store for
	 *
	 * supports_meal_customization
	 * allow_preassembled_customization
	 *
	 *
	 * @param $storeObj
	 */
	public function initFromCurrentStoreCustomizationSettings($storeObj)
	{
		//set default from store
		if (!is_null($storeObj))
		{
			$this->allowsPreAssembledCustomization = $storeObj->allow_preassembled_customization;
			$this->allowsMealCustomization = $storeObj->supports_meal_customization;
		}
	}

	/**
	 * Allow default values to match current values set at store for, if nothing has been set
	 *
	 * supports_meal_customization
	 * allow_preassembled_customization
	 *
	 *
	 * @param $storeObj
	 */
	public function initFromCurrentStoreCustomizationSettingsIfNull($storeObj)
	{
		//set default from store
		if (!is_null($storeObj) && is_null($this->allowsMealCustomization))
		{
			$this->allowsMealCustomization = $storeObj->supports_meal_customization;
		}

		if (!is_null($storeObj) && is_null($this->allowsPreAssembledCustomization))
		{
			$this->allowsPreAssembledCustomization = $storeObj->allow_preassembled_customization;
		}
	}

	/**
	 * @return null|boolean
	 */
	public function allowsPreAssembledCustomization()
	{
		if ($this->allowsPreAssembledCustomization || $this->allowsPreAssembledCustomization === "1")
		{
			return true;
		}

		if (!$this->allowsPreAssembledCustomization || $this->allowsPreAssembledCustomization === "0")
		{
			return false;
		}

		return $this->allowsPreAssembledCustomization;
	}

	/**
	 * @param null $allowsPreAssembledCustomization
	 */
	public function setAllowsPreAssembledCustomization($allowsPreAssembledCustomization)
	{
		$this->allowsPreAssembledCustomization = $allowsPreAssembledCustomization;
	}

	/**
	 * @return null|boolean
	 */
	public function allowsMealCustomization()
	{
		if ($this->allowsMealCustomization || $this->allowsMealCustomization === "1")
		{
			return true;
		}

		if (!$this->allowsMealCustomization || $this->allowsMealCustomization === "0")
		{
			return false;
		}

		return $this->allowsMealCustomization;
	}

	/**
	 * @param null $allowsMealCustomization
	 */
	public function setAllowsMealCustomization($allowsMealCustomization)
	{
		$this->allowsMealCustomization = $allowsMealCustomization;
	}

}

/**
 * Use a defined object instead of an array to define what meal customization
 * preferences are available
 *
 * This should match the MEAL_CUSTOMIZATION* constants in CUser so that
 * the user preferences can be mapped to this object
 */
class MealCustomizationObj
{

	public function __construct()
	{

		$this->MEAL_EXCLUDE_RAW_ONION = new stdClass();
		$this->MEAL_EXCLUDE_RAW_ONION->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_RAW_ONION->description = 'No Added Raw Onion';
		$this->MEAL_EXCLUDE_RAW_ONION->short = 'Raw Onion';
		$this->MEAL_EXCLUDE_RAW_ONION->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_RAW_ONION->information = 'Includes: white and red onion, green onion, pearl onions';

		$this->MEAL_EXCLUDE_ONION_SPICES = new stdClass();
		$this->MEAL_EXCLUDE_ONION_SPICES->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_ONION_SPICES->description = 'No Added Onion Spices';
		$this->MEAL_EXCLUDE_ONION_SPICES->short = 'Onion Spices';
		$this->MEAL_EXCLUDE_ONION_SPICES->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_ONION_SPICES->information = 'Includes: granulated onion powder, dried onion flakes';

		$this->MEAL_EXCLUDE_RAW_GARLIC = new stdClass();
		$this->MEAL_EXCLUDE_RAW_GARLIC->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_RAW_GARLIC->description = 'No Added Raw Garlic';
		$this->MEAL_EXCLUDE_RAW_GARLIC->short = 'Raw Garlic';
		$this->MEAL_EXCLUDE_RAW_GARLIC->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_RAW_GARLIC->information = 'Includes: chopped or whole garlic cloves';

		$this->MEAL_EXCLUDE_GARLIC_SPICES = new stdClass();
		$this->MEAL_EXCLUDE_GARLIC_SPICES->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_GARLIC_SPICES->description = 'No Added Garlic Spices';
		$this->MEAL_EXCLUDE_GARLIC_SPICES->short = 'Garlic Spices';
		$this->MEAL_EXCLUDE_GARLIC_SPICES->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_GARLIC_SPICES->information = 'Includes: garlic salt, garlic powder, granulated garlic';

		$this->MEAL_EXCLUDE_MUSHROOMS = new stdClass();
		$this->MEAL_EXCLUDE_MUSHROOMS->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_MUSHROOMS->description = 'No Added Mushrooms';
		$this->MEAL_EXCLUDE_MUSHROOMS->short = 'Mushrooms';
		$this->MEAL_EXCLUDE_MUSHROOMS->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_MUSHROOMS->information = null;

		$this->MEAL_EXCLUDE_OLIVES = new stdClass();
		$this->MEAL_EXCLUDE_OLIVES->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_OLIVES->description = 'No Added Olives';
		$this->MEAL_EXCLUDE_OLIVES->short = 'Olives';
		$this->MEAL_EXCLUDE_OLIVES->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_OLIVES->information = null;

		$this->MEAL_EXCLUDE_BACON = new stdClass();
		$this->MEAL_EXCLUDE_BACON->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_BACON->description = 'No Added Bacon';
		$this->MEAL_EXCLUDE_BACON->short = 'Bacon';
		$this->MEAL_EXCLUDE_BACON->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_BACON->information = null;

		$this->MEAL_EXCLUDE_CILANTRO = new stdClass();
		$this->MEAL_EXCLUDE_CILANTRO->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_CILANTRO->description = 'No Added Cilantro';
		$this->MEAL_EXCLUDE_CILANTRO->short = 'Cilantro';
		$this->MEAL_EXCLUDE_CILANTRO->type = 'CHECKBOX';
		$this->MEAL_EXCLUDE_CILANTRO->information = null;

		$this->MEAL_EXCLUDE_SPECIAL_REQUEST = new stdClass();
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->value = CUser::UNANSWERED;
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->description = 'Special Request';
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->short = 'Special Request';
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->type = 'SPECIAL_REQUEST';
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->information = 'Please contact your store if you would like to adjust you special request.';
		$this->MEAL_EXCLUDE_SPECIAL_REQUEST->details = '';

		//		$this->MEAL_EXCLUDE_CUSTOM = new stdClass();
		//		$this->MEAL_EXCLUDE_CUSTOM->value = CUser::UNANSWERED;
		//		$this->MEAL_EXCLUDE_CUSTOM->description = 'Custom';
		//		$this->MEAL_EXCLUDE_CUSTOM->type = 'INPUT';
	}

	public function toString($separator = ', ', $selectedOnly = false, $newLine = '<br>', $in_parans = true, $prefix = 'No added: ')
	{
		$result = '';
		$details = '';
		foreach ($this as $key => $value)
		{

			if ($selectedOnly && $value->value == 'OPTED_IN')
			{
				if (!empty($value->details))
				{
					$details .= $newLine . $value->short . ': ' . $value->details;
				}
				else
				{
					$result .= $value->short . $separator;
				}
			}
			else if (!$selectedOnly)
			{
				if (property_exists($value, 'details'))
				{
					if (!empty($value->details))
					{
						$details .= $newLine . $value->short . ': ' . $value->details;
					}
				}
				else
				{
					$result .= $value->short . $separator;
				}
			}
		}

		$optionsStr = rtrim($result, $separator);
		if ($optionsStr != '')
		{
			$optionsStr = $prefix . $optionsStr;
		}
		else
		{
			;
			$pos = strpos($details, $newLine);

			if ($pos !== false)
			{
				$details = substr_replace($details, '', $pos, strlen($newLine));
			}
		}

		$out = trim($optionsStr . $details);

		if ($out != '' && $in_parans)
		{
			$out = '(' . $out . ')';
		}

		return $out;
	}

	//DEFAULTS DEFINED HERE
	public $MEAL_EXCLUDE_RAW_ONION = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_ONION_SPICES = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_RAW_GARLIC = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_GARLIC_SPICES = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_MUSHROOMS = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_OLIVES = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_BACON = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_CILANTRO = CUser::UNANSWERED;
	public $MEAL_EXCLUDE_SPECIAL_REQUEST = CUser::UNANSWERED;
	//public $MEAL_EXCLUDE_CUSTOM = CUser::UNANSWERED;

}