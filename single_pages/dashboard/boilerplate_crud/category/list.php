<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Categories'); ?>

	<table id="boilerplate_crud_list_table">
		<tr>
			<th>Name</th>
			<th>Description</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>

		<?php foreach ($categories as $cat): ?>
		<tr>
			<td><?php echo htmlentities($cat['name']); ?></td>
			<td><?php echo htmlentities($cat['description']); ?></td>
			<td><?php echo $ih->button('View Widgets...', $this->action('widget_list', $cat['id']), false); ?></td>
			<td><?php echo $ih->button('Edit', $this->action('category_edit', $cat['id']), false); ?></td>
			<td><?php echo $ih->button('Delete', $this->action('category_delete', $cat['id']), false); ?></td>

		</tr>
		<?php endforeach; ?>
	</table>
	
	<p><?php echo $ih->button('Add New...', $this->action('category_add'), false, 'primary'); ?></p>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
