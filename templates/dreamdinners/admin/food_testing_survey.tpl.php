<?php $this->setScript('head', SCRIPT_PATH . '/admin/food_testing.min.js'); ?>
<?php $this->setOnload('food_testing_survey_init();'); ?>
<?php $this->assign('page_title','Food Testing'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Food Testing</h1>

<?php if (!empty($this->form['store_html'])) { ?>
<form method="post">
<b>Store:</b>&nbsp;<?php echo $this->form['store_html']; ?> Requires a store selection<br /><br />
</form>
<?php } ?>

<?php if ($this->cansubmitfoodtestingw9 && empty($this->store['food_testing_w9'])) { ?>
<p><img src="<?php echo IMAGES_PATH; ?>/icon/error.png" class="img_valign" /> Please complete Form W-9 in order to participate in the Food Testing program.</p>
<?php } ?>

<table style="width:100%;">
<thead>
<tr>
	<td colspan="8" class="bgcolor_dark catagory_row">Food Testing</td>
</tr>
</thead>
<tbody>
<tr>
	<td class="bgcolor_medium header_row">Survey</td>
	<td class="bgcolor_medium header_row">Created</td>
	<td class="bgcolor_medium header_row">Guests</td>
	<td class="bgcolor_medium header_row">Pending</td>
	<td class="bgcolor_medium header_row">Completed</td>
	<td class="bgcolor_medium header_row">Store Survey</td>
	<td class="bgcolor_medium header_row">Files</td>
	<td class="bgcolor_medium header_row">Guests</td>
</tr>
<?php foreach($this->recipes AS $id => $recipe) { ?>
<tr>
	<td class="bgcolor_lighter" id="recipe_row-<?php echo $id; ?>"><div id="recipe_row_disc-<?php echo $id; ?>" class="disc_closed"></div><a href="/backoffice/food-testing-survey?recipe=<?php echo $id; ?>"><?php echo $recipe['title']; ?></a></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo CTemplate::dateTimeFormat($recipe['timestamp_created'], MONTH_DAY_YEAR); ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['total_guests']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['pending_count']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['response_count']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php if (!empty($recipe['timestamp_completed'])) { ?><?php echo CTemplate::dateTimeFormat($recipe['timestamp_completed'], MONTH_DAY_YEAR); ?><?php } else { ?><input type="button" class="btn btn-primary btn-sm" id="recipe_survey_store-<?php echo $id; ?>" value="Survey" /><?php } ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php if (!empty($recipe['file_name'])) { ?><input type="button" class="btn btn-primary btn-sm" id="download_files-<?php echo $recipe['food_testing_id']; ?>" data-file_id="<?php echo $recipe['file_id']; ?>" name="download_files-<?php echo $recipe['food_testing_id']; ?>" data-tooltip="<?php echo $recipe['file_name']; ?>" value="Download Files" /><?php } else { ?>None Available<?php } ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><input type="button" class="btn btn-primary btn-sm" id="add_guests-<?php echo $id; ?>" value="Add Staff" /></td>
	</tr>
</tbody>
<tbody id="recipe_surveys-<?php echo $id; ?>" style="display:none;">
<tr>
	<td colspan="8">
		<table style="width:100%;">
		<tr>
			<td colspan="<?php echo (($this->canDeleteGuest) ? '4' : '3'); ?>" class="bgcolor_dark catagory_row">Guests assigned to <?php echo $recipe['title']; ?></td>
		</tr>
		<tr>
			<td class="bgcolor_medium header_row">Guest</td>
			<td class="bgcolor_medium header_row">Received Meal</td>
			<td class="bgcolor_medium header_row">Survey Status</td>
			<?php if ($this->canDeleteGuest) { ?>
			<td class="bgcolor_medium header_row">Manage</td>
			<?php } ?>
		</tr>
		<?php if (!empty($this->surveys[$id])) { ?>
		<?php foreach($this->surveys[$id] AS $sid => $survey) { ?>
		<tr id="survey_row-<?php echo $survey['id']; ?>">
			<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $survey['user_id']; ?>"><?php echo ucfirst($survey['firstname']); ?> <?php echo ucfirst($survey['lastname']); ?></a></td>
			<td class="bgcolor_light" style="text-align:center;" id="size_select_td-<?php echo $survey['id']; ?>"><?php if (!empty($survey['timestamp_received'])) { ?><?php echo CTemplate::dateTimeFormat($survey['timestamp_received'], MONTH_DAY_YEAR); ?><?php } else { ?><select id="size_select-<?php echo $survey['id']; ?>" name="size_select-<?php echo $survey['id']; ?>"><option value="0" selected="selected">Size</option><option value="HALF">3-srv</option><option value="FULL" >6-srv</option></select> <input type="button" class="btn btn-primary btn-sm" id="size_select_submit-<?php echo $survey['id']; ?>" name="size_select_submit-<?php echo $survey['id']; ?>" value="Guest Received" /><?php } ?></td>
			<td class="bgcolor_light" style="text-align:center;">
				<?php if (!empty($survey['timestamp_completed'])) { ?>
					<?php echo CTemplate::dateTimeFormat($survey['timestamp_completed'], MONTH_DAY_YEAR); ?>
				<?php } else if (!empty($survey['timestamp_received'])) { ?>
					Pending
				<?php } else { ?>
					Awaiting Entree
				<?php } ?>
			</td>
			<?php if ($this->canDeleteGuest) { ?>
			<td class="bgcolor_light" style="text-align:center;">
				<?php if (empty($survey['timestamp_completed'])) { ?>
					<span id="delete_guest-<?php echo $survey['id']; ?>" data-survey_submission_id="<?php echo $survey['id']; ?>" class="btn btn-primary btn-sm">Delete</span>
				<?php } else { ?>
					<span class="button disabled" data-tooltip="Unable to delete once survey has been completed.">Delete</span>
				<?php } ?>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="<?php echo (($this->canDeleteGuest) ? '4' : '3'); ?>" class="bgcolor_light">No guests assigned to this recipe.</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="<?php echo (($this->canDeleteGuest) ? '4' : '3'); ?>" class="bgcolor_dark catagory_row"></td>
		</tr>
		</table>
	</td>
</tr>
</tbody>
<?php } ?>
</table>

<div id="add_guests_content" style="display:none;">
<form method="post">
<input type="hidden" id="add_guest_survey_id" name="add_guest_survey_id" value="" />
<textarea name="add_guest" placeholder="Add staff by ID, one per line" style="width:260px;height:200px;"></textarea>
<br /><input type="submit" class="btn btn-primary btn-sm" value="Add Guests" />
</form>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>