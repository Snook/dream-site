<?php
$this->assign('page_title','Shipping Boxes Shipping Report');
$this->setCSS(CSS_PATH . '/admin/admin-styles-reports.css'); ?>

<?php if (!isset($this->suppress_header_footer)) { ?>
	<?php $this->assign('page_title','Reports'); ?>
	<?php $this->assign('print_view', true); ?>
	<?php $this->assign('suppress_table_wrapper', true); ?>
	<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>
	<div class="container">
<?php } ?>


<div id="" class="col-12">
	<h2 class="text-center"><?php echo $this->storeInfo['store_name'];?></h2>
	<h3 class="text-center mb-4"><?php echo $this->TypeStr . " " . CTemplate::dateTimeFormat($this->Date, VERBOSE_DATE);?></h3>

<?php foreach($this->orderInfo as $order_id => $thisOrder) { ?>

	<div class="row">
	<!-- Order Header -->
		<div class="col-6 font-weight-bold"><? echo $thisOrder['info']['menu']; ?> order for <?php echo $thisOrder['info']['name']; ?></div>
		<div class="col-6"> Shipping to <?php echo $thisOrder['info']['name'] ?><br /> Delivered on  <?php echo $thisOrder['info']['delivery_date'] ?></div>

		<div class="col-12">
			<?php foreach($thisOrder['boxes'] as $biid  => $thisBox) { ?>
				<div class="col-4"><? echo $thisBox['box_name']; ?></div>

				<div class="col-8 ">
					<ul>
						<?php foreach($thisBox['items'] as $mid  => $thisItem) { ?>
						<li>

							<? echo $thisItem; ?>

						</li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>
		<?php } ?>

		</div>
	</div>
</div>

<div id="" class="col-12">
	<h2 class="text-center">Item and Serving Counts</h2>
	<table id="" class="table table-striped table-bordered">
		<thead>
		<th scope="col">Entr&eacute;e Name</th>
		<th scope="col">Medium Items</th>
		<th scope="col">Large Items</th>
		<th scope="col">Total Items</th>
		<th scope="col">Total Servings</th>
		<th scope="col">Total Dinners for Ordering</th>
		</thead>
		<?php foreach($this->entreeCounts as $eid => $thisEntree) { ?>
			<tr >
				<th scope="row"><?php echo $thisEntree['name'];?></th>
				<td ><?php echo $thisEntree['medium_count'];?></td>
				<td><?php echo $thisEntree['large_count'];?></td>
				<td><?php echo $thisEntree['medium_count'] + $thisEntree['large_count'];?></td>
				<td><?php echo $thisEntree['total_servings'];?></td>
				<td><?php echo $thisEntree['total_servings'] / 6;?></td>
			</tr>
		<?php } ?>

	</table>
</div>

<?php if (!isset($this->suppress_header_footer)) { ?>
	<?php if (empty($this->suppress_table_wrapper)) { ?>
		</div>
	<?php } ?>
	</body>
	</html>
<?php } ?>