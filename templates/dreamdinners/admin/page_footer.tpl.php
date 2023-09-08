
<?php if (empty($this->suppress_table_wrapper)) { ?>
<div class="clear"></div>

</td>
</tr>
</table>
<?php } ?>

<?php if (empty($this->print_view) || $this->print_view != true) { ?>

	<div id="footerlinks" class="footerlinks" style="text-align:center;">
		<?php if (empty($this->hide_navigation)) { // hidden for some pages, ie admin_access_agreement ?>
			<a href="?page=admin_resources">Resources</a> |
			<a id="helpdesk_footer_link" href="https://support.dreamdinners.com" target="_blank" >Support Request</a> |
			<a href="https://support.dreamdinners.com/" target="_blank">Support Portal</a>
		<?php  } ?>
	</div>

	<div id="copyright" class="copyright" style="text-align:center;">
		<a href="main.php" onclick="confirmNavigate('main.php', 'You are still logged in as an admin! Are you sure you would like to visit the customer site as an admin user.');event.preventDefault();">Back to Customer Site</a> | &copy; Copyright Dream Dinners Inc. All rights reserved.
	</div>

<?php } ?>

<?php if ($this->page_is_bootstrap) { ?>
	<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>
	<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>
<?php } ?>

</body>
</html>