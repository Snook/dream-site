<?php $this->assign('topnav',$this->topNavName); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); 

if (!isset($this->pagename) || $this->pagename == "")
	echo "<table align='center' ><tr><td ><b>Sorry, you do not have access to this part of the Dream Dinners site.</b></td></tr></table>";
else
	echo "<table align='center' ><tr><td ><b>Sorry, you do not have access to the " . $this->pagename . " page</b></td></tr></table>";


?>



<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>