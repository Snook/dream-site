Taste of Dream Dinners
Party Canceled

Dear <?= $this->customer_name ?>,
Your registration for a Taste of Dream Dinners party on <?=$this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL);?> at our <?=$this->sessionInfo['store_name']?>
location has been canceled.

If you have any questions or concerns regarding this cancellation please contact your party host or the <?=$this->sessionInfo['store_name']?> location.
<?=HTTPS_BASE?>locations/<?=$this->store_id?>

Thank you,
Dream Dinners

------------------------------------
Learn more about Dream Dinners:
How It Works - <?=HTTPS_BASE?>howitworks