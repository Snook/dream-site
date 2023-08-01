<?php if (!empty($this->order_customization_json)) {
	$customizations = OrdersCustomization::initOrderCustomizationObj($this->order_customization_json);?>

	<?php foreach($customizations->meal as $key => $pref) { ?>
			<?php switch ($pref->type ) {
				case 'INPUT': ?>
					<?php echo $pref->description ?>:<?php echo htmlentities($pref->value); ?><br>
				<?php break; case 'CHECKBOX': ?>
					<?php echo ($pref->value == 'OPTED_IN' ) ?  $pref->description.(!empty($pref->details) ? ':'.$pref->details:'').'<br>':'';?>
				<?php break; } ?>

	<?php } ?>
<?php } ?>