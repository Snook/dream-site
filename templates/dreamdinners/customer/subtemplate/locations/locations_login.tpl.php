<div class="row">
	<div class="col">

		<form id="locations_login_form" method="POST">
			<input type="hidden" name="back" value="/session-menu" />
			<div class="row">
				<div class="col-lg-6 pr-lg-1">
					<div class="form-group has-danger">
						<div class="input-group">
							<?php echo $this->form_login['primary_email_login_html']; ?>
						</div>
					</div>
				</div>
				<div class="col-lg-6 pl-lg-1">
					<div class="form-group">
						<div class="input-group">
							<?php echo $this->form_login['password_login_html']; ?>
						</div>
					</div>
				</div>
			</div>
			<?php if (false) { ?>
			<div class="row">
				<div class="col">
					<div class="custom-control custom-control-inline custom-checkbox">
						<?php echo $this->form_login['remember_login_html']; ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="row pt-1">
				<div class="col-lg-6 pr-lg-1">
					<?php echo $this->form_login['submit_login_html']; ?>
				</div>
			</div>
			<div class="row pt-1">
				<div class="col">
					<a class="btn btn-link" data-toggle="collapse" href="#" data-target="#forgotPassDiv">Forgot Password?</a>
				</div>
			</div>
		</form>

		<div id="forgotPassDiv" class="row my-4 collapse">
			<form method="POST">
				<div class="col-lg-12 text-center">
					<h2 class="meal-title">Forgot password</h2>
				</div>
				<div class="col">
					<div class="row">
						<div class="col">
							<p>Please enter your primary email address below and an email will be sent to you containing a link that will allow you to reset your password.</p>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<div class="input-group">
									<input class="form-control" type="text" name="forgot_primary_email" placeholder="*Email" required="required" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<input class="btn btn-primary btn-block" type="submit" name="forgotPassword" value="Request Password Reset" />
						</div>
					</div>
				</div>
			</form>
		</div>

	</div>
</div>