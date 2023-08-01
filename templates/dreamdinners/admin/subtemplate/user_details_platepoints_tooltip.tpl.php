<div class="row">
	<div class="col-4">
		<?php if ($this->user->platePointsData['userIsOnHold']) { ?>
			<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-hold-119x119.png" class="img-fluid" />
		<?php } else { ?>
			<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $this->user->platePointsData['current_level']['image']; ?>-119x119.png" class="img-fluid" />
		<?php } ?>
	</div>
	<div class="col-8 pl-0">
		<div class="row">
			<div class="col">
				<h3 class="mb-0 ml-1">
					<?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?>
				</h3>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h1 class="mb-1 ml-1">
					<?php echo $this->user->platePointsData['current_level']['title']; ?>
				</h1>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<table class="table table-sm mb-0">
					<?php if (!empty($this->user->platePointsData['available_credit'])) { ?>
						<tr>
							<td>Available Dinner Dollars</td>
							<td class="text-right">$<?php echo CTemplate::moneyFormat($this->user->platePointsData['available_credit']); ?></td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
</div>