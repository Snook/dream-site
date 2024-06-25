<?php $this->setScript('foot', SCRIPT_PATH . '/admin/reports_guest.min.js'); ?>
<?php $this->assign('page_title','Guest Reports'); ?>
<?php $this->assign('topnav','guests'); ?>

<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1><a href="/backoffice/reports-guest">Guest Reports</a></h1>
			</div>
		</div>

		<form method="post" action="/backoffice/reports-guest">
			<?php echo $this->form['hidden_html'];  ?>

			<div class="form-row">
				<div class="form-group col-12">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Report</span>
						</div>
						<?php echo $this->form['guest_report_html']; ?>
						<div class="input-group-append collapse report-option option-multi-store-select">
							<?php echo $this->form['multi_store_select_html']; ?>
						</div>
					</div>
				</div>
				<div class="form-group col-12 report-description font-size-medium-small"></div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6 collapse report-option option-month-start">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Month</span>
						</div>
						<?php echo $this->form['month_start_html']; ?>
					</div>
				</div>
				<div class="form-group col-md-6 collapse report-option option-month-end">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Month End</span>
						</div>
						<?php echo $this->form['month_end_html']; ?>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6 collapse report-option option-date-start">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Date</span>
						</div>
						<?php echo $this->form['date_start_html']; ?>
					</div>
				</div>
				<div class="form-group col-md-6 collapse report-option option-date-end">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Date End</span>
						</div>
						<?php echo $this->form['date_end_html']; ?>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6 collapse report-option option-datetime-start">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Date Time</span>
						</div>
						<?php echo $this->form['datetime_start_html']; ?>
					</div>
				</div>
				<div class="form-group col-md-6 collapse report-option option-datetime-end">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Date Time End</span>
						</div>
						<?php echo $this->form['datetime_end_html']; ?>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="form-group col-md-6 collapse report-option-group">
					<p>Guest status</p>
					<ul class="list-unstyled">
						<li class="collapse report-option option-query-with-sessions"><?php echo $this->form['query_with_sessions_html']; ?></li>
						<li class="collapse report-option option-query-without-sessions"><?php echo $this->form['query_without_sessions_html']; ?></li>
					</ul>
				</div>
				<div class="form-group col-md-6 collapse report-option-group">
					<p>Report details</p>
					<ul class="list-unstyled">
						<li class="collapse report-option option-filter-guest-info"><?php echo $this->form['filter_guest_info_html']; ?></li>
						<li class="collapse report-option option-filter-guest-orders"><?php echo $this->form['filter_guest_orders_html']; ?></li>
						<li class="collapse report-option option-filter-guest-loyalty"><?php echo $this->form['filter_guest_loyalty_html']; ?></li>
						<li class="collapse report-option option-filter-guest-additional-info"><?php echo $this->form['filter_guest_additional_info_html']; ?></li>
					</ul>
				</div>
			</div>

			<div class="form-row collapse report-submit">
				<div class="form-group col">
					<?php echo $this->form['report_submit_html']; ?>
				</div>
			</div>

		</form>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>