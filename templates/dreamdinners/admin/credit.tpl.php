<?php $this->assign('page_title','Credit History'); ?>
<?php $this->assign('topnav','sessions'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_credits.min.js'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<form name="add_credit" action="" method="post" onSubmit="return _check_form(this);" >

<?php $count = 0; ?>
<table width="1200"><tr><td>
<?php if (isset($_REQUEST['back'])) { ?>
	<input type="button" class="btn btn-primary btn-sm" value="Back" onClick="window.location = '<?= $_REQUEST['back']?>';">
<?php } else { ?>
	<input type="button" class="btn btn-primary btn-sm" value="Back" onClick="window.location = '/backoffice/user_details?id=<?= $this->customer_id?>';">
<?php } ?>

</td><td width="95%" style="text-align:center;"><h3>Viewing Credits for: <?=$this->customer_first?> <?=$this->customer_last?></h3>
</td></tr></table>

<br />
<?php if (isset($this->form['store_html'])) { ?>
	<strong>Store:</strong> <?php echo $this->form['store_html']; ?><br /><br />
<?php } ?>


<div style="text-align:center">
<h2>Store Credit</h2>
</div>
<table width="100%" border="1">
<tr>
	<td class="bgcolor_dark catagory_row" rowspan="3" style="text-align:right;">Current Store Credit Totals:</td>
</tr>
<tr>
	<td class="bgcolor_medium header_row">Available Credit</td>
	<td class="bgcolor_medium header_row">Pending Credit</td>
	<td class="bgcolor_medium header_row">Applicable Store</td>
</tr>
<?php if (!empty($this->TotalsArray)) { ?>
<?php foreach ($this->TotalsArray as $id => $row) { ?>
<tr>
	<td id="sctc_<?=$row['store_id']?>" class="bgcolor_light"  style="text-align:center;">$<?php echo CTemplate::moneyFormat(!empty($row['available']) ? $row['available'] : 0);?></td>
	<td class="bgcolor_light" style="text-align:center;">$<?php echo CTemplate::moneyFormat(!empty($row['pending']) ? $row['pending'] : 0);?></td>
	<td class="bgcolor_light" style="text-align:center;"><?= $row['store_name']?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
	<td class="bgcolor_light" colspan="3" style="text-align:center;"><i>There is no available store credit for this customer</i></td>
</tr>
<?php } ?>
</table>





<table id="avail_credits_tbl" width="100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="8" style="text-align:center;">Available Store Credit</td>
</tr>
<tr id="avail_credits_header_row">
	<td class="bgcolor_medium header_row">Credit Type</td>
	<td class="bgcolor_medium header_row">Date Credit Awarded</td>
	<td class="bgcolor_medium header_row">Name of Guest Referred</td>
	<td class="bgcolor_medium header_row">Notes</td>
	<td class="bgcolor_medium header_row">Store</td>
	<td class="bgcolor_medium header_row">Expiration</td>
	<td class="bgcolor_medium header_row">Credit Amount</td>
		<td class="bgcolor_medium header_row">Operation</td>
</tr>
<?php if (!empty($this->IAFArray)) {
	foreach ($this->IAFArray as $id => $row) { $count++;?>
<tr id="scr_<?= $row['sc_id']?>">
	<td class="bgcolor_light">Referral - <?= ($row['origination_type_code'] == 1 ? "IAF" : "Direct")?></td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['origination_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['referred_user']?></td>
	<td class="bgcolor_light"></td>
	<td class="bgcolor_light"><?= $row['store_name']?></td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['expiration_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['amount']?></td>
		<td class="bgcolor_light"><button onclick="return delete_store_credit(<?php echo $row['sc_id']; ?>,<?php echo $row['user_id']; ?>);">Delete</button></td>
</tr>
<?php } }?>
<?php if (!empty($this->TODDArray)) {
	foreach ($this->TODDArray as $id => $row) {  $count++;?>
<tr id="scr_<?= $row['sc_id']?>">
	<td class="bgcolor_light">Taste Referral</td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['origination_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['referred_user']?></td>
	<td class="bgcolor_light">Event Date: <?= $row['event_date']?></td>
	<td class="bgcolor_light"><?= $row['store_name']?></td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['expiration_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['amount']?></td>
		<td class="bgcolor_light"><button onclick="return delete_store_credit(<?= $row['sc_id']?>,<?= $row['user_id']?>);">Delete</button></td>
</tr>
<?php } }?>
<?php if (!empty($this->DirectArray)) {
	foreach ($this->DirectArray as $id => $row) {  $count++; ?>
<tr id="scr_<?= $row['sc_id']?>">
	<td class="bgcolor_light">Entered by Store</td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['origination_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"></td>
	<td class="bgcolor_light"><?= $row['description']?> [added by <?= $row['adder_email']?>]</td>
	<td class="bgcolor_light"><?= $row['store_name']?></td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['expiration_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['amount']?></td>
		<td class="bgcolor_light"><button onclick="return delete_store_credit(<?= $row['sc_id']?>,<?= $row['user_id']?>);">Delete</button></td>

</tr>
<?php }	} ?>



<?php if (!empty($this->GCArray)) {
	foreach ($this->GCArray as $id => $row) {  $count++; ?>
<tr id="scr_<?= $row['sc_id']?>">
	<td class="bgcolor_light">Gift Card Refund</td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['origination_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light">N/A</td>
	<td class="bgcolor_light"></td>
	<td class="bgcolor_light"><?= $row['store_name']?></td>
	<td class="bgcolor_light">N/A</td>
	<td class="bgcolor_light"><?= $row['amount']?></td>
		<td class="bgcolor_light"><button onclick="return delete_store_credit(<?= $row['sc_id']?>,<?= $row['user_id']?>);">Delete</button></td>
</tr>
<?php }	} ?>



<?php if (!empty($this->pendingArray)) {
	foreach ($this->pendingArray as $id => $row) {  $count++; ?>
<tr>
	<td class="bgcolor_lighter">Referral - <font color="red">pending</font></td>
	<td class="bgcolor_lighter"><?= CTemplate::dateTimeFormat($row['award_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_lighter"><?= $row['referred_guest']?></td>
	<td class="bgcolor_lighter"></td>
	<td class="bgcolor_lighter"><?= $row['store_name']?></td>
	<td class="bgcolor_lighter"><?= CTemplate::dateTimeFormat($row['expiration_date'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_lighter"><?= CTemplate::moneyFormat($row['amount'])?></td>
		<td class="bgcolor_lighter"></td>

</tr>
<?php }	} ?>


<?php if (!$count) { ?>
<tr>
	<td class="bgcolor_light" colspan="8" style="text-align:center;"><i>There is no available store credit for this customer</i></td>
</tr>
<?php } ?>
</table>
</div>


<table width="100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="6" style="text-align:center;">Used and Expired Store Credit</td>
</tr>
<tr>
	<td class="bgcolor_medium header_row">Status</td>
	<td class="bgcolor_medium header_row">Date</td>
	<td class="bgcolor_medium header_row">Program - Guest/Description</td>
	<td class="bgcolor_medium header_row">Store</td>
	<td class="bgcolor_medium header_row">Credit Amount</td>
	<td class="bgcolor_medium header_row">Other</td>
</tr>
<?php if (!empty($this->goneArray)) {
	foreach ($this->goneArray as $id => $row) { ?>
<tr>
	<td class="bgcolor_light"><?= ($row['is_expired'] ? 'Expired' : 'Used')?></td>
	<td class="bgcolor_light"><?= CTemplate::dateTimeFormat($row['timestamp_updated'], NORMAL, $this->store_id, CONCISE)?></td>
	<td class="bgcolor_light"><?= $row['user_description']?></td>
	<td class="bgcolor_light"><?= $row['store_name']?></td>
	<td class="bgcolor_light"><?= $row['amount']?></td>
	<td class="bgcolor_light">Date Added: <?= CTemplate::dateTimeFormat($row['timestamp_created'], NORMAL, $this->store_id, CONCISE)?></td>
</tr>
<?php }
 } else { ?>
<tr>
	<td class="bgcolor_light" colspan="6" style="text-align:center;"><i>There is no used or expired store credit for this customer</i></td>
</tr>
<?php } ?>
</table>
<?=$this->form['hidden_html'] ?>


<?php if ($this->canAddStoreCredit ) { ?>

<table width="100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2" style="text-align:center;">Add Store Credit for <b><?=$this->customer_first?> <?=$this->customer_last?></b></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;"><label id="newCreditAmount_lbl" for="newCreditAmount" message="Store Credit amount is required.">Enter Store Credit Amount:</label></td>
	<td class="bgcolor_light"><?=$this->form['newCreditAmount_html'] ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;"><label id="newCreditDesc_lbl" for="newCreditDesc"  message="Strore Credit description is required.">Describe Purpose of Credit:</label></td>
	<td class="bgcolor_light"><?=$this->form['newCreditDesc_html'] ?></td>
</tr>
<tr>
	<td class="bgcolor_light" colspan="2" style="text-align:center;"><?=$this->form['submit_credit_html'] ?></td>
</tr>
</table>

<?php  } else { ?>
<table width="100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2" style="text-align:center;">Add Store Credit for <b><?=$this->customer_first?> <?=$this->customer_last?></b></td>
</tr>
<tr>
	<td colspan="2"  class="bgcolor_light" style="text-align:center; padding: 20px;"><i>Only Store Owners, Store Managers or Session Leads can add store credit.</i></td>
</tr>
</table>

<?php } ?>
</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php');?>