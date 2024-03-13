<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");
require_once('ExcelExport.inc');


ini_set('memory_limit','-1');
set_time_limit(3600 * 24);

define('TEST', true);
$menu_1 = 239;
$menu_2 = 240;

try {
   $path = "/DreamSite/sides_inv_move.sql";
  // $path = "C:\\Development\\Sites\\DD_Delivered\\sides_inv_move.sql";
   $fh = fopen($path, 'w');

   $store = new DAO();
   $store->query("select distinct store_id from menu_item_inventory where menu_id in (239,240)");

   while($store->fetch()) {
       $store_id = $store->store_id;
        echo "doing store $store_id  ....\r\n";
       $on_order = CMenu::getFutureFTPickups($store_id);

       // get global values
       $currentInventoryGetter = new DAO();
       $currentInventoryGetter->query("select recipe_id, number_sold, override_inventory from menu_item_inventory mmi where isnull(menu_id) and store_id = $store_id and is_deleted = 0");

       while ($currentInventoryGetter->fetch())
       {
           $inMenuOne = false;
           $inMenuTwo = false;

           if (isset($on_order[$currentInventoryGetter->recipe_id]))
           {
               $currentInventoryGetter->number_sold += $on_order[$currentInventoryGetter->recipe_id];
           }

           if ($currentInventoryGetter->override_inventory - $currentInventoryGetter->number_sold > 0)
           {

               $available = $currentInventoryGetter->override_inventory - $currentInventoryGetter->number_sold;

               $totalSoldMonth1Val = 0;
               $totalSoldMonth2Val = 0;

               $menu1Inv = DAO_CFactory::create('menu_item_inventory');
               $menu1Inv->menu_id = $menu_1;
               $menu1Inv->store_id = $store_id;
               $menu1Inv->recipe_id = $currentInventoryGetter->recipe_id;
                if ($menu1Inv->find(true))
                {
                    $inMenuOne = true;
                    $totalSoldMonth1 = new DAO();
                    $totalSoldMonth1->query("select mi.id, sum(oi.item_count) as total_sold from orders o
                                            join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
                                            join session s on s.id = b.session_id and s.menu_id = $menu_1
                                            join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
                                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = {$currentInventoryGetter->recipe_id}
                                            where o.store_id = $store_id and o.is_deleted = 0
                                            group by oi.menu_item_id");
                    if ($totalSoldMonth1->fetch())
                    {
                        $totalSoldMonth1Val = $totalSoldMonth1->total_sold;
                    }

                }

               $menu2Inv = DAO_CFactory::create('menu_item_inventory');
               $menu2Inv->menu_id = $menu_2;
               $menu2Inv->store_id = $store_id;
               $menu2Inv->recipe_id = $currentInventoryGetter->recipe_id;
               if ($menu2Inv->find(true))
               {
                   $inMenuTwo = true;
                   $totalSoldMonth2 = new DAO();
                   $totalSoldMonth2->query("select mi.id, sum(oi.item_count) as total_sold from orders o
                                            join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
                                            join session s on s.id = b.session_id and s.menu_id = $menu_2
                                            join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
                                            join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id = {$currentInventoryGetter->recipe_id}
                                            where o.store_id = $store_id and o.is_deleted = 0
                                            group by oi.menu_item_id");
                   if ($totalSoldMonth2->fetch())
                   {
                       $totalSoldMonth2Val = $totalSoldMonth2->total_sold;
                   }


               }

               if ($inMenuOne && $inMenuTwo)
               {
                   $menu1Amount = (int)ceil($available * 0.5);
                   $menu2Amount = $available - $menu1Amount;

                   if (TEST && $available > 0 && $available < 6)
				   {
				   		echo "menu1Amount: $menu1Amount menu2Amount $menu2Amount\r\n";
				   }


                   if (!TEST)
                   {
                       $oldMenu1 = clone($menu1Inv);
                       $menu1Inv->override_inventory = $menu1Amount + $totalSoldMonth1Val;
                       $menu1Inv->number_sold = $totalSoldMonth1Val;
                       $menu1Inv->updated_by = 1;
                       $menu1Inv->update($oldMenu1);
                   }
                   else
                   {
                       $o1 = $menu1Amount + $totalSoldMonth1Val;
                       $n1 = $totalSoldMonth1Val;
                       $length = fputs($fh, "update menu_item_inventory set override_inventory = $o1, number_sold = $n1 where store_id = $store_id and recipe_id = {$currentInventoryGetter->recipe_id} and menu_id = $menu_1 and is_deleted = 0;\r\n");
                   }

                   if ($menu2Amount > 0)
                   {
                       if (!TEST)
                       {
                           $oldMenu2 = clone($menu2Inv);
                           $menu2Inv->override_inventory = $menu2Amount + $totalSoldMonth2Val;
                           $menu2Inv->number_sold = $totalSoldMonth2Val;
                           $menu2Inv->updated_by = 1;
                           $menu2Inv->update($oldMenu2);
                       }
                       else
                       {
                           $o2 = $menu2Amount + $totalSoldMonth2Val;
                           $n2 = $totalSoldMonth2Val;
                           $length = fputs($fh, "update menu_item_inventory set override_inventory = $o2, number_sold = $n2 where store_id = $store_id and recipe_id = {$currentInventoryGetter->recipe_id} and menu_id = $menu_2 and is_deleted = 0;\r\n");
                       }
                   }
               }
               else if ($inMenuOne)
               {
				   $menu1Amount = $available;
				   if (!TEST)
                   {
                       $oldMenu1 = clone($menu1Inv);
                       $menu1Inv->override_inventory = $menu1Amount + $totalSoldMonth1Val;
                       $menu1Inv->number_sold = $totalSoldMonth1Val;
                       $menu1Inv->updated_by = 1;
                       $menu1Inv->update($oldMenu1);
                   }
                   else
                   {
                       $o1 = $menu1Amount + $totalSoldMonth1Val;
                       $n1 = $totalSoldMonth1Val;
                       $length = fputs($fh, "update menu_item_inventory set override_inventory = $o1, number_sold = $n1 where store_id = $store_id and recipe_id = {$currentInventoryGetter->recipe_id} and menu_id = $menu_1 and is_deleted = 0;\r\n");
                   }
               }
               else if ($inMenuTwo)
               {
				   $menu2Amount = $available;

				   if (!TEST)
                   {
                       $oldMenu2 = clone($menu2Inv);
                       $menu2Inv->override_inventory = $menu2Amount + $totalSoldMonth2Val;
                       $menu2Inv->number_sold = $totalSoldMonth2Val;
                       $menu2Inv->updated_by = 1;
                       $menu2Inv->update($oldMenu2);
                   }
                   else
                   {
                       $o2 = $menu2Amount + $totalSoldMonth2Val;
                       $n2 = $totalSoldMonth2Val;
                       $length = fputs($fh, "update menu_item_inventory set override_inventory = $o2, number_sold = $n2 where store_id = $store_id and recipe_id = {$currentInventoryGetter->recipe_id} and menu_id = $menu_2 and is_deleted = 0;\r\n");
                   }
               }
           }
       }
   }

    fclose($fh);
    
}
catch (exception $e)
{
    CLog::RecordException($e);
}

?>