<?php $this->assign('page_title','Resources'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<?php if (isset($this->form['store_html'])) { ?>
			<div class="row mb-4">
				<div class="col">
					<form method="post">
						<b>Store:</b>&nbsp;<?=$this->form['store_html']; ?> Some items are store specific
					</form>
				</div>
			</div>
		<?php } ?>

		<div class="row mb-4">
			<div class="col">
				<div class="list-group list-group-horizontal mb-2">
					<a class="list-group-item list-group-item-action" href="?page=print&amp;menu=<?php echo $this->current_menu_id; ?>&amp;store=<?php echo $this->store_id; ?>" target="Print_Menu">Print Menu <?php echo $this->current_menu_name; ?></a>
					<a class="list-group-item list-group-item-action" href="?page=print&amp;menu=<?php echo $this->next_menu_id; ?>&amp;store=<?php echo $this->store_id; ?>"  target="Print_Menu">Print Menu <?php echo $this->next_menu_name; ?></a>
				</div>
				<div class="list-group">
					<a class="list-group-item list-group-item-action" href="https://www.bargreen.com/" target="_blank">Bargreen Ellingson - For ordering additional smallwares</a>
					<a class="list-group-item list-group-item-action" href="https://dreammerch.com" target="_blank">Dream Dinners Estore</a>
					<a class="list-group-item list-group-item-action" href="https://support.dreamdinners.com/" target="_blank">Dream Dinners Support</a>
					<a class="list-group-item list-group-item-action" href="https://login.microsoftonline.com " target="_blank">Dream Dinners Webmail Login</a>
					<a class="list-group-item list-group-item-action" href="https://www.esysco.net" target="_blank">eSYSCO</a>
					<a class="list-group-item list-group-item-action" href="/?page=admin_link_utility">Link Creation Utility</a>
					<a class="list-group-item list-group-item-action" href="https://dreamdinners.workplace.com/work/knowledge/5299719493410560" target="_blank">The Pantry</a>
					<a class="list-group-item list-group-item-action" href="https://manager.paypal.com/" target="_blank">PayPal Manager</a>
					<a class="list-group-item list-group-item-action" href="https://dreamdinners.my.site.com"  target="_blank">Salesforce</a>
					<?php if ($this->user_type != CUser::FRANCHISE_STAFF) { ?>
						<a class="list-group-item list-group-item-action" href="/?page=admin_signature_generator">Signature Generator</a>
					<?php } ?>
					<a class="list-group-item list-group-item-action" href="https://dreamdinners.ourproshop.com" target="_blank">Vistaprint ProShop</a>
					<a class="list-group-item list-group-item-action" href="https://www.wasserstrom.com/" target="_blank">Wasserstrom Supplies &amp; Equipment</a>
					<a class="list-group-item list-group-item-action" href="https://dreamdinners.facebook.com/" target="_blank">Workplace</a>
					<a class="list-group-item list-group-item-action" href="https://view.monday.com/1949008947-34ae0641cf7d934d1b2de3fcd067f6bc?r=use1" target="_blank">Company Zoom Event Calendar</a>
					<a class="list-group-item list-group-item-action" href="https://app.reciprofity.com/users/login" target="_blank">Reciprofity</a>
				<?php if( CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER){ ?>
					<a class="list-group-item list-group-item-action" href="https://ship5.shipstation.com/" target="_blank">ShipStation</a>
				<?php } ?>

	</div>
			</div>
		</div>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>