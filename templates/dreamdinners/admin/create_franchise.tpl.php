<?php $this->assign('topnav', 'store'); ?>
<?php $this->assign('page_title','Create Franchise'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h1>Create Franchise</h1>

<form action="" method="POST" onSubmit="return _check_form(this);" >

<div style='padding: 1px 1px 1px 30px;'>

	<p>A franchise is an entity that can consist of multiple owners and multiple stores.<br />Enter in LLC, or owner name if no LLC, for this franchise such as <span class='standout'>Dream Food, LLC</span> or <span class='standout'>Betty White</span></p>

	<p>If the LLC or Owner already owns another store, assign the new store to the <a href="/backoffice/list_franchise">existing Franchise</a>.</p>

	<p><label id="franchise_name_lbl" for="franchise_name" data-message="Please enter a entity name.">Entity name:</label><?php echo $this->form_create_franchise['franchise_name_html']; ?></p>

	<p><?php echo $this->form_create_franchise['CheckBox_active_html']; ?><label id='CheckBox_active_lbl' for="CheckBox_active">Make franchise active</label></p>

	<p><label id='description_lbl' for="description">Description</label></p>

	<p><?php echo $this->form_create_franchise['franchise_description_html']; ?></p>

	<p><?php echo $this->form_create_franchise['Submit_html']; ?></p>

</div>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>