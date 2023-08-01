<script type="text/javascript">
//<![CDATA[
const CLIENT = { facebook: { id: '<?php echo FACEBOOK_APPID; ?>' }, google: { id: '<?php echo GOOGLE_CLIENTID; ?>', api: '<?php echo GOOGLE_APIKEY; ?>' }, microsoft: { id: '<?php echo MICROSOFT_CLIENTID; ?>' }, yahoo: { id: '<?php echo YAHOO_CLIENTID; ?>' } };
const PATH = { https_server: '<?php echo HTTPS_SERVER; ?>', css: '<?php echo CSS_PATH; ?>', image: '<?php echo IMAGES_PATH; ?>', image_admin: '<?php echo ADMIN_IMAGES_PATH; ?>', script: '<?php echo SCRIPT_PATH; ?>' };
const COOKIE = { prefix: '<?php echo DD_SERVER_NAME; ?>', dd_sc_name: '<?php echo CBrowserSession::getSessionCookieName(); ?>', domain: '<?php echo COOKIE_DOMAIN; ?>' };
const ANALYTICS = <?php echo json_encode($this->getAnalytics()); ?>;
USER_LOGGEDIN = <?php echo (CUser::isLoggedIn()) ? 'true' : 'false'; ?>;
STORE_DETAILS = {
	id: '<?php echo CBrowserSession::getCurrentFadminStoreID(); ?>'
};
<?php if (CUser::isLoggedIn()) { ?>
USER_DETAILS = {
	id: '<?php echo CUser::getCurrentUser()->id; ?>',
	firstname: "<?php echo CUser::getCurrentUser()->firstname; ?>",
	lastname: "<?php echo CUser::getCurrentUser()->lastname; ?>",
	primary_email: "<?php echo CUser::getCurrentUser()->primary_email; ?>",
	telephone_1: '<?php echo CUser::getCurrentUser()->telephone_1; ?>',
	browser: '<?php echo $_SERVER["HTTP_USER_AGENT"]; ?>'
};
USER_PREFERENCES = <?php echo json_encode(CUser::getCurrentUser()->preferences); ?>;
<?php if (!empty($this->debugInfoArray)) { ?>
DEBUG_INFO = '<?php echo json_encode($this->debugInfoArray )?>';
<?php } else { ?>
DEBUG_INFO = null;
<?php } } ?>
IS_TOUCH_SCREEN_DEVICE = <?php echo (IS_TOUCH_SCREEN_DEVICE) ? 'true' : 'false'; ?>;
//]]>
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
	foreach ($this->foot_script as $script)
	{
		echo '<script src="' . $script['src'] . '" type="text/javascript" ' . (!empty($script['async']) ? 'async' : '') . '></script>' . "\n";
	}
}
?>


