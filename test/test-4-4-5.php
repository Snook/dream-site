<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/CDashboardReportMenuBased.inc';

list($startDate, $interval)  = CMenu::getMenuStartandInterval(false,  '2024-03-01');

//$result = CDashboardMenuBased::getGrossSales(244, $startDate, $interval');

CDashboardMenuBased::updateGuestMetrics(313, $startDate, $startDate, $interval);