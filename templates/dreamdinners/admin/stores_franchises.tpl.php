<?php
$this->assign('page_title','Stores and Franchises');
$this->assign('topnav', 'store');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
      Welcome to the Stores and Franchises section.<span class="header"><br />
      <br />
      Available Tools:<br />
	  <a href="/backoffice/list-franchisees">Browse/Edit Franchisees</a>
	  <br />
	  <a href="/backoffice/create-franchise">Create a New Franchise</a>
	  <br />
	    <a href="/backoffice/list-franchise">Browse/Edit Store Franchise</a>
	  <br />
	  <a href="/backoffice/list-stores">Browse/Edit Stores</a>
	   <br />
	  <a href="/backoffice/create-store">Create a New Store</a>
	   <br />
	   	  <a href="/backoffice/promotions">Promotions</a>
	   <br />
	   	  <a href="/backoffice/resources">Resources</a>
	   <br />
	   	  <a href="/backoffice/coupons">Coupons</a>
	   <br />
	   	  <a href="/backoffice/menu-editor">Menu Editor</a>
	   <br />


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>