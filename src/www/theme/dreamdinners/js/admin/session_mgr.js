function hiliteIcon(id, index)
{
	img = document.getElementById(id);

	switch(index)
	{
		case 1:
			img.src = PATH.image_admin + '/calendar/session_pub_hl.png';
			return;
		case 2:
			img.src = PATH.image_admin + '/calendar/session_saved_hl.png';
			return;
		case 3:
			img.src = PATH.image_admin + '/calendar/session_new_hl.png';
			return;
		case 4:
			img.src = PATH.image_admin + '/calendar/session_closed_hl.png';
			return;
	}
}

function unHiliteIcon(id, index)
{
	img = document.getElementById(id);

	switch(index)
	{
		case 1:
			img.src = PATH.image_admin + '/calendar/session_pub.png';
			return;
		case 2:
			img.src = PATH.image_admin + '/calendar/session_saved.png';
			return;
		case 3:
			img.src = PATH.image_admin + '/calendar/session_new.png';
			return;
		case 4:
			img.src = PATH.image_admin + '/calendar/session_closed.png';
			return;
	}
}