<div class="row">
	<div class="col text-center">
		<ul class="nav justify-content-around justify-content-md-between">
			<li class="nav-item mb-3">
				<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-green-light text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>">
					<span>Location info</span>
				</a>
			</li>
			<?php if ($this->DAO_store->hasBioPage()) { ?>
				<li class="nav-item mb-3">
					<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-cyan-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/meet-the-owner">
						<span>Meet the owner</span>
					</a>
				</li>
			<?php } ?>
			<li class="nav-item mb-3">
				<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-orange text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/calendar">
					<span>What's New &amp; Store Calendar</span>
				</a>
			</li>
			<li class="nav-item mb-3">
				<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-green-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="/menu/<?php echo $this->DAO_store->id; ?>">
					<span>Order Now</span>
				</a>
			</li>
		</ul>
	</div>
</div>