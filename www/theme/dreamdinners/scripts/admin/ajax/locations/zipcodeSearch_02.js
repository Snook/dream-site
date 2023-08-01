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

function fetchZipcodes( szZip )
{
	if( (szZip.length > 4) && (szZip != g_lastSearch) ) {
		g_lastSearch = szZip;
		document.getElementById("selTarget").innerHTML = "Searching...";

		//if( (szZip.length == 0) ) {
		//   document.getElementById("selTarget").innerHTML = "";
		//   return;
		//}

	   xmlHttp = getXmlHttpObject();
	   if( xmlHttp == null ) {
		// temporary
		alert( "Browser does not support XML-HTTP-Request" );
		return;
	   }

	   var url = "ajax/locations/fetchZipcodes.php";
	   url=url + "?szZip=" + szZip;
	   url=url + "&sid=" + Math.random();
	   xmlHttp.onreadystatechange = zipcodeStateChanged;
	   xmlHttp.open( "GET", url, true );
	   xmlHttp.send( null );

	   //document.frmMain.txt1.select();
	} //else {
		//document.getElementById("selTarget").innerHTML = "(none)";
	//}
}

function zipcodeStateChanged()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) {
		document.getElementById("selTarget").innerHTML = xmlHttp.responseText;
	}
}

