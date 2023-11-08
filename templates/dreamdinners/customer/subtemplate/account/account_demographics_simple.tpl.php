<div class="row mb-4">
	<div class="col">
		<div class="form-row">
			<div class="col-12 col-lg-12">
				<div class="form-row">
					<div class="form-group col">
						<?php if (!empty($this->hasReferralSource)) { ?>
							<?php echo $this->form_account['referral_source_html']; ?>
							<div id="referral_source_details_div" class="collapse"><?php echo $this->form_account['referral_source_details_html'];?> </div>
							<div id="virtual_party_source_details_div" class="collapse"><?php echo $this->form_account['virtual_party_source_details_html'];?></div>
							<div id="customer_referral_email_div" class="collapse"><?php echo $this->form_account['customer_referral_email_html'];?></div>
						<?php } else {  ?>
							<div class="form-row">
								<div class="form-group col-md-12 text-center">
									<div class="form-row">
										<div class="form-group col">
											<?php echo $this->form_account['referral_source_html']; ?>
											<div id="referral_source_details_div" class="collapse">
												<?php echo $this->form_account['referral_source_details_html'];?>
											</div>
											<div id="virtual_party_source_details_div" class="collapse">
												<?php echo $this->form_account['virtual_party_source_details_html'];?>
											</div>
											<div id="customer_referral_email_div" class="collapse">
												<div class="input-group">
													<?php echo $this->form_account['customer_referral_email_html'];?>
													<div class="input-group-append">
														<div id="customer_referral_result" class="input-group-text">
															@
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>