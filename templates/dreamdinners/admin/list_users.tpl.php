<?php $this->assign('page_title','Guest Search'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/list_users.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/card_track.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setOnload('list_users_init();'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<?php $storeFilter = (isset($this->form_list_users['store'])) ? '&store='.$this->form_list_users['store'] : ''; ?>

<p>This area is designed for you to view guests who have signed up at your store. Below you can select to view all guests starting with the desired letter you choose, or you can enter the Guest's ID into the Guest's ID search box.</p>
<p>Once you have located a customer, click on their name or ID to view all of their information, email the guest or set their preferred status for your stores.</p>

<form id="list_users_form" action="" method="get">
	<input type="hidden" name="page" value="admin_list_users" />

	<table style="width:100%;" id="lookupGuestTarget">
		<tr>
			<td class="bgcolor_dark catagory_row" colspan="2">Search Guests</td>
		</tr>
		<?php if ( isset($this->form_list_users['store_html'] ) ) { ?>
			<tr>
				<td class="bgcolor_light" style="text-align:right;">Filter by store</td>
				<td class="bgcolor_light">
					<?php echo $this->form_list_users['store_html']; ?>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td class="bgcolor_light" style="text-align:right;">Search By</td>
			<td class="bgcolor_light">
				<?php echo $this->form_list_users['search_type_html']; ?>
				<input type="hidden" name="letter_select" value="<?php echo $this->letter_select; ?>" />
				<input type="text" id="q" name="q" <?php echo (!empty($this->phone_mask_css)) ? 'class="telephone"' : '';?> value="<?php echo $this->q; ?>" />
				<input type="checkbox" name="all_stores" <?php if (isset($this->all_stores)) echo 'checked="checked"';?> />All Stores
				<input type="submit" class="button" value="Search" /><br />
				<div id="search_help" style="margin-left:4px;display:none;color:#009933;"></div>
			</td>
		</tr>
		<tr>
			<td class="bgcolor_light" style="text-align:right;">Alphabetical Last Name search</td>
			<td class="bgcolor_light">
				<?php foreach(range('A', 'Z') as $letter) { ?>
					<a class="button" href="?page=admin_list_users<?=$storeFilter ?>&amp;letter_select=<?php echo $letter; ?>"><?php echo $letter; ?></a> <?php echo ($letter == 'M') ? '<a class="button" href="?page=admin_list_users' . $storeFilter . '&amp;letter_select=etc">Etc</a><br />' : ''; ?>
				<?php } ?>
				<a class="button" href="?page=admin_list_users<?=$storeFilter ?>&amp;letter_select=all">View All</a>
			</td>
		</tr>
		<tr>
			<td class="bgcolor_light" style="padding:6px;white-space:nowrap;text-align:right;vertical-align:top;font-weight:bold;"><input type="button" class="button" style="cursor:pointer;" value="Swipe Credit Card" onclick="document.getElementById('search_type').value='lastname'; prepareForCCSwipe();" /></td>
			<td class="bgcolor_light">
				<div id="scanArea" style="display:none;border:#93C89F 2px solid;padding:8px;text-align:left;">
					<span>Please swipe the credit card.</span><br />
					<textarea  onblur="endScanHandling();" id="hidden_text_store" name="hidden_text_store"  cols="50" rows="1" ></textarea>
				</div>
			</td>
		</tr>
	</table>

</form>

<br />

<div class="table-responsive">
	<table class="table table-sm table-striped" style="width:100%;">
		<thead>
		<tr>
			<?php if (($this->rowcount !== null) && $this->rows) {	?>
				<th colspan="2" class="bgcolor_dark catagory_row">
					<span style="color:<?echo ($this->rowcount == 0) ? 'red' : '#000'; ?>;"> <?php echo $this->rowcount; ?></span> <?php echo ($this->show_todays_guests) ? 'Guests Today' : 'Search Results';?>
				</th>
			<?php } ?>
			<th colspan="9" class="bgcolor_dark catagory_row" style="text-align:right;">
				<?php if (isset($this->store) && $this->can_export) { ?>
					<?php $exportAllLink = '?page=admin_list_users&amp;store=' . $this->store .  '&amp;letter_select=all&amp;export=xlsx'; ?>
					<?php include $this->loadTemplate('admin/export.tpl.php'); ?>
				<?php } ?>
			</th>
		</tr>
		<?php if ($this->rowcount && $this->rows) { ?>
		<tr>
			<th class="bgcolor_medium header_row">First Name</th>
			<th class="bgcolor_medium header_row">Last Name</th>
			<th class="bgcolor_medium header_row">Primary Email</th>
			<?php if ($this->support_corporate_crate_search) { ?>
				<th class="bgcolor_medium header_row">Secondary Email</th>
			<?php } ?>
			<th class="bgcolor_medium header_row">Telephone (Day)</th>
			<th colspan="3" class="bgcolor_medium header_row">Actions</th>
			<th colspan="2" class="bgcolor_medium header_row">Place Order</th>
			<th class="bgcolor_medium header_row">Guest ID</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$counter = 0;
		while( $this->rows->fetch() )
		{
			?>
			<tr class="bgcolor_<?php echo ($counter++ % 2 == 0) ? 'light' : 'lighter'; ?>">
				<td style="white-space:nowrap;"><a href="?page=admin_user_details&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->rows->firstname; ?></a></td>
				<td style="white-space:nowrap;"><a href="?page=admin_user_details&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->rows->lastname; ?></a></td>
				<td style="white-space:nowrap;"><a href="?page=admin_email&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->rows->primary_email; ?></a></td>
				<?php if ($this->support_corporate_crate_search) { ?>
					<td style="white-space:nowrap;"><a href="?page=admin_email&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->rows->secondary_email; ?></a></td>
				<?php } ?>
				<td style="white-space:nowrap;text-align:center;"><?php echo $this->telephoneFormat($this->rows->telephone_1); ?></td>
				<td style="white-space:nowrap;text-align:center;"><a class="button" href="?page=admin_user_details&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">View Acct</a></td>
				<td style="white-space:nowrap;text-align:center;"><a class="button" data-tooltip="<?php echo addToolTip($this->rows->id); ?>"  href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;edit_last_for=<?php echo $this->rows->id; ?>">Edit Last Order</a></td>
				<td style="white-space:nowrap;text-align:center;"><a class="button" href="?page=admin_order_history&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Orders</td>
				<?php if ($this->canPlaceOrder == true && ($this->rows->is_partial_account === "0" || $this->rows->is_partial_account === 0)) { ?>
					<td style="white-space:nowrap;text-align:center;"><a class="button" href="?page=admin_order_mgr&amp;user=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->thisMonthStr; ?></a></td>
					<td style="white-space:nowrap;text-align:center;"><a class="button" href="?page=admin_order_mgr&amp;user=<?php echo $this->rows->id; ?>&amp;month=<?php echo $this->nextMonthTimestamp; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->nextMonthStr; ?></a></td>
				<?php } else { ?>
					<td style="white-space:nowrap;text-align:center;" colspan="2"><a class="button" href="?page=admin_account&amp;upgrade=true&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Upgrade Account</a></td>
				<?php } ?>
				<td style="white-space:nowrap;text-align:right;"><a href="?page=admin_user_details&amp;id=<?php echo $this->rows->id; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><?php echo $this->rows->id; ?></a></td>
			</tr>
		<?php } ?>
		<?php } ?>
		</tbody>
	</table>
</div>

<?php
function addToolTip($user_id){
	$User = DAO_CFactory::create('user');

	$sql = "SELECT s.session_start, orders.timestamp_created, orders.id
				FROM `session` AS `s`
				INNER JOIN `booking` ON `s`.`id` = `booking`.`session_id` and `booking`.`is_deleted` = 0
				INNER JOIN `user` ON `booking`.`user_id` = `user`.`id` and `user`.`is_deleted` = 0
				INNER JOIN `orders` ON `booking`.`order_id` = `orders`.`id` and `orders`.`is_deleted` = 0

				WHERE booking.is_deleted = 0
					AND `booking`.status = 'ACTIVE'
					AND user.id = " . $user_id . "
					order by session_start desc
				limit 1";
	$User->query($sql);
	//$User->find();
	$order_id = '';
	while ($User->fetch())
	{
		$session_time = CTemplate::dateTimeFormat($User->session_start, NORMAL);
		$order_date = CTemplate::dateTimeFormat($User->timestamp_created, MONTH_DAY_YEAR);
		$order_id= $User->id;
	}

	return "<ul style='margin: 8px;'><li>Order Number: <br>&nbsp;&nbsp;  <span style='color: white;'>".$order_id."</span>
		</i><br><li>Ordered Date: <br>&nbsp;&nbsp;  <span style='color: white;'>".$order_date."</span></i>
		<br><li style='white-space:nowrap;margin-right: 35px;'>Session Time: <br>&nbsp;&nbsp;<span style='color: white;'> ".$session_time."</span></i></ul>";
}
?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>