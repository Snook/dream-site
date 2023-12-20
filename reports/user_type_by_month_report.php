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

try
{
	$path = "/DreamReports/user_type_by_month.csv";
	// $path = "C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace\\DreamSite\\Recent_Scripts\\user_type_by_month.csv";
	$fh = fopen($path, 'w');

	$stores = new DAO();
	$stores->query("select id, home_office_id, store_name, city, state_id, is_corporate_owned from store where active = 1 and is_deleted = 0 order by id asc");
	$labels = array(
		"ID",
		"Home Office Id",
		"Store Name",
		"City",
		"State",
	);

	$output = array();
	$months = array(
		209,
		210,
		211,
		212,
		213,
		214,
		215,
		216,
		217,
		218,
		219,
		220,
		221
	);
	$dataTemplate = array(
		'new' => 0,
		'reac' => 0,
		'existing' => 0,
		'first_full' => 0
	);

	while ($stores->fetch())
	{
		$storeArray[$stores->id] = DAO::getCompressedArrayFromDAO($stores);
		$output[$stores->id] = array(
			$stores->id,
			$stores->home_office_id,
			$stores->store_name,
			$stores->city,
			$stores->state_id
		);

		foreach ($months as $menu_id)
		{
			$output[$stores->id][$menu_id] = $dataTemplate;
		}
	}

	foreach ($months as $menu_id)
	{
		echo "Starting menu " . $menu_id . "\r\n";

		$menuObj = new DAO();
		$menuObj->query("select menu_name from menu where id = $menu_id");
		$menuObj->fetch();

		$labels[] = $menuObj->menu_name . " New";
		$labels[] = $menuObj->menu_name . " Reac";
		$labels[] = $menuObj->menu_name . " Ex";
		$labels[] = $menuObj->menu_name . " 1st full";

		$getter = new DAO();
		$getter->query("select s.store_id
                            , count( distinct if(od.user_state = 'NEW',  od.order_id, null)) as new_count
                            , count(distinct if(od.user_state = 'REACQUIRED',  od.order_id, null)) as reac_count
                            , count(distinct if(od.user_state = 'EXISTING',  od.order_id, null)) as ex_count
                            from orders_digest od
                            join booking b on b.order_id = od.order_id and b.`status` = 'ACTIVE'
                            join session s on s.id = b.session_id and s.menu_id = $menu_id
                            join store st on st.id = s.store_id and st.active = 1
                            group by s.store_id, s.menu_id");

		while ($getter->fetch())
		{
			$output[$getter->store_id][$menu_id]['new'] = $getter->new_count;
			$output[$getter->store_id][$menu_id]['reac'] = $getter->reac_count;
			$output[$getter->store_id][$menu_id]['existing'] = $getter->ex_count;
		}

		$getter2 = new DAO();
		$getter2->query("select oq2.store_id, count(distinct oq2.user_id) as first from (
            select oq.* from (
                select iq.*, MIN(s2.menu_id) as boo from (
                    select distinct b.user_id, s.store_id
                    from orders_digest od
                    join booking b on b.order_id = od.order_id and b.`status` = 'ACTIVE'
                    join session s on s.id = b.session_id and s.menu_id = $menu_id) as iq
            
                join booking b on b.user_id = iq.user_id and b.status = 'ACTIVE'
                join orders o on o.id = b.order_id and o.servings_total_count > 35
                join session s2 on s2.id = b.session_id 
                group by iq.user_id) as oq
            where oq.boo = $menu_id) as oq2
            group by oq2.store_id");

		while ($getter2->fetch())
		{
			$output[$getter2->store_id][$menu_id]['first_full'] = $getter2->first;
		}
	}

	$length = fputs($fh, implode(",", $labels) . "\r\n");

	foreach ($output as $store => $data)
	{
		foreach ($data as $menu => $dater)
		{
			if (is_array($dater))
			{
				$length = fputs($fh, implode(",", $dater) . ",");
			}
			else
			{
				$length = fputs($fh, $dater . ",");
			}
		}

		$length = fputs($fh, "\r\n");
	}

	fclose($fh);
}
catch (exception $e)
{
	CLog::RecordException($e);
}

?>