<?php
/*
 * Created on Jan 16, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

define('dev', false);


if (dev)
{
	require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
}
else
{
	require_once("/DreamSite/includes/Config.inc");
}

require_once("CLog.inc");
require_once("CAppUtil.inc");

function sort_by_pricey($a, $b)
{

    if (COrders::isPriceEqualTo($a->store_price, $b->store_price))
    {
        return 0;
    }
    return (COrders::isPriceLessThan($a->store_price, $b->store_price)) ? 1 : -1;
}


function getEntreePointsDiscountableAmount($order_id)
{

    $items = array();
    $nonDiscountableAmount = 0;
    $servingsCount = 0;

    $ItemsObj = DAO_CFactory::create('order_item');

    $ItemsObj->query("select oi.*, mi.servings_per_item, o.subtotal_home_store_markup, o.subtotal_menu_items from order_item oi
        join menu_item mi on mi.id = oi.menu_item_id and mi.menu_item_category_id < 5
        join orders o on o.id = $order_id
        where oi.order_id = $order_id and oi.is_deleted = 0");


    while ($ItemsObj->fetch())
    {
        $items[$ItemsObj->menu_item_id] = clone($ItemsObj);
        $items[$ItemsObj->menu_item_id]->store_price =  $items[$ItemsObj->menu_item_id]->sub_total /  $items[$ItemsObj->menu_item_id]->item_count;

    }

    uasort($items, 'sort_by_pricey');

    foreach ($items as $item)
    {

        $qty = $item->item_count;

        $servingThisItem = $qty * $item->servings_per_item;
        $floatVal = ((float)$item->store_price);
        $integerPrice = $floatVal * 100.0;
        $costThisItem = $qty * $integerPrice;
        $servingsCount += $servingThisItem;

        if ($servingsCount == 36)
        {
            $nonDiscountableAmount += $costThisItem;
            break;
        }
        else if ($servingsCount > 36)
        {
            $orgAmount = $servingsCount - $servingThisItem;
            $remainingServings = 36 - $orgAmount;
            $remainingItems = $remainingServings / $item->servings_per_item;

            $floatVal2 = ((float)$item->store_price);
            $integerPrice2 = $floatVal2 * 100.0;
            $nonDiscountableAmount += ($remainingItems * $integerPrice2);
            break;
        }
        else
        {
            $nonDiscountableAmount += $costThisItem;
        }
    }

    $costBasis = COrders::std_round(($ItemsObj->subtotal_menu_items + $ItemsObj->subtotal_home_store_markup) * 100);
    //$deduction = (int)(self::std_round($nonDiscountableAmount) * 100);

    $retVal = ($costBasis - $nonDiscountableAmount) / 100;

    return $retVal;
}



$dateSpans = array('2021-01-01', '2021-02-01', '2021-03-01', '2021-04-01','2021-05-01', '2021-06-01','2021-07-01', '2021-08-01',
    '2021-09-01', '2021-10-01','2021-11-01', '2021-12-01');


//$dateSpans = array('2016-04-01', '2016-05-01');

$retVal = array("Month" => array(),
                "Dinner Dollars Awarded" => array(),
                "Dinner Dollars Awarded (alt)" => array(),
                "Dinner Dollars Consumed" => array(),
                "Dinner Dollars Expired" => array(),
                "DD consumed by FT" => array(),
                "DD consumed by Entrees" => array(),
                "DD consumed by Service Fee" => array(),
                "Ambiguous DDs" => array());


try {

	if (dev)
	{
		$path = "C:\\Development\\Sites\\\\DreamSite\\dinner_dollar_consumed_2021.csv";
	}
	else
	{
		$path = "/DreamSite/dinner_dollar_consumed_2021.csv";
	}

	$dest_fp = fopen($path, 'w');
	if ($dest_fp === false)
	{
		echo "Order digestion script: fopen failed<br>\n";
		exit;
	}

	if (dev)
	{
		$path = "C:\\Development\\Sites\\\\DreamSite\\dinner_dollar_earned_2021.csv";
	}
	else
	{
		$path = "/DreamSite/dinner_dollar_earned_2021.csv";
	}

	$dest_fp_earned = fopen($path, 'w');
	if ($dest_fp_earned === false)
	{
		echo "Order digestion script: fopen failed<br>\n";
		exit;
	}

	if (dev)
	{
		$path = "C:\\Development\\Sites\\\\DreamSite\\dinner_dollar_expired_2021.csv";
	}
	else
	{
		$path = "/DreamSite/dinner_dollar_expired_2021.csv";
	}

	$dest_fp_expired = fopen($path, 'w');
	if ($dest_fp_expired === false)
	{
		echo "Order digestion script: fopen failed<br>\n";
		exit;
	}

	if (dev)
	{
		$path = "C:\\Development\\Sites\\\\DreamSite\\dinner_dollar_balance_2021.csv";
	}
	else
	{
		$path = "/DreamSite/dinner_dollar_balance_2021.csv";
	}

	$dest_fp_bal = fopen($path, 'w');
	if ($dest_fp_bal === false)
	{
		echo "Order digestion script: fopen failed<br>\n";
		exit;
	}

	if (dev)
	{
		$path = "C:\\Development\\Sites\\\\DreamSite\\dinner_dollar_on_hold_balance_2021.csv";
	}
	else
	{
		$path = "/DreamSite/dinner_dollar_on_hold_balance_2021.csv";
	}

	$dest_fp_bal_on_hold = fopen($path, 'w');
	if ($dest_fp_bal_on_hold === false)
	{
		echo "Order digestion script: fopen failed<br>\n";
		exit;
	}


	$debugMonth = 5;
    $debugYear = 2021;

	$labels = array("Store Name", "City", "State");
	foreach ($dateSpans as $monthYear)
	{
		$labels[] = $monthYear;

	}


		// ----------------------------- CONSUMED
	echo "Doing CONSUMED ---------------------------------\r\n";

	$massiveArray = array();
	$storeArray = array();
	$stores = new DAO();
	$stores->query('select id, store_name, city, state_id from store where active = 1');
	while($stores->fetch())
	{
		echo "Doing " . $stores->store_name . "\r\n";



		foreach ($dateSpans as $monthYear)
		{
			$storeArray[$stores->id] = $stores->store_name . "," . $stores->city. "," . $stores->state_id . ",";
			$parts = explode("-", $monthYear);
			$month = $parts[1];
			$year = $parts[0];


			$menu = CMenu::getMenuByAnchorDate($monthYear);
			$menu_id = $menu['id'];
			$DDsOUT = 0;

			$dd_out = new DAO();
			$dd_out->query("select sum(o.points_discount_total) as this_amount from orders o
							join booking b on b.order_id = o.id and b.status = 'ACTIVE'
							join session s on  s.id = b.session_id and s.menu_id = $menu_id and s.store_id = {$stores->id}
							where o.is_deleted = 0 and o.points_discount_total > 0");

			$dd_out->fetch();
			if (empty($dd_out->this_amount))
			{
				$DDsOUT = 0;
			}
			else
			{
				$DDsOUT = $dd_out->this_amount;
			}

			if (!isset($massiveArray[$stores->id]))
			{
				$massiveArray[$stores->id] = array();
			}

			if (!isset($massiveArray[$stores->id][$monthYear]))
			{
				$massiveArray[$stores->id][$monthYear] = array();
			}

			$massiveArray[$stores->id][$monthYear] =$DDsOUT;
		}
	}

	$length = fputs($dest_fp,  implode(",", $labels) . "\r\n");
	foreach($storeArray as $id => $storeData)
	{
		$outStr = $storeData;
		$outStr .= implode(",",$massiveArray[$id]);
		$length = fputs($dest_fp,  $outStr . "\r\n");

	}
	fclose($dest_fp);

 // ----------------------------- EARNED
	echo "Doing EARNED ---------------------------------\r\n";

	$massiveArray = array();
	$storeArray = array();
	$stores = new DAO();
	$stores->query('select id, store_name, city, state_id from store where active = 1');
	while($stores->fetch())
	{
		echo "Doing " . $stores->store_name . "\r\n";



		foreach ($dateSpans as $monthYear)
		{
			$storeArray[$stores->id] = $stores->store_name . "," . $stores->city. "," . $stores->state_id . ",";
			$parts = explode("-", $monthYear);
			$month = $parts[1];
			$year = $parts[0];


			$menu = CMenu::getMenuByAnchorDate($monthYear);
			$menu_id = $menu['id'];
			$DDsOUT = 0;

			$dd_out = new DAO();
			$dd_out->query("select sum(if(isnull(pc.original_amount), pc.dollar_value, pc.original_amount)) as this_amount 
							from points_credits pc
							join user u on u.id= pc.user_id and u.home_store_id = {$stores->id}
							where YEAR(pc.timestamp_created) = $year and MONTH(pc.timestamp_created) = $month and pc.is_deleted = 0 and ISNULL(pc.parent_of_partial)
							group by u.home_store_id");

			$dd_out->fetch();

			if (empty($dd_out->this_amount))
			{
				$DDsOUT = 0;
			}
			else
			{
				$DDsOUT = $dd_out->this_amount;
			}

			if (!isset($massiveArray[$stores->id]))
			{
				$massiveArray[$stores->id] = array();
			}

			if (!isset($massiveArray[$stores->id][$monthYear]))
			{
				$massiveArray[$stores->id][$monthYear] = array();
			}

			$massiveArray[$stores->id][$monthYear] =$DDsOUT;
		}
	}

	$length = fputs($dest_fp_earned,  implode(",", $labels) . "\r\n");
	foreach($storeArray as $id => $storeData)
	{
		$outStr = $storeData;
		$outStr .= implode(",",$massiveArray[$id]);
		$length = fputs($dest_fp_earned,  $outStr . "\r\n");

	}
	fclose($dest_fp_earned);

	// ----------------------------- EXPIRED
	echo "Doing EXPIRED ---------------------------------\r\n";

	$massiveArray = array();
	$storeArray = array();
	$stores = new DAO();
	$stores->query('select id, store_name, city, state_id from store where active = 1');
	while($stores->fetch())
	{
		echo "Doing " . $stores->store_name . "\r\n";



		foreach ($dateSpans as $monthYear)
		{
			$storeArray[$stores->id] = $stores->store_name . "," . $stores->city. "," . $stores->state_id . ",";
			$parts = explode("-", $monthYear);
			$month = $parts[1];
			$year = $parts[0];


			$menu = CMenu::getMenuByAnchorDate($monthYear);
			$menu_id = $menu['id'];
			$DDsOUT = 0;

			$dd_out = new DAO();
			$dd_out->query("select sum(pc.dollar_value) as this_amount
							from points_credits pc
							join user u on u.id = pc.user_id and u.home_store_id = {$stores->id}
							where YEAR(pc.timestamp_updated) = $year and MONTH(pc.timestamp_updated) = $month and pc.is_deleted = 0 and pc.credit_state = 'EXPIRED'");

			$dd_out->fetch();
			if (empty($dd_out->this_amount))
			{
				$DDsOUT = 0;
			}
			else
			{
				$DDsOUT = $dd_out->this_amount;
			}

			if (!isset($massiveArray[$stores->id]))
			{
				$massiveArray[$stores->id] = array();
			}

			if (!isset($massiveArray[$stores->id][$monthYear]))
			{
				$massiveArray[$stores->id][$monthYear] = array();
			}

			$massiveArray[$stores->id][$monthYear] =$DDsOUT;
		}
	}

	$length = fputs($dest_fp_expired,  implode(",", $labels) . "\r\n");
	foreach($storeArray as $id => $storeData)
	{
		$outStr = $storeData;
		$outStr .= implode(",",$massiveArray[$id]);
		$length = fputs($dest_fp_expired,  $outStr . "\r\n");

	}
	fclose($dest_fp_expired);

	// ---------------------------- BALANCE
	echo "Doing BALANCE ---------------------------------\r\n";

	$massiveArray = array();
	$storeArray = array();
	$stores = new DAO();
	$stores->query('select id, store_name, city, state_id from store where active = 1');
	while($stores->fetch())
	{
		echo "Doing " . $stores->store_name . "\r\n";



		foreach ($dateSpans as $monthYear)
		{
			$storeArray[$stores->id] = $stores->store_name . "," . $stores->city. "," . $stores->state_id . ",";
			$parts = explode("-", $monthYear);
			$month = $parts[1];
			$year = $parts[0];


			$menu = CMenu::getMenuByAnchorDate($monthYear);
			$menu_id = $menu['id'];
			$DDsOUT = 0;

			$dd_out = new DAO();
			$dd_out->query("select sum(pc.dollar_value) as this_amount
							from points_credits pc
							join user u on u.id = pc.user_id and u.home_store_id = {$stores->id}
							where pc.is_deleted = 0 and pc.credit_state = 'AVAILABLE' and pc.timestamp_created < '2022-01-01 00:00:00'");

			$dd_out->fetch();
			if (empty($dd_out->this_amount))
			{
				$DDsOUT = 0;
			}
			else
			{
				$DDsOUT = $dd_out->this_amount;
			}

			if (!isset($massiveArray[$stores->id]))
			{
				$massiveArray[$stores->id] = array();
			}

			if (!isset($massiveArray[$stores->id][$monthYear]))
			{
				$massiveArray[$stores->id][$monthYear] = array();
			}

			$massiveArray[$stores->id][$monthYear] =$DDsOUT;
		}
	}

	$length = fputs($dest_fp_bal,  implode(",", $labels) . "\r\n");
	foreach($storeArray as $id => $storeData)
	{
		$outStr = $storeData;
		$outStr .= implode(",",$massiveArray[$id]);
		$length = fputs($dest_fp_bal,  $outStr . "\r\n");

	}
	fclose($dest_fp_bal);



	// ---------------------------- BALANCE ON HOLD
	echo "Doing BALANCE ON HOLD---------------------------------\r\n";

	$massiveArray = array();
	$storeArray = array();
	$stores = new DAO();
	$stores->query('select id, store_name, city, state_id from store where active = 1');
	while($stores->fetch())
	{
		echo "Doing " . $stores->store_name . "\r\n";



		foreach ($dateSpans as $monthYear)
		{
			$storeArray[$stores->id] = $stores->store_name . "," . $stores->city. "," . $stores->state_id . ",";
			$parts = explode("-", $monthYear);
			$month = $parts[1];
			$year = $parts[0];


			$menu = CMenu::getMenuByAnchorDate($monthYear);
			$menu_id = $menu['id'];
			$DDsOUT = 0;

			$dd_out = new DAO();
			$dd_out->query("select sum(pc.dollar_value) as this_amount
							from points_credits pc
							join user u on u.id = pc.user_id and u.home_store_id = {$stores->id} and u.dream_reward_status = 5
							where pc.is_deleted = 0 and pc.credit_state = 'AVAILABLE'");

			$dd_out->fetch();
			if (empty($dd_out->this_amount))
			{
				$DDsOUT = 0;
			}
			else
			{
				$DDsOUT = $dd_out->this_amount;
			}

			if (!isset($massiveArray[$stores->id]))
			{
				$massiveArray[$stores->id] = array();
			}

			if (!isset($massiveArray[$stores->id][$monthYear]))
			{
				$massiveArray[$stores->id][$monthYear] = array();
			}

			$massiveArray[$stores->id][$monthYear] =$DDsOUT;
		}
	}

	$length = fputs($dest_fp_bal_on_hold,  implode(",", $labels) . "\r\n");
	foreach($storeArray as $id => $storeData)
	{
		$outStr = $storeData;
		$outStr .= implode(",",$massiveArray[$id]);
		$length = fputs($dest_fp_bal_on_hold,  $outStr . "\r\n");

	}
	fclose($dest_fp_bal_on_hold);


	exit;


/*




	{

        $dd_in = DAO_CFactory::create('orders');
        $dd_in->query("select sum(if(isnull(pc.original_amount), pc.dollar_value, pc.original_amount)) as this_amount from points_credits pc
                         where YEAR(pc.timestamp_created) = $year and MONTH(pc.timestamp_created) = $month and pc.is_deleted = 0 and ISNULL(pc.parent_of_partial)");

        $dd_in->fetch();

        $DDsIN = $dd_in->this_amount;


        $dd_in2 = DAO_CFactory::create('orders');
        $dd_in2->query("select 
                    SUM(SUBSTR(json_meta, -3, 2)) as juice from points_user_history puh
                    where YEAR(puh.timestamp_created) = $year and MONTH(puh.timestamp_created) = $month and puh.is_deleted = 0 and puh.event_type = 'REWARD_CREDIT'");

        $dd_in2->fetch();

        $DDsIN2 = $dd_in2->juice;

        $dd_exp = DAO_CFactory::create('orders');
        $dd_exp->query("select
                    sum(pc.dollar_value) as this_amount
                    from points_credits pc
                    where YEAR(pc.timestamp_updated) = $year and MONTH(pc.timestamp_updated) = $month and pc.is_deleted = 0 and pc.credit_state = 'EXPIRED'");

        $dd_exp->fetch();

        $DDsEXP = $dd_exp->this_amount;




         //BREAKDOWN




        $dd_study = DAO_CFactory::create('orders');
        $dd_study->query("select o.* from orders o
        join booking b on b.order_id = o.id and b.status = 'ACTIVE'
        join session s on  s.id = b.session_id and YEAR(s.session_start) = $year and MONTH(s.session_start) = $month
        where o.is_deleted = 0 and o.points_discount_total > 0");

        $Total_FT_DDs = 0;
        $Total_Entree_DDs = 0;
        $Total_Svc_Fee_DDs = 0;
        $Total_Ambiguous_DDs = 0;


        while($dd_study->fetch())
        {

            $FT_DDs = 0;
            $Entree_DDs = 0;
            $Svc_Fee_DDs = 0;
            $Ambiguous_DDs = 0;

            $debugArr = array();
            if ($month == $debugMonth && $year == $debugYear)
            {
                $debugArr[] = $dd_study->id;
                $debugArr[] = $dd_study->points_discount_total;
                $debugArr[] = $dd_study->subtotal_service_fee;
                $debugArr[] = $dd_study->misc_food_subtotal;
                $debugArr[] = $dd_study->servings_total_count;


            }

            if ($dd_study->servings_total_count == 36)
            {
                // easy
                // the DDs are either used on FT items or Service fee

                if (empty($dd_study->subtotal_service_fee))
                {
                    // there all FT and Misc Items
                    $FT_DDs = $dd_study->points_discount_total;
                }
                else
                {

                    $Svc_Fee_DDs = $dd_study->points_discount_total - $dd_study->subtotal_service_fee;
                    if ($Svc_Fee_DDs <= 0)
                    {
                        // discount less than or equal to service fee so all DDs go to service fee
                        $Svc_Fee_DDs = $dd_study->points_discount_total;
                    }
                    else
                    {
                        // otherwise entire service fee was discounted and remainder is FT_DDs
                        $FT_DDs = $Svc_Fee_DDs;
                        $Svc_Fee_DDs = $dd_study->subtotal_service_fee;
                    }
                }
            }
            else
            {
                 // entrees may have been discounted


                $userDDs = $dd_study->points_discount_total;

                // always apply to service fee first
                if (!empty($dd_study->subtotal_service_fee))
                {
                    $userDDs -= $dd_study->subtotal_service_fee;


                    if ($userDDs <= 0)
                    {
                        $Svc_Fee_DDs = $dd_study->points_discount_total;
                        $userDDs = 0;
                    }
                    else
                    {
                        $Svc_Fee_DDs = $dd_study->subtotal_service_fee;
                    }
                }

                $discountableEntreeAmount = 0;

                if ($userDDs > 0)
                {

                    // remaining dds after service fee
                    $FT_total = COrdersDigest::calculateAddonSales($dd_study->id);
                    $misc_sales = (empty($dd_study->misc_food_subtotal) ? 0 : $dd_study->misc_food_subtotal);

                    $FT_total_and_Misc_sales = $FT_total + $misc_sales;

                    $discountableEntreeAmount = getEntreePointsDiscountableAmount($dd_study->id);

                    if ($FT_total_and_Misc_sales == 0)
                    {
                        // they all go to entress
                        $Entree_DDs = $userDDs;
                    }
                    else if ($discountableEntreeAmount == 0)
                    {
                        // theuy all go to FTs and Misc
                        $FT_DDs = $userDDs;
                      //
                     //   try {
                      //  CLog::Assert($FT_total_and_Misc_sales <= $userDDs, "bad numNums");
                     //   } catch(Exception $e) {

                     //       echo $e->message();
                     //   }
                    }
                    else if ($userDDs >= $discountableEntreeAmount + $FT_total_and_Misc_sales )
                    {
                        $FT_DDs = $FT_total_and_Misc_sales;
                        $Entree_DDs = $discountableEntreeAmount;
                    }
                    else
                    {
                        $Ambiguous_DDs = $userDDs;
                    }
                }
            }


            if ($month == $debugMonth && $year == $debugYear)
            {
                $debugArr[] = $discountableEntreeAmount;
                $debugArr[] = $FT_total;
                $debugArr[] = $FT_total_and_Misc_sales;
                $debugArr[] = $Svc_Fee_DDs;
                $debugArr[] = $FT_DDs;
                $debugArr[] = $Entree_DDs;
                $debugArr[] = $Ambiguous_DDs;


                $length = fputs($dest_fp2,  implode(",", $debugArr) . "\r\n");

            }




            $Total_FT_DDs +=  $FT_DDs;
            $Total_Entree_DDs += $Entree_DDs;
            $Total_Svc_Fee_DDs += $Svc_Fee_DDs;
            $Total_Ambiguous_DDs += $Ambiguous_DDs;

        } // end while orders

        $retVal["Month"][] = date("M-Y", strtotime($monthYear));
        $retVal["Dinner Dollars Awarded"][] = $DDsIN;
        $retVal["Dinner Dollars Awarded (alt)"][] = $DDsIN2;
        $retVal["Dinner Dollars Consumed"][] = $DDsOUT;
        $retVal["Dinner Dollars Expired"][] = $DDsEXP;
        $retVal["DD consumed by FT"][] = $Total_FT_DDs;
        $retVal["DD consumed by Entrees"][] = $Total_Entree_DDs;
        $retVal["DD consumed by Service Fee"][] = $Total_Svc_Fee_DDs;
        $retVal["Ambiguous DDs"][] = $Total_Ambiguous_DDs;


    }


    foreach($retVal as $rowTitle => $data)
    {
        $thisLine = $rowTitle . "," . implode(",", $data);

        $length = fputs($dest_fp,  $thisLine . "\r\n");


    }

    fclose($dest_fp);
    fclose($dest_fp2);

*/

} catch (exception $e) {
    echo "Exception occurred in main loop\n";
    echo $e->getMessage();
}






?>