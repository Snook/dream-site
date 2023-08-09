<?php
require_once("../includes/Config.inc");
require_once 'includes/CDashboardReportMenuBased.inc';

list($startDate, $interval)  = CMenu::getMenuStartandInterval(false,  '2016-06-01');

//$result = CDashboardMenuBased::getGrossSales(244, $startDate, $interval');

CDashboardMenuBased::updateGuestMetrics(244, $startDate, $interval);