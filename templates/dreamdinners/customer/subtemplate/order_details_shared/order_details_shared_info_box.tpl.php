<div class="col-md-4 col-12 p-0 mb-xs-1 text-md-right text-center">
	<img src="<?php echo IMAGES_PATH; ?>/stores/<?php echo $this->storeInfo['id']; ?>.webp" class="img-fluid" alt="<?php echo $this->storeInfo['store_name']; ?> Store Front" />
</div>
<div class="col-md-4 col-12 p-3 text-md-right text-center">
	<h3 class="font-weight-bold text-uppercase mb-2">
		Delivery Date
	</h3>
	<div class="font-weight-bold font-size-medium-small text-uppercase">
		<?php echo CTemplate::dateTimeFormat($this->sessionInfo['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA); ?>
	</div>
	<?php if ($this->can_reschedule) { ?>
		<a class="btn btn-secondary btn-sm d-print-none" href="/?page=session&amp;reschedule=<?php echo $this->orderInfo['id']; ?>">Reschedule</a>
	<?php } ?>
	<?php if ($this->orderDetailsArray['bookingStatus'] != CBooking::CANCELLED) { ?>
		<?php if ( $this->orderDetailsArray['canEditDeliveredOrder'] ) { ?>
			<a href="/?page=checkout&restore_order=<?php echo $this->orderInfo['id']; ?>" class="btn btn-primary">Edit Order</a>
			<button id="handle-cancel-delivered-order" data-user_id="<?php echo $this->orderInfo['user_id']; ?>" data-store_id="<?php echo $this->storeInfo['id']; ?>" data-session_id="<?php echo $this->sessionInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>" data-menu_id="<?php echo $this->menuInfo['menu_id']; ?>" class="btn btn-primary btn-gray">Cancel Order</button>
		<?php } else{ ?>
			<p class="mt-2 font-size-small">To edit your order or make additional changes please contact Dream Dinners at <a href="mailto:support@dreamdinners.com?subject=Dream Dinners Delivered support request, confirmation number <?php echo $this->orderDetailsArray['orderInfo']['order_confirmation']; ?>">support@dreamdinners.com</a></p>
		<?php } ?>
	<?php } ?>
	<?php if ( !empty($this->orderInfo['orderShipping']['tracking_number'])) { ?>
		<div class="font-weight-bold font-size-small">FedEx Tracking Number: <a target="_blank" href="<?php echo CAppUtil::fedexTrackingUrl($this->orderInfo['orderShipping']['tracking_number']);?>"><?php echo $this->orderInfo['orderShipping']['tracking_number']; ?></a></div>
	<?php } ?>
</div>