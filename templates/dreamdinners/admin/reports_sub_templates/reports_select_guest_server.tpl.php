<div class="row">
	<div class="col-6">
		<table class="table ddtemp-table-border-collapse">
			<tr>
				<td><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<a href="/backoffice/reports-cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports-saved-orders">Saved Orders Report</a><br/>
					<?php if ($this->CurrentBackOfficeStore->isDistributionCenter()) { ?>
						<a href="/backoffice/reports-entree-delivered">Shipping Entr&eacute;e Report</a><br/>
					<?php } else { ?>
						<a href="/backoffice/reports-entree">Entr&eacute;e Report</a><br/>
					<?php } ?>
				</td>
			</tr>
		</table>

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
	</div>
</div>