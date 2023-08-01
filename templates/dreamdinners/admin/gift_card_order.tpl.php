<?php
$this->setOnLoad("onPageLoad();");
$this->assign('page_title', 'Order a New Gift Card');
$this->assign('topnav', 'giftcards');
$this->assign('helpLinkSection', 'GC_ORDER');

if (defined('TR_SIM_LINK'))
{
	$this->setScriptVar("transparent_redirect_link = '" . TR_SIM_LINK . "';");
}

if (defined('PFP_TEST_MODE') && PFP_TEST_MODE)
{
	$this->setScriptVar("pfp_test_mode = true;");
}

if (defined('PHP_ERROR_URL'))
{
	$this->setScriptVar("payflowErrorURL = '" . PHP_ERROR_URL . "';");
}


include $this->loadTemplate('admin/page_header.tpl.php');
?>


	<iframe name="paypal-result" id="paypal-result" style="display:none;"></iframe>

	<script type="text/javascript">

		function updateInfo()
		{
			if (document.getElementById('sameInfo').checked == 1)
			{

				document.getElementById('billing_name').value = document.getElementById('shipping_first_name').value + ' ' + document.getElementById('shipping_last_name').value;
				document.getElementById('billing_address').value = document.getElementById('shipping_address_1').value + ' ' + document.getElementById('shipping_address_2').value;
				document.getElementById('billing_zip').value = document.getElementById('shipping_zip').value;
			}
			else
			{
				document.getElementById('billing_name').value = '';
				document.getElementById('billing_address').value = '';
				document.getElementById('billing_zip').value = '';
			}

		}

		var currentMediaType = false;
		var currentDesignType = false;

		function onPageLoad()
		{
			<?php
			if (isset($this->hadError) && $this->hadError ) {?>

			document.getElementById('media_type_div_collapsed').style.display = "block";
			document.getElementById('design_type_div_collapsed').style.display = "block";
			document.getElementById('card_details_form').style.display = "block";
			document.getElementById('payment_form_div').style.display = "block";

			<?php if ($this->selectedMediaType == 'virt') { ?>
			document.getElementById('virtual_card_form').style.display = "block";
			mediaClick(document.getElementById('virtual_card'));
			<?php } else { ?>
			document.getElementById('physical_card_form').style.display = "block";
			document.getElementById('use_shipping').style.display = "inline";
			document.getElementById('s_and_h_note').style.display = "block";

			mediaClick(document.getElementById('physical_card'));
			<?php } ?>

			var designName = 'cd_<?php echo $this->selectedDesignID?>';
			designClick(document.getElementById(designName));

			<?php } else { ?>
			document.getElementById('media_type_div_expanded').style.display = "block";
			document.getElementById('procCardOrderBtn').style.display = "none";

			<?php } ?>

		}

		<?php if (!empty($this->card_designs)) { ?>
		var designImageNames = {};
		var designDescription = {};

		<?php	foreach ($this->card_designs as $id => $design_data) {
		if ($design_data['supports_physical']) { ?>
		designImageNames["<?php echo $id?>"] = "<?php echo $design_data['image_path']?>";
		designDescription["<?php echo $id?>"] = "<?php echo $design_data['title']?>";
		<?php }}} ?>

		<?php if (!empty($this->card_designs)) { ?>
		var designImageNamesVirt = {};
		var designDescriptionVirt = {};

		<?php	foreach ($this->card_designs as $id => $design_data){
		if ($design_data['supports_virtual']) { ?>
		designImageNamesVirt["<?php echo $id?>"] = "<?php echo $design_data['image_path_virtual']?>";
		designDescriptionVirt["<?php echo $id?>"] = "<?php echo $design_data['title']?>";
		<?php }}} ?>

		function designClick(obj)
		{
			document.getElementById('virt_design_type_div_expanded').style.display = "none";
			document.getElementById('phys_design_type_div_expanded').style.display = "none";

			document.getElementById('design_type_div_collapsed').style.display = "block";

			currentDesignType = obj.id.substr(3);

			<?php if (!empty($this->card_designs)) {
			foreach ($this->card_designs as $id => $design_data) { ?>
			//	document.getElementById('td_' + <?php echo $id?>).style.backgroundColor = "#f8f6f5";
			//	document.getElementById('tdv_' + <?php echo $id?>).style.backgroundColor = "#f8f6f5";

			<?php	if ($design_data['supports_physical']) { ?>
			document.getElementById('td_' + <?php echo $id?>).style.backgroundColor = "#f8f6f5";
			<?php } ?>

			<?php	if ($design_data['supports_virtual']) { ?>
			document.getElementById('tdv_' + <?php echo $id?>).style.backgroundColor = "#f8f6f5";
			<?php } ?>

			<?php }} ?>

			if (document.getElementById('td_' + currentDesignType))
			{
				document.getElementById('td_' + currentDesignType).style.backgroundColor = "#b5cfb5";
			}

			if (document.getElementById('tdv_' + currentDesignType))
			{
				document.getElementById('tdv_' + currentDesignType).style.backgroundColor = "#b5cfb5";
			}

			if (currentMediaType == 'virt')
			{
				document.getElementById('virtual_card_form').style.display = "block";
				document.getElementById('selectedDesignImg').src = "<?php echo ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNamesVirt[currentDesignType];

				document.getElementById('selected_design_desc').innerHTML = designDescriptionVirt[currentDesignType];
				document.getElementById('expanded_virt_design_message').innerHTML = "&nbsp;&nbsp;Currently Selected > " + designDescriptionVirt[currentDesignType];
				document.getElementById('expanded_phys_design_message').innerHTML = "&nbsp;&nbsp;Currently Selected > " + designDescriptionVirt[currentDesignType];

			}
			else
			{
				document.getElementById('physical_card_form').style.display = "block";
				document.getElementById('use_shipping').style.display = "inline";
				document.getElementById('s_and_h_note').style.display = "block";

				document.getElementById('selectedDesignImg').src = "<?php echo ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNames[currentDesignType];

				document.getElementById('selected_design_desc').innerHTML = designDescription[currentDesignType];
				document.getElementById('expanded_virt_design_message').innerHTML = "&nbsp;&nbsp;Currently Selected > " + designDescription[currentDesignType];
				document.getElementById('expanded_phys_design_message').innerHTML = "&nbsp;&nbsp;Currently Selected > " + designDescription[currentDesignType];
			}

			document.getElementById('card_details_form').style.display = "block";
			document.getElementById('payment_form_div').style.display = "block";

			document.getElementById('procCardOrderBtn').style.display = "block";

		}

		function modifyDesign(obj)
		{
			if (currentMediaType == 'virt')
			{
				document.getElementById('virt_design_type_div_expanded').style.display = "block";
			}
			else
			{
				document.getElementById('phys_design_type_div_expanded').style.display = "block";
			}

			document.getElementById('design_type_div_collapsed').style.display = "none";
		}

		function mediaClick(obj)
		{
			document.getElementById('media_type_div_expanded').style.display = "none";
			document.getElementById('media_type_div_collapsed').style.display = "block";

			if (!currentDesignType || document.getElementById('phys_design_type_div_expanded').style.display == "block" ||
				document.getElementById('virt_design_type_div_expanded').style.display == "block")
			{
				if (obj.id == "physical_card" || obj.id == "physical_card_img")
				{
					document.getElementById('phys_design_type_div_expanded').style.display = "block";
					document.getElementById('virt_design_type_div_expanded').style.display = "none";
				}
				else
				{
					document.getElementById('virt_design_type_div_expanded').style.display = "block";
					document.getElementById('phys_design_type_div_expanded').style.display = "none";
				}
			}

			if (obj.id == "physical_card" || obj.id == "physical_card_img")
			{
				document.getElementById('selected_media_desc').innerHTML = "Traditional Card (Sent via standard mail)";
				currentMediaType = "phys";

				document.getElementById("expanded_type_message").innerHTML = "&nbsp;&nbsp;Currently Selected > Traditional Card (Sent via standard mail)";

				document.getElementById("phys_div").style.backgroundColor = "#b5cfb5";
				document.getElementById("virt_div").style.backgroundColor = "#f8f6f5";

				if (currentDesignType) // design has been picked so show correct form
				{
					document.getElementById('physical_card_form').style.display = "block";
					document.getElementById('use_shipping').style.display = "inline";
					document.getElementById('s_and_h_note').style.display = "block";

					document.getElementById('virtual_card_form').style.display = "none";
					document.getElementById('procCardOrderBtn').style.display = "block";

					// also switch design image
					if (designImageNames[currentDesignType])
					{
						document.getElementById('selectedDesignImg').src = "<?php echo ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNames[currentDesignType];
						document.getElementById('selected_design_desc').innerHTML = designDescription[currentDesignType];
					}
					else
					{
						currentDesignType = false;
						document.getElementById('phys_design_type_div_expanded').style.display = "block";
						document.getElementById('virt_design_type_div_expanded').style.display = "none";
						document.getElementById('design_type_div_collapsed').style.display = "none";
						document.getElementById('physical_card_form').style.display = "none";
						document.getElementById('card_details_form').style.display = "none";
						document.getElementById('payment_form_div').style.display = "none";

						document.getElementById('procCardOrderBtn').style.display = "none";
					}

				}

			}
			else
			{
				document.getElementById('selected_media_desc').innerHTML = "Virtual eGift Card (Sent via email)";
				currentMediaType = "virt";

				document.getElementById('expanded_type_message').innerHTML = "&nbsp;&nbsp;Currently Selected > Virtual eGift Card (Sent via email)";

				document.getElementById("phys_div").style.backgroundColor = "#f8f6f5";
				document.getElementById("virt_div").style.backgroundColor = "#b5cfb5";

				if (currentDesignType) // design has been picked so show correct form
				{
					document.getElementById('physical_card_form').style.display = "none";
					document.getElementById('use_shipping').style.display = "none";
					document.getElementById('s_and_h_note').style.display = "none";

					document.getElementById('virtual_card_form').style.display = "block";

					document.getElementById('procCardOrderBtn').style.display = "block";

					document.getElementById('selectedDesignImg').src = "<?php echo ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNamesVirt[currentDesignType];
					document.getElementById('selected_design_desc').innerHTML = designDescriptionVirt[currentDesignType];

					// also switch design image
					if (designImageNamesVirt[currentDesignType])
					{
						document.getElementById('selectedDesignImg').src = "<?php echo ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNamesVirt[currentDesignType];
						document.getElementById('selected_design_desc').innerHTML = designDescriptionVirt[currentDesignType];
					}
					else
					{
						currentDesignType = false;
						document.getElementById('virt_design_type_div_expanded').style.display = "block";
						document.getElementById('phys_design_type_div_expanded').style.display = "none";
						document.getElementById('design_type_div_collapsed').style.display = "none";
						document.getElementById('virtual_card_form').style.display = "none";
						document.getElementById('card_details_form').style.display = "none";
						document.getElementById('payment_form_div').style.display = "none";

						document.getElementById('procCardOrderBtn').style.display = "none";

					}

				}

			}

		}

		function modifyMedia(obj)
		{
			document.getElementById('media_type_div_expanded').style.display = "block";
			document.getElementById('media_type_div_collapsed').style.display = "none";
		}

		function _override_check_form(form)
		{
			if (currentMediaType == "phys")
			{
				document.getElementById('shipping_address_1').setAttribute('data-dd_required', true);
				document.getElementById('shipping_city').setAttribute('data-dd_required', true);
				document.getElementById('shipping_state').setAttribute('data-dd_required', true);
				document.getElementById('shipping_zip').setAttribute('data-dd_required', true);
				document.getElementById('shipping_first_name').setAttribute('data-dd_required', true);
				document.getElementById('shipping_last_name').setAttribute('data-dd_required', true);
				document.getElementById('physical_card').setAttribute('data-dd_required', true);
				document.getElementById('confirm_recipient_email').setAttribute('optional_email', false);
				document.getElementById('recipient_email').setAttribute('optional_email', false);
				document.getElementById('confirm_recipient_email').setAttribute('email', false);
				document.getElementById('recipient_email').setAttribute('email', false);
				document.getElementById('confirm_recipient_email').setAttribute('data-dd_required', false);
				document.getElementById('recipient_email').setAttribute('data-dd_required', false);

			}
			else
			{
				document.getElementById('shipping_address_1').setAttribute('data-dd_required', false);
				document.getElementById('shipping_city').setAttribute('data-dd_required', false);
				document.getElementById('shipping_state').setAttribute('data-dd_required', false);
				document.getElementById('shipping_zip').setAttribute('data-dd_required', false);
				document.getElementById('shipping_first_name').setAttribute('data-dd_required', false);
				document.getElementById('shipping_last_name').setAttribute('data-dd_required', false);
				document.getElementById('physical_card').setAttribute('data-dd_required', false);
				document.getElementById('confirm_recipient_email').setAttribute('optional_email', false);
				document.getElementById('recipient_email').setAttribute('optional_email', false);
				document.getElementById('confirm_recipient_email').setAttribute('email', true);
				document.getElementById('recipient_email').setAttribute('email', true);
				document.getElementById('confirm_recipient_email').setAttribute('data-dd_required', true);
				document.getElementById('recipient_email').setAttribute('data-dd_required', true);

				if (!document.getElementById('shipping_zip').value || document.getElementById('shipping_zip').value == "")
				{
					document.getElementById('shipping_zip').value = "11111";
				}

			}

			if (currentMediaType)
			{
				document.getElementById("media_type").value = currentMediaType;
			}
			else
			{
				//error!
				return false;
			}

			if (currentDesignType)
			{
				document.getElementById("design_id").value = currentDesignType;
			}
			else
			{
				//error!
				return false;
			}

			var form_valid = _check_form(form);

			if (currentMediaType == "phys")
			{
				var zipStr = document.getElementById('shipping_zip').value;
				if (zipStr.length != 5)
				{
					_display_error_message(form.shipping_zip, 'message', 'The ZIP code must be 5 digits long.');
					return false;
				}
			}

			var zipStr = document.getElementById('billing_zip').value;
			if (zipStr.length != 5)
			{
				_display_error_message(form.billing_zip, 'message', 'The ZIP code must be 5 digits long.');
				return false;
			}

			return form_valid;

		}

		function _submit_click(obj)
		{
			obj.style.display = "none";

			var frmObj = $("#gift_card_order")[0];

			var validated = _override_check_form(frmObj);

			if (!validated)
			{
				document.getElementById('procCardOrderBtn').style.display = "block"
			}
			else
			{
				var recipient = "";
				if (currentMediaType == "virt")
				{
					recipient = $('#recipient_email').val();
				}
				else
				{
					recipient = $('#shipping_first_name').val() + " " + $('#shipping_last_name').val();
				}

				var msg = "Order a " + (currentMediaType == "virt" ? "VIRTUAL" : "PHYSICAL") + " Gift Card for the amount of $" + formatAsMoney($("#amount").val()) + " to be sent to <i>" + recipient + "</i>?";

				dd_message({
					title: 'Submit Gift Card Order',
					message: msg,
					modal: true,
					confirm: function ()
					{
						processOrder();

					},
					cancel: function ()
					{
						$('#procCardOrderBtn').show();
					}

				});

			}

			return true;
		}


		function processOrder()
		{
			$("#gift_card_order").submit();
		}

	</script>


	<form name="gift_card_order" method="post" id="gift_card_order" onsubmit="_override_check_form(this);">
		<input type="hidden" id="media_type" name="media_type" value=""/>
		<input type="hidden" id="design_id" name="design_id" value=""/>
		<?php echo $this->form_account['hidden_html']; ?>

		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>&nbsp;</td>
			</tr>

			<?php if (isset($this->form_account['store_html'])) { ?>
				<!-- Store Row : for site admin only -->
				<tr class="form_subtitle_cell">
					<td align="center" colspan="4" style="padding: 5px;">
						<b>Selected Store:</b><br/>&nbsp;<?php echo $this->form_account['store_html']; ?> </td>
				</tr>
			<?php } ?>

			<tr>
				<td colspan="3">

					<!-- begin content -->
					<br/>
					<!-- Media type Selection -->
					<div id="media_type_div_expanded" style="display:none; width:100%;">
						<span style="font-weight:bold">Select Gift Card Type:</span><span style="color:#628F62; font-weight:bold;" id="expanded_type_message"></span><br/><br/>

						<div id="phys_div" style="width:50%; float:left; text-align:center; padding-top:10px; padding-bottom:5px;">
							<img id="physical_card_img" onclick="mediaClick(this);" src="<?php echo ADMIN_IMAGES_PATH ?>/gift_card/gc_traditional.gif"/><br/>
							<strong>Traditional Card</strong><br/>(Via standard mail in 2-6 business days )<br/>
							<button id="physical_card" class="button" onClick="mediaClick(this); return false;">Select</button>
						</div>

						<div id="virt_div" style="text-align:center; padding-top:10px; padding-bottom:5px;">
							<img id="virtual_card_img" onclick="mediaClick(this);" src="<?php echo ADMIN_IMAGES_PATH ?>/gift_card/gc_electronic.gif"/><br/>
							<strong>Virtual eGift Card</strong><br/>(Sent instantly via email)<br/>
							<button id="virtual_card" class="button" onClick="mediaClick(this); return false;">Select</button>
						</div>
					</div>
					<div id="media_type_div_collapsed" style="display:none; width:100%;">
						<span style="font-weight:bold">Selected Card Type:</span> <span id="selected_media_desc"></span>&nbsp;<a href="javascript:modifyMedia();" class="button">Modify</a><br/><br/>
					</div>

					<br/>

					<!-- Physical Design Selection -->
					<div id="phys_design_type_div_expanded" style="display:none; width:100%; margin-top: 12px;">
						<?php if (!empty($this->card_designs))
						{
							$designCount = 0;
							?>
							<span style="font-weight:bold">Select a design:</span><span style="color:#628F62; font-weight:bold;" id="expanded_phys_design_message"></span><br/><br/>
							<table width="100%" cellpadding="5">

								<?php
								foreach ($this->card_designs as $id => $design_data)
								{
									if ($design_data['supports_physical'])
									{
										if ($designCount % 2 == 0)
										{
											echo '<tr>';
										}
										?>
										<td id="td_<?php echo $id ?>" style="text-align:center">
											<img id="di_<?php echo $id ?>" onclick="designClick(this);" src="<?php echo ADMIN_IMAGES_PATH ?>/gift_card/<?php echo $design_data['image_path'] ?>"/>

											<br/> <label for="cd_<?php echo $id ?>"><?php echo $design_data['title'] ?></label><br/>
											<button id="cd_<?php echo $id ?>" class="button" onClick="designClick(this); return false;">Select</button>


										</td>

										<?php
										$designCount++;
										if ($designCount % 2 == 0)
										{
											echo '</tr>';
										}
									}
								} ?>
							</table>
						<?php } ?>

					</div>
					<!-- Virtual Design Selection -->
					<div id="virt_design_type_div_expanded" style="display:none; width:100%; margin-top:12px;">
						<?php if (!empty($this->card_designs))
						{
							$designCount = 0;
							?>
							<span style="font-weight:bold">Select a design:</span><span style="color:#628F62; font-weight:bold;" id="expanded_virt_design_message"></span><br/><br/>
							<table width="100%" cellpadding="5">

								<?php
								foreach ($this->card_designs as $id => $design_data)
								{
									if ($design_data['supports_virtual'])
									{
										if ($designCount % 2 == 0)
										{
											echo '<tr >';
										}
										?>
										<td id="tdv_<?php echo $id ?>" style="text-align:center;">
											<img id="di_<?php echo $id ?>" onclick="designClick(this);" src="<?php echo ADMIN_IMAGES_PATH ?>/gift_card/<?php echo $design_data['image_path_virtual'] ?>"/>

											<br/><label for="cd_<?php echo $id ?>"><?php echo $design_data['title'] ?></label><br/>
											<button id="cd_<?php echo $id ?>" class="button" onClick="designClick(this); return false;">Select</button>


										</td>

										<?php

										$designCount++;
										if ($designCount % 2 == 0)
										{
											echo '</tr>';
										}
									}
								} ?>
							</table>
						<?php } ?>

					</div>

					<div id="design_type_div_collapsed" style="display:none; width:100%;">
						<span style="font-weight:bold">Selected Card Design:</span> <span id="selected_design_desc"></span>&nbsp;<a href="javascript:modifyDesign();" class="button">Modify</a>
						<br/><img id="selectedDesignImg" style="height: 40px;"/>
						<span id="selected_design_desc"></span>

					</div>
				</td>
			</tr>
		</table>
		<!-- END Design Selection -->


		<div style="margin-top:12px; display:none;" id="card_details_form">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" style="background-color: #E5E5E5;">
				<tr style="background-color: #f8f7f5;">
					<td colspan="3">
					</td>
				</tr>
				<tr>
					<td colspan="3" style="background-color: #D4D4D4;"><strong>Gift Card Details</strong> (*=Required)</td>
				</tr>

				<tr id="gift_card_amount">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="amount_lbl" for="amount" message="Please enter an amount.">*Amount to Load:</label>&nbsp;$</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['amount_html']; ?>&nbsp;<span class="form_explain">(numbers only, 25 to 500 dollars)</span><br/>
					</td>
				</tr>

				<tr id="tr_to_name">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="to_name_lbl" for="amount" message="Please enter a to name.">*To name:</label></td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['to_name_html']; ?>&nbsp;
					</td>
				</tr>

				<tr id="tr_from_name">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="from_name_lbl" for="amount" message="Please enter a from name.">*From Name:</label></td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['from_name_html']; ?>&nbsp;
					</td>
				</tr>


				<tr id="tr_message">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="message_lbl" for="amount" message="Please enter an amount.">Message:</label></td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['message_html']; ?>
					</td>
				</tr>

			</table>
		</div>
		<div id="physical_card_form" style="display:none">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" style="background-color: #E5E5E5;">
				<tr>
					<td colspan="3" style="background-color: #D4D4D4;"><strong>Gift Card Shipping Address</strong> (*=Required)</td>
				</tr>

				<tr id="shipping_first_name_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_first_name_lbl" for="shipping_first_name" message="Please enter the First Name of recipient.">*Shipping First
																																													Name:</label>&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_first_name_html']; ?><br/>
					</td>
				</tr>
				<tr id="shipping_last_name_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_last_name_lbl" for="shipping_last_name" message="Please enter the Last Name of recipient.">*Shipping Last
																																												 Name:</label>&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_last_name_html']; ?><br/>
					</td>
				</tr>
				<tr id="shipping_address_1_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_address_1_lbl" for="shipping_address_1" message="Please enter the Address Line 1 of recipient.">*Shipping Address Line
																																													  1:</label>&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_address_1_html']; ?><br/>
					</td>
				</tr>
				<tr id="shipping_address_2_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle">Shipping Address Line 2:&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_address_2_html']; ?><br/>
					</td>
				</tr>
				<tr id="shippging_city_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_city_lbl" for="shipping_city" message="Please enter the City of recipient.">*Shipping City:</label>&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_city_html']; ?><br/>
					</td>
				</tr>
				<tr id="shipping_state_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_state_lbl" for="shipping_state" message="Please enter the State of recipient.">*Shipping State:</label>&nbsp;&nbsp;
					</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_state_html']; ?><br/>
					</td>
				</tr>
				<tr id="shipping_zip_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="shipping_zip_lbl" for="shipping_zip" message="Please enter the Zip Code of recipient.">*Shipping Zip:</label>&nbsp;&nbsp;</td>
					<td width="300" align="left" valign="middle"><?php echo $this->form_account['shipping_zip_html']; ?>&nbsp;<span class="form_explain">(5 digits only)</span><br/>
					</td>
				</tr>

			</table>
		</div>
		<div id="virtual_card_form" style="display:none">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" style="background-color: #E5E5E5;">
				<tr>
					<td colspan="3" style="background-color: #D4D4D4;"><strong>eGift Card Recipient</strong> (*=Required)</td>
				</tr>
				<tr>
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="recipient_email_lbl" for="recipient_email" message="Please enter the recipient&rsquo;s email address.">*Email Address:</label>&nbsp;&nbsp;
					</td>
					<td width="300" valign="middle"><?php echo $this->form_account['recipient_email_html']; ?><br/>
					</td>
				</tr>
				<tr>
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="confirm_recipient_email_lbl" for="confirm_recipient_email" message="Please confirm the email address.">*Confirm Email
																																													Address:</label>&nbsp;&nbsp;</td>
					<td width="300" valign="middle"><?php echo $this->form_account['confirm_recipient_email_html']; ?><br/>
					</td>
			</table>
		</div>
		<div id="payment_form_div" style="display:none">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" style="background-color: #E5E5E5;">
				<tr align="left" valign="middle">
					<td width="564" colspan="4" style="background-color: #D4D4D4;"><strong>Payment Details&nbsp;&nbsp;
							<span style="display:none" id="use_shipping"> (<?php echo $this->form_account['sameInfo_html']; ?>&nbsp;Use Shipping address as billing address)</span>
					</td>
				</tr>
				<tr id="email_address">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="primary_email_lbl" for="primary_email">Email Address:</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['primary_email_html']; ?><br/>
					</td>
				</tr>
				<tr id="confirm_email_address">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="confirm_email_address_lbl" for="confirm_email_address" message="Email addresses do not match">Confirm Email Address:</label>&nbsp;&nbsp;
					</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['confirm_email_address_html']; ?><br/>
					</td>
				</tr>
				<tr id="billing_name_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="billing_name_lbl" for="billing_name" message="Please enter the name on the Credit Card ">*Name on Credit Card:</label>&nbsp;&nbsp;
					</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['billing_name_html']; ?><br/>
					</td>
				</tr>
				<tr id="billing_address_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="billing_address_lbl" for="billing address" message="Please enter the Billing Address">*Billing Address (street
																																								   address):</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['billing_address_html']; ?><br/>
					</td>
				</tr>
				<tr id="billing_address_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="billing_city_lbl" for="billing_city" message="Please enter the Billing City">*Billing City:</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['billing_city_html']; ?><br/>
					</td>
				</tr>
				<tr id="billing_address_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="billing_state_lbl" for="billing_state" message="Please enter the Billing State">*Billing State:</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['billing_state_id_html']; ?><br/>
					</td>
				</tr>

				<tr id="billing_zip_tr">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="billing_zip_lbl" for="billing_zip" message="Please enter a billing zip cpde.">*Billing Zip:</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['billing_zip_html']; ?>&nbsp;<span class="form_explain"></span><br/>
					</td>
				</tr>
				<tr id="ccNumber">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="credit_card_type_lbl" for="credit_card_type" message="Please enter a Credit Card Type">*Credit Card Type:</label>&nbsp;&nbsp;
					</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['credit_card_type_html']; ?>&nbsp;<br/>
					</td>
				</tr>
				<tr id="ccNumber">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="credit_card_number_lbl" for="credit_card_number" message="Please enter a Credit Card Number">*Credit Card Number:</label>&nbsp;&nbsp;
					</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['credit_card_number_html']; ?>&nbsp;<span class="form_explain">(No spaces or dashes)</span><br/>
					</td>
				</tr>
				<tr id="ccExp">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle">
						<label id="credit_card_exp_year_lbl" for="credit_card_exp_year" message="Please enter an expiration date year."></label>
						<label id="credit_card_exp_month_lbl" for="credit_card_exp_month" message="Please enter an expiration date month.">*Expiration Date(MM/YY):</label>&nbsp;&nbsp;
					</td>
					<td width="300" colspan="2" align="left" valign="middle">
						<div class="row">
							<div class="col-6">
								<?php echo $this->form_account['credit_card_exp_month_html']; ?>
							</div>
							<div class="col-6">
								<?php echo $this->form_account['credit_card_exp_year_html']; ?>
							</div>
						</div>
					</td>
				</tr>
				<tr id="ccCVV">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="middle"><label id="credit_card_cvv_lbl" for="credit_card_cvv" message="Please enter a CVV number.">*CVV number:</label>&nbsp;&nbsp;</td>
					<td width="300" colspan="2" align="left" valign="middle"><?php echo $this->form_account['credit_card_cvv_html']; ?>
						&nbsp;<a href="http://en.wikipedia.org/wiki/Card_Verification_Value" target="_blank"><span class="form_explain">(What is this?)</span></a><br/>
					</td>
				</tr>
				<tr>
					<td width="64">&nbsp;</td>
					<td width="500" colspan="3" align="center" valign="top"><span style="display:none" id="s_and_h_note">Note: $2.00 shipping/service fee will be added to the "Gift Card Amount" you entered above.</span>
					</td>
				</tr>
				<tr id="submit">
					<td width="64">&nbsp;</td>
					<td width="200" align="right" valign="top">&nbsp;&nbsp;</td>
					<td width="300"><input id="procCardOrderBtn" name="submit_gc_order" type="submit" value="Process Card Order"/>

					</td>
				</tr>
			</table>
		</div>

	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>