<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CTaskRetryQueue.php");

require_once("CLog.inc");

if (defined("DISABLE_CRON") && DISABLE_CRON)
{
    CLog::Record("CRON: process retry tasks called but cron is disabled");
    exit;
}

try {

    $retryTasks = DAO_CFactory::create('task_retry_queue');
    $retryTasks->query("select id from task_retry_queue where completion_code = 0 or completion_code = 3");

    $count = 0;
    while($retryTasks->fetch())
    {
        CTaskRetryQueue::process($retryTasks->id);
        $count++;
    }

    CLog::RecordNew(CLog::DEBUG, "Processed $count Delayed Tasks");

} catch (exception $e) {
	CLog::RecordException($e);
}

?>