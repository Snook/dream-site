<table id="dashboard_snapshot_table">
<tr>
	<td class="label" data-help="dashboard-adjusted_gross_revenue">Adj Gross Revenue</td>
	<td class="value" style="width: 70px;">$<?php echo CTemplate::number_format($this->dashboard_metrics['total_agr']); ?></td>

	<td class="label" data-help="dashboard-ranking-percent_increase">AGR by % Increase</td>
	<td class="value"><?php echo $this->dashboard_metrics['curMonthlastYearAGRDeltaPercent']; ?>%</td>
</tr>
</table>
