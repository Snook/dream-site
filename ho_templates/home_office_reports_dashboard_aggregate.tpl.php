<?php
$REPORTGIF = NULL;
$PAGETITLE = "Dashboard Aggregate Reporting Tool";
$HIDDENPAGENAME = "admin_reports_dashboard_aggregate";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=FALSE;
$SHOWYEAR=FALSE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

<script type="text/javascript">

function disableCheckboxes (state)
{


var rads = form.select_archive_chkbox_multi;

for(var i=0; i<rads.length;i++ )
{
 form.select_archive_chkbox_multi[i].disabled = state;
}


}

function SHOW(which)
{


		form = document.getElementById("aggregate");

		if (which == "archive") {

//disableCheckboxes (false);

			if (form.select_archive_chkbox.checked) {
				//form.archive_date_popup.disabled = false;
				form.current_date_popup.disabled = true;
				if (form.select_current_chkbox) form.select_current_chkbox.checked = false;


			}
			else {
					//form.archive_date_popup.disabled = true;


			}
		}
		else if (which == "current") {
//	disableCheckboxes (true);
			if (form.select_current_chkbox.checked) {
				form.current_date_popup.disabled = false;
				//form.archive_date_popup.disabled = true;
				if (form.select_archive_chkbox) form.select_archive_chkbox.checked = false;

			}
			else {

					form.current_date_popup.disabled = true;
			}
		}
		else if (which == "store") {
			if (form.select_store_chkbox.checked) {
				form.store_popup.disabled = false;
				if (form.regional_popup) form.regional_popup.disabled = true;
				if (form.district_popup) form.district_popup.disabled = true;
				if (form.state_popup) form.state_popup.disabled = true;

				if (form.select_regional_chkbox) form.select_regional_chkbox.checked = false;
				if (form.select_district_chkbox) form.select_district_chkbox.checked = false;
				if (form.select_national_chkbox) form.select_national_chkbox.checked = false;
				if (form.select_state_chkbox) form.select_state_chkbox.checked = false;

			}
			else
				form.store_popup.disabled = true;
		}
		else if (which == "state") {
			if (form.select_state_chkbox.checked) {
				form.state_popup.disabled = false;
				if (form.regional_popup) form.regional_popup.disabled = true;
				if (form.district_popup) form.district_popup.disabled = true;

				if (form.store_popup) form.store_popup.disabled = true;

				if (form.select_store_chkbox) form.select_store_chkbox.checked = false;
				if (form.select_regional_chkbox) form.select_regional_chkbox.checked = false;
				if (form.select_district_chkbox) form.select_district_chkbox.checked = false;
				if (form.select_national_chkbox) form.select_national_chkbox.checked = false;

			}
			else
				form.state_popup.disabled = true;
		}
		else if (which == "regional") {
			if (form.select_regional_chkbox.checked)
			{
				if (form.regional_popup)
					form.regional_popup.disabled = false;

				if (form.state_popup) form.state_popup.disabled = true;
				if (form.district_popup) form.district_popup.disabled = true;

				if (form.store_popup) form.store_popup.disabled = true;

if (form.select_store_chkbox) form.select_store_chkbox.checked = false;
				if (form.select_state_chkbox) form.select_state_chkbox.checked = false;
				if (form.select_district_chkbox) form.select_district_chkbox.checked = false;
				if (form.select_national_chkbox) form.select_national_chkbox.checked = false;

			}
			else
			{
				if (form.regional_popup)
					form.regional_popup.disabled = true;
			}

		}
		else if (which == "district") {
			if (form.select_district_chkbox.checked) {
				form.district_popup.disabled = false;

				if (form.state_popup) form.state_popup.disabled = true;
				if (form.regional_popup) form.regional_popup.disabled = true;
				if (form.store_popup) form.store_popup.disabled = true;

if (form.select_store_chkbox) form.select_store_chkbox.checked = false;
				if (form.select_state_chkbox) form.select_state_chkbox.checked = false;
				if (form.select_regional_chkbox) form.select_regional_chkbox.checked = false;
				if (form.select_national_chkbox) form.select_national_chkbox.checked = false;

			}
			else
				form.district_popup.disabled = true;
		}
		else if (which == "national") {
				if (form.select_national_chkbox.checked) {
					if (form.state_popup) form.state_popup.disabled = true;
					if (form.regional_popup) form.regional_popup.disabled = true;
					if (form.district_popup) form.district_popup.disabled = true;

					if (form.store_popup) form.store_popup.disabled = true;

				if (form.select_store_chkbox) form.select_store_chkbox.checked = false;
					if (form.select_state_chkbox) form.select_state_chkbox.checked = false;
					if (form.select_regional_chkbox) form.select_regional_chkbox.checked = false;
					if (form.select_district_chkbox) form.select_district_chkbox.checked = false;


			}

			else
			{
				if (form.select_national_chkbox) form.select_national_chkbox.checked = false;
			}
		}






	}

	function checkform(form)
	{
		var cansubmit = true;

		if (form.select_national_chkbox.checked == false && form.select_regional_chkbox.checked == false && form.select_state_chkbox.checked == false &&  form.select_district_chkbox.checked == false && form.select_store_chkbox.checked == false)
		{
			cansubmit = false;
			alert ("Sorry, you must choose a filter!");

		}

		if (form.select_archive_chkbox.checked == false && form.select_current_chkbox.checked == false)
		{
			cansubmit = false;
			alert ("Sorry, you must choose a Date!");
		}


		if (cansubmit == true)	{
			var returnval;
			returnval =  _check_form(form);
			return returnval;
		}

		else return false;



	}

</script>





<?php if (empty($this->form_array['select_store_chkbox_html'])) {  ?>

	Sorry, currently you do not have any store's assigned to your account or an error occurred in the system.  Please contact technical support.

<?php  } else {  ?>

	<form action=""  id="aggregate" method="post" onSubmit="return checkform(this);">

	<strong>1. Choose a Date:</strong><br/><br/>


	<?php  if (isset($this->form_array['select_current_chkbox_html'])) { ?>
		<?=$this->form_array['select_current_chkbox_html']?>Pick a day in the current month for review:
	<?php }

	 if (isset($this->form_array['current_date_popup_html'])) { ?>
		<?=$this->form_array['current_date_popup_html']?>
	<?php  } ?>


<?php if (isset($this->form_array['select_archive_chkbox_html'])) { ?>


		<br/><br/>
		<?=$this->form_array['select_archive_chkbox_html']?>Pick a single monthly rollup for archived Dashboard items:

	<br/>
		<div name="checkboxes" id="checkboxes">
		<?php

			foreach($this->archive_array as $key => $value){

				echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $this->form_array["select_archive_chkbox_multi_" . $key . "_html"] . " " . $value . "<br/>";
			}

		?>
		</div>

	<?php } ?>

	<br/><br/>

	<hr/>
	<br/>

	<strong>2. Choose a Filter:</strong><br/><br/>

	<?=$this->form_array['select_store_chkbox_html']?>Show data by Store:&nbsp;<?=$this->form_array['store_popup_html']?>
	<br/><br/>

	<?=$this->form_array['select_state_chkbox_html']?>Show data by State:&nbsp;<?=$this->form_array['state_popup_html']?>
	<br/><br/>

	<?=$this->form_array['select_district_chkbox_html']?>Show data by District:&nbsp;<?=$this->form_array['district_popup_html']?>


	<?php if (isset($this->form_array['select_regional_chkbox_html'])) { ?>
	<br/><br/>
	<?=$this->form_array['select_regional_chkbox_html']?>

	<?php if ($this->is_manager == true) {
		echo "Show All Store Data";
	}
	else
	{
		echo "Show Data By Regional Manager&nbsp;";
		echo $this->form_array['regional_popup_html'];
	}
	?>

	<?php } ?>

	<?php if (isset($this->form_array['select_national_chkbox_html'])) { ?>

		<br/><br/>
		<?=$this->form_array['select_national_chkbox_html']?>Show all data By Nation

	<?php } ?>


	<br/><br/>

	<?=$this->form_array['report_html']?>

	</form>

<?php  }  ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>