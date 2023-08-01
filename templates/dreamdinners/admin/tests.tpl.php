<?php
$this->assign('page_title','Tools');
$this->assign('topnav', 'tools');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
      Welcome to the Tests section.<br />
      <br />
      Available Tests:<br />
	  <a href="main.php?page=admin_tests&test=points_optin">Points Opt in</a>
	  <br />
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>