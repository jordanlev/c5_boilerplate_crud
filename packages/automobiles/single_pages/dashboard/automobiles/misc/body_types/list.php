<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$vh = Loader::helper('crud_view', 'automobiles');

echo $dh->getDashboardPaneHeaderWrapper('Body Types');
	
	$display_columns = array(
		'name' => 'Name',
	);
	echo $vh->listTable($this, $body_types, $display_columns, 'body_types_edit', 'body_types_delete', 'body_types_sort');
	
	echo '<p>' . $ih->button('Add New...', $this->action('body_types_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
