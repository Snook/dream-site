
function Reschedule()
{
	document.getElementById("actionForm").action = "/backoffice/reschedule";
	document.getElementById("actionForm").submit();
}

function Cancel()
{
	document.getElementById("actionType").value = "cancel";
	document.getElementById("actionForm").submit();
}

function SendReminderEmail()
{
	document.getElementById("actionType").value = "send_reminder";
	document.getElementById("actionForm").submit();
}

function SendAllOrderEmails()
{
	document.getElementById("actionType").value = "send_all_order_emails";
	document.getElementById("actionForm").submit();
}

function SendAllDRVIPEmails()
{
	document.getElementById("actionType").value = "send_all_dr_vip_emails";
	document.getElementById("actionForm").submit();
}

function sendDreamTasteInviteHostess()
{
	document.getElementById("actionType").value = "send_dream_taste_invite";
	document.getElementById("actionForm").submit();
}

function Edit()
{
	bounce('/backoffice/order-mgr?order=' + order_id);
}

function SessionDetails()
{
	bounce('/backoffice?session=' + session_id);
}

function GuestDetails()
{
	bounce('/backoffice/user-details?id=' + user_id);
}