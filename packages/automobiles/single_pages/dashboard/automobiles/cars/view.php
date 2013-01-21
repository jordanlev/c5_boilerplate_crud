<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$vh = Loader::helper('crud_view', 'automobiles');
$form = Loader::helper('form');
?>


<?php echo $dh->getDashboardPaneHeaderWrapper('Cars'); ?>

	<form action="<?php echo $this->action('view'); ?>" method="get" class="body-type-filter form-inline">
		<label for="type">Body Type:</label>
		<?php echo $form->select('type', $body_type_options, $body_type_id); ?>
		<input type="submit" class="btn ccm-input-submit" value="Go">
		<span class="loading-indicator"><img src="<?php echo ASSETS_URL_IMAGES; ?>/throbber_white_16.gif" width="16" height="16" alt="loading..." /></span>
	</form>

	<?php if (!empty($body_type_id)): ?>
	
		<hr>
	
		<?php
		$display_columns = array(
			'manufacturer_name' => 'Manufacturer',
			'year' => 'Model Year',
			'name' => 'Name',
		);
		echo $vh->listTable($this, $cars, $display_columns);
		?>
	
		<p><?php echo $ih->button("Add New {$body_type_options[$body_type_id]}...", $this->action('add', $body_type_id), false, 'primary'); ?></p>
	
	<?php endif ?>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
