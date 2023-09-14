<?php $this->assign('page_title', 'Dream Taste Events');?>
<?php $this->assign('page_description','Host a Dream Taste event with a few of your friends that have never tried Dream Dinners. They get to sample us for a special price and you get great gifts.'); ?>
<?php $this->assign('page_keywords','taste menu, sample menu, host party, host event, dream taste, dream event'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h2>Host a Dream Taste</h2>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
		<section>
			<div class="container">
				<div class="row">
					<div class="col text-center">
						<img src="<?php echo IMAGES_PATH; ?>/events_programs/dream-taste-logo.png" class="img-fluid" alt="Dream Taste Event" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12 col-md-12 col-lg-12">
						<p>PLATEPOINTS Chefs have the exclusive opportunity to host a Dream Taste session for their family and friends. Help them solve their dinnertime dilemma by showing exactly how Homemade, Made Easy can work for their families.</p>
						<p class="font-weight-bold text-uppercase">How it works</p>
						<ul>
							<li><span class="font-weight-bold text-uppercase">Hostess</span><br />
								Select a date and work with our team to invite your friends, family and neighbors.</li>
							<li><span class="font-weight-bold text-uppercase">Guests</span><br />
								Sign up online and assemble three wholesome meals to take home and enjoy.</li>
							<li><span class="font-weight-bold text-uppercase">Together</span><br />
								You'll bring your families back around the dinner table.</li>
						</ul>
						<p><span class="font-weight-bold text-uppercase">Hostess perks</span></p>
						<ul>
							<li>3 FREE Dinners</li>
							<li>500 PLATEPOINTS BONUS for hosting your Dream Taste.</li>
							<li>10 Dinner Dollars BONUS for each new guest who completes a standard order.</li>
							<li>250 PLATEPOINTS BONUS for each new guest who completes an Meal Prep Starter Pack.</li>
						</ul>
						<p>Contact your store today and schedule a Dream Taste event.</p>
					</div>
				</div>
			</div>
		</section>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>