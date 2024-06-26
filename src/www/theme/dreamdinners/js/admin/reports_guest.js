$(document).on('change', '#guest_report', function (e) {

	$('.report-submit, .report-option, .report-option-group').hideFlex();
	$('.report-description').html('');

	if ($(this).find(':selected').val() != '')
	{
		$('.report-submit').showFlex();
		$('.report-description').html($(this).find(':selected').data('description'));

		if ($(this).find(':selected').data('multi-store-select'))
		{
			$('.option-multi-store-select').showFlex();
		}

		if ($(this).find(':selected').data('month-start'))
		{
			$('.option-month-start').showFlex();
		}

		if ($(this).find(':selected').data('month-end'))
		{
			$('.option-month-end').showFlex();
		}

		if ($(this).find(':selected').data('date-start'))
		{
			$('.option-date-start').showFlex();
		}

		if ($(this).find(':selected').data('date-end'))
		{
			$('.option-date-end').showFlex();
		}

		if ($(this).find(':selected').data('datetime-start'))
		{
			$('.option-datetime-start').showFlex();
		}

		if ($(this).find(':selected').data('datetime-end'))
		{
			$('.option-datetime-end').showFlex();
		}

		if ($(this).find(':selected').data('query-with-sessions'))
		{
			$('.option-query-with-sessions').showFlex();
		}

		if ($(this).find(':selected').data('query-without-sessions'))
		{
			$('.option-query-without-sessions').showFlex();
		}

		if ($(this).find(':selected').data('query-all-guests'))
		{
			$('.option-query-all-guests').showFlex();
		}

		if ($(this).find(':selected').data('filter-guest-info'))
		{
			$('.option-filter-guest-info').showFlex();
		}

		if ($(this).find(':selected').data('filter-guest-orders'))
		{
			$('.option-filter-guest-orders').showFlex();
		}

		if ($(this).find(':selected').data('filter-guest-loyalty'))
		{
			$('.option-filter-guest-loyalty').showFlex();
		}

		if ($(this).find(':selected').data('filter-guest-additional-info'))
		{
			$('.option-filter-guest-additional-info').showFlex();
		}

		$(".report-option.show").closest(".report-option-group").showFlex();;
	}

});

$(document).ready(function () {
	$('#guest_report').trigger('change');
});