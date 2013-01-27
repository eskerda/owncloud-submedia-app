<artist <?php foreach($_['response']['artist'] as $key=>$value): ?>
<?php if ($key != 'album'): ?>
<?php echo $key; ?>="<?php echo $value; ?>"<?php endif; ?> <?php endforeach; ?>>
<?php foreach($_['response']['artist']['album'] as $album): ?>
<album <?php foreach($album as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
<?php endforeach; ?>
</artist>