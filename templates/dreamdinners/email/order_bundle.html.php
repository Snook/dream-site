<html lang="en">
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style.css'); ?></style>
</head>
<body>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?static=how_it_works">How It Works</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Meal Prep Starter Pack - Order Confirmation</span></p></td>
	</tr>
</table>
<table role="presentation" width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
		<td><p>Thank you for placing your first order with Dream Dinners.  Our guests say Dream Dinners makes them feel &quot;prepared, empowered, less stressed, and like a Rock Star Chef!&quot; We're happy to help you in the kitchen, so your family can enjoy homemade meals together.</p>
			<p>Things to know:</p>
			<ul>
				<li>All recipes and their ingredients  will be waiting for you when you arrive.</li>
				<li>We also provide baking pans and freezer bags for packaging your dinners. </li>
				<li><strong>Please bring a cooler or box to transport your dinner's home.</strong></li>
				<li>So that we can all start together, we encourage you to arrive on time.</li>
				<li>Our stores tailor each session to accommodate your order. If you have any special needs, please let us know  ahead of time.</li>
				<li>For your safety, as well as the safety of the little ones, we ask that no one under the age of 12 attend. </li>
				<li>Space is sometimes limited, if you intend to bring someone to assist you, please let us know in advance.</li>
			</ul>
			<p>Below is your order summary and payment information.  If you have any
			   questions regarding this or any other Dream Dinners information please
			   contact your store.</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
</table>
<?php include $this->loadTemplate('email/subtemplate/order_details/order_details_email.tpl.php'); ?>

</body>
</html>
