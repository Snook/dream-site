<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Let’s Cook Up Some Fun!</h3>
		<p>You’re invited to my Meal Prep Workshop. Learn my secret to making easy homemade meals at my exclusive pick up event. You’ll receive three delicious, medium-size meals already prepped and ready to enjoy at home with your family for <span class="font-weight-bold">just $<?php echo $this->bundle_cost; ?></span>.</p>
		<p>Plus, you get our exclusive Mug Cake trio recipes. Only available at our Meal Prep Workshops, these three unique recipes are a fun baking activity the whole family can help with.</p>
		<p>There are limited spaces available, and this offer is only available for guests of this event. Select three dinners from the menu below and complete your order to reserve your spot today!</p>
		<?php if (!empty($this->session['session_details'])) { ?>
			<div class="row m-2 p-3 bg-gray">
				<div class="col text-center">
					<p class="text-center font-weight-bold">Event Notes</p>
					<p class="mb-0"><?php echo $this->session['session_details']; ?></p>
				</div>
			</div>
		<?php } ?>
		
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
            <p class="font-weight-bold">Hosted by <?php echo $this->session['session_host_informal_name']; ?></p>
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<?php if (!empty($this->session['session_host_message'])) { ?>
					<p><?php echo $this->session['session_host_message']; ?></p>
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