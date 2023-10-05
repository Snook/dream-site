<?php $this->assign('page_title','Guest PlatePoints History'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/user_plate_points.min.js'); ?>
<?php $this->setScriptVar("numConfirmableOrders = " .$this->numConfirmableOrders . ";");  ?>
<?php $this->setOnLoad("PlatePoints_init('".$this->userObj->id."');");?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h2 style="margin-bottom: 10px;">PlatePoints History for <a href="/backoffice/user_details?id=<?php echo $this->userObj->id; ?>"><?php echo $this->userObj->firstname; ?> <?php echo $this->userObj->lastname; ?></a></h2>


	<div class="collapse" id="plate_points_head">
  	<!-- Hidden until redesign is available -->

		<div style="width:300px; float:left;">
			<img src="<?php echo IMAGES_PATH; ?>/style/PlatePoints/PlatePoints-logo-status-bar.png" alt="PlatePoints" style="width: 225px; height: 57px;" />

			<div>
				<div class="avatar_div">
					<img id="pp_user_avatar" src="<?php echo IMAGES_PATH; ?>/style/PlatePoints/placeholder_avatar.png" alt="Avatar" />
				</div>
				<div style="float: left; width: 190px; padding-top:10px;">
					<div style="font-weight: bold; color: #337A68;"><?php echo $this->userObj->firstname; ?> <?php echo $this->userObj->lastname; ?></div>
					<div>Points: <span data-pp_user_lifetime_points="<?php echo $this->userObj->id; ?>"><?php echo number_format($this->userObj->PlatePointsData['lifetime_points']); ?></span></div>
					<div>Level: <span data-pp_user_level_title="<?php echo $this->userObj->id; ?>"><?php echo $this->userObj->PlatePointsData['current_level']['title']; ?></span></div>
				</div>
				<div class="clear"></div>
			</div>
			<div></div>

		</div>

		<div id="pp_status_bar">
			<img class="pp_user_badge" data-pp_user_badge="<?php echo $this->userObj->id; ?>" data-pp_user_badge_size="119" src="<?php echo IMAGES_PATH; ?>/style/PlatePoints/badge-<?php echo $this->userObj->PlatePointsData['current_level']['image']; ?>-119x119.png" alt="PlatePoints" class="img_valign" />
			<div id="pp_progressbar" data-percent_complete="<?php echo  $this->userObj->PlatePointsData['current_level']['percent_complete']; ?>"><div id="pp_progresslabel"><?php echo ($this->userObj->PlatePointsData['current_level']['percent_complete']) ? $this->userObj->PlatePointsData['current_level']['percent_complete'] : 1; ?>%</div></div>
			<img class="pp_next_user_badge" data-pp_next_user_badge="<?php $this->userObj->id; ?>" data-pp_next_user_badge_size="57" src="<?php echo IMAGES_PATH; ?>/style/PlatePoints/badge-<?php echo $this->userObj->PlatePointsData['next_level']['image']; ?>-57x57.png" alt="PlatePoints" class="img_valign" />
		</div>

		<div class="clear"></div>

	</div>



<?php if (isset($this->points_form)) { ?>

	<form id="points_form" name="points_form" method="post">
		<div style="border:green solid 1px; background-color:#F1E8D8; padding:2px; margin:4px;">
			<h3>Add PlatePoints</h3>
			<div>
				<table>
					<tr>
						<td style="text-align:right;">
							Points Amount
						</td>
						<td>
							<?php echo $this->points_form['points_amount_html'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="suppress_DD_reward">Suppress Dinner Dollar Rewards for these Points</label> <?php echo $this->points_form['suppress_DD_reward_html'];?>
						</td>
					</tr>
					<tr>
						<td style="text-align:right;">
							Comment (Visible to Guest and Store)
						</td>
						<td>
							<?php echo $this->points_form['comments_html'];?>
						</td>
					</tr>
					<tr>
						<td style="text-align:right;">
							Admin Note (Visible to Store)
						</td>
						<td>
							<?php echo $this->points_form['admin_comments_html'];?>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td style="text-align:right;">
							<?php echo $this->points_form['add_points_html'];?>
						</td>
					</tr>

				</table>
			</div>
		</div>
	</form>

	<form id="convert_form" name="convert_form" method="post">
		<div style="border:green solid 1px; background-color:#F1E8D8; padding:2px; margin:4px; height:40px;">
			<div style="float:left;"><h3>Convert available Points to Dinner Dollars</h3></div>
			<div style="float:right;">
				<?php echo $this->convert_form['convert_points_html'];?>
			</div>
		</div>
	</form>
<?php  } ?>


<div id="dinner_dollar_history" style="border:green solid 1px; background-color:#F1E8D8; padding:2px; margin:4px;">
	<div ><h3>Dinner Dollar History</h3></div>
	<div style="text-align:center">
		<span style="font-size:12pt; font-weight:bold">Total Available Dinner Dollars: $<?php echo number_format($this->totalCredit, 2);?></span>
		<ul style="list-style-type: none;">
			<?php foreach($this->dd_history as $entry)
			{
				switch ($entry['state'])
				{
					case 'AVAILABLE':
						if($entry['amount'] > 0){
							echo '<li>$'.$entry['amount'] .' available. Expires on '.CTemplate::dateTimeFormat($entry['expires'], MONTH_DAY_YEAR).'</li>';
						}
						break;
				}
			}?>
		</ul>
	</div>
	<div id="dinner_dollar_history_table">
	<?php include $this->loadTemplate('admin/subtemplate/user_dinner_dollars/user_dinner_dollars_table.tpl.php'); ?>
	</div>
</div>


<div style="border:green solid 1px; background-color:#F1E8D8; padding:2px; margin:4px;">
	<div ><h3>Plate Points History</h3></div>
	<div style="text-align:center">
		<span style="font-size:12pt; font-weight:bold">Total PlatePoints: <?php echo number_format($this->plate_point_summary['unconverted_points']);?></span><br>
		<span style="font-size:12pt; font-weight:bold">Pending PlatePoints: <?php echo number_format($this->plate_point_summary['pending_points']);?></span>
	</div>
	<div id="history">
	<?php include $this->loadTemplate('admin/subtemplate/user_plate_points/user_plate_points_table.tpl.php'); ?>
	</div>
</div>

<?php if ($this->userObj->membershipData['enrolled']) { ?>
	<p class="text-center">Guest is currently enrolled in <a href="/backoffice/user_membership?id=<?php echo $this->userObj->id; ?>">Meal Prep+</a> and ineligible for the PlatePoints program.</p>
<?php } else { ?>
	<?php if (isset($this->suspend_form['suspend_member_html'])) { ?>
		<div class="mb-3">
			<form id="suspend_form" name="suspend_form" method="post">
				<input type="hidden" name="action" value="suspend_member" />
				<div style="border:green solid 1px; background-color:#F1E8D8; padding:6px; margin:4px;">
					<div>
						<div style="float:right;"><?php echo $this->suspend_form['suspend_member_html'];?></div>
						<h3>Place PlatePoints status on Hold</h3>
						<p class="font-italic">*Placing a guest on hold will send them a notification email tomorrow morning.</p>
					</div>
				</div>

			</form>
		</div>
	<?php } else if (isset($this->suspend_form['reactivate_member_html'])) { ?>
		<div class="mb-3">
			<form id="suspend_form" name="suspend_form" method="post">
				<input type="hidden" name="action" value="reactivate_member" />
				<div style="border:green solid 1px; background-color:#F1E8D8; padding:6px; margin:4px;">
					<div>
						<div style="float:right;"><?php echo $this->suspend_form['reactivate_member_html'];?></div>
						<h3>Enroll Guest back into PlatePoints</h3>
					</div>
				</div>
			</form>
		</div>
	<?php } ?>
<?php } ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>