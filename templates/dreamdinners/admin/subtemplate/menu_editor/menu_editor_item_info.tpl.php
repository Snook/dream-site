<div class="container-fluid bg-white mt-3 pt-2">
	<div class="row mb-3">
		<div class="col-9">
			<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->display_description; ?>
		</div>
		<div class="col-3">
			<span class="btn btn-primary w-100" data-add_menu_item_info_close="<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>">Close info</span>
		</div>
	</div>

	<nav>
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="instructions-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab" data-toggle="tab" data-target="#instructions-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" type="button" role="tab" aria-controls="profile" aria-selected="false">Instructions</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="nutrition-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab" data-toggle="tab" data-target="#nutrition-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" type="button" role="tab" aria-controls="contact" aria-selected="false">Nutrition</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="sales-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab" data-toggle="tab" data-target="#sales-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" type="button" role="tab" aria-controls="home" aria-selected="true">Sales</button>
			</li>
		</ul>
	</nav>

	<div class="tab-content">
		<div class="tab-pane fade" id="sales-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" role="tabpanel" aria-labelledby="sales-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab">
			<table>
				<tbody>
				<tr>
					<th>Menu Month</th>
					<th>Items Sold<br />(Lrg/Md(3)/Md(4)/Sm)</th>
					<th>Large Price</th>
					<th>Medium (3) Price</th>
					<th>Medium (4) Price</th>
					<th>Small Price</th>
				</tr>
				<?php foreach($this->salesData as $month_id => $thisMonth) { ?>
					<tr>
						<td>
							<?php echo $thisMonth['month_name']; ?>
						</td>
						<td>
							<?php if ($thisMonth['sales']['num_sold'] > 0) {
								if (empty($thisMonth['sales']['num_lrg_sold'])) {$thisMonth['sales']['num_lrg_sold'] = '-'; }
								if (empty($thisMonth['sales']['num_med_sold'])) {$thisMonth['sales']['num_med_sold'] = '-'; }
								if (empty($thisMonth['sales']['num_four_sold'])) {$thisMonth['sales']['num_four_sold'] = '-'; }
								if (empty($thisMonth['sales']['num_two_sold'])) {$thisMonth['sales']['num_two_sold'] = '-'; }
								?>
								<?php echo $thisMonth['sales']['num_sold']; ?> (<?php echo $thisMonth['sales']['num_lrg_sold']; ?>/<?php echo $thisMonth['sales']['num_med_sold']; ?>/<?php echo $thisMonth['sales']['num_four_sold']; ?>/<?php echo $thisMonth['sales']['num_two_sold']; ?>)
							<?php } else { ?>
								0
							<?php  } ?>
						</td>
						<td>
							$<?php echo $thisMonth['sales']['price_full']; ?>
						</td>
						<td>
							$<?php echo $thisMonth['sales']['price_half']; ?>
						</td>
						<td>
							$<?php echo $thisMonth['sales']['price_four']; ?>
						</td>
						<td>
							$<?php echo $thisMonth['sales']['price_two']; ?>
						</td>
					</tr>

				<?php  }?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane fade show active" id="instructions-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" role="tabpanel" aria-labelledby="instructions-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab">
			<?php if (!empty($this->curItem['cooking_inst']['full'])) { ?>
				<?php if (!$this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->is_chef_touched) {?>
					<h4>Instructions - Large</h4> <?php } ?>
				<p><?php echo nl2br($this->curItem['cooking_inst']['full']); ?></p>
				<?php if (!empty($this->curItem['cooking_inst']['prep_time_full'])) { ?>
					<p><b>Prep time:</b> <?php echo $this->curItem['cooking_inst']['prep_time_full']; ?></p>
				<?php } } ?>
			<?php if (!empty($this->curItem['cooking_inst']['half']) && $this->curItem['cooking_inst']['half'] != 'N/A') { ?>
				<h4>Instructions - Medium</h4>
				<p><?php echo nl2br($this->curItem['cooking_inst']['half']); ?></p>
				<?php if (!empty($this->curItem['cooking_inst']['prep_time_half'])) { ?>
					<p><b>Prep time:</b> <?php echo $this->curItem['cooking_inst']['prep_time_half']; ?></p>
				<?php } } ?>
			<?php if (!empty($this->curItem['cooking_inst']['suggestions'])) { ?>
				<h4>Serving Suggestions</h4>
				<p><?php echo $this->curItem['cooking_inst']['suggestions']; ?></p>
			<?php } ?>
		</div>
		<div class="tab-pane fade" id="nutrition-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>" role="tabpanel" aria-labelledby="nutrition-<?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->recipe_id; ?>-tab">
			<?php foreach($this->curItem['nutrition_data'] as $thisCompNum => $thisComp) { ?>
				<div class="row">
					<div class="col-4">
						<h4><?php echo ucfirst($thisComp['info']['serving']); ?></h4>
						<?php if (!empty($thisComp['element']['Calories']['label'])) { ?><div><?php echo $thisComp['element']['Calories']['label']; ?>: <?php echo $thisComp['element']['Calories']['value']; ?></div><?php } ?>
						<?php if (!empty($thisComp['info']['notes'])) { ?><p><?php echo $thisComp['info']['notes']; ?></p><?php } ?>
					</div>
					<div class="col-8">
						<table class="table table-sm">
							<?php foreach ($thisComp['element'] as $nutriLabel => $nutriData) {
								if ($nutriLabel != 'Calories') { ?>
									<tr>
										<th scope="row"><?php echo $nutriData['display_label']; ?></th>
										<td class="text-right"><?php echo (!empty($nutriData['value'])) ? $nutriData['value'] : 0; ?><?php echo (!empty($nutriData['note_indicator'])) ? $nutriData['note_indicator'] : '&nbsp;'; ?></td>
									</tr>
								<?php } } ?>
						</table>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>