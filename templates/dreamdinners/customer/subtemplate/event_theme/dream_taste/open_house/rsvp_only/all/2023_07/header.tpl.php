<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>You're invited!</h3>
		<p>This summer, the Dream Dinners Foundation is proud to partner with The Leukemia & Lymphoma Society (LLS) for The Dare to Dream Project, supporting pediatric blood cancer patients.</p>
		<p>Join us to learn more about The Dare to Dream Project at our Leukemia and Lymphoma Society Open House Event. Enjoy delicious samples, fun giveaways and help us support a great cause.</p>
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
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/landing-graphic.jpg" class="img-fluid w-100" alt="Open House Event" />
	</div>
</div>