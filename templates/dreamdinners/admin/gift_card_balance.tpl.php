<?php $this->assign('page_title', 'Gift Card Balance Inquiry'); ?>
<?php $this->assign('topnav', 'giftcards'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<script type="text/javascript">
		var codes = '';
		var i = 0;
		var x = 0;
		var browser = navigator.appName;
		function keyHandler(evt)
		{
			if (browser == "Microsoft Internet Explorer")
			{
				var charCode = (document.all) ? event.keyCode : evt.keyCode;
			}
			else
			{
				var charCode = evt.keyCode ? evt.keyCode : evt.charCode;
			}
			if (charCode == 13)
			{
				return false;
			}

			if (charCode == 94)
			{
				if (codes.length >= 15)
				{
					document.getElementById('gift_card_number').value = codes;

				}
				else
				{
					codes = '';
					i = 0;
				}
				return false;
			}
			if (codes.length == 15)
			{
				//document.getElementById('gift_card_number').value=codes;
				return false;
			}
			if ((String.fromCharCode(charCode) == '%') && (i == 0))
			{
				i = 1;
			}
			else if ((String.fromCharCode(charCode) == 'B') && (i == 1))
			{
				i = 2;
			}
			else if (i == 2 && (parseInt(charCode) > 57 || parseInt(charCode) < 48) && charCode != 94 && charCode != 8)
			{
				//document.getElementById('gift_card_number').value=codes;
				return false;
			}
			else if ((parseInt(charCode) > 57 || parseInt(charCode) < 48) && i < 2 && charCode != 94 && (String.fromCharCode(charCode)) != 'B' && charCode != 8)
			{
				//document.getElementById('gift_card_number').value=codes;
				return false;
			}

			if ((String.fromCharCode(charCode) != 'B') && (i == 2))
			{
				if ((String.fromCharCode(charCode) != '^'))
				{
					codes += String.fromCharCode(charCode);
				}
			}

		}
		document.onkeypress = keyHandler;

	</script>

	<form name="gift_card_balance" onSubmit="return _check_form(this);" action="main.php?page=admin_gift_card_balance" method="post">
		<?php if (isset($this->form_account['hidden_html'])) { echo $this->form_account['hidden_html']; } ?>

		<table width="100%">

			<?php if (empty($this->response) && empty($this->print_view)) { ?>

			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><img src="<?php echo ADMIN_IMAGES_PATH ?>/gift_card/2010_card_designs_stckd_sm.gif" hspace="8" vspace="0" border="0" align="right">Enter a Gift Card number, then click the Check
					Balance button to get the current balance on your Gift Card account. This balance inquiry form works with both Dream Dinners traditional gift cards and eGift cards. <br/>
					<br/>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<table>
						<tr align="left">
							<td colspan="3" style="font-weight: bold;">Balance Inquiry</td>
						</tr>
						<tr>
							<td width="200" align="right"><label id="gift_card_number_lbl" for="gift_card_number" message="Please enter a gift card number.">Gift Card Number:</label></td>
							<td width="150"><?php echo $this->form_account['gift_card_number_html']; ?></td>
							<td width="170">
								<a href="javascript:codes='';i=0;document.getElementById('gift_card_number').value = ''; document.getElementById('gift_card_number').focus();" class="button">Activate
									card swipe</a></td>
						</tr>
						<tr id="submit">
							<td></td>
							<td colspan="2"><?php echo $this->form_account['balance_submit_html']; ?></td>
						</tr>
						<tr>
							<td colspan="3"><p><br/>To view the complete Gift Card Policies &amp; Terms online or if you have questions regarding your Gift Card or Customer Service options please
									visit <a href="main.php?static=giftcards">gift cards</a>. For 24/7 Automated Cardholder Balance information: 1-360-804-2020.</p></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<?php } else { ?>
		<table width="100%">
			<?php if (empty($this->print_view)) { ?>
			<tr>
				<td align="right">
					<img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" border="0">&nbsp;&nbsp;<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&print=true&cn=<?php echo $this->card_number; ?>&r=<?php echo $this->response; ?>>" target="_blank">Print Balance</a></td>
			</tr>
			<?php } ?>
		</table>
		<table width="100%">
			<tr>
				<td colspan="3" style="font-weight: bold;">Dream Dinners Gift Card Balance Inquiry </td>
			</tr>
			<tr id="gift_card_number">
				<td width="30">&nbsp;</td>
				<td width="170" align="right" valign="middle">Gift Card Number:&nbsp;</td><td width="300" valign="middle"><?php echo $this->card_number; ?></td>
			</tr>
			<tr id="email_address_tr">
				<td width="30">&nbsp;</td>
				<td width="170"  align="right" valign="middle">Balance:</td>
				<td width="300"  align="left" valign="middle"><?php echo $this->response; ?></td>
			</tr>
			<tr>
				<td colspan="3">To view the complete Gift Card Policies &amp; Terms online or if you have questions regarding your Gift Card or Customer Service options please visit <a href="main.php?static=giftcards">gift cards</a>. For 24/7 Automated Cardholder Balance information: 1-360-804-2020.</td>
			</tr>
		</table>
		</td>
		</tr>
	</table>
	<?php } ?>
	</form>

	<script type="text/javascript">
		document.getElementById('gift_card_number').focus();
	</script>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>