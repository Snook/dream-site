		<?php if (isset($this->PendingDP)) { ?>
		<div id="dp_adjust_down_div" style="display:none;">
			<table width="100%">
			<tr>
				<td valign="top" class="form_subtitle_cell" colspan="2">
					<h3 align="center">Adjust Delayed Payment</h3>
				</td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="1" align="right">Original Transaction:</td>
				<td><?=$this->PendingDPOriginalTransActionID ?></td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="1" align="right">Current Amount:</td>
				<td class="form_field_cell">$ <?=$this->PendingDPAmount ?></td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="1" align="right">New Delayed Payment Amount:</td>
				<td class="form_field_cell">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">$</div>
						</div>
						<input class="form-control" type="text" name="adj_dp_amount_down" id="adj_dp_amount_down" value="<?=$this->PendingDPAmount ?>" disabled="disabled" size="7" />
					</div>
				</td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="1" align="right">Adjustment Amount:</td>
				<td class="form_field_cell">- $ <span id="adjustment_amount_down">0.00</span></td>
			</tr>
			</table>
		</div>
		<br />
		<?php } ?>

		<div id="creditDiv">
			<table border="0" width="100%">
			<tr>
				<td colspan="2" valign="top" class="form_subtitle_cell"><h3 align="center">Credit to Customer</h3></td>
			</tr>
			<tr class="form_subtitle_cell"><td colspan="2"><b>Refund Cash</b></td></tr>
			<tr>
				<td class="form_field_cell">Please enter cash Amount</td>
				<td class="form_field_cell">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">$</div>
						</div>
						<input class="form-control" type="text" name="credit_to_customer_refund_cash_amount" id="credit_to_customer_refund_cash_amount" onkeyup="validateRefTrans(this, 10000);"><br />(Dollar amount 00.00)
					</div>
				</td>
			</tr>
			<tr>
				<td class="form_field_cell">Payment Number</td>
				<td class="form_field_cell" style="padding-left:6px;"><input class="form-control" type="text" name="credit_to_customer_refund_cash_number" id="credit_to_customer_refund_cash_number" ></td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="2"><hr /></td>
			</tr>

			<?php
			     $RefundCCCount = 0;
				if (!empty($this->refTransArray)) {
				foreach($this->refTransArray as $id => $val)
				{
					if ($id !== 0 )
					{
					    $cap = $val['amount'];
					    if (!empty($val['current_refunded_amount']))  {
					        $cap = $val['amount'] - $val['current_refunded_amount'];
					    }

			?>
			<tr class="form_subtitle_cell"><td colspan="2"><b>Refund <?=$val['cc_type']?> <?=substr($val['card_number'],strlen($val['card_number']) - 4,4)?></b></td></tr>
			<tr>
				<td class="form_field_cell">Transaction Number</td>
				<td class="form_field_cell"><?=$id?></td>
			</tr>


	<?php 	if ($cap == 0) { ?>
    		<tr>
				<td class="form_field_cell">Max amount</td>
				<td class="form_field_cell"><span style="color:red">This payment has been fully refunded</span></td>
			</tr>
    <?php }  else if (!empty($val['current_refunded_amount']))  { ?>
    		<tr>
				<td class="form_field_cell">Max amount</td>
				<td class="form_field_cell">$<?=CTemplate::moneyFormat($val['amount'] - $val['current_refunded_amount']) ?> <span style="font-size:smaller;">( Orig. Amount: $<?=$val['amount']?> <br />minus $<?=$val['current_refunded_amount']?> already refunded )</span></td>
			</tr>
	<?php } else { ?>
	        <tr>
				<td class="form_field_cell">Max amount</td>
				<td class="form_field_cell">$<?=$val['amount']?></td>
		    </tr>
	<?php } ?>
			</tr>
			<tr>
				<td class="form_field_cell">Amount to Credit</td>
				<td class="form_field_cell">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">$</div>
						</div>
						<input class="form-control" type="text" name="Cr_RT_<?=$id?>" id="Cr_RT_<?=$id?>" onkeyup="validateRefTrans(this, <?=$cap?>);" <?php if ($cap == 0) echo 'disabled="disabled"';?> /><br />(Dollar amount 00.00)
					</div>
				</td>
			</tr>
			<tr>
				<td class="form_field_cell" colspan="2"><hr /></td>
			</tr>

			<?php
			 } } }
			?>

			<?php if (isset($this->storeCreditPaymentTotal)) { ?>
			<tr class="form_subtitle_cell"><td colspan="2"><b>Refund Store Credit</b></td></tr>
			<tr>
				<td class="form_field_cell">Store Credit Payment Total</td>
				<td class="form_field_cell"><?=$this->storeCreditPaymentTotal?></td>
			</tr>
			<tr>
				<td class="form_field_cell">Amount to Credit</td>
				<td class="form_field_cell">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">$</div>
						</div>
						<input class="form-control" type="text" name="storeCreditRefund" id="storeCreditRefund" onkeyup="validateRefTrans(this, <?=$this->storeCreditPaymentTotal?>);"><br />(Dollar amount 00.00)
					</div>
				</td>
			</tr>
			<?php } ?>

			<?php if (isset($this->debitGiftCardPaymentTotal)) { ?>
			<tr class="form_subtitle_cell"><td colspan="2"><br /><b>Refund Gift Card</b><br />(Convert to Store Credit)</td></tr>
			<tr>
				<td class="form_field_cell">Gift Card Payment Total</td>
				<td class="form_field_cell"><?=$this->debitGiftCardPaymentTotal?></td>
			</tr>
			<tr>
				<td class="form_field_cell">Amount to Credit</td>
				<td class="form_field_cell">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">$</div>
						</div>
						<input class="form-control" type="text" name="giftCardRefund" id="giftCardRefund" onkeyup="validateRefTrans(this, <?=$this->debitGiftCardPaymentTotal?>);"><br />(Dollar amount 00.00)
					</div>
				</td>
			</tr>
			<?php } ?>


			</table>
		</div>
