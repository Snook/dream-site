<div class="col-md-4 col-12 p-0 mb-xs-1 text-md-right text-center">
	<a href="/location/<?php echo $this->storeInfo['id']; ?>"><img src="<?php echo IMAGES_PATH; ?>/stores/<?php echo $this->storeInfo['id']; ?>.webp" class="img-fluid" alt="<?php echo $this->storeInfo['store_name']; ?> Store Front" /></a>
</div>
<div class="col-md-4 col-12 p-3 text-md-right text-center">
	<h3 class="font-weight-bold text-uppercase mb-2">
		<a href="/location/<?php echo $this->storeInfo['id']; ?>"><?php echo $this->storeInfo['store_name']; ?></a>
	</h3>
	<div class="font-weight-bold font-size-medium-small text-uppercase">
		<?php if ($this->can_reschedule && $this->sessionInfo['session_type_subtype'] == CSession::WALK_IN) {
			echo CTemplate::dateTimeFormat($this->sessionInfo['session_start'], VERBOSE_DATE) ;
		}else{
			 echo CTemplate::dateTimeFormat($this->sessionInfo['session_start'], VERBOSE_DATE_NO_YEAR_W_COMMA) . " at " . CTemplate::dateTimeFormat($this->sessionInfo['session_start'], SIMPLE_TIME); ?>
		<?php } ?>
	</div>
	<?php if ($this->can_reschedule && $this->sessionInfo['session_type_subtype'] != CSession::WALK_IN) { ?>
		<a class="btn btn-secondary btn-sm d-print-none" href="/session?reschedule=<?php echo $this->orderInfo['id']; ?>">Reschedule</a>
	<?php } ?>
	<?php if ($this->orderDetailsArray['bookingStatus'] != CBooking::CANCELLED) { ?>
		<p class="mt-1 mb-1 font-size-small">To edit your order or make additional changes please contact your store.</p>
	<?php } ?>
	<div class="font-weight-bold font-size-medium-small">
		<?php echo $this->customerActionString;?>
	</div>
	<?php if ($this->sessionInfo['session_type'] == CSession::SPECIAL_EVENT && ($this->sessionInfo['session_type_subtype'] == CSession::REMOTE_PICKUP || $this->sessionInfo['session_type_subtype'] == CSession::REMOTE_PICKUP_PRIVATE)) {
		$Remote_AddressObj = $this->sessionInfo['session_remote_location'];
		$Remote_Address = $Remote_AddressObj->address_line1 . ", " . (!empty($Remote_AddressObj->address_line2) ? $Remote_AddressObj->address_line2 . ", " : "") . $Remote_AddressObj->city . " " . $Remote_AddressObj->state_id . ", " . $Remote_AddressObj->postal_code;
		?>

		<div class="row bg-gray-light">
			<div class="col-md-12 col-6 text-md-right text-center py-2 col-12">
				<a target="_blank" href="https://maps.google.com/?q=<?php echo $Remote_Address; ?>"><?php echo $Remote_Address; ?></a>
			</div>
		</div>
	<?php } else if (empty($this->sessionInfo['session_type_subtype']) || $this->sessionInfo['session_type_subtype'] != CSession::DELIVERY) { ?>
	<div class="text-uppercase mt-1 mb-2">
		<div>
			<a target="map_view" onclick="showMap('<?php echo urlencode($this->storeInfo['linear_address']); ?>');event.preventDefault();" href="<?php echo $this->storeInfo['map']?>">
				<?php echo $this->storeInfo['address_line1']; ?><br />
				<?php echo (!empty($this->storeInfo['address_line2'])) ? $this->storeInfo['address_line2'] . "<br />" : ''; ?>
				<?php echo $this->storeInfo['city']; ?>, <?php echo $this->storeInfo['state_id']; ?> <?php echo $this->storeInfo['postal_code']; ?>
			</a>
		</div>
	</div>
	<?php } ?>
	<div><?php echo $this->storeInfo['telephone_day']; ?></div>

</div>