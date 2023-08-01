<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Let’s Cook Up Some Fun!</h3>
		<p>You’re invited to my free Meal Prep Workshop at Dream Dinners. We'll enjoy a fun event together and learn the secret to making easy homemade meals at my exclusive, free event. You’ll receive three delicious, medium-size meals that are already prepped and ready to enjoy at home with your family.</p>
		<p>View and choose which three dinners from the menu below you would like. Contact us to place your order.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
            <p class="font-weight-bold">Hosted by <?php echo $this->session['session_host_informal_name']; ?></p>
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<?php if (!empty($this->session['session_host_message'])) { ?>
					<p><?php echo $this->session['session_host_message']; ?></p>
				<?php } ?>
				<?php if (!empty($this->session['session_details'])) { ?>
					<p><?php echo $this->session['session_details']; ?></p>
				<?php } ?>
				<p class="font-weight-bold mb-0">Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="font-weight-bold mb-0"><?php echo $this->cart_info['store_info']['address_line1']; ?><?php echo (!empty($this->cart_info['store_info']['address_line2'])) ? ' ' . $this->cart_info['store_info']['address_line2'] : ''; ?> <?php echo $this->cart_info['store_info']['city'];?>, <?php echo $this->cart_info['store_info']['state_id']; ?></p>
			</div>
		</div>
	</div>
	<div class="d-none d-lg-block col-12 col-lg-6">
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/landing-graphic.jpg" class="img-fluid w-100" alt="Meal Prep Workshop" />
	</div>
</div>