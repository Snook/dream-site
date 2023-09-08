Dream Dinners
Meal Prep+ Membership Order Confirmation

Thank you for joining Dream Dinners Meal Prep+.

As a Meal Prep+ member, you will join an exclusive club of Dream Dinners guests who have fully embraced Dream Dinners as their dinnertime solution. No more last-minute trips to the grocery store or endless drive thru lines, you are prepared for whatever the week brings.

By signing up for Meal Prep+, you will receive 10% off all Dream Dinners meals, sides & sweets for the next 6 months. Plus, you have access to shop from the freezer all month long. Need an extra dessert, a few more meals or even just a side or two? You are covered.

On average, our guests save over $100 during their membership period. Now pat yourself on the back for being such a savvy shopper. For additional information on your new Meal Prep+ membership, contact your local Dream Dinners store.

------------------------------------------

6-Month Meal Prep+ Membership: $<?php echo $this->order_details->grand_total; ?>

Membership Purchase Date: <?php echo CTemplate::dateTimeFormat($this->user->membershipData['enrollment_date'], VERBOSE); ?>

Membership End Date: <?php echo $this->user->membershipData['completion_month']; ?>

------------------------------------------

See the complete Meal Prep+ terms and conditions: https://dreamdinners.com/?static=terms

If you have questions please contact us at <?php echo $this->order_details->storeObj->telephone_day ?> or via email by replying.

Dream Dinners <?php echo $this->order_details->storeObj->store_name; ?>
<?php echo $this->order_details->storeObj->address_line1; ?> <?php echo !empty( $this->order_details->storeObj->address_line2 ) ? $this->order_details->storeObj->address_line2 : ''; ?>
<?php echo $this->order_details->storeObj->city; ?>, <?php echo $this->order_details->storeObj->state_id; ?> <?php echo $this->order_details->storeObj->postal_code; ?>