<?php foreach ($this->itemArray as $id => $itemData) { ?>
	<?php if (!empty($this->DAO_bundle) && !$itemData->isInBundle($this->DAO_bundle)) { continue; } // menu item is not in starter pack, skip ?>
	<?php if ($itemData->isVisible()) { ?>
		<div class="col<?php echo ($itemData->isBundle()) ? '-12' : ''; ?> p-1">
			<button class="btn btn-<?php if (!empty($itemData->ltd_menu_item_supported)) { ?>orange<?php } else { ?>primary<?php } ?> btn-block btn-ripple py-3 <?php if ($itemData->isBundle()) { ?>configure-bundle<?php } else { ?>add-to-cart<?php } ?> <?php if ($itemData->this_type_out_of_stock || (($this->order_type != COrders::STANDARD && $this->order_type != COrders::MADE_FOR_YOU) && !empty($this->cart_info['entree_info'][$itemData->entree_id]))) echo ' disabled'; ?>" data-menu_item_id="<?php echo $itemData->id; ?>">
				<?php if (empty($itemData->is_chef_touched)) { ?>
					<span class="float-left"><?php echo $itemData->pricing_type_info['pricing_type_name_short']; ?></span>
				<?php } ?>
				<?php if ($this->order_type != COrders::INTRO) { ?>
					<?php if ($this->order_type == COrders::STANDARD || $this->order_type == COrders::MADE_FOR_YOU) { ?>
						<span class="price text-white"><?php if ($itemData->this_type_out_of_stock) { ?>Sold out</span><?php } else { ?>$<?php echo CTemplate::moneyFormat($itemData->store_price); ?><?php } ?></span>
					<?php } ?>
					<i class="fas fa-plus float-right pt-1 d-print-none<?php if (!empty($itemData->qty_in_cart) || $itemData->this_type_out_of_stock) { ?> collapse<?php } ?>"></i>
					<span class="cart-amount float-right d-print-none<?php if (empty($itemData->qty_in_cart)) { ?> collapse<?php } ?>">(<?php echo $itemData->qty_in_cart; ?>)</span>
				<?php } else { ?>
					<?php if (!empty($this->cart_info['item_info'][$itemData->id])) { ?>
						<i class="fas fa-check float-right text-dark pt-1 d-print-none"></i>
					<?php } else if (!empty($this->cart_info['entree_info'][$itemData->entree_id])) { ?>
						<i class="fas float-right text-white pt-1 d-print-none"></i>
					<?php } else { ?>
						<i class="fas fa-plus float-right text-white pt-1 d-print-none"></i>
					<?php } ?>
				<?php } ?>
			</button>
			<?php if (!$itemData->isMenuItem_SidesSweets()) { ?>
				<div class="row d-md-none d-print-none">
					<div class="col text-center font-size-small text-green-dark-extra mt-2">Serves <?php echo $itemData->pricing_type_info['pricing_type_serves_display']; ?></div>
				</div>
			<?php } ?>
		</div>

		<?php if ($itemData->isBundle()) { ?>
			<div class="col-12 py-2 collapse" data-master_item_id="<?php echo $itemData->id; ?>">

				<div class="row mb-2">
					<div class="col font-size-medium-small">
						You have selected <span class="bundle-subitem-total" data-menu_item_id="<?php echo $itemData->id; ?>">0</span> out of <?php echo $itemData->number_items_required; ?> items.
					</div>
				</div>

				<?php $groupTitle = ''; foreach ($itemData->sub_items as $subItemData) { ?>
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_bundle_sub_item.tpl.php'); ?>
				<?php } ?>

				<div class="row mt-2">
					<div class="col">
						<button class="btn btn-primary btn-block btn-ripple py-3 add-bundle-to-cart disabled" data-menu_item_id="<?php echo $itemData->id; ?>">
							Add bundle to cart
						</button>
					</div>
				</div>

			</div>
		<?php } ?>
	<?php } ?>
<?php } ?>