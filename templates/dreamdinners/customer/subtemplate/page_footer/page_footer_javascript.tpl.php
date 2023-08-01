<script type="text/javascript">
	const CLIENT = { facebook: { id: '<?php echo FACEBOOK_APPID; ?>' }, google: { id: '<?php echo GOOGLE_CLIENTID; ?>', api: '<?php echo GOOGLE_APIKEY; ?>' }, microsoft: { id: '<?php echo MICROSOFT_CLIENTID; ?>' }, yahoo: { id: '<?php echo YAHOO_CLIENTID; ?>' } };
	const PATH = { https_server: '<?php echo HTTPS_SERVER; ?>', css: '<?php echo CSS_PATH; ?>', image: '<?php echo IMAGES_PATH; ?>', script: '<?php echo SCRIPT_PATH; ?>', page: <?php echo (!empty($this->page)) ? "'" . $this->page . "'" : 'false'; ?>};
	const COOKIE = { prefix: '<?php echo DD_SERVER_NAME; ?>', dd_sc_name: '<?php echo CBrowserSession::getSessionCookieName(); ?>', domain: '<?php echo COOKIE_DOMAIN; ?>' };
	const ANALYTICS = <?php echo json_encode($this->getAnalytics()); ?>;
	var USER_PREFERENCES = <?php echo (CUser::isLoggedIn()) ? json_encode(CUser::getCurrentUser()->getPublicPreferences()) : 'false'; ?>;
<?php
if (!empty($this->head_script_var))
{
	foreach ($this->head_script_var as $script_var)
	{
		echo $script_var . "\n";
	}
}
?>
</script>
<?php
if(isset($this->foot_script))
{
	foreach ($this->foot_script AS $script)
	{
		echo '<script src="' . $script['src'] . '" type="text/javascript"' . (!empty($script['async']) ? ' async' : '') . (!empty($script['defer']) ? ' defer' : '') . (!empty($script['crossorigin']) ? ' crossorigin="' . $script['crossorigin'] . '"' : '') . '></script>' . "\n";
	}
}
?>
