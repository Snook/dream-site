<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" align="left" valign="top" bgcolor="#f9fadc">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr>
					<td valign="top">
						<strong>Visit Details</strong><br /><br />
						<strong>Date and Time:</strong>
						<?= $this->sessionTypeDateTimeFormat($this->sessionInfo['session_start'], $this->sessionInfo['session_type_subtype'], VERBOSE);?>
						<?php if (!empty($this->sessionInfo['session_type_subtype']) &&
							($this->sessionInfo['session_type_subtype'] == CSession::DELIVERY ||
								$this->sessionInfo['session_type_subtype'] == CSession::REMOTE_PICKUP ||
								$this->sessionInfo['session_type_subtype'] == CSession::REMOTE_PICKUP_PRIVATE)) {
							echo ' - '.$this->sessionInfo['session_end_dtf_time_only'];
						} else if ($this->sessionInfo['session_type'] == CSession::SPECIAL_EVENT) {
							echo ' - '.$this->sessionInfo['session_end_dtf_time_only'];
						}?>
						<br />
						<?php if (!empty($this->sessionInfo["session_remote_location"])) { ?>
							<strong> Location:</strong>
							<?php echo $this->sessionInfo["session_remote_location"]->location_title; ?>
							- <?php echo $this->sessionInfo["session_remote_location"]->address_line1; ?><?php echo (!empty($this->sessionInfo["session_remote_location"]->address_line2)) ? ',' . $this->sessionInfo["session_remote_location"]->address_line2 : ''; ?>
							<?php echo $this->sessionInfo["session_remote_location"]->city; ?>,
							<?php echo $this->sessionInfo["session_remote_location"]->state_id; ?>
							<?php echo $this->sessionInfo["session_remote_location"]->postal_code; ?>
							<br />
						<?php } else { ?>
							<strong> Location:</strong>
							<?php echo $this->sessionInfo['store_name'] ?><br />
						<?php } ?>
						<strong>Order Confirmation:</strong> <a href="<?= HTTPS_BASE ?>main.php?page=<?= $this->details_page ?>&order=<?= $this->orderInfo['id'] ?>">
							<?= $this->orderInfo['order_confirmation']?>
						</a>
						<br />

						<?php if (!empty($this->orderInfo['order_user_notes'])) { ?>
							<p>Your notes to the store:<br />
								<?php echo $this->orderInfo['order_user_notes']; ?>
							</p>
						<?php } ?>
					</td>
					<?php if (!empty($this->sessionInfo['session_type_subtype']) && $this->sessionInfo['session_type_subtype'] == CSession::DELIVERY) { ?>
						<td valign="top">
							<strong>Delivery Details</strong><br /><br />
							<p>
								<?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?><br />
								<?php echo $this->orderInfo['orderAddress']['telephone_1']; ?>
							</p>
							<p>
								<?php echo $this->orderInfo['orderAddress']['address_line1']; ?><br />
								<?php if (!empty($this->orderInfo['orderAddress']['address_line2'])) { ?>
									<?php echo $this->orderInfo['orderAddress']['address_line2']; ?><br />
								<?php } ?>
								<?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?>
							</p>
							<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
								<p>Address Note: <?php echo $this->orderInfo['orderAddress']['address_note']; ?></p>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			</table>
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="10">
				<tr style="text-align: center; ">
					<td colspan="6"><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"><p><strong>Add to Your Calendar</strong></p></td>
				</tr>
				<tr style="text-align: center; ">
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/google-calendar-icon.png" style="width: 50; height: 50;" alt="Google"/></td>
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/apple-calendar-icon.png" style="width: 50; height: 50;" alt="Apple"/></td>
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/office-365-calendar-icon.png" style="width: 50; height: 50;" alt="Office365"/></td>
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/outlook-calendar-icon.png" style="width: 50; height: 50;" alt="Outlook"/></td>
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/yahoo-calendar-icon.png" style="width: 50; height: 50;" alt="Yahoo"/></td>
					<td><img src="<?=EMAIL_IMAGES_PATH?>/email/icons/outlook-com-calendar-icon.png" style="width: 50; height: 50;" alt="Outlook.com" /></td>
				</tr>
				<?php if(strpos($this->sessionInfo['session_type_subtype'],'PICKUP')){?>
					<tr style="text-align: center; ">
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=google">Google</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=apple">Apple</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=office365">Office365</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=outlook">Outlook</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=yahoo">Yahoo</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['session_remote_location']->address_line1.' '.$this->sessionInfo['session_remote_location']->address_line2.' '.$this->sessionInfo['session_remote_location']->city.', '.$this->sessionInfo['session_remote_location']->state_id.' '.$this->sessionInfo['session_remote_location']->postal_code?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=outlookcom">Outlook.com</a></td>
					</tr>
				<?php } else{?>
					<tr style="text-align: center; ">
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=google">Google</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=apple">Apple</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=office365">Office 365</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=outlook">Outlook</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=yahoo">Yahoo</a></td>
						<td> <a href="https://www.addevent.com/dir/?client=aYXluBzdEzgASemdQmwx111525&start=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_start'], DATE_TIME_ITEMPROP)?>&end=<?=CTemplate::dateTimeFormat($this->sessionInfo['session_end'], DATE_TIME_ITEMPROP)?>&title=Dream+Dinners+Session&location=<?=$this->sessionInfo['address_line1'].' '.$this->sessionInfo['address_line2'].' '.$this->sessionInfo['city'].', '.$this->sessionInfo['state_id'].' '.$this->sessionInfo['postal_code']?>&description=Bring+your+cooler+to+easily+transport+your+meals+home.+If+you+have+any+questions+regarding+this+or+any+other+Dream+Dinners+information+please+contact+the+store.+Email:+<?=$this->sessionInfo['email_address']?>+Phone:+<?=$this->sessionInfo['telephone_day']?>&timezone=<?=$this->sessionInfo['PHPTimeZone']?>&service=outlookcom">Outlook.com</a></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
	<tr>
		<td width="100%" align="right" valign="top" bgcolor="#f1f1f1">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr>
					<td>
						<?php if (!empty($this->membership) && $this->membership['status'] == CUser::MEMBERSHIP_STATUS_CURRENT) { ?>
							<!--Membership details-->
							<table role="presentation" width="100%" style="width:100%;">

								<tr>
									<td colspan="2" style="width: 100%; text-align:left;"><b>Meal Prep+ Membership</b></td>
								</tr>
								<tr>
									<td style="width: 70%; text-align:left;">
										This is <?php echo $this->membership['display_strings']['order_position']; ?> membership orders.<br />
										My last membership month is <?php echo $this->membership['display_strings']['completion_month']; ?>.<br />
										My membership savings to date is $<?php echo CTemplate::moneyFormat($this->membership['display_strings']['total_savings']); ?>.<br />
									</td>
									<td style="width: 30%; text-align:center;"><img src="<?=EMAIL_IMAGES_PATH?>/style/membership/meal-prep-plus-badge-119x119.png" style="width: 119px; height: 119px;" alt="Meal Prep+" /></td>
								</tr>
							</table>
						<?php } ?>

						<?php if (!empty($this->plate_points) && $this->plate_points['status'] == 'active') { ?>
							<!--PLATEPOINTS details-->
							<table role="presentation" width="100%" style="width:100%;">

								<tr>
									<td colspan="2" style="width: 100%; text-align:left;"><b>PlatePoints</b></td>
								</tr>
								<tr>
									<td style="width: 70%; text-align:left;">
										<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="2" style="width: 100%;">
											<tr align="center">
												<td colspan="2"></td>
											</tr>
											<?php if (!$this->isEmptyFloat($this->plate_points['points_this_order'])) { ?>
												<tr>
													<td>Points earned this order:</td>
													<td><?php echo number_format($this->plate_points['points_this_order']); ?></td>
												</tr>
											<?php } ?>
											<?php if (!empty($this->plate_points['available_credit'])) { ?>
												<tr>
													<td>Available Dinner Dollars:</td>
													<td>$<?php echo CTemplate::moneyFormat($this->plate_points['available_credit']); ?></td>
												</tr>
											<?php } ?>
											<?php if (!$this->isEmptyFloat($this->plate_points['next_expiring_credit_amount'])) { ?>
												<tr>
													<td>Next Dinner Dollar Expiration:</td>
													<td>$<?php echo number_format($this->plate_points['next_expiring_credit_amount']) ?> on <br /><?php echo $this->plate_points['next_expiring_credit_date'] ?></td>
												</tr>
											<?php } ?>

										</table>
									</td>
									<td style="width: 30%; text-align:center;"></td>
								</tr>
							</table>
						<?php } else { ?>
							<p><strong>Rate Your Meals</strong><br />
								Did you know rating your meals helps us decide on future menus? If you want to see your favorites back on the menu, make sure you are rating your meals each month.</p>
							<p><a href="<?=HTTPS_BASE ?>main.php?page=my_meals">Rate your meals now &gt;</a></p>

						<?php } ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="3"><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<?php if (isset($this->show_GC_message) && $this->show_GC_message) { ?>
				<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 1px solid #CACACA; ">
					<tr>
						<td>
							<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/giftcards/dd_giftcard_new.gif" border="0" alt="" width="60" height="39" />
						</td>
						<td>
							Gift Card Purchased	(A separate email will be sent.)
						</td>
					</tr>
				</table>
			<?php } ?>
		</td>
	</tr>

	<?php if (!$this->isEmptyFloat($this->orderInfo['ltd_round_up_value'])) { ?>
		<tr>
			<td colspan="3">
				<div class="ltd_round_up" style="margin: 0 20px 10px 20px;">
					Thank you for your support of Dream Dinners Foundation's fight against hunger. Your donation of $<?php echo CTemplate::moneyFormat($this->orderInfo['ltd_round_up_value']); ?> has helped provide nutritious meals abroad.
				</div>
			</td>
		</tr>
	<?php } ?>

	<tr>
		<td colspan="3"><?php
			if( isset( $this->menuInfo ) ) {
				?>
				<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr bgcolor="#b9bf33">
						<td class="cart_ordered" style="padding: 5px;" align="center" width="40">Qty</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="70">Size</td>
						<td class="cart_ordered" style="padding: 5px;" width="200">Menu Item</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="50"><?= $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 'Item Price' : 'Family Savings Price' ?></td>
						<td class="cart_ordered" style="padding: 5px;" width="25"> </td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="50">Total Price</td>
					</tr>
					<?php
					foreach ($this->menuInfo as $categoryName => $subArray) {

						if (($this->orderInfo['type_of_order'] == 'INTRO' || $this->orderInfo['type_of_order'] == 'DREAM_TASTE' || $this->orderInfo['type_of_order'] == 'FUNDRAISER') && $categoryName == 'bundle_items')
						{
							continue;
						}

						//$showedCategory = false;
						if (is_array($subArray))
							foreach ( $subArray as $id => $item ) { if ( is_numeric($id) && $item['qty'] ) {

								/*
								if (!$showedCategory)
								{
									$showedCategory = true;
									echo '<tr><td>' . $categoryName . '</td></tr>';
								}
								*/
								?>
								<tr>
									<td class="customersData" style="padding: 5px;" align="center" valign="top"><?= $item['qty'] ?></td>
									<td class="customersData" style="padding: 5px;" align="center" valign="top"><?=!empty($item['is_chef_touched']) ? "" : (CMenuItem::translatePricingType($item['pricing_type']));?></td>
									<td class="customersData" style="padding: 5px;" valign="top"><?= $item['display_title'] ?></td>
									<td class="customersData" style="padding: 5px;" align="right" valign="top">
										<?php
										$itemPrice = 0.0;
										$numItemServings = isset($item['servings_per_item']) ? $item['servings_per_item'] : (CMenuItem::translatePricingTypeToNumeric($item['pricing_type']));

										if( !$this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ) {
											if( isset( $item['discounted_price'] ) ) {

												$itemPrice = $this->moneyFormat( round( $item['discounted_price'], 2 ) );
											} else {


												$itemPrice = $this->moneyFormat( COrders::getItemDiscountedPrice( $item['price'],
													$this->orderInfo['number_servings'], $numItemServings, $this->menuInfo['markup_discount_scalar'] ) );
											}
										} else {
											if( isset( $this->menuInfo['items_total_count'] ) ) {
												//echo '$', $this->menuInfo['items_total_count'] < 12 ? $this->moneyFormat( $item['premium_price'] ) : $this->moneyFormat( $item['price'] );
												$itemPrice = $this->menuInfo['items_total_count'] < 12 ? $this->moneyFormat( $item['premium_price'] ) : $this->moneyFormat( $item['price'] );
											} else {
												if( isset($item['discounted_price']) && !$this->isEmptyFloat( $item['discounted_price'] ) ) {
													$itemPrice = $this->moneyFormat( $item['discounted_price'] );
												} else {
													$itemPrice = $this->moneyFormat( $item['price'] );
												}
											}
										}
										echo '$', $itemPrice;
										?>
									</td>
									<td style="width: 25px;"> </td>
									<td class="customersData" style="padding: 5px;" align="right" valign="top">$<?= $this->moneyFormat( $item['qty'] * $itemPrice ) ?></td>
								</tr>
								<?php
							} }
					}
					if( isset( $this->menuInfo['promo_item'] ) ) {
						$numPromoItemServings = isset($this->menuInfo['promo_item']['servings_per_item']) ? $this->menuInfo['promo_item']['servings_per_item'] : (CMenuItem::translatePricingTypeToNumeric($this->menuInfo['promo_item']['pricing_type']));
						?>
						<tr>
							<td class="customersData" style="padding: 5px;" align="center">1</td>
							<td class="customersData" style="padding: 5px;" align="center"><?=$numPromoItemServings == 3 ? "3 servings" : "6 servings";?></td>
							<td width="1"> </td>
							<td class="customersData" style="padding: 5px;"><?=$this->menuInfo['promo_item']['display_title']?> (Promotion)</td>
							<td width="1"> </td>
							<td class="customersData" style="padding: 5px;" align="right"> $<?php echo $this->moneyFormat($this->menuInfo['promo_item']['price']); ?></td>
							<td width="1"> </td>
						</tr>
					<?php } ?>
					<?php if( isset( $this->menuInfo['free_meal_item'] ) ) {
						$numFreeMealItemServings = isset($this->menuInfo['free_meal_item']['servings_per_item']) ? $this->menuInfo['free_meal_item']['servings_per_item'] : (CMenuItem::translatePricingTypeToNumeric($this->menuInfo['free_meal_item']['pricing_type']));
						?>
						<tr>
							<td class="customersData" style="padding: 5px;" align="center">1</td>
							<td class="customersData" style="padding: 5px;" align="center"><?=$numFreeMealItemServings == 3 ? "3 servings" : "6 servings";?></td>
							<td width="1"> </td>
							<td class="customersData" style="padding: 5px;"><?=$this->menuInfo['free_meal_item']['display_title']?> (Coupon - Free Item)</td>
							<td width="1"> </td>
							<td class="customersData" style="padding: 5px;" align="right"> $<?php echo $this->moneyFormat($this->menuInfo['free_meal_item']['price']); ?></td>
							<td width="1"> </td>
						</tr>
					<?php } ?>
				</table>

			<?php } ?>
		</td>
	</tr>

	<tr>
		<td colspan="3"><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="2" width="325px" style="vertical-align:top; ">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php

				if ( isset( $this->paymentInfo ) ) {
					echo '<tr><td class="sectionhead" colspan="3"><b>Payment Information</b></td></tr>';
					$counter = 0;
					foreach( $this->paymentInfo as $arrItem ) {
						if( is_array( $arrItem ) ) {
							echo "<tr>";
							if( $arrItem['payment_type'] === CPayment::CC )
							{
								$isDeposit = isset( $arrItem['deposit'] ) ? '<i>(Status: Deposit Processed)</i> ' : '' ;
								$isDelayed = isset( $arrItem['delayed_status'] ) ? '<i>(' . $arrItem['delayed_status']['other'] . ')</i> ' : '';
								echo '<td><b>'. $arrItem['credit_card_type']['other'] . '</b> ' . $isDeposit . $isDelayed . '<br />';
								echo 'Last 4 digits: ' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
							}
							else if( $arrItem['payment_type'] === CPayment::GIFT_CARD )
							{
								echo '<b>Dream Dinners Debit Gift Card</b><br />';
								echo 'Last 4 digits: ' . substr( $arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other'])) . '<br />';
							}
							else if( $arrItem['payment_type'] === CPayment::STORE_CREDIT )
							{
								if (isset($arrItem['payment_number']))
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Last 4 digits: ' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
								else
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />';
							}
							else if( $arrItem['payment_type'] === CPayment::GIFT_CERT )
							{
								echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Gift Certificate Type: ' . $arrItem['gift_certificate_type']['other'] . '<br />';
							}
							else
							{
								echo '<td><b>' . $arrItem['payment_info']['other']. '</b><br />';
							}

							if (isset($arrItem['delayed_date']))
								echo 'Date: ' . $arrItem['delayed_date']['other'] . '<br />';
							else
								echo 'Date: ' . $arrItem['paymentDate']['other'] . '<br />';

							echo 'Amount: $' . $arrItem['total']['other'] . '</td>';
							echo '</tr>';
							if (isset($arrItem['payment_note']) && isset($arrItem['payment_note']['other']) && !empty($arrItem['payment_note']['other']))
								echo '<tr><td colspan="3">Note: ' . $arrItem['payment_note']['other'] . '</td></tr>';
							$counter++;
						}

					}
				}
				?>

			</table></td>
		<td width="325px" style="vertical-align:top;">
			<table role="presentation" border="0" width="100%">
				<tr>
					<td align="right">Total Item Count:</td>
					<td align="right"><?= $this->orderInfo['menu_items_total_count'] ?></td>
					<td> </td>
				</tr>
				<tr>
					<td align="right"><?= $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 'Item Subtotal:' : 'Discounted Item Subtotal:' ?></td>
					<td align="right">$<?= $this->moneyFormat( $this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_products'] + $this->orderInfo['subtotal_home_store_markup'] -
							( $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 0 : $this->orderInfo['family_savings_discount'] ) -
							( $this->isEmptyFloat( $this->orderInfo['bundle_discount'] ) ? 0 : $this->orderInfo['bundle_discount'] ) -
							( $this->isEmptyFloat( $this->orderInfo['subtotal_menu_item_mark_down'] ) ? 0 : $this->orderInfo['subtotal_menu_item_mark_down'] )); ?></td>
					<td> </td>
				</tr>

				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Food ( <?=$this->orderInfo['misc_food_subtotal_desc']?> ):</td>
						<td align="right">$<?= $this->moneyFormat($this->orderInfo['misc_food_subtotal']) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Non-Food ( <?=$this->orderInfo['misc_nonfood_subtotal_desc']?> ):</td>
						<td align="right">$<?= $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']) ?></td>
						<td> </td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['volume_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Volume Reward:</td>
						<td align="right">-<?= $this->moneyFormat($this->orderInfo['volume_discount_total']) ?></td>
						<td> </td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['points_discount_total'] ) ) { ?>
					<tr>
						<td align="right">PlatePoints Dinner Dollars:</td>
						<td align="right">-<?= $this->moneyFormat($this->orderInfo['points_discount_total']) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['user_preferred_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Preferred Discount:</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['user_preferred_discount_total'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['dream_rewards_discount'] ) ) { ?>
					<tr>
						<td align="right">Dream Rewards Discount:</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['dream_rewards_discount'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['direct_order_discount'] ) ) { ?>
					<tr>
						<td align="right">Direct Order Discount:</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['direct_order_discount'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['membership_discount'] ) ) { ?>
					<tr>
						<td align="right">Meal Prep+ Discount:</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['membership_discount'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['promo_code_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Promotional Code Discount:</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['promo_code_discount_total'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['coupon_code_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Promo Code Discount (<?=$this->orderInfo['coupon_title']?>):</td>
						<td align="right">-<?= $this->moneyFormat( $this->orderInfo['coupon_code_discount_total'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_fee'] ) || $this->orderInfo['service_fee_description'] == "Free Assembly Promo" ) { ?>
					<tr>
						<td align="right">Service Fees:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_fee'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>


				<?php if( $this->orderInfo['opted_to_customize_recipes'] == 1 ) { ?>
					<tr>
						<td align="right">Customization Fee:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_meal_customization_fee'] ) ?></td>
						<td> </td>
					</tr>
					<tr>
						<td align="right"><?= $this->meal_customization_string ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_bag_fee'] ) ) { ?>
					<tr>
						<td align="right">Bag Fees:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_bag_fee'] ) ?></td>
						<td> </td>
					</tr>
				<?php }else{ ?>
					<?php if ($this->orderInfo['opted_to_bring_bags']) { ?>
						<tr>
							<td align="right">Bag Fees:</td>
							<td align="right">I will bring my own bag</td>
							<td> </td>
						</tr>
					<?php } ?>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_fee'] )) { ?>
					<tr>
						<td align="right">Delivery Fee:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_delivery_fee'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<tr>
					<td align="right">Food Tax:</td>
					<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_food_sales_taxes'] ) ?></td>
					<td> </td>
				</tr>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_tax'] ) ) { ?>
					<tr>
						<td align="right">Service Tax:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_tax'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_tax'] ) ) { ?>
					<tr>
						<td align="right">Delivery Fee Tax:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_delivery_tax'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_bag_fee_tax'] ) ) { ?>
					<tr>
						<td align="right">Bag Fee Tax:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_bag_fee_tax'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_sales_taxes'] ) ) { ?>
					<tr>
						<td align="right">Non-Food Tax:</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_sales_taxes'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>



				<tr>
					<td align="right"><b>Menu Order Total:</b></td>
					<td align="right"><b>$<?= $this->moneyFormat($this->orderInfo['grand_total']) ?></b></td>
					<td> </td>
				</tr>

				<?php
				if (empty($this->orderInfo['bundle_id'])) {
					// older olders
					if (isset($this->orderInfo['average_per_serving_cost']) && $this->orderInfo['average_per_serving_cost'] >0 && !$this->isEmptyFloat($this->orderInfo['average_per_serving_cost']) ) { ?>
						<tr>
							<td align="right">Average Cost Per Serving:</td>
							<td align="right"><?= $this->moneyFormat($this->orderInfo['average_per_serving_cost']) ?></td>
							<td> </td>
						</tr>
					<?php } else if (!empty($this->orderInfo['servings_total_count']) && $this->orderInfo['servings_total_count']>0) {
						$basisAdjustment = COrders::getBasisAdjustment($this->menuInfo);
						?>
						<tr>
							<td align="right">Avg Cost Per Serving for Dinners:</td>
							<td align="right">$<?= $this->moneyFormat( COrders::averageCostPerServing($this->orderInfo, false, $basisAdjustment) ) ?></td>
							<td> </td>
						</tr>
					<?php } } ?>

				<?php if (!$this->isEmptyFloat($this->orderInfo['ltd_round_up_value'])) { ?>
					<tr>
						<td align="right">Donation Total:</td>
						<td align="right">$<?php echo CTemplate::moneyFormat($this->orderInfo['ltd_round_up_value']); ?></td>
						<td> </td>
					</tr>
				<?php } ?>

			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" bgcolor="#5c6670" style="padding: 2px;"><p align="center"> </p></td>
	</tr>
	<tr>
		<td colspan="3" align="left" style="padding: 15px;">
			<p><b>Not feeling well?</b><br />
				If you are experiencing a fever or other illness symptoms within 24 hours of your pick up or assembly session, please call <?= $this->sessionInfo['telephone_day'] ?> to reschedule your visit.</p>
			<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_meal_customization_fee'] ) ) { ?>
			<p><b>Meal Customization</b><br />
				<?php echo OrdersCustomization::RECIPE_LEGAL;
				} ?>
			<p><b>Reschedule and Cancellation Policy</b><br />
				If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee. During inclement weather, please contact your local store to see if your session has been canceled. In the event the store must close, information will be provided on the store's voicemail and every effort will be made to reschedule your session.</p>
			<p><b>Allergens</b><br /> In compliance with the "Food Allergen Labeling and Consumer Protection Act of 2004" please note that Dream Dinners' facilities may contain Dairy, Eggs, Crustacean Shellfish, Fish, Tree Nuts, Peanuts, Wheat, Soybeans and Sesame which account for most known allergens. Although Dream Dinners' store staff take appropriate safety measures, guests should be aware that cross contamination can occur among food products in store kitchens and at stations. The standard ingredients are available upon request from your local store; however, ingredient substitutions can be made at the store level due to regional availability. If guests feel that there may be a chance of allergens in any recipe, especially due to pre-made ingredients, they need to call the store to ask for specific nutritional information.</p>
			<p><b>Policies and Terms</b><br />
				By participating in the Dream Dinners program, you agree to the <a href="https://dreamdinners.com/main.php?static=terms">Policy and Terms</a>.</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="3" align="center"><p align="center">If you have questions please contact us at
				<?= $this->sessionInfo['telephone_day'] ?> or via email by replying.<br />
														 Dream Dinners <?=$this->sessionInfo['store_name']?><br /><?= $this->sessionInfo['address_line1'] ?>, <?= !empty( $this->sessionInfo['address_line2'] ) ? $this->sessionInfo['address_line2'] . '<br />' : '' ?> <?= $this->sessionInfo['city'] ?> <?= $this->sessionInfo['state_id'] ?> <?= $this->sessionInfo['postal_code'] ?><br />
				<a href="<?=$this->sessionInfo['map']?>">Get Directions</a></p>
		</td>
	</tr>
</table>