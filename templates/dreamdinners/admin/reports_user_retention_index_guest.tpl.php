<?php require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php'); ?>
<!-- ###################Guest Details############################################ !-->
				<td >


			<table width=420 border="0"  >



			<tr><td>
					<table width=420  class='guest'>
						<tr >
							<td>
								<table  width=420 class='subheads'>
										<tr>
											<td  >
												Guest Information (<?=$counter?>)
											</td>
										</tr>
								</table>

								<?php if ($this->step == 1) {  ?>
								<table style="padding-bottom: 4px;" border=0 >
								<?php } else { ?>
								<table  border=0 >
								<?php } ?>

										<?php
										$font = "black";
										//if ($user_array['is_reactivated'] == 1) $font = "#3300CC";  // this is for testing
										?>

										<tr>
											<td width=100 class='details'>Guest Name:</td><td width=120 ><font color=<?=$font?>>
												<?php
												if (!empty($user_array['firstname']) && !empty($user_array['lastname']))
													echo $user_array['lastname'] . ',&nbsp;' . $user_array['firstname'];
												else if (empty($user_array['firstname']) && !empty($user_array['lastname']))
													echo $user_array['lastname'];
												else if (!empty($user_array['firstname']) && empty($user_array['lastname']))
													echo $user_array['firstname'];
												?>
											</font></td>
										</tr>

										<tr>
											<td width=100  class='details'>ID:</td>


											<td  width=120 align=left><font color=<?=$font?>><?=$user_array['user_id']?></font>


											</td>
										</tr>

										<tr>
											<td width=100 class='details'>Primary Phone:</td><td  width=130><?=$user_array['telephone_1']?></td>
										</tr>

										<tr>
											<td width=100 class='details'>Secondary Phone:</td><td  width=130><?=$user_array['telephone_2']?></td>
										</tr>


										<tr>
											<td width=100 class='details'>Primary Call Time:</td><td width=130><?=$user_array['telephone_1_call_time']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Email:</td><td  width=130 align=left><?=$user_array['primary_email']?></td>
										</tr>

										<tr>
											<td width=100 class='details'>Address:</td>
											<td  width=250>
											<?=$user_array['address_line1']?><br/><?=$user_array['city']?>,&nbsp;<?=$user_array['state_id']?>&nbsp;<?=$user_array['postal_code']?>

											</td>
										</tr>

										<tr>
											<td width=100  class='details'>Last Session:</td><td  width=130 align=left><?=$user_array['session_start']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Last Session Type:</td><td  width=130 align=left><?=$user_array['session_type']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Attended:</td><td  width=130 align=left><?=$user_array['booking_count']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Days Inactive:</td><td  width=130 align=left><?=$user_array['days_inactive']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Loyalty Status:</td><td  width=130 align=left>Status : <i><?=CDreamRewardsHistory::$DRDescriptiveNameMap[$user_array['dream_reward_status']]?></i><br /></td>
										</tr>

										<?php if ($this->step == 1) {  ?>

										<tr>
											<td width=100  class='details'>Referral Credit Available:</td><td  width=130 align=left>$<?=$user_array['ReferralCredit']?></td>
										</tr>
										<tr>
											<td width=100  class='details'>Direct Credit Available:</td><td  width=130 align=left>$<?=$user_array['DirectCredit']?></td>
										</tr>

										<?php } else if ($this->step == 2) {  ?>

										<tr>
											<td width=100  class='details'>Referral Credit Expired:</td><td  width=130 align=left>$<?=$user_array['ReferralCreditExpired']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Direct Credit Expired:</td><td  width=130 align=left>$<?=$user_array['DirectCreditExpired']?></td>
										</tr>

					<?php } ?>
											<tr>
											<td width=100  class='details'>Gift Certifcate:</td><td  width=130 align=left><?=$user_array['payment_type']?></td>
										</tr>


								</table>




<!-- ###################Guest Details############################################ !-->

								<?php if ($this->step == 2) { ?>
									<?php if (!empty($followupArr[$retentionid][1][0]['follow_dates'])) { ?>

										<table width=420 class='subheads'>
											<tr>
												<td >60-89 Day Follow ups</td>
											</tr>
										</table>

										<table width=420 >

											<tr><td>Follow-up Date:&nbsp;<?=$followupArr[$retentionid][1][0]['follow_dates']?>&nbsp;&nbsp;Follow-up Type:&nbsp;<?=$followupArr[$retentionid][1][0]['follow_types']?></td><td></td></tr>

											<tr>

												<td VALIGN=TOP height="100" id='result_rep_01_01'>Results Date:&nbsp;&nbsp;&nbsp;&nbsp;<?=$followupArr[$retentionid][1][0]['result_dates']?>&nbsp;&nbsp;Comments:&nbsp;<?=$followupArr[$retentionid][1][0]['comment']?></td>
											</tr>




										</table>
										<?php } else { ?>

											<table width=420 class='subheads'>
											<tr>
												<td >60-89 Day Follow ups</td>
											</tr>
											</table>

											<table >

											<tr><td>No Follow ups have been reported</td><td></td></tr>

											<tr>	<td id='result_rep_01_01'>&nbsp;</td>	</tr>


											<tr><td>&nbsp;</td><td></td></tr>
											<tr><td id='result_rep_01_02'>	&nbsp;</td>	</tr>

										</table>
										<?php } ?>

									<?php } else { ?>
										<table><tr><td height=24>&nbsp;</td></tr></table>

									<?php } ?>



							</td>
					</table>
</td></tr></table>
				</td>