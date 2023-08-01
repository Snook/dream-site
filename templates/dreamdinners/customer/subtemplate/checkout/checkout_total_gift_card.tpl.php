<div class="row bg-gray-light">
	<div class="col p-4">
		<h5 class="font-weight-bold text-uppercase">Total</h5>

		<?php if ($this->cart_info['cart_info_array']['has_gift_cards']) { $count = 0; ?>
			<?php foreach ($this->cart_info['gift_card_info'] as $gcoid => $cardInfo) { ?>
				<div class="row" id="giftcard_purchase-<?php echo $gcoid?>">
					<div class="col-7">
						<img id="remove-giftcard_purchase-<?php echo $gcoid?>" src="<?php echo IMAGES_PATH; ?>/icon/delete.png" alt="Remove" class="img_valign" data-tooltip="Remove Item" />
						<span id="checkout_title-giftcard_purchase-<?php echo $gcoid?>">Gift Card <?php echo ++$count;?> - <?php echo $cardInfo['media_type']; ?></span>
					</div>
					<div class="col-5 text-right">
						<span class="value"><span id="checkout_total-giftcard_purchase-<?php echo $gcoid?>"><?php echo CTemplate::moneyFormat($cardInfo['gc_amount']);?></span></span>
					</div>
				</div>
			<?php } } ?>

		<div class="row">
			<div class="col mt-2 text-right">
				<span class="pt-2 border-top border-green border-width-1-imp">Subtotal <span id="sum_checkout_total-subtotal" class="font-weight-bold"><?php echo CTemplate::moneyFormat($this->cart_info['order_info']['grand_total']);?></span></span>
			</div>
		</div>
	</div>
</div>

<div class="row bg-gray-light mb-4">
	<div class="col p-4">
		<h5 class="font-weight-bold text-uppercase">Payment</h5>
		<div class="row">
			<div class="col-8">
				<span>Balance to be charged to Credit Card</span>
			</div>
			<div class="col-4 text-right">
				<span class="value">(<span id="credit_card_amount">0.00</span>)</span>
			</div>
		</div>
	</div>
</div>
