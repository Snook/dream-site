<div class="row cart-top-div">
	<div class="cart-total-div col-10 col-md-12">
		<div class="row  bg-gray-light">
			<?php if ($this->menu_view == 'session_menu') { ?>
				<a href="/session-menu?view=freezer" class="btn btn-primary btn-block btn-spinner disabled continue-btn">Continue</a>
			<?php } else { ?>
				<a href="/checkout" class="btn btn-primary btn-block btn-spinner disabled continue-btn">Continue</a>
			<?php } ?>
		</div>
	</div>
	<button class="col-2 btn btn-orange btn-ripple d-md-none show-cart-button" data-toggle="collapse" data-target=".menuCart" aria-expanded="false">
		<i class="fas fa-shopping-cart"></i>
	</button>
	<div class="col-12 shadow py-2 bg-cyan-dark text-center remaining-note">
		<div class="text-white font-size-small">
			<?php if ($this->order_type == COrders::INTRO) { ?>
				<?php if ($this->menu_id > 252) { ?>
					To order the Meal Prep Starter Pack, select 4 medium dinners or 2 large dinners (or a combination of both sizes).
				<?php } else {?>
					Select 6 medium dinners or 3 large dinners (or a combination of both sizes).
				<?php } ?>
			<?php } else if ($this->order_type == COrders::FUNDRAISER || $this->order_type == COrders::DREAM_TASTE) { ?>
				Select 3 dinners to complete your order.
			<?php } else { ?>
				<?php echo $this->standard_minimum_message; ?>
			<?php } ?>
		</div>
	</div>
</div>