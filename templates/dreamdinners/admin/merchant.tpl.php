<?php $this->assign('page_title','Merchant Account'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="alert alert-red-royal font-size-medium-small">DANGER! Changing the Username will immediately expire all saved credit cards for this store.</div>

	<center>
	<p> Merchant Information for: <b><?=$this->store['store_name']?></b>
	<form action="" method="post" class="needs-validation" novalidate>
		<input type="hidden" value="<?php echo $this->back;?>" name="back" />
		<table class="form_field_cell">
			<tr>
				<td>Partner ID</td>
				<td><?=$this->form_merchant['partner_id_html']?></td>
			</tr>
			<tr>
				<td>Username</td>
				<td><?=$this->form_merchant['ma_username_html']?></td>
			</tr>
			<tr>
				<td>Login Account</td>
				<td><?=$this->form_merchant['ma_login_account_html']?></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><?=$this->form_merchant['ma_password_html']?></td>
			</tr>
			<tr>
				<td></td>
				<td class="py-3"><input class="btn btn-danger w-100" type="submit" value="Save"></td>
			</tr>
			<tr>
				<td>last updated:</td><td><?=$this->timestamp_updated?></td>
			</tr>
			<tr>
				<td>updated by:</td><td><?=$this->updated_by?></td>
			</tr>
		</table>
	</form>
	<a href="<?php echo $this->back;?>" value="Back" class="button">Back</a>
	</p>
</center>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>