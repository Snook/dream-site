<?php
// constants for all report pages.
$PAGETITLE = "Master Preorder Export";
$HIDDENPAGENAME = "admin_reports_national_entree_projection";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;
$ADDFORMTOPAGE=TRUE;
?>

<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>
<script>

	function copyEmail(prefix)
	{
		let emails = [];
		$("[id^=" + prefix + "]").each(function(){
			let thisEmail = $(this).html().trim();
			emails.push(thisEmail);
		});

		var text = emails.join(";");
		navigator.clipboard.writeText(text).then(function() {
			console.log('Async: Copying to clipboard was successful!');
		}, function(err) {
			console.error('Async: Could not copy text: ', err);
		});

	}

</script>

<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
<input type="checkbox" id=test_stores" name="test_stores"> <label for="test_stores">Run for Test Menu Stores (if no test menu stores exist then all actjive stores are returned)</label><br />
<input type="checkbox" id=dry_run" name="dry_run"> <label for="dry_run">Return Statistics instead of Export</label>

</form>

<?php if (isset($this->stats)) {?>
<div class="container">
	<div class="row">
		<div class="col text-center ME_header_zebra_even h4">
			Stores without Inventory
		</div>
	</div>
	<div class="row font-weight-bold ME_header_zebra_odd">
		<div class="col-2">
			Store Name
		</div>
		<div class="col-1">
			State
		</div>
		<div class="col-4">
			Email  <button onclick="copyEmail('nf_');">Copy Emails to Clipboard</button>
		</div>
		<div class="col-2">
			Has Saved Guest Projection
		</div>
		<div class="col-1">
			# Standard Orders
		</div>
		<div class="col-1">
			# Workshop Orders
		</div>
		<div class="col-1">
			# Starter Pack Orders
		</div>
	</div>

	<?php if (!empty($this->stats['not_final'])) {
	foreach($this->stats['not_final'] as $store_id => $stats) { ?>
	<div class="row ME_header_zebra_odd">
		<div class="col-2">
			<?php echo $stats['store_name']; ?>
		</div>
		<div class="col-1">
			<?php echo $stats['state']; ?>
		</div>
		<div class="col-4 text-left" id="nf_<?php echo $store_id;?>">
			<?php echo $stats['email']; ?>
		</div>
		<div class="col-2">
			<?php echo $stats['saved_guest_projection']; ?>
		</div>
		<div class="col-1">
			<?php echo $stats['standard_orders']; ?>
		</div>
		<div class="col-1">
			<?php echo $stats['ws_orders']; ?>
		</div>
		<div class="col-1">
			<?php echo $stats['starter_pack_orders']; ?>
		</div>
	</div>
<?php } } ?>

	<div class="row">
		<div class="col text-center ME_header_zebra_even h4">
			Stores with Inventory
		</div>
	</div>
	<div class="row font-weight-bold ME_header_zebra_odd">
		<div class="col-2">
			Store Name
		</div>
		<div class="col-1">
			State
		</div>
		<div class="col-4 text-left">
			Email <button onclick="copyEmail('hi_');">Copy Emails to Clipboard</button>
		</div>
		<div class="col-2">
			Has Saved Guest Projection
		</div>
		<div class="col-1">
			# Standard Orders
		</div>
		<div class="col-1">
			# Workshop Orders
		</div>
		<div class="col-1">
			# Starter Pack Orders
		</div>
	</div>

	<?php if (!empty($this->stats['finalized'])) {
		foreach($this->stats['finalized'] as $store_id => $stats) { ?>
			<div class="row ME_header_zebra_odd">
				<div class="col-2">
					<?php echo $stats['store_name']; ?>
				</div>
				<div class="col-1">
					<?php echo $stats['state']; ?>
				</div>
				<div class="col-4 text-left" id="hi_<?php echo $store_id;?>">
					<?php echo $stats['email']; ?>
				</div>
				<div class="col-2">
					<?php echo $stats['saved_guest_projection']; ?>
				</div>
				<div class="col-1">
					<?php echo $stats['standard_orders']; ?>
				</div>
				<div class="col-1">
					<?php echo $stats['ws_orders']; ?>
				</div>
				<div class="col-1">
					<?php echo $stats['starter_pack_orders']; ?>
				</div>
			</div>
		<?php } } ?>

</div>
<?php } ?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>