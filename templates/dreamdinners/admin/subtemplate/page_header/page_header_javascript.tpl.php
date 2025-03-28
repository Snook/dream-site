<?php
if(isset($this->head_script))
{
	foreach ($this->head_script AS $script)
	{
		echo '<script src="' . $script['src'] . '" type="text/javascript"' . (!empty($script['async']) ? ' async' : '') . (!empty($script['defer']) ? ' defer' : '') . (!empty($script['crossorigin']) ? ' crossorigin="' . $script['crossorigin'] . '"' : '') . '></script>' . "\n";
	}
}
?>
<?php if (defined('ENABLE_ANALYTICS') && ENABLE_ANALYTICS == true) { ?>
	<script type="text/javascript">
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', '<?php echo GOOGLE_ANALYTICS_ID; ?>', {'user_id': '<?php echo CUser::getCurrentUser()->id; ?>' });
		gtag('config', 'UA-425666-1');
	</script>
<?php } ?>