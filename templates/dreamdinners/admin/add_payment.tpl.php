<?php $this->assign('page_title','Add Payment'); ?>
<?php $this->assign('topnav','sessions'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>




<form action="" method="post" onSubmit="return _check_form(this);">
	<table style="width: 100%;">
	  <tbody>
		<tr>
	      <td>
	        <table>
				<tr><td align="right"><?=$this->form_edit_order['submit_payment_html']?></td>
				<td>&nbsp;</td></tr>
          	</table>
	      </td>
	    </tr>
	  </tbody>
	</table>
</form>


<?php

include $this->loadTemplate('admin/page_footer.tpl.php');
?>
