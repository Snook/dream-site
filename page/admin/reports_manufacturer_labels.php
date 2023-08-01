<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");
require_once('DAO/BusinessObject/CMenu.php');
require_once('DAO/BusinessObject/CRecipe.php');
require_once("fpdf/dream_labels.php");

class page_admin_reports_manufacturer_labels extends CPageAdminOnly
{

	private $needsStoreSelector = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->needsStoreSelector = true;
		$this->manufacturerLabels();
	}

	function runSiteAdmin()
	{
		$this->needsStoreSelector = true;

		$this->manufacturerLabels();
	}

	function runManufacturerStaff()
	{
		$this->manufacturerLabels();
	}

	function runFranchiseManager()
	{
		$this->manufacturerLabels();
	}

	function runOpsLead()
	{
		$this->manufacturerLabels();
	}
	
	function runFranchiseOwner()
	{
		$this->manufacturerLabels();
	}

	function manufacturerLabels()
	{
		$tpl = CApp::instance()->template();

		$tpl->assign('show_nutritional_labels_pdf', false);
		$tpl->assign('show_cooking_instruction_labels_pdf', false);

		$Form = new CForm();
		$Form->Repost = true;

		// ------------------------------ figure out active store and create store widget if necessary
		$store_id = null;
		if ($this->needsStoreSelector)
		{
			$Form->DefaultValues['store'] = CBrowserSession::getCurrentFadminStore();

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$store_id = $Form->value('store');
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $store_id;
		$Store->find(true);

		$recipeArray = CRecipe::createManufacturerRecipeList($store_id);

		$tpl->assign('recipes', $recipeArray);

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);

		// generate PDF for nutritionals
		if (!empty($_POST['generate_nutritional_labels']) || !empty($_POST['generate_cooking_instructions']))
		{
			$printRecipeArray = array();

			foreach ($_POST AS $key => $value)
			{
				if (!empty($value) && strpos($key, '-'))
				{
					list($recipe_size, $recipe_id) = explode('-', $key);

					if (!empty($recipe_size) && !empty($recipe_id) && ($recipe_size == 'medium' || $recipe_size == 'large'))
					{
						$printRecipeArray[$recipe_id][$recipe_size] = $value;
					}
				}
			}

			if ($printRecipeArray)
			{
				$recipe_ids = implode(',', array_keys($printRecipeArray));

				$NutsArray = CRecipe::fetch_nutrition_data_by_recipe($recipe_ids);

				$masterarr = array();

				foreach ($printRecipeArray AS $recipe_id => $size_print_count)
				{
					if (!empty($size_print_count['large']))
					{
						$prep_arr = $this->prep_labels_per_sheet($NutsArray[$recipe_id]['size']['LARGE'], $size_print_count['large']);

						$masterarr = array_merge($masterarr, $prep_arr);
					}

					if (!empty($size_print_count['medium']))
					{
						$prep_arr = $this->prep_labels_per_sheet($NutsArray[$recipe_id]['size']['MEDIUM'], $size_print_count['medium']);

						$masterarr = array_merge($masterarr, $prep_arr);
					}
				}

				if (empty($masterarr))
				{
					$tpl->setStatusMsg("Sorry, nutritional sets for this menu are not currently available.  Please check back again at a later time.");
				}
				else if (!empty($_POST['generate_nutritional_labels']))
				{
					$pdf = new dream_labels('8164', 'mm', 1, 1);
					$pdf->Open();

					foreach ($masterarr as $index => $entity)
					{
						$entity['info']['label_type'] = $_REQUEST['print_label_type'];

						$pdf->Add_Manufacturer_Nutrition_Label($entity, 0, 0, $Store);
					}

					$pdf->Output();
				}
				else if (!empty($_POST['generate_cooking_instructions']))
				{
					$pdf = new dream_labels('8164', 'mm', 1, 1);
					$pdf->Open();

					foreach ($masterarr as $index => $entity)
					{
						$entity['info']['use_by_date'] = $_REQUEST['use_by_date'];
						$entity['info']['label_type'] = $_REQUEST['print_label_type'];

						$pdf->Add_Manufacturer_Cooking_Instruction_Label($entity, 0, $Store);
					}

					$pdf->Output();
				}
			}
		}
	}

	static function prep_labels_per_sheet($item_array, $pages_to_print = 1, $items_per_page = 1)
	{
		$masterarr = array();
		$mastcounter = 0;

		$total_items_to_iter = $items_per_page * $pages_to_print;

		for ($i = 0; $i < $total_items_to_iter; $i++)
		{
			$masterarr[$mastcounter++] = $item_array;
		}

		return $masterarr;
	}
}

?>