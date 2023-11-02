<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>Join us for an Assembly Session</h3>
		<p>Customize dinners for your family at Dream Dinners. We will take care of all of the shopping, chopping and clean up, so you can assemble a month of meals in about an hour.</p>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<p class="font-weight-bold mb-0">Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="font-weight-bold mb-0"><?php echo $this->cart_info['store_info']['address_line1']; ?><?php echo (!empty($this->cart_info['store_info']['address_line2'])) ? ' ' . $this->cart_info['store_info']['address_line2'] : ''; ?> <?php echo $this->cart_info['store_info']['city'];?>, <?php echo $this->cart_info['store_info']['state_id']; ?></p>
			</div>
		</div>
		<?php if (CUser::getCurrentUser()->isNewBundleCustomer() && !empty($this->session['remaining_intro_slots'])) { ?>
			<div class="row">
				<div class="col text-center">
					<?php if ($this->order_type == CSession::STANDARD) { ?>
						<div>New to Dream Dinners?</div>
						<button class="btn btn-primary btn-sm pp-view-intro" data-view_menu="INTRO">Try our Meal Prep Starter Pack</button>
					<?php } else if ($this->order_type == CSession::INTRO) { ?>
						<button class="btn btn-primary btn-sm pp-view-intro" data-view_menu="STANDARD">View standard menu</button>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="d-none d-lg-block col-12 col-lg-6">
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/standard/standard/standard/<?php echo CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH); ?>/landing-graphic.jpg" class="img-fluid w-100" alt="Meal Prep Workshop" />
	</div>
</div>
