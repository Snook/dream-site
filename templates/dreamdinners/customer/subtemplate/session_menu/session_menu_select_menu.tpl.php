<div class="row mb-3">
	<div class="col-12 col-md mb-2 mb-md-0 bg-gray-200 py-3">
		<div class="row">
			<div class="col mb-2 text-center">
				<div class="font-weight-bold pb-2">Looking for another menu?</div>
			</div>
		</div>
		<div class="row">
			<div class="col text-center">
				<span class="btn btn-primary dropdown-toggle sm-change-menu">
					View available menus
				</span>
			</div>
		</div>
		<div class="row mt-2 collapse sm-row-change-menu">
			<div class="col">
				<div class="row justify-content-center">
					<?php if (!empty($this->customerCalendarArray['no_closed_walkin']['menu'])) { ?>
						<?php foreach ($this->customerCalendarArray['no_closed_walkin']['menu'] as $mid => $menuInfo) { ?>
							<?php if (!empty($menuInfo['session_type']['ALL_STANDARD'])) { ?>
								<div class="col-12 col-md-auto">
									<a href="/menu/<?php echo $this->cart_info['storeObj']->id; ?>-<?php echo $menuInfo['DAO_menu']->menu_name_abbr; ?>" class="btn btn-primary w-100 mb-2 <?php if ($mid == $this->cart_info['menu_id'] && $this->cart_info['cart_info_array']['navigation_type'] == CTemplate::ALL_STANDARD) { ?>disabled<?php } ?>">
										<i class="dd-icon icon-<?php echo strtolower($menuInfo['DAO_menu']->menu_month); ?> font-size-medium-small align-middle"></i>
										<span class="px-4"><?php echo $menuInfo['DAO_menu']->menu_month; ?> menu</span>
									</a>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } ?>

					<?php if ($this->cart_info["storeObj"]->showNewUrl()) { ?>
						<div class="col-12 col-md-auto">
							<a href="<?php echo $this->cart_info['storeObj']->new_store_url; ?>" class="btn btn-primary w-100 mb-2">
								<i class="dd-icon icon-<?php echo (max(array_keys($this->customerCalendarArray['no_closed_walkin']['menu'])) == 280) ? 'january' : 'february'; ?> font-size-medium-small align-middle"></i>
								<span class="px-4"><?php echo (max(array_keys($this->customerCalendarArray['no_closed_walkin']['menu'])) == 280) ? 'January' : 'February'; ?> menu</span>
								<i class="fas fa-external-link-alt font-size-medium-small align-middle"></i>
							</a>
							<p class="text-center font-weight-bold">Order from our new site!</p>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ($this->cart_info["storeObj"]->storeSupportsIntroOrders($this->cart_info["menuObj"]->id) && CUser::getCurrentUser()->isEligibleForIntro() && !empty($this->customerCalendarArray['no_closed_walkin']['info']['session_type']['INTRO'])) { ?>
		<div class="col-12 col-md mb-2 mb-md-0 bg-gray-300 py-3">
			<div class="row">
				<div class="col mb-2 text-center">
					<div class="font-weight-bold pb-2">New to Dream Dinners?</div>
					Pick 4 medium or 2 large meals for only $<?php echo (!empty($this->starter_bundle)) ? $this->starter_bundle->price : $this->DAO_bundle->price; ?>
				</div>
			</div>
			<div class="row">
				<div class="col text-center">
					<?php if ($this->order_type != COrders::INTRO) { ?>
						<a href="/menu/<?php echo $this->cart_info['storeObj']->id; ?>-starter" class="btn btn-primary" <?php echo (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS) ? ' data-toggle="tooltip" title="This button currently enabled for all users for testing purposes."' : ''; ?>>View starter pack menu</a>
					<?php } else { ?>
						<span class="btn btn-primary dropdown-toggle sm-change-menu-starter">
							View next months menu
						</span>
					<?php } ?>
				</div>
			</div>
			<div class="row mt-2 collapse sm-row-change-menu-starter">
				<div class="col">
					<div class="row justify-content-center">
						<?php if (!empty($this->customerCalendarArray['no_closed_walkin']['info']['session_type']['INTRO'])) { ?>
							<?php foreach ($this->customerCalendarArray['no_closed_walkin']['menu'] as $mid => $menuInfo) { ?>
								<?php if (!empty($menuInfo['session_type']['INTRO'])) { ?>
									<div class="col-12 col-md-auto">
										<a href="/menu/<?php echo $this->cart_info['storeObj']->id; ?>-<?php echo $menuInfo['DAO_menu']->menu_name_abbr; ?>-starter" class="btn btn-primary w-100 mb-2 <?php if ($mid == $this->cart_info['menu_id'] && $this->cart_info['cart_info_array']['navigation_type'] == CTemplate::INTRO) { ?>disabled<?php } ?>">
											<span class="px-4"><?php echo $menuInfo['DAO_menu']->menu_month; ?> menu</span>
										</a>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($this->cart_info['storeObj']->storeIsClosing($this->cart_info['menuObj'])) { ?>
		<div class="col-12 col-lg bg-cyan-extra-light py-3">
			<div class="row">
				<div class="col mb-2 text-center">
					<div class="font-weight-normal pb-2">Our store is closing at the end of the month, and inventory is limited. We have additional dinners and items from the Sides and Sweets Freezer for sale. If you don't find the dinners you want on this page, click the continue button to view and order additional items.</div>
				</div>
			</div>
		</div>
	<?php } else if (CStore::storeSupportsMealCustomization($this->cart_info['storeObj'])) { ?>
		<div class="col-12 col-lg bg-cyan-extra-light py-3">
			<div class="row">
				<div class="col mb-2 text-center">
					<div class="font-weight-bold pb-2">Meal Customization Now Available</div>
				</div>
			</div>
			<div class="row">
				<div class="col text-center">
					<span class="btn btn-primary dropdown-toggle sm-customization-available">
						Learn more
					</span>
				</div>
			</div>
			<div class="row mt-2 collapse sm-row-customization-available">
				<div class="col">
					Don’t love an ingredient or two? You can personally customize your meals at an assembly session or let us do it by choosing a special customization pick up or home delivery day/time. Pick your meals, then during checkout, let us know if we should leave out onion, garlic, mushroom, olives, bacon, or cilantro. A nominal fee may apply.
				</div>
			</div>
		</div>
	<?php } ?>

</div>