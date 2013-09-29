<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

echo $dh->getDashboardPaneHeaderWrapper('Body Types', false, 'span6 offset3');
	
	Loader::library('crud_display_table', 'automobiles');
	$table = new CrudDisplayTable($this);
	$table->addColumn('name');
	$table->addAction('body_types_sort', 'left', 'Drag To Sort', 'icon-resize-vertical', true);
	$table->addAction('body_types_edit', 'right', 'Edit', 'icon-pencil');
	$table->addAction('body_types_delete', 'right', 'Delete', 'icon-trash');
	$table->display($body_types);
	
	echo '<p>' . $ih->button('Add New...', $this->action('body_types_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
