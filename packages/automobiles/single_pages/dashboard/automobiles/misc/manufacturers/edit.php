<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');

$is_new = empty($id);
$heading = ($is_new ? 'Add New' : 'Edit') . ' Manufacturer';
$action = $is_new ? $this->action('manufacturers_add') : $this->action('manufacturers_edit', (int)$id);
?>

<?=$dh->getDashboardPaneHeaderWrapper($heading, false, 'span8 offset2', false)?>

	<form method="post" action="<?=$action?>">
		<?=$token?>
		<input type="hidden" id="id" name="id" value="<?= (int)$id?>" /><?php /* redundant, but simplifies processing */ /*manual hidden, since C5 overrides with post values */ ?>
		
		<div class="ccm-pane-body">

			<table class="form-table">
				<tr>
					<td class="right"><?=$form->label('name', 'Name:')?></td>
					<td><?=$form->text('name', h($name), array('maxlength' => '255'))?></td>
				</tr>
			
				<tr>
					<td class="right"><?=$form->label('country', 'Country:')?></td>
					<td>
						<?=$form->select('country', $country_options, $country)?>
					</td>
				</tr>
			
				<tr>
					<td class="right">&nbsp;</td>
					<td>
						<label class="checkbox">
							<?=$form->checkbox('is_luxury', 1, $is_luxury)?>
							Luxury Brand
						</label>
					</td>
				</tr>
			</table>
			
		</div>
		
		<div class="ccm-pane-footer">
			<?=$form->submit('duplicate','Save & Duplicate',array('class'=>'ccm-button-v2-right'))?>
			<?=$form->submit('add-new','Save & Add New',array('class'=>'ccm-button-v2-right'))?>
			<?=$form->submit('save','Save',array('class'=>'ccm-button-v2-right primary'))?>
			<?=$ih->button('Cancel', $this->action('manufacturers_list'), 'left')?>
		</div>

	</form>
	
<?=$dh->getDashboardPaneFooterWrapper()?>
