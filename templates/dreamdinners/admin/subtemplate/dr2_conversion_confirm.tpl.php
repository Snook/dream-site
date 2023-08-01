<div>

<h2>Confirm Conversion to PLATEPOINTS</h2>

<div>
<p><b><?php echo $this->user_name?></b> is eligible to be converted to the PLATEPOINTS program.</p>

<p>This guest will be awarded <?php echo $this->conversion_data['points_award_display_value']?> points and a
$<?php echo $this->conversion_data['credit_award_display_value']?> credit award. This is based on an in program total spend
of $<?php echo $this->conversion_data['total_spend']?> (<?php echo $this->conversion_data['num_orders']?> orders).</p>

<p>Do you wish to proceed with the conversion? <br /><span style="font-size:smaller">After confirming you must still click the account Save button below to finalize the conversion.</span> </p>

</div>

<?php if ($this->has_opted_out) { ?>
 	<b>Guest has opted out</b>
 <p>
	This guest had requested to opt out. The guest may be opted in at any point so please ignore this message if you have a current request from this guest.
</p>



<?php } else { ?>

<h2>Opt out of PLATEPOINTS</h2>
<p>
	Marked the guest as having opted out of the PLATEPOINTS program. The guest will receive an email explaining the options.
	The guest can still join anytime.
</p>

<?php  } ?>

</div>