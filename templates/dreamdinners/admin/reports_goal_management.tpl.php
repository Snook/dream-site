<?php
$this->setScript('head', SCRIPT_PATH . '/admin/vendor/accounting.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_goal_management.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_expenses.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.stickyTableHeaders.js');
$this->setScriptVar('store_id = ' . $this->store_id . ';');
$this->setScriptVar('month = ' . $this->month . ';');
$this->setScriptVar('year = ' . $this->year . ';');
$this->setScriptVar('month_is_passed = ' . $this->monthIsPassed . ';');
$this->setScriptVar('month_is_future = ' . ($this->isFutureMonth ? 'true' : 'false') . ';');
$this->setScriptVar('hasPreviousMonthSessions = ' . $this->hasPreviousMonthSessions . ';');
$this->setScriptVar('hasFutureMonthSessions = ' . $this->hasFutureMonthSessions . ';');
$this->setScriptVar('isHomeOfficeAccess = ' . ($this->isHomeOfficeAccess ? 'true' : 'false') . ';');



$this->assign('topnav','reports');
$this->assign('page_title','Goal Management');
$this->setOnload('reports_goal_management_init();');
$this->setCSS(CSS_PATH . '/admin/admin-goal_tracking.css');

include $this->loadTemplate('admin/page_header.tpl.php');  ?>

	<form method="post" onsubmit="return _report_submitClick(this);">

		<div id="report_header" style="text-align:center;">
			<h3><?php echo $this->page_title;?></h3>
			<?php
			if (isset($this->form_session_list['store_html']) ) {
				echo '<strong>Store&nbsp;</strong>' .  $this->form_session_list['store_html'];
			}
			?>
			<div id="report_header">
				<?php echo '<strong>Menu Month&nbsp;</strong>' . $this->form_session_list['month_popup_html'] . "&nbsp";
				echo $this->form_session_list['year_field_001_html'] . "&nbsp";
				echo $this->form_session_list['report_submit_html']; ?>
			</div>

		</div>



		<div id="tracking_div" >
			<div id="taste_hostesses" style="text-align:center; width:30%; margin-top:12px; float:left;">
				<span class="subheads">Taste Host Signups</span><br />
				<textarea style="font-size:8pt;" rows="10" cols="42" readonly="readonly"><?php foreach($this->hostesses as $id => $data) { echo $data['name'] . " (" . CTemplate::dateTimeFormat($data['time'])  . ")\n"; } ?></textarea>

				<table style="width:100%; padding-right:20px;">
					<tr>
						<td style="text-align:right;">Total signed up for the month: </td><td style="text-align:center; width:60px;"><?php echo count($this->hostesses); ?></td>
					</tr>
					<tr>
						<td style="text-align:right">Taste signup goal for the month: </td><td style="text-align:center; width:60px;"><?php echo $this->form_session_list['taste_sessions_goal_html']; ?></td>
					</tr>
					<tr>
						<td style="text-align:right">Taste events held in current month: </td><td style="text-align:center; width:60px;"><?php echo $this->taste_event_count; ?></td>
					</tr>
				</table>
			</div>

			<div id="goals" style="text-align:center; float:right; width:70%;">
				<hr />
				<table style="width:100%">
					<tr>
						<td><span class="subheads"  style="font-weight:bold; color:#337A68; font-size:14px;">Lives Changed</span></td><td colspan="4" style="text-align:center;">
							<span class="subheads"  style="font-weight:bold; color:#337A68; font-size:14px;">Monthly Goal Setting</span><br />
						</td>
					</tr>
					<tr>
						<td style="min-width:300px; width:300px;">
							<table  style="width:100%; padding:0px; margin:0px;">
								<tr>
									<td class="sgs_upper_header">
									</td>
									<td class="sgs_upper_header">
										Store
									</td>
									<td class="sgs_upper_header">
										National Average
									</td>
									<td class="sgs_upper_header add_right_border">
										% Nat'l Avg
									</td>
								</tr>
								<tr>
									<td class="sgs_upper_header">
										Guests
									</td>
									<td class="session_cell">
										<?php echo $this->lives_changed['store_guests'];?>
									</td>
									<td class="session_cell">
										<?php echo $this->lives_changed['national_avg_guests'];?>
									</td>
									<td class="session_cell add_right_border">
										<?php echo $this->lives_changed['percent_of_avg_guests'];?>%
									</td>
								</tr>
								<tr>
									<td class="sgs_upper_header">
										Servings
									</td>
									<td class="session_cell">
										<?php echo $this->lives_changed['store_servings'];?>
									</td>
									<td class="session_cell">
										<?php echo $this->lives_changed['national_avg_servings'];?>
									</td>
									<td class="session_cell add_right_border">
										<?php echo $this->lives_changed['percent_of_avg_servings'];?>%
									</td>
								</tr>

							</table>

						</td>
						<td style="text-align:center; min-width:120px; width:100px;">
							<?php echo $this->form_session_list['gross_revenue_goal_html'];?><br />
							<span style="font-size:smaller;">Gross Revenue</span>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['avg_ticket_goal_html'];?><br />
							<span style="font-size:smaller;">Avg Ticket</span>
						</td>
						<td style="text-align:center; min-width:120px; width:150px;">
							<table>
								<tr><td colspan="2" style="font-weight:bold;">
										Guest Count Goals</td></tr>
								<tr>
									<td><?php echo $this->form_session_list['regular_guest_count_goal_html'];?></td><td style="font-size:smaller;">Regular Guests</td>
								</tr>
								<tr>
									<td><?php echo $this->form_session_list['taste_guest_count_goal_html'];?></td><td style="font-size:smaller;">Taste Guests</td>
								</tr>
								<tr>
									<td><?php echo $this->form_session_list['intro_guest_count_goal_html'];?></td><td style="font-size:smaller;">Starter Pack Guests</td>
								</tr>
							</table>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['finishing_touch_goal_html'];?><br />
							<span style="font-size:smaller;">Sides &amp; Sweets</span>
						</td>
					</tr>
				</table>
			</div>

			<div id="performance" style="text-align:center; float:right; width:70%;">
				<hr />
				<table style="width:100%">
					<tr><td></td><td colspan="4" style="text-align:center;"><span class="subheads" style="font-weight:bold; color:#337A68; font-size:14px;">Store Performance (excluding Taste)</span><br />
						</td></tr>
					<tr>
						<td style="min-width:300px; width:300px; text-align:right; font-weight:bold;">
							Session Goal:
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['sg_gross_revenue_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['sg_avg_ticket_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['sg_guests_goal_html'];?><br />
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['sg_finishing_touch_goal_html'];?>
						</td>
					</tr>

					<tr>
						<td style="min-width:300px; width:300px; text-align:right; font-weight:bold;">
							Average Actual MTD:
						</td>
						<td style="text-align:center">
							<?php echo $this->form_session_list['aam_gross_revenue_goal_html'];?>
						</td>
						<td style="text-align:center">
							<?php echo $this->form_session_list['aam_avg_ticket_goal_html'];?>
						</td>
						<td style="text-align:center">
							<?php echo $this->form_session_list['aam_guests_goal_html'];?>
						</td>
						<td style="text-align:center">
							<?php echo $this->form_session_list['aam_finishing_touch_goal_html'];?>
						</td>
					</tr>

					<tr>
						<td style="min-width:300px; width:300px; text-align:right; font-weight:bold;">
							Revised Goal for Remaining Sessions:
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['rgrs_gross_revenue_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['rgrs_avg_ticket_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['rgrs_guests_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['rgrs_finishing_touch_goal_html'];?>
						</td>
					</tr>

					<tr>
						<td style="min-width:300px; width:300px; text-align:right; font-weight:bold;">
							Month Actual Totals (excludes Taste):
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['mat_gross_revenue_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['mat_avg_ticket_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['mat_guests_goal_html'];?>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['mat_finishing_touch_goal_html'];?>
						</td>
					</tr>


				</table>
			</div>

			<div id="actuals" style="text-align:center; float:right; width:70%;">
				<hr />
				<table style="width:100%">
					<tr><td></td><td colspan="4" style="text-align:center;"><span class="subheads" style="font-weight:bold; color:#337A68; font-size:14px;">Monthly Actuals (including Taste)</span><br />
						</td></tr>

					<tr>
						<td style="min-width:300px; width:300px;">
							&nbsp;
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['ma_gross_revenue_goal_html'];?><br />
							<span style="font-size:smaller;">Gross Revenue</span>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['ma_avg_ticket_goal_html'];?><br />
							<span style="font-size:smaller;">Avg Ticket</span>
						</td>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['ma_guests_goal_html'];?><br />
							<span style="font-size:smaller;">Guests</span>
						</td>
						</td>
						<td style="text-align:center; min-width:120px; width:120px;">
							<?php echo $this->form_session_list['ma_finishing_touch_goal_html'];?><br />
							<span style="font-size:smaller;">Sides &amp; Sweets</span>
						</td>
						</td>
					</tr>
				</table>
			</div>


			<div id="main_table" style="width:100%; clear:both;">
				<span class="subheads">Metrics</span>

				<table id="session_summary_table" style="width:100%" border='0' cellpadding='0' cellspacing='0' >
					<thead>
					<tr>
						<th class="sgs_upper_header">Week</th>
						<th class="sgs_upper_header">Day</th>
						<th class="sgs_upper_header">Time</th>
						<th class="sgs_upper_header" colspan="2">Gross Revenue</th>
						<th class="sgs_upper_header add_bottom_border" colspan="2">Sides &amp;<br/>Sweets<br/>Sales</th>
						<th class="sgs_upper_header bold_left_border" colspan="3">New Guests</th>
						<th class="sgs_upper_header bold_left_border" colspan="3">Reacquired Guests</th>
						<th class="sgs_upper_header bold_left_border" colspan="3">Existing Guests</th>
						<th class="sgs_upper_header bold_left_border" colspan="3">Total Guests</th>
						<th class="sgs_upper_header add_bottom_border bold_left_border" rowspan="2">Session<br />Type</th>
						<th class="sgs_upper_header add_bottom_border add_right_border" rowspan="2">Session<br />Lead</th>
					</tr>
					<tr>
						<th class="sgs_lower_header"></th>
						<th class="sgs_lower_header"></th>
						<th class="sgs_lower_header"></th>
						<th class="sgs_lower_header">Daily Goal</th>
						<th class="sgs_lower_header">Actual</th>
						<th class="sgs_lower_header">Daily Goal</th>
						<th class="sgs_lower_header">Actual</th>
						<th class="sgs_lower_header bold_left_border">Count</th>
						<th class="sgs_lower_header">Sign Up</th>
						<th class="sgs_lower_header">Sign Up %</th>
						<th class="sgs_lower_header bold_left_border">Count</th>
						<th class="sgs_lower_header">Sign Up</th>
						<th class="sgs_lower_header">Sign Up %</th>
						<th class="sgs_lower_header bold_left_border">Count</th>
						<th class="sgs_lower_header">Sign Up</th>
						<th class="sgs_lower_header">Sign Up %</th>
						<th class="sgs_lower_header bold_left_border">Count</th>
						<th class="sgs_lower_header">Sign Up</th>
						<th class="sgs_lower_header">Sign Up %</th>
					</tr>
					</thead>
					<?php
					$allNonTasteSessionCount = 0;


					if (!empty($this->weeks))
					{
						CLog::Record("OLD\r\n" . print_r($this->weeks, true));

						$allTotal = 0;
						$allFTTotal = 0;
						$allFTTotalMinusTaste = 0;
						$allNewCountTotal = 0;
						$allNewSignupTotal = 0;
						$allReacCountTotal = 0;
						$allReacSignupTotal = 0;
						$allExCountTotal = 0;
						$allExSignupTotal = 0;
						$allAllCountTotal = 0;
						$allAllCountTotalMinusTaste = 0;
						$allAllSignupTotal = 0;
						$allSessionCount = 0;

						$allAllCompletedOrders = 0;
						$allNewCompletedOrders = 0;
						$allReacCompletedOrders = 0;
						$allExCompletedOrders = 0;
						$weekCounter = 1;

						foreach($this->weeks as $weekNumber => $thisWeek )
						{
							$newWeek = true;

							$weekTotal = 0;
							$weekFTTotal = 0;
							$weekNewCountTotal = 0;
							$weekNewSignupTotal = 0;
							$weekReacCountTotal = 0;
							$weekReacSignupTotal = 0;
							$weekExCountTotal = 0;
							$weekExSignupTotal = 0;
							$weekAllCountTotal = 0;
							$weekAllSignupTotal = 0;
							$weekSessionCount = 0;

							$weekAllCompletedOrders = 0;
							$weekNewCompletedOrders = 0;
							$weekReacCompletedOrders = 0;
							$weekExCompletedOrders = 0;


							$weekOOMTotal = 0;
							$weekOOMFTTotal = 0;
							$weekOOMNewCountTotal = 0;
							$weekOOMNewSignupTotal = 0;
							$weekOOMReacCountTotal = 0;
							$weekOOMReacSignupTotal = 0;
							$weekOOMExCountTotal = 0;
							$weekOOMExSignupTotal = 0;
							$weekOOMAllCountTotal = 0;
							$weekOOMAllSignupTotal = 0;
							$weekOOMSessionCount = 0;


							$weekOOMAllCompletedOrders = 0;
							$weekOOMNewCompletedOrders = 0;
							$weekOOMReacCompletedOrders = 0;
							$weekOOMExCompletedOrders = 0;



							$interWeekDayCount = 0;

							$hasOutOfMonthSessions = false;

							foreach($thisWeek as $date => $thisDay)
							{

								$newDay = true;

								$dayTotal = 0;
								$dayFTTotal = 0;
								$dayNewCountTotal = 0;
								$dayNewSignupTotal = 0;
								$dayReacCountTotal = 0;
								$dayReacSignupTotal = 0;
								$dayExCountTotal = 0;
								$dayExSignupTotal = 0;
								$dayAllCountTotal = 0;
								$dayAllSignupTotal = 0;
								$sessionCount = 0;
								$interWeekDayCount++;


								$dayAllCompletedOrders = 0;
								$dayNewCompletedOrders = 0;
								$dayReacCompletedOrders = 0;
								$dayExCompletedOrders = 0;


								$thisIsToday = false;
								$todaysClass = "";
								if (isset($thisDay['isToday']) && $thisDay['isToday'])
								{
									$thisIsToday = true;
									$todaysClass = "todays_row";
								}
								else if (isset($thisDay['first_future_session']) && $thisDay['first_future_session'])
								{
									echo '<tr><td colspan="21" class="todays_row add_left_border week_space add_right_border" >No sessions today - ' .  CTemplate::dateTimeFormat(date('Y-m-d H:i:s'), VERBOSE_DATE_NO_YEAR) . '</td></tr>';
								}


								foreach($thisDay as $sessionTime => &$thisSession)
								{

									if (!is_array($thisSession))
										continue;

									$overrideClass = "";
									if ($thisSession['out_of_month'])
									{
										$overrideClass = "out_of_month $todaysClass";
										$overrideClassSimple = "out_of_month";
										$hasOutOfMonthSessions = true;
									}
									else
									{
										$overrideClass = $todaysClass;
										$overrideClassSimple = "";
									}

									?>


									<tr id="wid_<?php echo $weekNumber ?>-did_<?php echo $date?>-sid_<?php echo $thisSession['session_id']?>" data-interweek_day_count="<?php echo $interWeekDayCount;?>">
										<?php

										if ($newWeek)
										{
											$newWeek = false;
											echo '<td class="add_top_border add_left_border week_space">' . $weekNumber . '</td>';
										}
										else
										{
											echo '<td class="week_space add_left_border"></td>';
										}


										if ($newDay)
										{
											$newDay = false;
											echo '<td class="' .$overrideClassSimple.' day_space add_top_border">' . $date . '</td>';
										}
										else
										{
											echo '<td class="' .$overrideClassSimple.' day_space"></td>';
										}

										$dayTotal += $thisSession['gross_revenue'];
										$dayFTTotal += $thisSession['ft_total'];


										$dayNewCountTotal += $thisSession['new_count'];
										$dayReacCountTotal += $thisSession['reac_count'];
										$dayExCountTotal += $thisSession['existing_count'];
										$dayAllCountTotal += $thisSession['total_count'];


										IF ($thisSession['isPast'])
										{
											$dayNewSignupTotal += $thisSession['new_sign_ups'];
											$dayReacSignupTotal += $thisSession['reac_sign_ups'];
											$dayAllSignupTotal += $thisSession['total_sign_ups'];
											$dayExSignupTotal += $thisSession['existing_sign_ups'];


											$dayAllCompletedOrders += $thisSession['total_count'];
											$dayNewCompletedOrders += $thisSession['new_count'];
											$dayReacCompletedOrders += $thisSession['reac_count'];
											$dayExCompletedOrders += $thisSession['existing_count'];

										}
										else
										{

											$thisSession['new_sign_ups'] = 0;
											$thisSession['reac_sign_ups'] = 0;
											$thisSession['total_sign_ups'] = 0;
											$thisSession['existing_sign_ups'] = 0;

										}


										if ($thisSession['out_of_month'])
										{
											$weekOOMTotal += $thisSession['gross_revenue'];
											$weekOOMFTTotal += $thisSession['ft_total'];
											$weekOOMNewCountTotal += $thisSession['new_count'];
											$weekOOMNewSignupTotal += $thisSession['new_sign_ups'];
											$weekOOMReacCountTotal += $thisSession['reac_count'];
											$weekOOMReacSignupTotal += $thisSession['reac_sign_ups'];
											$weekOOMExCountTotal += $thisSession['existing_count'];
											$weekOOMExSignupTotal += $thisSession['existing_sign_ups'];
											$weekOOMAllCountTotal += $thisSession['total_count'];
											$weekOOMAllSignupTotal += $thisSession['total_sign_ups'];


											$dayAllCompletedOrders += $thisSession['total_count'];
											$dayNewCompletedOrders += $thisSession['new_count'];
											$dayReacCompletedOrders += $thisSession['reac_count'];
											$dayExCompletedOrders += $thisSession['existing_count'];

											$weekOOMAllCompletedOrders += $thisSession['total_count'];
											$weekOOMNewCompletedOrders += $thisSession['new_count'];
											$weekOOMReacCompletedOrders += $thisSession['reac_count'];
											$weekOOMExCompletedOrders += $thisSession['existing_count'];


											$weekOOMSessionCount++;
										}
										else
										{

											$sessionCount++;
											$weekSessionCount++;
											$allSessionCount++;


											if ($thisSession['session_type'] != "Taste" && $thisSession['session_type'] != "Fundraiser")
												$allNonTasteSessionCount++;


											$weekTotal += $thisSession['gross_revenue'];
											$weekFTTotal += $thisSession['ft_total'];
											$weekNewCountTotal += $thisSession['new_count'];
											$weekNewSignupTotal += $thisSession['new_sign_ups'];
											$weekReacCountTotal += $thisSession['reac_count'];
											$weekReacSignupTotal += $thisSession['reac_sign_ups'];
											$weekExCountTotal += $thisSession['existing_count'];
											$weekExSignupTotal += $thisSession['existing_sign_ups'];
											$weekAllCountTotal += $thisSession['total_count'];
											$weekAllSignupTotal += $thisSession['total_sign_ups'];



											IF ($thisSession['isPast'])
											{
												$weekAllCompletedOrders += $thisSession['total_count'];
												$weekNewCompletedOrders += $thisSession['new_count'];
												$weekReacCompletedOrders += $thisSession['reac_count'];
												$weekExCompletedOrders += $thisSession['existing_count'];

												$allAllCompletedOrders += $thisSession['total_count'];
												$allNewCompletedOrders += $thisSession['new_count'];
												$allReacCompletedOrders += $thisSession['reac_count'];
												$allExCompletedOrders += $thisSession['existing_count'];

											}


											$allTotal += $thisSession['gross_revenue'];
											$allFTTotal += $thisSession['ft_total'];
											$allNewCountTotal += $thisSession['new_count'];
											$allNewSignupTotal += $thisSession['new_sign_ups'];
											$allReacCountTotal += $thisSession['reac_count'];
											$allReacSignupTotal += $thisSession['reac_sign_ups'];
											$allExCountTotal += $thisSession['existing_count'];
											$allExSignupTotal += $thisSession['existing_sign_ups'];
											$allAllCountTotal += $thisSession['total_count'];
											$allAllSignupTotal += $thisSession['total_sign_ups'];


											if ($thisSession['session_type'] != "Taste")
											{
												$allAllCountTotalMinusTaste += $thisSession['total_count'];
												$allFTTotalMinusTaste += $thisSession['ft_total'];
											}

										}

										if ($sessionCount == 1)
											$add_class = "add_top_border";
										else
											$add_class = "";

										if ($thisSession['out_of_month'])
											$add_class .= ' out_of_month';

										?>

										<td class="<?php echo $add_class;?> session_cell" id="ses_<?php ?><?php echo $thisSession['session_id'];?>"><a href="?page=admin_main&session=<?php echo $thisSession['session_id'];?>&back=?page=admin_reports_goal_management_v2"><?php echo $sessionTime;?></a></td>

										<?php if ($thisSession['session_type'] != "Taste" && $thisSession['session_type'] != "Fundraiser" ) { ?>

											<?php if ($thisSession['out_of_month']) { ?>

												<td class="<?php echo $add_class;?> session_cell" data-in_month="false"   data-is_taste="false"
													data-day_number="<?php echo $date ?>" data-week_number="<?php echo $weekNumber ?>" data-ord_pos="<?php echo $allNonTasteSessionCount ?>" id="dre_<?php echo $thisSession['session_id'];?>"  >$<?php echo CTemplate::number_format($thisSession['calc_revenue_goal'], 2);?></td>
											<?php } else { ?>

												<td class="<?php echo $add_class;?> session_cell" data-in_month="true"  data-is_taste="false"
													data-day_number="<?php echo $date ?>" data-week_number="<?php echo $weekNumber ?>" data-ord_pos="<?php echo $allNonTasteSessionCount ?>" id="dre_<?php echo $thisSession['session_id'];?>"  ></td>

											<?php } ?>

											<td class="<?php echo $add_class;?> session_cell"  data-ord_pos="<?php echo $allNonTasteSessionCount ?>" id="dac_<?php echo $thisSession['session_id'];?>" >$<?php echo CTemplate::number_format($thisSession['gross_revenue'], 2);?></td>
											<td class="<?php echo $add_class;?> session_cell" id="fttg_<?php echo $thisSession['session_id'];?>"></td>
											<td class="<?php echo $add_class;?> session_cell" id="ftt_<?php echo $thisSession['session_id'];?>">$<?php echo CTemplate::number_format($thisSession['ft_total'], 2);?></td>

										<?php  } else { ?>
											<td class="<?php echo $add_class;?> session_cell" data-day_number="<?php echo $date ?>" data-week_number="<?php echo $weekNumber ?>" data-is_taste ="true" id="dre_<?php echo $thisSession['session_id'];?>" data-in_month="<?php if ($thisSession['out_of_month']) echo 'false'; else echo 'true'; ?>" >$0.00</td>
											<td class="<?php echo $add_class;?> session_cell"  id="dac_<?php echo $thisSession['session_id'];?>" >$<?php echo CTemplate::number_format($thisSession['gross_revenue'], 2);?></td>
											<td class="<?php echo $add_class;?> session_cell" id="fttg_<?php echo $thisSession['session_id'];?>" >$0.00</td>
											<td class="<?php echo $add_class;?> session_cell" id="ftt_<?php echo $thisSession['session_id'];?>" >$<?php echo CTemplate::number_format($thisSession['ft_total'], 2);?></td>

										<?php } ?>


										<td class="<?php echo $add_class;?> bold_left_border session_cell "><?php echo $thisSession['new_count'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['new_sign_ups'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['new_sign_ups_%'];?>%</td>

										<td class="<?php echo $add_class;?> bold_left_border session_cell"><?php echo $thisSession['reac_count'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['reac_sign_ups'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['reac_sign_ups_%'];?>%</td>

										<td class="<?php echo $add_class;?> bold_left_border session_cell"><?php echo $thisSession['existing_count'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['existing_sign_ups'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['existing_sign_ups_%'];?>%</td>

										<?php if ($thisSession['session_type'] != "Taste") { ?>
											<td class="<?php echo $add_class;?> bold_left_border session_cell"  id="sgc_<?php echo $thisSession['session_id'];?>"><?php echo $thisSession['total_count'];?></td>
										<?php  } else { ?>
											<td class="<?php echo $add_class;?> bold_left_border session_cell"><?php echo $thisSession['total_count'];?></td>
										<?php } ?>

										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['total_sign_ups'];?></td>
										<td class="<?php echo $add_class;?> session_cell"><?php echo $thisSession['total_sign_ups_%'];?>%</td>

										<td class="<?php echo $add_class;?> bold_left_border session_cell"><?php echo $thisSession['session_type'];?></td>
										<td class="<?php echo $add_class;?> add_right_border session_cell"><select id="sl_<?php echo $thisSession['session_id'];?>" name="sl_<?php echo $thisSession['session_id'];?>">
												<option value="0" <?php if (empty($thisSession["session_lead"])) echo 'selected="selected"'?>>Please Choose</option>
												<?php foreach($this->leads as $id => $name) { ?>
													<option value="<?php echo $id?>" <?php if ($id == $thisSession['session_lead'])  echo 'selected="selected"'?>><?php echo $name?></option>
												<?php } ?>
											</select>

										</td>

									</tr>

								<?php } ?>

								<!--  Day Summary -->


								<?php


								$newSignUpPercentTotal = CTemplate::divide_and_format($dayNewSignupTotal * 100, $dayNewCompletedOrders, 2);
								$reacSignUpPercentTotal = CTemplate::divide_and_format($dayReacSignupTotal * 100, $dayReacCompletedOrders, 2);
								$exSignUpPercentTotal = CTemplate::divide_and_format($dayExSignupTotal * 100, $dayExCompletedOrders, 2);
								$allSignUpPercentTotal = CTemplate::divide_and_format($dayAllSignupTotal * 100, $dayAllCompletedOrders, 2);

								?>


								<tr id="wid_<?php echo $weekNumber ?>-did_<?php echo $date?>"  data-interweek_day_count="<?php echo $interWeekDayCount;?>">

									<td class="add_left_border week_space">&nbsp;</td>
									<td class="<?php echo $overrideClassSimple ?> day_space add_bottom_border"><div style="display:none;" id="dn_<?php echo $weekNumber?>-<?php echo $date ?>"><?php echo $date ?></div>
										<div  class="disc_control"  id="dc_<?php echo $weekNumber?>-<?php echo $date ?>">Hide</div>
									</td>
									<td class="<?php echo $overrideClass ?> small_label hide_left_border day_space add_bottom_border">
										<?php if ($thisIsToday) echo "Today's";?>
										Total</td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><span id="dce_<?php echo $date ?>-<?php echo $weekNumber?>"><?php echo CTemplate::number_format(0,2);?></span></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border">$<?php echo CTemplate::number_format($dayTotal,2);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border" id="ftdtg_<?php echo $date ?>-<?php echo $weekNumber?>">$0.00</td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border">$<?php echo CTemplate::number_format($dayFTTotal,2);?></td>
									<td class="<?php echo $overrideClass ?> bold_left_border day_space add_bottom_border"><?php echo CTemplate::number_format($dayNewCountTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo CTemplate::number_format($dayNewSignupTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo $newSignUpPercentTotal?>%</td>
									<td class="<?php echo $overrideClass ?> bold_left_border day_space add_bottom_border"><?php echo CTemplate::number_format($dayReacCountTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo CTemplate::number_format($dayReacSignupTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo $reacSignUpPercentTotal;?>%</td>
									<td class="<?php echo $overrideClass ?> bold_left_border day_space add_bottom_border"><?php echo CTemplate::number_format($dayExCountTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo CTemplate::number_format($dayExSignupTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo $exSignUpPercentTotal;?>%</td>
									<td class="<?php echo $overrideClass ?> bold_left_border day_space add_bottom_border"><?php echo CTemplate::number_format($dayAllCountTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo CTemplate::number_format($dayAllSignupTotal,0);?></td>
									<td class="<?php echo $overrideClass ?> day_space add_bottom_border"><?php echo $allSignUpPercentTotal;?>%</td>
									<td class="<?php echo $overrideClass ?> bold_left_border day_space add_bottom_border"><?php echo $sessionCount; ?></td>
									<td class="<?php echo $overrideClass ?> add_right_border day_space add_bottom_border"></td>
								</tr>

								<?php
							} ?>

							<!--  Week Summary -->

							<?php


							$newSignUpPercentTotal = CTemplate::divide_and_format($weekNewSignupTotal * 100, $weekNewCompletedOrders, 2);
							$reacSignUpPercentTotal = CTemplate::divide_and_format($weekReacSignupTotal * 100, $weekReacCompletedOrders, 2);
							$exSignUpPercentTotal = CTemplate::divide_and_format($weekExSignupTotal * 100, $weekExCompletedOrders, 2);
							$allSignUpPercentTotal = CTemplate::divide_and_format($weekAllSignupTotal * 100, $weekAllCompletedOrders, 2);

							?>


							<?php
							$rowTitle = "Week $weekCounter Total";
							$weekCounter += 1;

							$totalID = " id=\"wkt_" . $weekNumber . "\" ";
							if ($hasOutOfMonthSessions)
							{
								$rowTitle = "Calendar Week Total";
								$totalID = "";
							}
							?>



							<tr>
								<td class="add_left_border week_space "><div id="wcan_<?php echo $weekNumber ?>" style="display:none;" ><?php echo $weekNumber ?></div></td>
								<td class="week_space hide_left_border add_top_border">&nbsp;</td>
								<td class="small_label hide_left_border week_space add_top_border"><?php echo $rowTitle ?></td>
								<td class="week_space add_top_border"><span id="wcec_<?php echo $weekNumber ?>"><?php echo CTemplate::number_format(0,2);?></span></td>
								<td class="week_space add_top_border">$<span <?php echo $totalID ?>><?php echo CTemplate::number_format($weekTotal,2);?></span></td>
								<td class="week_space add_top_border" id="ftwtgc_<?php echo $weekNumber?>">$0.00</td>
								<td class="week_space add_top_border">$<?php echo CTemplate::number_format($weekFTTotal,2);?></td>
								<td class="bold_left_border week_space add_top_border"><?php echo CTemplate::number_format($weekNewCountTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo CTemplate::number_format($weekNewSignupTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo $newSignUpPercentTotal?>%</td>
								<td class="bold_left_border week_space add_top_border"><?php echo CTemplate::number_format($weekReacCountTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo CTemplate::number_format($weekReacSignupTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo $reacSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space add_top_border"><?php echo CTemplate::number_format($weekExCountTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo CTemplate::number_format($weekExSignupTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo $exSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space add_top_border"><?php echo CTemplate::number_format($weekAllCountTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo CTemplate::number_format($weekAllSignupTotal,0);?></td>
								<td class="week_space add_top_border"><?php echo $allSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space add_top_border"><?php echo $weekSessionCount; ?></td>
								<td class="add_right_border week_space add_top_border"></td>
							</tr>

							<?php if ($hasOutOfMonthSessions) {


							$newSignUpPercentTotal = CTemplate::divide_and_format(($weekNewSignupTotal + $weekOOMNewSignupTotal) * 100, ($weekNewCompletedOrders + $weekOOMNewCompletedOrders), 2);
							$reacSignUpPercentTotal = CTemplate::divide_and_format(($weekReacSignupTotal + $weekOOMReacSignupTotal ) * 100, ($weekReacCompletedOrders + $weekOOMReacCompletedOrders), 2);
							$exSignUpPercentTotal = CTemplate::divide_and_format(($weekExSignupTotal + $weekOOMExSignupTotal ) * 100, ($weekExCompletedOrders + $weekOOMExCompletedOrders), 2);
							$allSignUpPercentTotal = CTemplate::divide_and_format(($weekAllSignupTotal + $weekOOMAllSignupTotal) * 100, ($weekAllCompletedOrders + $weekOOMAllCompletedOrders), 2);
							$totalID = " id=\"wkt_" . $weekNumber . "\" ";


							?>


							<tr>
								<td class="add_left_border week_space"></td>
								<td class="week_space hide_left_border ">&nbsp;</td>
								<td class="small_label hide_left_border week_space ">Week Total</td>
								<td class="week_space"><span id="wce_<?php echo $weekNumber ?>"><?php echo CTemplate::number_format(0,2);?></span></td>
								<td class="week_space">$<span <?php echo $totalID ?>><?php echo CTemplate::number_format($weekTotal + $weekOOMTotal,2);?></span></td>
								<td class="week_space" id="ftwtg_<?php echo $weekNumber?>">$0.00</td>
								<td class="week_space">$<?php echo CTemplate::number_format($weekFTTotal + $weekOOMFTTotal,2);?></td>
								<td class="bold_left_border week_space "><?php echo CTemplate::number_format($weekNewCountTotal + $weekOOMNewCountTotal,0);?></td>
								<td class="week_space"><?php echo CTemplate::number_format($weekNewSignupTotal + $weekOOMNewSignupTotal,0);?></td>
								<td class="week_space"><?php echo $newSignUpPercentTotal?>%</td>
								<td class="bold_left_border week_space "><?php echo CTemplate::number_format($weekReacCountTotal + $weekOOMReacCountTotal,0);?></td>
								<td class="week_space"><?php echo CTemplate::number_format($weekReacSignupTotal + $weekOOMReacSignupTotal,0);?></td>
								<td class="week_space"><?php echo $reacSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space "><?php echo CTemplate::number_format($weekExCountTotal + $weekOOMExCountTotal,0);?></td>
								<td class="week_space"><?php echo CTemplate::number_format($weekExSignupTotal + $weekOOMExSignupTotal,0);?></td>
								<td class="week_space"><?php echo $exSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space "><?php echo CTemplate::number_format($weekAllCountTotal + $weekOOMAllCountTotal,0);?></td>
								<td class="week_space"><?php echo CTemplate::number_format($weekAllSignupTotal + $weekOOMAllSignupTotal,0);?></td>
								<td class="week_space"><?php echo $allSignUpPercentTotal;?>%</td>
								<td class="bold_left_border week_space "><?php echo $weekSessionCount + $weekOOMSessionCount; ?></td>
								<td class="add_right_border week_space "></td>
							</tr>



						<?php } ?>


							<?php
							$adjustedWeeknumber = $weekNumber;

							if ($weekNumber == 53)
								$adjustedWeeknumber = 0;

							$foodCostValue = "0.00";
							if (isset($this->food_costs[intval($adjustedWeeknumber)]))
								$foodCostValue = $this->food_costs[intval($adjustedWeeknumber)];

							$laborCostValue = "0.00";
							if (isset($this->labor_costs[intval($adjustedWeeknumber)]))
								$laborCostValue = $this->labor_costs[intval($adjustedWeeknumber)];


							?>


							<tr>
								<td class="add_left_border week_space"><div class="disc_control" id="wc_<?php echo $weekNumber ?>">Hide</div> </td>
								<td class="week_space hide_left_border ">&nbsp;</td>
								<td class="small_label hide_left_border week_space ">Food Cost&nbsp;</td>
								<td class="week_space ">

									<input class="cform_input gt_input" onchange="calculatePage();" type="button" name="fc_<?php echo $weekNumber . "_" . $this->year?>"
										   id="fc_<?php echo $weekNumber ."_" . $this->year?>" value="<?php echo $foodCostValue; ?>" />

								</td>
								<td class="small_label hide_left_border week_space ">Food Cost %&nbsp;</td>
								<td class="week_space "><span id="fcp_<?php echo $weekNumber ."_" . $this->year?>" value="<?php echo $foodCostValue; ?>"><?php echo CTemplate::number_format(42,2);?></span>%</td>

								<td colspan="15" class="week_space add_right_border"></td>
							</tr>

							<tr>
								<td class="add_left_border week_space">&nbsp;</td>
								<td class="week_space hide_left_border ">&nbsp;</td>


								<td class="small_label hide_left_border week_space ">Labor Cost&nbsp;</td>
								<td class="week_space ">
									<input class="cform_input gt_input" onchange="calculatePage();" type="button" name="lc_<?php echo $weekNumber . "_" . $this->year?>" id="lc_<?php echo $weekNumber . "_" . $this->year?>" value="<?php echo $laborCostValue; ?>"/>
								</td>
								<td class="small_label hide_left_border week_space ">Labor Cost %&nbsp;</td>
								<td class="week_space "><span id="lcp_<?php echo $weekNumber ."_" . $this->year?>" value="<?php echo $laborCostValue; ?>"><?php echo CTemplate::number_format(42,2);?></span>%</td>


								<td colspan="15" class="week_space add_right_border"></td>
							</tr>




							<tr>
								<td colspan="21" class="add_left_border week_space add_right_border">&nbsp;</td>
							</tr>


							<?php


						}

						$newSignUpPercentTotal = CTemplate::divide_and_format($allNewSignupTotal * 100, $allNewCompletedOrders, 2);
						$reacSignUpPercentTotal = CTemplate::divide_and_format($allReacSignupTotal * 100, $allReacCompletedOrders, 2);
						$exSignUpPercentTotal = CTemplate::divide_and_format($allExSignupTotal * 100, $allExCompletedOrders, 2);
						$allSignUpPercentTotal = CTemplate::divide_and_format($allAllSignupTotal * 100, $allAllCompletedOrders, 2);


						?>

						<tr>
							<td class="add_left_border all_space">&nbsp;</td>
							<td class="all_space hide_left_border ">&nbsp;</td>
							<td class="small_label hide_left_border all_space ">Menu Month Total</td>
							<td class="all_space " id="calendar_month_goal"></td>
							<td class="all_space">$<?php echo CTemplate::number_format($allTotal,2);?></td>
							<td class="all_space" id="FT_months_total_goal">$0.00</td>
							<td class="all_space" id="FT_months_total">$<?php echo CTemplate::number_format($allFTTotal,2);?></td>
							<td class="bold_left_border all_space "><?php echo CTemplate::number_format($allNewCountTotal,0);?></td>
							<td class="all_space "><?php echo CTemplate::number_format($allNewSignupTotal,0);?></td>
							<td class="all_space "><?php echo $newSignUpPercentTotal?>%</td>
							<td class="bold_left_border all_space "><?php echo CTemplate::number_format($allReacCountTotal,0);?></td>
							<td class="all_space "><?php echo CTemplate::number_format($allReacSignupTotal,0);?></td>
							<td class="all_space "><?php echo $reacSignUpPercentTotal;?>%</td>
							<td class="bold_left_border all_space "><?php echo CTemplate::number_format($allExCountTotal,0);?></td>
							<td class="all_space "><?php echo CTemplate::number_format($allExSignupTotal,0);?></td>
							<td class="all_space "><?php echo $exSignUpPercentTotal;?>%</td>
							<td class="bold_left_border all_space"  id="guest_count_months_total"><?php echo CTemplate::number_format($allAllCountTotal,0);?></td>
							<td class="all_space "><?php echo CTemplate::number_format($allAllSignupTotal,0);?></td>
							<td class="all_space "><?php echo $allSignUpPercentTotal;?>%</td>
							<td class="bold_left_border all_space " id="session_count"><?php echo $allSessionCount; ?></td>
							<td class="add_right_border all_space "></td>
						</tr>


						<tr>
							<td class="add_left_border all_space">&nbsp;</td>
							<td class="all_space hide_left_border ">&nbsp;</td>

							<td class="small_label hide_left_border all_space ">Membership Fee Revenue</td>
							<td class="all_space"></td>
							<td id="membership_fee_revenue" class="all_space">$<?php echo CTemplate::number_format($this->membership_fee_revenue,2);?></td>
							<td class="all_space"></td>
							<td colspan="19" class="all_space add_right_border"></td>
						</tr>

						<tr>
							<td class="add_left_border all_space">&nbsp;</td>
							<td class="all_space hide_left_border ">&nbsp;</td>

							<td class="small_label hide_left_border all_space ">Total Revenue</td>
							<td class="all_space"></td>
							<td  id="food_revenue_plus_membership_fees" class="all_space">$<?php echo CTemplate::number_format($allTotal + $this->membership_fee_revenue,2);?></td>
							<td class="all_space"></td>
							<td colspan="19" class="all_space add_right_border"></td>
						</tr>


						<tr>
							<td class="add_left_border all_space">&nbsp;</td>
							<td class="all_space hide_left_border ">&nbsp;</td>

							<td class="small_label hide_left_border all_space ">Food Cost</td>
							<td class="all_space" id="total_food_cost"></td>
							<td class="small_label hide_left_border all_space ">Food Cost %</td>
							<td class="all_space" id="total_food_cost_percentage"></td>

							<td colspan="19" class="all_space add_right_border"></td>
						</tr>

						<tr>
							<td class="add_left_border all_space">&nbsp;</td>
							<td class="all_space hide_left_border ">&nbsp;</td>

							<td class="small_label hide_left_border all_space ">Labor Cost</td>
							<td class="all_space" id="total_labor_cost"></td>
							<td class="small_label hide_left_border all_space ">Labor Cost %</td>
							<td class="all_space" id="total_labor_cost_percentage"></td>


							<td colspan="19" class="all_space add_right_border"></td>
						</tr>



						<?php
					} ?>


				</table>

				<input type="hidden" id="nonTasteSessionCount" name="nonTasteSessionCount" value="<?php echo $allNonTasteSessionCount;?>" />
				<input type="hidden" id="isCurrentMonth" name="isCurrentMonth" value="<?php echo ($this->isCurrentMonth ? "true" : "false");?>" />
				<input type="hidden" id="guestCountTotalMinusTaste" name="guestCountTotalMinusTaste" value="<?php echo $allAllCountTotalMinusTaste;?>" />
				<input type="hidden" id="allFTTotalMinusTaste" name="allFTTotalMinusTaste" value="<?php echo $allFTTotalMinusTaste;?>" />

			</div>
		</div>
	</form>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>