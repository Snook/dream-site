<?php if (!empty($this->create_new)) { $notice = $this->notice; } ?>
<div class="row mb-2 py-3 <?php echo (!empty($this->create_new)) ? 'collapse' : ''; ?>" data-notice_id_div="<?php echo $notice['id']; ?>" data-home_office_managed="<?php echo $notice['home_office_managed']; ?>">
	<form method="post" class="col notice-form needs-validation" novalidate>
		<input type="hidden" name="id" value="<?php echo $notice['id']; ?>" />
		<input type="hidden" name="store_id" value="<?php echo $notice['store_id']; ?>" />
		<input type="hidden" name="home_office_managed" value="<?php echo $notice['home_office_managed']; ?>" />
		<?php if ($this->manageSingleStore || empty($notice['home_office_managed'])) { ?>
			<input type="hidden" name="audience" value="STORE" />
		<?php } ?>
		<div class="row">
			<?php if (!$this->manageSingleStore && $notice['home_office_managed']) { ?>
				<div class="col-12 col-md-auto flex-fill mb-2">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">Audience</div>
						</div>
						<?php CForm::formElement(array(
							CForm::type => CForm::DropDown,
							CForm::required => true,
							CForm::name => 'audience',
							CForm::css_class => 'notice-select-audience',
							CForm::options => array(
								'CUSTOMER' => 'Website Banner',
								'FADMIN' => 'Admin Banner',
								'SITE_WIDE' => 'Website &amp; Admin',
								'STORE' => 'Store Page Promo'
							)
						), $notice['audience']); ?>
						<div class="input-group-append div-notice-select-store <?php echo ($notice['audience'] === 'STORE') ? '' : 'collapse'; ?>">
							<button type="button" class="btn btn-primary notice-store-select">
								<span class="d-block">Stores</span>
								<span class="d-block">(<span class="notice-store_id-count"><?php echo (empty($notice['store_id'])) ? 0 : count(explode(',', $notice['store_id'])); ?></span>)</span>
							</button>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="col-12 col-md-auto flex-fill mb-2">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">From</div>
					</div>
					<?php CForm::formElement(array(
						CForm::type => CForm::Date,
						CForm::name => 'message_start_date',
						CForm::required => true
					), CTemplate::dateTimeFormat($notice['message_start'], YEAR_MONTH_DAY)); ?>
					<?php CForm::formElement(array(
						CForm::type => CForm::Time,
						CForm::name => 'message_start_time',
						CForm::required => true
					), CTemplate::dateTimeFormat($notice['message_start'], HH_MM)); ?>
				</div>
			</div>
			<div class="col-12 col-md-auto flex-fill mb-2">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">To</div>
					</div>
					<?php CForm::formElement(array(
						CForm::type => CForm::Date,
						CForm::name => 'message_end_date',
						CForm::required => true
					), CTemplate::dateTimeFormat($notice['message_end'], YEAR_MONTH_DAY)); ?>
					<?php CForm::formElement(array(
						CForm::type => CForm::Time,
						CForm::name => 'message_end_time',
						CForm::required => true
					), CTemplate::dateTimeFormat($notice['message_end'], HH_MM)); ?>
				</div>
			</div>
			<div class="col-12 col-md-auto flex-fill div-notice-title mb-2 <?php echo ($notice['audience'] === 'STORE') ? '' : 'collapse'; ?>">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Title</div>
					</div>
					<?php CForm::formElement(array(
						CForm::type => CForm::Text,
						CForm::name => 'title',
						CForm::css_class => 'notice-title',
						CForm::maxlength => (($this->manageSingleStore) ? 50 : false),
						CForm::required => (($notice['audience'] === 'STORE') ? true : false)
					), $notice['title']); ?>
				</div>
			</div>
			<div class="col-12 col-md-auto flex-fill mb-2 div-notice-select-style <?php echo ($notice['audience'] !== 'STORE') ? '' : 'collapse'; ?>">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">Style</div>
					</div>
					<?php CForm::formElement(array(
						CForm::type => CForm::DropDown,
						CForm::required => (($notice['audience'] !== 'STORE') ? true : false),
						CForm::name => 'alert_css',
						CForm::css_class => 'notice-alert_css',
						CForm::options => array(
							'' => 'Select',
							'alert alert-cyan' => 'Cyan',
							'alert alert-cyan-dark' => 'Cyan Dark',
							'alert alert-danger' => 'Danger',
							'alert alert-dark' => 'Dark',
							'alert alert-gray' => 'Gray',
							'alert alert-green' => 'Green',
							'alert alert-green-dark' => 'Green Dark',
							'alert alert-green-dark-extra' => 'Green Dark Extra',
							'alert alert-green-light' => 'Green Light',
							'alert alert-info' => 'Info',
							'alert alert-light' => 'Light',
							'alert alert-orange' => 'Orange',
							'alert alert-orange-dark' => 'Orange Dark',
							'alert alert-primary' => 'Primary',
							'alert alert-red-royal' => 'Red Royal',
							'alert alert-secondary' => 'Secondary',
							'alert alert-success' => 'Success',
							'alert alert-warning' => 'Warning',
						)
					), $notice['alert_css']); ?>
				</div>
			</div>
		</div>
		<div class="row mb-2">
			<div class="col">
				<?php CForm::formElement(array(
					CForm::type => CForm::TextArea,
					CForm::name => 'message',
					CForm::css_class => 'notice-message' . (($notice['audience'] !== 'STORE' && !empty($notice['alert_css'])) ? ' ' . $notice['alert_css'] : ''),
					CForm::maxlength => (($this->manageSingleStore) ? 300 : false),
					CForm::required => true
				), $notice['message']); ?>
			</div>
		</div>
		<div class="row">
			<?php if ($this->manageSingleStore && $notice['home_office_managed']) { ?>
				<div class="col pr-lg-5 text-center text-lg-right">
					Managed by Home Office
				</div>
			<?php } else { ?>
				<div class="col-lg-8 pl-lg-4">
					<?php if (!$this->manageSingleStore && empty($notice['home_office_managed'])) { ?>
						<span class="btn btn-primary btn-sm notice-store-filter" data-filter_store_id="<?php echo $notice['store_id']; ?>">
						<i class="fas fa-filter"></i> <?php echo $notice['store_name']; ?></span><?php } ?>
				</div>
				<div class="col-lg-2">
					<button type="submit" class="btn btn-primary btn-sm btn-block btn-spinner notice-save">Save</button>
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-danger btn-sm btn-block notice-delete"><i class="far fa-trash-alt"></i> Delete</button>
				</div>
			<?php } ?>
		</div>
	</form>
</div>