<div class="col-md-12">
	<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left mb-4">Store Credits</h2>
</div>
<div class="col-md-12 mb-4">
	<form id="check-balance">
		<div class="form-row">
			<div class="form-group col-md-12">

				<table class="table">
					<tbody>
					<?php
					if( !empty( $this->Store_Credits ) )
					{
						$iTotal = 0.00;

						if ($this->numType2Credits > 0)
						{
							if ($this->numType2Credits > 1)
							{
					?>
					<tr>
						<td><input id="SC-total" type="checkbox" disabled="disabled" <?php echo (($this->countSelectedCredits > 0) ? 'checked="checked"' : "")?> onclick="rewardsCreditClick(this);" />
							<span onclick="toggleId('type2Credits');" style="cursor:pointer;">Apply all referral award credit</span></td>


						<td style="text-align:right;"><span><?php echo CTemplate::moneyFormat($this->totalType2Credit); ?></span></td>
					</tr>
					</tbody>
					<tbody id="type2Credits" style="display:none;">
					<?php }

					foreach( $this->Store_Credits as $SC_ID => $thisCredit )
					{
						// taste of invite a friend credit
						if ($thisCredit['credit_type'] == 2)
						{
							$iTotal += $thisCredit['total_amount'];
							?>
							<tr>
								<td><?php echo $this->credits_form['SC_'.$SC_ID . '_html']; ?></td>
								<td class="text-right"><span id="SCA_<?php echo $SC_ID; ?>"><?php echo CTemplate::moneyFormat($thisCredit['total_amount']); ?></span></td>
							</tr>
						<?php
						}

						// direct credit
						if ($thisCredit['credit_type'] == 3)
						{
							$iTotal += $thisCredit['total_amount'];
							?>
							<tr>
								<td><?php echo $this->credits_form['SC_'.$SC_ID . '_html']; ?></td>
								<td class="text-right"><span id="SCA_<?php echo $SC_ID; ?>"><?php echo CTemplate::moneyFormat($thisCredit['total_amount']); ?></span></td>
							</tr>
						<?php
						}
					}
						if ($this->numType2Credits > 1)
						{
						?>
						</tbody>
						<?php
						}
					}

					if ($this->numType1Credits > 0)
					{
						if ($this->numType1Credits > 1)
						{
							?>
			<tr>
				<td><input id="SC-total" type="checkbox"  disabled="disabled" <?php echo (($this->countSelectedCredits > 0) ? 'checked="checked"' : "")?> onclick="rewardsCreditClick(this);" />
				<span onclick="toggleId('type2Credits');" style="cursor:pointer;">Apply all refunded store credit</span></td>


				<td class="text-right"><span><?php echo CTemplate::moneyFormat($this->totalType1Credit); ?></span></td>
			</tr>
			</tbody>
			<tbody id="type2Credits" style="display:none;">
		<?php
						}

						foreach( $this->Store_Credits as $SC_ID => $thisCredit )
						{
							// taste of invite a friend credit
							if ($thisCredit['credit_type'] == 1)
							{
								$iTotal += $thisCredit['total_amount'];
								?>
								<tr>
									<td style="padding-left:10px;"><?php echo $this->credits_form['SC_'.$SC_ID . '_html']; ?></td>
									<td style="text-align:right;"><span id="SCA_<?php echo $SC_ID; ?>"><?php echo CTemplate::moneyFormat($thisCredit['total_amount']); ?></span></td>
								</tr>
							<?php
							}

						}

						if ($this->numType1Credits > 1)
						{
							?>
							</tbody>
						<?php
						}
					}

					?>
					<tbody>
					<tr>
						<td colspan="2" class="text-right">Total Credits: $<?php echo CTemplate::moneyFormat($iTotal); ?></td>
					</tr>
					</tbody>
					<?php
					}
					?>
				</table>
			</div>
		</div>
	</form>
</div>




