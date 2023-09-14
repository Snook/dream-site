<?php if($this->hasOtherMenuMonthOrders){ ?>
	<table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2"  class="form_field_cell" style="border:2px #808080 solid; margin-top:2px;">
		<tr>
			<td colspan="5" align="center" class ="form_subtitle_cell" style="background-color:#bcbcbc;font-weight:bold;">Other <?php echo $this->other_order_info[0]->menu_name; ?> Menu Orders</td>
		</tr>
		<tr>
			<td id="" style="text-align:left;">Order Number</td>
			<td id="" style="text-align:left;">Order Status</td>
			<td id="" style="text-align:left;">Session Type</td>
			<td id="" style="text-align:left;">Session Time</td>

		</tr>
		<?php if (is_array($this->other_order_info))
		{
			foreach ( $this->other_order_info as $otherOrder )
			{
				$additionalSessionProps = $otherOrder->session_obj->getSessionTypeProperties();
				$session_type_title = $additionalSessionProps[0]
				?>
				<tr>
					<td id="" style="text-align:left;"><a href="/?page=admin_order_mgr&order=<?php echo $otherOrder->id; ?>" target="_blank"><?php echo $otherOrder->id; ?></a></td>
					<td id="" style="text-align:left;text-transform:capitalize;"><span style="text-transform:capitalize;"><?php echo strtolower($otherOrder->status); ?></span></td>
					<td id="" style="text-align:left;"><?php echo  $session_type_title; ?></td>
					<td id="" style="text-align:left;"><?php echo  CTemplate::dateTimeFormat($otherOrder->session_start);  ?></td>
				</tr>
			<?php }
		} ?>
	</table>
<?php } ?>