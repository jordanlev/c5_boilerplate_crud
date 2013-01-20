<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');

Loader::element('editor_config'); //for wysiwyg editor
$al = Loader::helper('concrete/asset_library'); //for image chooser

$is_new = empty($id);
$heading = ($is_new ? 'Add New' : 'Edit') . ' Car';
$action = $is_new ? $this->action('add') : $this->action('edit', (int)$id);
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading); ?>

	<form method="post" action="<?php echo $action; ?>">
		<?php echo $token; ?>
		<?php echo $form->hidden('id', (int)$id); /* redundant, but simplifies processing */ ?>
		<table class="form-table">
			<tr>
				<td class="right"><?php echo $form->label('manufacturerId', 'Manufacturer:'); ?></td>
				<td><?php echo $form->select('manufacturerId', $manufacturer_options, $manufacturerId); ?></td>
			</tr>

			<tr>
				<td class="right"><?php echo $form->label('colorId', 'Color:'); ?></td>
				<td><?php echo $form->select('colorId', $color_options, $colorId); ?></td>
			</tr>

			<tr>
				<td class="right"><?php echo $form->label('year', 'Model Year:'); ?></td>
				<td>
					<?php echo $form->text('year', htmlentities($year), array('maxlength' => '4', 'class' => 'input-mini')); ?>
					<i>4-digit year</i>
				</td>
			</tr>

			<tr>
				<td class="right"><?php echo $form->label('name', 'Name:'); ?></td>
				<td><?php echo $form->text('name', htmlentities($name), array('maxlength' => '255')); ?></td>
			</tr>
			
	<tr><td colspan="2"><hr></td></tr>

			<tr>
				<td>&nbsp;</td>
				<td><h2>Description</h2></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<?php Loader::element('editor_controls'); ?>
					<?php echo $form->textarea('description', $description, array('class' => 'ccm-advanced-editor')); ?>
				</td>
			</tr>
		
			<tr>
				<td class="right"><?php echo $form->label('photoFID', 'Photo:'); ?></td>
				<td>
					<?php
					$photo_file = empty($photoFile) ? null : File::getByID($photoFID);
					echo $al->image('photoFID', 'photoFID', 'Photo', $photo_file);
					?>
				</td>
			</tr>
			
			<tr>
				<td class="right"><?php echo $form->label('price', 'Price:'); ?></td>
				<td>
					<div class="input-prepend">
						<span class="add-on">$</span><?php /* <-- no whitespace between <span> and <input>! */
						echo $form->text('price', number_format($price, 2), array('class' => 'input-small'));
						?>
					</div>
				</td>
			</tr>
			
	<tr><td colspan="2"><hr></td></tr>

			<tr>
				<td class="right">&nbsp;</td>
				<td>
					<?php echo $ih->submit('Save', false, false, 'primary'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->action('view'), false); ?>
				</td>
			</tr>
			
		</table>
	</form>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
