<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$dh = Loader::helper('concrete/dashboard');
$ih = Loader::helper('concrete/interface');
?>

<?php echo $dh->getDashboardPaneHeaderWrapper('Delete Car'); ?>

	<h2>Are you sure you wish to permanently delete the following car?</h2>
	
	<br><br>
	
	<table class="form-table">
		<tr>
			<td class="right">Manufacturer:</td>
			<td><?php echo htmlentities($manufacturer_name); ?></td>
		</tr>
		<tr>
			<td class="right">Color:</td>
			<td><?php echo htmlentities($color_name); ?></td>
		</tr>
		<tr>
			<td class="right">Model Year:</td>
			<td><?php echo htmlentities($year); ?></td>
		</tr>
		<tr>
			<td class="right">Name:</td>
			<td><?php echo htmlentities($name); ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br><br>
				<form method="post" action="<?php echo $this->action('delete', (int)$id); ?>">
					<?php echo $token; ?>
					<?php echo $ih->submit('Delete', false, false, 'error'); ?>
					&nbsp;&nbsp;&nbsp;
					<?php echo $ih->button('Cancel', $this->action('view'), false); ?>
				</form>
			</td>
		</tr>
	</table>

<?php echo $dh->getDashboardPaneFooterWrapper(); ?>
