<?php $this->assign('topnav', 'tools'); ?>
<?php $this->assign('page_title', 'Dev Utils'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<form  id="dev_form" name="dev_form" action="" method="post">
<?php echo $this->email_form['hidden_html']; ?>

<table style="width:100%;">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Functions</td>
</tr>

<tr>
	<td class="bgcolor_dark subheads" colspan="2">Get AGR for Order</td>
</tr>

<tr>
	<td> <?php echo $this->dev_form['order_id_html'] ?></td>
	<td> <?php echo $this->dev_form['submit_function_html'] ?></td>
</tr>

<tr>
	<td class="bgcolor_dark subheads" colspan="2">Get Balance Due for Order</td>
</tr>

<tr>
	<td> <?php echo $this->dev_form['bal_order_id_html'] ?></td>
	<td> <?php echo $this->dev_form['submit_bal_function_html'] ?></td>
</tr>

<?php if (isset($this->result)) { ?>
<tr>
	<td colspan="2"><?php echo $this->result ?></td>
</tr>


<?php } ?>

</table>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>