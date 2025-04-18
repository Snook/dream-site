Dream Dinners
Advanced Order - Session Balance Due

Dear <?php echo $this->customer_name?>,

Our records indicate that you placed and Advanced Order from a Dream Dinners location.

Your session is: <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], VERBOSE)?>

At the following store: <?php echo $this->sessionInfo['store_name']; ?>

However, in order to keep your order and your spot reserved, you need to pay for your
session in full at this time. If your session is not paid in full, within 48 hours,
your spot and order will be released.

To pay now, go to: <?php echo HTTPS_BASE?>order-details?order=<?php echo $this->orderInfo['id']?>

Once you've paid, you will be sent a reminder email of your session,
3 days prior to your session starting.

Sincerely,
DreamDinners - staff