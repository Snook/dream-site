<div id="ilgs_container">

	<div>

		<select id="ilgs_search_type">
			<option value="firstname">First Name</option>
			<option value="lastname" selected="selected">Last Name</option>
			<option value="firstlast">First &amp; Last Name</option>
			<option value="email">Email Address</option>
			<option value="id">Customer ID</option>
		</select>

		<input id="ilgs_search_value" type="text" />
		<input id="ilgs_search_all" type="checkbox" <?php if (!empty($this->all_stores_checked)) { ?>checked="checked"<?php } ?> data-tooltip="Search All Stores" />

		<span id="ilgs_search_go" class="button">Search</span> <img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign img_throbber_circle" data-tooltip="Processing" alt="Processing" />

	</div>

	<div id="ilgs_results"></div>

</div>