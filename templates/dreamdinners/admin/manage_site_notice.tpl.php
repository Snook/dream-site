<?php $this->setScript('foot', SCRIPT_PATH . '/admin/manage_site_notice.min.js'); ?>
<?php //$this->setScriptVar('maintenance_js = ' . $this->maintenance_js . ';'); ?>
<?php //$this->setScriptVar('storeList_js = ' . $this->stores_js . ';'); ?>
<?php $this->setScriptVar('manageSingleStore = ' . ((!empty($this->manageSingleStore)) ? $this->manageSingleStore : 'false') . ';'); ?>
<?php $this->assign('page_title', 'Manage Site Promotions'); ?>
<?php $this->assign('topnav', 'store'); ?>
<?php //include $this->loadTemplate('admin/subtemplate/page_header/page_header.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid" id="vuenotices">

		<div class="row my-4">
			<div class="col-lg-6 text-center mb-3 order-lg-2">
				<h1>Manage Site Promotions</h1>
				<h2>Times are Eastern, current time is <span class="notice-current-time"><?php echo CTemplate::dateTimeFormat(time(), SIMPLE_TIME); ?></span></h2>
			</div>
			<div class="col-8 col-lg-3 order-lg-1">
				<?php if (!$this->manageSingleStore) { ?>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<div class="input-group-text"><i class="fas fa-filter"></i></div>
						</div>
						<select class="form-control form-control-sm notice-select-filter">
							<option value="">Show All</option>
							<option value="home_office_managed">Home Office Managed</option>
							<option value="home_office_managed_not">Not Home Office Managed</option>
							<?php foreach (CStore::getSiteNoticeMenu(true, ture, false, $this->isHomeOffice) AS $state => $stateArray) { ?>
								<optgroup label="<?php echo $state; ?>">
									<?php foreach ($stateArray['stores'] AS $store) { ?>
										<option value="<?php echo $store['id']; ?>"><?php echo $store['store_name']; ?></option>
									<?php } ?>
								</optgroup>
							<?php } ?>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class="col-4 col-lg-3 text-center text-lg-right order-lg-3">
				<button class="btn btn-primary btn-sm notice-create"><i class="far fa-plus-square"></i> Create New</button>
			</div>
		</div>

		<section class="notice-list">
			<?php foreach ($this->maintenance_array AS $noticeIndex => $notice) { ?>
				<?php include $this->loadTemplate('admin/subtemplate/manage_site_notice/manage_site_notice_form.tpl.php'); ?>
			<?php } ?>
		</section>

	</div>

<?php //include $this->loadTemplate('admin/subtemplate/page_footer/page_footer.tpl.php'); ?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>