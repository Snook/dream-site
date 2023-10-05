<?php $this->setScript('head', SCRIPT_PATH . '/admin/food_testing.min.js'); ?>
<?php $this->setOnload('food_testing_survey_store_init();'); ?>
<?php $this->assign('page_title','Food Testing Preparation Survey'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Food Preparation Survey - <?php echo $this->survey['title']; ?></h1>

<form method="POST">
<input type="hidden" name="survey_id" value="<?php echo $this->survey['id']; ?>" />

<table style="width:100%;">
<tr>
	<td colspan="2" class="bgcolor_dark catagory_row">Schematic</td>
</tr>
<tr>
	<td class="bgcolor_light" style="width:50%;">Was the schematic accurate?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-schematic_accurate-1" name="schematic_accurate" value="1" /> Yes
		<input type="radio" id="question-schematic_accurate-2" name="schematic_accurate" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Easy to understand?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-schematic_easy_to_understand-1" name="schematic_easy_to_understand" value="1" /> Yes
		<input type="radio" id="question-schematic_easy_to_understand-2" name="schematic_easy_to_understand" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Please provide any recommendations or notes</td>
	<td class="bgcolor_light"><textarea style="width:400px;height:130px;" id="question-schematic_notes" name="schematic_notes"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="bgcolor_dark catagory_row">HoneyDew</td>
</tr>
<tr>
	<td class="bgcolor_light">Was the HoneyDew accurate?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-honeydew_accurate-1" name="honeydew_accurate" value="1" /> Yes
		<input type="radio" id="question-honeydew_accurate-2" name="honeydew_accurate" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Easy to understand?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-honeydew_easy_to_understand-1" name="honeydew_easy_to_understand" value="1" /> Yes
		<input type="radio" id="question-honeydew_easy_to_understand-2" name="honeydew_easy_to_understand" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Please provide any recommendations or notes</td>
	<td class="bgcolor_light"><textarea style="width:400px;height:130px;" id="question-honeydew_notes" name="honeydew_notes"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="bgcolor_dark catagory_row">Recipe Card/Assembly Procedures</td>
</tr>
<tr>
	<td class="bgcolor_light">Were the recipe card / assembly instructions accurate?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-recipe_assembly_accurate-1" name="recipe_assembly_accurate" value="1" /> Yes
		<input type="radio" id="question-recipe_assembly_accurate-2" name="recipe_assembly_accurate" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Easy to understand?</td>
	<td class="bgcolor_light">
		<input type="radio" id="question-recipe_assembly_easy_to_understand-1" name="recipe_assembly_easy_to_understand" value="1" /> Yes
		<input type="radio" id="question-recipe_assembly_easy_to_understand-2" name="recipe_assembly_easy_to_understand" value="0" /> No
	</td>
</tr>
<tr>
	<td class="bgcolor_light">Please provide any recommendations or notes</td>
	<td class="bgcolor_light"><textarea style="width:400px;height:130px;" id="question-recipe_assembly_notes" name="recipe_assembly_notes"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="bgcolor_dark catagory_row">Selling Features/Other notes</td>
</tr>
<tr>
	<td class="bgcolor_light">Please provide any recommendations or notes</td>
	<td class="bgcolor_light"><textarea style="width:400px;height:130px;" id="question-selling_features_notes" name="selling_features_notes"></textarea></td>
</tr>
<tr>
	<td colspan="2"><input id="submit_my_survey" type="submit" class="btn btn-primary btn-sm" value="Submit Survey" disabled="disabled" /> <span id="submit_my_survey_note">*Please complete all questions, thank you.</span></td>
</tr>
</table>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>