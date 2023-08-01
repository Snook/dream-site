<html>
<body>

<table style="width: 100%;">
<tr>
	<td ><img src="<?=ADMIN_IMAGES_PATH?>/new_logo.png" width="171" height="86" border="0"></td>
	<td ><div align="right" class="style2">Dream Dinners<br />PO Box #889<br />Snohomish, WA 98291-0889</div></td>
</tr>
</table>

<br />

<table style="width: 100%;">
<tr>
	<td>
		<table>
		<tr>
			<td>From:</td>
		</tr>
		<tr>
			<td style="width: 100%;"><?php echo $this->store_name;?> (#<?php echo $this->home_office_id;?>)</td>
		</tr>
		<tr>
			<td><?php echo $this->address;?></td>
		</tr>
		<tr>
			<td><?php echo $this->city . ',' . $this->state . ' ' .$this->postal;?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Date:<?=date('m/d/y')?></td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table style="width: 100%; border: #000 solid 1px;">
<tr>
	<td bgcolor="#CCCCCC"><b><?=$this->rows['month']?> <?=$this->rows['year']?> Royalty Invoice Report</b></td>
</tr>
<tr>
	<td>
		<table style="width: 100%;">
		<tr>
			<td width="37%" ><b>Marketing &amp; Royalties </b></td>
			<td width="63%"><b>Total</b></td>
	 	</tr>
		</table>

		<table style="width: 100%;">
		<tr>
			<td width="37%">Total Adjusted Gross Sales</td>
			<td width="63%"><?= CSessionReports::formatCurrency($this->rows['orders_total']);?></td>
		</tr>
		</table>

		<table style="width: 100%;">
		<tr>
			<td width="37%" >Marketing Fee</td>
			<td width="63%"><?= CSessionReports::formatCurrency($this->rows['marketing_total']) ?></td>
		</tr>
		</table>

		<?php if (!empty($this->rows['salesforce_fee'])) {?>
		<table style="width: 100%;">
		<tr>
			<td width="37%" >SalesForce Fee</td>
			<td width="63%"><?= CSessionReports::formatCurrency($this->rows['salesforce_fee']) ?></td>
		</tr>
		</table>
		<?php  } ?>

		<table style="width: 100%;">
		<tr>
			<td width="37%" >Royalty Fee</td>
			<td width="63%"><?= CSessionReports::formatCurrency($this->rows['royalty']) ?></td>
		</tr>
		</table>

		<table style="width: 100%;">
		<tr>
			<td width="37%" ><b>Total Monthly Fees</b></td>
			<td width="63%"><b><?= CSessionReports::formatCurrency($this->rows['total_fees'])?></b></td>
		</tr>
		</table>

		<table style="width: 100%;">
		<tr>
			<td width="37%" >Net Franchise Sales</td>
			<td width="63%"><?= CSessionReports::formatCurrency($this->rows['net_sales'])?></td>
		</tr>
		</table>

<!--
        <table style="width: 100%;">
            <tr>
                <td width="37%" ><b>Total Delivery Fees Owed to Delivery Vendor:</b></td>
                <td width="63%"><b><?= CSessionReports::formatCurrency($this->rows['subtotal_delivery_fee'])?></b></td>
            </tr>
        </table>
-->
        <table style="width: 100%;">
            <tr>
                <td width="37%" ><b>Donations Owed to Living the Dream:</b></td>
                <td width="63%"><b><?= CSessionReports::formatCurrency($this->rows['ltd_round_up_value'] + $this->rows['ltd_menu_item_value'])?></b></td>
            </tr>
        </table>

	</td>
</tr>
<tr>
	<td bgcolor="#CCCCCC">&nbsp;</td>
</tr>
<tr>
	<td>
		<p align="center"><br />Payment terms net 10 days<br />
			Mail authorization forms to:<br />

		Dream Dinners Inc.<br />
		PO Box #889<br />
		Snohomish, WA 98291-0889
		<br /><br />Thank you!
		</p><br />
	</td>
</tr>
</table>

</body>
</html>