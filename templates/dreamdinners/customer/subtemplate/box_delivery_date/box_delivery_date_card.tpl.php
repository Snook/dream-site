<div class="row mb-2">
	<div class="col list-group text-center">
		<?php foreach ($day['sessions'] AS $id => $session) { ?>
			<?php if($id == $this->selected_session) { ?>
				<button type="button" class="btn btn-sm btn-primary btn-block btn-spinner" data-select_delivery_day="<?php echo $session['id']; ?>">
					<?php echo 'Keep Current Delivery Date: '.CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>
				</button>
			<?php } else {?>
			<button type="button" class="btn btn-primary btn-spinner list-group-item list-group-item-action" data-select_delivery_day="<?php echo $session['id']; ?>">
				<?php echo CTemplate::dateTimeFormat($date, FULL_DAY); ?> <?php echo CTemplate::dateTimeFormat($date, FULL_MONTH_DAY_YEAR); ?>
			</button>
		<?php } ?>
		<?php } ?>
	</div>
</div>