function admin_coupons_init()
{
	calculatePage(document.getElementById('mid'));
}

function calculatePage(obj)
{
	var theForm = document.getElementById('coupon_optout_form');

	// first show hide controls if master control is clicked
	if (obj && obj.id.substr(0, 3) == "mid")
	{
		var tbl = document.getElementById('itemsTbl');
		var rows = tbl.getElementsByTagName('tr');
		if (obj.checked)
		{
			for (var row=0; row<rows.length;row++)
			{
				var cels = rows[row].getElementsByTagName('td');
				cels[0].style.display="block";
			}
		}
		else
		{
			for (var row=0; row<rows.length;row++)
			{
				var cels = rows[row].getElementsByTagName('td');
				cels[0].style.display="none";
			}
		}
	}

	// first disabled/enable codes if a program is clicked
	if (obj && obj.id.substr(0, 3) == "pid")
	{
		var prog_id = obj.id.substr(4);

		for ( i = 0; i < theForm.elements.length; i++ )
		{
			if (theForm.elements[i].type == 'checkbox')
			{
				if (theForm.elements[i].getAttribute('data-program_id') == prog_id)
				{
					theCID = theForm.elements[i].id.substr(4);

					if (obj.checked)
					{
						theForm.elements[i].disabled = false;
						document.getElementById('row_' + prog_id + '_' + theCID ).style.color = "#000000";
					}
					else
					{
						document.getElementById('row_' + prog_id + '_' + theCID ).style.color = "#808080";
						theForm.elements[i].disabled = true;
					}
				}
			}
		}
	}

	var str_optouts = "";
	var str_optins = "";

	for ( i = 0; i < theForm.elements.length; i++ )
	{
		if (theForm.elements[i].type == 'checkbox')
		{
			currentState = theForm.elements[i].getAttribute('data-orgval');
			if (currentState == 'CHECKED' && theForm.elements[i].checked == false)
			{
				str_optouts += theForm.elements[i].id + "|";
			}
			else if (currentState == 'UNCHECKED' && theForm.elements[i].checked == true)
			{
				str_optins += theForm.elements[i].id + "|";
			}
		}
	}

	document.getElementById('optouts').value = str_optouts;
	document.getElementById('optins').value = str_optins;

	if (str_optouts != "" || str_optins != "")
	{
		document.getElementById('saved_message').style.display = "block";
		document.getElementById('saved_message_2').style.display = "block";
	}
	else
	{
		document.getElementById('saved_message').style.display = "none";
		document.getElementById('saved_message_2').style.display = "none";
	}
}

function confirm_and_check_form(form)
{
	optouts = document.getElementById('optouts').value;
	optins = document.getElementById('optins').value;
	document.getElementById('action').value = "finalize";

	if (optouts == "" && optins == "") {
		return false;
	}

	return true;
}

function storeChange(obj)
{
	document.getElementById('action').value = "filterChange";
	document.getElementById('coupon_optout_form').submit();
}

function resetPage()
{
	bounce(window.location.href);
}