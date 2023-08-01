<nav class="mt-3 d-print-none">
	<ul class="pagination pagination-sm step-bar justify-content-center">
		<li class="page-item order-1 <?php echo ($this->page == 'locations') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_store'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=locations">
				<div class="step-pill"></div>
				Location
			</a>
		</li>
		<li class="page-item <?php echo ($this->page == 'session_menu') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_item'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=session_menu">
				<div class="step-pill"></div>
				Menu
			</a>
		</li>
		<li class="page-item <?php echo ($this->page == 'session') ? ' current' : ((!empty($this->cart_info['cart_info_array']['has_session'])) ? ' complete' : ' disabled'); ?>">
			<a class="page-link" href="main.php?page=session">
				<div class="step-pill"></div>
				Calendar
			</a>
		</li>
	</ul>
</nav>