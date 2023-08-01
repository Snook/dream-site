<table id="orders_history">
	<tr>
		<th>Time</th>
		<th>Action</th>
		<th>User</th>
		<th>Grand Total</th>
		<th>Item Count</th>
		<th># Servings</th>
	</tr>
	<?php

	$store = false;

	if (isset($this->storeInfo['id']))
	    $store = $this->storeInfo['id'];

	foreach($this->orders_history as $thisEntry) { ?>
	<tr>
		<td><?php echo CTemplate::dateTimeFormat($thisEntry['time'], VERBOSE, $store, CONCISE); ?></td>
		<td <?php if (!empty($thisEntry['notes'])) { echo "data-tooltip=\"" . $thisEntry['notes'] . "\""; }?>><?php echo $thisEntry['action']; ?></td>
		<td><?php echo $thisEntry['user']; ?></td>
		<td><?php echo $thisEntry['total']; ?></td>
		<td><?php echo $thisEntry['item_count']; ?></td>
		<td><?php echo $thisEntry['servings']; ?></td>
	</tr>
	<?php } ?>
</table>