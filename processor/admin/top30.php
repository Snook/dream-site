<?php
/*
 * Created on June 11, 2012
 * project_name guestSearch
 *
 * Copyright 2012 DreamDinners
 * @author Carls
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_top30 extends CPageProcessor
{

	private static $currentStore = null;


	function runFranchiseManager()
	{
		$this->showTop30ForMetric();
	}
	
	function runOpsLead()
	{
		$this->showTop30ForMetric();
	}
	
	function runFranchiseOwner()
	{
		$this->showTop30ForMetric();
	}
	function runEventCoordinator()
	{
		$this->showTop30ForMetric();
	}
	
	function runHomeOfficeStaff()
	{
		$this->showTop30ForMetric();
	}

	function runHomeOfficeManager()
	{
		$this->showTop30ForMetric();
	}

	function runSiteAdmin()
	{
		$this->showTop30ForMetric();
	}


	static $displayNameMap = array('agr' => "Adjusted Gross Revenue",
								   'agr_percent_change' => "Gross Revenue by % increase",
								   'in_store_signup' => "In Store Sign Up rate",
									'guest_visits' => "# Guest Visits",
								    'new_guest_visits' => "# New Guest Visits",
									'avg_visits_per_session' => "Average Guest Visits per Session",
									'avg_ticket' => "Average Ticket",
									'addon_sales' => "Add on Sales",
									'servings_per_guest' => "Servings per Guest",
									'converted_guests' => "# Converted Guests");

	static $displayPrefixMap = array('agr' => "$",
			'agr_percent_change' => "",
			'in_store_signup' => "",
			 'guest_visits' => "",
			 'new_guest_visits' => "",
			'avg_visits_per_session' => "",
			'avg_ticket' => "$",
			'addon_sales' => "$",
			'servings_per_guest' => "",
			'converted_guests' => "");

	static $displayPostfixMap = array('agr' => "",
			'agr_percent_change' => "%",
			'in_store_signup' => "%",
			'guest_visits' => "",
		  'new_guest_visits' => "",
		  'avg_visits_per_session' => "",
			'avg_ticket' => "",
			'addon_sales' => "",
			'servings_per_guest' => "",
			'converted_guests' => "%");


	static function getMetricDisplayName($metric)
	{

		if (isset(self::$displayNameMap[$metric]))
			return self::$displayNameMap[$metric];

		return $metric;

	}

	static function getMetricPrefix($metric)
	{

		if (isset(self::$displayPrefixMap[$metric]))
			return self::$displayPrefixMap[$metric];

		return "";

	}

	static function getMetricPostfix($metric)
	{

		if (isset(self::$displayPostfixMap[$metric]))
			return self::$displayPostfixMap[$metric];

		return "";

	}




	function showTop30ForMetric()
	{

		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$metric_type = isset($_REQUEST['metric_type']) ? $_REQUEST['metric_type'] : false;
		$month = isset($_REQUEST['month']) ? $_REQUEST['month'] : false;
		
		
		$isMenuBased = (isset($_REQUEST['report_type']) && $_REQUEST['report_type'] == "menu_based") ? true : false;

		if (!empty($metric_type) && !empty($month) )
		{
			$tableName = "dashboard_metrics_rankings";
			if ($isMenuBased)
			{
				$tableName = "dashboard_metrics_rankings_by_menu";
			}
			
			
			$printArray = array();

			$rankingsObj = DAO_CFactory::create($tableName);

			$rankingsObj->query("select s.store_name, s.city, s.state_id, dmr.store_id, dmr.$metric_type as value, dmr.{$metric_type}_rank as rank from $tableName  dmr
			join store s on s.id = dmr.store_id and s.active = 1 where dmr.{$metric_type}_rank < 31 and dmr.date = '$month' order by dmr.{$metric_type}_rank asc");
			

			$count = 0;
			$lastRank = 0;
			while ($rankingsObj->fetch())
			{
				$count++;
				// keep going after 30 so long as the rank is the same as the last
				if ($count > 31 && $lastRank != $rankingsObj->rank)
					break;


				$rankingValue = $rankingsObj->value;

				if ($metric_type == "agr" || $metric_type == "addon_sales")
				{
					$rankingValue = number_format($rankingValue, 2);
				}


				$printArray[$rankingsObj->store_id] = array('value' => $rankingValue, 'rank' => $rankingsObj->rank,
												'store_name' => $rankingsObj->store_name, 'city' => $rankingsObj->city, 'state' => $rankingsObj->state_id);


				$lastRank = $rankingsObj->rank;

			}

			$tpl = new CTemplate();
			$tpl->assign('printArray',$printArray);
			$tpl->assign('metric_display_name', self::getMetricDisplayName($metric_type));
			$tpl->assign('preVal', self::getMetricPrefix($metric_type));
			$tpl->assign('postVal', self::getMetricPostfix($metric_type));
			$html = $tpl->fetch('admin/dashboard_rankings_results.tpl.php');



			echo json_encode(array('processor_success' => true, 'result_code' => 1,  'data' => $html));

		}
		else
		{
		    echo json_encode(array('processor_success' => false, 'result_code' => 50001, 'processor_message' => 'There is a problem with the rankings. Please try again later.'));
		}

	}


}
?>