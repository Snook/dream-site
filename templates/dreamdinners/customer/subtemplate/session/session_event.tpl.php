<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1">
			<a href="/?page=store&amp;id=<?php echo $this->cart_info['storeObj']->id; ?>" class="btn btn-primary"><span class="pr-2">&#10094;</span> Location</a>
		</div>
		<div class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h2>Select your <span class="text-green font-weight-semi-bold"><?php echo CTemplate::dateTimeFormat($this->cart_info['menu_info']['menu_name'], FULL_MONTH); ?></span> event</h2>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right">

		</div>
	</div>
</header>

<main>
	<div class="container">
		<div class="row justify-content-center">
			<?php foreach ($this->sessions['sessions'] AS $date => $day) { ?>
				<?php if ($day['info']['has_available_sessions']) { ?>
					<?php if (!empty($day['sessions'])) { ?>
						<?php foreach ($day['sessions'] AS $id => $session) { ?>
							<?php include $this->loadTemplate('customer/subtemplate/session/session_event_card.tpl.php'); ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</main>

<?php if(count($this->sessions['sessions']) == 0){ ?>
	<div class="text-center mt-4 no-session-available" role="alert">
		Oops, it looks like there aren’t any dates available to complete your order.<br>Please select another menu to order from. <br><br>
		<a href="/session-menu" class="btn btn-primary">Menu</a>
	</div>
<?php } ?>