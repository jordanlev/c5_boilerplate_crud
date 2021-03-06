<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');

Loader::element('editor_config'); //for wysiwyg editor
$al = Loader::helper('concrete/asset_library'); //for image chooser

$is_new = empty($id);
$heading = ($is_new ? 'Add New' : 'Edit') . ' Car';
$action = $is_new ? $this->action('add', $body_type_id) : $this->action('edit', (int)$id);
?>

<?=$dh->getDashboardPaneHeaderWrapper($heading, false, 'span10 offset1', false)?>

	<form method="post" action="<?=$action?>">
		<?=$token?>
		<?=$form->hidden('id', (int)$id); /* redundant, but simplifies processing */ ?>
		
		<div class="ccm-pane-body">

			<table class="form-table">
				<tr>
					<td class="right"><?=$form->label('body_type_id', 'Body Type:')?></td>
					<td><?=$form->select('body_type_id', $body_type_options, $body_type_id)?></td>
				</tr>
			
				<tr>
					<td class="right"><?=$form->label('manufacturer_id', 'Manufacturer:')?></td>
					<td><?=$form->select('manufacturer_id', $manufacturer_options, $manufacturer_id)?></td>
				</tr>
			
				<tr>
					<td class="right"><?=$form->label('year', 'Model Year:')?></td>
					<td>
						<?=$form->text('year', h($year), array('maxlength' => '4', 'class' => 'input-mini'))?>
						<i>4-digit year</i>
					</td>
				</tr>

				<tr>
					<td class="right"><?=$form->label('name', 'Name:')?></td>
					<td><?=$form->text('name', h($name), array('maxlength' => '255'))?></td>
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
						<?=$form->textarea('description', $description, array('class' => 'ccm-advanced-editor'))?>
					</td>
				</tr>
		
				<tr>
					<td class="right"><?=$form->label('photo_fID', 'Photo:')?></td>
					<td>
						<?php
						$file = empty($photo_fID) ? null : File::getByID($photo_fID);
						echo $al->image('photo_fID', 'photo_fID', 'Photo', $file);
						?>
					</td>
				</tr>
			
				<tr>
					<td class="right"><?=$form->label('price', 'Price:')?></td>
					<td>
						<div class="input-prepend">
							<span class="add-on"><?=$currency_symbol?></span><?php /* <-- no whitespace between <span> and <input>! */
							echo $form->text('price', number_format($price, 2), array('class' => 'input-small'));
							?>
						</div>
					</td>
				</tr>
			
			<tr><td colspan="2"><hr></td></tr>
		
				<tr>
					<td>&nbsp;</td>
					<td><h2>Colors</h2></td>
				</tr>
			
				<tr>
					<td>&nbsp;</td>
					<td>
						<?php foreach ($colors as $color): ?>
						<label class="checkbox">
							<input type="checkbox" name="color_ids[]" value="<?=$color['id']?>" <?=(empty($color['has']) ? '' : 'checked="checked"')?>>
							<?=h($color['name'])?>
						</label>
						<?php endforeach; ?>
					</td>
				</tr>
		
			</table>
		
		</div>
		
		<div class="ccm-pane-footer">
			<?=$ih->submit('Save', false, 'right', 'primary')?>
			<?php if ($is_new): ?>
				<?=$form->submit('save-and-duplicate', 'Save &amp; Duplicate&hellip;', array('class' => 'ccm-button-v2-right'))?>
				<?=$form->submit('save-and-add', 'Save &amp; Add New&hellip;', array('class' => 'ccm-button-v2-right'))?>
			<?php endif; ?>
			<?=$ih->button('Cancel', $this->action("view?body_type={$body_type_id}"), 'left')?>
		</div>

	</form>
	
<?=$dh->getDashboardPaneFooterWrapper()?>
