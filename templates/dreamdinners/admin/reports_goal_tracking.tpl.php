<?php
$this->assign('page_title','Session Goal Sheet');
if (!$this->print_view) {
	include $this->loadTemplate('admin/page_header_reports.tpl.php');
	?>
	<form action="main.php?page=admin_reports_goal_tracking&export=xslx&hideheaders=true&csvfilename=<?=$this->filename;?>" name="frm" method="post">
		<?php
		if (isset($this->form_session_list['store_html']) )
		{
			echo '<strong>Pick a Store:</strong>' .  $this->form_session_list['store_html'] . '<br/><br/>';
		}
		?>

		Pick a Session: <?=$this->form_session_list['sessionpopup_html']?> <?=$this->form_session_list['print_html']?> <?=$this->form_session_list['report_submit_html']?>

		<?php
		if (!empty($this->no_results))
		{
			echo "<br/><br/>" . $this->no_results;
		}
		//print_r($this->rows);
		?>
	</form>

	<?php
} // end if !$this->print_view
else
{
	?>
	<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
	<style>
		table { font-size:10px;font-family:Helvetica Neue,Arial,Helvetica,'Liberation Sans',FreeSans,sans-serif; }
	</style>

	<?php
	$counter = 0;
	if (!empty($this->goal_sheet_array))
	{
		foreach($this->goal_sheet_array as $goal_sheet )
		{
			$counter++;
			?>
			<table style="width: 100%; border-spacing: 0px;">
				<tr>
					<td colspan="16" style="font-size:14px;font-weight:bold;text-align:right;">Session: <?php echo $this->sessionTypeDateTimeFormat($goal_sheet['session_start'], $goal_sheet['session_type_subtype'],VERBOSE); ?></td>
				</tr>
				<tr>
					<td style="text-align:right;white-space:nowrap;">Session Lead</td>
					<td colspan="3" style="width:120px;border-bottom:1px solid #000;">&nbsp;</td>
					<td rowspan="6" style="width:20px;">&nbsp;</td>
					<td rowspan="6" colspan="9" style="vertical-align:top;border:1px solid #000;">Session Comments</td>
					<td rowspan="6" style="width:20px;">&nbsp;</td>
					<td rowspan="6" colspan="5" style="vertical-align:top;border:1px solid #000;">Food / Inventory Issues</td>
				</tr>
				<tr>
					<td style="text-align:right;">Staff</td>
					<td colspan="3" style="border-bottom:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:right;">Staff</td>
					<td colspan="3" style="border-bottom:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:right;">Staff</td>
					<td colspan="3" style="border-bottom:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:right;">Staff</td>
					<td colspan="3" style="border-bottom:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:right;">Support</td>
					<td colspan="3" style="border-bottom:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="16">&nbsp;</td>
				</tr>
				<tr>
					<td style="text-align:center;border:1px solid #000;">Guest Name</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Total Ticket</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Balance Due</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Payment Type</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Total Visits</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Last Session Type</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Last Order Type</td>
					<?php if ($this->storeSupportsPlatePoints) { ?>
						<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Loyalty Status</td>
						<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">PLATEPOINTS</td>
					<?php } else { ?>
						<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">DR Status</td>
						<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">DR Level</td>
					<?php } ?>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Dinner Dollars</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Order Type</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Core/Other Item<br> Count</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">In-Store Signup (Current Order)</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">In-Store Signup (Yes/No)</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Addon Sales Amount</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Follow up Needed</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;width:150px;">Future Order Date</td>
					<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Checked Out By</td>
				</tr>
				<?php
				$session_total = array();
				$session_total['totalTicket'] = 0;
				$session_total['BalanceDue'] = 0;

				foreach($goal_sheet['guests'] as $booking_id => $guest)
				{
					$session_total['totalTicket'] += $guest['totalTicket'];
					$session_total['BalanceDue'] += $guest['BalanceDue'];
					?>
					<tr>
						<td style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;white-space:nowrap; <?php if ($guest['first_standard']) { echo " background-color:yellow;"; } ?>">
							<div><?php echo $guest['name']; ?>
								<?php if (!empty($guest['corporate_client_data']) && !empty($guest['corporate_client_data']->is_active)) { ?><img alt="<?php echo $guest['corporate_client_data']->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $guest['corporate_client_data']->icon_path; ?>_icon.png" style="float: right; margin-left: 4px;" data-tooltip="<?php echo $guest['corporate_client_data']->company_name; ?>" /><?php } ?>
								<?php if ($guest['is_birthday_month']){ ?><span style="float: right;"><img alt="Happy Birthday" src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/cake.png"/></span><?php } ?>
							</div>
							<div><?php echo $guest['telephone_1']; ?></div>
							<?php if (!empty($guest['consecutive_order_status'])) { ?>
								<div>Consecutive Order Count: <?php echo $guest['consecutive_order_status']; ?></div>
							<?php } ?>
							<div>Last Visit: <?php echo $guest['LastVisit']; ?></div>
						</td>
						<td style="text-align:right;border-right:1px solid #000;border-bottom:1px solid #000;">$<?php echo $guest['totalTicket']; ?></td>
						<td style="text-align:right;border-right:1px solid #000;border-bottom:1px solid #000;">
							<?php if (empty($guest['UserPreferences']['TC_DELAYED_PAYMENT_AGREE']['value'])) { ?><span style="float: left;"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/money_dollar.png" /></span><?php } ?>
							<?php if (!empty($guest['BalanceDue'])) {
								if ($guest['BalanceDue'] > 0) { ?>
									$<span style="color: red;"><?php echo $guest['BalanceDue']; ?></span>
								<?php } else { ?>
									$<span style="color: blue;"><?php echo $guest['BalanceDue']; ?></span>
								<?php } } else { ?>
								$<?php echo $guest['BalanceDue']; ?>
							<?php } ?>
						</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">CC / Cash<br />Check / Other</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['TotalVisits']; ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo CCalendar::sessionTypeTiny($guest['LastVisitType'],$guest['LastVisitTypeSubType']); ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo ucfirst(strtolower(str_replace('_', ' ', $guest['LastBookingType']))); ?></td>
						<?php if ($this->storeSupportsPlatePoints) { ?>
							<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['DreamRewardsStatus']; ?></td>
							<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['DRAccountLevel']; ?></td>
						<?php } else { ?>
							<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['DreamRewardsStatus']; ?></td>
							<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['DROrderLevel']; ?></td>
						<?php } ?>

						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['ReferralCreditonAccount']; ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo CBooking::getBookingTypeDisplayString($guest['BookingType']); ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['CoreItemCount']; ?>/<?php echo $guest['OtherItemCount']; ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['InStoreSignup']; ?></td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
						<td style="text-align:center;border-right:1px solid #000;border-bottom:1px solid #000;">&nbsp;</td>
					</tr>

					<?php if (count($guest['all_expiring_credits']) > 0) { ?>
						<tr>
							<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Dinner Dollars Expiration</td>
							<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;"> $<?php echo CTemplate::moneyFormat($guest['sum_all_expiring_credits']); ?> Available:
						<?php foreach ($guest['all_expiring_credits'] as $creditIndex => $credit){
									if( $creditIndex > 0){ echo ',  ';}
									echo ' $'.$credit->dollar_value . ' expire on '. CTemplate::dateTimeFormat(CPointsCredits::formatExpirationDateForGuest($credit->expiration_date), MONTH_DAY_YEAR) ;
								} ?>
							</td>
						</tr>
					<?php } ?>
					<?php if ($this->supports_membership) { ?>
					<tr>
						<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Meal Prep+</td>
						<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;">
							<?php echo $guest['membership_status']['display_strings']['status_abbr']; ?>&nbsp;&nbsp;This Order: <?php echo $guest['membership_status']['display_strings']['order_position']; ?>
																										&nbsp;&nbsp;Consecutive+ Orders: <?php echo $guest['membership_status']['display_strings']['progress']; ?>
						</td>
					</tr>
				<?php } ?>
					<tr>
						<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Admin Carryover Notes</td>
						<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['guest_carryover_notes']; ?></td>
					</tr>
					<?php if (!empty($guest['UserPreferences'][Cuser::USER_ACCOUNT_NOTE]['value'])) { ?>
					<tr>
						<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Account Note</td>
						<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['UserPreferences'][Cuser::USER_ACCOUNT_NOTE]['value']; ?></td>
					</tr>
				<?php } ?>
					<?php if (!empty($guest['order_user_notes'])) { ?>
					<tr>
						<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Order Special Instructions</td>
						<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;"><span style="color: red;"><?php echo $guest['order_user_notes']; ?></span></td>
					</tr>
				<?php } ?>
					<tr>
						<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">Admin Order Notes</td>
						<td colspan="17" style="border-right:1px solid #000;border-bottom:1px solid #000;"><?php echo $guest['order_admin_notes']; ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td style="text-align:right;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;white-space:nowrap;font-weight:bold;">Total</td>
					<td style="text-align:right;border-right:1px solid #000;border-bottom:1px solid #000;font-weight:bold;">$<?php echo $this->moneyFormat($session_total['totalTicket']); ?></td>
					<td style="text-align:right;border-right:1px solid #000;border-bottom:1px solid #000;font-weight:bold;">$<?php echo $this->moneyFormat($session_total['BalanceDue']); ?></td>
					<td colspan="16">&nbsp;</td>
				</tr>

				<?php if (!empty($this->goalsummary)) { // NOT IMPLEMENTED, DESIGN ONLY CURRENTLY CONCEIVED ?>
					<tr>
						<td colspan="16">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Session Total</td>
						<td style="text-align:right;">&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="4" style="text-align:center;border-bottom:1px solid #000;">Up Sales</td>
						<td>&nbsp;</td>
						<td colspan="4" style="text-align:center;border-bottom:1px solid #000;">Tastes</td>
						<td>&nbsp;</td>
						<td colspan="3" style="text-align:center;border-bottom:1px solid #000;">In Store Signup</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Session Goal</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3" style="text-align:right;">Upsale goal for the Month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3" style="text-align:right;">Taste goal for the Month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2" style="text-align:right;">In Store Signup Goal for Month</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Over / Under for Session</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3" style="text-align:right;">Upsales to date</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3" style="text-align:right;">Tastes scheduled to Date</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2" style="text-align:right;">% to Date</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Month Total</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3" style="text-align:right;">Over / Under for month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3" style="text-align:right;">Over / Under for month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2" style="text-align:right;">Over / Under for month</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Month Goal</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Over / Under for Month</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">Goal for each remaining Session</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td colspan="3" style="text-align:right;">Sessions for this month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="3" style="text-align:right;">Sessions for this month</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td colspan="2">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				<?php } // end if $this->goalsummary ?>
			</table>

			<?php
			if ($counter < count($this->goal_sheet_array))
			{
				echo '<div style="page-break-after:always;"></div>';
			}
			?>

		<?php } }// end $goal_sheet ?>

<?php } ?>

<?php if (!$this->print_view) { include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
<?php } else { ?>
	</body>
	</html>
<?php } ?>