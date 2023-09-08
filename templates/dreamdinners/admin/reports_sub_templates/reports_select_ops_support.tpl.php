<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><a href="?page=admin_main"><b>BackOffice Home</b></a></td>
	</tr>
	<tr>
		<td class="form_subtitle_cell"><b>Guests &amp; Orders</b></td>
	</tr>
	<tr>
		<td>
			<a href="?page=admin_reports_entree">Entr&eacute;e Report</a><br/>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="?page=admin_reports_growth_scorecard">Growth Scorecard</a><br/>
			<?php } ?>
		</td>
	</tr>
</table>

<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><b>Supporting Menu Materials</b></td>
	</tr>
	<tr>
		<td>
			<a href="?page=admin_session_tools_printing">Generic Menu Supporting Documents</a><br/>
			<a href="?page=admin_reports_customer_menu_item_labels&interface=1">Generic Cooking Instruction Labels</a><br/>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="?page=admin_reports_menu_item_nutritional_labels">Nutritional Labels</a><br/>
			<?php } ?>
	</tr>
</table>