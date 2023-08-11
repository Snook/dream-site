	function getXmlHttpObject()
	{
		var objXMLHttp = null;

		try
		{
			// Opera 8.0+, Firefox, Safari
			objXMLHttp = new XMLHttpRequest();
		}
		catch (e)
		{
			// Internet Explorer Browsers
			try
			{
				objXMLHttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e)
			{
				try
				{
					objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e)
				{
					// Something went wrong
				}
			}
		}

		return objXMLHttp;
	}