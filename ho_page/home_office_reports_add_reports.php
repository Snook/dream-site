<?php
require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');

class page_admin_home_office_reports_add_reports extends CPageAdminOnly {


	function runHomeOfficeManager()
    {
        $this->runSiteAdmin();
    }

    function runSiteAdmin()
    {

    }
}

?>