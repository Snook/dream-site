<div class="row mb-4">
	<div class="col-md-12">
		<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left mb-4">Delivery Address</h2>

		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_firstname_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_lastname_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_address_line1_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_address_line2_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<?php echo $this->form_direct_order['shipping_city_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_state_id_html']; ?>
			</div>
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_postal_code_html']; ?>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<?php echo $this->form_direct_order['shipping_phone_number_html']; ?>
			</div>
			<div class="form-group col-md-6 collapse shipping_phone_number_new_div">
				<?php echo $this->form_direct_order['shipping_phone_number_new_html']; ?>
			</div>
			<div class="form-group col">
				<span class="text-muted font-size-small">*Please provide the mobile number of the person who will be home during the delivery window. The delivery driver will contact them when they are on the way.</span>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-12">
				<h3 class="font-size-small text-uppercase font-weight-semi-bold">Delivery instructions</h3>
				<?php echo $this->form_direct_order['shipping_address_note_html']; ?>
			</div>
		</div>
	</div>
</div>