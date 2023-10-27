<?php
require_once("DAO/BusinessObject/CMenuItem.php");
require_once 'DAO.inc';

define('RECIPE_NAME', 0);
define('DESCRIPTION', 1);
define('RECIPE_ID', 2);
define('ALLERGENS', 3);
define('MENU_CLASS', 4);
define('SUB_CATEGORY', 5);
define('FOOD_COST', 6);
define('PRICE_6', 7);
define('SERVING_PER_ORDER', 8);  // servings_per_item, ie: 6 or 3
define('COMPONENT_NAME', 9); // serving size - ie: 1 cup
define('SERVING_WEIGHT', 10); // new
define('PRICE_TIER_1_MD', 11);
define('PRICE_TIER_1_LG', 12);
define('PRICE_TIER_2_MD', 13);
define('PRICE_TIER_2_LG', 14);
define('PRICE_TIER_3_MD', 15);
define('PRICE_TIER_3_LG', 16);
define('SERVE_WITH', 17);
define('SUGGESTED_SIDE', 18); // may need recipe id - column 11 is the sides name
define('STATION_NUMBER', 19); // custom field
define('INCLUDE_ON_INTRO', 20); // custom field
define('INCLUDE_ON_TASTE', 21); // custom field
define('INITIAL_MENU_OFFER', 22);  // custom field
define('MENU_SUBSORT', 23);
define('SALES_MIX', 24);
define('LTD_MOTM', 25);
define('IMPROVED', 26);
define('COOKING_METHOD', 27); //  custom field - review
define('DISPLAY_SERVINGS_PER_CONTAINER', 28); //
define('COOKING_TIME', 29); //  new
define('COOKING_INSTRUCTIONS', 30); //  new
define('AIR_FRYER_ICON', 31); //  new
define('COOKING_INSTRUCTIONS_AIR_FRYER', 32); //  new
define('CROCKPOT_ICON', 33); //  new
define('COOKING_INSTRUCTIONS_CROCK_POT', 34); //  new
define('GRILL_ICON', 35); //  new
define('COOKING_INSTRUCTIONS_GRILL', 36); //  new
define('PREPARE_BY', 37);
define('FROM_FROZEN_ICON', 38);
define('UNDER_30_ICON', 39);
define('UNDER_400_ICON', 40);
define('GLUTEN_FRIENDLY_ICON', 41);
define('HIGH_PROTEIN_ICON', 42);
define('VEGETARIAN_ICON', 43);
define('KID_PICK', 44);
define('NO_SALT_ADDED', 45);
define('PAN_MEAL', 46);
define('PRICING_CATEGORY', 47);
define('INGREDIENTS', 48); // yay
define('NUT_CALORIES', 49); // Discuss merging components OR use sub-recipes
define('NUT_FAT', 50);
define('NUT_SATFAT', 51);
define('NUT_TRANSFAT', 52);
define('NUT_CHOLESTEROL', 53);
define('NUT_CARBS', 54);
define('NUT_FIBER', 55);
define('NUT_SUGARS', 56);
define('NUT_ADDED_SUGARS', 57);
define('NUT_PROTEIN', 58);
define('NUT_SODIUM', 59);
define('NUT_VIT_A', 60);
define('NUT_VIT_C', 61);
define('NUT_VIT_D', 62);
define('NUT_CALCIUM', 63);
define('NUT_POTASSIUM_K', 64);
define('NUT_IRON', 65);

class CImportReciprofity extends DAO
{
	private static $categoryLookup = array(
		"Specials" => 1,
		"Core" => 1,
		"CORE TEST MENU" => 1,
		"Classics" => 2,
		"Best of the Best" => 3,
		"Pre-Assembled" => 4,
		"Pre Assembled" => 4,
		"Fast Lane" => 4,
		"Fastlane" => 4,
		"Sides & Accompaniments" => 5,
		"KidsChoice" => 6,
		"Kid's Choice" => 6,
		"Add-on Items" => 7,
		"Chef Touched" => 9,
		"Chef Touched Entrée" => 9,
		"Chef Touched Bread" => 9,
		"Chef Touched Dessert" => 9,
		"Chef Touched Side" => 9,
		"Chef Touched Appitizer" => 9,
		"Chef Touched Appetizer" => 9,
		"Side" => 5,
		"Holiday Party Packages" => 8,
		"Bundle" => 8,
		"Diabetic" => 10,
		"DFL" => 10,
		"Dinners for Life" => 10,
		"Store Special" => 4,
		"Store Specials" => 4,
		"Fast Lane Specials" => 4,
		"EFL" => 4,
		"Extended Fast Lane" => 4,
		"Chef Touched - Bread" => 9,
		"Chef Touched - Side" => 9,
		"Chef Touched - Dessert" => 9,
		"Chef Touched - Dinner" => 9,
		"FT" => 9,
		"SS" => 9,
		"Sides and Sweets" => 9,
		"Sides & Sweets" => 9,
		"Sides and Sweets Summer" => 9,
		"Sides and Sweets Winter" => 9,
		"Sides and Sweets Spring" => 9,
		"Sides and Sweets Fall" => 9
	);

	private static $containerLookup = array(
		"Zipped Freezer Bag" => CMenuItem::ZIP_LOCK,
		"Baking Pan" => CMenuItem::PAN,
		"Foil Wrap" => CMenuItem::FOIL_WRAP,
		"Pan" => CMenuItem::PAN,
		"0" => CMenuItem::NONE,
		"" => CMenuItem::NONE,
		"None" => CMenuItem::NONE,
		"Box" => CMenuItem::BOX,
		"Bag" => CMenuItem::BAG,
		"bag" => CMenuItem::BAG,
		"Bag/Pan" => CMenuItem::BAG,
		"Pan/Bag" => CMenuItem::BAG,
		"Foil" => CMenuItem::FOIL_WRAP,
		"Oven" => CMenuItem::BAG,
		"Wrapped" => CMenuItem::BAG,
		"Tray" => CMenuItem::PAN
	);

	private static $speeeling = array(
		'ingredient' => array(
			'ﬂ' => 'fl',
			'ﬁ' => 'fi',
			'&#64258;' => 'fl',
			'&#64257;' => 'fi',
			'Acetc' => 'Acetic',
			'Annato' => 'Annatto',
			'Artfcal' => 'Artificial',
			'Artfcial' => 'Artificial',
			'artfcial' => 'artificial',
			'Artifcial' => 'Artificial',
			'Bisulfte' => 'Bisulfite',
			'Breadstck' => 'Breadstick',
			'Buter' => 'Butter',
			'buter' => 'butter',
			'Butermilk' => 'Buttermilk',
			'Caulifower' => 'Cauliflower',
			'Clarifed' => 'Clarified',
			'Conditoner' => 'Conditioner',
			'Confecton' => 'Confection',
			'Contans' => 'Contains',
			'Cotja' => 'Cotija',
			'Cotonseed' => 'Cottonseed',
			'Cottonseed Ol' => 'Cottonseed Oil',
			'Derivatves' => 'Derivatives',
			'Dispotassium' => 'Dipotassium',
			'Distlled' => 'Distilled',
			'Extractve' => 'Extractive',
			'Extractves' => 'Extractives',
			'Faty' => 'Fatty',
			'Flavouring' => 'Flavoring',
			'Fransisco' => 'Francisco',
			'free fowing' => 'free flowing',
			'Lactc' => 'Lactic',
			'Malttol' => 'Maltitol',
			'modifed' => 'modified',
			'Modifed' => 'Modified',
			'Molases' => 'Molasses',
			'Olive OIl' => 'Olive Oil',
			'Parisan' => 'Parisian',
			'pastuerized' => 'pasteurized',
			'Pectn' => 'Pectin',
			'Preservatve' => 'Preservative',
			'preservatve' => 'preservative',
			'Prophosphate' => 'Pyrophosphate',
			'Retenton' => 'Retention',
			'retenton' => 'retention',
			'Ribofavin' => 'Riboflavin',
			'ribofavin' => 'riboflavin',
			'ricota' => 'ricotta',
			'Ricota' => 'Ricotta',
			'Safower' => 'Safflower',
			'Soluton' => 'Solution',
			'soluton' => 'solution',
			'Sulfte' => 'Sulfite',
			'Tofee' => 'Toffee',
			'Troula Yeast' => 'Torula Yeast',
			'Tumeric' => 'Turmeric',
			'Unblelached' => 'Unbleached',
			'Whte Rice' => 'White Rice',
			'Xantahn' => 'Xanthan',
			'Xanthum' => 'Xanthan'
		)
	);

	public static function spellCheck($type, $string)
	{
		return strtr($string, self::$speeeling[$type]);
	}

	public static function categoryLookup($category)
	{
		if (array_key_exists($category, self::$categoryLookup))
		{
			return self::$categoryLookup[$category];
		}
		else
		{
			return false;
		}
	}

	public static function containerLookup($container)
	{
		if (array_key_exists($container, self::$containerLookup))
		{
			return self::$containerLookup[$container];
		}
		else
		{
			return false;
		}
	}

	public static function charConversions($inStr)
	{
		return trim(mb_convert_encoding($inStr, 'HTML-ENTITIES', 'UTF-8'));
	}

	public static function distillCSVImport($_tmp_file, $tpl, $returnLabels = false)
	{

		$rows = array();
		$labels = array();
		$inputFileName = $_tmp_file['tmp_name'];

		$row = 1;
		$hasValidRow = false;
		if (($handle = fopen($inputFileName, "r")) !== false)
		{
			while (($data = fgetcsv($handle, 10000, ",")) !== false)
			{
				if ($row == 1)
				{
					if (trim(strtolower($data[RECIPE_NAME])) != "recipe_name")
					{
						CLog::Record('Menu import failed: no header row');
						$tpl->setErrorMsg('Menu import failed: no header row');

						return false;
					}
					else if ($returnLabels)
					{
						$numCols = count($data);
						for ($col = 0; $col < $numCols; $col++)
						{
							$labels[$col] = $data[$col];
						}
					}
				}
				else
				{
					// all other rows - skip until the first valid data row is found
					if (!$hasValidRow)
					{
						$testCell = $data[RECIPE_ID];
						$testCellArr = explode("_", $testCell);
						if (is_numeric($testCell) || (count($testCellArr) == 2 && is_numeric($testCellArr[0])))
						{
							$hasValidRow = true;
							$numCols = count($data);
							$rows[$row] = array();
							for ($col = 0; $col < $numCols; $col++)
							{
								$rows[$row][$col] = $data[$col];
							}
						}
					}
					else
					{
						$numCols = count($data);
						$rows[$row] = array();

						for ($col = 0; $col < $numCols; $col++)
						{
							$rows[$row][$col] = $data[$col];
						}
					}
				}
				$row++;
			}
			fclose($handle);
		}
		else
		{
			CLog::Record('Menu import failed: fopen failed');
			$tpl->setErrorMsg('Menu import failed: fopen failed');

			return false;
		}

		usort($rows, "self::sortOrderCompare");

		if ($returnLabels)
		{
			array_unshift($rows, $labels);
		}

		return $rows;
	}

	public static function sanityCheck(&$rows, $updateExistingMenu = false, $hasLabels = false)
	{
		// sanity check, see if we can find any issues with the data
		$sanity = array(
			'recipe_ids' => array(),
			//'subsort_ids' => array(),
			'show_on_web' => array(),
			'available_intro' => array(),
			'available_dreamtaste' => array(),
			'motm_value' => array()
		);

		$count = 0;
		foreach ($rows as $row => &$col)
		{
			$count++;
			if ($count == 1 && $hasLabels)
			{
				continue;
			}

			if (empty($col[RECIPE_ID]))
			{
				return "Missing recipe ID: Row " . $count . " - " . $col[RECIPE_NAME];
			}

			// check for duplicate ids
			if (empty($sanity['recipe_ids'][$col[RECIPE_ID]]))
			{
				$sanity['recipe_ids'][$col[RECIPE_ID]] = $col[RECIPE_ID];
			}
			else
			{
				return "Duplicate recipe ID: Row " . $count . " - " . $col[RECIPE_NAME];
			}

			if (strpos($col[RECIPE_ID], "_L") !== false)
			{
				$pricing_type = CMenuItem::FULL;
			}
			else if (strpos($col[RECIPE_ID], "_4") !== false)
			{
				$pricing_type = CMenuItem::FOUR;
			}
			else if (strpos($col[RECIPE_ID], "_M") !== false)
			{
				$pricing_type = CMenuItem::HALF;
			}
			else if (strpos($col[RECIPE_ID], "_2") !== false)
			{
				$pricing_type = CMenuItem::TWO;
			}
			else
			{
				$pricing_type = CMenuItem::FULL;
			}

			//$internalRecipeID =  trim($col[RECIPE_ID], " \n\r\t\v\0LM");

			$internalRecipeIDArr = explode("_", $col[RECIPE_ID]);
			$internalRecipeID = $internalRecipeIDArr[0];
			$col['recipe_id'] = $internalRecipeID;
			$col['pricing_type'] = $pricing_type;

			/*
			// check for duplicate subsort ids
			if (empty($sanity['subsort_ids'][$col[MENU_SUBSORT]]))
			{
				$sanity['subsort_ids'][$col[MENU_SUBSORT]] = $col[MENU_SUBSORT];
			}
			else
			{
				return "Duplicate subsort ID: Row " . $row . " - " . $col[RECIPE_NAME];
			}
			*/

			// check for missing food cost
			if (!is_numeric($col[FOOD_COST]))
			{
				return "Problem with Food Cost: Row " . $count . " - " . $col[RECIPE_NAME];
			}

			// check for missing base price
			if (!is_numeric($col[PRICE_6]))
			{
				return "Problem with Price: Row " . $count . " - " . $col[RECIPE_NAME];
			}

			// check for show on web
			if (!empty($col[STATION_NUMBER]) && strtolower($col[STATION_NUMBER]) == 'web')
			{
				$sanity['show_on_web'][$col[RECIPE_ID]] = $col[STATION_NUMBER];
			}

			// check for intro offer
			if (!empty($col[INCLUDE_ON_INTRO]) && strtolower($col[INCLUDE_ON_INTRO]) == 'yes' && $pricing_type == CMenuItem::FULL)
			{
				$sanity['available_intro'][$internalRecipeID] = $col[INCLUDE_ON_INTRO];
			}

			// check for dream taste
			if (!empty($col[INCLUDE_ON_TASTE]) && strtolower($col[INCLUDE_ON_TASTE]) == 'yes' && $pricing_type !== CMenuItem::HALF)
			{
				$sanity['available_dreamtaste'][$internalRecipeID] = $col[INCLUDE_ON_TASTE];
			}

			if (trim(strtolower($col[LTD_MOTM])) == "yes")
			{
				$sanity['motm_value'][$col[RECIPE_ID]] = $col[LTD_MOTM];
			}

			$col[MENU_CLASS] = trim($col[MENU_CLASS]);
		}

		// bypass these if doing an update to an already imported menu
		if (!$updateExistingMenu)
		{

//			if (empty($sanity['available_intro']) || count($sanity['available_intro']) <> 12)
//			{
//				return "Too few or too many intro items, should be 12 items";
//			}

			if (empty($sanity['available_dreamtaste']) || count($sanity['available_dreamtaste']) <> 5)
			{
				return "Too few or too many Meal Prep Workshop items, should be 5 items";
			}

			if (empty($sanity['motm_value']))
			{
				return "No meal of the month value specified";
			}
		}

		if ($hasLabels)
		{
			$labels = array_shift($rows);
			usort($rows, "self::sortOrderCompareInterRecipe");
			array_unshift($rows, $labels);
		}

		// all good? return true
		return true;
	}

	private static function sortOrderCompare($a, $b)
	{
		if ($a[MENU_SUBSORT] == $b[MENU_SUBSORT])
		{
			return 0;
		}

		return ($a[MENU_SUBSORT] < $b[MENU_SUBSORT]) ? -1 : 1;
	}

	private static function sortOrderCompareInterRecipe($a, $b)
	{
		if ($a[MENU_SUBSORT] == $b[MENU_SUBSORT])
		{
			if ($a['pricing_type'] == CMenuItem::TWO)
			{
				return -1;
			}
			else if ($a['pricing_type'] == CMenuItem::FULL)
			{
				return 1;
			}
			else if ($a['pricing_type'] == CMenuItem::FOUR)
			{
				if ($b['pricing_type'] == CMenuItem::TWO || $b['pricing_type'] == CMenuItem::HALF)
				{
					return 1;
				}
				else
				{
					return -1;
				}
			}
			else if ($a['pricing_type'] == CMenuItem::HALF)
			{
				if ($b['pricing_type'] == CMenuItem::TWO)
				{
					return 1;
				}
				else if ($b['pricing_type'] == CMenuItem::FOUR || $b['pricing_type'] == CMenuItem::FULL)
				{
					return -1;
				}
			}
		}

		return ($a[MENU_SUBSORT] < $b[MENU_SUBSORT]) ? -1 : 1;
	}
}
?>