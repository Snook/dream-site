<?php if (!defined('ENABLE_CUSTOMER_SITE') || ENABLE_CUSTOMER_SITE) { ?>
	<?php if (empty($this->no_footer) || $this->no_footer != true) { ?>
		<?php include $this->loadTemplate('customer/subtemplate/page_footer/page_footer_links.tpl.php'); ?>
	<?php } ?>
<?php } ?>

<?php include $this->loadTemplate('customer/subtemplate/page_footer/page_footer_javascript.tpl.php'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_footer/page_footer_onload.tpl.php'); ?>

<?php if (defined('ENABLE_ANALYTICS') && ENABLE_ANALYTICS == true) { ?>
	<?php include $this->loadTemplate('customer/subtemplate/page_footer/page_footer_analytics.tpl.php'); ?>
<?php } ?>

</body>
</html>