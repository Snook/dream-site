<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
<?php if ($msg = $this->getErrorMsg()) { CLog::RecordNew(CLog::DEBUG, 'Get Error Message: ' . $msg); ?>
	dd_message({
		title: 'Error',
		message: '<?php echo str_replace(array("\n\r", "\n", "\r"), "\\\n", $msg); ?>',
		div_id: 'dd_ErrorMessage'
	});
<?php } ?>
<?php if ($msg = $this->getStatusMsg()) { ?>
	dd_message({
		title: 'Status',
		message: '<?php echo str_replace(array("\n\r", "\n", "\r"), "\\\n", $msg); ?>',
		div_id: 'dd_StatusMessage'
	});
<?php } ?>
<?php
if (!empty($this->head_script_var) && !$this->page_is_bootstrap)
{
	foreach ($this->head_script_var as $script_var)
	{
		echo "\t" . $script_var . "\n";
	}
}
if (!empty($this->head_onload))
{
	foreach ($this->head_onload as $onload)
	{
		echo "\t" . $onload . "\n";
	}
}
?>
});

<?php if (defined('ENABLE_ANALYTICS') && ENABLE_ANALYTICS == true) { ?>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-425666-6']);
_gaq.push(['_trackPageview']);
_gaq.push(['_setDomainName', 'dreamdinners.com']);
_gaq.push(['_addIgnoredOrganic', 'www.dreamdinners.com']);
_gaq.push(['_addIgnoredOrganic', 'dreamdinners.com']);
_gaq.push(['_addIgnoredRef', 'dreamdinners.com']);
_gaq.push(['_addIgnoredRef', 'www.dreamdinners.com']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
<?php } ?>

<?php if (!empty($this->print_view) && empty($this->no_dd_print) && empty($_GET['no_dd_print'])) { ?>
dd_print();
<?php } ?>
//]]>
</script>