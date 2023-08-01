<?php if (!empty($this->compact) && $this->compact) {?>

<?php include $this->loadTemplate('customer/subtemplate/locations/locations_results_min.tpl.php'); ?>

<?php } else { ?>

<?php include $this->loadTemplate('customer/subtemplate/locations/locations_results_full.tpl.php'); ?>

<?php } ?>