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
  <td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:16pt; font-weight:bold;">Request for Account Closure</span></p></td>
</tr>
</table>
<table role="presentation" width="600"  border="0" cellspacing="0" cellpadding="10">
<tr>
<td><p>Hello,</p>
<p>One of your guests has requested that their account be closed and we do not contact them moving forward.</p>
<p>Guest Details:
</p>
<ul>
	<li>Guest ID: <?php echo $this->user->id; ?></li>
	<li>Name: <?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></li>
	<li>Email: <?php echo $this->user->primary_email; ?></li>
	<li>Mobile Phone Number: <?php echo $this->mobile; ?></li>
</ul>
<p>Your store needs to take the following actions to remove their contact information from 3rd party platforms, to ensure you do not contact them again:</p>
<ul>
	<li>Remove the guest from SMS/MMS Texting applications</li>
	<li>Unsubscribe the guest from Vertical Response</li>
	<li>Remove them from your store mailbox, employees mailbox, and your own accounts Outlook contacts if you have received or sent them emails from those accounts.</li>
	<li>Remove them from any email lists stored on your devices – such as in Excel files.</li>
	<li>Remove them from any phone contacts if you have them stored in a phone address book.</li>
</ul>
<p>Home office will make their account inactive in BackOffice and Salesforce. Please reach out to support if you have any questions or concerns.</p>
</td>
</tr>
</table>

</body>
</html>













