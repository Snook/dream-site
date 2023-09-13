    var isProcessing = false;
    var org_no_show_state = false;
    var cur_timer;
    var cur_booking_id = null;
	var xmlHttp = null;

	function handleNoShowResult()
	{
		if( (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") && isProcessing ) 
		{
			clearTimeout(cur_timer);
			isProcessing = false;
			if (xmlHttp.responseText.substr(0,1) == "0")
			{
				document.getElementById('bns_' + cur_booking_id).checked = org_no_show_state;
				printString = "The server was unable to save the 'no show' flag.";
				alert(printString);				
			}
			else if (xmlHttp.responseText == "Not logged in")
			{
				document.getElementById('bns_' + cur_booking_id).checked = org_no_show_state;
				if (confirm("Session timed out, please login again."))
				{
					window.location.reload();
				}
			}
			document.getElementById("bnsp_" + cur_booking_id).style.display="none";
		}
	}

	function handleTimeout()
	{
		if (isProcessing)
		{
			isProcessing = false;
			document.getElementById('bns_' + cur_booking_id).checked = org_no_show_state;
			document.getElementById("bnsp_" + cur_booking_id).style.display="none";
			printString = "The server has not responded.";
			alert(printString);
		}

	}

	function setNoShowState(booking_id, obj)
	{
		if (isProcessing)
		{
			obj.checked = !obj.checked;
			return;
		}

		org_no_show_state = !obj.checked;
		cur_booking_id = booking_id;

		xmlHttp = getXmlHttpObject();
		if( xmlHttp == null ) 
		{
			// temporary
			alert( "The No Show function is not compatible with your browser. Please contact your store for assistance." );
			return;
		}

		var bid = "&bid=" + booking_id;
		var state = "&state=" + (obj.checked ? "yes" : "no" );
		var url = "processor?processor=admin_no_show_state" + bid + state;
		isProcessing = true;

		xmlHttp.onreadystatechange = handleNoShowResult;
		xmlHttp.open( "GET", url, true );
		xmlHttp.send( null );

		document.getElementById("bnsp_" + booking_id).style.display="inline";

		cur_timer = setTimeout("handleTimeout();", 20000);
	}