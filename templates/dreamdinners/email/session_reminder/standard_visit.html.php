<p><b>For Your Upcoming Session, Remember:</b></p>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
	<td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/cooler-icon.gif" width="70" height="67" /></td>
	<td width="85%">Bring your cooler to easily transport your meals home.</td>
</tr>
<tr>
	<td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/phone-icon.gif" width="70" height="67" /></td>
	<td width="85%">Let us know how we can serve you. <a href="<?=HTTPS_BASE?>main.php?page=locations&store_id=<?=$this->store_id?>">Contact us if you need help</a> with order changes, special food accommodations, or need to make other arrangements.</td>
</tr>
<tr>
	<td width="15%" align="right"><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/menu-icon.gif" width="70" height="67" /></td>
	<td width="85%">Plan to order in-store for your next session. Save time by <a href="<?=HTTPS_BASE?>main.php?page=session_menu">ordering online or just take a peek at next month's menu</a> before you arrive and you'll be all set to order when you come in. Plus, you earn bonus PLATEPOINTS each time you sign up in-store or online prior to your next session.</td>
</tr>
</table>

<?php if (!empty($this->user_favorite)) { ?>
<p><b>Favorites</b><br />
	Next month's menu features some family favorites including:
</p>
<ul>
	<?php foreach ($this->user_favorite AS $favorite) { ?>
	<li><?php echo $favorite['recipe_name']; ?>, <?php echo (!empty($favorite['user_favorite']) ? 'your favorite!' : 'guest favorite!'); ?></li>
	<?php } ?>
</ul>
<?php } ?>

<?php
if ($this->bookings_made == 2)
{
	include $this->loadTemplate('email/session_reminder/standard_visit_rate_meals.html.php');
}
else if ($this->bookings_made == 3)
{
	include $this->loadTemplate('email/session_reminder/standard_visit_finishing_touch.html.php');
}
else if ($this->bookings_made == 4)
{
	include $this->loadTemplate('email/session_reminder/standard_visit_made_for_you.html.php');
}
else
{
	include $this->loadTemplate('email/session_reminder/standard_visit_monthly.html.php');
}
?>
