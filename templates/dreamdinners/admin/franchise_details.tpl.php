<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/franchise_details.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/franchise_details.css'); ?>
<?php $this->assign('page_title','Franchise Details'); ?>
<?php $this->assign('topnav','store'); ?>
<?php $this->setOnLoad("franchise_details_init();"); ?>
<?php $this->setScriptVar('franchise_id = ' . $this->franchise['id'] . ';'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3>Entity Details</h3>

<form action="" method="post">

<table style="width: 100%; margin-bottom: 10px;">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Basic Information</td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;">Entity ID</td>
	<td class="bgcolor_light"><?php echo $this->franchise['id']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;">Entity/Owner</td>
	<td class="bgcolor_light"><input type="text" name="franchise_name" value="<?php echo htmlentities($this->franchise['franchise_name']); ?>" size="60" /></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;">Active:</td>
	<td class="bgcolor_light">
		<select name="active" <?php if (!$this->permission['can_set_active']) { ?>disabled="disabled"<?php } ?>>
			<option value="0" <?php if ($this->franchise['active'] == 0) echo 'selected="selected"';?>>No</option>
			<option value="1" <?php if ($this->franchise['active'] == 1) echo 'selected="selected"';?>>Yes</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align:right;vertical-align:top;">Home Office Notes:</td>
	<td class="bgcolor_light"><textarea rows="4" name="franchise_description" cols="60"><?php echo htmlentities($this->franchise['franchise_description']); ?></textarea></td>
</tr>
<tr>
	<td class="bgcolor_light" colspan="2" style="text-align: right;">
		<input type="submit" class="button" value="Update Franchise Info" name="updateFranchise" />
		<?php if ($this->permission['can_delete_franchise']) { ?><a href="javascript:deleteFranchiseConfirm();" class="button">Delete</a><?php } ?>
	</td>
</tr>
</table>

</form>

<h3 style="margin-top: 10px;">Contractual Owners <span style="font-size: 11px;">(If not contractual, user should be set to Manager)</span></h3>

<div class="tabbed-content" data-tabid="owner">

	<div class="tabs-container">
		<ul class="tabs">
			<?php if (!empty($this->owners)) { ?>
			<?php $count = 0; foreach( $this->owners as $owner ) { $count++ ?>
			<li data-tabid="<?php echo $owner['user_id']; ?>"class="tab<?php if ($count == 1) { ?> selected<?php } ?>"><?php echo $owner['firstname']; ?> <?php echo $owner['lastname']; ?></li>
			<?php } ?>
			<?php } ?>
			<?php if ($this->permission['can_add_owner']) { ?><li data-guestsearch="add_owner" data-select_button_title="Add Owner" data-all_stores_checked="true" data-select_function="addOwner" data-tooltip="Add Owner" class="tab">+</li><?php } ?>
		</ul>
	</div>

	<div class="tabs-content">
		<?php if (!empty($this->owners)) { ?>
		<?php $count = 0; foreach( $this->owners as $owner ) { $count++ ?>
		<div data-tabid="<?php echo $owner['user_id']; ?>" style="background-color: #F1E8D8;">

			<div style="float: right;">
				<span class="button" data-manage_user_id="<?php echo $owner['user_id']; ?>" data-manage_action="view" data-tooltip="View Owner Details">Owner Details</span>
				<span class="button" data-manage_user_id="<?php echo $owner['user_id']; ?>" data-manage_action="edit" data-tooltip="Edit Owner Details">Edit Owner</span>
				<?php if ($this->permission['can_remove_owner']) { ?><span class="button" data-manage_user_id="<?php echo $owner['user_id']; ?>" data-manage_action="delete" data-tooltip="Remove Owner">Remove Owner</span><?php } ?>
			</div>
			<div><?php echo $owner['firstname']; ?> <?php echo $owner['lastname']; ?></div>
			<div><?php echo $owner['address_line1']; ?><?php echo (!empty($owner['address_line2'])) ? ', ' . $owner['address_line2'] : ''; ?>, <?php echo $owner['city']; ?>, <?php echo $owner['state_id']; ?> <?php echo $owner['postal_code']; ?></div>
			<div><?php echo $owner['primary_email']; ?></div>
			<div><?php echo $owner['telephone_1']; ?></div>
			<div><?php echo $owner['telephone_2']; ?></div>

			<div class="clear"></div>

		</div>
		<?php } ?>
		<?php } else { ?>
		<div class="content" style="background-color: #F1E8D8; display: block;">
			No owners found.
		</div>
		<?php } ?>
	</div>

</div>

<h3 style="margin-top: 10px;">Stores</h3>

<div class="tabbed-content" data-tabid="store">

	<div class="tabs-container">
		<ul class="tabs">
		<?php if (!empty($this->stores)) { ?>
		<?php $count = 0; foreach( $this->stores AS $store ) { $count++ ?>
			<li data-tabid="<?php echo $store['id']; ?>" data-tooltip="<?php echo $store['store_name']; ?>" class="tab<?php if ($count == 1) { ?> selected<?php } ?><?php echo (empty($store['active'])) ? ' inactive' : '' ?>"><?php echo $store['state_id']; ?>, <?php echo $store['city']; ?></li>
		<?php } ?>
		<?php } ?>
		<?php if ($this->permission['can_add_store']) { ?><li data-tabid="add_store" data-tooltip="Add New Store" class="tab" data-link="/backoffice/create-store?franchise_id=<?php echo $this->franchise['id']; ?>&back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">+</li><?php } ?>
		</ul>
	</div>

	<div class="tabs-content">
		<?php if (!empty($this->stores)) { ?>
		<?php $count = 0; foreach( $this->stores as $store ) { $count++ ?>
		<div data-tabid="<?php echo $store['id']; ?>" class="content<?php echo (empty($store['active'])) ? ' inactive' : '' ?>">

			<table style="width: 100%;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">
					<div style="float: left;">Store Details</div>

					<div style="float: right;">
						<span class="button" data-manage_store_id="<?php echo $store['id']; ?>" data-manage_action="view" data-store_name="<?php echo $store['store_name']; ?>" data-tooltip="View <?php echo $store['store_name']; ?>">Store Details</span>
						<?php if( $this->user_type == CUser::SITE_ADMIN) { ?><span class="button" data-manage_store_id="<?php echo $store['id']; ?>" data-manage_action="archive" data-store_name="<?php echo $store['store_name']; ?>" data-tooltip="Archive and Re-Open <?php echo $store['store_name']; ?>">Archive and Re-Open</span><?php } ?>
						<?php if( $this->user_type == CUser::SITE_ADMIN) { ?><span class="button" data-manage_store_id="<?php echo $store['id']; ?>" data-manage_action="delete" data-store_name="<?php echo $store['store_name']; ?>" data-tooltip="Delete <?php echo $store['store_name']; ?>">Delete Store</span><?php } ?>
					</div>

					<div class="clear"></div>
				<td>
			<tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right; width: 150px;">Name:</td>
				<td class="bgcolor_light" style="font-weight: bold;"><?php echo $store['store_name']; ?></td>
			</tr>

			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right; width: 150px;">Home Office ID:</td>
				<td class="bgcolor_light">#<?php echo $store['home_office_id']; ?></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Address:</td>
				<td class="bgcolor_light"><?php echo $store['address_line1']; ?><?php echo (!empty($store['address_line2'])) ? ', ' . $store['address_line2'] : ''; ?>, <?php echo $store['city']; ?>, <?php echo $store['state_id']; ?> <?php echo $store['postal_code']; ?></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Store Email:</td>
				<td class="bgcolor_light"><a href="mailto:<?php echo $store['email_address']; ?>"><?php echo $store['email_address']; ?></a></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Telephone Day:</td><td class="bgcolor_light"><?php echo $store['telephone_day']; ?></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Telephone Evening:</td><td class="bgcolor_light"><?php echo $store['telephone_evening']; ?></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Fax:</td><td class="bgcolor_light"><?php echo $store['fax']; ?></td>
			</tr>
			<tr>
				<td valign="top" class="bgcolor_light" style="text-align:right;">Active:</td><td class="bgcolor_light"><?php echo (!empty($store['active'])) ? 'Yes' : 'No' ?></td>
			</tr>
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Personnel<td>
			<tr>
			<tr>
				<td colspan="3">
					<table style="width: 100%;">
					<tr>
						<td class="bgcolor_medium header_row">User Type</td>
						<td class="bgcolor_medium header_row">Name</td>
						<td class="bgcolor_medium header_row">Email</td>
						<td class="bgcolor_medium header_row">Last Login</td>
						<td class="bgcolor_medium header_row">NDA Accepted</td>
						<td class="bgcolor_medium header_row">On Store Info Page</td>
					</tr>
					<?php if (!empty($store['personnel'])) { ?>
					<?php foreach ($store['personnel'] as $user_id => $userInfo) { ?>
					<tr>
						<td class="bgcolor_light"><a href="/backoffice/access-levels?id=<?php echo $user_id; ?>"><?php echo CUser::userTypeText($userInfo['user_type']); ?></a></td>
						<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $user_id; ?>"><?php echo $userInfo['firstname']; ?> <?php echo $userInfo['lastname']; ?></a></td>
						<td class="bgcolor_light"><a href="/backoffice/email?id=<?php echo $user_id; ?>"><?php echo $userInfo['primary_email']; ?></a></td>
						<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $user_id; ?>"><?php echo (!empty($userInfo['last_login'])) ? CTemplate::dateTimeFormat($userInfo['last_login'], MONTH_DAY_YEAR) : 'Never'; ?></a></td>
						<td class="bgcolor_light" style="text-align:center;"><?php echo (!empty($userInfo['fadmin_nda_agree'])) ? 'Yes' : '<span style="color: red;">No</span>'; ?></td>
						<td class="bgcolor_light" style="text-align:center;"><a href="/location/<?php echo $store['id']; ?>"><?php echo (!empty($userInfo['display_to_public'])) ? 'Yes' : 'No'; ?></a></td>
					</tr>
					<?php } } ?>
					</table>
				</td>
			</tr>
			</table>

		</div>
		<?php } ?>
		<?php } else { ?>
		<div class="content" style="background-color: #F1E8D8; display: block;">
			No stores found.
		</div>
		<?php } ?>
	</div>

</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>