<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Pick Up</h3>
		<p>Don't have enough time to assemble your dinners this month? Choose a Pick Up session and we will assemble delicious dinners for your family. You simply pick up your dinners during the selected pick up window. A nominal fee may apply. </p>
		<p>Want to assemble your meals in our store? <a href="/main.php?static=how_it_works">Learn more</a> about how our assembly sessions work.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<p class="font-weight-bold mb-0">Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="font-weight-bold mb-0"><?php echo $this->cart_info['store_info']['address_line1']; ?><?php echo (!empty($this->cart_info['store_info']['address_line2'])) ? ' ' . $this->cart_info['store_info']['address_line2'] : ''; ?> <?php echo $this->cart_info['store_info']['city'];?>, <?php echo $this->cart_info['store_info']['state_id']; ?></p>
			</div>
		</div>
	</div>
	<div class="d-none d-lg-block col-12 col-lg-6">
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/standard/made_for_you/standard/<?php echo CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH); ?>/landing-graphic.jpg" class="img-fluid w-100" alt="pick up" />
	</div>
</div>
