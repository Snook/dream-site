<table style="width: 100%; margin-top: 10px;">
<?php if ($this->rowcount && $this->rows) { ?>
<tr>
	<td class="bgcolor_medium header_row"></td>
	<td class="bgcolor_medium header_row">Name</td>
	<td class="bgcolor_medium header_row">Email</td>
</tr>
<?php $counter = 0; foreach( $this->rows as $thisRow) { ?>
<tr class="bgcolor_<?php echo ($counter++ % 2 == 0) ? 'light' : 'lighter'; ?>">
	<td style="white-space:nowrap; padding-left:15px;">
		<button class="button"
			data-ilgs_result="<?php echo $counter; ?>"
			data-user_id="<?php echo $thisRow['id']; ?>"
			data-firstname="<?php echo $thisRow['firstname']; ?>"
			data-lastname="<?php echo $thisRow['lastname']; ?>"
			data-telephone_1="<?php echo $thisRow['telephone_1']; ?>"
			data-primary_email="<?php echo $thisRow['primary_email']; ?>"><?php echo (!empty($this->select_button_title)) ? $this->select_button_title : 'Select'; ?></button>
	</td>
	<td style="white-space:nowrap; padding-left:15px;"><?php echo $thisRow['firstname']; ?> <?php echo $thisRow['lastname']; ?></td>
	<td style="white-space:nowrap; padding-left:15px;"><?php echo $thisRow['primary_email']; ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr class="bgcolor_light">
	<td colspan="4" style="text-align: center; padding: 10px;">No guests found. Search again or <a href="?page=admin_account" class="button">Create Account</a></td>
</tr>
<?php } ?>
</table>