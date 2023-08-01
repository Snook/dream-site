<div id="cancel_parent">

	<table style="width: 100%;">
		<tr>
			<td>
				<p>Are you sure you want to cancel the order set to be delivered on <?php echo $this->dateTimeFormat($this->session->session_start, MONTH_DAY_YEAR); ?>?</p>
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
			<td class="bgcolor_medium header_row font-weight-bold">Payment Type</td>
			<td class="bgcolor_medium header_row font-weight-bold">Amount</td>
			<td class="bgcolor_medium header_row font-weight-bold">Status</td>
		</tr>
		<?php foreach ($this->paymentArray as $payment){

			$cap = $payment['amt'];
			if ($payment['type'] == CPayment::CC)
			{
				$cap = $payment['amt'] - $payment['refunded_amount'];
			}

			?>
			<tr class="form_field_cell">
				<td class="bgcolor_light" style="text-align:left;"><?php echo CPayment::getPaymentDescription($payment['type']); ?><?php if ($payment['type'] == CPayment::REFUND) $hasRefunds = true; ?></td>
				<td class="bgcolor_light" style="text-align:left;">$<?php echo $this->moneyFormat($payment['amt']); ?></td>
				<td class="bgcolor_light" style="text-align:left;">
					<?php if ($payment['can_credit'] ) { ?>
						<input type="hidden" id="id<?php echo $payment['id']; ?>" name="id<?php echo $payment['id']; ?>" value="<?php echo $payment['id']; ?>" />
						<?php if ($cap == 0) { ?>
							This payment has been fully refunded.
						<?php } else { ?>
							<input type="hidden" id="Amt<?php echo $payment['id']; ?>" name="Amt<?php echo $payment['id']; ?>" value="<?php echo $cap; ?>" />
						<?php } ?>
					<?php } else if (!empty($payment['num']) && $payment['num'] == 'pending') { // pending delayed payment ?>
						<input type="hidden" id="Pnd<?php echo $payment['id']; ?>" name="Pnd<?php echo $payment['id']; ?>" value="<?php echo $payment['amt']; ?>" />
					<?php } else if ($payment['type'] == CPayment::GIFT_CARD) { ?>
						<input type="hidden" id="Dgc<?php echo $payment['id']; ?>" name="Dgc<?php echo $payment['id']; ?>" value="<?php echo $payment['amt']; ?>" />
						$<?php echo $payment['amt']; ?> refunded to <br />gift card
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

    <div style="margin-top:10px;">
		<span id="cancellation_reason_required_msg" style="display:none">Please seletect a reason for cancelling this order.</span>
        <select class="custom-select" id="cancellation_reason" name="cancellation_reason" value="" >
        <?php $options = CBooking::getCustomerCancellationReasonArray();
                foreach($options as $key => $name)
                {
                    echo "<option value='$key'>" . $name . "</option>";
                }
        ?>
        </select><br /><br />
    </div>
</div>