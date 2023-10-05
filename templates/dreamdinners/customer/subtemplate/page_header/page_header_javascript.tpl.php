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
<?php if (defined('ENABLE_HELP_SEARCH') && ENABLE_HELP_SEARCH == true) { ?>
<script>
	window.fwSettings={
		'widget_id':13000000535
	};
	!function(){if("function"!=typeof window.FreshworksWidget){var n=function(){n.q.push(arguments)};n.q=[],window.FreshworksWidget=n}}()
</script>
<script type='text/javascript' src='https://widget.freshworks.com/widgets/13000000535.js' async defer></script>
<?php } ?>

<script type="text/javascript">
	var _iub = _iub || [];
	_iub.csConfiguration = {"cookiePolicyInOtherWindow":true,"countryDetection":true,"enableGdpr":false,"enableUspr":true,"lang":"en","siteId":1685553,"showBannerForUS":true,"usprPurposes":"s,sh,adv","whitelabel":false,"cookiePolicyId":48982534, "banner":{ "backgroundColor":"#4c4c4c","closeButtonRejects":true,"position":"float-bottom-left","textColor":"white" }};
</script>
<script type="text/javascript" src="//cdn.iubenda.com/cs/gpp/stub.js"></script>
<script type="text/javascript" src="//cdn.iubenda.com/cs/iubenda_cs.js" charset="UTF-8" async></script>