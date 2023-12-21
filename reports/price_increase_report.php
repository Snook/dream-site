<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");
require_once('ExcelExport.inc');

ini_set('memory_limit', '-1');
set_time_limit(3600 * 24);

function getLastPrice2019($store_id, $recipe_id)
{
	$getter = new DAO();
	$getter->query("select  mi.recipe_id, oi.sub_total, oi.timestamp_updated, oi.item_count, oi.discounted_subtotal
                            from booking b
                            join session s on b.session_id = s.id and s.store_id = $store_id and s.menu_id <= 220 and s.menu_id >= 209
                            join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = $recipe_id and mi.pricing_type = 'FULL'
                            where b.status = 'ACTIVE' and b.is_deleted = 0
                            order by oi.timestamp_updated desc limit 1");
	$getter->fetch();

	$half_getter = new DAO();
	$half_getter->query("select  mi.recipe_id, oi.sub_total, oi.timestamp_updated, oi.item_count, oi.discounted_subtotal
                            from booking b
                            join session s on b.session_id = s.id and s.store_id = $store_id and s.menu_id <= 220 and s.menu_id >= 209
                            join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = $recipe_id and mi.pricing_type = 'HALF'
                            where b.status = 'ACTIVE' and b.is_deleted = 0
                            order by oi.timestamp_updated desc limit 1");

	$half_version = null;
	if ($half_getter->N > 0)
	{
		$half_getter->fetch();
		$half_version = $half_getter->sub_total / $half_getter->item_count;
	}

	$full_version = 0;
	if (!empty($getter->item_count))
	{
		$full_version = $getter->sub_total / $getter->item_count;
	}

	return array(
		$full_version,
		$half_version
	);
}

function getLastPrice($store_id, $recipe_id)
{
	$getter = new DAO();
	$getter->query("select  mi.recipe_id, oi.sub_total, oi.timestamp_updated, oi.item_count, oi.discounted_subtotal
                            from booking b
                            join session s on b.session_id = s.id and s.store_id = $store_id and s.menu_id <= 208 and s.menu_id >= 197
                            join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = $recipe_id and mi.pricing_type = 'FULL'
                            where b.status = 'ACTIVE' and b.is_deleted = 0
                            order by oi.timestamp_updated desc limit 1");
	$getter->fetch();

	$half_getter = new DAO();
	$half_getter->query("select  mi.recipe_id, oi.sub_total, oi.timestamp_updated, oi.item_count, oi.discounted_subtotal
                            from booking b
                            join session s on b.session_id = s.id and s.store_id = $store_id and s.menu_id <= 208 and s.menu_id >= 197
                            join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = $recipe_id and mi.pricing_type = 'HALF'
                            where b.status = 'ACTIVE' and b.is_deleted = 0
                            order by oi.timestamp_updated desc limit 1");

	$half_version = null;
	if ($half_getter->N > 0)
	{
		$half_getter->fetch();
		$half_version = $half_getter->sub_total / $half_getter->item_count;
	}

	$full_version = 0;
	if (!empty($getter->item_count))
	{
		$full_version = $getter->sub_total / $getter->item_count;
	}

	return array(
		$full_version,
		$half_version
	);
}

function getRevenue($prices2018, $store, $menu)
{

	// $fht = fopen("C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace\\DreamSite\\Recent_Scripts\\debug_orem.csv", 'w');

	$total_at_2018 = 0;
	$total_at_2019 = 0;
	$base_total_at_2019 = 0;

	$getter = new DAO();

	/*
	$getter->query("select distinct mi.recipe_id, mi.pricing_type, SUM(oi.item_count) as count, SUM(oi.sub_total) as cost from booking b
								join session s on b.session_id = s.id and s.store_id = $store and s.menu_id = $menu and s.session_type in ('STANDARD', 'SPECIAL_EVENT')
								join orders o on o.id = b.order_id and o.type_of_order <> 'INTRO'
								join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
								join menu_item mi on mi.id = oi.menu_item_id
								where b.status = 'ACTIVE' and b.is_deleted = 0
								group by mi.recipe_id, mi.pricing_type");
*/

	$getter->query("select distinct mi.recipe_id, mi.pricing_type, SUM(oi.item_count) as count, SUM(oi.sub_total) as cost, SUM(oi.pre_mark_up_sub_total) as base_price  from booking b
                                join session s on b.session_id = s.id and s.store_id = $store and s.menu_id = $menu and s.session_type in ('STANDARD', 'SPECIAL_EVENT')
                                join orders o on o.id = b.order_id and o.type_of_order <> 'INTRO'
                                join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                                join menu_item mi on mi.id = oi.menu_item_id and isnull(mi.copied_from) and mi.menu_item_category_id < 9 
                                where b.status = 'ACTIVE' and b.is_deleted = 0
                                group by mi.recipe_id, mi.pricing_type");

	while ($getter->fetch())
	{
		$recipeID = $getter->recipe_id;
		$revenue_2018 = 0;
		$revenue_2019 = 0;
		$baseRevenue2019 = 0;

		//   $thisRow = array($recipeID, $getter->pricing_type, $getter->count, $getter->cost, $getter->cost / $getter->count);

		if (!empty($prices2018[$recipeID][$getter->pricing_type]))
		{
			$revenue_2018 = $getter->count * $prices2018[$recipeID][$getter->pricing_type];
			//$thisRow[] = $prices2018[$recipeID][$getter->pricing_type];
		}
		else
		{
			$revenue_2018 = $getter->cost * .98;
			//$thisRow[] = "n/a";
		}

		$revenue_2019 = $getter->cost;
		$baseRevenue2019 = $getter->base_price;

		$total_at_2018 += $revenue_2018;
		$total_at_2019 += $revenue_2019;
		$base_total_at_2019 += $baseRevenue2019;
		//fputs($fht, implode(",", $thisRow) . "\r\n");

	}

	//   fclose($fht);

	return array(
		$total_at_2018,
		$total_at_2019,
		$base_total_at_2019
	);
}

$storeArray = array();

try
{
	$path = "/DreamReports/prices_comp7.csv";
	//$path = "C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace\\DreamSite\\Recent_Scripts\\prices.csv";
	$fh = fopen($path, 'w');

	$stores = new DAO();

	$stores->query("select id, home_office_id, store_name, city, state_id, is_corporate_owned from store where (last_menu_supported > 208 or isnull(last_menu_supported)) and is_deleted = 0 order by id asc");
	$labels = array(
		"ID",
		"Home Office Id",
		"Store Name",
		"City",
		"State",
	);

	$recipe2018Prices = array();
	$recipe2019Prices = array();

	$FIRST = true;
	$output = array();

	while ($stores->fetch())
	{
		echo "Starting store " . $stores->id . "\r\n";
		$storeArray[$stores->id] = DAO::getCompressedArrayFromDAO($stores);

		$recipe2018Prices[$stores->id] = array();

		$output[$stores->id] = array(
			$stores->id,
			$stores->home_office_id,
			$stores->store_name,
			$stores->city,
			$stores->state_id
		);

		$recipesSold = new DAO();
		$recipesSold->query(" select distinct mi.recipe_id from booking b
                                join session s on b.session_id = s.id and s.store_id = {$stores->id} and s.menu_id <= 208 and s.menu_id >= 197
                                join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
                                join menu_item mi on mi.id = oi.menu_item_id 
                                where b.status = 'ACTIVE' and b.is_deleted = 0");

		while ($recipesSold->fetch())
		{
			list($full_price, $half_price) = getLastPrice($stores->id, $recipesSold->recipe_id);
			list($full_price2019, $half_price2019) = getLastPrice2019($stores->id, $recipesSold->recipe_id);

			$recipe2018Prices[$stores->id][$recipesSold->recipe_id] = array(
				'FULL' => $full_price,
				'HALF' => $half_price
			);
			$recipe2019Prices[$stores->id][$recipesSold->recipe_id] = array(
				'FULL' => $full_price2019,
				'HALF' => $half_price2019
			);
		}

		echo "prices retrieved for store " . $stores->id . "\r\n";

		for ($menu_id = 209; $menu_id < 221; $menu_id++)
		{

			if ($FIRST)
			{
				$menuInfo = CMenu::getMenuInfo($menu_id);
				$labels[] = $menuInfo['menu_start'] . "2018 Prices";
				$labels[] = $menuInfo['menu_start'] . "2019 Prices";
			}

			list($rev2018, $rev2019, $baseRev) = getRevenue($recipe2018Prices[$stores->id], $stores->id, $menu_id);
			$output[$stores->id][] = $rev2018;
			$output[$stores->id][] = $rev2019;
			$output[$stores->id][] = $baseRev;
		}

		$FIRST = false;
	}

	$length = fputs($fh, implode(",", $labels) . "\r\n");

	foreach ($output as $store => $data)
	{
		$length = fputs($fh, implode(",", $data) . "\r\n");
	}

	//   print_r($recipe2018Prices);
	//   print_r($recipe2019Prices);

	fclose($fh);
	echo "all done\r\n";
}
catch (exception $e)
{
	CLog::RecordException($e);
}

?>