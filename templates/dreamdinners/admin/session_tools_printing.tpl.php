<?php $this->setScript('head', SCRIPT_PATH . '/admin/session_tools_printing.min.js'); ?>
<?php $this->setScriptVar('store_id = ' . $this->store_id . ';'); ?>
<?php $this->setOnload('session_tools_printing_init();'); ?>
<?php $this->assign('page_title','Printing Tool'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<h3>Print Generic Menus</h3>

<?php if ($this->show['store_selector'] && !empty($this->form['store_html'])) { ?>
	<div id="store_selector">
		<form method="post">
			Store: <?php echo $this->form['store_html']; ?>
		</form>
	</div>
<?php } ?>

	<table>
		<tr>
			<td colspan="2">
				<?php echo $this->form['menus_html']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<input id="core" name="core" data-print_menu="core" type="checkbox" />
			</td>
			<td><label for="core">Menu</label></td>
		</tr>
		<tr>
			<td>
				<input id="freezer" name="freezer" data-print_menu="freezer" type="checkbox" />
			</td>
			<td><label for="freezer">Freezer Sheet</label></td>
		</tr>
		<tr>
			<td>
				<input id="nutrition" name="nutrition" data-print_menu="nutrition" type="checkbox" />
			</td>
			<td><label for="nutrition">Nutritionals</label></td>
		</tr>
		<?php if ($this->CurrentBackOfficeStore->storeSupportsIntroOrders(CMenu::getCurrentMenuId())) { ?>
			<tr>
				<td>
					<input id="intro" name="intro" data-print_menu="intro" type="checkbox" />
				</td>
				<td><label for="intro">Meal Prep Starter Pack</label></td>
			</tr>
		<?php } ?>
		<tr>
			<td>
				<input id="dream_taste" name="dream_taste" data-print_menu="dream_taste" type="checkbox" />
			</td>
			<td><label for="dream_taste">Meal Prep Workshop / Fundraiser</label></td>
		</tr>
<!--	hidden per Brandy on 3/32/2023	<tr>-->
<!--			<td>-->
<!--				<input id="recipe_expert" name="recipe_expert" data-print_menu="recipe_expert" type="checkbox" />-->
<!--			</td>-->
<!--			<td><label for="recipe_expert">Recipe Expert</label></td>-->
<!--		</tr>-->
		<tr>
			<td colspan="2">
				<span id="submit_menus" class="btn btn-primary btn-sm disabled">Print</span>
			</td>
		</tr>
	</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>