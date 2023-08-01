<div class="row mb-2">
	<div class="col list-group text-center">
		<?php foreach ($day['sessions'] AS $id => $session) {
			if ($id == $this->orgSessionID) { ?>
			<button type="button" class="btn btn-primary btn-spinner list-group-item list-group-item-action disabled" style="color:white !important; padding:14px 6px;" data-selected_delivery_day="<?php echo $session['id']; ?>">&nbsp;(Selected)
				<?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>
			</button>
		<?php } else { ?>
				<button type="button" class="btn btn-primary btn-spinner list-group-item list-group-item-action" style="color:black !important; background-image:none; background-color: #9dd8d8; padding:14px 6px;" data-select_delivery_day="<?php echo $session['id']; ?>"
					data-date="<?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>" >
					<?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>
				</button>
		<?php } }  ?>
	</div>
</div>