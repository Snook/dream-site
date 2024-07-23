<?php $this->assign('page_title', 'Promotions, Contests and Special Offers');?>
<?php $this->assign('page_description','Find our latest in store promotions, special offers and events listed here.'); ?>
<?php $this->assign('page_keywords','contests, offers, email thaw reminders'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>Promotions & Partnerships</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">
	<!-- offer -->
		<section class="bg-cyan-dark">
			<div class="container mp-5">
				<div class="row">
					<div class="col">
						<div class="text-center">
							<h2 class="mt-5 mb-1 font-weight-bold text-center text-white">Exclusive New Customer Offer</h2>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/white-swash-320x12.webp" alt="swash">
							<p class="mt-1 mb-5 font-weight-bold text-center text-white">Try Us Today with One of the Following Offers</p>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container my-5">
				<div class="" id="" role="tabpanel" aria-labelledby="">
					<div class="row my-2">
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-center">
									<div class="card-body">
										<h3 class="font-weight-bold font-have-heart-two mt-2 font-size-extra-extra-large">$15 OFF Your First Order</h3>
										<p>Choose from pick up, in-store assembly, home delivery or shipping. Options vary by participating locations.</p>
										<p>Use code: <span class="font-weight-bold font-have-heart-two font-size-extra-large">15NEW24</span></p>
									</div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="card-group">
								<div class="card border-0 py-4 px-4 mx-1 text-center">
									<div class="card-body text-center">
										<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chelsee-hood-458x344.webp" alt="Dream Dinners Home Delivery" class="img-fluid mb-3" />
									</div>
									
								</div>
							</div>
						</div>
						<div class="row mt-4">
					<div class="col text-center">
					<a href="/locations" class="btn btn-lg btn-green mb-5">Get Started</a>
						<p class="font-italic">Offer valid for new customers or customers that have not placed a Dream Dinners order in more than 12 months. Offer cannot be combined with Dinner Dollars, other coupons or offers. No cash value. Offer can be redeemed once per guest and is not transferrable. Offer expires August 31, 2024. Valid only at participating locations.</p>
					</div>
				</div>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="border-top mb-3 mx-5" style="border-top: #b9bf33 dotted 5px !important;"></div>
			<div class="container-fluid my-5">
				<div class="row hero-double">
					<div class="col-md-6 p-0">
						<img src="<?php echo IMAGES_PATH; ?>/landing_pages/aug24-back-to-school-bundle-collage-circles-957x657.webp" alt="Specials" class="img-fluid">
					</div>
					<div class="col-md-6 text-left p-5 my-5">
						<h2 class="font-weight-bold mt-2">Back to School Bundle</h2>
						<p class="text-uppercase mb-4">Get ready to head back to school with these quick and easy kid picks. Our bundle includes BBQ Chip Chicken Tenders with Crispy Shoestring Fries, Italian Stuffed Shells and Chicken Carbonara Bake. Available at select locations, while supplies last. Not available for shipping.</p>
						<a href="/session-menu" class="btn btn-lg btn-green">Order Now</a>
					</div>


				</div>
			</div>
		</section>

		<!--<section>
			<div class="container">
				<img src="<?php echo IMAGES_PATH; ?>/events_programs/jellystone-park-1400x575.jpg" alt="Jellystone Park Camp-Resorts" class="img-fluid" />

				<div class="row my-5">
                	<div class="col mb-4">
						<h2><strong>2021 Dream Dinners & Jellystone Parks Camping Contest</strong></h2>
						<p>NO PURCHASE NECESSARY TO ENTER OR WIN. A PURCHASE WILL NOT INCREASE YOUR CHANCES OF WINNING.</p>
						<p><strong>Contest Period:</strong> Monday, October 4, 2021,at 8:00 am PT and ends Thursday, October 14, 2021 at 11:59 pm PT.</p>
						<p><strong>Entries:</strong> During the Contest Period, you may enter the contest by completing all three (3) of these actions:</p>
						<ol>
							<li>“Like” or “heart” the contest post (“Contest Post”). Contest posts will be on @DreamDinners and @CampJellystone Facebook and Instagram pages with “CONTEST POST” at the top of each post’s content.</li>
							<li>Follow @DreamDinners and @CampJellystone on Facebook or Instagram.</li>
							<li>Comment on the Contest Post with your favorite camping hack.</li>
						</ol>
						<p><strong>Drawing:</strong> Winners will be selected via a random number generator on or about October 21, 2021. Prospective winners will be notified within 10 days of selection via Instagram or Facebook (whichever platform their winning submission was posted on).</p>
						<p><strong>Prize:</strong> Three (3) winners will be selected to win and will each receive a $200 Dream Dinners® e-gift card, good at any Dream Dinners store location or with Dream Dinners Delivered and a $200 Jellystone Parks™ e-gift card, good at any Jellystone Parks U.S. location.</p>
						<p class="font-italic">Open to legal residents of the 50 United States and the District of Columbia who are 18 years of age or older at time of entry.  Odds of winning depend on number of qualifying entries received.  Limit one entry per person, per day. VOID WHERE PROHIBITED.  Sponsor: Dream Dinners, Inc., PO Box 889, Snohomish, WA 98291.</p>
						<p class="font-italic"><a href="/terms">View the complete Official Rules</a></p>
				  </div>
				</div>
			</div>
		</section>-->
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>