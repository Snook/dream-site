<?php
$this->assign('page_title','Employee Landing Page');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
<div style="text-align: center"><h3>Welcome to the Dream Dinners Store Management Web Site</h3></div>
<br />

<div style="text-align: center">
<input id="helpdesk_button" type="button" class="btn btn-primary btn-sm" value="Contact Dream Dinners Support"><br />
</div>
<br />

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>