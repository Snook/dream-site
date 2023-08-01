<div id="cancel_parent">

	<table style="width: 100%;">
		<tr>
			<td>
				<p>Are you sure you want to cancel the order for: <b><?php echo $this->user->customer_name; ?> - <?php echo $this->dateTimeFormat($this->session->session_start, VERBOSE); ?> at <?php echo $this->user->store_name; ?></b></p>
				<?php if (!empty($this->user->is_in_plate_points)) { ?><p style="color: red;">If canceling to reschedule the guest into a new month, place the next months order before canceling this order to retain guests PLATEPOINTS modifier.</p><?php } ?>
			</td>
		</tr>
	</table>

	<?php
	if (isset($this->paymentArray) && $this->paymentArray)
	{
	$hasRefunds = false;
	?>
	<table style="width:100%">
		<tr>
			<td class="bgcolor_medium header_row" style="font-weight: bold;">Payment Type</td>
			<td class="bgcolor_medium header_row" style="font-weight: bold;">Payment Number</td>
			<td class="bgcolor_medium header_row" style="font-weight: bold;">Payment Amount</td>
			<td class="bgcolor_medium header_row" style="font-weight: bold;">Amount to Credit</td>
		</tr>
		<?php foreach ($this->paymentArray as $payment){

			$cap = $payment['amt'];
			if ($payment['type'] == CPayment::CC)
			{
				$cap = $payment['amt'] - $payment['refunded_amount'];
			}

			?>
			<tr class="form_field_cell">
				<td class="bgcolor_light" style="text-align:center;"><?php echo CPayment::getPaymentDescription($payment['type']); ?><?php if ($payment['type'] == CPayment::REFUND) $hasRefunds = true; ?></td>
				<td class="bgcolor_light" style="text-align:center;"><?php echo (!empty($payment['num'])) ? $payment['num'] : ''; ?><?php echo ($payment['is_deposit']) ? ' **DEPOSIT** ' : ''; ?></td>
				<td class="bgcolor_light" style="text-align:center;">$<?php echo $this->moneyFormat($payment['amt']); ?></td>
				<td class="bgcolor_light" style="text-align:center;">
					<?php if ($payment['can_credit'] ) { ?>
						<input type="hidden" id="id<?php echo $payment['id']; ?>" name="id<?php echo $payment['id']; ?>" value="<?php echo $payment['id']; ?>" />


					<?php if ($cap == 0) { ?>
						This payment has been fully refunded.
					<?php } else { ?>
							<input type="text" maxlength="6" size="6" data-payment_input="true" id="Amt<?php echo $payment['id']; ?>" name="Amt<?php echo $payment['id']; ?>" value="<?php echo $cap; ?>" onKeyPress="validateKey(event);" onKeyUp="validateAmount( this, <?php echo $cap ?>);" />
					<?php } ?>

					<?php } else if (!empty($payment['num']) && $payment['num'] == 'pending') { // pending delayed payment ?>
						<input type="hidden" id="Pnd<?php echo $payment['id']; ?>" name="Pnd<?php echo $payment['id']; ?>" value="<?php echo $payment['amt']; ?>" />Pending Delayed Payment will be cancelled.
					<?php } else if ($payment['type'] == CPayment::GIFT_CARD) { ?>
						<input type="hidden" id="Dgc<?php echo $payment['id']; ?>" name="Dgc<?php echo $payment['id']; ?>" value="<?php echo $payment['amt']; ?>" /> $<?php echo $payment['amt']; ?> converted to <br />store credit
					<?php } else { ?>
						<?php echo $payment['type']; ?>
					<?php }	?>
					<?php if ($cap > 0 && $payment['type'] == CPayment::CC && $payment['refunded_amount'] > 0) { ?>
							$<?= $payment['refunded_amount']?> already refunded.
					<?php } ?>

				</td>
			</tr>
		<?php } // foreach ?>
		<?php } // isset paymentarray ?>
	</table>

	<table style="width:100%">
		<tr>
			<td class="bgcolor_light" style="width: 115px; vertical-align: top;">
				Admin Order Note
			</td>
			<td>
				<textarea id="order_admin_notes_cancel" name="order_admin_notes_cancel" style="width: 98%;"><?php echo $this->order->order_admin_notes; ?></textarea>
			</td>
		</tr>
	</table>


    <div style="margin-top:10px;">
        <select id="cancellation_reason" name="cancellation_reason" value="" >
        <?php $options = CBooking::getCancellationReasonArray();
                foreach($options as $key => $name)
                {
                    echo "<option value='$key'>" . $name . "</option>";
                }
        ?>
        </select><br /><br />
        <input type="checkbox" id="declined_MFY" name="declined_MFY"  /><label for="declined_MFY">Declined Made for You option</label><br /><br />
        <input type="checkbox" id="declined_to_reschedule" name="declined_to_reschedule"  /><label for="declined_to_reschedule">Declined to reschedule</label><br /><br />
		<input type="checkbox" id="suppress_cancel_email" name="suppress_cancel_email"  /><label for="suppress_cancel_email">Suppress Cancellation Email sent to Guest</label>
	</div>

	<?php if (!empty($this->order->membership_id)) { ?>
	<div style="margin-top:10px;">
		*Meal Prep+ canceled orders will not receive any emails from the Salesforce cancellation journey. Please accurately select the reason why they canceled from the drop down above.
	</div>
	<?php } ?>
</div>