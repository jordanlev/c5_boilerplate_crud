<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//NOTE: You should not need to modify this file!!
//
//This single_page is different from the others,
// because it does *not* serve as an example of a CRUD interface.
//Instead, it is a more general-purpose single_page that
// dynamically generates a form based on this package's config settings.

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$th = Loader::helper('text');
$form = Loader::helper('form');

$heading = 'Configuration';
$action = $this->action('');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper($heading); ?>

	<form method="post" action="<?php echo $action; ?>">
		<?php echo $token; ?>
		<table class="form-table">
			<?php foreach ($configs as $config): ?>
			<tr>
				<td class="right"><?php echo $form->label($config->key, $th->unhandle($config->key) . ':'); ?></td>
				<td><?php echo $form->text($config->key, h($config->value), array('maxlength' => '255', 'class' => 'input-small')); ?></td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td class="right">&nbsp;</td>
				<td>
					<?php echo $ih->submit('Save', false, false, 'primary'); ?>
				</td>
			</tr>
		</table>
	</form>
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
