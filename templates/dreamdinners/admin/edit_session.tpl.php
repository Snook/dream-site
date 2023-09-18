<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Edit Session</h1>
			</div>
		</div>

		<form id="session_edit" name="session_edit" autocomplete="off" action="" method="post" class="needs-validation" novalidate>

			<?php include $this->loadTemplate('admin/subtemplate/session/session_create_edit.tpl.php'); ?>

			<div class="row my-4">
				<div class="col-md-4 text-center">
					<?php echo $this->form_create_session['session_submit_html']; ?>
				</div>
				<div class="col-md-4 text-center">
					<?php echo $this->form_create_session['open_close_submit_html']; ?>
				</div>
				<div class="col-md-4 text-center">
					<?php echo $this->form_create_session['submit_delete_html']; ?>
					<?php if (!$this->canDelete) { ?>
						<div>Note: Sessions that have active orders cannot be deleted and the date and time cannot be edited. You must first reschedule or cancel all orders placed against the <a href="/?page=admin_main&amp;session=<?php echo $this->form_create_session['id']; ?>">session</a>.</div>
					<?php } ?>
				</div>
			</div>

		</form>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>