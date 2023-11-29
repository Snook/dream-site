<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("CDashboardReport.inc");

restore_error_handler();

	try {

		if (defined("DISABLE_CRON") && DISABLE_CRON)
		{
			CLog::Record("CRON: process_dashboard_cache called but cron is disabled");
			exit;
		}


			set_time_limit(100000);

			$instance = new CDashboardReport();
			if (!empty($instance)) {
				if (!empty($instance->m_error_array) && count($instance->m_error_array) > 0) {
					foreach($instance->m_error_array as $element){
						CLog::RecordCronTask(0, $element[0], CLog::DASHBOARDCACHING,$element[1] . ' ' . $element[2]);
					}
				}
				else {
					CLog::RecordCronTask(0,CLog::SUCCESS, CLog::DASHBOARDCACHING,'No Errors Occurred while loading Dashboard Data');
				}
			}
			else {
				CLog::RecordCronTask(0,CLog::ERROR, CLog::DASHBOARDCACHING,'Error, instance of dashboard report did not create.');
			}


	} catch (exception $e) {

		CLog::RecordException($e);
	}

?>