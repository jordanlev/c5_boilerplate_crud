<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
$disabled = isset($disabled) && $disabled;
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Delete Manufacturer'); ?>

	<?php if (!$disabled): ?>
	<h2>Are you sure you wish to permanently delete the following manufacturer?</h2>
	<?php endif ?>
	
	<table class="form-table">
		<tr>
			<td class="right">Name:</td>
			<td><?php echo htmlentities($name); ?></td>
		</tr>
		<tr>
			<td class="right">Country:</td>
			<td><?php echo htmlentities($country); ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<form method="post" action="<?php echo $this->action('manufacturers_delete', (int)$id); ?>">
					<?php echo $token; ?>
					<?php echo $disabled ? '' : $ih->submit('Delete', false, false, 'error'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->action('manufacturers_list'), false); ?>
				</form>
			</td>
		</tr>
	</table>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
