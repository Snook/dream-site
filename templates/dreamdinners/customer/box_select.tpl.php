<?php $this->setScript('foot', SCRIPT_PATH . '/customer/box.min.js'); ?>
<?php $this->assign('page_title', 'Select Box'); ?>
<?php $this->assign('page_description','At Dream Dinners you make homemade meals for your family in our store, then freeze, thaw and cook when you are ready at home. We are your dinnertime solution.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('customer/subtemplate/edit_order.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>What type of <span class="text-green font-weight-semi-bold">box</span> are you looking for?</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main class="container">

		<div class="row">

			<div class="col-12 col-lg-8 order-2 order-lg-1">

				<div class="row">

					<div class="card-deck w-100">

						<?php foreach ($this->boxArray AS $box) {?>

							<div class="col-12 p-0">
								<div class="card m-2">
									<div class="card-body text-center">
										<div class="row mt-2">
											<div class="col">
												<i class="dd-icon <?php echo $box->css_icon; ?> font-size-extra-extra-large text-green"></i>
											</div>
										</div>
										<h5 class="card-title font-size-medium">
											<?php echo $box->title; ?>
										</h5>
										<p class="card-text mb-3">
											<?php echo $box->description; ?>
										</p>
									</div>
									<div class="card-footer border-0">
										<div class="row">
											<?php foreach ($box->bundle AS $id => $bundle) { ?>
												<div class="col-lg-<?php echo ((count($box->bundle) > 1) ? '6' : '12'); ?>">
													<button class="btn btn-primary btn-block btn-spinner <?php if (!empty($bundle->info['out_of_stock'])) { ?>disabled<?php } ?>" data-view_box="<?php echo $box->id; ?>" data-view_bundle="<?php echo $bundle->id; ?>">
														<?php if (!empty($bundle->info['out_of_stock'])) { ?>
															Out of stock
														<?php } else { ?>
															$<?php echo $bundle->price; ?> <?php echo $bundle->bundle_name; ?>
														<?php } ?>
													</button>
													<div class="text-muted font-size-small text-center"><?php echo $bundle->menu_item_description; ?></div>
												</div>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>

						<?php } ?>

					</div>
				</div>

			</div>

			<div class="col-12 col-lg-4 order-1 order-lg-2 pt-lg-2 mb-4">

				<div class="row shadow">
					<div class="col">

						<?php if (empty($this->cart_info['cart_info_array']['has_food'])) { ?>

							<div class="row">
								<div class="col bg-green text-white text-center text-uppercase font-weight-bold p-2">
									Select a box to get started
								</div>
							</div>

							<div class="row">
								<div class="col py-4">
									<div class="text-center mb-3"><img src="<?php echo IMAGES_PATH; ?>/main/add-meal.png" alt="Cart empty" /></div>
									<div class="text-uppercase font-weight-bold font-size-small text-center">Your cart is empty</div>
								</div>
							</div>

						<?php } else { ?>

							<div class="row mb-4">
								<div class="col bg-green text-white text-center text-uppercase font-weight-bold p-2">
									Cart
								</div>
							</div>

							<?php foreach ($this->cart_info['item_info'] AS $bid => $box) { ?>
								<?php if (!empty($box['box_instance']->is_complete)) { ?>
									<div class="row mb-4" data-cart_box_id="<?php echo $bid; ?>">
										<div class="col-10">
											<div class="font-weight-bold font-size-medium-small text-uppercase mb-2"><i class="dd-icon <?php echo $box['box_instance']->css_icon; ?> text-green"></i> <?php echo $box['box_instance']->title; ?></div>
											<div class="font-weight-bold font-size-small text-uppercase"><?php echo $box['bundle']->bundle_name;?></div>
											<ul class="list-group list-group-flush font-size-small">
												<?php foreach ($box['items'] AS $item) { ?>
													<li class="list-group-item py-1"><?php echo $item['menu_item_name']; ?></li>
												<?php } ?>
											</ul>
										</div>
										<div class="col-2">
											<div class="row justify-content-end">
												<div class="col pr-2">
													<button class="btn btn-gray btn-block btn-ripple px-0 box-remove" data-box_instance_id="<?php echo $bid; ?>"><i class="fas fa-minus"></i></button>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>
							<?php } ?>

							<div class="row mb-4">
								<div class="col">
									<div class="input-group">
										<input type="text" class="form-control add-coupon-code" placeholder="Promo Code" value="<?php echo (!empty($this->cart_info['coupon'])) ? $this->cart_info['coupon']['coupon_code'] : ''; ?>" <?php echo (!empty($this->cart_info['coupon']) ? 'disabled="disabled"' : ''); ?> maxlength="36" />
										<div class="input-group-append">
											<div class="btn btn-primary add-coupon-add<?php echo (!empty($this->cart_info['coupon'])) ? ' disabled' : ''; ?>">
												Apply
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row mb-4 coupon-code-row <?php echo ((!empty($this->cart_info['coupon'])) ? '' : 'collapse') ?>">
								<div class="col-8 col-sm-7 col-lg-8 text-left">
									<i class="fas fa-minus-circle mr-2 add-coupon-remove"></i>Coupon (<span class="font-size-small coupon-code-title"><?php echo(!empty($this->cart_info['coupon']) ? $this->cart_info['coupon']['coupon_code_short_title'] : "") ?></span>)
									<div class="coupon-code-total-service-div collapse"><span class="font-size-small">*Discount applied at checkout</span></div>
								</div>
								<div class="col-4 col-sm-5 col-lg-4 pl-0 text-right">
									<div class="coupon-code-total-div">($<span class="coupon-code-total"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['coupon_code_discount_total']);?></span>)</div>
								</div>
							</div>

							<div class="row mb-4">
								<div class="col-6 text-uppercase">Cart subtotal</div>
								<div class="col-6 font-weight-bold text-right">$<span class="cart-total"><?php echo CTemplate::moneyFormat(((!empty($this->bundle_cost)) ? $this->bundle_cost : $this->cart_info['cart_info_array']['total_items_price']) - ((!empty($this->cart_info['order_info']['coupon_code_discount_total']) && empty($this->cart_info['coupon']['limit_to_mfy_fee']) && empty($this->cart_info['coupon']['limit_to_delivery_fee)'])) ? $this->cart_info['order_info']['coupon_code_discount_total'] : 0)); ?></span></div>
							</div>

							<div class="row">
								<div class="col px-0">
									<a href="/checkout" class="btn btn-primary btn-spinner btn-block">Checkout</a>
								</div>
							</div>

						<?php } ?>

					</div>
				</div>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>