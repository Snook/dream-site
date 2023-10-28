<?php (!CUser::isLoggedIn()) ? $this->setScript('foot', SCRIPT_PATH . '/customer/account.min.js') : ''; ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/jquery.creditCardValidator.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/checkout.min.js'); ?>
<?php (!CUser::isLoggedIn() && !$this->allowGuest) ? $this->setOnload('handle_account_form_validation();') : ''; ?>
<?php (!CUser::isLoggedIn()) ? $this->setScriptVar('is_create = true;') : ''; ?>
<?php $this->setScriptVar('maxPPCredit = ' .  (!empty( $this->maxPPCredit) ?  $this->maxPPCredit : 0) .  ';'); ?>
<?php $this->setScriptVar('maxPPDeduction = ' . (!empty( $this->maxPPDeduction) ?  $this->maxPPDeduction : 0) .  ';'); ?>
<?php $this->setScriptVar('store_id_of_order = ' . $this->store_id_of_order . ';'); ?>
<?php $this->setScriptVar('session_id = ' . $this->session_id . ';'); ?>
<?php $this->setScriptVar('allowGuest = ' . ($this->allowGuest ? "true" : "false") . ';'); ?>
<?php $this->setScriptVar('coupon = ' . (!empty($this->coupon) ? $this->coupon : "false") . ';'); ?>
<?php $this->setScriptVar('avgCostPerServing = ' . $this->cart_info["orderObj"]->getServingsTotalCount() . ';'); ?>
<?php $this->setScriptVar('avgCostPerServingEntreeServings = ' . $this->cart_info["orderObj"]->getServingsCoreTotalCount() . ';'); ?>
<?php $this->setScriptVar('avgCostPerServingEntreeCost = ' . $this->cart_info["orderObj"]->getFoodTotal() . ';'); ?>
<?php $this->setScriptVar('precheck_enroll_in_platepoints = ' . (!empty( $this->precheck_enroll_in_platepoints) ?  $this->precheck_enroll_in_platepoints : 0) . ';'); ?>
<?php $this->setScriptVar('ltd_round_up_value = ' . ((isset($this->orderInfo['ltd_round_up_value']) && $this->orderInfo['ltd_round_up_value'] != '') ? $this->orderInfo['ltd_round_up_value'] : 'null') . ';'); ?>
<?php $this->setScriptVar('is_discounts_page = true;'); ?>
<?php $this->setScriptVar('default_bag_fee = ' . $this->default_bag_fee . ';'); ?>
<?php $this->setScriptVar('supports_bag_fee = ' . ($this->supports_bag_fee ? "true" : "false") . ';'); ?>
<?php $this->setScriptVar('number_bags_required = ' . $this->numberBagsRequired . ';'); ?>
<?php $this->setScriptVar('has_meal_customization_selected = ' . ($this->has_meal_customization_selected ? "true" : "false"). ';'); ?>
<?php $this->setScriptVar('default_meal_customization_to_selected = ' . ($this->default_meal_customization_to_selected ? "true" : "false"). ';'); ?>
<?php $this->setScriptVar('meal_customization_preferences = ' . (empty($this->meal_customization_preferences_json) ? '{}' : $this->meal_customization_preferences_json). ';'); ?>
<?php $this->setScriptVar('scroll = "' . $this->scroll . '";'); ?>
<?php $this->assign('no_cache', true); ?>
<?php $this->assign('page_title', 'Checkout');?>
<?php $this->assign('logout_navigation_page', '?back=/checkout'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('customer/subtemplate/edit_order.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<?php if (!empty($this->cart_info['cart_info_array']['direct_invite'])) { ?>
					<a href="/session" class="btn btn-primary"><span class="pr-2">&#10094;</span> Change Session</a>
				<?php } else { ?>
					<?php if ($this->cart_info['cart_info_array']['navigation_type'] == CTemplate::DELIVERED) { ?>
						<a href="/box-select" class="btn btn-primary"><span class="pr-2">&#10094;</span> Make Changes</a>
					<?php } else { ?>
					<a href="/session" class="btn btn-primary"><span class="pr-2">&#10094;</span> Back to Calendar</a>
				<?php } ?>
				<?php } ?>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Provide <span class="text-green font-weight-semi-bold">Additional Information</span></h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_total.tpl.php'); ?>

						<?php if ($this->cart_info['cart_info_array']['navigation_type'] == CTemplate::DELIVERED) {?>
							<?php include $this->loadTemplate('customer/subtemplate/box_checkout/checkout_cart.tpl.php'); ?>
						<?php } else { ?>
							<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_cart.tpl.php'); ?>
						<?php } ?>
					</div>
					<div class="col-md-6">
						<?php
						if (CUser::isLoggedIn() || (isset($this->allowGuest) && $this->allowGuest))
						{
							include $this->loadTemplate('customer/subtemplate/payment_form/discounts_form.tpl.php');
						}
						else
						{
							include $this->loadTemplate('customer/subtemplate/checkout/checkout_login.tpl.php');
						}
						?>
					</div>
				</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>