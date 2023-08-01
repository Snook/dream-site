
	<div class="row" id="row-giftcard-<?php echo $this->paymentID;?>" data-gc_number="<?php echo $this->paymentInfo['tempData']['card_number'];?>" data-gc_amount="<?php echo $this->paymentInfo['amount']?>">
		<div class="col-md-6 col-8 text-left">
			<p>
				<?php if ($this->isEditDeliveredOrder && !empty($this->paymentInfo['tempData']['card_number'])) { ?>
				<i id="remove-giftcard-<?php echo $this->paymentID;?>" class="fas fa-trash-alt mr-2"></i>
				<?php } else if(!$this->isEditDeliveredOrder){?>
					<i id="remove-giftcard-<?php echo $this->paymentID;?>" class="fas fa-trash-alt mr-2"></i>
				<?php } ?>
				<span id="checkout_title-giftcard-<?php echo $this->paymentID;?>">Gift Card: <?php echo CTemplate::obfuscateCardNumber($this->paymentInfo['tempData']['card_number']);?></span>
			</p>

		</div>
		<div class="col-md-6 col-4 text-right">
			<span class="value"><p>-$<span style="width:100%" class="text-right" id="checkout_total_payment-giftcard-<?php echo $this->paymentID;?>"><?php echo CTemplate::moneyFormat( $this->paymentInfo['amount']);?></span></p></span>
		</div>
	</div>
