<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Refresh: 300');

define('SITE_WIDE', true);

// Some vars for dynamic info if config file exists
$maint_message = '';
$background = '';
$logo = '<h3>Maintenance</h3>';

// Check if there is a config file so we can make the maintenance page more informative otherwise the page is static
if (file_exists("../includes/CApp.inc"))
{
	require_once("../includes/CApp.inc");

	// assume site is not down
	$site_disabled = false;
	$checkdb = false;

	// check if site is forced disabled in config
	if (defined('SITE_DISABLED') && SITE_DISABLED == true)
	{
		$site_disabled = true;
	}
	else
	{
		$checkdb = true;

		// check to see if disabled by database schedule or if database is down
		$mysqli = new mysqli(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	// if config file message, add it to array
	if (defined('MAINT_PAGE_MESSAGE'))
	{
		$maintenance_messages[]['message'] = MAINT_PAGE_MESSAGE;
	}

	if ($checkdb && empty($mysqli->connect_errno))
	{
		$time_now = date('Y-m-d H:i:s', time());

		$result = $mysqli->query("SELECT *
			FROM site_message AS dmm
			WHERE dmm.message_start <= '" . $time_now . "'
			AND dmm.message_end >= '" . $time_now . "'
			AND dmm.is_active = '1'
			AND dmm.is_deleted = '0'
			AND dmm.audience != 'STORE'
			AND dmm.message_type = 'SITE_MESSAGE'
			ORDER BY dmm.message_start ASC");

		if (!empty($result->num_rows))
		{
			$maintenance_messages = array();

			while ($row = $result->fetch_assoc())
			{
				if ($row['audience'] == 'SITE_WIDE' || $row['audience'] == 'CUSTOMER')
				{
					$maintenance_messages[] = $row;
				}

				if (!empty($row['disable_site_start']) && $row['disable_site_start'] != '1970-01-01 00:00:01' && $time_now >= $row['disable_site_start'])
				{
					$site_disabled = true;
				}
			}

			$result->close();
		}
	}
	else
	{
		// database connection error, site disabled
		$site_disabled = true;
	}

	if (defined('IMAGES_PATH'))
	{
		if(file_exists(str_replace(HIGH_CAP_IMAGE_BASE, '', IMAGES_PATH . '/style/dreamdinners.png')))
		{
			$logo = '<img src="' . IMAGES_PATH . '/style/dreamdinners.png" alt="Dream Dinners" />';
		}
		if(file_exists(str_replace(HIGH_CAP_IMAGE_BASE, '', IMAGES_PATH . '/style/bg.jpg')))
		{
			$background = 'url(' . IMAGES_PATH . '/style/bg.jpg)';
		}
	}

	// If the site is not disabled, send them to the home page
	if ($site_disabled == false)
	{
		header('Location: ' . MAIN_SCRIPT);
	}

	// if the IP is excluded, send them home
	if (!empty($g_IP_ExclusionList) && in_array($_SERVER['REMOTE_ADDR'], $g_IP_ExclusionList))
	{
		header('Location: ' . MAIN_SCRIPT);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>503 Down For Maintenance - Dream Dinners</title>
<style type="text/css">
body {
	padding: 0;
	margin: 0;
	background: #512a02 <?php echo $background; ?>;
	font-family: museo-sans, arial, helvetica, verdana;
	font-size: 13px;
	line-height: 17px;
	color: #515151;
	font-weight: 300;
	}
h3 {
 	padding: 0 0 0 0;
	margin: 0 0 0 0;
	font-size: 18px;
	line-height: 24px;
	color: #337a68;
	font-weight: 900;
	}
.container {
	width:600px;
	margin:100px auto;
	}
.header {
	padding:15px;
	background: #ABAC4E;
	color: #337a68;
	font-weight: bold;
	font-size: 18px;
	}
.header img {
	float: left;
	}
.header h3 {
	padding:25px;
	}
.message {
	padding:30px;
	background: #ded6c9
	}
</style>
<script type="text/javascript">
//<![CDATA[
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-425666-1']);
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
//]]>
</script>
</head>
<body>

<div class="container">
	<div class="header">
		<?php echo $logo; ?>
		<div style="clear:both;"></div>
	</div>
	<div class="message">
		<h3>The Dream Dinners website is temporarily down for maintenance.</h3>
		<?php if (!empty($maintenance_messages)) { ?>
		<?php foreach ($maintenance_messages AS $message) { ?>
		<p><?php echo $message['message']; ?></p>
		<?php }} ?>
		<p>We apologize for any inconvenience.</p>
		<p>In the meantime, please come visit us on <a href="http://www.facebook.com/dreamdinners" target="_blank">Facebook</a>!</p>
	</div>
</div>

</body>
</html>