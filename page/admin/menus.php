<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_menus extends CPageAdminOnly
{

	function runSiteAdmin()
	{
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

			$this->Template->setStatusMsg('New menu created, <span class="text-danger">verify menu end date!</span>');

			CApp::bounce('/backoffice/menus?menu_edit=' . $newId);
		}

		$menu_edit = array_key_exists('menu_edit', $_GET) ? CGPC::do_clean($_GET['menu_edit'], TYPE_STR) : null;

		$Form->DefaultValues['menu_edit'] = $menu_edit;

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

				$this->Template->assign('global_menu_end_date', $Menu_Edit->global_menu_end_date);

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
					$this->Template->assign('update_current_menu_plan', $curplanupdate);
				}

				$this->Template->assign('menuItems', $items);
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
					$this->Template->assign('global_menu_end_date', CGPC::do_clean($_POST['global_menu_end_date'], TYPE_STR));

					$Menu_Edit->global_menu_end_date = CGPC::do_clean($_POST['global_menu_end_date'], TYPE_STR);
					$Menu_Edit->admin_notes = $Form->value('admin_notes');
					$Menu_Edit->menu_description = $Form->value('menu_description');
					$Menu_Edit->is_active = $Form->value('is_active');
					$Menu_Edit->update();

					CSession::generateWalkInSessionsForMenu($Menu_Edit->id, true);
					CSession::generateDeliveredSessionsForMenu($Menu_Edit->id);

					$this->Template->setStatusMsg('Your changes have been saved.');
				}
			}
			else
			{
				$this->Template->setErrorMsg('menu error');
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
				$this->Template->assign('sz_previous_menu_end', date('Y-m-d', $ts));
			}
			else
			{
				$this->Template->assign('sz_previous_menu_end', '** Warning: Prior month has no end date set **');
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
				$this->Template->assign('end_date', date('Y-m-d', mktime(0, 0, 0, $szMonth, $iDayCount, $szYear)));
			}
			else
			{
				// we already have a valid end date so use it...
				$this->Template->assign('end_date', $Menu_Edit->global_menu_end_date);
			}
			// ------------------------------------------------------
		}

		$formArray = $Form->render();
		$this->Template->assign('form_menus', $formArray);
	}
}

?>