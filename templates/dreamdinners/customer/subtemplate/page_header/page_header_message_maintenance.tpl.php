<?php if (CBrowserSession::getValue('FAUID')) { ?>
	<div class="container d-print-none">
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<div class="row">
				<div class="col-md-8 mb-2">
					Warning! You are currently logged into a guest account!
				</div>
				<div class="col-md-4">
					<span class="btn btn-sm btn-danger btn-block float-right return-fauid">Return to BackOffice</span>
				</div>
			</div>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
<?php } ?>
<?php if (!empty($this->app_maintenance_message) && (array_key_exists('SITE_WIDE', $this->app_maintenance_message['audience']) || array_key_exists('CUSTOMER', $this->app_maintenance_message['audience']))) { ?>
	<div class="container d-print-none">
		<?php foreach ($this->app_maintenance_message['message'] AS $maintenance) { ?>
			<?php if ($maintenance['audience'] == 'SITE_WIDE' || $maintenance['audience'] == 'CUSTOMER') { ?>
				<div class="<?php echo $maintenance['alert_css']; ?>" role="alert">
					<?php if (!empty($maintenance['icon'])) { ?><img src="<?php echo IMAGES_PATH; ?>/icon/<?php echo $maintenance['icon']; ?>.png" alt="Alert" class="align-baseline" /><?php } ?> <?php echo $maintenance['message']; ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
<?php } ?>
