<?php $this->assign('page_title', 'About Us');?>
<?php $this->assign('page_description','Dream Dinners is about getting families around the dinner table with a delicious home cooked meal.'); ?>
<?php $this->assign('page_keywords','family dinner, dinner around the table, dinner preparation, meal preparation, homemade dinner, fix and freeze, freezer dinner'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<header class="container my-5">
		<div class="row">
			<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">

			</div>
			<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
				<h1>About Us</h1>
			</div>
			<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

			</div>
		</div>
	</header>

	<main role="main">

		<section>
			<div class="container">
				<div class="row my-5">
					<div class="col-md-10 mx-auto bg-image-mission p-5">
						<p class="font-size-large text-uppercase text-center"><br/>
							At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life.<br/>
                        </p>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="container-fluid my-5">
				<div class="row">
					<div class="col-md-6 bg-image-our-story text-white text-left p-5">
						<h2 class="text-uppercase font-weight-bold">Our Story</h2>
						<p>Learn how Dream Dinners has been helping busy families get easy, delicious meals on the table since 2002.</p>
						<a href="/main.php?static=our_story" class="btn btn-secondary">Learn More</a>
					</div>
					<div class="col-md-6 bg-image-our-food text-white text-left p-5">
						<h2 class="text-uppercase font-weight-bold">Our Food</h2>
						<p>Providing Fresh Produce with Innovative Partnerships</p>
						<a href="/main.php?static=our_food" class="btn btn-secondary">Learn More</a>
					</div>

				</div>
			</div>
		</section>


		<!--<section>
			<div class="container">
				<div id="leadership" class="row">
					<div class="col">
						<h3 class="text-center mb-4">Dream Dinners Leadership</h3>
					</div>
				</div>
				
				<div class="row">
					<div class="col">

						<ul class="nav nav-tabs" id="myTab" role="tablist">
							
							<li class="nav-item col-3 px-0 px-md-3">
								<a class="nav-link active p-1 p-md-3" id="tina-tab" data-urlpush="true" data-toggle="tab" href="#tina" role="tab" aria-controls="profile" aria-selected="true">
									<img src="<?php echo IMAGES_PATH; ?>/about_us/tina-kuna.jpg" class="img-fluid" alt="Tina Kuna" />
									<span class="d-none d-md-block">Tina Kuna</span>
								</a>
							</li>
							<li class="nav-item col-3 px-0 px-md-3">
								<a class="nav-link p-1 p-md-3" id="kevin-tab" data-urlpush="true" data-toggle="tab" href="#kevin" role="tab" aria-controls="messages" aria-selected="false">
									<img src="<?php echo IMAGES_PATH; ?>/about_us/kevin-mayo.jpg" class="img-fluid" alt="Kevin Mayo" />
									<span class="d-none d-md-block">Kevin Mayo</span>
								</a>
							</li>
							<li class="nav-item col-3 px-0 px-md-3">
								<a class="nav-link p-1 p-md-3" id="laura-tab" data-urlpush="true" data-toggle="tab" href="#laura" role="tab" aria-controls="settings" aria-selected="false">
									<img src="<?php echo IMAGES_PATH; ?>/about_us/laura-mcmillan.jpg" class="img-fluid" alt="Laura McMillan" />
									<span class="d-none d-md-block">Laura McMillan</span>
								</a>
							</li>
							<li class="nav-item col-3 px-0 px-md-3">
								<a class="nav-link p-1 p-md-3" id="team-tab" data-urlpush="true" data-toggle="tab" href="#team" role="tab" aria-controls="messages" aria-selected="false">
									<img src="<?php echo IMAGES_PATH; ?>/about_us/our-team.jpg" class="img-fluid" alt="Our Team" />
									<span class="d-none d-md-block">Our Team</span>
								</a>
							</li>
						</ul>

						
						<div class="tab-content pt-4">
							
							<div class="tab-pane fade show active" id="tina" role="tabpanel" aria-labelledby="tina-tab">
								<h4>Tina Kuna, Chief Executive Officer &amp; Co-Founder </h4>
								<p>A recognized leader in the meal assembly industry, Tina Kuna was instrumental in creating the innovative Dream Dinners business model, which has become the industry standard. In 1996, as a working mother of three, Kuna adopted the assemble-and-freeze method for her family, as taught to her by eventual business partner Stephanie Allen. The partners founded Dream Dinners in 2002, and since that time, the company has flourished under Tina's strategic direction.  A strong advocate of families eating together, Tina co-authored the #1 New York Times bestselling book, The Hour That Matters Most. </p>
								<p>In addition to her responsibilities of company sales and franchise administration, Tina became the CEO of Dream Dinners in 2020. At the same time, Tina leads the Dream Dinners Foundation in their efforts to feed those in need both in the U.S. and abroad.  Named 2006 Ernst and Young’s Entrepreneurs of the Year Pacific Northwest, Tina’s two-decade track record of financial and management experience continues to help Dream Dinners grow across the nation. </p>
							</div>
							<div class="tab-pane fade" id="kevin" role="tabpanel" aria-labelledby="kevin-tab">
								<h4>Kevin Mayo, Chief Financial Officer &amp; Chief Information Officer</h4>
								<p>As Chief Financial Officer and Chief Information Officer, Kevin Mayo leads Dream Dinners in all financial facets of its operations, with strategic oversight of corporate accounting, finance, tax, capital planning, banking, and Information Technology.  While creating tactical efficiencies across key Dream Dinners programs, systems, and processes, Kevin leads the financial strategy to achieve Dream Dinners’ near and long-term growth targets. </p> 
								<p>Before joining the Dream Dinners team in 2018, Kevin held the role of Chief Financial Officer for a biotech startup company in Seattle, gaining valuable experience in a fast-paced, entrepreneurial environment.  He began his career in public accounting at Ernst and Young and previously held both domestic and international financial leadership roles in Kraft Food and Terex. </p>
							</div>
							<div class="tab-pane fade" id="laura" role="tabpanel" aria-labelledby="laura-tab">
								<h4>Laura McMillan, Senior Vice President of Brand</h4>
								<p>Laura McMillan returned to Dream Dinners in 2019 as the Senior Vice President of Marketing. Her previous experience and understanding of the brand allowed her to immediately implement marketing strategies to increase guest traffic, drive key performance indicators, and enhance franchisee relationships. As a proactive and engaging leader, Laura has since taken on the added responsibilities of leading Dream Dinners Sales and Food Development teams. Laura utilizes her brand expertise and growth focus to identify key business needs and create a cohesive strategy that looks toward the future.</p>
								<p>Pursuing other career opportunities in 2016, Laura worked as the Director of Marketing for both Restaurants Unlimited and L’Oréal. In 2017, she became a certified professional coach. With her return to Dream Dinners, she has proven to be a trusted adviser to bring data-driven ideas and compelling insights to drive business growth.</p>
							</div>
							<div class="tab-pane fade" id="team" role="tabpanel" aria-labelledby="team-tab">
								<h4>Our Dream Dinners Team</h4>
								<p>Dream Dinners is made up of passionate and caring people committed to helping our stores and communities bring their families around the dinner table. This passion leads to a hardworking, fast paced culture. However, despite all the hard work, we understand and emphasize the importance of time spent with our own families, creating loving memories and healthy lives. Here at Dream Dinners we strive to strike the perfect balance of work and play, creating a fun work environment to nourish both our bodies and souls.</p>
								<p>Interested in joining the Dream Dinners community?</p>
								<p>We are always looking for eager, hardworking individuals who are dedicated to helping families build strong relationships around the dinner table. <a href="main.php?static=job_opportunities">Visit our Careers page</a> for more information.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>-->

		<section>
			<div class="container-fluid">
				<div class="row my-5 p-5 bg-image-foundation">
					<div class="col-md-8 text-center text-white mx-auto">
						<img src="<?php echo IMAGES_PATH; ?>/about_us/dream-dinners-foundation-logo-white.png" class="img-fluid mb-3" alt="Dream Dinners Foundation" />
						<p class="mt-4">
							The Dream Dinners Foundation mission is Connecting Heart to Service by educating the next generation to actively impact communities locally and globally. We accomplish this by providing millions of meals to children in need, both in our own communities and abroad. We hope that you will join us in the fight against hunger!
						</p>
						<p><a href="https://dreamdinnersfoundation.org" class="btn btn-primary">DreamDinnersFoundation.org</a></p>
					</div>
				</div>
			</div>
		</section>

	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>