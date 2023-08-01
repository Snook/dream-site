<input type="hidden" id="new_payments_total" name="new_payments_total" value="0">

<table border="0" width="100%">
<!--
	<tr>
		<td colspan="3">
		<center><span style="font-size: 16px;">Balance Due: <span
			id="0">$0.00</span></span></center>
		</td>
	</tr>
	<tr class="form_subtitle_cell">
		<td colspan="3">
			<center><span style="font-size: 16px;">New Payments Total: <span id="payments_msg">$0.00</span></span> <br />
			Payments will not be applied until you "Finalize All Changes"</center>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<hr align="left" style="padding: 1px; margin: 0px;" width="100%" size="1" noshade>
		</td>
	</tr>
	-->
		<tr>
		<td valign="top" colspan="3" class="form_subtitle_cell">
		<?php if (!empty($this->Store_Credits) || !empty($this->pendingCredits)) { ?>
		<div valign="top" id="store_credits" style="display: block;" class="form_subtitle_cell">
		<table width="100%" cellpadding="5" cellspacing="0" class="paymentFormTables" style="margin-bottom:5px;">
			<tr class="form_subtitle_cell">
				<td><?=$this->form_direct_order['use_store_credits_html'];?> <label for="use_store_credits"><b>Use Store Credit</b>&nbsp;
					(Customer has $<span id="eoTotalStoreCredit"><?=CTemplate::moneyFormat($this->total_store_credit)?></span> store credit available)</label></td>
			</tr>
			<tr>
				<td align="left" style="padding-left:15px;">
					Amount to use: $<?=$this->form_direct_order['store_credits_amount_html'];?><br />
				</td>
			</tr>
		</table>
			<?php if (isset($this->pendingCredits) && !empty($this->pendingCredits)) { ?>

		<table width="100%"  class="paymentFormTables" id="pending_credits_table" >
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
		<?php } else { ?>
		<div height="100%" id="no_store_credits" style="display: block; margin: 0px;">
		<table height="100%" width="100%" cellpadding="0" cellspacing="0" class="paymentFormTables">
			<tr class="form_subtitle_cell">
				<td height="100%" width="150" style="text-align:center;font-style:italic;">No Store Credit</td>
			</tr>
		</table>
		</div>
		<?php } ?></td>
	</tr>
	<tr>
		<td colspan="3">
			<hr align="left" style="padding: 1px; margin: 0px;" width="100%" size="1" noshade>
		</td>
	</tr>

	<tr>
	<td width="50%" valign="top">
<?php include $this->loadTemplate('admin/order_mgr_discounts_delivered.tpl.php');?>


</td>
	<td bgcolor="black" width="1"></td>
	<td width="50%" valign="top">
	<?php include $this->loadTemplate('admin/subtemplate/order_mgr_payment_forms.tpl.php');?>

<?php include $this->loadTemplate('admin/subtemplate/order_mgr_aux_payment_forms.tpl.php');?>

	</td>
</tr>
<tr>
 <td colspan="3">
 <?php include $this->loadTemplate('admin/subtemplate/order_mgr_payment_info.tpl.php');?>

 </td>
</tr>
</table>