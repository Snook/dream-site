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



function deleteResultProcess ( followupID, rid )
{
		xmlHttp = getXmlHttpObject();
		if( xmlHttp == null ) {
			alert( "Sorry, a browser incompatibility error occurred while trying to save your data." );
			return;
		}
		var url = "processor?processor=admin_retentionProcessor";
		var post_str = "action=deleteResult&rfid=" + followupID + "&rid=" + rid ;
		xmlHttp.onreadystatechange = deleteResultReturnInternal;
		xmlHttp.open('POST', url, true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlHttp.setRequestHeader("Content-length", post_str.length);
		xmlHttp.setRequestHeader("Connection", "close");
		xmlHttp.send(post_str);
}


function deleteFollowupProcess ( followupID , rid)
{
		xmlHttp = getXmlHttpObject();
		if( xmlHttp == null ) {
			alert( "Sorry, a browser incompatibility error occurred while trying to save your data." );
			return;
		}
		var url = "processor?processor=admin_retentionProcessor";
		var post_str = "action=deleteFollowUp&rfid=" + followupID + "&rid=" + rid ;
		xmlHttp.onreadystatechange = deleteFollowupResultInternal;
		xmlHttp.open('POST', url, true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlHttp.setRequestHeader("Content-length", post_str.length);
		xmlHttp.setRequestHeader("Connection", "close");
		xmlHttp.send(post_str);
}



function updateFollowup ( follow_idx, report_id, action, rid, uid,  fDate, fType, followupid)
{
	addData ( follow_idx, 3,  report_id, action, rid, uid,  fType, fDate, null , null, followupid);
}


function addFollowup ( follow_idx, report_id, action, rid, uid,  fDate, fType)
{

	addData ( follow_idx, 1, report_id, action, rid, uid,  fType, fDate, null , null, null);
}

function addResults ( follow_idx, report_id,  action, rid, uid,  rDate , rComm, rfid)
{


	addData ( follow_idx, 2, report_id, action, rid, uid,  null, null, rDate , rComm, rfid);
}

function addData ( follow_idx, type, report_id,  action, rid, uid,  fType, fDate, rDate , rComm, rfid)
{


	xmlHttp = getXmlHttpObject();
	if( xmlHttp == null ) {
		alert( "Sorry, a browser incompatibility error occurred while trying to save your data." );
		return;
	}
	var url = "processor?processor=admin_retentionProcessor";

	var post_str = "";

	if (type == 1)
		post_str = "&follow_idx=" + follow_idx +"&report=" + report_id +  "&action=" + action + "&rid=" + rid + "&uid=" + uid + "&fType=" + fType + "&fDate=" + fDate;
	else if (type == 2)
		post_str = "&follow_idx=" + follow_idx + "&report=" + report_id + "&action=" + action + "&rfid=" + rfid +  "&rid=" + rid  + "&uid=" + uid + "&rDate=" + rDate+ "&rComm=" + rComm;
	else if (type == 3)
		post_str = "&follow_idx=" + follow_idx + "&report=" + report_id +  "&rid=" + rid  + "&action=" + action + "&rfid=" + rfid + "&uid=" + uid + "&fType=" + fType + "&fDate=" + fDate;



	if (action == "insert")
	{
		if (type == 1) xmlHttp.onreadystatechange = insertFollowUpDataDetails;
		else if (type == 2) xmlHttp.onreadystatechange = insertResultDataDetails;
	}
	else if (action == "update") {
		xmlHttp.onreadystatechange = updateDetails;
	}
	xmlHttp.open('POST', url, true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", post_str.length);
	xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(post_str);

}

function insertFollowUpDataDetails()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) processInsertFollowUpData(xmlHttp.responseText);
}
function insertResultDataDetails()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) processInsertReturnData(xmlHttp.responseText);
}

function updateDetails()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) updateFollowupReturn (xmlHttp.responseText);
}

function deleteFollowupResultInternal()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) deleteFollowupResult (xmlHttp.responseText);
}

function deleteResultReturnInternal()
{
	if( xmlHttp.readyState == 4 || xmlHttp.readyState == "complete" ) deleteResultReturn (xmlHttp.responseText);
}