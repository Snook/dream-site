<?php $this->assign('page_title','Create Store'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<script src="<?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>


<script type="text/javascript">
function updatePreview(textArea)
{
	var previewDiv = document.getElementById(textArea.name + '_preview');

	if ( textArea && previewDiv )
	{
		previewDiv.innerHTML = textArea.value;
	}
}

var previewOn = false;

function togglePreview()
{
	if ( previewOn )
	{
		var previewDiv = document.getElementById('store_description_preview');
		previewDiv.style.display = 'none';
	}
	else
	{
		var previewDiv = document.getElementById('store_description_preview');
		previewDiv.style.display = 'block';
		updatePreview(document.getElementById('store_description'));
	}

	previewOn = !previewOn;
}
</script>

<h1>Create Store</h1>

<form action="" method="post" onSubmit="return _check_form(this);" >

<table style="width: 100%;">
<tr>
	<td class="bgcolor_light" style="text-align: right;">Store Name</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['store_name_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Home Office ID</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['home_office_id_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Franchise</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['franchise_id_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Grand Opening Date</td>
	<td class="bgcolor_light"><script type="text/javascript">DateInput('grand_opening_date', true, 'MM/DD/YYYY', '<?php echo $this->initDate?>');</script></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Active</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['active_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Store Description<br /><a href="javascript:togglePreview()">Preview</a></td>
	<td class="bgcolor_light">
		<?php echo $this->form_create_store['store_description_html']; ?>
		<div style="display: none; border-width: thin; border-color: black; border-style: solid; padding-left: 10; padding-right: 10;" id="store_description_preview"></div>
	</td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Address Line 1</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['address_line1_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Address Line 2</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['address_line2_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">City</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['city_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">County</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['county_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">State</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['state_id_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Postal Code</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['postal_code_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Store Email</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['email_address_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Telephone Day</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['telephone_day_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Telephone Evening</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['telephone_evening_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Text Messaging (SMS) Number:</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['telephone_sms_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Fax</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['fax_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">USPS ADC</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['usps_adc_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Timezone</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['timezone_id_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Observes Daylight Savings Time</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['observes_DST_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Close Session Hours</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['close_session_hours_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Food Tax</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['food_tax_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Non-Food Tax Total</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['total_tax_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Service Tax</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['other1_tax_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" style="text-align: right;">Enrollment Tax</td>
	<td class="bgcolor_light"><?php echo $this->form_create_store['other2_tax_html']; ?></td>
</tr>
<tr>
	<td class="bgcolor_light" colspan="2"><?php echo $this->form_create_store['createStore_html']; ?></td>
</tr>
</table>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
