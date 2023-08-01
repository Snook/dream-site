    Dream Dinners
    PLATEPOINTS Program

	Hello <?php echo $this->firstname;?>,
	Don't forget! You have Dinner Dollars that are expiring soon. Dinner Dollars can only be applied to standard orders above 36 servings. Spend them on items in our Sides & Sweets freezer or apply them toward your next Made For You fee at participating locations. When purchasing more than 36 servings, Dinner Dollars can be applied toward the lowest-priced menu item.

    Too busy to come in for a session? Use your Dinner Dollars on Made for You fees and have us assemble your order for you.

	Dinner Dollars details:
  <?php foreach ($this->creditArray as $id => $credit) {?>
  #<?php echo $id?> - $<?php echo CTemplate::moneyFormat($credit['amount']);?> - <?php echo CTemplate::dateTimeFormat($credit['expiration_date']);?>
  <?php } ?>

  Sincerely,
	Dream Dinners

-------------------------------------------------------------------------
Order: <?=HTTPS_BASE ?>main.php?page=session_menu
My Account: <?=HTTPS_BASE ?>main.php?page=my_account
My PLATEPOINTS: <?=HTTPS_BASE ?>main.php?page=my_platepoints
