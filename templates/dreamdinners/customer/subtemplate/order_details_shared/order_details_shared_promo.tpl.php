			<div class="order-details<?php echo ($counter == $lastItem) ? " last" : ''; ?>">

				<div class="pic"><img class="media_container" style="width:105px;height:78px;" src="<?php echo IMAGES_PATH; ?>/recipe/<?php echo (empty($this->menuInfo['menu_image_override'])) ? 'default' : $this->menuInfo['menu_image_override']; ?>/<?php echo $itemInfo['recipe_id']; ?>.webp" alt="<?php echo htmlspecialchars($itemInfo['display_title']); ?>" /></div>

				<div class="menu-item"><a href="/?page=item&amp;recipe=<?php echo $itemInfo['recipe_id']; ?>"><?php echo $itemInfo['display_title']; ?></a></div>

				<div class="price">$<?php echo $itemInfo['price']; ?></div>

				<div class="qty"><?php echo $itemInfo['qty']; ?></div>

				<div class="serving-size"><?php echo $itemInfo['servings_per_item']; ?> Servings</div>

				<div class="total-price">$<?php echo $itemInfo['subtotal']; ?></div>

			</div>