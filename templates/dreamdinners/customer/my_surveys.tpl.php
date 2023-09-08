<?php $this->setScript('foot', SCRIPT_PATH . '/customer/my_surveys.min.js'); ?>
<?php $this->assign('page_title','My Surveys'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			<a href="/my-account" class="btn btn-primary"><span class="pr-2">&#10094;</span> My Account</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h1>My Surveys</h1>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

		</div>
	</div>
</header>

<main>
	<div class="container">

		<?php if ($this->edit_survey) { ?>
			<h3 class="mb-4"><?php echo $this->recipe['title']; ?></h3>
			<?php include $this->loadTemplate('customer/subtemplate/my_surveys/my_surveys_survey.tpl.php'); ?>
		<?php } else if (!empty($this->userTestRecipes)) { ?>
			<h2>DREAM DINNERS TEST RECIPES</h2>
			<?php include $this->loadTemplate('customer/subtemplate/my_surveys/my_surveys_list.tpl.php'); ?>
		<?php } else { ?>
			<p class="text-center">No surveys available at this time.</p>
		<?php } ?>
	</div>
</main>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>