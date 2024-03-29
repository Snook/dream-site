$(document).on('change', '#marketing_report', function (e) {

	$('.report-submit, .report-option').hideFlex();

	if ($(this).find(':selected').val() != '')
	{
		$('.report-submit').showFlex();

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

	}

});