<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>
			To view your account information or reschedule an order, go to <a href="<?= HTTPS_BASE ?>my-account">My Account</a>.
		</td>
	</tr>
</table>
<br />
<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dot_h_515.gif" border="0" alt="" width="515" height="20" />
<br />
<table border="0">
	<tr>
		<td colspan="3" class="sectionhead" style="padding: 10px;"><b>Order Summary</b></td>
	</tr>
	<tr>
		<td>Item Ordered:</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<?= $this->dinnerName?></td>
		<td align="right">$<?= $this->dinnerPrice?></td>
		<td>&nbsp;</td>
	</tr>

	<?php if( !$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ) { ?>
		<tr>
			<td>Misc Food ( <?=$this->orderInfo['misc_food_subtotal_desc']?> )</td>
			<td align="right">$<?= $this->moneyFormat($this->orderInfo['misc_food_subtotal']) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ) { ?>
		<tr>
			<td>Misc Non-Food ( <?=$this->orderInfo['misc_nonfood_subtotal_desc']?> )</td>
			<td align="right">$<?= $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>
	<?php if( !$this->isEmptyFloat( $this->orderInfo['volume_discount_total'] ) ) { ?>
		<tr>
			<td>Volume Reward</td>
			<td align="right">-<?= $this->moneyFormat($this->orderInfo['volume_discount_total']) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['user_preferred_discount_total'] ) ) { ?>
		<tr>
			<td>Preferred Discount</td>
			<td align="right">-<?= $this->moneyFormat( $this->orderInfo['user_preferred_discount_total'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['dream_rewards_discount'] ) ) { ?>
		<tr>
			<td>Dream Rewards Discount</td>
			<td align="right">-<?= $this->moneyFormat( $this->orderInfo['dream_rewards_discount'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['direct_order_discount'] ) ) { ?>
		<tr>
			<td>Direct Order Discount</td>
			<td align="right">-<?= $this->moneyFormat( $this->orderInfo['direct_order_discount'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['promo_code_discount_total'] ) ) { ?>
		<tr>
			<td>Promotional Code Discount</td>
			<td align="right">-<?= $this->moneyFormat( $this->orderInfo['promo_code_discount_total'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['coupon_code_discount_total'] ) ) { ?>
		<tr>
			<td>Promo Code Discount (<?=$this->orderInfo['coupon_title']?>)</td>
			<td align="right">-<?= $this->moneyFormat( $this->orderInfo['coupon_code_discount_total'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_fee'] ) or  $this->orderInfo['service_fee_description'] == "Free Assembly Promo" ) { ?>
		<tr>
			<td>Service Fees</td>
			<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_fee'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<tr>
		<td>Food Tax</td>
		<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_food_sales_taxes'] ) ?></td>
		<td>&nbsp;</td>
	</tr>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_tax'] ) ) { ?>
		<tr>
			<td>Service Tax</td>
			<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_tax'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_tax'] ) ) { ?>
		<tr>
			<td>Delivery Fee Tax</td>
			<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_delivery_tax'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_sales_taxes'] ) ) { ?>
		<tr>
			<td>Non-Food Tax</td>
			<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_sales_taxes'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<?php // older olders
	if (isset($this->orderInfo['average_per_serving_cost']) and !$this->isEmptyFloat($this->orderInfo['average_per_serving_cost']) ) { ?>
		<tr>
			<td>Average Cost Per Serving</td>
			<td align="right"><?= $this->moneyFormat($this->orderInfo['average_per_serving_cost']) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } else if (!empty($this->orderInfo['servings_total_count'])) {
		$basisAdjustment = COrders::getBasisAdjustment($this->menuInfo);
		?>
		<tr>
			<td>Avg Cost Per Serving for Dinners</td>
			<td align="right">$<?= $this->moneyFormat( COrders::averageCostPerServing($this->orderInfo, false, $basisAdjustment) ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<tr>
		<td><b>Total</b></td>
		<td align="right"><b>$<?= $this->moneyFormat($this->orderInfo['grand_total']) ?></b></td>
		<td>&nbsp;</td>
	</tr>

	<tr>
		<td><br />Order Type:</td>
		<td align="right"><br />
			<?php

			// CES TODO: There must be a better way..
			foreach($this->menuInfo as $cats)
			{

				if (is_array($cats))
				{
					$anItem = array_rand($cats);
					if ($cats[$anItem]['pricing_type'] == 'INTRO')
					{
						if ($cats[$anItem]['servings_per_item'] == 6)
							echo " (Meal Prep Starter Pack)&nbsp;&nbsp;";
						else
							echo " (Menu Sampler)&nbsp;&nbsp;";
					}
					break;
				}
			}
			echo $this->orderInfo['order_type'];
			?>
		</td>
		<td>&nbsp;</td>
	</tr>

	<tr>
		<td valign="top">Special Instructions/Requests:</td>
		<td valign="top" colspan="2">&nbsp;
			<?php
			if( $this->orderInfo['order_user_notes'] != NULL ) {
				echo $this->orderInfo['order_user_notes'];
			} else {
				echo "none";
			}
			?>
		</td>
	</tr>

	<?php if( !$this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ) { ?>
		<tr height="15"><td colspan="3">&nbsp;</td></tr>
		<tr>
			<td>Your total family savings is </td>
			<td align="right">$<?= $this->moneyFormat( $this->orderInfo['family_savings_discount'] ) ?></td>
			<td>&nbsp;</td>
		</tr>
	<?php } ?>

	<tr><td colspan="3"><br /><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dot_h_515.gif" border="0" alt="" width="515" height="20" /></td></tr>
	<?php

	if ( isset( $this->paymentInfo ) ) {
		echo '<tr><td  class="sectionhead" style="padding: 10px;" colspan="3"><b>Payment Information</b></td></tr>';
		$counter = 0;
		foreach( $this->paymentInfo as $arrItem ) {
			if( is_array( $arrItem ) ) {
				echo "<tr>";
				if( $arrItem['payment_type'] === CPayment::CC ) {
					$isDeposit = isset( $arrItem['deposit'] ) ? '<i>(Deposit)</i>&nbsp;' : '' ;
					$isDelayed = isset( $arrItem['delayed_status'] ) ? '<i>(' . $arrItem['delayed_status']['other'] . ')</i>&nbsp;' : '';


					echo '<td><b>'. $arrItem['credit_card_type']['other'] . '</b>&nbsp;&nbsp;' . $isDeposit . $isDelayed . '<br />';
					echo 'Last 4 digits:&nbsp;' .  substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
				} else if( $arrItem['payment_type'] === CPayment::STORE_CREDIT ) {
					if (isset($arrItem['payment_number']))
						echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Last 4 digits:&nbsp;' .  substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
					else
						echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />';
				} else if( $arrItem['payment_type'] === CPayment::GIFT_CERT ) {
					echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Gift Type:&nbsp;' . $arrItem['gift_cert_type']['other'] . '<br />';
				} else {
					echo '<td><b>' . $arrItem['payment_info']['other']. '</b><br />';
				}
				echo 'Payment Date:&nbsp;' . $arrItem['paymentDate']['other'] . '</td>';
				echo '<td align="right" valign="bottom">$' . $arrItem['total']['other'] . '</td>';
				echo '<td>&nbsp;</td>';
				echo '</tr>';
				if (isset($arrItem['payment_note']) and isset($arrItem['payment_note']['other']) and !empty($arrItem['payment_note']['other']))
					echo '<tr><td colspan="3">Payment Note:&nbsp;' . $arrItem['payment_note']['other'] . '</td></tr>';
				$counter++;
			}

		}
	}
	?>
</table>


<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/dot_h_515.gif" border="0" alt="" width="515" height="20" />
<br />
<?php
if( isset( $this->sessionInfo ) ) {
	?>

	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="sectionhead" colspan="2"><?=$this->sessionInfo['session_type'] == 'SPECIAL_EVENT' ? 'Pickup' : 'Party' ?> Details</td>
		</tr>
		<tr>
			<td style="padding: 5px;" valign="top" width="30">Time </td>
			<td style="padding: 5px;" colspan="3"><b><?=$this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE)?></b></td>
		</tr>
		<tr>
			<td style="padding: 5px;" valign="top" width="30">Location </td>
			<td style="padding: 5px;" colspan="3"><?=$this->sessionInfo['store_name']?></td>
		</tr>
		<tr>
			<td style="padding: 5px;" valign="top" width="30">Address </td>
			<td style="padding: 5px;" width="210" valign="top"><?= $this->sessionInfo['address_line1'] ?><br />
				<?= !empty( $this->sessionInfo['address_line2'] ) ? $this->sessionInfo['address_line2'] . '<br />' : '' ?>
				<?= $this->sessionInfo['city'] ?>&nbsp;<?= $this->sessionInfo['state_id'] ?>&nbsp;<?= $this->sessionInfo['postal_code'] ?><br />
				<a href="<?=$this->sessionInfo['map']?>">Map</a>
			</td>
			<td style="padding: 5px;" align="right" valign="top" width="100">Phone&nbsp;(Day)<br />Phone&nbsp;(Evening):<br />Fax</td>
			<td style="padding: 5px;" valign="top"><?= $this->sessionInfo['telephone_day'] ?><br /><?= $this->sessionInfo['telephone_evening'] ?><br /><?= $this->sessionInfo['fax'] ?></td>
		</tr>
	</table>

	<?php
}
?>