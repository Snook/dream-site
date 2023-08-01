<table style="width:100%;">

<?php if ($this->rowcount && $this->rows) { ?>
<tr>
	<td class="bgcolor_medium header_row" width="80"></td>
	<td class="bgcolor_medium header_row">Name</td>
	<td class="bgcolor_medium header_row">Email</td>
	<td class="bgcolor_medium header_row">Guest ID<br /></td>
</tr>
<?php
	$counter = 0;
	foreach( $this->rows as $thisRow)
	{
?>
<tr class="bgcolor_<?php echo ($counter++ % 2 == 0) ? 'light' : 'lighter'; ?>">
	<td style="white-space:nowrap; padding-left:15px;"><button onclick="javascript:useGuestAccount(0, '<?php echo $thisRow['primary_email']; ?>', <?php echo $thisRow['id']; ?>, false, <?php echo (($thisRow['dream_rewards_version'] == 3 && ($thisRow['dream_reward_status'] == 1 || $thisRow['dream_reward_status'] == 3)) ? "true" : "false" ) ?>);">Select</button></td>
	<td style="white-space:nowrap; padding-left:15px;"><?php echo $thisRow['firstname']; ?> <?php echo $thisRow['lastname']; ?></td>
	<td style="white-space:nowrap; padding-left:15px;"><?php echo $thisRow['primary_email']; ?></td>
	<td style="white-space:nowrap; padding-left:15px;text-align:center;"><?php echo $thisRow['id']; ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr class="bgcolor_light">
<td colspan="4" style="text-align:center"><i>No guests found</i></td>
</tr>
<?php } ?>
</table>
