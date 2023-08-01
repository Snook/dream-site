<?php // (!CUser::isLoggedIn()) ? $this->setScript('foot', SCRIPT_PATH . '/customer/account.min.js') : ''; ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/vendor/jquery.creditCardValidator.js'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/checkout.min.js'); ?>
<?php $this->setScriptVar('store_id_of_order = ' . (empty($this->store_id_of_order) ? '0' : $this->store_id_of_order)  . ';'); ?>
<?php $this->setScriptVar("user_id = '" . (empty($this->user_id) ? '0' : $this->user_id) . "';"); ?>
<?php $this->setScriptVar('allowGuest = ' . ($this->allowGuest ? "true" : "false") . ';'); ?>
<?php $this->setScriptVar('gift_card_order_ids = \'' . $this->gc_order_ids . '\';'); ?>
<?php $this->setScriptVar('giftCardPurchaseOnly = true;'); ?>
<?php $this->setScriptVar('avgCostPerServingEntreeServings = false;'); ?>
<?php $this->setScriptVar('avgCostPerServingEntreeCost = false;'); ?>
<?php $this->setScriptVar('ltd_round_up_value = false;'); ?>
<?php $this->setScriptVar('is_discounts_page = false;'); ?>

<?php if (defined('TR_SIM_LINK'))
{
	$this->setScriptVar("transparent_redirect_link = '" . TR_SIM_LINK . "';");
}

if (defined('PFP_TEST_MODE') && PFP_TEST_MODE) {
	$this->setScriptVar("pfp_test_mode = true;");
}

if (defined('PHP_ERROR_URL'))
{
	$this->setScriptVar("payflowErrorURL = '" . PHP_ERROR_URL .  "';");
}

?>
<?php $this->assign('no_cache', true); ?>
<?php $this->assign('page_title', 'Gift Card Checkout');?>
<?php  $this->cart_info = CUser::getCartIfExists(true); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
				<a href="/main.php?page=gift_card_cart" class="btn btn-primary btn-sm btn-md-lg p-2"><span class="pr-2">&#10094;</span> View cart</a>
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Provide payment for your gift cards</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<iframe name="paypal-result" id="paypal-result" class="d-none"></iframe>

	<main role="main">
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_total_gift_card.tpl.php'); ?>

						<?php include $this->loadTemplate('customer/subtemplate/checkout/checkout_cart_gift_card.tpl.php'); ?>
					</div>
					<div class="col-md-6">
						<?php include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_for_gift_card.tpl.php'); ?>
					</div>

				</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>