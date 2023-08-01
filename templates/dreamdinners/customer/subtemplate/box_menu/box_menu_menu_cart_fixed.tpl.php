<?php
/*
 * Develop this template using no element IDs to prevent duplicates, this template is included twice in the same page
 *
 */
?>
<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_progress.tpl.php'); ?>

<div class="collapse shadow d-md-block p-0 menuCart">

	<div class="row p-0 bg-gray-light">
		<div class="meals-list session_menu-meals-list mobile col">
			<?php foreach ($this->box_bundle_info->menu_item['items'] as $item) { ?>
				<?php include $this->loadTemplate('customer/subtemplate/box_menu/box_menu_menu_cart_item_fixed.tpl.php'); ?>
			<?php } ?>
		</div>
	</div>

	<div class="cart-bottom-div">

		<div class="row p-0 bg-gray-light">
			<div class="cart-total-div col-12 bg-gray">

				<div class="row">
					<div class="col p-0">
						<span class="btn btn-primary btn-block btn-spinner box-add">Add box to cart</span>
					</div>
				</div>

			</div>

		</div>

	</div>

</div>
