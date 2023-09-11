<?php
$this->assign('page_title','Tools');
$this->assign('topnav', 'tools');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
      Welcome to the Tools section.<br />
      <br />
      Available Tools:<br />
	  <a href="/?page=admin_errors">Error Log</a>
	  <br />
	  <a href="/?page=admin_manage_survey">Manage Test Recipes</a>
	  <br />
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>