<?php $this->assign('page_title','Order Complete'); ?>
<?php $this->assign('topnav', 'guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if ($this->bookingStatus == CBooking::CANCELLED) { ?>
	<h1>Payment for Canceled Order has been processed for <a href="/backoffice/user_details?id=<?php echo $this->orderInfo['user_id']; ?>"><?php echo $this->customerName; ?></a>!</h1>
<?php } else { ?>
	<h1>Order has been processed for <a href="/backoffice/user_details?id=<?=$this->orderInfo['user_id']?>"><?php echo $this->customerName; ?></a>!</h1>
<?php } ?>
	<table style="width: 100%; margin-bottom: 10px;">
		<tr>
			<td>
				<a href="/backoffice/main" class="button">Admin Home</a>
				<a href="/backoffice?session=<?php echo $this->sessionInfo['id']; ?>" class="button">View This Session</a>
				<a href="/backoffice/order-mgr?order=<?php echo $this->orderInfo['id']; ?>" class="button">Edit This Order</a>
				<a href="/backoffice/user_details?id=<?php echo $this->orderInfo['user_id']; ?>" class="button">Guest Details</a>
				<a href="/backoffice/order-history?id=<?php echo $this->orderInfo['user_id']; ?>" class="button">Guest Order History</a>
				<?php if( CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER){ ?>
					<a href="/backoffice/order-mgr-delivered?user=<?php echo $this->orderInfo['user_id']; ?>" class="button">Place New Order for This Guest</a>
				<?php } else { ?>
					<a href="/backoffice/order-mgr?user=<?php echo $this->orderInfo['user_id']; ?><?php echo $this->nextMenuParm; ?>" class="button">Place New Order for This Guest</a>
				<?php } ?>
				<a href="/backoffice/gift-card-order" class="button">Buy a Gift Card</a>
				<a style="margin-top:4px;" href="/backoffice/gift-card-load" class="button">Load a Gift Card</a>
			</td>
			<td align="right">
				<a target="_print" class="button" href="/backoffice/order-details-view-all?customer_print_view=1&amp;session_id=<?php echo $this->sessionInfo['id']; ?>&amp;booking_id=<?php echo $this->bookingInfo['id']; ?>&amp;menuid=<?php echo $this->menuInfo['menu_id']; ?>"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" border="0" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /> Print Order</a>
			</td>
		</tr>
	</table>

	<div style="border: 1px dashed #666666; background-color: #fff;">
		<div style="margin: 10px; ">
			<?php if ($this->is_delivered_order)
			{
				include $this->loadTemplate('admin/order_details_table_delivered.tpl.php');
			} else
			{
				include $this->loadTemplate('admin/order_details_table.tpl.php');
			} ?>
		</div>
	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>