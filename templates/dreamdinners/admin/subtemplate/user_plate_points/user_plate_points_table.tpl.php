<?php if (!empty($this->pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="button points-page-prev <?php echo ($this->pagination_prev) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="button points-page-next <?php echo ($this->pagination_next) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>
<?php if ($this->no_more_rows) { ?>
	<h3 style="padding-left:20px">No More Data</h3>
<?php } else{ ?>
	<table class="table table-sm table-striped text-center <?php if ($this->userObj->PlatePointsData["userIsOnHold"]) {echo 'text-muted'; } ?>">
		<tr>
			<th class="bgcolor_medium header_row">Type</th>
			<th class="bgcolor_medium header_row">Points Earned</th>
			<th class="bgcolor_medium header_row">Points Converted</th>
			<th class="bgcolor_medium header_row">Lifetime Points</th>
			<th class="bgcolor_medium header_row">Details</th>
			<th class="bgcolor_medium header_row">Event Time &#8595;</th>
		</tr>
		<?php
		function make_list($arr)
		{
			if (is_array($arr))
			{
				$return = '<ul style="list-style: none; margin: 0px 0px 0px -30px;">';

				foreach ($arr as $key => $item)
				{
					$return .= '<li>' . ucfirst(str_replace('_', ' ', $key)) . ': ' . (is_array($item) ? make_list($item) : $item) . '</li>';
				}

				$return .= '</ul>';

				return $return;
			}
		}

		foreach($this->rows as $thisRow) { ?>
			<tr>
				<td><?php echo $thisRow['event_title']; ?></td>
				<td><?php echo number_format($thisRow['points_allocated']); ?></td>
				<td><?php echo number_format($thisRow['points_converted']); ?></td>
				<td><?php echo number_format($thisRow['total_points']); ?></td>
				<td class="text-left"><?php echo make_list($thisRow['meta_array']); ?></td>
				<td><?php echo CTemplate::dateTimeFormat($thisRow['timestamp_created'], NORMAL); ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<?php if (!empty($this->pagination)) { ?>
	<div class="row pt-4">
		<div class="col" >
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="button points-page-prev <?php echo ($this->pagination_prev) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="button points-page-next <?php echo ($this->pagination_next) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>
