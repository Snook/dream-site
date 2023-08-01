
<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
	<tr>
		<td colspan="2"class="space_section_head" style="text-align:center;"><span>RSVPs</span></td>
	</tr>
	<tr>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-RSVPs-rsvp_count">Total RSVP Guests</span></td>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-RSVPs-rsvp_upgrade_count">RSVP Upgrade Count</span></td>
	</tr>
	<tr>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_rsvp'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_rsvp_upgraded'];?></td>
	</tr>
</table>