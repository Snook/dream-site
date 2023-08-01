<table style="width: 100%;">
	<tr>
		<td>The Order was Successfully Cancelled.</td>
	</tr>

	<?php foreach($this->cancel_result_array as $id => $msg)  {?>
		<tr>
			<td><?php echo $msg; ?></td>
		</tr>
	<?php } ?>
</table>