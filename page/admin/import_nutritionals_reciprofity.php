<?php
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once 'includes/class.Diff.php';
require_once("DAO/BusinessObject/CImportReciprofity.php");

class page_admin_import_nutritionals_reciprofity extends CPageAdminOnly
{
	static public $changelog = array();
	static public $testMode = false;
	static public $updateExistingMenu = false;

	static private function addToChangeLog($isDefault, $recipe_id, $recipe_name, $message, $diff = null, $hilight = 'none')
	{

		if ($hilight == 'red')
		{
			$message = "<span style='color:red;'>" . $message . "</span>";
		}

		if ($isDefault)
		{
			if (isset(self::$changelog[$recipe_id]))
			{
				self::$changelog[$recipe_id]['default_events'][] = array(
					'message' => $message,
					'diff' => $diff
				);
			}
			else
			{
				self::$changelog[$recipe_id] = array(
					'name' => $recipe_name,
					'default_events' => array(
						0 => array(
							'message' => $message,
							'diff' => $diff
						)
					),
					'events' => array()
				);
			}
		}
		else
		{
			if (isset(self::$changelog[$recipe_id]))
			{
				self::$changelog[$recipe_id]['events'][] = array(
					'message' => $message,
					'diff' => $diff
				);
			}
			else
			{
				self::$changelog[$recipe_id] = array(
					'name' => $recipe_name,
					'events' => array(
						0 => array(
							'message' => $message,
							'diff' => $diff
						)
					),
					'default_events' => array()
				);
			}
		}
	}

	function runSiteAdmin()
	{
		$this->importNuts();
	}

	function importNuts()
	{

		ini_set('memory_limit', '2000M');

		$tpl = CApp::instance()->template();

		if (isset($_REQUEST['testmode']))
		{
			self::$testMode = true;
		}
		$tpl->assign('testmode', self::$testMode);

		if (!empty($_POST['menu']) && !empty($_FILES['base_menu_import']) && $_FILES['base_menu_import']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['base_menu_import']['tmp_name']))
		{
			// check if we're updating an existing menu
			$menuArray = self::getMenuOptionsArray();
			if (!empty($menuArray[$_POST['menu']]['data']['data-imported']) && $menuArray[$_POST['menu']]['data']['data-imported'] == 'true')
			{
				self::$updateExistingMenu = true;
			}

			set_time_limit(100000);

			try
			{
				$uberObject = DAO_CFActory::create('menu_item');
				$uberObject->query('START TRANSACTION;');

				$rows = CImportReciprofity::distillCSVImport($_FILES['base_menu_import'], $tpl, true);

				// check data for problems
				$sanityResult = CImportReciprofity::sanityCheck($rows, self::$updateExistingMenu, true);
				if ($sanityResult !== true)
				{
					throw new Exception($sanityResult);
				}

				$labels = array_shift($rows);

				// passed sanity checks, now go over the array again and insert the information
				foreach ($rows as $row => $col)
				{
					if (self::$testMode)
					{
						self::compareIt($col, $labels, $tpl, CGPC::do_clean($_POST['menu'], TYPE_INT));
					}
					else
					{
						self::stickIt($col, $labels, $tpl, CGPC::do_clean($_POST['menu'], TYPE_INT));
					}
				}

				if (self::$testMode)
				{
					$tpl->assign('changelog', self::$changelog);
				}
				else
				{
					$tpl->setStatusMsg('<p>Nutrition imported: <a class="button" href="/nutritionals?store=166&amp;menu=' . CGPC::do_clean($_POST['menu'], TYPE_INT) . '&amp;show_entree=1&amp;show_efl=1&amp;show_ft=1&amp;filter_zero_inventory=1" target="_blank">Review</a></p><p>Next Step: <a class="button" href="/backoffice/import_sidesmap_reciprofity">Import Sides Map</a></p>');
				}

				$uberObject->query('COMMIT;');
			}
			catch (exception $e)
			{
				$commit = false;

				$uberObject->query('ROLLBACK;');
				$tpl->setErrorMsg('Nutrition import failed: exception occurred</br>Reason: ' . $e->getMessage());
				CLog::RecordException($e);
			}
		}

		/* Import menu fadmin template stuff below */
		// get fresh menu array state
		$menuArray = self::getMenuOptionsArray();

		$Form = new CForm();

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "menu",
			CForm::options => $menuArray
		));

		$tpl->assign('form_menu', $Form->Render());
		$tpl->assign('menu_count', (count($menuArray) - 1));
	}

	private static function getMenuOptionsArray()
	{
		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT
			m.id,
			m.menu_name,
			m.menu_start,
			COUNT(r.id) AS recipe_count
			FROM menu AS m
			LEFT JOIN recipe AS r ON r.override_menu_id = m.id AND r.is_deleted = '0'
			WHERE m.is_deleted = '0'
			GROUP BY m.id
			ORDER BY m.id DESC
			LIMIT 6");

		$menuArray = array(0 => 'Select Menu');
		$menu_count = 0;

		while ($Menu->fetch())
		{
			$menuArray[$Menu->id] = array(
				'title' => $Menu->menu_name . (!empty($Menu->recipe_count) ? ' (imported)' : ''),
				'data' => array(
					'data-imported' => (!empty($Menu->recipe_count) ? 'true' : 'false')
				)
			);
			$menu_count++;
		}

		return $menuArray;
	}

	private static function cookingMethod($method)
	{
		// temp until we have reliable entries that could be turned into enum
		return $method;

		switch ($method)
		{
			case 'stovetop':
				return 'STOVETOP';
				break;
			case 'oven':
				return 'OVEN';
				break;
			case 'grill':
				return 'GRILL';
				break;
			default:
				return '';
				break;
		}
	}

	private static function stickIt($fields, $labels, $tpl, $menu_id)
	{
		try
		{
			$thisRecipeRowID = null;
			$component_ids = array();

			if (empty($fields['recipe_id']) || !is_numeric($fields['recipe_id']))
			{
				throw new Exception("Invalid Recipe ID: " . $fields['recipe_id']);
			}

			$thisNullRecipe = DAO_CFactory::create('recipe');
			$thisNullRecipe->recipe_id = $fields['recipe_id'];
			$thisNullRecipe->override_menu_id = 'null';

			$nullRecipeExists = $thisNullRecipe->find(true);

			if ($nullRecipeExists)
			{
				$orgNullRecipe = clone($thisNullRecipe);
			}

			$thisRecipe = DAO_CFactory::create('recipe');
			$thisRecipe->recipe_id = $fields['recipe_id'];
			$thisRecipe->override_menu_id = $menu_id;

			$recipeExists = $thisRecipe->find(true);

			if ($recipeExists)
			{
				$orgRecipe = clone($thisRecipe);
			}

			// strip off size marker
			$sizeStrPos = strpos($fields[RECIPE_NAME], "|");
			//Rule: all characters after the first pipe symbol is stripped off
			if ($sizeStrPos !== false)
			{
				$fields[RECIPE_NAME] = substr($fields[RECIPE_NAME], 0, $sizeStrPos);
			}

			$thisNullRecipe->recipe_name = $thisRecipe->recipe_name = trim(CImportReciprofity::charConversions($fields[RECIPE_NAME]), '" ');
			$thisNullRecipe->flag_grill_friendly = $thisRecipe->flag_grill_friendly = (strtolower($fields[GRILL_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_cooks_from_frozen = $thisRecipe->flag_cooks_from_frozen = (strtolower($fields[FROM_FROZEN_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_crockpot = $thisRecipe->flag_crockpot = (strtolower($fields[CROCKPOT_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_under_400 = $thisRecipe->flag_under_400 = (strtolower($fields[UNDER_400_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_under_thirty = $thisRecipe->flag_under_thirty = (strtolower($fields[UNDER_30_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_no_added_salt = $thisRecipe->flag_no_added_salt = (strtolower($fields[NO_SALT_ADDED]) == 'yes' ? 1 : 0);
			$thisNullRecipe->gluten_friendly = $thisRecipe->gluten_friendly = (strtolower($fields[GLUTEN_FRIENDLY_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->air_fryer = $thisRecipe->air_fryer = (strtolower($fields[AIR_FRYER_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->high_protein = $thisRecipe->high_protein = (strtolower($fields[HIGH_PROTEIN_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->vegetarian = $thisRecipe->vegetarian = (strtolower($fields[VEGETARIAN_ICON]) == 'yes' ? 1 : 0);
			// 	$thisNullRecipe->flag_heart_healthy = $thisRecipe->flag_heart_healthy = empty($fields[HEART_ICON]) ? 0 : 1;
			//	$thisNullRecipe->kid_friendly = $thisRecipe->kid_friendly = empty($fields[KID_FRIENDLY]) ? 0 : 1;
			//	$thisNullRecipe->everyday_dinner = $thisRecipe->everyday_dinner = empty($fields[EVERYDAY_DINNER]) ? 0 : 1;
			//	$thisNullRecipe->gourmet = $thisRecipe->gourmet = empty($fields[GOURMET]) ? 0 : 1;
			//	$thisNullRecipe->flavor_profile = $thisRecipe->flavor_profile = trim(CImportReciprofity::charConversions($fields[FLAVOR_PROFILE]), '" ');
			//	$thisNullRecipe->packaging = $thisRecipe->packaging = trim(CImportReciprofity::charConversions($fields[PACKAGING]), '" ');
			//	$thisNullRecipe->recipe_expert = $thisRecipe->recipe_expert = trim(CImportReciprofity::charConversions($fields[RECIPE_EXPERT]), '" ');
			$thisNullRecipe->ingredients = $thisRecipe->ingredients = CImportReciprofity::spellCheck('ingredient', trim(CImportReciprofity::charConversions($fields[INGREDIENTS]), '" '));
			$thisNullRecipe->allergens = $thisRecipe->allergens = trim(CImportReciprofity::charConversions($fields[ALLERGENS]), '" ');
			//$thisNullRecipe->cooking_instruction_youtube_id = $thisRecipe->cooking_instruction_youtube_id = trim(CImportReciprofity::charConversions($fields[CI_YOUTUBE_ID]), '" ');
			$thisNullRecipe->cooking_method = $thisRecipe->cooking_method = trim(self::cookingMethod($fields[COOKING_METHOD]), '" ');
			$thisNullRecipe->ltd_menu_item_value = $thisRecipe->ltd_menu_item_value = ((!empty($fields[LTD_MOTM]) && $fields[LTD_MOTM] == "Yes") ? 1 : 0);

			// update menu specific
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

			// update null set
			if (!$nullRecipeExists)
			{
				$thisNullRecipe->version = 1;
				$thisNullRecipe->insert();
				$thisNullRecipeRowID = $thisNullRecipe->id;
			}
			else
			{
				$thisNullRecipeRowID = $thisNullRecipe->id;
				$thisNullRecipe->update($orgNullRecipe);
			}

			// ------------------------ Prepare to update components

			$theseComps = explode("\n", $fields[COMPONENT_NAME]);
			$expectedNumComps = count($theseComps);

			if ($expectedNumComps == 1 && empty($theseComps[0]))
			{
				throw new Exception("Serving Size string missing for {$thisRecipe->recipe_id}");
			}

			// create the components
			$compCount = 1;

			// ------------------------ INSERT, UPDATE or DELETE menu specific components
			$component_ids = array();
			$exCompsArr = array();
			$existingComps = DAO_CFactory::create('recipe_component');
			$existingComps->query("SELECT * FROM recipe_component WHERE recipe_id = '" . $thisRecipeRowID . "' AND is_deleted = '0'");

			while ($existingComps->fetch())
			{
				$exCompsArr[$existingComps->component_number] = clone($existingComps);
			}

			$componentNumberChange = false;
			if (!empty($exCompsArr) && count($exCompsArr) != count($theseComps))
			{
				$componentNumberChange = true;
			}

			foreach ($theseComps as $thisComp)
			{

				if ($thisComp != "")
				{
					if (!empty($exCompsArr[$compCount]))
					{
						$compObj = $exCompsArr[$compCount];
						$orgComp = clone($compObj);
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->recipe_id = $thisRecipeRowID;
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = (!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");
						$compObj->hasBeenUpdated = true;

						$compObj->update($orgComp);
						$component_ids[$compCount] = $compObj->id;
						$compCount++;
					}
					else
					{
						$compObj = DAO_CFactory::create('recipe_component');
						$compObj->recipe_number = $fields['recipe_id'];
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

			// find any components that existed but were not present in the import and delete them
			foreach ($exCompsArr as $thisComp)
			{
				if (empty($thisComp->hasBeenUpdated))
				{
					$thisComp->delete();

					$thisNutData = DAO_CFactory::create('nutrition_data');
					$thisNutData->query("update nutrition_data set is_deleted = 0 where component_id = {$thisComp->id}");
				}
			}

			// ------------------------ INSERT, UPDATE or DELETE Default (null menu) components
			$compCount = 1;
			$null_component_ids = array();
			$exNullCompsArr = array();
			$existingNullComps = DAO_CFactory::create('recipe_component');
			$existingNullComps->query("SELECT * FROM recipe_component WHERE recipe_id = '" . $thisNullRecipeRowID . "' AND is_deleted = '0'");

			while ($existingNullComps->fetch())
			{
				$exNullCompsArr[$existingNullComps->component_number] = clone($existingNullComps);
			}

			$nullComponentNumberChange = false;
			if (!empty($exNullCompsArr) && count($exNullCompsArr) != count($theseComps))
			{
				$nullComponentNumberChange = true;
			}

			foreach ($theseComps as $thisComp)
			{

				if ($thisComp != "")
				{
					if (!empty($exNullCompsArr) && isset($exNullCompsArr[$compCount]))
					{
						$compObj = $exNullCompsArr[$compCount];
						$orgComp = clone($compObj);
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->recipe_id = $thisNullRecipeRowID;
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ''; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");
						$compObj->hasBeenUpdated = true;

						$compObj->update($orgComp);
						$null_component_ids[$compCount] = $compObj->id;
						$compCount++;
					}
					else
					{
						$compObj = DAO_CFactory::create('recipe_component');
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->recipe_id = $thisNullRecipeRowID;
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ''; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");

						$compObj->insert();
						$null_component_ids[$compCount] = $compObj->id;
						$compCount++;
					}
				}
			}

			// find any components that existed but were not present in the import and delete them
			foreach ($exNullCompsArr as $thisComp)
			{
				if (empty($thisComp->hasBeenUpdated))
				{
					$thisComp->delete();
					$thisComp->query("update nutrition_data set is_deleted = 1 where component_id = {$thisComp->id}");
				}
			}

			// ---------------------------------- do nutritional elements
			$componentArray = array(
				'NUT_CALORIES' => NUT_CALORIES,
				'NUT_FAT' => NUT_FAT,
				'NUT_SATFAT' => NUT_SATFAT,
				'NUT_TRANSFAT' => NUT_TRANSFAT,
				'NUT_CHOLESTEROL' => NUT_CHOLESTEROL,
				'NUT_CARBS' => NUT_CARBS,
				'NUT_FIBER' => NUT_FIBER,
				'NUT_SUGARS' => NUT_SUGARS,
				'NUT_ADDED_SUGARS' => NUT_ADDED_SUGARS,
				'NUT_PROTEIN' => NUT_PROTEIN,
				'NUT_SODIUM' => NUT_SODIUM,
				'NUT_VIT_A' => NUT_VIT_A,
				'NUT_VIT_C' => NUT_VIT_C,
				'NUT_VIT_D' => NUT_VIT_D,
				'NUT_IRON' => NUT_IRON,
				'NUT_CALCIUM' => NUT_CALCIUM,
				'NUT_POTASSIUM_K' => NUT_POTASSIUM_K
			);

			$ReciprofityToDDNutNameMap = array(
				'calories' => 'Calories',
				'fat' => 'Fat',
				'sat_fat' => 'Sat Fat',
				'trans_fat' => 'Trans Fats',
				'cholesterol' => 'Cholesterol',
				'carbs' => 'Carbs',
				'fiber' => 'Fiber',
				'sugars' => 'Sugars',
				'added_sugars' => 'Added Sugar',
				'protein' => 'Protein',
				'sodium' => 'Sodium',
				'vit_a' => 'Vit A',
				'vit_c' => 'Vit C',
				'vit_d' => 'Vit D',
				'calcium' => 'Calcium',
				'potasium' => 'Potassium (K)',
				'iron' => 'Iron'
			);

			foreach ($componentArray as $thisField)
			{
				$thisLabel = $ReciprofityToDDNutNameMap[trim($labels[$thisField])];
				$theseNuts = explode("\n", $fields[$thisField]);

				$thisNutElement = DAO_CFactory::create('nutrition_element');
				$thisNutElement->label = $thisLabel;

				if (!$thisNutElement->find(true))
				{
					// let's not handle this anymore ... just bail so we know this happened
					throw new Exception("Invalid Recipe Label: " . $thisLabel);

					$thisNutElement->display_label = $thisLabel;
					$thisNutElement->insert();
				}

				$compCount = 1;

				foreach ($theseNuts as $thisNut)
				{
					$numberComponentOfNut = array();
					$origVal = $thisNut;
					preg_match('!\d+\.*\d*!', $thisNut, $numberComponentOfNut, PREG_OFFSET_CAPTURE);
					if (count($numberComponentOfNut) != 1)
					{
						// let's not handle this anymore ... just bail so we know this happened
						throw new Exception("Invalid Nutrition Amount: " . $thisLabel);
					}
					else
					{
						$thisNut = $numberComponentOfNut[0][0];
					}

					$inputHasLessThan = '';
					if (strpos($origVal, "<") === 0)
					{
						$inputHasLessThan = "<";
					}

					$inputHasAsterisk = '';
					if (strpos($origVal, "*") === 0)
					{
						$inputHasAsterisk = "*";
					}

					$thisNutData = DAO_CFactory::create('nutrition_data');

					if (!isset($component_ids[$compCount]))
					{
						throw new Exception("Invalid Component ID for {$thisRecipe->recipe_id}");
					}

					// menu specific data

					$thisNutData->component_id = $component_ids[$compCount];
					$thisNutData->component_number = $compCount;
					$thisNutData->nutrition_element = $thisNutElement->id;

					$dataExists = $thisNutData->find(true);

					if (!$dataExists)
					{
						$thisNutData->nutrition_element = $thisNutElement->id;
						$thisNutData->value = $thisNut;
						$thisNutData->prefix = $inputHasLessThan;

						if ($inputHasAsterisk)
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
						$thisNutData->prefix = $inputHasLessThan;

						if ($inputHasAsterisk)
						{
							$thisNutData->note_indicator = "*";
						}

						$thisNutData->update($orgData);
					}

					// default recipe
					$thisNutDataNull = DAO_CFactory::create('nutrition_data');

					if (!isset($null_component_ids[$compCount]))
					{
						throw new Exception("Invalid Component ID for {$thisRecipe->recipe_id}");
					}

					$thisNutDataNull->component_id = $null_component_ids[$compCount];
					$thisNutDataNull->component_number = $compCount++;
					$thisNutDataNull->nutrition_element = $thisNutElement->id;

					$dataExists = $thisNutDataNull->find(true);

					if (!$dataExists)
					{
						$thisNutDataNull->nutrition_element = $thisNutElement->id;
						$thisNutDataNull->value = $thisNut;
						$thisNutDataNull->prefix = $inputHasLessThan;
						if ($inputHasAsterisk)
						{
							$thisNutDataNull->note_indicator = "*";
						}

						$thisNutDataNull->insert();
					}
					else
					{
						$orgData = clone($thisNutDataNull);
						$thisNutDataNull->nutrition_element = $thisNutElement->id;
						$thisNutDataNull->value = $thisNut;
						$thisNutDataNull->prefix = $inputHasLessThan;

						if ($inputHasAsterisk)
						{
							$thisNutDataNull->note_indicator = "*";
						}
						$thisNutDataNull->update($orgData);
					}
				}
			}
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Nutrition import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}

	static function hasDiff($diff_result)
	{
		foreach ($diff_result as $thisChar)
		{
			if ($thisChar[1] != 0)
			{
				return true;
			}
		}

		return false;
	}

	static $fieldFilter = array(
		'timestamp_updated',
		'timestamp_created',
		'created_by',
		'updated_by'
	);
	static $fullDiffList = array(
		'recipe_name',
		'recipe_expert',
		'ingredients',
		'allergens'
		/*,
			   'flag_under_thirty',
			   'flag_grill_friendly',
			   'flag_cooks_from_frozen',
			   'flag_crockpot',
			   'flag_under_400'*/
	);

	static function compareRecipes($orgItem, $newItem)
	{
		$retVal = array();

		foreach ($orgItem as $k => $v)
		{

			if (!in_array($k, self::$fieldFilter))
			{

				if (in_array($k, self::$fullDiffList))
				{


					$res = Diff::compare($v, $newItem[$k], true);
					if (self::hasDiff($res))
					{
						$fullDiff = Diff::toHTML_DD($res);
						$retVal[] = array(
							'name' => $k,
							'org' => $v,
							'diff' => $fullDiff
						);
					}
				}
				else if ($v != $newItem[$k])
				{
					$retVal[] = array(
						'name' => $k,
						'org' => $v,
						'new' => $newItem[$k]
					);
				}
			}
		}

		return $retVal;
	}

	static $fieldFilterComp = array(
		'timestamp_updated',
		'timestamp_created',
		'created_by',
		'updated_by'
	);
	static $fullDiffListComp = array(
		'servings',
		'notes'
	);

	static function compareComponents($orgItem, $newItem)
	{
		$retVal = array();

		foreach ($orgItem as $k => $v)
		{
			if (!in_array($k, self::$fieldFilterComp))
			{

				if (in_array($k, self::$fullDiffListComp))
				{
					if (empty($v) && empty($newItem[$k]))
					{
						continue;
					}

					$res = Diff::compare($v, $newItem[$k], true);
					if (self::hasDiff($res))
					{
						$fullDiff = Diff::toHTML_DD($res);
						$retVal[] = array(
							'name' => $k,
							'org' => $v,
							'diff' => $fullDiff
						);
					}
				}
				else if ($v != $newItem[$k])
				{
					$retVal[] = array(
						'name' => $k,
						'org' => $v,
						'diff' => $newItem[$k]
					);
				}
			}
		}

		return $retVal;
	}

	private static function compareIt($fields, $labels, $tpl, $menu_id)
	{
		try
		{
			$thisRecipeRowID = null;
			$component_ids = array();

			if (empty($fields['recipe_id']) || !is_numeric($fields['recipe_id']))
			{
				throw new Exception("Invalid Recipe ID: " . $fields['recipe_id']);
			}

			$thisNullRecipe = DAO_CFactory::create('recipe');
			$thisNullRecipe->recipe_id = $fields['recipe_id'];
			$thisNullRecipe->override_menu_id = 'null';

			$nullRecipeExists = $thisNullRecipe->find(true);

			if ($nullRecipeExists)
			{
				$orgNullRecipe = clone($thisNullRecipe);
			}

			$thisRecipe = DAO_CFactory::create('recipe');
			$thisRecipe->recipe_id = $fields['recipe_id'];
			$thisRecipe->override_menu_id = $menu_id;

			$recipeExists = $thisRecipe->find(true);

			if ($recipeExists)
			{
				$orgRecipe = clone($thisRecipe);
			}

			// strip off size marker
			$sizeStrPos = strpos($fields[RECIPE_NAME], "|");
			//Rule: all characters after the first pipe symbol is stripped off
			if ($sizeStrPos !== false)
			{
				$fields[RECIPE_NAME] = substr($fields[RECIPE_NAME], 0, $sizeStrPos);
			}

			$thisNullRecipe->recipe_name = $thisRecipe->recipe_name = trim(CImportReciprofity::charConversions($fields[RECIPE_NAME]), '" ');

			$thisNullRecipe->flag_grill_friendly = $thisRecipe->flag_grill_friendly = (strtolower($fields[GRILL_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_cooks_from_frozen = $thisRecipe->flag_cooks_from_frozen = (strtolower($fields[FROM_FROZEN_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_crockpot = $thisRecipe->flag_crockpot = (strtolower($fields[CROCKPOT_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_under_400 = $thisRecipe->flag_under_400 = (strtolower($fields[UNDER_400_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_under_thirty = $thisRecipe->flag_under_thirty = (strtolower($fields[UNDER_30_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->flag_no_added_salt = $thisRecipe->flag_no_added_salt = (strtolower($fields[NO_SALT_ADDED]) == 'yes' ? 1 : 0);
			$thisNullRecipe->gluten_friendly = $thisRecipe->gluten_friendly = (strtolower($fields[GLUTEN_FRIENDLY_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->air_fryer = $thisRecipe->air_fryer = (strtolower($fields[AIR_FRYER_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->high_protein = $thisRecipe->high_protein = (strtolower($fields[HIGH_PROTEIN_ICON]) == 'yes' ? 1 : 0);
			$thisNullRecipe->vegetarian = $thisRecipe->vegetarian = (strtolower($fields[VEGETARIAN_ICON]) == 'yes' ? 1 : 0);
			//$thisNullRecipe->flag_heart_healthy = $thisRecipe->flag_heart_healthy = empty($fields[HEART_ICON]) ? 0 : 1;
			//$thisNullRecipe->kid_friendly = $thisRecipe->kid_friendly = empty($fields[KID_FRIENDLY]) ? 0 : 1;
			//$thisNullRecipe->everyday_dinner = $thisRecipe->everyday_dinner = empty($fields[EVERYDAY_DINNER]) ? 0 : 1;
			//$thisNullRecipe->gourmet = $thisRecipe->gourmet = empty($fields[GOURMET]) ? 0 : 1;
			//$thisNullRecipe->flavor_profile = $thisRecipe->flavor_profile = trim(CImportReciprofity::charConversions($fields[FLAVOR_PROFILE]), '" ');
			//$thisNullRecipe->packaging = $thisRecipe->packaging = trim(CImportReciprofity::charConversions($fields[PACKAGING]), '" ');
			//$thisNullRecipe->recipe_expert = $thisRecipe->recipe_expert = trim(CImportReciprofity::charConversions($fields[RECIPE_EXPERT]), '" ');
			$thisNullRecipe->ingredients = $thisRecipe->ingredients = CImportReciprofity::spellCheck('ingredient', trim(CImportReciprofity::charConversions($fields[INGREDIENTS]), '" '));
			$thisNullRecipe->allergens = $thisRecipe->allergens = trim(CImportReciprofity::charConversions($fields[ALLERGENS]), '" ');
			//$thisNullRecipe->cooking_instruction_youtube_id = $thisRecipe->cooking_instruction_youtube_id = trim(CImportReciprofity::charConversions($fields[CI_YOUTUBE_ID]), '" ');
			$thisNullRecipe->cooking_method = $thisRecipe->cooking_method = trim(self::cookingMethod($fields[COOKING_METHOD]), '" ');
			$thisNullRecipe->ltd_menu_item_value = $thisRecipe->ltd_menu_item_value = ((!empty($fields[LTD_MOTM]) && $fields[LTD_MOTM] == "Yes") ? 1 : 0);

			// update menu specific
			if (!$recipeExists)
			{
				$thisRecipe->version = 1;

				self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Will add Recipe");
			}
			else
			{
				$thisRecipeRowID = $thisRecipe->id;

				$orgArr = DAO::getCompressedArrayFromDAO($orgRecipe, true, true);
				$targetArr = DAO::getCompressedArrayFromDAO($thisRecipe, true, true);
				$diff = self::compareRecipes($orgArr, $targetArr);

				if (empty($diff))
				{
					self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Recipe Exists - no changes");
				}
				else
				{
					self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Recipe Exists - will update", $diff);
				}
			}

			// update null set
			if (!$nullRecipeExists)
			{
				$thisNullRecipe->version = 1;

				self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Will add Defaullt (null menu) Recipe");
			}
			else
			{
				$thisNullRecipeRowID = $thisNullRecipe->id;

				$orgNullArr = DAO::getCompressedArrayFromDAO($orgNullRecipe, true, true);
				$targetNullArr = DAO::getCompressedArrayFromDAO($thisNullRecipe, true, true);
				$diff = self::compareRecipes($orgNullArr, $targetNullArr);

				if (empty($diff))
				{
					self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Default (null menu) Recipe Exists - no changes");
				}
				else
				{
					self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Default (null menu) Recipe Exists - will update", $diff);
				}
			}

			// do components
			$theseComps = explode("\n", $fields[COMPONENT_NAME]);
			$expectedNumComps = count($theseComps);

			if ($expectedNumComps == 1 && empty($theseComps[0]))
			{
				throw new Exception("Serving Size string missing for {$thisRecipe->recipe_id}");
			}

			// create the components
			$compCount = 1;

			$exCompsArr = array();
			$component_ids = array();

			if (!empty($thisRecipeRowID))
			{
				$existingComps = DAO_CFactory::create('recipe_component');
				$existingComps->query("SELECT * FROM recipe_component WHERE recipe_id = '" . $thisRecipeRowID . "' AND is_deleted = '0'");

				while ($existingComps->fetch())
				{
					$exCompsArr[$existingComps->component_number] = clone($existingComps);
				}

				if (!empty($exCompsArr) && count($exCompsArr) != count($theseComps))
				{
					self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component number mismatch: existing - " . count($exCompsArr) . " | new - " . count($theseComps), $diff);
				}
			}

			$psuedoComponentID = 1;
			// since we don't do inserts we have to make this up

			foreach ($theseComps as $thisComp)
			{

				if ($thisComp != "")
				{
					if (isset($exCompsArr[$compCount]))
					{
						$compObj = $exCompsArr[$compCount];
						$orgComp = clone($compObj);
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->recipe_id = $thisRecipeRowID;
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ''; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");
						$compObj->hasBeenUpdated = true;

						$orgCompArr = DAO::getCompressedArrayFromDAO($orgComp, true, true);
						$targetCompArr = DAO::getCompressedArrayFromDAO($compObj, true, true);
						$diff = self::compareComponents($orgCompArr, $targetCompArr);

						if (empty($diff))
						{
							self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount exists - no changes");
						}
						else
						{
							self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount exists - will update", $diff);
						}

						$component_ids[$compCount] = $compObj->id;
						$compCount++;
					}
					else
					{
						$compObj = DAO_CFactory::create('recipe_component');
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ''; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");

						self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount will be added");

						$component_ids[$compCount] = $psuedoComponentID++;
						$compCount++;
					}
				}
			}

			// find any components that existed but were not present in the import and delete them
			foreach ($exCompsArr as $thisComp)
			{
				if (empty($thisComp->hasBeenUpdated))
				{
					self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component {$thisComp->component_number} exists - will be deleted");
				}
			}

			// -------------------   Default Recipe Components

			$exNullCompsArr = array();
			$null_component_ids = array();

			if (!empty($thisNullRecipeRowID))
			{
				$existingNullComps = DAO_CFactory::create('recipe_component');
				$existingNullComps->query("SELECT * FROM recipe_component WHERE recipe_id = '" . $thisNullRecipeRowID . "' AND is_deleted = '0'");

				while ($existingNullComps->fetch())
				{
					$exNullCompsArr[$existingNullComps->component_number] = clone($existingNullComps);
				}

				if (!empty($exNullCompsArr) && count($exNullCompsArr) != count($theseComps))
				{
					self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component number of Default Recipe mismatch: existing - " . count($exNullCompsArr) . " | new - " . count($theseComps), $diff);
				}
			}

			$psuedoComponentID = 1;
			// since we don't do inserts we have to make this up
			$compCount = 1;

			foreach ($theseComps as $thisComp)
			{

				if ($thisComp != "")
				{
					if (isset($exNullCompsArr[$compCount]))
					{
						$compObj = $exNullCompsArr[$compCount];
						$orgComp = clone($compObj);
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->recipe_id = $thisNullRecipeRowID;
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ''; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");
						$compObj->hasBeenUpdated = true;

						$orgCompArr = DAO::getCompressedArrayFromDAO($orgComp, true, true);
						$targetCompArr = DAO::getCompressedArrayFromDAO($compObj, true, true);
						$diff = self::compareComponents($orgCompArr, $targetCompArr);

						if (empty($diff))
						{
							self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount of Default Recipe exists - no changes");
						}
						else
						{
							self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount of Default Recipe exists - will update", $diff);
						}

						$null_component_ids[$compCount] = $compObj->id;
						$compCount++;
					}
					else
					{
						$compObj = DAO_CFactory::create('recipe_component');
						$compObj->recipe_number = $fields['recipe_id'];
						$compObj->serving = $thisComp;
						$compObj->component_number = $compCount;
						$compObj->notes = ' 	'; //(!empty($notesArr[$compCount - 1]) ? $notesArr[$compCount - 1] : "");

						self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component $compCount of Default Recipe will be added");

						$null_component_ids[$compCount] = $psuedoComponentID++;
						$compCount++;
					}
				}
			}

			// find any components that existed but were not present in the import and delete them
			foreach ($exNullCompsArr as $thisComp)
			{
				if (empty($thisComp->hasBeenUpdated))
				{
					self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Component {$thisComp->component_number} of Default Recipe exists - will be deleted");
				}
			}

			// do nutritional elements
			$componentArray = array(
				'NUT_CALORIES' => NUT_CALORIES,
				'NUT_FAT' => NUT_FAT,
				'NUT_SATFAT' => NUT_SATFAT,
				'NUT_TRANSFAT' => NUT_TRANSFAT,
				'NUT_CHOLESTEROL' => NUT_CHOLESTEROL,
				'NUT_CARBS' => NUT_CARBS,
				'NUT_FIBER' => NUT_FIBER,
				'NUT_SUGARS' => NUT_SUGARS,
				'NUT_ADDED_SUGARS' => NUT_ADDED_SUGARS,
				'NUT_PROTEIN' => NUT_PROTEIN,
				'NUT_SODIUM' => NUT_SODIUM,
				'NUT_VIT_A' => NUT_VIT_A,
				'NUT_VIT_C' => NUT_VIT_C,
				'NUT_VIT_D' => NUT_VIT_D,
				'NUT_CALCIUM' => NUT_CALCIUM,
				'NUT_POTASSIUM_K' => NUT_POTASSIUM_K,
				'NUT_IRON' => NUT_IRON
			);

			$fadminToReciProfityLabelMap = array(
				'Calories' => 'calories',
				'Fat' => 'fat',
				'Sat Fat' => 'sat_fat',
				'Trans Fats' => 'trans_fat',
				'Cholesterol' => 'cholesterol',
				'Carbs' => 'carbs',
				'Fiber' => 'fiber',
				'Sugars' => 'sugars',
				'Protein' => 'protein',
				'Sodium' => 'sodium',
				'Carb Exchanges' => 'vit_a',
				'Vit C' => 'vit_c',
				'Calcium' => 'calcium',
				'Iron' => 'iron',
				'Vit A' => 'vit_a',
				'Added Sugar' => 'added_sugars',
				'Potassium (K)' => 'potasium',
				'Vit D' => 'vit_d'
			);

			$reciProfityToFadminLabelMap = array(
				'calories' => 'Calories',
				'fat' => 'Fat',
				'sat_fat' => 'Sat Fat',
				'trans_fat' => 'Trans Fats',
				'cholesterol' => 'Cholesterol',
				'carbs' => 'Carbs',
				'fiber' => 'Fiber',
				'sugars' => 'Sugars',
				'protein' => 'Protein',
				'sodium' => 'Sodium',
				'Carb Exchanges' => '',
				'vit_c' => 'Vit C',
				'calcium' => 'Calcium',
				'iron' => 'Iron',
				'vit_a' => 'Vit A',
				'added_sugars' => 'Added Sugar',
				'potasium' => 'Potassium (K)',
				'vit_d' => 'Vit D'
			);

			foreach ($componentArray as $thisField)
			{
				$thisLabel = trim($labels[$thisField]);
				$theseNuts = explode("\n", $fields[$thisField]);

				$fadminLabel = $reciProfityToFadminLabelMap[$thisLabel];
				$thisNutElement = DAO_CFactory::create('nutrition_element');
				$thisNutElement->label = $fadminLabel;

				if (!$thisNutElement->find(true))
				{
					// let's not handle this anymore ... just bail so we know this happened
					throw new Exception("Invalid Recipe Label: " . $thisLabel);
				}

				$compCount = 1;

				foreach ($theseNuts as $thisNut)
				{
					$numberComponentOfNut = array();
					$origVal = $thisNut;
					preg_match('!\d+\.*\d*!', $thisNut, $numberComponentOfNut, PREG_OFFSET_CAPTURE);
					if (count($numberComponentOfNut) != 1)
					{
						// let's not handle this anymore ... just bail so we know this happened
						throw new Exception("Invalid Nutrition Amount: " . $thisLabel);
					}
					else
					{
						$thisNut = $numberComponentOfNut[0][0];
					}

					$inputHasLessThan = '';
					if (strpos($origVal, "<") === 0)
					{
						$inputHasLessThan = "<";
					}

					$inputHasAsterisk = '';
					if (strpos($origVal, "*") === 0)
					{
						$inputHasAsterisk = "*";
					}

					$thisNutData = DAO_CFactory::create('nutrition_data');

					if (!isset($component_ids[$compCount]))
					{
						throw new Exception("Invalid Component ID for {$thisRecipe->recipe_id}");
					}

					// menu specific
					if ($thisRecipeRowID)
					{
						$thisNutData->component_id = $component_ids[$compCount];
						$thisNutData->component_number = $compCount;
						$thisNutData->nutrition_element = $thisNutElement->id;
						$dataExists = $thisNutData->find(true);
					}
					else
					{
						$dataExists = false;
					}

					if (!$dataExists)
					{
						$thisNutData->nutrition_element = $thisNutElement->id;
						$thisNutData->value = $thisNut;

						if ($inputHasAsterisk)
						{
							$thisNutData->note_indicator = "*";
						}

						self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Nutrition Element $thisLabel will be added for Component {$component_ids[$compCount]}");
					}
					else
					{
						$orgData = clone($thisNutData);
						$thisNutData->nutrition_element = $thisNutElement->id;
						$thisNutData->value = $thisNut;

						if ($inputHasAsterisk)
						{
							$thisNutData->note_indicator = "*";
						}

						if ($orgData->value - $thisNut != 0 || $inputHasLessThan != $orgData->prefix)
						{
							self::addToChangeLog(false, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Nutrition Element $thisLabel will be updated for Component {$component_ids[$compCount]}: Was {$orgData->prefix}{$orgData->value} becomes $inputHasLessThan$thisNut", null, 'red');
						}
					}

					// default recipe
					$thisDefaultNutData = DAO_CFactory::create('nutrition_data');

					if ($thisNullRecipeRowID && $null_component_ids[$compCount] > 6)
					{

						$thisDefaultNutData->component_id = $null_component_ids[$compCount];
						$thisDefaultNutData->component_number = $compCount;
						$thisDefaultNutData->nutrition_element = $thisNutElement->id;

						$dataExists = $thisDefaultNutData->find(true);
					}
					else
					{
						$dataExists = false;
					}

					if (!$dataExists)
					{
						$thisDefaultNutData->nutrition_element = $thisNutElement->id;
						$thisDefaultNutData->value = $thisNut;

						if ($inputHasAsterisk)
						{
							$thisDefaultNutData->note_indicator = "*";
						}

						self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Nutrition Element $thisLabel will be added for Component {$component_ids[$compCount]} of Default Recipe");
					}
					else
					{
						$orgData = clone($thisDefaultNutData);
						$thisDefaultNutData->nutrition_element = $thisNutElement->id;
						$thisDefaultNutData->value = $thisNut;

						if ($inputHasAsterisk)
						{
							$thisDefaultNutData->note_indicator = "*";
						}

						if ($orgData->value - $thisNut != 0 || $inputHasLessThan != $orgData->prefix)
						{
							self::addToChangeLog(true, $thisRecipe->recipe_id, $thisRecipe->recipe_name, "Nutrition Element $thisLabel will be updated for Component {$component_ids[$compCount]} of Default Recipe: Was {$orgData->prefix}{$orgData->value} becomes $inputHasLessThan$thisNut", null, 'red');
						}
					}
					$compCount++;
				}
			}
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Nutrition import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}

}

?>