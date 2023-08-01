<?php
/*
 * Develop this template using no element IDs to prevent duplicates, this template is included twice in the same page
 *
 */
?>
<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_progress.tpl.php'); ?>

<div class="collapse shadow d-md-block p-0 menuCart">

	<div class="cart-list-div <?php echo (empty($this->box_instance['items'])) ? ' collapse' : ''; ?>">
		<div class="row p-0 bg-gray-light">
			<div class="meals-list session_menu-meals-list mobile col-12">
				<?php if (!empty($this->box_instance['items'])) { ?>
					<?php foreach ($this->box_instance['items'] AS $menu_item_id => $cart_info_item_info) { ?>
						<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_item.tpl.php'); ?>
					<?php } ?>
				<?php } ?>
			</div>
			<div class="col-12 bg-gray">
				<div class="row">
					<div class="col p-0">
						<span class="btn btn-primary btn-block btn-spinner box-add disabled">Add box to cart</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="cart-bottom-div">
		<div class="row p-0 bg-gray-light">
			<div class="msg_empty_cart mobile col-12 px-0<?php echo (!empty($this->box_instance['items'])) ? ' collapse' : ''; ?>">
				<div class="row m-0">
					<div class="col-12">
						<div class="row text-white text-center">
							<div class="bg-green col-12 py-2 p-0 text-uppercase">Select <?php echo $this->box_bundle_info->number_items_required; ?> dinners</div>
						</div>
					</div>
				</div>
				<div class="row m-0">
					<div class="col py-4">
						<div class="text-center mb-3"><img src="<?php echo IMAGES_PATH; ?>/main/add-meal.png" alt="Cart empty" /></div>
						<div class="text-uppercase font-weight-bold font-size-small text-center">The box is empty</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>