<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
//require_once("C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace12\\DreamSite\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("processor/admin/status.php");
require_once('CMail.inc');

require_once("CLog.inc");

//
try {

        $processor = new processor_admin_status();
        list($html, $hadFailures) = $processor->doCronStatusCheck(true);

        if ($hadFailures)
        {

            $Mail = new CMail();

            $data = array('contents' => $html);

            $contentsHtml = CMail::mailMerge('cron_job_failures.html.php', $data);

            $contentsHtml = str_replace("><", ">\n<", $contentsHtml);


            $Mail->send(null,
                null,
                "Dream Dinners Technical Staff",
                "evan.lee@dreamdinners.com,josh.thayer@dreamdinners.com,ryan.snook@dreamdinners.com",
                "Cron Job Failures",
                $contentsHtml,
                null,
                '',
                '',
                0,
                'cron_job_failures');


            $Mail->send(null,
                null,
                "Dream Dinners Technical Staff",
                "4257600812@vtext.com,4254205526@txt.att.net",
                "Cron Job Failures",
                null,
                "Cron failures occurred. Please check your email for details.",
                '',
                '',
                0,
                'cron_job_failures');


        }

} catch (exception $e) {
	CLog::RecordException($e);
}

?>
