<div id="" class="row">
	<div class="col-6 border border-primary m-0">
			<div>
				<h5 class="text-center">Shipping Options</h5>
			</div>

			<?php  if (!empty($this->menuInfo['box']))
			{
				foreach($this->menuInfo['box'] as $thisBox)
				{ ?>
					<?php echo "<h3>" . $thisBox->title . "</h3>";
					// TODO:  out of stock
					if (!empty($thisBox->box_bundle_1))
					{
						if (isset($thisBox->bundle1_out_of_stock) && $thisBox->bundle1_out_of_stock)
						{
							echo "<ul class='list-group mb-1 rounded-lg'>";
							echo "<li class='list-group-item pl-1 active' >" . $thisBox->bundle[$thisBox->box_bundle_1]->bundle_name . " <span data-toggle=\"collapse\" data-target=\".bit_" . $thisBox->box_bundle_1 . "\"  aria-expanded=\"true\" aria-controls=\".bil_" . $thisBox->box_bundle_1 . "\" class='cursor-pointer' >(<u>show/hide items</u>)</span>" . "&nbsp;<span class='float-right'>Out of Stock</span></li>";
							foreach ($this->menuInfo['bundle_items'][$thisBox->box_bundle_1] as $id => $itemData)
							{
								echo "<li class=\"list-group-item p-1 collapse bit_" . $thisBox->box_bundle_1 . " \">" . $itemData['display_title'] . "</li>";
							}
							echo "</ul>";
						}
						else
						{
							echo "<ul class='list-group mb-1 rounded-lg'>";
							echo "<li class='list-group-item pl-1 active' >" . $thisBox->bundle[$thisBox->box_bundle_1]->bundle_name . " <span data-toggle=\"collapse\" data-target=\".bit_" . $thisBox->box_bundle_1 . "\"  aria-expanded=\"true\" aria-controls=\".bil_" . $thisBox->box_bundle_1 . "\" class='cursor-pointer' >(<u>show/hide items</u>)</span>" . "&nbsp;<span class='button add_box float-right' 
									data-box_label='" . $thisBox->title . " (" . $thisBox->bundle[$thisBox->box_bundle_1]->bundle_name . ")' data-box_type='" . $thisBox->box_type . "' data-box_id='" . $thisBox->id . "' data-bundle_id='" . $thisBox->box_bundle_1 . "' id='ba_" . $thisBox->box_bundle_1 . "'>Add</span></li>";
							foreach ($this->menuInfo['bundle_items'][$thisBox->box_bundle_1] as $id => $itemData)
							{
								echo "<li class=\"list-group-item p-1 collapse bit_" . $thisBox->box_bundle_1 . " \">" . $itemData['display_title'] . "</li>";
							}
							echo "</ul>";
						}
					}
					if (!empty($thisBox->box_bundle_2))
					{
						if (isset($thisBox->bundle2_out_of_stock) && $thisBox->bundle2_out_of_stock)
						{
							echo "<ul class='list-group mb-1 rounded-lg'>";
							echo "<li class='list-group-item pl-1 active' >" . $thisBox->bundle[$thisBox->box_bundle_2]->bundle_name . " <span data-toggle=\"collapse\" data-target=\".bit_" . $thisBox->box_bundle_2 . "\"  aria-expanded=\"true\" aria-controls=\".bil_" . $thisBox->box_bundle_2 . "\" class='cursor-pointer' >(<u>show/hide items</u>)</span>" . "&nbsp;<span class='float-right'>Out of Stock</span></li>";
							foreach ($this->menuInfo['bundle_items'][$thisBox->box_bundle_2] as $id => $itemData)
							{
								echo "<li class=\"list-group-item p-1 collapse bit_" . $thisBox->box_bundle_2 . " \">" . $itemData['display_title'] . "</li>";
							}
							echo "</ul>";
						}
						else
						{
							echo "<ul class='list-group mb-1 rounded-lg'>";
							echo "<li class='list-group-item pl-1 active' >" . $thisBox->bundle[$thisBox->box_bundle_2]->bundle_name . " <span data-toggle=\"collapse\" data-target=\".bit_" . $thisBox->box_bundle_2 . "\"  aria-expanded=\"true\" aria-controls=\".bil_" . $thisBox->box_bundle_2 . "\" class='cursor-pointer' >(<u>show/hide items</u>)</span>" . "&nbsp;<span class='button add_box float-right' 
										data-box_label='" . $thisBox->title . " (" . $thisBox->bundle[$thisBox->box_bundle_2]->bundle_name . ")' data-box_type='" . $thisBox->box_type . "' data-box_id='" . $thisBox->id . "' data-bundle_id='" . $thisBox->box_bundle_2 . "' id='ba_" . $thisBox->box_bundle_2 . "'>Add</span></li>";
							foreach ($this->menuInfo['bundle_items'][$thisBox->box_bundle_2] as $id => $itemData)
							{
								echo "<li class=\"list-group-item p-1 collapse bit_" . $thisBox->box_bundle_2 . " \">" . $itemData['display_title'] . "</li>";
							}
							echo "</ul>";
						}
					}
					?>
			<?php }} ?>
	</div>

	<div id="box_editor"  class="col-6 border border-primary m-0">
			<h5 class="text-center">Current Selections</h5>
		<?php if (!empty($this->current_boxes))
			{
				foreach($this->current_boxes as $box_inst_id => $boxContents)
				{
					$this->assign('bundle_data', $boxContents['bundle_data']);
					$this->assign('bundle_items', $boxContents['bundle_items']);
					$this->assign('box_inst_id', $box_inst_id);
					$this->assign('box_id',  $boxContents['box_id']);
					$this->assign('box_type',  $boxContents['box_type']);
					$this->assign('box_label', $boxContents['box_label'] . " (" . $boxContents['bundle_data']->bundle_name .  ")");
					$this->assign('is_saved', true);

					include $this->loadTemplate('admin/subtemplate/order_mgr/box_instance_for_editor.tpl.php');
				}
			}
			?>
	</div>
</div>

<?php if ($this->orderOrStoreSupportDelivery) { ?>
	<div class="row mt-2 collapse">
		<div class="offset-8 col-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">Delivery Fee $</div>
				</div>
				<?=$this->form_direct_order['subtotal_delivery_fee_html']?>
			</div>
		</div>
	</div>
<?php } ?>