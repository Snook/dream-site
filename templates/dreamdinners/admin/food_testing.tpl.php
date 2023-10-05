<?php $this->setScript('head', SCRIPT_PATH . '/admin/food_testing.min.js'); ?>
<?php $this->setOnload('food_testing_init();'); ?>
<?php $this->assign('page_title','Food Testing Manager'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3>Food Testing Manager</h3>

<table style="width:100%;">
<thead>
<tr>
	<td colspan="8" class="bgcolor_dark catagory_row">
		<span style="float:right;"><input id="download_reports" type="button" value="Reports" class="btn btn-primary btn-sm" /></span>
		<span style="float:right;"><input id="create_survey" type="button" value="Add Recipes" class="btn btn-primary btn-sm" /></span>
		Food Testing
	</td>
</tr>
</thead>
<tbody>
<tr>
	<td class="bgcolor_medium header_row">Survey</td>
	<td class="bgcolor_medium header_row">Created</td>
	<td class="bgcolor_medium header_row">Stores</td>
	<td class="bgcolor_medium header_row">Pending</td>
	<td class="bgcolor_medium header_row">Completed</td>
	<td class="bgcolor_medium header_row">Operation</td>
	<td class="bgcolor_medium header_row">Reports</td>
	<td class="bgcolor_medium header_row">Close</td>
</tr>
<?php if (empty($this->recipes)) { ?>
<tr>
	<td colspan="7" class="bgcolor_lighter">No recipes available.</td>
</tr>
<?php } else { ?>
<?php foreach($this->recipes AS $id => $recipe) { ?>
<tr>
	<td class="bgcolor_lighter" id="recipe_row-<?php echo $id; ?>"><div id="recipe_row_disc-<?php echo $id; ?>" class="disc_closed"></div><a href="/backoffice/food-testing?recipe=<?php echo $id; ?>"><?php echo $recipe['title']; ?></a></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo CTemplate::dateTimeFormat($recipe['timestamp_created'], MONTH_DAY_YEAR); ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['total_stores']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['pending_surveys']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;"><?php echo $recipe['response_count']; ?></td>
	<td class="bgcolor_lighter" style="text-align:center;">
		<input type="button" id="add_stores-<?php echo $id; ?>" name="add_stores-<?php echo $id; ?>" value="Add Stores" class="btn btn-primary btn-sm<?php if (!empty($recipe['is_closed'])) { ?> disabled<?php } ?>" />
		<input type="button" id="add_files-<?php echo $id; ?>" value="<?php if ($recipe['file_name']) { ?>Update<?php } else { ?>Add<?php } ?> File" class="btn btn-primary btn-sm<?php if (!empty($recipe['is_closed'])) { ?> disabled<?php } ?>" />
		<?php if ($recipe['file_name']) { ?><input type="button" id="download_files-<?php echo $id; ?>" data-file_id="<?php echo $recipe['file_id']; ?>" name="download_files-<?php echo $id; ?>" value="V" data-tooltip="<?php echo $recipe['file_name']; ?>" class="btn btn-primary btn-sm" style="margin-left:-7px;width: 20px;" /><?php } ?>
	</td>
	<td class="bgcolor_lighter" style="text-align:center;">
		<input type="button" id="export_store_report-<?php echo $id; ?>" name="export_store_report-<?php echo $id; ?>" value="Store" class="btn btn-primary btn-sm" <?php if (empty($recipe['total_stores'])) { ?>disabled="disabled"<?php } ?> />
		<input type="button" id="export_guest_report-<?php echo $id; ?>" name="export_guest_report-<?php echo $id; ?>" value="Guest" class="btn btn-primary btn-sm" <?php if (empty($recipe['total_stores'])) { ?>disabled="disabled"<?php } ?> />
	</td>
	<td class="bgcolor_lighter" style="text-align:center;"><input id="survey_closed-<?php echo $id; ?>" name="survey_closed-<?php echo $id; ?>" data-survey_id="<?php echo $id; ?>" type="checkbox" <?php if (!empty($recipe['is_closed'])) { ?>checked="checked"<?php } ?> /></td>
</tr>
</tbody>
<tbody id="recipe_surveys-<?php echo $id; ?>" style="display:none;">
<tr>
	<td colspan="8">
		<table style="width:100%;">
		<tr>
			<td colspan="8" class="bgcolor_dark catagory_row">Stores assigned to <?php echo $recipe['title']; ?></td>
		</tr>
		<tr>
			<td class="bgcolor_medium header_row">Store</td>
			<td class="bgcolor_medium header_row">Created</td>
			<td class="bgcolor_medium header_row">Guests</td>
			<td class="bgcolor_medium header_row">Pending</td>
			<td class="bgcolor_medium header_row">Completed</td>
			<td class="bgcolor_medium header_row">Store Survey</td>
			<td class="bgcolor_medium header_row">Payment</td>
		</tr>
		<?php if (!empty($this->surveys[$id])) { ?>
		<?php foreach($this->surveys[$id] AS $sid => $survey) { ?>
		<tr>
			<td class="bgcolor_light"><a href="/backoffice/food-testing-survey?recipe=<?php echo $survey['id']; ?>&amp;store_id=<?php echo $survey['store_id']; ?>"><?php echo $survey['store_name']; ?></a></td>
			<td class="bgcolor_light" style="text-align:center;"><?php echo CTemplate::dateTimeFormat($survey['timestamp_created'], MONTH_DAY_YEAR); ?></td>
			<td class="bgcolor_light" style="text-align:center;"><?php echo $survey['guest_total']; ?></td>
			<td class="bgcolor_light" style="text-align:center;"><?php echo $survey['guest_pending']; ?></td>
			<td class="bgcolor_light" style="text-align:center;"><?php echo $survey['guest_completed']; ?></td>
			<td class="bgcolor_light" style="text-align:center;"><?php if (!empty($survey['timestamp_completed'])) { ?><?php echo CTemplate::dateTimeFormat($survey['timestamp_completed'], MONTH_DAY_YEAR); ?><?php } else { ?>Not Completed<?php } ?></td>
			<td class="bgcolor_light" style="text-align:center;" id="store_paid_td-<?php echo $survey['id']; ?>">
				<div  id="store_paid_div-<?php echo $survey['store_id']; ?>">
					<?php if (!empty($survey['timestamp_paid'])) { ?>
						<?php echo CTemplate::dateTimeFormat($survey['timestamp_paid'], MONTH_DAY_YEAR); ?>
					<?php } else { ?>
						<input type="button" id="mark_paid-<?php echo $survey['id']; ?>" name="mark_paid-<?php echo $survey['id']; ?>" data-store_paid_id="<?php echo $survey['store_id']; ?>" value="Mark Paid" class="btn btn-primary btn-sm" <?php if (empty($survey['food_testing_w9'])) { ?>style="display:none;"<?php } ?> />
					<?php } ?>
				</div>
				<?php if (empty($survey['food_testing_w9'])) { ?>
				<div id="store_w9_div-<?php echo $survey['store_id']; ?>">
					<input type="button" id="received_w9-<?php echo $survey['id']; ?>" name="received_w9-<?php echo $survey['id']; ?>" data-store_w9_id="<?php echo $survey['store_id']; ?>" value="W9 Received" class="btn btn-primary btn-sm" />
				</div>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="8" class="bgcolor_light">No stores assigned to this recipe.</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="8" class="bgcolor_dark catagory_row"></td>
		</tr>
		</table>
	</td>
</tr>
</tbody>
<?php } ?>
<?php } ?>
</table>

<div id="create_survey_content" style="display:none;">
<form method="post">
<textarea name="new_recipe" placeholder="New Recipes, one per line" style="width:260px;height:200px;"></textarea>
<br /><input type="submit" value="Add Recipes" class="btn btn-primary btn-sm" />
</form>
</div>

<div id="add_survey_file_content" style="display:none;">
<form method="post" enctype="multipart/form-data">
<input type="hidden" id="add_file_survey_id" name="add_file_survey_id" value="" />
<input type="file" name="add_survey_file" id="add_survey_file"><br /><br /><input type="submit" value="Add File" class="btn btn-primary btn-sm" />
</form>
</div>

<div id="add_stores_content" style="display:none;">
<form method="post">
<input type="hidden" id="add_store_survey_id" name="add_store_survey_id" value="" />
<textarea name="add_store" placeholder="Add stores by Home Office ID, one per line" style="width:260px;height:200px;"></textarea>
<br /><input type="submit" value="Add Stores" class="btn btn-primary btn-sm" />
</form>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>