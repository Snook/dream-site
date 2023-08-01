<?php $this->setScript('head', SCRIPT_PATH . '/admin/media.min.js'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/media.css'); ?>
<?php $this->setOnload('media_init();'); ?>
<?php $this->assign('page_title','Media'); ?>
<?php $this->assign('topnav','main'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h2>Dream Dinners Media</h2>

<div class="clear" style="margin-bottom: 10px;"></div>

<div id="media_container-div" class="media_container"><div style="text-align: center;margin-top: 80px;">Select media from the menu</div></div>

<div id="video_list">

	<ul>

		<?php if (!empty($this->soundcloud_tracks)) { ?>

		<?php foreach ($this->soundcloud_tracks AS $track) { ?>

		<li data-soundcloud_id="<?php echo $track->id; ?>"
			data-autoplay="true"
			data-title="<?php echo $track->title; ?>"
			data-date="<?php echo CTemplate::dateTimeFormat(strtotime($track->created_at), MONTH_DAY_YEAR); ?>"
			data-description=""></li>

		<?php } } ?>

		<?php if (!empty($this->youtube_videos->feed->entry)) { ?>

		<?php foreach ($this->youtube_videos->feed->entry AS $video) { ?>

		<li data-youtube_id="<?php echo str_replace('http://gdata.youtube.com/feeds/api/videos/', '', $video->id->{'$t'}); ?>"
			data-autoplay="true"
			data-title="<?php echo $video->title->{'$t'}; ?>"
			data-date="<?php echo CTemplate::dateTimeFormat(strtotime($video->published->{'$t'}), MONTH_DAY_YEAR); ?>"
			data-description="<?php echo $video->content->{'$t'}; ?>"></li>

		<?php } } ?>

	</ul>

</div>

<div id="video_description">
	<div id="description"></div>
</div>

<div class="clear" style="margin-bottom: 10px;"></div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>