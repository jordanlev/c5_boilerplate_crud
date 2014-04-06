<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');

$is_new = empty($id);
$heading = ($is_new ? 'Add New' : 'Edit') . ' Manufacturer';
$action = $is_new ? $this->action('manufacturers_add') : $this->action('manufacturers_edit', (int)$id);
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading, false, 'span8 offset2', false); ?>

	<form method="post" action="<?php echo $action; ?>">
		<?php echo $token; ?>
		<?php echo $form->hidden('id', (int)$id); /* redundant, but simplifies processing */ ?>
		
		<div class="ccm-pane-body">

			<table class="form-table">
				<tr>
					<td class="right"><?php echo $form->label('name', 'Name:'); ?></td>
					<td><?php echo $form->text('name', h($name), array('maxlength' => '255')); ?></td>
				</tr>
			
				<tr>
					<td class="right"><?php echo $form->label('country', 'Country:'); ?></td>
					<td>
						<?php echo $form->select('country', $country_options, $country); ?>
					</td>
				</tr>
			
				<tr>
					<td class="right">&nbsp;</td>
					<td>
						<label class="checkbox">
							<?php echo $form->checkbox('is_luxury', 1, $is_luxury); ?>
							Luxury Brand
						</label>
					</td>
				</tr>
			</table>
			
		</div>
		
		<div class="ccm-pane-footer">
			<?php echo $ih->submit('Save', false, 'right', 'primary'); ?>
			<?php echo $ih->button('Cancel', $this->action('manufacturers_list'), 'left'); ?>
		</div>

	</form>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
