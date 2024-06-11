<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1 d-print-none">
			<a href="/session-menu" class="btn btn-primary"><span class="pr-2">&#10094;</span> Menu</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center col-print-12">
			<h2>Select <span class="d-none d-md-inline">your</span> <span class="font-weight-bold text-green">order</span> date</h2>
			<p>Dream Dinners <?php echo $this->cart_info['store_info']['store_name']; ?></p>
		</div>
	</div>
</header>
<?php $ActiveTabSet = false; ?>
<main class="container">
	<div class="row mb-2">
		<div class="col px-0">
			<nav class="sticky-top row mb-4">
				<div class="col-12  flex-fill mb-2 nav nav-pills nav-fill" style="padding-right:0px" id="session_types" role="tablist">
					<?php if (!empty($this->sessions['info']['session_type'][CSession::MADE_FOR_YOU])) { ?>
						<a class="border nav-item nav-link font-weight-bold text-uppercase <?php if (!$ActiveTabSet) {echo "active"; $ActiveTabSet='SPECIAL_EVENT';}?>" id="nav-SPECIAL_EVENT-tab"
						   data-urlpush="true" data-toggle="tab" data-target="#nav-SPECIAL_EVENT"
						   href="/session?tab=SPECIAL_EVENT" role="tab" aria-controls="nav-SPECIAL_EVENT" aria-selected="true">
							<i class="dd-icon icon-store-front font-size-medium-large align-bottom"></i> View</br>Store Pick Up Times</a>
					<?php } ?>
					<?php if (!empty($this->sessions['info']['session_type'][CSession::REMOTE_PICKUP])) { ?>
						<a class="border nav-item nav-link font-weight-bold text-uppercase <?php if (!$ActiveTabSet) {echo "active"; $ActiveTabSet='REMOTE_PICKUP';}?>" id="nav-REMOTE_PICKUP-tab"
						   data-urlpush="true" data-toggle="tab" data-target="#nav-REMOTE_PICKUP"
						   href="/session?tab=REMOTE_PICKUP" role="tab" aria-controls="nav-REMOTE_PICKUP" aria-selected="true">
							<i class="dd-icon icon-community-pick-up font-size-medium-large align-bottom"></i> View</br>Community Pick Up Times</a>
					<?php } ?>
					<?php if (!empty($this->sessions['info']['session_type'][CSession::STANDARD])) { ?>
						<a class="border nav-item nav-link font-weight-bold text-uppercase <?php if (!$ActiveTabSet) {echo "active"; $ActiveTabSet='STANDARD';}?>" id="nav-STANDARD-tab"
						   data-urlpush="true" data-toggle="tab" data-target="#nav-STANDARD"
						   href="/session?tab=STANDARD" role="tab" aria-controls="nav-STANDARD" aria-selected="true">
							<i class="dd-icon icon-measuring_cup font-size-medium-large align-bottom"></i> View</br>In-Store Assembly Times</a>
					<?php } ?>
					<?php if (!empty($this->sessions['info']['session_type'][CSession::DELIVERY])) { ?>
						<a class="border nav-item nav-link font-weight-bold text-uppercase <?php if (!$ActiveTabSet) {echo "active"; $ActiveTabSet='DELIVERY';}?>" id="nav-DELIVERY-tab"
						   data-urlpush="true" data-toggle="tab" data-target="#nav-DELIVERY"
						   href="/session?tab=DELIVERY" role="tab" aria-controls="nav-DELIVERY" aria-selected="true">
							<i class="dd-icon icon-delivery font-size-medium-large align-bottom"></i> View</br>Home Delivery Times</a>
					<?php } ?>
				</div>
			</nav>

			<?php include $this->loadTemplate('customer/subtemplate/session/session_standard_types.tpl.php'); ?>

			<div class="alert alert-dark text-center mt-4 no-filter-selected collapse" role="alert">
				No times available, please select an order type from the filter above.
			</div>

			<?php if(count($this->sessions['sessions']) == 0){ ?>
				<div class="text-center mt-4 no-session-available" role="alert">
					Oops, it looks like there aren’t any dates available to complete your order.<br>Please select another menu to order from. <br><br>
					<a href="/session-menu" class="btn btn-primary">Menu</a>
				</div>
			<?php } ?>
		</div>
	</div>
</main>