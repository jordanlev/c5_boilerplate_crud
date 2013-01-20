<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

$vh = Loader::helper('crud_view', 'automobiles');
$display_columns = array(
	'manufacturer_name' => 'Manufacturer',
	'color_name' => 'Color',
	'year' => 'Model Year',
	'name' => 'Name',
);
?>


<?php echo $dh->getDashboardPaneHeaderWrapper('Cars'); ?>
	
	<?php echo $vh->listTable($this, $cars, $display_columns); ?>
	
	<p><?php echo $ih->button('Add New Car...', $this->action('add'), false, 'primary'); ?></p>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
