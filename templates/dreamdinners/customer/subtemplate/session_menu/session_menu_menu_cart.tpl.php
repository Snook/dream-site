<?php
/*
 * Develop this template using no element IDs to prevent duplicates, this template is included twice in the same page
 *
 */
?>
<?php if ($this->menu_view == 'session_menu') { ?>
	<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart_button.tpl.php'); ?>
<?php } ?>

<?php if ($this->menu_view == 'session_menu_freezer') { ?>
	<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart_freezer.tpl.php'); ?>
<?php } ?>

<div class="collapse shadow d-md-block p-0 menuCart">

	<div class="row p-0 bg-gray-light">
		<div class="meals-list session_menu-meals-list mobile col-12<?php echo (empty($this->cart_info['item_info'])) ? ' collapse' : ''; ?>">
			<?php if (!empty($this->cart_info['item_info'])) { ?>
				<?php foreach ($this->cart_info['item_info'] AS $menu_item_id => $cart_info_item_info) { ?>
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart_item.tpl.php'); ?>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="row mr-0 py-4 mobile clear-all-cart-items-div col-12<?php echo (empty($this->cart_info['item_info'])) ? ' collapse' : ''; ?>">
			<div class="col-12">
				<div class="row justify-content-end">
					<span class="btn btn-primary btn-sm clear-all-cart-items"><i class="fas fa-trash-alt"></i> Clear Items</span>
				</div>
			</div>
		</div>
	</div>

	<div class="cart-bottom-div">

		<div class="row">
			<div class="col-12">
				<div class="row text-white text-center">
					<div class="meal-nights bg-cyan-dark font-size-small col-12 py-2 p-0<?php echo (empty($this->cart_info['item_info'])) ? ' collapse' : ''; ?>">You have dinners for <span class="total-meal-nights font-weight-bold"><?php echo $this->cart_info['cart_info_array']['dinners_total_count']; ?></span> night<span class="total-meal-nights-plural">s</span></div>
					<?php if(!empty($this->additional_ordering_message)) { ?>
						<div class="meal-select bg-cyan-dark col-12 py-2 p-0 text-uppercase<?php echo (!empty($this->cart_info['item_info'])) ? ' collapse' : ''; ?>"><?php echo $this->additional_ordering_message; ?></div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="row p-0 bg-gray-light">
			<div class="cart-total-div col-12 bg-gray">
				<div class="row mt-2">
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
				<div class="row mt-2 coupon-code-row <?php echo ((!empty($this->cart_info['coupon'])) ? '' : 'collapse') ?>">
					<div class="col-8 col-sm-7 col-lg-8 text-left">
						<i class="fas fa-minus-circle mr-2 add-coupon-remove"></i>Coupon (<span class="font-size-small coupon-code-title"><?php echo(!empty($this->cart_info['coupon']) ? $this->cart_info['coupon']['coupon_code_short_title'] : "") ?></span>)
						<div class="coupon-code-total-service-div collapse"><span class="font-size-small">*Discount applied at checkout</span></div>
					</div>
					<div class="col-4 col-sm-5 col-lg-4 pl-0 text-right">
						<div class="coupon-code-total-div">($<span class="coupon-code-total"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['coupon_code_discount_total']);?></span>)</div>
					</div>
				</div>
				<div class="row py-2">
					<div class="col-6 text-uppercase">Subtotal</div>
					<div class="col-6 font-weight-bold text-right">$<span class="cart-total"><?php echo $this->initialCartSubtotal; ?></span></div>
					<div class="col-12 text-muted font-size-small font-italic">*May include discounts, taxes and fees.</div>
				</div>

				<div class="row">
					<div class="col p-0">
						<?php if ($this->menu_view == 'session_menu') { ?>
							<a href="/?page=session_menu&amp;view=freezer" class="btn btn-primary btn-block btn-spinner disabled continue-btn">Continue</a>
						<?php } else { ?>
							<a href="/?page=checkout" class="btn btn-primary btn-block btn-spinner disabled continue-btn">Continue</a>
						<?php } ?>
					</div>
				</div>

				<div class="row px-2 py-2 shadow">
					<div class="col text-uppercase text-center font-size-small">
						<div>Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></div>
						<div>Phone <?php echo $this->cart_info['store_info']['telephone_day']; ?></div>
					</div>
				</div>
			</div>

			<div class="msg_empty_cart mobile col-12 px-0<?php echo (!empty($this->cart_info['item_info'])) ? ' collapse' : ''; ?>">
				<div class="row m-0">
					<div class="col py-4">
						<div class="text-center mb-3"></div>
						<div class="text-uppercase font-weight-bold font-size-small text-center">Your cart is empty</div>
					</div>
				</div>
				<!--div class="row pb-3">
					<div class="col text-uppercase text-center font-size-small">
						<div>Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></div>
						<div>Phone <?php echo $this->cart_info['store_info']['telephone_day']; ?></div>
					</div>
				</div-->
			</div>
		</div>

	</div>

</div>