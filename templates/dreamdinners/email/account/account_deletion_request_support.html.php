<p>Account Deletion Request</p>
<p>The customer below has deleted their account and personal data.</p>
<p>Deleted Customer's Information:

</p>
<ul>
	<li>ID: <?php echo $this->user->id; ?></li>
	<li>Name: <?php echo $this->user->firstname; ?> <?php echo $this->user->lastname; ?></li>
	<li>Email: <?php echo $this->user->primary_email; ?></li>
	<li>Home Store: <?php echo $this->store->store_name; ?></li>
	<li>Stores Ordered From: <?php echo $this->order_store_names; ?></li>
</ul>
