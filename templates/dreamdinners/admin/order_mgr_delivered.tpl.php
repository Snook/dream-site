<?php $this->assign('page_title','Delivered Order Manager'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php $this->assign('helpLinkSection','EO'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/main.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/card_track.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/ajax_support.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_delivered.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_items_delivered.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/order_mgr_payments_delivered.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_credits.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/order_mgr.css'); ?>
<?php if ($this->orderState == 'NEW') { ?><?php $this->setCSS(CSS_PATH . '/admin/order_mgr_new.css'); ?><?php } ?>
<?php if ($this->orderState == 'SAVED') { ?><?php $this->setCSS(CSS_PATH . '/admin/order_mgr_saved.css'); ?><?php } ?>
<?php $this->setOnLoad("admin_order_mgr_init();"); ?>
<?php $this->setScriptVar("orderState = '" . $this->orderState . "';"); ?>
<?php $this->setScriptVar('store_id = ' . (!empty($this->store_id) ? $this->store_id : 'false') . ';'); ?>
<?php $this->setScriptVar("user_id = '" . $this->user_id . "';"); ?>
<?php $this->setScriptVar("user_email = '" . addslashes($this->user_email) . "';"); ?>
<?php $this->setScriptVar("order_id = '" . ((isset($_REQUEST['order']) && is_numeric($_REQUEST['order'])) ? $_REQUEST['order'] : 'false') . "';"); ?>
<?php $this->setScriptVar("session_id = '" . ((isset($this->session_id) && is_numeric($this->session_id)) ? $this->session_id : 'false') . "';"); ?>
<?php $this->setScriptVar("session_type = '" . (isset($this->session_type) ? $this->session_type : 'NONE') . "';"); ?>
<?php $this->setScriptVar("saved_booking_id = '" . $this->saved_booking_id . "';"); ?>
<?php $this->setScriptVar('couponDiscountMethod = \'' . $this->couponDiscountMethod . '\';'); ?>
<?php $this->setScriptVar('couponDiscountVar = ' . (!empty($this->couponDiscountVar) ? $this->couponDiscountVar : "0") . ';'); ?>
<?php $this->setScriptVar('couponlimitedToFT = ' . ($this->couponlimitedToFT ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('couponIsValidWithPlatePoints = ' . ($this->couponIsValidWithPlatePoints ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('couponFreeMenuItem = ' . ($this->couponFreeMenuItem ? $this->couponFreeMenuItem : 'false') . ';'); ?>
<?php $this->setScriptVar('coupon = ' . (!empty($this->coupon) ? json_encode($this->coupon) : 'false') . ';'); ?>
<?php $this->setScriptVar('isDreamRewardsOrder = false'); ?>
<?php $this->setScriptVar('menu_id = ' . (!empty($this->menuInfo['menu_id']) ? $this->menuInfo['menu_id'] : 'false') . ';'); ?>
<?php $this->setScriptVar('org_order_time = \'' . (!empty($this->org_order_time) ? $this->org_order_time : false) . '\';'); ?>
<?php $this->setScriptVar('request_uri = \'' . $_SERVER['REQUEST_URI'] . '\';'); ?>
<?php $this->setScriptVar('GUEST_PREFERENCES[' . $this->user_obj->id . '] = ' . json_encode($this->user_obj->preferences) . ';'); ?>
<?php $this->setScriptVar('forceManualAutoAdjust = ' . (!empty($this->forceManualAutoAdjust) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('supports_transparent_redirect = ' . (!empty($this->storeInfo['supports_transparent_redirect']) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('canAutoAdjust = ' . ((isset($this->paymentInfo['canAutoAdjust']) && $this->paymentInfo['canAutoAdjust']) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('canAdjustDelayedPayment = ' . ((isset($this->canAdjustDelayedPayment) && $this->canAdjustDelayedPayment) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('currentDPAmount = ' . ((isset($this->PendingDPAmount)) ? $this->PendingDPAmount : '0') . ';'); ?>
<?php $this->setScriptVar('creditBasis = ' . $this->CreditBasis . ';'); ?>
<?php $this->setScriptVar('orderInfoGrandTotal = ' . $this->moneyFormat($this->orderInfo['grand_total'] + (isset($this->orderInfo['ltd_round_up_value']) ?  $this->orderInfo['ltd_round_up_value'] : 0)) . ';'); ?>
<?php $this->setScriptVar('orderInfo = ' . json_encode($this->orderInfo) . ';'); ?>
<?php $this->setScriptVar('menuInfo = ' . (!empty($this->menuInfo) ? json_encode($this->menuInfo) : 'false') . ';'); ?>
<?php $this->setScriptVar('curFoodTax = ' . (!empty($this->curFoodTax) ? $this->curFoodTax : 'false') . ';'); ?>
<?php $this->setScriptVar('curServiceTax = ' . (!empty($this->curServiceTax) ? $this->curServiceTax : 'false') . ';'); ?>
<?php $this->setScriptVar('curDeliveryTax = ' . (!empty($this->curDeliveryTax) ? $this->curDeliveryTax : 'false') . ';'); ?>
<?php $this->setScriptVar('orderEditSuccess = ' . (isset($this->orderEditSuccess) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('newAddonQty = ' . (isset($this->newAddonQty) ? $this->newAddonQty : '0') . ';'); ?>
<?php $this->setScriptVar('newAddonAmount = ' . (isset($this->newAddonAmount) ? $this->newAddonAmount : '0') . ';'); ?>
<?php $this->setScriptVar('curNonFoodTax = ' . (!empty($this->curNonFoodTax) ? $this->curNonFoodTax : 'false') . ';'); ?>
<?php $this->setScriptVar('curEnrollmentTax = ' . (!empty($this->curEnrollmentTax) ? $this->curEnrollmentTax : 'false') . ';'); ?>
<?php $this->setScriptVar('discountMFYFeeFirst = ' . ($this->pp_discount_mfy_fee_first ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('tasteBundleMenuData = ' . (!empty($this->tasteBundleMenuData) ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('intro_price = ' . (!empty($this->introPrice) ? $this->introPrice : 0) . ';'); ?>
<?php $this->setScriptVar('existingPaymentAmountArray = ' . ((empty($this->refTransArray) || (isset($this->refTransArray[0]) && $this->refTransArray[0] == 'No Transactions available')) ?  'false' : json_encode($this->refTransArray)) . ';'); ?>
<?php $this->setScriptVar('externalPaymentAmountArray = ' . (!empty($this->refPaymentTypeData) ? json_encode($this->refPaymentTypeData) : 'false') . ';'); ?>
<?php $this->setScriptVar('sideStationBundleInfo = ' . (!empty($this->sideStationBundleInfo) ? json_encode($this->sideStationBundleInfo) : 'false') . ';'); ?>
<?php $this->setScriptVar('entreeIDToInventoryMap = ' . (!empty($this->entreeToInventoryMap) ? json_encode($this->entreeToInventoryMap) : 'false') . ';'); ?>
<?php $this->setScriptVar('PendingDPOriginalTransActionID = ' . (!empty($this->PendingDPOriginalTransActionID) ? "'" . $this->PendingDPOriginalTransActionID . "'" : 'false') . ';'); ?>
<?php $this->setScriptVar('PendingDPOriginalStatus = ' . (!empty($this->PendingDPOriginalStatus) ? "'" . $this->PendingDPOriginalStatus . "'" : 'false') . ';'); ?>
<?php if (defined('TR_SIM_LINK')) { $this->setScriptVar("transparent_redirect_link = '" . TR_SIM_LINK . "';"); } ?>
<?php if (defined('PFP_TEST_MODE') && PFP_TEST_MODE) { $this->setScriptVar("pfp_test_mode = true;"); } ?>
<?php if (defined('PHP_ERROR_URL')) {$this->setScriptVar("payflowErrorURL = '" . PHP_ERROR_URL .  "';"); }?>
<?php $this->setScriptVar('discountEligable = ' . json_encode($this->discountEligable) . ';'); ?>
<?php $this->setScriptVar('orderIsEligibleForMembershipDiscount = ' . ($this->orderIsEligibleForMembershipDiscount ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('storeSupportsMembership = ' . ($this->storeSupportsMembership ? 'true' : 'false') . ';'); ?>
<?php $this->setScriptVar('membership_status = ' . (!empty($this->membership_status) ? json_encode($this->membership_status) : 'false') . ';'); ?>
<?php $this->setScriptVar('initial_inventory = ' . (!empty($this->inventory_items) ? json_encode($this->inventory_items) : 'false') . ';'); ?>
<?php $this->setScriptVar('current_inventory = ' . (!empty($this->inventory_items) ? json_encode($this->inventory_items) : 'false') . ';'); ?>
<?php $this->setScriptVar('current_box_ids = ' . (!empty($this->current_box_ids) ? json_encode($this->current_box_ids) : 'false') . ';'); ?>
<?php $this->setScriptVar('service_days_for_current_zip = ' . (isset($this->shippingInfo->service_days) ? $this->shippingInfo->service_days : '2') . ';'); ?>

<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if ($this->orderState != 'NEW') { ?>
	<div id="changeListTab">Change List</div>
<?php } ?>

	<form id="editorForm" method="post" class="needs-validation" novalidate>

		<?php echo $this->form_direct_order['hidden_html']; ?>
		<input type="hidden" id="submit_changes" name="submit_changes" value="false">

		<table border="0" width="100%" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
			<tr>
				<td width="50%" colspan="2" align="center" valign="top">

					<table width="100%" cellpadding="0" cellspacing="0" class="form_subtitle_cell">

						<tr>
							<td colspan="2" style="text-align: center;">

								<?php if ($this->orderState == 'NEW') { ?>
									<span class="order_state_title">New DELIVERED Order</span>
								<?php } else if ($this->orderState == 'SAVED') { ?>
									<span class="order_state_title">Saved DELIVERED Order</span>
								<?php } else if ($this->order_is_locked == 'LOCKED') { ?>
									<span class="order_state_title cancelled">Locked DELIVERED Order</span>
								<?php } else if ($this->orderState == 'CANCELLED') { ?>
									<span class="order_state_title cancelled">Canceled DELIVERED Order</span>
								<?php } else { ?>
									<span class="order_state_title">Active DELIVERED Order</span>
								<?php } ?>

							</td>
						</tr>

						<tr>
							<td style="text-align: right;">Customer Name:&nbsp;</td>
							<td style="font-weight: bold;">
								<a href="?page=admin_user_details&amp;id=<?php echo $this->user['id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']);?>">
									<?php echo $this->user_obj->firstname; ?> <?php echo $this->user_obj->lastname; ?>
								</a>
								<?php if (!empty($this->plate_points) && $this->plate_points['status'] == 'active') { ?>
									<img data-user_id_pp_tooltip="<?php echo $this->user['id']; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/platepoints/badge-<?php echo $this->plate_points['current_level']['image']; ?>-16x16.png" class="img_valign" style="width: 16px; height: 16px;" />
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">Distribution Center:</td>
							<td style="font-weight: bold;"><b><?php echo ($this->orderState == 'NEW' ?  "-" : $this->storeInfo['store_name']); ?></b></td>
						</tr>
						<tr>
							<td style="text-align: right; vertical-align: top;">Delivery Destination ZIP Code:&nbsp;</td>
							<td style="font-weight: bold;">
								<div id="deliveryZipCode">
									<?php if ($this->orderState == 'NEW') { ?>
										<span style="color: red;">Please select</span>
									<?php } else { ?>
										<span><?php echo $this->shippingInfo->shipping_postal_code; ?></span>
									<?php } ?>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; vertical-align: top;">Shipping Days:&nbsp;</td>
							<td style="font-weight: bold;">
								<div id="daysInTransit">
									<?php if ($this->orderState == 'NEW') { ?>
										<span style="color: red;">-</span>
									<?php } else { ?>
										<?php echo $this->shippingInfo->service_days; ?>
									<?php } ?>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; vertical-align: top;">Ship Date:&nbsp;</td>
							<td style="font-weight: bold;">
								<div id="curShipDate">
									<?php if ($this->orderState == 'NEW') { ?>
										<span style="color: red;">-</span>
									<?php } else { ?>
										<?php echo $this->dateTimeFormat($this->shippingInfo->ship_date, FULL_MONTH_DAY_YEAR); ?>
									<?php } ?>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; vertical-align: top;">Delivery Date:&nbsp;</td>
							<td style="font-weight: bold;">
								<div id="curSessionDate">
									<?php if ($this->orderState == 'NEW') { ?>
										<span style="color: red;">-</span>
									<?php } else { ?>
										<?php echo $this->dateTimeFormat($this->shippingInfo->actual_delivery_date, FULL_MONTH_DAY_YEAR); ?>
									<?php } ?>
								</div>
							</td>
						</tr>

						<?php if ($this->orderState == 'ACTIVE') { ?>
							<tr>
								<td style="text-align: right;">Confirmation Number:&nbsp;</td>
								<td style="font-weight: bold;"><?php echo $this->orderInfo['order_confirmation']; ?></td>
							</tr>
						<?php } ?>
						<?php if ($this->orderState == 'ACTIVE') { ?>
							<tr>
								<td style="text-align: right;">Tracking Number:&nbsp;</td>
								<td style="font-weight: bold;"><?php echo $this->orderInfo['tracking_number']; ?></td>
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
					include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_header_new_delivered.tpl.php');
				}
				else if ($this->orderState == 'SAVED')
				{
					include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_header_saved_delivered.tpl.php');
				}
				else
				{
					include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_header_delivered.tpl.php');
				}
				?>

				<div id="cancel_div"></div>

				<?php if ($this->discountEligable['limited_access']) { ?>
					<div style="text-align: center;"><h3 style="color:red;">This order is Canceled or Locked. Only Payments and Notes can be altered.</h3></div>
				<?php } ?>

				<div id="update_console">
					<span id="addPaymentAndActivate">
						<input id="addPaymentAndActivateButton" type="button" class="button" disabled="disabled" onclick="addPaymentAndActivate(this); return false;" value="Add Payment and Book Order" style="width:200px;" />
					</span>
					<span id="saveOrderSpan" style="float:left;">
						<input class="button" id="saveOrderButton" onclick="saveAll(); return false;" value="Save Order" />You have unsaved changes.</span>
					<span id="finalizeOrderSpan">
						<input class="button" name="submit_button" id ="submit_button" type="button" value="Finalize All Changes" onclick="submitPage();" />
						<span id="finalize_msg">You have unsaved changes</span>
					</span>

					<?php if ($this->orderState == 'NEW') { ?>
						<span id="om_instructions">Provide and set a ZIP Code to save a new order.</span>
					<?php } ?>
					<div id="payment_help_msg"></div>
					<div class="clear"></div>
					<div class="row">
					<?php if ($this->orderState == 'ACTIVE') { ?>
						<div class="col-6">
							<div id="autoAdjustDiv" class="collapse">
								<?php echo $this->form_direct_order['autoAdjust_html']; ?>
							</div>
						</div>
						<div class="col-6 text-right">
							<?php CForm::formElement(array(CForm::type => CForm::CheckBox, CForm::name => 'suppressEmail', CForm::label => 'Suppress sending confirmation email to guest')); ?>
						</div>
					<?php } ?>
					</div>
					<div id="help_msg" ></div>
					<div class="clear"></div>
					<div class="invalid-feedback form-feedback text-center font-weight-bold font-size-small">Missing required information, please check all tabs.</div>
				</div>

				<div class="accordion-content" data-tabid="mgr">
					<div class="accordion-container">
						<div class="tabs">
							<div id="delivery_tab_li" data-tabid="deliveryTab" class="accordion<?php if ($this->orderState == 'NEW') { ?> active<?php } ?>"  data-callback="onDeliveryTabSelected"
								 data-deselect_callback="onDeliveryTabDeselected"><h4 class="accordionTitle">Ship To Address</h4></div>
							<div class="panel" data-tabidcontent="deliveryTabContent" id="delivery">
								<?php include $this->loadTemplate('admin/subtemplate/order_mgr/order_mgr_shipping_address_delivered.tpl.php');?>
							</div>
							<div id="sessions_tab_li" data-tabid="sessionsTab" class="accordion<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>" data-callback="onSessionTabSelected"
								 data-deselect_callback="onSessionTabDeselected"><h4 class="accordionTitle">Delivery Date</h4></div>
							<div class="panel" data-tabidcontent="sessionsTabContent" id="sessions" class="background-color:#a0a0a0;">
								<?php include $this->loadTemplate('admin/subtemplate/order_mgr/delivery_date_selector.tpl.php');?>
							</div>
							<div id="items_tab_li" data-tabid="itemsTab" class="accordion<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>" data-callback="onItemsTabSelected"
								 data-deselect_callback="onItemsTabDeselected"><h4 class="accordionTitle">Items</h4></div>
							<div class="panel" data-tabidcontent="itemsTabContent" id="items">
								<?php include $this->loadTemplate('admin/order_mgr_items_delivered.tpl.php');?>
							</div>
							<div id="notes_tab_li" data-tabid="notesTab" class="accordion<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>"   data-callback="onNotesTabSelected"
								 data-deselect_callback="onNotesTabDeselected"><h4 class="accordionTitle">Notes/History</h4></div>
							<div class="panel" data-tabidcontent="notesTabContent" id="notes">
								<?php if ($this->orderState != 'NEW') {
									include $this->loadTemplate('admin/order_mgr_notes.tpl.php'); } ?>
							</div>
							<div id="payments_tab_li" data-tabid="discounts_paymentsTab" class="accordion<?php if ($this->orderState == 'NEW') { ?> disabled<?php } ?>"  data-callback="onPaymentTabSelected"
								 data-deselect_callback="onPaymentTabDeselected"><h4 class="accordionTitle">Discounts / Payments</h4></div>
							<div class="panel" data-tabidcontent="discounts_paymentsTabContent" id="payments">
								<?php include $this->loadTemplate('admin/order_mgr_payments_delivered.tpl.php');?>
							</div>
						</div>
					</div>
				</div>


	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>