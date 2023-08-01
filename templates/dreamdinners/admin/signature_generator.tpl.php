<?php $this->setScript('head', SCRIPT_PATH . '/admin/signature_generator.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); ?>
<?php $this->setOnload('generate_signature_init();'); ?>
<?php $this->assign('topnav','store'); ?>
<?php $this->assign('page_title','Signature Generator'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<h3 style="margin-bottom:20px;">Signature Generator</h3>

<div style="float:left;width:400px;padding-left:40px;padding-bottom:10px;">
<?php echo $this->form['sig-first_last_html']; ?><br />
<?php echo $this->form['sig-title_html']; ?><br />
<?php echo $this->form['sig-telephone_html']; ?><br />
<?php echo $this->form['sig-cellphone_html']; ?><br />
<?php echo $this->form['sig-faxnumber_html']; ?><br />
<?php echo $this->form['sig-email_html']; ?><br />
<?php echo $this->form['sig-addressline1_html']; ?><br />
<?php echo $this->form['sig-addressline2_html']; ?><br />
<?php echo $this->form['sig-city_html']; ?><br />
<?php echo $this->form['sig-state_html']; ?><br />
<?php echo $this->form['sig-zipcode_html']; ?><br /><br />

<div>Optionally, add your store's id to link the "dreamdinners.com" text to your store's page. Remove id to link to the home page.</div>

<?php echo $this->form['sig-dd_link_html']; ?><br /><br />

<div>Optionally, you can edit these urls. You can leave the defaults, delete the ones you don't want to show or change them to link to your store's pages.</div>

<input type="text" size="50" id="sig_link-blog" name="sig-blog" placeholder="Blog" data-img="blog_feed_green16x16.png" data-title="Blog" value="http://blog.dreamdinners.com" /><br />
<?php /* ?><input type="text" size="50" id="sig_link-youtube" name="sig-youtube" placeholder="YouTube" data-img="you_tube_green16x16.png" data-title="YouTube" value="https://www.youtube.com/dreamdinnersvideo" /><br /><?php */ ?>
<input type="text" size="50" id="sig_link-facebook" name="sig-facebook" placeholder="Facebook" data-img="facebook_green16x16.png" data-title="Facebook" value="https://www.facebook.com/dreamdinners" /><br />
<input type="text" size="50" id="sig_link-twitter" name="sig-twitter" placeholder="Twitter" data-img="twitter_green16x16.png" data-title="Twitter" value="https://twitter.com/dreamdinners" /><br />
<input type="text" size="50" id="sig_link-pinterest" name="sig-pinterest" placeholder="Pinterest" data-img="pinterest_green16x16.png" data-title="Pinterest" value="http://pinterest.com/dreamdinners" /><br />
<?php /* ?><input type="text" size="50" id="sig_link-googleplus" name="sig-googleplus" placeholder="Google+" data-img="google_plus_green16x16.png" data-title="Google+" value="https://plus.google.com/104163195458704615110/posts" /><br /><?php */ ?>
</div>

<div style="float:left;width:400px;">
<div id="sig_preview"></div>
<div style="margin-top:20px;"><input type="button" class="button" onclick="download_signature()" value="Download Signature" /></div>
</div>

<div class="clear"></div>

<p>For instructions on adding this signature to Outlook and OWA, please view the <a href="https://support.dreamdinners.com/solution/articles/5000666137-how-to-change-my-email-or-the-store-mailbox-email-signature-outlook-web-app-" target="_blank">How to Change my email, or, the Store Mailbox Email Signature</a> solution.</p>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>