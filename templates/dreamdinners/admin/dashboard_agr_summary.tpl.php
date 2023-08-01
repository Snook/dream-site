<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
  <tr>
  	<td colspan="5"class="space_section_head" style="text-align:center;" ><span data-help="dashboard-adjusted_gross_revenue">Adjusted Gross Revenue Comparison</span></td>
  </tr>
  <tr>
    <td class="space_right_delimited" style="width:20%;"></td>
    <td class="space_right_delimited" style="text-align:center;" colspan="2"><?php echo $this->currentMonthStr;?></td>
    <td class="space_right_delimited" style="text-align:center;" ><?php echo $this->nextMonthStr;?></td>
    <td class="space_right_open" style="text-align:center;" ><?php echo $this->distantMonthStr;?></td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right"><?php echo $this->currentMonthStr;?> AGR</td>
    <td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['total_agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthAGRMetrics['total_agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distMonthAGRMetrics['total_agr'], 2);?></td>
  </tr>
	<tr>
		<td class="label_delimited" style="text-align:right"><span data-help="dashboard-adjusted_gross_revenue_month_start">Month Start <?php echo $this->currentMonthStr;?> AGR</span></td>
		<td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['month_start_total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthAGRMetrics['month_start_total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distMonthAGRMetrics['month_start_total_agr'], 2);?></td>
	</tr>

	<tr>
    <td class="label_delimited" style="text-align:right; font-weight:normal; font-size:10pt;"><span data-help="dashboard-sales_adjustments">Sales Adjustments</span></td>
    <td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['sales_adjustments_total'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthAGRMetrics['sales_adjustments_total'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distMonthAGRMetrics['sales_adjustments_total'], 2);?></td>
  </tr>
	<tr>
		<td class="label_delimited" style="text-align:right; font-weight:normal; font-size:10pt;"><span>Meal Prep+ Fees</span></td>
		<td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_from_membership_fees'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthAGRMetrics['agr_from_membership_fees'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distMonthAGRMetrics['agr_from_membership_fees'], 2);?></td>
	</tr>
	<!--
	<tr>
		<td class="label_delimited" style="text-align:right; font-weight:normal; font-size:10pt;"><span>Totals</span></td>
		<td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthAGRMetrics['total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distMonthAGRMetrics['total_agr'], 2);?></td>
	</tr>
	-->
	<tr>
    <td class="space_right_delimited" style="text-align:right"></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->previousMonthStr;?></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->curMonthLastYearStr;?></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->nextMonthLastYearStr;?></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->distantMonthLastYearStr;?></td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right"><span data-help="dashboard-adjusted_gross_revenue_history">Month End Total</span></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->prevMonthAGRMetrics['total_agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthLastYearAGRMetrics['total_agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthLastYearAGRMetrics['total_agr'], 2);?></td>
    <td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distantMonthLastYearAGRMetrics['total_agr'], 2);?></td>
  </tr>
	<tr>
		<td class="label_delimited" style="text-align:right"><span data-help="dashboard-adjusted_gross_revenue_month_start_history">Month Start Total</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->prevMonthAGRMetrics['month_start_total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthLastYearAGRMetrics['month_start_total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->nextMonthLastYearAGRMetrics['month_start_total_agr'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->distantMonthLastYearAGRMetrics['month_start_total_agr'], 2);?></td>
	</tr>
	<tr>
    <td class="label_delimited" style="text-align:right">+/-</td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->previousAGRDelta;?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthlastYearAGRDelta;?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->nextMonthlastYearAGRDelta;?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->distantMonthlastYearAGRDelta;?></td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right">%</td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->previousAGRDeltaPercent;?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthlastYearAGRDeltaPercent;?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->nextMonthlastYearAGRDeltaPercent;?></td>
    <td class="value_delimited" style="text-align:center;" ><?php echo $this->distantMonthlastYearAGRDeltaPercent;?></td>
  </tr>
</table>
