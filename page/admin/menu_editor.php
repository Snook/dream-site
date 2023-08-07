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

	function runFranchiseLead()
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
		ini_set('memory_limit', '64M');

		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		if (!$this->CurrentStore->id)
		{
			throw new Exception('no store id found');
		}

		CLog::Record("MENU_EDITOR: Page accessed for store: " . $this->CurrentStore->id);

		$menus = CMenu::getLastXMenus(4);
		$menuOptions = array();
		$menu_id = false;
		foreach ($menus as $menu)
		{
			$menuOptions[$menu->id] = $menu->menu_name;

			if (CBrowserSession::instance()->getValue('menu_editor_current_menu'))
			{
				$menu_id = CBrowserSession::instance()->getValue('menu_editor_current_menu');
			}
			else if ($menu->is_current_menu)
			{
				$menu_id = $menu->id;
			}
		}

		$Form->DefaultValues['menus'] = $menu_id;

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $menuOptions,
			CForm::name => 'menus'
		));

		CBrowserSession::instance()->setValue('menu_editor_current_menu', $Form->value('menus'));
		$menu_id = $Form->value('menus');

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;

		if (!$DAO_menu->find(true))
		{
			throw new Exception('Menu not found in Menu Editor');
		}

		if (isset($_POST['finalize']) && $_POST['finalize'] === "true")
		{
			CLog::Record("MENU_EDITOR: Page finalized for store: " . $this->CurrentStore->id);
			CLog::RecordDebugTrace("Menu Finalized\r\n" . print_r($_POST, true), "MENU_EDITOR");

			// do markup updates
			if (!$DAO_menu->isEnabled_Markup())
			{
				$_POST['markup_2_serving'] = 0;
				$_POST['markup_3_serving'] = 0;
				$_POST['markup_4_serving'] = 0;
				$_POST['markup_6_serving'] = 0;
			}
			else
			{
				$_POST['markup_2_serving'] = CGPC::do_clean($_POST['markup_2_serving'], TYPE_NUM);
				$_POST['markup_3_serving'] = CGPC::do_clean($_POST['markup_2_serving'], TYPE_NUM);
				$_POST['markup_4_serving'] = CGPC::do_clean($_POST['markup_2_serving'], TYPE_NUM);
				$_POST['markup_6_serving'] = CGPC::do_clean($_POST['markup_2_serving'], TYPE_NUM);
			}

			$_POST['markup_sides'] = CGPC::do_clean($_POST['markup_sides'], TYPE_NUM);
			$_POST['is_default_markup'] = CGPC::do_clean($_POST['is_default_markup'], TYPE_STR_SIMPLE);

			/*
				 $this->CurrentStore->setMarkUpMulti($_POST['markup_6_serving'], $_POST['markup_4_serving'], $_POST['markup_3_serving'], $_POST['markup_2_serving'], $_POST['markup_sides'], $_POST['volume_reward'], $DAO_menu->id, $_POST['is_default_markup'], 12.5, $_POST['assembly_fee'], $_POST['delivery_assembly_fee']);

				if ($shouldSaveNewMarkup && !$this->limitToInventoryControl)
				{
					if ($posted_default_value)
					{
						// make sure older default markups are set to non-default
						$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
						$oldDefaultMarkups->query("update mark_up_multi set is_default = 0 where store_id = $store_id and is_default = 1");
					}
					// delete any older markups for this menu/store
					$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
					$oldDefaultMarkups->query("update mark_up_multi set is_deleted = 1 where store_id = $store_id and menu_id_start = $menu_id and is_deleted = 0");
					$storeObj->setMarkUpMulti($_POST['markup_6_serving'], $_POST['markup_4_serving'], $_POST['markup_3_serving'], $_POST['markup_2_serving'], $_POST['markup_sides'], $_POST['volume_reward'], $menu_id, $posted_default_value, 12.5, $_POST['assembly_fee'], $_POST['delivery_assembly_fee']);
				}
			*/

			// do menu item updates
			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $this->CurrentStore->id,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false
			));

			while ($DAO_menu_item->fetch())
			{
				$orig_DAO_menu_to_menu_item = $DAO_menu_item->DAO_menu_to_menu_item->cloneObj(false, false);

				if (!empty($DAO_menu_item->is_price_controllable) && isset($_POST['ovr_' . $DAO_menu_item->id]))
				{
					if (!empty($_POST['ovr_' . $DAO_menu_item->id]))
					{
						$DAO_menu_item->DAO_menu_to_menu_item->override_price = CGPC::do_clean($_POST['ovr_' . $DAO_menu_item->id], TYPE_NUM);
					}
					else
					{
						$DAO_menu_item->DAO_menu_to_menu_item->override_price = 'NULL';
					}
				}

				if (!empty($DAO_menu_item->is_visibility_controllable))
				{
					if (isset($_POST['vis_' . $DAO_menu_item->id]))
					{
						$DAO_menu_item->DAO_menu_to_menu_item->is_visible = CGPC::do_clean($_POST['vis_' . $DAO_menu_item->id], TYPE_NUM);
					}
					if (isset($_POST['hid_' . $DAO_menu_item->id]))
					{
						$DAO_menu_item->DAO_menu_to_menu_item->is_hidden_everywhere = CGPC::do_clean($_POST['hid_' . $DAO_menu_item->id], TYPE_NUM);
					}
				}

				if (isset($_POST['pic_' . $DAO_menu_item->id]))
				{
					$DAO_menu_item->DAO_menu_to_menu_item->show_on_pick_sheet = CGPC::do_clean($_POST['pic_' . $DAO_menu_item->id], TYPE_NUM);
				}
				if (isset($_POST['form_' . $DAO_menu_item->id]))
				{
					$DAO_menu_item->DAO_menu_to_menu_item->show_on_order_form = CGPC::do_clean($_POST['form_' . $DAO_menu_item->id], TYPE_NUM);
				}

				$DAO_menu_item->DAO_menu_to_menu_item->update($orig_DAO_menu_to_menu_item);
			}
			$DAO_menu_item->free();
		}

		$Form->DefaultValues['is_default_markup'] = "no";
		$Form->DefaultValues['delivery_assembly_fee'] = 0;
		$Form->DefaultValues['assembly_fee'] = 25;

		$DAO_mark_up_multi = $this->CurrentStore->getMarkUpMultiObj($menu_id);

		if ($DAO_mark_up_multi)
		{
			$Form->DefaultValues['markup_2_serving'] = $DAO_mark_up_multi->markup_value_2_serving;
			$Form->DefaultValues['markup_3_serving'] = $DAO_mark_up_multi->markup_value_3_serving;
			$Form->DefaultValues['markup_4_serving'] = $DAO_mark_up_multi->markup_value_4_serving;
			$Form->DefaultValues['markup_6_serving'] = $DAO_mark_up_multi->markup_value_6_serving;
			$Form->DefaultValues['markup_sides'] = $DAO_mark_up_multi->markup_value_sides;
			$Form->DefaultValues['is_default_markup'] = ($DAO_mark_up_multi->is_default && $DAO_mark_up_multi->menu_id_start == $DAO_menu->id) ? "yes" : "no";
			$Form->DefaultValues['delivery_assembly_fee'] = $DAO_mark_up_multi->delivery_assembly_fee;
			$Form->DefaultValues['assembly_fee'] = $DAO_mark_up_multi->assembly_fee;
		}

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::min => 0,
			CForm::attribute => array(
				'step' => '0.01',
				'data-pricing_type' => CMenuItem::TWO,
				'data-orgval' => $DAO_mark_up_multi->markup_value_2_serving,
			),
			CForm::css_class => 'markup-input',
			CForm::name => 'markup_2_serving'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array(
				'step' => '0.01',
				'data-pricing_type' => CMenuItem::HALF,
				'data-orgval' => $DAO_mark_up_multi->markup_value_3_serving,
			),
			CForm::css_class => 'markup-input',
			CForm::name => 'markup_3_serving'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array(
				'step' => '0.01',
				'data-pricing_type' => CMenuItem::FOUR,
				'data-orgval' => $DAO_mark_up_multi->markup_value_4_serving,
			),
			CForm::css_class => 'markup-input',
			CForm::name => 'markup_4_serving'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array(
				'step' => '0.01',
				'data-pricing_type' => CMenuItem::FULL,
				'data-orgval' => $DAO_mark_up_multi->markup_value_6_serving,
			),
			CForm::css_class => 'markup-input',
			CForm::name => 'markup_6_serving'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 2,
			CForm::min => 0,
			CForm::attribute => array(
				'step' => '0.01',
				'data-orgval' => $DAO_mark_up_multi->markup_value_sides,
			),
			CForm::css_class => 'markup-input',
			CForm::name => 'markup_sides'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::size => 2,
			CForm::number => true,
			CForm::name => 'volume_reward'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 5,
			CForm::attribute => array(
				'step' => '0.01',
				'data-orgval' => $DAO_mark_up_multi->assembly_fee,
			),
			CForm::name => 'assembly_fee'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::size => 5,
			CForm::attribute => array(
				'step' => '0.01',
				'data-orgval' => $DAO_mark_up_multi->delivery_assembly_fee,
			),
			CForm::name => 'delivery_assembly_fee'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "is_default_markup",
			CForm::value => 'no'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "is_default_markup",
			CForm::value => 'yes'
		));

		// Build menu array
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->CurrentStore->id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$tabIndex = 0;
		$menuItemArray = array();
		while ($DAO_menu_item->fetch())
		{
			// add items to main array
			$menuItemArray[$DAO_menu_item->category_group][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

			// create form elements
			$Form->DefaultValues['ovr_' . $DAO_menu_item->id] = $DAO_menu_item->DAO_menu_to_menu_item->override_price;
			$Form->AddElement(array(
				CForm::type => CForm::Number,
				CForm::name => 'ovr_' . $DAO_menu_item->id,
				CForm::step => '0.01',
				CForm::min => '0',
				CForm::max => '9999',
				CForm::attribute => array(
					'data-category_group' => $DAO_menu_item->category_group,
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-pricing_type' => $DAO_menu_item->pricing_type,
					'data-price' => $DAO_menu_item->price,
					'data-orgval' => $DAO_menu_item->DAO_menu_to_menu_item->override_price,
					'data-ltd_menu_item_value' => $DAO_menu_item->DAO_recipe->ltd_menu_item_value,
					'data-tier_1_price' => $DAO_menu_item->getTierPrice($DAO_menu_item->pricing_type, 1),
					'data-tier_2_price' => $DAO_menu_item->getTierPrice($DAO_menu_item->pricing_type, 2),
					'data-tier_3_price' => $DAO_menu_item->getTierPrice($DAO_menu_item->pricing_type, 3)
				),
				CForm::tabindex => $tabIndex++,
				CForm::css_class => 'form-control-sm no-spin-button w-auto override-price-input',
				CForm::readonly => empty($DAO_menu_item->is_price_controllable)
			));

			$Form->DefaultValues['vis_' . $DAO_menu_item->id] = $DAO_menu_item->isVisible();
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'vis_' . $DAO_menu_item->id,
				CForm::attribute => array(
					'data-category_group' => $DAO_menu_item->category_group,
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-pricing_type' => $DAO_menu_item->pricing_type,
					'data-orgval' => $DAO_menu_item->isVisible(),
					'data-visibility_type' => 'vis'
				),
				CForm::css_class => 'custom-select-sm w-auto visibility-select',
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				),
				CForm::readonly => empty($DAO_menu_item->is_visibility_controllable)
			));

			$Form->DefaultValues['pic_' . $DAO_menu_item->id] = $DAO_menu_item->isShowOnPickSheet();
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'pic_' . $DAO_menu_item->id,
				CForm::attribute => array(
					'data-category_group' => $DAO_menu_item->category_group,
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-pricing_type' => $DAO_menu_item->pricing_type,
					'data-orgval' => $DAO_menu_item->isShowOnPickSheet(),
					'data-visibility_type' => 'pic'
				),
				CForm::css_class => 'custom-select-sm w-auto visibility-select',
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				)
			));

			$Form->DefaultValues['form_' . $DAO_menu_item->id] = $DAO_menu_item->isShowOnOrderForm();
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'form_' . $DAO_menu_item->id,
				CForm::attribute => array(
					'data-category_group' => $DAO_menu_item->category_group,
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-pricing_type' => $DAO_menu_item->pricing_type,
					'data-orgval' => $DAO_menu_item->isShowOnOrderForm(),
					'data-visibility_type' => 'form'
				),
				CForm::css_class => 'custom-select-sm w-auto visibility-select',
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				)
			));

			$Form->DefaultValues['hid_' . $DAO_menu_item->id] = $DAO_menu_item->isHiddenEverywhere();
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'hid_' . $DAO_menu_item->id,
				CForm::attribute => array(
					'data-category_group' => $DAO_menu_item->category_group,
					'data-menu_item_id' => $DAO_menu_item->id,
					'data-recipe_id' => $DAO_menu_item->recipe_id,
					'data-pricing_type' => $DAO_menu_item->pricing_type,
					'data-orgval' => $DAO_menu_item->isHiddenEverywhere(),
					'data-visibility_type' => 'hid'
				),
				CForm::css_class => 'custom-select-sm w-auto visibility-select',
				CForm::options => array(
					0 => 'No',
					1 => 'Yes'
				),
				CForm::readonly => empty($DAO_menu_item->is_visibility_controllable)
			));
		}
		$DAO_menu_item->free();

		// Build pricing array
		$pricingReference = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->CurrentStore->id,
			'exclude_menu_item_is_bundle' => false,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'join_pricing_to_menu_item' => 'INNER',
			// only get items with pricing info
			'groupBy' => 'RecipeID'
			// list by recipe instead of by menu item.
		));

		$pricingReferenceArray = array();
		while ($pricingReference->fetch())
		{
			$pricingReferenceArray[$pricingReference->recipe_id] = $pricingReference->cloneObj();
		}
		$pricingReference->free();

		$DAO_order_minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $this->CurrentStore->id, $DAO_menu->id);

		$this->Template->assign('DAO_store', $this->CurrentStore);
		$this->Template->assign('DAO_menu', $DAO_menu);
		$this->Template->assign('DAO_order_minimum', $DAO_order_minimum);
		$this->Template->assign('pricingReferenceArray', $pricingReferenceArray);
		$this->Template->assign('menuItemArray', $menuItemArray);

		$this->Template->assign('form', $Form->render());
	}
}

?>