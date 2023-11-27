<table role="presentation" border="1" width="100%" style="border-style:solid; border-width:1px; border-collapse: collapse;">
	<?php foreach ($this->gift_card_order AS $order) { ?>
		<tr>
			<?php foreach ($order AS $coldata) { ?>
				<td><?php echo $coldata; ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
</table>