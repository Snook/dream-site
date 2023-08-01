<?php if (!empty($this->app_maintenance_message) && (array_key_exists('SITE_WIDE', $this->app_maintenance_message['audience']) || array_key_exists('FADMIN', $this->app_maintenance_message['audience']))) { ?>
	<div class="container pt-3 d-print-none">
		<?php foreach ($this->app_maintenance_message['message'] AS $maintenance) { ?>
			<?php if ($maintenance['audience'] == 'SITE_WIDE' || $maintenance['audience'] == 'FADMIN') { ?>
				<div class="<?php echo $maintenance['alert_css']; ?>" role="alert">
					<?php if (!empty($maintenance['icon'])) { ?><img src="<?php echo IMAGES_PATH; ?>/icon/<?php echo $maintenance['icon']; ?>.png" alt="Alert" class="align-baseline" /><?php } ?> <?php echo $maintenance['message']; ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
<?php } ?>
<?php if (defined('DEV_BASE_NAME')) { ?>
	<div class="dev_branch_message"><?php echo DEV_BASE_NAME; ?> branch!</div>
<?php } ?>