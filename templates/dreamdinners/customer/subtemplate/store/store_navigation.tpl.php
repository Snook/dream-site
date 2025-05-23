<div class="row mb-5">
	<div class="col text-center">
		<ul class="nav justify-content-around">
			<?php if (!$this->DAO_store->hasAvailableCustomerMenu() && $this->DAO_store->hasNewUrl()) { ?>
				<li>
					<a class="m-auto nav-link text-uppercase font-weight-bold" href="<?php echo $this->DAO_store->new_store_url; ?>" rel="follow">
						<i class="dd-icon icon-cart font-size-extra-extra-large text-green-dark m-4 d-block"></i>
						Order Now
					</a>
					<p class="text-center font-weight-bold">Order from our new site!</p>
				</li>
			<?php } else if (!$this->DAO_store->isComingSoon()) { ?>
				<li>
					<a class="m-auto nav-link text-uppercase font-weight-bold" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/order" rel="nofollow">
						<i class="dd-icon icon-cart font-size-extra-extra-large text-green-dark m-4 d-block"></i>
						Order Now
					</a>
				</li>
				<li>
					<a class="m-auto nav-link text-uppercase font-weight-bold" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/calendar">
						<i class="dd-icon icon-calendar_add font-size-extra-extra-large text-orange m-4 d-block"></i>
						What's New &amp; Store Calendar
					</a>
				</li>
			<?php } ?>
			<?php if ($this->DAO_store->hasBioPage()) { ?>
				<li>
					<a class="m-auto nav-link text-uppercase font-weight-bold" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>/meet-the-owner">
						<i class="dd-icon icon-person font-size-extra-extra-large text-cyan-dark m-4 d-block"></i>
						Meet the owner
					</a>
				</li>
			<?php } ?>
			<li>
				<a class="m-auto nav-link text-uppercase font-weight-bold" href="<?php echo $this->DAO_store->getPrettyUrl(); ?>">
					<i class="dd-icon icon-location font-size-extra-extra-large text-green-light m-4 d-block"></i>
					Location info
				</a>
			</li>
		</ul>
	</div>
</div>