<div class="row pt-3 pb-2 mb-3 bg-gray-light d-none d-md-block">
	<div class="col-md-12">
		<div class="row">
			<div class="col-6">
				<h2 class="text-uppercase font-weight-semi-bold text-size-medium text-left">Cart</h2>
			</div>
			<?php if (empty($this->read_only_cart)) { ?>
			<div class="col-6">
				<p class="text-right">
					<button class="btn btn-primary btn-sm font-size-small clear-cart"><i class="fas fa-minus-circle mr-2"></i>Clear Cart</button>
				</p>
			</div>
			<?php } ?>
		</div>

		<?php if (!$this->isGiftCardOnlyOrder) { ?>
			<div class="row mb-4">
				<div class="col-xl-6">
					<?php if (!empty($this->read_only_cart)) {?>
						<div class="btn btn-green-dark btn-block disabled" style="opacity:.85;">
							Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?>
						</div>
					<?php }elseif (empty($this->cart_info['cart_info_array']['direct_invite'])) { ?>
					<a class="btn btn-primary btn-block" href="/locations">
						<i class="fas fa-edit float-left text-green-dark-extra pt-1"></i>
						Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?>
					</a>
					<?php } else { ?>
						<div class="font-weight-bold text-center p-2">
							Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?>
						</div>
					<?php } ?>
				</div>
				<div class="col-xl-6 mt-2 mt-xl-0">
					<?php if (!empty($this->read_only_cart)) {?>
						<div class="btn btn-green-dark btn-block disabled" style="opacity:.85;">
							<?php echo $this->customerActionString .CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA) . " at " . CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], SIMPLE_TIME); ?>
						</div>
					<?php }elseif (empty($this->cart_info['cart_info_array']['direct_invite'])) {?>
						<a class="btn btn-primary btn-block" href="/session">
							<i class="fas fa-edit float-left text-green-dark-extra pt-1"></i>
							<?php echo $this->customerActionString .CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA) . " at " . CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], SIMPLE_TIME); ?>
						</a>
					<?php } else { ?>
						<div class="font-weight-bold text-center p-2">
							<?php echo $this->customerActionString .CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA) . " at " . CTemplate::dateTimeFormat($this->cart_info['session_info']['session_start'], SIMPLE_TIME); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<div class="row">
			<div class="col">
				<?php if (!empty($this->read_only_cart)) {?>
					<a  class="btn btn-green-dark btn-block disabled" style="opacity:.85;">
				<?php }else{?>
					<a href="/session-menu" class="btn btn-primary btn-block">
						<i class="fas fa-edit float-left text-green-dark-extra pt-1"></i>
				<?php } ?>

					<?php if($this->cart_info['order_info']['servings_total_count'] > 0){?>
						You have <?php echo $this->cart_info['cart_info_array']['dinners_total_count']; ?> nights of dinners
					<?php }else{?>
						<?php if($this->cart_info['cart_info_array']['num_sides'] > 0){?>
							You have <?php echo $this->cart_info['cart_info_array']['num_sides']; ?> Sides and Sweets
						<?php }?>
					<?php }?>
				</a>
			</div>
		</div>

		<?php
		if (is_array($this->cart_info['item_info']))
		{
			$total_items = count($this->cart_info['item_info']);
			foreach ($this->cart_info['item_info'] as $id => $item)
			{
				$total_items--;
				$this->assignRef('curItem', $item);
				include $this->loadTemplate('customer/subtemplate/checkout/checkout_cart_menu_item.tpl.php');
			}
		}
		?>

	</div>
</div>