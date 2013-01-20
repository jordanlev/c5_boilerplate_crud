<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$vh = Loader::helper('crud_view', 'automobiles');

echo $dh->getDashboardPaneHeaderWrapper('Colors');

	$display_columns = array(
		'name' => 'Name',
	);
	echo $vh->listTable($this, $colors, $display_columns, 'colors_edit', 'colors_delete');
	
	echo '<p>' . $ih->button('Add New...', $this->action('colors_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
