<?php if (!empty($_['response']['directory'])): 
	$directory = $_['response']['directory']; ?>
<directory id="<?php echo $directory['id']; ?>" name="<?php echo htmlentities($directory['name']); ?>">
<?php foreach ($directory['child'] as $child): ?>
<child <?php foreach ($child as $key=>$value): ?>
	<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
	}else{
			$value = htmlentities($value);
		} ?>
	<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
</directory>
<?php endif; ?>