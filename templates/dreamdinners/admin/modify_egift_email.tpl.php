<?php $this->assign('page_title','Gift Card Order Details'); ?>
<?php $this->assign('print_view', true); ?>
<?php $this->assign('no_dd_print', true); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (isset($this->confirm) && $this->confirm ) { ?>

<center><h3><font color="red"><?php echo $this->successMsg;?></font></h3></center>

<?php } else if (isset($this->error) && $this->error ) { ?>

<center><h3><font color="red"><?php echo $this->errorMsg;?></font></h3></center>
<?php } ?>

<form method="POST">

<center> <h3>Change eGift Card Recipient Email Address</h3></center>
<div>
<?=$this->form['recipient_email_address_html']?>
<?=$this->form['change_email_html']?>
</form>
</div>


<button onclick="javascript:window.location ='<?=$this->back?>';">Back</button>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
