<?php
/*
 * Created on June 11, 2012
 * project_name guestSearch
 *
 * Copyright 2012 DreamDinners
 * @author Carls
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/CDashboardReport.inc");

class processor_admin_processMetrics extends CPageProcessor
{
	
	function runFranchiseManager()
	{
		$this->processMetrics();
	}
	
	function runOpsLead()
	{
		$this->processMetrics();
	}
	
	function runFranchiseOwner()
	{
		$this->processMetrics();
	}

	function runEventCoordinator()
	{
		$this->processMetrics();
	}
	
	function runHomeOfficeStaff()
	{
		$this->processMetrics();
	}

	function runHomeOfficeManager()
	{
		$this->processMetrics();
	}

	function runSiteAdmin()
	{
		$this->processMetrics();
	}


	function getPerStoreLock($store_id)
	{
		$QObj = new DAO();
		$QObj->query("SELECT GET_LOCK('lock_for_$store_id', 5) as is_locked");
		$QObj->fetch();

		if ($QObj->is_locked == "1")
			return true;

		return false;
	}


	function releasePerStoreLock($store_id)
	{
		$QObj = new DAO();
		$QObj->query("SELECT RELEASE_LOCK('lock_for_$store_id')");
	}

	function waitForFreeLock($store_id)
	{

		sleep(10);
		$locked = true;

		while($locked)
		{
			$QObj = new DAO();
			$QObj->query("SELECT IS_FREE_LOCK('lock_for_$store_id') as is_free");
			$QObj->fetch();

			if ($QObj->is_free == "1")
			{
				$locked = false;
			}
			else
			{
				sleep(10);
			}
		}

	}


	function processMetricsLocal($store_id)
	{
		if (!empty($store_id))
		{
			if (CDashboardNew::testForUpdateRequired($store_id))
			{
				if ($this->getPerStoreLock($store_id))
				{// now processes both calendar and menu based months
					CDashboardNew::updateMetricsForStoreIfNeeded($store_id);
					$this->releasePerStoreLock($store_id);
					return true;
				}
				else
				{
					// wait for lock to release and just return sucess
					$this->waitForFreeLock($store_id);
					CDashboardNew::updateLastMetricsUpdateTimestamp($store_id);
					return true;
				}
			}
			else
			{
				return true;
			}

		}
		else
		{
			return false;
		}

	}



	function processMetrics()
	{

		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$store_id = isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : false;

		if (!empty($store_id))
		{
			if (CDashboardNew::testForUpdateRequired($store_id))
			{
				if ($this->getPerStoreLock($store_id))
				{// now processes both calendar and menu based months
					 CDashboardNew::updateMetricsForStoreIfNeeded($store_id);
					 echo json_encode(array('processor_success' => true, 'result_code' => 1, 'processor_message' => 'There metrics have been updated.'));
					$this->releasePerStoreLock($store_id);
				}
				else
				{
					// wait for lock to release and just return sucess
					$this->waitForFreeLock($store_id);
					CDashboardNew::updateLastMetricsUpdateTimestamp($store_id);
					echo json_encode(array('processor_success' => true, 'result_code' => 1, 'processor_message' => 'There metrics have been updated.'));
				}
			}
			else
			{
				echo json_encode(array('processor_success' => true, 'result_code' => 1, 'processor_message' => 'There metrics are up-tp-date.'));
			}

		}
		else
		{
		    echo json_encode(array('processor_success' => false, 'result_code' => 50001, 'processor_message' => 'There is a problem with the rankings. Please try again later.'));
		}

	}


}
?>