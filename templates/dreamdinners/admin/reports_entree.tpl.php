<?php
// constants for all report pages.
$REPORTGIF = "page_header_entreereport.gif";
$PAGETITLE = "Entree Report";
$HIDDENPAGENAME = "admin_reports_entree";
$SHOWSINGLEDATE = true;
$SHOWRANGEDATE = true;
$SHOWMONTH = true;
$SHOWYEAR = false;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');
$this->setScript('foot', SCRIPT_PATH . '/admin/entree_report.min.js');
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
	winprint.document.write("<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td align='center'><h3><?= $this->timeSpanStr;?></h3></td></tr><tr><td align='center'>Session Types Included: <?= $this->sessionFilterStr; ?></td></tr></table>");
	winprint.document.write(sWinHTML);
	winprint.document.write("</div></div></td></tr></table></body></html>");
	winprint.document.close();
	winprint.focus();

}
</script>
<?php } ?>

<?php if ($this->report_submitted == true) { ?>
<table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >
<tr>
	<td align="center"><h3><?php echo $this->timeSpanStr; ?></h3></td>
</tr>
<tr>
	<td align="center">Session Types Included: <?php echo $this->sessionFilterStr; ?></td>
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
			$sessionTypes = $this->selectedSessionTypeArgs;
			$exportAllLink = '/?page=admin_reports_entree&store=' . $this->store . '&day=' . $this->report_day . '&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type . '&export=xlsx'.$sessionTypes;
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
		$total_2Serv = 0;
		$total_4Serv = 0;
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

						if (!empty($entity['two']))
						{
							$total_2Serv += $entity['two'];
						}

						if (!empty($entity['four']))
						{
							$total_4Serv += $entity['four'];
						}

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
		echo '<td align="left" width="200"><b>' . '% Small Dinners:</b></td>';
		echo '<td align="right">' . CTemplate::number_format(($total_2Serv / ($total_2Serv + $total_3Serv + $total_4Serv + $total_6Serv)) * 100, 2) . '%</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align="left" width="200"><b>' . '% Medium (3) Dinners:</b></td>';
		echo '<td align="right">' . CTemplate::number_format(($total_3Serv / ($total_2Serv + $total_3Serv + $total_4Serv + $total_6Serv)) * 100, 2) . '%</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td align="left" width="200"><b>' . '% Medium (4) Dinners:</b></td>';
		echo '<td align="right">' . CTemplate::number_format(($total_4Serv / ($total_2Serv + $total_3Serv + $total_4Serv + $total_6Serv)) * 100, 2) . '%</td>';
		echo '<td >&nbsp;</td>';
		echo '<td >&nbsp;</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td width="200"><b>% Large Dinners:</b></td>';
		echo '<td align="right" >' . CTemplate::number_format(($total_6Serv / ($total_2Serv + $total_3Serv + $total_4Serv + $total_6Serv)) * 100, 2)  . '%</td>';
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
		echo '<td width="200"><b>% of Orders 72 servings or more:</b></td>';
		echo '<td align="right" >' . $this->percent_total_orders_greater_than_72_servings . '%</td>';
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

			echo '<tr><td colspan="16" class="headers" border=0><font size=3 color="#480000"><b>' . $menu_entity . '&nbsp;Standard Entr&eacute;es</b></td></tr>';

			echo '<tr>';
			echo '<td class="subheaders" style="padding-left: 2px;" align="left" ><b>Entr&eacute;es:</b></td>';
			if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids)){
				echo '<td class="subheaders" align="center"><b>Small Items</b></td>';
				echo '<td class="subheaders" align="center"><b>Md (3) Items</b></td>';
				echo '<td class="subheaders" align="center"><b>Md (4) Items</b></td>';
			}else{
				echo '<td class="subheaders" align="center"><b>Medium Items</b></td>';
			}
			echo '<td class="subheaders" align="center"><b>Large Items</b></td>';
			echo '<td class="subheaders" align="center"><b>Station</b></td>';

			echo '<td class="subheaders" align="center"><b>Sales Mix</b></td>';
			echo '<td class="subheaders" align="center"><b>Total Servings</b></td>';
						echo '<td class="subheaders" align="center"><b>Remaining Servings</b></td>';

			echo '<td class="subheaders" bgcolor="LightGrey" align="center"><b>Total Dinners for Ordering</b></td>';
			if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids))
			{
				echo '<td class="subheaders" align="center"><b>Retail Price <br />Sm</b></td>';
				echo '<td class="subheaders" align="center"><b>Retail Price <br />Md (3)</b></td>';
				echo '<td class="subheaders" align="center"><b>Retail Price <br />Md (4)</b></td>';
			}else{
				echo '<td class="subheaders" align="center"><b>Retail Price <br />Md</b></td>';
			}
			echo '<td class="subheaders" align="center"><b>Retail Price <br />Lrg</b></td>';
			echo '<td class="subheaders" align="center"><b>Total Sold</b></td>';
			echo '<td class="subheaders" align="center"><b>Unique Guests Orders</b></td>';
			// echo '<td class="subheaders" align="center"><b>Unique Guest Orders</b></td>';
			echo '</tr>';


			$total_orders = 0;
			$total_servings = 0;
			$total_items_sold = 0;
			$total_intros = 0;
			$total_2Serv = 0;
			$total_4Serv = 0;
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
							if ($showcategory == true)
							{
								echo '<tr ><td class="reset" border=0><font size=3 color="#480000">&nbsp;</td></tr>';
								echo '<tr ><td class="reset"><font size=2 color="#480000"><b>' . $entity['category_name'] . '</font></td></tr>';
							}
						}

						echo '<tr >';

						$total_orders += $entity['total_dinners_for_ordering'];
						$total_servings += $entity['total_servings'];
						$total_items_sold += $entity['line_total'] ;
						$total_intros += $entity[CMenuItem::INTRO] ;

						if (!empty($entity['two']))
						{
							$total_2Serv += $entity['two'];
						}
						if (!empty($entity['four']))
						{
							$total_4Serv += $entity['four'];
						}

						$total_3Serv += $entity['medium'];
						$total_6Serv += $entity['large'];

						$total_pro_large += $entity['promo_full'];
						$total_pro_med += $entity['promo_half'];
?>
						<td class="subheaders" align="left" style="padding-left: 2px; max-width:200px; width:200px; overflow:hidden;" data-tooltip="<?php echo $entity['menu_name']; ?>"><?php echo $entity['menu_name']; ?></td>
						<?php if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids))
						{ ?>
							<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['two'] , 0);  ?></td>
							<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['medium'] + $entity['promo_half'], 0);  ?></td>
							<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['four'] , 0);  ?></td>
						<?php }else{ ?>
							<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['medium'] + $entity['promo_half'], 0);  ?></td>
						<?php } ?>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['large'] + $entity['promo_full'], 0);?></td>

						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo (($category == "Specials" && $entity['station_number'] == 0) ? 'FL' : $entity['station_number']); ?></td>
						<td class="subheaders" align="center" style="padding-right: 1px;" width="60"><?php echo $entity['percentages']; ?>%</td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['total_servings'], 0); ?></td>
						<td class="subheaders" align="center" style="padding-right: 0px;" width="40"><?php echo CTemplate::number_format($entity['remaining_inv'], 0); ?></td>
						<td class="subheaders" bgcolor="LightGrey" align="right" style="padding-right: 10px;" width="40"><?php echo CTemplate::number_format($entity['total_dinners_for_ordering'], 1); ?></td>

						<?php if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids))
						{ ?>
							<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php if (!empty($entity['two_price'])) echo "$" . $entity['two_price']; ?></td>
							<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php if (!empty($entity['half_price'])) echo "$" . $entity['half_price']; ?></td>
							<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php if (!empty($entity['four_price'])) echo "$" . $entity['four_price']; ?></td>
						<?php }else{ ?>
							<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php if (!empty($entity['half_price'])) echo "$" . $entity['half_price']; ?></td>
						<?php } ?>
						<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php if (!empty($entity['full_price'])) echo "$" . $entity['full_price']; ?></td>

						<td class="subheaders" align="right" style="padding: 2px;" width="40">$<?php echo CTemplate::number_format($entity['item_revenue'], 2); ?></td>
						<td class="subheaders" align="center" style="padding: 2px;" width="40"><?php echo CTemplate::number_format($entity['num_purchasers'], 0); ?></td>

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
			<?php if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids))
			{ ?>
				<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_2Serv , 0);?></td>
				<td class="headers" align="center" width="50"><?php echo CTemplate::number_format($total_3Serv + $total_pro_large, 0); ?></td>
				<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_4Serv , 0);?></td>
			<?php }else{ ?>
				<td class="headers" align="center" width="50"><?php echo CTemplate::number_format($total_3Serv + $total_pro_large, 0); ?></td>
			<?php } ?>
				<td class="headers" align="center" width="50"><?php echo CTemplate::number_format($total_6Serv + $total_pro_large, 0); ?></td>

				<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_items_sold, 0); ?></td>

			<td class="headers" align="center" width="60"><?php echo CTemplate::number_format($total_servings, 0); ?></td>
			<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="60"><?php echo $total_orders; ?></td>
			<?php if(CStore::isCoreTestStoreAcrossMenus($this->store, $this->menu_ids))
			{ ?>
				<td class="headers" align="center" width="50">-</td>
				<td class="headers" align="center" width="50">-</td>
			<?php } ?>
			<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="50">-</td>
			<td class="headers" align="center" width="50">-</td>
			</tr>
<?php
			if (isset($this->numBundles[$menu_entity])) {
			echo '<tr><td border=0>&nbsp;</td></tr>';

				echo "<tr><td colspan='14'><table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Meal Prep Starter Pack Bundles sold:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders">Meal Prep Starter Pack sold</td>';
				echo '<td align="left" class="subheaders">' . $this->numBundles[$menu_entity] . '</td>';
				echo '</tr>';

				echo '</table></td></tr>';
			}
			else
			{
			echo '<tr><td border=0>&nbsp;</td></tr>';

				echo "<tr><td colspan='14'><table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >";

				echo '<tr>';

				echo '<td width="200" class="headers"><b>Meal Prep Starter Pack Bundles sold:</b></td>';
				echo '<td align="left" class="headers"><b>' . $menu_entity . '</b></td>';

				echo '</tr>';

				echo '<tr>';
				echo '<td width="300" class="subheaders">Meal Prep Starter Pack sold</td>';
				echo '<td align="left" class="subheaders">0</td>';
				echo '</tr>';

				echo '</table></td></tr>';

			}

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