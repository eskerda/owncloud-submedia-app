<album <?php foreach($_['response']['album'] as $key=>$value): ?>
<?php if ($key != 'song'): ?>
<?php echo $key; ?>="<?php echo $value; ?>"<?php endif; ?> <?php endforeach; ?>>
<?php foreach($_['response']['album']['song'] as $song): ?>
<song <?php foreach($song as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
<?php endforeach; ?>
</album>
