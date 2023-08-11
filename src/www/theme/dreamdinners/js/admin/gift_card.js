var useTransparentRedirect = false;

var codes = '';
var i =0;
var x =0;
var browser=navigator.appName;
  function keyHandler(evt) {
        if(browser=="Microsoft Internet Explorer") {
			var charCode = (document.all)?event.keyCode:evt.keyCode;
		} else {
			var charCode=evt.keyCode? evt.keyCode : evt.charCode;
		}
     if(charCode==13 ){
         return false;
     }

	 if( charCode==94 ){
		 if( codes.length>=15 ) {
				var gc_elem = document.getElementById('gift_card_number');
				if (gc_elem) gc_elem.value=codes;
		 } else {
			 codes = '';
			 i=0;		 }
		 return false;
	 }
    if( codes.length==15 ) {
			 //document.getElementById('gift_card_number').value=codes;
             return false;
    }
	if((String.fromCharCode(charCode)=='%')&&(i==0)){
         i=1;
     }
     else if((String.fromCharCode(charCode)=='B')&&(i==1)){
         i=2;
     }
	 else if( i==2 && (parseInt(charCode)>57 || parseInt(charCode)<48)  && charCode!=94 && charCode!=8) {
		 //document.getElementById('gift_card_number').value=codes;
		 return false;
	 }
	else if((parseInt(charCode)>57 || parseInt(charCode)<48) && i<2 && charCode!=94 && (String.fromCharCode(charCode))!='B' && charCode!=8){
		 //document.getElementById('gift_card_number').value=codes;
		 return false;
	 }


     if((String.fromCharCode(charCode)!='B')&&(i==2)){
         if((String.fromCharCode(charCode)!='^')){
             codes += String.fromCharCode(charCode);
         }
     }

}


 //document.onkeypress = keyHandler;

 function _override_check_form(frmObj)
 {

	 return false;
	// defeat ordinary submission

 }

 function _submit_click(obj)
 {
	obj.style.display = "none";

	var frmObj = $("#gift_card_load")[0];

	var validated = _check_form(frmObj);

	if (!validated)
	{
		 document.getElementById('procCardLoadBtn').style.display = "block"
	}
	else
	{
		var amount = $("#amount").val();
		if(isNaN(amount) || amount < 25 || amount > 500)
		{
			dd_message({
				title: 'Error',
				message: "Please enter a dollar amount greater or equal to $25 and no greater than $500."
			});

			$("#amount").addClass('input_in_error');

			document.getElementById('procCardLoadBtn').style.display = "block";
			return true;
		}



		var msg = "Load Gift Card #" + $('#gift_card_number').val() + " by the amount of $" + formatAsMoney($("#amount").val()) + "?";


		dd_message({
			title: 'Submit Gift Card Order',
			message: msg,
			modal: true,
			confirm: function() {
				if (useTransparentRedirect)
				{
					getTokenAndSubmit();
				}
				else
				{
					submitDirect();
				}
			},
			cancel:function() {
				$('#procCardLoadBtn').show();
			}

		});

	}

	return true;
 }


 function submitDirect()
 {
	$('<input>').attr({
		type: 'hidden',
		name: 'load_submit',
		value: 'load_submit',
	}).appendTo($('#gift_card_load'));

	$('#gift_card_load').submit();
 }


 function getTokenAndSubmit()
 {

		displayModalWaitDialog('TR_process_dialog', 'Please wait as the payment is processed. This may take a short while.');

		var billing_name = $("#billing_name").val();
		var billing_address = $("#billing_address").val();
		var billing_zip = $("#billing_zip").val();
		var billing_email = $("#primary_email").val();


		var payload = new PayFlow_Payload();

		var billing_data = {
			bname: billing_name,
			badd: billing_address,
			bzip: billing_zip,
			bemail: billing_email
		};


		var comment_payload = new PayFlow_Payload();

		comment_payload.addNameValuePair('UserID', 0);
		comment_payload.addNameValuePair('StoreID', 0);
		comment_payload.addNameValuePair('OrderID', 0);
		comment_payload.addNameValuePair('Email', billing_email);


		payload.addAssocArray('billing_data', billing_data);
	 	var amt = $("#amount").val();


		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_order_mgr_processor',
				store_id: store_id,
				op: 'get_token',
				order_id: 0,
				amount: amt,
				billing_name: billing_name,
				billing_address: billing_address,
				billing_zip: billing_zip,
				use_corp: true,
				check_smart_transactions: true,
				dd_csrf_token: $('input[name=dd_csrf_token]', '#gift_card_load').val(),
				chain_token: true
			},
			success: function(json)
			{

				$('input[name=dd_csrf_token]', '#gift_card_load').val(json.getTokenToken);


				if(json.processor_success)
				{
					var token = json.token;

					submit_to_paypal(token, payload, comment_payload);
				}
				else
				{

					 $("#procCardLoadBtn").show();
					$("#TR_process_dialog").remove();

					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function(objAJAXRequest, strError)
			{

				 $("#procCardLoadBtn").show();
				$("#TR_process_dialog").remove();

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
 }


 function submit_to_paypal(token, payload, comment_payload)
 {
	$('#paypal-result').on("load", function()
	{
		dd_console_log( "frame is loading in submit_to_paypal()");

		try
		{
			//var datastuff = $('#paypal-result').contents().find('body').html();
			var next_dest  = $('#paypal-result').get(0).contentWindow.next_dest;
			var success = $('#paypal-result').get(0).contentWindow.pp_success;

			dd_console_log( "successful access with next_dest as " + next_dest);

		}
		catch(e)
		{
			dd_console_log( "exception thrown accessing iFrame: " + e.message);
			$("#TR_process_dialog").remove();
		    $("#procCardLoadBtn").show();

			dd_message({
				title: 'Exception',
				message: 'Some required information is missing or incorrect. Please correct the fields below and try again.'
			});

			return;
		}

		// in Safari - exception is not thrown and success is undefined
		// the test is false and we drop into the success = false block
		// then error_code and message also are set to undefined


		if (success)
		{

			dd_console_log( "sucess! about to bounce to " + next_dest);

			bounce(next_dest);
		}
		else
		{

			dd_console_log( "failure! ");


			var error_code = $('#paypal-result').get(0).contentWindow.error_code;
			var message = $('#paypal-result').get(0).contentWindow.message;

			if (typeof message == 'undefined')
			{

				dd_console_log( "failure with no data! ");

				error_code = "Generic";
				message = 'An unexpected error has occurred. It appears that the Gift Card processor is currently unavailable. Please contact Dream Dinners support at 1-360-804-2020.';
			}

			$("#TR_process_dialog").remove();

			dd_message({
				title: 'Error: ' + error_code,
				message: message
			});

			 $("#procCardLoadBtn").show();
		}
	});


 	var expdate = $("#credit_card_exp_month").val();
 	expdate += $("#credit_card_exp_year").val();
 	var csc = $("#credit_card_cvv").val();
 	var amt = $("#amount").val();
 	var gift_card_number = $("#gift_card_number").val();
	var card_type = $("#credit_card_type").val();


	payload.addNameValuePair('user_id', 0);
	payload.addNameValuePair('store_id', store_id);
	payload.addNameValuePair('order_id', 'none');
	payload.addNameValuePair('caller_id', 'fadmin_load');
	payload.addNameValuePair('card_type', card_type);
	payload.addNameValuePair('gcnum', gift_card_number);
	payload.addNameValuePair('admin_user_id', USER_DETAILS.id);


    if (typeof payflowErrorURL == 'undefined')
    {
    	payflowErrorURL = "https://dreamdinners.com/ddproc.php?processor=admin_payflow_callback";
    }

 	// make PARMLIST
 	var parmList = 'ERRORURL=' + payflowErrorURL + "&ACCT=" + $("#credit_card_number").val() + "&EXPDATE=" + expdate + "&CSC=" + csc + "&AMT=" + amt + "&USER1=" + payload.retrieveEncodedString() + "&COMMENT1=" + comment_payload.retrieveEncodedString();


    var tr_action = 'https://payflowlink.paypal.com';
    if (typeof pfp_test_mode != 'undefined' && pfp_test_mode)
    {
        var tr_action = 'https://pilot-payflowlink.paypal.com';
    }

    if (typeof transparent_redirect_link != 'undefined')
    {
    	tr_action = transparent_redirect_link;
    }

	dd_console_log( "About to send! ");

 	// Create dynamic form to post and redirect to session_menu
 	create_and_submit_form({
 		action: tr_action,
 		target: "paypal-result",
 		input: ({
			SECURETOKEN:token.token,
			SECURETOKENID: token.tokenID,
 		 	PARMLIST: parmList
 	 	})
 	});

 }
