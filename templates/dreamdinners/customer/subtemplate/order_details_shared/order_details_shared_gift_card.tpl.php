<div class="row">
	<div class="col-md-12 col-12 p-md-0 table-responsive">
		<table class="table">
			<thead>
			<tr class="bg-green-dark text-uppercase text-white text-nowrap">
				<th scope="col" colspan="2">Card Type</th>
				<th scope="col">Recipient</th>
				<th scope="col">Total</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($this->gift_card_purchase_array as $giftcard) { ?>
				<tr>
					<td class="w-25"><img src="<?php echo IMAGES_PATH; ?>/gift_cards/<?php echo $giftcard['image_name'];?>" alt="<?php echo $giftcard['image_name'];?>" class="img-fluid"></td>
					<td class="text-center"><?php echo $giftcard['gc_media_type']; ?><br /><?php echo $giftcard['desc']?></td>
					<td class="text-center"><?php echo $giftcard['to_name']; ?></td>
					<td class="text-right">$<?php echo $this->moneyFormat($giftcard['gc_amount']); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<div class="row">
	<div class="col-12 text-center">
		<h2>Payment details</h2>
	</div>
</div>
<div class="row">
	<div class="col-12 col-md-6 mt-4">
		Card Type: <?php echo $this->gcPaymentCardType; ?><br />
		Last 4 digits: <?php echo substr($this->gcPaymentCardNumber, strlen($this->gcPaymentCardNumber)-4, strlen($this->gcPaymentCardNumber)); ?><br />
		Amount: $<?php echo $this->gift_card_total; ?><br />
		Payment Date: <?php echo CTemplate::dateTimeFormat($this->gcPaymentDate); ?>
	</div>
	<div class="col-12 col-md-6 mt-4">
		Billing Name: <?php echo $this->billing_name; ?><br />
		<?php if (!empty($this->billing_address)) { ?>Billing Address: <?php echo $this->billing_address; ?>, <?php echo $this->billing_zip; ?> <br /><?php } ?>
		Billing Email: <?php echo $this->billing_email; ?>
	</div>
	<div class="row mt-4">
		<div class="col-12 p-4">
			<p><span class="font-weight-bold">Shipping Details</span>: Allow 2-6 business days for shipped card, emailed eGift Cards are sent instantly.</p>
			<p><span class="font-weight-bold">Notes:</span> Each Gift Card purchased appears as a separate charge on your credit card. Please print this page for your records.</p>
		</div>
	</div>
</div>
