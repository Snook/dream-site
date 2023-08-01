<?php $this->assign('page_title','Cart Watcher'); ?>
<?php $this->setScript('foot', SCRIPT_PATH . '/customer/cart_watcher.min.js'); ?>

<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<header class="container my-5">
	<div class="row">
		<div class="col-6 col-sm-3 p-0 order-2 order-sm-1"></div>
		<div
			class="col-12 col-sm-6 p-sm-0 order-1 order-sm-2 mb-4 mb-sm-0 text-center">
			<h2>Cart Watcher</h2>
		</div>
		<div class="col-6 col-sm-3 p-0 order-3 order-sm-3 text-right"></div>
	</div>
</header>

<form method="post" class="needs-validation" novalidate action="">

	<main class="container">
	<div class="row">
		<div class="col">
			<div class="form-row">
				<div class="form-group col-md-3">
					<p>Choose how to attach to cart</p>
				</div>
				<div class="form-group col-md-6 text-center">

					<div class="custom-control custom-control-inline custom-checkbox">
					<?php echo $this->form['attach_method_html']['MINE']; ?>
				</div>
					<br />
					<div class="custom-control custom-control-inline custom-checkbox">
					<?php echo $this->form['attach_method_html']['CART_ID']; ?>
				</div>
					<div class="custom-control custom-control-inline custom-checkbox">
					<?php echo $this->form['cart_id_html']; ?>
				</div>
					<div class="custom-control custom-control-inline custom-checkbox">
					<?php echo $this->form['attach_method_html']['USER_ID']; ?>
				</div>
					<div class="custom-control custom-control-inline custom-checkbox">
					<?php echo $this->form['user_id_html']; ?>
				</div>

				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col card cart_watcher_overview"></div>
		<div class="col card cart_history"></div>
	</div>
	</main>

</form>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>
