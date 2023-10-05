<?php
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

define('RECIPE_ID', 0);							// A
define('RECIPE_NAME', 1);						// B
define('INGREDIENTS', 2);						// C
define('PACKAGING', 3);							// D
define('SIX_SERV_ONLY', 4);						// E
define('COOKING_METHOD', 5);					// F
define('COOK_TIME_3_SVG',6);					// G
define('COOK_TIME_6_SVG', 7);					// H
define('GRILL_ICON', 8);						// I
define('FROM_FROZEN_ICON', 9);					// J
define('UNDER_30_ICON', 10);					// K
define('HEART_ICON', 11);						// L
define('EVERYDAY_DINNER', 12);					// M
define('GOURMET', 13);							// N
define('FLAVOR_PROFILE', 14);					// O
define('RECIPE_EXPERT', 15);					// P
define('CI_YOUTUBE_ID', 16);					// Q
define('ALLERGENS', 17);						// R
define('UPC_3_SVG', 18);						// S
define('UPC_6_SVG', 19);						// T
define('WEIGHT_3_SVG', 20);						// U
define('WEIGHT_6_SVG', 21);						// V
define('SERVINGS_PER_CONTAINER_MEDIUM', 22);	// W
define('SERVINGS_PER_CONTAINER_LARGE', 23);		// X
define('COOKING_INSTRUCTIONS_MEDIUM', 24);		// Y
define('COOKING_INSTRUCTIONS_LARGE', 25);		// Z
define('CATEGORY_ID', 26);						// AA
define('SERVINGS_SIZE_COMBINED', 27);			// AB
define('NOTES', 28);							// AC
define('COMPONENT_NAME', 29);					// AD - Serving Size Itemized
/*												// AE - Calories
|												// AF - Fat
|												// AG - Sat Fat
|												// AH - Trans Fats
|												// AI - Cholesterol
|												// AJ - Carbs
|												// AK - Fiber
|												// AL - Sugars
|												// AM - Protein
|												// AN - Sodium
|												// AO - % Vit C
|												// AP - % Calcium
|												// AQ - % Iron
												// AR - % Vit A
*/

class page_admin_import_base_recipes extends CPageAdminOnly
{
	function runHomeOfficeManager() {
		$this->importRecipes();
	}

	function runSiteAdmin() {
		$this->importRecipes();
	}

	function importRecipes()
	{
		$tpl = CApp::instance()->template();

		if (!empty($_FILES['base_recipe_import']) && $_FILES['base_recipe_import']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['base_recipe_import']['tmp_name']))
		{
			set_time_limit(100000);
			$labels = array();

			try
			{
				if (!$fp = file_get_contents($_FILES['base_recipe_import']['tmp_name']))
				{
					CLog::Record('Recipe import failed: fopen failed');
					$tpl->setErrorMsg('Recipe import failed: fopen failed');
					exit;
				}

				// massage data into individual rows
				$fp_massage = str_replace("\r\n", "\r", $fp);
				$fp_massage = str_replace("\n", "|", $fp_massage);
				$fp_massage = str_replace("\r", "\n", $fp_massage);

				// explode each recipe row
				$filearray = explode("\n", $fp_massage);

				$uberObject = DAO_CFActory::create('recipe');
				$uberObject->query('START TRANSACTION;');

				$count = 0;
				$imported_recipe_id_array = array();

				while (list($var, $buffer) = each($filearray))
				{
					// make sure it isn't an empty line, sometimes show up at the end of the file.
					$buffer = trim($buffer);

					if (!empty($buffer))
					{
						$valArr = explode("\t", $buffer);

						if ($count == 0)
						{
							$labels = $valArr;
						}
						else
						{
							$recipe_id = self::stickIt($valArr, $labels, $tpl);

							$imported_recipe_id_array[$recipe_id] = $recipe_id;
						}

						$count++;
					}
				}

				// assign Manufacturing labels to stores
				$storeArray = array(
					'291' => 'Mobile',
					'194' => 'Salt Lake',
					'159' => 'Rochester Hills'
				);

				foreach($storeArray AS $store_id => $store)
				{
					foreach($imported_recipe_id_array AS $recipe_id => $recipe)
					{
						CRecipe::assignManufacturingRecipe($store_id, $recipe_id, true);
					}
				}
				// end assign Manufacturing labels to stores

				$uberObject->query('COMMIT;');
				$tpl->setStatusMsg('Recipe imported ' . ($count - 1) .' items successfully.<br />Go to <a href="/backoffice/manage_manufacturer_items" class="btn btn-primary btn-sm">Manage Labels</a> to give store access to new recipe labels.');
			}
			catch (exception $e)
			{
				$uberObject->query('ROLLBACK;');
				$tpl->setErrorMsg('Recipe import failed: exception occurred</br>Reason: ' . $e->getMessage());
				CLog::RecordException($e);
			}
		}
	}

	function makeSQLDate($inStr)
	{
		$asTS = strtotime($inStr);

		return date("Y-m-d G:i:00", $asTS);
	}

	function myCharConversions($inStr)
	{
		$inStr = trim($inStr);
		$chars = array("<br />","\x92", "\x93", "\x94", "\xC2", "\xAE", "\"\"");
		$ents = array("\n", "'", "\"", "\"", "", "&reg;", "\"");

		return trim(trim(str_replace($chars, $ents, $inStr), '"'));
	}

	function myCookingInstConversions($inStr)
	{
		$inStr = trim($inStr);
		$chars = array("|", '""');
		$ents = array("\n", '"');

		return trim(trim(str_replace($chars, $ents, $inStr), '"'));
	}

	private function stickIt($fields, $labels, &$tpl)
	{
		try
		{
			$fieldCount = 0;
			$expectedNumComps = 1;
			$recipeID = null;
			$thisRecipeRowID = null;
			$component_ids = array();
			// trim all fields
			$fields = array_map('trim', $fields);

			foreach ($fields as &$thisField)
			{
				$thisField = self::myCharConversions(trim($thisField));

				if ($fieldCount == RECIPE_ID)
				{
					$recipeID = $thisField;
					if (empty($recipeID) || !is_numeric($recipeID))
					{
						throw new Exception("Invalid Recipe ID");
					}

					$thisRecipe = DAO_CFactory::create('recipe');
					$thisRecipe->recipe_id = $recipeID;
					$thisRecipe->override_menu_id = 'null';

					$recipeExists = $thisRecipe->find(true);

					if ($recipeExists)
					{
						$orgRecipe = clone($thisRecipe);
					}

					$thisRecipe->recipe_name = self::myCharConversions($fields[RECIPE_NAME]);
					$thisRecipe->flag_grill_friendly = empty($fields[GRILL_ICON]) ? 0 : 1;
					$thisRecipe->flag_under_thirty = empty($fields[UNDER_30_ICON]) ? 0 : 1;
					$thisRecipe->flag_heart_healthy = empty($fields[HEART_ICON]) ? 0 : 1;
					$thisRecipe->everyday_dinner = empty($fields[EVERYDAY_DINNER]) ? 0 : 1;
					$thisRecipe->gourmet = empty($fields[GOURMET]) ? 0 : 1;
					$thisRecipe->flavor_profile = trim(self::myCharConversions($fields[FLAVOR_PROFILE]),'" ');
					$thisRecipe->packaging = trim(self::myCharConversions($fields[PACKAGING]),'" ');
					$thisRecipe->recipe_expert = trim(self::myCharConversions($fields[RECIPE_EXPERT]),'" ');
					$thisRecipe->cooking_instruction_youtube_id = empty($fields[CI_YOUTUBE_ID]) ? 0 : $fields[CI_YOUTUBE_ID];
					$thisRecipe->ingredients = self::myCharConversions($fields[INGREDIENTS]);
					$thisRecipe->allergens = self::myCharConversions($fields[ALLERGENS]);
					$thisRecipe->menu_item_category_id = self::myCharConversions($fields[CATEGORY_ID]);

					if ($thisRecipe->menu_item_category_id == 'Core')
					{
						$thisRecipe->menu_item_category_id = 1;
					}

					if ($thisRecipe->menu_item_category_id == 'SS')
					{
						$thisRecipe->menu_item_category_id = 9;
					}

					if (!is_numeric($thisRecipe->menu_item_category_id))
					{
						$thisRecipe->menu_item_category_id = null;
					}

					if (empty($fields[SIX_SERV_ONLY]))
					{
						$RecipeSizeDAO = DAO_CFactory::create('recipe_size');
						$RecipeSizeDAO->recipe_id = $recipeID;
						$RecipeSizeDAO->menu_id = 'null';
						$RecipeSizeDAO->recipe_size = "MEDIUM";

						if ($RecipeSizeDAO->find(true))
						{
							$ORG = clone($RecipeSizeDAO);
							$RecipeSizeDAO->serving_size_combined = self::myCharConversions($fields[SERVINGS_SIZE_COMBINED]);
							$RecipeSizeDAO->servings_per_container = self::myCharConversions($fields[SERVINGS_PER_CONTAINER_MEDIUM]);
							$RecipeSizeDAO->weight = (!empty($fields[WEIGHT_3_SVG]) ? $fields[WEIGHT_3_SVG] : 0);
							$RecipeSizeDAO->upc = self::myCharConversions($fields[UPC_3_SVG]);
							$RecipeSizeDAO->cooking_time = self::myCharConversions($fields[COOK_TIME_3_SVG]);
							$RecipeSizeDAO->cooking_instructions = self::myCookingInstConversions($fields[COOKING_INSTRUCTIONS_MEDIUM]);
							$RecipeSizeDAO->update($ORG);
						}
						else
						{
							$RecipeSizeDAO->serving_size_combined = self::myCharConversions($fields[SERVINGS_SIZE_COMBINED]);
							$RecipeSizeDAO->servings_per_container = self::myCharConversions($fields[SERVINGS_PER_CONTAINER_MEDIUM]);
							$RecipeSizeDAO->weight = (!empty($fields[WEIGHT_3_SVG]) ? $fields[WEIGHT_3_SVG] : 0);
							$RecipeSizeDAO->upc = self::myCharConversions($fields[UPC_3_SVG]);
							$RecipeSizeDAO->cooking_time = self::myCharConversions($fields[COOK_TIME_3_SVG]);
							$RecipeSizeDAO->cooking_instructions = self::myCookingInstConversions($fields[COOKING_INSTRUCTIONS_MEDIUM]);
							$RecipeSizeDAO->insert();

						}
					}

					if (true) // do six serving
					{
						$RecipeSizeDAO = DAO_CFactory::create('recipe_size');
						$RecipeSizeDAO->recipe_id = $recipeID;
						$RecipeSizeDAO->menu_id = 'null';
						$RecipeSizeDAO->recipe_size = "LARGE";

						if ($RecipeSizeDAO->find(true))
						{
							$ORG = clone($RecipeSizeDAO);
							$RecipeSizeDAO->serving_size_combined = self::myCharConversions($fields[SERVINGS_SIZE_COMBINED]);
							$RecipeSizeDAO->servings_per_container = self::myCharConversions($fields[SERVINGS_PER_CONTAINER_LARGE]);
							$RecipeSizeDAO->weight = (!empty($fields[WEIGHT_6_SVG]) ? $fields[WEIGHT_6_SVG] : 0);
							$RecipeSizeDAO->upc = self::myCharConversions($fields[UPC_6_SVG]);
							$RecipeSizeDAO->cooking_time = self::myCharConversions($fields[COOK_TIME_6_SVG]);
							$RecipeSizeDAO->cooking_instructions = self::myCookingInstConversions($fields[COOKING_INSTRUCTIONS_LARGE]);
							$RecipeSizeDAO->update($ORG);
						}
						else
						{
							$RecipeSizeDAO->serving_size_combined = self::myCharConversions($fields[SERVINGS_SIZE_COMBINED]);
							$RecipeSizeDAO->servings_per_container = self::myCharConversions($fields[SERVINGS_PER_CONTAINER_LARGE]);
							$RecipeSizeDAO->weight = (!empty($fields[WEIGHT_6_SVG]) ? $fields[WEIGHT_6_SVG] : 0);
							$RecipeSizeDAO->upc = self::myCharConversions($fields[UPC_6_SVG]);
							$RecipeSizeDAO->cooking_time = self::myCharConversions($fields[COOK_TIME_6_SVG]);
							$RecipeSizeDAO->cooking_instructions = self::myCookingInstConversions($fields[COOKING_INSTRUCTIONS_LARGE]);
							$RecipeSizeDAO->insert();

						}
					}

					if (!$recipeExists)
					{
						$thisRecipe->version = 1;
						$thisRecipe->insert();
						$thisRecipeRowID = $thisRecipe->id;
					}
					else
					{
						$thisRecipeRowID = $thisRecipe->id;
						$thisRecipe->update($orgRecipe);
					}

					$fieldCount++;
					continue;
				}

				if ($fieldCount > 0 && $fieldCount < COMPONENT_NAME)
				{
					$fieldCount++;
					continue; // handled with field 0
				}

				if ($fieldCount == COMPONENT_NAME)
				{
					$theseComps = explode("|", $thisField);
					$expectedNumComps = count($theseComps);
					$fieldCount++;

					if ($expectedNumComps == 1 && empty($theseComps[0]))
					{
						$theseComps[0] = "Entr&eacute;e";
					}

					// create the components
					$compCount = 1;

					$notesArr =  explode("|", self::myCharConversions($fields[NOTES], '" '));

					foreach($notesArr as &$thisNote)
					{
						if ($thisNote == ".") $thisNote = "";
					}

					$ingredsArr =  explode("|", self::myCharConversions($fields[INGREDIENTS], '" '));

					foreach($ingredsArr as &$thisIngred)
					{
						if ($thisIngred == ".") $thisIngred = "";
					}

					$exCompsArr = array();
					$existingComps = DAO_CFactory::create('recipe_component');
					$existingComps->query("select * from recipe_component where recipe_id = '". $thisRecipeRowID . "' AND is_deleted = '0'");
					while ($existingComps->fetch())
					{
						$exCompsArr[$existingComps->component_number] = clone($existingComps);
					}

					if (!empty($exCompsArr) && count($exCompsArr) != count($theseComps))
					{
						// throw new Exception("Number of components changed");
						// Now will update existing components or delete non-updated. Nov 28 2017 SNOOK
					}

					foreach ($theseComps as $thisComp)
					{
						if ($thisComp != "")
						{
							if (!empty($exCompsArr) && !empty($exCompsArr[$compCount]))
							{
								$compObj = $exCompsArr[$compCount];
								$orgComp = clone($compObj);
								$compObj->recipe_number = $recipeID;
								$compObj->recipe_id = $thisRecipeRowID;
								$compObj->serving = $thisComp;
								$compObj->component_number = $compCount;
								$compObj->notes = (!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");

								$compObj->update($orgComp);
								$component_ids[$compCount] = $compObj->id;

								// this component has been updated, remove it from array, remaining array items will be deleted from table
								unset($exCompsArr[$compCount]);

								$compCount++;
							}
							else
							{
								$compObj = DAO_CFactory::create('recipe_component');
								$compObj->recipe_number = $recipeID;
								$compObj->recipe_id = $thisRecipeRowID;
								$compObj->serving = $thisComp;
								$compObj->component_number = $compCount;
								$compObj->notes = (!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");

								$compObj->insert();
								$component_ids[$compCount] = $compObj->id;
								$compCount++;
							}
						}
					}

					// remove remaining old recipe components beyond what was updated
					foreach ($exCompsArr as $removeComp)
					{
						// remove nutrition data for component
						$nutComp = DAO_CFactory::create('nutrition_data');
						$nutComp->component_id = $removeComp->id;
						$nutComp->find();

						while($nutComp->fetch())
						{
							$nutComp->delete();
						}

						$removeComp->delete();
					}

					continue;
				}

				if ($fieldCount > 0 && $fieldCount <= NOTES)
				{
					$fieldCount++;
					continue; // handled with field components
				}

				$thisLabel = trim($labels[$fieldCount]);
				$theseNuts = explode("|", $thisField);

				$thisNutElement = DAO_CFactory::create('nutrition_element');
				$thisNutElement->label = $thisLabel;

				if (!$thisNutElement->find(true))
				{
					// let's not handle this anymore ... just bail so we know this happened
					throw new Exception("Nutrition element not found Recipe " . $recipeID);

					$thisNutElement->display_label = $thisLabel;
					$thisNutElement->insert();
					//echo "Creating new Nutrition Element: $thisLabel" . "\n";
				}

				$compCount = 1;
				foreach ($theseNuts as $thisNut)
				{
					$thisNutData = DAO_CFactory::create('nutrition_data');

					if (!isset($component_ids[$compCount]))
					{
						throw new Exception("Invalid Component ID for {$thisRecipe->recipe_id}");
					}

					$thisNutData->component_id = $component_ids[$compCount];
					$thisNutData->component_number = $compCount++;
					$thisNutData->nutrition_element = $thisNutElement->id;

					$dataExists = $thisNutData->find(true);

					if (!$dataExists)
					{
						$thisNutData->nutrition_element = $thisNutElement->id;
						$thisNutData->value = $thisNut;

						if (strpos($thisNut, "*") !== false)
						{
							$thisNutData->note_indicator = "*";
						}


						$thisNutData->insert();
					}
					else
					{
						$orgData = clone($thisNutData);
						$thisNutData->nutrition_element = $thisNutElement->id;
						$thisNutData->value = $thisNut;

						if (strpos($thisNut, "*") !== false)
						{
							$thisNutData->note_indicator = "*";
						}

						$thisNutData->update($orgData);
					}
				}

				$fieldCount++;
			}

			return $thisRecipe->recipe_id;
		}
		catch(exception $e)
		{
			$tpl->setErrorMsg('Nutrition import failed: inner loop exception occurred<br />Reason: ' .$e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}
}
?>