<?php $this->assign('page_title','Credit Cards'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script type="text/javascript">

 function removeCard(ref_id, cc_number)
 {
	 // show removal confirmation
	 dd_message({
		 title: 'Remove XXXXXXXXXXXX' + cc_number,
		 message: 'Are you sure you wish to remove the credit card number XXXXXXXXXXXX' + cc_number + '.',
		 confirm: function () {
			 $('#operation').val('remove');
			 $('#target_card_number').val(ref_id);
			 $('#card_form').submit();
		 }
	 });

 }

</script>



<table width="1200"><tr><td>
<?php if (isset($_REQUEST['back'])) { ?>
	<input type="button" value="Back" onClick="window.location = '<?= $_REQUEST['back']?>';">
<?php } else { ?>
	<input type="button" value="Back" onClick="window.location = '/backoffice/user_details?id=<?= $this->customer_id?>';">
<?php } ?>
</td><td width="95%" style="text-align:center;"><h3>Viewing Credit Cards for: <?=$this->customer_first?> <?=$this->customer_last?></h3>
</td></tr></table>

<form method="post" id="card_form">
<?php echo $this->form['hidden_html']?>
<?php if (isset($this->form['store_html'])) { ?>
	<strong>Store:</strong> <?php echo $this->form['store_html']; ?><br /><br />
<?php } ?>


<p>Note: It is important to understand that Dream Dinners does not actually store a credit card number. Nowhere in our databases could a hacker or other malicious user
discover the guest's credit card information. The actual card holder data is stored by PayPal and is not only very secure due to their expertise in managing financial data
but it is inherently secure in that the data could only be used for purchases from your store.  This is an important distinction that should make your guest feel more at ease
with this feature.</p>


</form>

<table id="" width="100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="8" style="text-align:center;">Credit Card Management</td>
</tr>
<tr >
	<td class="bgcolor_medium header_row">Card Number</td>
	<td class="bgcolor_medium header_row">Card Type</td>
	<td class="bgcolor_medium header_row">Date Last Used</td>
	<td class="bgcolor_medium header_row">Current Status</td>
	<td class="bgcolor_medium header_row">Operation</td>

</tr>

<?php
 $count = 0;
 if (!empty($this->eligibleArray)) {
	foreach ($this->eligibleArray as $number => $data) { $count++;?>
<tr>
	<td class="bgcolor_light" style="text-align:center;">XXXXXXXXXXXX<?= $data['cc_number']?></td>
	<td class="bgcolor_light" style="text-align:center;"><?= $data['card_type']?></td>
	<td class="bgcolor_light" style="text-align:center;"><?= CTemplate::dateTimeFormat($data['date'], NORMAL, $this->store_id, CONCISE);?></td>
	<?php if ($data['stale']) { ?>
		<td class="bgcolor_light" style="text-align:center;">Permission to use this card has expired. Please contact the guest to re-enter this card again.</td>
		<?php } else { ?>
		<td class="bgcolor_light" style="text-align:center;">Eligible for Reference</td>
		<?php } ?>

	<td class="bgcolor_light" style="text-align:center;"><button onclick="javascript:return removeCard('<?= $number?>','<?= $data['cc_number']?>');" class="btn btn-primary btn-sm">Remove</button></td>
</tr>
<?php } }?>

<?php if (!$count) { ?>
<tr>
	<td class="bgcolor_light" colspan="5" style="text-align:center;"><i>There are no credit cards found for this customer</i></td>
</tr>
<?php } ?>
</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php');?>