<?php $this->assign('page_title', 'Sides and Sweets Order Request Form'); ?>
<?php $this->assign('logo_only', $this->haveOrders); ?>
<?php $this->assign('page_description', 'Dream Dinners brings easy, prepped dinners to families in the communities we serve. Our delicious meals are prepared with quality ingredients in our local assembly kitchens.'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/sides_and_sweets_order_form.min.js'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Sides &amp; Sweets Request Form</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<div class="container">
			<form id="main_form" method="post" class="needs-validation" novalidate>

				<?php if(!$this->haveOrders) { ?>

					<div class="row mb-4">
						<div class="col">
							<p>Hello <?php echo $this->user->firstname ?>,</p>
							<p>This form is used to add items from our freezer to an existing order. You don't currently have an order to add items to. Please place an order online or contact the store if you have any questions.</p>
						</div>

						<div class="col-12 text-center">
							<h4>Contact the store</h4>
							<p>Phone: <?php echo $this->store_info[0]['telephone_day']; ?></p>
							<p>Email: <?php echo $this->store_info[0]['email_address']; ?></p>
						</div>
					</div>

				<?php } else { ?>

					<div class="row mb-4">
						<div class="col">
							<p>Hello <?php echo $this->user->firstname ?>,</p>
							<p>Ready to shop our virtual Sides &amp; Sweets freezer? Choose the items below you would like to add to your order. These items are limited in quantities, available on a first come, first serve basis and may not be here for long. Reserve yours today by filling out the request form below. We will have the available items ready with your order. These items are made ahead by our team and cannot be customized.</p>
						</div>
					</div>

					<div class="row">
						<div class="col">
							<div class="input-group mb-4">
								<div class="input-group-prepend col-12 p-0 col-md-auto">
									<div class="input-group-text w-100 justify-content-center">
										Select an order to add items to
									</div>
								</div>
								<?php echo $this->form['orders_html']; ?>
							</div>
						</div>
					</div>

					<?php if (!empty($this->menu_items)) { ?>
						<?php foreach ($this->menu_items as $id => $DAO_menu_item) { ?>
							<div class="row">
								<div class="col-4 col-md-3 col-lg-2">
									<img src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo $DAO_menu_item->menuItemImagePath(); ?>/<?php echo $DAO_menu_item->recipe_id; ?>.webp" alt="<?php echo $DAO_menu_item->menu_item_name; ?>" class="img-fluid pb-3">
								</div>
								<div class="col-8 col-md-9 col-lg-10">
									<div class="row">
										<div class="col-12 col-lg-8">
											<h5 class="font-weight-semi-bold"><?php echo $DAO_menu_item->menu_item_name; ?><?php echo (($DAO_menu_item->isMenuItem_EFL()) ? ' - ' . $DAO_menu_item->pricing_type_info['pricing_type_name'] : ''); ?></h5>
											<p class="font-size-small"><?php echo $DAO_menu_item->menu_item_description ?> </p>
										</div>
										<div class="col-12 col-md-6 col-lg-4">
											<div class="input-group mb-4">
												<div class="input-group-prepend">
													<div class="input-group-text">
														Qty
													</div>
												</div>
												<input class="form-control" type="number" id="menu_item[<?php echo $DAO_menu_item->id; ?>]" name="menu_item[<?php echo $DAO_menu_item->id; ?>]" data-menu_item_id="<?php echo $DAO_menu_item->id; ?>" data-price="<?php echo $DAO_menu_item->store_price; ?>" value="0" min="0" max="<?php echo $DAO_menu_item->remaining_servings?>" />
												<div class="input-group-append">
													<div class="input-group-text">
														<?php echo $DAO_menu_item->store_price; ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>

						<div class="row mb-4">
							<div class="col">
								<span> Item(s) </span><span id="totalItems">0</span> <span>/ Sub Total*: </span><span id="total">0.00</span>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<p class="font-weight-bold">Payment</p>
								<div class="custom-control custom-radio">
									<input class="custom-control-input" type="radio" id="card_on_file" name="payment" value="card_on_file" checked>
									<label class="custom-control-label" for="card_on_file">Use credit card on file</label>
								</div>
								<div class="custom-control custom-radio">
									<input class="custom-control-input" type="radio" id="pay_at_session" name="payment" value="pay_at_session">
									<label class="custom-control-label" for="pay_at_session">Pay at pick up</label>
								</div>
								<?php if (!empty($this->user->platePointsData['available_credit'])) { ?>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input" id="dinnerdollars" name="Use_Dinner_Dollars" value="1">
										<label class="custom-control-label" for="dinnerdollars">Apply Dinners Dollars towards payment ($<?php echo $this->user->platePointsData['available_credit']; ?> available)</label>
									</div>
								<?php } ?>
							</div>
						</div>

						<div class="row pt-3">
							<div class="col text-center">
								<button class="d-print-none btn btn-lg btn-primary" id="submit" name="submit" value="submit" disabled>Submit Request</button>
								<div class="mt-4 text-muted font-size-small font-italic">*Sub-total does not include any taxes, discounts or Dinner Dollars that effect the final price. Dinner Dollars applied will not exceed the amount charged for the items requested.</div>
							</div>
						</div>

					<?php } else { ?>

						<div class="row">
							<div class="col">
								<p class="bg-warning text-center p-3">Oh no, it looks like our Sides & Sweets freezer order form is sold out for now. Please contact your store directly to see what additional items may be available or shop the freezer in person when you visit. We look forward to seeing you soon.<?php echo $this->store_phone ?> <?php echo $this->store_email ?></p>
							</div>
							<div class="col-12 text-center">
								<h4>Contact the store</h4>
								<p>Phone: <?php echo $this->store_info[0]['telephone_day']; ?></p>
								<p>Email: <?php echo $this->store_info[0]['email_address']; ?></p>
							</div>
						</div>

					<?php } ?>

				<?php } ?>

			</form>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>