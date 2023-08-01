<div>
	&#8226; <?php echo CTemplate::dateTimeFormat($item['time'], TIME, $this->store, CONCISE); ?>
	<?php if ($item['action'] == 'LOW') { ?>
		<span class="text-white-space-nowrap"><?php echo $item['description']; ?></span>
	<?php } ?>
</div>