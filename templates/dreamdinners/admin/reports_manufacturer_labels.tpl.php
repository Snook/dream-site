<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.uitablefilter.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_manufacturer_labels.min.js'); ?>
<?php $this->setOnload('reports_manufacturer_labels_init();'); ?>
<?php $this->assign('page_title','Manufacturing Labels'); ?>
<?php $this->assign('topnav','reports'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script src="<?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>

<h3>Manufacturing Labels</h3>

<?php if (!empty($this->form['store_html'])) { ?>
<form method="post">
<b>Store:</b>&nbsp;<?php echo $this->form['store_html']; ?> Requires a store selection<br /><br />
</form>
<?php } ?>

<?php if (empty($this->recipes)) { ?>
<p>No recipe labels available for this store.</p>
<?php } else { ?>
<form method="post" target="_print">
<table id="recipe_list" style="width:100%;">
<thead>
<tr>
	<th class="bgcolor_dark catagory_row" colspan="3"><span style="float:left;"><input name="filter" id="filter" placeholder="Filter Recipe ID, Name or UPC" data-tooltip="Filter Recipe ID, Name or UPC" size="30" type="text"><input id="clear_filter" type="button" class="button" value="Clear" /></span></th>
	<th class="bgcolor_dark catagory_row">Medium</th>
	<th class="bgcolor_dark catagory_row">Large</th>
<tr>
</thead>
<tbody>
<?php foreach ($this->recipes AS $recipe_id => $recipe) { ?>
<tr>
	<td class="bgcolor_light" style="text-align:right;"><a href="?page=item&amp;recipe=<?php echo $recipe['info']['recipe_id']; ?>" target="_blank"><?php echo $recipe['info']['recipe_id']; ?></a></td>
	<td class="bgcolor_light"><a href="<?php echo IMAGES_PATH; ?>/recipe/default/<?php echo $recipe['info']['recipe_id']; ?>.webp" data-tooltip="Click to open large image" target="_blank"><img src="<?php echo IMAGES_PATH; ?>/recipe/default/<?php echo $recipe['info']['recipe_id']; ?>.webp" style="margin: 2px 0px 0px 2px; width: 22px; height: 22px;" /></a></td>
	<td class="bgcolor_light"><?php echo $recipe['info']['recipe_name']; ?></td>
	<td class="bgcolor_light" style="text-align:center;"><?php if (!empty($recipe['MEDIUM'])) { ?><input type="number" data-recipe_id-medium="<?php echo $recipe_id; ?>" id="medium-<?php echo $recipe_id; ?>" name="medium-<?php echo $recipe_id; ?>" placeholder="Qty" style="width:40px;margin-right:10px;" min="0" size="2"> <?php if (!empty($recipe['MEDIUM']['upc'])) { ?><?php echo $recipe['MEDIUM']['upc']; ?><?php } else { ?>000000000000<?php } ?><?php } ?></td>
	<td class="bgcolor_light" style="text-align:center;"><?php if (!empty($recipe['LARGE'])) { ?><input type="number" data-recipe_id-large="<?php echo $recipe_id; ?>" id="large-<?php echo $recipe_id; ?>" name="large-<?php echo $recipe_id; ?>" placeholder="Qty" style="width:40px;margin-right:10px;" min="0" size="2"> <?php if (!empty($recipe['LARGE']['upc'])) { ?><?php echo $recipe['LARGE']['upc']; ?><?php } else { ?>000000000000<?php } ?><?php } ?></td>
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr>
	<th class="bgcolor_dark catagory_row" colspan="3">&nbsp;</th>
	<th class="bgcolor_dark catagory_row"><input id="clear_medium" type="button" class="button" value="Clear Medium" /></th>
	<th class="bgcolor_dark catagory_row"><input id="clear_large" type="button" class="button" value="Clear Large" /></th>
<tr>
</tfoot>
</table>

<p><span id="num_labels" style="font-weight:bold;">0</span> labels will be printed using <span id="num_sheets" style="font-weight:bold;">0</span> sheets.</p>

<h4>Label Type</h4>

<select id="print_label_type" name="print_label_type">
	<option value="avery_8164" selected="selected">Adhesive Labels - Avery 8164</option>
	<option value="perforated_labels">Perforated Labels</option>
</select>

<br />
<br />

<h4>Nutritional Labels</h4>
<input type="submit" class="button" id="generate_nutritional_labels" name="generate_nutritional_labels" value="Generate Nutritional Labels" disabled="disabled" />

<br />
<br />

<h4>Cooking Instructions</h4>
<span style="display:inline-block;float:left;font-weight:bold;padding:4px;">Use by:</span>
<script type="text/javascript">DateInput('use_by_date', true, 'MM/DD/YYYY', '<?php echo date('m/d/Y', strtotime("+2 month")); ?>');</script>
<input type="submit" class="button" id="generate_cooking_instructions" name="generate_cooking_instructions" value="Generate Cooking Instructions" disabled="disabled" />

</form>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>