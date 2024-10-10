<?php $this->assign('page_title', 'Log in to your Dream Dinners account'); ?>
<?php $this->assign('page_description', 'Please log in or create an account at Dream Dinners.'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

	<main class="container">
		<div class="row">
			<div class="col-lg-12 text-center my-5">
				<h1>Sign in</h1>
			</div>
			<div class="col-lg-12 text-center align-self-center">

				<?php include $this->loadTemplate('customer/subtemplate/login/login_form.tpl.php'); ?>

				<?php if (!defined('ENABLE_CUSTOMER_SITE') || ENABLE_CUSTOMER_SITE) { ?>
					<div class="col-lg-4 col-md-6 mx-auto mt-4">
						<a class="text-body text-decoration-hover-underline" href="/account-signup">Don't have an account? <span class="sign-up">sign up</span></a>
					</div>
				<?php } ?>

			</div>
		</div>
	</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>