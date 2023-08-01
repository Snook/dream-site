<table id="dashboard_snapshot_table">
<tr>
	<td class="label" data-help="dashboard-adjusted_gross_revenue">Adj Gross Revenue</td>
	<td class="value">$<?php echo CTemplate::number_format($this->dashboard_metrics['total_agr']); ?></td>

	<td class="label" data-help="dashboard-ranking-percent_increase">AGR by % Increase</td>
	<td class="value"><?php echo $this->dashboard_metrics['curMonthlastYearAGRDeltaPercent']; ?>%</td>
</tr>
<tr>
	<td class="label" data-help="dashboard-gross_revenue_breakdown-average_ticket">Average Ticket</td>
	<td class="value">$<?php echo CTemplate::number_format($this->dashboard_metrics['avg_ticket_all']); ?></td>
	<td class="label" data-help="dashboard-gross_revenue_breakdown-addon_sales">Sides &amp; Sweets Sales</td>
	<td class="value">$<?php echo CTemplate::number_format($this->dashboard_metrics['addon_sales_total']); ?></td>
</tr>
<tr>
	<td class="label" data-help="dashboard-guests-in_store_signup">In-Store Sign-Up</td>
	<td class="value"><?php echo $this->dashboard_metrics['instore_signup_percent']; ?>%</td>

	<td class="label" data-help="dashboard-gross_revenue_breakdown-avg_addon_sales">Avg FT Sales</td>
	<td class="value">$<?php echo CTemplate::number_format($this->dashboard_metrics['avg_FT_sales']); ?></td>
</tr>
<tr>
	<td class="label" data-help="dashboard-orders-unique_guests">Unique Guests</td>
	<td class="value"><?php echo $this->dashboard_metrics['guest_count_total']; ?></td>
	<td class="label" data-help="dashboard-sessions-orders_per_session">Avg Orders/Session</td>
	<td class="value"><?php echo $this->dashboard_metrics['orders_per_session']; ?></td>
</tr>
</table>