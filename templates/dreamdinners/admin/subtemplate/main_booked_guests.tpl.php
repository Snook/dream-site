<?php
//2022- until 'Made For You' can be changed to 'Pick Up' in CSession->getSessionTypeProperties()
if( !function_exists('temp_translateMYF_to_Pickup'))
{
	function temp_translateMYF_to_Pickup($title)
	{
		return $title == 'Made For You' ? 'Pick Up' : $title;
	}
}

$dateFormat = CTemplate::dateTimeFormat($this->session_info['session_start'], TIME_ONLY);
if($this->session_info['session_type_subtype'] == CSession::WALK_IN){
	$dateFormat = CTemplate::dateTimeFormat($this->session_info['session_start'], MONTH_DAY_YEAR);
}
?>

<h2 id="gd_title-<?php echo $this->session_info['id'];?>" data-session_id="<?php echo $this->session_info['id'];?>"><?php echo $dateFormat ?> - <?php echo temp_translateMYF_to_Pickup($this->session_info['session_type_title']); ?></h2>

<?php

if( !function_exists('translateOrderQuantityType')){
	function translateOrderQuantityType($tpl,$data)
	{
		try{
			if ($data['booking_type'] == CBooking::STANDARD)
			{
				$minimum = $tpl->order_minimum;
				if($minimum->getAllowsAdditionalOrdering()){
					if ($data['is_qualifying'])
					{
						return ' - <span data-tooltip="Qualifying Order">QO</span>';
					}
					else if ($data['is_additional'])
					{
						return ' - <span data-tooltip="Additional Order">AD</span>';
					}else if ($data['order_type'] == 'DIRECT')
					{
						$count = null;
						if($minimum->getMinimumType() == COrderMinimum::SERVING){
							$count = $data['servings_core_total_count'];
						}

						if($minimum->getMinimumType() == COrderMinimum::ITEM){
							$count = $data['menu_items_core_total_count'];
						}

						if(is_numeric($count) && $count < $minimum->getMinimum()){
							return ' - <span data-tooltip="Direct Order for Less Than Minimum">UM</span>';

						}else{

						}
					}
				}
			}

		} catch (Exception $e) {}


		return '';
	}
}?>

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
				if ($booking['status'] == CBooking::CANCELLED){
					$rowspan = 3;
				}
				else
				{
					$rowspan = 2;
				}
			}

			?>
			<tbody id="guest_details_tbody_id_<?php echo $booking['id']; ?>" class="guest guest_details_tbody <?php echo ($booking['status'] != CBooking::ACTIVE) ? 'is_past' : ''; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>">
			<tr>
				<td rowspan="<?php echo $rowspan;?>" class="value guest">

					<a class="guestname" id="gd_guest-<?php echo $booking['id']; ?>" href="/backoffice/user_details?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>"><?php echo $booking['firstname']; ?> <?php echo $booking['lastname']; ?></a>

					<div style="float: right;">
						<?php if (!empty($booking['corporate_crate_client']) && !empty($booking['corporate_crate_client']->is_active)) { ?><img alt="<?php echo $booking['corporate_crate_client']->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $booking['corporate_crate_client']->icon_path; ?>_icon.png" class="float-right ml-1" data-tooltip="<?php echo $booking['corporate_crate_client']->company_name; ?>" /><?php } ?>
						<?php if ($booking['user_type'] != CUser::CUSTOMER) { ?><img alt="Dream Dinners Staff" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/dreamdinners.png" class="float-right ml-1" data-tooltip="Dream Dinners Staff" /><?php } ?>
						<?php if ($booking['user']->membershipData['enrolled']) { ?><a href="/backoffice/user_membership?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img alt="Meal Prep+" data-tooltip="Meal Prep+" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/membership/badge-membership-16x16.png" class="float-right ml-1" /></a><?php } ?>
						<?php if ($booking['user']->platePointsData['status'] == 'active' || $booking['user']->platePointsData['userIsOnHold']) { ?>
							<?php if ($booking['user']->platePointsData['userIsOnHold']) { ?>
								<a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img data-user_id_pp_tooltip="<?php echo $booking['user']->id; ?>" alt="PlatePoints On Hold" data-tooltip="PlatePoints On Hold" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-hold-16x16.png" class="float-right ml-1" /></a>
							<?php } else { ?>
								<a href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img data-user_id_pp_tooltip="<?php echo $booking['user']->id; ?>" alt="PlatePoints <?php echo $booking['user']->platePointsData['current_level']['title'];?>" data-tooltip="PlatePoints <?php echo $booking['user']->platePointsData['current_level']['title'];?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $booking['user']->platePointsData['current_level']['image'];?>-16x16.png" class="float-right ml-1" /></a>
							<?php } ?>
						<?php } ?>
						<?php if (!empty($booking['preferred_type'])) { ?><a href="/backoffice/preferred?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><img alt="Preferred Guest" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/star_grey.png" class="float-right ml-1" data-tooltip="Preferred Guest" /></a><?php } ?>
						<?php if (empty($booking['user']->preferences['TC_DELAYED_PAYMENT_AGREE']['value'])) { ?><img alt="Delayed Payment terms" data-delayed_payment_tc="<?php echo $booking['user_id']; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/money_dollar.png" style="cursor: pointer; float: right; margin-left: 4px;" data-tooltip="Has not agreed to Delayed Payment terms" /><?php } ?>
						<?php if (!empty($booking['is_birthday_month'])) { ?><img alt="Happy Birthday" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cake.png" class="float-right ml-1" data-tooltip="Birthday this Month" /><?php } ?>
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
							<?php if (!empty($booking['can_edit'])) { ?>
								<li><a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Edit Order</a></li>
							<?php } else { ?>
								<li><a href="/backoffice/order-mgr?order=<?php echo $booking['order_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Edit Payments</a></li>

							<?php } ?>
							<?php if (!empty($booking['can_reschedule'])) { ?>
								<li><a id="gd_reschedule-<?php echo $booking['id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>">Reschedule</a></li>
							<?php } else { ?>
								<li data-tooltip="Can not reschedule" style="text-decoration: line-through;">Reschedule</li>
							<?php } ?>
							<li><a href="/backoffice/order-mgr?user=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Place <?php echo $this->date['this_M']; ?> Order</a></li>
							<li><a href="/backoffice/order-mgr?user=<?php echo $booking['user_id']; ?>&amp;month=<?php echo $this->date['next_M_time']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Place <?php echo $this->date['next_M']; ?> Order</a></li>
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
								<!--li>
									<p style="font-size: 10px;display:inline;">6-up&nbsp;<a href="/backoffice/reports_customer_menu_item_labels?session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=true" target="_blank">Labels</a> &bull;
									<a href="/backoffice/reports_customer_menu_item_labels?session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=false" target="_blank">w/ FL</a>
									</p>
								</li-->
								<li>
									<p style="font-size: inherit;display:inline;"><a href="/backoffice/reports_customer_menu_item_labels?labels_per_sheet=4&amp;session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=true" target="_blank">Labels</a> &bull;
										<a href="/backoffice/reports_customer_menu_item_labels?labels_per_sheet=4&amp;session_id=<?php echo $booking['session_id']; ?>&amp;booking_id=<?php echo $booking['id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;menuid=<?php echo $booking['menu_id']; ?>&amp;suppressFastlane=false" target="_blank">w/ FL</a>
									</p>
								</li>
								<li><a href="/backoffice/session-tools-printing?do=print&amp;user_id=<?php echo $booking['user_id']; ?>&amp;session=<?php echo $booking['session_id']; ?>&amp;store_id=<?php echo $booking['store_id']; ?>&amp;core=true&amp;freezer=true&amp;nutrition=true" target="_blank">Collated Docs</a></li>

							<?php } ?>

						</ul>
						<div class="clear"></div>
					</div>

					<?php if (empty($booking['future_session'])) { ?>
						<div class="clear"></div>
						<div class="no_future_session_warning">Guest does not have a future session.</div>
					<?php }  ?>

				</td>
				<td class="title">Order Status</td>
				<td class="value small_value <?php echo $booking['status_css']; ?>"><?php echo ucfirst(strtolower($booking['status'])); ?></td>
				<td class="title">Order Type</td>
				<td class="value small_value <?php echo $booking['booking_type_css']; ?>"><?php echo CBooking::getBookingTypeDisplayString($booking['booking_type']) . translateOrderQuantityType($this,$booking);  ?>
					<?php if (!empty($booking['opted_to_customize_recipes']) && $booking['opted_to_customize_recipes'] ) { ?>
					- <i class="dd-icon icon-customize text-orange" style="font-size: 85%;" data-tooltip="This order has customizations"></i>
					<?php } ?>
				</td>
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
			</tr>
			<?php if ($booking['status'] == CBooking::ACTIVE) { ?>
				<tr>
					<?php if ($booking['user']->membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT) { ?>
						<td class="title">MP+ Progress</td>
						<td class="value small_value"><a href="/backoffice/user_membership?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><?php echo $booking['user']->membershipData['display_strings']['progress']; ?></a></td>
						<td class="title">MP+ Skip Avail</td>
						<td class="value small_value"><a href="/backoffice/user_membership?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><?php echo $booking['user']->membershipData['remaining_skips_available']; ?></a></td>
					<?php } else  {?>
						<?php if (($booking['dream_reward_status'] != 1 && $booking['dream_reward_status'] != 3) || $booking['dream_rewards_version'] == 3) { ?>
							<td class="title">Dinner Dollars</td>
						<?php } else { ?>
							<td class="title">Dream Rewards</td>
						<?php } ?>
						<td class="value small_value">
							<?php if ($booking['dream_reward_status'] == 1 || $booking['dream_reward_status'] == 3) {
								if ($booking['dream_rewards_version'] < 3) { ?>
									Yes
								<?php } else { ?>
									<div><span data-pp_user_lifetime_points="<?php echo $booking['user_id']; ?>"><a id="pp_credit_<?php echo $booking['user_id']; ?>_<?php echo $booking['order_id']; ?>" href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">$<?php echo CTemplate::moneyFormat($booking['user']->platePointsData['available_credit']); ?></a></span></div>
								<?php } } else { ?>
								<?php if ($booking['dream_reward_status'] == 5) { ?>
									On Hold
								<?php } else { ?>
									No
								<?php } ?>
							<?php } ?>
						</td>
						<td class="title">PlatePoints Pending</td>
						<td class="value small_value">
							<?php if($booking['preferred_somewhere']){?>
								Not Eligible
							<?php }else{
								if (($booking['dream_reward_status'] == 1 || $booking['dream_reward_status'] == 3) && $booking['dream_rewards_version'] == 3) {
									if ($booking['can_confirm_order']) { ?>
										<span id="pp_co_<?php echo $booking['user_id']; ?>_<?php echo $booking['order_id']; ?>"><a class="btn btn-primary btn-sm" href="javascript:confirm_order_attended('<?php echo $booking['user_id'];?>', '<?php echo $booking['order_id'];?>');"> <?php echo $booking['points_this_order'];?> Points<br />Confirm Order</a></span>
									<?php } else { ?>
										<span id="pp_co_<?php echo $booking['user_id']; ?>_<?php echo $booking['order_id']; ?>"><?php echo $booking['points_this_order'];?> Points</span>
									<?php }
								} else { ?>
									<?php if ($booking['dream_reward_status'] == 5) { ?>
										<a class="btn btn-primary btn-sm" href="/backoffice/user_plate_points?id=<?php echo $booking['user_id']; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">PlatePoints</a>
									<?php } else { ?>
										<a class="btn btn-primary btn-sm" href="/backoffice/account?id=<?php echo $booking['user_id']; ?>&amp;pp_enroll=1&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>">Enroll in PlatePoints</a>
									<?php } ?>
								<?php }
							}?>
						</td>
					<?php } ?>
					<td class="title">No Show</td>
					<td class="value small_value"><input id="gd_noshow-<?php echo $booking['id']; ?>" data-booking_id="<?php echo $booking['id']; ?>" type="checkbox" <?php echo ($booking['no_show']) ? 'checked="checked"' : ''; ?> /></td>
				</tr>
				<tr>
					<td class="title">First Session</td>
					<td class="value small_value <?php echo (!empty($booking['this_is_first_session'])) ? 'first_session' : ''; ?>"><?php echo (!empty($booking['this_is_first_session'])) ? 'This Session!' : CTemplate::dateTimeFormat($booking['first_session'], MONTH_DAY_YEAR); ?></td>
					<td class="title">Previous Session</td>
					<td class="value small_value"><?php if (!empty($booking['last_session_attended'])) { ?><?php echo CTemplate::dateTimeFormat($booking['last_session_attended'], MONTH_DAY_YEAR); ?><?php } else { ?>None<?php } ?></td>
					<td class="title">Orders Completed</td>
					<td class="value small_value"><a href="/backoffice/order-history?id=<?php echo $booking['user_id']; ?>&back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $booking['session_id']; ?>"><?php echo (!empty($booking['bookings_made'])) ? $booking['bookings_made'] : 0; ?></a></td>
				</tr>
				<?php
				$currentMealPrepPlusMember = false;
				if (isset($booking['user']->membershipData['status']) && $booking['user']->membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
				{
					$currentMealPrepPlusMember = true;
				}
				if ($booking['user']->platePointsData['status'] == 'active' || $currentMealPrepPlusMember || !empty($booking['food_testing_survey_id'])) { ?>
					<?php if (!empty($this->food_testing_recipes) && (!empty($booking['user']->platePointsData['current_level']['rewards']['food_testing']) || $currentMealPrepPlusMember ||  !empty($booking['food_testing_survey_id']))) { ?>
						<tr>
							<td class="title">Food Testing</td>
							<td colspan="5" class="value" data-food_testing_select_user_id="<?php echo $booking['user_id']; ?>">
								<?php if (!empty($booking['food_testing_survey_id'])) { ?>
									<?php echo $booking['food_testing_title']; ?> - <?php echo ($booking['food_testing_size'] == 'HALF') ? 'Medium' : 'Large';?>
								<?php } else if (!empty($booking['user']->platePointsData['current_level']['rewards']['food_testing']) || $currentMealPrepPlusMember) { ?>
									<select id="food_testing-<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>">
										<option value="0">Choose Test Recipe</option>
										<?php foreach ($this->food_testing_recipes AS $id => $recipe) { ?>
											<option value="<?php echo $recipe['select_option_value']; ?>"><?php echo $recipe['select_option_title']; ?></option>
										<?php } ?>
									</select>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
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
			<?php } else if ($booking['status'] == CBooking::CANCELLED && !empty($booking['reason_for_cancellation'])) { ?>

				<tr>
					<td class="title" style="vertical-align: top;">
						REASON
					</td>
					<td colspan="5" class="value">
						<?php echo CBooking::getBookingCancellationReasonDisplayString($booking['reason_for_cancellation']); ?>&nbsp;&nbsp;Declined MFY Option: <?php echo ($booking['declined_MFY_option'] ? "YES" : "NO"); ?>&nbsp;&nbsp;Declined MFY Option: <?php echo ($booking['declined_to_reschedule'] ? "YES" : "NO"); ?>

					</td>
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
								<span id="gd_cancel_order-<?php echo $booking['id']; ?>" data-user_id="<?php echo $booking['user_id']; ?>" data-store_id="<?php echo $booking['store_id']; ?>" data-session_id="<?php echo $booking['session_id']; ?>" data-order_id="<?php echo $booking['order_id']; ?>" data-menu_id="<?php echo $booking['menu_id']; ?>" class="btn btn-primary btn-sm">Cancel Order</span>
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

	<?php if (!empty($this->session_info['dream_taste_can_rsvp_only'])) { ?>
		<tbody class="no_guest">
		<tr>
			<td colspan="4" class="title">RSVP Only Guests</td>
			<td colspan="3" class="title">
				<span>Add RSVP:</span>
				<span id="add_rsvp_button-<?php echo $this->session_info['id']; ?>" class="btn btn-primary btn-sm add_session_rsvp_guest_create" data-session_id="<?php echo $this->session_info['id']; ?>">Create Guest</span>
				<span data-guestsearch="add_session_rsvp" data-select_button_title="Add RSVP" data-all_stores_checked="false" data-session_id="<?php echo $this->session_info['id']; ?>" data-select_function="handle_session_rsvp_guest_find" class="btn btn-primary btn-sm">Lookup Guest</span>
			</td>
		</tr>
		<?php if (!empty($this->session_info['session_rsvp'])) { ?>
			<?php foreach ($this->session_info['session_rsvp'] AS $session_rsvp) { ?>
				<tr id="session_rsvp-<?php echo $this->session_info['id']; ?>-<?php echo $session_rsvp->user->id; ?>">
					<td colspan="4" class="value">
						<?php if (!empty($session_rsvp->user->firstname)) { ?>
							<?php echo $session_rsvp->user->firstname; ?> <?php echo $session_rsvp->user->lastname; ?>
						<?php } else { ?>
							<?php echo $session_rsvp->user->primary_email; ?>
						<?php } ?>
					</td>
					<td colspan="3" class="value" style="text-align: center;">
						<?php if (!empty($this->session_info['dream_taste_can_rsvp_upgrade'])) { ?>
							<input type="button" class="btn btn-primary btn-sm" value="Upgrade Order" onclick="hostessDreamTasteOrder(<?php echo $this->session_info['id']; ?>, '<?php echo CTemplate::dateTimeFormat($this->session_info['session_start']); ?>', <?php echo $session_rsvp->user->id; ?>);" />
						<?php } ?>
						<a class="btn btn-primary btn-sm" href="/backoffice/user_details?id=<?php echo $session_rsvp->user->id; ?>&amp;back=/%3Fpage%3Dadmin_main%26session%3D<?php echo $this->session_info['id']; ?>">View Guest</a></li>
						<span class="btn btn-primary btn-sm cancel_session_rsvp" data-user_id="<?php echo $session_rsvp->user->id; ?>" data-session_id="<?php echo $this->session_info['id']; ?>">Cancel RSVP</span>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tbody class="no_guest">
			<tr>
				<td class="value" colspan="7" style="text-align: center; font-weight: bold;">No RSVP for this session.</td>
			</tr>
			</tbody>
		<?php } ?>
		</tbody>
		<tbody>
		<tr>
			<td colspan="7" style="text-align: center; height: 4px;"></td>
		</tr>
		</tbody>
	<?php } ?>

	<?php if (empty($this->session_info['bookings']) && empty($this->session_info['session_rsvp'])) { ?>
		<tbody class="no_guest">
		<tr>
			<td colspan="7" class="value" style="text-align: center; font-weight: bold;">No guests scheduled for this session.</td>
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