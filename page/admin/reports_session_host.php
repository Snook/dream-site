<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once("phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

class page_admin_reports_session_host extends CPageAdminOnly
{

	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseOwner()
	{
		//$Owner = DAO_CFactory::create('user_to_franchise');
		//$Owner->user_id = CUser::getCurrentUser()->id;
		//if ( !$Owner->find(true) )
		//	throw new Exception('not a franchise owner, or store not found for current user');
		//$this->_franchise_id = $Owner->franchise_id;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{


		$store = null;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;
		$total_count = 0;
		$report_submitted = false;

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}


		$storeObj = DAO_CFactory::create('store');
        $storeObj->id = $store;
        $storeObj->find(true);


		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = false;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => 'button',
			CForm::value => 'Run Web Report'
		));


        //$month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$month_array = array(
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December'
		);

		$year = date("Y");
		$monthnum = date("n");
		$monthnum--;
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_001",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_002",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::default_value => $monthnum,
			CForm::name => 'month_popup'
		));

		$Form->DefaultValues['menu_or_calendar'] = 'menu';
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'cal'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'menu'
		));

        $Form->AddElement(array(CForm::type => CForm::Hidden, CForm::name => 'export', CForm::value => 'none'));

        if (isset ($_REQUEST["single_date"]))
		{
			$day_start = $_REQUEST["single_date"];
		}

		if (isset ($_REQUEST["range_day_start"]))
		{
			$range_day_start = $_REQUEST["range_day_start"];
		}
		if (isset ($_REQUEST["range_day_end"]))
		{
			$range_day_end = $_REQUEST["range_day_end"];
		}

		if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
		{
			$day = $_REQUEST["day"];
			$month = $_REQUEST["month"];
			$year = $_REQUEST["year"];
			$duration = $_REQUEST["duration"];
		}


        $export = false;

        if (isset($_POST['export']) && $_POST['export'] == 'xlsx')
        {
            $export = true;
        }


        if (isset($_REQUEST["report_submit"]) || $export)
		{

			if ($report_type_to_run == 1)
			{
				$implodedDateArray = explode("-", $day_start);
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';
			}
			else if ($report_type_to_run == 2)
			{
				$rangeReversed = false;
				$implodedDateArray = null;
				$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
				$diff++;  // always add one for SQL to work correctly
				if ($rangeReversed == true)
				{
					$implodedDateArray = explode("-", $range_day_end);
				}
				else
				{
					$implodedDateArray = explode("-", $range_day_start);
				}

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';
			}
			else if ($report_type_to_run == 3)
			{

				$month = $_REQUEST["month_popup"];
				$month++;
				$year = $_REQUEST["year_field_001"];

				if ($Form->value('menu_or_calendar') == 'menu')
				{
					// menu month
					$anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
					list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
					$start_date = strtotime($menu_start_date);
					$year = date("Y", $start_date);
					$month = date("n", $start_date);
					$day = date("j", $start_date);

					$duration = $interval . " DAY";
				}
				else
				{

					// process for a given month
					$day = "01";
					$duration = '1 MONTH';
				}
			}
			else if ($report_type_to_run == 4)
			{
				$spansMenu = true;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';
			}

			$rows = $this->findCustomers($store, $day, $month, $year, $duration);
			$numRows = count($rows);

			CLog::RecordReport("Session Host (Excel Export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration");

			if ($numRows)
			{
				$labels = array(
					"Session ID",
					"Session Start",
					"Event",
					"User Type",
					"User ID",
					"First Name",
					"Last Name",
					"Primary Email",
					"Primary Telephone",
                    "# orders / # RSVPs"
				);

				$columnDescs = array();

				$columnDescs['A'] = array(
					'align' => 'left',
					'width' => '9'
				); // session id
				$columnDescs['B'] = array(
					'align' => 'left',
					'type' => 'datetime',
					'width' => '25'
				); // session start
				$columnDescs['C'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // event type
				$columnDescs['D'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // user type
				$columnDescs['E'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // user id
				$columnDescs['F'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // first
				$columnDescs['G'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // last
				$columnDescs['H'] = array(
					'align' => 'left',
					'width' => 'auto'
				); // primary email
				$columnDescs['I'] = array(
					'align' => 'left',
					'width' => '14'
				); // telephone 1
                $columnDescs['J'] = array(
                    'align' => 'center',
                    'width' => '9'
                ); // telephone 1

                if ($export) {

                    $RangeStr =  $month . "_" . $day . "_" . $year . "_" . $duration;


                    $tpl->assign('col_descriptions', $columnDescs);
                    $tpl->assign('file_name', makeTitle("Session Hosts", $storeObj, $RangeStr));

                    $tpl->assign('labels', $labels);
                    $tpl->assign('rows', $rows);
                    $tpl->assign('rowcount', $numRows);

                    $_GET['export'] = 'xlsx';

                }
                else
                {

                    PHPExcel_Shared_String::setThousandsSeparator(",");

                    $overrideValues = array('links_target_new_tab' => true, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

                    list($css, $html) = writeExcelFile("Test", $labels, $rows, true, $titleRows, $columnDescs, false, $callbacks, $headers, true, false, $overrideValues);

                    echo "<html><head>";
                    echo $css;
                    echo "</head><body>";

                    echo $html;
                    echo "</body></html>";
                    exit;

                }
			}
			else
			{
                if ($export)
                {
                    $tpl->assign('empty_result', true);
                }
                else
                {
                    echo "<html><head>";
                    echo $css;
                    echo "</head><body>";
                    echo '<table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>';
                    echo "</body></html>";
                    exit;
                }
			}
		}

		$formArray = $Form->render();

		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Session Host Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function findCustomers($store_id, $Day, $Month, $Year, $Interval)
	{
		$session = DAO_CFactory::create("session_properties");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$selectStr = "SELECT 
			sp.session_id,
			s.session_start,
			IF(ISNULL(dream_taste_event_theme.title), 'Private Party', dream_taste_event_theme.title) AS title,
			u.user_type,
			sp.session_host,
			u.firstname,
			u.lastname,
			u.primary_email,
			u.telephone_1 ";
		$fromStr = " FROM session_properties AS sp
			INNER JOIN `session` AS s ON s.id = sp.session_id 
				AND s.store_id = '" . $store_id . "' 
				AND s.session_start >= '" . $current_date_sql . "' 
				AND s.session_start < DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) 
				AND s.is_deleted = 0
			INNER JOIN `user` AS u ON u.id = sp.session_host AND u.is_deleted = 0
			LEFT JOIN dream_taste_event_properties ON dream_taste_event_properties.id = sp.dream_taste_event_id
			LEFT JOIN dream_taste_event_theme ON dream_taste_event_theme.id = dream_taste_event_properties.dream_taste_event_theme ";
		$whereStr = " WHERE sp.session_host != 0 
			AND sp.is_deleted = 0 ";
		$orderStr = " ORDER BY s.session_start DESC ";


		$querystr = $selectStr . $fromStr . $whereStr . $orderStr;


    $outerQueryBegin = "select iq.*, count(distinct b.id) as num_orders, count(distinct sr.id) as num_RSVPs from ( ";
    $outerQueryEnd = " ) as iq
            left join booking b on b.session_id = iq.session_id and b.status = 'ACTIVE' and b.is_deleted = 0
            left join session_rsvp sr on sr.session_id = iq.session_id AND sr.upgrade_booking_id IS NULL and sr.is_deleted = 0
            group by iq.session_id";


        $querystr = $outerQueryBegin . $querystr . $outerQueryEnd;

		$session->query($querystr);
		$rows = array();
		$count = 0;

		while ($session->fetch())
		{

			$tarray = $session->toArray();

			$tarray['user_type'] = CUser::userTypeText($tarray['user_type']);
            $tarray['session_start'] = CTemplate::dateTimeFormat($tarray['session_start']);
            $tarray['session_start'] = "=HYPERLINK(\"" . HTTPS_BASE . "backoffice/main?session=" . $tarray['session_id'] . "\", \"{$tarray['session_start']}\")";
            $thisHost = $tarray['session_host'];
            $tarray['session_host'] = "=HYPERLINK(\"" . HTTPS_BASE. "backoffice/user_details?id=" . $tarray['session_host'] . "\", \"{$tarray['session_host']}\")";
            $tarray['primary_email'] = "=HYPERLINK(\"" . HTTPS_BASE . "backoffice/email?id=" . $thisHost . "\", \"{$tarray['primary_email']}\")";

            $tarray['firstname'] = htmlspecialchars($tarray['firstname']);
            $tarray['lastname'] = htmlspecialchars($tarray['lastname']);

            if ($tarray['title'] == "Friends Night Out")
            {
                $tarray['num_orders']  = $tarray['num_orders']  . "/" .   $tarray['num_RSVPs'];
            }

            $tarray = array_slice($tarray, 0, 10);

			$rows [$count++] = $tarray;
		}
		return ($rows);
	}

}

?>