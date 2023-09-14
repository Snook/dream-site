let bootDialog = [], icoMoon, icoMoonList = [];

$(function () {

	$(document).on('change', '#menu_id', function (e) {

		$('#availability_date_start').val(this.options[this.selectedIndex].dataset.date_start);
		$('#availability_date_start').prop({'min': this.options[this.selectedIndex].dataset.date_start});

		$('#availability_date_end').val(this.options[this.selectedIndex].dataset.date_end);

	});

	$(document).on('change', '.store-select-filter', function (e) {

		if (this.options[this.selectedIndex].value == '') {
			$('[data-box_store_id]').showFlex();
		}
		else
		{
			$('[data-box_store_id]').hideFlex();

			$('[data-box_store_id="' + this.options[this.selectedIndex].value + '"]').showFlex();
		}

	});

	$(document).on('click', '#BoxFormSubmit', function (e) {

		e.preventDefault();

		// if all three validate submit the main box form
		let validate_1 = $('#BoxForm').validateForm();
		let validate_2 = $('#Bundle1Form').validateForm();
		let validate_3 = $('#Bundle2Form').validateForm();

		if (validate_1 && validate_2 && validate_3)
		{
			if ($('#BoxForm input[name="id"]').val() == 'new')
			{
				$(this).prop({'disabled': true});

				$('#BoxForm').submit();
			}
			else
			{
				$('#BoxForm').submit();

				if (document.getElementById('box_bundle_1_active').checked)
				{
					$('#Bundle1Form').submit();
				}

				if (document.getElementById('box_bundle_2_active').checked)
				{
					$('#Bundle2Form').submit();
				}
			}
		}

	});

	$(document).on('click', '.box-deploy', function (e) {

		let box_id = this.dataset.box_id;

		bootDialog['deployDialog'] = bootbox.prompt({
			title: "Select stores to deploy to",
			inputType: 'checkbox',
			inputOptions: storeDeployOptions,
			callback: function (result) {

				if (result == null)
				{
					bootDialog['deployDialog'].modal('hide');
				}
				else if (result.length)
				{
					let formData = new FormData();
					formData.set('op', 'deploy_box');
					formData.set('stores', JSON.stringify(result));
					formData.set('box_id', box_id);

					fetch('ddproc.php?processor=admin_manage_box', {
						method: 'post',
						body: formData
					}).then(response => {
						if (!response.ok)
						{
							throw new Error("HTTP error " + response.status);
						}
						return response.json();
					}).then(json => {

						bounce('main.php?page=admin_manage_box');

					}).catch((error) => {
						console.error('Error:', error);
					});

				}

			}
		});

	});

	$(document).on('click', '.box-expire', function (e) {

		let box_id = this.dataset.box_id;

		bootbox.confirm("Do you wish to expire this box now?", function(result){
			if (result == true)
			{
				let formData = new FormData();
				formData.set('op', 'expire_box');
				formData.set('box_id', box_id);

				fetch('ddproc.php?processor=admin_manage_box', {
					method: 'post',
					body: formData
				}).then(response => {
					if (!response.ok)
					{
						throw new Error("HTTP error " + response.status);
					}
					return response.json();
				}).then(json => {

					bounce('main.php?page=admin_manage_box');

				}).catch((error) => {
					console.error('Error:', error);
				});

			}
		});

	});

	$(document).on('submit', '[data-processor]', function (e) {

		e.preventDefault();

		if (this.checkValidity() !== false)
		{
			let addInput = document.createElement('input');
			addInput.setAttribute('type', 'hidden');
			addInput.setAttribute('name', '_form_name');
			addInput.setAttribute('value', this.name);
			this.appendChild(addInput);

			fetch('ddproc.php?processor=' + this.dataset.processor, {
				method: ((this.dataset.processor_method.length > 0) ? this.dataset.processor_method : 'post'),
				body: new FormData(this)
			}).then(response => {
				if (!response.ok)
				{
					throw new Error("HTTP error " + response.status);
				}
				return response.json();
			}).then(json => {

				if (json.data.form_name == 'BoxForm' && json.data.bounce)
				{
					bounce('main.php?page=admin_manage_box&edit=' + json.data.box_id);
				}

				$('#BoxForm, #Bundle1Form, #Bundle2Form').removeClass('was-validated');

			}).catch((error) => {
				console.error('Error:', error);
			});
		}

	});

	$(document).on('change', '#box_bundle_1_active, #box_bundle_2_active', function (e) {

		let formData = new FormData();
		formData.set('op', 'set_bundle_active_state');
		formData.set('bundle_id', this.dataset.bundle_id);
		formData.set('box_id', this.dataset.box_id);
		formData.set('box_bundle', this.dataset.box_bundle);
		formData.set('checked', this.checked);

		fetch('ddproc.php?processor=admin_manage_box', {
			method: 'post',
			body: formData
		}).then(response => {
			if (!response.ok)
			{
				throw new Error("HTTP error " + response.status);
			}
			return response.json();
		}).then(json => {

			let formId, bundle;

			if (this.dataset.box_bundle == 'box_bundle_1')
			{
				formId = 'Bundle1Form';
				bundle = 'bundle_1';
			}
			else if (this.dataset.box_bundle == 'box_bundle_2')
			{
				formId = 'Bundle2Form';
				bundle = 'bundle_2';
			}

			if (!$(this).is(':checked'))
			{
				$('form#' + formId + ' :input').not(this).each(function () {
					$(this).prop({'disabled': true});
				});

				$('#' + bundle + '_menu_items').hideFlex();
			}
			else
			{
				$('form#' + formId + ' :input').not(this).each(function () {

					if($(this).data('has_orders') > 0)
					{
						return;
					}

					$(this).prop({'disabled': false});


				});

				$('#' + bundle + '_menu_items').showFlex();

				let orgState = this.dataset.bundle_id;

				if (orgState == 'new')
				{
					$('[id^="' + bundle + '_check_"]').attr('data-bundle_id', json.data.bundle_id);
					$('#box_' + bundle + '_active').attr('data-bundle_id', json.data.bundle_id);
					$('#' + formId + ' input[name="id"]').val(json.data.bundle_id);

					$('#' + formId).submit();
				}
			}

		}).catch((error) => {
			console.error('Error:', error);
		});

	});

	$(document).on('change', '[data-bundle_menu_item_id]', function (e) {

		let formData = new FormData();
		formData.set('op', 'update_bundle_menu_item');
		formData.set('bundle_id', this.dataset.bundle_id);
		formData.set('bundle_menu_item_id', this.dataset.bundle_menu_item_id);
		formData.set('checked', this.checked);

		fetch('ddproc.php?processor=admin_manage_box', {
			method: 'post',
			body: formData
		}).then(response => {
			if (!response.ok)
			{
				throw new Error("HTTP error " + response.status);
			}
			return response.json();
		}).then(json => {

			fetchToasts(json);

		}).catch((error) => {
			console.error('Error:', error);
		});

	});

	// read icoMoon.io json export file
	if ($('#css_icon').length)
	{
		fetch(PATH.css + "/customer/fonts/selection.json").then(response => {
			if (!response.ok)
			{
				throw new Error("HTTP error " + response.status);
			}
			return response.json();
		}).then(json => {

			// loop through the json data to define the css classes
			Object.keys(json.icons).forEach(function (key) {

				// some icons have multiple class names defined for the same icon, we only need one so split on comma and choose first one
				let icon_name = json.preferences.imagePref.prefix + json.icons[key].properties.name.split(',')[0];

				// put everything in a clean list of class names and sort them
				icoMoonList.push(icon_name);

			});

			// sort list alphabetically
			icoMoonList.sort();

			// get the page loaded value
			let css_icon_default = document.getElementById('css_icon').value;

			// clear the selection dropdown
			$('#css_icon').html('');

			// populate the select options with json data
			icoMoonList.forEach(function (icon_name) {

				$('#css_icon').append('<option value="' + icon_name + '">' + icon_name + '</option>');

			});

			// set the current value to what was loaded with page
			$('#css_icon').val(css_icon_default).trigger('change');

		}).catch((error) => {
			console.error('Error:', error);
		});
	}

	$(document).on('change', '#css_icon', function (e) {

		let css_icon = $(this).val();

		$('.css_icon_preview').tooltip('dispose').attr({
			'data-toggle': "tooltip",
			'data-placement': "top",
			'data-html': "true",
			'title': "<i class='dd-icon font-size-extra-extra-large " + css_icon + "'></i>"

		}).html('<i class="dd-icon ' + css_icon + '"></i>');

	});

	$(document).on('click', '.css_icon_preview', function (e) {

		if (icoMoonList.length > 0)
		{
			let message = '<div class="row mx-1">';

			$.each(icoMoonList, function (key, value) {

				message += '<a href="#" class="col p-2 m-1 mb-2 text-center text-decoration-hover-none' + (($('#css_icon').val() == value) ? ' text-white bg-orange border border-orange-dark' : ' text-body') + '" data-pick_icon_class="' + value + '" data-toggle="tooltip" data-placement="top" title="' + value + '"><i class="dd-icon ' + value + ' font-size-extra-large"></i></a>';

			});

			message += '</div>';

			iconDialog = bootbox.dialog({
				title: "Select Delivered Box Icon",
				message: message,
				size: 'large',
				scrollable: true,
				onEscape: true,
				closeButton: true,
				buttons: {
					cancel: {
						label: "Close",
						className: 'btn-primary'
					}
				}
			});
		}

	});

	$(document).on('click', '[data-pick_icon_class]', function (e) {

		e.preventDefault();

		let icon_class = $(this).data('pick_icon_class');

		$('#css_icon').val(icon_class).trigger('change');

		iconDialog.modal('hide');

	});

});