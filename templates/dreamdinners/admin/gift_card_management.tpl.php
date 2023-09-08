<?php
$this->assign('page_title','Gift Card Management');
$this->assign('topnav', 'giftcards');
include $this->loadTemplate('admin/page_header.tpl.php');
?>
      <p align="center"><img src="<?= ADMIN_IMAGES_PATH ?>/gift_card/gc_card_design_banner.gif" align="absbottom"/><br />This section contains the management tools for the Dream Dinners Gift Cards.</p>
<table width="450"  border="0" cellspacing="0" cellpadding="4" align="center">
<tr>
<td><a href="?page=admin_gift_card_load"><img src="<?= ADMIN_IMAGES_PATH ?>/gift_card/spiral_icon_for_links.gif" alt="Load Gift Cards" border="0" align="absmiddle"> <strong>Load Gift Cards</strong></a> - Load funds onto a new or existing Gift Card</td>
</tr>
<tr>
<td><a href="?page=admin_gift_card_order"><img src="<?= ADMIN_IMAGES_PATH ?>/gift_card/spiral_icon_for_links.gif" alt="Order a new gift card" border="0" align="absmiddle"> <strong>Order a New Gift Card</strong></a> - Order and load a traditional (plastic) or virtual eGift card.</td>
</tr>
<tr>
<td><a href="?page=admin_gift_card_balance"><img src="<?= ADMIN_IMAGES_PATH ?>/gift_card/spiral_icon_for_links.gif" alt="Check Balance" border="0" align="absmiddle"> <strong>Check Balance</strong></a> - Check the current balance on a Gift Card</td>
</tr>
<tr>
<td><a href="?page=admin_resend_gift_card_emails"><img src="<?= ADMIN_IMAGES_PATH ?>/gift_card/spiral_icon_for_links.gif" alt="Check Balance" border="0" align="absmiddle"> <strong>Resend Gift Card Emails</strong></a> - Search for orders and resend emails</td>
</tr>

</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>