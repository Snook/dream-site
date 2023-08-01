<?php $this->assign('page_title', 'Update your Dream Dinners password'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main class="container">
		<div class="row my-5">
			<div class="col-lg-12 text-center">
				<h2>Update password</h2>
			</div>
		</div>
		<?php if ($this->errorMessage) { ?>
			<div class="row mb-4">
				<div class="col-md-6 mx-auto">
					<div class="alert alert-warning" role="alert">
						<?php echo $this->errorMessage; ?>
					</div>
				</div>
			</div>
			<div class="row mb-5">
				<div class="col text-center">
					<a href="/main.php?page=login" class="btn btn-primary">Log in</a>
				</div>
			</div>
		<?php } else { ?>
			<div class="row">
				<div class="col-md-3 mx-auto text-center align-self-center">

					<form method="post">
						<div class="form-group">
							<?php echo $this->form['password_html']?>
						</div>

						<div class="form-group">
							<?php echo $this->form['password_confirm_html']?>
						</div>

						<div class="form-group">
							<?php echo $this->form['update_password_html']?>
						</div>
					</form>

				</div>
			</div>
		<?php } ?>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>