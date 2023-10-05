<?php $this->assign('page_title', 'Load Gift Card'); ?>
<?php $this->assign('helpLinkSection', 'GC_LOAD'); ?>
<?php $this->assign('topnav', 'giftcards'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/gift_card.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/pace.css'); ?>
<?php $this->setScriptVar('store_id = ' . $this->store_id . ';'); ?>
<?php if (defined('TR_SIM_LINK'))
{
	$this->setScriptVar("transparent_redirect_link = '" . TR_SIM_LINK . "';");
}

if (defined('PFP_TEST_MODE') && PFP_TEST_MODE)
{
	$this->setScriptVar("pfp_test_mode = true;");
}

if (defined('PHP_ERROR_URL'))
{
	$this->setScriptVar("payflowErrorURL = '" . PHP_ERROR_URL . "';");
}
?>

<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<iframe name="paypal-result" id="paypal-result" style="display:none;"></iframe>

	<form name="gift_card_load" id="gift_card_load" action="" method="post">
		<?= $this->form_account['hidden_html']; ?>

		<div style="width:100%;">
			<div style="width:600px; margin: 0 auto;">

				<table>
					<?php if (isset($this->form_account['store_html'])) { ?>
					<tr class="form_subtitle_cell">
						<td align="center" colspan="3" style="padding: 5px;"><b>Selected Store:</b> <?= $this->form_account['store_html']; ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td style="text-align: right;"><label id="gift_card_number_lbl" for="gift_card_number" message="Please enter a gift card number.">*Gift Card Number:</label></td>
						<td width="150"><?php echo $this->form_account['gift_card_number_html']; ?></td>
						<td width="170"><a href="javascript:codes='';i=0;document.getElementById('gift_card_number').value = ''; document.getElementById('gift_card_number').focus();" class="btn btn-primary btn-sm">Activate card swipe</a></td>
					</tr>
					<tr id="gift_card_amount">
						<td style="text-align: right;"><label id="amount_lbl" for="amount" message="Please enter an amount.">*Amount to Load:</label></td>
						<td colspan="2">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">$</div>
								</div>
								<?php echo $this->form_account['amount_html']; ?>
							</div>
							<span class="form_explain">(numbers only, 25 to 500 dollars)</span>
						</td>
					</tr>
					<tr>
						<td colspan="3" class="bgcolor_light">Email Address (Optional, but required in order to receive a confirmation email)</td>
					</tr>
					<tr id="email_address">
						<td style="text-align: right;"><label id="primary_email_lbl" for="primary_email">Email Address:</label></td>
						<td colspan="2"><?php echo $this->form_account['primary_email_html']; ?></td>
					</tr>
					<tr id="confirm_email_address">
						<td style="text-align: right;"><label id="confirm_email_address_lbl" for="confirm_email_address" message="Email addresses do not match">Confirm Email Address:</label></td>
						<td colspan="2"><?php echo $this->form_account['confirm_email_address_html']; ?></td>
					</tr>
					<tr>
						<td colspan="3" class="bgcolor_light">Payment Details (*=Required)</td>
					</tr>
					<tr id="billing_name_tr">
						<td style="text-align: right;"><label id="billing_name_lbl" for="billing_name" message="Please enter the name on the Credit Card ">*Name on Credit Card:</label></td>
						<td colspan="2"><?php echo $this->form_account['billing_name_html']; ?></td>
					</tr>
					<tr id="billing_address_tr">
						<td style="text-align: right;"><label id="billing_address_lbl" for="billing_address" message="Please enter the Billing Address">*Billing Street Address:</label></td>
						<td colspan="2"><?php echo $this->form_account['billing_address_html']; ?></td>
					</tr>
					<tr id="billing_city_tr">
						<td style="text-align: right;"><label id="billing_city_lbl" for="billing_city" message="Please enter the Billing City">*Billing City:</label></td>
						<td colspan="2"><?php echo $this->form_account['billing_city_html']; ?></td>
					</tr>
					<tr id="billing_state_tr">
						<td style="text-align: right;"><label id="billing_state_lbl" for="billing_state" message="Please enter the Billing State">*Billing State:</label></td>
						<td colspan="2"><?php echo $this->form_account['billing_state_id_html']; ?></td>
					</tr>
					<tr id="billing_zip_tr">
						<td style="text-align: right;"><label id="billing_zip_lbl" for="billing_zip" message="Please enter a billing zip code.">*Billing Zip:</label></td>
						<td colspan="2"><?php echo $this->form_account['billing_zip_html']; ?> <span class="form_explain"></span></td>
					</tr>
					<tr id="ccNumber">
						<td style="text-align: right;"><label id="credit_card_type_lbl" for="credit_card_type" message="Please enter a Credit Card Type">*Credit Card Type:</label></td>
						<td colspan="2"><?php echo $this->form_account['credit_card_type_html']; ?></td>
					</tr>
					<tr id="ccNumber">
						<td style="text-align: right;"><label id="credit_card_number_lbl" for="credit_card_number" message="Please enter a Credit Card Number">*Credit Card Number:</label></td>
						<td colspan="2"><?php echo $this->form_account['credit_card_number_html']; ?> <span class="form_explain">(No spaces or dashes)</span></td>
					</tr>
					<tr id="ccExp">
						<td style="text-align: right;"><label id="credit_card_exp_year_lbl" for="credit_card_exp_year" message="Please enter an expiration date year."></label><label id="credit_card_exp_month_lbl" for="credit_card_exp_month" message="Please enter an expiration date month.">*Expiration Date(MM/YY):</label></td>
						<td colspan="2"><?php echo $this->form_account['credit_card_exp_month_html'] . ' ' . $this->form_account['credit_card_exp_year_html']; ?></td>
					</tr>
					<tr id="ccCVV">
						<td style="text-align: right;"><label id="credit_card_cvv_lbl" for="credit_card_cvv" message="Please enter a CVV number.">*CVV number:</label></td>
						<td colspan="2"><?php echo $this->form_account['credit_card_cvv_html']; ?> <a href="http://en.wikipedia.org/wiki/Card_Verification_Value" target="_blank"><span class="form_explain">(What is this?)</span></a></td>
					</tr>
					<tr id="submit">
						<td style="text-align: right;"></td>
						<td colspan="2"><input onclick="_submit_click(this); return false;" id="procCardLoadBtn" type="submit" value="Process Card Load" class="btn btn-primary btn-sm" /></td>
					</tr>
				</table>
			</div>
		</div>
	</form>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>