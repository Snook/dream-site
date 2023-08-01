				<?php
				$hasGiftCardPaymentRow = false;
				if ($this->has_gift_card) {
						$hasGiftCardPaymentRow = true;
					?>

						<div id="row-creditcard-<?php echo $this->paymentID;?>">
							<img id="remove-creditcard-<?php echo $this->paymentID;?>" src="<?php echo IMAGES_PATH; ?>/icon/delete.png" alt="Remove" class="img_valign" data-tooltip="Remove Payment" />
							 <span id="gc_checkout_title-creditcard-<?php echo $this->paymentID;?>"><?php echo $this->paymentInfo['tempData']['card_type']?>
							 (<?php echo CTemplate::lastFourCardNumber($this->paymentInfo['tempData']['card_number']);?>) for Gift Card</span>
							<span class="value">(<span id="gc_checkout_total_adj_payment-creditcard-<?php echo $this->paymentID;?>"></span>)</span>
						</div>
						<div class="clear"></div>
				<?php } ?>

				<?php if ($this->foodState == 'adequateFood') { ?>
						<div id="row-creditcard-<?php echo $this->paymentID;?>">
						<?php if (!$hasGiftCardPaymentRow) {?>
							<img id="remove-creditcard-<?php echo $this->paymentID;?>" src="<?php echo IMAGES_PATH; ?>/icon/delete.png" alt="Remove" class="img_valign" data-tooltip="Remove Payment" />
						<?php } else { ?>
							<div style="width:20px; height:16px; float:left;">&nbsp;</div>
						<?php }  ?>

							 <span id="checkout_title-creditcard-<?php echo $this->paymentID;?>"><?php echo $this->paymentInfo['tempData']['card_type']?>
							 (<?php echo CTemplate::lastFourCardNumber($this->paymentInfo['tempData']['card_number']);?>)
							 <?php if ($hasGiftCardPaymentRow) { ?> for Dinners<?php } ?>
							 </span>
							<span class="value">(<span id="checkout_total_adj_payment-creditcard-<?php echo $this->paymentID;?>"></span>)</span>
						</div>
						<div class="clear"></div>
				<?php } ?>


