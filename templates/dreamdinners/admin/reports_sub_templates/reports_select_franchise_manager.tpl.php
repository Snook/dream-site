<div style="width:750px; margin-left:auto; margin-right:auto;">
<div style="float:left;">
<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><a href="main.php?page=admin_main"><b>BackOffice Home</b></a></td>
	</tr>
</table>

<br/>

<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><b>Financial &amp; Trending</b></td>
	</tr>
	<tr>
		<td>
			Dream Dashboard:&nbsp;&nbsp;<a href="main.php?page=admin_dashboard_menu_based">Menu-based</a> | <a href="main.php?page=admin_dashboard_new">Calendar-based</a><br/>
			Trending Report:&nbsp;&nbsp;<a href="main.php?page=admin_reports_trending_menu_based">Menu-based</a> | <a href="main.php?page=admin_reports_trending">Calendar-based</a><br/>
			<a href="main.php?page=admin_reports_goal_management_v2">Session Summary and Goal Setting</a><br />
			<a href="main.php?page=admin_reports_financial_statistic_v2">Financial/Statistical Report</a><br />
			<?php if ((isset($this->hasCorporateOverride) && $this->hasCorporateOverride) || (isset($this->hasWeeklyReportAccess) && $this->hasWeeklyReportAccess))
			{ ?>
				<a href="main.php?page=admin_reports_dream_weekly_v2">Weekly Report</a><br />
			<?php } ?>
			<a href="main.php?page=admin_reports_payment_reconciliation">Payment Reconciliation Report</a><br />
			<a href="main.php?page=admin_reports_cash_flow">Cash Flow Report</a><br />
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_fundraiser">Fundraising</a><br />
			<?php } ?>
			<a href="main.php?page=admin_reports_royalty">Royalty Report</a><br/>
			<?php if (isset($this->hasPandLAccess) && $this->hasPandLAccess) { ?>
			<a href="main.php?page=admin_reports_financial_performance">Financial Performance Report</a><br />
			<a href="main.php?page=admin_reports_p_and_l_input">Profit & Loss (P&L) Financial Summary Input Template</a><br/>
			<?php } ?>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_reports_growth_scorecard">Growth Scorecard</a><br/>
            	<a href="main.php?page=admin_reports_growth_dashboard">Growth Dashboard</a><br/>
				<a href="main.php?page=admin_reports_cornerstone_movement_contest">Cornerstone Movement Contest Report</a><br/>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="form_subtitle_cell"><b>Guest &amp; Orders</b></td>
	</tr>
	<tr>
		<td>
			<a href="main.php?page=admin_reports_door_dash_import">Door Dash Report</a><br/>
			<?php if ($this->storeSupportsMembership) { ?>
				<a href="main.php?page=admin_reports_meal_prep_plus">Meal Prep+ Report</a><br/> <?php } ?>
			<a href="main.php?page=admin_reports_food_sales">Menu Item Sales Report</a><br/>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_reports_session_host">Session Host Report</a><br/>
			<?php } ?>
			<a href="main.php?page=admin_reports_customer">Order History</a><br/>
			<a href="main.php?page=admin_reports_cancellations">Cancellation Report</a><br/>
			<a href="main.php?page=admin_reports_saved_orders">Saved Orders Report</a><br/>
			<?php if ($this->storeSupportsDelivery && !$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_reports_delivery_orders">Delivery Orders Report</a><br/>
			<?php } ?>
			<a href="main.php?page=admin_reports_user_retention">Inactive Guest Status Report</a><br/>
			<a href="main.php?page=admin_reports_coupon">Coupon Report</a><br/>
			<a href="main.php?page=admin_reports_entree">Entr&eacute;e Report</a><br/>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_reports_store_credit">Credit Report</a><br/>
				<a href="main.php?page=admin_reports_customer_referrals_revenue">Customer Referral Revenue</a><br/>
				<a href="main.php?page=admin_reports_preferred_users">Preferred Users</a><br/>
				<a href="main.php?page=admin_reports_points_status_by_date">PLATEPOINTS Awards</a><br/>
			<?php } ?>
			<a href="main.php?page=admin_reports_user_data_v2">Guest Details Report</a><br/>
			<a href="main.php?page=admin_food_testing_survey">Food Testing Store Surveys</a><br/>
		</td>
	</tr>
</table>
</div>
<div style="float:right">
<br />
<br />
<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><b>Supporting Menu Materials</b></td>
	</tr>
	<tr>
		<td>
			<a href="main.php?page=admin_session_tools_printing">Generic Menu Supporting Documents</a><br/>
			<a href="main.php?page=admin_reports_customer_menu_item_labels&interface=1">Generic Cooking Instruction Labels</a><br/>
			<?php if (!$this->storeIsDistributionCenter) { ?>
				<a href="main.php?page=admin_reports_menu_item_nutritional_labels">Nutritional Labels</a><br/>
			<?php } ?>
			<?php if ($this->storeSupportsPlatePoints) { ?>
				<a href="main.php?page=admin_user_plate_points&print_blank_form=true" target="_blank">Blank PLATEPOINTS Enrollment Form</a><br/>
			<?php } ?>
		</td>
	</tr>
</table>

<br/>

<table style="width: 100%;">
	<tr>
		<td class="form_subtitle_cell"><b>Document Download Links</b></td>
	</tr>
	<tr>
		<td>
			<a href="main.php?page=admin_reports&file_id=1">Food Cost Adjustments</a><br/>
			<a href="main.php?page=admin_reports&file_id=2">Sales Adjustment Request</a><br/>
		</td>
	</tr>
</table>
</div>
</div>
