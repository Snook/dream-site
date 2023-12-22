<?php
	if (defined('IMAGES_PATH'))
	{
			$logo = '<img style="width:80px; height:75px;" src="' . IMAGES_PATH . '/style/dreamdinners_logo.gif" alt="Dream Dinners" />';
			$background = 'url(' . IMAGES_PATH . '/style/bg.jpg)';
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php echo 'Dream Dinners - Quick, Healthy and Easy Family Dinners'; ?></title>

<?php $this->setOnLoad("portal_init();"); ?>

<?php include $this->loadTemplate('admin/page_head_css.tpl.php'); ?>

<style type="text/css">

.container {
	width:100%;
	height:100%;
	margin:0;
	}

.header {
	padding:0px;
	background: #DED6CB;
	color: #337a68;
	font-weight: bold;
	font-size: 18px;
	width:200px;
	height: 100%;
	}

.header img {
	float: left;
	margin: 3px;
	}

.dest_button {
    width:200px;
	height:50px;
	margin-bottom:4px;
	vertical-align:middle;
	text-align:center;
	color: #F1E8D8 !important;
      background-image: -moz-linear-gradient(#327967, #1D5042);
      background-image: -webkit-linear-gradient(#327967, #1D5042);
      background-image: linear-gradient(#327967, #1D5042);
      background-repeat: repeat-x;
      border: 1px solid #1D5042;
	  background-color: #009900;
  font-size: 18px !important;
	padding:0px;
	padding-top:14px;;
    }

    .selected_button
    {
	  color: #F1E8D8 !important;
      background-image: -moz-linear-gradient(#337a68, #1D5042) !important;
      background-image: -webkit-linear-gradient(#337a68, #1D5042) !important;
      background-image: linear-gradient(#33ff68, #1D5042) !important;
      background-repeat: repeat-x;
      border: 1px solid #1D5042;
	  background-color: #00cc00;
    }


  .dd_content {
  	width:100%;
  	height:100%;
  	display:none;
  	position:absolute;
    left: 200px;
    top:0px;

  }
</style>
</head>
<body>

<div class="container">
	<div class="header">
		<?php echo $logo; ?><br /><br />
        <div class="btn btn-primary btn-sm dest_button" data-content_link="calendar">Calendar</div>
		<div class="btn btn-primary btn-sm dest_button" data-content_link="fadmin">BackOffice</div>
		<div class="btn btn-primary btn-sm dest_button" data-content_link="support">Support</div>
	    <div class="btn btn-primary btn-sm dest_button" data-content_link="real_talk">Real Talk</div>
		<div class="btn btn-primary btn-sm dest_button" data-content_link="monthly_packet">Monthly Packet</div>
	    <div class="btn btn-primary btn-sm dest_button" data-content_link="flash_news">Flash News</div>
	    <div class="btn btn-primary btn-sm dest_button" data-content_link="weekly_news">Weekly News</div>
	    <div class="btn btn-primary btn-sm dest_button" data-content_link="marketing">Marketing Resources</div>
	    <div class="btn btn-primary btn-sm dest_button" data-content_link="sales">Sales Resources</div>

	   </div>
</div>

<div class="container">
    <iframe class="dd_content" data-has_loaded="false" id="calendar" data-content_id="calendar" data-content_source="<?= HTTPS_SERVER?>/backoffice/session-mgr" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="fadmin" data-content_id="fadmin" data-content_source="<?= HTTPS_SERVER?>/backoffice/main" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="support" data-content_id="support" data-content_source="https://support.dreamdinners.com" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="real_talk" data-content_id="real_talk" data-content_source="https://support.dreamdinners.com/discussions/forums/5000174751" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="monthly_packet" data-content_id="monthly_packet" data-content_source="https://app.box.com/embed_widget/s/kidqmxs7lrou8t7so0tifxym4c17ik5j?view=list&sort=name&direction=ASC&theme=blue" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="ddu" data-content_id="ddu" data-content_source="https://share.dreamdinners.com/" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="flash_news" data-content_id="flash_news" data-content_source="https://support.dreamdinners.com/discussions/forums/5000296233" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="weekly_news" data-content_id="weekly_news" data-content_source="https://support.dreamdinners.com/discussions/forums/5000296233" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="marketing" data-content_id="marketing" data-content_source="https://app.box.com/embed_widget/s/kidqmxs7lrou8t7so0tifxym4c17ik5j?view=list&sort=name&direction=ASC&theme=blue" src=""></iframe>
    <iframe class="dd_content" data-has_loaded="false" id="sales" data-content_id="sales" data-content_source="https://app.box.com/embed_widget/s/kidqmxs7lrou8t7so0tifxym4c17ik5j?view=list&sort=name&direction=ASC&theme=blue" src=""></iframe>

  <!--   <iframe src="" width="500" height="400" frameborder="0"allowfullscreen webkitallowfullscreen msallowfullscreen></iframe> -->

</div>

<?php include $this->loadTemplate('admin/page_footer_javascript.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer_onload.tpl.php'); ?>

<script type="text/javascript">
	//<![CDATA[
	function portal_init()
	{
		setContent('calendar');

		$("[data-content_link]").click(function() {
			setContent($(this).data('content_link'));
		});
	}

	function setContent(content_id)
	{
		$("[data-content_id]").each(function() {
			$(this).hide();
		});

		$("[data-content_link]").each(function() {
			$(this).removeClass('selected_button');
		});

		var src_url = $("[data-content_id=" + content_id + "]").data("content_source");
		var loaded_state = $("[data-content_id=" + content_id + "]").data('has_loaded');

		if (loaded_state == false)
		{
			$("[data-content_id=" + content_id + "]").attr('src',  src_url);
			$("[data-content_id=" + content_id + "]").data('has_loaded',  'true');
		}

		$("[data-content_id=" + content_id + "]").show();
		$("[data-content_link=" + content_id + "]").addClass('selected_button');

	}
	//]]>
</script>

</body>
</html>