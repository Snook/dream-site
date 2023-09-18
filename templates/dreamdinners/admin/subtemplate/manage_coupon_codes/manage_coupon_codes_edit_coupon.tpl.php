<form method="post" id="coupon_edit" name="coupon_edit" data-coupon_id="<?php echo (!empty($this->coupon->id)) ? $this->coupon->id : 'new'; ?>" class="needs-validation" novalidate>
	<?php echo $this->CouponForm['hidden_html']; ?>

	<div class="form-row mb-3">

		<div class="form-group col-md-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_store_coupon">Store coupon</label>
				</div>
				<?php echo $this->CouponForm['is_store_coupon_html']; ?>
			</div>
		</div>
		<div class="form-group col-md-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_delivered_coupon">Delivered coupon</label>
				</div>
				<?php echo $this->CouponForm['is_delivered_coupon_html']; ?>
			</div>
		</div>
		<div class="form-group col-md-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_product_type_membership">Product coupon</label>
				</div>
				<?php echo $this->CouponForm['is_product_coupon_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon">

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_order_editor_supported">Supported on Order Manager</label>
				</div>
				<?php echo $this->CouponForm['is_order_editor_supported_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_customer_order_supported">Supported on Customer Site</label>
				</div>
				<?php echo $this->CouponForm['is_customer_order_supported_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-product_coupon">

		<div class="col-12">
			<h4>Product</h4>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_product_type_membership">Meal Prep+ Membership</label>
				</div>
				<?php echo $this->CouponForm['valid_for_product_type_membership_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon section-product_coupon">

		<div class="col-12">
			<h4>Coupon details</h4>
		</div>

		<div class="form-group col-md-12">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="program_id">Program</label>
				</div>
				<?php echo $this->CouponForm['program_id_html']; ?>
				<!--
				<div class="input-group-append">
					<a class="btn btn-primary" href="/?page=admin_manage_coupon_codes&amp;program_create=true">Create new</a>
				</div>
				-->
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="coupon_code">Coupon Code</label>
				</div>
				<?php echo $this->CouponForm['coupon_code_html']; ?>
			</div>
			<div class="form-text">Letters and numbers only, no spaces, max 36 characters</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="discount_method">Discount</label>
				</div>
				<?php echo $this->CouponForm['discount_method_html']; ?>
				<div class="input-group-append">
					<?php echo $this->CouponForm['discount_var_html']; ?>
				</div>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="coupon_code_short_title">Compact title</label>
				</div>
				<?php echo $this->CouponForm['coupon_code_short_title_html']; ?>
			</div>
			<div class="form-text">Displays on customer website and receipts</div>
		</div>
		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="coupon_code_title">Long title</label>
				</div>
				<?php echo $this->CouponForm['coupon_code_title_html']; ?>
			</div>
			<div class="form-text">Displays on customer website and receipts</div>
		</div>
		<div class="form-group col-md-12">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="coupon_code_description">Description for store</label>
				</div>
				<?php echo $this->CouponForm['coupon_code_description_html']; ?>
			</div>
			<div class="form-text">Displays on store's coupon manager as help information</div>
		</div>
		<div class="form-group col-md-12">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="comments">Admin notes</label>
				</div>
				<?php echo $this->CouponForm['comments_html']; ?>
			</div>
			<div class="form-text">Home Office notes regarding this coupon</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_store_coupon">Store specific</label>
				</div>
				<?php echo $this->CouponForm['is_store_specific_html']; ?>
				<div class="input-group-append">
					<?php echo $this->CouponForm['multi_store_select_html']; ?>
				</div>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon section-product_coupon">

		<div class="col-12">
			<h4 class="pb-0">Dates</h4>
			<p class="font-size-small">Choose the date span the coupon can be entered, the time of ordering, not the session date.</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_timespan_start">Start date</label>
				</div>
				<?php echo $this->CouponForm['valid_timespan_start_html']; ?>
				<div class="input-group-append">
					<span class="btn btn-primary valid_timespan_start_now">Immediately</span>
				</div>
			</div>
			<div class="form-text">The date the coupon can start being entered</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_timespan_end">End date</label>
				</div>
				<?php echo $this->CouponForm['valid_timespan_end_html']; ?>
				<div class="input-group-append">
					<span class="btn btn-primary valid_timespan_end_max">Max Dec 31, 2037</span>
				</div>
			</div>
			<div class="form-text">The date the coupon can no longer be entered</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon">

		<div class="col-12">
			<h4>Menus</h4>
			<p class="font-size-small">Choose the menus the coupon can be applied to</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_menuspan_start">Menu start</label>
				</div>
				<?php echo $this->CouponForm['valid_menuspan_start_html']; ?>
			</div>
			<div class="form-text">The starting menu the coupon can be applied to</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_menuspan_end">Menu end</label>
				</div>
				<?php echo $this->CouponForm['valid_menuspan_end_html']; ?>
			</div>
			<div class="form-text">The last menu the coupon can be applied to</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4>Session dates</h4>
			<p class="font-size-small">Session periods the coupon can be applied to</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_session_timespan_start">Session start date</label>
				</div>
				<?php echo $this->CouponForm['valid_session_timespan_start_html']; ?>
				<div class="input-group-append">
					<button class="btn btn-primary" id="valid_session_timespan_start_clear" name="valid_session_timespan_start_clear"><i class="fas fa-eraser"></i></button>
				</div>
			</div>
			<div class="form-text">A session within these dates must be in the cart to apply the coupon</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_session_timespan_end">Session end date</label>
				</div>
				<?php echo $this->CouponForm['valid_session_timespan_end_html']; ?>
				<div class="input-group-append">
					<button class="btn btn-primary" id="valid_session_timespan_end_clear" name="valid_session_timespan_end_clear"><i class="fas fa-eraser"></i></button>
				</div>
			</div>
			<div class="form-text">A session within these dates must be in the cart to apply the coupon</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4 class="pb-0">Session types</h4>
			<p class="font-size-small">Choose the types of sessions this coupon can be used with</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_session_type_standard">Assembly / Pick Up / Walk-In</label>
				</div>
				<?php echo $this->CouponForm['valid_for_session_type_standard_html']; ?>
			</div>
			<div class="form-text">Standard qualifying order</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_session_type_private">Private session</label>
				</div>
				<?php echo $this->CouponForm['valid_for_session_type_private_html']; ?>
			</div>
			<div class="form-text">Can be used with a session that requires a password</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_session_type_discounted">Discounted sessions</label>
				</div>
				<?php echo $this->CouponForm['valid_for_session_type_discounted_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_session_type_delivery">Home Delivery</label>
				</div>
				<?php echo $this->CouponForm['valid_for_session_type_delivery_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4 class="pb-0">Order types</h4>
			<p class="font-size-small">Choose the types of orders this coupon can be used with</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_order_type_standard">Standard</label>
				</div>
				<?php echo $this->CouponForm['valid_for_order_type_standard_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_order_type_intro">Starter Pack</label>
				</div>
				<?php echo $this->CouponForm['valid_for_order_type_intro_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_for_order_type_dream_taste">Meal Prep Workshop</label>
				</div>
				<?php echo $this->CouponForm['valid_for_order_type_dream_taste_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4 class="pb-0">Programs</h4>
			<p class="font-size-small">Choose the types of programs this coupon can be used in combination with</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="valid_with_plate_points_credits">Dinner Dollars</label>
				</div>
				<?php echo $this->CouponForm['valid_with_plate_points_credits_html']; ?>
			</div>
		</div>
<!--
		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="is_platepoints_perk">PlatePoints Perk</label>
				</div>
				<?php echo $this->CouponForm['is_platepoints_perk_html']; ?>
			</div>
			<div class="form-text">Special case, this needs to have code modified if YES</div>
		</div>
-->
	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon">

		<div class="col-12">
			<h4>Customer requirements</h4>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="applicable_customer_type">Customer type</label>
				</div>
				<?php echo $this->CouponForm['applicable_customer_type_html']; ?>
			</div>
			<div class="form-text" id="applicable_customer_type_description"></div>
			<div class="form-text"><span class="font-weight-bold">New</span> guest means they are brand new, no order history. <span class="font-weight-bold">Existing</span> guest means they have attended a session any time in the past. To target <span class="font-weight-bold">Reacquired</span>, choose Existing guest absent specified number of months and set the number of months to 12. All options are based on the current day the coupon is being applied to the guest's latest session date.</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="use_limit_per_account">Uses per account</label>
				</div>
				<?php echo $this->CouponForm['use_limit_per_account_html']; ?>
			</div>
			<div class="form-text">Uses per account regardless of absence. Setting this to <span class="font-weight-bold">0</span> will allow unlimited use.</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="remiss_cutoff_date">Absent since date</label>
				</div>
				<?php echo $this->CouponForm['remiss_cutoff_date_html']; ?>
			</div>
			<div class="form-text">Existing guest absence since a specific date.</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="remiss_number_of_months">Absent number of months</label>
				</div>
				<?php echo $this->CouponForm['remiss_number_of_months_html']; ?>
			</div>
			<div class="form-text">Existing guest absence in months. To target Reacquired guests, set this to 12 months.</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4 class="pb-0">Minimum requirements</h4>
			<p class="font-size-small">What food minimum does the order require</p>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="minimum_order_amount">Minimum order grand total</label>
					<span class="input-group-text">$</span>
				</div>
				<?php echo $this->CouponForm['minimum_order_amount_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="minimum_servings_count">Minimum Servings</label>
				</div>
				<?php echo $this->CouponForm['minimum_servings_count_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="minimum_item_count">Minimum Core Items</label>
				</div>
				<?php echo $this->CouponForm['minimum_item_count_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon">

		<div class="col-12">
			<h4 class="pb-0">Limit to food or fees</h4>
			<p class="font-size-small">What portion of the total this coupon discounts</p>
		</div>

		<div class="form-group col-md-12">
			<?php echo $this->CouponForm['limit_coupon_html']['limit_to_grand_total']; ?>
		</div>

		<div class="form-group col-md-6">
			<?php echo $this->CouponForm['limit_coupon_html']['limit_to_core']; ?>
		</div>

		<div class="form-group col-md-6">
			<?php echo $this->CouponForm['limit_coupon_html']['limit_to_finishing_touch']; ?>
		</div>

		<div class="form-group col-md-6">
			<?php echo $this->CouponForm['limit_coupon_html']['limit_to_mfy_fee']; ?>
		</div>

		<div class="form-group col-md-6">
			<?php echo $this->CouponForm['limit_coupon_html']['limit_to_delivery_fee']; ?>
		</div>
		<div class="form-group col-md-6">

			<div class="input-group">
				<?php echo $this->CouponForm['limit_coupon_html']['limit_to_recipe_id']; ?>
				<div class="input-group-append pl-2">
					<?php echo $this->CouponForm['recipe_id_html']; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="form-row mb-3 collapse section-delivered_coupon">

		<div class="col-12">
			<h4>Delivered options</h4>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="delivered_requires_medium_box">Requires Medium Box</label>
				</div>
				<?php echo $this->CouponForm['delivered_requires_medium_box_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="delivered_requires_large_box">Requires Large Box</label>
				</div>
				<?php echo $this->CouponForm['delivered_requires_large_box_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="delivered_requires_custom_box">Requires Custom Box</label>
				</div>
				<?php echo $this->CouponForm['delivered_requires_custom_box_html']; ?>
			</div>
		</div>

		<div class="form-group col-md-6">
			<div class="input-group">
				<div class="input-group-prepend">
					<label class="input-group-text font-size-small" for="delivered_requires_curated_box">Requires Curated Box</label>
				</div>
				<?php echo $this->CouponForm['delivered_requires_curated_box_html']; ?>
			</div>
		</div>

	</div>

	<div class="form-row mb-3 collapse section-store_coupon section-delivered_coupon section-product_coupon">

		<div class="col-12">
			<?php echo $this->CouponForm['submit_html']; ?>
		</div>

	</div>
</form>