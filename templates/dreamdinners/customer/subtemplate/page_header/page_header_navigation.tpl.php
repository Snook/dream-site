<?php
//check optional template parameter to conditionally determine nav for Order and Get Started
if (empty($this->order_process_navigation_page)) {
	$this->order_process_navigation_page = 'locations';
}
$backNavigation = '';
if(array_key_exists('page', $_REQUEST) || array_key_exists('static', $_REQUEST))
{
	$backNavigation = '&back='.urlencode($_SERVER['REQUEST_URI']);
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white px-sm-5 py-sm-4 mb-5">
	<button class="navbar-toggler px-1" type="button" data-toggle="collapse" data-target="#main-sidenav" aria-expanded="false" aria-label="Toggle main navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<a class="navbar-brand d-block d-lg-none mr-0" href="/">
		<!--<img class="img-fluid" src="<?php echo IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" alt="Dream Dinners logo" />-->
		<img class="img-fluid" src="<?php echo IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" alt="Dream Dinners logo" />
	</a>
	<?php if (CUser::isLoggedIn()) { ?>
		<button class="navbar-toggler" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<img src="<?php echo IMAGES_PATH; ?>/style/platepoints/placeholder_avatar.png" alt="Profile picture" class="my-account-img rounded-circle">
		</button>
		<div class="dropdown-menu dropdown-menu-right d-lg-none mr-2" aria-labelledby="dropdownMenuButton">
			<a class="dropdown-item" href="/main.php?page=my_account">My Account</a>
			<a class="dropdown-item" href="/main.php?page=my_meals">My Reviews</a>
			<a class="dropdown-item" href="/main.php?page=my_meals&tab=nav-past_orders">My Orders</a>
			<?php if (!CUser::hasDeliveredOrdersOnly()) { ?>
				<a class="dropdown-item" href="/main.php?page=my_events">My Events</a>
			<?php } ?>
			<a class="dropdown-item" href="/main.php?page=account">Edit Account</a>
			<a class="dropdown-item" href="/main.php?page=signout">Sign Out</a>
		</div>
	<?php } else { ?>
		<a href="/main.php?page=login" class="btn btn-primary btn-sm px-1 d-lg-none">
			Sign in
		</a>
	<?php } ?>
	<div class="collapse navbar-collapse">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-6 col-xl-4 d-lg-block d-none mx-auto">
					<!--<a href="/"><img class="img-fluid" src="<?php echo IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" alt="Dream Dinners logo" /></a>-->
					<a href="/"><img class="img-fluid" src="<?php echo IMAGES_PATH; ?>/style/logo/dream-dinners-logo.png" alt="Dream Dinners logo" /></a>
				</div>
			</div>
            <?php if(empty($this->logo_only)) {  ?>
			<div class="row bg-light">
				<div class="col">
					<ul class="navbar navbar-nav">
						<li class="nav-item">
							<a class="nav-link" href="/main.php?page=browse_menu">Menu Preview</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/main.php?static=how_it_works">How it works</a>
						</li>
						<li class="nav-item d-block d-lg-none d-xl-block">
							<a class="nav-link" href="/main.php?page=session_menu">Order</a>
						</li>
						<li class="nav-item d-block d-lg-none d-xl-block">
							<a class="nav-link" href="/main.php?static=share">Share</a>
						</li>
						<li class="nav-item d-block d-lg-none d-xl-block">
							<a class="nav-link" href="/main.php?static=gift">Gift</a>
						</li>
						<li class="nav-item">
							<a class="nav-link login_sign_in<?php if (CUser::isLoggedIn()) { ?> collapse<?php } ?>" href="/main.php?page=login<?php echo $backNavigation;?>">Sign In</a>
							<div class="dropdown login_signed_in<?php if (!CUser::isLoggedIn()) { ?> collapse<?php } ?>">
								<a class="nav-link dropdown-toggle text-white-space-nowrap" href="/main.php?page=my_account" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Hi, <span class="login_first_name"><?php echo CUser::getCurrentUser()->firstname; ?></span>
								</a>

								<div class="dropdown-menu text-center text-md-left px-2" aria-labelledby="dropdownMenuLink">
									<a class="dropdown-item nav-link" href="/main.php?page=my_account">My account</a>
									<a class="dropdown-item nav-link" href="/main.php?page=my_meals">My reviews</a>
									<a class="dropdown-item nav-link" href="/main.php?page=my_meals&amp;tab=nav-past_orders">My orders</a>

									<?php if (!CUser::hasDeliveredOrdersOnly()) { ?>
										<a class="dropdown-item nav-link" href="/main.php?page=my_events">My events</a>
									<?php } ?>
									<a class="dropdown-item nav-link" href="/main.php?page=account">Edit account</a>
									<a class="dropdown-item nav-link" href="/main.php?page=signout<?php echo (property_exists($this,'logout_navigation_page') ?  $this->logout_navigation_page:''); ?>">Sign Out</a>

									<?php if (CUser::isUserStaff()) { ?>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item nav-link" href="main.php?page=admin_main">BackOffice Home</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_reports">Reports</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_list_users">Guests</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_session_mgr">Session Calendar</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_menu_inventory_mgr">Inventory Manager</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_menu_editor">Menu Editor</a>
										<a class="dropdown-item nav-link" href="main.php?page=admin_gift_card_management">Gift Cards</a>
									<?php } ?>

									<?php if (defined('DEV_BASE_NAME') && DEV_BASE_NAME) { ?>
										<div class="dropdown-divider"></div>
										<div class="dropdown-header">Development Tools</div>
										<div class="dropdown-item-text text-center"><?php echo DEV_BASE_NAME; ?></div>
										<div class="btn btn-outline-danger btn-block watch_cart">Watch Cart</div>
										<div class="btn btn-outline-danger btn-block clear-cart-gc">Clear cart</div>
										<?php if (CBrowserSession::getValue('FAUID')) { ?>
											<div class="btn btn-outline-danger btn-block return-fauid">Logout as guest</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</li>
						<li class="nav-item d-none d-lg-block ml-lg-4">
							<a class="btn btn-primary btn-lg py-1 px-3 mt-4 mt-md-0 text-white-space-nowrap" href="/main.php?page=<?php echo $this->order_process_navigation_page; ?>">Get started</a>
						</li>
					</ul>
				</div>
			</div>
            <?php } ?>
		</div>
	</div>
</nav>

<div id="main-sidenav" class="container sidenav d-lg-none py-2 d-print-none">
	<div class="row mb-3">
		<div class="col">
			<button class="navbar-toggler close float-none font-size-extra-large" type="button" data-toggle="collapse" data-target="#main-sidenav" aria-expanded="false" aria-label="Close main menu">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
	<a class="btn btn-primary btn-block mb-2" href="/main.php?page=session_menu">Order</a>
	<a class="dropdown-item" href="/main.php?page=browse_menu">Menu Preview</a>
	<a class="dropdown-item" href="/main.php?static=how_it_works">How It Works</a>
	<a class="dropdown-item" href="/main.php?page=locations">Store Locations</a>
	<a class="dropdown-item" href="/main.php?page=recipe_resources">Recipe Resources</a>
	<a class="dropdown-item" href="/main.php?static=share">Share</a>
	<a class="dropdown-item" href="/main.php?page=platepoints">PLATEPOINTS</a>
	<a class="dropdown-item" href="/main.php?static=fundraisers">Fundraisers</a>
	<a class="dropdown-item" href="/main.php?static=promotions">Promotions</a>
	<a class="dropdown-item" href="/main.php?page=gift_card_order">Gift Cards</a>
	<a class="dropdown-item" href="/main.php?static=about_us">About Us</a>
	<?php if (defined('ENABLE_HELP_SEARCH') && ENABLE_HELP_SEARCH == true) { ?>
		<a class="dropdown-item help-search-launcher" href="#">Help</a>
	<?php } ?>
	<?php if (!CUser::isLoggedIn()) { ?>
		<a class="btn btn-primary btn-block" href="/main.php?page=login">Sign In</a>
	<?php } ?>
	<?php if (CUser::isUserStaff()) { ?>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="main.php?page=admin_main">BackOffice Home</a>
		<a class="dropdown-item" href="main.php?page=admin_reports">Reports</a>
		<a class="dropdown-item" href="main.php?page=admin_list_users">Guests</a>
		<a class="dropdown-item" href="main.php?page=admin_session_mgr">Session Calendar</a>
		<a class="dropdown-item" href="main.php?page=admin_menu_inventory_mgr">Inventory Manager</a>
		<a class="dropdown-item" href="main.php?page=admin_menu_editor">Menu Editor</a>
		<a class="dropdown-item" href="main.php?page=admin_gift_card_management">Gift Cards</a>
	<?php } ?>

	<?php if (defined('DEV_BASE_NAME') && DEV_BASE_NAME) { ?>
		<div class="dropdown-divider"></div>
		<div class="dropdown-header">Development Tools</div>
		<div class="dropdown-item-text text-center"><?php echo DEV_BASE_NAME; ?></div>
		<div class="btn btn-outline-danger btn-block watch_cart">Watch Cart</div>
		<div class="btn btn-outline-danger btn-block clear-cart-gc">Clear cart</div>
		<?php if (CBrowserSession::getValue('FAUID')) { ?>
			<div class="btn btn-outline-danger btn-block return-fauid">Logout as guest</div>
		<?php } ?>
	<?php } ?>
</div>