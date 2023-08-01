<?php if($this->isEditDeliveredOrder ){ ?>
	<?php $display = $this->delta_has_new_total ? '':'edit-order-field collapse';?>
	<div id="edit-order-total-container" class="row  <?php echo $display; ?>">
		<div class="col-md-7 col-7 text-left">
			<p  class="font-weight font-size-small mb-2">Original Payment</p>
		</div>
		<div class="col-md-5 col-5 text-right">
			<p class="font-weight font-size-small mb-2">-$<span id="original_order_total"><?php echo CTemplate::moneyFormat($this->delta_original_total);?></span></p>
		</div>
		<div class="col-md-7 col-7 text-left">
			<p  class="font-weight-bold font-size-medium-small text-red mb-2"><?php echo $this->delta_total_diff >=0 ? 'Balance Due':'Refund Due';?></p>
		</div>
		<div class="col-md-5 col-5 text-right">
			<p class="font-weight-bold font-size-medium-small border-top text-red mb-2">$<span id="cc_amount_diff" data-is-visible="<?php echo $display; ?>"><?php echo CTemplate::moneyFormat($this->delta_total_diff);?></span></p>
		</div>
	</div>
<?php }?>