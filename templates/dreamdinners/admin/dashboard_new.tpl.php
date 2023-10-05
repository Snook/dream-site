<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php if (isset($this->updateRequired) && $this->updateRequired) {
	$this->setOnLoad("processMetrics();");
} ?>
<?php $this->assign('page_title','Calendar-Based Dashboard'); ?>
<?php $this->assign('topnav','reports'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard_old.min.js'); ?>
<?php if (isset($_REQUEST['print']) && $_REQUEST['print'] == "true")
{
	$this->assign('print_view', true);

}
else
{
	$this->assign('print_view', false);
}
?>
<?php $this->setOnload('dashboard_init();'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (false) { ?>
	<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:50px; padding:25px;">
		<span style="color:red;">This page is down for maintenance. Please Check Back Shortly.</span>
	</div>
	<?php include $this->loadTemplate('admin/page_footer.tpl.php');
	return; }
?>

	<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:0px; padding:5px;">
		<span style="color:green;">Dashboard (by Calendar Month)</span>
		<div style="float:right"><a href="/backoffice/dashboard-menu-based" class="btn btn-primary btn-sm">to Menu-Month Dashboard</a></div>
	</div>

	<form id="dashboard_form" action="/backoffice/dashboard-new" method="post">
		<?php echo $this->form_array['hidden_html'];?>

		<?php if (!$this->print_view) { ?>

			<table style="width: 100%; margin:0px; padding:0px;">
				<?php if (!$this->showReportTypeSelector) { ?>
					<tr>
						<td colspan="4" style="text-align:center"><h2><?php echo $this->titleString; ?></h2></td><td style="text-align:right">
							<?php if (!$this->showReportTypeSelector) { ?>
								<?php  if ($this->showCurrentMonth) { ?>
									<button onclick="toggleMonth('previous');" class="btn btn-primary btn-sm">Show previous month</button>
								<?php } else { ?>
									<button onclick="toggleMonth('current');" class="btn btn-primary btn-sm">Show current month</button>
								<?php } ?>
							<?php } ?>
							or Pick a Month: <?php echo $this->form_array['override_month_html']; ?>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<td style="text-align:right;"><b>Single Store</b></td>
						<td colspan="3"><?php echo $this->form_array['report_type_html']['dt_single_store']; ?><label for="report_typedt_single_store" >Select Store</label>:
							<?php echo $this->form_array['store_html']; ?></td>
						<?php  if ($this->showCurrentMonth) { ?>
							<td><button onclick="toggleMonth('previous');" class="btn btn-primary btn-sm">Show previous month</button></td>
						<?php } else { ?>
							<td><button onclick="toggleMonth('current');" class="btn btn-primary btn-sm">Show current month</button></td>
						<?php } ?>

					</tr>
					<tr>
						<td style="text-align:right; vertical-align:top;"><b>Roll up</b></td>
						<td  style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_corp_stores']; ?><label for="report_typedt_corp_stores" >Corporate Stores</label></td>
						<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_non_corp_stores']; ?><label for="report_typedt_non_corp_stores" >Franchise Stores</label></td>
						<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_all_stores']; ?><label for="report_typedt_all_stores" >All Stores</label></td>
						<td width="140" style="text-align:center;">or <br />Pick a Month: <?php echo $this->form_array['override_month_html']; ?></td>
					</tr>
					<tr>
						<td colspan="5"><div>
								Note:  Historical values in roll up report are based on current active stores and may not reflect total national revenue.
							</div>
						</td>
					</tr>
					<?php if (isset($this->includes_delivered_revenue) && $this->includes_delivered_revenue) { ?>
						<tr>
							<td colspan="5"><div>
									Delivered Revenue Note:  On this Calendar Month Dashboard the Distribution Center&rsquo;s revenue is included in Regular Orders rows.
								</div>
							</td>
						</tr>

						<tr>
							<td colspan="5"><div>
									Delivered Rollup Note:  On this Calendar Month Dashboard the SLC and RH Distribution Center&rsquo;s revenue are included in the Corporate Store rollup. The Mobile DC revenue is included in the Franchise Store rollup.
								</div>
							</td>
						</tr>
					<?php } ?>

				<?php } ?>

			</table>

			<?php if (!isset($this->dashboard_error)) { ?>
				<div style="float:right;">
					<a  href="javascript:export_order_list();">Export Order List <img style="vertical-align:middle;margin-bottom:.25em;" alt="Export" src="<?php echo IMAGES_PATH;?>/admin/icon/page_excel.png"></a>&nbsp;&nbsp;
					<a  href="javascript:print_dashboard();">Printer-Friendly Version <img style="vertical-align:middle;margin-bottom:.25em;" alt="Print" src="<?php echo IMAGES_PATH;?>/admin/icon/printer.png"></a>
				</div>
			<?php } ?>

		<?php } else { ?>
			<table style="width: 100%; margin:0px; padding:0px;">
				<tr>
					<td colspan="5" style="text-align:center"><h2><?php echo $this->titleString; ?></h2> for <?php echo CTemplate::dateTimeFormat(date("Y-m-d H:i:s", time())); ?></td>
				</tr>
			</table>

		<?php } ?>

		<?php if (isset($this->dashboard_error)) {?>

			<div style="color:red; width:100%; font-weight:bold; text-align:center;"><?php echo $this->dashboard_error; ?></div>
			<div id="busy_updating" style="display:none">The Metrics are currently being updated. Please wait. The page will reload in a few moments when the update is complete.<br />
				<div style="vertical-align:center; text-align:center; width:100%; height:100%;"><br /><br /><img src="<?php echo IMAGES_PATH?>/admin/throbber_processing_noborder.gif" /></div></div>
		<?php } else { ?>

			<?php include $this->loadTemplate('admin/dashboard_agr_summary.tpl.php'); ?>

			<?php include $this->loadTemplate('admin/dashboard_agr_breakdown.tpl.php'); ?>

			<?php include $this->loadTemplate('admin/dashboard_guests.tpl.php'); ?>

			<?php include $this->loadTemplate('admin/dashboard_sessions.tpl.php'); ?>

			<?php include $this->loadTemplate('admin/dashboard_retention.tpl.php'); ?>

			<?php
			if (!$this->isFutureMonth && !$this->isDistantMonth)
				include $this->loadTemplate('admin/dashboard_rankings.tpl.php');
			?>

		<?php } ?>

	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>