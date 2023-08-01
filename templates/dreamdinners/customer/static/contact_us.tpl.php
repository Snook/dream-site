<?php $this->assign('page_title', 'Contact Us');?>
<?php $this->assign('page_description','Contact Dream Dinners corporate guest services or our public relations company'); ?>
<?php $this->assign('page_keywords','contact dream dinners, dream dinners customer service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Contact Us</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main class="container">
		<?php list ($store, $ignore) = CUser::getCurrentStore(); ?>
		<?php if (!empty($store)) { ?>
			<?php list ($storeInfo, $ownerInfo)  = CStore::getStoreAndOwnerInfo($store); ?>
			<?php if (!empty($storeInfo)) { ?>
				<div class="row mb-5">
					<div class="col text-center">
						<h2 class="font-weight-semi-bold font-size-medium mb-md-4">For customer service inquiries</h2>
						<p class="mb-md-4">Please contact your local store.</p>
						<address>
							<strong>Dream Dinners <?php echo $storeInfo['store_name']; ?></strong>
							<div><?php echo (!empty($storeInfo['address_line2'])) ? $storeInfo['address_line1'] . ', ' . $storeInfo['address_line2'] : $storeInfo['address_line1']; ?>, <?php echo $storeInfo['city']; ?>, <?php echo $storeInfo['state_id']; ?> <?php echo $storeInfo['postal_code']; ?></div>
							<div><abbr title="Phone">P:</abbr> <?php echo $storeInfo['telephone_day']; ?></div>
							<div><a href="mailto:<?php echo $storeInfo['email_address']; ?>"><?php echo $storeInfo['email_address']; ?></a></div>
						</address>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
        <div class="row mb-5">
			<div class="col text-center">
				<h2 class="font-weight-semi-bold font-size-medium mb-md-4">FAQs</h2>
				<p class="mb-md-4"><a href="https://bugbase.dreamdinners.me/support/home" target="_blank" class="help-search-launcher">Browse our list of help topics</a> to find answers to your questions.</p>
			</div>
		</div>
		<div class="row mb-5">
			<div class="col text-center">
				<h2 class="font-weight-semi-bold font-size-medium mb-md-4">Corporate guest services &amp; website support</h2>
				<address>
					<strong>Dream Dinners, Inc.</strong>
					<div>P.O. Box #889, Snohomish, WA 98291</div>
					<div><abbr title="Phone">P:</abbr> 360-804-2020</div>
					<div><a href="mailto:customerservice@dreamdinners.com">customerservice@dreamdinners.com</a></div>
				</address>
			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>