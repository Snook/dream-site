<?php if (!empty($this->date_info['session_count'])) { ?>
<table id="date_details_table">
<tr>
	<td class="label" style="width: 126px;">Booked Guests</td>
	<td class="value" style="width: 90px;"><?php echo $this->date_info['booked_count']; ?></td>

	<td class="label" style="width: 120px;">Session Count</td>
	<td class="value"><?php echo $this->date_info['session_count']; ?></td>
</tr>
<tr>
	<td class="label" style="width: 126px;">Booked Standard</td>
	<td class="value" style="width: 90px;"><?php echo $this->date_info['booked_standard_slots']; ?></td>

	<td class="label" style="width: 120px;">Booked Intro</td>
	<td class="value"><?php echo $this->date_info['booked_intro_slots']; ?></td>
</tr>
<?php if (!empty($this->date_info['session_leads'])) { ?>
<tr>
	<td class="label">Leads</td>
	<td colspan="3" class="value">
	<?php $lead_count = 0; foreach ($this->date_info['session_leads'] AS $user_id => $lead) { ++$lead_count ?>
		<a href="/backoffice/user-details?id=<?php echo $user_id; ?>"><?php echo $lead['lead_firstname']; ?> <?php echo $lead['lead_lastname']; ?></a><?php echo (count($this->date_info['session_leads']) != $lead_count) ? ',' : ''; ?>
	<?php } ?>
	</td>
</tr>
<?php } ?>
</table>
<?php } ?>