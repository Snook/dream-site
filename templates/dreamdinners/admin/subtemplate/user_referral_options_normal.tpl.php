<tr>
	<td>
		<table>
			<?php if ($this->referral_status['userIsNew'] && !$this->referral_status['hasTriggeredReward'] ) { ?>
				<?php if ($this->referral_status['eligibleOrder']) { ?>
					<?php  if ($this->referral_status['hasPendingReferral']) { // Scenario 2: User is new and has ordered has pending referral ?>
						<tr id="pendingOptions">
							<td style="vertical-align:top;"><input id="leave_queued" name="reward_option" value="leave_queued" type="radio" /></td>
							<td style="text-align:left;"> <label for="leave_queued">Leave <?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? '<span data-reward_type="credit">$10 store credit</span><span data-reward_type="points">250 points</span>' : '<span data-reward_type="credit">$10 store credit</span><span data-reward_type="points">500 points</span>' )?> reward queued (available on <?php echo CTemplate::dateTimeFormat($this->referral_status['eligibleOrderInfo']['reward_date'], NORMAL, $this->store_id)?>)
								</label>
							</td>
						</tr>
						<tr id="nonpendingOptions">
							<td style="vertical-align:top;"><input id="queue_new" name="reward_option" value="queue_new" type="radio" /></td>
							<td style="text-align:left;"> <label for="queue_new">Queue <?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">250 points</span>' : '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">500 points</span>' )?> reward (available on <?php echo CTemplate::dateTimeFormat($this->referral_status['eligibleOrderInfo']['reward_date'], NORMAL, $this->store_id)?>)
								</label>
							</td>
						</tr>
						<tr>
							<td style="vertical-align:top;"><input id="reward_new" name="reward_option" value="<?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? 'reward_new_5' : 'reward_new' )?>" type="radio" /></td>
							<td style="text-align:left;"> <label for="reward_new">Reward the referring guest immediately <?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span>' : '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span>' )?>.
								</label>
							</td>
						</tr>
						<tr>
							<td style="vertical-align:top;"><input  id="no_reward" name="reward_option"  value="never" type="radio" /></td>
							<td style="text-align:left;"><label for="no_reward">Do not reward this user</label></td>
						</tr>
					<?php } else { // Scenario 3: User is new and has ordered has no pending referrals ?>
						<tr>
							<td style="vertical-align:top;"><input id="reward_new" name="reward_option" value="<?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? 'reward_new_5' : 'reward_new' )?>" type="radio" /></td>
							<td style="text-align:left;">
								<label for="reward_new">Reward the referring guest immediately <?php echo ($this->referral_status['eligibleOrderInfo']['isIntro'] ? '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span>' : '<span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span>' )?>.
								</label>
							</td>
						</tr>
						<tr>
							<td style="vertical-align:top;"><input  id="no_reward" name="reward_option"  value="never" type="radio"  checked="checked" /></td>
							<td style="text-align:left;"><label for="no_reward">Do not reward this user</label></td>
						</tr>
					<?php } ?>
				<?php } else { // Is new but has no orders // Scenario 4: User is new and has no orders ?>
					<tr>
						<td style="vertical-align:top;"><input id="queue_new" name="reward_option" value="queue_new" type="radio" checked="checked" /></td>
						<td style="text-align:left;"> <label for="queue_new">Queue this referral for the guest's upcoming first order</label></td>
					</tr>
					<tr>
						<td style="vertical-align:top;"><input id="reward_new" name="reward_option" value="reward_new" type="radio" /></td>
						<td style="text-align:left;"> <label for="reward_new">Reward the referring guest immediately <span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span> (not tied to a specific order)</label></td>
					</tr>
					<tr>
						<td style="vertical-align:top;"><input id="reward_new" name="reward_option" value="reward_new_5" type="radio" /></td>
						<td style="text-align:left;"> <label for="reward_new">Reward the referring guest immediately <span data-reward_type="credit">a $10 store credit</span><span data-reward_type="points">400 points</span> (not tied to a specific order)</label></td>
					</tr>
					<tr>
						<td style="vertical-align:top;"><input  id="no_reward" name="reward_option"  value="never" type="radio" /></td>
						<td style="text-align:left;"><label for="no_reward">Do not reward this user</label></td>
					</tr>
				<?php } ?>
			<?php  } ?>
		</table>
	</td>
</tr>