<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_contact_information.min.js'); ?>
<?php $this->setOnload('store_contact_information_init();'); ?>
<?php $this->assign('page_title','Store Contact Information'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if ($this->show['store_selector']) { ?>
<form method="post">
<div style="float: right;"><a href="/backoffice/reports_store_contact_information?export=xlsx&amp;form=sci" class="btn btn-primary btn-sm">Export All</a></div>
<div style="font-size: 13pt; font-weight: bold;">Store: <?php echo $this->form_store_details['store_html']; ?></div>
</form>
<?php } ?>

<p>The Home Office would like to ensure we have your latest information.  Please assist us by completing the following information for your store and each owner on your Franchise Agreement.</p>

<form action="" method="post" onsubmit="return _check_form(this);" >

<div style="width: 450px; float: left; margin-right: 30px; margin-left: 14px;">

<!-- // now on store information page

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Package Shipping Address</h4>

		<div><?php echo $this->form_store_details['pkg_ship_same_as_store_html']; ?> Same as store address.</div>

		<div><?php echo $this->form_store_details['pkg_ship_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_city_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['pkg_ship_telephone_day_html']; ?></div>

	</div>

-->

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Owner #1 Personal Contact Information</h4>

		<div><?php echo $this->form_store_details['owner_1_name_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_nickname_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_city_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_telephone_primary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_telephone_secondary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_1_email_address_html']; ?></div>

	</div>

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Owner #3 Personal Contact Information</h4>

		<div><?php echo $this->form_store_details['owner_3_name_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_nickname_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_city_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_telephone_primary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_telephone_secondary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_3_email_address_html']; ?></div>

	</div>

<!-- // now on store information page

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Operating Manager Contact Information</h4>

		<div><?php echo $this->form_store_details['manager_1_name_html']; ?></div>
		<div><?php echo $this->form_store_details['manager_1_nickname_html']; ?></div>
		<div><?php echo $this->form_store_details['manager_1_telephone_primary_html']; ?></div>

	</div>

-->

</div>

<div style="width: 450px; float: left;">

<!-- // now on store information page

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Letter Mailing Address &amp; Legal Notices</h4>

		<div><?php echo $this->form_store_details['letter_ship_same_as_store_html']; ?> Same as store address.</div>

		<div><?php echo $this->form_store_details['letter_ship_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_city_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['letter_ship_telephone_day_html']; ?></div>

	</div>

-->

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Owner #2 Personal Contact Information</h4>

		<div><?php echo $this->form_store_details['owner_2_name_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_nickname_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_city_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_telephone_primary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_telephone_secondary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_2_email_address_html']; ?></div>

	</div>

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<h4>Owner #4 Personal Contact Information</h4>

		<div><?php echo $this->form_store_details['owner_4_name_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_nickname_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_address_line1_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_address_line2_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_city_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_state_id_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_postal_code_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_attn_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_telephone_primary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_telephone_secondary_html']; ?></div>
		<div><?php echo $this->form_store_details['owner_4_email_address_html']; ?></div>

	</div>

	<div style="padding: 10px !important; margin-bottom: 10px; background-color: #F1E8D8 !important; -webkit-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); -moz-box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1); box-shadow: 0px 0px 6px 1px rgba(0,0,0,.1);">

		<div style="margin-bottom: 10px;">Last Updated: <?php echo ($this->timestamp_updated) ? CTemplate::dateTimeFormat($this->timestamp_updated) : 'Never'; ?><?php if ($this->timestamp_updated) { ?> by <a href="/backoffice/user_details?id=<?php echo $this->updated_by_user_id; ?>"><?php echo $this->updated_by_firstname; ?> <?php echo $this->updated_by_lastname; ?></a> <?php } ?></div>
		<div><?php echo $this->form_store_details['submit_html']; ?></div>

	</div>

</div>


<div class="clear"></div>



</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>