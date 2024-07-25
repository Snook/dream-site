<?php // page_admin_create_store.php
/**
 *
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');

class page_admin_home_office_reports_site_aggregate_samestore extends CPageAdminOnly {
    function runHomeOfficeStaff()
    {
        $this->runSiteAdmin();
    }
    function runHomeOfficeManager()
    {
        $this->runSiteAdmin();
    }
    function runSiteAdmin()
    {
        $report_submitted = false;
        $SessionReport = new CSessionReports();

        $tpl = CApp::instance()->template();
        $Form = new CForm();
        $Form->Repost = false;

        set_time_limit(18000);

        $report_array = array();

        $Form->AddElement(array (CForm::type => CForm::Submit,
                CForm::name => 'report_submit', CForm::value => 'Run Report'));

        $contestList = array("YES", "NO");

        $Form->AddElement(array(CForm::type => CForm::DropDown,
                CForm::onChangeSubmit => false,
                CForm::allowAllOption => true,
                CForm::options => $contestList,
                CForm::name => 'contest_type'));

        $submit = $Form->value('report_submit');
        if ($Form->value('report_submit')) {
            $filename = "";
            $contest_type = null;
            if (isset($_REQUEST['contest_type'])) $contest_type = $_REQUEST['contest_type'];

            if ($contest_type != null) {

                $dt = date('Y-m-d H:00:00');

                $sql = "select id from dreamcache.`aggregatecacherecord` order by cachedate desc limit 1";
                $objdate = DAO_CFactory::create("_dreamsite_version");
                $objdate->query($sql);
                $objdate->fetch();
                $proid = $objdate->id;

                if ($proid > 0) {


					$sql2 = "SELECT concat(`user`.`lastname`, ',' ,`user`.`firstname`) as RegionManager,`store_coach`.`store_id` FROM `store_coach` Inner Join `coach` ON `store_coach`.`coach_id` = `coach`.`id` Inner Join `user` ON `coach`.`user_id` = `user`.`id` where `store_coach`.`is_deleted` = 0 and `coach`.`active` = 1 and user.is_deleted = 0";
					$obj2 = DAO_CFactory::create("store_coach");
					$obj2->query($sql2);
					$coachrows = array();
					while ($obj2->fetch()) {
						$coachrows[$obj2->store_id] = $obj2->RegionManager;
		    				//echo "first looping: <br />";
					}



					$sql = "select id, `aggregatecache`.`aggregatecacherecordid`, `aggregatecache`.yearindicator, `aggregatecache`.`home_office_id`, " .
					" `aggregatecache`.`store_name`,`aggregatecache`.`city`,`aggregatecache`.`state_id`, " .
					" `aggregatecache`.`Gross_Sales_Less_Taxes`,`aggregatecache`.`Adjustments`, " .
					" `aggregatecache`.`Discounts`,`aggregatecache`.`Adjusted_Sales`, " .
					" `aggregatecache`.`grand_opening_date`,`aggregatecache`.`Details` from dreamcache.`aggregatecache` 
					where aggregatecacherecordid = $proid order by yearindicator asc";
				  $obj = DAO_CFactory::create("_dreamsite_version");

//echo "<br /><br />" . $sql . "<br /><br />";
//exit;
$obj->query($sql);
                    $rows = array();

					while ($obj->fetch()) {
						$arr = $obj->toArray();

		  //  echo "looping: <br />";
                  //      // find the coach
                        $manager = "";
                        if (isset($coachrows[$arr['id']]))
                        	$manager = $coachrows[$arr['id']];


						//unset($arr["Details"]);
						unset($arr["version_id"]);
						unset($arr["version_date"]);
						unset($arr["version_comments"]);


						$arr["RegionalManager"] = $manager;

                        if ($contest_type == 0)
                            $rows[$obj->id][$obj->yearindicator] = $arr;
                        else
                            $rows[] = $arr;
                    }

				//	print_r($rows);
				//	exit;

		//    echo "rows:" .count($rows) . "<br />";
                    $str = "Year, Home Office ID, Store Name, City, State, Gross Sales Less Taxes, Adjustments, Discounts, Adjusted Sales, Grand Opening Date, Details, Regional Manager";
                    $labels = explode(",", $str);

                    $curyear = date('Y');
                    if ($contest_type == 0) {
//echo "Contest type is 0<br />";
                        $temparray = $rows;
                        $rows = array();
                        foreach($temparray as $key => $element) {
                            $keys = array_keys($element);


                            if (count($element) == 1) {
                                echo "Contest type is 0<br />";
                                if ($keys[0] == $curyear) {
                                    $temp = array_slice($element[$keys[0]], 2, 13, true);  // Lori doesn't want 2008 records wihtout 2007 histroy
                                    $rows[] = $temp;
                                }
                            }
							else
							{
                                $prevyear = $curyear-1;
/*
								if (!empty($element[$curyear]['Gross_Sales_Less_Taxes']) && !empty($element[$prevyear]['Gross_Sales_Less_Taxes']))
								{

									 $temp1 = array_slice($element[$curyear], 2, 13, true);
									 $rows[] = $temp1;
									 $temp2 = array_slice($element[$prevyear], 2, 13, true);
									 $rows[] = $temp2;

								}
*/

								if ($element[$curyear]['Gross_Sales_Less_Taxes'] != 0 && $element[$prevyear]['Gross_Sales_Less_Taxes'] != 0)
								{

									 $temp1 = array_slice($element[$curyear], 2, 13, true);
									 $rows[] = $temp1;
									 $temp2 = array_slice($element[$prevyear], 2, 13, true);
									 $rows[] = $temp2;

								}




                            }

                        }
                    }


                    $tpl->assign('labels', $labels);
                    $tpl->assign('rows', $rows);
                    $tpl->assign('rowcount', count($rows));
                }
            }
        }

        $formArray = $Form->render();
        $tpl->assign('report_submitted', $report_submitted);

        $tpl->assign('report_type_to_run', 2);
        $tpl->assign('form_session_list', $formArray);
        $tpl->assign('display_navbar', true);
        $tpl->assign('page_title', 'Dream Dinners Same Store Aggregate');
        if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', true);
    }
}

?>