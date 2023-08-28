<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_menus extends CPageAdminOnly
{

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		//get next menu that needs to be created
		$LastMenu = DAO_CFactory::create('menu');
		$LastMenu->orderBy('menu_start DESC');
		$LastMenu->limitBy(0, 1);
		$LastMenu->selectAdd('DATE_ADD(menu_start,INTERVAL 1 MONTH) as "next_start", global_menu_end_date');
		$LastMenu->find(true);
		$NextMonthsMenuName = date("F Y", strtotime($LastMenu->next_start));

		if (isset($_REQUEST['create']))
		{
			$MenuEndDate = strtotime('last day of ' . date('F Y', strtotime($LastMenu->next_start)));
			$lastEndDateTS = strtotime($LastMenu->global_menu_end_date);
			$NewStartDateTS = mktime(0, 0, 0, date("n", $lastEndDateTS), date("j", $lastEndDateTS) + 1, date("Y", $lastEndDateTS));

			$NewMenu = DAO_CFactory::create('menu');
			$NewMenu->menu_start = $LastMenu->next_start;
			$NewMenu->menu_name = $NextMonthsMenuName;
			$NewMenu->global_menu_end_date = date('Y-m-d', $MenuEndDate);
			$NewMenu->global_menu_start_date = date('Y-m-d', $NewStartDateTS);
			$newId = $NewMenu->insert();

			CSession::generateDeliveredSessionsForMenu($newId);
			CSession::generateWalkInSessionsForMenu($newId);

			COrderMinimum::carryForwardMinimums($LastMenu->id, $newId);

			$tpl->setStatusMsg('New menu created, <span class="text-danger">verify menu end date!</span>');

			CApp::bounce('main.php?page=admin_menus&menu_edit=' . $newId);
		}

		$menu_edit = array_key_exists('menu_edit', $_GET) ? CGPC::do_clean($_GET['menu_edit'], TYPE_STR) : null;

		$Form->DefaultValues['menu_edit'] = $menu_edit;
		//php 8 $action = CGPC::do_clean($_REQUEST['action'] ?? null, TYPE_STR);
		$action = CGPC::do_clean((array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : null), TYPE_STR);
		if ($menu_edit && isset($action))
		{
			if (isset($_REQUEST['item']) && $_REQUEST['item'])
			{
				$_REQUEST['item'] = CGPC::do_clean($_REQUEST['item'], TYPE_INT);

				if (!empty($_REQUEST['item']))
				{
					switch ($action)
					{
						case 'up':
							$mi = DAO_CFactory::create('menu_item');
							$menuItemInfo = DAO_CFactory::create('menu_to_menu_item');
							$mi->entree_id = CGPC::do_clean($_REQUEST['entree_id'], TYPE_INT);
							$menuItemInfo->menu_id = $menu_edit;
							$mi->joinAdd($menuItemInfo);
							$cnt = $mi->find();
							$mi->fetch();
							$iter = 0;

							if ($mi->menu_order_value > 1)
							{
								$miOld = DAO_CFactory::create('menu_to_menu_item');
								$miOld->menu_id = $menu_edit;
								$miOld->menu_order_value = $mi->menu_order_value - 1;

								if ($miOld->find())
								{
									while ($miOld->fetch())
									{
										$miOld->menu_order_value = $mi->menu_order_value;
										$miOld->update();
									}
								}

								while ($iter < $cnt)
								{
									$miNew = DAO_CFactory::create('menu_to_menu_item');
									$miNew->menu_id = $menu_edit;
									$miNew->menu_order_value = $mi->menu_order_value - 1;
									$miNew->id = $mi->id;
									$miNew->update();
									$mi->fetch();
									$iter++;
								}
							}
							else if (!$mi->menu_order_value)
							{
								$miOld = DAO_CFactory::create('menu_to_menu_item');
								$miOld->menu_id = $menu_edit;
								$num = $miOld->find();
								$mi->menu_order_value = $num + 1;
								$mi->update();
							}

							CApp::bounce('main.php?page=admin_menus&menu_edit=' . $menu_edit);
							break;

						case 'down':
							$mi = DAO_CFactory::create('menu_item');
							$menuItemInfo = DAO_CFactory::create('menu_to_menu_item');
							$mi->entree_id = CGPC::do_clean($_REQUEST['entree_id'], TYPE_INT);
							$menuItemInfo->menu_id = $menu_edit;
							$mi->joinAdd($menuItemInfo);
							$cnt = $mi->find();
							$mi->fetch();
							$iter = 0;

							if ($mi->menu_order_value)
							{
								$miOld = DAO_CFactory::create('menu_to_menu_item');
								$miOld->menu_id = $menu_edit;
								$miOld->menu_order_value = $mi->menu_order_value + 1;

								if ($miOld->find())
								{
									while ($miOld->fetch())
									{
										$miOld->menu_order_value = $mi->menu_order_value;
										$miOld->update();
									}
								}
								while ($iter < $cnt)
								{
									$miNew = DAO_CFactory::create('menu_to_menu_item');
									$miNew->menu_id = $menu_edit;
									$miNew->menu_order_value = $mi->menu_order_value + 1;
									$miNew->id = $mi->id;
									$miNew->update();
									$mi->fetch();
									$iter++;
								}
							}
							else if (!$mi->menu_order_value)
							{
								$miOld = DAO_CFactory::create('menu_to_menu_item');
								$miOld->menu_id = $menu_edit;
								$num = $miOld->find();
								$mi->menu_order_value = $num + 1;
								$mi->update();
							}

							CApp::bounce('main.php?page=admin_menus&menu_edit=' . $menu_edit);
							break;

						case 'remove':
							$MenuItem = DAO_CFactory::create('menu_item');
							$MenuItem->entree_id = CGPC::do_clean($_REQUEST['entree_id'], TYPE_INT);
							if ($MenuItem->find())
							{
								while ($MenuItem->fetch())
								{
									$mi = DAO_CFactory::create('menu_to_menu_item');
									$mi->menu_id = $menu_edit;
									$mi->menu_item_id = $MenuItem->id;

									if ($mi->find(true))
									{
										$mi->delete();
									}
								}
							}

							CApp::bounce('main.php?page=admin_menus&menu_edit=' . $menu_edit);
							break;

						case 'feature':
							$menuItemInfo = DAO_CFactory::create('menu_item');
							$menuToMenuItem = DAO_CFactory::create('menu_to_menu_item');
							$menuToMenuItem->menu_id = $menu_edit;
							$menuItemInfo->joinAdd($menuToMenuItem);
							$cnt = $menuItemInfo->find();

							while ($menuItemInfo->fetch())
							{
								$mi = DAO_CFactory::create('menu_to_menu_item');
								$mi->id = $menuItemInfo->id;

								if ($menuItemInfo->entree_id == $_REQUEST['entree_id'])
								{
									$mi->featuredItem = 1;
								}
								else
								{
									$mi->featuredItem = 0;
								}

								$mi->update();
							}

							CApp::bounce('main.php?page=admin_menus&menu_edit=' . $menu_edit);
							break;

						case 'additem':
							$mi = DAO_CFactory::create('menu_to_menu_item');
							$mi->menu_id = $menu_edit;
							//$mi->menu_item_id = $_REQUEST['item'];
							$cnt = $mi->find();
							$isFound = false;
							$highest = 0;

							while ($mi->fetch())
							{
								if ($mi->menu_item_id == $_REQUEST['item'])
								{
									$isFound = true;
								}
								if ($mi->menu_order_value > $highest)
								{
									$highest = $mi->menu_order_value;
								}
							}

							if (!$isFound)
							{
								$existing_menu_items = null;
								// have to find all of the
								$MenuItem = DAO_CFactory::create('menu_item');
								$MenuItem->entree_id = CGPC::do_clean($_REQUEST['item'], TYPE_INT);

								if ($MenuItem->find())
								{
									while ($MenuItem->fetch())
									{
										$existing_menu_items[$MenuItem->pricing_type] = $MenuItem->toArray();
										//$mi->featuredItem = 0;
										//$mi->menu_item_id = $MenuItem->id;
										//$mi->menu_order_value = $highest + 1;
										//$mi->insert();
									}

									if (isset($existing_menu_items[CMenuItem::FULL]))
									{
										$MenuItemIns = DAO_CFactory::create('menu_item');
										$MenuItemIns->entree_id = CGPC::do_clean($_REQUEST['item'], TYPE_INT);

										foreach ($existing_menu_items as $element)
										{
											if ($element['pricing_type'] != CMenuItem::LEGACY)
											{
												$mi->featuredItem = 0;
												$mi->menu_item_id = $element['id'];
												$mi->menu_order_value = $highest + 1;
												$mi->insert();
											}
										}
									}
									else
									{
										$tpl->setStatusMsg('Sorry, cannot save this menu item to your new menu.  You must first add new FULL and INTRO pricing items to this menu item.');
									}
								}
							}

							CApp::bounce('main.php?page=admin_menus&menu_edit=' . $menu_edit);
							break;
					}
				}
			}
		}

		//build menu drop down
		$Menu = DAO_CFactory::create('menu');
		$currentMenusOnly = date('Y-m') . '-01';
		// LMH: we really don't need to display old menus.  We don't want any editing after the menu has been set!!!
		$Menu->whereAdd("menu_start > DATE_SUB('$currentMenusOnly', INTERVAL 3 MONTH)");
		$Menu->orderBy('menu_start');
		$numMenus = $Menu->find();

		//create drop down
		$options = array();

		while ($Menu->fetch())
		{
			$options[$Menu->id] = $Menu->menu_name;
		}

		$options['null'] = '--choose a menu to edit--';
		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "menu_edit",
			CForm::onChangeSubmit => true,
			CForm::options => $options
		));

		$Form->addElement(array(
			CForm::type => CForm::Label,
			CForm::name => "next_menu",
			CForm::value => $NextMonthsMenuName
		));

		$copyOptions = array();
		$copyOptions['null'] = 'Create New Menu';

		//display menu details
		if ($menu_edit)
		{
			$Menu_Edit = DAO_CFactory::create('menu');
			$Menu_Edit->id = $menu_edit;
			$numFound = $Menu_Edit->find(true);

			if ($numFound == 1)
			{
				$Form->DefaultValues['menu_description'] = $Menu_Edit->menu_description;
				$Form->DefaultValues['admin_notes'] = $Menu_Edit->admin_notes;
				$Form->DefaultValues['menu_name'] = $Menu_Edit->menu_name;
				$Form->DefaultValues['is_active'] = $Menu_Edit->is_active;
				$Form->DefaultValues['id'] = $Menu_Edit->id;

				$tpl->assign('global_menu_end_date', $Menu_Edit->global_menu_end_date);

				$Form->AddElement(array(
					CForm::type => CForm::TextArea,
					CForm::name => "menu_description",
					CForm::required => false,
					CForm::height => 100,
					CForm::width => 400
				));

				$Form->AddElement(array(
					CForm::type => CForm::TextArea,
					CForm::name => "admin_notes",
					CForm::required => false,
					CForm::maxlength => 255,
					CForm::size => 60
				));

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::name => "is_active"
				));

				$items = array();
				$MenuItems = $Menu_Edit->getMenuItemDAO('FeaturedFirst', false, false, true);

				while ($MenuItems->fetch())
				{
					$item = array();
					$item['id'] = $MenuItems->id;
					$item['entree_id'] = $MenuItems->entree_id;
					$item['menu_item_name'] = $MenuItems->menu_item_name;
					$item['menu_item_description'] = $MenuItems->menu_item_description;
					$item['menu_item_price'] = $MenuItems->price;
					$item['order_value'] = $MenuItems->menu_order_value;
					$item['isFeatured'] = $MenuItems->isFeatured();
					$items[$MenuItems->id] = $item;
				}

				$curplanupdate = false;
				if (count($items) > 0)
				{
					$curplanupdate = true;
					$tpl->assign('update_current_menu_plan', $curplanupdate);
				}

				$tpl->assign('menuItems', $items);
				$Form->DefaultValues['item'] = 0;
				$Form->AddElement(array(
					CForm::type => CForm::MenuItemDropDown,
					CForm::name => "item",
					CForm::required => false,
					CForm::onChangeSubmit => false
				));

				//update description fields
				if ($_POST && isset($_POST['save_changes']))
				{
					$tpl->assign('global_menu_end_date', CGPC::do_clean($_POST['global_menu_end_date'], TYPE_STR));

					$Menu_Edit->global_menu_end_date = CGPC::do_clean($_POST['global_menu_end_date'], TYPE_STR);
					$Menu_Edit->admin_notes = $Form->value('admin_notes');
					$Menu_Edit->menu_description = $Form->value('menu_description');
					$Menu_Edit->is_active = $Form->value('is_active');
					$Menu_Edit->update();

					CSession::generateWalkInSessionsForMenu($Menu_Edit->id, true);

					$tpl->setStatusMsg('Your changes have been saved.');
				}
			}
			else
			{
				$tpl->setErrorMsg('menu error');
			}

			// DavidB: Fetch prior month's menu end date if available
			// ------------------------------------------------------

			list($szYear, $szMonth, $szDay) = explode('-', $Menu_Edit->menu_start);
			$szYear = (int)$szYear;
			$szMonth = (int)$szMonth;

			$szMonth--;
			if ($szMonth < 1)
			{
				$szMonth = 12;
				$szYear--;
			}

			$dtMenuPrev = CMenu::getGlobalMenuEndDate("$szYear-$szMonth-01");
			if (!empty($dtMenuPrev))
			{
				$ts = strtotime($dtMenuPrev) + 86400;
				$tpl->assign('sz_previous_menu_end', date('Y-m-d', $ts));
			}
			else
			{
				$tpl->assign('sz_previous_menu_end', '** Warning: Prior month has no end date set **');
			}

			// ------------------------------------------------------

			// DavidB: Set a sane default end_date if not already set
			// ------------------------------------------------------
			list($szYear, $szMonth, $szDay) = explode('-', $Menu_Edit->global_menu_end_date);

			if (!checkdate((int)$szMonth, (int)$szDay, (int)$szYear))
			{
				// use menu_start as the base
				list($szYear, $szMonth, $szDay) = explode('-', $Menu_Edit->menu_start);

				// and find the number of days in that month
				$iDayCount = date('t', mktime(0, 0, 0, $szMonth, $szDay, $szYear));

				// and give that to the form...
				$tpl->assign('end_date', date('Y-m-d', mktime(0, 0, 0, $szMonth, $iDayCount, $szYear)));
			}
			else
			{
				// we already have a valid end date so use it...
				$tpl->assign('end_date', $Menu_Edit->global_menu_end_date);
			}
			// ------------------------------------------------------
		}

		$formArray = $Form->render();
		$tpl->assign('form_menus', $formArray);
	}
}

?>