<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/clipboard/clipboard.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/user_details.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/user_details.css'); ?>
<?php $this->setScriptVar('user_id = "' . $this->user['id'] . '";'); ?>
<?php $this->assign('page_title','Guest Details'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php $isDC = (CBrowserSession::getCurrentFadminStoreObj()->store_type === CStore::DISTRIBUTION_CENTER);?>

	<table style="width:100%;">
		<tr>
			<td colspan="2" style="text-align:center;font-weight:bold;font-size:large;padding-bottom:10px;"><?php echo  $this->user['firstname'] . ' ' . $this->user['lastname']; ?>

				<?php if (!empty($this->user['corporate_crate_client']) && !empty($this->user['corporate_crate_client']->is_active)) { ?>
					<img alt="<?php echo $this->user['corporate_crate_client']->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $this->user['corporate_crate_client']->icon_path; ?>_icon.png" style="margin-left: 4px;" data-tooltip="<?php echo $this->user['corporate_crate_client']->company_name; ?>" />
				<?php } ?>
				<?php if ($this->user['is_dd_employee'] ) { ?>
					<img alt="Dream Dinners Staff" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/dreamdinners.png" style="margin-left: 4px;" data-tooltip="Dream Dinners Staff:
									<?php echo CUser::userTypeText($this->user['user_type'] )?>" />
				<?php } ?>
				<?php if (($this->user['dream_reward_status'] == 1 || $this->user['dream_reward_status'] == 3) && $this->user['dream_rewards_version'] == 3) { ?>
					<img alt="PLATEPOINTS <?php echo $this->user['platePointsData']['current_level']['title'];?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $this->user['platePointsData']['current_level']['image'];?>-16x16.png" style="margin-left: 4px;"
						 data-tooltip="<?php echo $this->user['platePointsData']['current_level']['title'];?>" />
				<?php } ?>
				<?php if (!empty($this->user['is_preferred_somewhere'])) { ?>
					<img alt="Preferred Guest" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/star_grey.png" style="margin-left: 4px;" data-tooltip="Preferred Guest" />
				<?php } ?>
			</td>
		</tr>
	</table>

	<div class="row">
		<div class="col-2 pr-0">
			<?php if ($this->isPartialAccount) { ?>
				<input  type="button" class="btn btn-primary btn-block" value="Upgrade Account" onclick="bounce('/backoffice/account?upgrade=true&id=<?php echo  $this->user['id']?>');" />
			<?php } else if ($this->canPlaceOrder == true && !$isDC) { ?>
				<div class="row my-1">
					<div class="col-8 pr-1">
						<input type="button" class="btn btn-primary btn-block" value="Place Order" onclick="bounce('/backoffice/order-mgr?user=<?php echo  $this->user['id']?>');" />
					</div>
					<div class="col-4 pl-0">
						<input type="button" class="btn btn-primary btn-block" value="<?php echo $this->date['next_M']; ?>" onclick="bounce('/backoffice/order-mgr?user=<?php echo  $this->user['id']?>&month=<?php echo $this->date['next_M_time']; ?>');" />
					</div>
				</div>
			<?php } ?>

			<?php if ($this->canPlaceOrder == true && $isDC) { ?>
				<input type="button" style="height: 44px;" class="btn btn-delivered btn-block " value="Place Shipping Order" onclick="bounce('/backoffice/order-mgr-delivered?user=<?php echo  $this->user['id']?>');" />
			<?php } ?>

			<?php if ( $this->user['numorders'] ) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Order History" onclick="bounce('/backoffice/order-history?id=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canSetPrefStatus == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Preferred Status" onclick="bounce('/backoffice/preferred?id=<?php echo  $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canChangeAccess == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Access Levels" onclick="bounce('/backoffice/access-levels?id=<?php echo  $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canEditInfo == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Edit Guest Info" onclick="bounce('/backoffice/account?id=<?php echo  $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canEmailCustomer == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Email Guest" onclick="bounce('/backoffice/email?id=<?php echo  $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canModifyCreditCards == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Credit Cards" onclick="bounce('/backoffice/credit-cards?user=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canModifyReferrals) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Referred By" onclick="bounce('/backoffice/user_referral?user=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canModifyStoreCredit == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="Credit" onclick="bounce('/backoffice/credit?id=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canViewUserHistory == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="User History" onclick="bounce('/backoffice/user_history?id=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canViewEventLog == true) { ?>
				<input type="button" class="btn btn-primary btn-block" value="User Event Log" onclick="bounce('/backoffice/user_event_log?id=<?php echo $this->user['id']?>');" />
			<?php } ?>

			<?php if ($this->canViewPlatePointsHistory == true) { ?>
				<a class="btn btn-primary btn-block" href="/backoffice/user-plate-points?id=<?php echo $this->user['id']?>">PLATEPOINTS</a>
			<?php } else if (false){//$this->canJoinToPlatePoints == true ) { ?>
				<?php if (!$this->user['platePointsData']['userIsOnHold'] && !$this->user['membershipData']['enrolled']) { ?>
					<input type="button" class="btn btn-primary btn-block" value="Join PLATEPOINTS" onclick="enrollInPlatePoints(<?php echo $this->user['id']?>);" />
					<input type="button" class="btn btn-primary btn-block" value="Print Enrollment Form" onclick="printEnrollmentForm(<?php echo $this->user['id']?>);" />
				<?php } else { ?>
					<a class="btn btn-cyan btn-block" href="/backoffice/user-plate-points?id=<?php echo $this->user['id']?>">PLATEPOINTS</a>
				<?php } ?>
			<?php } ?>

			<?php if ( false ) { //dont show any longer - 7/28/2023 ?>
				<a class="btn btn-primary btn-block" href="/backoffice/user_membership?id=<?php echo $this->user['id']?>">Meal Prep+</a>
			<?php } ?>
			<?php if ($this->canPlaceOrder == true && !$isDC) { ?>
			<a class="btn btn-delivered btn-block" href="/backoffice/order-mgr-delivered?user=<?php echo $this->user['id']?>">Place Shipping Order</a><?php } ?>

			<?php if ((CBrowserSession::getCurrentFadminStore() == $this->user['home_store_id'] && $this->canUnsetHomeStore) || (CUser::getCurrentUser()->user_type == 'SITE_ADMIN' && $this->canUnsetHomeStore)) {	?>
				<input type="button" class="btn btn-danger btn-block mt-5" value="Remove Homestore" onclick="unsetHomeStore('<?php echo $this->user['id']?>');" data-tooltip="Disassociate guest's home store." />
			<?php } ?>
			<?php if (DD_SERVER_NAME != 'LIVE' || (CUser::getCurrentUser()->user_type == 'SITE_ADMIN' && $this->user['user_type'] != CUser::SITE_ADMIN)) { ?>
				<input type="button" class="btn btn-danger btn-block" value="Login as Guest" onclick="bounce('/backoffice/user-details?id=<?php echo $this->user['id']?>&amp;login_as_user=true');" data-tooltip="Login as guest." />
			<?php } ?>
			<?php if (CUser::getCurrentUser()->user_type == 'SITE_ADMIN' && ($this->user['numorders'] === 0 || $this->candelete == true)) {	?>
				<input type="button" class="btn btn-danger btn-block" value="Close Account" onclick="deleteUserConfirm('<?php echo $this->user['id']?>');" data-tooltip="Permanently delete the guest sitewide." />
			<?php } ?>
			<?php if (CUser::getCurrentUser()->user_type == 'SITE_ADMIN' && $this->user['hasPendingDataRequest'] ) {	?>
				<input type="button" class="btn btn-danger btn-block" value="Mark CCPA Complete" onclick="markAccountDataRequestCompleteConfirm('<?php echo $this->user['id']?>');" data-tooltip="Mark guest data request (CCPA) as complete." />
			<?php } ?>
		</div>
		<div class="col-10">
			<table style="width:100%;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Admin Carryover Notes</td>
				</tr>
				<tr>
					<td class="guest_details_list_item" colspan="2"><?php include $this->loadTemplate('admin/guest_carryover_notes.tpl.php'); ?></td>
				</tr>
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Guest Details</td>
				</tr>
				<tr>
					<td class="guest_details_list_name">First Name</td>
					<td class="guest_details_list_item"><?php echo  $this->user['firstname']; ?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Last Name</td>
					<td class="guest_details_list_item"><?php echo  $this->user['lastname']; ?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Gender</td>
					<td class="guest_details_list_item"><?php echo $this->user['gender']; ?></td>
				</tr>
				<?php if (empty($this->user['primary_email'])) { ?>
					<tr>
						<td class="guest_details_list_name">User Name (No Email)</td>
						<td class="guest_details_list_item"><?php echo $this->user['login_username']?></td>
					</tr>
				<?php } else { ?>
					<tr>
						<td class="guest_details_list_name">Primary Email</td>
						<td class="guest_details_list_item"><a href="/backoffice/email?id=<?php echo  $this->user['id']?>"><?php echo $this->user['primary_email']; ?></a></td>
					</tr>

					<?php if ($this->can['display_corporate_crate_email']) { ?>
						<tr>
							<td class="guest_details_list_name">Corporate Crate Email</td>
							<td class="guest_details_list_item">
								<?php if (!empty($this->user['secondary_email'])) { ?>
									<a href="/backoffice/email?id=<?php echo  $this->user['id']?>"><?php echo $this->user['secondary_email']; ?></a>
								<?php } ?>
							</td>
						</tr>
					<?php } } ?>
				<tr>
					<td class="guest_details_list_name">Primary Telephone</td>
					<td class="guest_details_list_item"><?php echo $this->telephoneFormat($this->user['telephone_1']); ?> (<?php echo ucwords(strtolower(str_replace('_', ' ', ($this->user['telephone_1_type'])))); ?>)</td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Primary Telephone Time to Call</td>
					<td class="guest_details_list_item"><?php echo $this->user['telephone_1_call_time']; ?></td>
				</tr>
				<?php if (!empty($this->user['telephone_2'])) { ?>
					<tr>
						<td class="guest_details_list_name">Secondary Telephone</td>
						<td class="guest_details_list_item"><?php echo $this->telephoneFormat( $this->user['telephone_2']); ?> (<?php echo ucwords(strtolower(str_replace('_', ' ', ($this->user['telephone_2_type'])))); ?>)</td>
					</tr>
					<tr>
						<td class="guest_details_list_name">Secondary Telephone Time to Call</td>
						<td class="guest_details_list_item"><?php echo $this->user['telephone_2_call_time']; ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td class="guest_details_list_name">Billing Address</td>
					<td class="guest_details_list_item">
						<div><?php echo $this->user['address_line1']; ?></div>
						<?php if ($this->user['address_line2']) { ?>
							<div><?php echo $this->user['address_line2']; ?></div>
						<?php } ?>
						<div><?php echo $this->user['city']; ?>, <?php echo $this->user['state_id']; ?> <?php echo $this->user['postal_code']; ?></div>
					</td>
				</tr>
				<?php if (!empty($this->shipping_address->id)) { ?>
					<tr>
						<td class="guest_details_list_name">Home Delivery Address</td>
						<td class="guest_details_list_item">
							<div><?php echo $this->shipping_address->address_line1; ?></div>
							<?php if ($this->shipping_address->address_line2) { ?>
								<div><?php echo $this->shipping_address->address_line2; ?></div>
							<?php } ?>
							<div><?php echo $this->shipping_address->city; ?>, <?php echo $this->shipping_address->state_id; ?> <?php echo $this->shipping_address->postal_code; ?></div>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Account Details</td>
				</tr>
				<tr>
					<td class="guest_details_list_name">User ID</td>
					<td class="guest_details_list_item"><?php echo $this->user['id']; ?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Guest's Referral Link</td>
					<td class="guest_details_list_item">
						<div class="input-group">
							<input type="text" id="my_share_pp_link" class="form-control" aria-label="Your referral link" value="<?php echo HTTPS_BASE; ?>share/<?php echo $this->user['id']; ?>" readonly>
							<div class="input-group-append">
								<div class="input-group-text btn-clip" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard" data-clipboard-target="#my_share_pp_link" ><i class="fas fa-clipboard-list"></i></div>
							</div>
							<div class="input-group-append">
								<a class="input-group-text" data-toggle="tooltip" data-placement="top" title="Download QR code" href="<?php echo HTTPS_BASE; ?>processor?processor=qr_code&amp;op=referral&amp;d=1&amp;s=10&amp;id=<?php echo $this->user['id']; ?>" ><i class="fas fa-qrcode"></i></a>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Account Status</td>
					<td class="guest_details_list_item"><?php echo ((isset($this->user['is_partial_account']) && $this->user['is_partial_account']) ? "Partial Account" : "Active")?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Member Since</td>
					<td class="guest_details_list_item"><?php echo $this->dateTimeFormat($this->user['timestamp_created'], NORMAL, $this->currentStore, CONCISE); ?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name" style="width:50%;">Last Login</td>
					<td class="guest_details_list_item"><?php if ( $this->user['visit_count']> 0 ) { echo $this->dateTimeFormat( $this->user['last_login'], NORMAL, $this->currentStore, CONCISE ); } else { echo 'Never'; } ?></td>
				</tr>
				<tr>
					<td class="guest_details_list_name">Number of Logins</td>
					<td class="guest_details_list_item"><?php echo $this->user['visit_count']; ?></td>
				</tr>

				<tr>
					<td class="guest_details_list_name">Order History</td>
					<?php if ( $this->user['numorders'] ) { ?>
						<td class="guest_details_list_item"><a href="/backoffice/order-history?id=<?php echo $this->user['id']; ?>"><?php echo  $this->user['numorders']; ?> total order<?php echo  $this->user['numorders'] != 1 ? 's' : '' ?> (<?php echo  $this->user['numcancelledorders']; ?> cancelled)</a></td>
					<?php } else { ?>
						<td class="guest_details_list_item">No orders</td>
					<?php } ?>
				</tr>


				<?php if (!empty($this->user['rsvp_history'])) { ?>

					<tr>
						<td class="guest_details_list_name">RSVPs</td>
						<td class="guest_details_list_item">
							<?php foreach($this->user['rsvp_history'] as $session_id => $details) { ?>
								<a href="/backoffice?session=<?php echo $session_id;?>">On <?php echo CTemplate::dateTimeFormat($details['rsvp_time'], MONTH_DAY_YEAR); ?> for session on <?php echo CTemplate::dateTimeFormat($details['session_start']); ?></a><br />
							<?php  } ?>
						</td>
					</tr>

				<?php  } ?>



				<tr>
					<td class="guest_details_list_name">Home Store</td>
					<td class="guest_details_list_item"><a target="_blank" href="/location/<?php echo $this->user['home_store_id']; ?>"><?php echo $this->user['store_name']; ?></a></td>
				</tr>
				<!--<tr>
					<td class="guest_details_list_name">AAA Program Member</td>
					<td class="guest_details_list_item"><?php echo  $this->isAAAReferred ? 'Yes' : 'No'; ?></td>
				</tr>-->

				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Guest's "My Preferences" <span style="font-size: .75em;">(Visible and editable by guest)</span></td>
				</tr>
				<?php if ($this->can['modify_delayed_payment_tc']) { ?>
					<tr>
						<td class="guest_details_list_name" style="vertical-align: middle;">Delayed Payment Terms &amp; Conditions</td>
						<td class="guest_details_list_item" style="vertical-align: middle;">
								<span id="tc_delayed_payment_status">
									<?php if (!empty($this->user['preferences'][CUser::TC_DELAYED_PAYMENT_AGREE]['updated_by'])) { ?>
										<?php echo (empty($this->user['preferences'][CUser::TC_DELAYED_PAYMENT_AGREE]['value'])) ? 'Declined' : 'Accepted'; ?> - <?php echo CTemplate::dateTimeFormat($this->user['preferences'][CUser::TC_DELAYED_PAYMENT_AGREE]['timestamp_updated']); ?>
									<?php } else { ?>
										Unanswered
									<?php } ?>
								</span>
							<span data-delayed_payment_tc="<?php echo $this->user['id']; ?>" class="btn btn-primary btn-sm">Change</span>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="guest_details_list_name">Visit Print Outs</td>
					<td class="guest_details_list_item">
						<div><input id="session_print_next_menu" data-user_pref="session_print_next_menu" data-user_id="<?php echo $this->user['id']; ?>" type="checkbox" <?php if (!empty($this->user['preferences'][CUser::SESSION_PRINT_NEXT_MENU]['value'])) {?>checked="checked"<?php } ?> /> <label for="session_print_next_menu">Next Month's Menu</label></div>
						<div><input id="session_print_freezer_sheet" data-user_pref="session_print_freezer_sheet" data-user_id="<?php echo $this->user['id']; ?>" type="checkbox" <?php if (!empty($this->user['preferences'][CUser::SESSION_PRINT_FREEZER_SHEET]['value'])) {?>checked="checked"<?php } ?> /> <label for="session_print_freezer_sheet">Freezer Sheet</label></div>
						<div><input id="session_print_nutritionals" data-user_pref="session_print_nutritionals" data-user_id="<?php echo $this->user['id']; ?>" type="checkbox" <?php if (!empty($this->user['preferences'][CUser::SESSION_PRINT_NUTRITIONALS]['value'])) {?>checked="checked"<?php } ?> /> <label for="session_print_nutritionals">Nutritionals</label></div>
					</td>
				</tr>
				<tr>
					<td class="guest_details_list_name">User's Account Note</td>
					<td class="guest_details_list_item">
						<textarea id="user_account_note" data-user_pref="user_account_note" data-user_id="<?php echo $this->user['id']; ?>" style="width: 378px; height: 50px;"><?php if (!empty($this->user['preferences'][CUser::USER_ACCOUNT_NOTE]['value'])) { echo htmlentities($this->user['preferences'][CUser::USER_ACCOUNT_NOTE]['value']); } ?></textarea>
					</td>
				</tr>

				<tr>
					<td class="guest_details_list_name">SMS Preferences</td>
					<td class="guest_details_list_item">
						<select
								data-sms_pref="false"
								id="text_message_opt_in"
								data-user_pref="text_message_opt_in"
								data-user_id="<?php echo $this->user['id']; ?>">
							<option value="UNANSWERED" <?php if ($this->user['preferences'][CUser::TEXT_MESSAGE_OPT_IN]['value'] == 'UNANSWERED') {?>selected<?php } ?>>Unanswered</option>
							<option value="OPTED_IN" <?php if ($this->user['preferences'][CUser::TEXT_MESSAGE_OPT_IN]['value'] == 'OPTED_IN') {?>selected<?php } ?>>Opted In</option>
							<option value="OPTED_OUT" <?php if ($this->user['preferences'][CUser::TEXT_MESSAGE_OPT_IN]['value'] == 'OPTED_OUT') {?>selected<?php } ?>>Opted Out</option>
						</select>
						<label for="text_message_opt_in">Receive SMS Texts</label>

						<div class="collapse"  data-sms_dlog_comp="true" >
							<div class="col">
								<button id="confirm_number_update" name="confirm_number_update" class="btn btn-primary custom-control-inline btn-spinner" value="Confirm Mobile Number Update">Confirm&nbsp;&nbsp;&nbsp;&nbsp;</button>
								<button id="cancel_number_update" name="cancel_number_update" class="btn btn-secondary custom-control-inline toggle-update_mobile_number" value="Cancel Mobile Number Update">Cancel</button>
							</div>
						</div>

					</td>
				</tr>

				<?php if ($this->userObj->homeStoreAllowsMealCustomization()) { ?>
				<tr>
					<td class="guest_details_list_name">Meal Customization</td>
					<td class="guest_details_list_item">
						<?php foreach($this->userObj->getMealCustomizationPreferences() as $key => $pref) { ?>
						<div class="ml-4">
							<?php switch ($pref->type ) { case 'INPUT': ?>
								<div class="">
									<br>
									<label class="control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?>:</span></label>
									<input id="<?php echo $key ?>" type="text" style="width: 300px;display: inline;" data-user_id="<?php echo $this->user['id']; ?>" data-user_pref="<?php echo $key ?>" size="20" maxlength="15" class="form-control" value="<?php if (!empty($pref->value)) { echo htmlentities($pref->value); } ?>"></input>
								</div>
								<?php break; case 'CHECKBOX': ?>
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="<?php echo $key ?>" data-user_id="<?php echo $this->user['id']; ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>"></span><?php } ?>
								</div>
								<?php break; case 'SPECIAL_REQUEST': ?>
								<div class="custom-control custom-switch">
									<input class="custom-control-input"  id="<?php echo $key ?>" data-user_id="<?php echo $this->user['id']; ?>" data-user_pref="<?php echo $key ?>" data-user_pref_value_check="OPTED_IN" data-user_pref_value_uncheck="OPTED_OUT" type="checkbox" <?php if ($pref->value == 'OPTED_IN' ) {?>checked="checked"<?php } ?> />
									<label class="custom-control-label" for="<?php echo $key ?>"><span class="font-weight-bold"><?php echo $pref->description ?></span></label> <span class="font-size-small"><?php if(!empty($pref->information)){ ?><span data-toggle="tooltip" class="fa fa-info-circle" data-placement="top" title="<?php echo $pref->information ?>. Applies to new orders."></span><?php } ?>
													<textarea maxlength="<?php echo OrdersCustomization::SPECIAL_REQUEST_MAX_CHARS; ?>" id="<?php echo OrdersCustomization::determineDetailsKey($key) ?>" data-user_id="<?php echo $this->user['id']; ?>" data-user_pref="<?php echo OrdersCustomization::determineDetailsKey($key) ?>" class="form-control"><?php echo htmlentities($pref->details);?></textarea>
														Special Request notes are visible to guests. 90 Character limit.
								</div>
								<?php break; } ?>
						</div>
		</div>
		<?php } ?>
		</td>
		</tr>
		<?php } ?>
		<?php if (false && $this->can['modify_user_preferences_round_up']) { ?>
			<tr>
				<td class="guest_details_list_name">Dream Dinners Foundation Auto Round Up Preference</td>
				<td class="guest_details_list_item">
					<select data-user_pref="ltd_auto_round_up" data-user_id="<?php echo $this->user['id']; ?>">
						<option value="0">Select a Round Up value</option>
						<option value="1" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '1') { ?>selected="selected"<?php } ?>>
							Nearest Dollar
						</option>
						<option value="2" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '2') { ?>selected="selected"<?php } ?>>
							2 Dollars
						</option>
						<option value="5" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '5') { ?>selected="selected"<?php } ?>>
							5 Dollars
						</option>
						<option value="10" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '10') { ?>selected="selected"<?php } ?>>
							10 Dollars
						</option>
						<option value="35" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '35') { ?>selected="selected"<?php } ?>>
							35 Dollars
						</option>
						<option value="54" <?php if (!empty($this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value']) && $this->user['preferences'][CUser::LTD_AUTO_ROUND_UP]['value'] == '54') { ?>selected="selected"<?php } ?>>
							54 Dollars
						</option>
					</select>
				</td>
			</tr>
		<?php } ?>

		<tr>
			<td class="bgcolor_dark catagory_row" colspan="2">Credit &amp; Dinner Dollars</td>
		</tr>
		<tr>
			<td class="guest_details_list_name">Store Credit<br /><font style="font-size:smaller">(Guest(s) that <?php echo  $this->user['firstname'] . ' ' . $this->user['lastname']; ?><br /> has invited to DD)</font></td>
			<td class="guest_details_list_item">
				Direct Referral / IAF Credit: <?php echo (isset($this->IAFRollup)) ? CTemplate::moneyFormat($this->IAFRollup) : '$0'; ?><br />
				Direct Store Credit: <?php echo (isset($this->hasDirectCredit) && $this->hasDirectCredit && isset($this->DirectRollup)) ? CTemplate::moneyFormat($this->DirectRollup) : '$0'; ?><br />
			</td>
		</tr>
		<tr>
			<td class="guest_details_list_name">Unused Gift Card Credit:</td>
			<td class="guest_details_list_item">$<?php echo $this->GCRollup ?></td>
		</tr>
		<?php if (isset($this->available_pp_credit)) {?>
			<tr>
				<td class="guest_details_list_name">Available Dinner Dollars:</td>
				<td class="guest_details_list_item">$<?php echo $this->available_pp_credit ?></td>
			</tr>

		<?php } ?>
		<?php
		foreach ($this->SFIData as $store => $data)
		{
			if ($store == 'admin') { ?>
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Personal Information</td>
				</tr>
			<?php } else if ($store == 'storeData') { ?>
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Personal Information</td>
				</tr>
			<?php } else { ?>
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2"><?php echo $store?> Data</td>
				</tr>
			<?php } ?>

			<?php if (isset($data[BIRTH_MONTH_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Birth Month</td>
				<td class="guest_details_list_item"><?php echo $data[BIRTH_MONTH_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[BIRTH_YEAR_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Birth Year</td>
				<td class="guest_details_list_item"><?php echo $data[BIRTH_YEAR_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[NUMBER_KIDS_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Number of Kids at Home</td>
				<td class="guest_details_list_item"><?php echo $data[NUMBER_KIDS_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[FAMILY_SIZE_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Number of Adults at Home</td>
				<td class="guest_details_list_item"><?php echo $data[FAMILY_SIZE_FIELD_ID] ?></td>
			</tr>
		<?php } ?>
			<?php if (isset($data[HOW_MANY_PEOPLE_FEEDING_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Number Feeding at Home</td>
				<td class="guest_details_list_item"><?php echo $data[HOW_MANY_PEOPLE_FEEDING_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">How many easy, homemade dinners would you like to serve your family each week?</td>
				<td class="guest_details_list_item"><?php echo $data[DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[FAVORITE_MEAL_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Favorite Dream Dinners Meal</td>
				<td class="guest_details_list_item"><?php echo $data[FAVORITE_MEAL_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[WHY_WORKS_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Why does Dream Dinners work for you?</td>
				<td class="guest_details_list_item"><?php echo $data[WHY_WORKS_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[GUEST_EMPLOYER_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Guests' Employment Details</td>
				<td class="guest_details_list_item"><?php echo $data[GUEST_EMPLOYER_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[SPOUSE_EMPLOYER_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Spouses' Employment Details</td>
				<td class="guest_details_list_item"><?php echo $data[SPOUSE_EMPLOYER_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[UPCOMING_EVENTS_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Upcoming Events</td>
				<td class="guest_details_list_item"><?php echo $data[UPCOMING_EVENTS_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[MISC_NOTES_FIELD_ID])) { ?>
			<tr>
				<td class="guest_details_list_name">Miscellaneous Notes</td>
				<td class="guest_details_list_item"><?php echo $data[MISC_NOTES_FIELD_ID] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[USE_LISTS])) { ?>
			<tr>
				<td class="guest_details_list_name">Do you use lists to keep you organized?</td>
				<td class="guest_details_list_item"><?php echo $data[USE_LISTS] ?></td>
			</tr>
		<?php } ?>

			<?php if (isset($data[NUMBER_NIGHTS_OUT])) { ?>
			<tr>
				<td class="guest_details_list_name">How many nights a week do you eat out or pick up dinners?</td>
				<td class="guest_details_list_item"><?php echo $data[NUMBER_NIGHTS_OUT] ?></td>
			</tr>
		<?php } ?>



		<?php } ?>

		<?php
		if( $this->canViewReferralSources )
		{
			echo '<tr><td class="bgcolor_dark catagory_row" colspan="2">How ' . $this->user['firstname'] . ' ' . $this->user['lastname'] . ' heard about Dream Dinners</td></tr>';

			if( count( $this->arReferralSources ) )
			{
				foreach( $this->arReferralSources as $source => $meta )
				{
					if (empty($meta))
					{
						echo '<tr>';
						echo '<td class="guest_details_list_name">How did you hear</td>';
						echo '<td class="guest_details_list_item">' . $source . '</td>';
						echo '</tr>';
					}
					else
					{
						echo '<tr>';
						echo '<td class="guest_details_list_name">' . $source . '</td>';
						echo '<td class="guest_details_list_item">' . $meta . '</td>';
						echo '</tr>';
					}
				}
			}
			else
			{
				echo '<tr><td class="guest_details_list_item" colspan="2">No Information</td></tr>';
			}
		}
		?>
		</table>
	</div>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>