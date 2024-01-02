<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';

class page_admin_menu_inspector extends CPageAdminOnly {

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$menus = CMenu::getActiveMenuArray();

		$lastActiveMenuId = null;
		$menuOptions = array();

		foreach ($menus as $thisMenu)
		{
			if ($thisMenu['id'] > 78) // only March 2008 or newer here
			{
				$menuOptions[$thisMenu['id']] = $thisMenu['name'];
			}

			$lastActiveMenuId = $thisMenu['id'];
		}

		$lastActiveMenuId++;

		$MenuTest = DAO_CFactory::create('menu');
		$MenuTest->query("select menu.id, menu.menu_name from menu join menu_to_menu_item on menu.id = menu_to_menu_item.menu_id where menu.id = '" . $lastActiveMenuId . "' limit 1");

		if ($MenuTest->fetch())
		{
			$menuOptions[$MenuTest->id] = $MenuTest->menu_name;
		}

		$currentMenu = CBrowserSession::getValue('menu_editor_current_menu');

		if (!empty($_GET['menus']) && is_numeric($_GET['menus']))
		{
			$Form->DefaultValues['menus'] = $_GET['menus'];
		}
		else if (array_key_exists($currentMenu, $menuOptions))
		{
			$Form->DefaultValues['menus'] = $currentMenu;
		}

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::onChangeSubmit => true,
								CForm::allowAllOption => false,
								CForm::options => $menuOptions,
								CForm::name => 'menus'));

		CBrowserSession::instance()->setValue('menu_editor_current_menu', $Form->value('menus'));

		$menu_id = $Form->value('menus');

		$tpl->assign('menuInfo', CMenuItem::getMenuItems($menu_id));

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);
	}
}
?>