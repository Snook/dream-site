<?php
require_once 'DAO/Recipe.php';

class CRecipe extends DAO_Recipe
{
	/**
	 * @var array
	 */
	public $icons;

	const MENU_CUTOFF_NO_ADDED_SALT = 270;

	/**
	 * @var array[]
	 *
	 * Main attributes for meal icons.
	 * Place in the order in which you want them to display.
	 *
	 */
	static function getIconSchematic($DAO_menu = false)
	{
		return array(
			'flag_grill_friendly' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-grill text-gray-dark',
				'png_icon' => 'menu-icon02.png',
				'tooltip' => 'Grill Option',
				'label' => 'Grill Option'
			),
			'air_fryer' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-air-fryer text-gray-dark',
				'png_icon' => 'icon-air-fryer.png',
				'tooltip' => 'Air Fryer Option',
				'label' => 'Air Fryer Option'
			),
			'flag_crockpot' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-instant-pot text-gray-dark',
				'png_icon' => 'icon-instant-pot.png',
				'tooltip' => 'Crock-Pot or Instant Pot Option',
				'label' => 'Crock-Pot or Instant Pot Option'
			),
			'flag_cooks_from_frozen' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-frozen text-gray-dark',
				'png_icon' => 'menu-icon06.png',
				'tooltip' => 'Cooks from Frozen',
				'label' => 'Cooks from Frozen'
			),
			'flag_under_thirty' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-minutes_30 text-gray-dark',
				'png_icon' => 'menu-icon03.png',
				'tooltip' => 'Cooks in under 30 Minutes',
				'label' => 'Cooks in under 30 Minutes'
			),
			'flag_under_400' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-calories text-gray-dark',
				'png_icon' => 'menu-icon04.png',
				'tooltip' => 'Under 500 Calories',
				'label' => 'Under 500 Calories'
			),
			'flag_no_added_salt' => array(
				'site_legend_enabled' => !(!empty($DAO_menu) && $DAO_menu->id >= self::MENU_CUTOFF_NO_ADDED_SALT),
				'print_menu_legend_enabled' => !(!empty($DAO_menu) && $DAO_menu->id >= self::MENU_CUTOFF_NO_ADDED_SALT),
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-no-added-salt text-gray-dark',
				'png_icon' => 'menu-icon09.png',
				'tooltip' => 'No Added Salt',
				'label' => 'No Added Salt'
			),
			'would_order_again' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-star text-yellow',
				'png_icon' => null,
				'tooltip' => 'Would order again',
				'label' => 'Would order again'
			),
			'pre_assembled' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => null,
				'png_icon' => null,
				'tooltip' => 'Pre-Assembled meals are always made by our team in our local assembly kitchen.',
				'label' => 'Pre-Assembled meals are always made by our team in our local assembly kitchen.'
			),
			'ltd_menu_item' => array(
				'site_legend_enabled' => true,
				'print_menu_legend_enabled' => true,
				'meal_detail_enabled' => true,
				'print_meal_detail_enabled' => true,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-steam_heart text-orange',
				'png_icon' => 'menu-icon07.png',
				'tooltip' => 'Dream Dinners Foundation Meal of the Month',
				'label' => 'Dream Dinners Foundation Meal of the Month'
			),
			'gluten_friendly' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-gluten-friendly text-gray-dark',
				'png_icon' => 'icon-gluten-friendly.png',
				'tooltip' => 'Gluten friendly',
				'label' => 'Gluten friendly'
			),
			'high_protein' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-high-protein text-gray-dark',
				'png_icon' => 'icon-high-protein.png',
				'tooltip' => 'High protein',
				'label' => 'High protein'
			),
			'vegetarian' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-vegetarian text-gray-dark',
				'png_icon' => null,
				'tooltip' => 'Vegetarian',
				'label' => 'Vegetarian'
			),
			'flag_heart_healthy' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-health text-gray-dark',
				'png_icon' => 'menu-icon01.png',
				'tooltip' => 'Heart Healthy',
				'label' => 'Heart Healthy'
			),
			'gourmet' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-gourmet text-gray-dark',
				'png_icon' => null,
				'tooltip' => 'Gourmet',
				'label' => 'Gourmet'
			),
			'cooking_instruction_youtube_id' => array(
				'site_legend_enabled' => false,
				'print_menu_legend_enabled' => false,
				'meal_detail_enabled' => false,
				'print_meal_detail_enabled' => false,
				'show' => false,
				'value' => null,
				'css_icon' => 'icon-video text-gray-dark',
				'png_icon' => 'menu-icon05.png',
				'tooltip' => 'Instructional Video',
				'label' => 'Instructional Video'
			)
		);
	}

	/*
	 * would_order_again is not a static meal flag, so it must be passed in
	 */
	function buildFlagArray($would_order_again = false, $DAO_menu = false)
	{
		$this->icons = self::getIconSchematic($DAO_menu);

		$this->icons['flag_grill_friendly']['show'] = !empty($this->flag_grill_friendly);
		$this->icons['flag_grill_friendly']['value'] = $this->flag_grill_friendly;

		$this->icons['air_fryer']['show'] = !empty($this->air_fryer);
		$this->icons['air_fryer']['value'] = $this->air_fryer;

		$this->icons['flag_crockpot']['show'] = !empty($this->flag_crockpot);
		$this->icons['flag_crockpot']['value'] = $this->flag_crockpot;

		$this->icons['flag_cooks_from_frozen']['show'] = !empty($this->flag_cooks_from_frozen);
		$this->icons['flag_cooks_from_frozen']['value'] = $this->flag_cooks_from_frozen;

		$this->icons['flag_under_thirty']['show'] = !empty($this->flag_under_thirty);
		$this->icons['flag_under_thirty']['value'] = $this->flag_under_thirty;

		$this->icons['flag_under_400']['show'] = !empty($this->flag_under_400);
		$this->icons['flag_under_400']['value'] = $this->flag_under_400;

		$this->icons['flag_no_added_salt']['show'] = !(!empty($DAO_menu) && $DAO_menu->id >= self::MENU_CUTOFF_NO_ADDED_SALT) && !empty($this->flag_no_added_salt);
		$this->icons['flag_no_added_salt']['value'] = $this->flag_no_added_salt;

		$this->icons['would_order_again']['show'] = !empty($would_order_again);
		$this->icons['would_order_again']['value'] = $would_order_again;

		$this->icons['ltd_menu_item']['show'] = !empty($this->ltd_menu_item_value);
		$this->icons['ltd_menu_item']['value'] = $this->ltd_menu_item_value;

		$this->icons['gluten_friendly']['show'] = !empty($this->gluten_friendly);
		$this->icons['gluten_friendly']['value'] = $this->gluten_friendly;

		$this->icons['high_protein']['show'] = !empty($this->high_protein);
		$this->icons['high_protein']['value'] = $this->high_protein;

		$this->icons['vegetarian']['show'] = !empty($this->vegetarian);
		$this->icons['vegetarian']['value'] = $this->vegetarian;

		$this->icons['flag_heart_healthy']['show'] = !empty($this->flag_heart_healthy);
		$this->icons['flag_heart_healthy']['value'] = $this->flag_heart_healthy;

		$this->icons['gourmet']['show'] = !empty($this->gourmet);
		$this->icons['gourmet']['value'] = $this->gourmet;

		$this->icons['cooking_instruction_youtube_id']['show'] = !empty($this->cooking_instruction_youtube_id);
		$this->icons['cooking_instruction_youtube_id']['value'] = $this->cooking_instruction_youtube_id;

		return $this->icons;
	}

	static function fetch_component_nutrition_data_by_recipe($recipe_ids, $menu_id = null)
	{
		/* Get recipe components */
		$DAO_recipe_comps = DAO_CFactory::create('recipe');
		$DAO_recipe_comps->override_menu_id = $menu_id;
		$DAO_recipe_comps->whereAdd("recipe.recipe_id IN(" . $recipe_ids . ")");
		$DAO_recipe_comps->selectAdd();
		$DAO_recipe_comps->selectAdd("recipe.recipe_id");
		$DAO_recipe_comps->selectAdd("recipe_component.serving");
		$DAO_recipe_comps->selectAdd("nutrition_element.label");
		$DAO_recipe_comps->selectAdd("nutrition_element.display_label");
		$DAO_recipe_comps->selectAdd("nutrition_element.measure_label");
		$DAO_recipe_comps->selectAdd("nutrition_data.component_number");
		$DAO_recipe_comps->selectAdd("nutrition_data.note_indicator");
		$DAO_recipe_comps->selectAdd("nutrition_data.prefix");
		$DAO_recipe_comps->selectAdd("ROUND(SUM(nutrition_data.value), 2) AS value");
		$DAO_recipe_comps->selectAdd("MAX(nutrition_element.daily_value) AS daily_value");
		$DAO_recipe_comps->selectAdd("ROUND((value / daily_value) * 100) AS percent_daily_value");
		$DAO_nutrition_data = DAO_CFactory::create('nutrition_data');
		$DAO_nutrition_data->joinAddWhereAsOn(DAO_CFactory::create('nutrition_element'));
		$DAO_recipe_component = DAO_CFactory::create('recipe_component');
		$DAO_recipe_component->joinAddWhereAsOn($DAO_nutrition_data);
		$DAO_recipe_comps->joinAddWhereAsOn($DAO_recipe_component);
		$DAO_recipe_comps->groupBy("recipe.recipe_id, nutrition_element.label, nutrition_data.component_number");
		$DAO_recipe_comps->orderBy("recipe.recipe_name ASC, nutrition_data.component_number ASC, nutrition_element.label ASC");
		$DAO_recipe_comps->find();

		/* Get recipe details */
		$DAO_recipe = DAO_CFactory::create('recipe');
		$DAO_recipe->override_menu_id = $menu_id;
		$DAO_recipe->whereAdd("recipe.recipe_id IN(" . $recipe_ids . ")");
		$DAO_recipe->selectAdd();
		$DAO_recipe->selectAdd("recipe.*");
		$DAO_recipe->selectAdd("recipe_component.notes");
		$DAO_recipe->selectAdd("recipe_component.component_number");
		$DAO_recipe->selectAdd("recipe_component.serving");
		$DAO_recipe->selectAdd("recipe_size.recipe_size");
		$DAO_recipe->selectAdd("recipe_size.weight");
		$DAO_recipe->selectAdd("recipe_size.cooking_time");
		$DAO_recipe->selectAdd("recipe_size.cooking_instructions");
		$DAO_recipe->selectAdd("recipe_size.upc");
		$DAO_recipe->selectAdd("recipe_size.serving_size_combined");
		$DAO_recipe->selectAdd("recipe_size.servings_per_container");
		$DAO_recipe->joinAddWhereAsOn(DAO_CFactory::create('recipe_component'), 'LEFT');
		$DAO_recipe->joinAddWhereAsOn(DAO_CFactory::create('recipe_size'), 'LEFT');
		$DAO_recipe->orderBy("recipe.recipe_name ASC");
		$DAO_recipe->find();

		$NutsArray = array();

		while ($DAO_recipe_comps->fetch())
		{
			$NutsArray[$DAO_recipe_comps->recipe_id]['component'][$DAO_recipe_comps->component_number][$DAO_recipe_comps->label] = $DAO_recipe_comps->toArray();

			// Calories row is the only row that contains the true serving value due to the joins?
			if ($DAO_recipe_comps->label == 'Calories')
			{
				$NutsArray[$DAO_recipe_comps->recipe_id]['component'][$DAO_recipe_comps->component_number]['serving'] = $DAO_recipe_comps->serving;
			}
		}

		while ($DAO_recipe->fetch())
		{
			$NutsArray[$DAO_recipe->recipe_id]['info'] = $DAO_recipe->toArray();

			$NutsArray[$DAO_recipe->recipe_id]['size'][$DAO_recipe->recipe_size] = array(
				'weight' => $DAO_recipe->weight,
				'cooking_time' => $DAO_recipe->cooking_time,
				'cooking_instructions' => $DAO_recipe->cooking_instructions,
				'upc' => $DAO_recipe->upc,
				'serving_size_combined' => $DAO_recipe->serving_size_combined,
				'servings_per_container' => $DAO_recipe->servings_per_container,
			);
		}

		return $NutsArray;
	}

	static function fetch_nutrition_data_by_recipe($recipe_ids)
	{
		$nutsElementObj = DAO_CFactory::create('nutrition_data');
		$nutsElementObj->query("SELECT
			r.recipe_id,
			ne.label,
			ne.display_label,
			ne.measure_label,
			ROUND(SUM(nd.value), 2) AS value,
       		nd.prefix,
			MAX(ne.daily_value) AS daily_value,
			ROUND((value / daily_value) * 100) AS percent_daily_value
			FROM recipe r
			JOIN recipe_component rc ON rc.recipe_id = r.id AND rc.is_deleted = '0'
			JOIN nutrition_data nd ON rc.id = nd.component_id
			JOIN nutrition_element ne ON nd.nutrition_element = ne.id AND nd.is_deleted = '0'
			WHERE r.recipe_id IN(" . $recipe_ids . ")
			AND ISNULL(r.override_menu_id)
			AND r.is_deleted ='0'
			GROUP BY r.recipe_id, ne.label
			ORDER BY r.recipe_id ASC, ne.label ASC");

		$nutsInfoObj = DAO_CFactory::create('recipe');
		$nutsInfoObj->query("SELECT
			r.*,
			rc.notes,
			rc.serving,
			rs.recipe_size,
			rs.weight,
			rs.cooking_time,
			rs.cooking_instructions,
			rs.upc,
			rs.serving_size_combined,
			rs.servings_per_container
			FROM recipe AS r
			LEFT JOIN recipe_component AS rc ON rc.recipe_id = r.id AND rc.is_deleted = '0'
			LEFT JOIN recipe_size AS rs ON rs.recipe_id = r.recipe_id AND rs.is_deleted = 0
			WHERE r.recipe_id IN (" . $recipe_ids . ")
			AND ISNULL(r.override_menu_id)
			AND r.is_deleted = '0'
			GROUP BY r.recipe_id, rs.recipe_size
			ORDER BY r.recipe_name ASC");

		$NutsArray = array();
		$elementArray = array();

		while ($nutsElementObj->fetch())
		{
			$elementArray[$nutsElementObj->recipe_id][$nutsElementObj->label] = $nutsElementObj->toArray();
		}

		while ($nutsInfoObj->fetch())
		{
			$NutsArray[$nutsInfoObj->recipe_id]['size'][$nutsInfoObj->recipe_size]['info'] = $nutsInfoObj->toArray();
			$NutsArray[$nutsInfoObj->recipe_id]['size'][$nutsInfoObj->recipe_size]['element'] = $elementArray[$nutsInfoObj->recipe_id];
		}

		return $NutsArray;
	}

	static function fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, $store = false, $sum_components = false)
	{
		$nutsComponent_group_by = "mi.id, ne.label, nd.component_number";
		$nutsInfo_group_by = "";
		$nutsInfo_select_group = "rc.serving,";

		// if $sum_components then return all components summed as one
		if ($sum_components)
		{
			$nutsComponent_group_by = "mi.id, ne.label";
			$nutsInfo_group_by = "GROUP BY mi.id";
			$nutsInfo_select_group = "GROUP_CONCAT(rc.serving SEPARATOR '; ') AS serving,";
		}

		$nutsComponentObj = DAO_CFactory::create('nutrition_data');
		$nutsComponentObj->query("SELECT
			mi.id,
			mi.recipe_id,
			ne.label,
			ne.display_label,
			ne.measure_label,
			nd.component_number,
			ROUND(SUM(nd.value), 2) AS value,
       		nd.prefix,
			MAX(ne.daily_value) AS daily_value,
			ROUND((value / daily_value) * 100) AS percent_daily_value
			FROM menu_item mi
			JOIN recipe r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = '" . $menu_id . "' AND r.is_deleted = '0'
			JOIN recipe_component rc ON rc.recipe_id = r.id AND rc.is_deleted = '0'
			JOIN nutrition_data nd ON rc.id = nd.component_id AND nd.is_deleted = '0'
			JOIN nutrition_element ne ON nd.nutrition_element = ne.id
			WHERE mi.id IN(" . $menu_item_ids . ")
			#AND mi.id = mi.entree_id
			GROUP BY " . $nutsComponent_group_by . "
			ORDER BY mi.menu_item_name ASC");

		$nutsInfoObj = DAO_CFactory::create('recipe');

		$store_inventory_select = '';
		$store_inventory_join = '';

		// INVENTORY TOUCH POINT 13

		if ($store)
		{
			$store_inventory_select = "mii.override_inventory - mii.number_sold as std_inventory,
				mtmi.is_visible,
				mtmi.is_hidden_everywhere,";

			$store_inventory_join = "LEFT JOIN menu_item_inventory mii on mii.menu_id = '" . $menu_id . "' and mii.recipe_id = r.recipe_id and mii.store_id = '" . $store . "' and mii.is_deleted = 0
    			INNER JOIN menu_to_menu_item AS mtmi ON mtmi.menu_item_id = mi.id AND mtmi.store_id = '" . $store . "' AND mtmi.is_deleted = '0'";
		}

		$nutsInfoObj->query("SELECT
			mi.id,
			mi.menu_item_name,
			mi.servings_per_item,
			mi.servings_per_container_display,
			r.*,
			mi.menu_item_category_id,
			rc.notes,
			rc.component_number,
			" . $nutsInfo_select_group . "
			" . $store_inventory_select . "
			mi.prep_time
			FROM recipe AS r
			JOIN menu_item mi ON mi.recipe_id = r.recipe_id
			JOIN recipe_component rc ON rc.recipe_id = r.id AND rc.is_deleted = '0'
			" . $store_inventory_join . "
			WHERE mi.id IN(" . $menu_item_ids . ")
			AND r.override_menu_id = '" . $menu_id . "'
			AND r.is_deleted = '0'
			" . $nutsInfo_group_by . "
			ORDER BY mi.menu_item_name ASC");

		$NutsArray = array();

		while ($nutsComponentObj->fetch())
		{
			$NutsArray[$nutsComponentObj->recipe_id]['component'][$nutsComponentObj->component_number][$nutsComponentObj->label] = $nutsComponentObj->toArray();
		}

		while ($nutsInfoObj->fetch())
		{
			$NutsArray[$nutsInfoObj->recipe_id]['info'] = $nutsInfoObj->toArray();

			if ($nutsInfoObj->menu_item_category_id == 9)
			{
				if (!empty($nutsInfoObj->servings_per_container_display))
				{
					$NutsArray[$nutsInfoObj->recipe_id]['info']['servings_per_container'] = $nutsInfoObj->servings_per_container_display;
				}
				else
				{
					$NutsArray[$nutsInfoObj->recipe_id]['info']['servings_per_container'] = 3;
				}
			}
			else
			{
				$NutsArray[$nutsInfoObj->recipe_id]['info']['servings_per_container'] = $nutsInfoObj->servings_per_item;
			}

			$NutsArray[$nutsInfoObj->recipe_id]['component'][$nutsInfoObj->component_number]['serving'] = $nutsInfoObj->serving;
		}

		if ($store)
		{
			foreach ($NutsArray as $rid => &$data)
			{
				if (isset($data['info']))
				{
					$data['info']['has_inventory'] = ($data['info']['std_inventory'] > 0);
				}
			}
		}

		return $NutsArray;
	}

	static function createManufacturerRecipeList($store_id)
	{
		$menuItemObj = DAO_CFactory::create('recipe');

		$menuItemObj->query("SELECT
			r.recipe_id,
			r.recipe_name,
			rs.recipe_size,
			rs.upc
			FROM manufacturer_items AS man
			LEFT JOIN recipe AS r ON r.recipe_id = man.recipe_id AND r.is_deleted = '0'
			INNER JOIN recipe_size AS rs ON rs.recipe_id = r.recipe_id AND rs.is_deleted = '0'
			WHERE man.store_id = '" . $store_id . "'
			AND ISNULL(r.override_menu_id)
			AND man.active = '1'
			ORDER BY r.recipe_name ASC, recipe_size ASC");

		$retVal = array();
		while ($menuItemObj->fetch())
		{
			$retVal[$menuItemObj->recipe_id]['info'] = $menuItemObj->toArray();
			$retVal[$menuItemObj->recipe_id][$menuItemObj->recipe_size] = $menuItemObj->toArray();
		}

		return $retVal;
	}

	static function manageNullRecipesForStore($store_id)
	{
		$menuItemObj = DAO_CFactory::create('recipe');

		$menuItemObj->query("SELECT
			r.recipe_id,
			r.recipe_name,
			mi.store_id,
			mi.id AS mi_id,
			mi.active
			FROM recipe AS r
			LEFT JOIN manufacturer_items AS mi ON mi.recipe_id = r.recipe_id AND (mi.store_id IS NULL OR mi.store_id = '" . $store_id . "')
			INNER JOIN recipe_size AS rs ON rs.recipe_id = r.recipe_id AND rs.is_deleted = '0'
			WHERE r.override_menu_id IS NULL
			AND r.is_deleted = '0'
			GROUP BY (r.recipe_id)
			ORDER BY r.recipe_id ASC");

		$retVal = array();
		while ($menuItemObj->fetch())
		{
			$retVal[$menuItemObj->recipe_id] = $menuItemObj->toArray();
		}

		return $retVal;
	}

	static function createMenuItemList($menu_id, $store_id, $visibleOnly = true)
	{
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => true,
			'exclude_menu_item_category_sides_sweets' => true,
			'menu_to_menu_item_is_visible' => $visibleOnly
		));

		$retVal = array();
		while ($daoMenuItem->fetch())
		{
			$retVal[$daoMenuItem->id] = clone $daoMenuItem;
		}

		return $retVal;
	}

	static function createEFLMenuItemList($menu_id, $store_id, $visibleOnly = true)
	{
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_core' => true,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => true,
			'menu_to_menu_item_is_visible' => $visibleOnly
		));

		$retVal = array();
		while ($daoMenuItem->fetch())
		{
			$retVal[$daoMenuItem->id] = clone $daoMenuItem;
		}

		return $retVal;
	}

	static function createFinishingTouchMenuItemList($menu_id, $store_id, $visibleOnly = false)
	{
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;
		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_core' => true,
			'exclude_menu_item_category_efl' => true,
			'exclude_menu_item_category_sides_sweets' => false,
			'menu_to_menu_item_is_visible' => $visibleOnly
		));

		$retVal = array();
		while ($daoMenuItem->fetch())
		{
			$retVal[$daoMenuItem->id] = clone $daoMenuItem;
		}

		return $retVal;
	}

	static function assignManufacturingRecipe($store_id, $recipe_id, $active = false)
	{
		$new_state = 0;

		if (!empty($active))
		{
			$new_state = 1;
		}

		$mItem = DAO_CFactory::create('manufacturer_items');
		$mItem->recipe_id = $recipe_id;
		$mItem->store_id = $store_id;

		if ($mItem->find(true))
		{
			$mItem->active = $new_state;
			$mItem->update();
		}
		else
		{
			$mItem->active = $new_state;
			$mItem->insert();
		}
	}
}

?>