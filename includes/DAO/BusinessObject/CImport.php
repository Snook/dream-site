<?php
require_once("DAO/BusinessObject/CMenuItem.php");
require_once 'DAO.inc';

define('RECIPE_ID', 0);
define('RECIPE_NAME', 1);
define('MENU_CLASS', 2);
define('MENU_SUBSORT', 3); // custom field
define('SUB_CATEGORY', 4);
define('DESCRIPTION', 5);
define('STATION_NUMBER', 6); // custom field
define('INCLUDE_ON_INTRO', 7); // custom field
define('INCLUDE_ON_TASTE', 8); // custom field
define('INITIAL_MENU_OFFER', 9);  // custom field
define('FOOD_COST', 10);
define('PRICE_6', 11);
define('SUGGESTED_SIDE', 12); // custom field - discuss with Food
define('SIX_SERV_ONLY', 13); // not needed
define('SERVING_PER_ORDER', 14);  // potentially custom field
define('SERVE_WITH', 15);
define('PACKAGING', 16); // custom field - review
define('COOKING_METHOD', 17); //  custom field - review
define('INGREDIENTS', 18); // yay
define('ALLERGENS', 19); //
define('COMPONENT_NAME', 20); // serving size - itemized
define('NOTES', 21); // comments
define('NUT_CALORIES', 22); // Discuss merging components OR use sub-recipes
define('NUT_FAT', 23);
define('NUT_SATFAT', 24);
define('NUT_TRANSFAT', 25);
define('NUT_CHOLESTEROL', 26);
define('NUT_CARBS', 27);
define('NUT_FIBER', 28);
define('NUT_SUGARS', 29);
define('NUT_ADDED_SUGARS', 30);
define('NUT_PROTEIN', 31);
define('NUT_SODIUM', 32);
define('NUT_VIT_A', 33);
define('NUT_VIT_C', 34);
define('NUT_VIT_D', 35);
define('NUT_CALCIUM', 36);
define('NUT_POTASSIUM_K', 37);
define('NUT_IRON', 38);

define('PREP_TIME_3', 57); // possibly custom
define('INST_3', 58); // custom if needed

define('PREPARE_BY', 62);
define('PREP_TIME_6', 63);
define('INST_6', 64);

define('RECIPE_EXPERT', 68); // custom field

define('FROM_FROZEN_ICON', 70);
define('CROCKPOT_ICON', 71);
define('LTD_MOTM', 72); // Yes/No
define('EVERYDAY_DINNER', 73);
define('GRILL_ICON', 74);
define('GOURMET', 75);
define('HEART_ICON', 76);
define('KID_FRIENDLY', 77);
define('UNDER_30_ICON', 78);
define('UNDER_400_ICON', 79);
define('VEGETARIAN_ICON', 80);
define('FLAVOR_PROFILE', 81);

define('CI_YOUTUBE_ID', 83); // possibly not needed

class CImport extends DAO
{
	private static $categoryLookup = array(
		"Specials" => 1,
		"Core" => 1,
		"Classics" => 2,
		"Best of the Best" => 3,
		"Fast Lane" => 4,
		"Pre-Assembled" => 4,
		"Pre Assembled" => 4,
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
		"SS" => 9
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

	public static function distillExcelImport($_tmp_file, $tpl)
	{
		if (!$fp = file_get_contents($_tmp_file['tmp_name']))
		{
			CLog::Record('Menu import failed: fopen failed');
			$tpl->setErrorMsg('Menu import failed: fopen failed');

			return false;
		}

		$inputFileName = $_tmp_file['tmp_name'];
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($inputFileName);
		$objWorksheet = $objPHPExcel->getActiveSheet();

		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

		$rows = array();

		// distill the object into an array
		for ($row = 1; $row <= $highestRow; ++$row)
		{
			$rows[$row] = array();

			// load excel obj into an array
			for ($col = 0; $col <= $highestColumnIndex; ++$col)
			{
				$rows[$row][$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
			}
		}

		return $rows;
	}

	public static function sanityCheck($rows, $updateExistingMenu = false)
	{
		// sanity check, see if we can find any issues with the data
		$sanity = array(
			'recipe_ids' => array(),
			'subsort_ids' => array(),
			'show_on_web' => array(),
			'available_intro' => array(),
			'available_dreamtaste' => array(),
			'motm_value' => array()
		);

		foreach ($rows as $row => $col)
		{
			// skip first row, it should be all the column labels
			if ($row == 1)
			{
				continue;
			}

			if (empty($col[RECIPE_ID]))
			{
				return "Missing recipe ID: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for duplicate ids
			if (empty($sanity['recipe_ids'][$col[RECIPE_ID]]))
			{
				$sanity['recipe_ids'][$col[RECIPE_ID]] = $col[RECIPE_ID];
			}
			else
			{
				return "Duplicate recipe ID: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for duplicate subsort ids
			if (empty($sanity['subsort_ids'][$col[MENU_SUBSORT]]))
			{
				$sanity['subsort_ids'][$col[MENU_SUBSORT]] = $col[MENU_SUBSORT];
			}
			else
			{
				return "Duplicate subsort ID: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for missing food cost
			if (!is_numeric($col[FOOD_COST]))
			{
				return "Problem with Food Cost: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for missing base price
			if (!is_numeric($col[PRICE_6]))
			{
				return "Problem with Price: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for show on web
			if (!empty($col[STATION_NUMBER]) && strtolower($col[STATION_NUMBER]) == 'web')
			{
				$sanity['show_on_web'][$col[RECIPE_ID]] = $col[STATION_NUMBER];
			}

			// check for intro offer
			if (!empty($col[INCLUDE_ON_INTRO]) && strtolower($col[INCLUDE_ON_INTRO]) == true)
			{
				// intro item should not be a fast lane item
				//	if (!empty($col[STATION_NUMBER]) && strtolower($col[STATION_NUMBER]) == 'fl')
				//	{
				//		return "Fast lane item marked as intro item: Row " . $row . " - " . $col[RECIPE_NAME];
				//	}
				//	else
				//	{
				$sanity['available_intro'][$col[RECIPE_ID]] = $col[INCLUDE_ON_INTRO];
				//	}
			}

			// check for dream taste
			if (!empty($col[INCLUDE_ON_TASTE]) && strtolower($col[INCLUDE_ON_TASTE]) == true)
			{
				// dream taste item should not be a fast lane item
				//	if (!empty($col[STATION_NUMBER]) && strtolower($col[STATION_NUMBER]) == 'fl')
				//	{
				//		return "Fast lane item marked as intro item: Row " . $row . " - " . $col[RECIPE_NAME];
				//	}
				//	else
				//	{
				$sanity['available_dreamtaste'][$col[RECIPE_ID]] = $col[INCLUDE_ON_TASTE];
				//	}
			}

			// check for Sides & Sweet not marked as 6 serving only
			if (empty($col[SIX_SERV_ONLY]) && CImport::categoryLookup($col[MENU_CLASS]) == 9)
			{
				return "Sides & Sweet not marked as 6 serving only: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for missing 3 serving instructions
			if (empty($col[SIX_SERV_ONLY]) && (empty($col[INST_3]) || strtolower($col[INST_3]) == 'n/a'))
			{
				return "Item not marked as 6 serving only and missing 3 serving instructions: Row " . $row . " - " . $col[RECIPE_NAME];
			}

			// check for motm column
			if (!empty($col[LTD_MOTM]))
			{
				$sanity['motm_value'][$col[RECIPE_ID]] = $col[LTD_MOTM];
			}
		}

		// bypass these if doing an update to an already imported menu
		if (!$updateExistingMenu)
		{
			// all SS are controllable as of March 30, 2020 - RCS
			/*
			if (empty($sanity['show_on_web']))
			{
				return "No stations specified as WEB";
			}
			*/

			/*
			if (empty($sanity['available_intro']) || count($sanity['available_intro']) <> 12)
			{
				return "Too few or too many intro items, should be 12 items";
			}
			*/

			if (empty($sanity['available_dreamtaste']) || count($sanity['available_dreamtaste']) <> 5)
			{
				return "Too few or too many Meal Prep Workshop items, should be 5 items";
			}

			if (empty($sanity['motm_value']))
			{
				return "No meal of the month value specified";
			}
		}

		// all good? return true
		return true;
	}

}

?>