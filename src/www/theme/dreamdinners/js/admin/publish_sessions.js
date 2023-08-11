function admin_publish_sessions_init()
{

}

function getNewItemsInSelection()
{
	items_string = "";

	form = document.getElementById("fillWindowForm");
	startElem = document.getElementById("start");

	var startDate = startElem.value;
	if (startDate != null && startDate != "") {
		var startCell = document.getElementById(startDate);
	}

	endElem = document.getElementById("end");
	var endDate = endElem.value;

	if (endDate != null && endDate != "") {
		var endCell = document.getElementById(endDate);
	}

	if (endCell == null && startCell == null)
	{
		alert ("Please set a Date Range");
		return items_string;
	}

	if (endCell == null){endCell = startCell;}

	if (startCell == null){startCell = endCell;}

	if (isSafari) {
	var ci = -1;
	 for (var i = 0; i < startCell.parentNode.cells.length; i++) {
	   if (startCell === startCell.parentNode.cells[i]) {
	     ci = i;
	   }
	 }
	 if (ci == -1){alert("Problem: acessing cells. Please notify Dream dinners staff"); // this should never happen
}
	startCol = ci;
	} else  {
		var startCol= startCell.cellIndex;
	}

	var startRow = startCell.parentNode.rowIndex;

	if (isSafari) {
	ci = -1;
	 for (var i = 0; i < endCell.parentNode.cells.length; i++) {
	   if (endCell === endCell.parentNode.cells[i]) {
	     ci = i;
	   }
	 }
	 if (ci == -1){alert("Problem: acessing cells. Please notify Dream dinners staff"); // this should never happen
}
	endCol = ci;
	} else  {
		var endCol= endCell.cellIndex;
	}

	var endRow = endCell.parentNode.rowIndex;

	items_string = "";

	for (y = startRow; y <= endRow; y++)
	{
		if (y == startRow){tempStartCol = startCol;} else {tempStartCol = 0;}

		if (y == endRow){tempEndCol = endCol;} else {tempEndCol = 6;}

		for (x = tempStartCol; x <= tempEndCol; x++)
		{
			thisCell=document.getElementById(calendarName).rows[y].cells[x];

			if (thisCell)
			{
				images = thisCell.getElementsByTagName("img");

				for (i= 0; i < images.length; i++)
				{

					if (images.item(i).id < 0)
					{
						items_string += (images.item(i).id + "^" + thisCell.id + "^" +  images.item(i).name + "|");
					}
				}
			}
		}
	}

	return items_string.substr(0, items_string.length - 1);
}

function serializeNewSessions()
{
	// send all the new sessions back to the server
	items_string = "";

	for (y = 2; y <= 8; y++)
	{
		for (x = 0; x <= 6; x++)
		{
			thisCell=document.getElementById(calendarName).rows[y].cells[x];

			if (thisCell)
			{
				images = thisCell.getElementsByTagName("img");

				for (i= 0; i < images.length; i++)
				{
					if (images.item(i).id < 0)
					{
						items_string += (images.item(i).id + "^" + thisCell.id + "^" +  images.item(i).name + "|");
					}
				}
			}
		}
	}

	if (items_string != "")
	{
		form = document.getElementById("fillWindowForm");
		item_ids = document.getElementById("item_ids");
		item_ids.value = items_string.substr(0, items_string.length - 1);
	}
}

function hiliteIcon(id, index)
{
	img = document.getElementById(id);

	switch(index)
	{
		case 1:
			img.src = PATH.image_admin + '/calendar/session_pub_hl.png';
			return;
		case 2:
			img.src = PATH.image_admin + '/calendar/session_saved_hl.png';
			return;
		case 3:
			img.src = PATH.image_admin + '/calendar/session_new_hl.png';
			return;
		case 4:
			img.src = PATH.image_admin + '/calendar/session_closed_hl.png';
			return;
	}
}

function unHiliteIcon(id, index)
{
	img = document.getElementById(id);

	switch(index)
	{
		case 1:
			img.src = PATH.image_admin + '/calendar/session_pub.png';
			return;
		case 2:
			img.src = PATH.image_admin + '/calendar/session_saved.png';
			return;
		case 3:
			img.src = PATH.image_admin + '/calendar/session_new.png';
			return;
		case 4:
			img.src = PATH.image_admin + '/calendar/session_closed.png';
			return;
	}
}

function onSetFillWindowStart()
{
	if (selectedCell)
	{
		if( !selectedCell.getAttribute( 'validForMenu' ) )
		{
			serializeNewSessions();

			form = document.getElementById("fillWindowForm");

			endID = document.getElementById("end").value;
			if (endID != null && endID != "")
			{
				endTD = document.getElementById(endID);
				endCol= endTD.cellIndex;
				endRow = endTD.parentNode.rowIndex;

				var startCol= selectedCell.cellIndex;
				var startRow = selectedCell.parentNode.rowIndex;

				if (endRow < startRow || (endRow == startRow && endCol < startCol))
				{
					document.getElementById("end").value = selectedCell.id;
				}

			}

			startElem = document.getElementById("start");
			startElem.value = selectedCell.id;

			operation = document.getElementById("operation");
			operation.value = null;

			form.submit();
		}
		else
		{
			alert( "Sorry, you selected a date outside of the valid menu range.\nPlease select a date between the two arrows." );
		}
	}
	else
	{
		alert("Please select a start day by clicking on a day in calendar");
	}
}

function onSetFillWindowEnd()
{
	if (selectedCell)
	{
		if( !selectedCell.getAttribute( 'validForMenu' ) ) {
			serializeNewSessions();

			form = document.getElementById("fillWindowForm");

			startID = document.getElementById("start").value;
			if (startID != null && startID != "")
			{
				startTD = document.getElementById(startID);
				startCol= startTD.cellIndex;
				startRow = startTD.parentNode.rowIndex;

				var endCol= selectedCell.cellIndex;
				var endRow = selectedCell.parentNode.rowIndex;

				if (endRow < startRow || (endRow == startRow && endCol < startCol))
				{
					document.getElementById("start").value = selectedCell.id;
				}
			}

			endElem = document.getElementById("end");
			endElem.value = selectedCell.id;

			operation = document.getElementById("operation");
			operation.value = null;

			form.submit();
		}
		else
		{
			alert( "Sorry, you selected a date outside of the valid menu range.\nPlease select a date between the two arrows." );
		}
	}
	else
	{
		alert("Please select an end day by clicking on a day in calendar");
	}
}

function onClearFillWindow()
{
	serializeNewSessions();

	form = document.getElementById("fillWindowForm");

	endElem = document.getElementById("end");
	endElem.value = null;

	startElem = document.getElementById("start");
	startElem.value = null;

	operation = document.getElementById("operation");
	operation.value = null;

	form.submit();
}

function onEditClick(id)
{
	window.location = "./main.php?page=admin_edit_session&session=" + id + "&back=main.php?page=admin_publish_sessions";
}

function onSessionClick(id)
{
	window.location = "./main.php?page=admin_main&session=" + id;
}

function saveItems()
{
	items_string = getNewItemsInSelection();

	if (items_string != "")
	{
		form = document.getElementById("fillWindowForm");

		document.getElementById("item_ids").value = items_string;
		document.getElementById("operation").value = 'save';
		form.submit();

		var submitDiv = document.getElementById("saveButton");
		submitDiv.style.display = 'none';
	}
}

function publishItems()
{
	items_string = getNewItemsInSelection();

	form = document.getElementById("fillWindowForm");

	if (items_string != "")
	{
		document.getElementById("item_ids").value = items_string;
	}

	document.getElementById("operation").value = 'publish';
	form.submit();

	var submitDiv = document.getElementById("publishButton");
	submitDiv.style.display = 'none';
}

function onDeleteClick(obj)
{
	theSpan = obj.parentNode;
	theCell = theSpan.parentNode;

	theCell.removeChild(theSpan);

}