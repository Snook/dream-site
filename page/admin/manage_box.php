<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CBox.php");

class page_admin_manage_box extends CPageAdminOnly
{
	public $ManagerForm = null;
	public $BoxForm = null;
	public $Bundle1Form = null;
	public $Bundle2Form = null;

	private $singleStore = false;

	public function __construct()
	{
		parent::__construct();

		$this->Template->setScript('foot', SCRIPT_PATH . '/admin/manage_box.min.js');
		$this->Template->assign('page_title', 'Manage Delivered Boxes');
		$this->Template->assign('topnav', (($this->CurrentStore->store_type == CStore::DISTRIBUTION_CENTER) ? 'store' : 'tools'));

		$this->ManagerForm = new CForm();
		$this->ManagerForm->Repost = true;
		$this->ManagerForm->Bootstrap = true;
		$this->ManagerForm->ElementID = true;

		$this->BoxForm = new CForm();
		$this->BoxForm->Repost = true;
		$this->BoxForm->Bootstrap = true;
		$this->BoxForm->ElementID = true;

		$this->Bundle1Form = new CForm();
		$this->Bundle1Form->Repost = true;
		$this->Bundle1Form->Bootstrap = true;
		$this->Bundle1Form->ElementID = false;

		$this->Bundle2Form = new CForm();
		$this->Bundle2Form->Repost = true;
		$this->Bundle2Form->Bootstrap = true;
		$this->Bundle2Form->ElementID = false;
	}

	function runHomeOfficeManager()
	{
		$this->runManageBox();
	}

	function runSiteAdmin()
	{
		$this->runManageBox();
	}

	function runFranchiseOwner()
	{
		$this->singleStore = true;
		$this->runManageBox();
	}

	function runFranchiseManager()
	{
		$this->singleStore = true;
		$this->runManageBox();
	}

	function runOpsLead()
	{
		$this->singleStore = true;
		$this->runManageBox();
	}

	function runManageBox()
	{
		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->active = 1;
		$DAO_store->store_type = CStore::DISTRIBUTION_CENTER;
		$DAO_store->find();

		$storeDeployOptions = array();
		$storeMenuOptions = array(
			'base' => 'Base Boxes'
		);

		while ($DAO_store->fetch())
		{
			$storeMenuOptions[$DAO_store->id] = $DAO_store->store_name;

			$storeDeployOptions[] = array(
				'text' => $DAO_store->store_name,
				'value' => $DAO_store->id
			);
		}

		$this->Template->assign('storeDeployOptions', json_encode($storeDeployOptions));

		$this->ManagerForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'store_filter',
			CForm::css_class => 'store-select-filter',
			CForm::options => $storeMenuOptions
		));

		$editBox = CGPC::do_clean((!empty($_REQUEST['edit']) ? $_REQUEST['edit'] : false), TYPE_INT);
		$createBox = CGPC::do_clean((!empty($_REQUEST['create']) ? $_REQUEST['create'] : false), TYPE_BOOL);

		// get all active boxes for display else if editing get a single box
		$box = DAO_CFactory::create('box', true);
		if ($editBox)
		{
			$box->id = $editBox;
			$box->menu_item_array = true;
		}
		if ($this->singleStore)
		{
			$box->store_id = $this->CurrentStore->id;
		}
		$box->_get['box_bundle_1_obj'] = true;
		$box->_get['box_bundle_2_obj'] = true;
		$box->_get['store_obj'] = true;
		$box->_get['menu_obj'] = true;
		$box->_get['orders'] = true;
		$box->_get['number_sold_n'] = true;
		$box->whereAddIn('box.menu_id', array_keys($this->CurrentFutureMenusDelivered), 'int');
		$box->orderBy("box.store_id, box.sort, box.availability_date_end");
		if ($editBox)
		{
			if ($box->find(true))
			{
				$editBox = $box->cloneObj();

				$this->Template->assign('createBox', false);
				$this->Template->assign('editBox', $editBox);
				$this->Template->assign('boxArray', false);
				$this->Template->assign('singleStore', $this->singleStore);
			}
			else
			{
				$this->Template->setErrorMsg('Requested box was not found.');
				CApp::bounce('/?page=admin_manage_box');
			}
		}
		else if (empty($createBox))
		{
			$boxArray = $box->getFetchAllResult();

			$this->Template->assign('createBox', false);
			$this->Template->assign('editBox', false);
			$this->Template->assign('boxArray', $boxArray);
			$this->Template->assign('singleStore', $this->singleStore);
		}
		else
		{
			$createBox = $box->cloneObj();
			$createBox->menu_id = current($this->CurrentFutureMenusDelivered)->id;

			$this->Template->assign('createBox', $createBox);
			$this->Template->assign('editBox', false);
			$this->Template->assign('boxArray', false);
			$this->Template->assign('singleStore', $this->singleStore);
		}

		if ($editBox || $createBox)
		{
			$this->BoxForm->DefaultValues['id'] = 'new'; // new so we know to create a new box in the processor
			$this->BoxForm->DefaultValues['sort'] = 1;
			$this->BoxForm->DefaultValues['is_visible_to_customer'] = 1;
			$this->BoxForm->DefaultValues['title'] = 'Build your own';
			$this->BoxForm->DefaultValues['css_icon'] = 'icon-send_love';
			$this->BoxForm->DefaultValues['availability_date_start'] = CTemplate::formatDateTime('Y-m-d\TH:i', (current($this->CurrentFutureMenusDelivered)->global_menu_start_date), false, 'next thursday');
			$this->BoxForm->DefaultValues['availability_date_end'] = CTemplate::formatDateTime('Y-m-d\TH:i', (current($this->CurrentFutureMenusDelivered)->global_menu_end_date), false, 'next thursday');
			$this->BoxForm->DefaultValues['menu_id'] = current($this->CurrentFutureMenusDelivered)->id;

			$this->Bundle1Form->DefaultValues['id'] = 'new';
			$this->Bundle1Form->DefaultValues['bundle_name'] = 'Medium';
			$this->Bundle1Form->DefaultValues['menu_item_description'] = 'Each dinner serves 2-3';
			$this->Bundle1Form->DefaultValues['number_items_required'] = 4;
			$this->Bundle1Form->DefaultValues['number_servings_required'] = 12;
			$this->Bundle1Form->DefaultValues['price'] = '99.00';
			$this->Bundle1Form->DefaultValues['price_shipping'] = '0.00';

			$this->Bundle2Form->DefaultValues['id'] = 'new';
			$this->Bundle2Form->DefaultValues['bundle_name'] = 'Large';
			$this->Bundle2Form->DefaultValues['menu_item_description'] = 'Each dinner serves 4-6';
			$this->Bundle2Form->DefaultValues['number_items_required'] = 4;
			$this->Bundle2Form->DefaultValues['number_servings_required'] = 24;
			$this->Bundle2Form->DefaultValues['price'] = '179.00';
			$this->Bundle2Form->DefaultValues['price_shipping'] = '0.00';

			if ($editBox)
			{
				// assign all the default values for the box
				foreach (DAO_CFactory::create('box', true)->table() as $key => $value)
				{
					switch ($key)
					{
						case 'availability_date_start':
						case 'availability_date_end':
							$this->BoxForm->DefaultValues[$key] = CTemplate::formatDateTime('Y-m-d\TH:i', $editBox->{$key}); // remove seconds to reduce form complexity
							break;
						case 'is_deleted':
							break;
						default:
							$this->BoxForm->DefaultValues[$key] = $editBox->{$key};
							break;
					}
				}

				// assign box values for items not in the default table
				$this->BoxForm->DefaultValues['store_id'] = (!empty($editBox->store_obj->id)) ? $editBox->store_obj->id : null;
				$this->BoxForm->DefaultValues['menu_id'] = $editBox->menu_id;
				$this->BoxForm->DefaultValues['has_orders'] = $editBox->number_sold_n;

				// if there is a box_bundle_1 assign all the default values
				if (!empty($editBox->box_bundle_1))
				{
					foreach (DAO_CFactory::create('bundle', true)->table() as $key => $value)
					{
						if (isset($editBox->box_bundle_1_obj->{$key}))
						{
							$this->Bundle1Form->DefaultValues[$key] = $editBox->box_bundle_1_obj->{$key};
						}
					}
				}

				// if there is a box_bundle_2 assign all the default values
				if (!empty($editBox->box_bundle_2))
				{
					foreach (DAO_CFactory::create('bundle', true)->table() as $key => $value)
					{
						if (isset($editBox->box_bundle_2_obj->{$key}))
						{
							$this->Bundle2Form->DefaultValues[$key] = $editBox->box_bundle_2_obj->{$key};
						}
					}
				}
			}

			if ($createBox)
			{
				$menu_id = $createBox->menu_id;
				$store_id = $createBox->store_id;
				$parent_store_id = !empty($createBox->store_obj->parent_store_id) ? $createBox->store_obj->parent_store_id : null;
			}
			else if ($editBox)
			{
				$menu_id = $editBox->menu_id;
				$store_id = $editBox->store_id;
				$parent_store_id = !empty($editBox->store_obj->parent_store_id) ? $editBox->store_obj->parent_store_id : null;
			}

			// get all the menu items for the current box or for new box
			if (!empty($menu_id))
			{
				$menuItemCategory = DAO_CFactory::create('menu_item_category', true);

				$menuToMenuItem = DAO_CFactory::create('menu_to_menu_item', true);
				$menuToMenuItem->store_id = ((empty($store_id)) ? 'NULL' : $parent_store_id);
				$menuToMenuItem->menu_id = $menu_id;

				$menuItem = DAO_CFactory::create('menu_item', true);
				$menuItem->selectAdd("menu_to_menu_item.override_price");
				$menuItem->selectAdd("menu_to_menu_item.is_visible");
				$menuItem->selectAdd("menu_to_menu_item.is_hidden_everywhere");
				$menuItem->selectAdd("menu_to_menu_item.show_on_pick_sheet");
				$menuItem->selectAdd("menu_item_category.category_type AS menu_item_category_type");
				$menuItem->selectAdd("menu_item_category.display_title AS menu_item_category_display_title");
				$menuItem->joinAdd($menuItemCategory, array(
					'joinType' => 'LEFT',
					'useWhereAsOn' => true
				));
				$menuItem->joinAdd($menuToMenuItem, array('useWhereAsOn' => true));
				if (empty($store_id))
				{
					$menuItem->whereAdd("ISNULL(menu_item.copied_from)");
				}
				$menuItem->orderBy("menu_item_category.global_order_value, menu_item.subcategory_label, menu_to_menu_item.featuredItem DESC, menu_to_menu_item.menu_order_value, menu_item.id");
				$menuItem->find();

				$menuItems = array(
					'category' => array(),
					'category_titles' => array(),
					'size' => array(),
					'menu_item' => array()
				);

				// put all the menu items in an array
				while ($menuItem->fetch())
				{
					$menuItems['category'][$menuItem->menu_item_category_id][$menuItem->entree_id][$menuItem->pricing_type] = $menuItem->cloneObj();
					$menuItems['category_titles'][$menuItem->menu_item_category_id] = $menuItem->menu_item_category_display_title;

					$menuItems['size'][$menuItem->pricing_type][$menuItem->menu_item_category_id][$menuItem->entree_id] = $menuItem->cloneObj();

					// include sided & sweets in medium list
					if ($menuItem->pricing_type == CMenuItem::FULL && $menuItem->menu_item_category_id == 9)
					{
						$menuItems['size'][CMenuItem::HALF][$menuItem->menu_item_category_id][$menuItem->entree_id] = $menuItem->cloneObj();
					}

					$menuItems['menu_item'][$menuItem->id] = $menuItem->cloneObj();
				}

				$this->Template->assign('menuItems', $menuItems);
			}

			// add hidden element for the box being edited
			$this->BoxForm->addElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'id'
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'has_orders'
			));

			// if the page is being managed with single store permissions otherwise permission to edit all stores
			if ($this->singleStore)
			{
				$storeOptions = array($this->CurrentStore->id => $this->CurrentStore->store_name);
			}
			else
			{
				$dcStore = DAO_CFactory::create('store', true);
				$dcStore->active = 1;
				$dcStore->store_type = CStore::DISTRIBUTION_CENTER;
				$dcStore->find();

				$storeOptions['NULL'] = array(
					'title' => 'Default',
					CForm::disabled => (($editBox) ? true : false)
				);

				while ($dcStore->fetch())
				{
					$storeOptions[$dcStore->id] = array(
						'title' => $dcStore->store_name,
						CForm::disabled => (($editBox) ? true : false)
					);
				}
			}

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'store_id',
				CForm::readonly => true,
				CForm::options => $storeOptions
			));

			$menuOptions = array();
			if ($createBox)
			{
				foreach ($this->CurrentFutureMenusDelivered as $menu)
				{
					$menuOptions[$menu->id] = array(
						'title' => $menu->menu_name,
						'data' => array(
							'data-date_start' => CTemplate::formatDateTime('Y-m-d\TH:i', $menu->global_menu_start_date, false, 'next thursday'),
							'data-date_end' => CTemplate::formatDateTime('Y-m-d\TH:i', $menu->global_menu_end_date, false, 'next thursday')
						)
					);
				}
			}
			else if ($editBox)
			{
				$menuOptions = array(
					$editBox->menu_id => array(
						'title' => $editBox->menu_obj->menu_name,
						CForm::disabled => true
					)
				);
			}

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'menu_id',
				CForm::required => true,
				CForm::options => $menuOptions
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'is_visible_to_customer',
				CForm::label => 'Show on web'
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'title',
				CForm::required => true,
				CForm::css_class => 'dd-strip-tags'
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::TextArea,
				CForm::name => 'description',
				CForm::required => true,
				CForm::css_class => 'dd-strip-tags',
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'css_icon',
				CForm::required => true,
				CForm::options => array($this->BoxForm->DefaultValues['css_icon'] => $this->BoxForm->DefaultValues['css_icon'])
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::Number,
				CForm::name => 'sort',
				CForm::required => true
			));

			$boxTypeOptions = array(
				CBox::DELIVERED_CUSTOM => 'Build your own',
				CBox::DELIVERED_FIXED => 'Curated'
			);

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'box_type',
				CForm::required => true,
				CForm::options => array(
					CBox::DELIVERED_CUSTOM => array(
						'title' => 'Build your own',
						CForm::disabled => (!empty($editBox))
					),
					CBox::DELIVERED_FIXED => array(
						'title' => 'Fixed',
						CForm::disabled => (!empty($editBox))
					)
				)
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DateTimeLocal,
				CForm::name => 'availability_date_start',
				//CForm::min => $this->BoxForm->DefaultValues['availability_date_start'],
				CForm::required => true
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::DateTimeLocal,
				CForm::name => 'availability_date_end',
				CForm::required => true
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::Submit,
				CForm::name => 'BoxFormSubmit',
				CForm::value => (!empty($createBox) ? 'Create box' : 'Update box'),
				CForm::css_class => 'btn btn-primary btn-block btn-spinner'
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'box_bundle_1_active',
				CForm::label => 'Box 1 - Medium',
				CForm::attribute => array(
					'data-bundle_id' => ((empty($editBox->box_bundle_1)) ? 'new' : $editBox->box_bundle_1),
					'data-box_id' => ((empty($editBox->id)) ? 'new' : $editBox->id),
					'data-box_bundle' => 'box_bundle_1'
				)
			));

			$this->BoxForm->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'box_bundle_2_active',
				CForm::label => 'Box 2 - Large',
				CForm::attribute => array(
					'data-bundle_id' => ((empty($editBox->box_bundle_2)) ? 'new' : $editBox->box_bundle_2),
					'data-box_id' => ((empty($editBox->id)) ? 'new' : $editBox->id),
					'data-box_bundle' => 'box_bundle_2'
				)
			));

			if ($editBox)
			{
				/* Bundle 1 */
				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Hidden,
					CForm::name => 'id'
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Text,
					CForm::name => 'bundle_name',
					CForm::required => true,
					CForm::css_class => 'dd-strip-tags',
					CForm::disabled => (empty($editBox->box_bundle_1_active))
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Text,
					CForm::name => 'menu_item_description',
					CForm::required => true,
					CForm::css_class => 'dd-strip-tags',
					CForm::disabled => (empty($editBox->box_bundle_1_active))
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'number_items_required',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_1_active) || $editBox->number_sold_n)
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'number_servings_required',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_1_active) || $editBox->number_sold_n)
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'price',
					CForm::step => '0.01',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_1_active) || $editBox->number_sold_n)
				));

				$this->Bundle1Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'price_shipping',
					CForm::step => '0.01',
					CForm::required => true,
					CForm::disabled => (empty($editBox->box_bundle_1_active) || $editBox->number_sold_n)
				));

				// menu items bundle 1
				if (!empty($menuItems))
				{
					foreach ($menuItems['size'][CMenuItem::HALF] as $menuItemSize)
					{
						foreach ($menuItemSize as $menuItem)
						{
							$this->Bundle1Form->DefaultValues['bundle_1_check_' . $menuItem->id] = (!empty($editBox->box_bundle_1_obj->menu_item_array['menu_item'][$menuItem->id]->current_offering));
							$this->Bundle1Form->addElement(array(
								CForm::type => CForm::CheckBox,
								CForm::name => 'bundle_1_check_' . $menuItem->id,
								CForm::attribute => array(
									'data-bundle_id' => $editBox->box_bundle_1,
									'data-bundle_menu_item_id' => $menuItem->id
								),
								CForm::disabled => (!empty($editBox->number_sold_n) && $editBox->box_type != CBox::DELIVERED_CUSTOM)
							));
						}
					}
				}

				/* Bundle 2 */
				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Hidden,
					CForm::name => 'id'
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Text,
					CForm::name => 'bundle_name',
					CForm::required => true,
					CForm::css_class => 'dd-strip-tags',
					CForm::disabled => (empty($editBox->box_bundle_2_active))
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Text,
					CForm::name => 'menu_item_description',
					CForm::required => true,
					CForm::css_class => 'dd-strip-tags',
					CForm::disabled => (empty($editBox->box_bundle_2_active))
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'number_items_required',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_2_active) || $editBox->number_sold_n)
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'number_servings_required',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_2_active) || $editBox->number_sold_n)
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'price',
					CForm::step => '0.01',
					CForm::required => true,
					CForm::attribute => array(
						'data-has_orders' => $editBox->number_sold_n,
					),
					CForm::disabled => (empty($editBox->box_bundle_2_active) || $editBox->number_sold_n)
				));

				$this->Bundle2Form->addElement(array(
					CForm::type => CForm::Number,
					CForm::name => 'price_shipping',
					CForm::step => '0.01',
					CForm::required => true,
					CForm::disabled => (empty($editBox->box_bundle_2_active) || $editBox->number_sold_n)
				));

				// menu items bundle 2
				if (!empty($menuItems))
				{
					foreach ($menuItems['size'][CMenuItem::FULL] as $menuItemSize)
					{
						foreach ($menuItemSize as $menuItem)
						{
							$this->Bundle2Form->DefaultValues['bundle_2_check_' . $menuItem->id] = (!empty($editBox->box_bundle_2_obj->menu_item_array['menu_item'][$menuItem->id]->current_offering));
							$this->Bundle2Form->addElement(array(
								CForm::type => CForm::CheckBox,
								CForm::name => 'bundle_2_check_' . $menuItem->id,
								CForm::attribute => array(
									'data-bundle_id' => $editBox->box_bundle_2,
									'data-bundle_menu_item_id' => $menuItem->id
								),
								CForm::disabled => (!empty($editBox->number_sold_n) && $editBox->box_type != CBox::DELIVERED_CUSTOM)
							));
						}
					}
				}
			}
		}

		$this->Template->assign('ManagerForm', $this->ManagerForm->Render());
		$this->Template->assign('BoxForm', $this->BoxForm->Render());
		$this->Template->assign('Bundle1Form', $this->Bundle1Form->Render());
		$this->Template->assign('Bundle2Form', $this->Bundle2Form->Render());
	}
}

?>