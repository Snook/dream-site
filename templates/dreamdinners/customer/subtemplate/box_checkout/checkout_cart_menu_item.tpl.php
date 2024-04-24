<div class="row mb-4" xmlns="http://www.w3.org/1999/html">
	<div class="col-8">
		<div class="font-weight-bold font-size-medium-small text-uppercase mb-2"><i class="dd-icon <?php echo $box['box_instance']->css_icon; ?> text-green"></i>  <?php echo $box['box_instance']->title;?></div>
		<div class="font-weight-bold font-size-small text-uppercase mb-2"><?php echo $box['bundle']->bundle_name;?></div>
		<ul class="list-group list-group-flush font-size-small">
			<?php foreach ($box['items'] AS $item) { ?>
				<li class="list-group-item py-1"><div class="float-left wi"><?php echo $item['qty']; ?>&nbsp;</div><?php echo $item['menu_item_name']; ?></li>
			<?php } ?>
		</ul>
	</div>
	<div class="col-4 text-right">
		<div class="row">
			<div class="col pr-2 font-weight-bold">
				$<?php echo $box['bundle']->price;?>
			</div>
		</div>
		<?php if (!$this->isEmptyFloat( $box['bundle']->price_shipping))  { ?>
		<div class="row">
			<div class="col pr-2">
				Shipping <?php if ($this->cart_info["menuObj"]->isEnabled_ShippingDiscount($this->cart_info["storeObj"])) {?><span class="text-decoration-line-through">($14.99)</span><?php } ?> $<?php echo $box['bundle']->price_shipping;?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>