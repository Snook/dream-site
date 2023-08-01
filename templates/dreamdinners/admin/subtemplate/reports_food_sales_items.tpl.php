<?php if ( isset($this->itemData) ) { ?>
	<div>
		<table style="width:100%;">
			<thead style="display:block; width:100%;">
			<tr>
				<th style="width:80px">Operation</th>
				<th style="width:50px;">ID</th>
				<th style="width:300px;">Name</th>
				<th style="width:100px;">Category</th>
				<th style="width:100px;">Number Sold (Lrg/Md(3)/Md(4)/Sm)</th>
			</tr>
			</thead>
			<tbody style="height:345px; overflow-y:auto;  display:block; width:100%;">

			<?php
			$counter = 1;
			foreach ( $this->itemData as $item )
			{ ?>
				<tr class="bgcolor_<?php echo ($counter++ % 2 == 0) ? 'light' : 'lighter'; ?>">
					<td style="width:80px; text-align:center;">
						<span class="button" style="font-size:69% !important;font-weight:normal !important;" id="rid_<?php echo $item['recipe_id'];?>">List</span>
						<span class="button"style="font-size:69% !important;font-weight:normal !important;" data-entree_id="<?php echo $item['entree_id'];?>"  data-menu_id="<?php echo $item['menu_id'];?>"  id="rmid_<?php echo $item['recipe_id'];?>">Details</span>
					</td>
					<td style="width:50px; text-align:center;"><?php echo $item['recipe_id'];?></td>
					<td style="width:300px; text-align:left;"><?php echo $item['name'];?></td>
					<td style="width:100px; text-align:left;"><?php echo $item['category'];?></td>
					<td style="width:100px;text-align:center;"><?php echo (!empty($item['num_sold']) ? $item['num_sold'] : "-");?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
<?php } else { ?>
	<table style="width:100%;">
		<tr align="center" class="form_subtitle_cell">
			<td colspan="4">
				<h3><?php echo $this->title; ?></h3>
			</td>
		</tr>
		<tr align="center" class="form_subtitle_cell">
			<td colspan="4" style="font-style: italic">
				There were items sold at sessions in the specified time range.
			</td>
		</tr>
	</table>
<?php } ?>
