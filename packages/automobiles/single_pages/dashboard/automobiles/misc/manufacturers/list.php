<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

echo $dh->getDashboardPaneHeaderWrapper('Manufacturers');
	
	Loader::library('crud_display_table', 'automobiles');
	$table = new CrudDisplayTable($this);
	$table->addColumn('name', 'Name');
	$table->addColumn('country', 'Country');
	$table->addColumn('is_luxury', 'Luxury Brand?');
	$table->addAction('manufacturers_edit', 'right', 'Edit', 'icon-pencil');
	$table->addAction('manufacturers_delete', 'right', 'Delete', 'icon-trash');
	//Reformat boolean column to show "yes" or "no" instead of "1" or "0"
	foreach ($manufacturers as $key => $mfg) {
		$manufacturers[$key]['is_luxury'] = $mfg['is_luxury'] ? 'yes'  : 'no';
	}
	$table->display($manufacturers);
	
	echo '<p>' . $ih->button('Add New...', $this->action('manufacturers_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
