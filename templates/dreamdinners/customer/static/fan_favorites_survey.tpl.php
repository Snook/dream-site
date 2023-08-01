<?php $this->assign('page_title', 'Dream Dinners Fan Favorites');?>
<?php $this->assign('page_description','Vote for your favorite dinners. Winners will be on our August menu'); ?>
<?php $this->assign('page_keywords','fan favorites, vote, best of the best'); ?>
<?php include $this->loadTemplate('customer/subtemplate/page_header.tpl.php'); ?>

<div style="margin:30px;">
<!--The survey is now closed. You will find the winners on August's menu!-->
<iframe class='embed_frame' data-embed-url='https://fanfavorites.pgtb.me/pJLdkq' data-v-offset='0' data-autoscroll='1' style='display: none;' ></iframe> <script src='https://d1m2uzvk8r2fcn.cloudfront.net/scripts/embed-code/1517592248.min.js' type='text/javascript'></script>
</div>

<?php include $this->loadTemplate('customer/subtemplate/page_footer.tpl.php'); ?>