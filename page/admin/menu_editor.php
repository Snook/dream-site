<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/OrdersHelper.php");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/CPricing.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');

class page_admin_menu_editor extends CPageAdminOnly
{
	function runSiteAdmin()
	{
		$this->runMenuEditor();
	}

	function runHomeOfficeManager()
	{
		$this->runMenuEditor();
	}

	function runOpsLead()
	{
		$this->runMenuEditor();
	}

	function runFranchiseManager()
	{
		$this->runMenuEditor();
	}

	function runFranchiseOwner()
	{
		$this->runMenuEditor();
	}

	function runMenuEditor()
	{
		CLog::Record("MENU_EDITOR: Page accessed for store: " . $this->CurrentBackOfficeStore->id);

		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		// Setup Menu Dropdown
		$array_DAO_menu = DAO_CFactory::create('menu', true);
		$array_DAO_menu->whereAdd("menu.global_menu_start_date >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH,'%Y-%m-%d') OR ( menu.global_menu_start_date <= DATE_FORMAT(NOW(),'%Y-%m-%d') AND menu.global_menu_end_date >= DATE_FORMAT(NOW(),'%Y-%m-%d') )");
		$array_DAO_menu->orderBy("menu.id DESC");
		$array_DAO_menu->find_DAO_menu();

		$menuOptions = array();
		while ($array_DAO_menu->fetch())
		{
			$menuOptions[$array_DAO_menu->id] = $array_DAO_menu->menu_name;
		}

		// Determine menu to display
		if (!empty($_REQUEST['menu']) && is_numeric($_REQUEST['menu']) && array_key_exists($_REQUEST['menu'], $menuOptions)) // url menu
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->id = $_REQUEST['menu'];
			$DAO_menu->find_DAO_menu(true);
		}
		else if (CBrowserSession::getValue('backoffice_current_menu')) // cookie menu
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->id = CBrowserSession::getValue('backoffice_current_menu');
			$DAO_menu->find_DAO_menu(true);
		}
		else // current menu
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->whereAdd("DATE_FORMAT(NOW(),'%Y-%m-%d') >= menu.global_menu_start_date");
			$DAO_menu->whereAdd("DATE_FORMAT(NOW(),'%Y-%m-%d') <= menu.global_menu_end_date");
			$DAO_menu->orderBy("menu.id");
			$DAO_menu->limit(1);
			$DAO_menu->find_DAO_menu(true);
		}

		if (array_key_exists($DAO_menu->id, $menuOptions))
		{
			$Form->DefaultValues['menu'] = $DAO_menu->id;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => $menuOptions,
			CForm::css_class => 'formHasChanged-ignore',
			CForm::name => 'menu'
		));

		CBrowserSession::instance()->setValue('backoffice_current_menu', $Form->value('menu'));

		if (!empty($_POST['action']) && $_POST["action"] == 'finalize')
		{
			CLog::Record("MENU_EDITOR: Page finalized for store: " . $this->CurrentBackOfficeStore->id);
			CLog::RecordDebugTrace("Menu Finalized\r\n" . print_r($_POST, true), "MENU_EDITOR");

			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $this->CurrentBackOfficeStore->id,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false
			));

			while ($DAO_menu_item->fetch())
			{
				$update_DAO_menu_to_menu_item = clone $DAO_menu_item->DAO_menu_to_menu_item;

				if (isset($_POST[$DAO_menu_item->id . '_vis']))
				{
					$update_DAO_menu_to_menu_item->is_visible = !empty($_POST[$DAO_menu_item->id . '_vis']) ? 1 : 0;
				}
				if (isset($_POST[$DAO_menu_item->id . '_pic']))
				{
					$update_DAO_menu_to_menu_item->show_on_pick_sheet = !empty($_POST[$DAO_menu_item->id . '_pic']) ? 1 : 0;
				}
				if (isset($_POST[$DAO_menu_item->id . '_form']))
				{
					$update_DAO_menu_to_menu_item->show_on_order_form = !empty($_POST[$DAO_menu_item->id . '_form']) ? 1 : 0;
				}
				if (isset($_POST[$DAO_menu_item->id . '_hid']))
				{
					$update_DAO_menu_to_menu_item->is_hidden_everywhere = !empty($_POST[$DAO_menu_item->id . '_hid']) ? 1 : 0;
				}
				if (isset($_POST[$DAO_menu_item->id . '_ovr']))
				{
					$update_DAO_menu_to_menu_item->override_price = CTemplate::number_format($_POST[$DAO_menu_item->id . '_ovr']);
				}

				$update_DAO_menu_to_menu_item->update($DAO_menu_item->DAO_menu_to_menu_item);
			}
		}

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->CurrentBackOfficeStore->id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$menuItemArray = array(
			CMenuItem::CORE => array(),
			CMenuItem::EXTENDED => array(),
			CMenuItem::SIDE => array(
				'not_hidden' => array(),
				'hidden' => array()
			),
		);

		$tabindex = 0;

		while ($DAO_menu_item->fetch())
		{
			if ($DAO_menu_item->isMenuItem_Core())
			{
				$menuItemArray[CMenuItem::CORE][$DAO_menu_item->id] = clone $DAO_menu_item;
			}
			else if ($DAO_menu_item->isMenuItem_EFL())
			{
				$menuItemArray[CMenuItem::EXTENDED][$DAO_menu_item->id] = clone $DAO_menu_item;
			}
			else if ($DAO_menu_item->isMenuItem_SidesSweets())
			{
				if ($DAO_menu_item->isHiddenEverywhere())
				{
					$menuItemArray[CMenuItem::SIDE]['hidden'][$DAO_menu_item->id] = clone $DAO_menu_item;
				}
				else
				{
					$menuItemArray[CMenuItem::SIDE]['not_hidden'][$DAO_menu_item->id] = clone $DAO_menu_item;
				}
			}

			$Form->DefaultValues[$DAO_menu_item->id . '_vis'] = ($DAO_menu_item->DAO_menu_to_menu_item->isVisible() && !$DAO_menu_item->DAO_menu_to_menu_item->isHiddenEverywhere()) ? 1 : 0;
			$Form->DefaultValues[$DAO_menu_item->id . '_pic'] = ($DAO_menu_item->DAO_menu_to_menu_item->isShowOnPickSheet() && !$DAO_menu_item->DAO_menu_to_menu_item->isHiddenEverywhere()) ? 1 : 0;
			$Form->DefaultValues[$DAO_menu_item->id . '_form'] = ($DAO_menu_item->DAO_menu_to_menu_item->isShowOnOrderForm() && !$DAO_menu_item->DAO_menu_to_menu_item->isHiddenEverywhere()) ? 1 : 0;
			$Form->DefaultValues[$DAO_menu_item->id . '_hid'] = $DAO_menu_item->DAO_menu_to_menu_item->isHiddenEverywhere() ? 1 : 0;
			$Form->DefaultValues[$DAO_menu_item->id . '_ovr'] = (!empty($DAO_menu_item->DAO_menu_to_menu_item->override_price)) ? $DAO_menu_item->DAO_menu_to_menu_item->override_price : $DAO_menu_item->getStorePrice();

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => $DAO_menu_item->id . '_vis',
				CForm::css_class => 'custom-select-sm menu-editor-vis',
				CForm::readonly => empty($DAO_menu_item->is_visibility_controllable),
				CForm::style => 'min-width: 4rem;',
				CForm::attribute => array(
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-category_id' => $DAO_menu_item->category_id,
					'data-is_store_special' => $DAO_menu_item->is_store_special
				),
				CForm::options => array(
					'1' => 'Yes',
					'0' => 'No'
				),
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => $DAO_menu_item->id . '_pic',
				CForm::css_class => 'custom-select-sm menu-editor-pic',
				CForm::readonly => empty($DAO_menu_item->is_visibility_controllable),
				CForm::style => 'min-width: 4rem;',
				CForm::attribute => array(
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-category_id' => $DAO_menu_item->category_id,
					'data-is_store_special' => $DAO_menu_item->is_store_special
				),
				CForm::options => array(
					'1' => 'Yes',
					'0' => 'No'
				),
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => $DAO_menu_item->id . '_form',
				CForm::css_class => 'custom-select-sm menu-editor-form',
				CForm::readonly => empty($DAO_menu_item->is_visibility_controllable),
				CForm::style => 'min-width: 4rem;',
				CForm::attribute => array(
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-category_id' => $DAO_menu_item->category_id,
					'data-is_store_special' => $DAO_menu_item->is_store_special
				),
				CForm::options => array(
					'1' => 'Yes',
					'0' => 'No'
				),
			));

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => $DAO_menu_item->id . '_hid',
				CForm::css_class => 'custom-select-sm menu-editor-hid',
				CForm::style => 'min-width: 4rem;',
				CForm::attribute => array(
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-category_id' => $DAO_menu_item->category_id,
					'data-is_store_special' => $DAO_menu_item->is_store_special
				),
				CForm::options => array(
					'1' => 'Yes',
					'0' => 'No'
				),
			));

			$Form->AddElement(array(
				CForm::type => CForm::Number,
				CForm::name => $DAO_menu_item->id . '_ovr',
				CForm::css_class => 'form-control-sm menu-editor-ovr',
				CForm::style => 'min-width: 6rem;',
				CForm::readonly => empty($DAO_menu_item->is_price_controllable),
				CForm::required => true,
				CForm::pattern => '^\d+\.\d{0,2}$',
				CForm::attribute => array(
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-category_id' => $DAO_menu_item->category_id,
					'data-is_store_special' => $DAO_menu_item->is_store_special,
					'data-lowest_tier_price' => !empty($DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price) ? $DAO_menu_item->pricing_tiers[1][$DAO_menu_item->pricing_type]->price : 'null',
					'data-highest_tier_price' => !empty($DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price) ? $DAO_menu_item->pricing_tiers[3][$DAO_menu_item->pricing_type]->price : 'null'
				),
				CForm::step => '0.01',
				CForm::tabindex => ++$tabindex
			));
		}

		$pricingReference = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->CurrentBackOfficeStore->id,
			'exclude_menu_item_is_bundle' => false,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'groupBy' => 'RecipeID'
		));

		$pricingReferenceArray = array();
		while ($pricingReference->fetch())
		{
			$pricingReferenceArray[$pricingReference->recipe_id] = $pricingReference->cloneObj();
		}

		$this->Template->assign('DAO_order_minimum', COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $this->CurrentBackOfficeStore->id, $DAO_menu->id));
		$this->Template->assign('DAO_menu', $DAO_menu);
		$this->Template->assign('menuItemArray', $menuItemArray);
		$this->Template->assign('pricingReferenceArray', $pricingReferenceArray);
		$this->Template->assign('form', $Form->render());
	}
}

?>