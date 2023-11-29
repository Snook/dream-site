<h2 id="gd_title-<?php echo $this->session_info['id'];?>" data-session_id="<?php echo $this->session_info['id'];?>"><?php echo $this->section_title ?></h2>
<table class="guest_details_table">
	<?php if (!empty($this->session_info[$this->type])) { ?>
		<?php
		foreach ($this->session_info[$this->type] AS $booking)
		{
			if ($booking['status'] == CBooking::ACTIVE)
			{
				$rowspan = 6;

				if (!empty($booking['order_user_notes']))
				{
					$rowspan++;
				}

				if ($booking['user']->platePointsData['status'] == 'active' || !empty($booking['food_testing_survey_id']))
				{
					if (!empty($this->food_testing_recipes) && (!empty($booking['user']->platePointsData['current_level']['rewards']['food_testing']) || !empty($booking['food_testing_survey_id'])))
					{
						$rowspan++;
					}
				}
			}
			else
			{
				$rowspan = 3;
			}

			?>
			<tbody id="guest_details_tbody_id_<?php echo $booking['id']; ?>" class="guest guest_details_tbody <?php echo ($booking['status'] != CBooking::ACTIVE) ? 'is_past' : ''; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>">
			<tr style="background-color: darkcyan">
				<td rowspan="<?php echo $rowspan;?>" class="value guest">

					<a class="guestname" id="gd_guest-<?php echo $booking['id']; ?>" href="/backoffice/user_details?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"><?php echo $booking['firstname']; ?> <?php echo $booking['lastname']; ?></a>

					<div style="float: right;">

						<?php if (!empty($booking['corporate_crate_client']) && !empty($booking['corporate_crate_client']->is_active)) { ?><img alt="<?php echo $booking['corporate_crate_client']->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $booking['corporate_crate_client']->icon_path; ?>_icon.png" class="float-right ml-1" data-tooltip="<?php echo $booking['corporate_crate_client']->company_name; ?>" /><?php } ?>
						<?php if ($booking['user_type'] != CUser::CUSTOMER) { ?><img alt="Dream Dinners Staff" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/dreamdinners.png" class="float-right ml-1" data-tooltip="Dream Dinners Staff" /><?php } ?>
						<?php if ($booking['user']->membershipData['enrolled']) { ?><a href="/backoffice/user_membership?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img alt="Meal Prep+" data-tooltip="Meal Prep+" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/membership/badge-membership-16x16.png" class="float-right ml-1" /></a><?php } ?>
						<?php if (!empty($booking['preferred_type'])) { ?><a href="/backoffice/preferred?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img alt="Preferred Guest" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/star_grey.png" class="float-right ml-1" data-tooltip="Preferred Guest" /></a><?php } ?>
						<?php if (empty($booking['user']->preferences['TC_DELAYED_PAYMENT_AGREE']['value'])) { ?><img alt="Delayed Payment terms" data-delayed_payment_tc="<?php echo $booking['user_id']; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/money_dollar.png" style="cursor: pointer; float: right; margin-left: 4px;" data-tooltip="Has not agreed to Delayed Payment terms" /><?php } ?>
						<?php if (!empty($booking['is_birthday_month'])) { ?><img alt="Happy Birthday" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cake.png" class="float-right ml-1" data-tooltip="Birthday this Month" /><?php } ?>
						<?php if ($booking['user']->platePointsData['status'] == 'active' || $booking['user']->platePointsData['userIsOnHold']) { ?>
							<?php if ($booking['user']->platePointsData['userIsOnHold']) { ?>
								<a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img data-user_id_pp_tooltip="<?php echo $booking['user']->id; ?>" alt="PLATEPOINTS On Hold" data-tooltip="PLATEPOINTS On Hold" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-hold-16x16.png" class="float-right ml-1" /></a>
							<?php } else { ?>
								<a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img data-user_id_pp_tooltip="<?php echo $booking['user']->id; ?>" alt="PLATEPOINTS <?php echo $booking['user']->platePointsData['current_level']['title'];?>" data-tooltip="PLATEPOINTS <?php echo $booking['user']->platePointsData['current_level']['title'];?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $booking['user']->platePointsData['current_level']['image'];?>-16x16.png" class="float-right ml-1" /></a>
							<?php } ?>
						<?php } ?>
						<div class="clear"></div>
					</div>

					<div id="gd_guest_menu-<?php echo $booking['id']; ?>" class="guest_menu_div">
						<ul class="guest_menu">

							<li><a href="/backoffice/user_details?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">View Guest</a></li>
							<li><a href="/backoffice/account?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Edit Guest</a></li>
							<li><a href="/backoffice/email?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Email Guest</a></li>

							<?php if ($booking['status'] != CBooking::RESCHEDULED) { ?>
								<li><a data-view_order_details="<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"
									   href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&amp;order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">View Order</a></li>
							<?php } ?>
							<li><a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Edit Order</a></li>
							<?php if (!empty($booking['can_reschedule'])) { ?>
								<li><a id="gd_reschedule-<?php echo $booking['id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>">Reschedule</a></li>
							<?php } else { ?>
								<li data-tooltip="Can not reschedule" style="text-decoration: line-through;">Reschedule</li>
							<?php } ?>
							<li><a href="/backoffice/order-details-view-all?customer_print_view=1&amp;session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>" target="_blank">Order Summary</a></li>
							<li><a href="/backoffice/order-mgr-delivered?user=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Place <?php echo $this->date['this_M']; ?> Order</a></li>
							<li><a href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Order History</a></li>
							<li><a href="" class="handle-resend-shipstation" data-order_id="<?php echo $booking['order_id']; ?>">Update ShipStation</a></li>
						</ul>
						<div class="clear"></div>
					</div>


					<?php if (empty($booking['future_session'])) { ?>
						<div class="clear"></div>
						<div class="no_future_session_warning">Guest does not have a future session.</div>
					<?php }  ?>

				</td>
				<td class="title">Order Status</td>
				<td class="value small_value <?php echo $booking['status_css']; ?>"><?php echo ucfirst(strtolower($booking['status'])); ?> <?php echo $booking['edit_order_count'] > 0 ? '- Edited':''; ?></td>
				<td class="title">Balance Due</td>
				<td class="value small_value">
					<?php if (!empty($booking['can_edit'])) { ?>
					<a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">
						<?php } else { ?>
						<a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">
							<?php } ?>
							<span class="<?php echo $booking['balance_due_css']; ?>" <?php if ($booking['balance_due'] != '0.00') { ?>
								data-tooltip="Balance due to <?php echo ($booking['balance_due'] > 0) ? 'store' : 'guest'; ?>"<?php } ?>><?php echo $booking['balance_due_text']; ?>
				        </span></a>

				</td>
				<td class="title">Total Boxes Delivered</td>
				<td class="value small_value"><?php echo $booking['user_digest_total_delivered_boxes']; ?></td>
			</tr>
			<tr>
				<td class="title">Order is a Gift</td>
				<td class="value small_value"><?php echo ($booking['order_id'] == "1") ? "Yes" : "No"; ?></td>
				<td class="title">Boxes This Order</td>
				<td class="value small_value" ><?php echo $booking['total_boxes']; ?></td>
				<td class="title">Tracking Code(s)</td>
				<?php if ($booking['tracking_number'] == '') { ?>
					<td class="value small_value" style="font-weight:bold;color: darkred"><a href="" class="handle-fetch-tracking-number" data-order_id="<?php echo $booking['order_id']; ?>" data-tooltip="Request shipping data from ShipStation.">Not Yet Shipped</a></td>
				<?php }else{ ?>
					<td class="value small_value"><?php echo $booking['tracking_number']?></td>
				<?php } ?>
			</tr>
			<?php if ($booking['status'] == CBooking::ACTIVE) { ?>
				<?php if (!empty($booking['order_user_notes'])) { ?>
					<tr>
						<td class="title" style="vertical-align: top;" data-tooltip="Guest's instructions for this order">Order Instructions</td>
						<td colspan="5" class="value special_inst"><?php echo $booking['order_user_notes']; ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td class="title" style="vertical-align: top;">
						<span id="gd_guest_account_note_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-edit_mode="false" data-tooltip="Guest's account notes" class="btn btn-primary btn-sm">Account Notes</span>
						<span id="gd_guest_account_note_cancel_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" class="btn btn-primary btn-sm" style="display: none;">Cancel Edit</span>
					</td>
					<td colspan="5" class="value">
						<div id="gd_guest_account_note-<?php echo $booking['id']; ?>" class="guest_note" data-user-id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"><?php if (!empty($booking['user']->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) { echo nl2br(htmlentities($booking['user']->preferences[CUser::USER_ACCOUNT_NOTE]['value'])); } ?></div></td>
					</td>
				</tr>
				<tr>
					<td class="title" style="vertical-align: top;">
						<span id="gd_guest_note_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-hide_carryover_notes="<?php echo $booking['hide_carryover_notes']; ?>" data-edit_mode="false" data-tooltip="Admin carryover notes" class="btn btn-primary btn-sm">Admin Carryover</span>
						<span id="gd_guest_note_cancel_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-hide_carryover_notes="<?php echo $booking['hide_carryover_notes']; ?>" class="btn btn-primary btn-sm" style="display: none;">Cancel Edit</span>
					</td>
					<td colspan="5" class="value"><div <?php if (!empty($booking['hide_carryover_notes'])) { ?>style="display: none;"<?php } ?> id="gd_guest_note-<?php echo $booking['id']; ?>" class="guest_note" data-user-id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>"><?php echo (!empty($booking['user_data']['16'])) ? nl2br(htmlspecialchars($booking['user_data']['16'])) : ''; ?></div><div class="btn btn-primary btn-sm" id="gd_show_guest_note-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" <?php if (empty($booking['user_data']['16']) || empty($booking['hide_carryover_notes'])) { ?>style="display: none;"<?php } ?>>View Note</div></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="title" style="vertical-align: top;">
					<span id="gd_admin_note_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-edit_mode="false" data-tooltip="Staff notes for this order" class="btn btn-primary btn-sm">Admin Order Note</span>
					<span id="gd_admin_note_cancel_button-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" class="btn btn-primary btn-sm" style="display: none;">Cancel Edit</span>
				</td>
				<td colspan="5" class="value">
					<div id="gd_admin_note-<?php echo $booking['id']; ?>" class="guest_note" data-user-id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"><?php echo $booking['order_admin_notes']; ?></div></td>
			</tr>
			</tbody>

			<tbody id="order_details_tbody_id_<?php echo $booking['id']; ?>" class="guest order_details_tbody" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>">
			<tr>
				<td colspan="7" class="title" style="text-align: left;">
					Order Summary
					<div style="float: right;">
						<?php if (!empty($booking['can_edit'])) { ?>
							<a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" class="btn btn-primary btn-sm">Edit Order</a>

							<?php if ($booking['status'] == CBooking::SAVED) { ?>
								<span id="gd_delete_order-<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" class="btn btn-primary btn-sm">Delete Order</span>
							<?php } else { ?>
								<span id="gd_cancel_delivered_order-<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>" class="btn btn-primary btn-sm">Cancel Order</span>
							<?php } ?>

						<?php } else { ?>
							<a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" class="btn btn-primary btn-sm">Edit Payments</a>

							<span data-tooltip="Cancel order period has expired" class="btn btn-primary btn-sm disabled">Cancel Order</span>
						<?php } ?>
						<?php if (!empty($booking['can_reschedule'])) { ?>
							<a id="gd_reschedule-<?php echo $booking['id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>" class="btn btn-primary btn-sm">Reschedule</a>
						<?php } else { ?>
							<span data-tooltip="Can not reschedule" class="btn btn-primary btn-sm disabled">Reschedule</span>
						<?php } ?>
						<a class="btn btn-primary btn-sm handle-resend-shipstation" data-order_id="<?php echo $booking['order_id']; ?>">Update ShipStation</a>
						<a class="btn btn-primary btn-sm handle-fetch-tracking-number" data-order_id="<?php echo $booking['order_id']; ?>">Load Tracking Number</a>
						<a href="/backoffice/order-details-view-all?customer_print_view=1&amp;session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>" class="btn btn-primary btn-sm" target="_blank">Print</a>
						<span class="btn btn-primary btn-sm close_order_details_table" data-booking_id="<?php echo $booking['id']; ?>">Close Details</span>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="7" class="value" id="order_details_table_id_<?php echo $booking['id']; ?>" style="padding: 10px;">
					<div id="order_details_table_div_id_<?php echo $booking['id']; ?>" class="order_details_table_div">
						<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" alt="Processing" /> Getting order details
					</div>
					<div id="order_history_div_id_<?php echo $booking['id']; ?>" class="order_history_table_div"></div>
				</td>
			</tr>
			</tbody>
			<tbody>
			<tr>
				<td colspan="7" style="text-align: center; height: 4px;"></td>
			</tr>
			</tbody>
		<?php } ?>
	<?php } ?>


	<?php if (empty($this->session_info[$this->type]) ) { ?>
		<tbody class="no_guest">
		<tr>
			<td class="value" style="text-align: center; font-weight: bold;">No <?php echo $this->section_title;?> Scheduled.</td>
		</tr>
		</tbody>
		<tbody>
		<tr>
			<td colspan="7" style="text-align: center; height: 4px;"></td>
		</tr>
		</tbody>
	<?php } ?>
</table>