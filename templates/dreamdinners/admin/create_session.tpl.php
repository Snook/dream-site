<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Create Session</h1>
			</div>
		</div>

		<form id="session_edit" name="session_edit" autocomplete="off" action="" method="post" class="needs-validation" novalidate>

			<?php include $this->loadTemplate('admin/subtemplate/session/session_create_edit.tpl.php'); ?>

			<div class="form-row mt-4">
				<div class="form-group col-md-6">
					<div class="input-group">
						<?php echo $this->form_create_session['session_publish_state_html']; ?>
						<div class="input-group-append">
							<?php echo $this->form_create_session['session_submit_html']; ?>
						</div>
					</div>
				</div>
			</div>

		</form>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>