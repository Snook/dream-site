<?php $this->setScript('foot', SCRIPT_PATH . '/admin/manage_coupon_codes.min.js'); ?>
<?php $this->assign('page_title','Manage Coupon Codes'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-lg-6 text-center mb-3 order-lg-2">
				<h1><a href="main.php?page=admin_manage_coupon_codes">Manage Coupons</a></h1>
			</div>
			<div class="col-8 col-lg-3 order-lg-1">
				<div class="input-group mb-2">
					<input class="form-control dd-strip-tags" type="text" id="edit_coupon_code" name="edit_coupon_code" pattern="^([a-zA-Z0-9\$]){3,36}$" placeholder="Coupon Code">
					<div class="input-group-append">
						<button class="btn btn-primary btn-sm coupon-search"><i class="fas fa-search font-size-medium-small"></i></button>
					</div>
				</div>
			</div>
			<div class="col-4 col-lg-3 text-center text-lg-right order-lg-3">
				<a href="main.php?page=admin_manage_coupon_codes&amp;create=true" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i> Create New</a>
			</div>
		</div>

		<?php if (!empty($this->editCoupon) || !empty($this->createCoupon)) { ?>

			<?php if ($this->couponOrders['hasOrders']) { ?>
				<div class="alert alert-warning" role="alert">
					<span class="font-weight-bold"><?php echo $this->couponOrders['count']; ?></span> <?php echo (($this->couponOrders['count'] != 1) ? 'orders have' : 'order has'); ?> been placed using this coupon. Some functionality has been limited in order to support those orders.
				</div>
			<?php } ?>

			<?php include $this->loadTemplate('admin/subtemplate/manage_coupon_codes/manage_coupon_codes_edit_coupon.tpl.php'); ?>

		<?php } else { ?>

			<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist">
					<button class="nav-link active" id="nav-coupons-current-tab" data-toggle="tab" data-target="#nav-coupons-current" type="button" role="tab" aria-controls="nav-coupons-current" aria-selected="true">Current Coupons</button>
					<button class="nav-link" id="nav-coupons-program-tab" data-toggle="tab" data-target="#nav-coupons-program" type="button" role="tab" aria-controls="nav-coupons-program" aria-selected="false">Coupon Programs</button>
					<button class="nav-link" id="nav-coupons-expired-tab" data-toggle="tab" data-target="#nav-coupons-expired" type="button" role="tab" aria-controls="nav-coupons-expired" aria-selected="false">Expired Coupons</button>
				</div>
			</nav>
			<div class="tab-content bg-white" id="nav-tabContent">
				<div class="tab-pane fade show active" id="nav-coupons-current" role="tabpanel" aria-labelledby="nav-coupons-current-tab">
					<table class="table table-striped ddtemp-table-border-collapse">
						<thead>
						<tr>
							<th>Code</th>
							<th>Short Title</th>
							<th>Value</th>
							<th>Expires</th>
							<th>Edit</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->couponArray['current'] AS $id => $coupon) { ?>
							<tr data-coupon_row="<?php echo $coupon->id; ?>">
								<td><?php echo $coupon->coupon_code; ?></td>
								<td><?php echo $coupon->coupon_code_short_title; ?></td>
								<td style="text-align: right;"><?php echo ($coupon->discount_method == 'FLAT') ? '$' : ''; ?><?php echo ($coupon->discount_method == 'FREE_MEAL') ? 'Meal' : $coupon->discount_var; ?><?php echo ($coupon->discount_method == 'PERCENT') ? '%' : ''; ?></td>
								<td><?php echo CTemplate::dateTimeFormat($coupon->valid_timespan_end); ?></td>
								<td><a href="main.php?page=admin_manage_coupon_codes&amp;edit=<?php echo $coupon->id; ?>" class="btn btn-primary">edit</a></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<div class="tab-pane fade" id="nav-coupons-program" role="tabpanel" aria-labelledby="nav-coupons-program-tab">
					<table class="table table-striped ddtemp-table-border-collapse">
						<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Description</th>
							<th>Owner</th>
							<th>Other Owner</th>
							<th>Comments</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->programArray AS $id => $program) { ?>
							<tr>
								<td><?php echo $program->id; ?></td>
								<td><?php echo $program->program_name; ?></td>
								<td><?php echo $program->program_description; ?></td>
								<td><?php echo $program->program_owner; ?></td>
								<td><?php echo $program->other_program_owner; ?></td>
								<td><?php echo $program->comments; ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<div class="tab-pane fade" id="nav-coupons-expired" role="tabpanel" aria-labelledby="nav-coupons-expired-tab">
					<table class="table table-striped ddtemp-table-border-collapse">
						<thead>
						<tr>
							<th>Code</th>
							<th>Short Title</th>
							<th>Value</th>
							<th>Expires</th>
							<th>Edit</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->couponArray['expired'] AS $id => $coupon) { ?>
							<tr>
								<td><?php echo $coupon->coupon_code; ?></td>
								<td><?php echo $coupon->coupon_code_short_title; ?></td>
								<td><?php echo ($coupon->discount_method == 'FLAT') ? '$' : ''; ?><?php echo ($coupon->discount_method == 'FREE_MEAL') ? 'Meal' : $coupon->discount_var; ?><?php echo ($coupon->discount_method == 'PERCENT') ? '%' : ''; ?></td>
								<td><?php echo CTemplate::dateTimeFormat($coupon->valid_timespan_end); ?></td>
								<td><a href="main.php?page=admin_manage_coupon_codes&amp;edit=<?php echo $coupon->id; ?>" class="btn btn-primary">edit</a></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>

		<?php } ?>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>