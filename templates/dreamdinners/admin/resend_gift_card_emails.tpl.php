<?php $this->setScript('head', SCRIPT_PATH . '/admin/gift_card_resend.min.js'); ?>
<?php $this->assign('page_title', 'Resend Gift Card Emails'); ?>
<?php $this->setOnLoad("searchTypeChange('" . (empty($_REQUEST['search_type']) ? 'recipient_email' : $_REQUEST['search_type']) . "');"); ?>
<?php $this->assign('topnav', 'giftcards'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<p>To resend gift card purchase receipts and eGift Card emails:</p>
	<ol>
		<li>First search for the corresponding order</li>
		<li>Find the order in the search results and click "View"</li>
		<li>Order details and links to resend emails will be shown in a pop-up window</li>
	</ol>
	<p><span style="color:#F00;">Note:</span> "Resend" email functionality works with shipped cards and eGift card orders. Does not apply to card "load" transactions.</p>

	<form action="" method="post" onsubmit="return checkSearchSring(this);">
		<table width="100%" class="bgcolor_light mb-4">
			<tr>
				<td><span class="header"><b>Search By:</b></span></td>
				<td>
					<?php echo $this->form_list_users['search_type_html']; ?>
					<input type="text" id="q" name="q" value="<?php if (!empty($this->q)) { echo $this->q; } ?>">
					<input type="submit" value="search" class="btn btn-primary btn-sm">
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<div id="search_help" style="display:none; color:#009933"></div>
					<div id="search_error" style="display:none; color:#993300"></div>
				</td>
			</tr>
		</table>
	</form>

<?php if ($this->rowcount && $this->rows) { ?>
	<table class="table table-striped table-hover table-hover-cyan bg-white ddtemp-table-border-collapse">
		<thead>
		<?php if (isset($this->store)) { ?>
			<tr>
				<td colspan="7" class="text-right">
					<?php $exportAllLink = '/backoffice/list_users?store=' . $this->store . '&letter_select=all&export=xlsx'; ?>
					<?php include $this->loadTemplate('admin/export.tpl.php'); ?>
				</td>
			</tr>
		<?php } ?>
		<tr class="text-nowrap">
			<th>Date Purchased</th>
			<th>Billing Name</th>
			<th>CC number</th>
			<th>CC Payment ID</th>
			<th>Recipient Email</th>
			<th>Recipient Postal</th>
			<th>Links</th>
		</tr>
		</thead>
		<tbody>
		<?php while ($this->rows->fetch()) { ?>
			<tr>
				<td class="text-nowrap"><?php echo CTemplate::dateTimeFormat($this->rows->purchase_date, NORMAL); ?></td>
				<td class="text-nowrap"><?php echo $this->rows->billing_name; ?></td>
				<td><?php echo substr($this->rows->payment_card_number, strlen($this->rows->payment_card_number) - 4); ?></td>
				<td><?php echo $this->rows->cc_ref_number; ?></td>
				<td><?php echo implode('<br />', explode("|", $this->rows->email_dest)); ?></td>
				<td><?php echo implode('<br />', explode("|", $this->rows->postal_dest)); ?></td>
				<td><a class="btn btn-primary btn-sm" href="javascript:viewGCOrder('<?php echo $this->rows->cc_ref_number; ?>');">View</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>