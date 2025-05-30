<?php $this->assign('page_title','Order Manager Test'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->assign('helpLinkSection','EO'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/main.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/card_track.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/ajax_support.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_test.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_items.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_payments.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/order_mgr.css'); ?>
<?php if ($this->orderState == 'NEW') { ?><?php $this->setCSS(CSS_PATH . '/admin/order_mgr_new.css'); ?><?php } ?>
<?php if ($this->orderState == 'SAVED') { ?><?php $this->setCSS(CSS_PATH . '/admin/order_mgr_saved.css'); ?><?php } ?>
<?php $this->setOnLoad("admin_order_mgr_init();"); ?>
<?php $this->setOnLoad("handle_delayed_payment();"); ?>
<?php $this->setScriptVar("orderState = '" . $this->orderState . "';"); ?>
<?php $this->setScriptVar("store_id = " .$this->store_id . ";");  ?>
<?php $this->setScriptVar("user_id = '" . $this->user_id . "';"); ?>
<?php $this->setScriptVar("user_email = '" . addslashes($this->user_email) . "';"); ?>
<?php $this->setScriptVar("store_specific_deposit = " . $this->store_specific_deposit . ";"); ?>
<?php $this->setScriptVar("hasBundle = '" . $this->hasBundle . "';"); ?>
<?php $this->setScriptVar("originallyHadBundle = '" . $this->originallyHadBundle . "';"); ?>
<?php if (!empty($this->bundleInfo)) { $this->setScriptVar("bundleInfo = " . json_encode($this->bundleInfo) . ";"); } ?>
<?php $this->setScriptVar("order_id = '" . ((isset($_REQUEST['order']) && is_numeric($_REQUEST['order'])) ? $_REQUEST['order'] : 'false') . "';"); ?>
<?php $this->setScriptVar("session_id = '" . ((isset($this->session_id) && is_numeric($this->session_id)) ? $this->session_id : 'false') . "';"); ?>
<?php $this->setScriptVar("saved_booking_id = '" . $this->saved_booking_id . "';"); ?>
<?php $this->setScriptVar('isDreamTaste = ' . ($this->isDreamTaste ? 'true' : 'false') . ';'); ?>
<?php if ($this->isDreamTaste) { $this->setScriptVar('dreamTasteServingsNeeded = ' . $this->dreamTasteProperties->number_servings_required . ';'); } ?>
<?php if ($this->isDreamTaste) { $this->setScriptVar('dreamTastePrice = ' . $this->dreamTasteProperties->price . ';'); } ?>
<?php $this->setScriptVar('couponDiscountMethod = \'' . $this->couponDiscountMethod . '\';'); ?>
<?php $this->setScriptVar('couponDiscountVar = ' . $this->couponDiscountVar . ';'); ?>
<?php $this->setScriptVar('couponlimitedToFT = ' . ($this->couponlimitedToFT ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('couponIsValidWithPlatePoints = ' . ($this->couponIsValidWithPlatePoints ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('couponFreeMenuItem = ' . ($this->couponFreeMenuItem ? $this->couponFreeMenuItem : 'false') . ';'); ?>
<?php $this->setScriptVar('hasDreamTasteBundle = ' . ($this->isDreamTaste? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('isDreamRewardsOrder = false'); ?>
<?php $this->setScriptVar('org_order_time = \'' . (!empty($this->org_order_time) ? $this->org_order_time : false) . '\';'); ?>
<?php $this->setScriptVar('request_uri = \'' . $_SERVER['REQUEST_URI'] . '\';'); ?>
<?php $this->setScriptVar('tc_delayed_payment_agree = ' . (!empty($this->user_obj->preferences[CUser::TC_DELAYED_PAYMENT_AGREE]['value']) ? $this->user_obj->preferences[CUser::TC_DELAYED_PAYMENT_AGREE]['value'] : 'false') . ';'); ?>
<?php $this->setScriptVar('forceManualAutoAdjust = ' . (!empty($this->forceManualAutoAdjust) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('supports_transparent_redirect = ' . (!empty($this->storeInfo['supports_transparent_redirect']) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('canAutoAdjust = ' . ((isset($this->paymentInfo['canAutoAdjust']) && $this->paymentInfo['canAutoAdjust']) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('canAdjustDelayedPayment = ' . ((isset($this->canAdjustDelayedPayment) && $this->canAdjustDelayedPayment) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('currentDPAmount = ' . ((isset($this->PendingDPAmount)) ? $this->PendingDPAmount : '0') . ';'); ?>
<?php $this->setScriptVar('creditBasis = ' . $this->CreditBasis . ';'); ?>
<?php $this->setScriptVar('orderInfoGrandTotal = ' . $this->moneyFormat($this->orderInfo['grand_total']) . ';'); ?>
<?php $this->setScriptVar('curFoodTax = ' . $this->curFoodTax . ';'); ?>
<?php $this->setScriptVar('curFoodTax = ' . $this->curFoodTax . ';'); ?>
<?php $this->setScriptVar('curServiceTax = ' . $this->curServiceTax . ';'); ?>
<?php $this->setScriptVar('originalSD = ' . json_encode($this->originalSD) . ';'); ?>
<?php $this->setScriptVar('originalUP = ' . json_encode($this->originalUP) . ';'); ?>
<?php $this->setScriptVar('activeUP = ' . json_encode($this->activeUP) . ';'); ?>
<?php $this->setScriptVar('activeSessionDiscount = ' . json_encode($this->activeSD) . ';'); ?>
<?php $this->setScriptVar('orderEditSuccess = ' . (isset($this->orderEditSuccess) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('newAddonQty = ' . (isset($this->newAddonQty) ? $this->newAddonQty : '0') . ';'); ?>
<?php $this->setScriptVar('newAddonAmount = ' . (isset($this->newAddonAmount) ? $this->newAddonAmount : '0') . ';'); ?>
<?php $this->setScriptVar('curNonFoodTax = ' . $this->curNonFoodTax . ';'); ?>
<?php $this->setScriptVar('curEnrollmentTax = ' . $this->curEnrollmentTax . ';'); ?>
<?php $this->setScriptVar('discountMFYFeeFirst = ' . ($this->pp_discount_mfy_fee_first ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('tasteBundleMenuData = ' . (!empty($this->tasteBundleMenuData) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('intro_price = ' . (!empty($this->introPrice) ? $this->introPrice : 0) . ';'); ?>
<?php $this->setScriptVar('existingPaymentAmountArray = ' . ((!empty($this->refTransArray) && $this->refTransArray[0] != 'No Transactions available') ? json_encode($this->refTransArray) : 'false') . ';'); ?>
<?php $this->setScriptVar('externalPaymentAmountArray = ' . (!empty($this->refPaymentTypeData) ? json_encode($this->refPaymentTypeData) : 'false') . ';'); ?>
<?php $this->setScriptVar('sideStationBundleInfo = ' . (!empty($this->sideStationBundleInfo) ? json_encode($this->sideStationBundleInfo) : 'false') . ';'); ?>
<?php $this->setScriptVar('entreeIDToInventoryMap = ' . (!empty($this->entreeToInventoryMap) ? json_encode($this->entreeToInventoryMap) : 'false') . ';'); ?>
<?php if (defined('TR_SIM_LINK')) { $this->setScriptVar("transparent_redirect_link = '" . TR_SIM_LINK . "';"); } ?>
<?php if (defined('PFP_TEST_MODE') && PFP_TEST_MODE) { $this->setScriptVar("pfp_test_mode = true;"); } ?>
<?php if (defined('PHP_ERROR_URL')) {$this->setScriptVar("payflowErrorURL = '" . PHP_ERROR_URL .  "';"); }?>
<?php $this->setScriptVar('allowLimitedAccess = ' . ($this->allowLimitedAccess ? 'true' : 'false') . ';'); ?>

<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if ($this->orderState != 'NEW') { ?>
	<div id="changeListTab">Change List</div>
<?php } ?>

	<iframe id="paypal-result" name="paypal-result"></iframe>

	<form id="editorForm" method="post">
		<?php echo $this->form_direct_order['hidden_html']; ?>
		<input type="hidden" id="submit_changes" name="submit_changes" value="false">

		<table border="0" width="100%" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
			<tr>
				<td width="50%" colspan="2" align="center" valign="top">

					<table width="100%" cellpadding="0" cellspacing="0" class="form_subtitle_cell">

						<tr>
							<td colspan="2" style="text-align: center;">

								<?php if ($this->orderState == 'NEW') { ?>
									<span class="order_state_title">New Order</span>
								<?php } else if ($this->orderState == 'SAVED') { ?>
									<span class="order_state_title">Saved Order</span>
								<?php } else if ($this->order_is_locked == 'LOCKED') { ?>
									<span class="order_state_title cancelled">Locked Order</span>
								<?php } else if ($this->orderState == 'CANCELLED') { ?>
									<span class="order_state_title cancelled">Canceled Order</span>
								<?php } else { ?>
									<span class="order_state_title">Active Order</span>
								<?php } ?>

							</td>
						</tr>

						<tr>
							<td style="text-align: right;">Customer Name:&nbsp;</td>
							<td style="font-weight: bold;">
								<a href="/backoffice/user-details?id=<?php echo $this->user['id']; ?>">
									<?php echo $this->user_obj->firstname; ?> <?php echo $this->user_obj->lastname; ?>
								</a>
								<?php if (!empty($this->plate_points) && $this->plate_points['status'] == 'active') { ?>
									<img data-user_id_pp_tooltip="<?php echo $this->user['id']; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $this->plate_points['current_level']['image']; ?>-16x16.png" class="img_valign" style="width: 16px; height: 16px;" />
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">Store:</td>
							<td style="font-weight: bold;"><b><?php echo $this->storeInfo['store_name']; ?></b></td>
						</tr>
						<tr>
							<td style="text-align: right; vertical-align: top;">Session:&nbsp;</td>
							<td style="font-weight: bold;">
								<div id="curSessionDate">
									<?php if ($this->orderState == 'NEW') { ?>
										<span style="color: red;">Please select</span>
									<?php } else { ?>
										<a href="/backoffice?session=<?php echo $this->sessionInfo['id']; ?>"><?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE); ?></a>
									<?php } ?>
								</div>
								<?php if ($this->orderState != 'NEW') { ?>
									<div>Session Type: <span id="curSessionTypeSpan"><?php echo $this->sessionInfo['session_type_text']?></span></div>
									<div>Remaining Std Slots: <span id="curSessionRemainingSlotsSpan"><?php echo $this->sessionInfo['remaining_slots']?></span></div>
									<div>Remaining Starter Pack Slots: <span id="curSessionRemainingIntroSlotsSpan"><?php echo $this->sessionInfo['remaining_intro_slots']?></span></div>
								<?php } ?>

							</td>
						</tr>
						<?php if ($this->orderState == 'ACTIVE') { ?>
							<tr>
								<td style="text-align: right;">Confirmation Number:&nbsp;</td>
								<td style="font-weight: bold;"><?php echo $this->orderInfo['order_confirmation']; ?></td>
							</tr>
						<?php } ?>

						<?php if ($this->orderState != 'NEW') { ?>
							<tr>
								<td style="text-align: right;">Order ID:&nbsp;</td>
								<td style="font-weight: bold;"><?php echo $this->orderInfo['id']; ?></td>
							</tr>
						<?php } ?>
					</table>
				</td>

				<?php
				if ($this->orderState == 'NEW')
				{
					include $this->loadTemplate('admin/order_mgr_header_new.tpl.php');
				}
				else if ($this->orderState == 'SAVED')
				{
					include $this->loadTemplate('admin/order_mgr_header_saved.tpl.php');
				}
				else
				{
					include $this->loadTemplate('admin/order_mgr_header.tpl.php');
				}
				?>

				<div id="cancel_div"></div>

				<?php if (!empty($this->isTODD) || $this->isDreamTaste) { ?>
					<div id="Todd_header">
						<?php if (!empty($this->isTODD)) { ?>
							Taste of Dream Dinners Order
						<?php } ?>
						<?php if ($this->isDreamTaste) { ?>
							Meal Prep Workshop Session
						<?php } ?>
					</div>
				<?php } ?>

			 <?php if ($this->allowLimitedAccess) { ?>
			     <div style="text-align: center;"><h3 style="color:red;">This order is Canceled or Locked. Only Payments and Notes can be altered.</h3></div>
			 <?php } ?>

				<div id="update_console">
					<span id="addPaymentAndActivate"><input id="addPaymentAndActivateButton" class="btn btn-primary btn-sm" disabled="disabled" onclick="addPaymentAndActivate(this); return false;" value="Add Payment and Book Order" style="width:200px;" /></span>
					<span id="saveOrderSpan" style="float:left;"><input class="btn btn-primary btn-sm" id="saveOrderButton" onclick="saveAll(); return false;" value="Save Order" />You have unsaved changes.</span>
					<span id="finalizeOrderSpan"><input class="btn btn-primary btn-sm" name="submit_button" id ="submit_button" type="button" value="Finalize All Changes" onclick="submitPage();" /><span id="finalize_msg">You have unsaved changes</span></span>
					<?php if ($this->orderState == 'NEW') { ?>
						<span id="om_instructions">Select a session to save a new order.</span>
					<?php } ?>
					<div id="payment_help_msg"></div>
					<div class="clear"></div>
					<div>
						<?php if ($this->orderState == 'ACTIVE') { ?>
							<div style="float:right;"><input type="checkbox" name="suppressEmail" id="suppressEmail"><label for="suppressEmail">Suppress sending confirmation email to guest.</label></div>
							<div id="autoAdjustDiv" style="display: none; width: 700px;">
								<?=$this->form_direct_order['autoAdjust_html']?>&nbsp;<span id=OEH_auto_pay_msg></span>
							</div>
							<div id="help_msg"></div>
						<?php } ?>
					</div>
					<div class="clear"></div>
				</div>

				<div class="tabbed-content" data-tabid="mgr">

					<div class="tabs-container">
						<ul class="tabs">
							<li id="sessions_tab_li" data-tabid="sessionsTab" class="tab<?php if ($this->orderState == 'NEW') { ?> selected<?php } ?>" data-callback="onSessionTabSelected" data-deselect_callback="onSessionTabDeselected">Sessions</li>
							<li id="items_tab_li" data-tabid="itemsTab" class="tab<?php if ($this->orderState != 'NEW') { ?> selected<?php } else { ?> disabled<?php } ?>" data-callback="onItemsTabSelected"  data-deselect_callback="onItemsTabDeselected">Items</li>
							<li id="notes_tab_li" data-tabid="notesTab" class="tab<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>"   data-callback="onNotesTabSelected"  data-deselect_callback="onNotesTabDeselected">Notes/History</li>
							<li id="payments_tab_li" data-tabid="discounts_paymentsTab" class="tab<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>"  data-callback="onPaymentTabSelected"  data-deselect_callback="onPaymentTabDeselected">Discounts / Payments</li>

							<?php if ($this->orderState == 'ACTIVE' && CUser::getCurrentUser()->user_type == 'SITE_ADMIN') { ?>
								<li id="site_admin_tab_li" data-tabid="siteAdminTab" class="tab"  data-callback="onSiteAdminTabSelected"  data-deselect_callback="onSiteAdminTabDeselected">Site Admin</li>
							<?php } ?>
							<li id="related_orders_tab_li" data-tabid="relatedOrdersTab" class="tab"  data-callback="onRelatedOrdersTabSelected"  data-deselect_callback="onRelatedOrdersTabDeselected">Related Orders</li>
						</ul>
					</div>

					<div class="tabs-content">

						<div data-tabid="sessionsTab" id="sessions" class="background-color:#a0a0a0;">
							<?php include $this->loadTemplate('admin/order_mgr_session.tpl.php');?>
						</div>

						<div data-tabid="itemsTab" id="items">
							<?php include $this->loadTemplate('admin/order_mgr_items.tpl.php');?>
						</div>

						<div data-tabid="notesTab" id="notes">
							<?php if ($this->orderState != 'NEW') {
								include $this->loadTemplate('admin/order_mgr_notes.tpl.php'); } ?>
						</div>

						<div data-tabid="discounts_paymentsTab" id="payments">
							<?php include $this->loadTemplate('admin/order_mgr_payments.tpl.php');?>
						</div>

						<div data-tabid="relatedOrdersTab" id="related_orders">
							<img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" alt="Processing" /> Loading ...
						</div>

						<?php if ($this->orderState == 'ACTIVE' && CUser::getCurrentUser()->user_type == 'SITE_ADMIN') { ?>
							<div data-tabid="siteAdminTab" id="site_admin">
								<?php include $this->loadTemplate('admin/order_mgr_site_admin.tpl.php');?>
							</div>
						<?php } ?>

					</div>
				</div>
	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>