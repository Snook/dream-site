
<?php
include $this->loadTemplate('customer/subtemplate/checkout/checkout_meal_customization.tpl.php');
?>

<?php if ($this->supports_bag_fee) { ?>
	<div class="row mb-2">
		<div class="col">

			<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left">Reusable Bags</h2>
		</div>
	</div>
	<div class="row mb-2">
		<div class="col">
			<div id="bag_text" class="<?php echo ($this->opted_to_bring_bags ? 'collapse' : '') ?> font-size-medium-small text-left mb-2"><?php echo $this->numberBagsRequired;?> Bag<?php echo $this->numberBagsRequired>1?'s':'';?></div>
			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input pl-3" id="opted_to_bring_bags" name="opted_to_bring_bags" <?php echo ($this->opted_to_bring_bags ? 'checked="checked"' : '') ?>>
				<label class="custom-control-label" for="opted_to_bring_bags">I will bring my own bags/cooler</label>
			</div>
		</div>
	</div>
	<br>
<?php } ?>

<?php
// PLATEPOINTS credits
if (!$this->isEditDeliveredOrder)
{
	if (isset($this->form_payment['plate_points_discount_html']))
	{
		include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_points_credit.tpl.php');
	}
}?>

<?php
// Store Credits
if ($this->payment_enabled_store_credit && !empty($this->Store_Credits) && !$this->isGiftCardOnlyOrder)
{
	include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_store_credit.tpl.php');
}
?>

<?php if($this->cart_info['store_info']['supports_bag_fee']){ ?>

<?php } ?>

<?php
// Coupon
if ($this->payment_enabled_coupon && !$this->isGiftCardOnlyOrder)
{
	include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_coupon.tpl.php');
}
?>

<?php
// Gift card payment form
if ($this->payment_enabled_gift_card)
{
	include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_gift_card.tpl.php');
}
?>


<form id="to_payment_or_checkout_form" method="post" class="needs-validation" novalidate>
	<?php if (isset($this->form_payment['hidden_html'])) echo $this->form_payment['hidden_html'];?>

	<?php if ($this->cart_info['sessionObj']->isDelivery() || $this->cart_info['sessionObj']->isDelivered()) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_shipping_address.tpl.php'); ?>
	<?php } ?>

	<?php if (!$this->isGiftCardOnlyOrder && $this->cart_info['session_info']['session_type'] !== CSession::DELIVERED ) { ?>
		<div class="row">
			<div class="col">
				<div class="form-row">
					<div class="form-group col-md-12">
						<h3 class="font-size-small text-uppercase font-weight-semi-bold">Note to the store </h3>
						<i class="font-size-small">(any special requests may incur additional fees)</i>
						<?php echo $this->form_payment['special_insts_html']; ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if (isset($this->allowGuest) && $this->allowGuest) {
		include $this->loadTemplate('customer/subtemplate/payment_form/payment_form_billing_email.tpl.php'); ?>
		<input type="hidden" name="run_as_guest" value="true" />
	<?php  } ?>


	<div id="checkoutOption" class="collapse">
		<?php // hidden unless there is enough coupons, dinner dollars,store credit or gift cards to pay for the order ?>
		<div class="row mb-2">
			<div class="col text-center">
				<?php $this->tandc_page = 'checkout'; include $this->loadTemplate('customer/subtemplate/terms_and_conditions/tandc_agree.tpl.php'); ?>
			</div>
		</div>

		<div class="row">
			<div class="col">
				<div class="form-row">
					<div class="form-group col-md-12 text-center">
						<?php echo $this->form_payment['complete_order_html']; ?>
						<div class="invalid-feedback form-feedback">Please complete the required information.</div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div id="toPaymentOption" class="collapse show">
		<?php // hidden unless there is enough coupons, dinner dollars,store credit or gift cards to pay for the order ?>

		<div class="row">
			<div class="col">
				<div class="form-row">
					<div class="form-group col-md-12 text-center">
						<?php echo $this->form_payment['to_payment_html']; ?>
						<div class="invalid-feedback form-feedback">Please complete the required information.</div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<input type="hidden" name="opted_to_bring_bags_hidden" id="opted_to_bring_bags_hidden" value="" />
</form>

<?php if ($this->cart_info["orderObj"]->isShipping()) { ?>
	<p class="font-size-small font-italic">Note: Our delicious dishes are expertly crafted at our local assembly stores and delivered straight to your door. Unfortunately, we are unable to accommodate customizations or special requests at this time.</p>
<?php } ?>
<?php if ($this->should_allow_meal_customization) { ?>
	<p class="font-size-small font-italic">*Meal Customization: <?php echo OrdersCustomization::RECIPE_LEGAL; ?></p>
<?php } ?>