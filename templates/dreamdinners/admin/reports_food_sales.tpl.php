<?php
// constants for all report pages.
//$PAGETITLE = "Food Sales Report";
$HIDDENPAGENAME = "admin_reports_food_sales";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;

$this->setScriptVar('store_id = ' . (!empty($this->cur_store_id) ? $this->cur_store_id : 'false') . ';');

$this->setScriptVar('show_store_selectors = ' . (!empty($this->show_store_selectors) ? 'true' : 'false') . ';');

$this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_food_sales.min.js');

if ($this->show_store_selectors)
{
	$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
	$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');
	$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');
}

$this->setOnload('reports_food_sales_init();');
$this->assign('topnav','reports');


?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>

	<div id="chosen_stores" style="display:none; position:fixed; left:0; top:5; background-color:#f1e8d8; width:230px; max-width:230px;">
		<div onclick="$('#chosen_stores').hide();" style="float:right;"><img src="<?php echo ADMIN_IMAGES_PATH;?>/icon/cross.png" /></div>
		<h3>Chosen stores</h3>
		<div id="chosen_stores_inner" style=" height:660px; display:block; overflow-y:auto; width:100%"></div>

	</div>


	<div style="width: 100%; text-align:center; margin:10px;"><h3 id="item_list_title">Menu Item Sales Report</h3>
	</div>

	<div>

		<?php if (isset($this->store_data))	 { ?>
			<div id="store_selector_open">
				<?php include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php'); ?>
				<span id="set_selected_stores" class="button" style="margin:10px;">Set Store Selection</span>
			</div>
			<div id="store_selector_closed" style="display:none">
				<span id="unset_selected_stores" class="button" style="margin:10px;">Choose New Stores</span>
			</div>


		<?php } ?>

		<div style="clear:both; height:10px; width:100%;"></div>

	</div>

	<div style="width: 100%;">

		<div  style="padding:5px; margin:5px; border:1px solid green;">
			<h3 style="background-color:#f1e8d8; ">Set Name Search String</h3>
			<?php echo $this->form_session_list['search_string_html']?>&nbsp;(Optional)
		</div>


		<div style="width:30%; float:left; padding:5px; border:1px solid green;">
			<h3 style="background-color:#f1e8d8; ">Set Date Range</h3>
			<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>

			<div class="mb-2">
				Omit guests with orders from menu:
				<?php echo $this->form_session_list['order_since_menu_id_html']?>
				<hr/>
			</div>

			<div>
				<span id="set_date_range" class="button">Set Date Range and Return Items</span>
				<br /><span class="note">Note: Date Range is applied to Session Date.</span>
			</div>
		</div>

		<div id="item_selector_outer" style="width:67%;  border:1px solid green; float:right; height:400px; max-height:400px; padding:5px;">

			<!-- 	<h3 id="item_list_title" style="background-color:#f1e8d8; "></h3> -->

			<div id="item_selector_inner">
				<span style="font-style:italic;">Select a Date Range</span>
			</div>
		</div>
	</div>

	<div style="clear:both; height:10px; width:100%;"></div>

	<div id="guest_list_outer" style="width: 100%; height:550px; border:1px solid green; display:none;">
	</div>

	<div id="menu_info_outer" style="width: 100%; height:auto; border:1px solid green; display:none;">
	</div>




<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>