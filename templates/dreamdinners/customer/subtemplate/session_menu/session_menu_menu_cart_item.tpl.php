<?php if (isset($this->menu_item) && is_object($this->menu_item)) { // two sections below are the same style, only difference is the way the variables are defined. ?>
	<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $this->menu_item->id; ?>">
		<div class="col-8">
			<div class="font-weight-bold font-size-small text-uppercase"><?php echo $this->menu_item->menu_item_name; ?></div>
			<div class="font-size-small text-gray-dark"><?php echo (!$this->menu_item->isMenuItem_SidesSweets()) ? $this->menu_item->pricing_type_info['pricing_type_name'] . ' serves' : 'Serves' ; ?> <?php echo $this->menu_item->pricing_type_info['pricing_type_serves_display']; ?></div>
		</div>
		<div class="col-4">
			<div class="row justify-content-end">
				<div class="col-4 p-0">
					<?php if ($this->menu_item->isVisible()) { ?>
						<button class="btn btn-primary btn-block btn-ripple px-0 remove-from-cart" data-menu_item_id="<?php echo $this->menu_item->id; ?>"><i class="fas fa-minus"></i></button>
					<?php } ?>
				</div>
				<div class="col-4 p-0">
					<button class="btn btn-<?php if ($this->menu_item->isFreezer()) { ?>cyan-dark<?php } else { ?>white<?php } ?> btn-block <?php if ($this->menu_item->isVisible()) { ?>btn-ripple add-to-cart<?php } ?>" data-menu_item_id="<?php echo $this->menu_item->id; ?>"><?php echo $this->cart_info['item_info'][$this->menu_item->id]['qty']; ?></button>
				</div>
				<?php if ($this->cart_info['cart_info_array']['navigation_type'] != COrders::INTRO) { ?>
					<div class="col-4 p-0">
						<?php if ($this->menu_item->isVisible()) { ?>
							<button class="btn btn-primary btn-block btn-ripple px-0 add-to-cart" data-menu_item_id="<?php echo $this->menu_item->id; ?>"><i class="fas fa-plus"></i></button>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } else { ?>
	<?php if (!empty($this->processor_cart_info_item_info)) { $cart_info_item_info = $this->processor_cart_info_item_info; } ?>
	<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $cart_info_item_info['menu_item_id']; ?>">
		<div class="col-8">
			<div class="font-weight-bold font-size-small text-uppercase"><?php echo $cart_info_item_info['menu_item_name']; ?></div>
			<div class="font-size-small text-gray-dark"><?php echo ($cart_info_item_info['menu_item_category_id'] != 9) ? $cart_info_item_info['pricing_type_info']['pricing_type_name'] . ' serves' : 'Serves' ; ?> <?php echo $cart_info_item_info['pricing_type_info']['pricing_type_serves_display']; ?></div>
		</div>
		<div class="col-4">
			<div class="row justify-content-end">
				<div class="col-4 p-0">
					<?php if ($cart_info_item_info['is_visible']) { ?>
						<button class="btn btn-primary btn-block btn-ripple px-0 remove-from-cart" data-menu_item_id="<?php echo $cart_info_item_info['menu_item_id']; ?>"><i class="fas fa-minus"></i></button>
					<?php } ?>
				</div>
				<div class="col-4 p-0">
					<button class="btn btn-<?php if (!empty($cart_info_item_info['is_freezer_menu'])) { ?>cyan-dark<?php } else { ?>white<?php } ?> btn-block <?php if ($cart_info_item_info['is_visible']) { ?>btn-ripple add-to-cart<?php } ?>" data-menu_item_id="<?php echo $cart_info_item_info['menu_item_id']; ?>"><?php echo $cart_info_item_info['qty']; ?></button>
				</div>
				<?php if ($this->cart_info['cart_info_array']['navigation_type'] != COrders::INTRO) { ?>
					<div class="col-4 p-0">
						<?php if ($cart_info_item_info['is_visible']) { ?>
							<button class="btn btn-primary btn-block btn-ripple px-0 add-to-cart" data-menu_item_id="<?php echo $cart_info_item_info['menu_item_id']; ?>"><i class="fas fa-plus"></i></button>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>