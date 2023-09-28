<?php $this->assign('page_title', 'Menu Inspector'); ?>
<?php $this->assign('topnav', 'import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<form name="menu_editor_form" id="menu_editor_form" method="get">
		<table class="ME_menu_editor_table" style="width: 100%;">
			<tr>
				<td style="padding-left: 10px;"><b>Selected Menu:</b><?php echo $this->form['menus_html']; ?></td>
			</tr>
		</table>

		<table class="table table-striped">
			<?php foreach ($this->menuInfo as $categoryName => $subArray) { ?>
				<?php if (is_array($subArray)) { ?>
					<tr>
						<th>Recipe ID</th>
						<th>Item ID</th>
						<th colspan="2"><?php echo $categoryName; ?></th>
						<th>HALF Price</th>
						<th>FULL Price</th>
					</tr>
					<?php foreach ($subArray as $id => $planNode) { ?>
						<tr>
							<td>
								<?php echo $planNode['recipe_id']; ?>
							</td>
							<td>
								<?php echo $id; ?>
							</td>
							<td>
								<img src="<?php echo IMAGES_PATH; ?>/recipe/default/<?php echo $planNode['recipe_id']; ?>.webp" style="width: 105px; height: 78px;"/>
							</td>
							<td>
								<div class="mb-2 font-weight-bold"><a href="/?page=item&amp;recipe=<?php echo $planNode['recipe_id']; ?>&amp;ov_menu=<?php echo $this->menuInfo['menu_id']; ?>" target="_blank"><?php echo $planNode['title']; ?></a></div>
								<div><?php echo $planNode['desc']; ?></div>
							</td>
							<td>
								<?php if (isset($planNode['HALF'])) { ?>
									<?php echo $planNode['HALF']['price']; ?>
								<?php } ?>
							</td>
							<td><?php echo $planNode['FULL']['price']; ?></td>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</table>

	</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>