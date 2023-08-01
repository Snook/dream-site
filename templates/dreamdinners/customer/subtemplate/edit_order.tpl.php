<?php if (CBrowserSession::getValue('EDIT_DELIVERED_ORDER')) { ?>
	<div class="container d-print-none">
		<div class="alert alert-warning fade show" role="alert">
			<div class="row">
				<div class="col-md-8 mb-2">
					You are currently editing a Delivered order.
				</div>
				<div class="col-md-4">
					<span class="btn btn-sm btn-red btn-block float-right clear-edit-delivered-order">Cancel Editing Order</span>
				</div>
			</div>
		</div>
	</div>
<?php } ?>