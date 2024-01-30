<ul id="session_selection">

	<?php foreach ($this->sessions AS $date => $day) { ?>

		<li class="day <?php echo ($day['info']['is_past']) ? 'is_past' : ''; ?>"
			id="day_<?php echo $date; ?>"
			data-unix_expiry="<?php echo (strtotime($date . ' 23:59:59')); ?>"
			data-date="<?php echo $date; ?>"

			data-menu_id="<?php echo $day['info']['menu_id']; ?>">


			<span class="name"><?php echo date('l', strtotime($date)); ?></span>
			<span class="date"><?php echo CTemplate::dateTimeFormat($date, MONTH_DAY_YEAR); ?></span>
			<div class="clear"></div>
		</li>

		<?php if (!empty($day['sessions'])) {
			foreach ($day['sessions'] AS $id => $session) {?>
					<li class="session <?php echo ($session['is_past']) ? 'is_past' : ''; ?>"
						id="session-<?php echo $session['id']; ?>"
						data-unix_expiry="<?php echo $session['unix_expiry']; ?>"
						data-session_id="<?php echo $session['id']; ?>"
						data-session_date="<?php echo CTemplate::dateTimeFormat($session['session_start'], YEAR_MONTH_DAY); ?>"
						data-menu_id="<?php echo $session['menu_id']; ?>">
						<span class="type"><span class="note_calendar note_pickup" data-toggle="tooltip" title="Pick Up">P</span></span>
						<span class="shipped" data-tooltip="Pick Up"><?php echo count($session['shipping_bookings']); ?></span>
						<span class="remaining" data-tooltip="Available to Ship"><?php echo $session['remaining_slots']; ?></span>
						<div class="clear"></div>
					</li>
				<li class="session <?php echo ($session['is_past']) ? 'is_past' : ''; ?>"
					id="session-<?php echo $session['id']; ?>"
					data-unix_expiry="<?php echo $session['unix_expiry']; ?>"
					data-session_id="<?php echo $session['id']; ?>"
					data-session_date="<?php echo CTemplate::dateTimeFormat($session['session_start'], YEAR_MONTH_DAY); ?>"
					data-menu_id="<?php echo $session['menu_id']; ?>">
					<span class="type"><span class="note_calendar note_delivery" data-toggle="tooltip" title="Delivery">D</span></span>
					<span class="attending" data-tooltip="Delivery"><?php echo count($session['bookings']);?></span>
					<div class="clear"></div>
				</li>
				<?php
			}
		} else {
			writeNone($date,$day);
		}
	}?>

</ul>

<?php function writeNone($date,$day){
	$past = $day['info']['is_past'] ? 'is_past' : '';
	$unix_expire = strtotime($date . ' 23:59:59');
	$menu_id = $day['info']['menu']['id'];
	$selected_day = CTemplate::dateTimeFormat($date, VERBOSE_DATE);
	echo "<li class='session {$past}'";
	echo "data-unix_expiry='{$unix_expire}'";
	echo "data-date='{$date}' data-menu_id='{$menu_id}'";
	echo "data-selected_day='{$selected_day}'>";
	echo "<div class='no_sessions'>No Shipping or Deliveries</div>";
	echo "</li>";
}
?>