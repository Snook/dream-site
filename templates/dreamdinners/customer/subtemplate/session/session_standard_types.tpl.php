<div class="tab-content" id="sesssionTypesTabContent">

<?php
$mealCustomizationSessionMessage = '<br><br>Times listed below are available for everyone! If you would like Meal Customization, please select a time marked with a <i class="dd-icon icon-customize text-orange font-size-small"></i> so we can have your dinners customized in time for your pick up or delivery. Customization options available at checkout.';
foreach ($this->sessions['info']['session_type'] AS $thisType => $count)
{
	if (in_array($thisType, array(
		'STANDARD',
		'SPECIAL_EVENT',
		'DELIVERY',
		'REMOTE_PICKUP'
	)) && $count > 0)
	{
		$DaySessionType = $thisType;
		if ($thisType == CSession::DELIVERY)
		{
			$DaySessionType = 'SPECIAL_EVENT-DELIVERY';
		}
		else if ($thisType == CSession::REMOTE_PICKUP)
		{
			$DaySessionType = 'SPECIAL_EVENT-REMOTE_PICKUP';
		}
		?>

	<div class="tab-pane fade <?php echo ($ActiveTabSet == $thisType ? "active show" : ""); $hasShownTab=true;?>" id="nav-<?php echo $thisType?>" role="tabpanel" aria-labelledby="<?php echo $thisType?>-tab">

	<!-- Type dependent messaging -->
	<?php switch($thisType) {
		case CSession::STANDARD: ?>
				<div class="text-center font-size-small mb-5">
					<?php if (!empty($this->session_type_descs['STANDARD']))
						{
							echo $this->session_type_descs['STANDARD'];
						}
						else
						{ ?>
							We look forward to seeing you in our store to assemble your meals. Be sure to bring a cooler and arrive on time.
						<?php } ?>
				</div>
		<?php	break;
		case CSession::SPECIAL_EVENT: ?>
			<div class="text-center font-size-small mb-5">
				<?php if (!empty($this->session_type_descs['PICKUP']))
				{
					echo $this->session_type_descs['PICKUP'];
				}
				else
				{ ?>
					Be sure to bring a cooler to take home your meals. A store service fee may apply. Fees will be displayed at checkout and may vary by location.
				<?php
				}
				if ($this->has_meal_customization_sessions){
					echo $mealCustomizationSessionMessage;
				} ?>
			</div>

			<?php	break;
		case CSession::DELIVERY: ?>
			<div class="text-center font-size-small mb-5">
				<?php if (!empty($this->session_type_descs['DELIVERY']))
				{
					echo $this->session_type_descs['DELIVERY'];
				}
				else
				{ ?>
					Select a delivery window from the list below. A store service fee may apply to this order. A delivery fee will apply. Fees will be displayed at checkout and may vary by location.
				<?php
				}
				if ($this->has_meal_customization_sessions){
					echo $mealCustomizationSessionMessage;
				} ?>
			</div>
			<?php	break;
		case CSession::REMOTE_PICKUP: ?>
			<div class="text-center font-size-small mb-5">
				<?php if (!empty($this->session_type_descs['REMOTE_PICKUP']))
				{
					echo $this->session_type_descs['REMOTE_PICKUP'];
				}
				else
				{ ?>
					Pick up your order at a local business or residents house located in the community instead of at the store location. Additional service fee may apply. Fees will be displayed at checkout and may vary by location.
				<?php
				}
				if ($this->has_meal_customization_sessions){
					echo $mealCustomizationSessionMessage;
				} ?>
			</div>

			<?php	break;
	} ?>

	<?php foreach ($this->sessions['sessions'] as $date => $day)
		{
			if ($day['info']['has_available_sessions'] && array_key_exists($DaySessionType, $day['info']['session_types'])) {
				$this->assign('this_session_type', $thisType)   ?>
				<?php include $this->loadTemplate('customer/subtemplate/session/session_type_day_card.tpl.php'); ?>

			<?php }
		}?>

	</div>

<?php } }
?>
</div>