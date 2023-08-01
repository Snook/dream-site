<?php $this->assign('helpLinkSection', 'CR'); ?>
<?php
$REPORTGIF = null;
$PAGETITLE = "Order History";
$HIDDENPAGENAME = "admin_reports_customer";
$SHOWSINGLEDATE = true;
$SHOWRANGEDATE = true;
$SHOWMONTH = true;
$SHOWYEAR = true;
$ADDFORMTOPAGE = true;
$OVERRIDESUBMITBUTTON = true;
$SHOWDATEHEADER = true;

$this->setScript('head', SCRIPT_PATH . '/admin/reports_customer.min.js');
$this->setOnload('reports_customer_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>
	<table>
		<tr>
			<td>This report will return a list of customers that have registered for a session during the selected timespan.
				The <i>Orders Made</i> variable represents the number of orders created during the given timespan. The selected Order Info columns will repeat to
				the right for the number of orders of each listed guest.
			</td>
		</tr>
		<tr>
			<td style="color:red"> Update: For a row count of less than 300 guests (and a maximum of 24 sessions) the report will be generated as a formatted Excel document. Reports exceeding these
								   limits will be delivered as a CSV file.
			<td>
		</tr>
	</table>

	<div class="row">
		<div class="col-6">
			<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
			<div style="margin-top:12px;">
				<input type="checkbox" name="drsw_suppress_repeating_columns" id="drsw_suppress_repeating_columns"/><label for="drsw_suppress_repeating_columns">Suppress Repeating Columns (use comma separated lists instead)</label><br/>
			</div>
		</div>

		<div class="col-6">
			<H2>Sections to Display</H2>
			<input type="checkbox" name="drsw_contact_info" id="drsw_contact_info" checked="checked"/><label for="drsw_contact_info">Show Phone Information</label><br/>
			<input type="checkbox" name="drsw_phys_add" id="drsw_phys_add" checked="checked"/><label for="drsw_phys_add">Show Physical Address</label><br/>
			<input type="checkbox" name="drsw_dr_info" id="drsw_dr_info"/><label for="drsw_dr_info">Show Loyalty Program Information</label><br/>
			<input type="checkbox" name="drsw_add_user_info" id="drsw_add_user_info"/><label for="drsw_add_user_info">Show Additional User Information</label><br/>
			<hr/>
			<h2>Order Info</h2>
			<input type="checkbox" style="margin-left:0px;" name="drsw_order_type" id="drsw_order_type" checked="checked"/><label for="drsw_order_type">Show Order Type</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_order_time" id="drsw_order_time" checked="checked"/><label for="drsw_order_time">Show Time Order was Placed</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_serving_count" id="drsw_serving_count"/><label for="drsw_serving_count">Show Servings Count</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_total_item_count" id="drsw_total_item_count"/><label for="drsw_total_item_count">Show Total Item Count</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_core_menu_item_count" id="drsw_core_menu_item_count"/><label for="drsw_core_menu_item_count">Show Core Menu Item Count</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_rewards_level" id="drsw_rewards_level"/><label for="drsw_rewards_level">Show Order's Loyalty Program Status</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_order_id" id="drsw_order_id"/><label for="drsw_order_id">Show Order ID</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_no_show" id="drsw_no_show"/><label for="drsw_no_show">Show "No Show" Status</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_ticket_amount" id="drsw_ticket_amount"/><label for="drsw_ticket_amount">Show Ticket Amount</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_coupon_code" id="drsw_coupon_code"/><label for="drsw_coupon_code">Show Coupon Code</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_in_store_status" id="drsw_in_store_status"/><label for="drsw_in_store_status">In-Store Sign up</label><br/>
			<input type="checkbox" style="margin-left:0px;" name="drsw_platepoints_consumed" id="drsw_platepoints_consumed"/><label for="drsw_platepoints_consumed">PlatePoints Dinner Dollars Consumed</label>
			<hr />
			Filter by Customer Type: <br/><?= $this->form_session_list['customer_type_filter_html'] ?>
			<hr />
			<?php echo $this->form_session_list['report_submit_html']; ?>
		</div>
	</div>

	</form>

<?php
$varcount = 0;
if ($this->no_results == true)
{

	$r_display = '<table id="no_results_msg"><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>';
	echo $r_display;
}
?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>