  Dream Dinners
  PLATEPOINTS Program

	Dear <?php echo $this->userObj->firstname;?>,
	You have earned <?php echo CTemplate::moneyFormat($this->amount);?> Dinner Dollars*. Dinner Dollars can only be applied to standard orders above 36 servings. Spend them on items in our Sides & Sweets freezer or apply them toward your next Made For You fee at participating locations. When purchasing more than 36 servings, Dinner Dollars can be applied toward the lowest-priced menu item.

  Your total available Dinner Dollars are: $<?php echo CTemplate::moneyFormat($this->total_available_credit);?>

	We look forward to seeing you soon,
	Dream Dinners

-------------------------------------------------------------------------
Order: <?php echo HTTPS_BASE ?>session-menu
My Account: <?php echo HTTPS_BASE ?>my-account
My PLATEPOINTS: <?php echo HTTPS_BASE ?>my-platepoints

*Dinner Dollars expire 6 months from the date awarded and can be redeemed at participating locations.