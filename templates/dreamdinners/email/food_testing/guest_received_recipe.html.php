<html lang="english">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>

<table role="presentation" width="550" border="0" cellspacing="0" cellpadding="0" class="border">
	<tr>
		<td align="center"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
	</tr>
	<tr>
		<td style="padding:10px 15px 5px 15px;">
			<p><?php echo $this->firstname; ?>,</p>

			<p>Thank you for testing <?php echo $this->recipe_name; ?>, one of our new Dream Dinners recipes. The opinions of our guests are so important to us, especially when we are developing new
				recipes for the menu. Please prepare the dinner for your family and give us your honest feedback in your <a href="<?php echo HTTPS_BASE; ?>my-surveys">My Test Recipes & Surveys</a> section of your Dream Dinners account.</p>
			<p>On behalf of all of us, we'd like to thank you for using Dream Dinners. We look forward to seeing you soon.</p>
			<p>Sincerely,<br/>
				Dream Dinners<br/>
			</p></td>
	</tr>
</table>

</body>
</html>