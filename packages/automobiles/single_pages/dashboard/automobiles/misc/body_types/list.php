<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

echo $dh->getDashboardPaneHeaderWrapper('Body Types');
	
	Loader::library('crud_list_table', 'automobiles');
	$table = new CrudListTable($this);
	$table->addColumn('name', 'Name');
	$table->addAction('body_types_edit', 'left', 'Edit', 'icon-pencil');
	$table->addAction('body_types_sort', 'right', 'Drag To Sort', 'icon-resize-vertical', true);
	$table->addAction('body_types_delete', 'right', 'Delete', 'icon-trash');
	$table->display($body_types);
	
	echo '<p>' . $ih->button('Add New...', $this->action('body_types_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
