<?php
require_once(dirname(__FILE__) . "/../../includes/Config.inc");
require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

ini_set('memory_limit', '768M');
set_time_limit(100000);

$months = array(
	264,
	265,
	266,
	267,
	268
);

try
{
	$fcount = 0;
	foreach ($months as $month)
	{
		gc_collect_cycles();
		$fcount++;

		$sheets = array();
		$line = '';
		$count = 0;

		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $month;
		$DAO_menu->find(true);

		$csv_path = REPORT_OUTPUT_BASE . "/sales_price_report/sales-price-report-sides-" . strtolower(str_replace(' ', '_', $DAO_menu->menu_name)) . "-as_of-" . date("Y-m-d") . ".xlsx";

		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->active = 1;
		$DAO_store->find();

		$rows = array();

		while ($DAO_store->fetch())
		{
			echo 'Processing ' . $DAO_store->store_name . "<br>\n";

			$arr = COrders::buildNewPricingMenuPlanArrays($DAO_store, $DAO_menu->id, 'FeaturedFirst', false, false);
			if (!array_key_exists('Chef Touched Selections', $arr))
			{
				continue;
			}
			foreach ($arr['Chef Touched Selections'] as $key => $coreItem)
			{
				$line = $DAO_menu->menu_name . ' (' . $DAO_menu->id . ')|';
				$line .= $DAO_store->store_name . ' (' . $DAO_store->id . ')|';
				$line .= $DAO_store->core_pricing_tier . '|';
				$line .= $DAO_store->city . '|';
				$line .= $DAO_store->state_id . '|';
				$line .= $coreItem['recipe_id'] . ($coreItem['pricing_type'] == 'HALF' ? '_M' : '_L') . '|';
				$line .= $coreItem['display_title'] . '|';
				$line .= $coreItem['base_price'] . '|';
				if (empty($coreItem['override_price']))
				{
					$line .= $coreItem['price'] . '|';
				}
				else
				{
					$line .= $coreItem['override_price'] . '|';
				}
				$line .= ($coreItem['pricing_type'] == 'HALF' ? 'Medium' : 'Large') . '|';
				$DAO_order_item = DAO_CFactory::create('order_item', true);
				$DAO_order_item->query("
					select oi.menu_item_id, sum(oi.item_count) as total_sold
					from order_item oi
					join orders o on o.id = oi.order_id and o.is_deleted = 0
					join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
					join session s on s.id = b.session_id and s.is_deleted = 0
					where oi.is_deleted = 0
					and oi.menu_item_id = {$coreItem['id']}
					and s.store_id = {$DAO_store->id}
					and s.menu_id = {$DAO_menu->id}
					group by oi.menu_item_id");
				$DAO_order_item->fetch();

				$line .= empty($DAO_order_item->total_sold) ? 0 : $DAO_order_item->total_sold;
				$DAO_order_item = null;

				$rows[$count++] = explode('|', $line);
				//echo $line.PHP_EOL;
				//echo memory_get_peak_usage() . PHP_EOL;
				$line = null;
			}
			$arr = null;
			//for testing
			//$rows[$count++]  = array($menu['name'],$storeObj->store_name,'Recipe Id', 'Recipe Name', 'Price');
		}
		$sheets[$DAO_menu->menu_name] = $rows;

		$rows = null;
		$header = array(
			'Menu',
			'Store',
			'Store Tier',
			'City',
			'State',
			'Recipe Id',
			'Recipe Name',
			'Base Price',
			'Price',
			'Size',
			'Sold'
		);
		$showHeader = true;

		$columnDescs = array();

		$columnDescs['A'] = array(
			'align' => 'left',
			'width' => 30
		);
		$columnDescs['B'] = array('align' => 'left');
		$columnDescs['C'] = array('align' => 'left');
		$columnDescs['D'] = array('align' => 'left');
		$columnDescs['E'] = array('align' => 'left');
		$columnDescs['F'] = array('align' => 'left');
		$columnDescs['G'] = array('align' => 'left');
		$columnDescs['H'] = array(
			'align' => 'center',
			'type' => 'currency'
		);
		$columnDescs['I'] = array(
			'align' => 'center',
			'type' => 'currency'
		);
		$columnDescs['J'] = array('align' => 'left');
		$columnDescs['K'] = array('align' => 'left');

		require_once('ExcelExport.inc');

		$fileName = "Sides Sales Price Report - Active Menus";
		$titleRows = false;
		$sectionHeader = false;
		$callbacks = false;
		$colDescriptions = $columnDescs;
		$suppressLabelsDisplay = false;
		$overrideValues = false;
		$useLib1_8 = true;
		$headersAreEmbedded = false;

		writeExcelFileMultiSheet($fileName, $header, $sheets, $showHeader, $titleRows, $colDescriptions, $headersAreEmbedded, $callbacks, $sectionHeader, false, $suppressLabelsDisplay, $overrideValues, $useLib1_8, $csv_path);
		$sheets = null;
	}
	echo "done<br>\n";
}
catch (exception $e)
{
	echo "pricing report failed: exception occurred<br>\n";
	echo "reason: " . $e->getMessage();
	CLog::RecordException($e);
}
?>