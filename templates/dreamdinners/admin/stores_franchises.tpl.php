<?php
$this->assign('page_title','Stores and Franchises');
$this->assign('topnav', 'store');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
      Welcome to the Stores and Franchises section.<span class="header"><br />
      <br />
      Available Tools:<br />
	  <a href="main.php?page=admin_list_franchisees">Browse/Edit Franchisees</a>
	  <br />
	  <a href="main.php?page=admin_create_franchise">Create a New Franchise</a>
	  <br />
	    <a href="main.php?page=admin_list_franchise">Browse/Edit Store Franchise</a>
	  <br />
	  <a href="main.php?page=admin_list_stores">Browse/Edit Stores</a>
	   <br />
	  <a href="main.php?page=admin_create_store">Create a New Store</a>
	   <br />
	   	  <a href="main.php?page=admin_promotions">Promotions</a>
	   <br />
	   	  <a href="main.php?page=admin_resources">Resources</a>
	   <br />
	   	  <a href="main.php?page=admin_coupons">Coupons</a>
	   <br />
	   	  <a href="main.php?page=admin_menu_editor">Menu Editor</a>
	   <br />


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>