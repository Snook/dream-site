<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("CLog.inc");
require_once('includes/CDashboardReport.inc');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


CDashboardNew::updateMetricsForStoreIfNeeded(store_id: 80, forceUpdate: true, testDate: false);

echo 'done';