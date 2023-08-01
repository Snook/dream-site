<html>
<head>
	<style type="text/css"><?php include $this->loadTemplate('email/css/style_dream_taste.css'); ?></style>
</head>
<body>
<table width="650"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="350" align="left" style="padding: 5px"><img src="<?=EMAIL_IMAGES_PATH?>/email/style/dream_dinners_grey_325x75.png" alt="Dream Dinners" width="325" height="75"></td>
		<td width="300" align="right" style="padding: 5px"><a href="<?=HTTPS_BASE ?>main.php?page=my_account">My Account</a> | <a href="<?=HTTPS_BASE ?>main.php?static=how_it_works">How It Works</a></td>
	</tr>
	<tr bgcolor="#5c6670">
		<td colspan="2" style="padding: 5px"><p align="center"><span style="color:#FFF; font-size:14pt; font-weight:bold;">Event Reminder</span></p></td>
	</tr>
</table>
<table width="650"  border="0" cellspacing="0" cellpadding="8">
	<tr>
	  <td><p>We're thrilled you'll be joining us for a Dream Dinners event. Be ready to have a great time, taste some delicious dinners and learn how Dream Dinners can be the solution to your dinnertime challenges!
			</p>
			<p><strong>Things to know:</strong></p>
			<ul>
				<li>All recipes and their ingredients will be waiting for you when you arrive.</li>
				<li><strong>Please bring a small cooler or box to transport your dinner home.</strong></li>
				<li>So that we can all start together, we encourage you to arrive on time.</li>
			</ul>
		<p>If you have any questions regarding this or any other Dream Dinners information please contact the store.</p>
		  <p>We look forward to meeting you!</p>
			<hr /></td>
	</tr>
    <tr>
        <td>
        <p><strong>Event Details</strong></p>
        <ul>
          <li>
            Time:
            <?=$this->dateTimeFormat($this->session_info->session_start, VERBOSE) . "\n";?>
          </li>
          <li>Location:
  <?=$this->store_info->store_name . "\n";?>
          </li>
          <li>Address:
  <?=$this->store_info->address_line1?>
            <?=!empty($this->store_info->address_line2) ? $this->store_info->address_line2 . "<br />" : ""?>
            <?=$this->store_info->city?>
            <?=$this->store_info->state_id?>
            <?=$this->store_info->postal_code?>
          </li>
          <li>Phone: <?=$this->store_info->telephone_day . "\n";?>
          </li>
        </ul></td>
    </tr>

</table>

</body>
</html>