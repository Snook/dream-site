<div class="row bg-cyan-extra-light">
	<div class="col pt-3">
		<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left">Dinner Dollars</h2>
	</div>
</div>
<div class="row mb-4 bg-cyan-extra-light">
	<div class="col-sm-5">
		<p>Your available Dinner Dollars <b>$<span id="plate_points_available"><?php echo CTemplate::moneyFormat($this->maxPPCredit); ?></span></b></p>
		<?php if ($this->maxPPDeduction < $this->maxPPCredit) { ?>
			<p>Max Dinner Dollars allowed this order $<span id="max_plate_points_deduction"><?php echo CTemplate::moneyFormat($this->maxPPDeduction); ?></span></p>
		<?php } ?>
	</div>
	<div class="col-sm-7">
		<div class="input-group mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text">$</span>
			</div>
			<?php echo $this->form_payment['plate_points_discount_html']; ?>
			<div class="input-group-append">
				&nbsp;<button id="apply_plate_points" class="btn btn-primary btn-sm">Apply</button>
				&nbsp;<button id="apply_all_plate_points" class="btn btn-primary btn-sm">Apply Max Allowed</button>
			</div>
		</div>
	</div>
</div>