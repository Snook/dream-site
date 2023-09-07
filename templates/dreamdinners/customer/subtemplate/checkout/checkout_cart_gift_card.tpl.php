<div class="row bg-gray-light mb-4">
	<div class="col p-4">
		<div class="row mb-4">
			<div class="col-6">
				<h5 class="font-weight-bold text-uppercase">Cart</h5>
			</div>
			<div class="col-6 text-right align-text-top">
				<a href="/?page=gift_card_cart" class="btn btn-sm btn-secondary">View Cart</a>
			</div>
		</div>

		<?php
		if ($this->cart_info['cart_info_array']['has_gift_cards']) {
			foreach ($this->cart_info['gift_card_info'] as $id => $giftCard)
			{
				include $this->loadTemplate('customer/subtemplate/checkout/checkout_cart_gift_card_item_slim.tpl.php');
			}
		}
		?>
	</div>
</div>