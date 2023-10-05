<?php if (!empty($this->dinner_dollar_pagination)) { ?>
	<div class="row">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm dollars-page-prev <?php echo ($this->dinner_dollar_pagination_prev) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->dinner_dollar_page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm dollars-page-next <?php echo ($this->dinner_dollar_pagination_next) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->dinner_dollar_page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php }
if ($this->dinner_dollar_no_more_rows) { ?>
	<h3 style="padding-left:20px">No More Data</h3>
<?php  } else{ ?>
<table id="avail_credits_tbl" width="100%" class="table-striped">
	<tr id="avail_credits_header_row">
		<td class="bgcolor_medium header_row">Dinner Dollar State</td>
		<td class="bgcolor_medium header_row">Awarded Date &#8595;</td>
		<td class="bgcolor_medium header_row">Consumed Date</td>
		<td class="bgcolor_medium header_row">Events</td>
		<td class="bgcolor_medium header_row">Expiration</td>
		<td class="bgcolor_medium header_row">Dinner Dollars</td>
	</tr>

		<?php
		if (isset($this->dd_history) && !empty($this->dd_history)) {
			foreach ($this->dd_history as $id => $row) {
				$consumedStr = '';
				if( $row['orders'] != ''){
					$consumedStr = 'Order ' . $row['orders'] . ',';
				}

				$eventStr = '';
				if( $row['unique_events'] != ''){
					$eventStr = ucwords(strtolower(str_replace('_', ' ', str_replace(',', ', ', $row['unique_events'])))).',';
				}
				$out = $consumedStr .$eventStr;
				$out = rtrim($out, ",");
				?>
				<tr>
					<td><?php echo $row['state']; ?></td>
					<td nowrap><?php echo CTemplate::dateTimeFormat($row['timestamp'], NORMAL, $this->store_id, CONCISE);?></td>
					<td nowrap><?php echo ($row['state'] != 'AVAILABLE' && $row['state'] != 'EXPIRED') ? CTemplate::dateTimeFormat($row['timestamp_updated'], NORMAL, $this->store_id, CONCISE) : ' '; ?></td>
					<td style="font-size: .55rem;"><?php echo ($row['state'] != 'EXPIRED') ? $out : ' '; ?></td>
					<td nowrap><?php echo CTemplate::dateTimeFormat($row['expires'], NORMAL, false, CONCISE); ?></td>
					<td><?php echo $row['amount']; ?></td>
				</tr>
			<?php } } else { ?>
			<tr>
				<td class="bgcolor_light" colspan="5" style="text-align:center;"><i>There are no Dinner Dollars for this customer.</i></td></tr>

		<?php } ?>
	<?php } ?>
</table>

<?php if (!empty($this->dinner_dollar_pagination)) { ?>
	<div class="row pt-4">
		<div class="col">
			<nav aria-label="Page navigation" style="float:right;">
				<ul class="pagination">
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm dollars-page-prev <?php echo ($this->dinner_dollar_pagination_prev) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->dinner_dollar_page_cur?>" data-user="<?php echo $this->user_id;?>">&#60;&#60; Previous Page</a></li>
					<li class="page-item" style="padding:5px;"><a class="btn btn-primary btn-sm dollars-page-next <?php echo ($this->dinner_dollar_pagination_next) ? ' ' : 'disabled' ?>" href="#"  data-current="<?php echo $this->dinner_dollar_page_cur?>" data-user="<?php echo $this->user_id;?>">Next Page &#62;&#62;</a></li>
				</ul>
			</nav>
		</div>
	</div>
<?php } ?>