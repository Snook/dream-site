<?php $this->assign('page_title','Load Gift Card'); ?>
<?php $this->assign('helpLinkSection','GC_LOAD'); ?>
<?php $this->assign('topnav', 'giftcards'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>



<table style="width:700px;" align="center">
<?php if (!isset($this->print_view) || !$this->print_view) { ?>
	<tr>
		<td align="right"><img
			src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" border="0">&nbsp;&nbsp;<a href="<?php echo $_SERVER['REQUEST_URI']?>&print=true" target="_blank" >Print Customer Receipt</a></td>
	</tr>
<?php } ?>
	<tr>
		<td><span class="largepageheader">Dream Dinners Gift Card Receipt</span><br />
			<strong>This receipt is your &quot;Proof of Purchase&quot;</strong>.
			Please print this receipt.</td>
	</tr>
	</table>

<table width="700" align="center"
	style="border: thin solid #666666; background-color: #F1F1F1;">
	<tr>
		<td><table width="100%" align="center" cellpadding="2">
				<tr style="background-color: #dddddd;">
					<td colspan="2"><strong>Gift Card Details</strong></td>
				</tr>
				<tr>
					<td width="50%" align="right" valign="middle">Amount Loaded on
						Card:</td>
					<td width="50%" align="left" valign="middle">$<?php echo $this->moneyFormat($this->purchase_data['amount']); ?>
					</td>
				</tr>
				<tr>
					<td width="50%" align="right" valign="middle">Gift Card Number:</td>
					<td width="50%" align="left" valign="middle"><?php echo str_repeat('X', (strlen($this->purchase_data['gift_card_number']) - 4)) . substr($this->purchase_data['gift_card_number'], -4)?>
					</td>
				</tr>
				<tr>
					<td width="50%" align="right" valign="middle">Date of Purchase:</td>
					<td width="50%" align="left" valign="middle"><?php echo CTemplate::dateTimeFormat($this->purchase_data['date']);?>
					</td>
				</tr>
			</table>
			<table border="0" cellspacing="0" cellpadding="2"
				style="width: 100%;">
				<tr>
					<td style="background-color: #dddddd;" width="50%" align="left"
						valign="top"><strong>Payment Details</strong></td>
					<td style="background-color: #dddddd;" width="50%" align="left"
						valign="top">&nbsp;</td>
				</tr>
				<tr>
					<td width="40%" align="left" valign="top">Card Type:&nbsp; <?=$this->purchase_data['credit_card_type'];?>
						<br /> Last 4 digits:&nbsp; <?=substr($this->purchase_data['credit_card_number'], strlen($this->purchase_data['credit_card_number'])-4, strlen($this->purchase_data['credit_card_number']))?>
						<br /> Amount:&nbsp;$ <?php echo $this->moneyFormat($this->purchase_data['amount']);?>
						<br /> Payment Date:&nbsp; <?= CTemplate::dateTimeFormat($this->purchase_data['date']);?>
					</td>
					<td width="60%" align="left" valign="top">Billing Name: <?php echo $this->purchase_data['billing_name']?><br />
						Billing Address: <?php echo $this->purchase_data['billing_address']?>,
						<?=$this->purchase_data['billing_zip']?><br /> Billing Email: <?php if (!empty($this->purchase_data['primary_email'])) {
							echo $this->purchase_data['primary_email'];
						} else { echo "No email address provided";
} ?>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="left" valign="top"><br /> <b>Shipping
							Details:</b> Allow 2-6 business days for shipped card, emailed
						eGift Cards are sent instantly.</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table style="width:700px;" align="center">
 <tr><td>
	To view the complete Gift Card Policies &amp; Terms online or if you
	have questions regarding your Gift Card or Customer Service options
	please visit <a href="/giftcards">gift cards</a>.

</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>