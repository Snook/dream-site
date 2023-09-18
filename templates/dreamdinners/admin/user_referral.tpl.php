<?php $this->assign('page_title','Guest Referral'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/list_users.min.js'); ?>
<?php $this->setScriptVar('active_referral_email = "' . (!empty($this->activeReferral['email']) ? $this->activeReferral['email'] : 'none')  . '";'); ?>
<?php $this->setOnload('list_users_init();'); ?>
<?php $this->setOnload('user_referral_init();'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (isset($this->form['store_html'])) { ?>
	<strong>Store:</strong> <?php echo $this->form['store_html']; ?><br /><br />
<?php } ?>
	<table style="width: 100%;"><tr><td>
				<?php if (isset($_REQUEST['back'])) { ?>
					<input type="button" value="Back" onClick="bounce('<?= $_REQUEST['back']?>');">
				<?php } else { ?>
					<input type="button" value="Back" onClick="bounce('/?page=admin_user_details&id=<?= $this->customer_id?>');">
				<?php } ?>

			</td><td width="95%" style="text-align:center;"><h3 >Who Referred <?php echo $this->customerName; ?></h3>
			</td></tr></table>

	<table style="width: 100%;">
		<tr>
			<td class="bgcolor_medium catagory_row"  style="text-align:right; width:50%;">Guest who referred <?php echo $this->customerName; ?>:</td>
			<td class="bgcolor_light" style="text-align:center;">
				<b>
					<?php echo ($this->activeReferral) ? $this->activeReferral['name'] . ' (' . $this->activeReferral['email'] . ') <br />on ' .
						CTemplate::dateTimeFormat($this->activeReferral['date'], NORMAL, $this->store_id, CONCISE ) : 'No Current Referral Source'; ?>
				</b>
			</td>
		</tr>
	</table>


	<table style="width: 100%;">
		<tr>
			<td class="bgcolor_dark catagory_row" colspan="7">Referral History</td>
		</tr>
		<tr class="form_title_cell">
			<td class="bgcolor_medium header_row"></td>
			<td class="bgcolor_medium header_row">Referring User's Name</td>
			<td class="bgcolor_medium header_row">Referring User's Email</td>
			<td class="bgcolor_medium header_row">Referral Type</td>
			<td class="bgcolor_medium header_row">Referral Date</td>
			<td class="bgcolor_medium header_row">Referral Status</td>
			<td class="bgcolor_medium header_row">Ordered</td>
		</tr>
		<?php if (isset($this->referralsArray) && count($this->referralsArray) > 0) {
			foreach($this->referralsArray as $i => $ref) {	?>
				<tr>
					<td><button style="height:20px; font-size:10px;"
								onclick="useGuestAccount(<?php echo $i; ?>, '<?php echo $ref['email']; ?>', <?php echo $ref['user_id']; ?>, <?php echo ($ref['is_pending']) ? "true" : "false"?>, <?php echo ($ref['inPP']) ? "true" : "false"?> );">Select</button></td>
					<td class="bgcolor_light"><a href="<?php echo CTemplate::getUserDetailsLink($ref['user_id']);?>"><?=$ref['name']?></a></td>
					<td class="bgcolor_light"><?=$ref['email']?></td>
					<td class="bgcolor_light"><?=$ref['type']?></td>
					<td class="bgcolor_light"><?=CTemplate::dateTimeFormat($ref['date'], NORMAL, $this->store_id, CONCISE)?></td>
					<td class="bgcolor_light"><?=$ref['status']?><?php if ($ref['is_pending']) { ?> - <span class="text-danger">Pending</span><?php } ?>

						<?php if ($ref['status'] == 'Award Complete')
						{

							if ($ref['inPP'])
							{
								echo " (" .$ref['amount'] . ")";

							}
							else
							{
								echo " ($" .$ref['amount'] . ")";
							}

						} ?>
					</td>


					<td class="bgcolor_light" style="text-align:center;"><?=$ref['ordered']?> </td>
				</tr>
			<?php } } else { ?>
			<tr>
				<td class="bgcolor_light" colspan="6"><i>There is no referral history for this customer.</i></td>
			</tr>
		<?php } ?>
	</table>

	<br />


	<table style="width: 100%;">
		<tr>
			<td class="bgcolor_dark catagory_row" colspan="2" width="150">Enter New Referral</td>
		</tr>
	</table>

	<table style="width: 100%;">
		<tr>
			<td class="bgcolor_gray catagory_row" rowspan="2" style="text-align:right"  width="150">Search Guests</td>
			<td><?php include $this->loadTemplate('admin/list_users_plugin.tpl.php'); ?>
				<div id="user_list_target"></div>
			</td>
		</tr>
	</table>

	<table style="width: 100%;">
		<tr>
			<td class="bgcolor_gray catagory_row"  width="150" style="text-align:right">Referring Guest</td>
			<td style="padding:10px;" class="bgcolor_light"><span id="ur_select_message">Please select a guest using the Search Form or Referral History</span>
				<span id="customer_referral_result" style="margin-left: 4px; font-weight:bold"></span>
			</td>
		</tr>
	</table>

	<!--
<?php if ($this->form_login['user_type'] == 'SITE_ADMIN') {?>
<table style="width: 100%;">
<tr>
	<td class="bgcolor_gray catagory_row"  width="150"  style="text-align:right">Diagnostics</td>
	<td class="bgcolor_light" text-align:right;"><?php print_r($this->referral_status); ?></td>
</tr>
</table>
<?php } ?>
-->

	<form method="post" onsubmit="return checkReferralSubmission(this); ">

		<table style="width: 100%;">
			<tr>
				<td class="bgcolor_gray catagory_row"  width="150"  style="text-align:right">Options</td>
				<td class="bgcolor_light" style="text-align: right;">
					<div id="optionsDiv" style="display:none">
						<table>
							<?php if ($this->referral_status['hasTriggeredReward'] || !$this->referral_status['userIsNew'] ) {
								// Scenario 1: user is ineligible to trigger award
								?>
								<tr>
									<td>
										<?php echo $this->customerName; ?>  is an existing guest or has already been referred by a guest
																			which resulted in a referral reward. Therefore the referring guest is not eligible for reward. You may still change listed referring user.
									</td>
								</tr>
							<?php } else {

								if (isset($this->promo_in_effect) and $this->promo_in_effect)
								{
									include $this->loadTemplate('admin/subtemplate/user_referral_options_promo.tpl.php');
								}
								else
								{
									include $this->loadTemplate('admin/subtemplate/user_referral_options_normal.tpl.php');
								}
							} ?>


						</table>
					</div>
				</td>
			</tr>
		</table>
		<?php echo $this->form['hidden_html']; ?>

		<table style="width: 100%;">
			<tr>
				<td class="bgcolor_gray catagory_row"  width="150"  style="text-align:right">Create Referral</td>
				<td class="bgcolor_light" style="text-align:center;" colspan="2"><?=$this->form['submit_referral_html'] ?></td>
			</tr>
		</table>


		<table style="width: 100%;">
			<tr>
				<td class="bgcolor_dark catagory_row">Referral Source Notes</td>
			</tr>
			<tr>
				<td class="bgcolor_light" style="text-align:center;"><?=$this->form['notes_html'] ?><br /><?=$this->form['submit_notes_html'] ?></td>
			</tr>
		</table>

	</form>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>