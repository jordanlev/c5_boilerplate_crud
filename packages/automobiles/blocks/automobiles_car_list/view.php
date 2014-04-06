<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<h2><?php echo h($body_type['name']); ?> Cars</h2>

<?php if (empty($cars)) { ?>
	
	<p>There are no cars of this body type.</p>
	
<?php } else { ?>
	
	<ul>
	<?php foreach ($cars as $car) { ?>
		<li>
			<h3><?php echo h($car['year']); ?> <?php echo h($car['manufacturer_name'])?> <?php echo h($car['name']); ?></h3>
			<div><?php echo $car['description']; ?></div>
		</li>
	<?php } ?>
	</ul>
	
<?php } ?>