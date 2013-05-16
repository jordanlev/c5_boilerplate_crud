<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

echo $dh->getDashboardPaneHeaderWrapper('Colors');

	Loader::library('crud_list_table', 'automobiles');
	$table = new CrudListTable($this);
	$table->addColumn('name', 'Name');
	$table->addAction('colors_edit', 'left', 'Edit', 'icon-pencil');
	$table->addAction('colors_delete', 'right', 'Delete', 'icon-trash');
	$table->display($colors);
	
	echo '<p>' . $ih->button('Add New...', $this->action('colors_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
