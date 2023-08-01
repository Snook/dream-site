<?php
$this->assign('page_title','Random Charactor Generator');
$this->assign('topnav','store');
include $this->loadTemplate('admin/page_header.tpl.php');
?>

<br />

<?php if ( $this->user_type ===  CUser::SITE_ADMIN) { ?>

<!-- Password Generator content start -->
<FORM><p>Every time this page is displayed, the server generates high quality random alphanumeric character strings than may be used as secure passwords.</p>
<?php include $this->loadTemplate('admin/generate_pswd.php'); ?>
<P><INPUT TYPE="button" VALUE="REGENERATE" onClick="parent.location='main.php?page=admin_pwd_generator'"></P>
</FORM>
<br/>
<p>*Double click the generated number to select it, then use your keyboard or mouse to copy and paste.</p>
<br/>
<!-- Password Generator content end -->

<?php } ?>




<?php if ( $this->user_type ===  CUser::FRANCHISE_OWNER  || $this->user_type ===  CUser::FRANCHISE_STAFF || $this->user_type ===  CUser::FRANCHISE_MANAGER ) { ?>
<br/><br/>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
