<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');

class processor_menu_item extends CPageProcessor
{
	function runPublic()
	{
		$this->runMenuItem();
	}

	function runCustomer()
	{
		$this->runMenuItem();
	}

	function runMenuItem()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);
		$User = CUser::getCurrentUser();

		if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'find_item')
		{
			$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT, true);
			$req_menu_item_id = CGPC::do_clean((!empty($_POST['menu_item_id']) ? $_POST['menu_item_id'] : false), TYPE_INT, true);
			$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT, true);
			$req_detailed = CGPC::do_clean((!empty($_POST['detailed']) ? $_POST['detailed'] : false), TYPE_BOOL, true);

			if (!empty($req_menu_item_id))
			{
				$DAO_menu = DAO_CFactory::create('menu');
				$DAO_menu->id = $req_menu_id;
				$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
					'menu_to_menu_item_store_id' => $req_store_id,
					'exclude_menu_item_category_core' => false,
					'exclude_menu_item_category_efl' => false,
					'exclude_menu_item_category_sides_sweets' => false,
					'menu_item_id_list' => $req_menu_item_id
				));

				if ($DAO_menu_item->N == 1)
				{
					$DAO_menu_item->find(true);

					$DAO_menu_item->getNutritionArray();

					$menu_id = $DAO_menu_item->menu_id;
					$recipe_id = $DAO_menu_item->recipe_id;

					$tpl = new CTemplate();
					$tpl->assign('menu_item', $DAO_menu_item);

					if ($req_detailed)
					{
						$html = $tpl->fetch('customer/subtemplate/item/item_recipe_popup.tpl.php');
					}
					else
					{
						$html = $tpl->fetch('customer/subtemplate/item/item_recipe_popup_nutritional_summary.tpl.php');
					}
				}
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved import form.',
				'menu_id' => $menu_id,
				'recipe_id' => $recipe_id,
				'html' => $html
			));
		}
	}
}
?>