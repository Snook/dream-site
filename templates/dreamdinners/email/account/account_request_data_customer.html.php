<html lang="en">
<head>
<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="600"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="350" align="center" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
</tr>
<tr bgcolor="#5c6670">
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:16pt; font-weight:bold;">Request for Information Confirmed</span></p></td>
</tr>
</table>
<table role="presentation" width="600"  border="0" cellspacing="0" cellpadding="10">
<tr>
<td><p>Dear <?php echo $this->user->firstname; ?>, </p>
      <p>We have received your request for a file that contains what categories of personal information have been collected, the purpose of collecting it, and what third parties we may have disclosed your personal information to over the last 12 months.	  </p>
      <p>We will advise you in our response if we are not able to honor your request. We will not provide account passwords, financial information, or any specific pieces of information if the disclosure presents the possibility of unauthorized access that could result in identity theft or fraud or unreasonable risk to data or systems and network security.</p>
	  <p><strong>You will receive an email with instructions on how to retrieve your file once it is ready.</strong></p>
	  <p>We will work to process all verified requests within 45 days pursuant to the CCPA. If we need an extension for up to an additional 45 days in order to process your request, we will provide you with an explanation for the delay.</p>
      <p>If you did not submit a request for your personal information and you think someone
		has accessed your account without your permission notify Dream Dinners support at
			<a href="mailto:support@dreamdinners.com">support@dreamdinners.com</a> right away.</p>

      <p>Sincerely,<br>
      Your Dream Dinners Team</p>
</td>
</tr>
</table>

</body>
</html>