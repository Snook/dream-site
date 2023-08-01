<nav class="mt-3 d-print-none">
	<ul class="pagination pagination-sm step-bar justify-content-center">
		<li class="page-item <?php echo ($this->page == 'locations') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_store'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=locations">
				<div class="step-pill"></div>
				Location
			</a>
		</li>
		<li class="page-item <?php echo ($this->page == 'box_select') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_navigation_type'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=box_select">
				<div class="step-pill"></div>
				Box Select
			</a>
		</li>
		<li class="page-item <?php echo ($this->page == 'session') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_session'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=box_delivery_date">
				<div class="step-pill"></div>
				Delivery Date
			</a>
		</li>
	</ul>
</nav>