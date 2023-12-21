<table class="report" border="0">
<tr>
	<td style="font-weight:bold;font-size:16px;">#<?php echo $array_entity['home_office_id']; ?> - <?php echo $array_entity['store_name']; ?> - <?php echo $array_entity['city']; ?>, <?php echo $array_entity['state_id']; ?>


	<?php if (!empty($array_entity['is_transition_month']))  { ?>

	&nbsp;<span style="color:red">(4-4-5 Transition Month &mdash; Includes Sales for June 1st through July 2nd)</span>

	<?php } else if (!empty($array_entity['is_menu_based_month']))  { ?>

	&nbsp;<span style="color:red">(Now based on Menu Month)</span>

	<?php } else { ?>

	&nbsp;(based on Calendar Month )

	<?php } ?>
	</td>
	<td style="text-align:right;">
<?php if (!$this->print_view) { ?>
	<a href="/backoffice/reports-royalty?store=<?php echo $array_entity['store_id']; ?>&amp;month_popup=<?php echo $this->report_month; ?>&amp;year_field_001=<?php echo $this->report_year; ?>&amp;report_submit=true&amp;print=true"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" class="img_valign"></a>
<?php
	$exportAllLink = '/backoffice/reports-royalty?store=' . $array_entity['store_id'] . '&day=' . $this->report_day . '&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type .  '&export=xlsx';
	include $this->loadTemplate('admin/export.tpl.php');
}
?>
	</td>
</tr>
</table>
<table class="report">
<tr>
	<td class="headers" style="width:50%;font-weight:bold;">Royalty Summary</td>
	<td class="headers" style="width:50%;font-weight:bold;">Totals</td>
</tr>
<tr>
	<td style="font-weight:bold;">Month:</td>
	<td style="font-weight:bold;"><?php echo $array_entity['Date']; ?></td>
</tr>
<tr>
	<td style="font-weight:bold;">Gross Taxable Income:</td>
	<td><?php echo CSessionReports::formatCurrency($array_entity['grand_total_less_taxes']); ?></td>
</tr>

<tr>
	<td style="font-weight:bold;">&nbsp;&nbsp;&nbsp;&nbsp;Less Discounts & Adjustments Not Subject to Royalty & Mktg Fees:<span style="color:red;">*</span></td>
	<td>(<?php echo CSessionReports::formatCurrency($array_entity['grand_total_less_taxes'] - $array_entity['total_less_discounts']); ?>)</td>
</tr>
<tr>
	<td style="font-weight:bold; font-size:larger;">Total Sales (AGR) Subject to Royalties & National Marketing Fees:</td>
	<td style="font-weight:bold; font-size:larger;"><?php echo CSessionReports::formatCurrency($array_entity['total_less_discounts']); ?></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>

	<?php if (!empty($array_entity['marketing_total'])) { ?>
<tr>
	<td style="font-weight:bold;">National Marketing Fee:</td>
	<td><?php echo CSessionReports::formatCurrency($array_entity['marketing_total']); ?></td>
</tr>
	<?php } ?>
<?php if (!empty($array_entity['salesforce_fee'])) { ?>
<tr>
	<td style="font-weight:bold;">Technology Fee:</td>
	<td><?php echo CSessionReports::formatCurrency($array_entity['salesforce_fee']); ?></td>
</tr>
<?php } ?>


    <tr>
	<td style="font-weight:bold;">Royalties Owed:</td>
	<td><?php echo (isset($array_entity['performance_standard']) && $array_entity['performance_standard'] == 2) ? '<span style="color:red">' . CSessionReports::formatCurrency($array_entity['royalty']) . '**</span>' : CSessionReports::formatCurrency($array_entity['royalty']); ?></td>
</tr>
<tr>
	<td style="font-weight:bold; font-size:larger;">Total Monthly Fees Owed:</td>
	<td style="font-weight:bold; font-size:larger;"><?php echo CSessionReports::formatCurrency($array_entity['total_fees']); ?></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>

    <?php if (false && !empty($array_entity['subtotal_delivery_fee'])) { ?>
        <tr>
            <td style="font-weight:bold; font-size:larger;">Total Delivery Fees Owed to Delivery Vendor:</td>
            <td style="font-weight:bold; font-size:larger;"><?php echo CSessionReports::formatCurrency($array_entity['subtotal_delivery_fee']); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>



<?php if (isset($array_entity['ltd_menu_item_value']) || isset($array_entity['ltd_round_up_value'])) {
	 $menu_item_value = (isset($array_entity['ltd_menu_item_value']) ? $array_entity['ltd_menu_item_value'] : 0);
	 $round_up_value = (isset($array_entity['ltd_round_up_value']) ? $array_entity['ltd_round_up_value'] : 0);
	 ?>
<tr>
	<td style="font-weight:bold; font-size:larger;">Total Donations Owed to Dream Dinners Foundation:</td>
	<td style="font-weight:bold; font-size:larger;"><?php echo CSessionReports::formatCurrency($menu_item_value + $round_up_value); ?></td>
</tr>
<?php } ?>



<?php if (!$this->print_view && $this->report_type_to_run == 3) { ?>
<tr>
	<td colspan="2"><input type="button" class="btn btn-primary btn-sm" name="Print Invoice" value="Print Invoice" onclick="bounce('/backoffice/reports-royalty_invoice?store=<?php echo $this->store; ?>&month=<?php echo $this->report_month; ?>&year=<?php echo $this->report_year; ?>&order_total=<?php echo (isset($this->report_data[0]['total_less_discounts']) ? $this->report_data[0]['total_less_discounts'] : 0); ?>');"></td>
</tr>
<?php } ?>

<tr>
	<td colspan="2" style="color:red">* For further detail, please refer to the "Taxable Discounts & Adjustments" section of your Financial Statistical Report.</td>
</tr>
<?php if (isset($array_entity['performance_standard']) && $array_entity['performance_standard'] == 2) { ?>
<tr>
	<td colspan="2" style="color:red">** Minimum Performance Standard in Effect</td>
</tr>
<?php } ?>

<?php /* if (isset($array_entity['used_performance_override']) && $array_entity['used_performance_override'] == true) { ?>
	<tr>
		<td><i>(Royalty was calculated with a one-time performance override.)</i></td>
	</tr>
<?php } */ ?>
</table>
<hr />
<div style="page-break-after:always;"></div>