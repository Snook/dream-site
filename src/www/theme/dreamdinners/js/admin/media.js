function media_init()
{
	// How to load content
	title_click_handler();

	// Handle direct linking to content
	handle_preselection();

	// Init link tooltips
	data_tooltips_init();
}

function title_click_handler()
{
	// Handle YouTube Videos
	$("[data-youtube_id]").each(function(e) {

		create_title_link(this);

		$(this).on('click', function () {

			handle_selection(this);

			var src = 'https://www.youtube.com/embed/' + $(this).data('youtube_id') + '?rel=0&amp;vq=hd1080&amp;autohide=1&amp;showinfo=0&amp;modestbranding=0';

			if ($(this).data('autoplay') == true)
			{
				src += '&amp;autoplay=1';
			}

			historyPush({ url: '?page=' + getQueryVariable('page') + '&youtube=' + $(this).data('youtube_id') });

			$('#media_container-div').html('<iframe id="youtube-player" name="youtube-player" class="youtube-player" src="' + src + '" type="text/html" width="640" height="390" frameborder="0" allowFullScreen></iframe>');

		});

	});

	// Handle SoundCloud Audio
	$("[data-soundcloud_id]").each(function(e) {

		create_title_link(this);

		$(this).on('click', function () {

			handle_selection(this);

			var src = 'https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F' + $(this).data('soundcloud_id') + '&amp;show_artwork=false&amp;sharing=false&amp;color=AFB757&amp;theme_color=E5E0D8&amp;show_comments=false&amp;show_user=false';

			if ($(this).data('autoplay') == true)
			{
				src += '&amp;auto_play=true';
			}

			historyPush({ url: '?page=' + getQueryVariable('page') + '&soundcloud=' + $(this).data('soundcloud_id') });

			$('#media_container-div').html('<iframe width="640" height="166" scrolling="no" frameborder="no" src="' + src + '"></iframe>');

		});

	});

}

function create_title_link(obj)
{
	title = 'Untitled';

	if ($(obj).data('title'))
	{
		title = $(obj).data('title');
	}

	description = '';

	if ($(obj).data('description'))
	{
		description =  '<hr />' + $(obj).data('description');
	}

	$(obj).append('<div class="title">' + title + '</div>').attr('data-tooltip', title);

	if ($(obj).data('date'))
	{
		$(obj).append('<div class="date">' + $(obj).data('date') + '</div>');
	}

	$(obj).append('<div class="clear"></div>');
}

function handle_selection(element)
{
	// clear current highlight
	$(".selected_media").removeClass('selected_media');
	$('#description').html('');

	// highlight this selection
	$(element).addClass('selected_media');

	// show description
	if ($(element).data('description'))
	{
		$('#description').html($(element).data('description'));

		if ($('#video_list').height() != '290')
		{
			// cancel hide timer
			$.doTimeout( 'hide_description_timer');

			$.doTimeout( 'show_description_timer', 1000, function() {
				$('#video_list').animate({height:'290px'}, 800);
				$('#video_description').slideDown(800);
			});
		}
	}
	else
	{
		if ($('#video_list').height() != '410')
		{
			// cancel show timer
			$.doTimeout( 'show_description_timer');

			$('#description').html('No description.');

			$.doTimeout( 'hide_description_timer', 2000, function() {
				$('#video_list').animate({height:'410px'}, 800);
				$('#video_description').slideUp(800);
			});
		}
	}
}

function handle_preselection()
{
	if (getQueryVariable('youtube'))
	{
		$(("[data-youtube_id=" + getQueryVariable('youtube') + "]")).trigger('click');
	}
	else if (getQueryVariable('soundcloud'))
	{
		$(("[data-soundcloud_id=" + getQueryVariable('soundcloud') + "]")).trigger('click');
	}
	else
	{
		$('.selected_media').trigger('click');
	}
}