<?php
require_once 'DAO/BusinessObject/CMenu.php';

class page_sitemap extends CPage
{

	function runPublic()
	{

		$tpl = CApp::instance()->template();

		$activeMenus = CMenu::getActiveMenuArray();

		$activeMenuArray = array();

		foreach ($activeMenus as $activeMenuId => $activeMenu)
		{
			$menuItemInfo = CMenu::buildPreviewMenuArray(null, $activeMenuId, 'NameAZ');

			$activeMenuArray[] = array(
				'id' => $activeMenu['id'],
				'month' => $activeMenu['name'],
				'description' => $activeMenu['description'],
				'menu_items' => $menuItemInfo
			);
		}

		// fetch current stores
		$currentStores = DAO_CFactory::create('store');
		$currentStores->show_on_customer_site = 1;
		$currentStores->whereAdd("store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
		$currentStores->orderBy("state_id ASC, city ASC, store_name ASC");
		$currentStores->find();
		$currentStores->fetchAll();

		$tpl->assign('currentStores', $currentStores->getFetchResult());
		$tpl->assign('activeMenus', $activeMenuArray);
	}
}

?>