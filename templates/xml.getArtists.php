<?php $indexes = $_['response']['artists']; ?>
<artists lastModified="<?php echo $indexes['lastModified']; ?>">
    <?php foreach ($indexes['index'] as $key=>$index): ?>
    <index name="<?php echo $key; ?>">
        <?php foreach ($index as $artist): ?>
        <artist <?php foreach($artist as $key=>$value): ?> 
            <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
        <?php endforeach; ?>
    </index>
    <?php endforeach; ?>
</artists>