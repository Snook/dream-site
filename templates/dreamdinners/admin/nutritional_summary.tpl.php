<?php $this->setScript('head', SCRIPT_PATH . '/admin/nutritional_summary.min.js'); ?>
<?php $this->setOnload('nutritional_summary_init();'); ?>
<?php $this->assign('page_title','Nutritional Summary'); ?>
<?php $this->assign('topnav','reports'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3>Print Nutritional Summary</h3>

<?php if (!empty($this->form['store_html'])) { ?>
<form method="post">
<b>Store:</b>&nbsp;<?php echo $this->form['store_html']; ?> Requires a store selection<br /><br />
</form>
<?php } else { ?>
	<input type="hidden" id="store" name="store" value="<?php echo $this->store_id;?>" />
<?php } ?>

<?php if (!empty($this->store_id)) { ?>
<?php echo $this->form['menus_dropdown_html']; ?><br /><br />
<input id="show_entree" type="checkbox" value="1"> Show Entrees<br />
<input id="show_efl" type="checkbox" value="1"> Show Extended Fast Lane<br />
<input id="show_ft" type="checkbox" value="1" checked="checked"> Show Sides & Sweets<br />
<input id="filter_zero_inventory" type="checkbox" value="1" checked="checked"> Filter out items with zero inventory<br /><br />
<input id="print_nutrition_summary" type="button" class="button" value="Print Summary" />
<?php } ?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>