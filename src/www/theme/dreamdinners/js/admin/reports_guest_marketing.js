$(document).on('change', '#marketing_report', function (e) {

	$('.report-submit, .report-option').hideFlex();
	$('.report-description').html('');

	if ($(this).find(':selected').val() != '')
	{
		$('.report-submit').showFlex();
		$('.report-description').html($(this).find(':selected').data('description'));

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

		if ($(this).find(':selected').data('multi-store-select'))
		{
			$('.option-multi-store-select').showFlex();
		}

	}

});

$(document).ready(function () {
	$('#marketing_report').trigger('change');
});