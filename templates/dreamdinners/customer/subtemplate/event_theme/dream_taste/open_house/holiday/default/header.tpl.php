<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Celebrate the season with our Holiday Trial Offer.</h3>
		<p><span class="font-weight-bold">For only $<?php echo $this->bundle_cost; ?></span>, Dream Dinners has you covered for both your holiday and the week that follows. With quick prep and our step-by-step timeline, you will get a full holiday dinner with all the fixings. Easy as pie! Plus, you will take home three additional medium dinners to enjoy after the big day. </p>
		<p>During this exclusive pick up event, you will receive our Holiday Roasted Turkey, Holiday Mashed Potatoes, Holiday Savory Stuffing, Holiday Green Bean Casserole, and Holiday Trio featuring Homestyle Biscuits with Pumpkin Butter, Turkey Gravy, and Cranberry Relish.  Serves 8-10 people.</p>
		<p>We’re excited to introduce you to the Dream Dinners experience and make your holiday homemade, made easy. Availability is limited — place your pick up order today.
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