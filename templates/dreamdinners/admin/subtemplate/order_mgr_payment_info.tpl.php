<?php if ( isset($this->paymentInfo) ) { ?>
	<table border="0" width="100%">
		<tr align="center" class="form_subtitle_cell">
			<td colspan="2">
				<h3>Existing Payments</h3>
			</td>
		</tr>
		<?php
		$counter = 1;
		foreach ( $this->paymentInfo as $arrItem )
		{
			$payment_is_delayed_payment = (isset($arrItem['is_delayed_payment']) && $arrItem['is_delayed_payment']);
			$payment_delayed_payment_status = (isset($arrItem['delayed_payment_status']) && $arrItem['delayed_payment_status']);
			$payment_type = '';
			$payment_id = '';

			if (is_array($arrItem))
			{
				$payment_type = $arrItem['payment_type'];
				$payment_id = $arrItem['id'];
				echo '<tr class="form_subtitle_cell"><td colspan="2"><b>Payment Number ' . $counter++ . '</b></td></tr>' . "\n";

				$currentPaymentRow = 1;
				$paymentRowCount = count($arrItem);
				$showProcessButton = false;

				foreach ( $arrItem as $itemtype => $subitem )
				{
					$currentPaymentRow++;

					if (is_array($subitem))
					{
						echo '<tr>' . "\n";

						$spanId = false;

						foreach ( $subitem as $key => $item )
						{
							$spanId = strtolower($payment_type) . '_' . $itemtype . '_' . $key;

							if($payment_is_delayed_payment)
							{
								if(!empty($this->form_direct_order['point_to_transaction_html']) && $spanId == 'cc_payment_number_other' && $this->hasDelayedPayment == true)
								{
									$showProcessButton = true;
									echo '<td width="50%"><span id="' . $spanId . '">' . $this->form_direct_order['point_to_transaction_html'] . '</span><img src="' . ADMIN_IMAGES_PATH . '/icon/accept.png" id="point_to_transaction_img" style="display:none;vertical-align:middle;margin-left:4px;margin-right:4px;" alt="Accept" /></td>' . "\n";
								}
								else if (!empty($this->form_direct_order['change_delayed_payment_status_html'] )&& $spanId == 'cc_delayed_status_other' && $this->hasDelayedPayment == true)
								{
									$showProcessButton = true;
									echo '<td width="50%"><span id="' . $spanId . '"><div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">Status <img src="' . ADMIN_IMAGES_PATH . '/icon/' . (($payment_delayed_payment_status != 'FAIL') ? 'accept' : 'error') . '.png" id="change_delayed_payment_status_img" style="display:' . (($payment_delayed_payment_status != 'FAIL') ? 'none' : 'inline') . ';vertical-align:middle;margin-left:4px;margin-right:4px;" alt="' . (($payment_delayed_payment_status != 'FAIL') ? 'accept' : 'error') . '" /></div>
								</div>' . $this->form_direct_order['change_delayed_payment_status_html'] . '</span></div></td>';
								}
								else
								{
									echo '<td width="50%"><span id="' . $spanId . '">' . $item . '</span></td>' . "\n";
								}
							}
							else if($payment_type == 'CHECK')
							{
								if($spanId == 'check_total_other')
								{
									?>
									<td width="50%">
										<span id="check_total_<?php echo $payment_id; ?>"><?php echo $item; ?></span>
										<span id="check_total_input_<?php echo $payment_id; ?>" style="display:none;">
												<input class="form-control" type="text" id="check_total_input_form_<?php echo $payment_id; ?>" name="check_edit_total[<?php echo $payment_id; ?>]" size="6" value="<?php echo $item; ?>" onKeyup="editCashCheckUpdate('<?php echo $payment_id; ?>','<?php echo strtolower($payment_type); ?>','this.value');" />
												<input class="btn btn-primary" style="float:right;" type="button" value="Commit" onclick="editCashCheckAmountCommit('<?php echo $payment_id; ?>','<?php echo strtolower($payment_type); ?>')"/>
											</span>
										<span id="check_editlink_<?php echo $payment_id; ?>" style="float:right;"><a href="javascript:editCashCheckAmount('<?php echo $payment_id; ?>','<?php echo strtolower($payment_type); ?>');">[Edit]</a></span>
										<span id="check_total_mess_<?php echo $payment_id; ?>" style="float:right;"></span>
									</td>
									<?php
								}
								else if($spanId == 'check_payment_number_other')
								{
									?>
									<td width="50%">
										<span id="check_payment_number_<?php echo $payment_id; ?>"><?php echo $item; ?></span>
										<span id="check_payment_number_input_<?php echo $payment_id; ?>" style="display:none;">
												<input class="form-control" type="text" id="check_payment_number_input_form_<?php echo $payment_id; ?>" name="check_edit_number[<?php echo $payment_id; ?>]" size="6" value="<?php echo $item; ?>" onKeyup="editCashCheckUpdate('<?php echo $payment_id; ?>','<?php echo strtolower($payment_type); ?>','this.value');" />
											</span>
									</td>
									<?php
								}
								else
								{
									echo '<td width="50%">' . $item . '</td>' . "\n";
								}
							}
							else
							{
								echo '<td width="50%">' . $item . '</td>' . "\n";
							}
						}

						echo '</tr>' . "\n";

						if ($currentPaymentRow == $paymentRowCount +1 && $showProcessButton)
						{
							if($payment_delayed_payment_status == 'PENDING')
							{
								echo '<tr>
										<td colspan="2" style="text-align:right;"><span id="payment_proc_mess"><input type="button" class="button" name="process_payment" value="Process delayed payment now" onclick="processPayment();"></span></td>
									</tr>';
							}
							else
							{
								echo '<tr>
										<td colspan="2" style="text-align:right;"><span id="payment_proc_mess"></span></td>
									</tr>';
							}
						}
					}
				}
			}
		}
		?>
	</table>
<?php } ?>