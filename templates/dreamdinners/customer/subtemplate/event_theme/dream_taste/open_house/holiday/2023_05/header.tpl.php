<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Celebrate with our Spring Trial Offer</h3>
		<p><span class="font-weight-bold">For only $<?php echo $this->bundle_cost; ?></span>, Dream Dinners has you covered for both your Spring Breakfast and beyond. During this exclusive pick up event, you will receive our Spring Brunch Bundle including our Lemon French Toast Bake, Breakfast Frittata and Breakfast Potatoes plus three medium dinners to enjoy for the week that follows.</p>
		<p>Space is limited — reserve your spot today. We're excited to introduce you to the Dream Dinners experience and make your Spring homemade, made easy.</p>
		<p>*Your Spring Brunch Bundle serves 2-3 people.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
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