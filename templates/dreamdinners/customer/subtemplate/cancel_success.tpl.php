<table style="width: 100%;">
	<tr>
		<td>The order was successfully canceled.</td>
	</tr>

	<?php foreach($this->cancel_result_array as $id => $msg)  {?>
		<tr>
			<td><?php echo $msg; ?></td>
		</tr>
	<?php } ?>
</table>