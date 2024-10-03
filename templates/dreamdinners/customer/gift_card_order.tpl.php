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
				<h1>Gift cards</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">
			</div>
		</div>
	</header>

	<main class="container">

			<p>We have discontinued our national gift card program. Contact your local store to purchase a gift certificate or learn about available gifting options.</p>

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
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>