<div class="row mb-2">
	<div class="col">
		<div class="row px-md-4">
			<div class="col-3 col-md-2 pr-0 py-2 bg-gray text-right">
				<img src="<?php echo IMAGES_PATH; ?>/style/platepoints/badge-<?php echo CUser::getCurrentUser()->platePointsData['current_level']['image']; ?>-119x119.png" alt="" class="img-fluid">
			</div>
			<div class="col-6 col-md-8 px-0 pt-3 pt-sm-3 pt-md-0 pt-lg-2 pt-xl-4 bg-gray">
				<div class="row">
					<div class="col-8 pr-0">
						<span class="text-green text-uppercase font-weight-semi-bold">Progress <?php echo CUser::getCurrentUser()->platePointsData['current_level']['percent_complete']; ?>%</span>
					</div>
					<div class="col-4 pl-0 text-right">
						<span class="font-size-small font-weight-bold text-uppercase"><?php echo number_format(CUser::getCurrentUser()->platePointsData['next_level']['req_points']); ?></span>
					</div>
				</div>
				<div class="progress bg-gray-dark height-2">
					<div class="progress-bar progress-bar-striped bg-green" role="progressbar" style="width: <?php echo CUser::getCurrentUser()->platePointsData['current_level']['percent_complete']; ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
				<div class="row">
					<div class="col text-right">
						<span class="font-size-small font-weight-bold text-uppercase">Next level</span>
					</div>
				</div>
			</div>
			<div class="col-3 col-md-2 pl-0 py-2 bg-gray">
				<img src="<?php echo IMAGES_PATH; ?>/style/platepoints/badge-<?php echo CUser::getCurrentUser()->platePointsData['next_level']['image']; ?>-119x119.png" alt="" class="img-fluid">
			</div>
		</div>
	</div>
</div>
