<?php
// constants for all report pages.
$REPORTGIF = "page_header_royaltyreport.gif";
$PAGETITLE = "Royalty Report";
$HIDDENPAGENAME = "admin_reports_royalty";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
?>

<?php if ($this->print_view) { ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-styles-reports.css'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<?php } else { ?>
<?php $this->setOnLoad("print_all_init();"); ?>
<?php include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>
<?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
<?php } ?>
<?php if (isset($this->report_data) && $this->report_data != "") { ?>

<?php

		$showdata = true;

	if ($this->report_type_to_run == 3)
	{
			if (empty($this->report_data[0]['grand_total']) && $this->store != 57 )
		{
				$showdata = false;

		}


	}
	else if ($this->report_type_to_run == 4)
	{
			$findrows = false;
		foreach($this->report_data as $obj)
		{
			foreach($obj as $element)
			{
				if (!isset($element['grand_total']))
				{
						$findrows = true;
						break;
					}
				}
			if ($findrows == true)
			{
				break;
			}


		}


		if ($findrows == false)
		{
			$showdata = false;




				}

			}

	if (isset($this->report_data) && $showdata )
	{
		foreach ($this->report_data as $array_entity)
		{
			if (($array_entity && !empty($array_entity['grand_total']) && $array_entity['grand_total'] > 0.00) || $this->store == 57)
			{
?>

<?php include $this->loadTemplate('admin/reports_royalty_summary_v2.tpl.php'); ?>

<?php } } ?>

<?php } else { ?>

<table class="report" border="0">
<tr>
	<td class="headers" width="224"><b>Sorry, a royalty report does not exist for this time period.</b></td>
</tr>
</table>

<?php } ?>

<?php } else if ($this->form_login['user_type'] == CUser::SITE_ADMIN || $this->form_login['user_type'] == CUser::HOME_OFFICE_MANAGER || $this->form_login['user_type'] == CUser::HOME_OFFICE_STAFF) { ?>

<script type="text/javascript">
function print_all_init()
{
	$('#print_all').on('click', function (e) {
		bounce('main.php?page=admin_reports_royalty&month_popup=' + $('#month_popup').val() + '&year_field_001=' + $('#year_field_001').val() + '&print_all=true');
	});

	}
</script>

<input type="button" class="button" id="print_all" value="Print All Active Store Reports for Selected Month" />

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>