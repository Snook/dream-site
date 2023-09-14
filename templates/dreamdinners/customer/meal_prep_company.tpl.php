<?php $this->assign('page_title', 'Meal Prep Company');?>
<?php $this->assign('page_description','Dream Dinners is your local meal prep company with delicious ready to cook meals hand-crafted just for you.'); ?>
<?php $this->assign('page_keywords','meal prep, meal prep company, meal prep near me, meal prep service'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main role="main">
        <section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-meal-prep-company">
					<div class="col-md-6 text-center mx-auto py-2" style="background: rgba(255, 255, 255, 0.8)">
						<h1 class="my-4 font-weight-bold">Have you been asking the question “Is there a company that offers meal prep near me?”</h1>
						<h3 class="mb-5">The answer is Yes! Dream Dinners is your local meal prep company with delicious ready-to-cook meals hand-crafted just for you.</h3>
						<a href="/locations" class="btn btn-primary mb-4">Find a Location</a>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-5 mb-6">
						<div>
							<img src="<?php echo IMAGES_PATH; ?>/landing_pages/mom-son-cooking-shrimp-green-beans-450x300.webp" alt="mom and son cooking together" class="img-fluid mb-3" />
						</div>
					</div>
					<div class="col-md-7 text-left">
					  <h2><strong>Here are just a few of the reasons to try your local meal prep company, Dream Dinners:</strong></h2>
						<br>&nbsp;
                      <ul>
                        <li>Chef-created, family-friendly recipes prepped in our local assembly kitchens.</li>
                        <li>A monthly order of ready-to-cook frozen dinners leaving you prepared for the next month.</li>
                        <li>Easy to follow instructions making both cooking and clean up simple and stress-free.</li>
						<li>More than 19 years of experience as the Original Meal Kit.</li>  
                      </ul>
					</div>
				</div>
			  </div>
		</section>

		<section>
			<div class="container-fluid my-5 bg-cyan-dark">
				<div class="container">
				<div class="row">
					<div class="col">
						<div class="text-center text-white">
							<h2 class="my-4">Three Convenient Ways We Serve You Locally</h2>
							<div class="border-bottom border-cyan mb-3 mx-5"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="card-group text-center text-white">
							
							<div class="card bg-cyan-dark border-0">
								<i class="dd-icon icon-cooler font-size-extra-extra-large text-white m-4"></i>
								<div class="card-body">
									<h5 class="card-title">Store Pick Up</h5>
									<p>Visit one of our local stores to quickly and easily pick up your assembled meals.</p>
								</div>
							</div>
							<div class="card bg-cyan-dark border-0">
								<i class="dd-icon icon-delivery font-size-extra-extra-large text-white m-4"></i>
								<div class="card-body">
									<h5 class="card-title">Home Delivery</h5>
									<p>For ultimate convenience, get your fully-prepped meals delivered to your door.</p>
								</div>
							</div>
							<div class="card bg-cyan-dark border-0">
								<i class="dd-icon icon-measuring_cup font-size-extra-extra-large text-white m-4"></i>
								<div class="card-body">
									<h5 class="card-title">Store Assembly</h5>
									<p>At your local store, assemble and customize your dinners using our easy-to-follow recipes.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row my-4 pb-4">
					<div class="col text-center text-white">					
						<p><a href="/locations" class="btn btn-cyan text-white">Find a Location</a></p>
						<p><em>*Availability varies by location. Check your local stores calendar to find available ordering options.</em></p>
					</div>
				</div>
			</div>
		</div>		
	</section>

	<!-- Body Plates -->
	<!--
        <section>
			<div class="container">
				<div class="row">
					<div class="col my-3">
						<div class="text-center">
							<h2 class="my-3">December Menu Recommendations</h2>
							<div class="border-bottom border-red mb-3 mx-5"></div>
						</div>
					</div>
				</div>
				<div class="row my-4">
					<div class="col">
						<div class="card-group text-center">
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/pork-chop-milanese-circle-458x344.webp" alt="Pork Chop Milanese" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Pork Chop Milanese</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/beef-bourguignon-circle-458x344.webp" alt="Beef Bourguignon" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Beef Bourguignon</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/chicken-marsala-circle-458x344.webp" alt="Chicken Marsalawith Mushrooms & Mashed Potatoes" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Chicken Marsala with Mushrooms & Mashed Potatoes</p>
								</div>
							</div>
							<div class="card border-0 mx-1">
								<img src="<?php echo IMAGES_PATH; ?>/landing_pages/italian-stuffed-shells-circle-458x344.webp" alt="Italian Stuffed Shells" class="img-fluid" />
								<div class="card-body">
									<p class="card-text">Italian Stuffed Shells</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row my-4">
					<div class="col text-center mb-5">
						<a href="/locations" class="btn btn-lg btn-primary">Get Cooking</a>
					</div>
				</div>
			</div>
		</section>-->
		
		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-6 mb-6 text-left">
							<h3 class="my-4">OUR GUESTS LOVE US</h3>
						<p>Hear what our guest, Brittnie has to say about her favorite meal prep company.</p>
						
						<p><a href="/locations" data-gaq_cat="original" data-gaq_action="Order Now" data-gaq_label="Landing Page" class="btn btn-lg btn-primary">Order Now</a></p>
					</div>
					<div class="col-md-6 text-right">
						<div class="embed-responsive embed-responsive-16by9">
						<iframe class="embed-responsive-item" loading="lazy" src="https://www.youtube.com/embed/MrsU9fG2w3I?rel=0&amp;controls=0" allowfullscreen></iframe>
					</div>
				</div>
			  </div>
			</div>
		</section>
		
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>