<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

$isNew = empty($id);
$heading = ($isNew ? 'Add New' : 'Edit') . ' Category';
$action = $isNew ? $this->action('category_add') : $this->action('category_edit', (int)$id);
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading); ?>

	<form method="post" action="<?php echo $action; ?>">
		<?php echo $form->hidden('id', (int)$id); /* redundant, but simplifies validation */ ?>
		<table id="boilerplate_crud_form_table">
			<tr>
				<td class="right"><?php echo $form->label('name', 'Name:'); ?></td>
				<td><?php echo $form->text('name', htmlentities($name)); ?></td>
			</tr>
			<tr>
				<td class="right"><?php echo $form->label('description', 'Description:'); ?></td>
				<td><?php echo $form->text('description', htmlentities($description)); ?></td>
			</tr>
			<tr>
				<td class="right">&nbsp;</td>
				<td>
					<?php echo $ih->submit('Save', false, false, 'primary'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->controller->url('category_list'), false); ?>
				</td>
			</tr>
		</table>
	</form>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
