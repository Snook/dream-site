<?php $this->setScript('head', SCRIPT_PATH . '/admin/preferred.min.js'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->assign('page_title','Preferred Customer'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
	<style>label[for=include_sides] {padding-top: .25rem;}</style>

	<div class="container-fluid">

		<?php if ($this->customer_id) { ?>
			<div class="row mb-2">
				<div class="col">
					<table width="100%" border="0">
						<tbody>
						<tr>
							<td width="160">
								<input style="width:150px;" type="button" value="Back" class="button" onClick="window.location = '<?=$this->back?>';" />
							</td>
							<td colspan="5">
								You are viewing preferred status for <b><a href="/?page=admin_user_details&amp;id=<?php echo $this->user->id; ?>"><?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></a></b>.
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		<?php } ?>

		<?php if (!empty($this->form_preferred['store_html'])) { ?>
			<div class="row mb-2">
				<div class="col">
					<form id="store_form" action="" method="post">
						<input type="hidden" name="id" value="<?= $this->user->id ?>">
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">Store</div>
							</div>
							<?php echo $this->form_preferred['store_html']; ?>
						</div>
					</form>
				</div>
			</div>
		<?php } ?>

		<form name="preferred" action="" method="post" onSubmit="return _check_form(this);" >

			<table class="table table-striped">
				<?php if (isset($this->rows) && $this->rows) { ?>
					<thead>
					<tr>
						<th class="bgcolor_dark catagory_row">Name</th>
						<th class="bgcolor_dark catagory_row">Store</th>
						<th class="bgcolor_dark catagory_row">Type</th>
						<th class="bgcolor_dark catagory_row">Value</th>
						<th class="bgcolor_dark catagory_row">Start Date</th>
						<th class="bgcolor_dark catagory_row">Cap Per Menu</th>
						<th class="bgcolor_dark catagory_row">Include S&S</th>
						<th class="bgcolor_dark catagory_row">All stores</th>
						<th class="bgcolor_dark catagory_row">Set By</th>
						<th class="bgcolor_dark catagory_row">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->rows as $id => $row) { ?>
						<tr>
							<td><a href="/?page=admin_user_details&amp;id=<?php echo $row['user_id']; ?>"><?= $row['firstname']?> <?=$row['lastname']?></a></td>
							<td><?= $row['store_name']?></td>
							<td><?= $row['preferred_type']?></td>
							<td><?= $row['preferred_value']?></td>
							<td><?= CTemplate::dateTimeFormat($row['user_preferred_start'], NORMAL, $row['store_id'], CONCISE)?></td>
							<td class="text-center"  style="text-transform: capitalize;"><?= $row['preferred_cap_value'] > 0 ? $row['preferred_cap_value'] : ''?> <?= strtolower($row['preferred_cap_type'])?> </td>
							<td class="text-center"><?= $row['include_sides'] ? 'Yes' : 'No'?></td>
							<td class="text-center"><?= ($row['all_stores'] ? 'Yes' : "No")?></td>
							<td><a href="/?page=admin_user_details&amp;id=<?php echo $row['created_by']; ?>"><?php echo $row['added_by_firstname']; ?> <?php echo $row['added_by_lastname']; ?></a></td>
							<td><button class="button" onclick="submitDeleteRequest(<?=$id?>)">Delete</button></td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<thead>
					<tr>
						<td class="bgcolor_dark catagory_row">Status</td>
					</tr>
					</thead>
					<tbody>

						<td>No preferred status for this store.</td>
					</tr>

				<?php } ?>

				<?php if (isset($this->ups_at_other_stores) && $this->ups_at_other_stores) { ?>
					<tr> <td colspan="10">Guest has Preferred Status at these other stores:
							<?php $count = 0; foreach ($this->ups_at_other_stores as $row) {
								if($count > 0){
									echo ' | ';
								}
								echo trim($row['store_name']) . ', ' . $row['state_id'];
								if($row['records']> 1){
									?> <span data-tooltip="<?php echo $row['records'];?> active for this store">(<?php echo $row['records'];?>)</span>
								<?php }
								$count++;
							} ?>
						</td><tr>
				<?php } ?>
				</tbody>
			</table>

			<?php if ($this->customer_id) { ?>

				<?php if ($this->user->membershipStatusCurrent()) { ?>

					<h4 class="text-center">Guest not eligible for Preferred Status while active in the Meal Prep+ program.</h4>

				<?php } else { ?>

					<br />

					<form name="preferred" action="" method="post" onSubmit="return _check_form(this);" >
						<?= $this->form_preferred['hidden_html'];?>
						<input type="hidden" name="action" id="paction" value="create">
						<input type="hidden" name="upid" id="upid" value="">
						<table class="table table-sm" style="border-collapse: collapse">
							<thead>
							<tr>
								<td class="bgcolor_dark catagory_row" colspan="2">Set Preferred Status</td>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td class="bgcolor_light w-50" style="border: 0px solid #dee2e6 !important;">
									<div class="form-row">
										<div class="form-group col-md-12">
											<label class="font-weight-bold" for="preferred_type">Preferred Type</label>
											<?= $this->form_preferred['preferred_type_html'] ?>
										</div>
									</div>
								</td>
								<td class="bgcolor_light" style="border: 0px solid #dee2e6 !important;">
									<div id="preferred_flat_div" <?php echo ( $this->form_preferred['preferred_type'] == CUserPreferred::FLAT || !$this->form_preferred['preferred_type']) ? '' : 'class="collapse"'; ?>>
										<div class="form-row">
											<div class="form-group col-md-12">
												<label class="font-weight-bold" for="preferred_flat">Discount Amount</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<div class="input-group-text">$</div>
													</div>
													<?php echo $this->form_preferred['preferred_flat_html']; ?>
												</div>
												* Please format price as $00.00<br />A Flat amount is the price that a preferred customer will pay for each large entree (excluding any store mark up). Medium entrees will priced at half of this value.
											</div>
										</div>
									</div>
									<div id="preferred_percent_div" <?php echo ( $this->form_preferred['preferred_type'] == CUserPreferred::PERCENTAGE ) ? '' : 'class="collapse"'; ?>>
										<div class="form-row">
											<div class="form-group col-md-12">
												<label class="font-weight-bold" for="preferred_percent">Discount Amount</label>
												<div class="input-group">
													<?php echo $this->form_preferred['preferred_percent_html']; ?>
													<div class="input-group-append">
														<div class="input-group-text">%</div>
													</div>
												</div>
												* Please enter a percent value
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="bgcolor_light w-50" style="border: 0px solid #dee2e6 !important;">
									<div class="form-row">
										<div class="form-group col-md-12">
											<label class="font-weight-bold" for="preferred_type">Preferred Cap Type</label>
											<?= $this->form_preferred['preferred_cap_type_html'] ?>
										</div>
									</div>
								</td>
								<td class="bgcolor_light" style="border: 0px solid #dee2e6 !important;">
									<div id="preferred_cap_none_div" <?php echo ( $this->form_preferred['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_NONE || !$this->form_preferred['preferred_cap_type']) ? '' : 'class="collapse"'; ?>>
										<div class="form-row">
											<div class="form-group col-md-12">
												<div class="input-group">
												</div>
											</div>
										</div>
									</div>
									<div id="preferred_cap_servings_div" <?php echo ( $this->form_preferred['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_SERVINGS ) ? '' : 'class="collapse"'; ?>>
										<div class="form-row">
											<div class="form-group col-md-12">
												<label class="font-weight-bold" for="preferred_cap_servings">Servings Cap</label>
												<div class="input-group">
													<?php echo $this->form_preferred['preferred_cap_servings_html']; ?>
												</div>
												&nbsp;This is the number of core menu item <b>servings</b> allowed per menu month
											</div>

										</div>
									</div>
									<div id="preferred_cap_items_div" <?php echo ( $this->form_preferred['preferred_cap_type'] == CUserPreferred::PREFERRED_CAP_ITEMS ) ? '' : 'class="collapse"'; ?>>
										<div class="form-row">
											<div class="form-group col-md-12">
												<label class="font-weight-bold" for="preferred_cap_items">Items Cap</label>
												<div class="input-group">
													<?php echo $this->form_preferred['preferred_cap_items_html']; ?>
												</div>
												&nbsp;This is the number of core menu <b>items</b> allowed per menu month
											</div>

										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="bgcolor_dark catagory_row" colspan="2">Include Sides and Sweets</td>
							</tr>
							<tr>
								<td class="bgcolor_light" colspan="2" style="border: 0px solid #dee2e6 !important;">
									<div id="preferred_include_sides_div">
										<div class="form-row">
											<div class="form-group col-md-12">
												<label class="font-weight-bold"></label>
												<?= $this->form_preferred['include_sides_html'] ?><br>The Preferred Cap does not apply to Sides &amp; Sweets. Including Sides &amp; Sweets is only available to Percent discount.
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="bgcolor_light" colspan="2" style="text-align:right;border: 0px solid #dee2e6 !important;"><input type="submit" id="submit_preferred" name="submit_preferred" value="Save" class="button" /></td>
							</tr>
							</tbody>
						</table>
					</form>
				<?php } ?>
			<?php } ?>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>