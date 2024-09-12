<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_my_meals extends CPageAdminOnly
{

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	/**
	 * @throws Exception
	 */
	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = false;

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Run Report'
		));

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

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_001",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_popup'
		));

		if ($Form->value('report_submit') || (isset($_GET['export']) && $_GET['export'] === "xlsx"))
		{
			// process for a given month
			$month = $_REQUEST["month_popup"];
			$month++;
			$year = $_REQUEST["year_field_001"];

			$title_range = "Month of " . date("F", mktime(0, 0, 0, $month, 1, $year));

			$tpl->assign('report_title_range', $title_range);

			$menuObj = DAO_CFactory::create('menu');

			if ($menuObj->findForMonthAndYear($month, $year))
			{
				$rows = array();

				$labels = array(
					"User Name",
					"Store Name",
					"Recipe ID",
					"Menu Item Name",
					"Item Type",
					"Date Rated",
					"Rating",
					"Would Order Again",
					"Customizations"
				);

				$columnDescs = array();

				$columnDescs['A'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['B'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['C'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['D'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['E'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['F'] = array(
					'align' => 'left',
					'width' => 'auto',
					'type' => 'datetime'
				);
				$columnDescs['G'] = array(
					'align' => 'left',
					'width' => '6'
				);
				$columnDescs['H'] = array(
					'align' => 'left',
					'width' => 'auto',
					'decor' => 'yes_no_condform'
				);
				$columnDescs['I'] = array(
					'align' => 'left',
					'width' => 'auto',
					'decor' => 'yes_no_condform'
				);

				$menuObj->fetch();
				$menu_id = $menuObj->id;

				$filterNonVIP = false;
				if (!empty($_POST['vip_only']))
				{
					$filterNonVIP = true;
				}

				$surveyObj = DAO_CFactory::create('food_survey');
				$surveyObj->query("select b.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, o.opted_to_customize_recipes, oi.menu_item_id, mi.recipe_id, mi.menu_item_name,
										fs.rating, fs.timestamp_created, fs.would_order_again as favorite, if (mi.menu_item_category_id = 9, 'FT', 'CORE') as cat, max(puh.total_points) as points_total,	
       									(select store_name from store sto where sto.id = fs.store_id) as store_name
 											from booking b
								join session s on s.id = b.session_id and s.menu_id = $menu_id
								join user u on u.id = b.user_id
 								join orders o on o.id = b.order_id and o.is_deleted = 0
								join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
								join menu_item mi on mi.id = oi.menu_item_id
								join food_survey fs on fs.recipe_id = mi.recipe_id and fs.user_id = b.user_id and fs.is_active = 1 and fs.is_deleted = 0
								left join points_user_history puh on puh.user_id = u.id
								where b.`status` = 'ACTIVE'
								group by b.user_id, mi.recipe_id
								order by b.user_id");

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
						$would_order_again = "Unanswered";

						if ($surveyObj->favorite === '1')
						{
							$would_order_again = 'YES';
						}
						else if ($surveyObj->favorite === '2')
						{
							$would_order_again = 'NO';
						}

						$order_has_customizations = "";
						if ($surveyObj->opted_to_customize_recipes)
						{
							$order_has_customizations = "Yes";
						}

						$storeName = '';
						if (!is_null($surveyObj->store_name))
						{
							$storeName = $surveyObj->store_name;
						}

						if (!$filterNonVIP || $surveyObj->points_total > 4999)
						{
							$rows[] = array(
								$surveyObj->name,
								$storeName,
								$surveyObj->recipe_id,
								$surveyObj->menu_item_name,
								$surveyObj->cat,
								$surveyObj->timestamp_created,
								$surveyObj->rating,
								$would_order_again,
								$order_has_customizations
							);
						}
					}

					$tpl->assign('labels', $labels);
					$tpl->assign('rows', $rows);
					$tpl->assign('rowcount', count($rows));
					$tpl->assign('col_descriptions', $columnDescs);
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
		$tpl->assign('page_title', 'My Meals Report');
	}
}