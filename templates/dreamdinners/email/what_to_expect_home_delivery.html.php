<html lang="en">
<head>
</head>
<body>
<table role="presentation" width="600" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" style="padding: 10px"><a href="<?php echo HTTPS_SERVER; ?>"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dream_dinners_logotype_darkgrey_300x28.png" alt="Dream Dinners" width="300" height="28"></a></td>
	</tr>
</table>
<table role="presentation" width="600" align="center">
	<tr>
		<td><img src="<?php echo EMAIL_IMAGES_PATH?>/email/misc/in-a-snap-chicken-tenders-collage-600x300.jpg" width="600" height="300" alt="Dream Dinners"></td>
	</tr>
</table>
<table role="presentation" width="600" border="0" align="center" cellpadding="20" cellspacing="0">
	<tr>
		<td><p style="font-family:Arial, Helvetica, sans-serif; font-size: 24px; color: #444444;"><strong>What to Expect</strong></p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">Your Dream Dinners will be delivered soon! We are so excited to help you with ready-to-cook meals that are easy and delicious. Here is what to expect when they arrive.</p>
			<ul style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">
				<li>Your meals will be delivered on <b><?php echo $this->DAO_booking->DAO_session->sessionStartDateTime()->format("F j, Y - g:i A"); ?> to <?php echo $this->DAO_booking->DAO_session->sessionEndDateTime()->format("g:i A"); ?></b>. You will be notified when they are on the way.</li>
				<li><strong>Once they arrive, place 3-4 meals in your fridge to thaw & enjoy this week.</strong> Add the extras to your freezer to enjoy next week. </li>
				<li><strong>Ready to eat?</strong> Use our quick  easy-to-follow cooking instructions including an estimated time-to-table to  help plan your busy nights.</li>
				<li><strong>Forget to thaw dinner for the night?</strong> Grab a cook from frozen option or use these quick thaw tips.
					<ul>
						<li>Submerge bag in cold tap water, changing the water every 30 min. Your meal should thaw in an hour or less.</li>
						<li>To speed things up, separate the bags of sauce and meat and place them directly in the cold water.</li>
						<li>Once thawed, cook the meal immediately.</li>
					</ul>
				</li>
				<li><strong>Looking to use the grill or your air fryer.</strong> Scan the QR code on the cooking instructions to see alternate cooking methods. </li>
			</ul>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">Enjoy your dinners with your family! When you need more meals, place another order at <a href="<?php echo $this->DAO_booking->DAO_store->getMenuURL(); ?>">DreamDinners.com</a>. Want more meals this month? You can order extras of your favorites, grab a few sides or sweets or try something new. Our menu changes monthly, with at least 17 delicious recipes to choose from.</p>
			<hr>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">If you have any questions, please reach out to us at <?php echo $this->DAO_booking->DAO_store->telephone_day; ?> or via email by replying.</p>
			<p style="font-family:Arial, Helvetica, sans-serif; font-size: 16px; color: #444444;">&nbsp; </p>
		</td>
	</tr>
</table>
</body>
</html>