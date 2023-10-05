<?php $this->setScript('head', SCRIPT_PATH . '/admin/manage_dream_event_properties.min.js'); ?>
<?php $this->setOnload('manage_dream_event_properties_init();'); ?>
<?php $this->assign('page_title','Manage Dream/Event Properties'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h1>Manage Dream/Event/Fundraiser Properties</h1>

<?php if (!empty($this->editProperties) || !empty($this->createBundleTheme)) { ?>

	<form method="post">
		<?php echo $this->form['hidden_html']; ?>

		<table style="width: 100%;">
			<tbody>
			<tr>
				<td class="bgcolor_medium header_row" style="width: 50%;">Name</td>
				<td class="bgcolor_medium header_row">Value</td>
			</tr>
			</tbody>
			<tbody id="tbody_bundle_id">
			<?php if (!empty($this->editProperties)) { ?>
				<tr>
					<td class="bgcolor_light">Theme ID</td>
					<td class="bgcolor_light"><?php echo $this->editProperties->id; ?></td>
				</tr>
			<?php } ?>
			</tbody>
			<tr>
				<td class="bgcolor_light">Menu</td>
				<td class="bgcolor_light"><?php echo $this->form['menu_id_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Title</td>
				<td class="bgcolor_light"><?php echo $this->form['dream_taste_event_theme_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Bundle</td>
				<td class="bgcolor_light"><?php echo $this->form['bundle_id_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Show menu items on collateral and website</td>
				<td class="bgcolor_light"><?php echo $this->form['menu_used_with_theme_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Host Required</td>
				<td class="bgcolor_light"><?php echo $this->form['host_required_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Password Required</td>
				<td class="bgcolor_light"><?php echo $this->form['password_required_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">On Customer Site</td>
				<td class="bgcolor_light"><?php echo $this->form['available_on_customer_site_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Fundraiser Value</td>
				<td class="bgcolor_light"><?php echo $this->form['fundraiser_value_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Can RSVP Only</td>
				<td class="bgcolor_light"><?php echo $this->form['can_rsvp_only_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Can RSVP Upgrade</td>
				<td class="bgcolor_light"><?php echo $this->form['can_rsvp_upgrade_html']; ?></td>
			</tr>
			<tr>
				<td class="bgcolor_light">Existing guests can attend</td>
				<td class="bgcolor_light"><?php echo $this->form['existing_guests_can_attend_html']; ?></td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td class="bgcolor_light" colspan="2" style="text-align: center; padding: 6px;">
					<?php echo $this->form['submit_html']; ?>
					<?php echo $this->form['delete_html']; ?>
				</td>
			</tr>
			</tfoot>
		</table>

	</form>

<?php } else { ?>

	<table style="width: 100%;">
		<thead>
		<tr>
			<td class="bgcolor_medium header_row">ID</td>
			<td class="bgcolor_medium header_row">Menu</td>
			<td class="bgcolor_medium header_row">Title</td>
			<td class="bgcolor_medium header_row">Session Type</td>
			<td class="bgcolor_medium header_row">Path</td>
			<td class="bgcolor_medium header_row"><a href="/backoffice/manage_dream_event_properties?create" class="button">Create Properties</a></td>
		</tr>
		</thead>
		<?php foreach ($this->dtePropertiesArray AS $id => $dteProperties) { ?>
			<tbody>
			<tr>
				<td class="bgcolor_light" style="text-align: center;"><?php echo $dteProperties->id; ?></td>
				<td class="bgcolor_light"><?php echo $dteProperties->menu_name; ?></td>
				<td class="bgcolor_light"><?php echo $dteProperties->title; ?></td>
				<td class="bgcolor_light"><?php echo $dteProperties->session_type; ?></td>
				<td class="bgcolor_light"><?php echo $dteProperties->theme_string; ?></td>
				<td class="bgcolor_light" style="text-align: center;">
					<span data-dtep_id="<?php echo $dteProperties->id; ?>" class="view_items button">Details</span>
					<a href="/backoffice/manage_dream_event_properties?edit=<?php echo $dteProperties->id; ?>" class="button">Edit</a>
				</td>
			</tr>
			</tbody>
			<tbody data-properties="<?php echo $dteProperties->id; ?>" style="display: none;">
			<tr>
				<td class="bgcolor_light" colspan="7">
					<ul>
						<li>Host required: <?php echo (!empty($dteProperties->host_required) ? 'Yes' : 'No'); ?></li>
						<li>Password required: <?php echo (!empty($dteProperties->password_required) ? 'Yes' : 'No'); ?></li>
						<li>Available on customer site: <?php echo (!empty($dteProperties->available_on_customer_site) ? 'Yes' : 'No'); ?></li>
						<li>Number of servings required: <?php echo $dteProperties->number_servings_required; ?></li>
						<li>Price: $<?php echo $dteProperties->price; ?></li>
						<li>Theme title: <?php echo $dteProperties->title; ?></li>
						<li>Theme path: <?php echo $dteProperties->theme_string; ?></li>
						<li>Session type: <?php echo $dteProperties->session_type; ?></li>
						<li>Fundraiser value: <?php echo $dteProperties->fundraiser_value; ?></li>
						<li>Can RSVP Only: <?php echo (!empty($dteProperties->can_rsvp_only) ? 'Yes' : 'No'); ?></li>
						<li>Can RSVP Upgrade: <?php echo (!empty($dteProperties->can_rsvp_upgrade) ? 'Yes' : 'No'); ?></li>
					</ul>
				</td>
			</tr>
			</tbody>
		<?php } ?>
	</table>

<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>