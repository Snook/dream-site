<?php
$this->assign('page_title','Order a New Gift Card');
$this->assign('topnav', 'giftcards');

if (isset($_REQUEST['print']) && $_REQUEST['print'] == "true")
{
	$this->assign('print_view', true);
}
else
{
	$this->assign('print_view', false);
}
?>

<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script type="text/javascript">
function openPrintWindow()
{
	var settings = 'height=500,width=900,status=no,toolbar=no,scrollbars=1,resizable=1';
	window.open('/backoffice/gc-only-order-thankyou?gcOrders=<?php echo $_REQUEST['gcOrders']?>&print=true','_print',settings);
}
</script>

<?php if (!$this->print_view) {?>
<table style="width: 100%;"><tr><td align="right"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" border="0">&nbsp;&nbsp;
<a href="javascript:openPrintWindow()">Print Receipt</a></td>
</tr></table>
<?php } ?>

<div id="printer">

 <p><span class="largepageheader">Dream Dinners Gift Card Purchase Receipt</span><br />
  <strong>This receipt is your &quot;Proof of Purchase&quot;</strong>. Please print this receipt for your records.</p>

<div style="border: thin solid darkgrey; padding:5px;">
<?php include $this->loadTemplate('admin/order_details_gift_card.tpl.php'); ?>
</div>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>