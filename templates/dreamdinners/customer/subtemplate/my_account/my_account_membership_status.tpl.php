<div class="row mb-2">
	<div class="col">
		<!--<p class="text-uppercase">Meal Prep+</p>-->
		<img src="<?php echo IMAGES_PATH; ?>/events_programs/Meal-Prep-Plus-150x34.png" alt="Meal Prep+" class="img-fluid" style="margin-bottom: 15px;" />
		<div>Meal Prep+ Progress: <?php echo CUser::getCurrentUser()->membershipData['display_strings']['progress']; ?></div>
		<!--<div class="font-size-small">Number of orders placed: <?php echo CUser::getCurrentUser()->membershipData['total_orders']; ?></div>-->
		<div>Last Membership Month: <?php echo CUser::getCurrentUser()->membershipData['display_strings']['completion_month']; ?></div>
		<div>Total Membership Savings: $<?php echo CTemplate::moneyFormat(CUser::getCurrentUser()->membershipData['total_savings']); ?></div>
	</div>
</div>