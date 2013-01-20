<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Misc. Settings'); ?>

	<ul class="unstyled">
		<li><a href="<?php echo $this->action('colors_list'); ?>">Colors</a></li>
		<li><a href="<?php echo $this->action('manufacturers_list'); ?>">Manufacturers</a></li>
	</ul>
	
	<hr>
	<p><?php echo $ih->button('&lt; Back to Cars', View::url('/dashboard/automobiles/cars'), false); ?></p>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
