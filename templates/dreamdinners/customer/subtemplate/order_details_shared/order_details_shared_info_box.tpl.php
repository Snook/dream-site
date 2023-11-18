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
		<a class="btn btn-secondary btn-sm d-print-none" href="/session?reschedule=<?php echo $this->orderInfo['id']; ?>">Reschedule</a>
	<?php } ?>
	<?php if ($this->orderDetailsArray['bookingStatus'] != CBooking::CANCELLED) { ?>
		<p class="mt-2 font-size-small">To edit your order or make additional changes please contact Dream Dinners at <a href="mailto:<?php echo $this->contactStoreInfo->email_address; ?>?subject=Dream Dinners Delivered support request, confirmation number <?php echo $this->orderDetailsArray['orderInfo']['order_confirmation']; ?>"><?php echo $this->contactStoreInfo->email_address; ?></a></p>
	<?php } ?>
	<?php if ( !empty($this->orderInfo['orderShipping']['tracking_number'])) { ?>
		<div class="font-weight-bold font-size-small">UPS Tracking Number: <a target="_blank" href="<?php echo CAppUtil::upsTrackingUrl($this->orderInfo['orderShipping']['tracking_number']);?>"><?php echo $this->orderInfo['orderShipping']['tracking_number']; ?></a></div>
	<?php } ?>
</div>