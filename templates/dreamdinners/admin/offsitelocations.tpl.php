<?php $this->setScript('head', SCRIPT_PATH . '/admin/offsitelocations.min.js'); ?>
<?php $this->assign('page_title','Manage Community Pick Up Locations'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Community Pick Up Locations</h1>
			</div>
		</div>

		<?php if (empty($this->store->supports_offsite_pickup)) { ?>

			<p class="font-weight-bold mt-3 text-center">Store does not support Community Pick Up locations.</p>

		<?php } else { ?>

			<div id="detailed_reporting" class="collapse mt-3">

				<?php
				$HIDDENPAGENAME = "admin_offsitelocations";
				$SHOWSINGLEDATE=TRUE;
				$SHOWRANGEDATE=TRUE;
				$SHOWMONTH=TRUE;
				$SHOWYEAR=TRUE;
				$ADDFORMTOPAGE=TRUE;
				$OVERRIDESUBMITBUTTON=TRUE;
				define('SUPPRESS_STORE_SELECTOR', true);
				?>

				<div>
					<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
				</div>

				<div>
					<?php echo $this->form['hidden_html']; ?>
					<?php echo $this->form['offsitelocation_chooser_html']; ?><br /><br />
					<?php echo $this->form['report_submit_html']; ?>
				</div>

				</form>

			</div>


			<hr />

			<div class="row mb-2">
				<div class="col">
					<span id="add_offsitelocation" class="btn btn-primary float-right">Add Community Pick Up location</span>
					<span id="show_detailed_reporting" class="btn btn-primary float-right mr-2">Detailed Reporting</span>
				</div>
			</div>

			<div id="add_offsitelocation_content" class="collapse">

				<form id="offiste_location_form" class="form-row needs-validation" novalidate>
					<input type="hidden" id="edit_location" name="edit_location" value="false">
					<div class="form-group col-12">
						<input class="form-control" type="text" size="30" maxlength="255" id="location_name" name="location_name" placeholder="Location Name" required />
					</div>
					<div class="form-group col-8">
						<input class="form-control" type="text" size="30" maxlength="255" id="address_line1" name="address_line1" placeholder="Address Line 1" required />
					</div>
					<div class="form-group col-4">
						<input class="form-control" type="text" size="30" maxlength="255" id="address_line2" name="address_line2" placeholder="Address Line 2" />
					</div>
					<div class="form-group col-4">
						<input class="form-control" type="text" size="30" maxlength="255" id="city" name="city" placeholder="City" required />
					</div>
					<div class="form-group col-4">
						<select class="custom-select" name="state" id="state" required>
							<?php echo $this->statelist; ?>
						</select>
					</div>
					<div class="form-group col-4">
						<input class="form-control" type="number" size="30" maxlength="255" id="postal_code" name="postal_code" placeholder="Zip Code" required />
					</div>

					<div class="form-group col-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<button type="button" data-guestsearch="contact" data-select_button_title="Add Contact" data-all_stores_checked="false" data-select_function="addContact" class="btn btn-primary"><i class="far fa-address-book font-size-medium-small mx-2"></i></button>
							</div>
							<input class="form-control" type="text" size="30" maxlength="255" id="contact" name="contact" placeholder="Contact person" />
						</div>
					</div>
					<div class="form-group col-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Latitude</div>
							</div>
							<?php echo $this->form['address_latitude_html']; ?>
						</div>
					</div>
					<div class="form-group col-4">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Longitude</div>
							</div>
							<?php echo $this->form['address_longitude_html']; ?>
						</div>
					</div>

					<div class="form-group col-10">
						<input class="btn btn-primary btn-block" id="offsite_submit" name="offsite_submit" type="submit" value="Save Community Pick Up Location" />
					</div>
					<div class="form-group col-2">
						<button class="btn btn-danger btn-block" id="add_offsite_cancel" name="add_offsite_cancel" type="button"  />Cancel</button>
					</div>

				</form>

			</div>

			<table class="table table-sm table-striped ddtemp-table-border-collapse bg-white">
				<thead class="text-center">
				<tr>
					<th>Show in session editor</th>
					<th>Show on customer site</th>
					<th>Contact</th>
					<th>Location</th>
					<th>Address</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Updated</th>
					<th>Manage</th>
				</tr>
				</thead>
				<tbody id="offsitelocation_list">
				<?php if (!empty($this->OffsitelocationArray)) { ?>
					<?php foreach ($this->OffsitelocationArray AS $id => $offsitelocation) { ?>
						<tr data-offsite_id="<?php echo $id; ?>">
							<td class="text-center">
								<div class="custom-control custom-checkbox">
									<input class="custom-control-input" id="location-<?php echo $id; ?>" name="location-<?php echo $id; ?>" data-enable_location="<?php echo $id; ?>" type="checkbox" <?php echo (!empty($offsitelocation->active)) ? 'checked="checked"' : ''; ?> />
									<label class="custom-control-label" for="location-<?php echo $id; ?>"></label>
								</div>
							</td>
							<td class="text-center">
								<div class="custom-control custom-checkbox">
									<input class="custom-control-input" id="location-customer-vis-<?php echo $id; ?>" name="location-customer-vis-<?php echo $id; ?>" data-enable_customer_visibility="<?php echo $id; ?>" type="checkbox" <?php echo (!empty($offsitelocation->show_on_customer_site)) ? 'checked="checked"' : ''; ?> />
									<label class="custom-control-label" for="location-customer-vis-<?php echo $id; ?>"></label>
								</div>
							</td>
							<td><?php if (!empty($offsitelocation->contact_user_id)) { ?><a href="/backoffice/user-details?id=<?php echo $offsitelocation->contact_user_id; ?>"><?php echo $offsitelocation->firstname; ?> <?php echo $offsitelocation->lastname; ?></a><?php } else { ?>None<?php } ?></td>
							<td><?php echo $offsitelocation->location_title; ?></td>
							<td data-offsite_title="<?php echo $offsitelocation->address_line1; ?>"><?php echo $offsitelocation->address_line1; ?></td>
							<td data-offsite_desc="<?php echo $offsitelocation->city; ?>"><?php echo $offsitelocation->city; ?></td>
							<td data-offsite_value="<?php echo $offsitelocation->state_id; ?>" style="text-align: center;"><?php echo $offsitelocation->state_id; ?></td>
							<td class="text-center"><?php echo $offsitelocation->postal_code; ?></td>
							<td class="text-center"><?php echo CTemplate::dateTimeFormat($offsitelocation->timestamp_updated, CONCISE); ?></td>
							<td class="text-center"><span class="btn btn-primary" data-offsite_id_edit="<?php echo $id; ?>">Edit</span></td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<tr>
						<td class="font-weight-bold text-center">No Community Pick Up locations have been created</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

		<?php } ?>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>