<?php
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/Menu_item_inventory.php");
require_once("DAO/CFactory.php");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once("CLog.inc");
require_once("DAO/BusinessObject/CImportReciprofity.php");

class page_admin_import_sidesmap_reciprofity extends CPageAdminOnly
{
	static public $updateExistingMenu = false;

	function runSiteAdmin()
	{
		$this->importSidesMap();
	}

	function importSidesMap()
	{
		$tpl = CApp::instance()->template();

		if (!empty($_POST['menu']) && !empty($_FILES['base_menu_sidesmap']) && $_FILES['base_menu_sidesmap']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['base_menu_sidesmap']['tmp_name']))
		{
			// check if we're updating an existing menu
			$menuArray = self::getMenuOptionsArray();
			if (!empty($menuArray[$_POST['menu']]['data']['data-imported']) && $menuArray[$_POST['menu']]['data']['data-imported'] == 'true')
			{
				self::$updateExistingMenu = true;
			}

			set_time_limit(100000);
			$labels = array();

			try
			{
				$uberObject = DAO_CFActory::create('menu_item');
				$uberObject->query('START TRANSACTION;');

				$rows = CImportReciprofity::distillCSVImport($_FILES['base_menu_sidesmap'], $tpl);

				// check data for problems
				$sanityResult = CImportReciprofity::sanityCheck($rows, self::$updateExistingMenu);
				if ($sanityResult !== true)
				{
					throw new Exception($sanityResult);
				}

				$count = 0;

				// passed sanity checks, now go over the array again and insert the information
				foreach ($rows AS $row => $col)
				{
					if (strtolower($col[MENU_CLASS]) != 'app')
					{
						self::stickIt($rows[$row],CGPC::do_clean( $_POST['menu'],TYPE_INT), $tpl);

						$count++;
					}
				}

				$uberObject->query('COMMIT;');

				$tpl->setStatusMsg('<p>Sides Map imported.</p><p>Next Step: <a class="button" href="?page=admin_import_bundles_reciprofity">Import Bundles</a></p>');

				$commit = true;
			}
			catch (exception $e)
			{
				$commit = false;

				$uberObject->query('ROLLBACK;');
				$tpl->setErrorMsg('Instructions import failed: exception occurred</br>Reason: ' . $e->getMessage());
				CLog::RecordException($e);
			}
		}

		/* Import menu fadmin template stuff below */
		// get fresh menu array state
		$menuArray = self::getMenuOptionsArray();

		$Form = new CForm();

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "menu",
			CForm::options => $menuArray
		));

		$tpl->assign('form_menu', $Form->Render());
		$tpl->assign('menu_count', (count($menuArray)-1));
	}

	private static function getMenuOptionsArray()
	{
		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT
			IQ.id,
			IQ.menu_name
			FROM (SELECT
				menu.id,
				menu.menu_name,
				MAX(entree_to_side.side_menu_item_id) AS side_menu_item_id
				FROM menu
				INNER JOIN menu_to_menu_item ON menu_to_menu_item.menu_id = menu.id
				LEFT JOIN entree_to_side ON entree_to_side.entree_id = menu_to_menu_item.menu_item_id
				GROUP BY menu.id
				ORDER BY menu.id DESC
				LIMIT 10) AS IQ
			WHERE side_menu_item_id IS NULL
			ORDER BY IQ.id ASC");

		$menuArray = array(0 => 'Select Menu');
		$menu_count = 0;

		while ($Menu->fetch())
		{
			$menuArray[$Menu->id] = $Menu->menu_name;
			$menu_count++;
		}

		return $menuArray;
	}

	private static function stickIt($fields, $menu_id, $tpl)
	{
		try
		{
			if (!empty($fields[SUGGESTED_SIDE]) && is_numeric($fields[SUGGESTED_SIDE]) && $fields['pricing_type'] == 'FULL')
			{
				// find the entree
				$menuItem = DAO_CFactory::create('menu_item');
				$menuItem->query("select
						mi.id,
						mi.entree_id
						from menu_item mi
						join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and isnull(mmi.store_id) and mmi.menu_id = $menu_id
						where mi.recipe_id = {$fields['recipe_id']}
						and mi.is_deleted = 0");

				// Removing assumption of how many sizes would be found for 2/4/6
				if ($menuItem->N > 0/* && $menuItem->N < 3*/)
				{

					while ($menuItem->fetch())
					{
						$sideMenuItem = DAO_CFactory::create('menu_item');

						$sideMenuItem->query("select
								mi.id
								from menu_item mi
								join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and isnull(mmi.store_id) and mmi.menu_id = $menu_id
								where mi.recipe_id = {$fields[SUGGESTED_SIDE]}
								and mi.entree_id = mi.id
								and mi.is_deleted = 0");

						if ($sideMenuItem->N == 1)
						{
							$sideMenuItem->fetch();

							$MapEntry = DAO_CFactory::create('entree_to_side');
							$MapEntry->entree_id = $menuItem->entree_id;
							$MapEntry->entree_menu_item_id = $menuItem->id;
							$MapEntry->entree_recipe_id = $fields['recipe_id'];
							$MapEntry->side_menu_item_id = $sideMenuItem->id;

							$MapEntry->insert();
						}
						else
						{
							throw new Exception("Sides Menu_item mismatch:  {$fields['recipe_id']} to  {$fields[SUGGESTED_SIDE]} ");
						}
					}
				}
				else
				{
					throw new Exception("Menu_item mismatch:  {$fields['recipe_id']} to  {$fields[SUGGESTED_SIDE]} ");
				}
			}
		}
		catch (exception $e)
		{
			$tpl->setErrorMsg('Sides Map import failed: inner loop exception occurred<br />Reason: ' . $e->getMessage());
			CLog::RecordException($e);
			throw new Exception('rethrow from inner loop');
		}
	}
}

?>