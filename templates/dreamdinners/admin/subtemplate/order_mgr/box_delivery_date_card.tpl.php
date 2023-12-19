<div class="row mb-2">
	<div class="col-8 m-auto text-center">
		<?php foreach ($day['sessions'] AS $id => $session) { ?>
			<button type="button" class="btn btn-green btn-sm w-100 btn-spinner py-3 <?php if ($id == $this->orgSessionID) { ?>disabled<?php } ?>"
					data-select_delivery_day="<?php echo $session['id']; ?>"
					data-date="<?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>"
					data-booked_count="<?php echo $session["booked_count"]; ?>"
					data-remaining_slots="<?php echo $session["remaining_slots"]; ?>">
					<span class="row">
						<span class="col-3"><span class="collapse <?php if ($id == $this->orgSessionID) { ?>show<?php } ?>">(Selected)</span></span>
						<span class="col-6"><?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?></span>
						<span class="col-3"><span data-toggle="tooltip" data-placement="top" title="Booked / Available">(<?php echo $session["booked_count"]; ?>/<?php echo $session["remaining_slots"]; ?>)</span></span>
					</span>
			</button>
		<?php }  ?>
	</div>
</div>