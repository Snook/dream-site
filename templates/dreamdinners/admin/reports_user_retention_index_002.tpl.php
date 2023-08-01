<?php require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php'); ?>
<!-- ###################Guest Details############################################ !-->
<div id='header_<?=$key?>'>
<table  class='ActiveGuestMainOutline'  border="0" ><tr><td>
	<table  border=0  class='indicators'><tr>

	<td width=20 align="left" >
	<?php
		echo '<img src="'  . ADMIN_IMAGES_PATH . '/icon/bullet_toggle_plus.png" id="imgpls_' . $key . '" onClick="expandcontent(this, ' . "'" . $key . "'" .')" />';
	?>
	</td>

	<td align="left" width=140>
		<b>Name:</b><br/>
		<?php

			if (!empty($user_array['firstname']) && !empty($user_array['lastname']))
				echo $user_array['lastname'] . ',&nbsp;' . $user_array['firstname'];
			else if (empty($user_array['firstname']) && !empty($user_array['lastname']))
				echo $user_array['lastname'];
			else if (!empty($user_array['firstname']) && empty($user_array['lastname']))
				echo $user_array['firstname'];

		?>
	</td>

	<td align="left" width=100>
		<b>Primary Phone:</b><br/>
		<?php
				if (!empty($user_array['telephone_1']))
						echo $user_array['telephone_1'];
		?>
	</td>


	<td align="left" width=100>
		<b>Secondary Phone:</b><br/>
		<?php
				if (!empty($user_array['telephone_2']))
						echo $user_array['telephone_2'];
		?>
	</td>

	<td align="left" width=100>
		<b>Primary Call Time:</b><br/>
		<?php
				if (!empty($user_array['telephone_1_call_time']))
						echo $user_array['telephone_1_call_time'];
		?>
	</td>

	<td align="left" width=140>
		<b>Last/Upcoming Session:</b><br/>
		<?php
				if (!empty($user_array['session_start']))
						echo $user_array['session_start'];
		?>
	</td>

	<td width=100 align='center'>
		<b>Days Inactive:</b><br/>
		<?php
				if (!empty($user_array['days_inactive']))
						echo $user_array['days_inactive'];
		?>
	</td>


	<td width=80 align='center' id='has_follow_up_<?=$key?>'>
	<b>Has Follow-Up</b><br/>
	<?php
		if (empty($followup_id_001))
					echo '<img id="' . $key . '_check_image" src="'  . ADMIN_IMAGES_PATH . '/check_no.gif"  />';
				else
					echo '<img id="' . $key . '_check_image" src="'  . ADMIN_IMAGES_PATH . '/check_yes.gif"  />';

	?>
	</td>

	</tr></table>
</div>
<div id='master_<?=$key?>' class='masterfield'>



				<table  class='ActiveGuestSubHeadActive'   border=0 class='guest' >
					<tr >
							<td>
								<table  width=100% class='subheads'>
										<tr>
											<td  >
												Guest Information
											</td>
										</tr>
								</table>

								<table style="padding-bottom: 22px;" width=100% border=0 >
										<tr>
											<td width=100 class='details'>Guest Name:</td><td class='details_rows'  width=120 >
												<?php
												if (!empty($user_array['firstname']) && !empty($user_array['lastname']))
													echo $user_array['lastname'] . ',&nbsp;' . $user_array['firstname'];
												else if (empty($user_array['firstname']) && !empty($user_array['lastname']))
													echo $user_array['lastname'];
												else if (!empty($user_array['firstname']) && empty($user_array['lastname']))
													echo $user_array['firstname'];
												?>
											</td>
										</tr>

										<tr>
											<td width=100  class='details'>ID:</td>
											<td  width=120 class='details_rows'  align=left><?=$user_array['user_id']?></td>
										</tr>

										<tr>
											<td width=100 class='details'>Primary Phone:</td><td  class='details_rows' width=120><?=$user_array['telephone_1']?></td>
										</tr>

										<tr>
											<td width=100 class='details'>Secondary Phone:</td><td class='details_rows'  width=120><?=$user_array['telephone_2']?></td>
										</tr>


										<tr>
											<td width=100 class='details'>Primary Call Time:</td><td class='details_rows' width=120><?=$user_array['telephone_1_call_time']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Email:</td><td  class='details_rows'  width=120 align=left><?=$user_array['primary_email']?></td>
										</tr>

										<tr>
											<td width=100 class='details'>Address:</td>
											<td class='details_rows'  width=200>
											<?=$user_array['address_line1']?><br/><?=$user_array['city']?>,&nbsp;<?=$user_array['state_id']?>&nbsp;<?=$user_array['postal_code']?>

										</td>
										</tr>


											<tr>
											<td width=80  class='details'>Last/Upcoming Session:</td><td class='details_rows'  width=120 align=left><?=$user_array['session_start']?></td>
										</tr>

										<tr>
											<td width=80  class='details'>Last Session Type:</td><td class='details_rows'  width=120 align=left><?=$user_array['session_type']?></td>
										</tr>


										<tr>
											<td width=80  class='details'>Attended:</td><td class='details_rows'  width=120 align=left><?=$user_array['booking_count']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Days Inactive:</td><td  class='details_rows' width=120 align=left><?=$user_array['days_inactive']?></td>
										</tr>

										<tr>
											<td width=100  class='details'>Loyalty Status:</td><td class='details_rows' width=130 align=left>Status : <i><?=CDreamRewardsHistory::$DRDescriptiveNameMap[$user_array['dream_reward_status']]?></i><br />Level : <i><?=CDreamRewardsHistory::shortLevelDesc($user_array['dream_reward_level'])?></i></td>
										</tr>

										<tr>
											<td width=80  class='details'>Gift Certifcate:</td><td class='details_rows'  width=250 align=left><?=$user_array['payment_type']?></td>
										</tr>

										<tr>
											<td height="134" width=80 >&nbsp;</td><td  class='details_rows'  width=120 align=left>&nbsp;</td>
										</tr>

								</table>

							</td>






								<td>

								<table   width="100%" class='subheads'>
										<tr>
											<td  >
												60-89 Day Follow-up and Result
											</td>
										</tr>
								</table>

								<table width=100%>
										<?php
											$follow_up_array = isset($this->follow_up_array) ? $this->follow_up_array : null;
										?>

										<tr>
											<td   class='details'>Follow-up Date:</td><td class='details_rows'  width=120 align=left>

											<?php
											if (!empty($follow_up_array[$user_array['user_id']][$key][1][0]['follow_dates']))
											echo $follow_up_array[$user_array['user_id']][$key][1][0]['follow_dates'] ;
											else echo  'n/a';
											?>
											</td>
										</tr>

										<tr>
											<td   class='details'>Follow-up Action:</td><td  class='details_rows'  width=120 align=left>
											<?php
											if (!empty($follow_up_array[$user_array['user_id']][$key][1][0]['follow_types']))
											echo $follow_up_array[$user_array['user_id']][$key][1][0]['follow_types'] ;
											else echo  'n/a';
											?>

										</tr>

										<tr>
											<td class='details'>Result Date:</td><td class='details_rows'   width=120 align=left>
												<?php
												if (!empty($follow_up_array[$user_array['user_id']][$key][1][0]['result_dates']))
												echo $follow_up_array[$user_array['user_id']][$key][1][0]['result_dates'] ;
												else echo  'n/a';
												?>
											</td>
										</tr>

										<tr>
											<td VALIGN=TOP class='details' >Notes:</td><td class='details_rows'  height=140  width=250 VALIGN=TOP align=left>
											<?php
											if (!empty($follow_up_array[$user_array['user_id']][$key][1][0]['comment']))
											echo $follow_up_array[$user_array['user_id']][$key][1][0]['comment'] ;
											else echo  'n/a';
											?>
											</td>
										</tr>



								</table>


								<table  width=100% class='subheads'>
										<tr>
											<td  >
												90-119 Day Follow-up and Result
											</td>
										</tr>
								</table>

								<table  width=100%>

										<tr>
											<td class='details'>Follow-up Date:</td><td  class='details_rows'  width=120 align=left>
											<?php
											if (!empty($follow_up_array[$user_array['user_id']][$key][2][0]['follow_dates']))
											echo $follow_up_array[$user_array['user_id']][$key][2][0]['follow_dates'] ;
											else echo  'n/a';
											?>


											</td>
										</tr>

										<tr>
											<td  class='details'>Follow-up Action:</td><td  class='details_rows'  width=120 align=left>
											<?php
											if (!empty($follow_up_array[$user_array['user_id']][$key][2][0]['follow_types']))
											echo $follow_up_array[$user_array['user_id']][$key][2][0]['follow_types'] ;
											else echo  'n/a';
											?>
											</td>
										</tr>

										<tr>
											<td class='details'>Result Date:</td><td  class='details_rows'  width=120 align=left>
											<?php
												if (!empty($follow_up_array[$user_array['user_id']][$key][2][0]['result_dates']))
												echo $follow_up_array[$user_array['user_id']][$key][2][0]['result_dates'] ;
												else echo  'n/a';
												?>
											</td>
										</tr>

										<tr>
											<td  VALIGN=TOP class='details'>Notes:</td><td class='details_rows'  height=140  width=250 VALIGN=TOP align=left>
											<?php
												if (!empty($follow_up_array[$user_array['user_id']][$key][2][0]['comment']))
												echo $follow_up_array[$user_array['user_id']][$key][2][0]['comment'] ;
												else echo  'n/a';
												?>
											</td>
										</tr>



								</table>





							</td>


						</tr>

					</table>



		</div>

	</td></tr></table>