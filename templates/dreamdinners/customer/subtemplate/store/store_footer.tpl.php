<section>
	<div class="container-fluid my-5">
		<div class="row mb-5">
			<div class="col-md-6 p-5 text-center mx-auto bg-gray">
				<h2 class="mb-4 font-weight-bold">Job Opportunities</h2>
				<p>
					Dream Dinners is an innovative concept in meal preparation that eliminates the stress of dealing with dinner – We remove menu planning, shopping & prep-work from the equation, leaving more quality time for families.
					We are looking for amazing team members to help us change more lives and bring Homemade, Made Easy meals into the community.
				</p>
				<?php if (!empty($this->DAO_store->AvailableJobsArray)) { ?>
					<h4 class="text-uppercase font-weight-bold">Available Positions</h4>
					<p>Our store is hiring for the following positions</p>
					<div class="row">
						<?php foreach ($this->DAO_store->AvailableJobsArray AS $DAO_store_job) { ?>
							<div class="col-12 mb-md-3">
								<div class="card">
									<div class="card-body bg-gray-light">
										<p class="card-text text-center"><?php echo CStore::translateStorePosition($DAO_store_job->position); ?></p>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<p class="mt-3">To apply for one of the positions above, please send a resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a></p>
				<?php } else { ?>
					<p>Join our Dream Dinners team. Feel free to submit your resume to <a href="mailto:<?php echo $this->DAO_store->email_address; ?>"><?php echo $this->DAO_store->email_address; ?></a>.</p>
				<?php } ?>
			</div>
			<div class="col-md-6 bg-green text-white text-center p-5 mx-auto">
				<h2>At Dream Dinners, <span class="font-weight-bold">our mission</span> is to make gathering around the family table a cornerstone of daily life. </h2>
			</div>
		</div>
	</div>
</section>