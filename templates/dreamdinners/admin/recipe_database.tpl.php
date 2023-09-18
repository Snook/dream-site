<?php $this->assign('page_title', 'Recipe Database'); ?>
<?php $this->assign('topnav', 'import'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<div class="row my-4">
			<div class="col text-center">
				<h1><a href="/?page=admin_recipe_database">Recipe Database</a></h1>
			</div>
		</div>

		<div class="row">
			<div class="col">
				<table class="table table-sm table-striped table-hover table-hover-cyan bg-white ddtemp-table-border-collapse">
					<thead>
					<tr class="text-center">
						<th>Recipe ID</th>
						<th>Recipe Title</th>
						<th>Latest Menu</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->recipeArray AS $DAO_recipe) { ?>
						<tr>
							<td class="text-right"><?php echo $DAO_recipe->recipe_id; ?></td>
							<td>
								<?php if (!empty($DAO_recipe->menu_name)) { ?>
									<a href="/item?recipe=<?php echo $DAO_recipe->recipe_id; ?>" target="_blank"><?php echo $DAO_recipe->recipe_name; ?></a>
								<?php } else { ?>
									<?php echo $DAO_recipe->recipe_name; ?>
								<?php } ?>
							</td>
							<td class="text-right"><?php echo $DAO_recipe->menu_name; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>