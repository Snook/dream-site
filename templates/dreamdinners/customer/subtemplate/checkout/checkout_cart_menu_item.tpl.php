<div class="row my-4">
	<div class="col-9 text-left">
		<h3 class="font-size-small text-uppercase font-weight-semi-bold"><?php echo $this->curItem['display_title']; ?></h3>
		<div class="text-uppercase text-green-dark font-size-small">
			<div>
				<?php if (empty($this->curItem['is_bundle']) && $this->curItem['menu_item_category_id'] != 9) { ?>
					<?php echo $this->curItem['pricing_type_info']['pricing_type_name']; ?> serves <?php echo $this->curItem['pricing_type_info']['pricing_type_serves']; ?>
				<?php } else if (!empty($this->curItem['pricing_type_info']['pricing_type_serves_display'])) { ?>
					Serves <?php echo $this->curItem['pricing_type_info']['pricing_type_serves_display']; ?>
				<?php } ?>
				(Qty: <?php echo $this->curItem['qty']; ?> )
			</div>
			<?php if ($this->curItem['is_preassembled']) { ?>
				<div>Pre-Assembled</div>
			<?php } ?>
			<?php if ($this->should_allow_meal_customization) { ?>
				<?php if ($this->curItem['is_freezer_menu'] || ($this->curItem['is_preassembled'] && !$this->allow_preassembled_customization)) { ?>
					<div>Not Customizable</div>
				<?php } ?>
			<?php } ?>
		</div>

		<?php if (!empty($this->curItem['subItems'])) { ?>
			<ul class="mt-3 font-size-small">
				<?php foreach ($this->curItem['subItems'] as $id => $itemData) { ?>
					<li>
						<?php echo $itemData['display_title']; ?>
						<div class="ml-2 text-uppercase text-green-dark">
							<?php if (!empty($itemData['pricing_type_info']['pricing_type_serves_display'])) { ?>
								<?php if ($itemData['menu_item_category_id'] != 9) { ?>
									<?php echo $itemData['pricing_type_info']['pricing_type_name']; ?> serves <?php echo $itemData['pricing_type_info']['pricing_type_serves']; ?>
								<?php } else if (!empty($itemData['pricing_type_info']['pricing_type_serves_display'])) { ?>
									Serves <?php echo $itemData['pricing_type_info']['pricing_type_serves_display']; ?>
								<?php } ?>
								(Qty: <?php echo $itemData['qty']; ?> )
							<?php } ?>
							<?php if ($itemData['is_preassembled']) { ?>
								<div>Pre-Assembled</div>
							<?php } ?>
							<?php if ($this->should_allow_meal_customization) { ?>
								<?php if ($itemData['is_freezer_menu'] || ($itemData['is_preassembled'] && !$this->allow_preassembled_customization)) { ?>
									<div>Not Customizable</div>
								<?php } ?>
							<?php } ?>
						</div>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>
	</div>
	<?php if ($this->cart_info['show_price_and_servings']) { ?>
		<div class="col-3 text-right">
			<p class="font-weight-bold">$<?php echo CTemplate::moneyFormat($this->curItem['price']); ?></p>
		</div>
	<?php } ?>
</div>