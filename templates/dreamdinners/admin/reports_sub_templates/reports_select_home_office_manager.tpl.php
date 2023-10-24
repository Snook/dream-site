<div style="width:750px; margin-left:auto; margin-right:auto;">
	<div style="float:left;">
		<table style="width: 100%;">
			<tr>
				<td class="form_subtitle_cell"><a href="/backoffice/main"><b>BackOffice Home</b></a><br/></td>
			</tr>
		</table>

		<br/>

		<table style="width: 100%;">
			<tr>
				<td class="form_subtitle_cell"><b>Financial &amp; Trending</b></td>
			</tr>
			<tr>
				<td>
					Dream Dashboard:&nbsp;&nbsp;<a href="/backoffice/dashboard-menu-based">Menu-based</a> | <a href="/backoffice/dashboard-new">Calendar-based</a><br/>
					Trending Report:&nbsp;&nbsp;<a href="/backoffice/reports_trending_menu_based">Menu-based</a> | <a href="/backoffice/reports_trending">Calendar-based</a><br/>
					<a href="/backoffice/reports_trending_new">Rolling-13 Month Business Analysis Report (<span style="color:red">beta</span>)</a><br/>
					<a href="/backoffice/reports_goal_management_v2">Goal Management</a><br/>
					<a href="/backoffice/reports_financial_statistic_v2">Financial/Statistical Report</a><br/>
					<a href="/backoffice/reports_dream_weekly_v2">Weekly Report</a><br/>
					<a href="/backoffice/reports_payment_reconciliation">Payment Reconciliation Report</a><br/>
					<a href="/backoffice/reports_cash_flow">Cash Flow Report</a><br/>
					<a href="/backoffice/fundraiser">Fundraising</a><br/>
					<a href="/backoffice/reports_same_store_sales">Same Store Sales Report</a><br/>
					<a href="/backoffice/reports_dashboard_aggregate_v2">Dashboard Aggregate Report</a><br/>
					<a href="/backoffice/reports_financial_performance">Financial Performance Report</a><br/>
					<a href="/backoffice/reports_royalty">Royalty Report</a><br/>
					<a href="/backoffice/reports_p_and_l_input">Profit & Loss (P&L) Financial Summary Input Template</a><br/>
					<a href="/backoffice/reports_growth_scorecard">Growth Scorecard</a><br/>
					<a href="/backoffice/reports_growth_dashboard">Growth Dashboard</a><br/>
					<a href="/backoffice/reports_business_health_assessment">Business Health Assessment</a><br/>
				</td>
			</tr>
		</table>

		<table style="width: 100%;">
			<tr>
				<td class="form_subtitle_cell"><b>Guests &amp; Orders</b></td>
			</tr>
			<tr>
				<td>
					<a href="/backoffice/reports_meal_prep_plus">Meal Prep+ Report</a><br/>
					<a href="/backoffice/reports_food_sales">Menu Item Sales Report</a><br/>
					<a href="/backoffice/reports_session_host">Session Host Report</a><br/>
					<a href="/backoffice/reports_customer">Order History</a><br/>
					<a href="/backoffice/reports_cancellations">Cancellation Report</a><br/>
					<a href="/backoffice/reports_saved_orders">Saved Orders Report</a><br/>
					<a href="/backoffice/reports_delivery_orders">Delivery Orders Report</a><br/>
					<a href="/backoffice/reports_user_retention">Inactive Guest Status Report</a><br/>
					<a href="/backoffice/reports_coupon">Coupon Report</a><br/>
					<a href="/backoffice/reports_entree">Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports_entree_delivered">Shipping Entr&eacute;e Report</a><br/>
					<a href="/backoffice/reports_store_credit">Credit Report</a><br/>
					<a href="/backoffice/reports_customer_referrals_revenue">Customer Referral Revenue</a><br/>
					<a href="/backoffice/reports_preferred_users">Preferred Users</a><br/>
					<a href="/backoffice/reports_user_data_v2">Guest Details Report</a><br/>
					<a href="/backoffice/reports_points_status_by_date">PLATEPOINTS Awards</a><br/><br/>
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
					<a href="/backoffice/session-tools-printing">Generic Menu Supporting Documents</a><br/>
					<a href="/backoffice/reports_customer_menu_item_labels?interface=1">Generic Cooking Instruction Labels</a><br/>
					<a href="/backoffice/reports_menu_item_nutritional_labels">Nutritional Labels</a><br/>
					<?php if ($this->storeSupportsPlatePoints) { ?>
					<a href="/backoffice/user_plate_points?print_blank_form=true" target="_blank">Blank PLATEPOINTS Enrollment Form</a><br/>
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
					<a href="/backoffice/reports?file_id=1">Food Cost Adjustments</a><br/>
					<a href="/backoffice/reports?file_id=2">Sales Adjustment Request</a><br/>
				</td>
			</tr>
		</table>

		<br/>

		<?php if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'HO_REPORTING') {?>
			<table style="width: 100%;">
				<tr>
					<td class="form_subtitle_cell"><b>Home Office Reporting Server Specific</b></td>
				</tr>
				<tr>
					<td>
						<a href="/backoffice/home_office_reports_site_aggregate_samestore">Same Store Sales Site Aggregate Report</a><br/>
						<a href="/backoffice/home_office_reports_dashboard_aggregate">Dream Dashboard Aggregate</a><br/>
						<a href="/backoffice/home_office_reports_weekly_metrics">Weekly Metrics Report (<span style="color:red">beta</span>)</a><br/>
						<a href="/backoffice/home_office_reports_guest_metrics">Guest Metrics Report (<span style="color:red">beta</span>)</a><br/>
					</td>
				</tr>
			</table>

			<br/>
		<?php  } ?>

		<table style="width: 100%;">
			<tr>
				<td class="form_subtitle_cell"><b>Home Office Reporting</b></td>
			</tr>
			<tr>
				<td>
					<a href="/backoffice/reports_cornerstone_movement_contest">Cornerstone Movement Contest Report</a><br/>
					<a href="/backoffice/performance_override">Performance Override Form</a><br/>
					<a href="/backoffice/reports_site_royalty_national">Royalty Report by Store</a><br/>
					<a href="/backoffice/reports_store_contact_information?format=store&amp;export=xlsx">Store Contact Information - Active</a><br/>
					<a href="/backoffice/reports_store_contact_information?format=owner&amp;active=1&amp;export=xlsx">Entity Contact Information - Active</a><br/>
					<a href="/backoffice/reports_store_contact_information&amp;format=owner&amp;active=0&amp;export=xlsx">Entity Contact Information - Inactive</a><br/>
					<a href="/backoffice/reports_my_meals">My Meals Ratings</a><br/>
					<a href="/backoffice/reports_my_meals_comments">My Meals Comments</a><br/>
					<a href="/backoffice/food-testing">Food Testing Manager</a><br/>
					<a href="/backoffice/food-testing-survey">Food Testing Store Surveys</a><br/>
					<a href="/backoffice/reports_menu_skipping">Menu Skipping Report (<span style="color:red">beta</span>)</a><br/>
					<a href="/backoffice/reports_door_dash_import">Door Dash Report & Import</a><br/>
					<a href="/backoffice/reports_meal_prep_plus_site_wide">Meal Prep+ Subscriptions (All stores)</a><br/>
					<a href="/backoffice/reports_national_entree_projection">Master Preorder Export</a><br/>
					<a href="/backoffice/reports_menu_planning">Menu Planning Report</a><br/>
				</td>
			</tr>
		</table>
	</div>
</div>