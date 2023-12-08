<?php
$REPORTGIF = null;
$PAGETITLE = false;
$HIDDENPAGENAME = "admin_reports_preferred_users";
$SHOWSINGLEDATE = false;
$SHOWRANGEDATE = false;
$SHOWMONTH = false;
$SHOWYEAR = false;
?>
<?php $this->assign('page_title','Preferred Users Report'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1><a href="/backoffice/reports-preferred-users">Preferred Users Report</a></h1>
			</div>
		</div>

		<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
		<br />

		<?php if (isset($this->rows) && count($this->rows) > 0) { ?>
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
					<th>User Type</th>
					<?php if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_STAFF || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER ) { ?>
						<th>All Stores</th>
					<?php } ?>
					<th>Pref Type</th>
					<th>Pref Value</th>
					<th>Start Date</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->rows as $entity) { ?>
					<tr>
						<td><a href="/backoffice/preferred?id=<?php echo $entity['id']; ?>"><?php echo $entity['id']; ?></a></td>
						<td>
							<div class="mb-2"><?php echo $entity['firstname']; ?> <?php echo $entity['lastname']; ?></div>
							<div><a href="/backoffice/email?id=<?php echo $entity['id']; ?>"><?php echo $entity['primary_email']; ?></a></div>
						</td>
						<td><?php echo CUser::userTypeText($entity['user_type']); ?></td>
						<?php if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_STAFF || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER ) { ?>
							<td><?php echo $entity['all_stores']; ?></td>
						<?php } ?>
						<td><?php echo $entity['preferred_type']; ?></td>
						<td><?php echo $entity['preferred_value']; ?></td>
						<td><?php echo $entity['user_preferred_start']; ?></td>
					</tr >
				<?php } ?>
				</tbody>
			</table>

		<?php } else { ?>
			No preferred users could be found for your store.
		<?php } ?>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>