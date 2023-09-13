(function () {
	'use strict';

	if (manageSingleStore)
	{
		$(document).on('keyup blur', '.notice-message, .notice-title', function (e) {

			if ($(this).val() != strip_tags($(this).val()))
			{
				$(this).val(strip_tags($(this).val()));
			}

		});
	}

	$(document).on('click', '.storeSelector-select-all', function (e) {

		$("#storeSelector input[type=checkbox]").prop('checked', true);

	});

	$(document).on('click', '.storeSelector-select-none', function (e) {

		$("#storeSelector input[type=checkbox]").prop('checked', false);

	});

	$(document).on('click', '.storeSelector-select-corporate', function (e) {

		$('#storeSelector [data-storeSelect_franchise_id="220"]').prop('checked', true);

	});

	$(document).on('click', '.storeSelector-select-not-corporate', function (e) {

		$('#storeSelector [data-storeSelect_franchise_id!="220"]').prop('checked', true);

	});

	$(document).on('click', '.notice-store-filter:not(.disabled)', function (e) {

		$('.notice-select-filter').val($(this).data('filter_store_id')).trigger('change');

	});


	$(document).on('change', '.notice-select-filter', function (e) {

		$('.notice-list').children().each(function () {
			$(this).hide();
		});

		$('[data-filter_store_id]').removeClass('disabled');

		if ($(this).val() == 'home_office_managed')
		{
			$('.notice-list').children('[data-home_office_managed="1"]').each(function () {
				$(this).show();
			});
		}
		else if ($(this).val() == 'home_office_managed_not')
		{
			$('.notice-list').children('[data-home_office_managed="0"]').each(function () {
				$(this).show();
			});
		}
		else if ($(this).val() != '')
		{
			var store = $(this).val();

			$('.notice-list').children().find('[name="store_id"]').each(function () {

				var store_ids = $(this).val().split(',');

				if (!$.inArray(store, store_ids))
				{
					$(this).parentsUntil('[data-notice_id_div]').parent().show();
					$(this).parentsUntil('[data-notice_id_div]').find('[data-filter_store_id]').addClass('disabled');
				}
			});
		}
		else
		{
			$('.notice-list').children().each(function () {
				$(this).show();
			});
		}

	});

	$(document).on('change', '.notice-alert_css', function (e) {

		var form = $(this).parents('form:first');
		var alert_css = $(this).val();

		$(this).parent().find("select option").each(function () {
			var option_css = $(this).val();
			$("[name='message']", form).removeClass(option_css);
		});

		$("[name='message']", form).addClass(alert_css);

	});

	$(document).on('change', '.notice-select-audience', function (e) {

		var form = $(this).parents('form:first');
		var notice_id = $("[name='id']", form).val();
		var notice_div = $('[data-notice_id_div="' + notice_id + '"]');

		if ($(this).val() === 'STORE')
		{
			$(notice_div).find('.div-notice-title').show();
			$("[name='title']", form).prop('required', true);

			$(notice_div).find('.div-notice-select-style').hide();
			$(notice_div).find('.div-notice-select-store').show();
			$("[name='alert_css']", form).prop('required', false).val('').trigger('change');
		}
		else
		{
			$(notice_div).find('.div-notice-select-style').show();
			$("[name='alert_css']", form).prop('required', true);

			$(notice_div).find('.div-notice-select-store').hide();
			$(notice_div).find('.div-notice-title').hide();
			$("[name='title']", form).prop('required', false);
		}

	});

	$(document).on('click', '.notice-create', function (e) {

		e.preventDefault();

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_manage_site_notice',
				op: 'get_notice_form'
			},
			success: function (json) {
				if (json.processor_success)
				{
					$('.notice-list').prepend(json.html);
					$('[data-notice_id_div="' + json.notice_id + '"]').slideDown();
				}
			},
			error: function (objAJAXRequest, strError) {
				strError = 'Unexpected error';
			}
		});

	});

	$(document).on('click', '.notice-store-select:not(.disabled)', function (e) {

		var this_button = $(this);
		var notice_form = $(this_button).parents('form:first');
		var store_id = $("[name='store_id']", notice_form).val();

		$('.notice-store-select').addClass('disabled');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_manage_site_notice',
				op: 'get_store_select',
				store_id: store_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					bootbox.confirm({
						message: json.html,
						scrollable: true,
						callback: function (result) {
							if (result)
							{
								var form = $('#storeSelector');
								var checked = form.serializeArray();

								var store_ids = [];
								$.each(checked, function (index, value) {
									store_ids.push(value.value);
								});

								$("[name='store_id']", notice_form).val(store_ids.join());

								$(this_button).find('.notice-store_id-count').text(checked.length);
							}

							$('.notice-store-select').removeClass('disabled');
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				strError = 'Unexpected error';
			}
		});

	});

	$(document).on('submit', '.notice-form', function (e) {

		var form = $(this);

		e.preventDefault();

		// prevent enter key from submitting form
		if ($(e.delegateTarget.activeElement).not('input, textarea').length == 0)
		{

			$(form).removeClass('was-validated');
			$(form).find('.btn-spinning').removeClass('btn-spinning');
			$(form).find('.ld-spin').remove();

			e.preventDefault();
			return false;
		}

		if (form['0'].checkValidity() !== false)
		{

			var result = {};
			$.each(form.serializeArray(), function () {
				result[this.name] = this.value;
			});

			var data = JSON.stringify(result);

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_manage_site_notice',
					op: 'save_site_notice',
					notice_details: data
				},
				success: function (json) {
					if (json.processor_success)
					{
						if (json.notice_id)
						{
							$("[name='id']", form).val(json.notice_id);
							$(form).parent().attr('data-notice_id_div', json.notice_id);
						}

						$(form).removeClass('was-validated');
						$(form).find('.btn-spinning').removeClass('btn-spinning');
						$(form).find('.ld-spin').remove();
					}
				},
				error: function (objAJAXRequest, strError) {
					strError = 'Unexpected error';
				}
			});
		}

	});

	$(document).on('click', '.notice-delete', function (e) {

		e.preventDefault();

		var form = $(this).parents('form:first');
		var notice_id = $("[name='id']", form).val();

		var confirm_message = '<p>Are you sure you wish to delete this notice?</p>';

		if ($("[name='message']", form).val())
		{
			confirm_message += '<p class="' + (($("[name='alert_css']", form).val()) ? $("[name='alert_css']", form).val() : 'alert') + '">' + $("[name='message']", form).val() + '</p>';
		}

		bootbox.confirm({
			message: confirm_message,
			callback: function (result) {
				if (result)
				{
					// not yet saved to db, just delete from array
					if (notice_id.startsWith('new-'))
					{
						$('[data-notice_id_div="' + notice_id + '"]').slideUp("normal", function () {
							$(this).remove();
						});
					}
					else
					{
						$.ajax({
							url: '/processor',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'admin_manage_site_notice',
								op: 'delete_site_notice',
								notice_id: notice_id
							},
							success: function (json) {
								if (json.processor_success)
								{
									$('[data-notice_id_div="' + notice_id + '"]').slideUp("normal", function () {
										$(this).remove();
									});

									$('.btn-spinning').removeClass('btn-spinning');
									$('.ld-spin').remove();
								}
							},
							error: function (objAJAXRequest, strError) {
								strError = 'Unexpected error';
							}
						});
					}
				}
			}
		});

	});

	/*
	const NoticeStores = Vue.component('site-notice-stores', {
		template: "#storeCheckList",
		props: ['notice','noticeIndex'],
		data: function () {
			return {
				stores: storeList_js
			}
		},
		methods: {
			selectFranchise: function () {

				debugger;

			},
			onChange: function(e) {

				debugger;

				this.$emit('input', this.checkedProxy)
			}
		}
	});

	const Notices = Vue.component('site-notice', {
		template: "#template-vuenotice",
		data: function () {
			return {
				notices: maintenance_js
			}
		},
		methods: {
			numStores: function (notice) {
				if (typeof notice.store_id != 'undefined' && notice.store_id.length)
				{
					return notice.store_id.split(',').length;
				}
				else
				{
					return 0;
				}
			},
			updateMessageStart: function (notice) {

				notice.message_start = notice.message_start_date + ' ' + notice.message_start_time;

			},
			updateMessageEnd: function (notice) {

			},
			selectStores: function (notice) {

				bootbox.dialog({
					message: $('#noticeStores-' + notice.id).html(),
					scrollable: true,
					callback: function (result) {
						if (result)
						{
							debugger;
						}
					},
					buttons: {
						cancel: {
							label: "Cancel",
							className: 'btn-primary',
							callback: function (result) {
								if (result)
								{

								}
							}
						},
						ok: {
							label: "Ok",
							className: 'btn-primary',
							callback: function (result) {
								if (result)
								{

								}
							}
						}
					}
				});

			},
			createNotice: function () {
				this.notices.unshift({
					id: "new",
					message_start: false,
					message_end: false
				});
			},
			deleteNotice: function (notice) {

				var vueNotices = this.notices;
				var notice_id = notice.id;
				var confirm_message = '<p>Are you sure you wish to delete this notice?</p>';

				if (typeof notice.message != 'undefined')
				{
					confirm_message += '<p class="' + ((typeof notice.alert_css != 'undefined' && notice.alert_css.length) ? notice.alert_css : 'alert') + '">' + notice.message + '</p>';
				}

				bootbox.confirm({
					message: confirm_message,
					callback: function (result) {
						if (result)
						{
							// not yet saved to db, just delete from array
							if (notice_id === 'new')
							{
								vueNotices.splice(vueNotices.indexOf(notice), 1);
							}
							else
							{
								$.ajax({
									url: '/processor',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'admin_manage_site_notice',
										op: 'delete_site_notice',
										notice_id: notice_id
									},
									success: function (json) {
										if (json.processor_success)
										{
											vueNotices.splice(vueNotices.indexOf(notice), 1);

											$('.btn-spinning').removeClass('btn-spinning');
											$('.ld-spin').remove();
										}
									},
									error: function (objAJAXRequest, strError) {
										strError = 'Unexpected error';
									}
								});
							}
						}
					}
				});

			},
			saveNotice: function (notice) {

				var vueNotices = this.notices;
				var notice_details = JSON.stringify(notice);

				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_manage_site_notice',
						op: 'save_site_notice',
						notice_details: notice_details
					},
					success: function (json) {
						if (json.processor_success)
						{
							$('.was-validated').removeClass('was-validated');
							$('.btn-spinning').removeClass('btn-spinning');
							$('.ld-spin').remove();
						}
					},
					error: function (objAJAXRequest, strError) {
						strError = 'Unexpected error';
					}
				});
			}
		},
		filters: {
			dateFormat: function (value) {
				if (value)
				{
					return moment(String(value)).format('YYYY-MM-DD')
				}
			},
			timeFormat: function (value) {
				if (value)
				{
					return moment(String(value)).format('HH:mm');
				}
			}
		}
	});

	var vuenotices = new Vue({
		el: '#vuenotices'
	});
	*/
})();