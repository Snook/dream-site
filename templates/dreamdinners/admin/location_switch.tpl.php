<?php $this->assign('page_title','Location Switch'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<div style="text-align: center;">
	<form action="" method="post">
		<?php if (!empty($this->form_location_switch['store_html'])) { ?>
			Assign a current store <?php echo  $this->form_location_switch['store_html']; ?> <input type="submit" class="btn btn-primary btn-sm" name="submit" value="Submit">
		<?php } else { ?>
			Error: No access to any store.
		<?php } ?>
	</form>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>