<?php
$url = "?page=admin_reports_user_retention";
?>

<?php $this->assign('page_title','Inactive Guest Status Reports'); ?>
<?php $this->assign('topnav','reports'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/ajax/retention/retention.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-retention.css'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<script src=" <?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>

	<script type="text/javascript">

		var dtCh= "/";
		var minYear=1900;
		var maxYear=2100;

		function isIntegerValue(s)
		{
			var i;
			for (i = 0; i < s.length; i++){
				var c = s.charAt(i);
				if (((c < "0") || (c > "9"))) return false;
			}
			return true;
		}

		function stripChars(s, bag){
			var i;
			var returnString = "";
			for (i = 0; i < s.length; i++){
				var c = s.charAt(i);
				if (bag.indexOf(c) == -1) returnString += c;
			}
			return returnString;
		}

		function daysInFebruary (year){
			// February has 29 days in any year evenly divisible by four,
			// EXCEPT for centurial years which are not also divisible by 400.
			return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
		}
		function DaysArray(n) {
			for (var i = 1; i <= n; i++) {
				this[i] = 31
				if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
				if (i==2) {this[i] = 29}
			}
			return this
		}

		/* VALIDATE DATE FUNCTION */
		function isDate(dtStr){
			var daysInMonth = DaysArray(12)
			var pos1=dtStr.indexOf(dtCh)
			var pos2=dtStr.indexOf(dtCh,pos1+1)
			var strMonth=dtStr.substring(0,pos1)
			var strDay=dtStr.substring(pos1+1,pos2)
			var strYear=dtStr.substring(pos2+1)
			strYr=strYear
			if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
			if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
			for (var i = 1; i <= 3; i++) {
				if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
			}
			month=parseInt(strMonth)
			day=parseInt(strDay)
			year=parseInt(strYr)
			if (pos1==-1 || pos2==-1){
				alert("The date format should be : mm/dd/yyyy")
				return false
			}
			if (strMonth.length<1 || month<1 || month>12){
				alert("Please enter a valid month")
				return false
			}
			if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
				alert("Please enter a valid day")
				return false
			}
			if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
				alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
				return false
			}
			if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isIntegerValue(stripChars(dtStr, dtCh))==false){
				alert("Please enter a valid date")
				return false
			}
			return true
		}


		/* FIND LOCATION SO WE CAN DISPLAY THE CALENDAR DIV */
		function getRealPos(el,which)
		{
			iPos = 0;
			while (el!=null) {
				iPos += el["offset" + which];
				el = el.offsetParent;
			}
			return iPos;
		}

		/* WIDGET METHOD FOR CUSTOMIZED CALENDAR OBJECT */
		var curTextWidget = null;
		function saveCalendar()
		{
			var Month = calendarwidget_Object.getMonthList().value;

			var dayvar = calendarwidget_Object.getDayList().selectedIndex;
			dayvar++;


			var Year = calendarwidget_Object.getYearField().value;
			Month++;

			if (dayvar < 10)
				dayvar = '0' + dayvar;

			if (Month < 10)
				Month = '0' + Month;

			mydate = Month + "/" + dayvar + "/" + Year;
			document.getElementById(curTextWidget).value = mydate;
			document.getElementById('calendar').style.display="none";

		}

		/* SHOW THE ACTUAL CALENDAR IN A DIV */
		function showCalendar(widgetid, element, which)
		{
			curTextWidget = widgetid;
			var obj = document.getElementById('calendar');
			obj.style.left = (getRealPos(element,"Left")-90) + 'px'; // set the pop-up's left
			obj.style.top = (getRealPos(element,"Top")-59) + 'px'; // set the pop-up's top
			obj.style.zIndex = 2;
			obj.style.display="block";
		}


		/* THIS METHOD WILL BE CALLED AFTER AJAX RETURNS FROM SAVING A NEW FOLLOWUP */
		function processInsertFollowUpData( resultVar )
		{

			var rArray = resultVar.split('<node>');
			var arraysize = rArray.length;

			if (arraysize == 2) {
				//var substr = rArray[1].substring(rArray[1].lastIndexOf("ERROR:"), rArray[1].length);
				alert(rArray[1]);
			}
			else {
				var follow_idx = rArray[1];
				var fid = rArray[2];
				var reportid = rArray[3];
				var retentionid = rArray[4];
				var userid = rArray[5];
				if (follow_idx == 1)
				{
					document.getElementById('section_follow_up_001_' + retentionid + '_follow_up_id').value = fid;
					document.getElementById('section_follow_up_result_001_' + retentionid + '_follow_up_id').value = fid;
					var datefieldvalue = document.getElementById('section_follow_up_001' + '_date_'+retentionid).value;
					document.getElementById('section_follow_up_001' + '_set_'+retentionid).style.visibility = "hidden";
					document.getElementById('section_follow_up_001' + '_title_date_td_'+retentionid).style.visibility = "hidden";
					var type = document.getElementById('section_follow_up_001' + '_select_' + retentionid).value;
					var dropdown = getIndexType (type);
					document.getElementById('section_follow_up_001' + '_select_td_' + retentionid).innerHTML = dropdown;
					document.getElementById('section_follow_up_001' + '_set_date_td_' + retentionid).innerHTML = datefieldvalue;
					var inputvars = '<input id="section_follow_up_001_delete_' + retentionid + '" onclick="deleteFollowup(\'section_follow_up_001\', ' + retentionid + ', ' + userid + ', \'section_follow_up_result_001\');" name="delete" value="Delete" type="button">';
					inputvars += '<input id="section_follow_up_001_reset_' + retentionid + '" onclick="editSection(\'section_follow_up_001\', ' + retentionid + ');" name="reset" value="Edit" type="button">';


					document.getElementById('section_follow_up_001' + '_buttons_' + retentionid).innerHTML = inputvars;

					var str = "<b>Has Follow-Up</b><br/>";
					//	str += "<img id='" + retentionid + '_check_image" src=" <?php echo ADMIN_IMAGES_PATH?>/check_yes.gif" />'";

					str += '<img id="' + retentionid + '_check_image" src=" <?php echo ADMIN_IMAGES_PATH?>/check_yes.gif" />';


					document.getElementById('has_follow_up_' + retentionid).innerHTML = str;


				}
			}

		}

		/* CALLED WHEN CLICKING THE EDIT BUTTON ON EITHER THE FOLLOWUP OR RESULT SECTIONS */
		function editSection(sectionId, id)
		{


			document.getElementById(sectionId + '_title_date_td_'+id).style.visibility = "visible";
			document.getElementById(sectionId + '_set_'+id).style.visibility = 'visible';

			if (sectionId == 'section_follow_up_001')
			{


				var selectedvalue = document.getElementById(sectionId + '_select_td_' + id).innerHTML;
				var idx = getIndexValue (selectedvalue);

				var selecte = createSelectHTML ( sectionId + '_select_' + id, idx );
				document.getElementById(sectionId + '_select_td_' + id).innerHTML = selecte;

			}
			else if (sectionId == 'section_follow_up_result_001')
			{
				var selectedvalue = document.getElementById(sectionId + '_notes_td_' + id).innerHTML;
				var textfield = '<textarea cols="25" rows="2" id="' + sectionId + '_text_' + id + '">' + selectedvalue + '</textarea>';
				document.getElementById(sectionId + '_notes_td_' + id).innerHTML = textfield;

			}

			var date = document.getElementById(sectionId + '_set_date_td_' + id).innerHTML;
			var textfield = '<input value="' + date + '" id="' + sectionId + '_date_' + id + '" size="10" maxlength="10" type="text">';
			document.getElementById(sectionId + '_set_date_td_' + id).innerHTML = textfield;


			var userid = document.getElementById(sectionId + "_" + id + '_user_id').value;
			var savebutton = '<input id="' + sectionId + '_save_' + id + '" onclick="saveSection(\'' + sectionId + '\', ' + id + ', ' + userid + ');" name="Save" value="Save" type="button">';
			document.getElementById(sectionId + '_buttons_' + id).innerHTML = savebutton;

			document.getElementById(sectionId + '_title_date_td_'+id).style.color = "#BFBFBF"


		}

		function deleteResult ( sectionID, retentionID )
		{

			var answer = confirm ("Are you sure you want to delete this Result?")
			if (answer)
			{


				var followupid = document.getElementById('section_follow_up_001' + "_" + retentionID + '_follow_up_id').value; // CHANGE TO GENERICE IF NEED TO HANDLE MORE THAN 1 FOLLOWUP

				deleteResultProcess ( followupid, retentionID );




			}
		}


		/* CALLED AFTER GOING TO SERVER -- AJAX */
		function deleteFollowup ( sectionID, retentionID , uid, siblingField)
		{
			var hasResult = document.getElementById(siblingField + "_" + retentionID + '_result_id').value;

			var answer = null;
			if (hasResult == 1)
				answer = confirm ("Are you sure you want to delete this Follow-Up and the corresponding Result?")
			else
				answer = confirm ("Are you sure you want to delete this Follow-Up?")

			if (answer)
			{
				var followupid = document.getElementById(sectionID + "_" + retentionID + '_follow_up_id').value;

				deleteFollowupProcess ( followupid , retentionID);




			}
		}

		function deleteResultReturn (resultVar)
		{
			// TODO do we need this for anything
			var rArray = resultVar.split('<node>');
			var arraysize = rArray.length;

			if (arraysize == 2) {
				//var substr = rArray[1].substring(rArray[1].lastIndexOf("ERROR:"), rArray[1].length);
				alert(rArray[1]);
			}
			else {
				var retentionID = rArray[1];
				var fid = rArray[2];

				var sectionID = 'section_follow_up_result_001';


				var userid = document.getElementById('section_follow_up_001' + "_" + retentionID + '_user_id').value;

				var textfield = '<textarea cols="25" rows="2" id="' + sectionID + '_text_' + retentionID + '"></textarea>';

				document.getElementById(sectionID + '_notes_td_' + retentionID).innerHTML = textfield;

				var textfield2 = '<input value="" id="' + sectionID + '_date_' + retentionID + '" size="10" maxlength="10" type="text">';
				document.getElementById(sectionID + '_set_date_td_' + retentionID).innerHTML = textfield2;

				document.getElementById(sectionID + '_title_date_td_'+retentionID).style.visibility = "visible";
				document.getElementById(sectionID + '_set_'+retentionID).style.visibility = 'visible';

				var savebutton = '<input id="' + sectionID + '_save_' + retentionID + '" onclick="saveSection(\'' + sectionID + '\', ' + retentionID + ', ' + userid + ');" name="Save" value="Save" type="button">';
				document.getElementById(sectionID + '_buttons_' + retentionID).innerHTML = savebutton;
				document.getElementById(sectionID + "_" + retentionID + '_result_id').value = 0;

			}
		}




		function deleteFollowupResult ( resultVar )
		{
			// TODO do we need this for anything
			var rArray = resultVar.split('<node>');
			var arraysize = rArray.length;

			if (arraysize == 2) {
				//var substr = rArray[1].substring(rArray[1].lastIndexOf("ERROR:"), rArray[1].length);
				alert(rArray[1]);
			}
			else {
				var sectionID = 'section_follow_up_001';
				var siblingField = 'section_follow_up_result_001';

				var retentionID = rArray[1];
				var fid = rArray[2];



				var userid = document.getElementById(sectionID + "_" + retentionID + '_user_id').value;
				document.getElementById(sectionID + "_" + retentionID + '_follow_up_id').value = 0;
				document.getElementById('section_follow_up_001' + '_title_date_td_'+retentionID).style.visibility = "visible";
				document.getElementById(sectionID + '_set_'+retentionID).style.visibility = 'visible';
				var textfield = '<input value="" id="' + sectionID + '_date_' + retentionID + '" size="10" maxlength="10" type="text">';
				document.getElementById(sectionID + '_set_date_td_' + retentionID).innerHTML = textfield;
				var savebutton = '<input id="' + sectionID + '_save_' + retentionID + '" onclick="saveSection(\'' + sectionID + '\', ' + retentionID + ', ' + userid + ');" name="Save" value="Save" type="button">';
				document.getElementById(sectionID + '_buttons_' + retentionID).innerHTML = savebutton;
				var selecte = createSelectHTML ( sectionID + '_select_' + retentionID, null );
				document.getElementById(sectionID + '_select_td_' + retentionID).innerHTML = selecte;


				var str = "<b>Has Follow-Up</b><br/>";
				//str += "<img id='" + retentionID + '_check_image" src=" <?php echo ADMIN_IMAGES_PATH?>/check_no.gif" />';
				str += '<img id="' + retentionID + '_check_image" src=" <?php echo ADMIN_IMAGES_PATH?>/check_no.gif" />';


				document.getElementById('has_follow_up_' + retentionID).innerHTML = str;

				if (document.getElementById(siblingField + '_notes_td_' + retentionID))
				{
					var textfield = '<textarea cols="25" rows="2" id="' + siblingField + '_text_' + retentionID + '"></textarea>';
					document.getElementById(siblingField + '_notes_td_' + retentionID).innerHTML = textfield;
					var textfield = '<input value="" id="' + siblingField + '_date_' + retentionID + '" size="10" maxlength="10" type="text">';
					document.getElementById(siblingField + '_set_date_td_' + retentionID).innerHTML = textfield;
					document.getElementById(siblingField + '_title_date_td_'+retentionID).style.visibility = "visible";
					document.getElementById(siblingField + '_set_'+retentionID).style.visibility = 'visible';
					var savebutton = '<input id="' + siblingField + '_save_' + retentionID + '" onclick="saveSection(\'' + siblingField + '\', ' + retentionID + ', ' + userid + ');" name="Save" value="Save" type="button">';
					document.getElementById(siblingField + '_buttons_' + retentionID).innerHTML = savebutton;
					document.getElementById(siblingField + "_" + retentionID + '_result_id').value = 0;
					document.getElementById(siblingField + "_" + retentionID + '_follow_up_id').value = 0;
				}
			}

		}



		function hidediv(divname) {
			if (document.getElementById) { // DOM3 = IE5, NS6
				//document.getElementById(divname).style.visibility = 'hidden';
				document.getElementById(divname).style.display = 'none';
			}

		}

		function showdiv(divname) {
			if (document.getElementById) { // DOM3 = IE5, NS6
				//document.getElementById(divname).style.visibility = 'visible';
				document.getElementById(divname).style.display = 'block';
			}

		}




		function processInsertReturnData ( resultVar)
		{


			var rArray = resultVar.split('<node>');
			var arraysize = rArray.length;

			if (arraysize == 2) {
				//var substr = rArray[1].substring(rArray[1].lastIndexOf("ERROR:"), rArray[1].length);
				alert(rArray[1]);
			}
			else {
				var follow_idx = rArray[1];
				var fid = rArray[2];
				var reportid = rArray[3];
				var retentionid = rArray[4];
				var userid = rArray[5];
				var sectionId = "section_follow_up_result_001";
				if (follow_idx == 2) sectionId = "section_follow_up_result_002";
				var userid = document.getElementById(sectionId + "_" + retentionid + '_user_id').value;
				var inputvars = '<input id="' + sectionId + '_delete_' + retentionid + '" onclick="deleteResult(\'section_follow_up_result_001\', ' + retentionid + ');" name="delete" value="Delete" type="button">';
				inputvars += '<input id="' + sectionId + '_reset_' + retentionid + '" onclick="editSection(\'section_follow_up_result_001\', ' + retentionid + ');" name="reset" value="Edit" type="button">';
				document.getElementById(sectionId+ '_buttons_' + retentionid).innerHTML = inputvars;
				var datefieldvalue = document.getElementById(sectionId + '_date_'+retentionid).value;
				document.getElementById(sectionId + '_set_date_td_' + retentionid).innerHTML = datefieldvalue;
				document.getElementById(sectionId + '_set_'+retentionid).style.visibility = "hidden";
				document.getElementById(sectionId + '_title_date_td_'+retentionid).style.visibility = "hidden";
				var selectedvalue = document.getElementById(sectionId + '_text_' + retentionid).value;
				document.getElementById(sectionId + '_notes_td_' + retentionid).innerHTML = selectedvalue;
				document.getElementById(sectionId + "_" + retentionid + '_result_id').value = 1;
			}
		}

		// follow up return method
		function updateFollowupReturn ( resultVar )
		{
			var rArray = resultVar.split('<node>');
			var arraysize = rArray.length;

			if (arraysize == 2) {
				//var substr = rArray[1].substring(rArray[1].lastIndexOf("ERROR:"), rArray[1].length);
				alert(rArray[1]);
			}
			else {

				var follow_idx = rArray[1];
				var fid = rArray[2];
				var id = rArray[3];
				var isFollowup = rArray[4];
				var sectionId = "section_follow_up_001";
				if (follow_idx == 2) sectionId = "section_follow_up_002";

				if (isFollowup == 1) {
					var userid = document.getElementById(sectionId + "_" + id + '_user_id').value;
					var inputvars = '<input id="' + sectionId + '_delete_' + id + '" onclick="deleteFollowup(\'section_follow_up_001\', ' + id + ', ' + userid + ', \'section_follow_up_result_001\');" name="delete" value="Delete" type="button">';
					inputvars += '<input id="' + sectionId + '_reset_' + id + '" onclick="editSection(\'section_follow_up_001\', ' + id + ');" name="reset" value="Edit" type="button">';
					document.getElementById(sectionId+ '_buttons_' + id).innerHTML = inputvars;
					var type = document.getElementById(sectionId + '_select_' + id).value;
					var dropdown = getIndexType (type);
					document.getElementById(sectionId + '_select_td_' + id).innerHTML = dropdown;
					var datefieldvalue = document.getElementById(sectionId + '_date_'+id).value;
					document.getElementById(sectionId + '_set_date_td_' + id).innerHTML = datefieldvalue;
					document.getElementById(sectionId + '_set_'+id).style.visibility = "hidden";

				}
			}


		}

		<?php if ( isset($this->follow_up_choices) ) { ?>

		function createSelectHTML ( id, selectedID )
		{
			var elements = [' <?php echo implode("','", $this->follow_up_choices);?>'];
			var indexes = [' <?php $arr = array_keys($this->follow_up_choices); echo implode("','", $arr);?>'];

			var select = "<select id='" + id + "'>";
			select += '<option value="0">Select a Follow-up Type</option>';
			var selectedvalue = "";

			for (var i = 0; i< indexes.length; i++) {
				if (selectedID != null && selectedID == indexes[i]) selectedvalue = "SELECTED";
				select += '<option ' + selectedvalue + ' value="' + indexes[i] + '">' + elements[i] + ' </option>';
				selectedvalue = "";
			}
			select += '</select>';
			return select;

		}

		function getIndexType (varindex)
		{

			var id = 0;
			var elements = [' <?php echo implode("','", $this->follow_up_choices);?>'];
			var indexes = [' <?php $arr = array_keys($this->follow_up_choices); echo implode("','", $arr);?>'];
			for (var i = 0; i< indexes.length; i++) {
				if (varindex == indexes[i])
					id = elements[i];
			}
			return id;

		}



		function getIndexValue (type)
		{

			var typeo = '';
			var elements = [' <?php echo implode("','", $this->follow_up_choices);?>'];
			var indexes = [' <?php $arr = array_keys($this->follow_up_choices); echo implode("','", $arr);?>'];
			for (var i = 0; i< elements.length; i++) {
				if (type == elements[i])
					typeo = indexes[i];
			}
			return typeo;

		}

		<?php } ?>

		function saveSection (sectionId, id, uid)
		{
			var followupid = document.getElementById(sectionId + '_' + id + '_follow_up_id').value;
			var resultid = 0;
			if (document.getElementById(sectionId + '_' + id + '_result_id'))
				resultid = document.getElementById(sectionId + '_' + id + '_result_id').value;

			if (sectionId == 'section_follow_up_001' || sectionId == 'section_follow_up_002' )
			{
				var sectionIDv = 1;

				if (sectionId == 'section_follow_up_002') sectionIDv = 2;

				if (document.getElementById(sectionId + '_select_' + id).selectedIndex == 0 || document.getElementById(sectionId + '_date_' + id).value == '')
					alert("Please enter and save a valid date and action for this Follow-up!");
				else
				{
					var fdate = document.getElementById(sectionId + '_date_'+id).value;


					var isDatevalue = isDate(fdate);


					if (isDatevalue == true) {

						var type = document.getElementById(sectionId + '_select_' + id).value;

						// function updateFollowup ( follow_idx, report_id, action, rid, uid, fDate, fType, followupid)
						// function addFollowup ( follow_idx, report_id, action, rid, uid, fDate, fType)

						if (followupid > 0)
							updateFollowup ( sectionIDv, <?php echo $this->step ?>, "update", id, uid, fdate, type, followupid);

						else
							addFollowup ( sectionIDv, <?php echo $this->step ?>, "insert", id, uid, fdate, type);

					}

				}
			}
			else {

				var comment = document.getElementById(sectionId + '_text_'+id).value;
				var date = document.getElementById(sectionId + '_date_'+id).value;

				var isDatevalue = isDate(date);
				if (isDatevalue == true) {

					if (comment == "" || date == "") {
						alert("Please enter and save a date and any notes for this Follow-Up Result.");
						return;
					}
					if (sectionId == 'section_follow_up_result_001') {

						var followupid = document.getElementById('section_follow_up_result_001_' + id + '_follow_up_id').value;

						if (followupid == 0) {
							alert ("Sorry, please enter a Follow-up first.");
						}
						else
						{
							document.getElementById(sectionId + '_save_' + id).disabled = true;
							addResults ( 1, <?php echo $this->step ?>, "insert", id, uid, date , comment, followupid);
						}
					}
				}
			}
		}

		var contractsymbol=' <?php echo ADMIN_IMAGES_PATH?>/icon/bullet_toggle_minus.png' ;//Path to image to represent contract state.
		var expandsymbol=' <?php echo ADMIN_IMAGES_PATH?>/icon/bullet_toggle_plus.png' ;//Path to image to represent expand state.

		function expandcontent (curobj, id)
		{

			if (document.getElementById('master_' + id).style.display == "" || document.getElementById('master_' + id).style.display == "none")
				document.getElementById('master_' + id).style.display="block";
			else
				document.getElementById('master_' + id).style.display="";

			//	document.getElementById('master_' + id).style.display=(document.getElementById('master_' + id).style.display!="")? "" : "block";
			curobj.src=(document.getElementById('master_' + id).style.display=="")? expandsymbol : contractsymbol;
		}

		var masterminus =' <?php echo ADMIN_IMAGES_PATH?>/oe_minus.gif' ;//Path to image to represent contract state.
		var masterplus =' <?php echo ADMIN_IMAGES_PATH?>/oe_plus.gif' ;//Path to image to represent expand state.

		function openAll (curobj, records)
		{

			var nameobj = curobj.id;

			var arr = document.getElementsByTagName('div');
			for(var i=0; i<arr.length;i++)
			{
				var name = arr[i].id;
				var subname = name.substring(0, 7);
				if (subname == "master_")
				{

					if (nameobj=='open')
						showdiv(name);
					else
						hidediv(name);

				}
			}




			var arr = document.getElementsByTagName('img');
			for(var i=0; i<arr.length;i++)
			{
				var name = arr[i].id;
				var subname = name.substring(0, 7);
				if (subname == "imgpls_")
				{
					if (nameobj=='open')
						arr[i].src = contractsymbol;
					else
						arr[i].src = expandsymbol;

				}
			}


			if (nameobj=='open') {
				curobj.src=masterminus;
				curobj.id = 'close';
			}
			else {
				curobj.src=masterplus;
				curobj.id = 'open';
			}


		}


	</script>

<?php if ($this->show_detailed_report == true && ($this->step > 0)) { ?>
	<?php if ($this->step == 1 || $this->step == 2) { ?>

		<div id="calendar" style="background-color: #FFFFFF; position: absolute; border: 1px #808080 solid; width; 300px; height: 80px; left:90px; display:none; top:100px;">
			<form>
				<table>
					<tr class='details'>
						<td>
							Please Enter a Date:
						</td>
					</tr>
					<tr>
						<td>
							<script type="text/javascript">
								DateInput('calendarwidget', true, 'MM/DD/YYYY');
							</script>
						</td>
					</tr>
					<tr>
						<td align=right>
							<INPUT TYPE="button" NAME="Cancel" VALUE="Cancel" OnClick="document.getElementById('calendar').style.display='none';">
							<INPUT TYPE="button" NAME="Save" VALUE="Save" OnClick="saveCalendar();">
						</td>
					</tr>
				</table>
			</form>
		</div>

	<?php }
}
?>

	<table align='center' class='retention' width=800 border=0>
		<tr>

			<td align=center>

				<table class='header' >
					<tr>
						<td align=center><strong> <?php echo $this->storeName ?></strong></td>
					</tr>
					<tr>
						<td align=center ><strong>Inactive Guest Status Reports</strong></td>
					</tr>
				</table>

				<!-- ############################################################### !-->
				<!-- ############################################################### !-->

				<table width=800 border="0">

					<tr>
						<TD align='center'>
							<form name="frm" action="" method="post" onSubmit="return _check_form(this);" >
								<?php
								// This is for any report in the fadmin section
								if (isset($this->form_session_list['store_html']) ) {
									echo '<strong>Store</strong>' . $this->form_session_list['store_html'] . '<br />';
								}
								?>
							</form>

						</td>
					</tr>

					<?php if ($this->step == 0 || empty($this->rows) || count($this->rows)== 0) { ?>
						<tr>
							<td class='header' align='center'>
								<form action="" method="post" onSubmit="return _check_form(this);">
									<b>Select a Report:</b>
									<SELECT ONCHANGE="location = this.options[this.selectedIndex].value;" ID="report_type" NAME="report_type">
										<OPTION ID="ch00" VALUE=" <?php echo 	$url."&step=0"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Summary Details
										<OPTION ID="ch01" VALUE=" <?php echo 	$url."&step=1&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive Report
										<OPTION ID="ch02" VALUE=" <?php echo 	$url."&step=2&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive Report
										<OPTION ID="ch03" VALUE=" <?php echo 	$url."&step=3&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive to Active Report
										<OPTION ID="ch04" VALUE=" <?php echo 	$url."&step=4&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive to Active Report
									</SELECT>
								</form>
							</td>
						</tr>
					<?php } ?>

				</table>

				<!-- ############################################################### !-->
				<!-- Initial Summary Page HTML !-->
				<!-- This table here is always linked to the initial summary results !-->
				<!-- ############################################################### !-->
				<?php if ($this->show_detailed_report == false) { ?>

					<?php

					if ($this->isPublic == 0)
						include $this->loadTemplate('admin/reports_user_retention_index.tpl.php');
					else
						echo "You have been logged off of the Dream Dinners Franchise Administration web site. Please log in again and select a different report to re-active this page";

					?>

				<?php } ?>

				<!-- ############################################################### !-->
				<!-- This table here is always linked to the initial summary results

				* Individual Templates will be created here to handle html differences and
				* processing

				!-->
				<!-- ############################################################### !-->
				<?php if ($this->show_detailed_report == true && ($this->step > 0)) { ?>
				<?php if ($this->step == 1 || $this->step == 2) { ?>



				<?php if (!empty($this->rows) && count($this->rows) > 0) { ?>
				<form name="form3" action="" method="post" onSubmit="return _check_form(this);">
					<table width=800 border=0 >
						<tr>
							<td align=center width=800 class='header'>
								<b>Select a Report:</b>
								<SELECT ONCHANGE="location = this.options[this.selectedIndex].value;" ID="report_type" NAME="report_type">
									<OPTION ID="ch00" VALUE=" <?php echo 	$url."&step=0"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Summary Details
									<OPTION ID="ch01" VALUE=" <?php echo 	$url."&step=1&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive Report
									<OPTION ID="ch02" VALUE=" <?php echo 	$url."&step=2&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive Report
									<OPTION ID="ch03" VALUE=" <?php echo 	$url."&step=3&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive to Active Report
									<OPTION ID="ch04" VALUE=" <?php echo 	$url."&step=4&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive to Active Report
								</SELECT>



								<b>Sort By:</b>
								<SELECT ONCHANGE="location = this.options[this.selectedIndex].value;" ID="sort_type" NAME="sort_type">
									<OPTION ID="sort1" VALUE=" <?php echo $url."&step=". $this->step	?>&sort=1&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Last Session
									<OPTION ID="sort2" VALUE=" <?php echo $url."&step=". $this->step?>&sort=2&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Last Name
									<OPTION ID="sort3" VALUE=" <?php echo $url."&step=". $this->step ?>&sort=3&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Total Days Inactive
								</SELECT>
							</td>
						</tr>
						<tr>
					</table>
					<table width=800 border=0 ><tr>
							<td class='standard' align=left width=40% >

								<?php
								echo '<img src="' . ADMIN_IMAGES_PATH . '/oe_plus.gif" id="open" class="showstate" onClick="openAll(this,' . $this->report_count . ')"/>' ;
								?>




							</td>

							<td> </td>
							<td align=right>
								<?php
								$exportAllLink = $url."&step=". $this->step . '&sort=' . $this->sort . '&report_type=true&export=xlsx'	;
								?>
								<a href=" <?php echo $exportAllLink?>">Export All Records</a>
								<?php if (!empty($this->bandwidth) && $this->bandwidth == 1) {
									$pages = ceil($this->report_count/$this->rowcount);
									if ($pages > 1) { echo ' Page: ';	}

									$pagelink = $url."&step=". $this->step . '&sort=' . $this->sort . '&report_type=true&bandwidth=1'	;


									for ($i = 0; $i<$pages; $i++) {
										$pageid = $i+1;
										$pagelinkstr = $pagelink . '&pagenum=' . $pageid;
										if ($this->pagenum == $pageid)
											echo " <b>$pageid</b>";
										else
											echo " <a href='$pagelinkstr'>$pageid</a>";
									}
								}
								?>
							</td>


						</tr>

					</table>
					<div id='user_fields'>
						<!-- ############################################################### !-->
						<?php
						$counter = 0;
						if (empty($this->bandwidth)) $this->bandwidth = 0;

						$bandwidth = $this->bandwidth;


						if ($bandwidth == 1) {
							$page = $this->pagenum;
							$this->rows = array_slice($this->rows , $this->rownum, $this->rowcount);
						}


						foreach ($this->rows as $user_array)
						{
						$key = $user_array['retention_id'];
						$followup_id_001 = null;
						$followup_002_arr = null;
						$followup_001_arr = null;
						$retentionid = null;
						$counter++;
						$userid = null;
						$userid = $user_array['user_id'];
						$retentionid = $key;

						$index = 0;
						if ($this->step == 2) $index = 1;


						if (!empty($this->follow_up_array[$user_array['user_id']])) {

							$followupArr = $this->follow_up_array[$user_array['user_id']];

							if (!empty($followupArr[$retentionid][1]))
								$followup_001_arr = $followupArr[$retentionid][1];

							if (!empty($followupArr[$retentionid][2]))
								$followup_002_arr = $followupArr[$retentionid][2];

							if ($index == 0)
								$followup_id_001 = isset( $followup_001_arr[0]['key'] ) ? $followup_001_arr[0]['key'] : 0;
							else
								$followup_id_001 = isset($followup_002_arr[0]['key']) ? $followup_002_arr[0]['key'] : 0;


						}

						?>

						<div id='header_ <?php echo $key?>'>
							<table border=0 class='ActiveGuestMainOutline'><tr><td>
										<table border=0 class='indicators'><tr>

												<td width=20 align="left" >
													<?php
													echo '<img src="' . ADMIN_IMAGES_PATH . '/icon/bullet_toggle_plus.png" id="imgpls_' . $key . '" onClick="expandcontent(this, ' . "'" . $key . "'" .')" />';
													?>
												</td>

												<td width=140>
													<b>Name:</b><br/>
													<?php

													if (!empty($user_array['firstname']) && !empty($user_array['lastname']))
														echo $user_array['lastname'] . ', ' . $user_array['firstname'];
													else if (empty($user_array['firstname']) && !empty($user_array['lastname']))
														echo $user_array['lastname'];
													else if (!empty($user_array['firstname']) && empty($user_array['lastname']))
														echo $user_array['firstname'];

													?>
												</td>

												<td width=100>
													<b>Primary Phone:</b><br/>
													<?php
													if (!empty($user_array['telephone_1']))
														echo $user_array['telephone_1'];
													?>
												</td>

												<td width=100>
													<b>Primary Call Time:</b><br/>
													<?php
													if (!empty($user_array['telephone_1_call_time']))
														echo $user_array['telephone_1_call_time'];
													?>
												</td>

												<td width=100>
													<b>Secondary Phone:</b><br/>
													<?php
													if (!empty($user_array['telephone_2']))
														echo $user_array['telephone_2'];
													?>
												</td>

												<td width=100>
													<b>Secondary Call Time:</b><br/>
													<?php
													if (!empty($user_array['telephone_2_call_time']))
														echo $user_array['telephone_2_call_time'];
													?>
												</td>

												<td width=140>
													<b>Last Session:</b><br/>
													<?php
													if (!empty($user_array['session_start']))
														echo $user_array['session_start'];
													?>
												</td>

												<td width=100 align='center'>
													<b>Days Inactive:</b><br/>
													<?php
													if (!empty($user_array['days_inactive']))
														echo $user_array['days_inactive'];
													?>
												</td>


												<td width=80 align='center' id='has_follow_up_ <?php echo $key?>'>
													<b>Has Follow-Up</b><br/>
													<?php
													if (empty($followup_id_001))
														echo '<img id="' . $key . '_check_image" src="' . ADMIN_IMAGES_PATH . '/check_no.gif" />';
													else
														echo '<img id="' . $key . '_check_image" src="' . ADMIN_IMAGES_PATH . '/check_yes.gif" />';

													?>
												</td>

											</tr></table>
						</div>

						<div id='master_ <?php echo $key?>' class='masterfield'>
							<table border=0 class='ActiveGuestSubHead'>
								<tr >

									<!-- ###################vertical sep ############################################ !-->

									<?php include $this->loadTemplate('admin/reports_user_retention_index_guest.tpl.php'); ?>

									<!-- ###################vertical sep ############################################ !-->


									<!-- ################### retention tables start ############################################ !-->
									<td>
										<?php
										for ($i = 1; $i <= 1; $i++){
										$sectionname = "section_follow_up_00" . $i;

										if ($index == 0) {
											$arr = $followup_001_arr;
										}
										else
										{
											$arr = $followup_002_arr;
										}


										?>

										<!-- ################### followup ############################################ !-->
										<div id=' <?php echo $sectionname?>_ <?php echo $key?>'>
											<input type="hidden" id=' <?php echo $sectionname?>_ <?php echo $key?>_user_id' name="user_id" value=" <?php echo $userid?>"></input>
											<input type="hidden" id=' <?php echo $sectionname?>_ <?php echo $key?>_retention_id' name='retention_id' value=" <?php echo $retentionid?>"></input>
											<input type="hidden" id=' <?php echo $sectionname?>_ <?php echo $key?>_follow_up_id' name='follow_up_id' value=" <?php echo isset($followup_id_001) ? $followup_id_001 : 0 ?>"></input>


											<table id='user_entry_fields' border=0 class='detailsOff' width=280>
												<tr>
													<td>
														<table id='60_89_Day_Follow_Up_00 <?php echo $i?>' border=0 width=280 >
															<tr>
																<td>
																	<table width=100% class='subheads'>
																		<tr >
																			<td><b>
																					<?php if ($this->step == 1) { ?>
																						60-89 Day Follow-up
																					<?php } else { ?>
																						90-119 Day Follow-up
																					<?php } ?>
																				</b>
																			</td>
																		</tr>
																	</table>


																	<div id=' <?php echo $sectionname?>_ <?php echo $key?>_follow_up_fields'>
																		<table border=0 id='60_89_Day_Follow_Up_Date_00 <?php echo $i?>' width=280>
																			<tr>
																				<td>
																					<table>
																						<tr>
																							<td width=48>Date:</td>

																							<td id=' <?php echo $sectionname?>_set_date_td_ <?php echo $key?>'> <?php
																								$date = isset($arr[$i-1]['follow_dates']) ? $arr[$i-1]['follow_dates'] : '' ;
																								$isSelectedVar = isset($arr[$i-1]['follow_types']) ? $arr[$i-1]['follow_types'] : null;
																								?>
																								<?php if ($this->isStoreOwner == true && empty($date) && empty($isSelectedVar))
																								{ ?>
																									<input TYPE="text" VALUE=" <?php echo $date?>" ID=' <?php echo $sectionname?>_date_ <?php echo $key;?>' SIZE="10" MAXLENGTH="10" >
																								<?php } else {
																									echo empty($date) ? 'n/a' : $date;
																								}?></td>



																							<td> <?php if ($this->isStoreOwner == true) { ?><input <?php if (!empty($date) && !empty($isSelectedVar)) echo 'style="visibility:hidden;"';?> id=' <?php echo $sectionname?>_set_ <?php echo $key?>' type=button value="Set Date" onClick="showCalendar(' <?php echo $sectionname?>_date_ <?php echo $key?>', this);"> <?php } ?></td>

																						</tr>
																					</table>
																				</td>
																			</tr>

																			<tr>
																				<td>
																					<table style="color:#BFBFBF; margin-top:-6px;" >
																						<tr>
																							<td width=48> </td>
																							<?php if ($this->isStoreOwner == true) { ?>
																								<td <?php if (!empty($date) && !empty($isSelectedVar)) echo 'style="visibility:hidden;"'; ?> style="color:#BFBFBF; margin-top:-6px;" id=' <?php echo $sectionname?>_title_date_td_ <?php echo $key?>' align=center><i>(mm/dd/yyyy)</i></td>
																							<?php } ?>
																							<td> </td>
																						</tr>
																					</table>
																				</td>
																			</tr>

																			<tr>
																				<td>
																					<table id='60_89_Day_Follow_Up_Action_00 <?php echo $i?>' border=0>
																						<tr>
																							<td width=40>Action:</td>
																							<td width=4> </td>
																							<td width=100 align=left id=' <?php echo $sectionname . "_select_td_" . $key?>'> <?php
																								if ($this->isStoreOwner == true && (empty($date) && empty($isSelectedVar))) {
																									if (empty($isSelectedVar))
																										echo "<select id='" . $sectionname . "_select_" . "$key'><option SELECTED value=0>Select a Follow-up Type</option>";
																									else
																										echo "<select id='" . $sectionname . "_select_" . "$key'><option value=0>Select a Follow-up Type</option>";

																									foreach($this->follow_up_choices as $choicekey => $type){
																										$SELECTED = '';
																										if (!empty($isSelectedVar) && $type == $isSelectedVar) {
																											echo "<option SELECTED value='$choicekey' >" . $type . "</option>";
																										}
																										else
																											echo "<option value='$choicekey' >" . $type . "</option>";
																									}
																								}
																								else echo empty($isSelectedVar) ? 'n/a' : $isSelectedVar;?></select></td>
																						</tr>
																					</table>
																				</td>
																			</tr>

																			<tr>
																				<td id=' <?php echo $sectionname?>_buttons_ <?php echo $key?>' align=right>

																					<?php if ($this->isStoreOwner == true && (!empty($isSelectedVar) && !empty($date))) { ?>
																						<?php if (!empty($isSelectedVar) && !empty($date)) { ?>

																							<input <?php if (empty($date) && empty($isSelectedVar)) echo 'disabled=true'; ?> id=' <?php echo $sectionname?>_delete_ <?php echo $key?>' type="button" onclick="deleteFollowup(' <?php echo $sectionname?>', <?php echo $key?>, <?php echo $userid?>, 'section_follow_up_result_00 <?php echo $i?>');" name="delete" value="Delete" >

																							<input <?php if (empty($date) && empty($isSelectedVar)) echo 'disabled=true'; ?> id=' <?php echo $sectionname?>_reset_ <?php echo $key?>' type="button" onclick="editSection(' <?php echo $sectionname?>', <?php echo $key?>);" name="reset" value="Edit" >
																						<?php } else { ?>

																							<input <?php if (!empty($date) && !empty($isSelectedVar)) echo 'disabled=true'; ?> id=' <?php echo $sectionname?>_save_ <?php echo $key?>' type="button" onclick="saveSection(' <?php echo $sectionname?>', <?php echo $key?>, <?php echo $userid?>);" name="save" value="Save" >

																						<?php } ?>
																					<?php } else if ($this->isStoreOwner == true) { ?>
																						<input <?php if (!empty($date) && !empty($isSelectedVar)) echo 'disabled=true'; ?> id=' <?php echo $sectionname?>_save_ <?php echo $key?>' type="button" onclick="saveSection(' <?php echo $sectionname?>', <?php echo $key?>, <?php echo $userid?>);" name="save" value="Save" >
																					<?php } ?>

																				</td>
																			</tr>

																		</table>
																	</div>


																</td>


															</tr>
														</table>
										</div>

									</td>
								</tr>

								<tr>
									<td>

										<!-- ################### result ############################################ !-->
										<div id='section_follow_up_result_00 <?php echo $i?>_ <?php echo $key?>'>

											<input type="hidden" id='section_follow_up_result_00 <?php echo $i?>_ <?php echo $key?>_user_id' name="user_id" value=" <?php echo $userid?>"></input>
											<input type="hidden" id='section_follow_up_result_00 <?php echo $i?>_ <?php echo $key?>_retention_id' name="retention_id" value=" <?php echo $retentionid?>"></input>
											<input type="hidden" id='section_follow_up_result_00 <?php echo $i?>_ <?php echo $key?>_follow_up_id' name="follow_up_id" value=" <?php echo isset($followup_id_001) ? $followup_id_001 : 0 ?>"></input>
											<input type="hidden" id='section_follow_up_result_00 <?php echo $i?>_ <?php echo $key?>_result_id' name="result_id" value=" <?php echo isset($followup_id_001) ? 1 : 0 ?>"></input>
											<table id='60_89_Day_Result_00 <?php echo $i?>' border=0 width=280 >
												<tr>
													<td>
														<table width=100% class='subheads'>
															<tr><td>
																	<?php if ($this->step == 1) { ?>
																		60-89 Day Result
																	<?php } else { ?>
																		90-119 Day Result
																	<?php } ?>
																</td></tr>
														</table>

														<table border=0 id='60_89_Day_Result_Date_00 <?php echo $i?>'>
															<?php
															$comment = isset($arr[$i-1]['comment']) ? $arr[$i-1]['comment'] : '';
															$date = isset($arr[$i-1]['result_dates']) ? $arr[$i-1]['result_dates'] : '' ;
															?>

															<tr>
																<td width=40>Date:</td>
																<td width=4> </td>

																<td id='section_follow_up_result_00 <?php echo $i?>_set_date_td_ <?php echo $key?>'> <?php if (!empty($comment) && !empty($date)) { ?> <?php echo isset($arr[$i-1]['result_dates']) ? $arr[$i-1]['result_dates'] : '' ?> <?php } else { ?> <?php if ($this->isStoreOwner == true) { ?><input TYPE="text" VALUE=" <?php echo isset($arr[$i-1]['result_dates']) ? $arr[$i-1]['result_dates'] : '' ?>" id='section_follow_up_result_00 <?php echo $i?>_date_ <?php echo $key?>' SIZE="10" MAXLENGTH="10" > <?php } else { ?>n/a <?php } } ?></td>

																<td>
																	<?php if ($this->isStoreOwner == true) { ?><input <?php if (!empty($comment) && !empty($date)) echo 'style="visibility:hidden;"'; ?> type=button id='section_follow_up_result_00 <?php echo $i?>_set_ <?php echo $key?>' value="Set Date" onClick="showCalendar('section_follow_up_result_00 <?php echo $i?>_date_ <?php echo $key?>', this);"> <?php } ?>
																</td>

															</tr>

															<tr >
																<td width=40> </td>
																<td width=4> </td>
																<?php if ($this->isStoreOwner == true) { ?>
																	<td id='section_follow_up_result_00 <?php echo $i?>_title_date_td_ <?php echo $key?>' <?php if (!empty($date) && !empty($comment)) echo 'style="visibility:hidden;"';?> style="color:#BFBFBF; margin-top:-6px;" align=center>
																		<i>(mm/dd/yyyy)</i>
																	</td>
																<?php } ?>
																<td> </td>
															</tr>


														</table>

													</td>
												</tr>
												<tr>
													<td>
														<table border=0 id='60_89_Day_Result_Notes_00 <?php echo $i?>'>
															<tr>
																<td width=40>Notes: </td>
																<td width=4> </td>
																<?php if ($this->isStoreOwner == true) { ?>
																	<td width=400 id='section_follow_up_result_00 <?php echo $i?>_notes_td_ <?php echo $key?>' align=left> <?php if (!empty($comment) && !empty($date)) { ?> <?php echo $comment ?> <?php } else { ?><textarea cols="25" rows="2" id="section_follow_up_result_00 <?php echo $i?>_text_ <?php echo $key?>"> <?php echo $comment?></textarea> <?php } ?></td>
																<?php } else { ?>
																	<td width=400 id='section_follow_up_result_00 <?php echo $i?>_notes_td_ <?php echo $key?>' align=left> <?php echo empty($comment) ? 'n/a' : $comment; ?></td>
																<?php } ?>
															</tr>
														</table>
													</td>
												</tr>

												<tr>
													<td id='section_follow_up_result_00 <?php echo $i?>_buttons_ <?php echo $key?>' align=right>
														<?php if ($this->isStoreOwner == true) { ?>
															<?php if (!empty($comment) && !empty($date)) { ?>
																<input <?php if (empty($comment) && empty($date)) echo 'disabled=true'; ?> id='section_follow_up_result_00 <?php echo $i?>_delete_ <?php echo $key?>' type="button" onclick="deleteResult('section_follow_up_result_00 <?php echo $i?>', <?php echo $key?>);" name="delete" value="Delete" >

																<input <?php if (empty($comment) && empty($date)) echo 'disabled=true'; ?> type="button" id='section_follow_up_result_00 <?php echo $i?>_reset_ <?php echo $key?>' onclick="editSection('section_follow_up_result_00 <?php echo $i?>', <?php echo $key?>);" name="reset" value="Edit" >
															<?php } else { ?>
																<input <?php if (!empty($comment) && !empty($date)) echo 'disabled=false'; ?> type="button" id='section_follow_up_result_00 <?php echo $i?>_save_ <?php echo $key?>' onclick="saveSection('section_follow_up_result_00 <?php echo $i?>', <?php echo $key?>, <?php echo $userid?>);" name="save" value="Save" >
															<?php } ?>
														<?php } else { ?>
															<br/>
														<?php } ?>
													</td>
												</tr>

												<?php if ($this->step == 1) { ?>
													<tr><td height="10"> </td></tr>
												<?php } else { ?>
													<tr><td height="100"> </td></tr>
												<?php } ?>


											</table>


										</div>

									</td>


									<!-- ################### result ############################################ !-->
								</tr>
							</table>


							<?php
							}
							?>
			</td>
			<!-- ################### retention tables end ############################################ !-->

		</tr>
	</table>

	</td></tr></table>

	</div> <!-- end master div for each item !-->
	<!-- ############################################################### !-->
	<?php
}
	?>
	</form>
	</div>
	<p> </p>
	<!-- ############################################################### !-->
<?php } else { ?>
	<table >
		<tr>
			<td align=center> </td>
		</tr>
		<tr><td align=center class='header'>
				<b>No records are available for review.</b>
			</td>
		</tr>
	</table>

<?php } ?>
	<!-- ############################################################### !-->

<?php } else if ($this->step == 3 || $this->step == 4) { ?>
	<?php if (!empty($this->rows) && count($this->rows) > 0) { ?>
		<form id="form2" action="" method="post" onSubmit="return _check_form(this);">
			<table width=800 border=0 >
				<tr>

					<td align=center width=800 class='header'>

						<b>Select a Report:</b>
						<SELECT ONCHANGE="location = this.options[this.selectedIndex].value;" ID="report_type" NAME="report_type">
							<OPTION ID="ch00" VALUE=" <?php echo 	$url."&step=0"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Summary Details
							<OPTION ID="ch01" VALUE=" <?php echo 	$url."&step=1&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive Report
							<OPTION ID="ch02" VALUE=" <?php echo 	$url."&step=2&report_type=true"?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive Report
							<OPTION ID="ch03" VALUE=" <?php echo 	$url."&step=3&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">60-89 days Inactive to Active Report
							<OPTION ID="ch04" VALUE=" <?php echo 	$url."&step=4&report_type=true"	?> <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">90-119 days Inactive to Active Report
						</SELECT>




						<b>Sort By:</b>

						<SELECT ONCHANGE="location = this.options[this.selectedIndex].value;" ID="sort_type" NAME="sort_type">
							<OPTION ID="sort1" VALUE=" <?php echo $url."&step=". $this->step	?>&sort=1&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Last Session
							<OPTION ID="sort2" VALUE=" <?php echo $url."&step=". $this->step?>&sort=2&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Last Name
							<OPTION ID="sort3" VALUE=" <?php echo $url."&step=". $this->step ?>&sort=3&report_type=true <?php echo isset($this->bandwidth) ? "&bandwidth=1" : null;?>">Total Days Inactive
						</SELECT>
					</td>
				</tr>
			</table>
			<table width=800>
				<tr>
					<td class='standard' align=left >

						<?php
						echo '<img src="' . ADMIN_IMAGES_PATH . '/oe_plus.gif" id="open" class="showstate" onClick="openAll(this,' . $this->report_count . ')"/>' ;
						?>




					</td>

					<td> </td>
					<td align=right>
						<?php
						$exportAllLink = $url."&step=". $this->step . '&sort=' . $this->sort . '&report_type=true&export=xlsx'	;
						?>
						<a href=" <?php echo $exportAllLink?>">Export All Records</a>
					</td>

				</tr>
			</table>
		</form>
		<?php


		foreach ($this->rows as $key => $user_array)
		{
			$retentionid = $key;
			if (!empty($this->follow_up_array[$user_array['user_id']])) {

				$followupArr = $this->follow_up_array[$user_array['user_id']];

				if (!empty($followupArr[$retentionid][1]))
					$followup_001_arr = $followupArr[$retentionid][1];

				if (!empty($followupArr[$retentionid][2]))
					$followup_002_arr = $followupArr[$retentionid][2];

				if (!empty($index) && $index == 0)
					$followup_id_001 = isset( $followup_001_arr[0]['key'] ) ? $followup_001_arr[0]['key'] : 0;
				else
					$followup_id_001 = isset($followup_002_arr[0]['key']) ? $followup_002_arr[0]['key'] : 0;


			}


			?>




			<?php include $this->loadTemplate('admin/reports_user_retention_index_002.tpl.php'); ?>




			<?php
		}
		?>

		<p> </p>
	<?php } else { ?>
		<table >
			<tr>
				<td align=center> </td>
			</tr>
			<tr><td align=center class='header'>
					<b>No records are available for review.</b>
				</td>
			</tr>
		</table>
	<?php } ?>
	<!-- ############################################################### !-->
	<!-- ############################################################### !-->

<?php } ?>
<?php } ?>
	<!-- ############################################################### !-->
	<!-- ############################################################### !-->


	</table>
	</td>
	</tr>
	</table>

<?php if ($this->show_detailed_report == true && ($this->step == 1 || $this->step == 2)) { ?>

	<!-- add javascript here for any reports !-->
	<script type="text/javascript">
		// ADD ANY SCRIPTING HERE FOR SPECIFIC REPORTS
	</script>

<?php } ?>
	<script type="text/javascript">
		if (document.getElementById("report_type"))
			(document.getElementById("report_type")).selectedIndex = <?php echo $this->step?>;
		if (document.getElementById("sort_type"))
			(document.getElementById("sort_type")).selectedIndex = <?php echo $this->sort-1?>;
	</script>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>