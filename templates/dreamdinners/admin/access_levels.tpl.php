<?php $this->setScript('head', SCRIPT_PATH . '/admin/access_levels.min.js'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setOnLoad("access_levels_init();"); ?>
<?php $this->setScriptVar('current_user_type = "' . $this->current_user_type . '";'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script type="text/javascript">
function accessChange()
{
	var msg = "Are you sure you want to change access for this user.";

	<?php if (!$this->isAdmin && !empty($this->has_other_links))	{ ?>
			msg += " The user will also be granted access to the current store.";

	<?php } else { ?>

    	var nextType = document.getElementById('user_type').value;

    	if (nextType != '<?php echo $this->user_type?>' &&
    	    	(nextType == 'FRANCHISE_OWNER' ||
    	    	 nextType == 'FRANCHISE_STAFF' ||
    	    	 nextType == 'FRANCHISE_MANAGER' ||
    	    	 nextType == 'GUEST_SERVER' ||
    	    	 nextType == 'EVENT_COORDINATOR' ||
    	    	 nextType == 'OPS_SUPPORT' ||
    	    	 nextType == 'OPS_LEAD'))

    		msg += " The user will have " + nextType + " access at all assigned stores.";

	<?php } ?>

	var isConfirmed = confirm(msg);

	isConfirmed = true;
	if (isConfirmed)
	{
		return true;
	}
	else
	{
		return false;
	}
}
</script>

<?php if (isset($this->form['store_html'])) { ?>
	<strong>Store:</strong> <?php echo $this->form['store_html']; ?><br /><br />
<?php } ?>
<?php if (isset($_REQUEST['back'])) { ?>
	<input type="button" class="button" value="Back" onclick="window.location = '<?php echo  $_REQUEST['back']?>';">
<?php } else { ?>
	<input type="button" class="button" value="Back" onclick="window.location = '/backoffice/user_details?id=<?php echo  $this->id?>';">
<?php } ?>

Access Level for: <b><?php echo $this->firstname; ?> <?php echo $this->lastname; ?></b>

<p>By clicking the Save Changes button, you will be changing the access level of this user account.
	A user account type of staff is the lowest access level in the system.
	This user can view session reports, place a direct order, etc.  An account type of manager can create new sessions and templates, edit guest info, view session and entree reports.
	If the account needs to be downgraded, please assign a customer access level.</p>

<table style="width:100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Current access level</td>
</tr>
<tr>
	<td class="bgcolor_light">ID</td>
	<td class="bgcolor_light"><?php echo $this->id;  ?></td>
</tr>
<tr>
	<td class="bgcolor_light">Name</td>
	<td class="bgcolor_light"><?php echo $this->firstname . '&nbsp;' . $this->lastname;  ?></td>
</tr>
<tr>
	<td class="bgcolor_light">Email Address</td>
	<td class="bgcolor_light"><?php echo $this->email;  ?></td>
</tr>
<tr>
	<td class="bgcolor_light">Current User Type</td>
	<td class="bgcolor_light" style="font-weight: bold;"><?php echo CUser::userTypeText($this->user_type); ?></td>
</tr>
</table>

<?php if (isset($this->users_stores) && count($this->users_stores) > 0) { ?>
<br />

<form id="accessAction" action="<?php echo HTTPS_SERVER . $_SERVER["REQUEST_URI"]; ?>" name="accessAction" method="post">
<input type="hidden" value="none" name="al_action" id="al_action">
<input type="hidden" value="" id="which_store" name="which_store">

<table style="width:100%">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="<?php echo ($this->isAdmin) ? '4' : '3'; ?>">User has access to these stores</td>
</tr>
<tr>
	<td class="bgcolor_medium header_row">Store</td>
	<td class="bgcolor_medium header_row">Display in Store Info</td>
	<td class="bgcolor_medium header_row">Display Text (Overrides First & Last Name)</td>
	<?php if ($this->isAdmin) { ?>
	<td class="bgcolor_medium header_row">Operation</td>
	<?php } ?>
</tr>
<?php foreach ($this->users_stores as $store_id => $thisStore) { ?>
<tr>
	<td class="bgcolor_light"><?php if ($this->isAdmin || (!empty($this->currentStoreID) && $this->currentStoreID  == $store_id)) { ?><a href="/backoffice/store_details?id=<?php echo $thisStore['id']; ?>"><?php echo $thisStore['name']; ?></a><?php } else { ?><?php echo $thisStore['name']; ?><?php } ?></td>
<?php if ($this->isAdmin || (!empty($this->currentStoreID) && $this->currentStoreID  == $store_id)) { ?>
	<td class="bgcolor_light" style="text-align: center;"><input type="checkbox" data-show_on_customer="<?php echo $thisStore['uts_id']; ?>" <?php echo ($thisStore['display'] ? 'checked="checked"' : ''); ?> /></td>
	<td class="bgcolor_light"><input type="text" style="width: 300px;" data-display_text="<?php echo $thisStore['uts_id']; ?>" value="<?php echo htmlentities($thisStore['text']); ?>"
	maxlength="100" placeholder="<?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?>" /> <input type="button" data-display_text_save="<?php echo $thisStore['uts_id']; ?>" class="button disabled" value="Save" /></td>
<?php } else { ?>
	<td class="bgcolor_light" style="text-align: center;"><?php echo ($thisStore['display'] ? 'Yes' : 'No'); ?></td>
	<td class="bgcolor_light"><?php echo (!empty($thisStore['text'])) ? htmlentities($thisStore['text']) : $this->user->firstname . ' ' . $this->user->lastname; ?></td>
	<?php } ?>
	<?php if ($this->isAdmin) {?>
	<td class="bgcolor_light" style="text-align:center;"><input type="button" class="button<?php if ($this->user->user_type == CUser::FRANCHISE_OWNER) { ?> disabled<?php } ?>" value="Delete"
	               <?php if ($this->user->user_type != CUser::FRANCHISE_OWNER) { ?>onclick="deleteStore('<?php echo $store_id; ?>');"<?php } else { ?>data-tooltip="Franchise owners must be managed in franchise admin"<?php } ?> /></td>
	<?php } ?>
</tr>
<?php } ?>
</table>



</form>
<?php } ?>

<?php if ($this->user->user_type == CUser::FRANCHISE_OWNER) { ?>

<br />

<table style="width:100%">
	<tr>
		<td class="bgcolor_dark catagory_row" colspan="5">User owns these franchises. Franchise Owner access must be assigned through the franchise manager</td>
	</tr>
	<tr>
		<td class="bgcolor_medium header_row">ID</td>
		<td class="bgcolor_medium header_row">Entity Name</td>
		<td class="bgcolor_medium header_row">Active</td>
		<td class="bgcolor_medium header_row">Date Created</td>
		<td class="bgcolor_medium header_row">Last Updated</td>
	</tr>
	<?php if (!empty($this->users_franchises)) { ?>
	<?php foreach ($this->users_franchises as $id => $thisFranchise) { ?>
		<tr>
			<td class="bgcolor_light"><?php if ($this->isAdmin) { ?><a href="/backoffice/franchise-details?id=<?php echo $thisFranchise['id']; ?>"><?php echo $thisFranchise['id']; ?></a><?php } else { ?><?php echo $thisFranchise['id']; ?><?php } ?></td>
			<td class="bgcolor_light"><?php if ($this->isAdmin) { ?><a href="/backoffice/franchise-details?id=<?php echo $thisFranchise['id']; ?>"><?php echo $thisFranchise['franchise_name']; ?></a><?php } else { ?><?php echo $thisFranchise['franchise_name']; ?><?php } ?></td>
			<td class="bgcolor_light" style="text-align: center;"><?php echo (!empty($thisFranchise['active'])) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>'; ?></td>
			<td class="bgcolor_light" style="text-align: center;"><?php echo CTemplate::dateTimeFormat($thisFranchise['timestamp_created'], MONTH_DAY_YEAR); ?></td>
			<td class="bgcolor_light" style="text-align: center;"><?php echo CTemplate::dateTimeFormat($thisFranchise['timestamp_updated'], MONTH_DAY_YEAR); ?></td>
		</tr>
	<?php } ?>
	<?php } else { ?>
	<tr>
		<td class="bgcolor_light" style="text-align: center; font-weight: bold; padding: 8px;" colspan="5">No franchises found, this should not have happened, please contact an admin.</td>
	</tr>
	<?php } ?>
</table>

<?php } else { ?>

<br />

<form name="change_access" id="change_access" action="<?php echo HTTPS_SERVER.$_SERVER["REQUEST_URI"];?>" method="post"  onSubmit="return accessChange();">

<table style="width:100%">
<tbody>
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Add Access Level</td>
</tr>
<tr>
	<td class="bgcolor_light" style="width:50%;text-align:right;">Select a new Access Level here</td>
	<td class="bgcolor_light"><?php echo $this->form_account['user_type_html']; ?></td>
</tr>
</tbody>
<?php if (($this->target_user_type == CUser::FRANCHISE_MANAGER || $this->target_user_type == CUser::EVENT_COORDINATOR || $this->target_user_type == CUser::OPS_LEAD) && !empty($this->add_mgr_privileges)) {  ?>
<tbody>
<tr>
	<td colspan="2" style="font-weight:bold; text-align:center;">Additional Access Privileges</td>
</tr>
<?php foreach($this->add_mgr_privileges as $thisPriv) { ?>
<tr>
<td style="width:20px; text-align:right;">
<input type="checkbox" id="priv_<?php echo $thisPriv['id'];?>" name="priv_<?php echo $thisPriv['id'];?>" <?php if ($thisPriv['active']) echo "checked='checked'"?> />
</td>
<td>
<span><?php echo $thisPriv['title']?></span>
</td>
</tr>

<?php } ?>
</tbody>
<?php } ?>

<tbody id="store_settings_div">
<tr>
	<td class="bgcolor_dark catagory_row" colspan="2">Store Specific Settings</td>
</tr>
<?php if ($this->isAdmin == true) { ?>
<tr>
	<td class="bgcolor_light" style="text-align:right;">Choose a Store</td>
	<td class="bgcolor_light"><?php echo (isset($this->form_account['store_html'])) ? $this->form_account['store_html'] : '';  ?></td>
</tr>
<?php } else { ?>
<tr>
	<td class="bgcolor_light" style="width:50%;text-align:right;">Current Store</td>
	<td class="bgcolor_light" style="font-weight:bold; padding-left:8px;"><?php echo (!empty($this->currentStoreName) ? $this->currentStoreName : ""); ?>

<?php } ?>
<?php if (!$this->isAdmin) { ?>
		<?php if (!$this->store_is_linked && count($this->users_stores) > 0) { ?>
			<button onclick="addStore(<?php echo $this->currentStoreID; ?>)" name="addCurrentStore" value="" class="button">Add Access to this Store</button>
		<?php } else if (count($this->users_stores) > 0) {	?>
			<button onclick="deleteStore(<?php echo $this->currentStoreID; ?>)" name="deleteCurrentStore" value="" class="button">Remove Access to this Store</button>
		<?php } ?>
	</td>
</tr>
<?php } ?>
</tbody>
<tbody>
<tr>
	<td class="bgcolor_light" style="text-align: center;" colspan="2"><input type="submit" class="button" id="submit_account" name="submit_account" value="Save Access Level Changes" /></td>
</tr>
</tbody>
</table>


</form>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>