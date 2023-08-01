<div id="calendar_holder">
  <?php if ( isset($this->rows) ) { ?>
    <?php include $this->loadTemplate('admin/subtemplate/calendar.tpl.php'); ?>
  <?php } else { ?>
    <img src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" alt="Processing" /> Loading ...
  <?php } ?>
</div>
