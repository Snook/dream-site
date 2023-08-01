<?php //
/**
 * @author Carl Samuelson
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/CDashboardReportWeekBased.inc');
 require_once('phplib/PHPExcel/PHPExcel.php');
 require_once('ExcelExport.inc');


 class page_admin_reports_menu_planning extends CPageAdminOnly {



	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

     function runHomeOfficeManager()
     {
         $this->runReport();
     }

     function runSiteAdmin()
     {
         $this->runReport();
     }

 	function runReport()
 	{
		$tpl = CApp::instance()->template();


		$year = date("Y");

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

		$Form = new CForm();
		$Form->Repost = TRUE;


		$menuSetter = CMenu::getMenuByDate(date("Y-m-d"));
		$defaultMonth = date("n", strtotime($menuSetter['menu_start']));
		$defaultYear = date("Y", strtotime($menuSetter['menu_start']));

		$Form->DefaultValues['year'] = $defaultYear;
		$Form->DefaultValues['month'] = $defaultMonth - 1;

		$Form->AddElement(array(
		    CForm::type => CForm::Text,
		    CForm::name => "year",
		    CForm::required => true,
		    CForm::default_value => $year,
		    CForm::length => 6
		));

		$year = $Form->value('year');

		$Form->AddElement(array(
		    CForm::type => CForm::DropDown,
		    CForm::onChangeSubmit => false,
		    CForm::allowAllOption => false,
		    CForm::options => $month_array,
		    CForm::name => 'month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'export_report',
		   CForm::value => 'Export Report'
		));

		$month = $Form->value('month');
		$month++;

		$Form->AddElement(array(CForm::type => CForm::Hidden, CForm::name => 'export', CForm::value => 'none'));

		$export = true;

		if ($Form->value('export_report'))
		{
			$template = array('recipe_id' => 0,
								'recipe_name' => "",
								'class' => "",
								'ratings_by_menu_purchasers' => 0,
								'woa_by_purchasers' => 0,
								'wnoa_by_purchasers' => 0,
								'global_ratings' => 0,
								'global_woa' => 0,
								'global_wnoa' => 0,
							    'num_items_sold_2_serv' => 0,
								'num_items_sold_3_serv' => 0,
							    'num_items_sold_4_serv' => 0,
								'num_items_sold_6_serv' => 0,
								'total_servings_sold' => 0,
								'menu_mix' => 0,
								'ratings_since' => 0,
								'woa_since' => 0,
								'wnoa_since' => 0,
								'total_revenue' => 0);

			$rows = array();

			$requestedMonthTS = mktime(0,0,0,$month, 1,  $year);
			$anchorDate = date("Y-m-01", $requestedMonthTS);
			$menuInfo = CMenu::getMenuByAnchorDate($anchorDate);
			$menu_id = $menuInfo['id'];
			$monthShortName = date("M", strtotime($anchorDate));


            $curYear = date("Y");
            $curMonth = date("m");
            $curMonth = $curYear . "-" . $curMonth . "-01 00:00:00";
            $curDateDT = new DateTime($curMonth);

			$menuMain = new DAO();
			$menuMain->query("select iq.cat as Category, iq.recipe_id, iq.menu_item_name as item_name, avg(iq.rating) as average_rating, count(if (would_order_again = 1, would_order_again, null)) as would_order_again,
									count(if (would_order_again = 2, would_order_again, null)) as would_NOT_order_again from
											(select b.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, oi.menu_item_id, mi.recipe_id, mi.menu_item_name,
												fs.rating, fs.timestamp_created, fs.would_order_again, if (mi.menu_item_category_id = 9, 'FT', 'CORE') as cat
													from booking b
												join session s on s.id = b.session_id and s.menu_id = $menu_id
												join user u on u.id = b.user_id
												join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
												join menu_item mi on mi.id = oi.menu_item_id and isnull(mi.copied_from) and mi.is_store_special = 0
												join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and mmi.menu_id = $menu_id and isnull(mmi.store_id) and mmi.is_deleted = 0
												join food_survey fs on fs.recipe_id = mi.recipe_id and fs.user_id = b.user_id and fs.is_active = 1 and fs.is_deleted = 0 
												where b.`status` = 'ACTIVE'
												group by b.user_id, mi.recipe_id
												order by b.user_id) as iq
										group by iq.recipe_id
										order by iq.recipe_id;");

			$recipeArr = array();

			while ($menuMain->fetch())
			{
				$rows[$menuMain->recipe_id] = $template;
				$rows[$menuMain->recipe_id]['recipe_id'] = $menuMain->recipe_id;
				$rows[$menuMain->recipe_id]['recipe_name'] = $menuMain->item_name;
				$rows[$menuMain->recipe_id]['class'] = $menuMain->Category;
				$rows[$menuMain->recipe_id]['ratings_by_menu_purchasers'] = $menuMain->average_rating;
				$rows[$menuMain->recipe_id]['woa_by_purchasers'] = $menuMain->would_order_again;
				$rows[$menuMain->recipe_id]['wnoa_by_purchasers'] = $menuMain->would_NOT_order_again;
				$recipeArr[] = $menuMain->recipe_id;
			}

			$recipeStr = implode(",", $recipeArr);

			$globalRatings = new DAO();
			$globalRatings->query("select iq.*, count(if (fsf2.would_order_again = 1, fsf2.would_order_again, null)) as would_order_again_global, count(if (fsf2.would_order_again = 2, fsf2.would_order_again, null)) as would_NOT_order_again_global  from
						(select fs.recipe_id, avg(fs.rating) as global_ratings from food_survey fs
							where fs.is_active = 1 and fs.is_deleted = 0 and fs.recipe_id IN($recipeStr)						
							group by fs.recipe_id) as iq
						left join food_survey fsf2 on fsf2.recipe_id = iq.recipe_id and fsf2.is_active = 1 and fsf2.is_deleted = 0
						group by iq.recipe_id
						order by iq.recipe_id");

			while($globalRatings->fetch())
			{
				if (isset($rows[$globalRatings->recipe_id]))
				{
					$rows[$globalRatings->recipe_id]['global_ratings'] = $globalRatings->global_ratings;
					$rows[$globalRatings->recipe_id]['global_woa'] = $globalRatings->would_order_again_global;
					$rows[$globalRatings->recipe_id]['global_wnoa'] = $globalRatings->would_NOT_order_again_global;
				}
			}



			$rev_and_mix = new DAO();
			$rev_and_mix->query("select iq.*,  sum(if(iq.pricing_type = 'TWO', oi.item_count, null)) as two_serv_items_sold,sum(if(iq.pricing_type = 'FOUR', oi.item_count, null)) as four_serv_items_sold,sum(if(iq.pricing_type = 'HALF', oi.item_count, null)) as three_serv_items_sold, sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) as six_serv_items_sold, 
							if(iq.menu_item_category_id < 9, (sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) * 6) + (sum(if(iq.pricing_type = 'HALF', oi.item_count, 0)) * 3) + (sum(if(iq.pricing_type = 'TWO', oi.item_count, 0)) * 2) + (sum(if(iq.pricing_type = 'FOUR', oi.item_count, 0)) * 4), null) as total_servings,
							sum(oi.sub_total) as totes_rev
							 from (
							select mi.id, mi.entree_id, mi.menu_item_name, mi.recipe_id, mi.pricing_type, mi.menu_item_category_id, mi.copied_from from menu_to_menu_item mmi
							join menu_item mi on mi.id = mmi.menu_item_id
							where mmi.menu_id = $menu_id and isnull(mmi.store_id) and mmi.is_deleted = 0 and isnull(mi.copied_from) and mi.menu_item_category_id < 10 and mi.is_store_special = 0
							order by mmi.menu_order_value) as iq 
							join order_item oi on oi.menu_item_id = iq.id and oi.is_deleted = 0 and isnull(oi.parent_menu_item_id)
							join booking b on b.order_id = oi.order_id and b.status = 'ACTIVE'
							group by iq.entree_id
							order by iq.recipe_id;");

			$totalCoreServings = 0;
			while ($rev_and_mix->fetch())
			{
				if (isset($rows[$rev_and_mix->recipe_id]))
				{
					$totalCoreServings += $rev_and_mix->total_servings;
				}
			}

			$rev_and_mix2 = new DAO();
			$rev_and_mix2->query("select iq.*, sum(if( iq.pricing_type = 'TWO', oi.item_count, null )) AS two_serv_items_sold, sum(if(iq.pricing_type = 'HALF', oi.item_count, null)) as three_serv_items_sold, sum(if( iq.pricing_type = 'FOUR', oi.item_count, null )) AS four_serv_items_sold, sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) as six_serv_items_sold, 
							if(iq.menu_item_category_id < 9, (sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) * 6) + (sum(if(iq.pricing_type = 'HALF', oi.item_count, 0)) * 3) + (sum(if(iq.pricing_type = 'TWO', oi.item_count, 0)) * 2) + (sum(if(iq.pricing_type = 'FOUR', oi.item_count, 0)) * 4), null) as total_servings,										if (iq.menu_item_category_id < 9, ((sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) * 6) + (sum(if(iq.pricing_type = 'HALF', oi.item_count, 0)) * 3)) / 311850, null) as menu_mix,
							if (iq.menu_item_category_id < 9, ((sum(if(iq.pricing_type = 'FULL', oi.item_count, null)) * 6) + (sum(if(iq.pricing_type = 'HALF', oi.item_count, 0)) * 3) + (sum(if(iq.pricing_type = 'TWO', oi.item_count, 0)) * 2) + (sum(if(iq.pricing_type = 'FOUR', oi.item_count, 0)) * 4)) / $totalCoreServings, null) as menu_mix,
							sum(oi.sub_total) as totes_rev
							 from (
							select mi.id, mi.entree_id, mi.menu_item_name, mi.recipe_id, mi.pricing_type, mi.menu_item_category_id, mi.copied_from from menu_to_menu_item mmi
							join menu_item mi on mi.id = mmi.menu_item_id
							where mmi.menu_id = $menu_id and isnull(mmi.store_id) and mmi.is_deleted = 0 and isnull(mi.copied_from) and mi.menu_item_category_id < 10 and mi.is_store_special = 0
							order by mmi.menu_order_value) as iq 
							join order_item oi on oi.menu_item_id = iq.id and oi.is_deleted = 0 and isnull(oi.parent_menu_item_id)
							join booking b on b.order_id = oi.order_id and b.status = 'ACTIVE'
							group by iq.entree_id
							order by iq.recipe_id;");


			while ($rev_and_mix2->fetch())
			{
				if (isset($rows[$rev_and_mix2->recipe_id]))
				{

					$rows[$rev_and_mix2->recipe_id]['num_items_sold_2_serv'] = $rev_and_mix2->two_serv_items_sold;
					$rows[$rev_and_mix2->recipe_id]['num_items_sold_3_serv'] = $rev_and_mix2->three_serv_items_sold;
					$rows[$rev_and_mix2->recipe_id]['num_items_sold_4_serv'] = $rev_and_mix2->four_serv_items_sold;
					$rows[$rev_and_mix2->recipe_id]['num_items_sold_6_serv'] = $rev_and_mix2->six_serv_items_sold;
					$rows[$rev_and_mix2->recipe_id]['total_servings_sold'] = $rev_and_mix2->total_servings;
					$rows[$rev_and_mix2->recipe_id]['menu_mix'] = $rev_and_mix2->menu_mix;
					$rows[$rev_and_mix2->recipe_id]['total_revenue'] = $rev_and_mix2->totes_rev;
				}
			}

			$menuStartDateTime = date("Y-m-d 00:00:00", strtotime($menuInfo['global_menu_start_date']));
			$PrintableMenuStartDateTime = date("m/d/y", strtotime($menuInfo['global_menu_start_date']));

			$ratingsSince = new DAO();
			$ratingsSince->query("select iq.cat as Category, iq.recipe_id, iq.menu_item_name as item_name, avg(iq.rating) as average_rating, count(if (would_order_again = 1, would_order_again, null)) as would_order_again,
								count(if (would_order_again = 2, would_order_again, null)) as would_NOT_order_again from
								(select b.user_id, CONCAT(u.firstname, ' ', u.lastname) as name, oi.menu_item_id, mi.recipe_id, mi.menu_item_name,
										fs.rating, fs.timestamp_created, fs.would_order_again, if (mi.menu_item_category_id = 9, 'FT', 'CORE') as cat
 											from booking b
								join session s on s.id = b.session_id and s.menu_id = $menu_id
								join user u on u.id = b.user_id
								join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
								join menu_item mi on mi.id = oi.menu_item_id and isnull(mi.copied_from) and mi.is_store_special = 0
								join food_survey fs on fs.recipe_id = mi.recipe_id and fs.user_id = b.user_id and fs.is_active = 1 and fs.is_deleted = 0 
								                           and (fs.timestamp_created > '$menuStartDateTime' or fs.timestamp_updated> '$menuStartDateTime')
								where b.`status` = 'ACTIVE'
								group by b.user_id, mi.recipe_id
								order by b.user_id) as iq
							group by iq.recipe_id
							order by iq.recipe_id");

			while($ratingsSince->fetch())
			{
				if (isset($rows[$ratingsSince->recipe_id]))
				{
					$rows[$ratingsSince->recipe_id]['ratings_since'] = $ratingsSince->average_rating;
					$rows[$ratingsSince->recipe_id]['woa_since'] = $ratingsSince->would_order_again;
					$rows[$ratingsSince->recipe_id]['wnoa_since'] = $ratingsSince->would_NOT_order_again;
				}

			}

			$labels = array('Recipe ID',
							'Recipe Name',
							'Class',
							"Ratings by $monthShortName Purchasers",
							"WOA by $monthShortName Purchasers",
							"WNOA by $monthShortName purchasers",
							'Global Ratings',
							'Global WOA',
							'Global WNOA',
							'# items sold 2-serv',
							'# items sold 3-serv',
							'# items sold 4-serv',
							'# items sold 6-serv',
							'Total Servings Sold',
							'Menu Mix',
							"Ratings Since $PrintableMenuStartDateTime",
							"WAO Since $PrintableMenuStartDateTime",
							"WNOA Since $PrintableMenuStartDateTime",
							'Total Revenue');


		    $columnDescs = array();
		    $columnDescs['A'] = array(
		        'align' => 'right',
		        'width' => 6,
				'type' => 'number'
		    );

		    $columnDescs['B'] = array(
		        'align' => 'left',
		        'width' => 42
		    );
		    $columnDescs['C'] = array(
		        'align' => 'left',
		        'width' => 7
		    );

		    //purchaser ratings
		    $columnDescs['D'] = array(
		        'align' => 'center',
		        'width' => 8,
				'type' => 'number_xxx',
		    );
		    $columnDescs['E'] = array(
		        'align' => 'right',
		        'width' => 10
		    );
		    $columnDescs['F'] = array(
		        'align' => 'right',
		        'width' => 10
		    );

			//global ratings
			$columnDescs['G'] = array(
		        'align' => 'center',
		        'width' => 8,
				'type' => 'number_xxx',
			);
		    $columnDescs['H'] = array(
		        'align' => 'right',
		        'width' => 10
		    );
		    $columnDescs['I'] = array(
		        'align' => 'right',
		        'width' => 10
		    );

			// counts and mix
			$columnDescs['J'] = array(
				'align' => 'right',
				'width' => 9
			);
			$columnDescs['K'] = array(
		        'align' => 'right',
		        'width' => 9
		    );
			$columnDescs['L'] = array(
				'align' => 'right',
				'width' => 9
			);
		    $columnDescs['M'] = array(
		        'align' => 'right',
		        'width' => 9
		    );
		    $columnDescs['N'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );

		    $columnDescs['O'] = array(
		        'align' => 'center',
		        'width' => 10,
				'type' => 'percent'
		    );

		    //ratings since
		    $columnDescs['P'] = array(
		        'align' => 'center',
		        'width' => 8,
				'type' => 'number_xxx'
		    );
		    $columnDescs['Q'] = array(
		        'align' => 'right',
		        'width' => 10,
		    );
		    $columnDescs['R'] = array(
		        'align' => 'right',
		        'width' => 10,
		    );

		    $columnDescs['S'] = array(
		        'align' => 'right',
		        'width' => 14,
				'type' => 'currency'
		    );

		//    $callbacks = array('row_callback' => 'growthScorecardSummaryRowsCallback', 'cell_callback' => 'growthScorecardSummaryCellCallback');


		    $_GET['export'] = 'xlsx';

		    $numRows = count($rows);
		    $tpl->assign('labels', $labels);
		    $tpl->assign('rows', $rows);
		    $tpl->assign('rowcount', $numRows);
		    $tpl->assign('col_descriptions', $columnDescs);
		//    $tpl->assign('excel_callbacks', $callbacks);

		    return;

		}


	$tpl->assign('query_form', $Form->render());

 }

    static function getTotalFormula($column, $totalRows)
    {
        $retVal = "=";
        foreach($totalRows as $row)
        {
            $retVal .= $column . $row . "+";
        }

        $retVal = trim($retVal,"+");
        return $retVal;
    }

     static function getAverageFormula($column, $totalRows)
     {
         $count = 0;
         $retVal = "=(";
         foreach($totalRows as $row)
         {
             $retVal .= $column . $row . "+";
             $count++;
         }

         $retVal = trim($retVal,"+");
         $retVal .= ") / " . $count;
         return $retVal;
     }

 }



?>