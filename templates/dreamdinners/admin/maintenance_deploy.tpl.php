<?php $this->assign('page_title','Deploy'); ?>
<?php $this->assign('topnav','tools'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

			<div class="row mb-4">
				<div class="col">
					<form method="post">
						<button type="submit" name="submit" value="deploy">Deploy</button>
					</form>
				</div>
			</div>

	</div>

<?php echo $this->output; ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>