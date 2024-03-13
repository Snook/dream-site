<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 // 1) Create new 3 servings intro items for the June Menu
 
define('DEV', false);
define('REVERSE', false);

if (DEV)
{
    require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
}
else 
{
    require_once("/DreamSite/includes/Config.inc");
}

require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

set_time_limit(100000);
ini_set('memory_limit', '1024M');

$RegularOrdersOnly = true;
//$Year = 2015;  $Cap_menu = 173;
//$Year = 2016;  $Cap_menu = 185;
//$Year = 2017;  $Cap_menu = 197;
//$Year = 2018;  $Cap_menu = 209;
$Year = 2021;  $Cap_menu = 221;
 try {
     
     
     $storeObj = new DAO();
     $storeArr = array();
     $storeObj->query("select id, home_office_id, store_name, city, state_id, is_corporate_owned from store where is_deleted = 0");
     while($storeObj->fetch())
     {
         $storeArr[$storeObj->id] = array('hoid' => $storeObj->home_office_id, 'store_name' => $storeObj->store_name, 'city'=> $storeObj->city, 'state_id' => $storeObj->state_id, 'is_corporate_owned' => $storeObj->is_corporate_owned);
     }
     
     
     if (REVERSE)
     {
     
         if (DEV)
         {
             $path = "C:\\Development\\Sites\\DreamSite\\Guests_of_Year_$Year.csv";
             $path2 = "C:\\Development\\Sites\\DreamSite\\reverse_guests_drop_off $Year.csv";
             $path3 = "C:\\Development\\Sites\\DreamSite\\reverse_guests_drop_off_summary $Year.csv";
         }
         else
         {
             $path = "/DreamSite/Dropoff_reporting/Guests_of_Year_$Year.csv";
             $path2 = "/DreamSite/Dropoff_reporting/reverse_guests_drop_off (all orders) $Year.csv";
             $path3 = "/DreamSite/Dropoff_reporting/reverse_guests_drop_off_summary (all orders) $Year.csv";
         }
     }
     else 
     {
         if (DEV)
         {
             $path = "C:\\Development\\Sites\\DreamSite\\Drop Off Basis $Year.csv";
             $path2 = "C:\\Development\\Sites\\DreamSite\\guests_drop_off $Year.csv";
             $path3 = "C:\\Development\\Sites\\DreamSite\\guests_drop_off_summary $Year.csv";
         }
         else
         {
             $path = "/DreamSite/Drop Off Basis $Year.csv";
             $path2 = "/DreamSite/guests_drop_off_$Year.csv";
             $path3 = "/DreamSite/guests_drop_off_summary_$Year.csv";
         }
     }
         
     $src_fp = fopen($path, 'r');
 	 $dest_fp = fopen($path2, 'w+');
 	
 	$len = fputcsv($dest_fp, array("Store ID", "HOID", "Store Name", "City", "State", "Is Corproate", "ID", "First Name", "Last Name", "Email", 
 	    "First Session", "First Session Type", "Visit Count", "User Class", "Attendance Span", "Attendance Frequency", "Is Currrent") );
 	
 	$dest_fp_sum = fopen($path3, 'w+');
 	
 	
 	$summaryArray = array();
 	
 	 $headers = fgetcsv( $src_fp);
 	 
 	 echo " beginning processing ...\r\n";
 	 
 	 $counter = 0;
 	
 	 while($thisRow = fgetcsv( $src_fp))
 	 {
 	     $menu_id = array_pop($thisRow);
 	     
 	     $counter++;
 	   //  print_r($thisRow);
 	   
 	     // USER Classes
 	     // ------------------
 	     // 4 month span classes
 	     // -----
 	     // one Promo and done
 	     // one Standard and done
 	     // two promo and done
 	     // two orders one promo
 	     // three orders out at first standard
 	     // three orders and done
 	     $userDetailArray = array();
 	     
 	     $attendance_studier = new DAO();
 	     
 	     
 	     if ($RegularOrdersOnly)
 	     {
 	         $attendance_studier->query("select o.user_id, o.type_of_order, s.menu_id, s.session_start, s.store_id, 0 as host_required, 0 as can_rsvp_only from booking b
                         	     join session s on s.id = b.session_id
                         	     join orders o on o.id = b.order_id
                         	     where b.user_id = {$thisRow[0]} and b.status = 'ACTIVE' and o.type_of_order = 'STANDARD'
                         	     order by s.session_start");
 	     }
 	     else 
 	     {
     	     $attendance_studier->query("select o.type_of_order, s.session_start, s.menu_id, s.store_id, dtep.host_required, dtep.can_rsvp_only from booking b
                         	     join session s on s.id = b.session_id
                                 left join session_properties sp on sp.session_id = s.id
                                 left join dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id
                         	     join orders o on o.id = b.order_id
                         	     where b.user_id = {$thisRow[0]} and b.status = 'ACTIVE'
                         	     order by s.session_start");
 	     }
 	     
 	     $visitsThisGuest = $attendance_studier->N;
 	     if ($attendance_studier->N == 0)
 	     {
 	         continue;
 	     }
 	     
 	     $firstOrderPromo = false;
 	     $secondOrderPromo = false;
 	     $orderCount = 0;
 	     $regularOrderCount = 0;
 	     
 	     $firstSessionDate = false;
 	     $lastStoreAttended = false;
 	     $firstSessionType = false;
 	     
 	     $lastSessionMenu = false;
 	     $skippedMenuCount = 0;
 	     
 	     
 	     while ($attendance_studier->fetch())
 	     {
 	         $orderCount++;
			 
			if ($RegularOrdersOnly && ($attendance_studier->type_of_order == 'FUNDRAISER' || $attendance_studier->type_of_order == 'DREAM_TASTE'))
			{
				throw new Exception("Should be no promos 1 " . $thisRow[0]);	
			}

 	         
 	         if ($orderCount == 1)
 	         {
 	             $firstSessionDate = $attendance_studier->session_start;
 	             
 	             $firstSessionType = $attendance_studier->type_of_order;
 	             if ($firstSessionType == 'DREAM_TASTE')
 	             {
 	                 if ($attendance_studier->can_rsvp_only)
 	                 {
 	                     $firstSessionType = 'FNO';
 	                 }
 	                 else if (!$attendance_studier->host_required)
 	                 {
 	                     $firstSessionType = 'OPEN_HOUSE';
 	                 }
 	             }
 	         }
 	         
 	         
 	         if ($orderCount == 1 &&
 	             ($attendance_studier->type_of_order == 'DREAM_TASTE' || $attendance_studier->type_of_order == 'FUNDRAISER' || $attendance_studier->type_of_order == 'INTRO'))
 	         {
 	             $firstOrderPromo = true;
 	         }
 	         
 	         if ($orderCount == 2 &&
 	             ($attendance_studier->type_of_order == 'DREAM_TASTE' || $attendance_studier->type_of_order == 'FUNDRAISER' || $attendance_studier->type_of_order == 'INTRO'))
 	         {
 	             $secondOrderPromo = true;
 	         }
 	         
 	         if ($attendance_studier->type_of_order == 'STANDARD')
 	         {
 	             $regularOrderCount++;
 	         }
 	         
 	         if ($lastSessionMenu && $attendance_studier->menu_id - $lastSessionMenu > 1)
 	         {
 	             $skippedMenuCount++;
 	         }
 	         
 	         
 	         
 	         $lastSessionMenu = $attendance_studier->menu_id;
 	         $lastSessionDate = $attendance_studier->session_start;
 	         $lastStoreAttended = $attendance_studier->store_id;
 	     }
 	     
 	     $attendSpan = (strtotime($lastSessionDate) - strtotime($firstSessionDate)) / 86400;
 	     $attendFreq = $attendSpan / $orderCount;
 	     $IsCurrent = (time() - strtotime(strtotime($lastSessionDate)) > (86400 * 45));
 	     
 	     $isIntermittent = false;
 	     if ($regularOrderCount > 3)
 	     {
 	         $threshold = (int)$regularOrderCount / 2;
 	         if ($skippedMenuCount >= $threshold)
 	         {
 	             $isIntermittent = true;
 	         }
 	     }
 	     
 	     
 	     if (!isset($summaryArray[$lastStoreAttended]))
 	     {
 	         $summaryArray[$lastStoreAttended] = 
 	              array('store_id' => $lastStoreAttended, 
 	                  "hoid" => $storeArr[$lastStoreAttended]['hoid'],
 	                  "store_name" => $storeArr[$lastStoreAttended]['store_name'],
 	                  "city" => $storeArr[$lastStoreAttended]['city'],
 	                  "state_id" => $storeArr[$lastStoreAttended]['state_id'],
 	                  "is_corporate_owned" => $storeArr[$lastStoreAttended]['is_corporate_owned'],
 	                  'one_Promo_and_done' => 0,
 	                  'one_Promo_and_done %' => 0,
 	                  'one_Standard_and_done' => 0,
 	                  'one_Standard_and_done %' => 0,
 	                  'two_promo_and_done' => 0,
 	                  'two_promo_and_done %' => 0,
 	                  'two_orders_one_promo' => 0,
 	                  'two_orders_one_promo %' => 0,
 	                  'three_orders_out_at_first_standard' => 0,
 	                  'three_orders_out_at_first_standard %' => 0,
 	                  'three_orders_and_done' => 0,
 	                  'three_orders_and_done %' => 0,
 	                  '1 out' => 0,
 	                  '1 out %' => 0,
 	                  '2 out' => 0,
 	                  '2 out %' => 0,
 	                  '3 out' => 0,
 	                  '3 out %' => 0,
 	                  '4 out' => 0,
 	                  '4 out %' => 0,
 	                  '5 out' => 0,
 	                  '5 out %' => 0,
 	                  '6 out' => 0,
 	                  '6 out %' => 0,
 	                  '7 out' => 0,
 	                  '7 out %' => 0,
 	                  '8 out' => 0,
 	                  '8 out %' => 0,
 	                  '9 out' => 0,
 	                  '9 out %' => 0,
 	                  '10 out' => 0,
 	                  '10 out %' => 0,
 	                  '11 out' => 0,
 	                  '11 out %' => 0,
 	                  '12 plus' => 0,
 	                  '12 plus %' => 0,
 	                  'total_new' => 0,
 	                  'Current Guest' => 0,
 	                  'Average Attend Span' => 0,
 	                  'Average Num Visits' => 0,
 	                  'temp_count' => 0,
 	                  'temp_sum_freq' => 0,
 	                  'temp_sum_span' => 0,
 	                  'temp_sum_visits' => 0,
 	                  'num_intermittent' => 0
 	              );
 	     }
 	     
 	     
 	     $summaryArray[$lastStoreAttended]['temp_count']++;
 	     $summaryArray[$lastStoreAttended]['temp_sum_freq'] += $attendFreq;
 	     $summaryArray[$lastStoreAttended]['temp_sum_span'] += $attendSpan;
 	     $summaryArray[$lastStoreAttended]['temp_sum_visits'] += $visitsThisGuest;
 	     
 	     if ($isIntermittent)
 	     {
 	         $summaryArray[$lastStoreAttended]['num_intermittent']++;
 	     }
 	     
 	     
 	     if ($IsCurrent)
 	     {
 	         $summaryArray[$lastStoreAttended]['Current Guest']++;
 	     }
 	    
 	     // store summary
 	     if ($orderCount > 11)
 	     {
 	         $summaryArray[$lastStoreAttended]['12 plus']++;
 	     }
 	     else
 	     {
 	         $summaryArray[$lastStoreAttended]["$orderCount out"]++;
 	     }
 	     
 	     if ($orderCount == 1)
 	     {
 	         
 	         if ($firstOrderPromo)
 	         {
 	             $summaryArray[$lastStoreAttended]['one_Promo_and_done']++;
 	         }
 	         else 
 	         {
 	             $summaryArray[$lastStoreAttended]['one_Standard_and_done']++;
 	         }
 	     }
 	     else if ($orderCount == 2)
 	     {
 	         
 	         if ($firstOrderPromo && $secondOrderPromo)
 	         {
 	             $summaryArray[$lastStoreAttended]['two_promo_and_done']++;
 	         }
 	         else if ($firstOrderPromo || $secondOrderPromo)
 	         {
 	             $summaryArray[$lastStoreAttended]['two_orders_one_promo']++;
 	         }
 	     }
 	     else if ($orderCount == 3)
 	     {
 	         if ($firstOrderPromo || $secondOrderPromo)
 	         {
 	             $summaryArray[$lastStoreAttended]['three_orders_out_at_first_standard']++;
 	         }
 	         else
 	         {
 	             $summaryArray[$lastStoreAttended]['three_orders_and_done']++;
 	         }
 	     }
 	     
 	     //user detail
 	     
 	     
 	     $thisUserClass = "Usual";
 	     if ($orderCount == 1)
 	     {
 	         
 	         if ($firstOrderPromo)
 	         {
 	             $thisUserClass = 'one_Promo_and_done';
 	         }
 	         else
 	         {
 	             $thisUserClass = 'one_Standard_and_done';
 	         }
 	     }
 	     else if ($orderCount == 2)
 	     {
 	         
 	         if ($firstOrderPromo && $secondOrderPromo)
 	         {
 	             $thisUserClass = 'two_promo_and_done';
 	         }
 	         else if ($firstOrderPromo || $secondOrderPromo)
 	         {
 	             $thisUserClass = 'two_orders_one_promo';
 	         }
 	     }
 	     else if ($orderCount == 3)
 	     {
 	         if ($firstOrderPromo || $secondOrderPromo)
 	         {
 	             $thisUserClass = 'three_orders_out_at_first_standard';
 	         }
 	         else
 	         {
 	             $thisUserClass = 'three_orders_and_done';
 	         }
 	     }
 	     
 	     $attendSpan = (strtotime($lastSessionDate) - strtotime($firstSessionDate)) / 86400;
 	     $attendFreq = $attendSpan / $orderCount;
 	     
 	     $userDetailArray = array_merge($storeArr[$lastStoreAttended], $thisRow, array($firstSessionType, $orderCount, $thisUserClass, $attendSpan, $attendFreq, ($IsCurrent ? "Yes" : "No")));
 	     
 	     $len = fputcsv($dest_fp, $userDetailArray);
 	     
 	     if ($counter % 500 == 0)
 	     {
 	         echo $counter . " users processed\r\n";
 	     }
 	 }
 	 
 	 $len = fputcsv($dest_fp_sum, array("Store ID", "HOID", "Store Name", "City", "State", "Is Corporate", 
 	     "One Promo and out", "%", "One Std and Out", "%", "Two Promo and Out", "%", "Two Orders (1 promo) and Out", "%", "Three and out w/ Promo", "%", "Three and Out", "%", "1 Order", "%", "2 Order", "%", "3 Order", "%",
 	     "4 Order", "%", "5 Order", "%", "6 Order", "%", "7 Order", "%", "8 Order", "%", "9 Order", "%", "10 Order", "%", "11 Order", "%", "12 Plus", "%", "Total New in Period", "Num Current", "Average Span", "Average #  Visits" , "# Intermittent Guests"));
 	
 	 
 	 echo " beginning summary output ...\r\n";
 	 
 	 foreach($summaryArray as $store => &$data)
 	 { 	               
 	     $data['one_Promo_and_done %'] = $data['one_Promo_and_done'] / $data['temp_count'];
 	     $data['one_Standard_and_done %'] = $data['one_Standard_and_done'] / $data['temp_count'];
 	     $data['two_promo_and_done %'] = $data['two_promo_and_done'] / $data['temp_count'];
 	     $data['two_orders_one_promo %'] = $data['two_orders_one_promo'] / $data['temp_count'];
 	     $data['three_orders_out_at_first_standard %'] = $data['three_orders_out_at_first_standard'] / $data['temp_count'];
 	     $data['three_orders_and_done %'] = $data['three_orders_and_done'] / $data['temp_count'];
 	     
 	     $data['total_new'] = $data['temp_count'];

 	     for ($i = 1; $i < 12; $i++)
 	     {
 	         $data["$i out %"] = $data["$i out"] / $data['temp_count'];
 	     }
 	     
 	     $data["12 plus %"] = $data["12 plus"] / $data['temp_count'];
 	     
 	     
 	     $data['Average Attend Span'] = $data['temp_sum_span'] / $data['temp_count'];
 	     $data['Average Num Visits'] = $data['temp_sum_visits'] / $data['temp_count'];
 	     
 	     unset($data['temp_count']);
 	     unset($data['temp_sum_span']);
 	     unset($data['temp_sum_freq']);
 	     unset($data['temp_sum_visits']);
 	     
 	     $len = fputcsv($dest_fp_sum, $data);
 	 }
 	 
	
 	fclose($src_fp);
 	fclose($dest_fp);
 	fclose($dest_fp_sum);
 	
 	echo " Done! \r\n";
 	

	} catch (exception $e) {
		echo "new user behVIOR report failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}
?>
