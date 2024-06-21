<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>You're invited to a Private Party</h3>
		<p>Join me at a Private Party to simplify your mealtime and get prepped family-style dinners to cook at home. Dream Dinners will take care of all of the shopping, chopping, and clean up. All you have to do is relax and enjoy some extra time with your friends and family.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center font-weight-bold">
				<p class="font-weight-bold">
					<?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?>
					to <?php echo CTemplate::dateTimeFormat($this->session['session_end'], TIME_ONLY); ?>
				</p>
				<?php if (!empty($this->session['session_host_informal_name'])) { ?>
					<p>Hosted by <?php echo $this->session['session_host_informal_name']; ?></p>
				<?php } ?>
				<?php if (!empty($this->session['session_details'])) { ?>
					<p><?php echo $this->session['session_details']; ?></p>
				<?php } ?>
				<p class="mb-0">Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="mb-0"><?php echo $this->cart_info['store_info']['address_line1']; ?><?php echo (!empty($this->cart_info['store_info']['address_line2'])) ? ' ' . $this->cart_info['store_info']['address_line2'] : ''; ?> <?php echo $this->cart_info['store_info']['city'];?>, <?php echo $this->cart_info['store_info']['state_id']; ?></p>
			</div>
		</div>
	</div>
	<div class="d-none d-lg-block col-12 col-lg-6">
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/standard/private_party/standard/<?php echo CTemplate::dateTimeFormat($this->session['menu_name'], YEAR_UNDERSCORE_MONTH); ?>/landing-graphic.jpg" class="img-fluid w-100" alt="Meal Prep Workshop" />
	</div>
</div>