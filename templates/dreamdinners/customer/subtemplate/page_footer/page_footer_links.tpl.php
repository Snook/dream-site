<?php if (empty($this->sticky_nav_bottom_disable)) { ?>
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

<footer class="bg-green-dark text-white p-5 mt-5 d-print-none">
	<div class="row">
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1">Quick Links</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/locations">Order</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/locations">Locations</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/shipping">Shipping</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/browse-menu">Menu Preview</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/my-account">My Account</a>
				
			</div>
		</div>
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1 mt-4 mt-md-0">Our Company</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0" href="/our-food">Our Food</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/help">Help</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/gift-card-order">Gift Cards</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/contact-us">Contact Us</a>
			</div>
		</div>
		<div class="col-md-4 col-lg-3 col-xl-2">
			<p class="font-weight-bold text-uppercase pl-1 mt-4 mt-md-0">Learn More</p>
			<div class="list-group list-group-flush">
				<a class="list-group-item bg-green-dark text-white py-0 border-top-0" href="/how-it-works">How It Works</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/fundraisers">Fundraisers</a>
				<a class="list-group-item bg-green-dark text-white py-0" href="/recipe-resources">Recipe Resources</a>
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
				</div>
			<?php } ?>
		</div>
	</div>
</footer>


<section>
	<div class="container-fluid">
		<div class="row bg-green-dark">
			<div class="col">
				<ul class="list-inline font-size-small text-white text-center m-3">
					<li class="list-inline-item"><a class="text-white" href="/">&copy; Dream Dinners, Inc.</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/terms">Terms</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/privacy">Privacy</a></li>
					<li class="list-inline-item"><a class="text-white d-print-none" href="/cookies">Cookies</a></li>
				</ul>
			</div>
		</div>
	</div>
</section>