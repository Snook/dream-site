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
							<a href="mailto:support@dreamdinners.com" target="_blank">Dream Dinners Support</a><br/>
							<a href="https://login.microsoftonline.com " target="_blank">Dream Dinners Webmail Login</a><br/>
							<a href="/print?menu=<?php echo $this->current_menu_id; ?>&amp;store=<?php echo $this->CurrentBackOfficeStore->id; ?>" target="Print_Menu">Print Menu <?php echo $this->current_menu_name; ?> - <?php echo $this->CurrentBackOfficeStore->store_name; ?></a><br/>
						</td>
					</tr>
					<tr>
						<td><b>Vendors</b></td>
					</tr>
					<tr>
						<td>
							<a href="https://shop.sysco.com" target="_blank">SYSCO</a><br/>
							<a href="https://app.reciprofity.com/users/login" target="_blank">Reciprofity</a><br/>
							<a href="https://manager.paypal.com/" target="_blank">PayPal Manager</a><br/>
							<a href="https://www.bargreen.com/" target="_blank">Bargreen Ellingson (smallwares)</a><br/>
							<a href="https://www.wasserstrom.com/" target="_blank">Wasserstrom Supplies &amp; Equipment</a><br/>
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
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EgsUeqhDzulHmWlGEWMBTnEBzgYOlKNH_mNRLZBU9nmzrg?e=90ijaa" target="_blank">All Folders</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/Enp9qWgW6plGrINO0wyEHp8BuAXJuTb1-GbnPitkJ-9q2A?e=ZbvHM4" target="_blank">Monthly Packets</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EggdLnRxM2lIldWSzbyYD9IBpLxZ9xukfJbMtdkLQ5IZzA?e=Te7RPr" target="_blank">Transition Videos</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EmxdVerU2eNAuNCoZsquGiMBCbbSMql_-Et0Aw-e7NTMQQ?e=HGFdCV" target="_blank">The Pantry</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/EuibZbgyjb5ItuWHQQ9pj44BqL68jsQWDWtpTHqUbI-WJg?e=rUWkNc" target="_blank">Finance Files</a><br/>
							<a href="https://dreamdinners-my.sharepoint.com/:f:/p/externalfiles/Eu9rgo4VIVpGtvsgV49K9IEBsLeG-2mV41EI6R9PYkMYuw?e=bgMeQF" target="_blank">Marketing Files</a><br/>
						</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>