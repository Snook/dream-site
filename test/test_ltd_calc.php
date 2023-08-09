<?php
/*
 * Created on Jan 16, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("../includes/Config.inc");
require_once("CLog.inc");
require_once("CAppUtil.inc");

// -------------------------------------------------------------------------------------------------------
	// has ltd but store does  support ltd - editing true
$menu_item_ids = array(14493, 14495, 14497, 14499, 14501, 14503);
$store_id = 274;
$menu_id = 188;
$editing = true;

try {

	$menuItemInfo = DAO_CFactory::create('menu_item');
	$OrderObj = new COrders();

	$query = "SELECT
					mmi.override_price AS override_price,
					mimd.id as markdown_id,
					mimd.markdown_value,
					r.ltd_menu_item_value,
					mi.*
					FROM menu_item  mi
					left join menu_item_mark_down mimd on mi.id = mimd.menu_item_id and mimd.store_id = {$store_id} and mimd.is_deleted = 0
					LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = {$store_id} AND mmi.menu_id = $menu_id  AND mmi.is_deleted = 0
					INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = $menu_id AND r.is_deleted = 0
					WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ")
									AND mi.is_deleted = 0";

	$menuItemInfo->query($query);
	while ($menuItemInfo->fetch())
	{
		$OrderObj->addMenuItem(clone($menuItemInfo), 1);
	}

	$doaStore =  DAO_CFactory::create('store');
	$doaStore->id = $store_id;
	$doaStore->find(true);

	$OrderObj->addStore($doaStore);
	$result = $OrderObj->applyLTDMenuItemValue($editing);

	echo "Store not in program, editing true, item not added in program Result = " . $result . "\r\n";

	// -------------------------------------------------------------------------------------------------------
	// has ltd but store does  support ltd - editing true
	$menu_item_ids = array(14493, 14495, 14497, 14499, 14501, 14503);
	$store_id = 244;
	$menu_id = 188;

	$menuItemInfo = DAO_CFactory::create('menu_item');
	$OrderObj = new COrders();

	$query = "SELECT
	mmi.override_price AS override_price,
	mimd.id as markdown_id,
	mimd.markdown_value,
	r.ltd_menu_item_value,
	mi.*
	FROM menu_item  mi
	left join menu_item_mark_down mimd on mi.id = mimd.menu_item_id and mimd.store_id = {$store_id} and mimd.is_deleted = 0
	LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = {$store_id} AND mmi.menu_id = $menu_id  AND mmi.is_deleted = 0
	INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = $menu_id AND r.is_deleted = 0
	WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ")
								AND mi.is_deleted = 0";


	$menuItemInfo->query($query);
	while ($menuItemInfo->fetch())
	{
		$OrderObj->addMenuItem(clone($menuItemInfo), 1);
	}

	$doaStore =  DAO_CFactory::create('store');
	$doaStore->id = $store_id;
	$doaStore->find(true);

	$OrderObj->addStore($doaStore);
	$result = $OrderObj->applyLTDMenuItemValue($editing);

	echo "Store IS in program, editing true, item not added in program  Result = " . $result . "\r\n";

	// -------------------------------------------------------------------------------------------------------
	// has ltd but store does  support ltd - editing true
	$menu_item_ids = array(14493, 14495, 14497, 14499, 14501, 14503);
	$store_id = 274;
	$menu_id = 188;

	$menuItemInfo = DAO_CFactory::create('menu_item');
		$OrderObj = new COrders();

	$query = "SELECT
		mmi.override_price AS override_price,
		mimd.id as markdown_id,
		mimd.markdown_value,
		r.ltd_menu_item_value,
		mi.*
		FROM menu_item  mi
		left join menu_item_mark_down mimd on mi.id = mimd.menu_item_id and mimd.store_id = {$store_id} and mimd.is_deleted = 0
		LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = {$store_id} AND mmi.menu_id = $menu_id  AND mmi.is_deleted = 0
		INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = $menu_id AND r.is_deleted = 0
		WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ")
									AND mi.is_deleted = 0";

	$menuItemInfo->query($query);
		while ($menuItemInfo->fetch())
		{
			if ($menuItemInfo->id == 14493)
			{
				$menuItemInfo->order_item_ltd_menu_item = true;
			}

			$OrderObj->addMenuItem(clone($menuItemInfo), 1);
		}

	$doaStore =  DAO_CFactory::create('store');
		$doaStore->id = $store_id;
		$doaStore->find(true);

	$OrderObj->addStore($doaStore);
		$result = $OrderObj->applyLTDMenuItemValue($editing);

	echo "Store not in program, editing false, item added in program  Result = " . $result . "\r\n";

	// -------------------------------------------------------------------------------------------------------
		// has ltd but store does  support ltd - editing true
		$menu_item_ids = array(14493, 14495, 14497, 14499, 14501, 14503);
		$store_id = 244;
		$menu_id = 188;

	$menuItemInfo = DAO_CFactory::create('menu_item');
		$OrderObj = new COrders();

	$query = "SELECT
		mmi.override_price AS override_price,
		mimd.id as markdown_id,
		mimd.markdown_value,
		r.ltd_menu_item_value,
		mi.*
		FROM menu_item  mi
		left join menu_item_mark_down mimd on mi.id = mimd.menu_item_id and mimd.store_id = {$store_id} and mimd.is_deleted = 0
		LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = {$store_id} AND mmi.menu_id = $menu_id  AND mmi.is_deleted = 0
		INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = $menu_id AND r.is_deleted = 0
		WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ")
								AND mi.is_deleted = 0";

	$menuItemInfo->query($query);
		while ($menuItemInfo->fetch())
		{

			if ($menuItemInfo->id == 14493)
			{
				$menuItemInfo->order_item_ltd_menu_item = true;
			}

			$OrderObj->addMenuItem(clone($menuItemInfo), 1);
		}

	$doaStore =  DAO_CFactory::create('store');
		$doaStore->id = $store_id;
		$doaStore->find(true);

	$OrderObj->addStore($doaStore);
		$result = $OrderObj->applyLTDMenuItemValue($editing);

	echo "Store IS in program, editing false, item added in program  Result = " . $result . "\r\n";

	// -------------------------------------------------------------------------------------------------------
		// has ltd but store does  support ltd - editing true
		$menu_item_ids = array(14493, 14495, 14497, 14499, 14501, 14503);
		$store_id = 244;
		$menu_id = 188;

	$menuItemInfo = DAO_CFactory::create('menu_item');
		$OrderObj = new COrders();

	$query = "SELECT
		mmi.override_price AS override_price,
		mimd.id as markdown_id,
		mimd.markdown_value,
		r.ltd_menu_item_value,
		mi.*
		FROM menu_item  mi
		left join menu_item_mark_down mimd on mi.id = mimd.menu_item_id and mimd.store_id = {$store_id} and mimd.is_deleted = 0
		LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = {$store_id} AND mmi.menu_id = $menu_id  AND mmi.is_deleted = 0
		INNER JOIN recipe AS r ON r.recipe_id = mi.recipe_id AND r.override_menu_id = $menu_id AND r.is_deleted = 0
		WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ")
								AND mi.is_deleted = 0";

	$menuItemInfo->query($query);
		while ($menuItemInfo->fetch())
		{
			$OrderObj->addMenuItem(clone($menuItemInfo), 1);
		}

	$doaStore =  DAO_CFactory::create('store');
		$doaStore->id = $store_id;
		$doaStore->find(true);

	$OrderObj->subtotal_ltd_menu_item_value = 1.0;
		$OrderObj->addStore($doaStore);
		$result = $OrderObj->applyLTDMenuItemValue($editing);

	echo "Store IS in program, editing false, item added in program  Result = " . $result . "\r\n";
} catch (exception $e) {
    echo $e->getMessage();
}






?>