<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Delete Widget'); ?>

	<h2>Are you sure you wish to permanently delete this widget?</h2>
	<table id="boilerplate_crud_form_table">
		<tr>
			<td class="right">Name:</td>
			<td><?php echo htmlentities($name); ?></td>
		</tr>
		<tr>
			<td class="right">Is Something:</td>
			<td><?php echo $isSomething ? 'Yes' : 'No'; ?></td>
		</tr>
		<tr>
			<td class="right">Rating:</td>
			<td><?php echo $rating; ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<form method="post" action="<?php echo $this->action('widget_delete', (int)$id); ?>">
					<?php echo $ih->submit('Delete', false, false, 'error'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->action('widget_list', $categoryId), false); ?>
				</form>
			</td>
		</tr>
	</table>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
