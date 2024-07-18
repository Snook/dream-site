<?php
require_once 'DAO/BusinessObject/CMenuItem.php';
require_once 'DAO/BusinessObject/CBundle.php';

require_once 'CCart2.inc';

class page_item extends CPage
{

	function runPublic()
	{

		CTemplate::noCache();

		$tpl = CApp::instance()->template();

		$req_recipe = CGPC::do_clean((!empty($_REQUEST['recipe']) ? $_REQUEST['recipe'] : false), TYPE_INT);
		$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
		$req_ov_menu = CGPC::do_clean((!empty($_REQUEST['ov_menu']) ? $_REQUEST['ov_menu'] : false), TYPE_INT);

		try
		{
			if ($req_recipe)
			{
				$menu_id = false;

				if ($req_ov_menu)
				{
					$menu_id = $req_ov_menu;
				}
				else if ($req_menu_id)
				{
					$menu_id = $req_menu_id;
				}

				$menuItemArray = CMenuItem::getItemDetailGenericNew($req_recipe, $menu_id);

				if (empty($menuItemArray))
				{
					$tpl->setErrorMsg("Sorry we could not find the recipe. The recipe_id was missing or invalid ");
					CApp::bounce("/session-menu");
				}

				$tpl->assign('menuItemArray', $menuItemArray);
			}
			else
			{
				$tpl->setErrorMsg("Sorry we could not find the recipe. The recipe_id was missing or invalid ");
				CApp::bounce("/session-menu");
			}
		}
		catch (Exception $e)
		{
			$tpl->setErrorMsg("Sorry we could not find the recipe. The recipe_id was missing or invalid ");
			CApp::bounce("/session-menu");
		}
	}

	function firstInArray($array)
	{
		if (empty($array))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}