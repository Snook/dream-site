<table style="width:100%; margin:0px; padding:0px;" >
<?php if ( isset($this->form_list_users['store_html'] ) ) { ?>
<tr>
	<td class="bgcolor_light" style="text-align:right;">Filter by store</td>
	<td class="bgcolor_light"><?php echo $this->form_list_users['store_html']; ?></td>
</tr>
<?php } ?>
<tr style="margin:0px; padding:0px;">
	<td class="bgcolor_light" style="text-align:right; margin:0px; vertical-align:top; padding-right:10px;">Search By</td>
	<td class="bgcolor_light"  style="margin:0px; padding:0px;">
		<?php echo $this->form_list_users['search_type_html']; ?>
		<?php echo $this->form_list_users['hidden_html']; ?>

		<input type="text" id="q" name="q" value="<?php if (isset($this->q)) echo $this->q; ?>" />
		<input type="checkbox" id="all_stores" name="all_stores" <?php if (isset($this->all_stores)) echo 'checked="checked"';?> />All Stores
		<input type="submit" value="Search" onclick="javascript:processorGuestSearch();" /><img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" alt="Processing" /><br />
		<div id="search_help" style="margin-left:4px;display:none;color:#009933;"></div>
  	</td>
</tr>
</table>
