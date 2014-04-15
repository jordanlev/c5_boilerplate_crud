<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');
?>


<?=$dh->getDashboardPaneHeaderWrapper('Cars')?>

	<form action="<?=$this->action('view')?>" method="get" class="segment-filter form-inline">
		<label for="type">Body Type:</label>
		<?=$form->select('body_type', $body_type_options, $body_type_id)?>
		<noscript><input type="submit" class="btn ccm-input-submit" value="Go"></noscript>
		<span class="loading-indicator" style="display:none;"><img src="<?=ASSETS_URL_IMAGES?>/throbber_white_16.gif" width="16" height="16" alt="loading..." /></span>
	</form>

	<?php if (!empty($body_type_id)): ?>
	
		<hr>
	
		<?php
		Loader::library('crud_display_table', 'automobiles');
		$table = new CrudDisplayTable($this);
		
		$table->addColumn('manufacturer_name', 'Manufacturer');
		$table->addColumn('year', 'Model Year');
		$table->addColumn('name', 'Name');
		
		$table->addAction('edit', 'right', 'Edit', 'icon-pencil');
		$table->addAction('duplicate', 'right', 'Duplicate', 'icon-share-alt');
		$table->addAction('delete', 'right', 'Delete', 'icon-trash');
		
		$table->display($cars);
		?>
	
		<p><?=$ih->button("Add New {$body_type_options[$body_type_id]}...", $this->action('add', $body_type_id), false, 'primary')?></p>
	
	<?php endif ?>
	
<?=$dh->getDashboardPaneFooterWrapper()?>
