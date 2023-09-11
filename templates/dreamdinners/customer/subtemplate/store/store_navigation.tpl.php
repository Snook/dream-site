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
				<a class="m-auto nav-link text-uppercase font-weight-bold rounded-circle bg-green-dark text-white d-flex align-items-center justify-content-center" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order">
					<span>Order Now</span>
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="row">
	<div class="col text-center">
		<ul class="nav justify-content-around justify-content-md-between">
			<li class="mb-3">
				<i class="dd-icon icon-location font-size-extra-extra-large text-green-light m-4"></i>
				<a class="m-auto nav-link text-uppercase font-weight-bold" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>">Location info</a>
			</li>
			<?php if ($this->DAO_store->hasBioPage()) { ?>
				<li class="mb-3">
					<i class="dd-icon icon-friend font-size-extra-extra-large text-cyan-dark m-4"></i>
					<a class="m-auto nav-link text-uppercase font-weight-bold" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/meet-the-owner">Meet the owner</a>
					</li>
			<?php } ?>
			<li class="nav-item mb-3">
				<i class="dd-icon icon-calendar_add font-size-extra-extra-large text-orange m-4"></i>
				<a class="m-auto nav-link text-uppercase font-weight-bold" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/calendar">
					<span>What's New &amp; Store Calendar</span>
				</a>
			</li>
			<li class="nav-item mb-3">
				<i class="dd-icon icon-order_online font-size-extra-extra-large text-green-dark m-4"></i>
				<a class="m-auto nav-link text-uppercase font-weight-bold" style="width: 10rem; height: 10rem;" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order">
					<span>Order Now</span>
				</a>
			</li>
		</ul>
	</div>
</div>