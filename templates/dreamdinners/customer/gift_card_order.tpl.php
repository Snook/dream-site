<?php $this->setScript('foot', SCRIPT_PATH . '/customer/gift_card_order.min.js'); ?>
<?php $this->setScriptVar("is_edit = " . (($this->edit) ? 'true' : 'false') . ";"); ?>
<?php $this->setScriptVar("is_error = " . (($this->hadError) ? 'true' : 'false') . ";"); ?>
<?php $this->setScriptVar("currentMediaType = '" . (($this->selectedMediaType) ? $this->selectedMediaType : 'none') . "';"); ?>
<?php $this->setScriptVar("currentDesignType = '" . (($this->selectedDesignID) ? $this->selectedDesignID : 'none') . "';"); ?>
<?php $this->setScriptVar('card_designs = ' . $this->card_designjs . ';'); ?>
<?php $this->assign('page_title','Order a Dream Dinners Gift Card'); ?>
<?php $this->assign('page_description','Select from our traditional gift cards and electronic gift cards.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Buy a gift card</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
				<a href="/gift-card-cart" class="btn btn-primary btn-sm btn-md-lg p-2 <?php if (empty($this->cart_info['cart_info_array']['has_gift_cards']))  { ?> disabled<?php } ?>">View cart <span class="pl-2">&#10095;</span></a>
			</div>
		</div>
	</header>

	<main class="container">
		<form id="add_gc_form" method="post" class="needs-validation" novalidate>

			<input type="hidden" id="media_type" name="media_type" value="" />
			<input type="hidden" id="design_id" name="design_id" value="" />
			<?php if ($this->edit) { ?>
				<input type="hidden" id="gc_edit_id" name="gc_edit_id" value="<?php echo $_POST['gc_edit_id']?>" />
			<?php } ?>

			<!-- Media type Selection -->
			<div id="media_type_div_expanded">
				<div class="card-group justify-content-around">

					<?php if ($this->physical_cards_enabled && !empty($this->card_designs['info']['num_physical'])) { ?>
						<div class="card p-0 mb-4 <?php if (!empty($this->card_designs['info']['num_virtual'])) { ?>col-md-4 offset-md-1<?php } else { ?>col-md-4 offset-md-4<?php } ?>" id="phys_div">
							<img class="card-img-top <?php if (!empty($this->card_designs['info']['num_physical'])) { ?>choose_media<?php } ?>" id="physical_card_img" src="<?php echo IMAGES_PATH?>/gift_cards/traditional-gift-card-500x317.png" alt="Traditional gift card">
							<div class="card-body">
								<h5 class="card-title">Traditional Gift Card</h5>
								<p class="card-text">Delivered via standard mail in 2-6 business days $2 S&amp;H fee added to each Gift Card shipped.</p>
							</div>
							<div class="card-footer p-0">
								<button id="physical_card" class="btn btn-primary btn-block choose_media">Select</button>
							</div>
						</div>
					<?php } ?>

					<?php if ($this->physical_cards_enabled && !empty($this->card_designs['info']['num_physical']) && !empty($this->card_designs['info']['num_virtual'])) { ?>
						<div class="col-md-2 text-center">
							<h2 class="mt-md-6 pt-md-5">Or</h2>
						</div>
					<?php } ?>

					<?php if (!empty($this->card_designs['info']['num_virtual'])) { ?>
						<div class="card p-0 mb-4 <?php if (!empty($this->card_designs['info']['num_physical'])) { ?>col-md-4<?php } else { ?>col-md-4 offset-md-4<?php } ?>" id="virt_div">
							<img class="card-img-top <?php if (!empty($this->card_designs['info']['num_virtual'])) { ?>choose_media<?php } ?>" id="virtual_card_img" src="<?php echo IMAGES_PATH?>/gift_cards/egift-card-dd-logo-500x317.png" alt="Virtual eGift Card">
							<div class="card-body">
								<h5 class="card-title">Virtual eGift Card</h5>
								<p class="card-text">Delivered instantly via email.</p>
							</div>

							<div class="card-footer p-0">
								<button id="virtual_card" class="btn btn-primary btn-block choose_media">Select</button>
							</div>
						</div>
					<?php } ?>

				</div>
			</div>

			<div id="media_type_div_collapsed" class="collapse">
				<p>Selected card type: <span id="selected_media_desc" class="font-weight-bold"></span> <span class="btn btn-sm btn-primary modify_media">Modify</span></p>
			</div>

			<?php if (defined('USE_STS_STORE') && USE_STS_STORE)  {?>
				<div id="phys_design_type_div_expanded" class="collapse">
					<iframe id='emb_lgc_store' src='https://dreamdinners.localgiftcards.com/?emb_tpl=1&tpl_orr=ffffff-1a1716-00804e-21b78e-dce1c3-b6aa8a-1a1716-cfd7da-3c534f-008999-e2e1c1-5f5b45-d8e588-a69666-e6d273-bb9120-c8cda6-8c9568-d2da9b-96a559-d3dd91-aba471-ede483-b8aa9d-b65' data-emb-tpl='1' data-emb-tpl-orr='ffffff-1a1716-00804e-21b78e-dce1c3-b6aa8a-1a1716-cfd7da-3c534f-008999-e2e1c1-5f5b45-d8e588-a69666-e6d273-bb9120-c8cda6-8c9568-d2da9b-96a559-d3dd91-aba471-ede483-b8aa9d-b65' style='width:790px; height:900px; display:block; border:0px; scrolling:auto; frameborder:0; margin:30px auto; padding:0px;'>Iframes must be supported by your browser to view this site.</iframe>
				</div>
			<?php } else { ?>
				<?php if ($this->physical_cards_enabled) { ?>
					<!-- Physical Design Selection -->
					<div id="phys_design_type_div_expanded" class="collapse">
						<?php if (!empty($this->card_designs['designs'])) { ?>
							<h6>Select a card design:</h6>

							<div class="card-deck">
								<?php foreach ($this->card_designs['designs'] as $id => $design_data) { ?>
									<?php if ($design_data['supports_physical']) { ?>
										<div class="card p-0 col-md-4 m-md-4" id="td_<?php echo $id?>">
											<img class="card-img-top choose_design" id="di_<?php echo $id?>" data-design_id="<?php echo $id?>" src="<?php echo IMAGES_PATH?>/gift_cards/<?php echo $design_data['image_path']?>" alt="<?php echo $design_data['title']?>">
											<div class="card-body">
												<h5 class="card-title" for="cd_<?php echo $id?>"><?php echo $design_data['title']?></h5>
											</div>
											<div class="card-footer p-0">
												<button id="cd_<?php echo $id?>" data-design_id="<?php echo $id?>" class="btn btn-primary btn-block choose_design">Select</button>
											</div>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			<?php } ?>

			<!-- Virtual Design Selection -->
			<div id="virt_design_type_div_expanded" class="collapse">
				<?php if (!empty($this->card_designs['designs'])) { ?>
					Select a Design:<span id="expanded_virt_design_message"></span>

					<div class="card-deck">
						<?php $designCount = 0; foreach ($this->card_designs['designs'] as $id => $design_data) {  ?>
							<?php if ($design_data['supports_virtual']) { ?>
								<div class="card p-0 col-md-4 m-md-4" id="tdv_<?php echo $id?>">
									<img class="card-img-top choose_design" id="di_<?php echo $id?>" data-design_id="<?php echo $id?>" src="<?php echo IMAGES_PATH?>/gift_cards/<?php echo $design_data['image_path_virtual']?>" alt="<?php echo $design_data['title']?>">
									<div class="card-body">
										<h5 class="card-title" for="cd_<?php echo $id?>"><?php echo $design_data['title']?></h5>
									</div>
									<div class="card-footer p-0">
										<button id="cd_<?php echo $id?>" data-design_id="<?php echo $id?>" class="btn btn-primary btn-block choose_design">Select</button>
									</div>
								</div>
								<?php if (++$designCount % 3 === 0) {  ?>
									<div class="w-100 d-none d-md-block"><!-- wrap every 3 on md--></div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>
			</div>

			<p id="design_type_div_collapsed" class="collapse">
				Selected card design:
				<span id="selectedDesignImg"></span>
				<span id="selected_design_desc" class="font-weight-bold"></span>
				<span class="btn btn-sm btn-primary modify_design">Modify</span>
			</p>

			<div id="card_details_form" class="collapse">
				<h6>Enter gift card details</h6>

				<div class="row">

					<div class="col-md-6">

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">To</span>
								</div>
								<?php echo $this->form['to_name_html']; ?>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">From</span>
								</div>
								<?php echo $this->form['from_name_html']; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">Message</span>
						</div>
						<?php echo $this->form['message_html']; ?>
					</div>
				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">$</div>
								</div>
								<?php echo $this->form['amount_html']; ?>
							</div>
							<small id="ammountHelp" class="form-text text-muted">Numbers only, 25 to 500 dollars</small>
							<div id="amount_erm" class="text-warning collapse"></div>
						</div>
					</div>

					<?php if (!$this->edit) {?>
						<div class="col-md-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">Quantity</span>
									</div>
									<?php echo $this->form['quantity_html']; ?>
								</div>
								<small id="quantitytHelp" class="form-text text-muted">Please contact Dream Dinners customer support to order more than 50 gift cards.</small>
							</div>
						</div>
					<?php } ?>

				</div>

			</div>
			<div id="physical_card_form" class="collapse">
				<h6>Gift Card Shipping Address</h6>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<?php echo $this->form['shipping_first_name_html']; ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<?php echo $this->form['shipping_last_name_html']; ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<?php echo $this->form['shipping_address_1_html']; ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?php echo $this->form['shipping_address_2_html']; ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<?php echo $this->form['shipping_city_html']; ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<?php echo $this->form['shipping_state_html']; ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<?php echo $this->form['shipping_zip_html']; ?>
							<small id="shipziptHelp" class="form-text text-muted">(<span id="numZipDigits">5</span> digits)</small>
						</div>

					</div>
				</div>
			</div>

			<div id="virtual_card_form" class="collapse">
				<h6>eGift Card Recipient</h6>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">Email Address</div>
								</div>
								<?php echo $this->form['recipient_email_html']; ?>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">Confirm Email</div>
								</div>
								<?php echo $this->form['confirm_recipient_email_html']; ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col">
					<?php if ($this->edit) {?>
						<div class="form-group text-center">
							<?php echo $this->form['edit_submit_html']; ?>
						</div>
					<?php } else { ?>
						<div class="form-group text-center">
							<?php echo $this->form['add_submit_html']; ?>
						</div>
					<?php } ?>
				</div>
			</div>

			<div class="row mt-5">
				<div class="col text-center">
					<a href="/gift-card-balance" class="btn btn-primary"><i class="fas fa-credit-card mr-2"></i> Check gift card balance</a>
				</div>
			</div>

			<?php if (empty($this->card_designs['info']['num_physical']) || empty($this->card_designs['info']['num_virtual'])) { ?>
				<div class="row mt-4 font-size-small text-center font-italic">
					<?php if (empty($this->card_designs['info']['num_virtual'])) { ?>
						*Our virtual gift cards are currently unavailable. Please reach out to your local store to purchase over the phone or send a traditional gift card in the mail. Sorry for the inconvenience.
					<?php } else { ?>
						*Our traditional gift cards are currently unavailable. Please reach out to your local store to purchase over the phone or send a virtual gift card via email. Sorry for the inconvenience.
					<?php } ?>
				</div>
			<?php } ?>
		</form>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>