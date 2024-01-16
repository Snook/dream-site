<div class="ml-4">
	<div class="custom-control custom-switch">
		<input
				data-sms_pref="false"
				class="custom-control-input"
				id="text_message_opt_in"
				data-user_pref="text_message_opt_in"
				data-user_pref_value_check="OPTED_IN"
				data-user_pref_value_uncheck="OPTED_OUT"
				type="checkbox"
				<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_OPT_IN]['value'] == 'OPTED_IN') {?>checked="checked"<?php } ?> />
		<label class="custom-control-label" for="text_message_opt_in">Opt-in to receive text messages from the store.</label>
	</div>
</div>