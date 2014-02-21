<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<label>
	Display cars of body type:
	<?=$form->select('body_type_id', $body_type_options, $body_type_id)?>
</label>