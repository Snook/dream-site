<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CCouponCode.php');
require_once('includes/DAO/BusinessObject/CStore.php');

class processor_admin_helpers extends CPageProcessor
{
	private $singleStore = false;

	function runSiteAdmin()
	{
		$this->runHelpers();
	}

	function runHomeOfficeManager()
	{
		$this->runHelpers();
	}

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runHelpers();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runHelpers();
	}

	function runHelpers()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		// Multiple store selector
		if (!empty($_POST['op']) && $_POST['op'] == 'multi_store_select')
		{
			$tpl = new CTemplate();

			$store_id_array = array();

			if (!empty($_POST['store_id']))
			{
				$store_id_array = explode(',', $_POST['store_id']);
			}

			$tpl->assign('store_id_array', $store_id_array);

			$store_select = $tpl->fetch('admin/subtemplate/helpers/multi_store_select.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved store select form.',
				'html' => $store_select
			));
		}

		// Recipe search
		if (!empty($_POST['op']) && $_POST['op'] == 'recipe_select')
		{
			$tpl = new CTemplate();

			$req_search = CGPC::do_clean((!empty($_POST['search']) ? substr($_POST['search'], 0, 255) : false), TYPE_NOHTML, true);
			$req_menu_start = CGPC::do_clean((!empty($_POST['menu_start']) ? $_POST['menu_start'] : false), TYPE_INT, true);
			$req_menu_end = CGPC::do_clean((!empty($_POST['menu_end']) ? $_POST['menu_end'] : false), TYPE_INT, true);

			$DAO_Menu = DAO_CFactory::create('menu');
			if ($req_menu_start)
			{
				$DAO_Menu->whereAdd("menu.id >= '" . $req_menu_start . "'");
			}
			if ($req_menu_end)
			{
				$DAO_Menu->whereAdd("menu.id <= '" . $req_menu_end . "'");
			}

			$DAO_MenuToMenuItem = DAO_CFactory::create('menu_to_menu_item');
			$DAO_MenuToMenuItem->store_id = 'NULL';
			$DAO_MenuToMenuItem->joinAddWhereAsOn($DAO_Menu);

			$DAO_MenuItem = DAO_CFactory::create('menu_item');
			$DAO_MenuItem->selectAdd();
			$DAO_MenuItem->selectAdd("menu_item.*");

			if (is_numeric($req_search))
			{
				$DAO_MenuItem->recipe_id = $req_search;
			}
			else
			{
				$DAO_MenuItem->whereAdd("menu_item.menu_item_name LIKE '%" . $DAO_MenuItem->escape($req_search, true) . "%'");
			}

			$DAO_MenuItem->joinAddWhereAsOn($DAO_MenuToMenuItem);
			$DAO_MenuItem->groupBy("menu_item.recipe_id, menu_item.pricing_type DESC");
			$DAO_MenuItem->orderBy("menu_item.menu_item_name ASC, menu_item.pricing_type ASC");

			$DAO_MenuItem->find();

			$menuItemArray = array();

			while ($DAO_MenuItem->fetch())
			{
				$menuItemArray[] = clone $DAO_MenuItem;
			}

			$tpl->assign('menuItemArray', $menuItemArray);

			$recipe_select = $tpl->fetch('admin/subtemplate/manage_coupon_codes/manage_coupon_codes_recipe_search.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved recipe select form.',
				'html' => $recipe_select
			));
		}

		// Order search
		if (!empty($_POST['op']) && $_POST['op'] == 'order_search')
		{
			$req_order_identifier = CGPC::do_clean((!empty($_POST['order_identifier']) ? $_POST['order_identifier'] : false), TYPE_STR, true);

			if (!empty($req_order_identifier))
			{
				$DAO_orders = DAO_CFactory::create('orders');

				if (is_numeric($req_order_identifier))
				{
					$DAO_orders->id = $req_order_identifier;
				}
				else if (ctype_alnum($req_order_identifier))
				{
					$DAO_orders->order_confirmation = $req_order_identifier;
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'No order ID was specified.'
				));
			}

			if ($DAO_orders->find(true))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Order found.',
					'bounce_to' => 'main.php?page=admin_order_history&id=' . $DAO_orders->user_id . '&order=' . $DAO_orders->id
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'There was no order found by that ID.'
				));
			}
		}

		// Change store
		if (!empty($_POST['op']) && $_POST['op'] == 'store_selector')
		{
			if (!empty($_POST['do']) && $_POST['do'] == 'selector_get')
			{
				$Form = new CForm();
				$Form->Repost = false;
				$Form->Bootstrap = true;

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->query("SELECT
					store.id
					FROM store, user_to_store
					WHERE user_to_store.store_id = store.id
					AND user_to_store.user_id = '" . $this->CurrentUser->id . "'
					AND user_to_store.is_deleted = '0'");

				//if there's just one store, then continue
				if ($StoreObj->N == 1)
				{
					$StoreObj->fetch();
					CBrowserSession::setCurrentFadminStore($StoreObj->id);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Single store found.'
					));
				}

				$Form->DefaultValues['change_store-selector'] = $this->CurrentStore->id;

				if ($this->CurrentUser->user_type == CUser::SITE_ADMIN || $this->CurrentUser->user_type == CUser::HOME_OFFICE_MANAGER || $this->CurrentUser->user_type == CUser::HOME_OFFICE_STAFF)
				{
					$Form->addElement(array(
						CForm::type => CForm::AdminStoreDropDown,
						CForm::name => 'change_store-selector',
						CForm::allowAllOption => false,
						CForm::showInactiveStores => true
					));
				}
				else if (!empty($StoreObj->N))
				{
					$Form->addElement(array(
						CForm::type => CForm::StoreDropDown,
						CForm::userAccessFilter => $this->CurrentUser->id,
						CForm::name => 'change_store-selector',
						CForm::showInactiveStores => true
					));
				}

				$formArray = $Form->render();

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Selector fetched.',
					'form' => $formArray['change_store-selector_html']
				));

			}

			if (!empty($_POST['do']) && $_POST['do'] == 'selector_select')
			{
				if (!empty($_POST['store']) && is_numeric($_POST['store']))
				{
					CBrowserSession::setCurrentFadminStore($_POST['store']);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Store set.',
					));
				}
			}
		}
	}
}

?>