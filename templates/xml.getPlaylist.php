<playlist <?php foreach ($_['response']['playlist'] as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>>
    <?php foreach($_['response']['entry'] as $entry): ?>
    <entry <?php foreach ($entry as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
    <?php endforeach; ?>
</playlist>