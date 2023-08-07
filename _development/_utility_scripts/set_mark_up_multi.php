<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("../../includes/Config.inc");

require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/BusinessObject/COrdersDigest.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once('processor/admin/order_mgr_processor.php');

set_time_limit(100000);
ini_set('memory_limit', '-1');

try
{
	$DAO_menu = DAO_CFactory::create('menu');
	$DAO_menu->id = 265;
	$DAO_menu->find(true);

	$DAO_store = DAO_CFactory::create('store');
	$DAO_store->active = 1;
	$DAO_store->store_type = CStore::FRANCHISE;
	$DAO_store->find();

	while ($DAO_store->fetch())
	{
		$DAO_store->clearMarkupMultiObj();
		$DAO_mark_up_multi = $DAO_store->getMarkUpMultiObj($DAO_menu->id);

		if ($DAO_mark_up_multi)
		{
			$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
			$oldDefaultMarkups->store_id = $DAO_store->id;
			$oldDefaultMarkups->is_default = 1;
			if ($oldDefaultMarkups->find())
			{
				while ($oldDefaultMarkups->fetch())
				{
					$oldDefaultMarkups_org = $oldDefaultMarkups->cloneObj();
					$oldDefaultMarkups->is_default = 0;
					$oldDefaultMarkups->update($oldDefaultMarkups_org);
				}
			}

			$oldDefaultMarkups = DAO_CFactory::create('mark_up_multi');
			$oldDefaultMarkups->store_id = $DAO_store->id;
			$oldDefaultMarkups->menu_id_start = $DAO_menu->id;
			if ($oldDefaultMarkups->find())
			{
				while ($oldDefaultMarkups->fetch())
				{
					$oldDefaultMarkups->delete();
				}
			}

			$DAO_store->setMarkUpMulti(0, 0, 0, 0, $DAO_mark_up_multi->markup_value_sides, 30, $DAO_menu->id, 1, 12.5, $DAO_mark_up_multi->assembly_fee, $DAO_mark_up_multi->delivery_assembly_fee);
		}
	}
}
catch (exception $e)
{
	echo "Error occurred during processing<br>\n";
	echo "reason: " . $e->getMessage();
	CLog::RecordException($e);
}
?>