<playlist <?php foreach ($_['response']['playlist'] as $key=>$value): ?>
	<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
	}else{
			$value = htmlentities($value);
		} ?>
	<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>>
	<?php foreach($_['response']['entry'] as $entry): ?>
	<entry <?php foreach ($entry as $key=>$value): ?>
	<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
	}else{
			$value = htmlentities($value);
		} ?>
	<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
	<?php endforeach; ?>
</playlist>