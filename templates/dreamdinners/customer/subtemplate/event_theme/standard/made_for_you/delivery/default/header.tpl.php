<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Home Delivery</h3>
		<p>Want delicious easy dinners delivered directly to your home? Choose this Home Delivery session and we will prep and assemble your dinners then drop them off to your door. A delivery fee may apply.</p>
		<p>Note: Delivery fee will vary based the distance from the store.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<p class="font-weight-bold mb-0">Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="font-weight-bold mb-0"><?php echo $this->cart_info['store_info']['address_line1']; ?><?php echo (!empty($this->cart_info['store_info']['address_line2'])) ? ' ' . $this->cart_info['store_info']['address_line2'] : ''; ?> <?php echo $this->cart_info['store_info']['city'];?>, <?php echo $this->cart_info['store_info']['state_id']; ?></p>
			</div>
		</div>
	</div>
	<div class="d-none d-lg-block col-12 col-lg-6">
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/standard/made_for_you/delivery/<?php echo CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH); ?>/landing-graphic.jpg" class="img-fluid w-100" alt="hoem delivery" />
	</div>
</div>
