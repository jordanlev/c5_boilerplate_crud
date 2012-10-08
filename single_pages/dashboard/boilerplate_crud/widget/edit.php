<?php defined('C5_EXECUTE') or die(_("Access Denied."));
$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');

$isNew = empty($id);
$categoryId = $category['id'];
$heading = ($isNew ? 'Add New' : 'Edit') . ' Widget in Category ' . htmlentities($category['name']);
$action = $isNew ? $this->action('widget_add', $categoryId) : $this->action('widget_edit', (int)$id);
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading); ?>

	<form method="post" action="<?php echo $action; ?>">
		<?php echo $form->hidden('id', (int)$id); /* redundant, but simplifies validation */ ?>
		<?php echo $form->hidden('categoryId', $categoryId); ?>
		<table id="boilerplate_crud_form_table">
			<tr>
				<td class="right"><?php echo $form->label('name', 'Name:'); ?></td>
				<td><?php echo $form->text('name', htmlentities($name)); ?></td>
			</tr>
			<tr>
				<td class="right">&nbsp;</td>
				<td>
					<label class="checkbox">
						<?php echo $form->checkbox('isSomething', 1, $isSomething); ?>
						Is Something
					</label>
				</td>
			</tr>
			<tr>
				<td class="right"><?php echo $form->label('rating', 'Rating:'); ?></td>
				<td><?php echo $form->text('rating', htmlentities($rating)); ?></td>
			</tr>
			<tr>
				<td class="right">&nbsp;</td>
				<td>
					<?php echo $ih->submit('Save', false, false, 'primary'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->action('widget_list', $categoryId), false); ?>
				</td>
			</tr>
		</table>
	</form>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
