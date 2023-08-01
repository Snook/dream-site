<?php 
$this->assign('page_title','oops');
$this->assign('topnav','main');
include $this->loadTemplate('admin/page_header.tpl.php');
?>

<p>We're sorry, but an error has occurred and someone from Dream Dinners will be notified.<br /><br />Please try again later.</P>
	
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>