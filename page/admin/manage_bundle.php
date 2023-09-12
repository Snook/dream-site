<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CBundle.php');

class page_admin_manage_bundle extends CPageAdminOnly
{
	const MENU_HISTORY_LIMIT = 6;

	function runHomeOfficeManager()
	{
		$this->manageBundle();
	}

	function runSiteAdmin()
	{
		$this->manageBundle();
	}

	function manageBundle()
	{
		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$oldest_menu_to_fetch = Cmenu::getLastMenuID() - self::MENU_HISTORY_LIMIT;

		$all_DAO_bundles = DAO_CFactory::create('bundle');
		$all_DAO_bundles->selectAdd();
		$all_DAO_bundles->selectAdd("bundle.*");
		$all_DAO_bundles->selectAdd("COUNT(bundle_to_menu_item.menu_item_id) AS menu_item_count");
		$all_DAO_bundles->selectAdd("GROUP_CONCAT(bundle_to_menu_item.menu_item_id) AS menu_item_ids");
		$all_DAO_menu = DAO_CFactory::create('menu');
		$all_DAO_menu->whereAdd("menu.id > '" . $oldest_menu_to_fetch . "'");
		$all_DAO_bundles->joinAddWhereAsOn($all_DAO_menu);
		$all_DAO_bundles->joinAddWhereAsOn(DAO_CFactory::create('bundle_to_menu_item'), 'LEFT', false, false, false);
		$all_DAO_bundles->whereAdd("bundle.bundle_type != '" . CBundle::DELIVERED . "'");
		$all_DAO_bundles->groupBy("bundle.id");
		$all_DAO_bundles->orderBy("bundle.menu_id DESC, bundle.bundle_type");
		$all_DAO_bundles->find();

		$bundleArray = array();
		$menuItems = array();
		while ($all_DAO_bundles->fetch())
		{
			$menuItems = array_merge($menuItems, explode(',', $all_DAO_bundles->menu_item_ids));

			$bundleArray[$all_DAO_bundles->id] = $all_DAO_bundles->cloneObj();
			$bundleArray[$all_DAO_bundles->id]->menu_item_ids = explode(',', $all_DAO_bundles->menu_item_ids);
			$bundleArray[$all_DAO_bundles->id]->has_orders = false; // checked below
		}

		// get a list of all menu items used
		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->whereAdd("menu_item.id IN ('" . implode("','", $menuItems) . "')");
		$DAO_menu_item->orderBy("NameAZ");
		$DAO_menu_item->find();

		$menuItemsArray = array();
		while ($DAO_menu_item->fetch())
		{
			$menuItemsArray[$DAO_menu_item->id] = $DAO_menu_item->cloneObj();
		}

		$this->Template->assign('editable', true);
		$this->Template->assign('editBundle', false);
		$this->Template->assign('createBundle', false);

		if (!empty($_POST['delete']) && $_POST['delete'] == 'delete')
		{
			$p_bundle_id = CGPC::do_clean((!empty($_POST['bundle_id']) ? $_POST['bundle_id'] : false), TYPE_INT);

			$DAO_bundle = DAO_CFactory::create('bundle');
			$DAO_bundle->id = $p_bundle_id;

			if ($DAO_bundle->find(true))
			{
				$DAO_bundle->delete();
			}

			$this->Template->setStatusMsg('Bundle deleted.');
			CApp::bounce('/?page=admin_manage_bundle');
		}

		if (!empty($_GET['edit']) && is_numeric($_GET['edit']))
		{
			$edit_DAO_bundle = DAO_CFactory::create('bundle');
			$edit_DAO_bundle->id = $_GET['edit'];
			$edit_DAO_bundle->selectAdd();
			$edit_DAO_bundle->selectAdd("bundle.*");
			$edit_DAO_bundle->joinAddWhereAsOn(DAO_CFactory::create('menu_item'), 'LEFT');

			if (!$edit_DAO_bundle->find(true))
			{
				$this->Template->setStatusMsg('Bundle not found.');
				CApp::bounce('/?page=admin_manage_bundle');
			}

			// check if bundles have orders against them
			$check_DAO_orders = DAO_CFactory::create('orders');
			$check_DAO_orders->bundle_id = $edit_DAO_bundle->id;
			$check_DAO_booking = DAO_CFactory::create('booking');
			$check_DAO_booking->status = CBooking::ACTIVE;
			$check_DAO_orders->joinAddWhereAsOn($check_DAO_booking);
			$check_DAO_orders->groupBy("orders.bundle_id");
			$check_DAO_orders->find();

			$bundlesWithOrders = array();

			while ($check_DAO_orders->fetch())
			{
				$bundleArray[$check_DAO_orders->bundle_id]->has_orders = true;
				$bundlesWithOrders[$check_DAO_orders->bundle_id] = $check_DAO_orders->bundle_id;
			}

			if (in_array($edit_DAO_bundle->id, $bundlesWithOrders))
			{
				$this->Template->assign('editable', false);
			}

			if (!empty($_POST['submit']) && $_POST['submit'] == 'update')
			{
				$bundleEditOrg = clone $edit_DAO_bundle;

				$p_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$p_bundle_type = CGPC::do_clean((!empty($_POST['bundle_type']) ? $_POST['bundle_type'] : false), TYPE_NOHTML, true);
				$p_bundle_name = CGPC::do_clean((!empty($_POST['bundle_name']) ? $_POST['bundle_name'] : false), TYPE_NOHTML, true);
				$p_menu_item_description = CGPC::do_clean((!empty($_POST['menu_item_description']) ? $_POST['menu_item_description'] : false), TYPE_NOHTML);
				$p_master_menu_item = CGPC::do_clean((!empty($_POST['master_menu_item']) ? $_POST['master_menu_item'] : false), TYPE_INT);
				$p_number_items_required = CGPC::do_clean((!empty($_POST['number_items_required']) ? $_POST['number_items_required'] : false), TYPE_INT);
				$p_number_servings_required = CGPC::do_clean((!empty($_POST['number_servings_required']) ? $_POST['number_servings_required'] : false), TYPE_INT);
				$p_servings_per_item = CGPC::do_clean((!empty($_POST['servings_per_item']) ? $_POST['servings_per_item'] : false), TYPE_INT);
				$p_item_count_per_item = CGPC::do_clean((!empty($_POST['item_count_per_item']) ? $_POST['item_count_per_item'] : false), TYPE_INT);
				$p_price = CGPC::do_clean((!empty($_POST['price']) ? $_POST['price'] : false), TYPE_NUM);

				//	$edit_DAO_bundle->menu_id = $p_menu_id;
				$edit_DAO_bundle->bundle_type = $p_bundle_type;
				$edit_DAO_bundle->bundle_name = $p_bundle_name;
				$edit_DAO_bundle->menu_item_description = $p_menu_item_description;
				$edit_DAO_bundle->master_menu_item = (($p_bundle_type == CBundle::MASTER_ITEM) ? $p_master_menu_item : 'NULL');
				$edit_DAO_bundle->number_items_required = $p_number_items_required;
				$edit_DAO_bundle->number_servings_required = $p_number_servings_required;
				$edit_DAO_bundle->price = $p_price;
				$edit_DAO_bundle->update($bundleEditOrg);

				if ($edit_DAO_bundle->bundle_type == CBundle::MASTER_ITEM || $bundleEditOrg->bundle_type == CBundle::MASTER_ITEM)
				{
					$menuItem = DAO_CFactory::create('menu_item');

					if ($edit_DAO_bundle->bundle_type == CBundle::MASTER_ITEM)
					{
						$menuItem->id = $edit_DAO_bundle->master_menu_item;
					}
					else
					{
						$menuItem->id = $bundleEditOrg->master_menu_item;
					}

					$menuItem->find(true);

					$menuItemOrg = clone $menuItem;

					if ($edit_DAO_bundle->bundle_type == CBundle::MASTER_ITEM)
					{
						$menuItem->is_bundle = 1;
						$menuItem->servings_per_item = $p_servings_per_item;
						$menuItem->item_count_per_item = $p_item_count_per_item;
					}
					else
					{
						$menuItem->is_bundle = 0;
					}

					$menuItem->update($menuItemOrg);
				}

				$this->Template->setStatusMsg('Bundle updated');
			}

			$menuItemArray = $this->getMenuItems($edit_DAO_bundle->menu_id);

			$this->Template->assign('editBundle', $edit_DAO_bundle);
			$this->Template->assign('menuItems', $menuItemArray);

			$Form->DefaultValues['bundle_type'] = $edit_DAO_bundle->bundle_type;
			$Form->DefaultValues['bundle_name'] = $edit_DAO_bundle->bundle_name;
			$Form->DefaultValues['menu_id'] = $edit_DAO_bundle->menu_id;
			$Form->DefaultValues['master_menu_item'] = $edit_DAO_bundle->master_menu_item;
			$Form->DefaultValues['menu_item_description'] = $edit_DAO_bundle->menu_item_description;
			$Form->DefaultValues['number_items_required'] = $edit_DAO_bundle->number_items_required;
			$Form->DefaultValues['number_servings_required'] = $edit_DAO_bundle->number_servings_required;
			$Form->DefaultValues['servings_per_item'] = $edit_DAO_bundle->DAO_menu_item->servings_per_item;
			$Form->DefaultValues['item_count_per_item'] = $edit_DAO_bundle->DAO_menu_item->item_count_per_item;
			$Form->DefaultValues['price'] = $edit_DAO_bundle->price;

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::value => $edit_DAO_bundle->id,
				CForm::name => 'bundle_id'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'btn btn-primary',
				CForm::value => 'update',
				CForm::text => 'Update Bundle'
			));

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'delete',
				CForm::css_class => 'btn btn-primary',
				CForm::value => 'delete',
				CForm::text => 'Delete Bundle'
			));
		}

		if (isset($_GET['create']))
		{
			if (isset($_GET['menu']) && is_numeric($_GET['menu']))
			{
				$menuItemArray = $this->getMenuItems($_GET['menu']);

				$Form->DefaultValues['menu_id'] = $_GET['menu'];
			}

			if (!empty($_POST['submit']) && $_POST['submit'] == 'create')
			{
				$p_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$p_bundle_type = CGPC::do_clean((!empty($_POST['bundle_type']) ? $_POST['bundle_type'] : false), TYPE_NOHTML, true);
				$p_bundle_name = CGPC::do_clean((!empty($_POST['bundle_name']) ? $_POST['bundle_name'] : false), TYPE_NOHTML, true);
				$p_menu_item_description = CGPC::do_clean((!empty($_POST['menu_item_description']) ? $_POST['menu_item_description'] : false), TYPE_NOHTML);
				$p_master_menu_item = CGPC::do_clean((!empty($_POST['master_menu_item']) ? $_POST['master_menu_item'] : false), TYPE_INT);
				$p_number_items_required = CGPC::do_clean((!empty($_POST['number_items_required']) ? $_POST['number_items_required'] : false), TYPE_INT);
				$p_number_servings_required = CGPC::do_clean((!empty($_POST['number_servings_required']) ? $_POST['number_servings_required'] : false), TYPE_INT);
				$p_servings_per_item = CGPC::do_clean((!empty($_POST['servings_per_item']) ? $_POST['servings_per_item'] : false), TYPE_INT);
				$p_item_count_per_item = CGPC::do_clean((!empty($_POST['item_count_per_item']) ? $_POST['item_count_per_item'] : false), TYPE_INT);
				$p_price = CGPC::do_clean((!empty($_POST['price']) ? $_POST['price'] : false), TYPE_NUM);

				$transactionObject = new DAO();
				$transactionObject->query('START TRANSACTION;');

				try
				{
					$createBundle = DAO_CFactory::create('bundle');
					$createBundle->menu_id = $p_menu_id;
					$createBundle->bundle_type = $p_bundle_type;
					$createBundle->bundle_name = $p_bundle_name;
					$createBundle->master_menu_item = $p_master_menu_item;
					$createBundle->menu_item_description = $p_menu_item_description;
					$createBundle->number_items_required = $p_number_items_required;
					$createBundle->number_servings_required = $p_number_servings_required;
					$createBundle->price = $p_price;

					if ($createBundle->bundle_type == CBundle::MASTER_ITEM)
					{
						$menuItem = DAO_CFactory::create('menu_item');
						$menuItem->id = $createBundle->master_menu_item;

						if ($menuItem->find(true))
						{
							$menuItemOrg = clone $menuItem;

							$menuItem->is_bundle = 1;
							$menuItem->servings_per_item = $p_servings_per_item;
							$menuItem->item_count_per_item = $p_item_count_per_item;

							$menuItem->update($menuItemOrg);
						}
						else
						{
							throw new Exception('Master menu item not found');
						}
					}

					$createBundle->insert();

					$transactionObject->query('COMMIT;');

					$this->Template->setStatusMsg('Bundle created, select menu items.');
					CApp::bounce('/?page=admin_manage_bundle&edit=' . $createBundle->id);
				}
				catch (Exception $e)
				{
					$transactionObject->query('ROLLBACK;');
					$this->Template->setErrorMsg('Bundle create failed: exception occurred</br>Reason: ' . $e->getMessage());
				}
			}

			$this->Template->assign('createBundle', true);

			$Form->AddElement(array(
				CForm::type => CForm::Button,
				CForm::name => 'submit',
				CForm::css_class => 'btn btn-primary',
				CForm::disabled => (empty($menuItemArray) ? true : false),
				CForm::value => 'create',
				CForm::text => 'Create Bundle'
			));
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'bundle_type',
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::options => array(
				'' => 'Select Type',
				CBundle::TV_OFFER => 'Starter Pack',
				CBundle::FUNDRAISER => 'Fundraiser',
				CBundle::DREAM_TASTE => 'Workshop',
				CBundle::MASTER_ITEM => 'Master Item Bundle'
			)
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::name => 'bundle_name'
		));

		$menuArray = CMenu::getLastXMenus(self::MENU_HISTORY_LIMIT);
		$menuOptionsArray = array('' => 'Select Menu');
		foreach ($menuArray as $id => $menu)
		{
			$menuOptionsArray[$id] = $menu->menu_name;
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'menu_id',
			CForm::disabled => (empty($this->Template->createBundle) ? true : false),
			CForm::options => $menuOptionsArray
		));

		$Form->addElement(array(
			CForm::type => CForm::TextArea,
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::name => 'menu_item_description'
		));

		$masterItemOptions = array('Select Master Item');
		if (!empty($menuItemArray))
		{
			foreach ($menuItemArray AS $category => $entree_id)
			{
				foreach($entree_id AS $entree_info)
				{
					foreach ($entree_info['menu_item'] as $DAO_menu_item)
					{
						$masterItemOptions[$DAO_menu_item->id] = array(
							'title' => $category . '^^^' . $DAO_menu_item->menu_item_name . ' - ' . $DAO_menu_item->pricing_type_info['pricing_type_name_short_w_qty'],
							'data' => array(
								'data-pricing_type' => $DAO_menu_item->pricing_type
							)
						);
					}
				}
			}
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => $masterItemOptions,
			CForm::number => true,
			CForm::name => 'master_menu_item'
		));

		$Form->addElement(array(
			CForm::type => CForm::Number,
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::name => 'number_items_required'
		));

		$Form->addElement(array(
			CForm::type => CForm::Number,
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::name => 'number_servings_required'
		));

		$Form->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'servings_per_item'
		));

		$Form->addElement(array(
			CForm::type => CForm::Number,
			CForm::name => 'item_count_per_item'
		));

		$Form->addElement(array(
			CForm::type => CForm::Number,
			CForm::disabled => (empty($menuItemArray) ? true : false),
			CForm::name => 'price'
		));

		$this->Template->assign('bundleArray', $bundleArray);
		$this->Template->assign('menuItemsArray', $menuItemsArray);
		$this->Template->assign('form', $Form->render());
	}

	function getMenuItems($menu_id)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$menuItemInfo = array();

		while ($DAO_menu_item->fetch())
		{
			if ($DAO_menu_item->isMenuItem_EFL())
			{
				$DAO_menu_item->category_type = "Extended Fast Lane";
			}
			else if ($DAO_menu_item->isMenuItem_Core())
			{
				$DAO_menu_item->category_type = "Core";
			}
			else if ($DAO_menu_item->isMenuItem_SidesSweets())
			{
				$DAO_menu_item->category_type = "Sides & Sweets";
			}
			else
			{
				$DAO_menu_item->category_type = $DAO_menu_item->category;
			}

			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['menu_item'][$DAO_menu_item->pricing_type] = $DAO_menu_item->cloneObj();
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['menu_item_info']['menu_item_name'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['menu_item_info']['menu_item_description'] = $DAO_menu_item->menu_item_description;
			$menuItemInfo[$DAO_menu_item->category_type][$DAO_menu_item->entree_id]['menu_item_info']['recipe_id'] = $DAO_menu_item->recipe_id;
		}

		return $menuItemInfo;
	}
}
?>