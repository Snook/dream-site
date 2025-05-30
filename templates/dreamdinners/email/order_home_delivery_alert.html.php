<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px">&nbsp;</td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Order Home Delivery</span></p></td>
	</tr>
</table>
<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p class="sectionhead"><b>Customer Information</b></p>
			Name:&nbsp;<?php echo $this->customer_name; ?><br />
			Email:&nbsp;<?php echo $this->customer_primary_email; ?>

			<p class="sectionhead"><b>Delivery Address</b></p>
			<p>
				<?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?><br />
				<?php echo $this->orderInfo['orderAddress']['telephone_1']; ?>
			</p>
			<p>
				<?php echo $this->orderInfo['orderAddress']['address_line1']; ?><br />
				<?php if (!empty($this->orderInfo['orderAddress']['address_line2'])) { ?>
					<?php echo $this->orderInfo['orderAddress']['address_line2']; ?><br />
				<?php } ?>
				<?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?>
			</p>
			<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
				<p>Address Note: <?php echo $this->orderInfo['orderAddress']['address_note']; ?></p>
			<?php } ?>
			<p>&nbsp; </p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;">
		</td>
	</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>