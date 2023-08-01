<?php $this->assign('page_title','Gift Card Order Details'); ?>
<?php $this->assign('print_view', true); ?>
<?php $this->assign('no_dd_print', true); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (isset($this->confirm) && $this->confirm ) { ?>

<center><h3><font color="red"><?php echo $this->successMsg;?></font></h3></center>

<?php } else if (isset($this->error) && $this->error ) { ?>

<center><h3><font color="red"><?php echo $this->errorMsg;?></font></h3></center>
<?php } ?>



<script type="text/javascript">

	function resend_receipt()
	{
		document.getElementById('action').value = "send_receipt";
		document.getElementById('actionForm').submit();
	}

	function resend_eGiftCard(id)
	{
		document.getElementById('action').value = "send_egift_card";
		document.getElementById('order_id').value = id;
		document.getElementById('actionForm').submit();

	}

	function modify_eGiftCard(id)
	{
		document.getElementById('action').value = "modify_email";
		document.getElementById('order_id').value = id;
		document.getElementById('actionForm').submit();
	}

</script>

<!--
<table width="100%"><tr><td align="right"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" border="0">&nbsp;&nbsp;
<a href="javascript:openPrintWindow()">Print Receipt</a></td>
</tr></table>
-->


 <p><span class="largepageheader">Dream Dinners Gift Card Purchase Receipt</span><br />
  <strong>This receipt is your &quot;Proof of Purchase&quot;</strong>. Please print this receipt for your records.</p>

<div style="border: thin solid darkgrey; padding:5px;">
<?php include $this->loadTemplate('admin/order_details_gift_card.tpl.php'); ?>
</div>

<form id="actionForm" name="actionForm" method="POST">
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="order_id" id="order_id" value="" />
</form>

<table align="right" cellspacing="15">
<tr>
<td>
<button onclick="javascript:resend_receipt();">Resend Full Purchase Receipt</button>
</td>
<td>
<button onClick="window.print()">Print this page</button>
</td>
<td>
<button onclick="javascript:window.close()">Close this window</button>
</td>
</tr>
<tr><td colspan="3" align="right"><strong>Resend eGift Card emails:</strong> Use the link(s) shown in the receipt above next to each eGift card.</td></tr>
</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
