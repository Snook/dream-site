<div class="card col-md-4 p-0 m-2">
	<img class="card-img-top" src="<?php echo $giftCard['image_path']?>" alt="Dream Dinners Gift Card">
	<div class="card-body">
		<h5 class="card-title">Dream Dinners <?php echo $giftCard['media_type']?></h5>
		<p>$<?php echo CTemplate::moneyFormat($giftCard['init_cost']); ?></p>
		<p class="card-text"><?php echo $giftCard['desc']; ?></p>
		<p class="gc-subtotal">
			Subtotal<?php echo ($giftCard['init_cost'] < $giftCard['gc_amount']) ? ' w/ S&H' : ''; ?>: <?php echo CTemplate::moneyFormat($giftCard['gc_amount']); ?>
		</p>

	</div>
	<div class="card-footer">
		<form action="/gift-card-order" method="post">
			<button class="btn btn-primary btn-sm" name="gc_edit_id" value="<?php echo $giftCard['order_id']?>">Edit</button>
            <button class="btn btn-primary btn-sm" name="gc_delete_id" value="<?php echo $giftCard['order_id']?>">Delete</button>
        </form>
	</div>
</div>