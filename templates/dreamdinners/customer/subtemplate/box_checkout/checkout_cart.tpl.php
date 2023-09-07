<div class="row pt-3 pb-2 mb-3 bg-gray-light d-none d-md-block">
	<div class="col-md-12">
		<div class="row">
			<div class="col-6">
				<h2 class="text-uppercase font-weight-semi-bold text-size-medium text-left">Cart</h2>
			</div>
			<div class="col-6">
				<?php if ( !$this->isEditDeliveredOrder) { ?>
				<p class="text-right">
					<button class="btn btn-primary btn-sm font-size-small clear-cart"><i class="fas fa-minus-circle mr-2"></i>Clear Cart</button>
				</p>
				<?php } ?>
			</div>
		</div>

		<div class="row mb-4">
			<div class="col-xl-6">

				<?php if ($this->delta_boxes) { ?>
					<a href="/?page=box_select" class="btn btn-red btn-block">
						<i class="fas fa-edit float-left text-white pt-1"></i>
						You now have <?php echo $this->cart_info['cart_info_array']['dinners_total_count']; ?> nights of dinners
					</a>
				<?php } else {?>
					<a href="/?page=box_select" class="btn btn-primary btn-block">
						<i class="fas fa-edit float-left text-green-dark-extra pt-1"></i>
						You have <?php echo $this->cart_info['cart_info_array']['dinners_total_count']; ?> nights of dinners
					</a>
				<?php }?>
			</div>
			<div class="col-xl-6 mt-2 mt-xl-0">
				<?php if ($this->delta_session) { ?>
					<a class="btn btn-red btn-block" href="/?page=box_delivery_date">
						<i class="fas fa-edit float-left text-white pt-1"></i>
						Updated Delivered <?php echo CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA); ?>
					</a>
				<?php } else {?>
					<a class="btn btn-primary  btn-block" href="/?page=box_delivery_date">
						<i class="fas fa-edit float-left text-green-dark-extra pt-1"></i>
						Delivered <?php echo CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA); ?>
					</a>
				<?php }?>
			</div>
		</div>

		<div class="row mt-2 px-2">
			<div class="col">
				<?php foreach ($this->cart_info['item_info'] AS $bid => $box) { ?>
					<?php if (!empty($box['box_instance']->is_complete)) { ?>
						<?php include $this->loadTemplate('customer/subtemplate/box_checkout/checkout_cart_menu_item.tpl.php'); ?>
					<?php } ?>
				<?php } ?>
			</div>
		</div>

	</div>
</div>