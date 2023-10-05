<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/calendarDateInput.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_details.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery-gmaps-latlon-picker.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/jquery-gmaps-latlon-picker.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/store_details.css'); ?>
<?php $this->setScript('head', '//maps.googleapis.com/maps/api/js?v=3&amp;key=' . GOOGLE_APIKEY); ?>
<?php $this->setOnload('store_details_init();'); ?>
<?php $this->assign('page_title','Store Details'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php $isSiteAdmin = ($this->form_login['user_type'] == 'SITE_ADMIN' || (isset($this->siteadminoverride) == true && $this->siteadminoverride == true)); ?>

	<form action="" method="post" onSubmit="return override_check_form(this);" >

		<?php if (isset($this->form_store_details[hidden_html])) echo $this->form_store_details['hidden_html'];?>

		<span style="font-size: 13pt; font-weight: bold;">Distribution Center: <?php echo $this->form_store_details['ddu_id'] ?></span>
		<br /><br />

		<?php if( $isSiteAdmin ) { ?>
			<div align="right"><a class="btn btn-primary btn-sm" id="archive_store" data-home_office_id="<?php echo $this->store['home_office_id']; ?>" data-store_id="<?php echo $this->store['id']; ?>"
								  data-store_name="<?php echo $this->store['store_name']; ?>">Archive and Re-Open Store</a>
				<a id="delete_store" data-store_id="<?php echo $this->store['id']; ?>" class="btn btn-primary btn-sm">Delete Store</a></div>
		<?php } ?>

		<?php if ($isSiteAdmin) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Basic Information</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 210px;">Active:</td>
					<td class="bgcolor_light"><?php if( $isSiteAdmin ) { echo $this->form_store_details['active_html']; } else { echo ($this->form_store_details['active']? 'Yes': 'No');} ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;vertical-align:top;">About Store:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['store_description_html']; ?>
						<div style="border: 1px solid brown; padding: 4px; display: none;" id="store_description_preview"></div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Show on Customer Site:</td>
					<td class="bgcolor_light"><?php if( $isSiteAdmin ) { echo $this->form_store_details['show_on_customer_site_html']; } else { echo ($this->form_store_details['show_on_customer_site']? 'Yes': 'No');} ?></td>
				</tr>
			</table>
		<?php } ?>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Location Information
					<?php if (!$isSiteAdmin) { ?>
						<span style="font-size:9pt; font-weight:lighter">Please contact <a href="mailto:<?php echo IT_EMAIL; ?>">Home Office</a> to request changes to your store location information.</span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 210px;">Entity/Owner:</td>
				<td class="bgcolor_light">
					<?php if ($isSiteAdmin) { ?><a href="/backoffice/franchise-details?id=<?php echo $this->form_store_details['franchise_id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary btn-sm" style="float: right;">View Entity</a><?php } ?>
					<?php echo ($isSiteAdmin) ? $this->form_store_details['franchise_id_html'] : $this->franchise_name; ?>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Store Name:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['store_name_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Address 1:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['address_line1_html']; ?> (Street Address)</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Address 2:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['address_line2_html']; ?> (Suite, Unit)</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">City:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['city_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">State:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['state_id_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Postal Code:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['postal_code_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">USPS ADC:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['usps_adc_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">County:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['county_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Timezone:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['timezone_id_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Observes DST:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['observes_DST_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Assigned District:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['regiondropdown_html']; ?></td>
			</tr>
			<?php if ($isSiteAdmin) {?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Assigned Manager:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['coachdropdown_html']; ?></td>
				</tr>
			<?php } ?>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Customer Contact Information
					<?php if (!$isSiteAdmin) { ?>
						<span style="font-size:9pt; font-weight:lighter">Please contact <a href="mailto:<?php echo IT_EMAIL; ?>">Home Office</a> to request changes to your store contact information.</span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 210px;">Store Email:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['email_address_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Telephone (Day):</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['telephone_day_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Telephone (Evening):</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['telephone_evening_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Text Messaging (SMS) Number:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['telephone_sms_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Fax Line:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['fax_html']; ?></td>
			</tr>
		</table>

		<table style="width: 100%;">
			<tr>
				<td class="bgcolor_dark catagory_row">Franchise Contact Information</td>
			</tr>
			<tr>
				<td>
					<div class="store_contact_info" style="float: left;">
						<h4>Package Shipping Address</h4>

						<div><?php echo $this->form_store_details['pkg_ship_same_as_store_html']; ?> Same as store address.</div>
						<div><?php echo $this->form_store_details['pkg_ship_is_commercial_html']; ?> Commercial address.</div>

						<div><?php echo $this->form_store_details['pkg_ship_attn_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_address_line1_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_address_line2_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_city_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_state_id_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_postal_code_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_telephone_day_html']; ?></div>

					</div>

					<div class="store_contact_info" style="float: right;">

						<h4>Letter Mailing Address &amp; Legal Notices</h4>

						<div><?php echo $this->form_store_details['letter_ship_same_as_store_html']; ?> Same as store address.</div>
						<div><?php echo $this->form_store_details['letter_ship_is_commercial_html']; ?> Commercial address.</div>

						<div><?php echo $this->form_store_details['letter_ship_attn_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_address_line1_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_address_line2_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_city_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_state_id_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_postal_code_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_telephone_day_html']; ?></div>

					</div>

					<div class="clear"></div>

					<div class="store_contact_info" style="float: left;">

						<h4>Operating Manager Contact Information</h4>

						<div><?php echo $this->form_store_details['manager_1_user_id_html']; ?><span data-guestsearch="add_manager" data-select_button_title="Add Manager" data-all_stores_checked="true" data-select_function="addManager" data-tooltip="Add Manager" class="btn btn-primary btn-sm">Add Manager</span></div>
						<div><input id="manager_1_name" type="text" disabled="disabled" value="<?php echo $this->store['manager_1_firstname']; ?> <?php echo $this->store['manager_1_lastname']; ?>" /></div>
						<div><input id="manager_1_primary_email" type="text" disabled="disabled" value="<?php echo $this->store['manager_1_primary_email']; ?>" /></div>
						<div><input id="manager_1_telephone_1" type="text" disabled="disabled" value="<?php echo $this->store['manager_1_telephone_1']; ?>" /></div>

					</div>

				</td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Session Settings</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 400px;">Default number of<br />Delivery Sessions:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['default_delivered_sessions_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 400px;">Default number of<br />Starter Pack slots:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['default_intro_slots_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Publish notes on<br />customer calendar:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['publish_session_details_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Default close interval:</td>
				<td class="bgcolor_light">
					<?php
					echo $this->form_store_details['close_interval_type_html'][CStore::HOURS];
					echo $this->form_store_details['close_session_hours_html'] . "hours prior";
					echo $this->form_store_details['close_interval_type_html'][CStore::ONE_FULL_DAY] . "1 day prior";
					?>
				</td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Additional Settings</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 400px;">Tab key moves down columns in direct/edit order:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['serving_tabindex_vertical_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Hide guest carryover notes:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['hide_carryover_notes_html']; ?><div style="color: #cc4444;font-style:italic;">*It is recommended that you do not put anything in the carryover note that you wouldn't want the guest to see.</div></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Hide dashboard snapshot on BackOffice Home:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['hide_fadmin_home_dashboard_html']; ?><div style="color: #cc4444;font-style:italic;">*Only owner and manager accounts can see the dashboard snapshot when enabled.</div></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Receive Low Item Inventory Alert:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['receive_low_inv_alert_html']; ?><div style="color: #cc4444;font-style:italic;"></div></td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Financial Settings</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Delivery Fee - Large</td>
				<td class="bgcolor_light">$<?php echo $this->form_store_details['large_ship_cost_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Delivery Fee - Medium</td>
				<td class="bgcolor_light">$<?php echo $this->form_store_details['medium_ship_cost_html']; ?></td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Personnel</td>
			</tr>
			<tr>
				<td colspan="2">
					<table style="width: 100%;">
						<tr>
							<td class="bgcolor_medium header_row">User Type</td>
							<td class="bgcolor_medium header_row">Name</td>
							<td class="bgcolor_medium header_row">Email</td>
							<td class="bgcolor_medium header_row">Last Login</td>
							<td class="bgcolor_medium header_row">NDA Accepted</td>
							<td class="bgcolor_medium header_row">On Store Info Page</td>
						</tr>
						<?php if (!empty($this->store['personnel'])) { ?>
							<?php foreach ($this->store['personnel'] as $user_id => $userInfo) { ?>
								<tr>
									<td class="bgcolor_light"><a href="/backoffice/access-levels?id=<?php echo $user_id; ?>"><?php echo CUser::userTypeText($userInfo['user_type']); ?></a></td>
									<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $user_id; ?>"><?php echo $userInfo['firstname']; ?> <?php echo $userInfo['lastname']; ?></a></td>
									<td class="bgcolor_light"><a href="/backoffice/email?id=<?php echo $user_id; ?>"><?php echo $userInfo['primary_email']; ?></a></td>
									<td class="bgcolor_light"><a href="/backoffice/user_details?id=<?php echo $user_id; ?>"><?php echo (!empty($userInfo['last_login'])) ? CTemplate::dateTimeFormat($userInfo['last_login'], MONTH_DAY_YEAR) : 'Never'; ?></a></td>
									<td class="bgcolor_light" style="text-align:center;"><?php echo (!empty($userInfo['fadmin_nda_agree'])) ? 'Yes' : '<span style="color: red;">No</span>'; ?></td>
									<td class="bgcolor_light" style="text-align:center;"><a href="/location/<?php echo $this->store['id']; ?>"><?php echo (!empty($userInfo['display_to_public'])) ? 'Yes' : 'No'; ?></a></td>
								</tr>
							<?php } } ?>
					</table>
				</td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">

			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Home Office Use</td>
			</tr>
			<?php if( isset( $this->form_store_details['home_office_id_html'] ) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Home Office ID:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['home_office_id_html']; ?></td>
				</tr>
			<?php } ?>
			<?php if( $isSiteAdmin == TRUE ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Grand Opening Date:</td>
					<td class="bgcolor_light">
						<?php echo $this->grand_opening_label ?><br />
						<?php echo $this->form_store_details['update_grandopeningdate_html']; ?>Click to set/edit Grand Opening Date
						<script>DateInput('grand_opening_date', true, 'MM/DD/YYYY', '<?php echo $this->initDate ?>');</script>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;" style="vertical-align:middle;">Vertical Response Tracking Code:</td>
					<td class="bgcolor_light">
						cts.vresp.com/s.gif?h=<?php echo $this->form_store_details['vertical_response_code_html']; ?>&amp;amount=PUR ...
					</td>
				</tr>
			<?php } else { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Grand Opening Date:</td>
					<td class="bgcolor_light"><?php echo $this->grand_opening_label ?></td>
				</tr>
			<?php } ?>
			<?php if( isset( $this->form_store_details['gp_account_id_html'] ) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Great Plains Account ID:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['gp_account_id_html']; ?></td>
				</tr>
			<?php } ?>

			<?php if( isset( $this->form_store_details['opco_id_html'] ) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Primary Sysco Opco:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['opco_id_html']; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Last update:</td>
				<td class="bgcolor_light"><?php echo $this->store['timestamp_updated']; ?> by <a href="/backoffice/user_details?id=<?php echo $this->store['updated_by']; ?>"><?php echo $this->store['updated_by']; ?></a></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Created on:</td>
				<td class="bgcolor_light"><?php echo $this->store['timestamp_created']; ?> by <a href="/backoffice/user_details?id=<?php echo $this->store['created_by']; ?>"><?php echo $this->store['created_by']; ?></a></td>
			</tr>
			<tr>
				<td colspan="2" class="tbl_section_footer">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="right"><?php echo $this->form_store_details['updateStore_html']; ?></td>
			</tr>
		</table>
	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>