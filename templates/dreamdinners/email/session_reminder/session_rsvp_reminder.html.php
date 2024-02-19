<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?php echo HTTPS_BASE; ?>my-account">My Account</a> | <a href="<?php echo HTTPS_BASE; ?>how_it_works">How It Works</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Event Reminder</span></p></td>
	</tr>
</table>
<table width="650" border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td>
			<p>We're thrilled you'll be joining us for a Dream Dinners event. Be ready to have a great time, taste some delicious dinners and learn how Dream Dinners can be the solution to your dinnertime challenges!
			</p>
			<p><strong>Things to know:</strong></p>
			<ul>
				<li>Please bring a small cooler or box to transport any meals you take home.</li>
				<li>So that we can all start together, we encourage you to arrive on time.</li>
			</ul>
			<p>If you have any questions regarding this or any other Dream Dinners information please contact the store.</p>
			<p>We look forward to meeting you!</p>
			<hr />
		</td>
	</tr>
	<tr>
		<td>
			<p><strong>Event Details</strong></p>
			<ul>
				<li>Time: <?php echo $this->DAO_session->sessionStartDateTime()->format("F j, Y - g:i A"); ?></li>
				<li>Location: <?php echo $this->DAO_store->store_name; ?></li>
				<li>Address: <?php echo $this->DAO_store->generateAddressHTML(); ?></li>
				<li>Phone: <?php echo $this->DAO_store->telephone_day; ?></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"><br/>
			<p><b>Not feeling well?</b><br/>
				If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call to reschedule your visit.</p>

			<p><a href="<?php echo HTTPS_BASE?>/locations/<?php echo $this->DAO_store->id?>">Contact your local store</a> | <a href="<?php echo HTTPS_BASE?>terms">View Terms and Conditions</a></p>
		</td>
	</tr>
</table>

</body>
</html>