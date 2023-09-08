<?php
$REPORTGIF = null;
$PAGETITLE = false;
$HIDDENPAGENAME = "admin_reports_store_credit";
$SHOWSINGLEDATE = false;
$SHOWRANGEDATE = false;
$SHOWMONTH = false;
$SHOWYEAR = false;
?>
<?php $this->assign('page_title','Credit Report'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center ">
				<h1><a href="?page=admin_reports_store_credit">Credit Report</a></h1>
			</div>
		</div>

		<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>

		<br/>

		<?php if (isset($this->rows) && count($this->rows) > 0)  { ?>

			<div class="row">
				<div class="col text-right">
					<?php include $this->loadTemplate('admin/export.tpl.php'); ?>
				</div>
			</div>

			<table class="table table-striped table-bordered table-hover ddtemp-table-border-collapse bg-white">
				<thead>
				<tr>
					<th>User Id</th>
					<th>Customer</th>
					<th>Credit Type</th>
					<th>Amount</th>
					<th>Will Expire</th>
					<th>Description</th>
					<th>Referred Guest</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->rows as $user_element) { ?>
					<?php foreach ($user_element as $entity) { ?>
						<tr>
							<td <?php if (count($user_element) > 1) { ?>class="font-weight-bold"<?php } ?>><a href="?page=admin_user_details&id=<?php echo $entity['user_id']; ?>"><?php echo $entity['user_id']; ?></a></td>
							<td>
								<div class="mb-2"><?php echo $entity['firstname']; ?> <?php echo $entity['lastname']; ?></div>
								<div><a href="?page=admin_email&id=<?php echo $entity['user_id']; ?>"><?php echo $entity['primary_email']; ?></a></div>
								<div><?php echo $entity['telephone']; ?></div>
							</td>
							<td><?php echo $entity['credit_type']; ?></td>
							<td><?php echo $entity['amount']; ?></td>
							<td><?php echo $entity['expiration_date']; ?></td>
							<td><?php echo $entity['description']; ?></td>
							<td><a href="?page=admin_user_details&id=<?php echo $entity['referred_guest_id']; ?>&back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $entity['referred_guest']; ?></a></td>
						</tr>
					<?php } ?>
				<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			No outstanding store credit could be found for this store.
		<?php } ?>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>