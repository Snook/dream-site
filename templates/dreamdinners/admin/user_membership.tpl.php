<?php $this->setScriptVar('order_conversion_results = ' . (!empty($this->order_conversion_results) ?  json_encode($this->order_conversion_results) : "null") . ';'); ?>
<?php $this->setScriptVar('error_occurred = ' . (!empty($this->error_occurred) ? "true" : "false") . ';'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/admin/user_membership.min.js'); ?>

<?php if (!empty($this->error_occurred))
{
	$this->setOnload('init_with_error();');
} ?>

<?php $this->assign('page_title', 'Dream Dinners Meal Prep+'); ?>
<?php $this->assign('topnav', 'guests'); ?>
<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Dream Dinners Meal Prep+</h1>
				<h2><a href="/backoffice/user_details?id=<?php echo $this->user->id; ?>"><?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></a></h2>
			</div>
		</div>

		<?php if ( $this->user->platePointsData['status'] == 'active' ) { ?>

			<div class="row">
				<div class="col text-center">
					<p>Guest is ineligible to purchase a Meal Prep+ Membership. Please put their <a class="text-decoration-underline" href="/backoffice/user_plate_points?id=<?php echo $this->user->id; ?>">PLATEPOINTS enrollment on hold</a> first.</p>
				</div>
			</div>

		<?php } else if ( $this->user->isUserPreferred()) { ?>

			<div class="row">
				<div class="col text-center">
					<p>Guest is ineligible to purchase a Meal Prep+ Membership. Please remove their <a class="text-decoration-underline" href="/backoffice/preferred?id=<?php echo $this->user->id; ?>">Preferred Status</a> first.</p>
				</div>
			</div>

		<?php } else { ?>

			<?php if (!empty($this->user->membershipData['enrollment_eligible']) && $this->supports_new_memberships) { ?>
				<div class="row mb-3">
					<div class="col-6">
						<?php include $this->loadTemplate('admin/subtemplate/user_membership/user_membership_program_notes.tpl.php'); ?>
					</div>
					<div class="col-6">

						<form id="membership_enroll" name="membership_enroll" autocomplete="off" action="" method="post" class="needs-validation" novalidate>
							<?php echo $this->form_membership['hidden_html']; ?>

							<div class="form-row">
								<div class="form-group col-md-6">
									<?php echo $this->form_membership['product_html']; ?>
								</div>
								<div class="form-group col-md-6">
									<?php echo $this->form_membership['initial_menu_html']; ?>
								</div>
								<div class="form-group col-md-12">
									<?php echo $this->form_membership['payment_type_html']; ?>
								</div>
							</div>

							<div class="row form-payment form-new-credit-card collapse">
								<div class="col">
									<?php include $this->loadTemplate('admin/subtemplate/form/form_payment_credit_card.tpl.php'); ?>
								</div>
							</div>

							<div class="row form-payment form-new-credit-card collapse">
								<div class="col">
									<?php include $this->loadTemplate('admin/subtemplate/form/form_billing_address.tpl.php'); ?>
								</div>
							</div>

							<div class="row form-payment form-payment-cash collapse">
								<div class="col">
									<?php include $this->loadTemplate('admin/subtemplate/form/form_payment_cash.tpl.php'); ?>
								</div>
							</div>

							<div class="row form-payment form-payment-check collapse">
								<div class="col">
									<?php include $this->loadTemplate('admin/subtemplate/form/form_payment_check.tpl.php'); ?>
								</div>
							</div>

							<div class="row form-payment form-discount-coupon collapse">
								<div class="col">
									<?php include $this->loadTemplate('admin/subtemplate/form/form_discount_coupon.tpl.php'); ?>
								</div>
							</div>

							<div class="row checkout-summary collapse">
								<div class="col">
									<div class="row mb-3">
										<div class="col text-right">
											<div class="mb-1">Subtotal <span class="checkout-subtotal"></span></div>
											<div class="mb-1 collapse checkout-line-discount">Coupon (<span class="checkout-discount"></span>)</div>
											<div class="mb-1">Tax <span class="checkout-tax"></span></div>
											<hr class="my-1" />
											<div class="mb-1 font-weight-bold">Total $<span class="checkout-total"></span></div>
										</div>
									</div>
									<div class="row">
										<div class="col">
											<?php echo $this->form_membership['enroll_submit_html']; ?>
										</div>
									</div>
								</div>
							</div>

						</form>
					</div>

				</div>
			<?php } ?>

			<?php if (!empty($this->order_conversion_results)) { ?>
				<div class="row mb-3">
					<div class="col">
						<p>This membership enrollment has been applied to the following order(s):</p>
						<ul>
							<?php foreach ($this->order_conversion_results AS $order_id => $result) { ?>
								<div><a href="/backoffice/order-mgr?order=<?php echo $order_id; ?>"><?php echo $order_id; ?> - <?php echo $result; ?></a></div>
							<?php } ?>
						</ul>
					</div>
				</div>
			<?php } ?>

			<?php if (!empty($this->user->membershipsArray)) { ?>
				<div class="row mb-3">
					<div class="col">
						<table class="table table-striped">
							<thead>
							<tr>
								<th>Enrollment date</th>
								<th>End date</th>
								<th>Membership</th>
								<th>Discount</th>
								<th>Total saved</th>
								<?php if (count($this->user->membershipsArray) > 1) { ?>
									<th>Manage</th>
								<?php } ?>
							</tr>
							</thead>
							<?php foreach ($this->user->membershipsArray AS $membership) { ?>
								<tbody>
								<tr>
									<td><?php echo CTemplate::dateTimeFormat($membership['enrollment_date'], FULL_MONTH_DAY_YEAR); ?></td>
									<td><?php echo $membership['completion_month']; ?></td>
									<td><?php echo $membership['display_strings']['status_abbr']; ?></td>
									<td><?php echo $membership['discount_var']; ?>% per order</td>
									<td>$<?php echo CTemplate::moneyFormat($membership['total_savings']); ?></td>
									<?php if (count($this->user->membershipsArray) > 1) { ?>
										<td>
											<button type="button" class="btn btn-primary btn-block dropdown-toggle membership-manage <?php if ($membership['membership_id'] == $this->user->membershipData['membership_id']) { ?>disabled<?php } ?>" data-membership_id="<?php echo $membership['membership_id']; ?>">Manage Membership</button>
										</td>
									<?php } ?>
								</tr>
								</tbody>
								<tbody data-membership_id_manage="<?php echo $membership['membership_id']; ?>" <?php if ($membership['membership_id'] != $this->user->membershipData['membership_id']) { ?>class="collapse"<?php } ?>>
								<tr>
									<td colspan="<?php echo (count($this->user->membershipsArray) > 1) ? '6' : '5'; ?>">
										<div class="row">
											<div class="col-4">
												<ul>
													<li>Status: <?php echo $membership['display_strings']['status']; ?></li>
													<li>Term: <?php echo $membership['term_months']; ?> months</li>
													<li>Progress: <?php echo $membership['display_strings']['progress']; ?></li>
													<li>Skips allowed: <?php echo $membership['number_skips_allowed']; ?></li>
													<li>Skips available: <?php echo $membership['remaining_skips_available']; ?></li>
													<li>Total orders: <?php echo $membership['total_orders']; ?></li>
												</ul>
											</div>
											<div class="col-4">
												<?php if (!empty($membership['paymentData'])) { ?>
													<?php foreach ($membership['paymentData'] AS $payment) { ?>
														<ul>
															<li>Payment method: <?php echo $payment['display_strings']['payment_method']; ?></li>
															<li>Payment total: <?php echo $payment['total_amount']; ?></li>
														</ul>
													<?php } ?>
												<?php } ?>
											</div>
											<div class="col-4 text-right">
												<?php if (!empty($membership['enrollment_cancel_eligible'])) { ?>
													<button class="btn btn-danger btn-block membership-cancel" data-user_id="<?php echo $this->user->id; ?>" data-membership_id="<?php echo $membership['membership_id']; ?>">Cancel & Refund Meal Prep+</button>
												<?php } else if (!empty($membership['enrollment_termination_eligible'])) { ?>
													<button class="btn btn-danger btn-block membership-terminate" data-user_id="<?php echo $this->user->id; ?>" data-membership_id="<?php echo $membership['membership_id']; ?>">Cancel Meal Prep+ (No Refund)</button>
												<?php } else if (!empty($membership['can_be_reinstated'])) { ?>
													<button class="btn btn-danger btn-block membership-reinstate" data-user_id="<?php echo $this->user->id; ?>" data-membership_id="<?php echo $membership['membership_id']; ?>">Reinstate Meal Prep+</button>
												<?php } ?>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="6">
										<div class="row">
											<div class="col">
												<table class="table table-striped">
													<thead>
													<tr>
														<th>Menu</th>
														<th>Orders</th>
														<th class="w-25">Skip</th>
													</tr>
													</thead>
													<tbody>
													<?php foreach ($membership['eligible_menus'] AS $menu_id => $membershipInfo) { ?>
														<tr>
															<td><?php echo $membershipInfo['menu_info']->menu_name; ?></td>
															<td>
																<?php if (!empty($membershipInfo['orders'])) { ?>
																	<?php foreach ($membershipInfo['orders'] AS $order_id => $order) { ?>
																		<div><a href="/backoffice/order-mgr?order=<?php echo $order_id; ?>"><?php echo CTemplate::dateTimeFormat($order['session_start'], VERBOSE); ?></a></div>
																	<?php } ?>
																<?php } else { ?>
																	None
																<?php } ?>
															</td>
															<td class="text-center" data-skip_menu_id="<?php echo $menu_id; ?>">
																<?php if (!empty($membershipInfo['skippable']) && (empty($membershipInfo['is_hard_skip']))) { ?>
																	<button data-user_id="<?php echo $this->user->id; ?>" data-menu_id="<?php echo $menu_id; ?>" class="btn btn-primary membership-skip-month">Skip Month</button>
																<?php } else if (!empty($membershipInfo['skippable']) && !empty($membershipInfo['skipped']) && !empty($membershipInfo['is_hard_skip']) ) { ?>
																	<button data-user_id="<?php echo $this->user->id; ?>" data-menu_id="<?php echo $menu_id; ?>" class="btn btn-primary membership-unskip-month">Unskip Month</button>
																<?php } ?>
															</td>
														</tr>
													<?php } ?>
													</tbody>
												</table>
											</div>
										</div>
									</td>
								</tr>
								</tbody>
							<?php } ?>
						</table>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col">
						<?php include $this->loadTemplate('admin/subtemplate/user_membership/user_membership_program_notes.tpl.php'); ?>
					</div>
				</div>
			<?php } ?>

		<?php } ?>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>