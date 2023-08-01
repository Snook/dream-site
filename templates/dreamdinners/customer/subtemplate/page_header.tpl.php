<!DOCTYPE html>
<html lang="en"<?php if (empty($this->itemscope) || $this->itemscope !== false) { ?> itemscope itemtype="http://schema.org/<?php echo (!empty($this->itemscope)) ? $this->itemscope : 'Article';?>"<?php } ?> prefix="fb: http://www.facebook.com/2008/fbml">
<head>
	<title lang="en-us"><?php echo (!empty($this->page_title)) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - The Original Meal Kit - Easy, Homemade Meals'; ?></title>
	<?php if (!empty($this->no_cache))  { //always refresh order pages ?>
		<meta http-equiv="pragma" content="no-cache" />
		<meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate" />
		<meta http-equiv="expires" content="Fri, 30 Dec 2000 12:00:00 GMT" />
	<?php } ?>
	<?php if (DD_SERVER_NAME != 'LIVE') { ?>
		<meta name="robots" content="noindex">
	<?php }?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?php echo (!empty($this->page_description)) ? $this->page_description : 'Dream Dinners provides everything you need to make homemade dinners for your family.'; ?>" />
	<meta name="keywords" content="<?php echo (!empty($this->page_keywords)) ? $this->page_keywords : 'meal kit, freezer dinners, make ahead meals, prepared dinners, meal prep, family dinner, meal assembly, prepared meals, whats for dinner, family meals, make your own meals'; ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?php echo (!empty($this->og_title)) ? $this->og_title  . ' - Dream Dinners' : (!empty($this->page_title) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - The Original Meal Kit Company'); ?>" />
	<meta property="og:url" content="<?php echo (!empty($this->og_url)) ? $this->og_url : ((!empty($this->canonical_url)) ? $this->canonical_url : basename($_SERVER['SCRIPT_NAME']) . ((!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '') ); ?>" />
	<meta property="og:image" content="<?php echo (!empty($this->og_image)) ? $this->og_image : IMAGES_PATH . '/style/share_dreamdinners.png'; ?>" />
	<meta property="og:description" content="<?php echo (!empty($this->og_description)) ? $this->og_description : 'Dream Dinners provides everything you need to serve homemade dinners for your family.'; ?>" />
	<meta itemprop="name" content="<?php echo (!empty($this->og_title)) ? $this->og_title  . ' - Dream Dinners' : (!empty($this->page_title) ? $this->page_title . ' - Dream Dinners' : 'Dream Dinners - Easy Family Dinners'); ?>" />
	<meta itemprop="description" content="<?php echo (!empty($this->og_description)) ? $this->og_description : 'Dream Dinners provides everything you need to serve homemade dinners for your family.'; ?>" />
	<meta itemprop="image" content="<?php echo (!empty($this->og_image)) ? $this->og_image : IMAGES_PATH . '/style/share_dreamdinners.png'; ?>" />
	<?php echo (!empty($this->canonical_url)) ? '<link rel="canonical" href="' . $this->canonical_url . '" />' . "\n" : ''; ?>
	<link rel="alternate" type="application/rss+xml" title="Dream Dinners Blog" href="http://blog.dreamdinners.com/feed/" />
	<link rel="icon" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon.ico" type="image/x-icon" />
	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon-precomposed" sizes="60x60" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-60x60.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon-152x152.png" />
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo IMAGES_PATH; ?>/style/favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon-196x196.png" sizes="196x196" />
	<link rel="icon" type="image/png" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/png" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon-16x16.png" sizes="16x16" />
	<link rel="icon" type="image/png" href="<?php echo IMAGES_PATH; ?>/style/favicon/favicon-128.png" sizes="128x128" />


	<link rel="preload" href="<?php echo IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" as="image" type="image/png" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-ExtraBold.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-ExtraLight.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-Light.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-SemiBold.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/Montserrat-Thin.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo CSS_PATH; ?>/customer/fonts/ddicon.ttf" as="font" type="font/ttf" crossorigin>
	<link rel="preload" href="//use.fontawesome.com/releases/v5.9.0/webfonts/fa-brands-400.woff2" as="font" type="font/ttf" crossorigin>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" rel="stylesheet">

	<link rel="manifest" href="<?php echo IMAGES_PATH; ?>/style/favicon/manifest.json" crossorigin="use-credentials">
	<link rel="mask-icon" href="<?php echo IMAGES_PATH; ?>/style/favicon/safari-pinned-tab.svg" color="#829c2a">
	<meta name="application-name" content="Dream Dinners"/>
	<meta name="theme-color" content="#829c2a">
	<meta name="apple-mobile-web-app-title" content="Dream Dinners">
	<meta name="apple-mobile-web-app-status-bar-style" content="#512b1b">
	<meta name="msapplication-config" content="<?php echo IMAGES_PATH; ?>/style/favicon/browserconfig.xml">
	<meta name="msapplication-TileColor" content="#829c2a" />
	<meta name="msapplication-navbutton-color" content="#512b1b">
	<meta name="msapplication-TileImage" content="<?php echo IMAGES_PATH; ?>/style/favicon/mstile-144x144.png" />
	<meta name="msapplication-square70x70logo" content="<?php echo IMAGES_PATH; ?>/style/favicon/mstile-70x70.png" />
	<meta name="msapplication-square150x150logo" content="<?php echo IMAGES_PATH; ?>/style/favicon/mstile-150x150.png" />
	<meta name="msapplication-wide310x150logo" content="<?php echo IMAGES_PATH; ?>/style/favicon/mstile-310x150.png" />
	<meta name="msapplication-square310x310logo" content="<?php echo IMAGES_PATH; ?>/style/favicon/mstile-310x310.png" />
	<meta name="msvalidate.01" content="B11D797C8672F208105ED91D6FFEBE21" />
	<link rel="publisher" href="https://www.facebook.com/dreamdinners" />
	<link rel="publisher" href="https://twitter.com/dreamdinners" />
    <?php include $this->loadTemplate('customer/subtemplate/page_header/page_header_preload.tpl.php'); ?>
	<?php include $this->loadTemplate('customer/subtemplate/page_header/page_header_css.tpl.php'); ?>
	<?php include $this->loadTemplate('customer/subtemplate/page_header/page_header_javascript.tpl.php'); ?>
</head>
<body>

<?php if (!empty($this->javascript_required_alert)) { ?>
	<noscript>
		<div id="noscript-text">
			<b>The Dream Dinners website makes extensive use of JavaScript to provide you with a dynamic browsing experience and is required for the ordering process.</b><br />
			Please <a href="https://support.google.com/adsense/answer/12654" target="_blank">enable JavaScript</a> in your browser.
		</div>
	</noscript>
<?php } ?>

<?php if (empty($this->no_navigation) || $this->no_navigation != true) { include $this->loadTemplate('customer/subtemplate/page_header/page_header_navigation.tpl.php'); } ?>

<?php include $this->loadTemplate('customer/subtemplate/page_header/page_header_message_maintenance.tpl.php'); ?>