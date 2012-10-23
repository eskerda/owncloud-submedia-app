<?php $indexes = $_['response']['indexes']; ?>
<indexes lastModified="<?php echo $indexes['lastModified']; ?>">
	<?php foreach ($indexes['index'] as $key=>$index): ?>
	<index name="<?php echo $key; ?>">
		<?php foreach ($index as $artist): ?>
		<artist name="<?php echo $artist['name']; ?>" id="<?php echo $artist['id']; ?>"/>
		<?php endforeach; ?>
	</index>
	<?php endforeach; ?>
</indexes>
