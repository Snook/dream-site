<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1 d-print-none">
			<a href="/locations" class="btn btn-primary"><span class="pr-2">&#10094;</span> Change Store Location</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
			<h2><span class="text-green font-weight-semi-bold"><?php echo $this->menu_info['menu_month']; ?></span> menu</h2>
			<p>Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></p>
			<?php if ($this->cart_info['cart_info_array']['navigation_type'] == CTemplate::EVENT || !empty($this->cart_info['cart_info_array']['direct_invite'])) { ?>
				<div class="row">
					<div class="col text-center">
						<button class="btn btn-primary btn-sm clear-cart-session">Order for another date</button>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right d-print-none">
			<?php if (empty($this->menu_view)) { ?>
				<a href="/checkout" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
			<?php } else { ?>
				<a href="/session-menu?view=freezer" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
			<?php } ?>
		</div>
	</div>
</header>

<main class="container">
	<?php
	if (!empty($this->session['dream_taste_theme_string']))
	{
		include $this->loadTemplateIfElse('customer/subtemplate/event_theme/' . $this->session['dream_taste_theme_string'] . '/header.tpl.php', 'customer/subtemplate/event_theme/' . $this->session['dream_taste_theme_string_default'] . '/header.tpl.php');
	}
	else if (!empty($this->cart_info['sessionObj']) && is_object($this->cart_info['sessionObj']))
	{
		if ($this->cart_info['sessionObj']->isStandardPrivate())
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/private_party/standard/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/private_party/standard/default/header.tpl.php');
		}
		else if ($this->cart_info['sessionObj']->isRemotePickupPublic() && !empty($this->cart_info['cart_info_array']['direct_invite']))
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/made_for_you/remote_pickup/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/made_for_you/remote_pickup/default/header.tpl.php');
		}
		else if ($this->cart_info['sessionObj']->isRemotePickupPrivate() && !empty($this->session['session_type_true']))
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/made_for_you/remote_pickup_private/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/made_for_you/remote_pickup_private/default/header.tpl.php');
		}
		else if ($this->cart_info['sessionObj']->isMadeForYou() && !$this->cart_info['sessionObj']->isDelivery() && !empty($this->cart_info['cart_info_array']['direct_invite']))
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/made_for_you/standard/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/made_for_you/standard/default/header.tpl.php');
		}
		else if ($this->cart_info['sessionObj']->isDelivery() && !empty($this->cart_info['cart_info_array']['direct_invite']))
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/made_for_you/delivery/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/made_for_you/delivery/default/header.tpl.php');
		}
		else if (!empty($this->cart_info['cart_info_array']['direct_invite']))
		{
			include $this->loadTemplateIfElse('customer/subtemplate/event_theme/standard/standard/standard/' . CTemplate::dateTimeFormat($this->menu_info['menu_name'], YEAR_UNDERSCORE_MONTH) . '/header.tpl.php', 'customer/subtemplate/event_theme/standard/standard/standard/default/header.tpl.php');
		}
		else
		{
			include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_select_menu.tpl.php');
		}
	}
	else
	{
		include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_select_menu.tpl.php');
	}
	?>

	<?php if (!empty($this->session['dream_taste_can_rsvp_only'])) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/event_theme/event_theme_rsvp.tpl.php'); ?>
	<?php } ?>

	<div class="session_menu_div <?php if (!empty($this->session['dream_taste_can_rsvp_only'])) { ?>collapse<?php } ?>">

		<?php if (!empty($this->session['dream_taste_can_rsvp_only'])) { ?>
			<div class="row mt-5">
				<div class="col text-center">
					<h2>Select your meals</h2>
				</div>
			</div>
		<?php } ?>

		<div class="row">
			<div class="col-12 col-md-8 col-print-12">

				<div class="container-fluid px-0 sticky-top mobile-cart-div d-print-none">
					<div class="row bg-white d-md-none mb-4">
						<div class="col">
							<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart.tpl.php'); ?>
						</div>
					</div>
				</div>

				<div class="card-deck m-md-auto">
					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu.tpl.php'); ?>
				</div>
			</div>

			<div class="col-12 col-md-4 px-md-4 d-none d-md-block d-print-none">

				<div class="container p-0 sticky-top bg-white pt-md-2 desktop-cart-div">

					<?php include $this->loadTemplate('customer/subtemplate/session_menu/session_menu_menu_cart.tpl.php'); ?>

					<div class="row mt-4">
						<span class="col-6 text-center text-green-dark font-size-small font-weight-bold"><span class="text-uppercase">Medium</span> serves 2-3</span>
						<span class="col-6 text-center text-green font-size-small font-weight-bold"><span class="text-uppercase">Large</span> serves 4-6</span>
					</div>
					<div class="row mt-3 d-none d-md-block font-size-small">
						<?php foreach (CRecipe::getIconSchematic($this->cart_info["menuObj"]) AS $icon) { ?>
							<?php if ($icon['site_legend_enabled']) { ?>
								<div class="col-12">
									<?php if (!empty($icon['css_icon'])) { ?><i class="font-size-medium-large align-middle dd-icon <?php echo $icon['css_icon']; ?>"></i><?php } ?> <?php echo $icon['label']; ?>
								</div>
							<?php } ?>
						<?php } ?>

						<?php if ($this->order_type == COrders::STANDARD || $this->order_type == COrders::INTRO || $this->order_type == COrders::MADE_FOR_YOU) { ?>
							<div class="col-12 mt-4">
								<a class="btn btn-green-dark-extra btn-sm btn-block" href="/print?store=<?php echo $this->cart_info['store_info']['id']; ?>&amp;menu=<?php echo $this->cart_info['menu_info']['id']; ?><?php echo(($this->order_type == COrders::INTRO) ? '&amp;intro=true' : ''); ?>" target="_blank"><i class="dd-icon icon-print mr-2"></i> Print <?php echo $this->menu_info['menu_month']; ?> Menu</a>
								<a class="btn btn-green-dark-extra btn-sm btn-block" href="/print?store=<?php echo $this->cart_info['store_info']['id']; ?>&amp;menu=<?php echo $this->cart_info['menu_info']['id']; ?>&amp;nutrition=true" target="_blank"><i class="dd-icon icon-print mr-2"></i> Print <?php echo $this->menu_info['menu_month']; ?> Nutrition</a>
								<a class="btn btn-green-dark-extra btn-sm btn-block" href="/nutritionals?store=<?php echo $this->cart_info['store_info']['id']; ?>&amp;menu=<?php echo $this->cart_info['menu_info']['id']; ?>" target="_blank">View <?php echo $this->menu_info['menu_month']; ?> Menu & Sides Nutrition</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-5 d-print-none">
		<div class="col p-0 text-right">
			<?php if (empty($this->menu_view)) { ?>
				<a href="/checkout" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
			<?php } else { ?>
				<a href="/session-menu?view=freezer" class="btn btn-primary disabled continue-btn">Continue <span class="pl-2">&#10095;</span></a>
			<?php } ?>
		</div>
	</div>

	<div class="row mt-3">
		<p class="text-center font-italic">Your local store may make substitutions to various ingredients based on availability. These substitutions can affect our nutritional information and may cause slight changes to what is listed for each recipe.</p>
	</div>

	<?php if ($this->order_type == COrders::STANDARD || $this->order_type == COrders::MADE_FOR_YOU || $this->order_type == COrders::INTRO) { ?>
		<div class="row mb-4 d-md-none d-print-none">
			<div class="col">
				<a class="btn btn-green-dark-extra btn-sm w-100" href="/nutritionals?store=<?php echo $this->cart_info['store_info']['id']; ?>&amp;menu=<?php echo $this->cart_info['menu_info']['id']; ?>" target="_blank"> View <?php echo $this->menu_info['menu_month']; ?> Menu & Sides Nutrition</a>
			</div>
		</div>
	<?php } ?>

</main>