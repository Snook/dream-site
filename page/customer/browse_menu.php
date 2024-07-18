<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CBox.php');

class page_browse_menu extends CPage
{

	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runBrowseMenuPage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$this->runBrowseMenuPage($tpl);
	}

	/**
	 * @throws Exception
	 */
	function runBrowseMenuPage($tpl)
	{
		$activeMenus = CMenu::getActiveMenuArray();

		$activeMenuArray = array();

		foreach ($activeMenus as $activeMenuId => $activeMenu)
		{
			$activeMenuArray[$activeMenu['id']] = CMenu::buildPreviewMenuArray($activeMenuId);
		}

		// check to see if there are any active delivered stores
		if (CStore::active_Distribution_Centers())
		{
			// shows null box menu, non-store specific, limit to custom box items
			$deliveredMenuItems = CBox::getBoxArray(false, false, true, false, array('DELIVERED_CUSTOM'));

			if (!empty($deliveredMenuItems['info']['all_recipes']))
			{
				$activeMenuArray['delivered'] = array(
					'menu_id' => 'delivered',
					'menu_items' => $deliveredMenuItems['info']['all_recipes'],
					'menu_month' => 'Delivered',
					'menu_name' => 'Delivered',
					'menu_tab_css' => 'bg-cyan btn-cyan'
				);
			}
		}

		$tpl->assign('activeMenus', $activeMenuArray);
	}
}