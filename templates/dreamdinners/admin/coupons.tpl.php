<?php $this->setScript('head', SCRIPT_PATH . '/admin/coupons.min.js'); ?>
<?php $this->assign('page_title', 'Coupon Codes'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php $this->setOnLoad("admin_coupons_init();"); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<form name="coupon_optout_form" id="coupon_optout_form" method="post" onsubmit="return confirm_and_check_form(this);">
		<input type="hidden" name="action" id="action" value="none" />
		<input type="hidden" name="optouts" id="optouts" value="" />
		<input type="hidden" name="optins" id="optins" value="" />

		<table style="width: 100%;">
			<tr align="right">
				<td>
					<?php if (!$this->read_only) { ?>
						<input name="submit_changes" id="submit_changes" type="submit" class="btn btn-primary btn-sm" value="Finalize All Changes" />
						<input type="button" class="btn btn-primary btn-sm" value="Reset to Current" onclick="resetPage('<?php echo $_SERVER['REQUEST_URI']; ?>');" />
					<?php } ?>
				</td>
			</tr>
		</table>

		<table class="ME_menu_editor_table" style="width: 100%;">
			<!-- Header area -->

			<?php if (isset($this->form['store_html'])) { ?>
				<!-- Store Row : for site admin only -->
				<tr class="form_subtitle_cell">
					<td align="center" colspan="2" style="padding: 5px;">
						<b>Selected Store:</b>&nbsp;<?php echo $this->form['store_html']; ?>
					</td>
				</tr>
			<?php } ?>

			<?php $midchecked = $this->master_exclusion ? '' : 'checked="checked"'; ?>
			<tr class="form_subtitle_cell">
				<td colspan="2">
					<table style="width: 100%;">
						<tr>
							<td colspan="2" align="left" style="padding-left: 10px;">
								<?php if (!$this->read_only) { ?>
									<input onclick="calculatePage(this);" data-orgval="<?php echo($this->master_exclusion ? "UNCHECKED" : "CHECKED"); ?>" id="mid" name="mid" type="checkbox" <?php echo $midchecked; ?> />
									<b>Allow coupon codes at this store?</b>&nbsp;(Check this box for yes)
								<?php } ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="form_subtitle_cell">
				<td colspan="2">
					<table style="width: 100%;">
						<tr>
							<td align="left" style="padding-left: 10px;"><b>Filter:</b>&nbsp;<?php echo $this->form['filter_html']; ?></td>
							<td align="right" style="padding-right: 10px;"><span id="saved_message" style="color:red; font-weight:bold; display:none">Your changes have not yet been saved.</span></td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- data display area -->
			<tr>
				<td colspan="2">
					<table id="itemsTbl" class="table table-striped ddtemp-table-border-collapse mb-0">
						<thead>
						<tr class="text-white-space-nowrap">
							<th>Opt in</th>
							<th>Coupon title</th>
							<th>Coupon code</th>
							<th>Value Price</th>
							<th>Expires</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ($this->coupon_array as $program => $subArray)
						{
							foreach ($subArray as $key => $coupon_data)
							{
								if (!is_array($coupon_data))
								{
									if ($key == 'name')
									{
										$progchecked = ((!$subArray['excluded']) ? "CHECKED" : "");
										?>
										<tr class="bg-green">
											<td class="text-center">
												<?php if (!$this->read_only) { ?>
													<input onclick="calculatePage(this);" data-orgval="<?php echo (!$subArray['excluded']) ? "CHECKED" : "UNCHECKED"; ?>" id="pid_<?php echo $program; ?>" name="pid_<?php echo $program; ?>" type="checkbox" <?php echo $progchecked; ?> />
												<?php } ?>
											</td>
											<td colspan="4">Coupon Program: <b><?php echo $coupon_data; ?></b></td>
										</tr>
										<?php
									}
								}
								else
								{
									$checked = !$coupon_data['excluded'] ? "CHECKED" : "";
									?>
									<tr id="row_<?php echo $program; ?>_<?php echo $key; ?>" style="color:<?php echo $progchecked == "CHECKED" ? '#000000' : '#808080'; ?>;">

										<td class="text-center">

											<?php if (!$this->read_only) { ?>
												<input onclick="calculatePage(this);" data-orgval="<?php echo((!$coupon_data['excluded']) ? "CHECKED" : "UNCHECKED"); ?>" id="cid_<?php echo $key; ?>" data-program_id="<?php echo $program; ?>" name="cid_<?php echo $key; ?>" type="checkbox" <?php echo $checked; ?> <?php echo $progchecked == "CHECKED" ? '' : 'disabled' ?> />
											<?php } ?>
										</td>

										<td>
											<?php echo $coupon_data['coupon_title']; ?>

											<?php if (!empty($coupon_data['coupon_code_description'])) { ?>
												<div class="font-size-small text-orange"><?php echo $coupon_data['coupon_code_description']; ?></div>
											<?php } ?>
										</td>

										<td><?php echo $coupon_data['coupon_code']; ?></td>
										<td class="text-right"><?php echo(!empty($coupon_data['coupon_value']) ? $coupon_data['coupon_value'] : "-"); ?></td>
										<td class="text-right text-white-space-nowrap"><?php echo CTemplate::dateTimeFormat($coupon_data['valid_timespan_end'], MONTH_DAY_YEAR); ?></td>
									</tr>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						</tbody>
					</table>
				</td>
			</tr>
		</table>

		<table style="width: 100%;">
			<tr align="right">
				<td>
					<?php if (!$this->read_only) { ?>
						<input name="submit_changes" id="submit_changes_2" type="submit" class="btn btn-primary btn-sm" value="Finalize All Changes" />
						<input type="button" class="btn btn-primary btn-sm" value="Reset to Current" onclick="resetPage('<?php echo $_SERVER['REQUEST_URI']; ?>');" />
						<span id="saved_message_2" style="color:red; font-weight:bold; display:none">Your changes have not yet been saved.</span>
					<?php } ?>
				</td>
			</tr>
		</table>

	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>