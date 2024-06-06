<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("CDashboardReport.inc");

restore_error_handler();

try
{
	set_time_limit(100000);

	$instance = new CDashboardReport('2007-10-31 23:59:59');
	if (!empty($instance))
	{
		if (!empty($instance->m_error_array) && count($instance->m_error_array) > 0)
		{
			foreach ($instance->m_error_array as $element)
			{
				CLog::RecordCronTask(0, $element[0], CLog::DASHBOARDCACHING, $element[1] . ' ' . $element[2]);
			}
		}
		else
		{
			CLog::RecordCronTask(0, CLog::SUCCESS, CLog::DASHBOARDCACHING, 'No Errors Occurred while loading Dashboard Data');
		}
	}
	else
	{
		CLog::RecordCronTask(0, CLog::ERROR, CLog::DASHBOARDCACHING, 'Error, instance of dashboard report did not create.');
	}
}
catch (exception $e)
{

	CLog::RecordException($e);
}

?>