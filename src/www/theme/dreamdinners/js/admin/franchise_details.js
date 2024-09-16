function franchise_details_init()
{
	handle_owner_links();

	handle_store_links();
}

function deleteFranchiseConfirm()
{
	dd_message({
		title: 'Delete Franchise',
		message: 'Are you sure you want to delete this franchise?',
		confirm: function() {

			bounce('/backoffice/franchise-details?id=' + franchise_id + '&action=deleteFranchise');

		},
		cancel: function() {
		}
	});
}

function addOwner(guest)
{
	dd_toast({message: 'Adding ' + $(guest).data('firstname') + ' ' + $(guest).data('lastname')});

	create_and_submit_form({
		action: window.location.search,
		input: ({
			addOwner: true,
			user_id: $(guest).data('user_id')
		})
	});
}

function handle_owner_links()
{
	$('[data-manage_user_id]').on('click', function () {

		var user_id = $(this).data('manage_user_id');
		var manage_action = $(this).data('manage_action');

		if (manage_action == 'view')
		{
			bounce('/backoffice/user-details?id=' + user_id);
		}

		if (manage_action == 'edit')
		{
			bounce('/backoffice/account?id=' + user_id);
		}

		if (manage_action == 'delete')
		{
			dd_message({
				title: 'Remove Owner',
				message: 'Are you sure you want to remove this owner? The owners access privileges will be set to customer if they are not an owner of any other entities.',
				confirm: function() {

					dd_toast({message: 'Removing Owner'});

					create_and_submit_form({
						action: window.location.search,
						input: ({
							action: 'deleteOwner',
							owner_id: user_id
						})
					});

				},
				cancel: function() {
				}
			});

		}
	});
}

function handle_store_links()
{
	$('[data-manage_store_id]').on('click', function () {

		var store_id = $(this).data('manage_store_id');
		var manage_action = $(this).data('manage_action');
		var store_name = $(this).data('store_name');

		if (manage_action == 'view')
		{
			bounce('/backoffice/store-details?id=' + store_id);
		}

		if (manage_action == 'archive')
		{
			dd_message({
				title: 'Archive Store',
				message: 'Are you sure you want to archive and re-open <span style="color: red; font-weight: bold;">' + store_name + '</span>?',
				confirm: function() {

					bounce('/backoffice/archive-store?store=' + store_id);

				},
				cancel: function() {
				}
			});

		}

		if (manage_action == 'delete')
		{
			dd_message({
				title: 'Delete Store',
				message: 'Are you sure you want to <span style="color: red; font-weight: bold;">permanently</span> delete <span style="color: red; font-weight: bold;">' + store_name + '</span> store from all of Dream Dinners?',
				confirm: function() {

					dd_toast({message: 'Deleting Store'});

					create_and_submit_form({
						action: '/backoffice/store-details?id=' + store_id,
						input: ({
							action: 'deleteStore',
							id: store_id
						})
					});

				},
				cancel: function() {
				}
			});

		}
	});
}