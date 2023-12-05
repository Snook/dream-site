<div class="row">
	<div class="col-6">
		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<?php if (!$this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
					<a href="/backoffice/reports-session-host">Session Host Report</a><br/>
					<?php } ?>
					<a href="/backoffice/reports-customer">Order History</a><br/>
					<a href="/backoffice/reports-cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports-saved-orders">Saved Orders Report</a><br/>
					<a href="/backoffice/reports-delivery-orders">Delivery Orders Report</a><br/>
					<a href="/backoffice/reports-entree">Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports-entree-delivered">Shipping Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports-store-credit">Credit Report</a><br/>
					<a href="/backoffice/reports-preferred-users">Preferred Users</a><br/>
					<a href="/backoffice/reports-user-data-v2">Guest Details Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->supportsPlatePoints() ) { ?>
						<a href="/backoffice/reports-points-status-by-date">PLATEPOINTS Awards</a><br/>
					<?php } ?>
				</td>
			</tr>
		</table>
	</div>
</div>