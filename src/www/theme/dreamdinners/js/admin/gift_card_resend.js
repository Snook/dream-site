function searchTypeChange(value)
{
	//	var newtype = document.getElementById('search_type').value;
	var helpString = "";
	switch (value)
	{
		case 'recipient_email':
			helpString = "Enter the full or partial email of the eGift card recipient";
			break;

		case 'billing_email':
			helpString = "Enter the full or partial email of the purchaser of the gift card";
			break;

		case 'billing_name':
			helpString = "Enter the full or partial name of the billing name of the purchaser (name on credit card)";
			break;
	}

	document.getElementById('search_help').style.display = "block";
	document.getElementById('search_help').innerHTML = helpString;
	document.getElementById('q').focus();
}

function viewGCOrder(id)
{
	window.open('/backoffice/gift-card-details?gcOrder=' + id, 'dd_help', 'toolbar=no,menubar=no,width=675,height=575');
}

function checkSearchSring(form)
{
	document.getElementById('search_error').style.display = "none";

	var searchType = document.getElementById('search_type').value;
	var searchString = document.getElementById('q').value;
	switch (searchType)
	{
		case 'recipient_email':
			if (searchString.length < 5)
			{
				document.getElementById('search_error').style.display = "block";
				document.getElementById('search_error').innerHTML = "The search string must be at least 5 characters long.";
				return false;
			}

			break;

		case 'billing_email':
			if (searchString.length < 5)
			{
				document.getElementById('search_error').style.display = "block";
				document.getElementById('search_error').innerHTML = "The search string must be at least 5 characters long.";
				;
				return false;
			}

			break;

		case 'billing_name':

			if (searchString.length < 3)
			{
				document.getElementById('search_error').style.display = "block";
				document.getElementById('search_error').innerHTML = "The search string must be at least 3 characters long.";
				;
				return false;
			}
			break;

	}
	return true;

}