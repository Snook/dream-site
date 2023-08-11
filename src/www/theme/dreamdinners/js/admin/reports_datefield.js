
	function hidefields(field1) {

		if (field1 == "1") {
			setstate ("single_date", false);
			setstate ("range_day_start", true);
			setstate ("range_day_end", true);
			if (document.getElementById('year_field_002'))
				document.getElementById('year_field_002').disabled = true;
			if (document.getElementById('year_field_001'))
				document.getElementById('year_field_001').disabled = true;
			if (document.frm.month_popup)
				document.frm.month_popup.disabled = true;
		}
		else if (field1 == "2") {
			setstate ("single_date", true);
			setstate ("range_day_start", false);
			setstate ("range_day_end", false);
			if (document.getElementById('year_field_002'))
				document.getElementById('year_field_002').disabled = true;
			if (document.getElementById('year_field_001'))
				document.getElementById('year_field_001').disabled = true;
			if (document.frm.month_popup)
				document.frm.month_popup.disabled = true;
		}
		else if (field1 == "3") {
			setstate ("single_date", true);
			setstate ("range_day_start", true);
			setstate ("range_day_end", true);
			if (document.getElementById('year_field_002'))
				document.getElementById('year_field_002').disabled = true;
			if (document.getElementById('year_field_001'))
				document.getElementById('year_field_001').disabled = false;
			if (document.frm.month_popup)
				document.frm.month_popup.disabled = false;

		}
		else if (field1 == "4") {
			setstate ("single_date", true);
			setstate ("range_day_start", true);
			setstate ("range_day_end", true);

			if (document.getElementById('year_field_002'))
				document.getElementById('year_field_002').disabled = false;

			if (document.getElementById('year_field_001'))
				document.getElementById('year_field_001').disabled = true;

			if (document.frm.month_popup)
				document.frm.month_popup.disabled = true;

		}

	}

	function setstate (fieldname, disable) {

		if (disable == true) {
			if (document.getElementById(fieldname + '_Year_ID'))
				document.getElementById(fieldname + '_Year_ID').disabled = true;

			if (document.getElementById(fieldname + '_Month_ID'))
				document.getElementById(fieldname + '_Month_ID').disabled = true;

			if (document.getElementById(fieldname + '_Day_ID'))
				document.getElementById(fieldname + '_Day_ID').disabled = true;

			if (document.getElementById(fieldname + '_ID_Link'))
				document.getElementById(fieldname + '_ID_Link').style.visibility = 'hidden';
		}
		else
		{
			if (document.getElementById(fieldname + '_Year_ID'))
				document.getElementById(fieldname + '_Year_ID').disabled = false;
			if (document.getElementById(fieldname + '_Month_ID'))
				document.getElementById(fieldname + '_Month_ID').disabled = false;
			if (document.getElementById(fieldname + '_Day_ID'))
				document.getElementById(fieldname + '_Day_ID').disabled = false;
			if (document.getElementById(fieldname + '_ID_Link'))
				document.getElementById(fieldname + '_ID_Link').style.visibility = 'visible';

		}

	}


	// methods below are for the financials report (reports_financial_statistic.tpl.php

	function getElementbyClass(rootobj, classname){
		var temparray=[];
		var inc=0;
		var rootlength=rootobj.length;
		for (i=0; i<rootlength; i++){
		if (rootobj[i].className==classname)
			temparray[inc++]=rootobj[i];
		}
		return temparray;
	}

	var prefix = 'sc';
	var imprefix = 'bt';
	var contractsymbol= PATH.image + 'admin/icon/bullet_toggle_minus.png' ;//Path to image to represent contract state.
	var expandsymbol= PATH.image + 'admin/icon/bullet_toggle_plus.png' ;//Path to image to represent expand state.

	function expandcontent(curobj, cid){
		document.getElementById(cid).style.display=(document.getElementById(cid).style.display!="")? "" : "block";
		curobj.src=(document.getElementById(cid).style.display=="")? expandsymbol : contractsymbol;

	}

	function closeAll ( nodeCount  )
	{

		for (i=1; i<nodeCount+1; i++){

			var str = imprefix + i;
			var obj = document.getElementById(str);
			obj.src = expandsymbol;
			var div = document.getElementById(prefix + i);
			div.style.display = "";

		}
	}

	function openAll ( nodeCount  )
	{

		for (i=1; i<nodeCount+1; i++){
			var str = imprefix + i;
			var obj = document.getElementById(str);
			obj.src = contractsymbol;
			var div = document.getElementById(prefix + i);
			div.style.display = "block";
		}
	}

