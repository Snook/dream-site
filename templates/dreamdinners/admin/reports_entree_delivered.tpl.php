<?php
// constants for all report pages.
$REPORTGIF = "page_header_entreereport.gif";
$PAGETITLE = "Shipping Entree Report";
$HIDDENPAGENAME = "admin_reports_entree_delivered";
$SHOWSINGLEDATE = true;
$SHOWRANGEDATE = true;
$SHOWMONTH = true;
$SHOWYEAR = false;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');
?>


<script src="<?= SCRIPT_PATH ?>/admin/misc.js"></script>
<?php
$zeroItemsAreHiddenByDefault = CBrowserSession::instance()->getValue('hide_zero_qty_items', true);
$displayProp = CTemplate::isOldIE() ? "block" : "table-row";
$initialZeroItemDisplay = $zeroItemsAreHiddenByDefault ? "none" : $displayProp;
$rowCounter = 0;
?>

<script type="text/javascript">
var zerosAreHidden = <?=($zeroItemsAreHiddenByDefault ? "true" : "false")?>;

function showHideZeros()
{
	var displayProp = "table-row";
	if( navigator.appName.indexOf( "Microsoft" ) != -1 )
	{
		displayProp = "block";
	}

	var trows=document.getElementsByTagName('tr');

	if (zerosAreHidden)
		for(var j=0;j<trows.length;j++)
		{
			if (trows[j].id.search('hz_') == 0)
			{
				trows[j].style.display = displayProp;
			}
		}
	else
		for(var j=0;j<trows.length;j++)
		{
			if (trows[j].id.search('hz_') == 0)
			{
				trows[j].style.display = "none";
			}
		}

		zerosAreHidden = !zerosAreHidden;

		var shouldHideZeros = 0;
		if (zerosAreHidden)
			shouldHideZeros = 1;
		var current_date = new Date;
		var cookie_year = current_date.getFullYear ( ) + 1;
		var cookie_month = current_date.getMonth ( );
		var cookie_day = current_date.getDate ( );

		set_cookie('hide_zero_qty_items', shouldHideZeros, cookie_year, cookie_month, cookie_day);

}

</script>

<?php if (isset($this->table_data) && count($this->table_data) > 0) { ?>
<script type="text/javascript">
function externalLink()
{
	var sWinHTML = document.getElementById('printer').innerHTML;
	var winprint=window.open("","");
	winprint.document.open();
	winprint.document.write("<html><link href='<?= CSS_PATH ?>/admin/admin-styles-reports.css' rel='stylesheet' type='text/css' /><link href='<?= CSS_PATH ?>/admin/admin-styles.css' rel='stylesheet' type='text/css' /><link href='<?= CSS_PATH ?>/admin/print.css' rel='stylesheet' type='text/css' /><body onload='window.print();' bgcolor='#537686'><title>Dream Dinners | Entree Report</title><table bgcolor='#FFFFFF'><tr><td>");
	winprint.document.write("<div><div style='margin: 10px; '>");
	winprint.document.write("<h2>Entree Report</h2>");
	winprint.document.write("<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td align='center'><h3><?= $this->timeSpanStr;?></h3></td></tr></table>");
	winprint.document.write(sWinHTML);
	winprint.document.write("</div></div></td></tr></table></body></html>");
	winprint.document.close();
	winprint.focus();

}
</script>
<?php } ?>

	<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >
		<tr>
			<td align="center"><h4>Note: Menu Items are included if Shipping Date falls within time span.</h4></td>
		</tr>
	</table>

<?php if ($this->report_submitted == true) { ?>
<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >
<tr>
	<td align="center"><h3><?php echo $this->timeSpanStr; ?></h3></td>
</tr>
</table>
<?php } ?>

<?php
if ($this->report_submitted == true)
{
	if (isset($this->table_data) && count($this->table_data) > 0)
	{
		$counter = 0;
		$oldMenuID = 0;

		echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

		echo '<tr>';

		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';

		echo ('<td align="right" ><A HREF=' . '"javascript:void(0)"' . " onclick=externalLink('" . 'print' . "');>Printer-Friendly Version&nbsp;</A><img src='" . ADMIN_IMAGES_PATH . "/icon/printer.png' />&nbsp;</td>");
		echo '<td align="right">';
		if (isset($this->store))
		{
			$exportAllLink = '/?page=admin_reports_entree_delivered&store=' . $this->store . '&day=' . $this->report_day . '&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type . '&export=xlsx';
			include $this->loadTemplate('admin/export.tpl.php');
		}
		echo '</td>';

		echo '</tr>';

		echo '<tr>';
		echo '<td colspan="4" class="headers"><font size=3 color="#480000"><b>Entr&eacute;e Report Totals</b></font></td>';
		//echo '<td class="headers" >&nbsp;</td>';
		//echo '<td class="headers" >&nbsp;</td>';
		//echo '<td class="headers" >&nbsp;</td>';
		echo '</tr>';

	/*

�	Total Entrees Sold
�	Total Servings Sold
�	Total Sides Sold
�	% 3-Serving Dinners (see note1)
�	% 6-Serving Dinners (see note1)
�	Average No. Dinners per Order (see note1)
�	% of Orders 72 servings or more  (see note1)
 */


		$sides_sold = 0;
		$total_orders = 0;
		$total_servings = 0;
		$total_items_sold = 0;
		$total_intros = 0;
		$total_3Serv = 0;
		$total_6Serv = 0;

		$total_pro_large = 0;
		$total_pro_med = 0;


		foreach ($this->menu_order_array as $menu_entity)
		{

			foreach ($this->table_data as $entity)
			{
				$category = isset($entity['category_name']) ? $entity['category_name'] : null;

				if ($entity['menu_month'] == $menu_entity)
				{
					if ($entity['is_side_dish'] == 0 && $entity['is_kids_choice'] == 0 && $entity['is_menu_addon'] == 0 && $entity['is_bundle'] == 0 && $entity['is_chef_touched'] == 0)
					{

						$total_orders += $entity['total_dinners_for_ordering'];
						$total_servings += $entity['total_servings'];
						$total_items_sold += $entity['line_total'];
						$total_intros += $entity[CMenuItem::INTRO];

						$total_3Serv += $entity['medium'];
						$total_6Serv += $entity['large'];

						$total_pro_large += $entity['promo_full'];
						$total_pro_med += $entity['promo_half'];

					}
					else if ($entity['is_chef_touched'] == 1)
					{
						$sides_sold += $entity['line_total'] ;
					}
				}
			}


		}



		echo '<tr>';
		echo '<td width="260"><b>Total Entr&eacute;es Sold:</b></td>';
		echo '<td align="right" >' . CTemplate::number_format($total_items_sold, 0) . '</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align="left" width="200"><b>' . 'Total Servings Sold:</b></td>';
		echo '<td align="right" >' . CTemplate::number_format($total_servings, 0) . '</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align="left" width="200"><b>' . 'Total Sides Sold:</b></td>';
		echo '<td align="right" >' .CTemplate::number_format( $sides_sold, 0) . '</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align="left" width="200"><b>' . '% Medium Dinners:</b></td>';
		echo '<td align="right">' . CTemplate::number_format(($total_3Serv / ($total_3Serv + $total_6Serv)) * 100, 2) . '%</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td width="200"><b>% Large Dinners:</b></td>';
		echo '<td align="right" >' . CTemplate::number_format(($total_6Serv / ($total_3Serv + $total_6Serv)) * 100, 2)  . '%</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td width="200"><b>Average No. Dinners per Order:</b></td>';
		echo '<td align="right" >' . CTemplate::number_format($total_items_sold / $this->total_order_count, 2) . '</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo "</table>";

		$varcount = count($this->menu_order_array);
		echo "<div id='printer'>";
		foreach ($this->menu_order_array as $menu_entity)
		{
			echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

			echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

			echo '<tr><td colspan="12" class="headers" border=0><font size=3 color="#480000"><b>' . $menu_entity . '&nbsp;Standard Entr&eacute;es</b></td></tr>';

			echo '<tr>';
			echo '<td class="subheaders" style="padding-left: 2px;" align="left" ><b>Entr&eacute;es:</b></td>';
			echo '<td class="subheaders" align="center"><b>Medium Items</b></td>';
			echo '<td class="subheaders" align="center"><b>Large Items</b></td>';


			echo '<td class="subheaders" align="center"><b>Sales Mix</b></td>';
			echo '<td class="subheaders" align="center"><b>Total Servings</b></td>';
						echo '<td class="subheaders" align="center"><b>Remaining Servings</b></td>';

			echo '<td class="subheaders" bgcolor="LightGrey" align="center"><b>Total Dinners for Ordering</b></td>';
			echo '<td class="subheaders" align="center"><b>Total Sold</b></td>';
			// echo '<td class="subheaders" align="center"><b>Unique Guest Orders</b></td>';
			echo '</tr>';


			$total_orders = 0;
			$total_servings = 0;
			$total_items_sold = 0;
			$total_intros = 0;
			$total_3Serv = 0;
			$total_6Serv = 0;

			$total_pro_large = 0;
			$total_pro_med = 0;

			$lastcategory = null;
			$showcategory = false;

			foreach ($this->table_data as $entity)
			{
				$category = isset($entity['category_name']) ? $entity['category_name'] : null;

				if ($entity['menu_month'] == $menu_entity)
				{
					if ($entity['is_side_dish'] == 0 && $entity['is_kids_choice'] == 0 && $entity['is_menu_addon'] == 0 && $entity['is_bundle'] == 0 && $entity['is_chef_touched'] == 0)
					{
						$curMenuID = $entity['curMenuID'];

						if (!empty($category))
						{
							if (empty($lastcategory) || $category != $lastcategory)
							{
								$showcategory = true;
							}
							else
							{
								$showcategory = false;
							}
							$lastcategory = $category;

							$displayCatVal = $entity['category_name'];
							if ($displayCatVal == 'Specials')
							{
								$displayCatVal = "Core";
							}
							if ($showcategory == true)
							{
								echo '<tr ><td class="reset" border=0><font size=3 color="#480000">&nbsp;</td></tr>';
								echo '<tr ><td class="reset"><font size=2 color="#480000"><b>' . $displayCatVal . '</font></td></tr>';
							}
						}

						echo '<tr >';

						$total_orders += $entity['total_dinners_for_ordering'];
						$total_servings += $entity['total_servings'];
						$total_items_sold += $entity['line_total'] ;
						$total_intros += $entity[CMenuItem::INTRO] ;

						$total_3Serv += $entity['medium'];
						$total_6Serv += $entity['large'];

						$total_pro_large += $entity['promo_full'];
						$total_pro_med += $entity['promo_half'];
?>
						<td class="subheaders" align="left" style="padding-left: 2px; max-width:200px; width:200px; overflow:hidden;" nowrap="nowrap" data-tooltip="<?php echo $entity['menu_name']; ?>"><?php echo $entity['menu_name']; ?></td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['medium'] + $entity['promo_half'], 0);  ?></td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['large'] + $entity['promo_full'], 0); ?></td>
						<td class="subheaders" align="center" style="padding-right: 1px;" width="60"><?php echo $entity['percentages']; ?>%</td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['total_servings'], 0); ?></td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['remaining_inv'], 0); ?></td>
						<td class="subheaders" bgcolor="LightGrey" align="right" style="padding-right: 10px;" width="40"><?php echo CTemplate::number_format($entity['total_dinners_for_ordering'], 1); ?></td>
						<td class="subheaders" align="right" style="padding: 2px;" width="40">$<?php echo CTemplate::number_format($entity['item_revenue'], 2); ?></td>

						<!--  <td class="subheaders" align="center" style="padding: 2px;" width="40" nowrap="nowrap">TBD</td> -->


						</tr>
<?php
						$counter++;
						$oldMenuID = $curMenuID;
					}
				}
			}
?>
			<tr>

			<td class="headers" align="left" ><b>Standard Sub Totals:</b></td>
			<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_3Serv + $total_pro_med, 0);?></td>
			<td class="headers" align="center" width="50"><?php echo CTemplate::number_format($total_6Serv + $total_pro_large, 0); ?></td>
			<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_items_sold, 0); ?></td>

			<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_servings, 0); ?></td>
			<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="60"><?php echo $total_orders; ?></td>
			<td class="headers" align="center" width="50">-</td>
			</tr>

<?php
			echo '<table class="report" width="100%"><tr><td>&nbsp;</td></tr>';
			echo '<tr style="display:', (isset($this->report_submitted) && $this->report_submitted) ? "$displayProp" : 'none', '"><td class="headers"><font size=3 color="#480000"><b>' . $menu_entity . '&nbsp;Additional Menu Items</b></font></td></tr>';
			echo '<tr style="display:', (isset($this->report_submitted) && $this->report_submitted) ? "$displayProp" : 'none', '"><td>';
			echo '<input type="checkbox" id="showZeros" name="showZeros" onclick="javascript:showHideZeros();"', ($zeroItemsAreHiddenByDefault) ? "checked=\"checked\"" : "", ' /><label for="showZeros" >Show only items sold (uncheck to show all)</label>';
			echo '</td></tr></table>';

			$hasSeasonalBundles = false;
			foreach ($this->table_data as $entity) {
				if ($entity['is_bundle'] == 1) {
					$hasSeasonalBundles = true;
					break;
				}
			}

			if ($hasSeasonalBundles == true) {
				echo "<br />";
				echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Holiday and Dinner Bundles Sold for:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders"><b>Holiday and Dinner Bundles</b></td>';
				echo '<td align="left" class="subheaders"><b>Items Sold</b></td>';
				echo '</tr>';

				$totalItems = 0;

				foreach ($this->table_data as $entity) {
					// $arr = $entity->getAllReportData ( $menu_entity );
					if ($entity['menu_month'] == $menu_entity) {
						if ($entity['is_bundle'] == 1) {
							$tempitems = $entity['large'] + $entity['medium'];
							echo '<tr id="' . ($tempitems == "0" ? "hz_" : "nhz_") . ($rowCounter + 1) . '" style="display:' . ($tempitems == "0" ? "$initialZeroItemDisplay" : "$displayProp") . '">';
							echo '<td width="300" class="subheaders">' . $entity['menu_name'] . '</td>';
							echo '<td align="left" class="subheaders">' . $tempitems . '</td>';
							$totalItems += $tempitems;
							echo '</tr>';
						}
					}
				}

				echo '<tr>';
				echo '<td width="300" class="headers"><b>Holiday and Dinner Bundles Totals:</b></td>';
				echo '<td align="left" class="headers"><b>' . $totalItems . '</b></td>';
				echo '</tr>';

				echo '</table>';
			}

			$haskidschoice = false;
			foreach ($this->table_data as $entity) {
				if ($entity['is_kids_choice'] == 1) {
					$haskidschoice = true;
					break;
				}
			}

			if ($haskidschoice == true) {
				echo "<br />";
				echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>KidsChoice Meals Sold for:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders"><b>Kids Choice</b></td>';
				echo '<td align="left" class="subheaders"><b>Items Sold</b></td>';
				echo '</tr>';

				$totalItems = 0;

				foreach ($this->table_data as $entity) {
					// $arr = $entity->getAllReportData ( $menu_entity );
					if ($entity['menu_month'] == $menu_entity) {
						if ($entity['is_kids_choice'] == 1) {
							$tempitems = $entity['large'] + $entity['medium'];
							echo '<tr id="' . ($tempitems == "0" ? "hz_" : "nhz_") . ($rowCounter + 1) . '" style="display:' . ($tempitems == "0" ? "$initialZeroItemDisplay" : "$displayProp") . '">';
							echo '<td width="300" class="subheaders">' . $entity['menu_name'] . '</td>';
							echo '<td align="left" class="subheaders">' . $tempitems . '</td>';
							$totalItems += $tempitems;
							echo '</tr>';
						}
					}
				}

				echo '<tr>';
				echo '<td width="300" class="headers"><b>KIdsChoice Totals:</b></td>';
				echo '<td align="left" class="headers"><b>' . $totalItems . '</b></td>';
				echo '</tr>';

				echo '</table>';
			}

			$hassidedish = false;
			foreach ($this->table_data as $entity) {
				if ($entity['is_side_dish'] == 1) {
					$hassidedish = true;
					break;
				}
			}

			if ($hassidedish == true) {
				echo "<br />";
				echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Side Dishes Sold for:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders"><b>Side Dish </b></td>';
				echo '<td align="left" class="subheaders"><b>Items Sold</b></td>';
				echo '</tr>';

				$totalItems = 0;

				foreach ($this->table_data as $entity) {
					// $arr = $entity->getAllReportData ( $menu_entity );
					if ($entity['menu_month'] == $menu_entity) {
						if ($entity['is_side_dish'] == 1) {
							$tempitems = $entity['large'] + $entity['medium'];
							echo '<tr id="' . ($tempitems == "0" ? "hz_" : "nhz_") . ($rowCounter + 1) . '" style="display:' . ($tempitems == "0" ? "$initialZeroItemDisplay" : "$displayProp") . '">';
							echo '<td width="300" class="subheaders">' . $entity['menu_name'] . '</td>';
							echo '<td align="left" class="subheaders">' . $tempitems . '</td>';
							$totalItems += $tempitems;
							echo '</tr>';
						}
					}
				}

				echo '<tr>';
				echo '<td width="300" class="headers"><b>Side Dish Totals:</b></td>';
				echo '<td align="left" class="headers"><b>' . $totalItems . '</b></td>';
				echo '</tr>';

				echo '</table>';
			}

			$hasMenuAddons = false;
			foreach ($this->table_data as $entity) {
				if ($entity['is_menu_addon'] == 1) {
					$hasMenuAddons = true;
					break;
				}
			}

			if ($hasMenuAddons == true) {
				echo "<br />";
				echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Add-on Items Sold for:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders"><b>Add-on Items </b></td>';
				echo '<td align="left" class="subheaders"><b>Items Sold</b></td>';
				echo '</tr>';

				$totalItems = 0;

				foreach ($this->table_data as $entity) {
					// $arr = $entity->getAllReportData ( $menu_entity );
					if ($entity['menu_month'] == $menu_entity) {
						if ($entity['is_menu_addon'] == 1) {
							$tempitems = $entity['large'] + $entity['medium'];
							echo '<tr id="' . ($tempitems == "0" ? "hz_" : "nhz_") . ($rowCounter + 1) . '" style="display:' . ($tempitems == "0" ? "$initialZeroItemDisplay" : "$displayProp") . '">';
							echo '<td width="300" class="subheaders">' . $entity['menu_name'] . '</td>';
							echo '<td align="left" class="subheaders">' . $tempitems . '</td>';
							$totalItems += $tempitems;
							echo '</tr>';
						}
					}
				}

				echo '<tr>';
				echo '<td width="300" class="headers"><b>Addon Items Totals:</b></td>';
				echo '<td align="left" class="headers"><b>' . $totalItems . '</b></td>';
				echo '</tr>';

				echo '</table>';
				echo '<p style="page-break-before: always">';
			}

			$hasChefTouched = false;
			foreach ($this->table_data as $entity) {
				if ($entity['is_chef_touched'] == 1) {
					$hasChefTouched = true;
					break;
				}
			}

			if ($hasChefTouched == true) {
				echo "<br />";
				echo "<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Sides and Sweets Items Sold for:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';
				echo '<td width="300" class="headers"></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders"><b>Sides and Sweets Items </b></td>';
				echo '<td align="left" class="subheaders"><b>Items Sold</b></td>';
				echo '<td width="300" class="subheaders"><b>Price</b></td>';

				echo '</tr>';

				$totalItems = 0;

				foreach ($this->table_data as $entity) {
					// $arr = $entity->getAllReportData ( $menu_entity );
					if ($entity['menu_month'] == $menu_entity) {
						if ($entity['is_chef_touched'] == 1) {
							$tempitems = $entity['large'] + $entity['medium'];
							echo '<tr id="' . ($tempitems == "0" ? "hz_" : "nhz_") . ($rowCounter + 1) . '" style="display:' . ($tempitems == "0" ? "$initialZeroItemDisplay" : "$displayProp") . '">';
							echo '<td width="300" class="subheaders">' . $entity['menu_name'] . '</td>';
							echo '<td align="left" class="subheaders">' . $tempitems . '</td>';
							echo '<td align="left" class="subheaders">' . $entity['full_price'] . '</td>';
							$totalItems += $tempitems;
							echo '</tr>';
						}
					}
				}

				echo '<tr>';
				echo '<td width="300" class="headers"><b>Sides and Sweets Item Totals:</b></td>';
				echo '<td align="left" class="headers"><b>' . $totalItems . '</b></td>';
				echo '</tr>';

				echo '</table>';
				//echo '<p style="page-break-before: always">';
			}




			echo "<br />";
		}

	} else {
		$r_display = '<table><tr><td width="610" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>';
		echo $r_display;
	}
}
echo "</div>";

?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>