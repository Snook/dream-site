<?php
require_once 'DAO/BusinessObject/CMenu.php';

class page_sitemap extends CPage
{

	/**
	 * @throws Exception
	 */
	function runPublic()
	{

		$tpl = CApp::instance()->template();

		$activeMenus = CMenu::getActiveMenuArray();

		$activeMenuArray = array();

		foreach ($activeMenus as $activeMenuId => $activeMenu)
		{
			$menuItemInfo = CMenu::buildPreviewMenuArray($activeMenuId, 'NameAZ');

			$activeMenuArray[] = array(
				'id' => $activeMenu['id'],
				'month' => $activeMenu['name'],
				'description' => $activeMenu['description'],
				'menu_items' => $menuItemInfo
			);
		}

		// fetch current stores
		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->show_on_customer_site = 1;
		$DAO_store->whereAdd("store_type <> '" . CStore::DISTRIBUTION_CENTER . "'");
		$DAO_store->orderBy("state_id ASC, city ASC, store_name ASC");
		$DAO_store->find_DAO_store();

		$currentStoreArray = array();

		while($DAO_store->fetch())
		{
			$currentStoreArray[$DAO_store->state_id][$DAO_store->id] = clone $DAO_store;
		}

		$tpl->assign('currentStores', $currentStoreArray);
		$tpl->assign('activeMenus', $activeMenuArray);
	}
}