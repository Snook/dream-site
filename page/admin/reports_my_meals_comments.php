<?php

/**
 * @author Carl Samuelson
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');

 class page_admin_reports_my_meals_comments extends CPageAdminOnly {

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }


	function runHomeOfficeManager(){
		$this->runSiteAdmin();
	}

 	function runSiteAdmin() {

		$tpl = CApp::instance()->template();

		ini_set('memory_limit', '1000M');

		$Form = new CForm();
		$Form->Repost = FALSE;

		$total_count = 0;

		$month = 0;
		$year = 0;

		$report_array = array();

		$Form->AddElement(array (CForm::type => CForm::Submit,
 		CForm::name => 'report_submit', CForm::value => 'Run Report'));

		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");

		$Form->AddElement(array(CForm::type=> CForm::Text,
						CForm::name => "year_field_001",
						CForm::required => true,
						CForm::default_value => $year,
						CForm::length => 6));


		$Form->AddElement(array(CForm::type=> CForm::DropDown,
					CForm::onChangeSubmit => false,
					CForm::allowAllOption => false,
					CForm::options => $month_array,
					CForm::name => 'month_popup'));


		$title_range = "";


		if ( $Form->value('report_submit') || (isset($_GET['export']) && $_GET['export'] === "xlsx"))
		{
			// process for a given month
			$day = "01";
			$month = $_REQUEST["month_popup"];
			$month++;
			$duration = '1 MONTH';
			$year = $_REQUEST["year_field_001"];

			$title_range = "Month of " . date("F", mktime(0,0,0,$month, 1, $year));

			$tpl->assign('report_title_range', $title_range);

			$menuObj = DAO_CFactory::create('menu');

			if ($menuObj->findForMonthAndYear($month, $year))
			{

				$rows = array();

				$labels = array("User Name", "Store Name","Recipe ID", "Menu Item Name", "Comment", "Date Commented",  "Item Type", "Rating", "WOA", "Customizations", "Date Rated");
				$columnDescs = array();

				$columnDescs['A'] = array('align' => 'left', 'width' => '15');
				$columnDescs['B'] = array('align' => 'left', 'width' => '15');
				$columnDescs['C'] = array('align' => 'left', 'width' => '6');
				$columnDescs['D'] = array('align' => 'left', 'width' => '32');
				$columnDescs['E'] = array('align' => 'left', 'width' => '50', 'wrap' => true);
				$columnDescs['F'] = array('align' => 'left', 'width' => 'auto', 'type' => 'datetime');
				$columnDescs['G'] = array('align' => 'left', 'width' => '6');
				$columnDescs['H'] = array('align' => 'left', 'width' => 'auto');
				$columnDescs['I'] = array('align' => 'left', 'width' => '6', 'decor' => 'yes_no_condform');
				$columnDescs['J'] = array('align' => 'left', 'width' => '6', 'decor' => 'yes_no_condform');
				$columnDescs['K'] = array('align' => 'left', 'width' => 'auto', 'type' => 'datetime');

				$menuObj->fetch();
				$menu_id = $menuObj->id;


				$surveyObj = DAO_CFactory::create('food_survey_comments');


				$surveyObj->query("select iq.*, fs.rating, 
       				if (fs.would_order_again = 1, 'yes', if (fs.would_order_again = 2, 'no', 'unanswered')) as would_order_again, 
       				fs.timestamp_created as rated_on from (
					select b.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, 
					if (o.opted_to_customize_recipes = 1, 'yes', '') as opted_to_customize_recipes, 
					oi.menu_item_id, mi.recipe_id, mi.menu_item_name,
					fsc.comment, fsc.timestamp_created,  if (mi.menu_item_category_id = 9, 'FT', if(mi.is_store_special = 1, 'EFL', 'CORE')) as cat,
					(select store_name from store sto where sto.id = fs.store_id) as store_name
					from booking b
					join session s on s.id = b.session_id and s.menu_id = $menu_id
					join user u on u.id = b.user_id
					join orders o on o.id = b.order_id and o.is_deleted = 0
					join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
					join menu_item mi on mi.id = oi.menu_item_id
					join food_survey_comments fsc on fsc.recipe_id = mi.recipe_id and fsc.user_id = b.user_id and fsc.is_active = 1 and fsc.is_deleted = 0
					join food_survey fs on fs.recipe_id = mi.recipe_id and fs.user_id = b.user_id and fs.is_active = 1 and fs.is_deleted = 0
					where b.`status` = 'ACTIVE'
					group by b.user_id, mi.recipe_id
					order by b.user_id) as iq
					left join food_survey fs on fs.user_id = iq.user_id and fs.recipe_id = iq.recipe_id  and fs.is_active = 1 and fs.is_deleted = 0");

				if ($surveyObj->N == 0)
				{
					unset($_REQUEST['export']);
					unset($_GET['export']);

					$tpl->setErrorMsg("There were no results for this menu.");
				}
				else
				{
					while ($surveyObj->fetch())
					{
						$storeName = '';
						if(!is_null($surveyObj->store_name)){
							$storeName = $surveyObj->store_name;
						}
						$rows[] = array($surveyObj->name,
										$storeName,
										$surveyObj->recipe_id,
										$surveyObj->menu_item_name,
										$surveyObj->comment,
										$surveyObj->timestamp_created,
										$surveyObj->cat,
										$surveyObj->rating,
										$surveyObj->would_order_again,
										$surveyObj->opted_to_customize_recipes,
										$surveyObj->rated_on);
					}

					$tpl->assign('labels', $labels);
					$tpl->assign('rows', $rows);
					$tpl->assign('rowcount', count($rows));
					$tpl->assign('col_descriptions', $columnDescs );
				}
			}
			else
			{
				unset($_REQUEST['export']);
				unset($_GET['export']);

				$tpl->setErrorMsg("The menu was not found.");
			}

		}

		$formArray = $Form->render();
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','My Meals Report');
	}






}

?>