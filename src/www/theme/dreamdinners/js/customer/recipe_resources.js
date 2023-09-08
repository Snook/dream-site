$(function () {

	// Handle YouTube Videos
	$('#ci_search_main, #ci_search').on('keyup', function(e) {

		if ($(this).val() != strip_tags($(this).val()))
		{
			$(this).val(strip_tags($(this).val()));
		}

		if(e.which == 13)
		{
			$('#ci_search_main_submit, #ci_search_submit').trigger('click');
		}

	});

	$('#ci_search_submit').on('click', function (e) {

		var q =	strip_tags($('#ci_search').val());

		var vid_only = '';
		if ($('#ci_search_vids').is(':checked'))
		{
			vid_only = '&video=true';
		}

		bounce('?page=recipe_resources&q=' + q + vid_only);

	});

	$('#ci_search_main_submit').on('click', function (e) {

		var q =	strip_tags($('#ci_search_main').val());

		var vid_only = '';
		if ($('#ci_search_vids').is(':checked'))
		{
			vid_only = '&video=true';
		}

		bounce('?page=recipe_resources&q=' + q + vid_only);

	});

});