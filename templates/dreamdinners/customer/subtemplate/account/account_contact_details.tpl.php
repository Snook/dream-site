<div class="form-row">
	<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">Contact Details</h2>
</div>
<div class="form-row">
	<div class="form-group col-md-6">
		<?php echo $this->form_account['telephone_1_html']; ?>
	</div>
	<div class="form-group col-md-6">
		<?php echo $this->form_account['telephone_1_call_time_html']; ?>
	</div>
</div>
<div class="form-row text-center text-md-left pl-2 mb-4">
	<div class="col">
		<div class="custom-control-inline">
			<?php echo $this->form_account['telephone_1_type_html']['MOBILE']; ?>
		</div>
		<div class="custom-control-inline">
			<?php echo $this->form_account['telephone_1_type_html']['LAND_LINE']; ?>
		</div>
    </div>
</div>
<?php if($this->hide_second_number){

}else{ ?>
	<div class="form-row">
		<div class="form-group col-md-6">
			<?php echo $this->form_account['telephone_2_html']; ?>
		</div>
		<div class="form-group col-md-6">
			<?php echo $this->form_account['telephone_2_call_time_html']; ?>
		</div>
	</div>
	<div class="form-row text-center text-md-left pl-2">
		<div class="col">
			<div class="custom-control-inline">
				<?php echo $this->form_account['telephone_2_type_html']['MOBILE']; ?>
			</div>
			<div class="custom-control-inline">
				<?php echo $this->form_account['telephone_2_type_html']['LAND_LINE']; ?>
			</div>
		</div>
	</div>
<?php } ?>