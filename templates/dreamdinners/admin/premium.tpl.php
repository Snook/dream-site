<?php $this->assign('page_title','Quick 6 Premium'); ?>
<?php $this->assign('topnav','store'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
<script>
	function toggleType(typeVal) {
		if ( typeVal == '<?=CPremium::PERCENTAGE?>' ) {
			var myDiv = document.getElementById('premium_percent_div');
			myDiv.style.display = 'block';
			myDiv = document.getElementById('premium_flat_div');
			myDiv.style.display = 'none';

		} else {
			var myDiv = document.getElementById('premium_percent_div');
			myDiv.style.display = 'none';
			myDiv = document.getElementById('premium_flat_div');
			myDiv.style.display = 'block';
		}
	}
</script>

<b>Quick 6 Premium</b><br />
<p> Welcome to the Quick 6 Premium settings section. </p>

<p> To create a premium for all all Quick 6 orders, use the form below:</p>
<form action="" method="get">
  <strong>Store:</strong> <?php echo $this->form_premium['store_html']; ?><br />
</form>
<form name="kithen_markup" action="" method="post" onSubmit="return _check_form(this);" >
  <input type="hidden" name="store" value="<?= $this->form_premium['store']; ?>">
  <table>
    <tbody>
      <tr>
        <td style="padding: 5px;" align="right" width="125"><label for="markup" message="enter a markup type" id="premium_lbl">Markup
            Type:</label>
        </td>
        <td style="padding: 5px;">
          <?= $this->form_premium['premium_type_html'] ?>
        </td>
      </tr>
      <tr>
        <td style="padding: 5px;" align="right" width="125"><label for="markup" message="Please enter a markup value in the format 00.00" id="premium_lbl">Store
            Markup:</label>
        </td>
        <td style="padding: 5px;">
          <div id="premium_flat_div" style="display: <?php if ( $this->form_premium['premium_type'] == CPremium::FLAT || !$this->form_premium['premium_type']) echo 'block';else echo 'none';?>" >$
            <?= $this->form_premium['premium_flat_html'] ?>
            <br />
            * Please format price as $00.00</div>
          <div id="premium_percent_div" style="display: <?php if ( $this->form_premium['premium_type'] == CPremium::PERCENTAGE ) echo 'block';else echo 'none';?>"><?= $this->form_premium['premium_percent_html'] ?>%
            <br />* Please enter a percent value</div>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding: 5px;" align="right"><input type="submit" name="submit_mark_up" value="Save">
        </td>
      </tr>
    </tbody>
  </table>
</form>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>