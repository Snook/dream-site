function email_init()
{
	handle_email_submit();

	handle_recipient_delete();

	//tinyeditor();
}

function handle_email_submit()
{
	$('#email_form').submit(function() {

		message = '';

		// Check required elements
		if($('#sender_name').length && !$('#sender_name').val())
		{
			message += '<li>From Name</li>';
		}
		if(!$('#sender_email').val() && !$('#sender_email_dropdown').val())
		{
			message += '<li>From Email</li>';
		}
		if(!$('#recipient_list').find('.recipient').length)
		{
			message += '<li>Recipient</li>';
		}
		if(!$('#email_subject').val())
		{
			message += '<li>Subject</li>';
		}

		// put contents from tiny editor into textarea
		if($("iframe").contents().find("#tinyeditor").html())
		{
			$('#email_body').val($("iframe").contents().find("#tinyeditor").html());
		}

		if(!$('#email_body').val())
		{
			message += '<li>Message</li>';
		}

		if(message)
		{
			dd_message({ title: 'The following fields are required', message: '<ul>' + message + '</ul>' });

			return false;
		}

		// Check attachment
		if($('#email_attachment').val())
		{
			var ext = $('#email_attachment').val().split('.').pop().toLowerCase();
			if($.inArray(ext, $.phpVar.js_extensions) == -1)
			{
			    dd_message({ title: 'Invalid file extension', message: 'Only ' + $.phpVar.extensions + ' allowed.' });

			    return false;
			}
		}

		$('#email_submit').hide();
		$('#processing_message').show();
	});
}

function addRecipient(guest)
{
	// check if they already exist in the list
	if ($('.delete[data-user_id="' + $(guest).data('user_id') + '"]').length)
	{
		$('.delete[data-user_id="' + $(guest).data('user_id') + '"]').parent().parent().hide().show('pulsate');

		return;
	}

	$('#recipient_list_end').before(
		$('<div class="recipient"><span class="name" data-tooltip="' + $(guest).data('primary_email') + '">' + $(guest).data('firstname') + ' ' + $(guest).data('lastname') + ' <svg class="delete icon white icon-cancel-circle" data-user_id="' + $(guest).data('user_id') + '"><use xlink:href="#icon-cancel-circle"></use></svg></span></div>').show('pulsate')
	);

	$('#email_form').prepend(
		$('<input type="hidden" name="recipient[' + $(guest).data('user_id') + ']" id="recipient[' + $(guest).data('user_id') + ']" value="' + $(guest).data('user_id') + '" />')
	);

	data_tooltips_init();
}

function handle_recipient_delete()
{
	$('#recipient_list').on('click', '.recipient', function (e) {
		e.stopPropagation();
	});

	$('#recipient_list').on('click', '.delete', function (e) {

		e.stopPropagation();

		var user_id = $(this).data('user_id');
		var parent = $(this).parent().parent();

		$('#recipient\\[' + user_id + '\\]').remove();

		if ($.fn.qtip)
		{
			$(this).parent().qtip('destroy');
		}

		$(parent).hide('puff', function() {

			$(parent).remove();

		});

	});
}

function emailCancel()
{
	bounce($.phpVar.page_back);
}

function resetAttachment()
{
	$('#email_attachment').val('');
}

function tinyeditor()
{
	new TINY.editor.edit('editor',{
		id:'email_body',
		width:'100%',
		height:175,
		cssclass:'te',
		controlclass:'tecontrol',
		rowclass:'teheader',
		dividerclass:'tedivider',
		controls:['bold','italic','underline','strikethrough','|','subscript','superscript','|',
				  'orderedlist','unorderedlist','|','outdent','indent','|','leftalign',
				  'centeralign','rightalign','blockjustify','|','unformat','|','undo','redo','n',
				  'font','size','style','|','image','hr','link','unlink','|','cut','copy','paste','print'],
		footer:false,
		fonts:['Verdana','Arial','Georgia','Trebuchet MS'],
		xhtml:true,
		cssfile: PATH.css + '/admin/tinyeditor.css',
		bodyid:'tinyeditor',
		footerclass:'tefooter',
		toggle:{text:'show source',activetext:'show wysiwyg',cssclass:'toggle'},
		resize:{cssclass:'resize'}
	});
}