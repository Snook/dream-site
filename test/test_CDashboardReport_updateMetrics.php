<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("CLog.inc");
require_once('includes/CDashboardReport.inc');



CDashboardNew::updateMetricsForStoreIfNeeded(store_id: 80, forceUpdate: true, testDate: false);

echo 'done';