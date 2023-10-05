<h2 id="gd_title-<?php echo $this->session_info['id'];?>" data-session_id="<?php echo $this->session_info['id'];?>"><?php echo CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY); ?></h2>

<table class="guest_details_table">
	<?php if (!empty($this->session_info['bookings'])) { ?>
		<?php
		foreach ($this->session_info['bookings'] AS $booking)
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
				$rowspan = 2;
			}

			?>
		<tbody id="guest_details_tbody_id_<?php echo $booking['id']; ?>" class="guest guest_details_tbody <?php echo ($booking['status'] != CBooking::ACTIVE) ? 'is_past' : ''; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>">
			<tr>
				<td rowspan="<?php echo $rowspan;?>" class="value guest">

					<a class="guestname" id="gd_guest-<?php echo $booking['id']; ?>" href="/backoffice/user_details?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"><?php echo $booking['firstname']; ?> <?php echo $booking['lastname']; ?></a>

					<div style="float: right;">

						<?php if (!empty($booking['corporate_crate_client']) && !empty($booking['corporate_crate_client']->is_active)) { ?><img alt="<?php echo $booking['corporate_crate_client']->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $booking['corporate_crate_client']->icon_path; ?>_icon.png" style="float: right; margin-left: 4px;" data-tooltip="<?php echo $booking['corporate_crate_client']->company_name; ?>" /><?php } ?>
						<?php if ($booking['user_type'] != CUser::CUSTOMER) { ?><img alt="Dream Dinners Staff" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/dreamdinners.png" style="float: right; margin-left: 4px;" data-tooltip="Dream Dinners Staff" /><?php } ?>
						<?php if (($booking['dream_reward_status'] == 1 || $booking['dream_reward_status'] == 3) && $booking['dream_rewards_version'] == 3) { ?><a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img data-user_id_pp_tooltip="<?php echo $booking['user']->id; ?>" alt="PLATEPOINTS <?php echo $booking['user']->platePointsData['current_level']['title'];?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $booking['user']->platePointsData['current_level']['image'];?>-16x16.png" style="float: right; margin-left: 4px;" /></a><?php } ?>
						<?php if (!empty($booking['preferred_type'])) { ?><a href="/backoffice/preferred?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img alt="Preferred Guest" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/star_grey.png" style="float: right; margin-left: 4px;" data-tooltip="Preferred Guest" /></a><?php } ?>
						<?php if (empty($booking['user']->preferences['TC_DELAYED_PAYMENT_AGREE']['value'])) { ?><img alt="Delayed Payment terms" data-delayed_payment_tc="<?php echo $booking['user_id']; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/money_dollar.png" style="cursor: pointer; float: right; margin-left: 4px;" data-tooltip="Has not agreed to Delayed Payment terms" /><?php } ?>
						<?php if (!empty($booking['is_birthday_month'])) { ?><img alt="Happy Birthday" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cake.png" style="float: right; margin-left: 4px;" data-tooltip="Birthday this Month" /><?php } ?>
						<div class="clear"></div>
					</div>

					<div id="gd_guest_menu-<?php echo $booking['id']; ?>" class="guest_menu_div">
						<ul class="guest_menu">

							<li><a href="/backoffice/user_details?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">View Guest</a></li>

						<?php if ($booking['status'] != CBooking::RESCHEDULED) { ?>
							<li><a data-view_order_details="<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"
									 href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&amp;order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">View Order</a></li>
						<?php } ?>


							<li><a href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Order History</a></li>
							<?php if ($this->store_supports_plate_points && $booking['dream_rewards_version'] != 3) { ?>
								<li><a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;print_enrollment_form=true&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" target="_blank">PP Enroll Form</a></li>
							<?php } ?>

							<?php if ($booking['status'] != CBooking::SAVED) { ?>
								<li><a href="/print?order=<?php echo $booking['order_id']; ?>&amp;freezer=true" target="_blank">Freezer Sheet</a></li>
								<li><a href="/print?order=<?php echo $booking['order_id']; ?>&amp;core=true&amp;cur=true" target="_blank">This Month's Menu</a></li>
								<li><a href="/print?order=<?php echo $booking['order_id']; ?>&amp;core=true" target="_blank">Next Month's Menu</a></li>
								<li><a href="/print?order=<?php echo $booking['order_id']; ?>&amp;nutrition=true" target="_blank">Nutritionals</a></li>
								<li><a href="/backoffice/order-details-view-all?customer_print_view=1&amp;session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>" target="_blank">Order Summary</a></li>
								<li>
									<a href="/backoffice/reports_customer_menu_item_labels?session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=true" target="_blank">Labels</a> &bull;
									<a href="/backoffice/reports_customer_menu_item_labels?session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=false" target="_blank">w/ FL</a>
								</li>
								<li><a href="/backoffice/session-tools-printing?do=print&amp;user_id=<?php echo $booking['user_id']; ?>&amp;session=<?php echo $booking['session_id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;core=true&amp;freezer=true&amp;nutrition=true" target="_blank">Collated Docs</a></li>
							<?php } ?>

						</ul>
						<div class="clear"></div>
					</div>

					<?php
					if ($booking['status'] == CBooking::ACTIVE && (!empty($booking['user']->platePointsData['due_reward_for_current_level']) &&
							$booking['user']->platePointsData['due_reward_for_current_level']) && !$booking['preferred_somewhere'] && $this->store_supports_plate_points) { ?>

						<div data-pp_gift_reward_div="<?php echo $booking['user_id']; ?>" style="bottom: 8px; width: 96%; text-align: center; margin-top: 20px;">
							<div>PLATEPOINTS Reward Due:</div>
							<span><?php echo $booking['user']->platePointsData['gift_display_str']; ?></span>
						</div>

					<?php } ?>

        			<?php if (empty($booking['future_session'])) { ?>
						<div class="clear"></div>
        				<div class="no_future_session_warning">Guest does not have a future session.</div>
        			<?php }  ?>

				</td>
				<td class="title">Order Status</td>
				<td class="value small_value <?php echo $booking['status_css']; ?>"><?php echo ucfirst(strtolower($booking['status'])); ?></td>
				<td class="title">Order Type</td>
				<td class="value small_value <?php echo $booking['booking_type_css']; ?>"><?php echo ucfirst(strtolower($booking['booking_type'])); ?></td>
				<td class="title">Balance Due</td>
				<td class="value small_value">
				<?php if (!empty($booking['can_edit'])) { ?>
				    <a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">
				<?php } else { ?>
					 <a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">
				<?php } ?>
				<span class="<?php echo $booking['balance_due_css']; ?>" <?php if ($booking['balance_due'] != '0.00') { ?>
				        data-tooltip="Balance due to <?php echo ($booking['balance_due'] > 0) ? 'store' : 'guest'; ?>"<?php } ?>><?php echo $booking['balance_due_text']; ?>
				        </span></a></td>
			</tr>
			<?php if ($booking['status'] == CBooking::ACTIVE) { ?>
			<tr>
				<?php if (($booking['dream_reward_status'] != 1 && $booking['dream_reward_status'] != 3) || $booking['dream_rewards_version'] == 3) { ?>
					<td class="title">PLATEPOINTS</td>
				<?php } else { ?>
					<td class="title">Dream Rewards</td>
				<?php } ?>
				<td class="value small_value">
					<?php if ($booking['dream_reward_status'] == 1 || $booking['dream_reward_status'] == 3) {
						if ($booking['dream_rewards_version'] < 3) { ?>
							Yes
						<?php } else { ?>
							<div><span data-pp_user_level_title="<?php echo $booking['user_id'];?>"><?php echo $booking['user']->platePointsData['current_level']['title'];?></span></div>
							<div>Points: <span data-pp_user_lifetime_points="<?php echo $booking['user_id']; ?>"><a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><?php echo number_format($booking['user']->platePointsData['lifetime_points']); ?></a></span></div>
						<?php } } else { ?>
						No
					<?php } ?>
				</td>
				<td class="title">PLATEPOINTS Gain</td>
				<td class="value small_value">
					<?php if (($booking['dream_reward_status'] == 1 || $booking['dream_reward_status'] == 3) && $booking['dream_rewards_version'] == 3) {
						if ($booking['can_confirm_order']) { ?>
							<span id="pp_co_<?php echo $booking['user_id']; ?>_<?php echo $booking['order_id']; ?>"><?php echo $booking['points_this_order'];?> Unconfirmed Points</span>
						<?php } else { ?>
							<span id="pp_co_<?php echo $booking['user_id']; ?>_<?php echo $booking['order_id']; ?>"><?php echo $booking['points_this_order'];?> Points</span>
						<?php } } else { ?>
							<span>Not yet enrolled</span>
					<?php } ?>
				</td>
				<td class="title">No Show</td>
				<td class="value small_value"><span> <?php echo ($booking['no_show']) ? "Flagged as No Show" : ""; ?> </span></td>
			</tr>
			<tr>
				<td class="title">First Session</td>
				<td class="value small_value <?php echo (!empty($booking['this_is_first_session'])) ? 'first_session' : ''; ?>"><?php echo (!empty($booking['this_is_first_session'])) ? 'This Session!' : CTemplate::dateTimeFormat($booking['first_session'], MONTH_DAY_YEAR); ?></td>
				<td class="title">Previous Session</td>
				<td class="value small_value"><?php if (!empty($booking['last_session_attended'])) { ?><?php echo CTemplate::dateTimeFormat($booking['last_session_attended'], MONTH_DAY_YEAR); ?><?php } else { ?>None<?php } ?></td>
				<td class="title">Orders Completed</td>
				<td class="value small_value"><a href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><?php echo (!empty($booking['bookings_made'])) ? $booking['bookings_made'] : 0; ?></a></td>
			</tr>
			<?php if (!empty($booking['order_user_notes'])) { ?>
				<tr>
					<td class="title" style="vertical-align: top;" data-tooltip="Guest's instructions for this order">Order Instructions</td>
					<td colspan="5" class="value special_inst"><?php echo $booking['order_user_notes']; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="title" style="vertical-align: top;">
					<span>Account Notes</span>
				</td>
				<td colspan="5" class="value">
					<div><?php if (!empty($booking['user']->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) { echo nl2br(htmlentities($booking['user']->preferences[CUser::USER_ACCOUNT_NOTE]['value'])); } ?></div></td>
			</tr>
			<tr>
				<td class="title" style="vertical-align: top;">
					<span>Admin Carryover</span>
				</td>
				<td colspan="5" class="value">
				<div><?php echo (!empty($booking['user_data']['16'])) ? nl2br(htmlspecialchars($booking['user_data']['16'])) : ''; ?></div></td>
			</tr>
		<?php } ?>
			<tr>
				<td class="title" style="vertical-align: top;">
					<span>Admin Order Note</span>
				</td>
				<td colspan="5" class="value">
					<div><?php echo $booking['order_admin_notes']; ?></div></td>
			</tr>
			</tbody>

			<tbody id="order_details_tbody_id_<?php echo $booking['id']; ?>" class="guest order_details_tbody" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>">
			<tr>
				<td colspan="7" class="title" style="text-align: left;">
					Order Summary
					<div style="float: right;">
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
	<?php } else { ?>
		<tbody class="no_guest">
		<tr>
			<td class="value" style="text-align: center; font-weight: bold;">No guests scheduled for this session.</td>
		</tr>
		</tbody>
		<tbody>
		<tr>
			<td colspan="7" style="text-align: center; height: 4px;"></td>
		</tr>
		</tbody>
	<?php } ?>
</table>

<?php if (empty($this->date_info)) { ?>
	<?php if (!empty($this->session_info['bookings'])) { ?>
		<?php include $this->loadTemplate('admin/subtemplate/main_booked_guests_legend.tpl.php'); ?>
	<?php } ?>
<?php } ?>