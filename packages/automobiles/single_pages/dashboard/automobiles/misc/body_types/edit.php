<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');

$is_new = empty($id);
$heading = ($is_new ? 'Add New' : 'Edit') . ' Body Type';
$action = $is_new ? $this->action('body_types_add') : $this->action('body_types_edit', (int)$id);
?>

<?=$dh->getDashboardPaneHeaderWrapper($heading, false, 'span6 offset3', false)?>

	<form method="post" action="<?=$action?>">
		<?=$token?>
		<?=$form->hidden('id', (int)$id); /* redundant, but simplifies processing */ ?>
		
		<div class="ccm-pane-body">
			<table class="form-table">
				<tr>
					<td class="right"><?=$form->label('name', 'Name:')?></td>
					<td><?=$form->text('name', h($name), array('maxlength' => '255'))?></td>
				</tr>
				<tr>
					<td class="right"><?=$form->label('url_slug', 'URL Slug:')?></td>
					<td>
						<?=$form->text('url_slug', h($url_slug), array('maxlength' => '255'))?>
					</td>
				</tr>
			</table>
		</div>
		
		<div class="ccm-pane-footer">
			<?=$ih->submit('Save', false, 'right', 'primary')?>
			<?=$ih->button('Cancel', $this->action('body_types_list'), 'left')?>
		</div>
	</form>
	
<?=$dh->getDashboardPaneFooterWrapper()?>

<?php if ($is_new && empty($error)): /* URL Slug-ify... */ ?>
<script type="text/javascript">
var url_slug_was_manually_changed = false;
$(document).ready(function() {
	$('input#name').on('change keyup paste', function() { //http://stackoverflow.com/a/17317620/477513
		if (!url_slug_was_manually_changed) {
			var name = $(this).val();
			var slug = name.toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, ''); //https://gist.github.com/bentruyman/1211400
			$('input#url_slug').val(slug);
		}
	});
	$('input#url_slug').on('change', function() {
		url_slug_was_manually_changed = true;
	});
});
</script>
<?php endif; ?>
