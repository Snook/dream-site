<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<div class="row">
			<div class="col">
				<h3><?php echo $this->session['session_host_informal_name']; ?>'s Pick Up Event</h3>
				<p>You’re invited to a special pick up event hosted by <?php echo $this->session['session_host_informal_name']; ?>. Order your perfectly prepped dinners from our menu below. We will assemble them for your family at our local assembly kitchen and you simply pick up your dinners at <?php echo $this->session['session_host_informal_name']; ?>'s community pick up location on <?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?>.</p>
				<p class="font-italic text-muted">Note: A store fee may be added at checkout for this private event.</p>
			</div>
		</div>
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
				<p class="font-weight-bold">Pick Up Location Details</p>
				<p class="font-weight-bold"><?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?></p>
				<p class="font-weight-bold">Hosted by <?php echo $this->session['session_host_informal_name']; ?><br>
					<?php echo $this->session['session_remote_location']->address_line1; ?><?php echo (!empty($this->session['session_remote_location']->address_line2)) ? ' ' . $this->session['session_remote_location']->address_line2 : ''; ?> <?php echo $this->session['session_remote_location']->city; ?>, <?php echo $this->session['session_remote_location']->state_id; ?> <?php echo $this->session['session_remote_location']->postal_code; ?></p>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-6">
		<div class="row d-none d-lg-block ">
			<div class="col">
				<img src="<?php echo IMAGES_PATH; ?>/event_theme/standard/made_for_you/standard/<?php echo CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH); ?>/landing-graphic.jpg" class="img-fluid w-100" alt="pick up event menu item" />
			</div>
		</div>
		<div class="row m-2 p-3 bg-gray">
			<div class="col text-center">
				<p>Your dinners will be prepped by<br>Dream Dinners <?php echo $this->session['store_name']; ?></p>
				<p class="mb-0">For questions or concerns about your order call <span class="font-weight-bold"><?php echo $this->cart_info['store_info']['telephone_day']; ?></span> or email <span class="font-weight-bold"><a href="mailto:<?php echo $this->cart_info['store_info']['email_address']; ?>?subject=Regarding session on <?php echo CTemplate::dateTimeFormat($this->session['session_start'], VERBOSE); ?>"><?php echo $this->cart_info['store_info']['email_address']; ?></a></span></p>
			</div>
		</div>
		<?php } ?>
	</div>
</div>