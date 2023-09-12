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

<script type="text/javascript">

	function searchTypeChange(value)
	{
		//	var newtype = document.getElementById('search_type').value;
		var helpString = "";
		switch (value)
		{
			case 'recipient_email':
				helpString = "Enter the full or partial email of the eGift card recipient";
				break;

			case 'billing_email':
				helpString = "Enter the full or partial email of the purchaser of the gift card";
				break;

			case 'billing_name':
				helpString = "Enter the full or partial name of the billing name of the purchaser (name on credit card)";
				break;
		}

		document.getElementById('search_help').style.display = "block";
		document.getElementById('search_help').innerHTML = helpString;
		document.getElementById('q').focus();
	}

	function viewGCOrder(id)
	{
		var dest = '/?page=admin_gift_card_details&gcOrder=' + id;

		NewWindowScroll(dest, 'GC_Details', '1000', '460');
	}

	function checkSearchSring(form)
	{
		document.getElementById('search_error').style.display = "none";

		var searchType = document.getElementById('search_type').value;
		var searchString = document.getElementById('q').value;
		switch (searchType)
		{
			case 'recipient_email':
				if (searchString.length < 5)
				{
					document.getElementById('search_error').style.display = "block";
					document.getElementById('search_error').innerHTML = "The search string must be at least 5 characters long.";
					return false;
				}

				break;

			case 'billing_email':
				if (searchString.length < 5)
				{
					document.getElementById('search_error').style.display = "block";
					document.getElementById('search_error').innerHTML = "The search string must be at least 5 characters long.";
					;
					return false;
				}

				break;

			case 'billing_name':

				if (searchString.length < 3)
				{
					document.getElementById('search_error').style.display = "block";
					document.getElementById('search_error').innerHTML = "The search string must be at least 3 characters long.";
					;
					return false;
				}
				break;

		}
		return true;

	}
</script>

<form action="" method="post" onsubmit="return checkSearchSring(this);">
	<table width="100%" class="bgcolor_light">
		<tr>
			<td><span class="header"><b>Search By:</b></span></td>
			<td>
				<?= $this->form_list_users['search_type_html'] ?>
				<input type="text" id="q" name="q" value="<?php if (!empty($this->q)) { echo $this->q; } ?>">
				<input type="submit" value="search" class="button">
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
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="bgcolor_light">
	<tr valign="center" height="20">
		<td></td>
	</tr>

</table>
<br/>

<table width="100%" border="0" cellpadding="5" cellspacing="0" style="background-color:#ccffcc">
	<tr>
		<td>
			<?php if (($this->rowcount !== null) && $this->rows)
			{
				$fontColor = 'black';
				if ($this->rowcount == 0)
				{
					$fontColor = 'red';
				}
				?>
			<span style="color:<?= $fontColor ?>;"><b>Returned <?php echo $this->rowcount; ?> matches</b>
			<?php } ?></td>

		<?php if (isset($this->store)) { ?>
		<td align="right"><?php $exportAllLink = '/?page=admin_list_users&store=' . $this->store . '&letter_select=all&export=xlsx'; ?>
			<?php include $this->loadTemplate('admin/export.tpl.php'); ?>
			<?php } ?>

		</td>
	</tr>
</table>

<?php if ($this->rowcount && $this->rows) { ?>
<table class="report">
  <tr style="background: #ccc;">
    <td>Date Purchased</td>
    <td>Billing Name</td>
    <td>CC number</td>'
    <td>CC Payment ID</td>
    <td>Recipient Address</td>
    <td colspan="2">Links</td>
  </tr>

  <?php
	$counter = 0;
	while ($this->rows->fetch())
	{
		$style = 'style="background: #ddd;"';
		if ($counter++ % 2 == 0)
		{
			$style = '';
		}

		echo '<tr ' . $style . '>';
		$id = $this->rows->id;
		echo '<td nowrap>' . CTemplate::dateTimeFormat($this->rows->purchase_date, NORMAL) . '</td>';
		echo '<td>' . $this->rows->billing_name . '</td>';
		echo '<td nowrap>' . substr($this->rows->payment_card_number, strlen($this->rows->payment_card_number) - 4) . '</td>';
		echo '<td nowrap>' . $this->rows->cc_ref_number . '</td>';
		echo '<td>';

		$emails = explode("|", $this->rows->email_dest);
		foreach ($emails as $emailAdd)
		{
			echo $emailAdd . '<br />';
		}

		$postals = explode("|", $this->rows->postal_dest);
		foreach ($postals as $postalAdd)
		{
			echo $postalAdd . '<br />';
		}

		echo '</td>';

		echo '<td nowrap><a href="javascript:viewGCOrder(\'' . $this->rows->cc_ref_number . '\');">view</a></td>';
		echo '</tr>';
	}
	?>
</table>
</p>
<?php } ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>