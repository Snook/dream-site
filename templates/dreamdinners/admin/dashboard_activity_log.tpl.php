<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_activity.min.js'); ?>
<?php $this->assign('page_title','Store Activity'); ?>
<?php $this->assign('topnav','store'); ?>
<?php $this->setOnLoad("store_activity_init();"); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

	<div class="container-fluid">

		<form method="post">

			<?php if (isset($this->form_array['store_html'])) { ?>
				<div class="row mb-4">
					<div class="col">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Store</span>
							</div>
							<?php echo $this->form_array['store_html']; ?>
						</div>
					</div>
				</div>
			<?php } ?>

			<div class="row mb-3">
				<?php if (isset($this->form_array['filter_html'])) { ?>
					<div class="col">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Activity Type</span>
							</div>
							<?= $this->form_array['filter_html']; ?>
						</div>
					</div>
				<?php } ?>
				<?php if (isset($this->form_array['filter_sub_ot_html'])) { ?>
					<div class="col">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Order Type</span>
							</div>
							<?= $this->form_array['filter_sub_ot_html']; ?>
						</div>
					</div>
				<?php } ?>
				<?php if (isset($this->form_array['timeframe_html'])) { ?>
					<div class="col-3">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text">Days</span>
							</div>
							<?= $this->form_array['timeframe_html']; ?>
						</div>
					</div>
				<?php } ?>
			</div>

			<div class="row mb-3">
				<div class="col text-center">
					<h1><a href="/backoffice/dashboard-activity_log">Store Activity</a></h1>
				</div>
			</div>

			<div class="row">
				<div class="col">
					<div class="activity-feed px-3">
						<?php if (count($this->activity) > 0) { ?>
							<?php $index = 0; foreach ($this->activity as $date => $items) { ?>
								<div class="feed-item">
									<div class="date font-weight-bold mb-1"><?php echo $date; ?></div>
									<?php
									$hasActivity = false;
									foreach ($items as $item)
									{
										if ($this->limit_to != '' && $item['type'] != $this->limit_to)
										{
											continue;
										}

										$index++;

										switch ($item['type'])
										{
											case "RESCHEDULED":
											case "EDITED":
											case "PLACED":
											case "SAVED":
											case "CANCELLED":
												include $this->loadTemplate('admin/subtemplate/store_activity/order_element.tpl.php');
												$hasActivity = true;
												break;
											case CStoreActivityLog::SIDES_ORDER:
												include $this->loadTemplate('admin/subtemplate/store_activity/sides_n_sweets_form_alert.tpl.php');
												$hasActivity = true;
												break;
											case "SESSION CREATED":
												include $this->loadTemplate('admin/subtemplate/store_activity/session_details.tpl.php');
												$hasActivity = true;
												break;
											case "RECIPE_UPDATED":
												include $this->loadTemplate('admin/subtemplate/store_activity/recipe_updated.tpl.php');
												$hasActivity = true;
												break;
											case "INVENTORY":
												include $this->loadTemplate('admin/subtemplate/store_activity/inventory_alert.tpl.php');
												$hasActivity = true;
												break;
											case "CURRENT":
												break;
											default:
												echo 'Unknown event type ' . $item['type'];
										}
									}
									?>
									<?php if (count($items) == 0 || !$hasActivity) { ?>
										<div>&#8226; No Activity</div>
									<?php } ?>
								</div>
							<?php } ?>
						<?php } else { ?>
							<div class="feed-item">
								<div>No Activity in the last <?php echo $this->days_back; ?> days.</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

		</form>

	</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>