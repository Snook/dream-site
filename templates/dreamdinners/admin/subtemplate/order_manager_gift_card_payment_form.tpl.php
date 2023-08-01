
<div id="payment1_debit_gift_card" style="display: none;">
	<table width="100%">
		<tr>
			<td valign="top" align="right" class="form_field_cell">Card Number</td>
			<td class="form_field_cell">
				<input type="text" autocomplete='off'
											   class="form-control"
				name="debit_gift_card_number" id='debit_gift_card_number' /><br />
				<a href="javascript:getGiftCardBalance();">[Check Balance]</a><br />
				<label id="debit_gift_card_number_lbl"
				name="debit_gift_card_number_lbl"
				message="Please enter the gift card number."></label>
			</td>
		</tr>
		<tr>
			<td valign="top"  align="right" class="form_field_cell">Amount to Redeem</td>
			<td class="form_field_cell"><input type="text" class="form-control"
				name="debit_gift_card_amount" id="debit_gift_card_amount" value=""
				onkeyup="payAmountChange(this.value);" data-money="true" /><br /> <label
				id="debit_gift_card_amount_lbl" name="debit_gift_card_amount_lbl"
				message="Please enter the gift card amount."></label>
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2" class="form_field_cell"><span
				id="balance_target" style="display: none"></span> <span
				id="balance_proc_mess" class="warning_text" style="display: none"></span>
				<span id="gc_proc_anim" style="display: none;"><img
					src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif"
					alt="Processing" /> </span> <span id="gc_success_msg"
				style="display: none"></span> <span id="gc_proc_mess"
				class="warning_text" style="display: none"></span></td>
		</tr>
	</table>
</div>
