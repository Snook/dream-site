<?php $this->setScript('head', SCRIPT_PATH . '/admin/fundraiser.min.js'); ?>
<?php $this->setOnload('fundraiser_init();'); ?>
<?php $this->assign('page_title','Fundraising Manager'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col-4 offset-4 text-center ">
				<h1><a href="/?page=admin_fundraiser">Fundraising Manager</a></h1>
			</div>
			<div class="col-4 text-right">
			</div>
		</div>

		<?php if ($this->show['store_selector'] && !empty($this->form_session_list['store_html'])) { ?>
			<div id="store_selector" class="row mb-3">
				<div class="col">
					<form method="post">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Store</span>
							</div>
							<?php echo $this->form_session_list['store_html']; ?>
						</div>
					</form>
				</div>
			</div>
		<?php } ?>

		<div id="detailed_reporting" class="row collapse">
			<div class="col">

				<?php
				$HIDDENPAGENAME = "admin_fundraiser";
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
					<?php echo $this->form_session_list['hidden_html']; ?>

					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Select Fundraiser</span>
						</div>
						<?php echo $this->form_session_list['fundraiser_chooser_html']; ?>
					</div>

					<?php echo $this->form_session_list['report_submit_html']; ?>
				</div>
				</form>

				<hr />

			</div>

		</div>

		<div class="row my-2">
			<div class="col-8">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Fundraiser Page Link</span>
					</div>
					<input type="text" class="form-control" value="<?php echo HTTPS_BASE; ?>store/<?php echo $this->store->id; ?>-fundraisers">
				</div>
			</div>
			<div class="col-4 text-right">
				<span id="show_detailed_reporting" class="btn btn-primary">Detailed Reporting</span>
				<span id="add_fundraiser" class="btn btn-primary">Add Fundraiser</span>
			</div>
		</div>

		<div class="row">
			<div class="col">
				<table class="table table-striped table-bordered table-hover ddtemp-table-border-collapse bg-white">
					<thead>
					<tr>
						<th>Enable</th>
						<th>Created</th>
						<th>Fundraiser</th>
						<th>Description</th>
						<th>Donation</th>
						<th>Total Raised</th>
						<th>Total Orders</th>
						<th>Manage</th>
					</tr>
					</thead>
					<tbody id="fundraiser_list">
					<?php if (!empty($this->fundraiserArray)) { ?>
						<?php foreach ($this->fundraiserArray AS $id => $fundraiser) { ?>
							<tr data-fund_id="<?php echo $id; ?>">
								<td class="text-center"><input data-enable_fund="<?php echo $id; ?>" type="checkbox" <?php echo (!empty($fundraiser->active)) ? 'checked="checked"' : ''; ?> /></td>
								<td><?php echo CTemplate::dateTimeFormat($fundraiser->timestamp_created, CONCISE); ?></td>
								<td data-fund_title="<?php echo $fundraiser->fundraiser_name; ?>"><a href="?page=fundraiser&amp;id=<?php echo $this->store->id; ?>&amp;fid=<?php echo $fundraiser->id; ?>" target="_blank"><?php echo $fundraiser->fundraiser_name; ?></a></td>
								<td data-fund_desc="<?php echo $fundraiser->fundraiser_description; ?>"><?php echo $fundraiser->fundraiser_description; ?></td>
								<td data-fund_value="<?php echo $fundraiser->donation_value; ?>" style="text-align: right;">$<?php echo number_format($fundraiser->donation_value, 2); ?></td>
								<td class="text-right">$<?php echo number_format($fundraiser->fundraiser_total, 2); ?></td>
								<td class="text-right"><?php echo $fundraiser->total_orders; ?></td>
								<td class="text-center"><span class="button" data-fund_id_edit="<?php echo $id; ?>">Edit</span></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="8" class="text-center font-weight-bold">No fundraisers have been created</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>

			</div>
		</div>
	</div>

	<template id="add_fundraiser_content">
		<table class="w-100">
			<tr>
				<td></td>
				<td><input type="text" maxlength="255" id="new_fundraiser" name="new_fundraiser" placeholder="Fundraiser title" /></td>
			</tr>
			<tr>
				<td></td>
				<td><textarea name="new_fundraiser_desc" placeholder="Description" style="width:256px;height:100px;"></textarea></td>
			</tr>
			<tr>
				<td>$</td>
				<td><input name="new_fundraiser_value" type="number" min="0" placeholder="0.00" /> Default value</td>
			</tr>
			<tr>
				<td></td>
				<td><input name="fund_submit" type="submit" value="Add Fundraiser" class="button" /></td>
			</tr>
		</table>
	</template>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>