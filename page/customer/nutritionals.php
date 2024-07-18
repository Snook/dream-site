<?php
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CRecipe.php");

class page_nutritionals extends CPage
{

	function getCSVCompatibleArray($nutsArray, &$header)
	{

		$retVal = array();

		$countCat = 0;
		foreach ($nutsArray as $catName => $outerArray)
		{
			$countCat++;

			$colHeaders = array(
				"Dinner",
				//"Heart Healthy",
				"Grill Option",
				"Cooks from Frozen",
				"Crockpot",
				"Instant pot",
				"Under 400",
				"Ready in under 30",
				//"Instructional Video",
				"Time to Prep*",
				"Serving Size",
				"Cal ",
				"Fat",
				"Sat. Fat",
				"Cholesterol",
				"Carbs",
				"Fiber",
				"Sugar",
				"Protein",
				"Sodium",
				"Notes"
			);

			if ($countCat == 1)
			{
				$header = $colHeaders;
			}
			else
			{
				$retVal[] = $colHeaders;
			}

			foreach ($outerArray['nutritionals'] as $recipeID => $recipe)
			{
				$count = 0;
				foreach ($recipe['component'] as $componentNum => $component)
				{
					$thisRow = array();

					if ($count++ == 0)
					{
						// first row for an entree will have the non-component specific data
						$thisRow[] = $recipe['info']['recipe_name'];
						//$thisRow[] = !empty($recipe['info']['flag_heart_healthy']) ? 'X' : ''; // disabled 9-23-2020
						$thisRow[] = !empty($recipe['info']['flag_grill_friendly']) ? 'X' : '';
						$thisRow[] = !empty($recipe['info']['flag_cooks_from_frozen']) ? 'X' : '';
						$thisRow[] = !empty($recipe['info']['flag_crockpot']) ? 'X' : '';
						$thisRow[] = !empty($recipe['info']['flag_instant_pot']) ? 'X' : '';
						$thisRow[] = !empty($recipe['info']['flag_under_400']) ? 'X' : '';
						//$thisRow[] = !empty($recipe['info']['flag_no_added_salt']) ? 'X' : '';
						$thisRow[] = !empty($recipe['info']['flag_under_thirty']) ? 'X' : '';
						//$thisRow[] = !empty($recipe['info']['cooking_instruction_youtube_id']) ? 'http://youtu.be/' . $recipe['info']['cooking_instruction_youtube_id'] : '';
						$thisRow[] = $recipe['info']['prep_time'];
					}
					else
					{
						$thisRow[] = "";
						//$thisRow[] = ""; // disabled 9-23-2020
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
						$thisRow[] = "";
					}

					$thisRow[] = $component['serving'] . ((!empty($component['serving_weight'])) ? ' (' . $component['serving_weight'] . 'g)' : '');
					$thisRow[] = CTemplate::formatDecimal($component['Calories']['value']) . $component['Calories']['measure_label'];
					$thisRow[] = $component['Fat']['prefix']. CTemplate::formatDecimal($component['Fat']['value']) . $component['Fat']['measure_label'];
					$thisRow[] = $component['Sat Fat']['prefix']. CTemplate::formatDecimal($component['Sat Fat']['value']) . $component['Sat Fat']['measure_label'];
					$thisRow[] = $component['Cholesterol']['prefix']. CTemplate::formatDecimal($component['Cholesterol']['value']) . $component['Cholesterol']['measure_label'];
					$thisRow[] = $component['Carbs']['prefix']. CTemplate::formatDecimal($component['Carbs']['value']) . $component['Carbs']['measure_label'];
					$thisRow[] = $component['Fiber']['prefix']. CTemplate::formatDecimal($component['Fiber']['value']) . $component['Fiber']['measure_label'];
					$thisRow[] = $component['Sugars']['prefix']. CTemplate::formatDecimal($component['Sugars']['value']) . $component['Sugars']['measure_label'];
					$thisRow[] = $component['Protein']['prefix']. CTemplate::formatDecimal($component['Protein']['value']) . $component['Protein']['measure_label'];
					$thisRow[] = $component['Sodium']['prefix']. CTemplate::formatDecimal($component['Sodium']['value']) . $component['Sodium']['measure_label'];
					$thisRow[] = $recipe['info']['notes'];

					$retVal[] = $thisRow;
				}
			}
		}

		return $retVal;
	}

	/**
	 * @throws Exception
	 */
	function runPublic()
	{
		$show = array(
			'entree' => false,
			'efl' => false,
			'ft' => false,
			'print' => false
		);

		if (!empty($_REQUEST['print']) && $_REQUEST['print'] == true)
		{
			$show['print'] = true;
		}

		if (!empty($_REQUEST['show_entree']) && $_REQUEST['show_entree'] == true)
		{
			$show['entree'] = true;
		}

		if (!empty($_REQUEST['show_efl']) && $_REQUEST['show_efl'] == true)
		{
			$show['efl'] = true;
		}

		if (!empty($_REQUEST['show_ft']) && $_REQUEST['show_ft'] == true)
		{
			$show['ft'] = true;
		}

		$filter_by_inventory = false;
		if (!empty($_REQUEST['filter_zero_inventory']) && $_REQUEST['filter_zero_inventory'] == true)
		{
			$filter_by_inventory = true;
		}

		// all were set to false, at minimum show entree
		if ($show['entree'] + $show['efl'] + $show['ft'] == false)
		{
			$show['entree'] = true;
			$show['ft'] = true;
		}

		$tpl = CApp::instance()->template();

		$tpl->assign('store', false);
		$tpl->assign('filter_zero_inventory', $filter_by_inventory);

		$store_id = 0;

		if (!empty($_GET['store']) && is_numeric($_GET['store']))
		{
			$store_id = $_GET['store'];
		}
		else if (CBrowserSession::getCurrentStore())
		{
			$store_id = CBrowserSession::getCurrentStore();
		}

		if (!empty($_GET['menu']) && is_numeric($_GET['menu']))
		{
			$menu_id = $_GET['menu'];
		}
		else
		{
			$menu_id = CMenu::getCurrentMenuId();
		}

		$Form = new CForm();
		$Form->Repost = true;

		$daoMenu = DAO_CFactory::create('menu');
		$current_date_sql = date("Y-m-01", mktime(0, 0, 0, date("m"), "01", date("Y")));
		$daoMenu->whereAdd(" menu_start >= DATE_SUB('$current_date_sql',INTERVAL 3 MONTH) and menu_start <= DATE_ADD('$current_date_sql',INTERVAL 3 MONTH) AND is_active = '1'");
		$daoMenu->OrderBy("menu_start");
		$daoMenu->find();
		$menuArray = array();

		while ($daoMenu->fetch())
		{
			$menuArray[$daoMenu->id] = $daoMenu->menu_name;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::default_value => $menu_id,
			CForm::name => "menus_dropdown",
			CForm::options => $menuArray
		));

		$tpl->assign('form', $Form->Render());

		$menu_info = CMenu::getMenuInfo($menu_id);

		$tpl->assign('menu', $menu_info);

		if ($Store = CStore::getStoreAndOwnerInfo($store_id))
		{
			$tpl->assign('store', $Store['0']);
		}

		$nutritionalArray = array();

		if ($show['entree'])
		{
			$entree_arr = CRecipe::createMenuItemList($menu_id, $store_id);

			$menu_item_ids = "'" . implode("','", array_keys($entree_arr)) . "'";

			$nutritionalArray['entree']['name'] = 'Dinner';
			$nutritionalArray['entree']['nutritionals'] = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, $store_id);
		}

		if ($show['efl'])
		{
			$efl_arr = CRecipe::createEFLMenuItemList($menu_id, $store_id);

			$menu_item_ids = "'" . implode("','", array_keys($efl_arr)) . "'";

			$nutritionalArray['efl']['name'] = 'Extended Fast Lane';
			$nutritionalArray['efl']['nutritionals'] = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, $store_id);
		}

		if ($show['ft'])
		{
			$ft_arr = CRecipe::createFinishingTouchMenuItemList($menu_id, $store_id);

			$menu_item_ids = "'" . implode("','", array_keys($ft_arr)) . "'";

			$nutritionalArray['ft']['name'] = 'Sides &amp; Sweets';
			$nutritionalArray['ft']['nutritionals'] = CRecipe::fetch_nutrition_data_by_mid($menu_id, $menu_item_ids, $store_id);
		}

		if (!empty($nutritionalArray) && $show['print'])
		{
			$tpl->assign('print_view', true);
		}
		else
		{
			$tpl->assign('print_view', false);
		}

		$tpl->assign('nutritional_array', $nutritionalArray);

		if (isset($_GET['export']) && $_GET['export'] == 'csv')
		{
			$headers = array();
			$rows = $this->getCSVCompatibleArray($nutritionalArray, $headers);

			$filename = "Nutrition _Info_" . str_replace(" ", "_", $menu_info['menu_name']);

			$_GET['csvfilename'] = $filename;

			$tpl->assign('rows', $rows);
			$tpl->assign('labels', $headers);
		}
	}
}