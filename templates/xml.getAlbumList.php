<albumList>
<?php foreach($_['response'] as $album): ?>
<album <?php foreach($album as $key=>$value): ?>
    <?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?>/>
<?php endforeach; ?>
</albumList>