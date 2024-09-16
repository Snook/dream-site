<div>
	<table><tr>
			<td style="text-align:center; white-space: nowrap; overflow: hidden;"><h3 style="width:800px;"><?php echo $this->title; ?></h3></td>
			<td><span id="export_purchasers" class="btn btn-primary btn-sm" style="float:right; margin-right:5px;">export</span></td></tr></table>
</div>



<?php if ( isset($this->guestData) ) { ?>
	<table  style="width:100%;">
		<thead style="display:block; width:100%;">
		<tr>
			<?php if ($this->showStore) { ?>

				<th style="width:50px; text-align:center;">HOID</th>
				<th style="width:100px; text-align:left;">Store Name</th>
				<th style="width:90px; text-align:left;">Store City</th>
				<th style="width:35px; text-align:center;">State</th>

			<?php } ?>

			<th style="width:240px; text-align:left;">Guest Name</th>
			<th style="width:200px; text-align:left;">Email</th>

			<?php if (!$this->showStore) { ?>

				<th style="width:100px; text-align:center;">Telephone 1</th>
				<th style="width:80px; text-align:center;">Telephone 1 type</th>
				<th style="width:90px; text-align:center;">Telephone 1 Call Time</th>

			<?php } ?>

			<th style="width:170px; text-align:center;">Last Session</th>
			<th style="width:60px; text-align:center;"># Lrg</th>
			<th style="width:60px; text-align:center;"># Md(3)</th>
			<th style="width:60px; text-align:center;"># Md(4)</th>
			<th style="width:60px; text-align:center;"># Sm</th>

		</tr>
		</thead>
		<tbody style="height:434px; overflow-y:auto;  display:block; width:100%;">

		<?php
		$counter = 1;
		foreach ( $this->guestData as $guest )
		{ ?>
			<tr class="bgcolor_<?php echo ($counter++ % 2 == 0) ? 'light' : 'lighter'; ?>">

				<?php if ($this->showStore) { ?>

					<td style="width:50px; text-align:left;"><?php echo $guest['home_office_id'];?></td>
					<td style="width:100px; text-align:center;"><?php echo $guest['store_name'];?></td>
					<td style="width:90px; text-align:center;"><?php echo $guest['store_city'];?></td>
					<td style="width:35px; text-align:center;"><?php echo $guest['store_state'];?></td>


				<?php } ?>


				<td style="width:240px; max-width:240px; text-align:left;"><a href="/backoffice/user-details?id=<?php echo $guest['user_id'];?>" target="_blank"><?php echo $guest['firstname'];?> <?php echo $guest['lastname'];?></a></td>
				<td style="width:200px; text-align:left;"><?php echo $guest['email'];?></td>


				<?php if (!$this->showStore) { ?>
					<td style="width:100px; text-align:center;"><?php echo $guest['telephone_1'];?></td>
					<td style="width:80px; text-align:center;"><?php echo $guest['telephone_1_type'];?></td>
					<td style="width:90px; text-align:center;"><?php echo $guest['telephone_1_call_time'];?></td>

				<?php } ?>

				<?php
				$all_sessions = explode(",", $guest['sessions']);
				$all_session_ids = explode(",", $guest['session_ids']);
				$all_order_ids = explode(",", $guest['order_ids']);

				$last_session = $all_sessions[0];
				$sessionsStarts = "";
				$sessionIDs = "";
				$orderIDs = "";


				if (count($all_sessions) > 1)
				{
					$sessionsStarts = 'data-session_starts="' . implode(",", $all_sessions) . '"';
					$sessionsIDs = 'data-session_ids="' . implode(",", $all_session_ids) . '"';
					$orderIDs = 'data-order_ids="' . implode(",", $all_order_ids) . '"';

				}
				else
				{
					$sessionsStarts = 'data-session_starts="' . implode(",", $all_sessions) . '"';
					$sessionsIDs = 'data-session_ids="' . implode(",", $all_session_ids) . '"';
					$orderIDs = 'data-order_ids="' . implode(",", $all_order_ids) . '"';

				}
				?>

				<td id="suser_<?php echo $guest['user_id'];?>" <?php echo $sessionsStarts . " " . $sessionsIDs . " " . $orderIDs; ?> style="width:170px; text-align:center;"><a href="javascript:void(0);"><?php echo CTemplate::dateTimeFormat($last_session);?></a></td>


				<td style="width:60px; text-align:center;"><?php echo $guest['full_num_ordered'];?></td>
				<td style="width:60px; text-align:center;"><?php echo $guest['half_num_ordered'];?></td>
				<td style="width:60px; text-align:center;"><?php echo $guest['four_num_ordered'];?></td>
				<td style="width:60px; text-align:center;"><?php echo $guest['two_num_ordered'];?></td>

			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<table  style="width:100%;">
		<tr align="center" class="form_subtitle_cell">
			<td colspan="4">
				<h3><?php echo $this->title; ?></h3>
			</td>
		</tr>
		<tr align="center" class="form_subtitle_cell">
			<td colspan="4" style="font-style: italic">
				There were no guests purchasing the selected items at sessions in the specified time range.
			</td>
		</tr>
	</table>
<?php } ?>