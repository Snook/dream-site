<?php
require_once("C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace12\\DreamSite_4_4_5\\includes\\Config.inc");
require_once 'includes/CDashboardReportMenuBased.inc';

list($startDate, $interval)  = CMenu::getMenuStartandInterval(false,  '2016-06-01');

//$result = CDashboardMenuBased::getGrossSales(244, $startDate, $interval');

CDashboardMenuBased::updateGuestMetrics(244, $startDate, $interval);