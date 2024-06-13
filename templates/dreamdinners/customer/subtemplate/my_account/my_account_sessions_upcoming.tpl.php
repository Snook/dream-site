<?php
$dateType = VERBOSE;
if ($order['session_type_subtype'] == CSession::WALK_IN) {
	$dateType = VERBOSE_DATE;
}
?>
<div class="row my-1 mb-1">
	<div class="col-md-12 mb-1 text-left">
		<a class="btn btn-primary btn-block" href="/order-details?order=<?php echo $order['id']; ?>"><?php echo CTemplate::dateTimeFormat($order['session_start'], $dateType); ?></a>
		<?php if ($order['is_gift']) { ?>
		<div class="custom-control custom-checkbox">
			<input class="custom-control-input" id="my_meals_search_all" name="my_meals_search_all" type="checkbox" checked="checked" onclick="return false;">
			<label class="custom-control-label" for="my_meals_search_all">Gifted Box</label>
		</div>
		<?php }?>
	</div>
</div>
<div class="row my-1 mb-4">
	<?php if (false) { // disabled add to calendar ?>
	<div class="col-4 col-md-4">
		<div class="btn-group btn-block">
			<div class="addeventatc btn btn-primary" data-styling="none" aria-haspopup="true" aria-expanded="false">
				<i class="dd-icon icon-calendar_add font-size-extra-large"></i>
				<div class="font-size-small">Add to <br>Calendar</div>
				<span class="start collapse"><?php echo CTemplate::dateTimeFormat($order['session_start'], DATE_TIME_ITEMPROP); ?></span>
				<span class="end collapse"><?php echo CTemplate::dateTimeFormat($order['session_end'], DATE_TIME_ITEMPROP); ?></span>
				<span class="timezone collapse"><?php echo $this->store['PHPTimeZone'];?></span>
				<span class="title collapse">Dream Dinners Session</span>
				<span class="description collapse">Bring your cooler to easily transport your meals home. If you have any questions regarding this or any other Dream Dinners information please contact the store. Email: <?php echo $this->store['email_address']; ?> Phone: <?php echo $this->store['telephone_day']; ?></span>
				<span class="location collapse"><?php if(strpos($order['session_type_subtype'],'PICKUP') !== false)
					{

						$Remote_AddressObj = $order['session_remote_location'];
						$Remote_Address = $Remote_AddressObj->address_line1 . ", " . (!empty($Remote_AddressObj->address_line2) ? $Remote_AddressObj->address_line2
								. ", " : "") . $Remote_AddressObj->city . " " . $Remote_AddressObj->state_id . ", " . $Remote_AddressObj->postal_code;
						echo $Remote_Address;
					}
					else
					{
						echo $order['address_line1']; ?>
						<?php echo (!empty($order['address_line2'])) ? ' ' . $order['address_line2'] : ''; ?>
						<?php echo $order['city']; ?>
						<?php echo $order['state_id']; ?>
						<?php echo $order['postal_code']; }?></span>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php if ($order['session_type'] !== CSession::DELIVERED) { ?>
		<div class="col-4 col-md-4">
			<a class="btn btn-primary btn-block" href="/my-events?sid=<?php echo $order['session_id']; ?>">
				<i class="dd-icon icon-email_1 font-size-extra-large"></i>
				<div class="font-size-small">Invite <br>Friends</div>
			</a>
		</div>
	<?php } ?>
	<?php if (isset($order['has_freezer_inventory']) && $order['has_freezer_inventory'] && $order['session_type_subtype'] != CSession::WALK_IN) { ?>
		<div class="col-4 col-md-4">
			<a class="btn btn-primary btn-block" href="/sides-and-sweets-order-form?id=<?php echo $order['id']; ?>">
				<i class="dd-icon icon-pie font-size-extra-large"></i>
				<div class="font-size-small">Sides &amp; Sweets <br>Order Form</div>
			</a>
		</div>
	<?php } ?>
</div>
<br>