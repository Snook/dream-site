<input type="hidden" id="new_payments_total" name="new_payments_total" value="0">

<div class="row">
	<div class="col-lg-6 border-right border-dark">
		<?php include $this->loadTemplate('admin/order_mgr_discounts.tpl.php');?>
	</div>
	<div class="col-lg-6">

		<?php if (!empty($this->Store_Credits) || !empty($this->pendingCredits)) { ?>
			<div valign="top" id="store_credits" style="display: block;" class="form_subtitle_cell">

				<div class="row">
					<div class="col my-2">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<?=$this->form_direct_order['use_store_credits_html'];?>
									<span id="eoTotalStoreCredit" class="collapse"><?=CTemplate::moneyFormat($this->total_store_credit)?></span>
								</div>
							</div>
							<?=$this->form_direct_order['store_credits_amount_html'];?>
							<div class="input-group-append">
								<div class="input-group-text">
									Max $<span id="eoTotalStoreCredit"><?=CTemplate::moneyFormat($this->total_store_credit)?></span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<?php if (isset($this->pendingCredits) && !empty($this->pendingCredits)) { ?>

							<table class="paymentFormTables" id="pending_credits_table" >
								<tr class="form_subtitle_cell">
									<td colspan="4"><b>Pending Store Credit</b></td>
								</tr>
								<tr>
									<td></td>
									<td><i>Amount</i></td>
									<td><i>Source</i></td>
									<td><i>Scheduled Award Date</i></td>
								</tr>
								<?php foreach ($this->pendingCredits as $thisPendingCredit) { ?>
									<tr id="pscr_<?php echo $thisPendingCredit['credit_id']?>" class="bgcolor_lighter">
										<td><button onclick="javascript:return eo_process_pending_credit_now(<?php echo $thisPendingCredit['user_id']?>,
											<?php echo $thisPendingCredit['credit_id']?>,<?php echo $thisPendingCredit['referred_guest_id']?>,
											<?php echo $thisPendingCredit['store_id']?> );">Process Now</button><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" alt="Processing" /></td>
										<td>$<span id="psc_"><?=$thisPendingCredit['amount']?></span></td>
										<td>Referral</td>
										<td><font color="red"><i>Pending - <?=$thisPendingCredit['award_date']?></i></font></td>
									</tr>
								<?php } ?>
							</table>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div height="100%" id="no_store_credits" style="display: block; margin: 0px;">
				<table class="paymentFormTables">
					<tr class="form_subtitle_cell">
						<td height="100%" width="150" style="text-align:center;font-style:italic;">No Store Credit</td>
					</tr>
				</table>
			</div>
		<?php } ?>


		<?php include $this->loadTemplate('admin/subtemplate/order_mgr_payment_forms.tpl.php');?>
		<?php include $this->loadTemplate('admin/subtemplate/order_mgr_aux_payment_forms.tpl.php');?>
	</div>
</div>

<div class="row">
	<div class="col">
		<?php include $this->loadTemplate('admin/subtemplate/order_mgr_payment_info.tpl.php');?>
	</div>
</div>
<br>

<?php if( $showExpiredFootNote ){ ?>
<div style="border:1px #bbc56f solid; margin:5px; padding:10px;">
	<table>
		<tbody>
		<tr>
			<td style="vertical-align:top"><span style="font-weight:bold">**</span></td>
			<td>These Dinner Dollars were applied to an order before their expiration date and are valid on the order they are applied to. However, they have expired and cannot be transferred to a different order. If the order is cancelled the Dinner Dollars will be lost. Any edits made to the order that include a reimbursement of those Dinner Dollars will be expired and not usable on other orders.</td>
		</tr>
		</tbody>
	</table>
</div>
<?php } ?>