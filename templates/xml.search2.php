<?php $data = $_['response']['searchResult2']; ?>
<searchResult2>
<?php if (isset($data['artist'])): ?>
<?php foreach($data['artist'] as $artist): ?>
<artist name="<?php echo $artist['name']; ?>" id="<?php echo $artist['id']; ?>"/>
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($data['album'])): ?>
<?php foreach($data['album'] as $album): ?>
<album <?php foreach($album as $key=>$value): ?>
<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
		}else{
			$value = htmlentities($value);
		} 
?>
<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($data['song'])): ?>
<?php foreach($data['song'] as $song): ?>
<song <?php foreach($song as $key=>$value): ?>
<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
		}else{
			$value = htmlentities($value);
		} 
?>
<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
<?php endif; ?>
</searchResult2>
