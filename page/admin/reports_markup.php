<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');

 class page_admin_reports_markup extends CPageAdminOnly {

	private $currentStore = null;
	private $affectedOrdersFixed = array();
	private $affectedOrders = array();
	private $chosenStore = null;

	function __construct()
 	{
		parent::__construct();
 		$this->cleanReportInputs();
 	}

    function getAvgOverrideMU($store_id, $menu_id)
    {
        $item = new DAO();
        $item->query("select mmi.override_price, mi.price from menu_to_menu_item mmi
						join menu_item mi on mi.id = mmi.menu_item_id and mi.menu_item_category_id < 5 and mi.is_store_special = 0
						where mmi.store_id = $store_id and mmi.menu_id = $menu_id and mmi.is_deleted = 0 and not isnull(mmi.override_price) and mmi.override_price> 0");

        if ($item->N == 0) {
            return "-";
        }

        $OPSum = 0;
        $BaseSum = 0;
        while ($item->fetch())
        {
            $OPSum += $item->override_price;
            $BaseSum += $item->price;
        }

        $delta = $OPSum - $BaseSum;
        $percentage = CTemplate::divide_and_format($delta, $BaseSum, 4);

        return ($percentage * 100) . "%";
    }



 	function runSiteAdmin() {
		$store = NULL;

		CLog::RecordReport("Non Linked Report", $_SERVER['REQUEST_URI'] );
		$tpl = CApp::instance()->template();

 		$store = DAO_CFactory::create('store');

		//$store->id = 296;
		$store->active = 1;
	//	$store->is_corporate_owned = 1;
		$store->find();
		$rows = array();


        $menus = array();
        $months = array();
        $labels = array("Store");

        $lookback = 1;
        if (isset($_REQUEST['months_back']) and is_numeric($_REQUEST['months_back']) and $_REQUEST['months_back'] > 0 and $_REQUEST['months_back'] < 36)
        {
            $lookback = $_REQUEST['months_back'];
        }

        $curMenuID = CMenu::getCurrentMenuId();
        $curMenuID -= $lookback;



        while(true)
        {
            $menuCheck = new DAO();
            $menuCheck->query("select menu_start from menu where id = $curMenuID");
            if ($menuCheck->N == 0)
            {
                break;
            }
            $menuCheck->fetch();
            $menus[] = $curMenuID;
            $months[] = date("M Y", strtotime($menuCheck->menu_start));
            $curMenuID++;
        }

		$labels = array("Store");

		foreach($months as $thisMonth)
		{
			$labels = array_merge($labels, array( "$thisMonth from Default",  "$thisMonth Med MU" , "$thisMonth Lrg MU", "$thisMonth Sides MU", "$thisMonth # overriden / total", "$thisMonth Avg. Override Markup"));
		}


		while ($store->fetch())
		{

			$rows[$store->id] = array(
				'storeName' => $store->store_name . " : " . $store->state_id . " (" . $store->id . ")");

			foreach($menus as $menu_id)
			{

				$theMU = $store->getMarkUpMultiObj($menu_id);
				$store->clearMarkupMultiObj();

				$theDefault = "NO";

				if ($theMU && $theMU->menu_id_start != $menu_id && $theMU->is_default )
					$theDefault = $theMU->menu_id_start;


				$MMIObj = DAO_CFactory::create('menu_to_menu_item');
				$MMIObj->query("select count(mmi.id) as total, count(if(mmi.override_price >= 0, mmi.id, null)) as overridden  from menu_to_menu_item mmi
						join menu_item mi on mi.id = mmi.menu_item_id and mi.menu_item_category_id < 5 and mi.is_store_special = 0
						where mmi.store_id = {$store->id} and mmi.menu_id = $menu_id and mmi.is_deleted = 0");

				$MMIObj->fetch();


				if ($theMU)
				{
						//$rows[$store->id][] = ($theMU->menu_id_start == $menu_id ? "YES" : "NO");
						$rows[$store->id][] = $theDefault;
						$rows[$store->id][] = $theMU->markup_value_3_serving . "%";
						$rows[$store->id][] = $theMU->markup_value_6_serving . "%";
						$rows[$store->id][] = $theMU->markup_value_sides . "%";
						$rows[$store->id][] = $MMIObj->overridden . "/" . $MMIObj->total;
                        $rows[$store->id][] = $this->getAvgOverrideMU($store->id, $menu_id);

				}
				else
				{
						//$rows[$store->id][] = "-";
						$rows[$store->id][] = "-";
						$rows[$store->id][] = "-";
						$rows[$store->id][] = "-";
						$rows[$store->id][] = "-";
                        $rows[$store->id][] = "-";
                        $rows[$store->id][] = "-";
				}
			}
		}


		$tpl->assign('rows', $rows);
		$tpl->assign('rowcount', count($rows));
		$tpl->assign('labels', $labels);
		$_GET["export"] = 'xlsx';

	}

}



?>