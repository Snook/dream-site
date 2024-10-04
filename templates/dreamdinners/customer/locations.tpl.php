<?php $this->setScript('foot', '//maps.googleapis.com/maps/api/js?v=3&amp;key=' . GOOGLE_APIKEY); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/locations.min.js'); ?>
<?php if (!empty($this->zip_code))
{
	$this->assign('page_title', $this->zip_code . ' Locations');
}
elseif (!empty($this->state))
{
	$this->assign('page_title', $this->state . ' Locations');
}
else
{
	$this->assign('page_title', 'Dream Dinners Store Locations');
} ?>
<?php $this->assign('page_description', 'Please enter your zip code to find a Dream Dinners location near you.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<div class="container my-5">

		<div class="row">
			<div class="col-lg-8 mx-auto col-sm-12">

				<div class="row">
					<div class="col text-center">
						<h1>Find how we can <span class="text-green font-weight-semi-bold">serve you</h1>
						<p>Enter your zip code below to see services in your area.</p>
					</div>
				</div>

				<?php if (false) { ?>
					<div class="form-row">
						<div class="col-12 mb-2">
							<?php echo $this->locations_form['zipsearch_address_html']; ?>
						</div>
						<div class="col-6 col-md-4 mb-2">
							<?php echo $this->locations_form['zipsearch_city_html']; ?>
						</div>
						<div class="col-6 col-md-4 mb-2">
							<?php echo $this->locations_form['zipsearch_zipcode_html']; ?>
						</div>
						<div class="col-12 col-md-4 mb-2">
							<?php echo $this->locations_form['zipsearch_state_id_html']; ?>
						</div>
					</div>

					<div class="row mb-4">
						<div class="col">
							<input id="full_addr_search_btn" type="submit" name="full_addr_search_btn" value="Search by address" class="cform_input btn btn-primary btn-block">
						</div>
					</div>

					<h2 class="mt-3 mb-4 col-lg-6  mx-auto col-sm-12 text-center">or</h2>
				<?php } ?>

				<div class="row mb-2">

					<div class="col-12">
                        <div class="collapse" id="zipsearch_zipcode_errorMessage">
                            <text class="text-danger">Invalid Zip Code</text>
                        </div>
						<?php echo $this->locations_form['zipsearch_zipcode_only_html']; ?>
					</div>
				</div>

				<div class="row mb-4">
					<div class="col">
						<button id="zipsearch_search_btn" type="submit" name="zipsearch_search_btn" value="Search by zip" class="cform_input btn btn-primary btn-block btn-spinner">Search by zip</button>
					</div>
				</div>

				<?php if (!CUser::isLoggedIn()) { ?>
					<div class="row mb-3">
						<span class="col text-center">
							<p>(Already a guest? <a class="btn-link collapsed font-weight-bold text-uppercase text-decoration-underline" data-toggle="collapse" href="#" data-target="#signInDiv" aria-expanded="false">Sign in</a> for faster checkout)</p>
						</span>
					</div>
					<div id="signInDiv" class="col-lg-12 collapse text-center align-self-center">
						<?php include $this->loadTemplate('customer/subtemplate/locations/locations_login.tpl.php'); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<div class="row">
			<div id="store_search_results" class="col">
				<?php
				if (isset($_REQUEST['zip']) || isset($_REQUEST['state']))
				{
					require_once('processor/location_search.php');
					$page = new processor_location_search();
					echo $page->runPublic();
				}
				?>
			</div>
		</div>

		<hr />

		<h2 class="col-lg-6  mx-auto col-sm-12 text-center my-5">Availability by State</h2>

		<div class="row">
			<div class="col">
				<div id="map"></div>
			</div>
		</div>
	</div>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>