<div id="ajax_login">

	<fieldset id="loginBody">
		<fieldset>
			<label for="fadmin_primary_email_login" class="login_label">Email Address</label>
			<input type="text" id="fadmin_primary_email_login" name="fadmin_primary_email_login" class="login_input" value="<?php echo $this->primary_email; ?>" />
		</fieldset>
		<fieldset>
			<label for="fadmin_password_login" class="login_label">Password</label>
			<input type="password" id="fadmin_password_login" name="fadmin_password_login" class="login_input" /> <img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" alt="Processing" />
		</fieldset>
	</fieldset>

	<span id="error_message"></span>

</div>
