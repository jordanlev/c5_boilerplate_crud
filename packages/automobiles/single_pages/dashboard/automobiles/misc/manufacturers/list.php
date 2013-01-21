<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$vh = Loader::helper('crud_view', 'automobiles');

echo $dh->getDashboardPaneHeaderWrapper('Manufacturers');
	
	//Reformat boolean column to show "yes" or "no" instead of "1" or "0"
	foreach ($manufacturers as $key => $color) {
		$manufacturers[$key]['isLuxury'] = $color['isLuxury'] ? 'yes'  : 'no';
	}
	
	$display_columns = array(
		'name' => 'Name',
		'country' => 'Country',
		'isLuxury' => 'Luxury Brand?',
	);
	echo $vh->listTable($this, $manufacturers, $display_columns, 'manufacturers_edit', 'manufacturers_delete');
	
	echo '<p>' . $ih->button('Add New...', $this->action('manufacturers_add'), false, 'primary') . '</p>';
	
	echo '<hr>';
	
	echo '<p>' . $ih->button('&lt; Back to Misc. Settings', $this->action('view'), false) . '</p>';
	
echo $dh->getDashboardPaneFooterWrapper();
