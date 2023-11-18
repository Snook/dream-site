<p class="font-size-small">Select your Dream Dinners SMS Preferences</p>

<div class="ml-4">
	<div class="custom-control custom-switch">
		<input
				data-sms_pref="true"
			<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] == 'UNANSWERED') { echo "disabled='disabled'" ;} ?>
				class="custom-control-input"
				id="text_message_reminder_session_primary"
				data-user_pref="text_message_reminder_session_primary"
				data-user_pref_value_check="OPTED_IN"
				data-user_pref_value_uncheck="OPTED_OUT"
				type="checkbox"
				<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_REMINDER_SESSION_PRIMARY]['value'] == 'OPTED_IN' || CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_REMINDER_SESSION_PRIMARY]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
		<label class="custom-control-label" for="text_message_reminder_session_primary"><span class="font-weight-bold">Orders & Sessions:</span></label> <span class="font-size-small">Receive session reminder texts and order-related notifications</span>
	</div>
</div>


<div class="ml-4">
	<div class="custom-control custom-switch">
		<input
				data-sms_pref="true"
			<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] == 'UNANSWERED') { echo "disabled='disabled'" ;} ?>
				class="custom-control-input"
				id="text_message_promo_primary"
				data-user_pref="text_message_promo_primary"
				data-user_pref_value_check="OPTED_IN"
				data-user_pref_value_uncheck="OPTED_OUT"
				type="checkbox"
				<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_PROMO_PRIMARY]['value'] == 'OPTED_IN' || CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_PROMO_PRIMARY]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
		<label class="custom-control-label" for="text_message_promo_primary"><span class="font-weight-bold">Promotions & Announcements:</span></label> <span class="font-size-small">Get notified about promotions, offers and announcements</span>
	</div>
</div>

<div class="ml-4">
	<div class="custom-control custom-switch">
		<input
				data-sms_pref="true"
			<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'] == 'UNANSWERED') { echo "disabled='disabled'" ;} ?>
				class="custom-control-input"
				id="text_message_thaw_primary"
				data-user_pref="text_message_thaw_primary"
				data-user_pref_value_check="OPTED_IN"
				data-user_pref_value_uncheck="OPTED_OUT"
				type="checkbox"
				<?php if (CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_THAW_PRIMARY]['value'] == 'OPTED_IN' || CUser::getCurrentUser()->preferences[CUser::TEXT_MESSAGE_THAW_PRIMARY]['value'] == 'PENDING_OPT_IN') {?>checked="checked"<?php } ?> />
		<label class="custom-control-label" for="text_message_thaw_primary"><span class="font-weight-bold">Thaw reminders:</span></label> <span class="font-size-small">Receive a text reminding you to thaw your meals every Sunday</span>
	</div>
</div>

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