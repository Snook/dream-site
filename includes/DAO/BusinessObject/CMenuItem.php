<?php
/**
 * subclass of DAO/Menu_item
 */
require_once 'DAO/Menu_item.php';
require_once 'DAO/BusinessObject/COrders.php';
require_once 'DAO/BusinessObject/CBundle.php';

class CMenuItem extends DAO_Menu_item
{
	//ToddW: why was QUICK_HEARTY commented out? That's what I'm basing the
	//prices on for the new combo plan demo
	const QUICK_HEARTY = 'QUICK_HEARTY'; // REMOVE THIS FOR THE NEW SYStem
	const FULL = 'FULL';
	const FOUR = 'FOUR';
	const HALF = 'HALF';
	const TWO = 'TWO';
	const INTRO = 'INTRO';
	const LEGACY = 'LEGACY'; // this is the standard price for old menu items in the system... It is not to be changed or used.

	const TWO_SERVING_SIZE = 2;
	const FOUR_SERVING_SIZE = 4;
	const HALF_SERVING_SIZE = 3;
	const FULL_SERVING_SIZE = 6;

	const CORE = 'CORE';
	const EXTENDED = 'EXTENDED';
	const SIDE = 'SIDE';
	const SIDE_HIDDEN = 'SIDE_HIDDEN';

	const NONE = 'None';
	const ZIP_LOCK = 'Zipped Freezer Bag';
	const SPLIT_PAN = '6 x 12 Split';
	const STANDARD_PAN = '9 x 13 Standard';
	const STANDARD_PAN_DOUBLE = '(2) 9 x 13 Standard';
	const FOIL_WRAP = 'Foil Wrap';
	const BAG = 'Bag';
	const BOX = 'Box';
	const PAN = 'Pan';

	const NO_CONTAINER = 'No Container';

	// notice fix
	public $featuredItem = null;

	// Added 08/2010: Used by a new mechanism for managing a bundle. If non-null then the menu_item with this ID determines the price and serving amount of the attached children
	public $parentItemId = null;

	// Added 08/2010:This tracks the quantity of the item that is belongs to the bundle. Price calculations must subtract this quantity first as the parentItem will provide the cost
	public $bundleItemCount = null;

	private static $container_types = array(
		1 => CMenuItem::SPLIT_PAN,
		2 => CMenuItem::STANDARD_PAN,
		3 => CMenuItem::ZIP_LOCK,
		4 => CMenuItem::STANDARD_PAN_DOUBLE,
		5 => CMenuItem::FOIL_WRAP,
		6 => CMenuItem::NO_CONTAINER,
		7 => CMenuItem::NONE
	);

	// please do not change this order.. value type always should be first
	//private static $plan_types = array( CMenuItem::VALUE, CMenuItem::VARIETY, CMenuItem::INTRO, CMenuItem::QUICK_HEARTY);
	private static $plan_types = array(
		CMenuItem::LEGACY,
		CMenuItem::FULL,
		CMenuItem::FOUR,
		CMenuItem::HALF,
		CMenuItem::TWO,
		CMenuItem::INTRO
	);

	public $pricing_type_info;
	public $icons;
	public $nutrition_array = array();
	public $in_bundle = array();
	public $pricing_tiers = array(
		'1' => array(
			CMenuItem::FULL => null,
			CMenuItem::HALF => null
		),
		'2' => array(
			CMenuItem::FULL => null,
			CMenuItem::HALF => null
		),
		'3' => array(
			CMenuItem::FULL => null,
			CMenuItem::HALF => null
		),
	);
	public $is_shiftsetgo = false;
	public $store_price;
	public $store_price_no_ltd;
	public $remaining_servings;
	public $override_inventory;
	public $number_sold;
	public $ltd_menu_item_show;
	public $this_type_out_of_stock;
	public $show_on_pick_sheet;
	public $menu_item_name_truncated;
	public $display_title;
	public $display_description;
	public $category_id;
	public $category_group_id;
	public $category_group;
	public $is_freezer_menu;
	public $is_visible;
	public $show_on_order_form;
	public $is_hidden_everywhere;
	public $is_users_favorite;
	public $ltd_menu_item_supported;
	public $out_of_stock;
	public $limited_qtys;
	public $ltd_menu_item_value;

	public $DAO_food_survey;
	public $DAO_food_survey_comments;
	public $menu_id;

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		if ($res)
		{
			// try not to do anything that adds queries to each fetch
			// just digest what information is already present
			$this->digestMenuItem(); // getStorePrice() within this function adds a query if  $this->DAO_mark_up_multi is not present
			$this->translatePricing();
			$this->buildIconFlags();
			$this->isShiftSetGo();
		}

		return $res;
	}

	function orderBy($order = false)
	{
		if ($order == 'FeaturedFirst') //put the featured item first
		{
			return parent::orderBy("
			 	/** 
				*	First sort by menu categories, Specials and Fast Lane (1), then EFL (2), then Sides (3) so they don't affect other sorting parameters
				*/
			 	CASE 
					WHEN menu_item.menu_item_category_id = 1 OR (menu_item.menu_item_category_id = 4 AND menu_item.is_store_special = 0) THEN 1
					WHEN menu_item.menu_item_category_id = 4 AND menu_item.is_store_special = 1 THEN 2
					WHEN menu_item.menu_item_category_id = 9 THEN 3
				END ASC,
				CASE WHEN menu_item.menu_item_category_id = 9 THEN menu_to_menu_item.is_hidden_everywhere END ASC,
				menu_to_menu_item.featuredItem DESC,
				CASE WHEN menu_item.menu_item_category_id = 1 OR (menu_item.menu_item_category_id = 4 AND menu_item.is_store_special = 0) THEN menu_to_menu_item.menu_order_value END ASC,
				CASE WHEN menu_item.menu_item_category_id = 4 AND menu_item.is_store_special = 1 THEN menu_item.menu_item_name END ASC,
				/** 
				*	For Sides, sort the Limited Time Offer first, then Holiday subcategory_label second then sort the other labels alphabetically 
				*/
				CASE 
					WHEN menu_item.menu_item_category_id = 9 AND menu_item.subcategory_label = 'Limited Time Offer' THEN 1
					WHEN menu_item.menu_item_category_id = 9 AND menu_item.subcategory_label = 'Holiday' THEN 2
					ELSE menu_item.subcategory_label
				END ASC,
				CASE WHEN menu_item.menu_item_category_id = 9 THEN menu_item.is_bundle END DESC,
				CASE WHEN menu_item.menu_item_category_id = 9 THEN menu_item.menu_item_name END ASC,
				CASE menu_item.pricing_type
					WHEN 'TWO' THEN 1
					WHEN 'HALF' THEN 2
					WHEN 'FOUR' THEN 3
					WHEN 'FULL' THEN 4
				END ASC
			");
		}
		else if ($order == 'NameAZ')
		{
			return parent::orderBy("
				menu_item.menu_item_name ASC,
				CASE menu_item.pricing_type
					WHEN 'TWO' THEN 1
					WHEN 'HALF' THEN 2
					WHEN 'FOUR' THEN 3
					WHEN 'FULL' THEN 4
				END ASC
			");
		}
		else if ($order == 'Inventory')
		{
			return parent::orderBy("
				menu_item_inventory.override_inventory desc, menu_item_inventory.number_sold desc
			");
		}
		else if ($order == 'FullFirst')
		{
			return parent::orderBy("
				menu_item.menu_item_name ASC,
				CASE menu_item.pricing_type
					WHEN 'FULL' THEN 1
					WHEN 'FOUR' THEN 2
					WHEN 'HALF' THEN 3
					WHEN 'TWO' THEN 4
				END ASC
			");
		}

		return parent::orderBy($order);
	}

	static public function containerTypes()
	{
		return self::$container_types;
	}

	static public function planTypes()
	{
		return self::$plan_types;
	}

	function translatePricing()
	{
		$this->pricing_type_info = array(
			'pricing_type_name' => self::translatePricingType($this->pricing_type),
			'pricing_type_name_w_qty' => self::translatePricingType($this->pricing_type) . ' (' . $this->servings_per_item . ')',
			'pricing_type_name_short' => self::translatePricingType($this->pricing_type, true),
			'pricing_type_name_short_w_qty' => self::translatePricingType($this->pricing_type, true) . ' (' . $this->servings_per_item . ')',
			'pricing_type_servings' => self::translatePricingTypeToNumeric($this->pricing_type),
			'pricing_type_serves' => self::translatePricingTypeToServes($this->pricing_type)
		);

		$this->translateServingsPerContainer();

		return $this->pricing_type_info;
	}

	function isShiftSetGo()
	{
		$this->is_shiftsetgo = CBundle::isShiftSetGoBundle($this->recipe_id, $this->menu_item_name);

		return $this->is_shiftsetgo;
	}

	function buildIconFlags()
	{
		$this->icons = null;

		if (!empty($this->DAO_recipe))
		{
			$would_order_again = false;
			if (!empty($this->DAO_food_survey))
			{
				$would_order_again = $this->DAO_food_survey->would_order_again;
			}

			$this->icons = $this->DAO_recipe->buildFlagArray($would_order_again, $this->DAO_menu);
		}
	}

	function translateServingsPerContainer()
	{
		if ($this->servings_per_container_display >= 6)
		{
			$this->pricing_type_info['pricing_type_serves_display'] = ($this->servings_per_container_display - 2) . '-' . $this->servings_per_container_display;
		}
		else if ($this->servings_per_container_display >= 2)
		{
			$this->pricing_type_info['pricing_type_serves_display'] = ($this->servings_per_container_display - 1) . '-' . $this->servings_per_container_display;
		}
		else
		{
			$this->pricing_type_info['pricing_type_serves_display'] = 1;
		}

		return $this->pricing_type_info['pricing_type_serves_display'];
	}

	static function translatePricingType($type, $short = false)
	{
		if ($short)
		{
			switch ($type)
			{
				case CMenuItem::FULL:
					return 'Lg';
				case CMenuItem::HALF:
				case CMenuItem::FOUR:
					return 'Md';
				case CMenuItem::TWO:
					return 'Sm';
				default:
					return $type;
			}
		}
		else
		{
			switch ($type)
			{
				case CMenuItem::FULL:
					return 'Large';
				case CMenuItem::HALF:
				case CMenuItem::FOUR:
					return 'Medium';
				case CMenuItem::TWO:
					return 'Small';
				default:
					return $type;
			}
		}
	}

	/**
	 * Input a CMenuItem::PricingType and return an equivalent serving count
	 *
	 * @param $pricingType
	 * @return int numeric serving size, will return 0 if no match
	 */
	static public function translatePricingTypeToNumeric($pricingType)
	{
		switch ($pricingType)
		{
			case CMenuItem::FULL:
				return 6;
			case CMenuItem::HALF:
				return 3;
			case CMenuItem::FOUR:
				return 4;
			case CMenuItem::TWO:
				return 2;
			default:
				return 0;//Unknown, should this raise error?
		}
	}

	/**
	 * Input a CMenuItem::PricingType and return an equivalent serving range
	 *
	 * @param $pricingType
	 * @return string serving range, will return 0 if no match
	 */
	static public function translatePricingTypeToServes($pricingType)
	{
		switch ($pricingType)
		{
			case CMenuItem::FULL:
				return '4-6';
			case CMenuItem::HALF:
				return '2-3';
			case CMenuItem::FOUR:
				return '3-4';
			case CMenuItem::TWO:
				return '1-2';
			default:
				return 0;//Unknown, should this raise error?
		}
	}

	/**
	 * @param      $pricingType         CMenuItem::PRICING_TYPE CONST
	 * @param bool $short               type of string to return e.g. Lg (6) or Large (6)
	 * @param null $servingSizeOverride optional overide of serving size
	 *
	 * @return mixed|string String translation of PricingType constand, or pricingtype constant if no known match
	 */
	static public function translatePricingTypeWithQuantity($pricingType, $short = true, $servingSizeOverride = null)
	{
		if ($short)
		{
			switch ($pricingType)
			{
				case CMenuItem::FULL:
					return 'Lg (' . (is_null($servingSizeOverride) ? 6 : $servingSizeOverride) . ')';
				case CMenuItem::FOUR:
					return 'Md (' . (is_null($servingSizeOverride) ? 4 : $servingSizeOverride) . ')';
				case CMenuItem::HALF:
					return 'Md (' . (is_null($servingSizeOverride) ? 3 : $servingSizeOverride) . ')';
				case CMenuItem::TWO:
					return 'Sm (' . (is_null($servingSizeOverride) ? 2 : $servingSizeOverride) . ')';
				default:
					return $pricingType;
			}
		}
		else
		{
			switch ($pricingType)
			{
				case CMenuItem::FULL:
					return 'Large (' . (is_null($servingSizeOverride) ? 6 : $servingSizeOverride) . ')';
				case CMenuItem::FOUR:
					return 'Medium (' . (is_null($servingSizeOverride) ? 4 : $servingSizeOverride) . ')';
				case CMenuItem::HALF:
					return 'Medium (' . (is_null($servingSizeOverride) ? 3 : $servingSizeOverride) . ')';
				case CMenuItem::TWO:
					return 'Small (' . (is_null($servingSizeOverride) ? 2 : $servingSizeOverride) . ')';
				default:
					return $pricingType;
			}
		}
	}

	/**
	 * @param      $servingSize number
	 * @param bool $short       short or long string version
	 *
	 * @return int|mixed|string string representation of serving size or serving size if passed value is not int
	 */
	static public function translateServingSizeToPricingType($servingSize, $short = true)
	{

		if (!is_numeric($servingSize))
		{
			return $servingSize;
		}
		if ($short)
		{
			switch ($servingSize)
			{
				case 6:
					return 'Lg (' . $servingSize . ')';
				case 3:
				case 4:
					return 'Md (' . $servingSize . ')';
				case 2:
					return 'Sm (' . $servingSize . ')';
				default:
					return $servingSize;
			}
		}
		else
		{
			switch ($servingSize)
			{
				case 6:
					return 'Large (' . $servingSize . ')';
				case 3:
				case 4:
					return 'Medium (' . $servingSize . ')';
				case 2:
					return 'Small (' . $servingSize . ')';
				default:
					return $servingSize;
			}
		}
	}

	/**
	 * @param      $servingSize number
	 * @param bool $short       short or long string version
	 *
	 * @return int|mixed|string string representation of serving size or serving size if passed value is not int
	 */
	static public function translateServingSizeToPricingTypeNoQuantity($servingSize, $short = true)
	{

		if (!is_numeric($servingSize))
		{
			return $servingSize;
		}
		if ($short)
		{
			switch ($servingSize)
			{
				case 6:
					return 'Lg';
				case 3:
				case 4:
					return 'Md';
				case 2:
					return 'Sm';
				default:
					return $servingSize;
			}
		}
		else
		{
			switch ($servingSize)
			{
				case 6:
					return 'Large';
				case 3:
				case 4:
					return 'Medium';
				case 2:
					return 'Small';
				default:
					return $servingSize;
			}
		}
	}

	function digestMenuItem()
	{
		$this->menu_item_name_truncated = (strlen($this->menu_item_name) > 50 ? substr($this->menu_item_name, 0, 50) . "..." : $this->menu_item_name);
		$this->display_title = $this->menu_item_name; // legacy support
		$this->display_description = stripslashes($this->menu_item_description); // legacy support
		$this->category_id = $this->menu_item_category_id; // legacy support
		$this->category_group = $this->categoryGroup();
 		$this->category_group_id = $this->categoryGroupId();
		$this->is_freezer_menu = $this->isFreezer();
		$this->is_visible = $this->isVisible();
		$this->show_on_pick_sheet = $this->showOnPickSheet();
		$this->show_on_order_form = $this->showOnOrderForm();
		$this->is_hidden_everywhere = $this->isHiddenEverywhere();

		$this->is_users_favorite = (!empty($this->favorite) && $this->favorite == 1 ? true : false);
		$this->ltd_menu_item_show = $this->ltd_menu_item_value;
		$this->ltd_menu_item_supported = (!empty($this->DAO_store->supports_ltd_roundup) && !empty($this->ltd_menu_item_value));

		$this->remaining_servings = $this->getRemainingServings();
		$this->out_of_stock = $this->isOutOfStock();
		$this->this_type_out_of_stock = $this->isOutOfStock_ThisType();
		$this->limited_qtys = $this->isLimitedQuantities();

		$this->base_price = $this->price; // legacy support, ideally 'price' should always be the unmodified base price from the database
		$this->store_price = $this->getStorePrice(); // store_price should be what the customer pays

		// convert in_bundle json to bundle objects
		$this->in_bundle = array();
		if (!empty($this->_in_bundle))
		{
			$bundlesArray = explode(',', $this->_in_bundle);

			foreach ($bundlesArray as $bundleInfo)
			{
				list($bundle['id'], $bundle['bundle_type'], $bundle['master_menu_item']) = explode(':', $bundleInfo);

				$this->in_bundle[$bundle['id']] = DAO_CFactory::create('bundle', true);
				$this->in_bundle[$bundle['id']]->id = $bundle['id'];
				$this->in_bundle[$bundle['id']]->bundle_type = $bundle['bundle_type'];
				$this->in_bundle[$bundle['id']]->master_menu_item = $bundle['master_menu_item'];
			}
		}

		// convert recipe_component_info json to recipe_component objects
		$this->nutrition_array = array();
		if (!empty($this->_recipe_component))
		{
			$componentArray = explode('|||', $this->_recipe_component);

			foreach ($componentArray as $componentInfo)
			{
				list($recipe_component['component_number'], $recipe_component['serving'], $recipe_component['notes']) = explode(':::', $componentInfo);

				$this->nutrition_array['component'][$recipe_component['component_number']]['info'] = array(
					'serving' => $recipe_component['serving'],
					'notes' => $recipe_component['notes']
				);
			}

			// convert recipe_component_info json to recipe_component objects
			if (isset($this->_recipe_component_nutrition_data) && !empty($this->_recipe_component_nutrition_data))
			{
				$dataArray = explode(',', $this->_recipe_component_nutrition_data);

				foreach ($dataArray as $dataInfo)
				{
					list($nutrition_data['component_number'], $nutrition_data['value'], $nutrition_data['note_indicator'], $nutrition_data['prefix'], $nutrition_element['label'], $nutrition_element['display_label'], $nutrition_element['measure_label'], $nutrition_element['daily_value'], $nutrition_element['do_display'], $nutrition_element['parent_element'], $nutrition_element['sprintf'], $nutrition_element['indent']) = explode(':', $dataInfo);

					if (!empty($nutrition_element['do_display']))
					{
						$this->nutrition_array['component'][$nutrition_data['component_number']]['element'][$nutrition_element['label']] = array(
							'label' => $nutrition_element['label'],
							'display_label' => $nutrition_element['display_label'],
							'prefix' => ((strtolower($nutrition_data['prefix']) != 'null') ? $nutrition_data['prefix'] : ''),
							'value' => $nutrition_data['value'],
							'measure_label' => ((strtolower($nutrition_element['measure_label']) != 'null') ? $nutrition_element['measure_label'] : ''),
							'value_formatted' => $nutrition_data['prefix'] . (self::formatDecimal($nutrition_data['value']) . ((strtolower($nutrition_element['measure_label']) != 'null') ? $nutrition_element['measure_label'] : '')),
							'percent_daily_value' => ((!empty($nutrition_element['daily_value']) && strtolower($nutrition_element['daily_value']) != 'null') ? round(($nutrition_data['value'] / $nutrition_element['daily_value']) * 100) : false),
							'note_indicator' => ((strtolower($nutrition_data['note_indicator']) == 'null') ? null : $nutrition_data['note_indicator']),
							'parent_element' => ((strtolower($nutrition_element['parent_element']) == 'null') ? null : $nutrition_element['parent_element']),
							'indent' => $nutrition_element['indent'],
							'sprintf_label' => (!empty($nutrition_element['sprintf']) ? sprintf($nutrition_element['display_label'], (self::formatDecimal($nutrition_data['value']) . $nutrition_element['measure_label'])) : false)
						);
					}
				}
			}
		}

		$this->pricing_tiers = array(
			'1' => array(
				CMenuItem::FULL => null,
				CMenuItem::HALF => null
			),
			'2' => array(
				CMenuItem::FULL => null,
				CMenuItem::HALF => null
			),
			'3' => array(
				CMenuItem::FULL => null,
				CMenuItem::HALF => null
			),
		);
		if (!empty($this->_pricing_tiers))
		{
			$pricingInfoArray = explode(',', $this->_pricing_tiers);

			foreach ($pricingInfoArray as $pricingInfo)
			{
				list($pricing['id'], $pricing['menu_id'], $pricing['recipe_id'], $pricing['pricing_type'], $pricing['tier'], $pricing['price']) = explode(':', $pricingInfo);

				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']] = DAO_CFactory::create('pricing', true);
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->id = $pricing['id'];
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->menu_id = $pricing['menu_id'];
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->recipe_id = $pricing['recipe_id'];
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->tier = $pricing['tier'];
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->pricing_type = $pricing['pricing_type'];
				$this->pricing_tiers[$pricing['tier']][$pricing['pricing_type']]->price = $pricing['price'];
			}
		}
	}

	/**
	 * Supply $DAO_mark_up_multi to override the markup
	 * Otherwise markup used is the latest markup
	 *
	 */
	function getStorePrice($DAO_mark_up_multi = null)
	{
		// Start with base price
		$this->store_price = $this->price;
		$this->store_price_pre_markup = $this->store_price;
		$this->store_price_markup_amount = 0;

		if (isset($this->override_price))
		{
			$this->store_price = $this->override_price;
			$this->store_price_pre_markup = $this->store_price;
			$this->store_price_markup_amount = 0;
		}
		else if (!empty($this->DAO_store) && !empty($this->menu_id))
		{
			if (empty($DAO_mark_up_multi))
			{
				if (!empty($this->DAO_mark_up_multi))
				{
					$DAO_mark_up_multi = $this->DAO_mark_up_multi;
				}
				else
				{
					$DAO_mark_up_multi = $this->DAO_store->getMarkupMultiObj($this->menu_id);
				}
			}

			$this->store_price_pre_markup = $this->store_price;
			$this->store_price = COrders::getItemMarkupMultiSubtotal($DAO_mark_up_multi, $this);
			$this->store_price_markup_amount = $this->store_price - $this->store_price_pre_markup;
		}
		else if (!empty($this->store_id) && !empty($this->menu_id))
		{
			if (empty($DAO_mark_up_multi))
			{
				if (!empty($this->DAO_mark_up_multi))
				{
					$DAO_mark_up_multi = $this->DAO_mark_up_multi;
				}
				else
				{
					$this->DAO_store = DAO_CFactory::create('store');
					$this->DAO_store->id = $this->store_id;
					$this->DAO_store->fetch();
					$DAO_mark_up_multi = $this->DAO_store->getMarkupMultiObj($this->menu_id);
				}
			}

			$this->store_price_pre_markup = $this->store_price;
			$this->store_price = COrders::getItemMarkupMultiSubtotal($DAO_mark_up_multi, $this);
			$this->store_price_markup_amount = $this->store_price - $this->store_price_pre_markup;
		}

		if (!empty($this->DAO_menu_item_mark_down->id))
		{
			$percentage = $this->DAO_menu_item_mark_down->markdown_value / 100;

			$this->store_price -= COrders::std_round(($this->store_price * $percentage));
		}

		$this->store_price_no_ltd = $this->store_price;

		if(!empty($this->store_id) && empty($this->DAO_store))
		{
			$this->DAO_store = DAO_CFactory::create('store', true);
			$this->DAO_store->id = $this->store_id;
			$this->DAO_store->find_DAO_store(true);
		}

		if (!empty($this->DAO_store->supports_ltd_roundup))
		{
			if (!empty($this->ltd_menu_item_value))
			{
				$this->store_price += $this->ltd_menu_item_value;
			}
		}

		// If the order_item object exists, restore the purchase price and LTD value
		if (!empty($this->DAO_order_item) && !empty($this->DAO_order_item->item_count))
		{
			// discounted_subtotal != null and menu_item_mark_down_id = null
			// means that this is an LTD item so restore the LTD values
			if (!empty($this->DAO_order_item->discounted_subtotal) && empty($this->DAO_order_item->menu_item_mark_down_id))
			{
				// Restore the price
				$this->store_price_no_ltd = $this->DAO_order_item->sub_total / $this->DAO_order_item->item_count;

				if(!empty($this->DAO_recipe))
				{
					// Restore the LTD price on the recipe object
					$this->DAO_recipe->ltd_menu_item_value = ($this->DAO_order_item->discounted_subtotal - $this->DAO_order_item->sub_total) / $this->DAO_order_item->item_count;

					// Restore the LTD value on the menu_item object
					$this->ltd_menu_item_value = $this->DAO_recipe->ltd_menu_item_value; // legacy support
				}
			}
			else
			{
				// Restore the price
				$this->store_price_no_ltd = $this->DAO_order_item->sub_total / $this->DAO_order_item->item_count;

				if(!empty($this->DAO_recipe))
				{
					// Restore the LTD price on the recipe object
					$this->DAO_recipe->ltd_menu_item_value = 0;
				}

				// Restore the LTD value on the menu_item object
				$this->ltd_menu_item_value = 0;
			}

			$this->store_price = $this->store_price_no_ltd + $this->ltd_menu_item_value;
		}

		return CTemplate::number_format($this->store_price);
	}

	function buildMenuItemArray($DAO_store = false, $DAO_mark_up_multi = null)
	{
		$menuItemArray = $this->toArray();
		$menuItemArray['menu_item_name_truncated'] = (strlen($this->menu_item_name) > 50 ? substr($this->menu_item_name, 0, 50) . "..." : $this->menu_item_name);
		$menuItemArray['display_title'] = $this->menu_item_name;
		$menuItemArray['category_id'] = $this->menu_item_category_id;
		$menuItemArray['display_description'] = stripslashes($this->menu_item_description);
		$menuItemArray['is_shiftsetgo'] = $this->is_shiftsetgo;
		$menuItemArray['base_price'] = $this->price;
		$menuItemArray['is_freezer_menu'] = $this->isFreezer();
		$menuItemArray['pricing_type_info'] = $this->pricing_type_info;
		$menuItemArray['excluded'] = isset($this->excluded) ? true : false;
		$menuItemArray['is_visible'] = $this->is_visible;
		$menuItemArray['is_hidden_everywhere'] = $this->isHiddenEverywhere();
		$menuItemArray['show_on_pick_sheet'] = $this->show_on_pick_sheet;
		$menuItemArray['show_on_order_form'] = $this->show_on_order_form;
		$menuItemArray['parent_item'] = 0;
		$menuItemArray['number_items_required'] = 0;
		$menuItemArray['is_users_favorite'] = (!empty($this->favorite) && $this->favorite == 1 ? true : false);
		//PHP 8 $menuItemArray['DAO_food_survey'] = $this->DAO_food_survey ?? null;
		//PHP 8 $menuItemArray['DAO_food_survey_comments'] = $this->DAO_food_survey_comments ?? null;
		$menuItemArray['DAO_food_survey'] = $this->DAO_food_survey;
		$menuItemArray['DAO_food_survey_comments'] = $this->DAO_food_survey_comments;
		$menuItemArray['ltd_menu_item_show'] = $this->ltd_menu_item_value;
		$menuItemArray['ltd_menu_item_supported'] = (!empty($DAO_store->supports_ltd_roundup) && !empty($this->ltd_menu_item_value));
		$menuItemArray['icons'] = $this->icons;
		$menuItemArray['remaining_servings'] = $this->remaining_servings;
		$menuItemArray['this_type_out_of_stock'] = $this->this_type_out_of_stock;
		$menuItemArray['out_of_stock'] = $this->out_of_stock;
		$menuItemArray['limited_qtys'] = $this->limited_qtys;

		$menuItemArray['store_price'] = $this->getStorePrice($DAO_mark_up_multi);

		// this legacy method overrides 'price' to the current price, ultimately 'price' should remain the base price store_price should be the modified price
		if (isset($this->override_price))
		{
			$menuItemArray['price'] = $this->override_price;
		}
		else
		{
			if (isset($DAO_store) && $DAO_store)
			{
				if (empty($DAO_mark_up_multi))
				{
					$DAO_mark_up_multi = $DAO_store->getMarkUpMultiObj($this->menu_id);
				}

				$menuItemArray['price'] = CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($DAO_mark_up_multi, $this, 1));
			}
		}

		if ($menuItemArray['markdown_id'])
		{
			$percentage = $menuItemArray['markdown_value'] / 100;

			$menuItemArray['price'] -= COrders::std_round(($menuItemArray['price'] * $percentage));
		}

		if (!empty($DAO_store->supports_ltd_roundup))
		{
			if (!empty($menuItemArray['ltd_menu_item_value']))
			{
				$menuItemArray['price'] += $menuItemArray['ltd_menu_item_value'];
			}
		}

		return $menuItemArray;
	}

	private static $labelToToolTipMap = array(
		"Guest Favorite" => "Top rated by guests",
		"New" => "Brand new on our menu!",
		"Fast Lane" => "Pre-assembled for you",
		"New Fast Lane" => "Pre-assembled for you"
	);

	static function toolTipForMenuLabel($label)
	{
		if (array_key_exists($label, self::$labelToToolTipMap))
		{
			return self::$labelToToolTipMap[$label];
		}
		else
		{
			return false;
		}
	}

	static function validateItemForOrder($item_id, $quantity, $store_id, $menu_id, $nav_type, $bundle_id = false, $actualSessionType = false, $numberServingsInCart = 0, $numberCoreServingsInCart = 0, $doBundle = false)
	{
		$bundleClause = "";
		$bundleSelect = "";

		if ($nav_type == CTemplate::INTRO || $doBundle || ($nav_type == CTemplate::EVENT && ($actualSessionType != CSession::STANDARD && $actualSessionType != 'SPECIAL_EVENT')))
		{
			if (empty($bundle_id))
			{
				return array(
					false,
					"Bundle is required for this session type."
				);
			}

			$bundleClause = " inner join bundle b on b.id = $bundle_id inner join bundle_to_menu_item bmi on bmi.bundle_id = $bundle_id and bmi.menu_item_id = mi.id and bmi.is_deleted = 0 ";
			$bundleSelect = ", b.number_servings_required ";
		}

		$visibleAndInventoryTest = new DAO();
		$visibleAndInventoryTest->query("SELECT 
			iq.*, 
			mii.override_inventory, 
			mii.initial_inventory, 
			mii.number_sold, 
			mii2.override_inventory as global_override_inv,
			mii2.initial_inventory as global_intial_inv, 
			mii2.number_sold as global_num_sold 
			FROM (
				SELECT mi.recipe_id, mi.menu_item_category_id, mi.servings_per_item, mmi.is_visible, mi.is_store_special, mmi.is_hidden_everywhere $bundleSelect
				FROM menu_item mi
				LEFT JOIN menu_to_menu_item mmi on mmi.menu_item_id = mi.id and mmi.store_id = $store_id and mmi.menu_id = $menu_id and mmi.is_deleted = 0
				$bundleClause
				where mi.id = $item_id and mi.is_deleted = 0
			) as iq
			LEFT JOIN menu_item_inventory mii on mii.recipe_id = iq.recipe_id and mii.store_id = $store_id and mii.menu_id = $menu_id and mii.is_deleted = 0
			LEFT JOIN menu_item_inventory mii2 on mii2.recipe_id = iq.recipe_id and mii2.store_id = $store_id and isnull(mii2.menu_id) and mii2.is_deleted = 0");

		if ($visibleAndInventoryTest->N == 0)
		{
			return array(
				false,
				"Item was deleted or item not in bundle."
			);
		}

		$visibleAndInventoryTest->fetch();

		if (!$visibleAndInventoryTest->is_visible || $visibleAndInventoryTest->is_hidden_everywhere)
		{
			return array(
				false,
				"Item was removed from menu."
			);
		}

		if ($bundle_id && $numberServingsInCart + $visibleAndInventoryTest->servings_per_item > $visibleAndInventoryTest->number_servings_required)
		{
			return array(
				false,
				"This item cannot be added as it would exceed the number of servings allowed for this offer."
			);
		}

		if (!$bundle_id && ($nav_type == CTemplate::MADE_FOR_YOU || $nav_type == CTemplate::STANDARD || $nav_type == CTemplate::SPECIAL_EVENT))
		{
			if ($visibleAndInventoryTest->is_store_special && CURRENT_PLATE_POINTS_VERSION > 1 && $numberCoreServingsInCart < 36)
			{
				return array(
					false,
					"Store Specials cannot be added until you have 36 servings from the Core Menu."
				);
			}
		}

		// TODO: Could add inventory test here but this will require checking cart for possible other sizes of the same recipe and that quantity be included in test

		return array(
			true,
			"Success"
		);
	}

	static function getPerMenuItemLock($item_id)
	{
		$QObj = new DAO();
		$QObj->query("SELECT GET_LOCK('lock_for_$item_id', 5) as is_locked");
		$QObj->fetch();
		if ($QObj->is_locked == "1")
		{
			return true;
		}

		return false;
	}

	function getTierPrice($pricing_type, $tier)
	{
		if (!empty($this->pricing_tiers[$tier]))
		{
			if (!empty($this->pricing_tiers[$tier][$pricing_type]))
			{
				return $this->pricing_tiers[$tier][$pricing_type]->price;
			}
		}

		return null;
	}

	function getRemainingServings()
	{
		$this->remaining_servings = $this->override_inventory - $this->number_sold;

		return $this->remaining_servings;
	}

	static function releasePerMenuItemLock($item_id)
	{
		$QObj = new DAO();
		$QObj->query("SELECT RELEASE_LOCK('lock_for_$item_id')");
	}

	static function waitForFreeMenuItemLock($item_id)
	{
		sleep(10);
		$locked = true;
		while ($locked)
		{
			$QObj = new DAO();
			$QObj->query("SELECT IS_FREE_LOCK('lock_for_$item_id') as is_free");
			$QObj->fetch();
			if ($QObj->is_free == "1")
			{
				$locked = false;
			}
			else
			{
				sleep(10);
			}
		}
	}

	private static function duplicate($itemToDuplicate, $targetMenu, $store_id, $override_menu_item_subcategory_label = null)
	{
		try
		{
			$retVal = array();

			$uberObject = new DAO();
			$uberObject->query('START TRANSACTION;');

			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $targetMenu;

			// get the source menu item to be duplicated
			$DAO_menu_item = DAO_CFactory::create('menu_item');
			$DAO_menu_item->id = $itemToDuplicate;
			$DAO_menu_item->orderBy("FullFirst");

			// check tha the source menu exists
			if ($DAO_menu_item->find(true))
			{
				// get all sizes for source menu item to be duplicated
				$DAO_menu_item_by_entree = DAO_CFactory::create('menu_item');
				$DAO_menu_item_by_entree->entree_id = $DAO_menu_item->id;
				$DAO_menu_item_by_entree->orderBy("FullFirst");
				$DAO_menu_item_by_entree->find();

				// loop over the source sizes and check if they've already been copied
				while ($DAO_menu_item_by_entree->fetch())
				{
					// check if this item has already been copied
					// test that this item has not been duplicated yet for the target month
					$test_DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
					$test_DAO_menu_to_menu_item->menu_id = $DAO_menu->id;
					$test_DAO_menu_to_menu_item->store_id = 'NULL';
					$test_DAO_menu_item = DAO_CFactory::create('menu_item');
					$test_DAO_menu_item->copied_from = $DAO_menu_item_by_entree->id;
					$test_DAO_menu_to_menu_item->joinAddWhereAsOn($test_DAO_menu_item);
					$test_DAO_menu_to_menu_item->selectAdd();
					$test_DAO_menu_to_menu_item->selectAdd("menu_to_menu_item.*");

					// it has been copied, reset the source item to be duplicated to this copied item
					if ($test_DAO_menu_to_menu_item->find(true))
					{
						// get the menu item to be duplicated
						$DAO_menu_item = DAO_CFactory::create('menu_item');
						$DAO_menu_item->id = $test_DAO_menu_to_menu_item->menu_item_id;
						$DAO_menu_item->orderBy("FullFirst");
					}

					// get the source menu for the item being copied
					$DAO_source_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
					$DAO_source_menu_to_menu_item->menu_item_id = $DAO_menu_item->id;
					$DAO_source_menu_to_menu_item->store_id = 'NULL';
					$DAO_source_menu_to_menu_item->find(true);

					// get all sizes for menu item to be duplicated
					$DAO_menu_item_by_entree = DAO_CFactory::create('menu_item');
					$DAO_menu_item_by_entree->entree_id = $DAO_menu_item->id;
					$DAO_menu_item_by_entree->orderBy("FullFirst");
					$DAO_menu_item_by_entree->find();

					// loop over the sizes and duplicate them
					$newEntreeId = false;

					while ($DAO_menu_item_by_entree->fetch())
					{
						// clone the entree as the new menu_item to be inserted or added to menu_to_menu_item
						$new_menu_item = $DAO_menu_item_by_entree->cloneObj(false);

						// check if this item has already been copied
						// test that this item has not been duplicated yet for the target month
						$test_DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
						$test_DAO_menu_to_menu_item->menu_id = $DAO_menu->id;
						$test_DAO_menu_to_menu_item->store_id = 'NULL';
						$test_DAO_menu_item = DAO_CFactory::create('menu_item');
						$test_DAO_menu_item->copied_from = $new_menu_item->id;
						$test_DAO_menu_to_menu_item->joinAddWhereAsOn($test_DAO_menu_item);
						$test_DAO_menu_to_menu_item->selectAdd();
						$test_DAO_menu_to_menu_item->selectAdd("menu_to_menu_item.*");

						// it has not been copied, insert otherwise use as the entree id
						if (!$test_DAO_menu_to_menu_item->find())
						{
							$new_menu_item->copied_from = $DAO_menu_item_by_entree->id;

							if ($new_menu_item->menu_item_category_id != 9)
							{
								$new_menu_item->menu_item_category_id = 4;
								$new_menu_item->is_store_special = 1;
								$new_menu_item->station_number = 'null';
								$new_menu_item->is_preassembled = 1;
								$new_menu_item->menu_label = 'null';
								$new_menu_item->is_key_menu_push_item = 0;
								$new_menu_item->is_visibility_controllable = 1;
							}

							if (!is_null($override_menu_item_subcategory_label))
							{
								$new_menu_item->subcategory_label = $override_menu_item_subcategory_label;
							}

							$new_menu_item->insert();

							// set the new entree id for this menu item group
							if (empty($newEntreeId))
							{
								$newEntreeId = $new_menu_item->id;
							}
						}
						else
						{
							if (empty($newEntreeId))
							{
								$newEntreeId = $DAO_menu_item_by_entree->entree_id;
							}
						}

						// set the new entree id for this menu item group
						if ($new_menu_item->entree_id != $newEntreeId)
						{
							$updated_menu_item = $new_menu_item->cloneObj(false);
							$updated_menu_item->entree_id = $newEntreeId;

							// update the entree id and reassign $new_menu_item to the new object
							$updated_menu_item->update($new_menu_item);
							$new_menu_item = $updated_menu_item;
						}

						// global menu
						$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
						$DAO_menu_to_menu_item->store_id = 'null';
						$DAO_menu_to_menu_item->menu_id = $DAO_menu->id;
						$DAO_menu_to_menu_item->menu_item_id = $new_menu_item->id;

						// check to see if it exists yet
						if (!$DAO_menu_to_menu_item->find())
						{
							$DAO_menu_to_menu_item->featuredItem = 0;
							$DAO_menu_to_menu_item->is_visible = 0;
							$DAO_menu_to_menu_item->insert();
						}

						// store menu
						$DAO_menu_to_menu_item_store = DAO_CFactory::create('menu_to_menu_item');
						$DAO_menu_to_menu_item_store->store_id = $store_id;
						$DAO_menu_to_menu_item_store->menu_id = $DAO_menu->id;
						$DAO_menu_to_menu_item_store->menu_item_id = $new_menu_item->id;

						// check to see if it exists yet
						if (!$DAO_menu_to_menu_item_store->find())
						{
							// markup is gone, set the default override
							if (!$DAO_menu->isEnabled_Markup() || !$DAO_menu->isEnabled_Markup_Sides())
							{
								// Look for the last price this sold for at the store and set the override to that
								$last_price_DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
								$last_price_DAO_menu_to_menu_item->store_id = $store_id;
								$last_price_DAO_menu_item = DAO_CFactory::create('menu_item');
								$last_price_DAO_menu_item->recipe_id = $new_menu_item->recipe_id;
								$last_price_DAO_menu_item->pricing_type = $new_menu_item->pricing_type;
								$last_price_DAO_menu_to_menu_item->joinAddWhereAsOn($last_price_DAO_menu_item);
								$last_price_DAO_menu_to_menu_item->selectAdd();
								$last_price_DAO_menu_to_menu_item->selectAdd("menu_to_menu_item.*");
								$last_price_DAO_menu_to_menu_item->orderBy("menu_to_menu_item.menu_id DESC");
								$last_price_DAO_menu_to_menu_item->limit("1");

								if ($last_price_DAO_menu_to_menu_item->find(true))
								{
									$DAO_menu_to_menu_item_store->override_price = (!empty($last_price_DAO_menu_to_menu_item->override_price)) ? $last_price_DAO_menu_to_menu_item->override_price : $last_price_DAO_menu_to_menu_item->DAO_menu_item->price;
								}
							}

							$DAO_menu_to_menu_item_store->featuredItem = 0;
							$DAO_menu_to_menu_item_store->is_visible = 0;
							$DAO_menu_to_menu_item_store->insert();
						}

						// inventory
						$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
						$DAO_menu_item_inventory->menu_id = $DAO_menu->id;
						$DAO_menu_item_inventory->store_id = $store_id;
						$DAO_menu_item_inventory->recipe_id = $new_menu_item->recipe_id;

						// make sure we check to see if inventory already exists, can't have duplicate rows
						if (!$DAO_menu_item_inventory->find(true))
						{
							$DAO_menu_item_inventory->initial_inventory = 0;
							$DAO_menu_item_inventory->override_inventory = 0;
							$DAO_menu_item_inventory->insert();
						}

						// create nutritional data for target menu
						// check if recipe exists for the target menu
						$DAO_recipe_test = DAO_CFactory::create('recipe');
						$DAO_recipe_test->override_menu_id = $DAO_menu->id;
						$DAO_recipe_test->recipe_id = $new_menu_item->recipe_id;

						// check to see if it exists yet
						if (!$DAO_recipe_test->find())
						{
							// check if recipe exists from the source menu
							$DAO_recipe = DAO_CFactory::create('recipe');
							$DAO_recipe->override_menu_id = $DAO_source_menu_to_menu_item->menu_id;
							$DAO_recipe->recipe_id = $new_menu_item->recipe_id;

							if ($DAO_recipe->find(true))
							{
								$newRecipe = $DAO_recipe->cloneObj(false);
								$newRecipe->override_menu_id = $DAO_menu->id;
								// check to see if it exists for the target menu
								if (!$newRecipe->find())
								{
									$newRecipe->ltd_menu_item_value = 0;
									$newRecipe->insert();
								}

								$components = DAO_CFactory::create('recipe_component');
								$components->recipe_id = $DAO_recipe->id;
								$components->find();

								while ($components->fetch())
								{
									$newComponent = $components->cloneObj(false);
									$newComponent->recipe_id = $newRecipe->id;
									$newComponent->insert();

									$exData = DAO_CFactory::create('nutrition_data');
									$exData->component_id = $components->id;
									$exData->find();

									while ($exData->fetch())
									{
										$newDatum = $exData->cloneObj(false);
										$newDatum->component_id = $newComponent->id;
										$newDatum->insert();
									}
								}
							}
						}

						$retVal[$new_menu_item->id] = array(
							'item_id' => $new_menu_item->id,
							'name' => $new_menu_item->menu_item_name,
							'size' => $new_menu_item->pricing_type_info['pricing_type_name_short_w_qty'],
							'base_price' => (!empty($DAO_menu_to_menu_item_store->override_price)) ? $DAO_menu_to_menu_item_store->override_price : $new_menu_item->price,
							'subcategory_label' => $new_menu_item->subcategory_label
						);
					}
				}
			}

			//	throw new Exception("forced out!");
			$uberObject->query('COMMIT;');

			return $retVal;
		}
		catch (Exception $e)
		{
			$uberObject->query('ROLLBACK;');

			return false;
		}
	}

	static function duplicateItemForMenu($itemToDuplicate, $targetMenu, $store_id, $override_menu_item_subcategory_label = null)
	{
		// menu item has yet to be duplicated so get a lock on that item
		if (self::getPerMenuItemLock($itemToDuplicate))
		{
			$retVal = self::duplicate($itemToDuplicate, $targetMenu, $store_id, $override_menu_item_subcategory_label);
			self::releasePerMenuItemLock($itemToDuplicate);
		}
		else
		{
			// wait for lock to release and just return success
			self::waitForFreeMenuItemLock($itemToDuplicate);

			if (self::getPerMenuItemLock($itemToDuplicate))
			{
				$retVal = self::duplicate($itemToDuplicate, $targetMenu, $store_id, $override_menu_item_subcategory_label);
				self::releasePerMenuItemLock($itemToDuplicate);
			}
		}

		return $retVal;
	}

	static function getEFLEligibleItems($menu_id, $store_id)
	{
		$retVal = array();

		$curMonthIDs = array();

		$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
		$DAO_menu_to_menu_item->store_id = $store_id;
		$DAO_menu_to_menu_item->menu_id = $menu_id;

		$DAO_menu_to_menu_item->selectAdd();
		$DAO_menu_to_menu_item->selectAdd("DISTINCT (menu_item.recipe_id) as recipe_id");

		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->is_bundle = 0;

		$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_menu_item, 'INNER', false, false, false);

		$DAO_menu_to_menu_item->find();

		while ($DAO_menu_to_menu_item->fetch())
		{
			$curMonthIDs[] = $DAO_menu_to_menu_item->recipe_id;
		}

		//CLog::Record("curMonth:\r\n" . print_r($curMonthIDs,true));

		/*
		 * This is not needed as the first query picks them all up.
		 *
		$itemsAlreadyAdded = DAO_CFactory::create('menu_to_menu_item');
		$itemsAlreadyAdded->query("select distinct mi.recipe_id from menu_to_menu_item mmi
									join menu_item mi on mi.id = mmi.menu_item_id and mi.menu_item_category_id < 5 and not ISNULL(mi.copied_from)
									where mmi.menu_id = $menu_id and mmi.store_id = $store_id and mmi.is_deleted = 0");

		while($itemsAlreadyAdded->fetch())
		{
			$curMonthIDs[] = $itemsAlreadyAdded->recipe_id;
		}
		*/

		//CLog::Record("with Added:\r\n" . print_r($curMonthIDs,true));

		$isTestMenu = CStore::isTestMenuStore($menu_id, $store_id);

		$recipeClause = " and mi.recipe_id < 10000 ";
		if ($isTestMenu)
		{
			$recipeClause = "";
		}
		$menuItemObj = DAO_CFactory::create('menu_item');
		$menuItemObj->query("select iq.*, mmi2.menu_item_id, m.menu_name, mi2.price, mi2.menu_item_name as recent_name, mi2.menu_item_description from
								(select mi.recipe_id, mi.menu_item_name, max(mmi.menu_id) as most_recent_menu from menu_item mi
								join menu_to_menu_item mmi on isnull(mmi.store_id) and mmi.menu_id < $menu_id and mmi.menu_id > $menu_id - 19 and mmi.is_deleted = 0 and mmi.menu_item_id = mi.id
								JOIN recipe  ON (recipe.recipe_id=mi.recipe_id)  AND  ( recipe.override_menu_id = mmi.menu_id )  AND ( recipe.is_deleted = 0 ) 
								where mi.menu_item_category_id < 5 and mi.recipe_id <> 286 and mi.is_bundle = 0 and mi.is_deleted = 0 $recipeClause
								group by mi.recipe_id) as iq
								    
								join menu_item mi2 on iq.recipe_id = mi2.recipe_id and mi2.entree_id = mi2.id and mi2.is_deleted = 0  
								join menu_to_menu_item mmi2 on mi2.id = mmi2.menu_item_id and ISNULL(mmi2.store_id) and mmi2.is_deleted = 0 and mmi2.menu_id = iq.most_recent_menu
								JOIN recipe  ON (recipe.recipe_id=mi2.recipe_id)  AND  ( recipe.override_menu_id = mmi2.menu_id )  AND ( recipe.is_deleted = 0 ) 
								join menu m on m.id = iq.most_recent_menu
								order by iq.most_recent_menu desc, mmi2.menu_item_id");

		while ($menuItemObj->fetch())
		{

			if (in_array($menuItemObj->recipe_id, $curMonthIDs))
			{
				continue;
			}

			if (!isset($retVal[$menuItemObj->most_recent_menu]))
			{
				$retVal[$menuItemObj->most_recent_menu] = array(
					"menu_info" => array(),
					"items" => array()
				);
			}

			$retVal[$menuItemObj->most_recent_menu]['items'][$menuItemObj->recipe_id] = array(
				'name' => $menuItemObj->recent_name,
				'id' => $menuItemObj->menu_item_id,
				'price' => $menuItemObj->price,
				'recipe_id' => $menuItemObj->recipe_id,
				'description' => $menuItemObj->menu_item_description
			);

			if (!isset($retVal[$menuItemObj->most_recent_menu]['menu_info']['name']))
			{
				$retVal[$menuItemObj->most_recent_menu]['menu_info']['name'] = $menuItemObj->menu_name;
				$retVal[$menuItemObj->most_recent_menu]['menu_info']['menu_id'] = $menuItemObj->most_recent_menu;
			}
		}

		return $retVal;
	}

	static function getEFLEligibleSidesSweets($menu_id, $store_id)
	{

		$retVal = array();

		$curMonthIDs = array();

		$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
		$DAO_menu_to_menu_item->store_id = $store_id;
		$DAO_menu_to_menu_item->menu_id = $menu_id;

		$DAO_menu_to_menu_item->selectAdd();
		$DAO_menu_to_menu_item->selectAdd("DISTINCT (menu_item.recipe_id) as recipe_id");

		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->is_bundle = 0;

		$DAO_menu_to_menu_item->joinAddWhereAsOn($DAO_menu_item, 'INNER', false, false, false);

		$DAO_menu_to_menu_item->find();

		while ($DAO_menu_to_menu_item->fetch())
		{
			$curMonthIDs[] = $DAO_menu_to_menu_item->recipe_id;
		}

		$isTestMenu = CStore::isTestMenuStore($menu_id, $store_id);

		$recipeClause = " and mi.recipe_id < 10000 ";
		if ($isTestMenu)
		{
			$recipeClause = "";
		}
		$menuItemObj = DAO_CFactory::create('menu_item');

		$query = "select distinct iq.*, m.menu_name, mi2.price, mi2.menu_item_name as recent_name, mi2.menu_item_description, mi2.subcategory_label as parent_subcategory_label 
								from
								(select distinct mi.recipe_id, mi.menu_item_name, max(mmi.menu_id) as most_recent_menu, mi.id as menu_item_id ,
								IFNULL((select mii.subcategory_label from menu_to_menu_item mmii
								join menu_item mii on mii.id = mmii.menu_item_id and mmii.is_deleted = 0
								where mii.recipe_id = mi.recipe_id and mmii.menu_id = $menu_id
								and mmii.store_id is not null order by mmii.timestamp_created limit 1),  'NO STORE') as subcategory_label           
								from menu_item mi
								join menu_to_menu_item mmi on isnull(mmi.store_id) and mmi.menu_id < $menu_id and mmi.menu_id > $menu_id - 19 and mmi.is_deleted = 0 and mmi.menu_item_id = mi.id
								JOIN recipe  ON (recipe.recipe_id=mi.recipe_id)  AND  ( recipe.override_menu_id = mmi.menu_id )  AND ( recipe.is_deleted = 0 ) 
								where mi.menu_item_category_id = 9 and mi.recipe_id <> 286 AND mi.is_bundle = 0 and mi.is_deleted = 0 $recipeClause
								group by mi.recipe_id) as iq
								join menu_item mi2 on iq.recipe_id = mi2.recipe_id and mi2.entree_id = mi2.id and mi2.is_deleted = 0 
								join menu_to_menu_item mmi2 on mi2.id = mmi2.menu_item_id and ISNULL(mmi2.store_id) and mmi2.is_deleted = 0 and mmi2.menu_id = iq.most_recent_menu
								JOIN recipe  ON (recipe.recipe_id=mi2.recipe_id)  AND  ( recipe.override_menu_id = mmi2.menu_id )  AND ( recipe.is_deleted = 0 ) 
								join menu m on m.id = iq.most_recent_menu
								order by iq.most_recent_menu desc, mmi2.menu_item_id";
		$menuItemObj->query($query);

		while ($menuItemObj->fetch())
		{
			// Previously a core item, don't allow for S&S
			if ($menuItemObj->recipe_id == 219)
			{
				continue;
			}

			if (in_array($menuItemObj->recipe_id, $curMonthIDs))
			{
				continue;
			}

			if (!isset($retVal[$menuItemObj->most_recent_menu]))
			{
				$retVal[$menuItemObj->most_recent_menu] = array(
					"menu_info" => array(),
					"items" => array()
				);
			}

			$retVal[$menuItemObj->most_recent_menu]['items'][$menuItemObj->recipe_id] = array(
				'name' => $menuItemObj->recent_name,
				'id' => $menuItemObj->menu_item_id,
				'price' => $menuItemObj->price,
				'category' => ($menuItemObj->subcategory_label == 'NO STORE' ? $menuItemObj->parent_subcategory_label : $menuItemObj->subcategory_label),
				'category_locked' => !($menuItemObj->subcategory_label == 'NO STORE'),
				'recipe_id' => $menuItemObj->recipe_id,
				'description' => $menuItemObj->menu_item_description
			);

			if (!isset($retVal[$menuItemObj->most_recent_menu]['menu_info']['name']))
			{
				$retVal[$menuItemObj->most_recent_menu]['menu_info']['name'] = $menuItemObj->menu_name;
				$retVal[$menuItemObj->most_recent_menu]['menu_info']['menu_id'] = $menuItemObj->most_recent_menu;
			}
		}

		return $retVal;
	}

	/**
	 * @param $menu_select can be a menu id or 'next' for next month's menu
	 *
	 * @return array(    $menuOptions[id]['menu_name']
	 *                    $menuOptions[id]['startdate']
	 *                    $menuOptions[id]['enddate'],
	 *                    $menuItemInfo
	 */
	public static function getMenuItems($menu_id)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$menuItemInfo = array();

		while ($DAO_menu_item->fetch())
		{
			if ($DAO_menu_item->isMenuItem_EFL())
			{
				$DAO_menu_item->category_type = "Extended Fast Lane";
			}
			else if ($DAO_menu_item->isMenuItem_Core())
			{
				$DAO_menu_item->category_type = "Specials";
			}
			else
			{
				$DAO_menu_item->category_type = $DAO_menu_item->category;
			}

			$perPricingTypeData = array(
				'menu_item_id' => $DAO_menu_item->id,
				'price' => $DAO_menu_item->price,
				'container_type' => $DAO_menu_item->container_type
			);

			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id][$DAO_menu_item->pricing_type] = $perPricingTypeData;
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['title'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['desc'] = $DAO_menu_item->menu_item_description;
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['recipe_id'] = $DAO_menu_item->recipe_id;
		}

		$menuItemInfo['menu_id'] = $DAO_menu->id;
		$menuItemInfo['menu_name'] = $DAO_menu->menu_name;
		$menuItemInfo['menu_month'] = strftime('%B', strtotime($DAO_menu->menu_start));

		return $menuItemInfo;
	}

	/**
	 */
	public static function getMenuItemsArray($menu_id)
	{
		//fetch the menu for the month
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$daoMenu->find(true);
		// get entrees
		$daoMenuItem = DAO_CFactory::create('menu_to_menu_item');
		$daoMenuItem->query("SELECT
			mmi.id,
			mi.entree_id,
			mi.id AS menu_item_id,
			mi.menu_item_name,
			mi.menu_item_description,
			mic.category_type,
			mi.pricing_type,
			mi.container_type,
			mi.price,
			mi.recipe_id
		FROM menu_to_menu_item mmi
		JOIN menu_item mi ON mmi.menu_item_id = mi.id AND mi.is_deleted = 0
		JOIN menu_item_category mic ON mi.menu_item_category_id = mic.id
		WHERE mmi.menu_id = '" . $menu_id . "'
		AND mmi.store_id IS NULL
		AND mmi.is_deleted = 0
		ORDER BY mmi.menu_order_value, mic.id");

		$menuItemInfo = array();

		while ($daoMenuItem->fetch())
		{

			$menuItemInfo['recipe_ids'][$daoMenuItem->recipe_id][$daoMenuItem->pricing_type] = array(
				'menu_item_id' => $daoMenuItem->menu_item_id,
				'recipe_id' => $daoMenuItem->recipe_id,
				'entree_id' => $daoMenuItem->entree_id
			);
		}

		$menuItemInfo['menu_id'] = $daoMenu->id;
		$menuItemInfo['menu_name'] = $daoMenu->menu_name;
		$menuItemInfo['menu_month'] = strftime('%B', strtotime($daoMenu->menu_start));

		return $menuItemInfo;
	}

	static function getMenuItemChangeHistory($store_id, $startingDate, $daysBack = 1)
	{
		$retVal = array();

		$intervalQuery = "
			(mi.timestamp_updated >= '" . $startingDate . " 00:00:01' and mi.timestamp_updated <= '" . $startingDate . " 23:59:59' and mi.timestamp_updated > mi.timestamp_created)
			OR 
			(r.timestamp_updated >= '" . $startingDate . " 00:00:01' and r.timestamp_updated <= '" . $startingDate . " 23:59:59' and r.timestamp_updated > r.timestamp_created)
			OR 
			(rc.timestamp_updated >= '" . $startingDate . " 00:00:01' and rc.timestamp_updated <= '" . $startingDate . " 23:59:59' and rc.timestamp_updated > rc.timestamp_created)
			OR 
			(nd.timestamp_updated >= '" . $startingDate . " 00:00:01' and nd.timestamp_updated <= '" . $startingDate . " 23:59:59' and nd.timestamp_updated > nd.timestamp_created)		
		";

		if ($daysBack > 1)
		{
			$intervalQuery = "
				(mi.timestamp_updated >= DATE_SUB('" . $startingDate . " 00:00:01', INTERVAL " . $daysBack . " DAY) and mi.timestamp_updated <= '" . $startingDate . " 23:59:59' and mi.timestamp_updated > mi.timestamp_created)
				OR 
				(r.timestamp_updated >= DATE_SUB('" . $startingDate . " 00:00:01', INTERVAL " . $daysBack . " DAY) and r.timestamp_updated <= '" . $startingDate . " 23:59:59' and r.timestamp_updated > r.timestamp_created)
				OR 
				(rc.timestamp_updated >= DATE_SUB('" . $startingDate . " 00:00:01', INTERVAL " . $daysBack . " DAY) and rc.timestamp_updated <= '" . $startingDate . " 23:59:59' and rc.timestamp_updated > rc.timestamp_created)
				OR 
				(nd.timestamp_updated >= DATE_SUB('" . $startingDate . " 00:00:01', INTERVAL " . $daysBack . " DAY) and nd.timestamp_updated <= '" . $startingDate . " 23:59:59' and nd.timestamp_updated > nd.timestamp_created)
			";
		}

		$changeQuery = DAO_CFactory::create('menu_item');
		$changeQuery->query("SELECT 
    		iq.*,
			u.firstname,
			u.lastname,
			u.user_type
			FROM (
				SELECT 
				mtmi.menu_id,
				mi.id AS menu_item_id,
				mi.entree_id,
				mi.recipe_id,
				mi.menu_item_name,
				mi.menu_item_category_id,
				mi.pricing_type,
				m.menu_name,
				# get the latest updated time from all the tables
				GREATEST(mi.timestamp_updated, r.timestamp_updated, rc.timestamp_updated, nd.timestamp_updated) AS timestamp_updated,
				# get the first non-null user_id, likely the person who updated the recipe
				COALESCE(mi.updated_by, r.updated_by, rc.updated_by, nd.updated_by) AS updated_by
				FROM menu_to_menu_item AS mtmi
				INNER JOIN menu AS m ON m.id = mtmi.menu_id AND m.is_deleted = 0
				INNER JOIN menu_item AS mi ON mi.id = mtmi.menu_item_id and mi.is_deleted = 0 AND mi.updated_by IS NOT NULL
				INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id and r.override_menu_id = mtmi.menu_id and r.is_deleted = 0
				INNER JOIN recipe_component AS rc ON rc.recipe_id = r.id and rc.is_deleted = 0
				LEFT JOIN nutrition_data AS nd ON rc.id = nd.component_id and nd.is_deleted = 0
				WHERE mtmi.store_id = '" . $store_id . "'
				AND mtmi.is_deleted = 0
				AND (" . $intervalQuery . ") 
				GROUP BY menu_item_id
			) AS iq
			INNER JOIN `user` AS u ON u.id = iq.updated_by
			ORDER BY timestamp_updated DESC, iq.entree_id ASC, iq.pricing_type ASC");

		while ($changeQuery->fetch())
		{
			$retVal[] = array(
				'type' => 'RECIPE_UPDATED',
				'time' => $changeQuery->timestamp_updated,
				'user_id' => $changeQuery->updated_by,
				'firstname' => $changeQuery->firstname,
				'lastname' => $changeQuery->lastname,
				'menu_id' => $changeQuery->menu_id,
				'menu_name' => $changeQuery->menu_name,
				'menu_name_abbr' => $changeQuery->menu_name_abbr,
				'menu_item_category_id' => $changeQuery->menu_item_category_id,
				'is_store_special' => $changeQuery->is_store_special,
				'pricing_type_info' => $changeQuery->pricing_type_info,
				'user_type' => CUser::userTypeText($changeQuery->user_type),
				'menu_item_name' => $changeQuery->menu_item_name,
				'recipe_id' => $changeQuery->recipe_id,
				'action' => 'Recipe Updated'
			);
		}

		return $retVal;
	}

	static function getFullMenuItemsAndMenuInfo($DAO_store, $menu_id, $currentItemsInCart, $bundle_id = false, $inItemList = false, $join_bundle_to_menu_item = 'LEFT')
	{
		$retrieveFavorites = false;
		if (CUser::isLoggedIn())
		{
			$retrieveFavorites = CUser::getCurrentUser()->id;
		}

		$menuInfo = array();

		//fetch the menu for the month
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		if (!$DAO_menu->find(true))
		{
			throw new exception('error: menu not found');
		}

		// by default attach TV_OFFER
		if (!$bundle_id)
		{
			$DAO_bundle = CBundle::getActiveBundleForMenu($menu_id, $DAO_store);
			if ($DAO_bundle)
			{
				$bundle_id = $DAO_bundle->id;
			}
		}

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => (!empty($DAO_store)) ? $DAO_store->id : 'NULL',
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'join_food_survey_user_id' => $retrieveFavorites,
			'join_bundle_to_menu_item_bundle_id' => $bundle_id,
			'join_bundle_to_menu_item' => $join_bundle_to_menu_item,
			'menu_item_id_list' => $inItemList
		));

		$menuItemInfo = array();
		$menuItemCategoryInfo = array(
			'num_visible' => array(
				1 => 0,
				2 => 0,
				3 => 0
			)
		);

		$markup = $DAO_store->getMarkUpMultiObj($DAO_menu->id);

		$bundleParents = array();
		$sideStationBundleInfo = array();

		while ($DAO_menu_item->fetch())
		{
			$DAO_menu_item->digestMenuItem($DAO_store, $markup);

			$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

			if ($DAO_menu_item->isVisible())
			{
				$menuItemCategoryInfo['num_visible'][$DAO_menu_item->category_group_id]++;
			}

			if (!empty($currentItemsInCart['mbundleitems']))
			{
				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->qty_in_cart = array_key_exists($DAO_menu_item->id, $currentItemsInCart['mbundleitems']) ? $currentItemsInCart['mbundleitems'][$DAO_menu_item->id] : 0;
			}

			if (!empty($currentItemsInCart['mitems']))
			{
				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->qty_in_cart = array_key_exists($DAO_menu_item->id, $currentItemsInCart['mitems']) ? $currentItemsInCart['mitems'][$DAO_menu_item->id] : 0;
			}

			if (empty($currentItemsInCart['mbundleitems']) && empty($currentItemsInCart['mitems']))
			{
				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->qty_in_cart = 0;
			}

			// Make adjustments for MASTER_ITEM style bundle
			if ($DAO_menu_item->is_bundle)
			{
				$subItems = CBundle::getBundleMenuInfoForMenuItem($DAO_menu_item->id, $DAO_menu_item->menu_id, $DAO_store->id);
				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->sub_items = array();
				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->number_items_required = $subItems['number_items_required'];

				$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->bundle_groups = $subItems['bundle_groups'];

				if ($DAO_menu_item->isVisible())
				{
					$bundleParents[$DAO_menu_item->id] = $DAO_menu_item->entree_id;
				}

				$sideStationBundleInfo[$DAO_menu_item->id] = $subItems;

				$totalSubItemInventory = 0;
				$hasFixedOutOfStock = false;

				foreach ($subItems['bundle'] as $sid => $subItemInfo)
				{
					$totalSubItemInventory += ($subItemInfo['override_inventory'] - $subItemInfo['number_sold']);

					// addSideStation ..this is for storing inventory numbers on the page for items not drawn in the main menu loop
					if (isset($flatList) && !$flatList[$sid]['is_visible'])
					{
						$addStationInfo[$sid] = $flatList[$sid];
					}

					if (isset($currentItemsInCart['mBundleMap'][$DAO_menu_item->id]))
					{
						$subItemInfo['qty_in_cart'] = array_key_exists($sid, $currentItemsInCart['mBundleMap'][$DAO_menu_item->id]) ? $currentItemsInCart['mBundleMap'][$DAO_menu_item->id][$sid] : 0;
					}
					else
					{
						$subItemInfo['qty_in_cart'] = 0;
					}

					if (!$hasFixedOutOfStock && !empty($subItemInfo['fixed_quantity']))
					{
						if (($subItemInfo['fixed_quantity'] * $subItemInfo['servings_per_item']) > $subItemInfo['remaining_servings'])
						{
							$hasFixedOutOfStock = true;
						}
					}

					$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->sub_items[$sid] = $subItemInfo;
				}

				// Note: assumes 1 serving per sub item
				if ($hasFixedOutOfStock || (isset($subItems['number_items_required']) && $totalSubItemInventory < $subItems['number_items_required']))
				{
					$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->override_inventory = 0;
					$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->number_sold = 0;
					$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->this_type_out_of_stock = true;
					$menuItemInfo[$DAO_menu_item->entree_id][$DAO_menu_item->id]->out_of_stock = true;
				}
			}
		}

		// adjust item counts of any bundle items also found on the flat menu
		foreach ($bundleParents as $id => $entree_id)
		{
			if (!empty($menuItemInfo[$entree_id][$id]->sub_items))
			{
				foreach ($menuItemInfo[$entree_id][$id]->sub_items as $sid => $sinfo)
				{
					if (isset($menuItemInfo[$sinfo['entree_id']][$sid]) && isset($sinfo['qty_in_cart']))
					{
						$menuItemInfo[$sinfo['entree_id']][$sid]->qty_in_cart -= $sinfo['qty_in_cart'];
					}
				}
			}
		}

		$beginTime = null;
		$endTime = null;
		$DAO_menu->getValidMenuRange($beginTime, $endTime);
		$menuInfo['begin_day'] = date("M jS", $beginTime);
		$menuInfo['end_day'] = date("M jS", $endTime);
		$menuInfo['store_id'] = $DAO_store->id;
		$menuInfo['menu_id'] = $DAO_menu->id;
		$menuInfo['menu_name'] = $DAO_menu->menu_name;
		$menuInfo['menu_month'] = strftime('%B', strtotime($DAO_menu->menu_start));
		$menuInfo['volume_discount_amount'] = COrders::getVolumeDiscountAmount($DAO_menu->id, $markup);

		/* info for javascript to use */
		$menuItemInfoByMID = array();
		foreach ($menuItemInfo as $menuItem)
		{
			$menuItemInfoByMID = self::recipeReferenceArray($menuItemInfoByMID, $menuItem);
		}

		return array(
			'menuInfo' => $menuInfo,
			'menuItemInfo' => $menuItemInfo,
			'menuItemInfoByMID' => $menuItemInfoByMID,
			'menuItemCategoryInfo' => $menuItemCategoryInfo
		);
	}

	function showOnOrderForm()
	{
		if (!empty($this->show_on_order_form))
		{
			return true;
		}

		return false;
	}

	function showOnPickSheet()
	{
		if (!empty($this->show_on_pick_sheet))
		{
			return true;
		}

		return false;
	}

	function hasAvailableInventory()
	{
		if ($this->remaining_servings < $this->servings_per_item)
		{
			return false;
		}

		return true;
	}

	/**
	 *
	 *    Utility function to see if store has additional, non-core items
	 *  to offer for sale in a given menu
	 *
	 * @param $storeObj
	 * @param $menu_id
	 *
	 * @return bool
	 * @throws exception
	 */
	static function hasFreezerInventory($storeObj, $menu_id)
	{
		$excludeMenuAddons = false;
		$excludeCoreMenuItems = true;
		$excludeChefTouchedSelections = false;
		$excludeStoreSpecialsIfMenuIsGlobal = false;

		//fetch the menu for the month
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$cnt = $daoMenu->find(true);
		if ($cnt == 0)
		{
			throw new exception('error: menu not found');
		}

		$daoMenuItem = $daoMenu->getMenuItemDAO('FeaturedFirst', $storeObj->id, false, false, $excludeMenuAddons, $excludeChefTouchedSelections, $excludeStoreSpecialsIfMenuIsGlobal, false, false, $excludeCoreMenuItems); //returns the associated menu item query

		$count = 0;
		while ($daoMenuItem->fetch())
		{
			if (isset($daoMenuItem->is_visible) && $daoMenuItem->is_visible)
			{
				$initial_inventory = isset($daoMenuItem->initial_inventory) ? $daoMenuItem->initial_inventory : 9999;
				$override_inventory = isset($daoMenuItem->override_inventory) ? $daoMenuItem->override_inventory : 9999;
				$number_sold = isset($daoMenuItem->number_sold) ? $daoMenuItem->number_sold : 0;
				$remaining_servings = $override_inventory - $number_sold;

				if ($remaining_servings > 0)
				{
					$count++;
					break;
				}
			}
		}

		return $count > 0;
	}

	/**
	 *
	 *    Utility function to distill which pricing_types are avialable
	 * given array of array(
	 *                        paren_item_id_1[  item_id_1[item_info],item_id_2[item_info],..  ],
	 *                    paren_item_id_2[  item_id_1[item_info],item_id_2[item_info],..  ],
	 *                        ...
	 *                    )
	 *
	 * @param $sizesAvailable array of arrays containing menu item information
	 *
	 * @return array of unique pricing_types
	 */
	static function availableSizes($sizesAvailable)
	{
		$available = array();
		foreach ($sizesAvailable as $items)
		{
			foreach ($items as $subitem)
			{
				if (!in_array($subitem->pricing_type, $available))
				{
					$available[] = $subitem->pricing_type;
				}
			}
		}

		return $available;
	}

	function allowsMealCustomization($storeObj)
	{

		if (!empty($storeObj) && $storeObj->supports_meal_customization)
		{
			if ($this->isMenuItem_Core())
			{
				if ($this->isMenuItem_Core_Assemble())
				{
					return true;
				}
				if ($this->isMenuItem_Core_Preassembled() && $storeObj->allow_preassembled_customization)
				{
					return true;
				}
			}
		}

		return false;
	}

	function menuItemImagePath()
	{
		return empty($this->menu_image_override) ? 'default' : $this->menu_image_override;
	}

	function categoryGroupName()
	{
		switch ($this->categoryGroup())
		{
			case CMenuItem::SIDE:
				return 'Sides & Sweets';
			case CMenuItem::EXTENDED:
				return 'Extended Fast Lane';
			case CMenuItem::CORE:
				return 'Core';
			default:
				return null;
		}
	}

	function categoryGroup()
	{
		if ($this->isMenuItem_Core())
		{
			return CMenuItem::CORE;
		}

		if ($this->isMenuItem_EFL())
		{
			return CMenuItem::EXTENDED;
		}

		if ($this->isMenuItem_SidesSweets())
		{
			return CMenuItem::SIDE;
		}

		return false;
	}

	function categoryGroupId()
	{
		if ($this->isMenuItem_Core())
		{
			return 1;
		}

		if ($this->isMenuItem_EFL())
		{
			return 2;
		}

		if ($this->isMenuItem_SidesSweets())
		{
			return 3;
		}

		return false;
	}

	function isLimitedQuantities()
	{
		if ($this->isMenuItem_SidesSweets())
		{
			return ($this->getRemainingServings() < 5);
		}
		else
		{
			return ($this->getRemainingServings() < 18);
		}
	}

	function isOutOfStock()
	{
		if ($this->isMenuItem_SidesSweets())
		{
			return ($this->getRemainingServings() < 1);
		}
		else
		{
			return ($this->getRemainingServings() < 3);
		}
	}

	function isOutOfStock_ThisType()
	{
		return !$this->hasAvailableInventory();
	}

	function isBundle()
	{
		if (!empty($this->is_bundle))
		{
			return true;
		}

		return false;
	}

	function isFeatured()
	{
		if (!empty($this->featuredItem))
		{
			return true;
		}

		return false;
	}

	function isInBundle($DAO_bundle)
	{
		if (!empty($this->DAO_bundle) && $this->DAO_bundle->id == $DAO_bundle->id)
		{
			return true;
		}

		if (!empty($this->DAO_bundle) && !empty($this->in_bundle))
		{
			foreach ($this->in_bundle as $in_DAO_bundle)
			{
				if ($in_DAO_bundle->id == $DAO_bundle->id)
				{
					return true;
				}
			}
		}

		return false;
	}

	function isFreezer()
	{
		if ($this->isMenuItem_EFL() || $this->isMenuItem_SidesSweets())
		{
			return true;
		}

		return false;
	}

	function isHiddenEverywhere()
	{
		if(!empty($this->DAO_menu_to_menu_item))
		{
			return $this->DAO_menu_to_menu_item->isHiddenEverywhere();
		}
		else if (!empty($this->is_hidden_everywhere))
		{
			return true;
		}

		return false;
	}

	function isShowOnOrderForm()
	{
		if (!empty($this->show_on_order_form))
		{
			return true;
		}

		return false;
	}

	function isShowOnPickSheet()
	{
		if (!empty($this->show_on_pick_sheet))
		{
			return true;
		}

		return false;
	}

	function isVisibleAndNotHiddenEverywhere()
	{
		if ($this->isVisible() && !$this->isHiddenEverywhere())
		{
			return true;
		}

		return false;
	}

	function isVisible()
	{
		if (!empty($this->is_visible))
		{
			return true;
		}

		return false;
	}

	function isMenuItem_Core()
	{
		if ($this->menu_item_category_id == 1 || ($this->menu_item_category_id == 4 && empty($this->is_store_special)))
		{
			return true;
		}

		return false;
	}

	function isMenuItem_Core_Assemble()
	{
		if ($this->menu_item_category_id == 1)
		{
			return true;
		}

		return false;
	}

	function isMenuItem_Core_Preassembled()
	{
		if ($this->menu_item_category_id == 4 && empty($this->is_store_special))
		{
			return true;
		}

		return false;
	}

	function isMenuItem_EFL()
	{
		if ($this->menu_item_category_id == 4 && !empty($this->is_store_special))
		{
			return true;
		}

		return false;
	}

	function isMenuItem_SidesSweets()
	{
		if ($this->menu_item_category_id == 9)
		{
			return true;
		}

		return false;
	}

	static function recipeReferenceArray($menuItemInfoByMID, $itemArray)
	{
		foreach ($itemArray as $item)
		{
			$menuItemInfoByMID['entree'][$item->entree_id][$item->id] = $item->pricing_type;
			$menuItemInfoByMID['recipe'][$item->recipe_id][$item->id] = $item->pricing_type;

			$midArray = array(
				'menu_item_id' => $item->id,
				'entree_id' => $item->entree_id,
				'menu_item_name' => $item->menu_item_name,
				'recipe_id' => $item->recipe_id,
				'parent_item' => (!empty($item->parent_item) ? $item->parent_item : null),
				'category_id' => $item->category_id,
				'qty_in_cart' => $item->qty_in_cart,
				'remaining_servings' => $item->remaining_servings,
				'servings_per_item' => $item->servings_per_item,
				'pricing_type' => $item->pricing_type,
				'pricing_type_info' => (!empty($item->pricing_type_info) ? $item->pricing_type_info : null),
				'price' => $item->store_price,
				'is_bundle' => $item->is_bundle,
				'number_items_required' => (!empty($item->number_items_required) ? $item->number_items_required : null),
				'item_count_per_item' => $item->item_count_per_item,
				'item_contributes_to_minimum_order' => ($item->category_id < 5 and $item->is_store_special == 0),
				'bundle_to_menu_item_group_id' => (!empty($item->bundle_to_menu_item_group_id) ? $item->bundle_to_menu_item_group_id : false)
			);

			if (!empty($item->parent_item))
			{
				$menuItemInfoByMID['mid'][$item->parent_item]['sub_item'][$item->id] = $midArray;

				if (empty($menuItemInfoByMID['mid'][$item->id]))
				{
					$menuItemInfoByMID['mid'][$item->id] = $midArray;
				}
			}
			else if (empty($item->parent_item) && !empty($item->is_bundle) && empty($item->bundle_groups))
			{
				$menuItemInfoByMID['mid'][$item->id] = $midArray;
				$menuItemInfoByMID['mid'][$item->id]['bundle_groups'] = array();

				foreach ($item->sub_items as $subid => $sub_midArray)
				{
					$menuItemInfoByMID['mid'][$item->id]['sub_item'][$subid] = array(
						'menu_item_id' => $sub_midArray['id'],
						'entree_id' => $sub_midArray['entree_id'],
						'menu_item_name' => $sub_midArray['menu_item_name'],
						'recipe_id' => $sub_midArray['recipe_id'],
						'parent_item' => $sub_midArray['parent_item'],
						'category_id' => $sub_midArray['category_id'],
						'qty_in_cart' => $sub_midArray['qty_in_cart'],
						'remaining_servings' => $sub_midArray['remaining_servings'],
						'servings_per_item' => $sub_midArray['servings_per_item'],
						'pricing_type' => $sub_midArray['pricing_type'],
						'pricing_type_info' => (array_key_exists('pricing_type_info', $sub_midArray) ? $sub_midArray['pricing_type_info'] : null),
						'price' => $sub_midArray['price'],
						'is_bundle' => $sub_midArray['is_bundle'],
						'item_count_per_item' => $item->item_count_per_item,
						'number_items_required' => $sub_midArray['number_items_required'],
						'item_contributes_to_minimum_order' => ($sub_midArray['category_id'] < 5 and $sub_midArray['is_store_special'] == 0),
						'bundle_to_menu_item_group_id' => (!empty($sub_midArray['bundle_to_menu_item_group_id']) ? $sub_midArray['bundle_to_menu_item_group_id'] : false)
					);
				}
			}
			else
			{
				$menuItemInfoByMID['mid'][$item->id] = $midArray;
			}

			if (!empty($item->is_bundle) && !empty($item->bundle_groups))
			{
				$menuItemInfoByMID = self::recipeReferenceArray($menuItemInfoByMID, $item->sub_items);

				$menuItemInfoByMID['mid'][$item->id]['bundle_groups'] = array();

				foreach ($item->bundle_groups as $bundle_group)
				{
					$menuItemInfoByMID['mid'][$item->id]['bundle_groups'][$bundle_group->id]['group_title'] = $bundle_group->group_title;
					$menuItemInfoByMID['mid'][$item->id]['bundle_groups'][$bundle_group->id]['number_items_required'] = $bundle_group->number_items_required;
					$menuItemInfoByMID['mid'][$item->id]['bundle_groups'][$bundle_group->id]['number_servings_required'] = $bundle_group->number_servings_required;
				}
			}
		}

		return $menuItemInfoByMID;
	}

	/* returns a menu_item DAO object if it exists
	 * if a store specific menu exists it returns the override price in ->store_price
	 * otherwise the base price plus markup is returned in ->store_price
	 */
	static public function getStoreSpecificItem($storeDAO, $menu_item_id)
	{
		if (is_numeric($storeDAO))
		{
			$store_id = $storeDAO;

			$storeDAO = DAO_CFactory::create('store');
			$storeDAO->id = $store_id;
			$storeDAO->find(true);
		}

		// Note: assumes that a given menu_item id occurs only per menu per store
		$menuItemDAO = DAO_CFactory::create('menu_item');
		$menuItemDAO->query("select mi.* , mmi.override_price, mmi.menu_id from menu_item mi " . " join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id = " . $storeDAO->id . " where mi.id = $menu_item_id ");
		if ($menuItemDAO->N > 0) // store version exists
		{
			$menuItemDAO->fetch();
			if (isset($menuItemDAO->override_price))
			{
				$menuItemDAO->store_price = $menuItemDAO->override_price;
			}
			else
			{
				$markup = $storeDAO->getMarkupMultiObj($menuItemDAO->menu_id);
				$menuItemDAO->store_price = COrders::getItemMarkupMultiSubtotal($markup, $menuItemDAO, 1);
			}
		}
		else
		{
			unset($menuItemDAO);
			$menuItemDAO = DAO_CFactory::create('menu_item');
			$menuItemDAO->query("select mi.* , mmi.override_price, mmi.menu_id from menu_item mi " . " join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id is null " . " where mi.id = $menu_item_id ");
			if ($menuItemDAO->N > 0)
			{
				$menuItemDAO->fetch();
				$markup = $storeDAO->getMarkupMultiObj($menuItemDAO->menu_id);
				$menuItemDAO->store_price = COrders::getItemMarkupMultiSubtotal($markup, $menuItemDAO, 1);
			}
		}

		return $menuItemDAO;
	}

	static function myCharConversions($inStr)
	{
		return trim(mb_convert_encoding($inStr, 'HTML-ENTITIES', 'UTF-8'));
	}

	static function formatDecimal($inStr)
	{
		if (intval($inStr) == floatval($inStr))
		{
			return intval($inStr);
		}
		if (strpos($inStr, ".") !== false)
		{
			$inStr = trim($inStr, "0");
		}

		return $inStr;
	}

	static function recipeIsInMenu($menu_id, $recipe_id, $store_id)
	{
		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->recipe_id = $recipe_id;
		$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
		$DAO_menu_to_menu_item->store_id = $store_id;
		$DAO_menu_to_menu_item->menu_id = $menu_id;
		$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);
		$DAO_menu_item->find();

		if ($DAO_menu_item->N > 0)
		{
			return true;
		}

		return false;
	}

	static function getInfoForEFLCandidate($recipe_id, $menu_id, $store_id)
	{
		$retval = array(
			'card_data' => array(),
			'pantry_data' => array(),
			'sales_data' => array(),
			'item_detail' => array()
		);

		/*
		 * Note this was a Proof of Concept. Defeated for now until we have the Food Dev data support
		 *
		 *
		$cardData = DAO_CFactory::create('rm_recipes');
		$cardData->query("select r.* from rm_recipes r where r.Recipe_ID = $recipe_id order by Recipe_Order");
		while($cardData->fetch())
		{
			$retval['card_data'][$cardData->Recipe_Order] = array("Desc" => $cardData->Recipe_Card_Description, 'Qty' => $cardData->Recipe_Card_Qty, 'UOM' => $cardData->Recipe_Card_UOM);
		}
		*/

		if (is_array($store_id))
		{
			$storeClause = " and s.store_id in (" . implode(",", $store_id) . ") ";
		}
		else
		{
			$storeClause = " and s.store_id = $store_id";
		}

		$Menus = new DAO();
		$Menus->query("select m.id, m.menu_name from menu m where m.id > $menu_id - 39 order by m.id desc");
		while ($Menus->fetch())
		{
			$retval['sales_data'][$Menus->id] = array(
				'month_name' => $Menus->menu_name,
				'sales' => array(
					'num_sold' => 0,
					'price_full' => 0,
					'price_half' => 0,
					'price_two' => 0,
					'price_four' => 0
				)
			);
		}

		$QObj = new DAO();
		$QObj->query("select
							m.id,
							m.menu_name,
							sum(oi.item_count) as total_sold,
							sum(if(mi.pricing_type = 'FULL', oi.item_count, null)) as qty_full,
							sum(if(mi.pricing_type = 'HALF', oi.item_count, null)) as qty_half,
							sum(if(mi.pricing_type = 'FULL', oi.sub_total, null)) as sales_full,
							sum(if(mi.pricing_type = 'HALF', oi.sub_total, null)) as sales_half,
       						sum(if(mi.pricing_type = 'FOUR', oi.item_count, null)) as qty_four,
							sum(if(mi.pricing_type = 'TWO', oi.item_count, null)) as qty_two,
							sum(if(mi.pricing_type = 'FOUR', oi.sub_total, null)) as sales_four,
							sum(if(mi.pricing_type = 'TWO', oi.sub_total, null)) as sales_two
							from menu m
							left join session s on s.menu_id = m.id and s.is_deleted = 0 and s.session_publish_state <> 'SAVED' $storeClause
							join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
							join orders o on o.id = b.order_id and o.is_deleted = 0
							join order_item oi on oi.order_id = b.order_id
							and oi.is_deleted = 0
							join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = $recipe_id
							where m.id > $menu_id - 39
							group by m.id
							order by m.id desc");

		while ($QObj->fetch())
		{
			$qtyfull = $QObj->qty_full;
			$qtyhalf = $QObj->qty_half;
			$qtyfour = $QObj->qty_four;
			$qtytwo = $QObj->qty_two;

			$priceHalf = ($qtyhalf != 0 ? CTemplate::moneyFormat($QObj->sales_half / $qtyhalf) : "0.00");
			$priceFull = ($qtyfull != 0 ? CTemplate::moneyFormat($QObj->sales_full / $qtyfull) : "0:00");
			$priceFour = ($qtyfour != 0 ? CTemplate::moneyFormat($QObj->sales_four / $qtyfour) : "0.00");
			$priceTwo = ($qtytwo != 0 ? CTemplate::moneyFormat($QObj->sales_two / $qtytwo) : "0:00");

			$retval['sales_data'][$QObj->id]['sales'] = array(
				'num_sold' => $QObj->total_sold,
				'num_med_sold' => $qtyhalf,
				'num_lrg_sold' => $qtyfull,
				'price_full' => $priceFull,
				'price_half' => $priceHalf,
				'num_four_sold' => $qtyfour,
				'num_two_sold' => $qtytwo,
				'price_four' => $priceFour,
				'price_two' => $priceTwo
			);
		}

		$retval['item_detail'] = self::getItemDetailGeneric($recipe_id);

		return $retval;
	}

	function getNutritionArray()
	{
		$DAO_recipe = DAO_CFactory::create('recipe');
		$DAO_recipe->recipe_id = $this->recipe_id;
		$DAO_recipe->override_menu_id = $this->menu_id;

		$DAO_nutrition_element = DAO_CFactory::create('nutrition_element');
		$DAO_nutrition_element->do_display = 1;

		$DAO_nutrition_data = DAO_CFactory::create('nutrition_data');
		$DAO_nutrition_data->joinAddWhereAsOn($DAO_nutrition_element);

		$DAO_recipe_component = DAO_CFactory::create('recipe_component');
		$DAO_recipe_component->joinAddWhereAsOn($DAO_nutrition_data);

		$DAO_recipe->joinAddWhereAsOn($DAO_recipe_component);
		$DAO_recipe->orderBy('nutrition_element.display_order');

		$DAO_recipe->find();

		$this->nutrition_array = array();

		while ($DAO_recipe->fetch())
		{
			if (!isset($this->nutrition_array['component'][$DAO_recipe->component_number]))
			{
				$this->nutrition_array['component'][$DAO_recipe->component_number]['info'] = array(
					'serving' => $DAO_recipe->serving,
					'notes' => $DAO_recipe->notes
				);
			}

			$this->nutrition_array['component'][$DAO_recipe->component_number]['element'][$DAO_recipe->label] = array(
				'label' => $DAO_recipe->label,
				'display_label' => $DAO_recipe->display_label,
				'value' => $DAO_recipe->prefix . (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label),
				'percent_daily_value' => (!empty($DAO_recipe->daily_value) ? round(($DAO_recipe->value / $DAO_recipe->daily_value) * 100) : false),
				'note_indicator' => $DAO_recipe->note_indicator,
				'parent_element' => $DAO_recipe->parent_element,
				'indent' => $DAO_recipe->indent,
				'sprintf_label' => (!empty($DAO_recipe->sprintf) ? sprintf($DAO_recipe->display_label, (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label)) : false)
			);
		}
	}

	static function getItemDetailGeneric($recipe_id, $override_menu = false)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		if (!$override_menu)
		{
			// find the latest menu this recipe was on and use it as the menu id
			$DAO_menu_item = DAO_CFactory::create("menu_item");
			$DAO_menu_item->recipe_id = $recipe_id;
			$DAO_menu_item->copied_from = 'NULL';
			$DAO_menu_to_menu_item = DAO_CFactory::create("menu_to_menu_item");
			$DAO_menu_to_menu_item->store_id = 'NULL';
			$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_menu_item->selectAdd("MAX(menu_to_menu_item.menu_id) AS latest_menu_id");
			$DAO_menu_item->find(true);

			$override_menu = $DAO_menu_item->latest_menu_id;
		}

		$DAO_menu->id = $override_menu;

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_item_recipe_id_list' => $recipe_id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
		));

		$retVal = array();
		// get entrees

		while ($DAO_menu_item->fetch())
		{
			$retVal['entree_id'] = $DAO_menu_item->entree_id;
			$retVal[$DAO_menu_item->entree_id][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();
		}

		$DAO_recipe = DAO_CFactory::create('recipe');
		$DAO_recipe->recipe_id = $DAO_menu_item->recipe_id;
		$DAO_recipe->override_menu_id = $override_menu;
		$DAO_recipe_component = DAO_CFactory::create('recipe_component');
		$DAO_nutrition_data = DAO_CFactory::create('nutrition_data');
		$DAO_nutrition_element = DAO_CFactory::create('nutrition_element');
		$DAO_nutrition_element->do_display = 1;
		$DAO_nutrition_data->joinAddWhereAsOn($DAO_nutrition_element);
		$DAO_recipe_component->joinAddWhereAsOn($DAO_nutrition_data);
		$DAO_recipe->joinAddWhereAsOn($DAO_recipe_component);
		$DAO_recipe->orderBy("recipe.override_menu_id DESC, nutrition_element.display_order");
		$DAO_recipe->find();

		$NutsArray = array();
		$menu = null;

		while ($DAO_recipe->fetch())
		{
			if (!is_null($menu) && $menu != $DAO_recipe->override_menu_id)
			{
				break;
			}
			$menu = $DAO_recipe->override_menu_id;

			if (!isset($NutsArray[$DAO_recipe->component_number]))
			{
				$NutsArray[$DAO_recipe->component_number]['info'] = array(
					'serving' => $DAO_recipe->serving,
					'notes' => $DAO_recipe->notes
				);
			}

			$NutsArray[$DAO_recipe->component_number]['element'][$DAO_recipe->label] = array(
				'label' => $DAO_recipe->label,
				'display_label' => $DAO_recipe->display_label,
				'value' => $DAO_recipe->prefix . (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label),
				'percent_daily_value' => (!empty($DAO_recipe->daily_value) ? round(($DAO_recipe->value / $DAO_recipe->daily_value) * 100) : false),
				'note_indicator' => $DAO_recipe->note_indicator,
				'parent_element' => $DAO_recipe->parent_element,
				'indent' => $DAO_recipe->indent,
				'sprintf_label' => (!empty($DAO_recipe->sprintf) ? sprintf($DAO_recipe->display_label, (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label)) : false)
			);
		}

		$retVal['nutrition_data'] = $NutsArray;

		foreach ($retVal[$retVal['entree_id']] as $cookingInstructionsObj)
		{
			$retVal['cooking_inst']['has_cooking_inst'] = true;

			$retVal['cooking_inst']['suggestions'] = self::myCharConversions($cookingInstructionsObj->serving_suggestions);
			$retVal['cooking_inst']['best_prepared_by'] = self::myCharConversions($cookingInstructionsObj->best_prepared_by);

			switch ($cookingInstructionsObj->pricing_type)
			{
				case CMenuItem::FULL:
					$retVal['cooking_inst']['full'] = self::myCharConversions($cookingInstructionsObj->instructions);
					$retVal['cooking_inst']['prep_time_full'] = self::myCharConversions($cookingInstructionsObj->prep_time);
					break;
				case CMenuItem::FOUR:
					$retVal['cooking_inst']['four'] = self::myCharConversions($cookingInstructionsObj->instructions);
					$retVal['cooking_inst']['prep_time_four'] = self::myCharConversions($cookingInstructionsObj->prep_time);
					break;
				case CMenuItem::HALF:
					$retVal['cooking_inst']['half'] = self::myCharConversions($cookingInstructionsObj->instructions);
					$retVal['cooking_inst']['prep_time_half'] = self::myCharConversions($cookingInstructionsObj->prep_time);
					break;
				case CMenuItem::TWO:
					$retVal['cooking_inst']['two'] = self::myCharConversions($cookingInstructionsObj->instructions);
					$retVal['cooking_inst']['prep_time_two'] = self::myCharConversions($cookingInstructionsObj->prep_time);
					break;
			}
		}

		$retVal['show_cooking'] = false;
		$retVal['show_nutritionals'] = false;
		$retVal['show_recipe_note'] = false;

		if ($DAO_menu_item->DAO_recipe->show_cooking && $retVal['cooking_inst']['has_cooking_inst'])
		{
			$retVal['show_cooking'] = true;
		}

		if ($DAO_menu_item->DAO_recipe->show_nutritionals && !empty($retVal['nutrition_data']))
		{
			$retVal['show_nutritionals'] = true;
		}

		if ($DAO_menu_item->DAO_recipe->show_recipe_note && !empty($DAO_menu_item->recipe_note))
		{
			$retVal['recipe_note'] = $DAO_menu_item->recipe_note;
			$retVal['show_recipe_note'] = true;
		}

		return $retVal;
	}

	static function getItemDetailGenericNew($recipe_id, $override_menu = false)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		if (!$override_menu)
		{
			// find the latest menu this recipe was on and use it as the menu id
			$DAO_menu_item = DAO_CFactory::create("menu_item");
			$DAO_menu_item->recipe_id = $recipe_id;
			$DAO_menu_item->copied_from = 'NULL';
			$DAO_menu_to_menu_item = DAO_CFactory::create("menu_to_menu_item");
			$DAO_menu_to_menu_item->store_id = 'NULL';
			$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);
			$DAO_menu_item->selectAdd("MAX(menu_to_menu_item.menu_id) AS latest_menu_id");
			$DAO_menu_item->find(true);

			$override_menu = $DAO_menu_item->latest_menu_id;
		}

		$DAO_menu->id = $override_menu;

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_item_recipe_id_list' => $recipe_id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
		));

		$retVal = array(
			'entree' => null,
			'menu_item' => array()
		);
		// get entrees

		if (!empty($DAO_menu_item->N))
		{
			while ($DAO_menu_item->fetch())
			{
				if (empty($menuItems[$DAO_menu_item->recipe_id]['entree']) || $DAO_menu_item->id == $DAO_menu_item->entree_id)
				{
					$retVal['entree'] = $DAO_menu_item->cloneObj();
				}

				$retVal['menu_item'][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();
			}

			$DAO_recipe = DAO_CFactory::create('recipe');
			$DAO_recipe->recipe_id = $DAO_menu_item->recipe_id;
			$DAO_recipe->override_menu_id = $override_menu;
			$DAO_recipe_component = DAO_CFactory::create('recipe_component');
			$DAO_nutrition_data = DAO_CFactory::create('nutrition_data');
			$DAO_nutrition_element = DAO_CFactory::create('nutrition_element');
			$DAO_nutrition_element->do_display = 1;
			$DAO_nutrition_data->joinAddWhereAsOn($DAO_nutrition_element);
			$DAO_recipe_component->joinAddWhereAsOn($DAO_nutrition_data);
			$DAO_recipe->joinAddWhereAsOn($DAO_recipe_component);
			$DAO_recipe->orderBy("recipe.override_menu_id DESC, nutrition_element.display_order");
			$DAO_recipe->find();

			$NutsArray = array();
			$menu = null;

			while ($DAO_recipe->fetch())
			{
				if (!is_null($menu) && $menu != $DAO_recipe->override_menu_id)
				{
					break;
				}
				$menu = $DAO_recipe->override_menu_id;

				if (!isset($NutsArray[$DAO_recipe->component_number]))
				{
					$NutsArray[$DAO_recipe->component_number]['info'] = array(
						'serving' => $DAO_recipe->serving,
						'notes' => $DAO_recipe->notes
					);
				}

				$NutsArray[$DAO_recipe->component_number]['element'][$DAO_recipe->label] = array(
					'label' => $DAO_recipe->label,
					'display_label' => $DAO_recipe->display_label,
					'value' => $DAO_recipe->prefix . (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label),
					'percent_daily_value' => (!empty($DAO_recipe->daily_value) ? round(($DAO_recipe->value / $DAO_recipe->daily_value) * 100) : false),
					'note_indicator' => $DAO_recipe->note_indicator,
					'parent_element' => $DAO_recipe->parent_element,
					'indent' => $DAO_recipe->indent,
					'sprintf_label' => (!empty($DAO_recipe->sprintf) ? sprintf($DAO_recipe->display_label, (self::formatDecimal($DAO_recipe->value) . $DAO_recipe->measure_label)) : false)
				);
			}

			$retVal['nutrition_data'] = $NutsArray;
		}
		else
		{
			return false;
		}

		return $retVal;
	}
}

?>