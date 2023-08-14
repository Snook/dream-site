$(function () {

	$(document).on('change', 'input[name="order_as_is"]', function (e) {

		if ($(this).val() == '0')
		{
			// no is selected
			$('#order_as_is_no').slideDown();
			$('#order_as_is_yes').slideUp();
		}
		else
		{
			// no is not selected
			$('#order_as_is_no').slideUp();
			$('#order_as_is_yes').slideDown();
		}

	});

});
