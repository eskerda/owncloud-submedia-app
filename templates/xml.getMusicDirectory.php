<?php if (!empty($_['response']['directory'])): 
    $directory = $_['response']['directory']; ?>
<directory id="<?php echo $directory['id']; ?>" name="<?php echo $directory['name']; ?>">
<?php foreach ($directory['child'] as $child): ?>
<child <?php foreach ($child as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
</directory>
<?php endif; ?>