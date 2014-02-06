<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?=$dh->getDashboardPaneHeaderWrapper('Misc. Settings', false, 'span6 offset3')?>

	<ul class="unstyled">
		<li><a href="<?=$this->action('body_types_list')?>">Body Types</a></li>
		<li><a href="<?=$this->action('colors_list')?>">Colors</a></li>
		<li><a href="<?=$this->action('manufacturers_list')?>">Manufacturers</a></li>
	</ul>
	
	<hr>
	<p><?=$ih->button('&lt; Back to Cars', View::url('/dashboard/automobiles/cars'), false)?></p>
	
<?=$dh->getDashboardPaneFooterWrapper()?>
