<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<!-- Help character display properly. -->
	<meta charset="utf-8">
	<!-- Force some Outlook clients to render with a better MS engine. -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Set the initial scale of the email. -->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<!-- Help prevent blue links and autolinking if needed. -->
	<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
	<!-- Prevent Apple from reformatting and zooming messages. -->
	<meta name="x-apple-disable-message-reformatting">
	<!-- Use the title for assistive technology and when people open emails in a browser tab.  -->
	<title></title>
	<!-- Allow for better image rendering on Windows hi-DPI displays. -->
	<!--[if mso]>
	<xml>
		<o:OfficeDocumentSettings>
			<o:AllowPNG/>
			<o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
	</xml>
	<![endif]-->

	<style>
		/* Client-specific reset styles. */
		body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
		table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
		img { -ms-interpolation-mode: bicubic; }

		/* General reset styles. */
		img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
		table { border-collapse: collapse !important; }
		body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

		/* Prevent Apple blue links. */
		a[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
			}

		/* Prevent Gmail blue links. */
		u + #body a {
			color: inherit;
			text-decoration: none;
			font-size: inherit;
			font-family: inherit;
			font-weight: inherit;
			line-height: inherit;
			}

		/* Prevent Samsung blue links. */
		#MessageViewBody a {
			color: inherit;
			text-decoration: none;
			font-size: inherit;
			font-family: inherit;
			font-weight: inherit;
			line-height: inherit;
			}

		/* Provide default styles for links and hovers. */
		a { color: #959a21; font-weight: bold; text-decoration: underline; }
		a:hover { color: black; text-decoration: underline; }
		a.button:hover { background-color: black !important; }

		/* Responsive styles taking a desktop-first approach. */
		@media screen and (max-width: 600px) {
			.mobile { width: 100% !important; }
			}

		<?php include $this->loadTemplate('email/css/style_membership.css'); ?>



	</style>
</head>
<body id="body" style="margin: 0 !important; padding: 0 !important;">
<div role="article" aria-roledescription="email" aria-label="email name" lang="en" style="font-size:1rem">
	<div style="color: #333333; font-family: "Arial", serif; font-size: 16px; font-weight: normal; line-height: 1.4; margin: 1rem auto;">
	<table class="mobile" role="presentation" width="600"  border="0" cellspacing="0" cellpadding="0">
		<!-- Logo -->
		<tr>
			<td align="center" style="padding: 10px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/Meal-Prep-Plus-300x68.png" alt="Meal Prep+ Logo" width="300" height="68"></td>
		</tr>
		<tr>
			<td style="padding:20px 20px 10px 20px;">
				<p>Your Meal Prep+ membership has now ended. During your 6-month membership period, you saved $[membership savings] at Dream Dinners. Saving money and feeding your family with delicious prepped dinners, now that’s a win-win. </p>
				<p>Want to keep your Meal Prep+ perks? Contact your local store at <?php echo $this->store->telephone_day; ?> to renew your membership.</p>
		</tr>
		<tr>
			<td><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
		</tr>
		<tr>
			<td align="center"  style="padding:20px 20px 20px 20px;">
				<p>See the complete <a href="<?php echo HTTPS_SERVER; ?>/terms">Meal Prep+ terms and conditions here</a>.</p>
				<p align="center">
					Dream Dinners <?php echo $this->store->store_name; ?><br />
					<?php echo $this->store->address_line1; ?> <?php echo !empty( $this->store->address_line2 ) ? $this->store->address_line2 : '' ?>
					<?php echo $this->store->city; ?>, <?php echo $this->store->state_id; ?> <?php echo $this->store->postal_code; ?>
				</p>
			</td>
		</tr>
	</table>

</div>
</div>
</body>
</html>