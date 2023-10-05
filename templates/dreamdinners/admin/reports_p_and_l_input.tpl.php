 <?php
$this->setScript('head', SCRIPT_PATH . '/admin/vendor/accounting.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_p_and_l_input.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_expenses.min.js');
$this->setScriptVar('store_id = ' . $this->store_id . ';');
$this->setScriptVar('month = ' . $this->month . ';');
$this->setScriptVar('year = ' . $this->year . ';');
$this->setScriptVar('isHomeOfficeAccess = ' . ($this->isHomeOfficeAccess ? 'true' : 'false') . ';');


$this->assign('topnav','reports');
$this->assign('page_title','Proft and Loss Form');
$this->setOnload('reports_p_and_l_init();');
$this->setCSS(CSS_PATH . '/admin/admin-goal_tracking.css');

include $this->loadTemplate('admin/page_header.tpl.php');  ?>

<form id="p_and_l_form"  method="post">


<div id="report_header" style="text-align:center;">
    <h3><?php echo $this->page_title;?></h3>
    	<?php
    		if (isset($this->form_session_list['store_html']) ) {
    		    echo '<strong>Store&nbsp;</strong>' .  $this->form_session_list['store_html'];
    		}
    	?>
    <div id="report_header">
    	<?php echo $this->form_session_list['month_popup_html'] . "&nbsp";
    	 echo $this->form_session_list['year_field_001_html'] . "&nbsp";?>
    	 <button type="button" class="btn btn-primary btn-sm" id="report_submit" onclick="_report_submitClick();" >Run Report</button>

    	</div>

</div>

<div id="finance_div" style="border: solid 2px green;">
<?php include $this->loadTemplate('admin/subtemplate/reports_P_and_L_input.tpl.php'); ?>
</div>

</form>

<?php
include $this->loadTemplate('admin/page_footer.tpl.php');
?>