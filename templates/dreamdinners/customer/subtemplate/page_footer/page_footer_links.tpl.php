<?php if (empty($this->sticky_nav_bottom_disable)) { ?>
	<?php if (defined('ENABLE_HELP_SEARCH') && ENABLE_HELP_SEARCH == true) { ?>
		<section>
			<div class="container-fluid footer-nav border-bottom border-top border-green-dark py-2 mt-5 bg-white d-lg-none d-print-none">
				<div class="row">

					<a href="/how-it-works" class="col-3 px-0 text-center font-size-small">
						<i class="dd-icon icon-measuring_cup font-size-medium-large text-green"></i>
						<div>How it Works</div>
					</a>
					<a href="/session-menu" class="col-3 px-0 text-center font-size-small">
						<i class="dd-icon icon-cart font-size-medium-large text-green mr-1"></i>
						<div>Order</div>
					</a>
					<a href="#" class="col-3 px-0 text-center font-size-small help-search-launcher">
						<i class="dd-icon icon-information-solid font-size-medium-large text-green mr-1"></i>
						<div>Help</div>
					</a>
					<a href="#" data-toggle="collapse" data-target="#main-sidenav" aria-expanded="false" aria-label="Toggle main navigation" class="col-2 px-0 text-center font-size-small">
						<i class="dd-icon icon-ellipsis font-size-medium-large text-green"></i>
						<div>More</div>
					</a>
				</div>
			</div>
		</section>
	<?php }else { ?>
		<section>
			<div class="container-fluid footer-nav border-bottom border-top border-green-dark py-2 mt-5 bg-white d-lg-none d-print-none">
				<div class="row">
					<a href="/browse-menu" class="col-4 pr-0 text-center font-size-small">
						<i class="dd-icon icon-table_setting font-size-medium-large text-green"></i>
						<div>Menu Preview</div>
					</a>
					<a href="/how-it-works" class="col-3 px-0 text-center font-size-small">
						<i class="dd-icon icon-measuring_cup font-size-medium-large text-green"></i>
						<div>How it Works</div>
					</a>
					<a href="/session-menu" class="col-2 px-0 text-center font-size-small">
						<i class="dd-icon icon-cart font-size-medium-large text-green mr-1"></i>
						<div>Order</div>
					</a>
					<a href="#" data-toggle="collapse" data-target="#main-sidenav" aria-expanded="false" aria-label="Toggle main navigation" class="col-2 px-0 text-center font-size-small">
						<i class="dd-icon icon-ellipsis font-size-medium-large text-green"></i>
						<div>More</div>
					</a>
				</div>
			</div>
		</section>
	<?php } ?>
<?php } ?>

<footer class="bg-green-dark text-white p-5 mt-5 d-print-none">
	<div class="row">
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1">Quick Links</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/locations">Order</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/locations">Locations</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/shipping">Shipping</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/browse-menu">Menu Preview</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/share">Share</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/my-account">My Account</a>			
				<?php if (defined('ENABLE_HELP_SEARCH') && ENABLE_HELP_SEARCH == true) { ?>
					<a class="list-group-item bg-green-dark text-white py-0 help-search-launcher" href="#">Help</a>
				<?php } ?>
				<a class="list-group-item bg-green-dark text-white py-0" href="/gift-card-order">Gift Cards</a>
			</div>
		</div>
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1 mt-4 mt-md-0">Our Company</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/about-us">About Us</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/our-food">Our Food</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/job-opportunities">Careers</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/contact-us">Contact Us</a>
			</div>
		</div>
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1 mt-4 mt-md-0">Learn More</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/how-it-works">How It Works</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/gift">Gift</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/platepoints">PlatePoints</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/promotions">Promotions</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/fundraisers">Fundraisers</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/recipe-resources">Recipe Resources</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="https://blog.dreamdinners.com" rel="noopener" target="_blank">Our Blog</a>
			</div>
		</div>
		<div class="col-md-4 col-lg-3 col-xl-2">
			<?php if (CUser::isUserStaff()) { ?>
				<p class="font-weight-bold text-uppercase pl-1 mt-4 mt-lg-0">Administration</p>
				<div class="list-group list-group-flush">
					<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/backoffice/main">BackOffice Home</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/reports">Reports</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/list-users">Guests</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/session-mgr">Calendar</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/menu-inventory-mgr">Inventory Manager</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/menu-editor">Menu Editor</a>
					<a class="list-group-item bg-green-dark text-white py-0" href="/backoffice/gift-card-management">Gift Cards</a>
				</div>
			<?php } ?>
		</div>
		<div class="<?php if (CUser::isUserStaff()) { ?>col-md-8<?php } ?> col-lg-12 col-xl-4 mt-4">
			<div class="row">
				<div class="col text-center text-xl-right">
					<a href="https://instagram.com/dreamdinners" rel="noopener" class="text-decoration-hover-none font-size-extra-large text-green-light mr-3" target="_blank"><i class="fab fa-instagram"></i></a>
					<a href="https://facebook.com/dreamdinners" rel="noopener" class="text-decoration-hover-none font-size-extra-large text-green-light mr-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
					<a href="https://pinterest.com/dreamdinners" rel="noopener" class="text-decoration-hover-none font-size-extra-large text-green-light" target="_blank"><i class="fab fa-pinterest"></i></a>
				</div>
			</div>
		</div>
	</div>
</footer>

<section class="container-fluid py-2 d-print-none">
	<div class="row">
		<div class="col-6 text-right">
			<a href="https://dreamdinnersfranchise.com" rel="noopener" target="_blank"><img loading="lazy" src="<?php echo IMAGES_PATH; ?>/main/footer-2.png" class="img-fluid" alt="Dream Dinners Franchise"></a>
		</div>
		<div class="col-6 text-left">
			<a href="https://dreamdinnersfoundation.org/" rel="noopener" target="_blank"><img loading="lazy" src="<?php echo IMAGES_PATH; ?>/main/footer-3.png" class="img-fluid" alt="Dream Dinners Foundation"></a>
		</div>
	</div>
</section>

<section>
	<div class="container-fluid">
		<div class="row bg-green-dark">
			<div class="col">
				<ul class="list-inline font-size-small text-white text-center m-3">
					<li class="list-inline-item"><a class="text-white" href="/">&copy; Dream Dinners, Inc.</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/terms">Terms</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/privacy">Privacy</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/cookies">Cookies</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/sitemap">Sitemap</a></li>
				</ul>
			</div>
		</div>
	</div>
</section>