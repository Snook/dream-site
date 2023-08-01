<div class="row mb-4">
	<div class="col-8">
		<div class="font-weight-bold font-size-medium-small text-uppercase mb-2"><i class="dd-icon <?php echo $box['box_info']->css_icon; ?> text-green"></i>  <?php echo $box['box_info']->title;?></div>
		<div class="font-weight-bold font-size-small text-uppercase mb-2"><?php echo $box['bundle_data']->bundle_name;?></div>
		<ul class="list-group list-group-flush font-size-small">
			<?php foreach ($box['bundle_items'] AS $item) {
				if ($item['qty'] > 0) {
				?>
				<li class="list-group-item py-1"><span class="mr-2"><?php echo $item['qty']; ?></span><?php echo $item['display_title']; ?></li>
			<?php } } ?>
		</ul>
	</div>
	<div class="col-4 text-right">
		<div class="row">
			<div class="col pr-2 font-weight-bold">
				$<?php echo $box['bundle_data']->price;?>
			</div>
		</div>
	</div>
</div>