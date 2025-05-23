Dream Dinners
RSVP and Order Confirmation

We're thrilled you'll be joining us for a Dream Dinners Fundraiser. Be ready to help a great cause and learn how Dream Dinners can be the solution to your dinnertime challenges.
Here's what to expect.

You will...
-	Experience how to save time and money with our simple cook-at-home meals.
-	Learn from our helpful team members who can answer any questions you have.
-	Receive three prepped meals to take home and cook for your family.
-	Find out about a special opportunity for you to help raise even more for your organization! When you sign up to return to Dream Dinners with a standard order,* we will donate an additional $20 to the organization.

Below is your RSVP summary and payment information. If you have any questions about this event, please contact your store by using the contact information below.

We look forward to meeting you!

----------------

Order for: <?php echo $this->customer_name ?>
attending <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE)?>
at <?php echo $this->sessionInfo['store_name']; ?>
Confirmation Number: <?php echo $this->orderInfo['order_confirmation'] ?>
Please keep this number for your records


Order Summary
---------------------------
Total Item Count			<?php echo $this->orderInfo['menu_items_total_count'] . "\n" ?>
<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_premium_markup']) ) { ?>
Quick 6 Premium			$<?php echo $this->moneyFormat($this->orderInfo['subtotal_premium_markup'])."\n" ?>
<?php } ?>
<?php if ( $this->orderInfo['user_preferred_discount_total'] ) { ?>
Preferred Discount			<?php echo $this->moneyFormat($this->orderInfo['user_preferred_discount_total']) . "\n" ?>
<?php } ?>
<?php if ( $this->orderInfo['direct_order_discount'] ) { ?>
Direct Order Discount			$<?php echo $this->moneyFormat($this->orderInfo['direct_order_discount']) . "\n" ?>
<?php } ?>
<?php if ( $this->orderInfo['promo_code_discount_total'] ) { ?>
Promotional Code Discount		$<?php echo $this->moneyFormat($this->orderInfo['promo_code_discount_total']) . "\n" ?>
<?php } ?>
Item Subtotal			$<?php echo $this->moneyFormat($this->orderInfo['subtotal_all_items']) . "\n" ?>
Tax Subtotal			$<?php echo $this->moneyFormat($this->orderInfo['subtotal_all_taxes']) . "\n" ?>
Total				$<?php echo $this->moneyFormat($this->orderInfo['grand_total']) . "\n" ?>
Special Instructions/Requests:	<?php if ($this->orderInfo['order_user_notes'] != NULL) {
	      echo $this->orderInfo['order_user_notes'] . "\n" ;
	    }
			else echo "\tnone" . "\n" ;?>

<?php

	if ( isset($this->paymentInfo) ) {
		 	echo "\nPayment Information\n";
		 	echo "-------------------\n";
			$counter = 0;
			foreach ( $this->paymentInfo as $arrItem ) {
				if (is_array($arrItem)) {
					if ($arrItem['payment_type'] === CPayment::CC) {
						$isDeposit = isset($arrItem['deposit']) ? " (Deposit) " : '' ;
						echo $arrItem['credit_card_type']['other'] . $isDeposit . "\nLast 4 digits: " . substr($arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other']));
					}
					else if ($arrItem['payment_type'] === CPayment::STORE_CREDIT)
						echo $arrItem['payment_info']['other'] . "\nLast 4 digits: " .
						 (isset($arrItem['payment_number']['other']) ? (substr($arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other']))) : '');
					else if ($arrItem['payment_type'] === CPayment::GIFT_CERT)
						echo $arrItem['payment_info']['other'] . "\nGift Type: " . $arrItem['gift_cert_type']['other'];
					else
						echo $arrItem['payment_info']['other'];
					echo "\t$" . $arrItem['total']['other'];
					$counter++;
				}
			}

			echo "\n";
		}
?>

<?php if ( isset($this->menuInfo) ) { ?>
Order Itemization
-----------------
Entrees
Qty		Item Price 	Dinner
<?php foreach ( $this->menuInfo as $id => $item ) { if ( is_numeric($id) and $item['qty'] ) { ?>
<?php echo $item['qty']?>		<?php echo $item['price'] ?>		<?php echo $item['display_title'] ?>
<?php }
} }?>

<?php if ( isset($this->sessionInfo) ) { ?>
Session Details
---------------
Time: <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE) . "\n";?>
Location: <?php echo $this->sessionInfo['store_name'] . "\n";?>
Address: <?php echo $this->sessionInfo['address_line1']?>
		<?php echo !empty($this->sessionInfo['address_line2']) ? $this->sessionInfo['address_line2'] . "<br />" : ""?>
		<?php echo $this->sessionInfo['city']?> <?php echo $this->sessionInfo['state_id']?> <?php echo $this->sessionInfo['postal_code']?>
Phone
    (Day): <?php echo $this->sessionInfo['telephone_day'] . "\n";?>
  (Evening): <?php echo $this->sessionInfo['telephone_evening'] . "\n";?>
		Fax: <?php echo $this->sessionInfo['fax'] . "\n";?>
<?php } ?>