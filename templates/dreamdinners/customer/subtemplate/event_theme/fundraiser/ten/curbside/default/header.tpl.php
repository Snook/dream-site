<div class="row mb-5">
	<div class="col-12 col-lg-6">
		<h3>FUNDRAISER</h3>
		<p>Help <span class="font-weight-bold"><?php echo $this->session['fundraiser_name']; ?></span> meet their fundraising goal.</p>
		<p>At this exclusive pick up event, you will receive three delicious, medium-size dinners to take home to enjoy with your family. You'll also learn about how easy homemade meals can be and help a deserving cause, all while having a fun time. We will donate $10 from each order to <?php echo $this->session['fundraiser_name']; ?>.</p>
		<p>Select three medium dinners for $<?php echo $this->session['dream_taste_price']; ?> from the menu below and complete the checkout process. We look forward to seeing you at your pickup time.</p>
		<p><i>*Fundraiser sessions are limited to one fundraising order per household per organization.</i></p>
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
            <p class="font-weight-bold">Benefits <?php echo $this->session['fundraiser_name']; ?></p>
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
		<img src="<?php echo IMAGES_PATH; ?>/event_theme/<?php echo $this->session['dream_taste_theme_string']; ?>/landing-graphic.jpg" class="img-fluid w-100" alt="Fundraiser" />
	</div>
</div>