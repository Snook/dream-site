var nda_read_all = false;
var nda_checked = false;

function access_agreement_init()
{
	checkbox_handler();
	terms_read_all();
	can_submit_nda();
}

function checkbox_handler()
{
	$('#agree_to_nda').on('click', function () {

		 can_submit_nda();

	});

}

function terms_read_all()
{
	$('#terms').scroll(function ()
	{
		var scrolltop = $(this).scrollTop();
		var height = $(this).height();
		var scrollheight_height = ($(this)[0].scrollHeight - $(this).height()) - 100;

	    if (scrolltop > scrollheight_height)
	    {
	    	nda_read_all = true;
	    	$('#read_all_notice').hide();
	    }

        can_submit_nda();
	});
}

function can_submit_nda()
{
	if($("#agree_to_nda").is(':checked'))
	{
		nda_checked = true;
	}
	else
	{
		nda_checked = false;
	}

	if (nda_read_all && nda_checked)
	{
		$('#agree_to_nda_submit').removeAttr('disabled');
	}
	else
	{
		$('#agree_to_nda_submit').prop("disabled", true);
	}
}