<?php $this->setScript('foot', SCRIPT_PATH . '/admin/reports_guest_marketing.min.js'); ?>
<?php $this->assign('page_title','Guest Marketing'); ?>
<?php $this->assign('topnav','guests'); ?>

<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1><a href="/backoffice/reports-guest-marketing">Guest Marketing Reports</a></h1>
			</div>
		</div>

		<form method="post" action="/backoffice/reports-guest-marketing">
			<?php echo $this->form['hidden_html'];  ?>

			<div class="form-row">
				<div class="form-group col-12">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Report</span>
						</div>
						<?php echo $this->form['marketing_report_html']; ?>
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
						<?php echo $this->form['datetime_start_html']; ?>
					</div>
				</div>

				<div class="form-group col-md-6 collapse report-option option-date-end">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Date End</span>
						</div>
						<?php echo $this->form['datetime_end_html']; ?>
					</div>
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