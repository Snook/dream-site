<?php $this->setScript('foot', SCRIPT_PATH . '/customer/account.min.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/my_meals.min.js'); ?>
<?php $this->assign('page_title', 'My Reviews & Orders'); ?>
<?php $this->setOnload('restorePagingLocation("'.$this->user_id.'");'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>My reviews &amp; orders</h1>
				<p><i>Notes and comments added to meals may be made public or used in marketing materials.</i></p>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main>
		<div class="container-fluid">

			<div class="row">
				<div class="col-12 col-sm-8 col-md-6 mx-auto m2-3 text-center mb-3">
					<div class="row">
						<div class="input-group mx-auto">
							<?php if (!empty($this->food_search)) { ?>
								<div class="input-group-prepend">
									<a class="btn btn-primary" href="/?page=my_meals"><i class="fas fa-times"></i></a>
								</div>
							<?php } ?>
							<input type="text" class="form-control" id="my_meals_search" type="text" placeholder="Food Search: Chicken, Steak, Tortellini, ..." <?php echo (!empty($this->food_search)) ? 'value="' . $this->food_search . '"' : ''; ?>>
							<div class="input-group-append">
								<button id="my_meals_search_submit" type="button" class="btn btn-sm btn-primary">Search</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<nav class="row mb-4">
				<div class="col-12 col-sm-8 col-md-6 mx-auto nav nav-pills nav-fill pr-0" id="myMealsDetails" role="tablist">
					<a class="nav-item nav-link font-weight-bold text-uppercase active" id="nav-last_order-tab" data-urlpush="true" data-toggle="tab" data-target="#nav-last_order" href="/?page=my_meals&amp;tab=last_order" role="tab" aria-controls="nav-last_order" aria-selected="true">Rate items</a>
					<a class="nav-item nav-link font-weight-bold text-uppercase" id="nav-past_orders-tab" data-urlpush="true" data-toggle="tab" data-target="#nav-past_orders" href="/?page=my_meals&amp;tab=past_orders" role="tab" aria-controls="nav-past_orders" aria-selected="true">Past Orders</a>
				</div>
			</nav>

			<div class="tab-content" id="myMealsDetailsContent">
				<!-- last order -->
				<div class="tab-pane fade show active" id="nav-last_order" role="tabpanel" aria-labelledby="last_order-tab">

					<?php if ($this->no_rate_orders) { ?>

						<h4 class="text-center">You have not completed an order. Please come back to rate your meals after your next session.</h4>

					<?php } else if (empty($this->recipes) && !$this->no_past_orders) { ?>

						<h4 class="text-center">Eligible orders must be later than January 2012. Please come back to rate your meals after your next session.</h4>

					<?php } else if (!empty($this->food_search) && $this->no_search_results) { ?>

						<h4 class="text-center">No results for <?php echo $this->food_search; ?></h4>

					<?php } else { ?>

						<?php $active_menus = CMenu::getActiveMenuArray(); ?>

						<?php foreach ($this->recipes as $order_id => $order) { ?>

							<div class="row mb-4">
								<div class="col-md-8 mx-auto text-center">
									<p class="font-size-small"><span class="text-uppercase font-weight-bold">Items for</span> <?php echo CTemplate::dateTimeFormat($order['session_start'], VERBOSE_DATE) . " at " . CTemplate::dateTimeFormat($order['session_start'], SIMPLE_TIME); ?></p>
								</div>
							</div>

							<div class="row">
								<?php $counter = 0; foreach ($order['recipes'] as $recipe) { $counter++; ?>

									<?php include $this->loadTemplate('customer/subtemplate/my_meals/my_meals_item.tpl.php'); ?>

								<?php } ?>
							</div>

							<?php if (empty($this->food_search)) { ?>
								<div class="row mb-3">
									<div class="col-md-12 ">
										<div class="row">
											<div class="col-md-6 mx-auto text-center">
												<?php if ($order['session_start'] > '2014-11-01 00:00:00' && $order['status'] != CBooking::CANCELLED) { ?>
													<a href="/?page=print&amp;order=<?php echo $order['id']; ?>&amp;freezer=true" class="btn btn-primary" target="_blank">Freezer Sheet</a>
													<a href="/?page=print&amp;order=<?php echo $order['id']; ?>&amp;nutrition=true" class="btn btn-primary" target="_blank">Nutritionals</a>
													<?php if (array_key_exists($order['menu_id'] + 1, $active_menus)) { ?>
														<a href="/?page=print&amp;order=<?php echo $order['id']; ?>&amp;core=true" class="btn btn-primary" target="_blank">Next Month's Menu</a>
													<?php } ?>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							<?php } ?>

						<?php } ?>

					<?php } ?>

				</div>
				<!-- end last order -->

				<!-- orders -->
				<div class="tab-pane fade" id="nav-past_orders" role="tabpanel" aria-labelledby="past_orders-tab">
					<div class="col-lg-10 mx-auto">
						<?php if ($this->no_past_orders) { ?>

							<h4 class="text-center">No orders</h4>

						<?php } else { ?>
						<div id="order_history">
							<?php include $this->loadTemplate('customer/subtemplate/my_meals/my_meals_orders.tpl.php'); ?>
						</div>

						<?php } ?>
					</div>
				</div>
				<!-- end orders -->
			</div>

		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>