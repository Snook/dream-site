<div class="row">
	<div class="col-2">
		<img class="card-img-top" src="<?php echo $giftCard['image_path']?>" alt="Dream Dinners Gift Card">
	</div>
	<div class="col-8">
		<h5 class="card-title">Dream Dinners <?php echo $giftCard['media_type']; ?></h5>
		<?php if (!empty($giftCard['address'])) { ?>
			<p>Shipping to: <?php echo $giftCard['address']; ?></p>
		<?php } ?>
		<p>$<?php echo CTemplate::moneyFormat($giftCard['init_cost']); ?><?php echo ($giftCard['init_cost'] < $giftCard['gc_amount']) ? ' + $2 S&amp;H' : ''; ?></p>
	</div>
	<div class="col-2 text-right">
		<?php echo CTemplate::moneyFormat($giftCard['gc_amount']); ?>
	</div>
</div>