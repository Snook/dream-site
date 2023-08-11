var allErrorsAreDisplayedInLine = true;

function _check_form(form)
{
	allErrorsAreDisplayedInLine = true;
	return _check_elements(form.elements);
}

function dd_insertAfter(newElement, targetElement)
{
	var parent = targetElement.parentNode;

	if (parent.lastChild == targetElement)
	{
		parent.appendChild(newElement);
	}
	else
	{
		parent.insertBefore(newElement, targetElement.nextSibling);
	}
}

function emailCheck(emailStr, allowEmpty)
{
	if (allowEmpty && (emailStr == null || emailStr == ""))
	{
		return true;
	}

	emailStr = emailStr.toLowerCase();
	var emailPat = /^(.+)@(.+)$/;
	var specialChars = "\\(\\)><@,;:\\\\\\\"\\.\\[\\]";
	var validChars = "\[^\\s" + specialChars + "\]";
	var quotedUser = "(\"[^\"]*\")";
	var ipDomainPat = /^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
	var atom = validChars + '+';
	var word = "(" + atom + "|" + quotedUser + ")";
	var userPat = new RegExp("^" + word + "(\\." + word + ")*$");
	var matchArray = emailStr.match(emailPat);
	if (matchArray == null)
	{
		if (allowEmpty)
		{
			return "The email address you entered is incorrect.";
		}
		else
		{
			return "The email address you entered is empty or incorrect.";
		}
	}
	var user = matchArray[1];
	var domain = matchArray[2];
	for (i = 0; i < user.length; i++)
	{
		if (user.charCodeAt(i) > 127)
		{
			return "The user name of the email address you entered contains invalid characters.";
		}
	}
	for (i = 0; i < domain.length; i++)
	{
		if (domain.charCodeAt(i) > 127)
		{
			return "The domain name of the email address you entered contains invalid characters.";
		}
	}
	if (user.match(userPat) == null)
	{
		return "The user name of the email address you entered seems incorrect.";
	}
	var IPArray = domain.match(ipDomainPat);
	if (IPArray != null)
	{
		for (var i = 1; i <= 4; i++)
		{
			if (IPArray[i] > 255)
			{
				return "The IP address of the email address you entered seems incorrect.";

			}
		}
		return true;
	}
	/*
	 Must contain the @ symbol
	 Must contain a character before the @ symbol
	 Must contain a dot: .
	 Must contain two characters between the @ symbol and dot
	 Must contain two characters after the dot
	 */
	var emailRegEx = /^\S{1,}@\S{1,}\.\S{2,}$/;

	if (!emailRegEx.test(emailStr))
	{
		return "The email address you entered does not appear to a valid format.";
	}

	var domArr = domain.split(".");

	if (domArr[domArr.length - 1] == "local" && domArr[domArr.length - 2] != "dreamdinners")
	{
		return "The top domain 'local' can only be used with a the 'dreamdinners' domain.";
	}

	return true;
}

//defines input as an integer
function isInteger(value)
{
	return (parseInt(value) == value);
}

function hourCheck(hourStr)
{

	if (isNaN(hourStr))
	{
		return "The hour entry is not a number.";
	}

	if (!isInteger(hourStr))
	{
		return "The hour entry is not an integer.";
	}

	if (hourStr < 0 || hourStr > 12)
	{
		return "The hour entry must be a number from 0 to 12.";
	}

	return true;
}

function minutesCheck(minutesStr)
{

	if (isNaN(minutesStr))
	{
		return "The minutes entry is not a number.";
	}

	if (!isInteger(minutesStr))
	{
		return "The minutes entry is not an integer.";
	}

	if (minutesStr < 0 || minutesStr > 59)
	{
		return "The minutes entry must be a number from 0 to 59.";
	}

	return true;
}

function numberCheck(numberStr)
{

	if (isNaN(numberStr))
	{
		return "The entry is not a number.";
	}

	return true;
}

function moneyCheck(moneyStr)
{

	if (isNaN(moneyStr))
	{
		return "The entry is not a number.";
	}

	if (moneyStr == 0)
	{
		return "The entry is zero. Please enter a non-zero amount.";
	}

	if (moneyStr < 0)
	{
		return "The entry is less than zero. Please enter a positive amount.";
	}

	return true;
}

function telephoneCheck(telephoneStr)
{
	var teleArr = telephoneStr.split("-");

	var isValid = true;
	if (teleArr.length != 3)
	{
		isValid = false;
	}

	if (isValid)
	{
		if (teleArr[0].length != 3)
		{
			isValid = false;
		}
		else if (teleArr[1].length != 3)
		{
			isValid = false;
		}
		else if (teleArr[2].length != 4)
		{
			isValid = false;
		}
	}

	if (isValid && isNaN(teleArr[0]))
	{
		isValid = false;
	}
	else if (isValid && isNaN(teleArr[1]))
	{
		isValid = false;
	}
	else if (isValid && isNaN(teleArr[2]))
	{
		isValid = false;
	}

	if (!isValid)
	{
		return "Telphone numbers must be in the format: ###-###-####";
	}

	return true;
}