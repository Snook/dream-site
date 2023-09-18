
<div>
	<h3><?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->menu_item_name; ?></h3>
	<p><?php echo $this->curItem[$this->curItem['entree_id']][$this->curItem['entree_id']]->display_description; ?></p>
</div>

<div class="tabbed-content" data-tabid="menu">
	<div class="tabs-container">
		<ul class="tabs">
			<!-- data-tabid can be any string, required on all elements -->
			<li data-tabid="instructions" class="tab selected" data-urlpush="">Cooking Instructions</li>
			<li data-tabid="nutrition" class="tab" data-urlpush="">Nutritional Data</li>
			<li data-tabid="sales" class="tab"  data-urlpush="">Sales Data</li>
			<!-- 	<li data-tabid="recipe_card" class="tab"  data-urlpush="">Recipe Card</li> -->
		</ul>
	</div>

	<div class="tabs-content">

		<div data-tabid="instructions">
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

		<div data-tabid="nutrition">

			<div style="width:500px;">

				<?php foreach($this->curItem['nutrition_data'] as $thisCompNum => $thisComp) { ?>

					<div style="width:250px;float:left;">
						<h4><?php echo ucfirst($thisComp['info']['serving']); ?></h4>
						<?php if (!empty($thisComp['element']['Calories']['label'])) { ?><div><?php echo $thisComp['element']['Calories']['label']; ?>: <?php echo $thisComp['element']['Calories']['value']; ?></div><?php } ?>
						<?php if (!empty($thisComp['info']['notes'])) { ?><p style="margin-top:20px;"><?php echo $thisComp['info']['notes']; ?></p><?php } ?>
					</div>

					<div style="width:200px;float:right;margin-bottom:20px;">
						<div style="border-bottom:2px solid #512b1b;text-align:right;">Amount / Serving</div>
						<table>
							<?php foreach ($thisComp['element'] as $nutriLabel => $nutriData) {
								if ($nutriLabel != 'Calories') { ?>
									<tr>
										<th scope="row"><?php echo $nutriData['display_label']; ?></th>
										<td class="text-right"><?php echo (!empty($nutriData['value'])) ? $nutriData['value'] : 0; ?><?php echo (!empty($nutriData['note_indicator'])) ? $nutriData['note_indicator'] : '&nbsp;'; ?></td>
									</tr>
								<?php } } ?>
						</table>
					</div>

					<div class="clear"></div>

				<?php } ?>

			</div>

		</div>

		<div data-tabid="sales">
			<table>
				<tbody>
				<tr>
					<th>Menu Month</th>
					<th style="width:100px; text-align:left;">Items Sold<br />(Lrg/Md(3)/Md(4)/Sm)</th>
					<th style="width:100px; text-align:left;">Large Price</th>
					<th style="width:100px; text-align:left;">Medium (3) Price</th>
					<th style="width:100px; text-align:left;">Medium (4) Price</th>
					<th style="width:100px; text-align:left;">Small Price</th>
				</tr>
				<?php foreach($this->salesData as $month_id => $thisMonth) { ?>
					<tr>
						<td style="width:250px;">
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


		<?php if (false) {
			// Defeated for now until we have the food dev data
			?>
			<div data-tabid="recipe_card">
				<table>
					<tbody>
					<tr>
						<th>Description</th>
						<th>Quantity</th>
						<th>UOM</th>
					</tr>
					<?php foreach($this->cardData as $id => $thisLine) { ?>
						<tr>
							<td style="width:250px;">
								<?php echo $thisLine['Desc']; ?>
							</td>
							<td>
								<?php echo $thisLine['Qty']; ?>
							</td>
							<td>
								<?php echo $thisLine['UOM']; ?>
							</td>
						</tr>

					<?php  }?>
					</tbody>
				</table>
			</div>
		<?php } ?>


	</div>