<?php $this->assign('page_title','Links &amp; Resources'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1>Links &amp; Resources</h1>
			</div>
		</div>

		<div class="row">
			<div class="col-6">
				<table class="table ddtemp-table-border-collapse">
					<tr>
						<td><b>Dream Dinners</b></td>
					</tr>
					<tr>
						<td>
							<a href="https://support.dreamdinners.com/" target="_blank">Dream Dinners Support</a><br/>
							<a href="https://login.microsoftonline.com " target="_blank">Dream Dinners Webmail Login</a><br/>
							<a href="/backoffice/link_utility">Link Creation Utility</a><br/>
							<a href="/print?menu=<?php echo $this->current_menu_id; ?>&amp;store=<?php echo $this->CurrentBackOfficeStore->id; ?>" target="Print_Menu">Print Menu <?php echo $this->current_menu_name; ?> - <?php echo $this->CurrentBackOfficeStore->store_name; ?></a><br/>
							<a href="/print?menu=<?php echo $this->next_menu_id; ?>&amp;store=<?php echo $this->CurrentBackOfficeStore->id; ?>"  target="Print_Menu">Print Menu <?php echo $this->next_menu_name; ?> - <?php echo $this->CurrentBackOfficeStore->store_name; ?></a><br/>
							<a href="/backoffice/signature_generator">Signature Generator</a><br/>
						</td>
					</tr>
					<tr>
						<td><b>Vendors</b></td>
					</tr>
					<tr>
						<td>
							<a href="https://www.esysco.net" target="_blank">eSYSCO</a><br/>
							<a href="https://app.reciprofity.com/users/login" target="_blank">Reciprofity</a><br/>
							<a href="https://manager.paypal.com/" target="_blank">PayPal Manager</a><br/>
							<a href="https://www.bargreen.com/" target="_blank">Bargreen Ellingson (smallwares)</a><br/>
							<a href="https://www.wasserstrom.com/" target="_blank">Wasserstrom Supplies &amp; Equipment</a><br/>
							<a href="https://dreamdinners.facebook.com/" target="_blank">Workplace</a><br/>
							<a href="https://view.monday.com/1949008947-34ae0641cf7d934d1b2de3fcd067f6bc?r=use1" target="_blank">Company Zoom Event Calendar</a><br/>
							<a href="https://dreamdinners.ourproshop.com" target="_blank">Vistaprint ProShop</a><br/>
							<a href="https://dreammerch.com" target="_blank">Estore</a><br/>
						</td>
					</tr>
				</table>
			</div>

			<div class="col-6">
				<table class="table ddtemp-table-border-collapse">
					<tr>
						<td><b>OneDrive Files</b></td>
					</tr>
					<tr>
						<td>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/Enp9qWgW6plGrINO0wyEHp8BuAXJuTb1-GbnPitkJ-9q2A?e=y06mcP" target="_blank">Monthly Packet Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EmxdVerU2eNAuNCoZsquGiMBCbbSMql_-Et0Aw-e7NTMQQ?e=31SQE6" target="_blank">The Pantry Training and Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EuibZbgyjb5ItuWHQQ9pj44BqL68jsQWDWtpTHqUbI-WJg?e=G5de8f" target="_blank">Finance Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/Ep0hmBj6NVBOhQVLVJFAsiQBVo0VJLPhTCkUei9MuU6H8Q?e=lsHCLZ" target="_blank">Franchise Sales Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/Eu9rgo4VIVpGtvsgV49K9IEBsLeG-2mV41EI6R9PYkMYuw?e=nxNoLK" target="_blank">Marketing Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EgKu2LZ8zCBAsnxRnNKrGuUBODPoDnPpluXmbw0Gtn4PPg?e=m5VEvI" target="_blank">Sales Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EpYwg2N6wnpNqcLFFsVOn5wB2nC8g-r5x8pZlnQmf3gM2g?e=VNwCIT" target="_blank">Live Streams &amp; Webinars</a><br/>
						</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>