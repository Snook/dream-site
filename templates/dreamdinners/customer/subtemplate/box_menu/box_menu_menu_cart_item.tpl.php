<?php if (!empty($this->processor_cart_info_item_info)) { $cart_info_item_info = $this->processor_cart_info_item_info; } ?>

<div class="row mr-0 py-4" data-cart_menu_item="<?php echo $cart_info_item_info[1]->id; ?>">
	<div class="col-8">
		<div class="font-weight-bold font-size-small text-uppercase"><?php echo $cart_info_item_info[1]->menu_item_name; ?></div>
	</div>
	<div class="col-4">
		<div class="row justify-content-end">
			<div class="col-4 p-0">
				<button class="btn btn-gray btn-block btn-ripple px-0 box-item-update" data-box_update_action="del" data-menu_item_id="<?php echo $cart_info_item_info[1]->id; ?>"><i class="fas fa-minus"></i></button>
			</div>
			<div class="col-4 p-0">
				<button class="btn btn-primary btn-block btn-ripple box-item-update" data-box_update_action="add" data-menu_item_id="<?php echo $cart_info_item_info[1]->id; ?>"><?php echo $cart_info_item_info[0]; ?></button>
			</div>
			<div class="col-4 p-0">
				<button class="btn btn-gray btn-block btn-ripple px-0 box-item-update" data-box_update_action="add" data-menu_item_id="<?php echo $cart_info_item_info[1]->id; ?>"><i class="fas fa-plus"></i></button>
			</div>
		</div>
	</div>
</div>
