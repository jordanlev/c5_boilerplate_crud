<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Delete Category'); ?>

	<h2>Are you sure you wish to permanently delete the following category (and all of its widgets)?</h2>
	<table id="boilerplate_crud_form_table">
		<tr>
			<td class="right">Name:</td>
			<td><?php echo htmlentities($name); ?></td>
		</tr>
		<tr>
			<td class="right">Description:</td>
			<td><?php echo htmlentities($description); ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<form method="post" action="<?php echo $this->action('category_delete', $id); ?>">
					<?php echo $ih->submit('Delete', false, false, 'error'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->controller->url('category_list'), false); ?>
				</form>
			</td>
		</tr>
	</table>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
