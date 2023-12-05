<div class="row">
	<div class="col-6">
		<table class="table ddtemp-table-border-collapse">
			<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
				<tr>
					<td><b>Financial &amp; Trending</b></td>
				</tr>
				<tr>
					<td>
						<a href="/backoffice/fundraiser">Fundraising</a><br/>
						<a href="/backoffice/reports-growth-scorecard">Growth Scorecard</a><br/>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<a href="/backoffice/reports-food-sales">Menu Item Sales Report</a><br/>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-session-host">Session Host Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-customer">Order History</a><br/>
					<a href="/backoffice/reports-cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports-saved-orders">Saved Orders Report</a><br/>
					<a href="/backoffice/reports-user-retention">Inactive Guest Status Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-entree-delivered">Shipping Entr&eacute;e Report</a><br/>
					<?php } else { ?>
						<a href="/backoffice/reports-entree">Entr&eacute;e Report</a><br/>
					<?php } ?>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-store-credit">Credit Report</a><br/>
						<a href="/backoffice/reports-customer-referrals-revenue">Customer Referral Revenue</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-user-data-v2">Guest Details Report</a><br/>
				</td>
			</tr>
		</table>

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