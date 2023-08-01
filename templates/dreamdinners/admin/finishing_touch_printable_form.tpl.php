<?php $this->assign('page_title', 'Sides & Sweets Printable Form'); ?>
<?php $this->assign('print_view', true); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<table style="width:100%;">
		<tr>
			<td><img src="<?php echo IMAGES_PATH?>/admin/DD-Finishing-Touch.png" style="width:300px;" /></td>
			<td style="text-align:right;vertical-align:bottom;">
				<table style="float:right;">
					<tr>
						<td style="white-space:nowrap;">Guest Name:</td>
						<td>______________________________</td>
					</tr>
					<tr>
						<td colspan="2" style="white-space:nowrap;padding-top:4px;">
							<div>Okay to use card on file:<input type="checkbox" /></div>
							<div>Use available Dinner Dollars:<input type="checkbox" /></div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td style="vertical-align:top;">

				<table style="float:left;border-collapse: collapse;">
					<?php

					$all = array();

					$allowedTitleLength = 86;
					$columnCutOff = 41;


					// preflight to get count
					$numDisplayedItems = 0;
					$lastSubcategory = "nothing";

					foreach ($this->FT_Items as $id => $data )
					{
						$amountRemaining = $data['override_inventory'] -  $data['number_sold'];

						if ($amountRemaining > 0 and !$data['is_hidden_everywhere'])
						{

							if ($data['subcategory_label'] != $lastSubcategory)
							{
								$numDisplayedItems++;
								$lastSubcategory = $data['subcategory_label'];

								$all[] = $data['subcategory_label'];

							}


							if ($data['is_store_special'])
							{
								if (!$data['show_on_pick_sheet'])
								{
									continue;
								}
							}

							$numDisplayedItems++;

							$all[] = $data['menu_item_name'];
						}



					}

					if ($numDisplayedItems > $columnCutOff )
					{
						$allowedTitleLength = 32;
					}

					$lastSubcategory = "nothing";
					$count = 0;
					$has_split = false;

					foreach ($this->FT_Items as $id => $data )
					{
					$amountRemaining = $data['override_inventory'] -  $data['number_sold'];

					if ($amountRemaining > 0 && (($data['is_store_special'] && !$data['is_hidden_everywhere']) || (!$data['is_store_special'] && $data['show_on_order_form'])))
					{
					$printSizeStr = "";

					if ($data['is_store_special'])
					{
						if ($data['show_on_pick_sheet'])
						{
							$sizeStr = "Lrg";
							if ($data['servings_per_item'] < 4)
							{
								$sizeStr = "Med";
							}

							$printSizeStr = "(" .  $sizeStr . ") ";
						}
						else
						{
							continue;
						}
					}
					?>
					<?php if ($count >= $columnCutOff && !$has_split) {

					$has_split = true; ?>
				</table>

			</td>
			<td style="vertical-align:top;">

				<table style="float:left;">
					<?php } ?>
					<?php
					$count++;

					if ($data['subcategory_label'] != $lastSubcategory)
					{
						$count++;

						$lastSubcategory = $data['subcategory_label'];
						?>
						<tr>
							<th>Qty</th>
							<th style="white-space:nowrap;width:50px;">Price Each</th>
							<th style="white-space:nowrap;padding-left:40px;text-align:left;"><?php echo $data['subcategory_label']; ?></th>
						</tr>
					<?php } ?>
					<tr>
						<td style="white-space:nowrap;height:15px;">____</td>
						<td style="padding:0 10px;vertical-align:top;">$<?php echo $data['price']; ?></td>

						<?php // ensure html entities are not cutoff

						$overrideAllowedLength = $allowedTitleLength;
						$decodedStr = html_entity_decode($data['menu_item_name'], ENT_COMPAT | ENT_HTML401, "UTF-8");

						if (strlen($decodedStr) < strlen($data['menu_item_name']))
						{
							$overrideAllowedLength += (strlen($data['menu_item_name']) - strlen($decodedStr));
						}

						?>



						<td style="white-space:nowrap;vertical-align:top;"><?php echo $printSizeStr . ucwords(strtolower(CAppUtil::truncate($data['menu_item_name'], $overrideAllowedLength)) ); ?></td>
					</tr>
					<?php } } ?>

				</table>

			</td>
		</tr>
	</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>