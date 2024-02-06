Taste of Dream Dinners
Party Canceled

Dear <?php echo $this->customer_name ?>,
Your registration for a Taste of Dream Dinners party on <?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?> at our <?php echo $this->sessionInfo['store_name']?>
location has been canceled.

If you have any questions or concerns regarding this cancellation please contact your party host or the <?php echo $this->sessionInfo['store_name']?> location.
<?php echo HTTPS_BASE?>locations/<?php echo $this->store_id?>

Thank you,
Dream Dinners

------------------------------------
Learn more about Dream Dinners:
How It Works - <?php echo HTTPS_BASE?>howitworks