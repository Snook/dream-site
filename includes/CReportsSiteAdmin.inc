<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2006
 */
require_once ('includes/DAO/User.php');

class CReportsSiteAdmin
{

	// NOT USED ANYMORE...
	static function calculateUnregisteredHomeStoreUsers ($Day, $Month, $Year,  $interval)
	{

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$wherestr = "";
		$groupby = "";
		$varstr = 'Select Count(`user`.id) as user_count, "Unknown Home Store ID" as store_name From `user` ' ;
		$wherstr =  "where user.home_store_id is null and user.is_deleted = 0 and user.timestamp_created >= '"  .  $current_date_sql . "' AND " . " user.timestamp_created <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
		$sql = $varstr . $wherstr;
		$user = DAO_CFactory::create("user");
		$user->query($sql);
		$rows = array();
		$user->fetch();
		$newarray = $user->toArray();
		return ($newarray);
	}


	static function calculateNewCustomerCounts ($Day, $Month, $Year,  $interval)
	{

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$wherestr = "";
		$groupby = "";
		$varstr = 'Select IF(store.home_office_id is NULL, "-----", home_office_id ) as home_office_id, IF(store_name is NULL, "-----", store_name) as store_name,IF(city is NULL, "-----", city) as city, IF(state_id is NULL, "-----", store.state_id) as state_id,  Count(`user`.id) as user_count From `user` left Join store ON `user`.home_store_id = store.id  ' ;
		$wherstr =  "where user.is_deleted = 0 and user.timestamp_created >= '"  .  $current_date_sql . "' AND " . " user.timestamp_created <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
		$groupby = ' Group By home_store_id order by store.state_id, store.city, store_name ASC';

		$sql = $varstr . $wherstr . $groupby;
		$user = DAO_CFactory::create("user");
		$user->query($sql);
		$rows = array();
		$count = 0;
		while ($user->fetch()) {

				$newarray = $user->toArray();
				unset($newarray['is_deleted']);
				$rows [$count++]= $newarray;
		}

		/*
		if (count($rows) > 0 ) {
		   $tempArray = CReportsSiteAdmin::calculateUnregisteredHomeStoreUsers ($Day, $Month, $Year,  $interval) ;
		   if ($tempArray['user_count'] > 0) {
		   	 array_unshift ($rows, $tempArray);
		   }

		}
		* */

		return ($rows);
	}

	// ALTER TABLE `dreamsite`.`orders` ADD INDEX `Idx_created`(`timestamp_created`);
	static function calculateCustomerOrders ($Day, $Month, $Year,  $interval, $show_all_orders)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "";
		$groupby = "";
		$newuser = "";
		$ordertime = "";

		$varstr = "Select store.home_office_id, store.store_name, store.city, store.state_id, count(user.id) as user_count, sum(grand_total) as grand_total, sum(promo_code_discount_total) as promo_code_discount_total, sum(direct_order_discount) as direct_order_discount, sum(session_discount_total) as session_discount_total, Sum(family_savings_discount) as family_savings_discount,  Sum(volume_discount_total) as volume_discount_total, sum(subtotal_premium_markup) as subtotal_premium_markup, sum(user_preferred_discount_total) as user_preferred_discount_total, Sum(orders.subtotal_home_store_markup) as subtotal_home_store_markup, sum(menu_items_total_count) as menu_items_total_count, sum(product_items_total_count) as product_items_total_count, sum(subtotal_menu_items) as subtotal_menu_items, sum(subtotal_products) as subtotal_products, sum(subtotal_food_sales_taxes) as subtotal_food_sales_taxes, ";
		$nextstr = ' count(order_type = "WEB" OR NULL) as webcount,count(order_type =  "DIRECT" OR NULL) as direct,count(order_type = "IN_STORE" OR NULL) as instore,count(session_class = "QUICKSIX" OR NULL) as quicksix,count(session_class =  "STANDARD" OR NULL) as standard ';

		$fromstr = " FROM booking Inner Join session ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join store ON orders.store_id = store.id Inner Join user ON booking.user_id = user.id ";


		$wherstr= " Where session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.session_publish_state != 'SAVED'" ;

		if ($show_all_orders) {
		   $newuser = " and user.timestamp_created >= '"  .  $current_date_sql . "' AND " . " user.timestamp_created <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
		}

		$ordertime =  " and orders.timestamp_created >= '"  .  $current_date_sql . "' AND " . " orders.timestamp_created <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";

		$groupby = " Group By orders.store_id Order By store.store_name Asc";


		$sql = $varstr . $nextstr  . $fromstr . $wherstr . $newuser . $ordertime . $groupby;

		$bookings = DAO_CFactory::create("booking");
		$bookings->query($sql);
		$rows = array();
		$count = 0;
		while ($bookings->fetch()) {
				$rows [$count++]= $bookings->toArray();
		}
		return ($rows);
	}


	// ALTER TABLE `dreamsite`.`orders` ADD INDEX `Idx_created`(`timestamp_created`);
	static function calculateSessionFinancials ($Day, $Month, $Year,  $interval)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "";
		$groupby = "";
		$newuser = "";
		$ordertime = "";

		$varstr = "Select store.home_office_id, store.store_name, store.city, store.state_id, count(user.id) as user_count, sum(grand_total) as grand_total, sum(session_discount_total) as session_discount_total, sum(promo_code_discount_total) as promo_code_discount_total, sum(direct_order_discount) as direct_order_discount,sum(volume_discount_total) as volume_discount_total, sum(subtotal_premium_markup) as subtotal_premium_markup, sum(user_preferred_discount_total) as user_preferred_discount_total, Sum(orders.subtotal_home_store_markup) as subtotal_home_store_markup,  sum(menu_items_total_count) as menu_items_total_count, sum(product_items_total_count) as product_items_total_count, sum(subtotal_menu_items) as subtotal_menu_items, sum(subtotal_products) as subtotal_products, sum(subtotal_food_sales_taxes) as subtotal_food_sales_taxes, ";
		$nextstr = ' count(order_type = "WEB" OR NULL) as webcount,count(order_type =  "DIRECT" OR NULL) as direct,count(order_type = "IN_STORE" OR NULL) as instore,count(session_class = "QUICKSIX" OR NULL) as quicksix,count(session_class =  "STANDARD" OR NULL) as standard ';
		$fromstr = " FROM booking Inner Join session ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join store ON orders.store_id = store.id Inner Join user ON booking.user_id = user.id ";
		$wherstr= " Where session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.session_publish_state != 'SAVED'" ;

		$ordertime =  " and session.session_start >= '"  .  $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
		$groupby = " Group By orders.store_id Order By store.state_id, store.city, store.store_name Asc";
		$sql = $varstr . $nextstr  . $fromstr . $wherstr . $ordertime . $groupby;

		$bookings = DAO_CFactory::create("booking");

		$bookings->query($sql);
		$rows = array();
		$count = 0;
		while ($bookings->fetch()) {
				$arr = $bookings->toArray();
				if ($arr['volume_discount_total'] == '') $arr['volume_discount_total'] = 0;

				$rows [$count++]=$arr;
		}
		return ($rows);
	}


	static function calculateCustomerSessionCounts ($Day, $Month, $Year,  $interval)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "";
		$groupby = "";
		$ordertime = "";

		$varstr = "Select store.home_office_id, store.store_name, store.city, store.state_id,  count(user.id) as user_count From" .
		" booking Inner Join session ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id" .
		" Inner Join store ON orders.store_id = store.id Inner Join `user` ON booking.user_id = `user`.id ";
		$wherstr= " Where session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.session_publish_state != 'SAVED'" ;
		$ordertime =  " and session.session_start >= '"  .  $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
		$groupby = " Group By orders.store_id Order By store.state_id, store.city, store.store_name Asc";

		$sql = $varstr . $wherstr  . $ordertime . $groupby;
		$user = DAO_CFactory::create("user");
		$user->query($sql);
		$rows = array();
		$count = 0;
		while ($user->fetch()) {
				$arr =  $user->toArray();
				unset($arr['is_deleted']);
				$rows [$count++]= $arr;
		}
		return ($rows);
	}



	static function sessionCounts ($Day, $Month, $Year,  $interval, $show_by_store)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "";
		$groupby = "";
		$ordertime = "";

		if ($show_by_store == 0) {
		   $varstr = "Select sum(is_sampler) as is_sampler, pricing_type, Sum(order_item.item_count) as menucount, menu_item.menu_item_name, menu_item.id as menu_item_id From booking" .
			" Inner Join orders ON booking.order_id = orders.id" .
			" Inner Join session ON booking.session_id = session.id" .
			" Inner Join order_item ON orders.id = order_item.order_id" .
			" Inner Join menu_item ON order_item.menu_item_id = menu_item.id" ;

			$wherstr= " Where order_item.is_deleted = 0 and session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.session_publish_state != 'SAVED'" ;
			$ordertime =  " and session.session_start >= '"  .  $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
			$groupby = " Group By menu_item_id Order By menu_item_name, pricing_type Asc";
		}
		else {
		  	$varstr = "Select sum(is_sampler) as is_sampler, pricing_type, Sum(order_item.item_count) as menucount, menu_item.menu_item_name, menu_item.id as menu_item_id, store.home_office_id, store.store_name, store.city, store.state_id, store.id From booking" .
			" Inner Join orders ON booking.order_id = orders.id" .
			" Inner Join session ON booking.session_id = session.id" .
			" Inner Join order_item ON orders.id = order_item.order_id" .
			" Inner Join menu_item ON order_item.menu_item_id = menu_item.id" .
			" Inner Join store ON orders.store_id = store.id" ;
			$wherstr= " Where order_item.is_deleted = 0 and session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.session_publish_state != 'SAVED'" ;
			$ordertime =  " and session.session_start >= '"  .  $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" .  $current_date_sql . "',INTERVAL " . $interval  . ")";
			$groupby = " Group By orders.store_id, menu_item_id Order By store.state_id, store.city, store.store_name, menu_item_name, pricing_type Asc";


		}

		$sql = $varstr . $wherstr  . $ordertime . $groupby;

		$user = DAO_CFactory::create("booking");
		$user->query($sql);
		$rows = array();
		$count = 0;
		while ($user->fetch()) {

				$is_sampler_sum = $user->is_sampler;
				// take this amount away from the appropriate item type and create a new pricing type with the proper sum
							$pricing_type = $user->pricing_type;
				if ($pricing_type == CMenuItem::FULL || $pricing_type == CMenuItem::LEGACY)  $pricing_type = "6 Serv";
				else if ($pricing_type == CMenuItem::HALF)  $pricing_type = "3 Serv";
				else if ($pricing_type == CMenuItem::INTRO)  $pricing_type = "Intro";

				$menu_count =  $user->menucount;
				if ($is_sampler_sum > 0) {
					$rows [$count]['pricing_type']= "18 Serv Sampler";
					$menu_count -= $is_sampler_sum;
					$rows [$count]['menucount']= $is_sampler_sum;
					$rows [$count]['menu_item_name']= $user->menu_item_name;
					if (isset($user->store_name)) $rows[$count]['store_name'] = $user->store_name;
					$rows[$count]['menu_item_id']= $user->menu_item_id;
					$count++;
				}

				$rows [$count]['pricing_type']= $pricing_type;
				$rows [$count]['menucount']= $menu_count;
				$rows [$count]['menu_item_name']= $user->menu_item_name;
				if (isset($user->store_name)) $rows[$count]['store_name'] = $user->store_name;


				$rows[$count++]['menu_item_id']= $user->menu_item_id;


		}
		return ($rows);
	}


	static function findCustomers ( $Day, $Month, $Year, $Interval, $CSV, &$filename)
	{
		$booking = DAO_CFactory::create("booking");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		srand((double)microtime()*1000000);
		$ran =  rand(0,1000000);
		$filename = "DD" . $ran . ".csv";
		$selectStr = "Select store.store_name, user.id, `user`.lastname,`user`.firstname, IF(user.is_deleted>0, 'no', 'yes') as deleted, user.gender, `user`.primary_email,`user`.telephone_1,`user`.telephone_1_call_time,`user`.telephone_2,`user`.telephone_2_call_time, address.address_line1, IF(count(address.address_line2) > 1, address.address_line2, '') as  address_line2 , address.city,address.state_id, address.postal_code , ";
		$selectStr .= " GROUP_CONCAT(session_start) AS sessions_attended, GROUP_CONCAT(session_class) AS session_type, Count(orders.id) AS orders_made, GROUP_CONCAT(grand_total) AS order_amounts";
		$selectStr .= " into OUTFILE " . "'" . CSVMYSQLPATH  . $ran . ".csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ";
		$selectStr .= " From booking Inner Join orders ON booking.order_id = orders.id Inner Join session ON booking.session_id = session.id  Inner Join store ON orders.store_id = store.id  Inner Join `user` ON booking.user_id = `user`.id Inner Join address ON `user`.id = address.user_id";
		$selectStr .=  " where session_start >= '"  . $current_date_sql . "' AND " ;
		$selectStr .=  " session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND " ;
		$selectStr .=   ' booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" ';
		$selectStr .= " group by user.id order by store.store_name, user.lastname, user.firstname";
		$booking->query($selectStr);
	}

	static function findStoreDetails ( &$filename )
	{
		$store = DAO_CFactory::create("store");

		srand((double)microtime()*1000000);
		$ran =  rand(0,1000000);
		$filename = "DD" . $ran . ".csv";

		$selectStr = "select '  store name   ' AS store_name," .
		"' home_office_id  ' AS home_office_id," .
		"' grand_opening_date  ' AS grand_opening_date," .
		"' store id   ' AS storeid," .
		"' address_line1   ' AS address_line1," .
		"' address_line2   ' AS address_line2," .
		"' city   ' AS city," .
		"' state   ' AS state_id," .
		"' country   ' AS country_id," .
		"' postal_code   ' AS postal_code," .
		"' usps_adc   ' AS usps_adc," .
		"' email_address   ' AS email_address," .
		"' telephone_day' AS telephone_day," .
		"' telephone_evening   ' AS telephone_evening," .
		"' fax   ' AS fax," .
		"' observes_DST   ' AS observes_DST," .
		"' timezone   ' as tz_name," .
		"' is Store Active   ' as is_active from store limit 1 union " .

		$selectStr .= "Select store.store_name,home_office_id,IF(grand_opening_date is null, '', grand_opening_date) as grand_opening_date, store.id,store.address_line1,store.address_line2,store.city,store.state_id,store.country_id,store.postal_code,store.usps_adc,store.email_address,store.telephone_day,store.telephone_evening,store.fax,store.observes_DST,timezones.tz_name,store.active ";
		$selectStr .= " into OUTFILE " . "'" . CSVMYSQLPATH  . $ran . ".csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ";
		$selectStr .= " From store left Join timezones ON store.timezone_id = timezones.id ";
		$selectStr .= " where store.is_deleted = 0 order by state_id, store_name";

		$store->query($selectStr);
	}
//TODO: ACCESS_CHANGE
	static function findFranchiseDetails ( &$filename )
	{
		$store = DAO_CFactory::create("store");
		srand((double)microtime()*1000000);
		$ran =  rand(0,1000000);
		$filename = "DD" . $ran . ".csv";

		$selectStr = "select '   store name   ' AS store_name," .
		"' home_office_id  ' AS home_office_id," .
		"' city of store   ' AS city," .
		"' state of store   ' AS state_id," .
		"' owner firstname   ' AS firstname," .
		"' owner lastname   ' AS lastname," .
		"' store primary_email   ' AS primary_email," .
		"' store telephone_day   ' AS telephone_day," .
		"' store telephone_evening   ' AS telephone_evening," .
		"' store fax   ' AS fax," .
		"' store telephone_1_call_time   ' AS telephone_1_call_time," .
		"' store last_login   ' AS last_login," .
		"' visit_count   ' AS visit_count," .
		"' franchise_name' AS franchise_name," .
		"' owner address_line1   ' AS address_line1," .
		"' owner  address_line2   ' AS address_line2," .
		"' owner city   ' AS address_city," .
		"' owner state   ' as state," .
		"' owner postal_code   ' from store limit 1 union " .

		$selectStr .= "Select store.store_name,home_office_id,store.city,store.state_id,`user`.firstname,`user`.lastname,`user`.primary_email,`user`.telephone_1,`user`.telephone_2,`user`.fax,`user`.telephone_1_call_time,`user`.last_login,`user`.visit_count,franchise.franchise_name,address.address_line1,address.address_line2,address.city as address_city,address.state_id,address.postal_code";
		$selectStr .= " into OUTFILE " . "'" . CSVMYSQLPATH  . $ran . ".csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ";
		$selectStr .= " From `user` Inner Join owner ON `user`.id = owner.user_id Inner Join franchise ON owner.franchise_id = franchise.id Inner Join store ON franchise.id = store.franchise_id Inner Join address ON `user`.id = address.user_id where user_type = 'FRANCHISE_OWNER' and user.is_deleted = 0 and franchise.is_deleted = 0 and store.is_deleted = 0 and owner.is_deleted = 0";
		$selectStr .= " order by  state_id, city, store_name,lastname,firstname";

		$store->query($selectStr);
	}


	function findCount ( $Day, $Month, $Year, $Interval = '1 DAY')
	{
		$booking = DAO_CFactory::create("booking");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$selectStr = "Select count(*) as records";
		$selectStr .= " From booking Inner Join orders ON booking.order_id = orders.id Inner Join session ON booking.session_id = session.id  Inner Join store ON orders.store_id = store.id  Inner Join `user` ON booking.user_id = `user`.id Inner Join address ON `user`.id = address.user_id";
		$selectStr .=  " where session_start >= '"  . $current_date_sql . "' AND " ;
		$selectStr .=  " session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND " ;
		$selectStr .=   ' booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" ';
		$booking->query($selectStr);
		$rows = array();
		$booking->fetch();
		$rows = $booking->toArray();
		return ($rows['records']);
	}



	static function writeCSVHeader( $fileName) {

    $now       = gmdate('D, d M Y H:i:s') . ' GMT';
    $mime_type = 'text/x-csv';


    // send the write header statements to the browser
    header('Content-Type: ' . $mime_type);
    header('Expires: ' . $now);

    // lem9 & loic1: IE need specific headers
    $isIE = strstr( $_SERVER['HTTP_USER_AGENT'], 'MSIE' );
    if ( $isIE ) {
      header('Content-Disposition: inline; filename="' . $fileName .  '"');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
    } else {
      header('Content-Disposition: attachment; filename="' . $fileName .  '"');
      header('Pragma: no-cache');
    }

  }








	}

?>