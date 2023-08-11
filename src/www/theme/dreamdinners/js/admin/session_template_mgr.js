var selectedCell = null;
var orgColor = null;

function admin_session_template_mgr_init()
{

	adjustForSessionType($("#session_type").val());
	$(document).on('keyup', '#session_notes, #session_title', function (e) {

		if ($(this).val() != strip_tags($(this).val()))
		{
			$(this).val(strip_tags($(this).val()));
		}

	});

	$("#start_time").on("change", function() {
		let startVal = $(this).val();
		let endVal = $("#end_time").val();
		let duration = $("#duration_minutes").val();

		let startMinutes = 0;
		let endMinutes = 0;
		if (startVal != "")
		{
			startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
		}

		if (startVal != "")
		{
			if (duration == 0)
			{
				$("#end_time").val(startVal);
			}
			else
			{
				endMinutes = startMinutes + (duration * 1);
				if (endMinutes >= 1440)
				{
					duration = 1439 - startMinutes;
					endMinutes = 1439;
					$("#duration_minutes").val(duration);
				}

				$("#end_time").val(minutesToTime(endMinutes));
			}
		}
	});

	$("#end_time").on("change", function() {
		let startVal = $("#start_time").val();
		let endVal = $(this).val();
		let duration = $("#duration_minutes").val();
		let startMinutes = 0;
		let endMinutes = 0;
		if (endVal != "")
		{
			endMinutes = endVal.split(":")[0] * 60 + (endVal.split(":")[1] * 1);
		}

		if (startVal != "" && endVal != "")
		{
			startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
			if (startVal > endVal)
			{
				if (duration == 0)
				{
					$("#end_time").val(startVal);
				}
				else
				{
					endMinutes = startMinutes + (duration * 1);
					if (endMinutes >= 1440)
					{
						$("#duration_minutes").val(1439-startMinutes);
						endMinutes = 1439;
						$("#end_time").val(minutesToTime(endMinutes));
					}
					else
					{
						$("#end_time").val(minutesToTime(endMinutes));
					}
				}
			}
			else
			{
				$("#duration_minutes").val(endMinutes-startMinutes);
			}
		}
	});

	$("#session_type").on("change", function() {
		adjustForSessionType($(this).val());
	});

	$("#duration_minutes").on("change", function() {
		let newDuration = $(this).val();

		if (newDuration < 0)
		{
			newDuration = 0;
			$("#duration_minutes").val(0);
		}
		let startVal = $("#start_time").val();
		if (startVal.length == 8)
		{
			//drop seconds
			startVal = startVal.substr(0,5);
		}

		if (startVal != "")
		{
			let startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
			let endMinutes = startMinutes + (newDuration * 1);

			if (endMinutes >= 1440)
			{
				duration = 1439 - startMinutes;
				endMinutes = 1439;
				$("#duration_minutes").val(duration);
			}

			$("#end_time").val(minutesToTime(endMinutes));
		}
	});

}

function adjustForSessionType(session_type){
	switch(session_type) {
		case 'SPECIAL_EVENT':
		case 'DELIVERY':
			$('#meal_customization_row').show();
			break;
		default:
			$('#meal_customization_row').hide();
	}
}
function minutesToTime(minutes)
{
	let newHour = Math.floor(minutes / 60);
	let newMinutes = minutes - (newHour * 60);

	newHour = newHour.toString();
	newMinutes = newMinutes.toString();

	if (newHour.length == 1)
	{
		newHour = "0" + newHour;
	}
	if (newMinutes.length == 1)
	{
		newMinutes = "0" + newMinutes;
	}

	return newHour + ":" + newMinutes;
}

function onDayClick(obj)
{
	if (obj == selectedCell) {
		return;
	}

	orgColor = obj.style.backgroundColor;

	//alert(obj.offsetLeft);

	if (selectedCell){selectedCell.style.backgroundColor = orgColor;}

	obj.style.backgroundColor = "#CAA9DE";
	selectedCell = obj;

	var dayIndex = selectedCell.cellIndex;
	document.getElementById("start_day").selectedIndex = dayIndex;
}

function editSession(itemID, setID)
{
	document.getElementById("action").value = "edit";
	document.getElementById("item_id").value = itemID;
	document.getElementById("itemEditor").submit();
}

function _item_submitClick(form)
{
	document.getElementById("action").value = "new";
}

function _item_updateClick(form)
{
	document.getElementById("action").value = "update";
}

function _item_deleteClick(form)
{
	document.getElementById("action").value = "delete";
}

function _item_update_cancelClick(form)
{
	document.getElementById("action").value = "cancel";
}

function handleItemSubmit(form)
{
	var action = document.getElementById("action").value;

	if (action == "delete" || action == "cancel" || action == "edit") {
		return true;
	}

	return _check_form(form);
}