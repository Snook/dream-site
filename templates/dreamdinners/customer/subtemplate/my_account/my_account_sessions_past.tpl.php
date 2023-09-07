<?php
$dateType = VERBOSE;
if ($order['session_type_subtype'] == CSession::WALK_IN) {
	$dateType = VERBOSE_DATE;
 }
?>
<div class="row my-1 mb-4">
	<div class="col-md-6 mb-1 text-center">
		<a class="btn btn-gray-dark btn-block" href="/?page=order_details&amp;order=<?php echo $order['id']; ?>"><?php echo CTemplate::dateTimeFormat($order['session_start'], $dateType); ?></a>
	</div>
	<?php if ($order['can_rate_my_meals']) { ?>
	<div class="col-6 col-md-3 pr-1">
		<a class="btn btn-primary btn-block" href="/?page=my_meals&amp;order=<?php echo $order['id']; ?>">
			<i class="dd-icon icon-star font-size-extra-large"></i>
			<div>Rate Meals</div>
		</a>
	</div>
	<?php } ?>
	<?php if ($order['session_type'] !== CSession::DELIVERED) { ?>
	<div class="col-6 col-md-3 pl-1">
		<a class="btn btn-primary btn-block" href="/?page=print&amp;order=<?php echo $order['id']; ?>&amp;freezer=true" target="_blank">
			<i class="dd-icon icon-freezer font-size-extra-large"></i>
			<div>Freezer Sheet</div>
		</a>
	</div>
	<?php } ?>
</div>