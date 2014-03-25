<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<h2><?=h($body_type['name'])?> Cars</h2>

<?php if (empty($cars)) { ?>
	
	<p>There are no cars of this body type.</p>
	
<?php } else { ?>
	
	<ul>
	<?php foreach ($cars as $car) { ?>
		<li>
			<h3><?=h($car['year'])?> <?=h($car['manufacturer_name'])?> <?=h($car['name'])?></h3>
			<div><?=$car['description']?></div>
		</li>
	<?php } ?>
	</ul>
	
<?php } ?>