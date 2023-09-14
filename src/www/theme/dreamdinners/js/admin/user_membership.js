if ($('.input-credit-card').length)
{
	var cleaveCreditCard = new Cleave('.input-credit-card', {
		creditCard: true,
		onCreditCardTypeChanged: function (type) {
			switch (type)
			{
				case 'visa':
					currentTypeWidgetValue = 'Visa';
					break;
				case 'mastercard':
					currentTypeWidgetValue = 'Mastercard';
					break;
				case 'amex':
					currentTypeWidgetValue = 'American Express';
					break;
				case 'Discover':
					currentTypeWidgetValue = 'Discover';
					break;
				default:
					break;
			}

			$('#ccType').val(currentTypeWidgetValue).trigger('change');
		}
	});
}

$(document).on('change', '#ccType', function (e) {

	if ($(this).val() == 'American Express')
	{
		$('#ccSecurityCode').prop({
			'min': '0',
			'max': '9999',
			'maxlength': '4',
			'pattern': '^[0-9]{4}$'
		});
	}
	else
	{
		$('#ccSecurityCode').prop({
			'min': '0',
			'max': '999',
			'maxlength': '3',
			'pattern': '^[0-9]{3}$'
		});
	}
});

$(document).on('change', '#payment_type', function (e) {

	$('.form-payment').hideFlex();

	if ($(this).val() == 'newcc')
	{
		$('#ccNameOnCard, #ccNumber, #ccSecurityCode, #ccMonth, #ccYear, #ccType, #billing_address, #billing_postal_code').prop({
			'disabled': false,
			'required': true
		});
		$('.form-new-credit-card').showFlex();
	}
	else
	{
		$('#ccNameOnCard, #ccNumber, #ccSecurityCode, #ccMonth, #ccYear, #ccType, #billing_address, #billing_postal_code').prop({
			'disabled': true,
			'required': false
		});
	}

	if ($(this).val() == 'cash')
	{
		$('#payment_cash').prop({
			'disabled': false,
			'required': true
		});
		$('.form-payment-cash').showFlex();
	}
	else
	{
		$('#payment_cash').prop({
			'disabled': true,
			'required': false
		});
	}

	if ($(this).val() == 'check')
	{
		$('#payment_check').prop({
			'disabled': false,
			'required': true
		});
		$('.form-payment-check').showFlex();
	}
	else
	{
		$('#payment_check').prop({
			'disabled': true,
			'required': false
		});
	}

});

$(document).on('change', '#product', function (e) {

	$('.checkout-summary, .checkout-line-discount').hide();

	if ($(this).val() != '')
	{
		var discount = 0;
		var discount_var = 0;
		var discount_method = 0;

		var price = parseFloat($(this).find(':selected').data('price'));

		if ($('#discount_coupon').val().trim() != '')
		{
			discount_var = parseFloat($('#discount_coupon').data('discount_var'));
			discount_method = $('#discount_coupon').data('discount_method');

			if (discount_method == 'PERCENT')
			{
				discount = price * (discount_var / 100);
			}
			if (discount_method == 'FLAT')
			{
				discount = discount_var;
			}

			$('.checkout-line-discount').show();
		}

		var discount_price = price - discount;
		var tax_rate = parseFloat($(this).find(':selected').data('tax'));
		var tax = discount_price * (tax_rate / 100);
		var total = discount_price + tax;

		$('.checkout-subtotal').text(formatAsMoney(price));

		$('.checkout-discount').text(formatAsMoney(discount));

		$('.checkout-tax').text(formatAsMoney(tax));
		$('.checkout-total').text(formatAsMoney(total));

		$('#payment_cash, #payment_check').val(formatAsMoney(total)).prop({'readonly': true});

		$('.form-discount-coupon, .checkout-summary').show();

	}

});

$(document).on('click', '.membership-manage:not(.disabled)', function (e) {

	var membership_id = $(this).data('membership_id');

	$('.membership-manage').removeClass('disabled');

	$('[data-membership_id_manage]').hide();
	$('[data-membership_id_manage="' + membership_id + '"]').show();
	$(this).addClass('disabled');

});

$(document).on('click', '.membership-skip-month', function (e) {

	var menu_id = $(this).data('menu_id');
	var user_id = $(this).data('user_id');
	var this_el = this;

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_user_membership',
			op: 'skip',
			menu_id: menu_id,
			user_id: user_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				// reset other buttons
				$('.membership-unskip-month').removeClass('membership-unskip-month').addClass('membership-skip-month').text('Skip month');
				$(this_el).removeClass('membership-skip-month').addClass('membership-unskip-month').text('Unskip month');
				bounce(window.location.href);

			}
			else
			{
				dd_message({
					title: 'Processing Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			dd_message({
				title: 'Error',
				message: response = 'Unexpected error:' + strError
			});
		}
	});

});

$(document).on('click', '.membership-unskip-month', function (e) {

	var menu_id = $(this).data('menu_id');
	var user_id = $(this).data('user_id');
	var this_el = this;

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_user_membership',
			op: 'unskip',
			menu_id: menu_id,
			user_id: user_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				$(this_el).removeClass('membership-unskip-month').addClass('membership-skip-month').text('Skip month');
				bounce(window.location.href);
			}
			else
			{
				dd_message({
					title: 'Processing Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			dd_message({
				title: 'Error',
				message: response = 'Unexpected error:' + strError
			});
		}
	});

});

$(document).on('click', '.membership-cancel', function (e) {

	var membership_id = $(this).data('membership_id');
	var user_id = $(this).data('user_id');

	bootbox.confirm({
		message: 'Are you sure you wish to cancel this membership and refund the fee?',
		buttons: {
			confirm: {
				label: 'Yes',
			},
			cancel: {
				label: 'No',
			}
		},
		callback: function (result) {
			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_user_membership',
						op: 'cancel',
						membership_id: membership_id,
						user_id: user_id
					},
					success: function (json) {
						if (json.processor_success)
						{
							// refresh page
							bounce(window.location.href);
						}
						else
						{
							dd_message({
								title: 'Processing Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						dd_message({
							title: 'Error',
							message: response = 'Unexpected error:' + strError
						});
					}
				});
			}
		}
	});

});

$(document).on('click', '.membership-terminate', function (e) {

	var membership_id = $(this).data('membership_id');
	var user_id = $(this).data('user_id');

	bootbox.confirm({
		message: 'Are you sure you wish to cancel this membership? The fee will not be refunded.',
		buttons: {
			confirm: {
				label: 'Yes',
			},
			cancel: {
				label: 'No',
			}
		},
		callback: function (result) {
			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_user_membership',
						op: 'terminate',
						membership_id: membership_id,
						user_id: user_id
					},
					success: function (json) {
						if (json.processor_success)
						{
							// refresh page
							bounce(window.location.href);
						}
						else
						{
							dd_message({
								title: 'Processing Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						dd_message({
							title: 'Error',
							message: response = 'Unexpected error:' + strError
						});
					}
				});
			}
		}
	});

});

$(document).on('click', '.membership-reinstate', function (e) {

	var membership_id = $(this).data('membership_id');
	var user_id = $(this).data('user_id');

	bootbox.confirm({
		message: 'Are you sure you wish to reinstate this membership?',
		callback: function (result) {
			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_user_membership',
						op: 'reinstate',
						membership_id: membership_id,
						user_id: user_id
					},
					success: function (json) {
						if (json.processor_success)
						{
							// refresh page
							bounce(window.location.href);
						}
						else
						{
							dd_message({
								title: 'Processing Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						dd_message({
							title: 'Error',
							message: response = 'Unexpected error:' + strError
						});
					}
				});
			}
		}
	});

});

$(document).on('click', '.apply-coupon', function (e) {

	var coupon_code = $('#discount_coupon').val().trim();
	var user_id = $('#discount_coupon').data('user_id');
	var store_id = $('#discount_coupon').data('store_id');

	if (coupon_code == '')
	{
		$('#discount_coupon').data('discount_var', 0);
		$('#discount_coupon').data('discount_method', 0);
		$('#product').trigger('change');
	}
	else
	{
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_user_membership',
				op: 'check_coupon',
				coupon_code: coupon_code,
				user_id: user_id,
				store_id: store_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					$('#discount_coupon').data('discount_var', json.discount_var);
					$('#discount_coupon').data('discount_method', json.discount_method);
					$('#product').trigger('change');
				}
				else
				{
					dd_message({
						title: 'Processing Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				dd_message({
					title: 'Error',
					message: response = 'Unexpected error:' + strError
				});
			}
		});
	}

});

function init_with_error()
{
	$('#product').trigger("change");
}