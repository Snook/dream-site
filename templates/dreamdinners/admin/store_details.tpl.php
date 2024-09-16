<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/clipboard/clipboard.min.js'); ?>
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
<?php $this->setScriptVar('time_picker_hours = ' . ($this->time_picker_hours ? $this->time_picker_hours : '{}') . ';'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php $isSiteAdmin = ($this->form_login['user_type'] == 'SITE_ADMIN' || (isset($this->siteadminoverride) == true && $this->siteadminoverride == true)); ?>

	<form action="" method="post" class="needs-validation" novalidate>

		<?php if (isset($this->form_store_details['hidden_html'])) echo $this->form_store_details['hidden_html'];?>

		<span style="font-size: 13pt; font-weight: bold;">Store: <?php echo $this->form_store_details['store_name']; ?></span>
		<br /><br />

		<?php if( $isSiteAdmin ) { ?>
			<div align="right"><a class="btn btn-primary btn-sm" id="archive_store" data-home_office_id="<?php echo $this->store['home_office_id']; ?>" data-store_id="<?php echo $this->store['id']; ?>"
								  data-store_name="<?php echo $this->store['store_name']; ?>">Archive and Re-Open Store</a>
				<a id="delete_store" data-store_id="<?php echo $this->store['id']; ?>" class="btn btn-primary btn-sm">Delete Store</a></div>
		<?php } ?>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Basic Information</td>
			</tr>
			<?php if ($isSiteAdmin) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 210px;">Active:</td>
					<td class="bgcolor_light"><?php if( $isSiteAdmin ) { echo $this->form_store_details['active_html']; } else { echo ($this->form_store_details['active']? 'Yes': 'No');} ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Show on Customer Site:</td>
					<td class="bgcolor_light"><?php if( $isSiteAdmin ) { echo $this->form_store_details['show_on_customer_site_html']; } else { echo ($this->form_store_details['show_on_customer_site']? 'Yes': 'No');} ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="bgcolor_light" style="text-align: right;vertical-align:top;">About Store:</td>
				<td class="bgcolor_light">
					<?php echo $this->form_store_details['store_description_html']; ?>
					<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="store_description_preview"></div>
				</td>
			</tr>
		</table>

		<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Public Bio</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 210px;">Store Name:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['bio_store_name_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager Name:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['bio_primary_party_name_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager Title:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['bio_primary_party_title_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager Story:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['bio_primary_party_story_html']; ?>
						<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_primary_party_story_preview"></div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager #2 Name:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['bio_secondary_party_name_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager #2 Title:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['bio_secondary_party_title_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Owner/Manager #2 Story:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['bio_secondary_party_story_html']; ?>
						<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_secondary_party_story_preview"></div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Meet the Team:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['bio_team_description_html']; ?>
						<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_team_description_preview"></div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;vertical-align:top;">Store Hours:</td>
					<td class="bgcolor_light">
						<?php include $this->loadTemplate('admin/subtemplate/helpers/store_hour_select.tpl.php'); ?>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;vertical-align:top;">Store Holiday Hours:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['bio_store_holiday_hours_html']; ?>
						<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="bio_store_holiday_hours_preview"></div>
					</td>
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
					<div class="input-group">
						<?php echo ($isSiteAdmin) ? $this->form_store_details['franchise_id_html'] : $this->franchise_name; ?>
						<?php if ($isSiteAdmin) { ?>
							<div class="input-group-append">
								<a href="/backoffice/franchise-details?id=<?php echo $this->form_store_details['franchise_id']; ?>" class="btn btn-primary btn-sm" style="float: right;">View Entity</a>
							</div>
						<?php } ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Store Name:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['store_name_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Street Address:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['address_line1_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Suite, Unit:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['address_line2_html']; ?></td>
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
				<td class="bgcolor_light" style="text-align: right;">State County:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['county_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right; vertical-align: top;">Store Latitude\Longitude:</td>
				<td class="bgcolor_light">
					<?php if (!$isSiteAdmin) { ?>
						<div><iframe width="500" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="//maps.google.com/maps?&q=loc:<?php echo $this->store['address_latitude']; ?>,<?php echo $this->store['address_longitude']; ?>&z=<?php echo (!empty($this->store['address_latitude'])) ? '17' : '3' ?>&output=embed&iwloc=near"></iframe></div>
					<?php } else { ?>
						<fieldset class="gllpLatlonPicker" style="border: 0;">
							<input type="hidden" class="gllpZoom" value="17"/>
							<input type="hidden" class="gllpSearchField" value="<?php echo $this->store['linear_address']; ?>">
							<div class="gllpMap">Google Maps</div>

							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">Latitude</span>
								</div>
								<?php echo $this->form_store_details['address_latitude_html']; ?>
								<div class="input-group-prepend">
									<span class="input-group-text">Longitude</span>
								</div>
								<?php echo $this->form_store_details['address_longitude_html']; ?>
								<div class="input-group-append">
									<input type="button" class="btn btn-primary btn-sm gllpSearchButton" value="Fill by Address">
								</div>
							</div>
						</fieldset>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Map link:</td>
				<td class="bgcolor_light"><a href="<?php echo $this->store['map']; ?>" id="map_link" class="btn btn-primary btn-sm" target="_blank">Click To Test</a></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;vertical-align:top;">Google Place ID:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['google_place_id_html']; ?> (more accurately display in Google Maps, <a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank">find place id</a>)</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;vertical-align:top;">Location directions:</td>
				<td class="bgcolor_light">
					<?php echo $this->form_store_details['address_directions_html']; ?>
					<div style="border: 2px solid #a8a94c; padding: 4px; display: none;" id="address_directions_preview"></div>
				</td>
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
			<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Store Information Page QR Code:</td>
					<td class="guest_details_list_item">
						<div class="input-group">
							<input type="text" id="store_page_link" class="form-control" aria-label="Store landing page" value="<?php echo HTTPS_SERVER; ?><?php echo $this->DAO_store->getPrettyUrl(); ?>" readonly>
							<div class="input-group-append">
								<div class="input-group-text btn-clip" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard" data-clipboard-target="#store_page_link" ><i class="fas fa-clipboard-list"></i></div>
							</div>
							<div class="input-group-append">
								<a class="input-group-text" data-toggle="tooltip" data-placement="top" title="Download QR code" href="<?php echo HTTPS_BASE; ?>processor?processor=qr_code&op=store_info&d=1&s=10&id=<?php echo $this->store['id']; ?>" ><i class="fas fa-qrcode"></i></a>
							</div>
						</div>
						<?php if (!empty($this->shortURLArray)) { ?>
							<ul class="list-group list-group-horizontal">
								<?php foreach ($this->shortURLArray AS $DAO_short_url) { ?>
									<?php if (!empty($DAO_short_url->is_deleted)) { ?>
										<li class="list-group-item list-group-item-gray-900 p-1"><?php echo $DAO_short_url->short_url; ?></li>
									<?php } ?>
								<?php } ?>
							</ul>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
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
			<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Twitter:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['social_twitter_html']; ?> (account name only, e.g DDLancasterPA)</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Facebook:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['social_facebook_html']; ?> (account name only, e.g. dreamdinnersmillcreek)</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Instagram:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['social_instagram_html']; ?> (account name only, e.g. dreamdinnersmillcreek)</td>
				</tr>
			<?php } ?>
		</table>

		<table style="width: 100%;">
			<tr>
				<td class="bgcolor_dark catagory_row">Franchise Contact Information</td>
			</tr>
			<tr>
				<td>

					<div class="store_contact_info" style="float: left;">

						<h4>Package Shipping Address</h4>

						<div><?php echo $this->form_store_details['pkg_ship_same_as_store_html']; ?></div>
						<div><?php echo $this->form_store_details['pkg_ship_is_commercial_html']; ?></div>

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

						<div><?php echo $this->form_store_details['letter_ship_same_as_store_html']; ?></div>
						<div><?php echo $this->form_store_details['letter_ship_is_commercial_html']; ?></div>

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

						<div>
							<div class="input-group mb-4">
								<div class="input-group-prepend">
									<div class="input-group-text">User ID</div>
								</div>
								<?php echo $this->form_store_details['manager_1_user_id_html']; ?>
								<div class="input-group-append">
									<a class="btn btn-primary" data-guestsearch="add_manager" data-select_button_title="Add Manager" data-all_stores_checked="true" data-select_function="addManager" data-tooltip="Select manager" class="btn btn-primary btn-sm">Select manager</a>
								</div>
							</div>
							<div><input class="form-control" id="manager_1_name" type="text" disabled="disabled" value="<?php echo $this->manager_DAO_user->firstname; ?> <?php echo $this->manager_DAO_user->lastname; ?>" /></div>
							<div><input class="form-control" id="manager_1_primary_email" type="text" disabled="disabled" value="<?php echo $this->manager_DAO_user->primary_email; ?>" /></div>
							<div><input class="form-control" id="manager_1_telephone_1" type="text" disabled="disabled" value="<?php echo $this->manager_DAO_user->telephone_1; ?>" /></div>
						</div>
					</div>

				</td>
			</tr>
		</table>

		<?php if ($this->DAO_store->isDistributionCenter()) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Shipping Settings</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 400px;">Default number of<br />orders per day:</td>
					<td class="bgcolor_light">
						<div class="input-group">
							<?php echo $this->form_store_details['default_delivered_sessions_html']; ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Shipping Fee - Large</td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">$</span>
							</div>
							<?php echo $this->form_store_details['large_ship_cost_html']; ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Shipping Fee - Medium</td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">$</span>
							</div>
							<?php echo $this->form_store_details['medium_ship_cost_html']; ?>
						</div>
					</td>
				</tr>
			</table>
		<?php } ?>

		<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Session Settings</td>
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
						<div class="input-group">
							<div class="input-group-prepend">
							<span class="input-group-text">
								<?php echo $this->form_store_details['close_interval_type_html'][CStore::HOURS]; ?>
							</span>
							</div>
							<?php echo $this->form_store_details['close_session_hours_html']; ?>
							<div class="input-group-append">
								<span class="input-group-text">Hours prior</span>
							</div>
							<div class="input-group-prepend">
							<span class="input-group-text">
								<?php echo $this->form_store_details['close_interval_type_html'][CStore::ONE_FULL_DAY] ; ?>
							</span>
							</div>
							<div class="input-group-append">
								<span class="input-group-text">1 day prior</span>
							</div>
						</div>

					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Support Made for You sessions (Special Events):<br />
																		 (If unchecked the Made for You sessions <br />feature will NOT be available.)</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_special_events_html']; ?></td>
				</tr>
			</table>
		<?php } ?>

		<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Calendar Order Type Descriptions</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Assembly</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['assembly_session_desc_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Pick Up Sessions</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['pickup_session_desc_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Home Delivery Sessions</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['delivery_session_desc_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Community Pick Up Sessions</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['remote_pickup_session_desc_html']; ?></td>
				</tr>
			</table>
		<?php } ?>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Programs</td>
			</tr>
			<?php if (isset($this->form_store_details['supports_plate_points_html']) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Opt in to PLATEPOINTS Dinner Dollars and rewards:</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_plate_points_html']; ?></td>
				</tr>
				<tr id="supports_plate_points_signature_row" style="display:none">
					<td class="bgcolor_light" style="text-align: right; color:red;">Please enter your name as the party responsible for opting in the PLATEPOINTS program.</td>
					<td class="bgcolor_light" style="vertical-align:top;"><label for="supports_plate_points_signature"  id="supports_plate_points_signature_lbl" message="You must provide your full name when opting into PLATEPOINTS.">
						</label><?php echo $this->form_store_details['supports_plate_points_signature_html']; ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="bgcolor_light" style="text-align: right;"><b>Support PLATEPOINTS enhancements and promotions</b> such as, but not limited to Double Taste Host Incentives, Summer Bonus Points and Early Bird Incentives.<br /> (if unchecked, PLATEPOINTS enhancements and promotions feature will NOT be available)</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['supports_plate_points_enhancements_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Opt out of Meal Prep Workshop:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['dream_taste_opt_out_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;"><b>Support seasonal promotions</b> including, but not limited to summer retention programs and holiday promotions. <br />(If unchecked, you will not be included in seasonal promotions.)</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['supports_retention_programs_html']; ?></td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Additional Settings</td>
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
				<td class="bgcolor_light"><?php echo $this->form_store_details['receive_low_inv_alert_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Show "Pre-Assembled" label on print menu:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['show_print_menu_pre_assembled_label_html']; ?></td>
			</tr>
		</table>

		<table style="width: 100%; margin-bottom: 10px;">
			<tr>
				<td class="bgcolor_dark catagory_row" colspan="2">Financial Settings</td>
			</tr>
			<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Delayed Payment Supported:</td>
					<td class="bgcolor_light">
						<?php echo $this->form_store_details['supports_delayed_payment_html']; ?>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Delayed Payment Deposit Amount:<br /><span style="font-size:smaller;">($20 minimum)</span></td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">$</span>
							</div>
							<?php echo $this->form_store_details['default_delayed_payment_deposit_html']; ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Delayed Payment Order Minimum<br /><span style="font-size:smaller;">($0.00 indicates no minimum)</span></td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">$</span>
							</div>
							<?php echo $this->form_store_details['delayed_payment_order_minimum_html']; ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Guest Home Delivery Fee:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">$</span>
						</div>
						<?php echo $this->form_store_details['delivery_fee_html']; ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Home Delivery Radius:</td>
				<td class="bgcolor_light">
					<?php echo $this->form_store_details['delivery_radius_html']; ?>
				</td>
			</tr>
			<?php if (!empty($this->form_store_details['supports_bag_fee_html'])) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Charge Reusable Bag Fees:<br /><span style="font-size:smaller;">(Charges 1 bag per 4 core dinners)</span></td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_bag_fee_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Set Reusable Bag Fee</td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">$</span>
							</div>
							<?php echo $this->form_store_details['default_bag_fee_html']; ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="bgcolor_light" style="text-align: right; width: 400px;">Food tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['food_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Non-food tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['total_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Service tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['other1_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Membership Fee tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['other2_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Delivery tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['other3_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Reusable Bag Fee tax:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<?php echo $this->form_store_details['other4_tax_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">American Express processing:</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['credit_card_amex_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Discover Card processing:</td>
				<td class="bgcolor_light">
					<?php echo $this->form_store_details['credit_card_discover_html']; ?>
					<br />
					<span style="color: #cc4444;">
						<i>* Your PayPal account must be configured for these card types before enabling them.</i>
						<br />
						<i>** It is not required to enable Discover Card processing to support Gift Cards.</i>
						<br />
						<i>*** Gift Card support does not enable Discover Card support.</i>
					</span>
				</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Supports Meal Customization</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['supports_meal_customization_html']; ?></td>
			</tr>

			<tr class="customization_fields" style="display:<?php if (!$this->store_supports_meal_customization) { ?>none;<?php } ?>">
				<td class="bgcolor_light" style="text-align: right;">Allow Pre-Assembled Customization</td>
				<td class="bgcolor_light"><?php echo $this->form_store_details['allow_preassembled_customization_html']; ?></td>
			</tr>
			<tr class="customization_fields" style="display:<?php if (!$this->store_supports_meal_customization) { ?>none;<?php } ?>">
				<td class="bgcolor_light" style="text-align: right;">Customization Fee<br><span style="font-size:smaller;">(Flat Rate based on number of core meals)</span></td>
				<td class="bgcolor_light">
					<table>
						<?php foreach ( $this->customization_fees as $key => $fee){ ?>
							<tr>
								<td><?php echo $fee['description']; ?>:</td>
								<td><div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">$</span>
										</div>
										<?php echo $this->form_store_details[$fee['name'].'_html']; ?>
									</div>
								</td>
							</tr>
						<?php } ?>
					</table>
				</td>
			</tr>
			<tr class="customization_fields" style="display:<?php if (!$this->store_supports_meal_customization) { ?>none;<?php } ?>">
				<td class="bgcolor_light" style="text-align: right;">Default Customization Close Interval:</td>
				<td class="bgcolor_light">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<?php echo $this->form_store_details['meal_customization_close_interval_type_html'][CStore::HOURS]; ?>
							</span>
						</div>
						<?php echo $this->form_store_details['close_customization_session_hours_html']; ?>
						<div class="input-group-append">
							<span class="input-group-text">Hours prior</span>
						</div>
						<div class="input-group-prepend">
							<span class="input-group-text">
								<?php echo $this->form_store_details['meal_customization_close_interval_type_html'][CStore::FOUR_FULL_DAYS] ; ?>
							</span>
						</div>
						<div class="input-group-append">
							<span class="input-group-text">4 day prior</span>
						</div>
					</div>

				</td>
			</tr>

		</table>

		<?php if (!$this->DAO_store->isDistributionCenter()) { ?>
			<table style="width: 100%; margin-bottom: 10px;">
				<tr>
					<td class="bgcolor_dark catagory_row" colspan="2">Available Positions</td>
				</tr>
				<?php foreach ($this->job_array AS $job_id => $job) { ?>
					<tr>
						<td class="bgcolor_light" style="text-align: right; width: 400px;"><label for="job_position[<?php echo $job_id; ?>]"><?php echo $job['title']; ?></label></td>
						<td class="bgcolor_light"><input type="checkbox" id="job_position[<?php echo $job_id; ?>]" name="job_position[<?php echo $job_id; ?>]" <?php echo (!empty($job['available'])) ? 'checked' : ''; ?> /> <?php if (!empty($job['available'])) { ?><label for="job_position[<?php echo $job_id; ?>]">Posted <?php echo CTemplate::dateTimeFormat($job['timestamp_updated'], CONCISE) ;?></label><?php } ?></td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>

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
									<td class="bgcolor_light"><a href="/backoffice/user-details?id=<?php echo $user_id; ?>"><?php echo $userInfo['firstname']; ?> <?php echo $userInfo['lastname']; ?></a></td>
									<td class="bgcolor_light"><a href="/backoffice/email?id=<?php echo $user_id; ?>"><?php echo $userInfo['primary_email']; ?></a></td>
									<td class="bgcolor_light"><a href="/backoffice/user-details?id=<?php echo $user_id; ?>"><?php echo (!empty($userInfo['last_login'])) ? CTemplate::dateTimeFormat($userInfo['last_login'], MONTH_DAY_YEAR) : 'Never'; ?></a></td>
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
					<td class="bgcolor_light" style="text-align: right;">Home Office ID</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['home_office_id_html']; ?></td>
				</tr>
			<?php } ?>
			<?php if( $isSiteAdmin == TRUE ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 210px;">Vanity URL (numbers, hyphens and lower case letters allowed)</td>
					<td class="bgcolor_light">
						<div><?php echo $this->form_store_details['short_url_html']; ?></div>
						<?php if (!empty($this->shortURLArray)) { ?>
							<ul class="list-group list-group-horizontal">
								<?php foreach ($this->shortURLArray AS $DAO_short_url) { ?>
									<?php if (!empty($DAO_short_url->is_deleted)) { ?>
										<li class="list-group-item list-group-item-gray-900 p-1"><?php echo $DAO_short_url->short_url; ?></li>
									<?php } ?>
								<?php } ?>
							</ul>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 400px;">Enable Menu Imports - Active/Show on Customer Site always import menus regardless of this setting, enable this to import menus for an inactive store</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['ssm_builder_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Grand Opening Date</td>
					<td class="bgcolor_light">
						<?php echo $this->grand_opening_label ?><br />
						<?php echo $this->form_store_details['update_grandopeningdate_html']; ?>Click to set/edit Grand Opening Date
						<script>DateInput('grand_opening_date', true, 'MM/DD/YYYY', '<?php echo $this->initDate ?>');</script>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;" style="vertical-align:middle;">Vertical Response Tracking Code</td>
					<td class="bgcolor_light">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">cts.vresp.com/s.gif?h=</span>
							</div>
							<?php echo $this->form_store_details['vertical_response_code_html']; ?>
							<div class="input-group-append">
								<span class="input-group-text">&amp;amount=PUR ...</span>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Daily Story Tenant UID</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['dailystory_tenant_uid_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Core Pricing Tier</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['core_pricing_tier_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right; width: 400px;">Supports Starter Pack</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_intro_orders_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Supports Fundraising</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_fundraiser_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Supports DDF Round Up</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_ltd_roundup_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Supports Home Delivery</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_delivery_html']; ?></td>
				</tr>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Supports Community Pick Up</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['supports_offsite_pickup_html']; ?></td>
				</tr>
			<?php } else { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Grand Opening Date</td>
					<td class="bgcolor_light"><?php echo $this->grand_opening_label ?></td>
				</tr>
			<?php } ?>
			<?php if( isset( $this->form_store_details['gp_account_id_html'] ) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Great Plains Account ID</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['gp_account_id_html']; ?></td>
				</tr>
			<?php } ?>
			<?php if( isset( $this->form_store_details['door_dash_id_html'] ) ) { ?>
				<tr>
					<td class="bgcolor_light" style="text-align: right;">Door Dash Marketplace ID</td>
					<td class="bgcolor_light"><?php echo $this->form_store_details['door_dash_id_html']; ?></td>
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
				<td class="bgcolor_light"><?php echo $this->store['timestamp_updated']; ?> by <a href="/backoffice/user-details?id=<?php echo $this->store['updated_by']; ?>"><?php echo $this->store['updated_by']; ?></a></td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align: right;">Created on:</td>
				<td class="bgcolor_light"><?php echo $this->store['timestamp_created']; ?> by <a href="/backoffice/user-details?id=<?php echo $this->store['created_by']; ?>"><?php echo $this->store['created_by']; ?></a></td>
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