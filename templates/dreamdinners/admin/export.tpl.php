Export:
<?php if ( isset($this->rows) ) { ?>
	<input type="button" class="btn btn-primary btn-sm" style="cursor:pointer;" value="This Page" onclick="window.location = '<?php echo $_SERVER['REQUEST_URI']; ?>?export=xlsx';" />
<?php } ?>
<?php if (isset($exportAllLink)) { ?>
	<input type="button" class="btn btn-primary btn-sm" style="cursor:pointer;" value="All" onclick="window.location = '<?php echo $exportAllLink; ?>';" />
<?php } ?>