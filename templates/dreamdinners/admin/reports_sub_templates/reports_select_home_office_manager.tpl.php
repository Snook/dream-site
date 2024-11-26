<div class="row">
	<div class="col-6">
		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Financial &amp; Trending</b></td>
			</tr>
			<tr>
				<td>
					Dream Dashboard: <a href="/backoffice/dashboard-menu-based">Menu-based</a> | <a href="/backoffice/dashboard-new">Calendar-based</a><br/>
					Trending Report: <a href="/backoffice/reports-trending-menu-based">Menu-based</a> | <a href="/backoffice/reports_trending">Calendar-based</a><br/>
					<a href="/backoffice/reports-trending-new">Rolling-13 Month Business Analysis Report</a><br/>
					<a href="/backoffice/reports-goal-management-v2">Goal Management</a><br/>
					<a href="/backoffice/reports-financial-statistic-v2">Financial/Statistical Report</a><br/>
					<a href="/backoffice/reports-dream-weekly-v2">Weekly Report</a><br/>
					<a href="/backoffice/reports-payment-reconciliation">Payment Reconciliation Report</a><br/>
					<a href="/backoffice/reports-cash-flow">Cash Flow Report</a><br/>
					<a href="/backoffice/fundraiser">Fundraising</a><br/>
					<a href="/backoffice/reports-same-store-sales">Same Store Sales Report</a><br/>
					<a href="/backoffice/reports-dashboard-aggregate-v2">Dashboard Aggregate Report</a><br/>
					<a href="/backoffice/reports-financial-performance">Financial Performance Report</a><br/>
					<a href="/backoffice/reports-royalty">Royalty Report</a><br/>
					<a href="/backoffice/reports-p-and-l-input">Profit & Loss (P&L) Financial Summary Input Template</a><br/>
					<a href="/backoffice/reports-growth-scorecard">Growth Scorecard</a><br/>
					<a href="/backoffice/reports-growth-dashboard">Growth Dashboard</a><br/>
					<a href="/backoffice/reports-business-health-assessment">Business Health Assessment</a><br/>
				</td>
			</tr>
		</table>

		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-meal-prep-plus">Meal Prep+ Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-food-sales">Menu Item Sales Report</a><br/>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-session-host">Session Host Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-customer">Order History</a><br/>
					<a href="/backoffice/reports-cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports-saved-orders">Saved Orders Report</a><br/>
					<a href="/backoffice/reports-delivery-orders">Delivery Orders Report</a><br/>
					<a href="/backoffice/reports-user-retention">Inactive Guest Status Report</a><br/>
					<a href="/backoffice/reports-coupon">Coupon Report</a><br/>
					<a href="/backoffice/reports-entree" <?php if ($this->CurrentBackOfficeStore->isDistributionCenter()) { ?>class="text-decoration-line-through" data-toggle="tooltip" data-placement="top" title="Not available for Distribution Centers"<?php } ?>>Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports-entree-delivered" <?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>class="text-decoration-line-through" data-toggle="tooltip" data-placement="top" title="Not available for Franchises"<?php } ?>>Shipping Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports-store-credit">Credit Report</a><br/>
					<a href="/backoffice/reports-customer-referrals-revenue">Customer Referral Revenue</a><br/>
					<a href="/backoffice/reports-preferred-users">Preferred Users</a><br/>
					<a href="/backoffice/reports-user-data-v2">Guest Details Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->supportsPlatePoints() ) { ?>
						<a href="/backoffice/reports-points-status-by-date">PLATEPOINTS Awards</a><br/>
					<?php } ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="col-6">
		<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter() || $this->CurrentBackOfficeStore->supportsPlatePoints()) { ?>
			<table class="table ddtemp-table-border-collapse">
				<tr>
					<td><b>Supporting Menu Materials</b></td>
				</tr>
				<tr>
					<td>
						<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
							<a href="/backoffice/session-tools-printing">Generic Menu Supporting Documents</a><br/>
							<a href="/backoffice/reports-customer-menu-item-labels?interface=1">Generic Cooking Instruction Labels</a><br/>
							<a href="/backoffice/reports-menu-item-nutritional-labels">Nutritional Labels</a><br/>
						<?php } ?>
						<?php if ($this->CurrentBackOfficeStore->supportsPlatePoints() ) { ?>
							<a href="/backoffice/user-plate-points?print_blank_form=true" target="_blank">Blank PLATEPOINTS Enrollment Form</a><br/>
						<?php } ?>
					</td>
				</tr>
			</table>
		<?php } ?>

		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Document Download Links</b></td>
			</tr>
			<tr>
				<td>
					<a href="/backoffice/reports?file_id=1">Food Cost Adjustments</a><br/>
					<a href="/backoffice/reports?file_id=2">Sales Adjustment Request</a><br/>
				</td>
			</tr>
		</table>

		<?php if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'HO_REPORTING') {?>
			<table class="table ddtemp-table-border-collapse">
				<tr>
					<td><b>Home Office Reporting Server Specific</b></td>
				</tr>
				<tr>
					<td>
						<a href="/backoffice/home-office-reports-site-aggregate-samestore">Same Store Sales Site Aggregate Report</a><br/>
						<a href="/backoffice/home-office-reports-dashboard-aggregate">Dream Dashboard Aggregate</a><br/>
						<a href="/backoffice/home-office-reports-weekly-metrics">Weekly Metrics Report</a><br/>
						<a href="/backoffice/home-office-reports-guest-metrics">Guest Metrics Report</a><br/>
					</td>
				</tr>
			</table>
		<?php } ?>

		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Home Office Reporting</b></td>
			</tr>
			<tr>
				<td>
					<ul class="list-unstyled">
						<li><a href="/backoffice/reports-cornerstone-movement-contest">Cornerstone Movement Contest Report</a></li>
						<li><a href="/backoffice/reports-food-and-labor-costs">Food and Labor Costs</a></li>
						<li><a href="/backoffice/performance-override">Performance Override Form</a></li>
						<li><a href="/backoffice/reports-site-royalty-national">Royalty Report by Store</a></li>
						<li><a href="/backoffice/reports-store-contact-information?format=store&amp;export=xlsx">Store Contact Information - Active</a></li>
						<li><a href="/backoffice/reports-store-contact-information?format=owner&amp;active=1&amp;export=xlsx">Entity Contact Information - Active</a></li>
						<li><a href="/backoffice/reports-store-contact-information?format=owner&amp;active=0&amp;export=xlsx">Entity Contact Information - Inactive</a></li>
						<li><a href="/backoffice/reports-my-meals">My Meals Ratings</a></li>
						<li><a href="/backoffice/reports-my-meals-comments">My Meals Comments</a></li>
						<li><a href="/backoffice/food-testing">Food Testing Manager</a></li>
						<li><a href="/backoffice/food-testing-survey">Food Testing Store Surveys</a></li>
						<li><a href="/backoffice/reports-menu-skipping">Menu Skipping Report</a></li>
						<li><a href="/backoffice/reports-door-dash-import">Door Dash Report & Import</a></li>
						<li><a href="/backoffice/reports-meal-prep-plus-site-wide">Meal Prep+ Subscriptions (All stores)</a></li>
						<li><a href="/backoffice/reports-national-entree-projection">Master Preorder Export</a></li>
						<li><a href="/backoffice/reports-menu-planning">Menu Planning Report</a></li>
						<li>
							<a href="/backoffice/reports-guest">Guest Reports</a>
							<ul>
								<li><a href="/backoffice/reports-guest?report=power-bi">Power BI Dashboard Export</a></li>
								<li><a href="/backoffice/reports-guest?report=driver-tip">Driver Tip Report</a></li>
								<li><a href="/backoffice/reports-guest?report=guest-details">Guest Details</a></li>
								<li><a href="/backoffice/reports-guest?report=guest-birthdays">Guest Birthdays</a></li>
								<li><a href="/backoffice/reports-guest?report=guest-with-dinner-dollars">Guests with Expiring Dinner Dollars</a></li>
								<li><a href="/backoffice/reports-guest?report=preferred-user">Preferred Users</a></li>
								<li><a href="/backoffice/reports-guest?report=gift-card-balance">Virtual Gift Card Balance</a></li>
							</ul>
						</li>
					</ul>
				</td>
			</tr>
		</table>
	</div>
</div>