<form id="shared_login_form" method="POST" class="needs-validation" novalidate>

	<div class="row">
		<div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
			<div class="form-group has-danger">
				<label class="sr-only" for="email">E-Mail Address</label>

				<div class="input-group mb-2 mr-sm-2 mb-sm-0">
					<?php echo $this->form_login['primary_email_login_html']; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
			<div class="form-group">
				<label class="sr-only" for="password">Password</label>

				<div class="input-group mb-2 mr-sm-2 mb-sm-0">
					<?php echo $this->form_login['password_login_html']; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
			<div class="custom-control custom-control-inline custom-checkbox">
				<?php echo $this->form_login['remember_login_html']; ?>
			</div>
		</div>
	</div>
	<div class="row pt-1">
		<div class="col-lg-4 col-md-6 mx-auto">
			<?php echo $this->form_login['submit_login_html']; ?>
		</div>
	</div>

</form>

<div class="row pt-3">
	<div class="col-lg-4 col-md-6 mx-auto">
		<button class="btn btn-gray btn-sm" data-toggle="collapse" data-target="#forgotPassDiv">Forgot Password?</button>
	</div>
</div>

<div id="forgotPassDiv" class="row my-4 collapse">
	<form method="post" class="needs-validation" novalidate>
		<div class="col-lg-12 text-center">
			<h2 class="meal-title">Forgot password</h2>
		</div>
		<div class="col-lg-12 text-center align-self-center">
			<div class="row">
				<div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
					<p>Please enter your primary email address below and an email will be sent to you containing a link that will allow you to reset your password.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
					<div class="form-group has-danger">
						<label class="sr-only" for="email">E-Mail Address</label>
						<div class="input-group mb-2 mr-sm-2 mb-sm-0">
							<input class="form-control" type="text" name="forgot_primary_email" placeholder="*Email" required="required" />
							<div class="invalid-feedback">Please enter your email address.</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-md-6 mx-auto">
					<input class="btn btn-primary btn-spinner btn-block" type="submit" name="forgotPassword" value="Request Password Reset" />
				</div>
			</div>
		</div>
	</form>
</div>