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
					<a href="/backoffice/reports-goal-management-v2">Goal Management</a><br/>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/fundraiser">Fundraising</a><br/>
					<?php } ?>
					<?php if (isset($this->hasPandLAccess) && $this->hasPandLAccess) { ?>
						<a href="/backoffice/reports-p-and-l-input">Profit & Loss (P&L) Financial Summary Input Template</a><br/>
					<?php } ?>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-growth-scorecard">Growth Scorecard</a><br/>
						<a href="/backoffice/reports-growth-dashboard">Growth Dashboard</a><br/>
						<a href="/backoffice/reports-cornerstone-movement-contest">Cornerstone Movement Contest Report</a><br/>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<?php if ($this->CurrentBackOfficeStore->supportsMembership() ) { ?>
						<a href="/backoffice/reports-meal-prep-plus">Meal Prep+ Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-food-sales">Menu Item Sales Report</a><br/>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-session-host">Session Host Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-customer">Order History</a><br/>
					<a href="/backoffice/reports-cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports-saved-orders">Saved Orders Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->supportsDelivery()  && !$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-delivery-orders">Delivery Orders Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-user-retention">Inactive Guest Status Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-entree-delivered">Shipping Entr&eacute;e Report</a><br/>
					<?php } else { ?>
						<a href="/backoffice/reports-entree">Entr&eacute;e Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-coupon">Coupon Report</a><br/>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-customer-referrals-revenue">Customer Referral Revenue</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-user-data-v2">Guest Details Report</a><br/>
				</td>
			</tr>
		</table>
	</div>
	<div class="col-6">
		<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
			<table class="table ddtemp-table-border-collapse">
				<tr>
					<td><b>Supporting Menu Materials</b></td>
				</tr>
				<tr>
					<td>
						<a href="/backoffice/session-tools-printing">Generic Menu Supporting Documents</a><br/>
						<a href="/backoffice/reports-customer-menu-item-labels?interface=1">Generic Cooking Instruction Labels</a><br/>
						<a href="/backoffice/reports-menu-item-nutritional-labels">Nutritional Labels</a><br/>
					</td>
				</tr>
			</table>
		<?php } ?>
	</div>
</div>