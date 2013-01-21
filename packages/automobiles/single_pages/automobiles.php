<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<div class="automobiles-test">
<?php foreach ($cars as $car): ?>
	<?php var_dump($car); ?>
	<hr>
<?php endforeach; ?>
</div>