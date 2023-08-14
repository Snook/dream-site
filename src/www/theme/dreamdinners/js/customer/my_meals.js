function doFoodSearch()
{
	bounce('main.php?page=my_meals&search=' + $('#my_meals_search').val());
}

function setRatingStar(element_id, star)
{
	// Set all to grey
	for (var i = 0; i <= 5; i++)
	{
		$('#rating-' + i + '-' + element_id).removeClass('fas text-yellow').addClass('far');
	}

	// Make gold star up to star
	for (var i = 0; i <= star; i++)
	{
		$('#rating-' + i + '-' + element_id).addClass('fas text-yellow').removeClass('far');
	}
}

function mouseenterRatingStar(element)
{
	hover_rating = $(element).attr('data-rating');
	element_id = $(element).attr('data-element_id');

	setRatingStar(element_id, hover_rating);
}

function mouseleaveRatingStar(element)
{
	let org_val = $('#org_rating-' + element_id).val();

	setRatingStar(element_id, org_val);
}

function restorePagingLocation(currentUser)
{
	let page = localStorage.getItem('customer-orders-paging-current');
	let user_id = localStorage.getItem('customer-history-paging-user');

	if (user_id != currentUser)
	{
		localStorage.setItem('customer-orders-paging-current', null);
		localStorage.setItem('customer-history-paging-user', null);

		return;
	}

	if (page != null && typeof page !== 'undefined' && user_id != null && typeof user_id !== 'undefined')
	{
		$('#order_history').html('');
		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: page,
				processor: 'my_meals',
				operation: 'next'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#order_history').html(json.html);
				}
				else
				{
					console.log('There was an issue trying to restore paging location.');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('There was an issue retrieving your orders. Please reload the page.');
				return false;
			}
		});
	}
}

$(function () {

	// Handle rating clicks
	$('.my_meals-rate').bind({
		mouseenter: function (e) {
			mouseenterRatingStar(this);
		},
		mouseleave: function (e) {
			mouseleaveRatingStar(this);
		}
	});

	$('.my_meals-rate').on('click', function (e) {

		let element_id = $(this).data('element_id');
		let store_id = $(this).data('store_id');
		let recipe_id = $(this).data('recipe_id');
		let recipe_version = $(this).data('recipe_version');
		let rating = $(this).data('rating');

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				processor: 'my_meals',
				operation: 'rate',
				store_id: store_id,
				recipe_id: recipe_id,
				recipe_version: recipe_version,
				rating: rating
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					$('#org_rating-' + element_id).val(rating)

					setRatingStar(element_id, rating);
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {
				return false;
			}
		});
	});

	// Handle favorite clicks
	$('.my_meals-favorite').on('change', function (e) {

		let element_id = $(this).data('element_id');
		let store_id = $(this).data('store_id');
		let recipe_id = $(this).data('recipe_id');
		let recipe_version = $(this).data('recipe_version');
		let set_favorite = $(this).val();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				processor: 'my_meals',
				operation: 'favorite',
				store_id: store_id,
				recipe_id: recipe_id,
				recipe_version: recipe_version,
				set_favorite: set_favorite
			},
			success: function (json, status) {
				if (json.processor_success)
				{
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {
				return false;
			}
		});

	});

	$(document).on('keyup change', '.my_meals-review, .my_meals-note', function (e) {

		let field_type = 'review';
		if ($(this).hasClass('my_meals-note'))
		{
			field_type = 'note';
		}

		let element_id = $(this).data('element_id');
		let this_val = $.trim($(this).val());
		let org_val = this.defaultValue;

		if (this_val != org_val)
		{
			$('.my_meals-' + field_type + '-submit-row[data-element_id="' + element_id + '"]').showFlex();
		}
		else
		{
			$('.my_meals-' + field_type + '-submit-row[data-element_id="' + element_id + '"]').hideFlex();
		}

	});

	$(document).on('click', '.my_meals-review-cancel, .my_meals-note-cancel', function (e) {

		e.preventDefault();

		let field_type = 'review';
		if ($(this).hasClass('my_meals-note-cancel'))
		{
			field_type = 'note';
		}

		let element_id = $(this).data('element_id');
		let text_area = $('.my_meals-' + field_type + '[data-element_id="' + element_id + '"]');

		$('.my_meals-' + field_type + '[data-element_id="' + element_id + '"]').val(text_area[0].defaultValue);

		$(text_area).trigger('change');

	});

	$(document).on('click', '.my_meals-review-submit, .my_meals-note-submit', function (e) {

		e.preventDefault();

		let field_type = 'review';
		if ($(this).hasClass('my_meals-note-submit'))
		{
			field_type = 'note';
		}

		let element_id = $(this).data('element_id');
		let comment_element = $('.my_meals-' + field_type + '[data-element_id="' + element_id + '"]');
		let recipe_id = $(comment_element).data('recipe_id');
		let recipe_version = $(comment_element).data('recipe_version');
		let store_id = $(comment_element).data('store_id');
		let comment = $.trim($(comment_element).val());

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'my_meals',
				operation: field_type,
				recipe_id: recipe_id,
				recipe_version: recipe_version,
				comment: comment,
				store_id: store_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					$('.my_meals-' + field_type + '-submit-row[data-element_id="' + element_id + '"]').hideFlex();
					comment_element[0].defaultValue = comment;
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {
				return false;
			}
		});

	});

	// Handle search submit button
	$(document).on('click', '#my_meals_search_submit', function (e) {
		doFoodSearch();
	});

	// Handle search enter button
	$(document).on('keyup', '#my_meals_search', function (e) {
		if (e.key === 'Enter' || e.keyCode === 13)
		{
			doFoodSearch();
		}
	});

	$(document).on("click", ".orders-page-prev", function (event) {
		event.preventDefault();

		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		let cached = current_page;

		if (cached > 0)
		{
			cached = cached - 2;
		}

		localStorage.setItem('customer-orders-paging-current', cached);
		localStorage.setItem('customer-history-paging-user', user_id);

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'my_meals',
				operation: 'prev'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#order_history').html(json.html);
				}
				else
				{
					console.log('There was an issue trying to navigate to the previous page.');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('There was an issue retrieving your orders. Please reload the page.');
				return false;
			}
		});
		return false;
	});

	$(document).on("click", ".orders-page-next", function (event) {
		event.preventDefault();

		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		localStorage.setItem('customer-orders-paging-current', current_page);
		localStorage.setItem('customer-history-paging-user', user_id);

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'my_meals',
				operation: 'next'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#order_history').html(json.html);
				}
				else
				{
					console.log('There was an issue trying to navigate to the next page.');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('There was an issue retrieving your orders. Please reload the page.');
				return false;
			}
		});
		return false;
	});

});