function getXmlHttpObject()
{
	var objXMLHttp = null;
	if( window.XMLHttpRequest ) {
		objXMLHttp = new XMLHttpRequest();
	} else if( window.ActiveXObject ) {
		objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	return objXMLHttp;
}

var g_lastSearch = '';
var g_lastSearchArea = 0;

function onZipCodeFieldFocus(obj)
{
	if (obj.value == "Enter Zip Code")
		obj.value = "";
}

function fetchZipcodes( szZip )
{
	if(szZip && szZip.length > 4) {
		
		
		document.getElementById("selTarget").innerHTML = "Searching...";
		   

	   xmlHttp = getXmlHttpObject();
	   if( xmlHttp == null ) {
		// temporary
		alert( "Browser does not support XML-HTTP-Request" );
		return;
	   }

	   var url = "ajax/locations/fetchBundleOfferZipcodes.php";
	   url=url + "?szZip=" + szZip;
	   url=url + "&sid=" + Math.random();
	   
	   xmlHttp.onreadystatechange = zipcodeStateChanged;
	   
	   
	   xmlHttp.open( "GET", url, true );
	   xmlHttp.send( null );

	} 
	else
	{
		document.getElementById("selTarget").innerHTML = "<span style='color:red'>Invalid zip code.</span>";
	}
}

function zipcodeStateChanged()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) {
		document.getElementById("selTarget").innerHTML = xmlHttp.responseText;
	}
}

function fetchStoresForState( state_id )
{
		   
	  document.getElementById("selTarget").innerHTML = "getting Stores...";

	   xmlHttp = getXmlHttpObject();
	   if( xmlHttp == null ) {
		// temporary
		alert( "Browser does not support XML-HTTP-Request" );
		return;
	   }

	   var url = "ajax/locations/fetchBundleOfferZipcodes.php";
	   url=url + "?state=" + state_id;
	   
	   xmlHttp.onreadystatechange = stateBrowseStateChanged;
	   
	   
	   xmlHttp.open( "GET", url, true );
	   xmlHttp.send( null );

}

function stateBrowseStateChanged()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) {
		document.getElementById("selTarget").innerHTML = xmlHttp.responseText;
	}
}


